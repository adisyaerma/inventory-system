<?php

namespace App\Http\Controllers\Web;

use App\Exports\StockMutationExport;
use App\Exports\StockMutationTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StockMutationImport;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Stock;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockMutationController extends Controller
{
    private function recalculateLocationStock($stockId, $locationId, $startMutationId = null)
    {
        if ($startMutationId) {

            $startMutation = StockMutation::find($startMutationId);

            if (! $startMutation) {
                return;
            }

            $previousMutation = StockMutation::where('stock_id', $stockId)
                ->where('location_id', $locationId)
                ->where(function ($q) use ($startMutation) {
                    $q->where('transaction_date', '<', $startMutation->transaction_date)
                        ->orWhere(function ($q2) use ($startMutation) {
                            $q2->where('transaction_date', $startMutation->transaction_date)
                                ->where('id', '<', $startMutation->id);
                        });
                })
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->first();

            $balance = $previousMutation ? $previousMutation->qty_balance : 0;

            $mutations = StockMutation::where('stock_id', $stockId)
                ->where('location_id', $locationId)
                ->where(function ($q) use ($startMutation) {
                    $q->where('transaction_date', '>', $startMutation->transaction_date)
                        ->orWhere(function ($q2) use ($startMutation) {
                            $q2->where('transaction_date', $startMutation->transaction_date)
                                ->where('id', '>=', $startMutation->id);
                        });
                })
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

        } else {

            $balance = 0;

            $mutations = StockMutation::where('stock_id', $stockId)
                ->where('location_id', $locationId)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();
        }

        foreach ($mutations as $mutation) {

            $balance += $mutation->qty_in;
            $balance -= $mutation->qty_out;

            $mutation->update([
                'qty_balance' => $balance,
            ]);
        }

        LocationStock::updateOrCreate(
            [
                'stock_id' => $stockId,
                'location_id' => $locationId,
            ],
            [
                'quantity' => $balance,
            ]
        );
    }

    public function index(Request $request)
    {
        $query = StockMutation::query();

        if ($request->filled('stock')) {
            $query->where('stock_id', $request->stock);
        }

        $locations = Location::orderBy('location_name')->get();

        $mutations = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return view('stock_mutation', compact('mutations', 'locations'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        Excel::import(
            new StockMutationImport,
            $request->file('file')
        );

        return back()->with('success', 'Import berhasil');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new StockMutationTemplateExport,
            'template_mutasi_stok.xlsx'
        );
    }

    public function destroy(StockMutation $stockMutation)
    {
        DB::beginTransaction();

        try {

            $stockId = $stockMutation->stock_id;
            $locationId = $stockMutation->location_id;

            $nextMutation = StockMutation::where('stock_id', $stockId)
                ->where('location_id', $locationId)
                ->where(function ($q) use ($stockMutation) {
                    $q->where('transaction_date', '>', $stockMutation->transaction_date)
                        ->orWhere(function ($q2) use ($stockMutation) {
                            $q2->where('transaction_date', $stockMutation->transaction_date)
                                ->where('id', '>', $stockMutation->id);
                        });
                })
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->first();

            $stockMutation->delete();

            if ($nextMutation) {

                $this->recalculateLocationStock(
                    $stockId,
                    $locationId,
                    $nextMutation->id
                );

            } else {

                // Jika sudah tidak ada mutasi setelahnya,
                // cukup update quantity berdasarkan mutasi terakhir
                $lastBalance = StockMutation::where('stock_id', $stockId)
                    ->where('location_id', $locationId)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->value('qty_balance') ?? 0;

                LocationStock::updateOrCreate(
                    [
                        'stock_id' => $stockId,
                        'location_id' => $locationId,
                    ],
                    [
                        'quantity' => $lastBalance,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('deleted', 'Data mutasi berhasil dihapus.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());

        }
    }

    public function searchStock(Request $request)
    {
        $keyword = $request->q;

        $stocks = Stock::query()

            ->when($keyword, function ($query) use ($keyword) {

                $query->where(function ($q) use ($keyword) {

                    $q->where('item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%");

                });

            })

            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(

            $stocks->map(function ($stock) {

                return [

                    'id' => $stock->id,

                    'text' => $stock->item_code_internal.' | '.$stock->name,

                ];

            })

        );
    }

    public function currentStock(Request $request)
    {
        $request->validate([

            'stock_id' => 'required|exists:stocks,id',

            'location' => 'required',

        ]);

        $location = Location::where(
            'location_name',
            $request->location
        )->first();

        if (! $location) {

            return response()->json([

                'qty' => 0,

            ]);

        }

        $locationStock = LocationStock::where('stock_id', $request->stock_id)
            ->where('location_id', $location->id)
            ->first();

        return response()->json([

            'qty' => $locationStock?->quantity ?? 0,

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'stock_id' => 'required|exists:stocks,id',
            'location' => 'required',
            'transaction_type' => 'required',
            'transaction_number' => 'nullable|max:100',
            'description' => 'nullable',
            'qty_in' => 'required|numeric|min:0',
            'qty_out' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $location = Location::firstOrCreate([
                'location_name' => trim($request->location),
            ]);

            $locationStock = LocationStock::firstOrCreate(
                [
                    'stock_id' => $request->stock_id,
                    'location_id' => $location->id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            if ($request->qty_out > $locationStock->quantity) {

                DB::rollBack();

                return back()
                    ->withErrors([
                        'qty' => 'Qty keluar melebihi stok.',
                    ])
                    ->withInput();
            }

            $mutation = StockMutation::create([
                'stock_id' => $request->stock_id,
                'location_id' => $location->id,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'transaction_number' => $request->transaction_number,
                'description' => $request->description,
                'qty_in' => $request->qty_in,
                'qty_out' => $request->qty_out,
                'qty_balance' => 0,
            ]);

            $this->recalculateLocationStock(
                $request->stock_id,
                $location->id,
                $mutation->id
            );

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Mutasi berhasil ditambahkan.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(StockMutation $mutation)
    {
        $mutation->load([
            'stock',
            'location',
        ]);

        return response()->json([

            'id' => $mutation->id,

            'transaction_date' => Carbon::parse($mutation->transaction_date)
                ->format('Y-m-d'),

            'stock_id' => $mutation->stock_id,

            'stock_name' => $mutation->stock->item_code_internal.' | '.$mutation->stock->name,

            'location' => $mutation->location->location_name,

            'transaction_type' => $mutation->transaction_type,

            'transaction_number' => $mutation->transaction_number,

            'description' => $mutation->description,

            'qty_type' => $mutation->qty_in > 0 ? 'in' : 'out',

            'qty' => $mutation->qty_in > 0
                ? $mutation->qty_in
                : $mutation->qty_out,

            'qty_in' => $mutation->qty_in,

            'qty_out' => $mutation->qty_out,

        ]);
    }

    public function update(Request $request, StockMutation $mutation)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'stock_id' => 'required|exists:stocks,id',
            'location' => 'required',
            'transaction_type' => 'required',
            'transaction_number' => 'nullable|max:100',
            'description' => 'nullable',
            'qty_in' => 'required|numeric|min:0',
            'qty_out' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $oldStockId = $mutation->stock_id;
            $oldLocationId = $mutation->location_id;

            $location = Location::firstOrCreate([
                'location_name' => trim($request->location),
            ]);

            $locationStock = LocationStock::firstOrCreate(
                [
                    'stock_id' => $request->stock_id,
                    'location_id' => $location->id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            /*
            |--------------------------------------------------------------
            | Validasi stok keluar
            |--------------------------------------------------------------
            */

            $availableQty = $locationStock->quantity;

            // kalau edit lokasi & barang yang sama, kembalikan qty mutasi lama
            if (
                $oldStockId == $request->stock_id &&
                $oldLocationId == $location->id
            ) {
                $availableQty = $availableQty - $mutation->qty_in + $mutation->qty_out;
            }

            if ($request->qty_out > ($availableQty + $request->qty_in)) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Qty keluar melebihi stok.',
                ], 422);
            }

            $startMutationId = $mutation->id;

            $mutation->update([
                'stock_id' => $request->stock_id,
                'location_id' => $location->id,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'transaction_number' => $request->transaction_number,
                'description' => $request->description,
                'qty_in' => $request->qty_in,
                'qty_out' => $request->qty_out,
            ]);

            /*
            |--------------------------------------------------------------
            | Hitung ulang saldo
            |--------------------------------------------------------------
            */

            $this->recalculateLocationStock(
                $oldStockId,
                $oldLocationId,
                $startMutationId
            );

            if (
                $oldStockId != $request->stock_id ||
                $oldLocationId != $location->id
            ) {
                $this->recalculateLocationStock(
                    $request->stock_id,
                    $location->id,
                    $startMutationId
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Mutasi berhasil diperbarui.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error($e);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        return Excel::download(
            new StockMutationExport($request),
            'Stock Mutation.xlsx'
        );
    }

    public function defaultLocation(Request $request)
    {
        $locationStock = LocationStock::with('location')
            ->where('stock_id', $request->stock_id)
            ->orderByDesc('quantity')
            ->first();

        if (! $locationStock) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $locationStock->location->location_name,
            'text' => $locationStock->location->location_name,
        ]);
    }
}
