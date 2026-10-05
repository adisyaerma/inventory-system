<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StagingOutHistoryDetail;
use App\Services\StagingOutHistoryService;
use App\Services\StagingOutStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request)
    {
        $totalHistory = StagingOutHistory::count();

        $createdToday = StagingOutHistory::whereDate('created_at', now()->toDateString())->count();

        $activeCount = StagingOutHistory::where('is_active', true)
            ->whereNull('delivery_date')
            ->count();

        // Selesai = delivery_date sudah terisi, TIDAK peduli is_active —
        // termasuk yang baris aslinya sudah dihapus otomatis (auto-archive).
        $deliveredCount = StagingOutHistory::whereNotNull('delivery_date')->count();

        // Dihapus = hilang SEBELUM sempat terkirim (bukan auto-archive
        // karena delivery_date sudah terisi).
        $removedCount = StagingOutHistory::where('is_active', false)
            ->whereNull('delivery_date')
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
                'ld.qty_after as last_qty_after',
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
        $query = $this->baseQuery()->orderByDesc('staging_out_histories.id');

        // Filter dipusatkan di StagingOutHistoryExport supaya tabel & export
        // selalu identik.
        StagingOutHistoryExport::applyFilters($query, $request);

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                // Semua baris bisa dicentang. data-active=1 menandai history
                // yang barisnya masih ada di Staging Out (peringatan tambahan
                // di konfirmasi Hapus).
                //
                // data-cancel-stock=1 menandai history yang barangnya diambil
                // dari stok DAN transaksinya akan dibatalkan kalau dihapus:
                //  - Aktif  : baris Staging Out masih ada & sumbernya stok.
                //  - Selesai: baris sudah tidak ada, asal barang dibaca dari
                //             snapshot di event "created".
                // "Dihapus" tidak ikut (stoknya sudah dikembalikan dulu).
                if ($row->is_active) {
                    $cancelsStock = $row->running_source_type === 'stock';
                } else {
                    $createdMeta = is_string($row->created_meta)
                        ? json_decode($row->created_meta, true)
                        : $row->created_meta;

                    $cancelsStock = (bool) $row->delivery_date
                        && ($createdMeta['source_type'] ?? null) === 'stock';
                }

                // data-restorable=1 menandai history berstatus "Selesai"
                // (sudah terkirim & barisnya sudah tidak ada di Staging Out):
                // hanya ini yang ikut dihitung/dikirim tombol Pulihkan.
                $restorable = ! $row->is_active && (bool) $row->delivery_date;

                return '<input type="checkbox" class="form-check-input historyCheckbox"
                    data-active="'.($row->is_active ? '1' : '0').'"
                    data-cancel-stock="'.($cancelsStock ? '1' : '0').'"
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

        DB::transaction(function () use ($request, &$restored, &$errors) {

            $histories = StagingOutHistory::whereIn('id', $request->ids)
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

                        $previousReceiptDate = $history->delivery_date instanceof \DateTimeInterface
                            ? $history->delivery_date->format('Y-m-d')
                            : $history->delivery_date;

                        $history->staging_out_id = $newStaging->id;
                        $history->is_active = true;
                        $history->delivery_date = null;
                        $history->save();

                        StagingOutHistoryDetail::create([
                            'staging_out_history_id' => $history->id,
                            'event_type' => 'restored',
                            'qty_before' => 0,
                            'qty_change' => $snapshot['qty'],
                            'qty_after' => $snapshot['qty'],
                            'meta' => [
                                'restored_staging_out_id' => $newStaging->id,
                                'cleared_delivery_receipt_date' => $previousReceiptDate,
                            ],
                            'notes' => 'Dipulihkan ke Staging Out (baris baru #'.$newStaging->id.'), tgl resi pengiriman dikosongkan.',
                            'performed_by' => auth()->id(),
                        ]);
                    });

                    $restored++;

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
            'source_type' => $isStock ? 'stock' : 'external',
            'location_id' => $isStock ? $source['location_id'] : null,
            'lot' => $isStock ? $source['lot'] : null,
            'qty' => $info['qty'] ?? (int) $history->initial_qty,
        ];
    }

    /**
     * Hapus PERMANEN sejumlah history sekaligus (beserta seluruh detail/
     * timeline-nya). Semua status boleh dihapus.
     *
     * Kalau barangnya diambil dari STOK, transaksinya dibatalkan: qty
     * dikembalikan ke location_stocks dan mutasi keluar di stock_mutations
     * dihapus. Berlaku untuk:
     *  - Aktif   : baris Staging Out masih ada -> dibatalkan lewat baris itu,
     *              dan baris Staging Out-nya ikut dihapus (kalau dibiarkan,
     *              ia terlihat mengambil stok padahal stoknya sudah kembali).
     *  - Selesai : baris Staging Out sudah tidak ada (terkirim / auto-archive,
     *              mis. salah input) -> lokasi, lot, dan qty akhir
     *              direkonstruksi dari timeline history, lalu stok
     *              dikembalikan.
     * Yang TIDAK dibatalkan:
     *  - Dihapus : stoknya sudah dikembalikan saat baris itu dihapus dari
     *              halaman Staging Out.
     *  - Eksternal : tidak pernah menyentuh stok.
     *
     * History lama (dibuat sebelum asal barang dicatat) yang berstatus
     * Selesai tidak punya data lokasi, jadi stoknya tidak bisa dikembalikan
     * otomatis. History-nya tetap dihapus dan hal ini dilaporkan di
     * 'errors' supaya bisa dikoreksi manual.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_out_histories,id',
        ]);

        $deleted = 0;
        $cancelled = 0;
        $warnings = [];

        try {
            DB::transaction(function () use ($request, &$deleted, &$cancelled, &$warnings) {

                $histories = StagingOutHistory::with('details')
                    ->whereIn('id', $request->ids)
                    ->lockForUpdate()
                    ->get();

                $stock = app(StagingOutStockService::class);
                $stagingIdsToDelete = [];

                foreach ($histories as $history) {

                    // 1) AKTIF: baris Staging Out masih ada. Baris dicari
                    //    HANYA kalau history masih aktif, supaya ID yang
                    //    kebetulan dipakai ulang baris lain tidak ikut kena.
                    if ($history->is_active && $history->staging_out_id) {
                        $staging = StagingOut::lockForUpdate()->find($history->staging_out_id);

                        if ($staging && $stock->isStockSourced($staging)) {
                            $stock->reverse($staging);
                            $stagingIdsToDelete[] = $staging->id;
                            $cancelled++;
                        }

                        continue;
                    }

                    // 2) SELESAI: baris Staging Out sudah tidak ada, jadi
                    //    direkonstruksi dari timeline history.
                    if ($history->delivery_date) {
                        $info = $this->reconstructSource($history);
                        $label = 'SO '.($history->so_number ?: '-').' (history #'.$history->id.')';

                        if (! $info['source']) {
                            $warnings[] = $label.': asal barang tidak tercatat di history lama, jadi stok tidak bisa dikembalikan otomatis. Cek manual kalau barangnya dari stok.';

                            continue;
                        }

                        if ($info['source']['type'] !== 'stock') {
                            continue;
                        }

                        if (! $history->item_id || ! $info['source']['location_id']) {
                            $warnings[] = $label.': data lokasi stok tidak lengkap, jadi stok tidak bisa dikembalikan otomatis.';

                            continue;
                        }

                        $transient = new StagingOut;
                        $transient->forceFill([
                            'id' => $history->staging_out_id,
                            'source_type' => 'stock',
                            'item_id' => $history->item_id,
                            'location_id' => $info['source']['location_id'],
                            'lot' => $info['source']['lot'],
                            'qty' => $info['qty'] ?? $history->initial_qty,
                            'so_number' => $history->so_number,
                            'customer' => $history->customer,
                        ]);

                        $stock->reverse($transient);
                        $cancelled++;
                    }

                    // 3) DIHAPUS (belum terkirim): stok sudah kembali, tidak
                    //    ada yang perlu dibatalkan.
                }

                $ids = $histories->pluck('id');

                StagingOutHistoryDetail::whereIn('staging_out_history_id', $ids)->delete();

                $deleted = StagingOutHistory::whereIn('id', $ids)->delete();

                // Dihapus SETELAH history-nya hilang, supaya tidak
                // bentrok dengan relasi staging_out_id di header history.
                if (! empty($stagingIdsToDelete)) {
                    StagingOut::whereIn('id', $stagingIdsToDelete)->delete();
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        $message = $deleted.' history berhasil dihapus.';

        if ($cancelled > 0) {
            $message .= ' '.$cancelled.' transaksi stok dibatalkan (qty dikembalikan ke stok dan mutasi keluar dihapus).';
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'cancelled' => $cancelled,
            'errors' => $warnings,
            'message' => $message,
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
                }

                continue;
            }

            if ($detail->event_type !== 'updated') {
                continue;
            }

            if ($detail->qty_after !== null) {
                $qty = $detail->qty_after;
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
            }
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
                return ['type' => 'external', 'location' => null, 'lot' => null];
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
        $currentQty = $row->is_active
            ? (int) $row->running_qty
            : (int) ($row->last_qty_after ?? $row->initial_qty);

        $initialQty = (int) $row->initial_qty;

        $html = '<div class="fw-semibold small">'.number_format($currentQty, 0, ',', '.').' PCS</div>';

        if ($currentQty !== $initialQty) {
            $html .= '<div class="text-muted" style="font-size:.72rem;">dari '.number_format($initialQty, 0, ',', '.').' PCS awal</div>';
        }

        return $html;
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
        $history->load(['item', 'creator', 'details' => function ($query) {
            // Relasi details() di model sudah pakai ->latest() (descending).
            // reorder() dulu supaya timeline tampil dari yang paling lama
            // ke terbaru.
            $query->reorder()->orderBy('created_at')->orderBy('id');
        }, 'details.performedBy']);

        $lastDetail = $history->details->last();

        $status = $this->statusMeta($history->is_active, $history->delivery_date);

        $staging = $history->is_active ? StagingOut::find($history->staging_out_id) : null;

        $currentQty = $history->is_active
            ? (int) optional($staging)->qty
            : (int) ($lastDetail->qty_after ?? $history->initial_qty);

        $source = $this->resolveSource($history, $staging);

        return response()->json([
            'id' => $history->id,
            'item_code' => optional($history->item)->item_code_internal,
            'item_name' => optional($history->item)->name,
            'so_number' => $history->so_number,
            'status' => $status,
            'informasi_awal' => [
                'customer' => $history->customer ?: '-',
                'line_item' => $history->line_item ?: '-',
                'delivery_instruction_date' => optional($history->delivery_instruction_date)->format('d M Y') ?: '-',
                'sumber_barang' => $this->sourceLabel($source),
                'initial_qty' => number_format($history->initial_qty, 0, ',', '.').' PCS',
                'current_qty' => number_format($currentQty, 0, ',', '.').' PCS',
                'created_by' => optional($history->creator)->name ?: '-',
                'created_at' => $history->created_at->format('d M Y, H:i'),
            ],
            'status_pengiriman' => [
                'picking_date' => optional($history->picking_date)->format('d M Y') ?: '-',
                'do_number' => $history->do_number ?: '-',
                'delivery_date' => optional($history->delivery_date)->format('d M Y') ?: '-',
            ],
            'timeline' => $history->details->map(function ($detail) {
                $meta = $detail->meta;

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
                    'event_type' => $detail->event_type,
                    'label' => StagingOutHistoryDetail::EVENT_TYPES[$detail->event_type] ?? self::EXTRA_EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
                    'qty_before' => $detail->qty_before,
                    'qty_change' => $detail->qty_change,
                    'qty_after' => $detail->qty_after,
                    'meta' => $meta,
                    'notes' => $detail->notes,
                    'performed_by' => optional($detail->performedBy)->name ?: 'System',
                    'created_at' => $detail->created_at->format('d M Y, H:i'),
                ];
            }),
        ]);
    }
}