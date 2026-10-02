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

                return '<input type="checkbox" class="form-check-input historyCheckbox"
                    data-active="'.($row->is_active ? '1' : '0').'"
                    data-cancel-stock="'.($cancelsStock ? '1' : '0').'"
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
            ->filter(function ($query) use ($request) {
                StagingOutHistoryExport::applySearch($query, $request->input('search.value'));
            }, true)

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
                    'label' => StagingOutHistoryDetail::EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
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