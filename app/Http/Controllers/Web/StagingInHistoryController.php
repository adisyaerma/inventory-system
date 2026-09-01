<?php

namespace App\Http\Controllers\Web;

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
 * Halaman ini murni read-only (audit log), jadi tidak ada create/update/
 * delete di sini.
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

        if ($request->filled('status')) {
            match ($request->status) {
                'aktif' => $query->where('staging_in_histories.is_active', true),
                'selesai' => $query->where('staging_in_histories.is_active', false)
                    ->whereIn('ld.event_type', self::COMPLETED_EVENTS),
                'dihapus' => $query->where('staging_in_histories.is_active', false)
                    ->whereIn('ld.event_type', self::REMOVED_EVENTS),
                default => null,
            };
        }

        if ($request->filled('event')) {
            $query->where('ld.event_type', $request->event);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('staging_in_histories.arrival_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('staging_in_histories.arrival_date', '<=', $request->end_date);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('item_po', function ($row) {
                return '
                    <small class="fw-bold">'.e($row->item_code).'</small>
                    <div class="text-muted">'.e($row->item_name).'</div>
                    <div class="text-primary small">'.e($row->po_number ?: '-').'</div>
                ';
            })

            ->filterColumn('item_po', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.name', 'like', "%{$keyword}%")
                        ->orWhere('items.item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('staging_in_histories.po_number', 'like', "%{$keyword}%");
                });
            })

            ->orderColumn('item_po', 'items.name $1')

            ->filterColumn('supplier_origin', function ($query, $keyword) {
                $query->where('staging_in_histories.supplier_origin', 'like', "%{$keyword}%");
            })

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
}