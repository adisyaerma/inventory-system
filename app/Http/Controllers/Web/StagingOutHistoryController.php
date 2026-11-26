<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutHistoryDetailExport;
use App\Exports\StagingOutHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\StagingIn;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StagingOutHistoryDetail;
use App\Services\StagingInReturnService;
use App\Services\StagingOutHistoryService;
use App\Support\CurrentUser;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * Menampilkan riwayat (history) staging out: 1 baris per "siklus hidup"
 * staging_out (staging_out_histories) beserta detail kejadian di
 * dalamnya (staging_out_history_details).
 *
 * Berbeda dengan staging in, siklus staging_out linear dan tidak pernah
 * "berpindah" ke mana-mana: dibuat -> picking dikonfirmasi -> dikirim
 * (DO terbit). Karena itu status pengiriman (picking_date, do_number,
 * delivery_date) dibaca langsung dari kolom header staging_out_histories
 * (sudah disinkronkan tiap event terjadi), bukan dihitung ulang dari
 * detail terakhir seperti qty pada staging in.
 *
 * is_active di sini HANYA menandai apakah baris staging_out aslinya
 * masih ada (lihat komentar di migration) — tidak ada hubungannya
 * dengan status pengiriman. Staging out yang sudah delivered tetap
 * is_active = true selama baris aslinya belum dihapus.
 *
 * Halaman ini pada dasarnya read-only (audit log). Satu-satunya aksi tulis
 * adalah bulkDestroy(): hapus permanen history yang dipilih.
 */
class StagingOutHistoryController extends Controller
{
    /**
     * Event yang dianggap "menghapus" siklus staging out tanpa ada
     * penyelesaian pengiriman yang jelas.
     */
    private const REMOVED_EVENTS = ['deleted', 'bulk_deleted', 'reset_by_import'];

    /**
     * Label & warna untuk event 'restored' (pemulihan dari Selesai ke
     * Staging Out). Dipakai sebagai tambahan di atas
     * StagingOutHistoryDetail::EVENT_TYPES / ::EVENT_COLORS, jadi event ini
     * tetap tampil rapi walau belum didaftarkan di model. Kalau nanti
     * didaftarkan di model, nilai di model yang dipakai.
     */
    private const EXTRA_EVENT_TYPES = ['restored' => 'Dipulihkan'];

    private const EXTRA_EVENT_COLORS = ['restored' => 'info'];

    // =====================================================================
    //  "SATU RECORD" = BEBERAPA HISTORY DARI SO + BARANG + LINE ITEM SAMA
    // =====================================================================
    //
    // Kalau satu barang diambil dari lebih dari satu sumber, staging out
    // tersimpan sebagai beberapa baris -- dan tiap barisnya punya history
    // sendiri. Di layar, history-history itu digabung jadi SATU baris
    // (tidak ada tabel/kolom baru). Kunci penggabung: No. SO + barang +
    // line item + customer + status yang sama (aktif / selesai / dihapus).
    // No. SO kosong tidak pernah digabung.

    /** @var array<int, EloquentCollection> */
    protected array $groupCache = [];

    /**
     * Semua history dalam satu record (urut id; yang pertama = yang tampil di tabel).
     */
    private function groupOf(StagingOutHistory $history): EloquentCollection
    {
        if (! $history->group_key) {
            return new EloquentCollection([$history]);
        }

        $query = StagingOutHistory::where('group_key', $history->group_key)
            ->where('is_active', $history->is_active)
            ->orderBy('id');

        $history->delivery_date
            ? $query->whereNotNull('delivery_date')
            : $query->whereNull('delivery_date');

        $rows = $query->get();

        return $rows->isEmpty() ? new EloquentCollection([$history]) : $rows;
    }

    private function groupFor(StagingOutHistory $history): EloquentCollection
    {
        return $this->groupCache[$history->id] ??= $this->groupOf($history);
    }

