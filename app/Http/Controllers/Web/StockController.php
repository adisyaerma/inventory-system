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

class StockController extends Controller
{
    public function mutations(Stock $stock)
    {
        $mutations = $stock->mutations()
            ->oldest('transaction_date')
            ->get();

        return response()->json($mutations);
    }

    public function index()
    {
        $stocks = Stock::with('locations')->latest()->get();
        $locations = Location::orderBy('location_name')->get();

        return view('stock', compact('stocks', 'locations'));
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
                    'quantity' => $row['quantity'],
                ]);
            }
        });

        return back()->with(
            'success',
            'Data stok berhasil ditambahkan.'
        );
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

            /*
            |--------------------------------------------------------------------------
            | Update data stock
            |--------------------------------------------------------------------------
            */

            $stock->update([

                'item_code_internal' => $request->item_code_internal,

                'item_code_supplier' => $request->item_code_supplier,

                'item_code_customer' => $request->item_code_customer,

                'name' => $request->name,

                'description' => $request->description,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Sync Location
            |--------------------------------------------------------------------------
            */

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

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();

        return redirect()->back()
            ->with('deleted', 'Data berhasil dihapus');
    }

    public function export(Request $request)
    {

        return Excel::download(
            new StockExport($request->location_id),
            'stock.xlsx'
        );
    }
}
