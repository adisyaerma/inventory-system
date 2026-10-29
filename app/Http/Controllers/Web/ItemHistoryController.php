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

        foreach ($stagingOutDetails->where('event_type', 'created')->sortBy('created_at') as $detail) {
            $soNumber = $detail->history?->so_number;

            if (empty($soNumber)) {
                continue;
            }

            $key = $soNumber.'|'.(float) $detail->qty_after;

            if (! empty($mutationOutIdsByKey[$key])) {
                $mutationId = array_shift($mutationOutIdsByKey[$key]);

                $stagingOutByMutationId[$mutationId] = $detail;
            }
        }

        $timeline = $mutationRows
            ->map(function ($row) use ($movedToStockByMutationId, $stagingOutByMutationId) {
                $isIn = $row->qty_in > 0;

                $label = $isIn ? 'Barang Masuk' : 'Barang Keluar';
                $notes = $row->description;

                $fromStagingIn = $isIn ? $movedToStockByMutationId->get($row->id) : null;

                if ($fromStagingIn) {
                    $poNumber = $fromStagingIn->history?->po_number;

                    $label = 'Barang Masuk (dari Staging In)';
                    $notes = trim(
                        
                        ($row->description ? ' - '.$row->description : '')
                    );
                }

                $fromStagingOut = ! $isIn ? ($stagingOutByMutationId[$row->id] ?? null) : null;

                if ($fromStagingOut) {
                    $so = $fromStagingOut->history;

                    $label = 'Barang Keluar (dari Staging Out)';
                    $notes = trim(
                        ($so?->customer ? 'Customer: '.$so->customer : '').
                        ($so?->do_number ? ' | DO: '.$so->do_number : '')
                    );
                }

                return [
                    'date' => optional($row->transaction_date)->toDateString(),
                    'sort_key' => optional($row->created_at)->timestamp
                        ?? optional($row->transaction_date)->timestamp
                        ?? 0,
                    'source' => 'mutation',
                    'type' => $isIn ? 'mutation_in' : 'mutation_out',
                    'label' => $label,
                    'qty' => $isIn ? $row->qty_in : $row->qty_out,
                    'location' => $row->location->location_name ?? '-',
                    'lot' => $row->lot,
                    'reference' => $row->transaction_number,
                    'notes' => $notes,
                ];
            })
            ->sortBy('sort_key')
            ->values();

        return [
            'locations' => $locations,
            'timeline' => $timeline,
            'total_qty' => $locations->sum('quantity'),
        ];
    }
}