    /**
     * Id yang dipilih di tabel + semua history saudaranya dalam record yang sama.
     */
    private function expandIds(array $ids): array
    {
        return StagingOutHistory::whereIn('id', $ids)->get()
            ->flatMap(fn (StagingOutHistory $h) => $this->groupOf($h))
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Batasi query ke history "utama" tiap record (id terkecil di grupnya).
     */
    private function onlyGroupRepresentatives($query)
    {
        return $query->whereNotExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('staging_out_histories as sib')
                ->whereColumn('sib.group_key', 'staging_out_histories.group_key')
                ->whereRaw('sib.is_active = staging_out_histories.is_active')
                ->whereRaw('(sib.delivery_date IS NULL) = (staging_out_histories.delivery_date IS NULL)')
                ->whereRaw('sib.id < staging_out_histories.id');
        });
    }

    /**
     * Qty saat ini satu history: dari staging_outs.qty bila masih aktif,
     * atau dari qty_after event terakhir bila sudah tidak aktif.
     */
    private function memberQty(StagingOutHistory $history, ?StagingOut $staging = null): int
    {
        if ($history->is_active) {
            $staging ??= StagingOut::find($history->staging_out_id);

            return (int) optional($staging)->qty;
        }

        $lastAfter = $history->relationLoaded('details')
            ? $history->details->whereNotNull('qty_after')->last()?->qty_after
            : $history->details()->reorder()->whereNotNull('qty_after')->orderByDesc('id')->value('qty_after');

        return (int) ($lastAfter ?? $history->initial_qty);
    }

    public function index(Request $request)
    {
        // Satu record (walau dari beberapa sumber) dihitung satu.
        $records = fn () => $this->onlyGroupRepresentatives(StagingOutHistory::query());

        $totalHistory = $records()->count();

        $createdToday = $records()->whereDate('staging_out_histories.created_at', now()->toDateString())->count();

        $activeCount = $records()->where('staging_out_histories.is_active', true)
            ->whereNull('staging_out_histories.delivery_date')
            ->count();

        // Selesai = delivery_date sudah terisi, TIDAK peduli is_active —
        // termasuk yang baris aslinya sudah dihapus otomatis (auto-archive).
        $deliveredCount = $records()->whereNotNull('staging_out_histories.delivery_date')->count();

        // Dihapus = hilang SEBELUM sempat terkirim (bukan auto-archive
        // karena delivery_date sudah terisi).
        $removedCount = $records()->where('staging_out_histories.is_active', false)
            ->whereNull('staging_out_histories.delivery_date')
            ->count();

        $statusOptions = [
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dihapus' => 'Dihapus',
        ];

        $eventOptions = StagingOutHistoryDetail::EVENT_TYPES + self::EXTRA_EVENT_TYPES;

        return view('staging_out_history', compact(
            'totalHistory',
            'createdToday',
            'activeCount',
            'deliveredCount',
            'removedCount',
            'statusOptions',
            'eventOptions'
        ));
    }

    /**
     * Export tabel history ke Excel, menghormati filter yang sama dengan
     * yang dipakai di tabel — lihat App\Exports\StagingOutHistoryExport.
     */
    public function export(Request $request)
    {
        $filename = 'staging_out_history_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new StagingOutHistoryExport($request), $filename);
    }

    /**
     * Query dasar: staging_out_histories digabung dengan item,
     * staging_outs (untuk qty berjalan bila masih aktif), dan detail
     * TERAKHIR dari masing-masing history (untuk badge event & aktivitas
     * terakhir). Status pengiriman TIDAK diambil dari sini — itu langsung
     * dari kolom header (picking_date/do_number/delivery_date).
     */
    private function baseQuery()
    {
        $latestDetail = DB::table('staging_out_history_details as d')
            ->select(
                'd.staging_out_history_id',
                'd.event_type',
                'd.qty_after',
                'd.meta',
                'd.created_at as event_at'
            )
            ->whereRaw('d.id = (
                select max(d2.id) from staging_out_history_details d2
                where d2.staging_out_history_id = d.staging_out_history_id
            )');

        // Meta event "created" (snapshot asal barang: stok/eksternal +
        // lokasi + lot) -- dipakai untuk menandai history Selesai yang
        // barangnya diambil dari stok.
        $createdDetail = DB::table('staging_out_history_details as c')
            ->select('c.staging_out_history_id', 'c.meta as created_meta')
            ->where('c.event_type', 'created')
            ->whereRaw('c.id = (
                select min(c2.id) from staging_out_history_details c2
                where c2.staging_out_history_id = c.staging_out_history_id
                and c2.event_type = ?
            )', ['created']);

        return StagingOutHistory::query()
            ->select('staging_out_histories.*')
            ->leftJoin('items', 'items.id', '=', 'staging_out_histories.item_id')
            ->leftJoin('staging_outs', 'staging_outs.id', '=', 'staging_out_histories.staging_out_id')
            ->leftJoinSub($latestDetail, 'ld', function ($join) {
                $join->on('ld.staging_out_history_id', '=', 'staging_out_histories.id');
            })
            ->leftJoinSub($createdDetail, 'cd', function ($join) {
                $join->on('cd.staging_out_history_id', '=', 'staging_out_histories.id');
            })
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
                'staging_outs.qty as running_qty',
                'staging_outs.source_type as running_source_type',
                'ld.event_type as last_event_type',
                DB::raw('(select d3.qty_after from staging_out_history_details d3 where d3.staging_out_history_id = staging_out_histories.id and d3.qty_after is not null order by d3.id desc limit 1) as last_qty_after'),
                'ld.meta as last_meta',
                'ld.event_at as last_event_at',
                'cd.created_meta as created_meta',
            ]);
    }

    /**
     * Server-side DataTables source untuk tabel history.
     */
    public function data(Request $request)
    {
        $query = $this->baseQuery();

        // Satu record tampil sekali saja (history dari beberapa sumber digabung).
        $this->onlyGroupRepresentatives($query);

        // Urutan bawaan (terbaru di atas) hanya kalau user belum klik header kolom.
        if (! $request->has('order')) {
            $query->orderByDesc('staging_out_histories.id');
        }

        // Filter dipusatkan di StagingOutHistoryExport supaya tabel & export
        // selalu identik.
        StagingOutHistoryExport::applyFilters($query, $request);

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->orderColumn('delivery_instruction_date', 'staging_out_histories.delivery_instruction_date $1, staging_out_histories.id $1')
            ->orderColumn('customer', 'staging_out_histories.customer $1, staging_out_histories.id $1')
            ->orderColumn('line_item', 'staging_out_histories.line_item $1, staging_out_histories.id $1')

            ->addColumn('checkbox', function ($row) {
                // Semua baris bisa dicentang. data-active=1 menandai history
                // yang barisnya masih ada di Staging Out (peringatan tambahan
                // di konfirmasi Hapus).

                // data-restorable=1 menandai history berstatus "Selesai"
                // (sudah terkirim & barisnya sudah tidak ada di Staging Out):
                // hanya ini yang ikut dihitung/dikirim tombol Pulihkan.
                $restorable = ! $row->is_active && (bool) $row->delivery_date;

                return '<input type="checkbox" class="form-check-input historyCheckbox"
                    data-active="'.($row->is_active ? '1' : '0').'"
                    data-restorable="'.($restorable ? '1' : '0').'"
                    value="'.$row->id.'">';
            })

            ->addColumn('item_so', function ($row) {
                return '
                    <small class="fw-bold">'.e($row->item_code).'</small>
                    <div class="text-muted">'.e($row->item_name).'</div>
                    <div class="text-primary small">'.e($row->so_number ?: '-').'</div>
                ';
            })

            ->orderColumn('item_so', 'items.name $1')

            // Pencarian global DataTables diganti dengan logika bersama
            // (sama dengan export). Keyword = search[value] dari DataTables.
            //
            // Argumen ke-2 HARUS false: kalau true, Yajra tetap menjalankan
            // pencarian global bawaannya (LIKE ke SEMUA kolom yang searchable,
            // termasuk kolom hasil addColumn seperti item_so yang bukan kolom
            // database) lalu menggabungkannya dengan AND ke filter di bawah ini,
            // sehingga pencarian kode/nama barang tidak pernah lolos.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));

                if ($keyword === '') {
                    StagingOutHistoryExport::applySearch($query, $keyword);

                    return;
                }

                // Pencarian bawaan (StagingOutHistoryExport::applySearch) tetap
                // dipakai apa adanya, lalu DITAMBAH (OR) pencarian ke kode
                // barang & nama barang dari tabel items yang sudah di-join.
                // LOWER() + LIKE supaya tidak peka huruf besar/kecil di DB
                // apa pun (LIKE di PostgreSQL peka huruf besar/kecil).
                $like = '%'.addcslashes(mb_strtolower($keyword), '\\%_').'%';

                $query->where(function ($search) use ($keyword, $like) {
                    $search->where(function ($existing) use ($keyword) {
                        StagingOutHistoryExport::applySearch($existing, $keyword);
                    })
                        ->orWhereRaw('LOWER(items.item_code_internal) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(items.name) LIKE ?', [$like]);
                });
            }, false)

            ->editColumn('line_item', function ($row) {
                return $row->line_item ?: '-';
            })

            ->editColumn('delivery_instruction_date', function ($row) {
                return $row->delivery_instruction_date ? $row->delivery_instruction_date->format('d M Y') : '-';
            })

            ->addColumn('current_qty', function ($row) {
                return $this->currentQtyMarkup($row);
            })

            ->addColumn('shipping_status', function ($row) {
                return $this->shippingStatusMarkup($row);
            })

            ->addColumn('row_status', function ($row) {
                $status = $this->statusMeta($row->is_active, $row->delivery_date);

                return '<span class="badge bg-'.$status['color'].'-subtle text-'.$status['color'].'">'.$status['label'].'</span>';
            })

            ->addColumn('last_activity', function ($row) {
                return $this->lastActivityMarkup($row);
            })

            ->addColumn('action', function ($row) {
                return '
                    <button type="button"
                        class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnViewHistory"
                        data-id="'.$row->id.'"
                        title="Lihat Detail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path fill="currentColor" d="M12 9a3 3 0 1 0 0 6a3 3 0 0 0 0-6m0 8c-4.42 0-8.27-2.94-9.5-7c1.23-4.06 5.08-7 9.5-7s8.27 2.94 9.5 7c-1.23 4.06-5.08 7-9.5 7"/>
                        </svg>
                    </button>
                ';
            })

            ->rawColumns([
                'checkbox',
                'item_so',
                'current_qty',
                'shipping_status',
                'row_status',
                'last_activity',
                'action',
            ])

            ->make(true);
    }

    /**
     * Pulihkan sejumlah history berstatus "Selesai" sekaligus -- dipanggil
     * dari tombol "Pulihkan" setelah user centang baris-baris di tabel
     * (pola sama dengan bulkRestore di Staging In History).
     *
     * Tujuannya: barang yang sudah terlanjur "terkirim" (mis. salah input)
     * dikembalikan ke Staging Out supaya bisa diedit lagi seperti biasa.
     *
     * Untuk setiap history yang valid (is_active=false DAN delivery_date
     * terisi, yaitu status "Selesai"):
     * 1. Rekonstruksi data staging_out persis sebelum diarsipkan (lihat
     *    reconstructSnapshot()) dari header + detail history-nya sendiri,
     *    karena baris staging_outs aslinya sudah hilang.
     * 2. Buat BARIS BARU di staging_outs dari snapshot itu, dengan
     *    delivery_receipt_date DIKOSONGKAN. No DO & tgl picking tetap
     *    dipertahankan. Baris staging_outs lain tidak disentuh.
     * 3. History yang sama "dihidupkan" lagi: staging_out_id diarahkan ke
     *    baris baru, is_active=true, delivery_date dikosongkan -> status
     *    kembali "Aktif".
     * 4. Dicatat 1 detail baru event_type 'restored' di timeline.
     *
     * STOK TIDAK DISENTUH. Kalau barangnya diambil dari stok, potongan
     * stok & mutasi keluarnya dari saat pertama dibuat masih berlaku
     * (barang memang sudah dipotong), jadi baris pulihan hanya
     * "menyambung" ke transaksi itu: source_type/lokasi/lot disalin, tanpa
     * memotong stok lagi. Kalau nanti baris pulihan dihapus atau diubah,
     * pembatalan/penyesuaian stoknya berjalan normal seperti baris lain.
     *
     * Yang dilewati dan dilaporkan sebagai error per-baris (tidak
     * menggagalkan baris lain): status "Aktif" (barisnya masih ada) dan
     * "Dihapus" (belum pernah terkirim; stoknya sudah dikembalikan saat
     * dihapus, jadi memulihkannya butuh memotong stok lagi -- belum
     * didukung).
     *
     * History lama yang dibuat sebelum asal barang dicatat tidak punya data
     * lokasi stok, jadi dipulihkan sebagai barang Eksternal (tanpa
     * keterkaitan ke stok) dan hal ini dilaporkan di 'errors'.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_out_histories,id',
        ]);

        $restored = 0;
        $errors = [];

        // Satu record bisa terdiri dari beberapa history (1 per sumber):
        // pulihkan semuanya.
        $requestedIds = array_map('intval', $request->ids);
        $allIds = $this->expandIds($requestedIds);

        DB::transaction(function () use ($allIds, $requestedIds, &$restored, &$errors) {

            $histories = StagingOutHistory::whereIn('id', $allIds)
                ->lockForUpdate()
                ->get();

            foreach ($histories as $history) {

                $label = ($history->so_number ?: 'History #'.$history->id);

                if ($history->is_active) {
                    $errors[] = "{$label}: dilewati -- statusnya Aktif, datanya masih ada di Staging Out.";

                    continue;
                }

                if (! $history->delivery_date) {
                    $errors[] = "{$label}: dilewati -- statusnya \"Dihapus\", hanya history berstatus \"Selesai\" yang bisa dipulihkan.";

                    continue;
                }

                try {

                    // Dibungkus DB::transaction() TERPISAH per baris (nested)
                    // supaya Laravel memakai SAVEPOINT: kegagalan satu baris
                    // tidak meng-abort seluruh transaksi luar (Postgres).
                    $note = null;

                    DB::transaction(function () use ($history, &$note) {

                        // Detail dimuat kronologis (lama -> baru) untuk replay.
                        $history->load(['details' => function ($query) {
                            $query->reorder()->orderBy('created_at')->orderBy('id');
                        }]);

                        $snapshot = $this->reconstructSnapshot($history);

                        if ($snapshot['source_known'] === false) {
                            $note = 'asal barang tidak tercatat di history lama, dipulihkan sebagai barang Eksternal (tidak terhubung ke stok).';
                        }

                        $newStaging = StagingOut::create([
                            'so_number' => $snapshot['so_number'],
                            'customer' => $snapshot['customer'],
                            'source_type' => $snapshot['source_type'],
                            'item_id' => $snapshot['item_id'],
                            'line_item' => $snapshot['line_item'],
                            'qty' => $snapshot['qty'],
                            'location_id' => $snapshot['location_id'],
                            'lot' => $snapshot['lot'],
                            'delivery_instruction_date' => $snapshot['delivery_instruction_date'],
                            'picking_date' => $snapshot['picking_date'],
                            'do_number' => $snapshot['do_number'],
                            // Inti pemulihan: tgl resi pengiriman dikosongkan.
                            'delivery_receipt_date' => null,
                        ]);

                        $newStaging->forceFill(['group_key' => $history->group_key])->save();

                        $previousReceiptDate = $history->delivery_date instanceof \DateTimeInterface
                            ? $history->delivery_date->format('Y-m-d')
                            : $history->delivery_date;

                        $history->staging_out_id = $newStaging->id;
                        $history->is_active = true;
                        $history->delivery_date = null;
                        $history->save();

                        // Sambungkan lagi ke Staging In asal (kalau barangnya
                        // dulu dari Staging In), supaya kalau baris pulihan ini
                        // diedit / dihapus, qty dikembalikan ke Staging In.
                        // Qty Staging In sendiri TIDAK diubah sekarang: barang
                        // memang kembali "terpakai" oleh baris Staging Out ini.
                        $stagingInId = $snapshot['staging_in_id'];

                        if ($stagingInId) {
                            $in = StagingIn::find($stagingInId);

                            if ($in) {
                                // Baris Staging In masih ada (qty belum habis).
                                app(StagingInReturnService::class)->link($newStaging, $in);
                            } elseif (is_array($snapshot['staging_in_snapshot'])) {
                                // Sudah terhapus karena qty-nya habis: cukup
                                // simpan id + snapshot; baris dibuat ulang oleh
                                // StagingInReturnService::giveBack() saat qty
                                // memang dikembalikan.
                                $newStaging->staging_in_id = $stagingInId;
                                $newStaging->staging_in_snapshot = json_encode($snapshot['staging_in_snapshot']);
                                $newStaging->save();
                            } else {
                                $note = 'asal Staging In sudah terhapus dan datanya tidak tercatat, dipulihkan sebagai barang Eksternal.';
                            }
                        }

                        StagingOutHistoryDetail::create([
                            'staging_out_history_id' => $history->id,
                            'event_type' => 'restored',
                            'qty_before' => 0,
                            'qty_change' => $snapshot['qty'],
                            'qty_after' => $snapshot['qty'],
                            'meta' => [
                                'restored_staging_out_id' => $newStaging->id,
                                'restored_staging_in_id' => $newStaging->staging_in_id,
                                'cleared_delivery_receipt_date' => $previousReceiptDate,
                            ],
                            'notes' => 'Dipulihkan ke Staging Out, tgl resi pengiriman dikosongkan.',
                            'performed_by' => CurrentUser::email(),
                        ]);
                    });

                    // Dihitung per record yang dipilih, bukan per sumber.
                    if (in_array((int) $history->id, $requestedIds, true)) {
                        $restored++;
                    }

                    if ($note) {
                        $errors[] = "{$label}: berhasil dipulihkan, tapi {$note}";
                    }

                } catch (\Throwable $e) {
                    $errors[] = "{$label}: gagal dipulihkan -- ".$e->getMessage();
                }
            }
        });

        return response()->json([
            'success' => true,
            'restored' => $restored,
            'errors' => $errors,
            'message' => $restored.' data berhasil dipulihkan'
                .(count($errors) ? ', '.count($errors).' catatan/baris dilewati.' : '.'),
        ]);
    }

    /**
     * Rekonstruksi kondisi staging_out PERSIS SEBELUM diarsipkan, dengan
     * "memutar ulang" detail history (kronologis) di atas snapshot awal
     * (kolom header staging_out_histories = data saat pertama dibuat):
     *
     * - Field yang tidak disinkronkan ke header (so_number, customer,
     *   line_item, tgl instruksi kirim, item) diambil dari 'new' pada
     *   event 'updated' yang terakhir mengubahnya.
     * - picking_date & do_number dibaca dari header, karena header selalu
     *   disinkronkan ke nilai terbaru (lihat
     *   StagingOutHistoryService::HEADER_SYNC_MAP).
     * - Asal barang (stok/eksternal + lokasi + lot) dan qty akhir dipakai
     *   dari reconstructSource() yang sudah ada.
     * - delivery_receipt_date SENGAJA tidak disalin (inti pemulihan).
     *
     * 'source_known' = false kalau history dibuat sebelum snapshot asal
     * barang dicatat (maka dianggap Eksternal).
     */
    private function reconstructSnapshot(StagingOutHistory $history): array
    {
        $snapshot = [
            'item_id' => $history->item_id,
            'so_number' => $history->so_number,
            'customer' => $history->customer,
            'line_item' => $history->line_item,
            'delivery_instruction_date' => $history->delivery_instruction_date,
        ];

        foreach ($history->details as $detail) {
            if ($detail->event_type !== 'updated' || ! $detail->meta) {
                continue;
            }

            $meta = is_string($detail->meta) ? json_decode($detail->meta, true) : $detail->meta;

            foreach ((array) $meta as $field => $change) {
                if (array_key_exists($field, $snapshot) && is_array($change) && array_key_exists('new', $change)) {
                    $snapshot[$field] = $change['new'];
                }
            }
        }

        $info = $this->reconstructSource($history);
        $source = $info['source'];

        $isStock = $source && $source['type'] === 'stock'
            && $snapshot['item_id'] && $source['location_id'];

        return $snapshot + [
            'picking_date' => $history->picking_date,
            'do_number' => $history->do_number,
            'source_known' => $source !== null,
            'staging_in_id' => $isStock ? null : ($source['staging_in']['id'] ?? null),
            'staging_in_snapshot' => $isStock ? null : ($source['staging_in']['snapshot'] ?? null),
            'source_type' => $isStock ? 'stock' : 'external',
            'location_id' => $isStock ? $source['location_id'] : null,
            'lot' => $isStock ? $source['lot'] : null,
            'qty' => $info['qty'] ?? (int) $history->initial_qty,
        ];
    }

    /**
     * Hapus PERMANEN history yang dipilih (beserta detail/timeline-nya).
     * Murni hapus record: TIDAK mengembalikan stok, TIDAK menyentuh stock
     * mutations, Staging In, maupun baris Staging Out.
     *
     * Satu record bisa terdiri dari beberapa history (1 per sumber barang):
     * semuanya ikut dihapus supaya record-nya benar-benar hilang.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_out_histories,id',
        ]);

        $allIds = $this->expandIds($request->ids);

        try {
            DB::transaction(function () use ($allIds) {
                StagingOutHistoryDetail::whereIn('staging_out_history_id', $allIds)->delete();
                StagingOutHistory::whereIn('id', $allIds)->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus history: '.$e->getMessage(),
            ], 500);
        }

        $deleted = count($allIds);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => $deleted.' history berhasil dihapus.',
        ]);
    }

    /**
     * Rekonstruksi asal barang & qty akhir dari timeline sebuah history.
     *
     * - source: snapshot di event "created" ('source_type', 'location_id',
     *   'lot'), lalu diterapkan perubahan source_type / location_id / lot
     *   dari event "updated" berikutnya. null kalau history ini dibuat
     *   sebelum snapshot dicatat.
     * - qty: qty_after dari event created / updated TERAKHIR, yaitu qty
     *   yang dipotong dari stok (sama dengan qty_out di stock_mutations).
     *
     * @return array{source: ?array{type: string, location_id: ?int, lot: ?string}, qty: ?int}
     */
    private function reconstructSource(StagingOutHistory $history): array
    {
        $source = null;
        $qty = null;
        $stagingIn = null;

        $stagingInFrom = fn (array $m) => ! empty($m['staging_in_id'])
            ? ['id' => (int) $m['staging_in_id'], 'snapshot' => $m['staging_in_snapshot'] ?? null]
            : null;

        foreach ($history->details->sortBy('id') as $detail) {
            $meta = $detail->meta;
            $meta = is_string($meta) ? json_decode($meta, true) : $meta;
            $meta = $meta ?: [];

            if ($detail->event_type === 'created') {
                $qty = $detail->qty_after;

                if (isset($meta['source_type'])) {
                    $source = [
                        'type' => $meta['source_type'],
                        'location_id' => $meta['location_id'] ?? null,
                        'lot' => $meta['lot'] ?? null,
                    ];

                    $stagingIn = $stagingInFrom($meta);
                }

                continue;
            }

            // Event "delivered" (auto-archive) menyimpan kondisi TERAKHIR baris
            // sebelum diarsipkan -- ini yang paling akurat, jadi menimpa hasil
            // replay. Kalau ada beberapa (selesai -> pulih -> selesai lagi),
            // yang terakhir yang dipakai.
            if ($detail->event_type === 'delivered') {
                if (isset($meta['final_qty'])) {
                    $qty = (int) $meta['final_qty'];
                }

                if (isset($meta['final_source']['source_type'])) {
                    $final = $meta['final_source'];

                    $source = [
                        'type' => $final['source_type'],
                        'location_id' => $final['location_id'] ?? null,
                        'lot' => $final['lot'] ?? null,
                    ];

                    $stagingIn = $stagingInFrom($final);
                }

                continue;
            }

            if ($detail->event_type !== 'updated') {
                continue;
            }

            if ($detail->qty_after !== null) {
                $qty = $detail->qty_after;
            }

            if (array_key_exists('staging_in_id', $meta)) {
                $newId = $meta['staging_in_id']['new'] ?? null;

                $stagingIn = $newId
                    ? ['id' => (int) $newId, 'snapshot' => $stagingIn['snapshot'] ?? null]
                    : null;
            }

            if (! $source) {
                continue;
            }

            if (isset($meta['source_type']['new'])) {
                $source['type'] = $meta['source_type']['new'];
            }

            if (array_key_exists('location_id', $meta)) {
                $source['location_id'] = $meta['location_id']['new'] ?? null;
            }

            if (array_key_exists('lot', $meta)) {
                $source['lot'] = $meta['lot']['new'] ?? null;
            }

            if ($source['type'] !== 'stock') {
                $source['location_id'] = null;
                $source['lot'] = null;
            } else {
                // Sumber diganti ke stok: tidak lagi terhubung ke Staging In.
                $stagingIn = null;
            }
        }

        if ($source) {
            if ($source['type'] === 'stock') {
                $stagingIn = null;
            } elseif ($stagingIn) {
                $source['type'] = 'staging_in';
            }

            $source['staging_in'] = $stagingIn;
        }

        return ['source' => $source, 'qty' => $qty !== null ? (int) $qty : null];
    }

    /**
     * Tentukan asal barang untuk 1 history (dipakai panel detail):
     * ['type' => 'stock'|'external', 'location' => ?string, 'lot' => ?string],
     * atau null kalau tidak diketahui (history lama tanpa snapshot yang
     * baris aslinya sudah tidak ada).
     *
     * Baris staging_out masih ada: dibaca dari baris itu (kondisi terkini).
     * Sudah tidak ada: direkonstruksi dari timeline.
     */
    private function resolveSource(StagingOutHistory $history, ?StagingOut $staging): ?array
    {
        $service = app(StagingOutHistoryService::class);

        if ($history->is_active && $staging) {
            if ($staging->source_type !== 'stock') {
                return [
                    'type' => $staging->staging_in_id ? 'staging_in' : 'external',
                    'location' => null,
                    'lot' => null,
                ];
            }

            return [
                'type' => 'stock',
                'location' => $service->locationName($staging->location_id),
                'lot' => $staging->lot ?: null,
            ];
        }

        $source = $this->reconstructSource($history)['source'];

        if (! $source) {
            return null;
        }

        return [
            'type' => $source['type'],
            'location' => $service->locationName($source['location_id']),
            'lot' => $source['lot'],
        ];
    }

    /**
     * Teks asal barang untuk panel detail.
     */
    private function sourceLabel(?array $source): string
    {
        if (! $source) {
            return '-';
        }

        if ($source['type'] === 'staging_in') {
            return 'Staging In';
        }

        if ($source['type'] !== 'stock') {
            return 'Eksternal';
        }

        $label = 'Stok - '.($source['location'] ?: 'lokasi tidak diketahui');

        return $label.' ('.($source['lot'] ? 'Lot '.$source['lot'] : 'Tanpa Lot').')';
    }

    /**
     * Qty saat ini (dari staging_outs.qty bila masih aktif, atau dari
     * qty_after event terakhir bila record sudah dihapus) vs qty awal.
     * Tidak pakai progress bar seperti staging in karena staging out
     * biasanya tidak berkurang bertahap — hanya dikoreksi lewat event
     * 'updated'.
     */
    private function currentQtyMarkup($row): string
    {
        $group = $this->groupFor($row);

        // Record dari beberapa sumber: tampilkan total semua sumbernya.
        if ($group->count() > 1) {
            $currentQty = $group->sum(fn (StagingOutHistory $h) => $this->memberQty($h));
        } else {
            $currentQty = $row->is_active
                ? (int) $row->running_qty
                : (int) ($row->last_qty_after ?? $row->initial_qty);
        }

        return '<div class="fw-semibold small">'.number_format($currentQty, 0, ',', '.').' PCS</div>';
    }

    /**
     * Badge status pengiriman, dibaca LANGSUNG dari kolom header
     * (picking_date / do_number / delivery_date) — bukan dari detail
     * terakhir — sesuai desain migration.
     */
    private function shippingStatusMarkup($row): string
    {
        if ($row->delivery_date) {
            return '
                <span class="badge bg-success-subtle text-success">Terkirim</span>
                <div class="text-muted small mt-1">DO '.e($row->do_number ?: '-').'</div>
                <div class="text-muted" style="font-size:.72rem;">'.$row->delivery_date->format('d M Y').'</div>
            ';
        }

        if ($row->picking_date) {
            return '
                <span class="badge bg-info-subtle text-info">Siap Kirim</span>
                <div class="text-muted small mt-1">Picking '.$row->picking_date->format('d M Y').'</div>
            ';
        }

        return '<span class="badge bg-warning-subtle text-warning">Menunggu Picking</span>';
    }

    /**
     * Label + warna badge status ringkas (siklus record) untuk 1 baris
     * history. Berbeda dari shippingStatusMarkup() yang bicara soal
     * pengiriman — ini bicara soal siklus hidup recordnya sendiri.
     *
     * delivery_date dicek LEBIH DULU: begitu barang sudah dikirim, record
     * dianggap "Selesai" walau baris staging_out aslinya sudah tidak ada
     * lagi (mis. dihapus otomatis via auto-archive saat import/konfirmasi
     * kirim). "Dihapus" hanya berlaku untuk record yang hilang SEBELUM
     * sempat terkirim (dihapus manual, bulk delete, atau reset_by_import).
     */
    private function statusMeta(bool $isActive, $deliveryDate): array
    {
        if ($deliveryDate) {
            return ['label' => 'Selesai', 'color' => 'secondary'];
        }

        if (! $isActive) {
            return ['label' => 'Dihapus', 'color' => 'danger'];
        }

        return ['label' => 'Aktif', 'color' => 'success'];
    }

    /**
     * Markup kolom "Aktivitas Terakhir": badge event + ringkasan singkat +
     * waktu relatif.
     */
    private function lastActivityMarkup($row): string
    {
        $eventType = $row->last_event_type;

        if (! $eventType) {
            return '-';
        }

        $label = StagingOutHistoryDetail::EVENT_TYPES[$eventType] ?? self::EXTRA_EVENT_TYPES[$eventType] ?? $eventType;
        $color = StagingOutHistoryDetail::EVENT_COLORS[$eventType] ?? self::EXTRA_EVENT_COLORS[$eventType] ?? 'secondary';

        $subtitle = $this->eventSubtitle($eventType, $row->last_meta);

        $time = $row->last_event_at
            ? Carbon::parse($row->last_event_at)->diffForHumans()
            : '';

        return '
            <span class="badge bg-'.$color.'-subtle text-'.$color.'">'.$label.'</span>
            '.($subtitle ? '<div class="text-muted small mt-1">'.e($subtitle).'</div>' : '').'
            <div class="text-muted" style="font-size:.72rem;">'.$time.'</div>
        ';
    }

    /**
     * Ringkasan 1 baris untuk sebuah event, dibaca dari kolom meta (json).
     * Bentuk meta per event mengikuti komentar di migration
     * create_staging_out_histories_table.
     */
    private function eventSubtitle(string $eventType, $meta): ?string
    {
        $meta = is_string($meta) ? json_decode($meta, true) : $meta;
        $meta = $meta ?: [];

        if (isset($meta['sources'])) {
            return 'Sumber barang diatur';
        }

        return match ($eventType) {
            'updated' => collect($meta)->keys()
                ->map(fn ($field) => ucfirst(str_replace('_', ' ', $field)).' diubah')
                ->implode(', ') ?: null,
            'picking_confirmed' => isset($meta['picking_date']) ? 'Picking '.$meta['picking_date'] : null,
            'delivered' => isset($meta['do_number']) ? 'DO '.$meta['do_number'] : null,
            'restored' => 'Tgl resi pengiriman dikosongkan',
            default => null,
        };
    }

    /**
     * Detail satu history (header + timeline) untuk panel di sisi kanan,
     * dipanggil lewat AJAX saat tombol "mata" pada tabel diklik.
     */
    public function detail(StagingOutHistory $history)
    {
        return response()->json($this->buildDetailPayload($history));
    }

    /**
     * Export SATU siklus staging_out_history (info awal + status
     * pengiriman + activity timeline lengkap) ke Excel -- ini yang
     * dipanggil tombol "Export" di panel History Detail sebelah kanan
     * (lihat staging_out_history.blade.php: #detailExportBtn).
     *
     * Datanya diambil dari buildDetailPayload() yang SAMA dengan yang
     * dipakai panel detail, jadi isi Excel selalu persis sama dengan yang
     * tampil di layar (status, asal barang, label event, dsb).
     */
    public function exportDetail(StagingOutHistory $history)
    {
        $payload = $this->buildDetailPayload($history);

        $filename = 'staging_out_history_detail_'
            .Str::slug($payload['item_code'] ?: 'item').'_'
            .now()->format('Y-m-d').'.xlsx';

        return Excel::download(new StagingOutHistoryDetailExport($payload), $filename);
    }

    /**
     * Ubah meta event "updated" (field => [old, new]) jadi daftar yang mudah
     * dibaca: label Indonesia + nilai lama/baru. Lokasi sudah berupa nama.
     *
     * @return array<int, array{label: string, old: ?string, new: ?string}>
     */
    private function describeChanges($meta): array
    {
        if (! is_array($meta)) {
            return [];
        }

        $labels = [
            'qty' => 'Qty',
            'source_type' => 'Sumber',
            'location_id' => 'Lokasi stok',
            'lot' => 'Lot',
            'item_id' => 'Barang',
            'so_number' => 'No. SO',
            'customer' => 'Customer',
            'line_item' => 'Line Item',
            'delivery_instruction_date' => 'Tgl Instruksi Kirim',
            'picking_date' => 'Tgl Picking',
            'do_number' => 'No. DO',
            'delivery_receipt_date' => 'Tgl Resi Pengiriman',
            'staging_location' => 'Posisi',
            'note' => 'Catatan',
        ];

        $value = function (string $field, $v): ?string {
            if ($v === null || $v === '') {
                return null;
            }

            if ($field === 'source_type') {
                return $v === 'stock' ? 'Stok' : 'Bukan dari stok';
            }

            if ($field === 'staging_location') {
                return ucfirst((string) $v);
            }

            return (string) $v;
        };

        $out = [];

        foreach ($meta as $field => $change) {
            if (! is_array($change) || ! (array_key_exists('old', $change) || array_key_exists('new', $change))) {
                continue;
            }

            $out[] = [
                'label' => $labels[$field] ?? ucfirst(str_replace('_', ' ', (string) $field)),
                'old' => $value($field, $change['old'] ?? null),
                'new' => $value($field, $change['new'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Susun data detail satu history (dipakai detail() dan exportDetail()).
     */
    private function buildDetailPayload(StagingOutHistory $history): array
    {
        // Satu record bisa terdiri dari beberapa history (1 per sumber).
        $members = $this->groupOf($history);
        $isGroup = $members->count() > 1;
        $rep = $members->first();

        // Relasi details() di model sudah pakai ->latest() (descending).
        // reorder() dulu supaya timeline tampil dari yang paling lama
        // ke terbaru.
        $members->load(['details' => function ($query) {
            $query->reorder()->orderBy('created_at')->orderBy('id');
        }]);
        $rep->load(['item', 'creator']);

        $status = $this->statusMeta($rep->is_active, $rep->delivery_date);

        // Tiap sumber: jenis (Stok / Staging In / Eksternal), rincian lokasi,
        // dan qty-nya -- dipakai panel detail & export.
        $sources = $members->values()->map(function (StagingOutHistory $m, int $i) {
            $staging = $m->is_active ? StagingOut::find($m->staging_out_id) : null;
            $src = $this->resolveSource($m, $staging);
            $type = $src['type'] ?? 'unknown';
            $detail = '';

            if ($type === 'stock') {
                $detail = ($src['location'] ?: 'Lokasi tidak diketahui')
                    .' · '.($src['lot'] ? 'Lot '.$src['lot'] : 'Tanpa Lot');
            } elseif ($type === 'staging_in' && $staging && $staging->staging_in_id) {
                $in = StagingIn::find($staging->staging_in_id);

                $detail = implode(' · ', array_filter([
                    $in ? $in->location : null,
                    $in && $in->po_number ? 'PO '.$in->po_number : null,
                ]));
            }

            return [
                'no' => $i + 1,
                'type' => $type,
                'type_label' => match ($type) {
                    'stock' => 'Stok',
                    'staging_in' => 'Staging In',
                    // Sumber Staging In yang barisnya sudah selesai tidak tercatat
                    // di timeline, jadi hanya bisa dibaca sebagai "bukan stok".
                    'external' => $m->is_active ? 'Eksternal / belum dipilih' : 'Eksternal / Staging In',
                    default => 'Tidak diketahui',
                },
                'detail' => $detail,
                'label' => $this->sourceLabel($src),
                'qty' => $this->memberQty($m, $staging),
            ];
        })->values();

        $currentQty = (int) $sources->sum('qty');

        // "10 PCS dari Stok (Gudang A · Tanpa Lot) + 2 PCS dari Staging In"
        $sumberRingkas = $sources->map(function ($src) {
            return $src['qty'].' PCS dari '.$src['type_label']
                .($src['type'] === 'stock' && $src['detail'] ? ' ('.$src['detail'].')' : '');
        })->implode(' + ');

        $sumberBarang = $isGroup ? $sumberRingkas : $sources->first()['label'];

        // Timeline gabungan semua sumber, urut waktu. Event "Data Dibuat"
        // milik sumber ke-2 dst tidak ditampilkan (itu hanya hasil
        // pemecahan sumber, bukan data baru).
        // Waktu event "created" milik sumber ke-2 dst: penanda barang dipecah.
        $splitTimes = $members->filter(fn ($m) => $m->id !== $rep->id)
            ->flatMap(fn ($m) => $m->details->where('event_type', 'created')
                ->map(fn ($d) => $d->created_at->getTimestamp()))
            ->values();

        $events = $members->flatMap(function (StagingOutHistory $m, $idx) use ($rep) {
            return $m->details
                ->reject(fn ($d) => $m->id !== $rep->id && $d->event_type === 'created')
                ->map(fn ($d) => ['detail' => $d, 'source_no' => $idx + 1]);
        })->sort(function ($a, $b) {
            return [$a['detail']->created_at->getTimestamp(), $a['detail']->id]
                <=> [$b['detail']->created_at->getTimestamp(), $b['detail']->id];
        })->values();

        $events = $events->reject(fn ($e) => $e['detail']->notes === '__hidden__')->values();
        $sourceCount = 0;
        $sourceEvents = [];

        if ($isGroup) {
            $bookkeeping = ['qty', 'source_type', 'location_id', 'lot'];
            $ts = fn ($e) => $e['detail']->created_at->getTimestamp();
            $metaKeys = fn ($e) => is_array($e['detail']->meta) ? array_keys($e['detail']->meta) : [];

            $assignTimes = $events
                ->filter(fn ($e) => $e['detail']->event_type === 'updated'
                    && array_intersect(['source_type', 'location_id', 'lot'], $metaKeys($e)))
                ->map(fn ($e) => $ts($e))
                ->merge($splitTimes)
                ->values();

            $isBookkeeping = fn ($e) => $e['detail']->event_type === 'updated'
                && $metaKeys($e)
                && ! array_diff($metaKeys($e), $bookkeeping)
                && $assignTimes->contains(fn ($t) => abs($t - $ts($e)) <= 15);

            $noisy = $events->filter($isBookkeeping)->values();
            $events = $events->reject($isBookkeeping)->values();

            // Tiap sumber punya history sendiri, jadi event yang berlaku untuk
            // seluruh barang (picking, kirim, dipulihkan, dihapus, ubah tgl resi,
            // dsb) tercatat dobel -- satu per sumber. Event kembar cukup tampil
            // sekali: jenis sama + isi yang DITAMPILKAN sama + waktu berdekatan
            // (<= 30 detik) dari sumber yang berbeda. Data meta lain yang khusus
            // per sumber (id baris, qty sumber, dll) tidak ikut dibandingkan.
            //
            // Khusus event "updated" seluruh isi perubahannya dibandingkan, supaya
            // perubahan qty yang memang berbeda antar sumber tidak ikut hilang.
            $shownMeta = ['picking_date', 'do_number', 'delivery_date', 'cleared_delivery_receipt_date'];
            $seen = [];

            $events = $events->filter(function ($e) use (&$seen, $ts, $shownMeta) {
                $d = $e['detail'];
                $meta = is_array($d->meta) ? $d->meta : [];
                $compare = $d->event_type === 'updated' ? $meta : array_intersect_key($meta, array_flip($shownMeta));

                ksort($compare);

                $sig = $d->event_type.'|'.json_encode($compare);
                $time = $ts($e);

                foreach ($seen[$sig] ?? [] as [$t, $no, $kept]) {
                    if ($no !== $e['source_no'] && abs($t - $time) <= 30) {
                        // Catatan yang beda ikut digabung supaya tidak hilang.
                        if ($d->notes && ! str_contains((string) $kept->notes, (string) $d->notes)) {
                            $kept->notes = trim((string) $kept->notes.' '.$d->notes);
                        }

                        return false;
                    }
                }

                $seen[$sig][] = [$time, $e['source_no'], $d];

                return true;
            })->values();

            // Catatan teknis yang berdekatan (<= 15 detik) = satu kali penetapan sumber.
            $bursts = [];

            foreach ($noisy as $e) {
                $last = count($bursts) - 1;

                if ($last >= 0 && $ts($e) - $bursts[$last]['end'] <= 15) {
                    $bursts[$last]['end'] = $ts($e);
                } else {
                    $bursts[] = [
                        'start' => $ts($e),
                        'end' => $ts($e),
                        'by' => $e['detail']->performed_by,
                        'at' => $e['detail']->created_at,
                    ];
                }
            }

            foreach ($bursts as $b => $burst) {
                $isLast = $b === count($bursts) - 1;

                $sourceEvents[] = [
                    'id' => null,
                    'event_type' => 'sources_set',
                    'label' => $isLast ? 'Sumber Barang Dipilih' : 'Sumber Barang Diubah',
                    'qty_before' => null,
                    'qty_change' => null,
                    'qty_after' => $isLast ? $currentQty : null,
                    'meta' => null,
                    'notes' => null,
                    'performed_by' => $burst['by'] ?: 'System',
                    'created_at' => $burst['at']->format('d M Y, H:i'),
                    'ts' => $burst['start'],
                    'source_no' => null,
                    'source' => null,
                    'changes' => [],
                    'split_note' => null,
                    // Hanya penetapan terakhir yang memuat daftar sumber (kondisi sekarang).
                    'source_summary' => $isLast ? [
                        'total' => $currentQty,
                        'count' => $sources->count(),
                        'items' => $sources->map(fn ($src) => [
                            'qty' => $src['qty'],
                            'text' => $src['type_label'].($src['detail'] ? ' · '.$src['detail'] : ''),
                        ])->all(),
                    ] : null,
                ];
            }
        }

        $payload = [
            'id' => $rep->id,
            'item_code' => optional($rep->item)->item_code_internal,
            'item_name' => optional($rep->item)->name,
            'so_number' => $rep->so_number,
            'status' => $status,
            'informasi_awal' => [
                'customer' => $rep->customer ?: '-',
                'line_item' => $rep->line_item ?: '-',
                'delivery_instruction_date' => optional($rep->delivery_instruction_date)->format('d M Y') ?: '-',
                'sumber_barang' => $sumberBarang,
                'sumber_ringkas' => $isGroup ? $sumberRingkas : null,
                'sumber_list' => $isGroup ? $sources->all() : [],
                'initial_qty' => number_format($rep->initial_qty, 0, ',', '.').' PCS',
                'current_qty' => number_format($currentQty, 0, ',', '.').' PCS',
                'created_by' => optional($rep->creator)->name ?: '-',
                'created_at' => $rep->created_at->format('d M Y, H:i'),
            ],
            'status_pengiriman' => [
                'picking_date' => optional($rep->picking_date)->format('d M Y') ?: '-',
                'do_number' => $rep->do_number ?: '-',
                'delivery_date' => optional($rep->delivery_date)->format('d M Y') ?: '-',
            ],
            'timeline' => $events->map(function ($event) use ($isGroup, $sources, $splitTimes, &$sourceCount) {
                $detail = $event['detail'];
                $meta = $detail->meta;

                if (is_array($meta) && isset($meta['sources'])) {
                    return [
                        'id' => $detail->id,
                        'ts' => $detail->created_at->getTimestamp(),
                        'event_type' => 'sources_set',
                        'label' => $sourceCount++ ? 'Sumber Barang Diubah' : 'Sumber Barang Dipilih',
                        'notes' => null,
                        'performed_by' => $detail->performed_by ?: 'System',
                        'created_at' => $detail->created_at->format('d M Y, H:i'),
                        'source_summary' => [
                            'total' => array_sum(array_column($meta['sources'], 'qty')),
                            'count' => count($meta['sources']),
                            'items' => $meta['sources'],
                        ],
                    ];
                }

                // Perubahan lokasi stok pada event "updated" disimpan sebagai
                // id; tampilkan sebagai nama lokasi supaya terbaca.
                if ($detail->event_type === 'updated' && is_array($meta) && isset($meta['location_id'])) {
                    $service = app(StagingOutHistoryService::class);

                    $meta['location_id'] = [
                        'old' => $service->locationName($meta['location_id']['old'] ?? null),
                        'new' => $service->locationName($meta['location_id']['new'] ?? null),
                    ];
                }

                return [
                    'id' => $detail->id,
                    'ts' => $detail->created_at->getTimestamp(),
                    'event_type' => $detail->event_type,
                    'label' => StagingOutHistoryDetail::EVENT_TYPES[$detail->event_type] ?? self::EXTRA_EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
                    'qty_before' => $detail->qty_before,
                    'qty_change' => $detail->qty_change,
                    'qty_after' => $detail->qty_after,
                    'meta' => $meta,
                    'notes' => $detail->notes,
                    'performed_by' => $detail->performed_by ?: 'System',
                    'created_at' => $detail->created_at->format('d M Y, H:i'),
                    // Penanda sumber ke berapa (hanya kalau sumbernya lebih dari satu).
                    'source_no' => $isGroup ? $event['source_no'] : null,
                    'source' => $isGroup ? ($sources[$event['source_no'] - 1] ?? null) : null,
                    // Perubahan dalam bahasa yang mudah dibaca (label + nilai lama/baru).
                    'changes' => $detail->event_type === 'updated' ? $this->describeChanges($meta) : [],
                    'split_note' => $isGroup
                        && $detail->event_type === 'updated'
                        && is_array($meta) && isset($meta['qty'])
                        && $splitTimes->contains(fn ($t) => abs($t - $detail->created_at->getTimestamp()) <= 15)
                            ? 'Barang dibagi ke beberapa sumber, jadi qty sumber ini disesuaikan.'
                            : null,
                ];
            })->all(),
        ];

        if ($sourceEvents) {
            $payload['timeline'] = collect($payload['timeline'])
                ->concat($sourceEvents)
                ->sort(fn ($a, $b) => [$a['ts'], $a['id'] ?? PHP_INT_MAX] <=> [$b['ts'], $b['id'] ?? PHP_INT_MAX])
                ->values()
                ->all();
        }

        return $payload;
    }
}
