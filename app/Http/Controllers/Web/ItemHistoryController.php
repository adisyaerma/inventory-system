<?php

namespace App\Http\Controllers\Web;

use App\Exports\ItemHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\LocationStock;
use App\Models\StagingInHistoryDetail;
use App\Models\StagingOutHistoryDetail;
use App\Models\StockMutation;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ItemHistoryController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::orderBy('name')->get();

        return view('item_history.index', compact('vendors'));
    }

    public function data(Request $request)
    {
        $query = Item::query()
            ->with('vendor')
            ->orderByDesc('id');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->editColumn('item_code_internal', fn ($row) => $row->item_code_internal ?: '-')
            ->editColumn('name', fn ($row) => $row->name ?: '-')

            ->editColumn('description', function ($row) {

                if (empty($row->description)) {
                    return '-';
                }

                $short = \Illuminate\Support\Str::limit($row->description, 80);

                return '<span title="'.e($row->description).'">'.e($short).'</span>';
            })

            ->addColumn('vendor', fn ($row) => $row->vendor->name ?? '-')

            ->filterColumn('vendor', function ($query, $keyword) {
                $query->whereHas('vendor', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })

            ->addColumn('total_qty', function ($row) {
                $qty = LocationStock::where('item_id', $row->id)->sum('quantity');

                return number_format($qty, 0, ',', '.');
            })

            ->addColumn('last_activity', function ($row) {
                $lastDate = StockMutation::where('item_id', $row->id)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->value('transaction_date');

                return $lastDate ? Carbon::parse($lastDate)->format('d-m-Y') : '-';
            })

            ->addColumn('action', function ($row) {
                $url = route('item-history.show', $row->id);

                return '
<a href="'.$url.'"
   class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-6" viewBox="0 0 24 24">
        <path d="M0 0h24v24H0z" fill="none"/>
        <path fill="currentColor" d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89l.07.14L9 12H6a7 7 0 1 1 2.05 4.95l-1.42 1.42A9 9 0 1 0 13 3m-1 5v5l4.28 2.54l.72-1.21l-3.5-2.08V8z"/>
    </svg>
    <span class="d-none d-md-inline ms-1">Riwayat</span>
</a>';
            })

            ->rawColumns([
                'action',
                'description',
            ])

            ->make(true);
    }

    public function show(Item $item)
    {
        $item->load('vendor');

        return view('item_history.detail', compact('item'));
    }

    public function timeline(Item $item)
    {
        $data = $this->buildHistoryData($item);

        return response()->json([
            'item' => [
                'item_code_internal' => $item->item_code_internal,
                'name' => $item->name,
                'vendor' => $item->vendor->name ?? '-',
                'total_qty' => $data['total_qty'],
            ],
            'locations' => $data['locations'],
            'timeline' => $data['timeline'],
        ]);
    }

    public function export(Item $item)
    {
        $item->load('vendor');

        $data = $this->buildHistoryData($item);

        $safeCode = str_replace(
            ['/', '\\'],
            '-',
            $item->item_code_internal ?: $item->id
        );

        $fileName = 'Riwayat - '.$safeCode.'.xlsx';

        return Excel::download(
            new ItemHistoryExport($item, $data['timeline'], $data['total_qty']),
            $fileName
        );
    }

    private const FIELD_LABELS = [
        'qty' => 'Qty',
        'item_id' => 'Barang',
        'po_number' => 'PO',
        'supplier_origin' => 'Supplier',
        'location' => 'Lokasi',
        'location_name' => 'Lokasi',
        'arrival_date' => 'Tgl Kedatangan',
        'incoterms' => 'Incoterms',
        'so_number' => 'SO',
        'customer' => 'Customer',
        'line_item' => 'Line Item',
        'delivery_instruction_date' => 'Tgl Instruksi Kirim',
        'picking_date' => 'Tgl Picking',
        'do_number' => 'DO',
        'delivery_date' => 'Tgl Kirim',
        'staging_location' => 'Lokasi Staging',
        'lot' => 'Lot',
        'notes' => 'Catatan',
        'transaction_date' => 'Tgl Transaksi',
        'transaction_number' => 'No. Transaksi',
    ];

    private function fmtValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return is_scalar($value) ? (string) $value : (string) json_encode($value);
    }

    /** Ringkas perubahan event "updated": Qty: 5 → 3 | Lokasi: A → B */
    private function describeChanges(array $changes): string
    {
        $parts = [];

        foreach ($changes as $field => $change) {
            if (! is_array($change) || ! array_key_exists('new', $change)) {
                continue;
            }

            $parts[] = (self::FIELD_LABELS[$field] ?? $field).': '
                .$this->fmtValue($change['old'] ?? null).' → '.$this->fmtValue($change['new']);
        }

        return implode(' | ', $parts);
    }   

    /** Ringkas meta event selain "updated" (id teknis dilewati). */
    private function describeMeta(array $meta): string
    {
        $parts = [];

        foreach ($meta as $field => $value) {
            if (! isset(self::FIELD_LABELS[$field]) || $value === null || $value === '') {
                continue;
            }

            $parts[] = self::FIELD_LABELS[$field].': '.$this->fmtValue($value);
        }

        return implode(' | ', $parts);
    }

    /**
     * Tentukan arah (in/out) dan judul untuk satu event staging.
     * Return [null, null] kalau event itu bukan pergerakan barang
     * (tidak ada perubahan qty), supaya tidak ditampilkan di riwayat.
     */
    private function stagingMovement(string $area, string $eventType, $qty): array
    {
        if ($qty === null || (float) $qty == 0.0) {
            return [null, null];
        }

        $name = $area === 'in' ? 'Staging In' : 'Staging Out';

        if ($eventType === 'created') {
            return ['in', 'Masuk ke '.$name];
        }

        if (str_starts_with($eventType, 'moved') || str_contains($eventType, 'delete')) {
            $dest = '';

            if (str_contains($eventType, 'delete')) {
                $dest = ' (data dihapus)';
            } elseif (str_contains($eventType, 'stock')) {
                $dest = ' (ke Stok)';
            } elseif ($area === 'in' && str_contains($eventType, 'out')) {
                $dest = ' (ke Staging Out)';
            }

            return ['out', 'Keluar dari '.$name.$dest];
        }

        // event lain (mis. data diubah) yang mengubah qty
        return (float) $qty > 0
            ? ['in', 'Masuk ke '.$name.' (qty ditambah)']
            : ['out', 'Keluar dari '.$name.' (qty dikurangi)'];
    }

    private function stagingInRows($details)
    {
        return $details
            ->map(function ($d) {
                [$direction, $title] = $this->stagingMovement('in', (string) $d->event_type, $d->qty_change);

                if ($direction === null) {
                    return null;
                }

                $meta = is_array($d->meta) ? $d->meta : [];

                $notes = $d->event_type === 'updated'
                    ? $this->describeChanges($meta)
                    : $this->describeMeta($meta);

                if (! empty($d->notes)) {
                    $notes = trim($notes.($notes ? ' | ' : '').$d->notes);
                }

                return [
                    'date' => optional($d->created_at)->toDateString(),
                    'date_label' => 'Tanggal',
                    'sort_key' => optional($d->created_at)->timestamp ?? 0,
                    'source' => 'staging_in',
                    'type' => 'staging_in',
                    'direction' => $direction,
                    'label' => $title,
                    'qty' => abs((float) $d->qty_change),
                    'location' => $d->history?->location ?: '-',
                    'lot' => null,
                    'reference' => $d->history?->po_number,
                    'notes' => $notes,
                    'by' => $d->performed_by,
                ];
            })
            ->filter()
            ->values();
    }

    private function stagingOutRows($details, array $matchedCreated)
    {
        // "Data Dibuat" yang sudah punya mutasi keluar tampil lewat mutasinya.
        $matchedDetailIds = collect($matchedCreated)->map(fn ($d) => $d->id)->all();

        return $details
            ->reject(fn ($d) => in_array($d->id, $matchedDetailIds, true))
            // Status pengiriman (picking / sudah dikirim) bukan pergerakan barang.
            ->reject(fn ($d) => in_array($d->event_type, ['delivered', 'picking_confirmed'], true))
            ->map(function ($d) {
                [$direction, $title] = $this->stagingMovement('out', (string) $d->event_type, $d->qty_change);

                // "Masuk ke Staging Out" tidak ditampilkan; cukup sisi barang keluarnya.
                if ($direction === null || $direction === 'in') {
                    return null;
                }

                $meta = is_array($d->meta) ? $d->meta : [];

                $notes = $d->event_type === 'updated'
                    ? $this->describeChanges($meta)
                    : $this->describeMeta($meta);

                if (! empty($d->history?->customer) && $d->event_type === 'created') {
                    $notes = trim('Customer: '.$d->history->customer.($notes ? ' | '.$notes : ''));
                }

                if (! empty($d->notes)) {
                    $notes = trim($notes.($notes ? ' | ' : '').$d->notes);
                }

                return [
                    'date' => optional($d->created_at)->toDateString(),
                    'date_label' => 'Tanggal',
                    'sort_key' => optional($d->created_at)->timestamp ?? 0,
                    'source' => 'staging_out',
                    'type' => 'staging_out',
                    'direction' => $direction,
                    'label' => $title,
                    'qty' => abs((float) $d->qty_change),
                    'location' => $meta['location_name'] ?? '-',
                    'lot' => $meta['lot'] ?? null,
                    'reference' => $d->history?->so_number,
                    'notes' => $notes,
                    'by' => $d->performed_by,
                ];
            })
            ->filter()
            ->values();
    }

    private function isMoveRow($m): bool
    {
        return empty($m->transaction_number)
            && str_starts_with((string) $m->description, 'Pindah lokasi');
    }

    /** Pasangkan mutasi keluar+masuk "Pindah lokasi" jadi 1 baris. */
    private function pairMoves($mutationRows): array
    {
        $ins = $mutationRows->filter(fn ($m) => $m->qty_in > 0 && $this->isMoveRow($m))->sortBy('created_at')->values();
        $outs = $mutationRows->filter(fn ($m) => $m->qty_out > 0 && $this->isMoveRow($m))->sortBy('created_at')->values();

        $used = [];
        $rows = [];

        foreach ($outs as $out) {
            $in = $ins->first(fn ($i) => ! isset($used[$i->id])
                && (float) $i->qty_in == (float) $out->qty_out
                && (string) $i->lot === (string) $out->lot
                && str_contains((string) $out->description, $i->location->location_name ?? '#')
                && str_contains((string) $i->description, $out->location->location_name ?? '#'));

            if (! $in) {
                continue;
            }

            $used[$in->id] = true;
            $used[$out->id] = true;

            $rows[] = [
                'date' => optional($in->transaction_date)->toDateString(),
                'date_label' => 'Tgl Pindah',
                'sort_key' => optional($in->created_at)->timestamp ?? 0,
                'source' => 'mutation',
                'type' => 'mutation_move',
                'direction' => 'move',
                'label' => 'Pindah Lokasi',
                'qty' => (float) $in->qty_in,
                'location' => ($out->location->location_name ?? '-').' → '.($in->location->location_name ?? '-'),
                'lot' => $in->lot,
                'reference' => null,
                'notes' => null,
                'by' => null,
            ];
        }

        return [$rows, $used];
    }

    private function buildHistoryData(Item $item): array
    {
        $locations = LocationStock::with('location')
            ->where('item_id', $item->id)
            ->get()
            ->map(function ($row) {
                return [
                    'location' => $row->location->location_name ?? '-',
                    'lot' => $row->lot,
                    'quantity' => (float) $row->quantity,
                ];
            })
            ->values();

        $stagingInDetails = StagingInHistoryDetail::with('history')
            ->whereHas('history', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })
            ->get();

        $movedToStockByMutationId = $stagingInDetails
            ->where('event_type', 'moved_to_stock')
            ->filter(fn ($d) => ! empty($d->meta['stock_mutation_id']))
            ->keyBy(fn ($d) => $d->meta['stock_mutation_id']);

        $mutationRows = StockMutation::with('location')
            ->where('item_id', $item->id)
            ->get();

        $stagingOutDetails = StagingOutHistoryDetail::with('history')
            ->whereHas('history', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })
            ->get();

        $mutationOutIdsByKey = [];

        foreach ($mutationRows->filter(fn ($m) => $m->qty_out > 0 && ! empty($m->transaction_number))->sortBy('created_at') as $m) {
            $mutationOutIdsByKey[$m->transaction_number.'|'.(float) $m->qty_out][] = $m->id;
        }

        $stagingOutByMutationId = [];
        $unmatchedOutDetails = [];

        foreach ($stagingOutDetails->where('event_type', 'created')->sortBy('created_at') as $detail) {
            $soNumber = $detail->history?->so_number;

            if (! empty($soNumber)) {
                $key = $soNumber.'|'.(float) $detail->qty_after;

                if (! empty($mutationOutIdsByKey[$key])) {
                    $mutationId = array_shift($mutationOutIdsByKey[$key]);

                    $stagingOutByMutationId[$mutationId] = $detail;

                    continue;
                }
            }

            $unmatchedOutDetails[] = $detail;
        }

        // Cadangan: no SO kosong / tidak cocok -> cocokkan lewat qty, lokasi, lot,
        // dan waktu terdekat (mutasi keluar dibuat bersamaan dengan data staging out).
        foreach ($unmatchedOutDetails as $detail) {
            $meta = is_array($detail->meta) ? $detail->meta : [];
            $qty = abs((float) ($detail->qty_after ?: $detail->qty_change));
            $locName = $meta['location_name'] ?? $meta['location'] ?? null;
            $lot = (string) ($meta['lot'] ?? '');
            $at = optional($detail->created_at)->timestamp ?? 0;

            $best = null;
            $bestDiff = 3600;

            foreach ($mutationRows as $m) {
                if ($m->qty_out <= 0
                    || isset($stagingOutByMutationId[$m->id])
                    || $this->isMoveRow($m)
                    || abs((float) $m->qty_out - $qty) > 0.0001
                    || (string) ($m->lot ?? '') !== $lot
                    || ($locName !== null && ($m->location->location_name ?? null) !== $locName)) {
                    continue;
                }

                $diff = abs((optional($m->created_at)->timestamp ?? 0) - $at);

                if ($diff <= $bestDiff) {
                    $best = $m;
                    $bestDiff = $diff;
                }
            }

            if ($best) {
                $stagingOutByMutationId[$best->id] = $detail;
            }
        }

        [$moveRows, $pairedIds] = $this->pairMoves($mutationRows);

        $mutationIdSet = $mutationRows->pluck('id')->flip()->all();

        $timeline = $mutationRows
            ->reject(fn ($row) => isset($pairedIds[$row->id]))
            ->map(function ($row) use ($movedToStockByMutationId, $stagingOutByMutationId) {
                $isIn = $row->qty_in > 0;

                $label = $isIn ? 'Barang Masuk' : 'Barang Keluar';
                $notes = $row->description;

                $isMove = empty($row->transaction_number)
                    && str_starts_with((string) $row->description, 'Pindah lokasi');

                if ($isMove) {
                    $label = $isIn ? 'Pindah Lokasi (Masuk)' : 'Pindah Lokasi (Keluar)';
                }

                $fromStagingIn = $isIn ? $movedToStockByMutationId->get($row->id) : null;

                if ($fromStagingIn) {
                    return [
                        'date' => optional($row->transaction_date)->toDateString(),
                        'date_label' => 'Tgl Pindah',
                        'sort_key' => optional($row->created_at)->timestamp
                            ?? optional($row->transaction_date)->timestamp
                            ?? 0,
                        'source' => 'mutation',
                        'type' => 'staging_in_to_stock',
                        'direction' => 'move',
                        'label' => 'Masuk ke Stok (dari Staging In)',
                        'qty' => (float) $row->qty_in,
                        'location' => ($fromStagingIn->history?->location ?: 'Staging In')
                            .' → '.($row->location->location_name ?? '-'),
                        'lot' => $row->lot,
                        'reference' => $fromStagingIn->history?->po_number ?: $row->transaction_number,
                        'notes' => $row->description ?: null,
                        'by' => $fromStagingIn->performed_by,
                    ];
                }

                $reference = $row->transaction_number;

                $fromStagingOut = ! $isIn ? ($stagingOutByMutationId[$row->id] ?? null) : null;

                if ($fromStagingOut) {
                    $so = $fromStagingOut->history;
                    $soNumber = $so?->so_number;

                    $label = 'Keluar dari Stok (ke Staging Out)';
                    $notes = implode(' | ', array_filter([
                        $so?->customer ? 'Customer: '.$so->customer : null,
                        $so?->do_number ? 'DO: '.$so->do_number : null,
                    ]));
                    $reference = $soNumber ?: $row->transaction_number;
                }

                // Masuk: tgl masuk = transaction_date di stock_mutations.
                // Keluar: tgl kirim = tgl resi pengiriman di staging out
                // (fallback ke transaction_date kalau belum terisi).
                $date = optional($row->transaction_date)->toDateString();
                $dateLabel = $isMove ? 'Tgl Pindah' : ($isIn ? 'Tgl Masuk' : 'Tgl Kirim');

                if ($fromStagingOut) {
                    $deliveryDate = $fromStagingOut->history?->delivery_date;

                    if (! empty($deliveryDate)) {
                        $date = Carbon::parse($deliveryDate)->toDateString();
                    }
                }

                return [
                    'date' => $date,
                    'date_label' => $dateLabel,
                    'sort_key' => optional($row->created_at)->timestamp
                        ?? optional($row->transaction_date)->timestamp
                        ?? 0,
                    'source' => 'mutation',
                    'type' => $isIn ? 'mutation_in' : 'mutation_out',
                    'direction' => $isIn ? 'in' : 'out',
                    'label' => $label,
                    'qty' => (float) ($isIn ? $row->qty_in : $row->qty_out),
                    'location' => $row->location->location_name ?? '-',
                    'lot' => $row->lot,
                    'reference' => $reference,
                    'notes' => $notes,
                    'by' => null,
                ];
            })
            ->concat($moveRows)
            ->concat($this->stagingInRows($stagingInDetails->reject(
                fn ($d) => $d->event_type === 'moved_to_stock'
                    && ! empty($d->meta['stock_mutation_id'])
                    && isset($mutationIdSet[$d->meta['stock_mutation_id']])
            )))
            ->concat($this->stagingOutRows($stagingOutDetails, $stagingOutByMutationId))
            // Urut berdasarkan tgl masuk/kirim (terlama di atas); kalau tanggal
            // sama, urut berdasarkan waktu data dibuat.
            ->sortBy([
                fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']),
                fn ($a, $b) => $a['sort_key'] <=> $b['sort_key'],
            ])
            ->values();

        return [
            'locations' => $locations,
            'timeline' => $timeline,
            'total_qty' => $locations->sum('quantity'),
        ];
    }
}