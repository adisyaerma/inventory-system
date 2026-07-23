<?php

namespace App\Http\Controllers\Web;

use App\Exports\StockExport;
use App\Exports\StockTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StockImport;
use App\Models\Location;
use App\Models\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function data(Request $request)
    {
        $query = Stock::with('locations')
            ->orderByRaw("
        CASE WHEN EXISTS (
            SELECT 1 FROM location_stock
            INNER JOIN locations ON locations.id = location_stock.location_id
            WHERE location_stock.stock_id = stocks.id
            AND locations.location_name = '-'
        ) THEN 1 ELSE 0 END ASC
    ")
            ->orderByDesc('id');

        if ($request->filled('location_id')) {

            $query->whereHas('locations', function ($q) use ($request) {

                $q->where('locations.id', $request->location_id);

            });

        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->editColumn('item_code_internal', fn ($row) => $row->item_code_internal ?: '-')
            ->editColumn('item_code_supplier', fn ($row) => $row->item_code_supplier ?: '-')
            ->editColumn('item_code_customer', fn ($row) => $row->item_code_customer ?: '-')
            ->editColumn('name', fn ($row) => $row->name ?: '-')
            ->editColumn('description', fn ($row) => $row->description ?: '-')

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
        class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEditStock"
        type="button"
        data-id="'.$row->id.'">

        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path fill="currentColor"
                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
        </svg>

    </button>

    <form action="'.route('stocks.destroy', $row->id).'"
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
                'locations_qty',
                'action',
            ])

            ->make(true);

    }

    public function mutations(Stock $stock)
    {
        $mutations = $stock->mutations()
            ->oldest('transaction_date')
            ->get();

        return response()->json($mutations);
    }

    public function index()
    {
        $locations = Location::orderBy('location_name')->get();

        return view('stock', compact('locations'));
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

            Excel::import(new StockImport, $request->file('file'));

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
            new StockTemplateExport,
            'template_stock.xlsx'
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'item_code_internal' => 'required|string|max:255|unique:stocks,item_code_internal',
            'item_code_supplier' => 'nullable|string|max:255',
            'item_code_customer' => 'nullable|string|max:255',

            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'locations' => 'required|array|min:1',

            'locations.*.location' => 'required|string|max:255',
            'locations.*.quantity' => 'required|numeric|min:0',

        ], [

            'item_code_internal.required' => 'Kode internal wajib diisi.',
            'item_code_internal.unique' => 'Barang sudah ada.',

            'name.required' => 'Nama barang wajib diisi.',

            'locations.required' => 'Minimal harus ada satu lokasi.',
            'locations.*.location.required' => 'Lokasi wajib diisi.',
            'locations.*.quantity.required' => 'Qty wajib diisi.',
            'locations.*.quantity.numeric' => 'Qty harus berupa angka.',
            'locations.*.quantity.min' => 'Qty tidak boleh kurang dari 0.',

        ]);

        DB::transaction(function () use ($request) {

            $stock = Stock::create([

                'item_code_internal' => $request->item_code_internal,
                'item_code_supplier' => $request->item_code_supplier,
                'item_code_customer' => $request->item_code_customer,

                'name' => $request->name,
                'description' => $request->description,

            ]);

            foreach ($request->locations as $row) {

                $locationName = strtoupper(trim($row['location']));

                $location = Location::firstOrCreate([
                    'location_name' => $locationName,
                ]);

                $stock->locations()->attach($location->id, [
                    'opening_balance' => $row['quantity'],
                    'quantity' => $row['quantity'],
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan.',
        ]);
    }

    public function edit(Stock $stock)
    {
        $stock->load('locations');

        return response()->json([
            'stock' => $stock,
        ]);
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'item_code_internal' => 'required|string|max:255',
            'item_code_supplier' => 'nullable|string|max:255',
            'item_code_customer' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'locations' => 'required|array|min:1',

            'locations.*.location' => 'required|string',

            'locations.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $stock) {

            $stock->update([

                'item_code_internal' => $request->item_code_internal,

                'item_code_supplier' => $request->item_code_supplier,

                'item_code_customer' => $request->item_code_customer,

                'name' => $request->name,

                'description' => $request->description,

            ]);

            $syncData = [];

            foreach ($request->locations as $row) {

                $location = Location::firstOrCreate(

                    [
                        'location_name' => trim($row['location']),
                    ]

                );

                $syncData[$location->id] = [

                    'quantity' => $row['quantity'],

                ];

            }

            $stock->locations()->sync($syncData);

        });

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
        ]);
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {

        return Excel::download(new StockExport($request), 'stock.xlsx');
    }
}
