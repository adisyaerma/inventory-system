<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingInHistoryDetailExport;
use App\Exports\StagingInHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\StagingIn;
use App\Models\StagingInHistory;
use App\Models\StagingInHistoryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * Menampilkan riwayat (history) staging in: 1 baris per "siklus hidup"
 * staging_in (staging_in_histories) beserta detail kejadian di dalamnya
 * (staging_in_history_details).
 *
 * Relasi yang dipakai (sesuai model App\Models\StagingInHistory &
 * App\Models\StagingInHistoryDetail):
 * - StagingInHistory::item()
 * - StagingInHistory::creator()   -> FK created_by
 * - StagingInHistory::details()   -> hasMany, sudah ->latest() di model
 * - StagingInHistoryDetail::performedBy() -> FK performed_by
 * - Label & warna event pakai StagingInHistoryDetail::EVENT_TYPES /
 *   ::EVENT_COLORS, bukan didefinisikan ulang di sini.
 *
 * Halaman ini pada dasarnya read-only (audit log), dengan DUA pengecualian:
 * bulkDestroy() (hapus permanen history yang dipilih) dan
 * bulkRestore() di bawah, yang memulihkan siklus
 * staging_in yang sudah "Dihapus" (deleted/bulk_deleted/reset_by_import)
 * dengan cara membuat BARIS BARU di staging_ins (data staging_ins yang
 * sudah ada sama sekali tidak disentuh/dihapus).
 */
class StagingInHistoryController extends Controller
{
    /**
     * Event yang dianggap "menyelesaikan" siklus staging in (barang benar-benar
     * pindah, bukan hilang/terhapus).
     */
    private const COMPLETED_EVENTS = ['moved_to_stock', 'moved_to_staging_out'];

    /**
     * Event yang dianggap "menghapus" siklus staging in tanpa ada tujuan
     * perpindahan yang jelas.
     */
    private const REMOVED_EVENTS = ['deleted', 'bulk_deleted', 'reset_by_import'];

    public function index(Request $request)
    {
        $totalHistory = StagingInHistory::count();

        $createdToday = StagingInHistory::whereDate('created_at', now()->toDateString())->count();

        $activeCount = StagingInHistory::where('is_active', true)->count();

        $movedCount = $this->baseQuery()
            ->where('staging_in_histories.is_active', false)
            ->whereIn('ld.event_type', self::COMPLETED_EVENTS)
            ->count();

        $removedCount = $this->baseQuery()
            ->where('staging_in_histories.is_active', false)
            ->whereIn('ld.event_type', self::REMOVED_EVENTS)
            ->count();

        $statusOptions = [
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dihapus' => 'Dihapus',
        ];

        $eventOptions = StagingInHistoryDetail::EVENT_TYPES;

        return view('staging_in_history', compact(
            'totalHistory',
            'createdToday',
            'activeCount',
            'movedCount',
            'removedCount',
            'statusOptions',
            'eventOptions'
        ));
    }

    /**
     * Export tabel history ke Excel, menghormati filter yang sama dengan
     * yang dipakai di tabel (status, event, rentang tanggal, dan kata
     * kunci pencarian) — lihat App\Exports\StagingInHistoryExport.
     */
    public function export(Request $request)
    {
        $filename = 'staging_in_history_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new StagingInHistoryExport($request), $filename);
    }

    /**
     * Export SATU siklus staging_in_history (info awal + activity
     * timeline lengkap) ke Excel -- ini yang dipanggil tombol "Export"
     * di panel History Detail sebelah kanan (lihat
     * staging_in_history.blade.php: #detailExportBtn).
     */
    public function exportDetail(StagingInHistory $history)
    {
        $history->load('item');

        $itemCode = optional($history->item)->item_code_internal ?: 'item';

        $filename = 'staging_in_history_detail_'
            .\Illuminate\Support\Str::slug($itemCode).'_'
            .now()->format('Y-m-d').'.xlsx';

        return Excel::download(new StagingInHistoryDetailExport($history), $filename);
    }

