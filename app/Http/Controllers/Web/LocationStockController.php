<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LocationStockExport;
use App\Exports\LocationStockTemplateExport;
use App\Imports\LocationStockImport;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Validators\ValidationException;




class LocationStockController extends Controller
{
    /**
     * Ambang batas "stok rendah". Disamakan dengan
     * DashboardController::LOW_STOCK_THRESHOLD.
     */
    private const LOW_STOCK_THRESHOLD = 10;

    public function index(Request $request)
    {
        $locations = Location::orderBy('location_name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('location_stock', compact('locations', 'vendors'))
            ->with('activeFilter', $request->query('filter'));
    }

    /**
     * Endpoint pencarian item untuk TomSelect (remote search).
     */
    public function searchItem(Request $request)
    {
        $keyword = $request->get('q');

        $items = Item::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('item_code_internal', 'LIKE', "%{$keyword}%")
                    ->orWhere('name', 'LIKE', "%{$keyword}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'item_code_internal', 'name']);

        return response()->json($items->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->item_code_internal.' - '.$item->name,
            ];
        }));
    }

    public function data(Request $request)
    {
        $query = Item::query()
            ->whereHas('locations')
            ->with(['locations', 'vendor'])
            ->orderByDesc('id');

        if ($request->filled('location_id')) {

            $query->whereHas('locations', function ($q) use ($request) {
                $q->where('locations.id', $request->location_id);
            });

        }

        if ($request->filled('vendor_id')) {

            $query->where('vendor_id', $request->vendor_id);

        }

        // Filter dari kartu/notifikasi dashboard: item yang punya stok di
        // salah satu lokasi kurang dari LOW_STOCK_THRESHOLD (tapi masih > 0).
        if ($request->query('filter') === 'low_stock') {

            $lowStockItemIds = LocationStock::where('quantity', '>', 0)
                ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
                ->pluck('item_id');

            $query->whereIn('id', $lowStockItemIds);

        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('item_code_internal', fn ($row) => $row->item_code_internal ?: '-')
            ->editColumn('name', fn ($row) => $row->name ?: '-')
            ->editColumn('vendor', fn ($row) => $row->vendor ? $row->vendor->name : '-')

            ->filterColumn('vendor', function ($query, $keyword) {
                $query->whereHas('vendor', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })

            ->orderColumn('vendor', function ($query, $order) {
                $query->orderBy(
                    Vendor::select('name')
                        ->whereColumn('vendors.id', 'items.vendor_id')
                        ->limit(1),
                    $order
                );
            })

            ->addColumn('locations_qty', function ($row) {

                if ($row->locations->isEmpty()) {
                    return '-';
                }

                $html = '';

                foreach ($row->locations as $location) {

                    $html .=
                        '<div>'
                        .$location->location_name.
                        ' <span class="text-muted fw-bold">('.
                        number_format($location->pivot->quantity, 0, ',', '.').
                        ')</span></div>';

                }

                return $html;

            })

            ->addColumn('action', function ($row) {

                return '
<div class="d-flex align-items-center gap-2">

    <button
        class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEditLocationStock"
        type="button"
        data-id="'.$row->id.'">

        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path fill="currentColor"
                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
        </svg>

    </button>

    <form action="'.route('location-stock.destroy', $row->id).'"
          method="POST"
          class="form-hapus m-0">

        '.csrf_field().'
        '.method_field('DELETE').'

        <button type="submit"
            class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0">

            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">

                <path d="M0 0h24v24H0z" fill="none"/>
                <path fill="currentColor"
                    d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>

            </svg>

        </button>

    </form>

</div>';
            })

            ->rawColumns([
                'checkbox',
                'locations_qty',
                'action',
            ])

            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([

            'item_id' => 'required|exists:items,id|unique:location_stock,item_id',

            'locations' => 'required|array|min:1',

            'locations.*.location' => 'required|string|max:255',
            'locations.*.quantity' => 'required|numeric|min:0',

        ], [

            'item_id.required' => 'Silakan pilih barang terlebih dahulu.',
            'item_id.exists' => 'Barang tidak ditemukan.',
            'item_id.unique' => 'Barang ini sudah memiliki data stok. Silakan edit data yang sudah ada.',

            'locations.required' => 'Minimal harus ada satu lokasi.',
            'locations.*.location.required' => 'Lokasi wajib diisi.',
            'locations.*.quantity.required' => 'Qty wajib diisi.',
            'locations.*.quantity.numeric' => 'Qty harus berupa angka.',
            'locations.*.quantity.min' => 'Qty tidak boleh kurang dari 0.',

        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->locations as $row) {

                $locationName = strtoupper(trim($row['location']));

                $location = Location::firstOrCreate([
                    'location_name' => $locationName,
                ]);

                LocationStock::create([
                    'item_id' => $request->item_id,
                    'location_id' => $location->id,
                    'opening_balance' => $row['quantity'],
                    'quantity' => $row['quantity'],
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Stok barang berhasil ditambahkan.',
        ]);
    }

    public function edit(Item $item)
    {
        $item->load('locations');

        return response()->json([
            'item' => $item,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([

            'locations' => 'required|array|min:1',

            'locations.*.location' => 'required|string|max:255',
            'locations.*.quantity' => 'required|numeric|min:0',

        ], [

            'locations.required' => 'Minimal harus ada satu lokasi.',
            'locations.*.location.required' => 'Lokasi wajib diisi.',
            'locations.*.quantity.required' => 'Qty wajib diisi.',
            'locations.*.quantity.numeric' => 'Qty harus berupa angka.',
            'locations.*.quantity.min' => 'Qty tidak boleh kurang dari 0.',

        ]);

        DB::transaction(function () use ($request, $item) {

            $syncData = [];

            foreach ($request->locations as $row) {

                $location = Location::firstOrCreate([
                    'location_name' => strtoupper(trim($row['location'])),
                ]);

                $syncData[$location->id] = [
                    'quantity' => $row['quantity'],
                ];

            }

            $item->locations()->sync($syncData);

        });

        return response()->json([
            'success' => true,
            'message' => 'Stok barang berhasil diperbarui.',
        ]);
    }

    public function destroy(Item $item)
    {
        $item->locations()->detach();

        return response()->json([
            'success' => true,
            'message' => 'Data stok barang berhasil dihapus.',
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:items,id',
        ]);

        $count = LocationStock::whereIn('item_id', $request->ids)
            ->distinct('item_id')
            ->count('item_id');

        LocationStock::whereIn('item_id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count.' data stok barang berhasil dihapus.',
        ]);
    }

     public function export(Request $request)
    {

        return Excel::download(new LocationStockExport($request), 'stock.xlsx');
    }

        public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        try {

            Excel::import(new LocationStockImport, $request->file('file'));

            return back()->with(
                'success',
                'Import data stock berhasil.'
            );

        } catch (QueryException $e) {

            $message = $e->getMessage();

            if (str_contains($message, 'location_stock_stock_id_location_id_unique')) {
                return back()->with('error', $message);
            }

            return back()->with('error', $message);

        } catch (ValidationException $e) {

            return back()->with('error', $e->getMessage());

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());

        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new LocationStockTemplateExport,
            'template_stock.xlsx'
        );
    }
}