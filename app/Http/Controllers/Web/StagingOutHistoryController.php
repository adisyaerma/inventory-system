<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StagingOutHistoryDetail;
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
 * Halaman ini murni read-only (audit log), jadi tidak ada create/update/
 * delete di sini.
 */
class StagingOutHistoryController extends Controller
{
    /**
     * Event yang dianggap "menghapus" siklus staging out tanpa ada
     * penyelesaian pengiriman yang jelas.
     */
    private const REMOVED_EVENTS = ['deleted', 'bulk_deleted', 'reset_by_import'];

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

        $eventOptions = StagingOutHistoryDetail::EVENT_TYPES;

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

        return StagingOutHistory::query()
            ->select('staging_out_histories.*')
            ->leftJoin('items', 'items.id', '=', 'staging_out_histories.item_id')
            ->leftJoin('staging_outs', 'staging_outs.id', '=', 'staging_out_histories.staging_out_id')
            ->leftJoinSub($latestDetail, 'ld', function ($join) {
                $join->on('ld.staging_out_history_id', '=', 'staging_out_histories.id');
            })
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
                'staging_outs.qty as running_qty',
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
        $query = $this->baseQuery()->orderByDesc('staging_out_histories.id');

        if ($request->filled('status')) {
            match ($request->status) {
                'aktif' => $query->where('staging_out_histories.is_active', true)
                    ->whereNull('staging_out_histories.delivery_date'),
                // Selesai = delivery_date sudah terisi, apapun is_active-nya.
                'selesai' => $query->whereNotNull('staging_out_histories.delivery_date'),
                // Dihapus = hilang sebelum sempat terkirim.
                'dihapus' => $query->where('staging_out_histories.is_active', false)
                    ->whereNull('staging_out_histories.delivery_date'),
                default => null,
            };
        }

        if ($request->filled('event')) {
            $query->where('ld.event_type', $request->event);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '<=', $request->end_date);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('item_so', function ($row) {
                return '
                    <small class="fw-bold">'.e($row->item_code).'</small>
                    <div class="text-muted">'.e($row->item_name).'</div>
                    <div class="text-primary small">'.e($row->so_number ?: '-').'</div>
                ';
            })

            ->filterColumn('item_so', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.name', 'like', "%{$keyword}%")
                        ->orWhere('items.item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('staging_out_histories.so_number', 'like', "%{$keyword}%");
                });
            })

            ->orderColumn('item_so', 'items.name $1')

            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('staging_out_histories.customer', 'like', "%{$keyword}%");
            })

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

        $label = StagingOutHistoryDetail::EVENT_TYPES[$eventType] ?? $eventType;
        $color = StagingOutHistoryDetail::EVENT_COLORS[$eventType] ?? 'secondary';

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

        $currentQty = $history->is_active
            ? (int) optional(StagingOut::find($history->staging_out_id))->qty
            : (int) ($lastDetail->qty_after ?? $history->initial_qty);

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
                return [
                    'id' => $detail->id,
                    'event_type' => $detail->event_type,
                    'label' => StagingOutHistoryDetail::EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
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