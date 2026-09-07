<?php

namespace App\Http\Controllers\Web;

use App\Exports\StockMutationExport;
use App\Exports\StockMutationTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StockMutationImport;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StockMutationController extends Controller
{
    public function data(Request $request)
    {
        $query = StockMutation::with(['item', 'location'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($request->filled('item')) {
            $query->where('item_id', $request->item);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('transaction_date', function ($row) {
                return optional($row->transaction_date)->format('d-m-Y');
            })

            // ================= GABUNGAN KODE + NAMA BARANG =================
            ->addColumn('barang', function ($row) {
                return '<div class="fw-bold">'.e($row->item->item_code_internal).'</div>'
                     .'<div class="text-muted small">'.e($row->item->name).'</div>';
            })

            ->filterColumn('barang', function ($query, $keyword) {
                $query->whereHas('item', function ($q) use ($keyword) {
                    $q->where('item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('location_name', function ($row) {
                $location = $row->location->location_name ?? '-';

                if (! empty($row->lot)) {
                    return '<div class="fw-semibold">'.e($location).'</div>'
                         .'<div class="text-muted small">Lot: '.e($row->lot).'</div>';
                }

                return '<div class="fw-semibold">'.e($location).'</div>';
            })

            ->editColumn('qty_in', function ($row) {
                return rtrim(rtrim(number_format($row->qty_in, 2, '.', ''), '0'), '.');
            })

            ->editColumn('qty_out', function ($row) {
                return rtrim(rtrim(number_format($row->qty_out, 2, '.', ''), '0'), '.');
            })

            ->editColumn('qty_balance', function ($row) {
                return rtrim(rtrim(number_format($row->qty_balance, 2, '.', ''), '0'), '.');
            })

            ->addColumn('description', function ($row) {
                return $row->description;
            })
            ->addColumn('action', function ($row) {

                return '
<div class="d-flex align-items-center gap-2">

    <button
        class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
        type="button"
        data-id="'.$row->id.'"
        data-bs-toggle="modal"
        data-bs-target="#editMutationModal">

        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path fill="currentColor"
                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
        </svg>

    </button>

    <form action="'.route('stock-mutation.destroy', $row->id).'"
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
                'action',
                'barang',
                'checkbox',
                'location_name',
            ])

            ->make(true);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:stock_mutations,id',
        ]);

        DB::beginTransaction();

        try {

            $mutations = StockMutation::whereIn('id', $request->ids)->get();

            foreach ($mutations as $mutation) {
                $this->deleteMutationAndRecalculate($mutation);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($mutations).' mutasi berhasil dihapus.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    private function deleteMutationAndRecalculate(StockMutation $stockMutation)
    {
        $itemId = $stockMutation->item_id;
        $locationId = $stockMutation->location_id;
        $lot = $stockMutation->lot;

        $nextMutation = StockMutation::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('lot', $lot)
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
                $itemId,
                $locationId,
                $nextMutation->id,
                $lot
            );

        } else {

            $locationStock = LocationStock::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('lot', $lot)
                ->first();

            $lastBalance = StockMutation::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('lot', $lot)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->value('qty_balance');

            LocationStock::updateOrCreate(
                [
                    'item_id' => $itemId,
                    'location_id' => $locationId,
                    'lot' => $lot,
                ],
                [
                    'quantity' => $lastBalance ?? ($locationStock->opening_balance ?? 0),
                ]
            );
        }
    }

    private function recalculateLocationStock($itemId, $locationId, $startMutationId = null, $lot = null)
    {
        if ($startMutationId) {

            $startMutation = StockMutation::find($startMutationId);

            if (! $startMutation) {
                return;
            }

            $previousMutation = StockMutation::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('lot', $lot)
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

            if ($previousMutation) {

                $balance = $previousMutation->qty_balance;

            } else {

                $locationStock = LocationStock::where('item_id', $itemId)
                    ->where('location_id', $locationId)
                    ->where('lot', $lot)
                    ->first();

                $balance = $locationStock
                    ? $locationStock->opening_balance
                    : 0;
            }
            $mutations = StockMutation::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('lot', $lot)
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

            $mutations = StockMutation::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('lot', $lot)
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
                'item_id' => $itemId,
                'location_id' => $locationId,
                'lot' => $lot,
            ],
            [
                'quantity' => $balance,
            ]
        );
    }

    public function index(Request $request)
    {

        $locations = Location::orderBy('location_name')->get();

        // Total semua transaksi
        $totalMutasi = StockMutation::count();

        // Total barang masuk BULAN INI
        $barangMasuk = StockMutation::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('qty_in');

        // Total barang keluar BULAN INI
        $barangKeluar = StockMutation::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('qty_out');

        // Total stok saat ini (saldo seluruh lokasi)
        $totalStok = DB::table('location_stock')->sum('quantity');

        return view('stock_mutation', compact(
            'locations',
            'totalMutasi',
            'barangMasuk',
            'barangKeluar',
            'totalStok'
        ));
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

            $this->deleteMutationAndRecalculate($stockMutation);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil dihapus.',
            ]);

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

        $items = Item::query()

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

            $items->map(function ($item) {

                return [

                    'id' => $item->id,

                    'text' => $item->item_code_internal.' | '.$item->name,

                ];

            })

        );
    }

    public function currentStock(Request $request)
    {
        $request->validate([

            'item_id' => 'required|exists:items,id',

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

        $lot = $request->filled('lot') ? trim($request->lot) : null;

        $locationStock = LocationStock::where('item_id', $request->item_id)
            ->where('location_id', $location->id)
            ->where('lot', $lot)
            ->first();

        return response()->json([

            'qty' => $locationStock?->quantity ?? 0,

        ]);
    }

    /**
     * Daftar lot yang tersedia untuk kombinasi barang + lokasi.
     * Dipakai frontend untuk memunculkan pilihan lot otomatis setelah
     * user memilih barang lalu lokasi.
     */
    public function lots(Request $request)
    {
        $request->validate([

            'item_id' => 'required|exists:items,id',

            'location' => 'required',

        ]);

        $location = Location::where(
            'location_name',
            $request->location
        )->first();

        if (! $location) {
            return response()->json([]);
        }

        $stocks = LocationStock::where('item_id', $request->item_id)
            ->where('location_id', $location->id)
            ->orderByRaw('lot IS NULL, lot ASC')
            ->get(['lot', 'quantity']);

        return response()->json(

            $stocks->map(function ($stock) {

                return [
                    'id' => $stock->lot ?? '',
                    'text' => $stock->lot
                        ? $stock->lot.' (Qty: '.number_format($stock->quantity, 0, ',', '.').')'
                        : 'Tanpa Lot (Qty: '.number_format($stock->quantity, 0, ',', '.').')',
                    'qty' => $stock->quantity,
                ];

            })

        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'location' => 'required',
            'lot' => 'nullable|string|max:255',
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

            $lot = $request->filled('lot') ? trim($request->lot) : null;

            // Kalau barang ini punya lebih dari satu baris stok (lot) di
            // lokasi tersebut, user wajib memilih salah satu lot-nya
            // terlebih dahulu supaya saldo tidak tercampur antar lot.
            $lotRowCount = LocationStock::where('item_id', $request->item_id)
                ->where('location_id', $location->id)
                ->count();

            if ($lotRowCount > 1 && ! $lot) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Barang ini memiliki beberapa lot di lokasi tersebut. Silakan pilih lot terlebih dahulu.',
                ], 422);
            }

            $locationStock = LocationStock::firstOrCreate(
                [
                    'item_id' => $request->item_id,
                    'location_id' => $location->id,
                    'lot' => $lot,
                ],
                [
                    'quantity' => 0,
                    'opening_balance' => 0,
                ]
            );

            if ($request->qty_out > 0 && $request->qty_out > $locationStock->quantity) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Qty keluar melebihi stok.',
                ], 422);
            }

            $mutation = StockMutation::create([
                'item_id' => $request->item_id,
                'location_id' => $location->id,
                'lot' => $lot,
                'transaction_date' => $request->transaction_date,
                'transaction_number' => $request->transaction_number,
                'description' => $request->description,
                'qty_in' => $request->qty_in,
                'qty_out' => $request->qty_out,
                'qty_balance' => 0,
            ]);

            $this->recalculateLocationStock(
                $request->item_id,
                $location->id,
                $mutation->id,
                $lot
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil ditambahkan.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function edit(StockMutation $mutation)
    {
        $mutation->load([
            'item',
            'location',
        ]);

        return response()->json([

            'id' => $mutation->id,

            'transaction_date' => Carbon::parse($mutation->transaction_date)
                ->format('Y-m-d'),

            'item_id' => $mutation->item_id,

            'item_name' => $mutation->item->item_code_internal.' | '.$mutation->item->name,

            'location' => $mutation->location->location_name,

            'lot' => $mutation->lot,

            'transaction_type' => $mutation->transaction_type,

            'transaction_number' => $mutation->transaction_number,

            'description' => $mutation->description,

            'warehouse' => $mutation->warehouse,

            'reference' => $mutation->reference,

            'value' => $mutation->value,

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
            'item_id' => 'required|exists:items,id',
            'location' => 'required',
            'lot' => 'nullable|string|max:255',
            'transaction_number' => 'nullable|max:100',
            'description' => 'nullable',
            'qty_in' => 'required|numeric|min:0',
            'qty_out' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $oldItemId = $mutation->item_id;
            $oldLocationId = $mutation->location_id;
            $oldLot = $mutation->lot;

            $location = Location::firstOrCreate([
                'location_name' => trim($request->location),
            ]);

            $lot = $request->filled('lot') ? trim($request->lot) : null;

            $lotRowCount = LocationStock::where('item_id', $request->item_id)
                ->where('location_id', $location->id)
                ->count();

            if ($lotRowCount > 1 && ! $lot) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Barang ini memiliki beberapa lot di lokasi tersebut. Silakan pilih lot terlebih dahulu.',
                ], 422);
            }

            $locationStock = LocationStock::firstOrCreate(
                [
                    'item_id' => $request->item_id,
                    'location_id' => $location->id,
                    'lot' => $lot,
                ],
                [
                    'quantity' => 0,
                    'opening_balance' => 0,
                ]
            );

            $availableQty = $locationStock->quantity;

            if (
                $oldItemId == $request->item_id &&
                $oldLocationId == $location->id &&
                $oldLot == $lot
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
                'item_id' => $request->item_id,
                'location_id' => $location->id,
                'lot' => $lot,
                'transaction_date' => $request->transaction_date,
                'transaction_number' => $request->transaction_number,
                'description' => $request->description,
                'qty_in' => $request->qty_in,
                'qty_out' => $request->qty_out,
            ]);

            $this->recalculateLocationStock(
                $oldItemId,
                $oldLocationId,
                $startMutationId,
                $oldLot
            );

            if (
                $oldItemId != $request->item_id ||
                $oldLocationId != $location->id ||
                $oldLot != $lot
            ) {
                $this->recalculateLocationStock(
                    $request->item_id,
                    $location->id,
                    $startMutationId,
                    $lot
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
            ->where('item_id', $request->item_id)
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