    /**
     * Query dasar: staging_in_histories digabung dengan item, staging_in
     * (untuk qty berjalan bila masih aktif), dan detail TERAKHIR dari
     * masing-masing history (untuk status, qty terkini & aktivitas terakhir).
     */
    private function baseQuery()
    {
        $latestDetail = DB::table('staging_in_history_details as d')
            ->select(
                'd.staging_in_history_id',
                'd.event_type',
                'd.qty_before',
                'd.qty_change',
                'd.qty_after',
                'd.meta',
                'd.created_at as event_at'
            )
            ->whereRaw('d.id = (
                select max(d2.id) from staging_in_history_details d2
                where d2.staging_in_history_id = d.staging_in_history_id
            )');

        return StagingInHistory::query()
            ->select('staging_in_histories.*')
            ->leftJoin('items', 'items.id', '=', 'staging_in_histories.item_id')
            ->leftJoin('staging_ins', 'staging_ins.id', '=', 'staging_in_histories.staging_in_id')
            ->leftJoinSub($latestDetail, 'ld', function ($join) {
                $join->on('ld.staging_in_history_id', '=', 'staging_in_histories.id');
            })
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
                'staging_ins.qty as running_qty',
                'ld.event_type as last_event_type',
                'ld.qty_after as last_qty_after',
                'ld.meta as last_meta',
                'ld.event_at as last_event_at',
            ]);
    }

    /**
     * Server-side DataTables source untuk tabel history.
     */
    public function data(Request $request)
    {
        $query = $this->baseQuery()->orderByDesc('staging_in_histories.id');

        // Filter dipusatkan di StagingInHistoryExport supaya tabel & export
        // selalu identik.
        StagingInHistoryExport::applyFilters($query, $request);

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                $status = $this->statusMeta($row->is_active, $row->last_event_type);

                // Semua baris bisa dicentang (untuk Hapus). data-removed=1
                // menandai baris berstatus "Dihapus" -- hanya ini yang ikut
                // dihitung/dikirim oleh tombol Pulihkan. data-active=1
                // menandai history yang barisnya masih ada di Staging In,
                // dipakai untuk peringatan tambahan di konfirmasi Hapus.
                return '<input type="checkbox" class="form-check-input historyCheckbox"
                    data-removed="'.($status['label'] === 'Dihapus' ? '1' : '0').'"
                    data-active="'.($row->is_active ? '1' : '0').'"
                    value="'.$row->id.'">';
            })

            ->addColumn('item_po', function ($row) {
                return '
                    <small class="fw-bold">'.e($row->item_code).'</small>
                    <div class="text-muted">'.e($row->item_name).'</div>
                    <div class="text-primary small">'.e($row->po_number ?: '-').'</div>
                ';
            })

            ->orderColumn('item_po', 'items.name $1')

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
                    StagingInHistoryExport::applySearch($query, $keyword);

                    return;
                }

                // Pencarian bawaan (StagingInHistoryExport::applySearch) tetap
                // dipakai apa adanya, lalu DITAMBAH (OR) pencarian ke kode
                // barang & nama barang dari tabel items yang sudah di-join.
                // LOWER() + LIKE supaya tidak peka huruf besar/kecil di DB
                // apa pun (LIKE di PostgreSQL peka huruf besar/kecil).
                $like = '%'.addcslashes(mb_strtolower($keyword), '\\%_').'%';

                $query->where(function ($search) use ($keyword, $like) {
                    $search->where(function ($existing) use ($keyword) {
                        StagingInHistoryExport::applySearch($existing, $keyword);
                    })
                        ->orWhereRaw('LOWER(items.item_code_internal) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(items.name) LIKE ?', [$like]);
                });
            }, false)

            ->editColumn('arrival_date', function ($row) {
                return $row->arrival_date ? $row->arrival_date->format('d M Y') : '-';
            })

            ->editColumn('initial_qty', function ($row) {
                return number_format($row->initial_qty, 0, ',', '.').' PCS';
            })

            ->addColumn('current_qty', function ($row) {
                return $this->currentQtyBadge($row);
            })

            ->addColumn('row_status', function ($row) {
                $status = $this->statusMeta($row->is_active, $row->last_event_type);

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
                'item_po',
                'current_qty',
                'row_status',
                'last_activity',
                'action',
            ])

            ->make(true);
    }

    /**
     * Progress bar + label qty terkini vs qty awal.
     */
    private function currentQtyBadge($row): string
    {
        $currentQty = $row->is_active
            ? (int) $row->running_qty
            : (int) ($row->last_qty_after ?? 0);

        $initialQty = (int) $row->initial_qty;

        $percent = $initialQty > 0 ? min(100, round(($currentQty / $initialQty) * 100)) : 0;

        return '
            <div class="fw-semibold small">'.number_format($currentQty, 0, ',', '.').' PCS</div>
            <div class="progress mt-1" style="height:6px;width:90px;">
                <div class="progress-bar bg-primary" style="width:'.$percent.'%"></div>
            </div>
            <div class="text-muted" style="font-size:.72rem;">'.$percent.'%</div>
        ';
    }

    /**
     * Label + warna badge status ringkas untuk 1 baris history.
     */
    private function statusMeta(bool $isActive, ?string $lastEventType): array
    {
        if ($isActive) {
            return ['label' => 'Aktif', 'color' => 'success'];
        }

        if (in_array($lastEventType, self::COMPLETED_EVENTS, true)) {
            return ['label' => 'Selesai', 'color' => 'secondary'];
        }

        return ['label' => 'Dihapus', 'color' => 'danger'];
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

        $label = StagingInHistoryDetail::EVENT_TYPES[$eventType] ?? $eventType;
        $color = StagingInHistoryDetail::EVENT_COLORS[$eventType] ?? 'secondary';

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
     */
    private function eventSubtitle(string $eventType, $meta): ?string
    {
        $meta = is_string($meta) ? json_decode($meta, true) : $meta;
        $meta = $meta ?: [];

        return match ($eventType) {
            'updated' => collect($meta)->keys()
                ->map(fn ($field) => ucfirst(str_replace('_', ' ', $field)).' diubah')
                ->implode(', ') ?: null,
            'moved_to_stock' => isset($meta['location']) ? 'Ke lokasi '.$meta['location'] : null,
            'moved_to_staging_out' => isset($meta['customer']) ? 'Ke '.$meta['customer'] : null,
            default => null,
        };
    }

    /**
     * Detail satu history (header + timeline) untuk panel di sisi kanan,
     * dipanggil lewat AJAX saat tombol "mata" pada tabel diklik.
     */
    public function detail(StagingInHistory $history)
    {
        $history->load(['item', 'creator', 'details' => function ($query) {
            // Relasi details() di model sudah pakai ->latest() (descending).
            // reorder() dulu supaya tidak numpuk beberapa ORDER BY dan
            // timeline benar-benar tampil dari yang paling lama ke terbaru.
            $query->reorder()->orderBy('created_at')->orderBy('id');
        }, 'details.performedBy']);

        $lastDetail = $history->details->last();

        $status = $this->statusMeta($history->is_active, $lastDetail?->event_type);

        $currentQty = $history->is_active
            ? (int) optional(StagingIn::find($history->staging_in_id))->qty
            : (int) ($lastDetail->qty_after ?? 0);

        return response()->json([
            'id' => $history->id,
            'item_code' => optional($history->item)->item_code_internal,
            'item_name' => optional($history->item)->name,
            'po_number' => $history->po_number,
            'status' => $status,
            'informasi_awal' => [
                'supplier' => $history->supplier_origin ?: '-',
                'location' => $history->location ?: '-',
                'arrival_date' => optional($history->arrival_date)->format('d M Y') ?: '-',
                'incoterms' => $history->incoterms ?: '-',
                'initial_qty' => number_format($history->initial_qty, 0, ',', '.').' PCS',
                'current_qty' => number_format($currentQty, 0, ',', '.').' PCS',
                'created_by' => optional($history->creator)->name ?: '-',
                'created_at' => $history->created_at->format('d M Y, H:i'),
            ],
            'timeline' => $history->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'event_type' => $detail->event_type,
                    'label' => StagingInHistoryDetail::EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
                    'qty_before' => $detail->qty_before,
                    'qty_change' => $detail->qty_change,
                    'qty_after' => $detail->qty_after,
                    'meta' => $detail->meta,
                    'notes' => $detail->notes,
                    'performed_by' => optional($detail->performedBy)->name ?: 'System',
                    'created_at' => $detail->created_at->format('d M Y, H:i'),
                ];
            }),
        ]);
    }

    /**
     * Pulihkan sejumlah history yang berstatus "Dihapus" sekaligus --
     * dipanggil dari tombol "Pulihkan" setelah user centang baris-baris
     * yang mau dipulihkan di tabel (mirip pola bulkDestroy di modul lain).
     *
     * Untuk setiap history yang valid (is_active=false DAN event
     * terakhirnya ada di self::REMOVED_EVENTS):
     * 1. Rekonstruksi data staging_in persis sebelum terhapus (lihat
     *    reconstructSnapshot()) dari staging_in_histories +
     *    staging_in_history_details-nya sendiri -- SATU-SATUNYA sumber
     *    data yang tersisa, karena baris staging_ins aslinya sudah hilang.
     * 2. Buat BARIS BARU di staging_ins dari snapshot itu (data
     *    staging_ins yang sudah ada sekarang sama sekali tidak disentuh).
     * 3. History yang sama di-"hidupkan" lagi: staging_in_id diarahkan ke
     *    baris baru itu, is_active jadi true.
     * 4. Dicatat 1 detail baru dengan event_type 'restored' supaya
     *    kelihatan di timeline sebagai aktivitas pemulihan.
     *
     * History yang statusnya BUKAN "Dihapus" (mis. baru saja dipulihkan
     * lagi, sudah aktif, atau sudah "Selesai"/moved) dilewati dan
     * dilaporkan sebagai error per-baris -- tidak menggagalkan baris lain
     * yang valid.
     *
     * PENTING: event_type 'restored' HARUS didaftarkan dulu di
     * App\Models\StagingInHistoryDetail::EVENT_TYPES & ::EVENT_COLORS
     * (mis. 'restored' => 'Dipulihkan' dan 'restored' => 'success') --
     * kalau belum, label/warnanya di tabel & timeline cuma fallback ke
     * key mentahnya ('restored') / warna 'secondary', tidak error, tapi
     * kurang rapi tampilannya.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_in_histories,id',
        ]);

        $restored = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$restored, &$errors) {

            $histories = StagingInHistory::whereIn('id', $request->ids)
                ->lockForUpdate()
                ->get();

            foreach ($histories as $history) {

                $history->load(['details' => function ($query) {
                    // Urutan KRONOLOGIS (lama -> baru) -- reconstructSnapshot()
                    // butuh urutan ini buat replay perubahan field satu-satu.
                    $query->reorder()->orderBy('created_at')->orderBy('id');
                }]);

                $lastDetail = $history->details->last();
                $label = ($history->po_number ?: 'History #'.$history->id);

                if ($history->is_active || ! $lastDetail || ! in_array($lastDetail->event_type, self::REMOVED_EVENTS, true)) {
                    $errors[] = "{$label}: dilewati -- statusnya bukan \"Dihapus\", tidak perlu/tidak bisa dipulihkan.";

                    continue;
                }

                try {

                    // PENTING (khusus Postgres): dibungkus DB::transaction()
                    // TERPISAH per baris (nested di dalam transaction luar)
                    // supaya Laravel otomatis pakai SAVEPOINT. Tanpa ini,
                    // begitu satu baris gagal (mis. melanggar constraint),
                    // Postgres langsung nge-abort SELURUH transaksi luar --
                    // baris lain yang sebenarnya valid ikut gagal semua
                    // dengan pesan generik "current transaction is aborted".
                    // Dengan SAVEPOINT, kegagalan 1 baris cuma di-ROLLBACK
                    // TO SAVEPOINT itu sendiri, baris lain & transaksi
                    // luarnya tetap aman dilanjutkan.
                    DB::transaction(function () use ($history) {

                        $snapshot = $this->reconstructSnapshot($history);

                        $newStagingIn = StagingIn::create([
                            'item_id' => $snapshot['item_id'],
                            'po_number' => $snapshot['po_number'],
                            'supplier_origin' => $snapshot['supplier_origin'],
                            'location' => $snapshot['location'],
                            'arrival_date' => $snapshot['arrival_date'],
                            'incoterms' => $snapshot['incoterms'],
                            'qty' => $snapshot['qty'],
                            'notes' => 'Dipulihkan dari History #'.$history->id,
                        ]);

                        $history->staging_in_id = $newStagingIn->id;
                        $history->is_active = true;
                        $history->save();

                        StagingInHistoryDetail::create([
                            'staging_in_history_id' => $history->id,
                            'event_type' => 'restored',
                            'qty_before' => 0,
                            'qty_change' => $snapshot['qty'],
                            'qty_after' => $snapshot['qty'],
                            'meta' => ['restored_staging_in_id' => $newStagingIn->id],
                            'notes' => 'Dipulihkan kembali ke Staging In (baris baru #'.$newStagingIn->id.')',
                            'performed_by' => auth()->id(),
                        ]);
                    });

                    $restored++;

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
                .(count($errors) ? ', '.count($errors).' baris dilewati/gagal.' : '.'),
        ]);
    }

    /**
     * Hapus PERMANEN sejumlah history sekaligus (beserta seluruh detail/
     * timeline-nya) -- dipanggil dari tombol "Hapus" setelah user centang
     * baris-baris di tabel. Semua status boleh dihapus, termasuk yang
     * masih Aktif. Data staging_ins itu sendiri tidak disentuh.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_in_histories,id',
        ]);

        $deleted = 0;

        DB::transaction(function () use ($request, &$deleted) {

            $ids = StagingInHistory::whereIn('id', $request->ids)
                ->lockForUpdate()
                ->pluck('id');

            StagingInHistoryDetail::whereIn('staging_in_history_id', $ids)->delete();

            $deleted = StagingInHistory::whereIn('id', $ids)->delete();
        });

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'errors' => [],
            'message' => $deleted.' history berhasil dihapus.',
        ]);
    }

    /**
     * Rekonstruksi kondisi staging_in PERSIS SEBELUM dihapus, dengan
     * "memutar ulang" seluruh staging_in_history_details-nya secara
     * kronologis di atas snapshot awal (kolom staging_in_histories itu
     * sendiri, yang merupakan data SAAT PERTAMA KALI dibuat):
     *
     * - Event 'updated': field apapun yang ada di meta-nya (format
     *   ['field' => ['old' => ..., 'new' => ...]]) menimpa snapshot
     *   dengan nilai 'new'-nya -- supaya kalau field selain qty (lokasi,
     *   supplier, PO, incoterms, tanggal) pernah diedit sebelum akhirnya
     *   dihapus, yang dipulihkan adalah nilai TERAKHIR, bukan nilai awal.
     * - qty: diambil dari qty_after tiap detail (running balance), KECUALI
     *   pada detail event terminasi (deleted/bulk_deleted/reset_by_import)
     *   yang qty_after-nya biasanya 0 (barisnya memang sudah hilang) --
     *   di situ dipakai qty_before-nya, yaitu qty PERSIS sebelum terhapus.
     *
     * Asumsi ini didasarkan pada pola qty_before/qty_change/qty_after
     * yang sudah ada di kode existing (lihat currentQtyBadge(), yang juga
     * pakai last_qty_after buat qty "saat ini"). Kalau ternyata konvensi
     * di StagingInHistoryService beda dari asumsi ini, silakan sesuaikan
     * logic di sini.
     */
    private function reconstructSnapshot(StagingInHistory $history): array
    {
        $snapshot = [
            'item_id' => $history->item_id,
            'po_number' => $history->po_number,
            'supplier_origin' => $history->supplier_origin,
            'location' => $history->location,
            'arrival_date' => $history->arrival_date,
            'incoterms' => $history->incoterms,
        ];

        $qty = (int) $history->initial_qty;

        foreach ($history->details as $detail) {

            if (in_array($detail->event_type, self::REMOVED_EVENTS, true)) {
                if (! is_null($detail->qty_before)) {
                    $qty = (int) $detail->qty_before;
                }

                break;
            }

            if ($detail->event_type === 'updated' && $detail->meta) {
                $meta = is_string($detail->meta) ? json_decode($detail->meta, true) : $detail->meta;

                foreach ((array) $meta as $field => $change) {
                    if (array_key_exists($field, $snapshot) && is_array($change) && array_key_exists('new', $change)) {
                        $snapshot[$field] = $change['new'];
                    }
                }
            }

            if (! is_null($detail->qty_after)) {
                $qty = (int) $detail->qty_after;
            }
        }

        $snapshot['qty'] = $qty;

        return $snapshot;
    }
}