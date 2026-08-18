<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingInExport;
use App\Exports\StagingInTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StagingInImport;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\StagingIn;
use App\Models\StagingOut;
use App\Models\StockMutation;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Psy\TabCompletion\Matcher\FunctionDefaultParametersMatcher;
use Yajra\DataTables\Facades\DataTables;

class StagingInController extends Controller
{
    public function index()
    {
        $totalEntry = StagingIn::count();

        $totalQty = StagingIn::sum('qty');

        $inboundShipment = StagingIn::where('location', 'Inbound shipment')->count();

        $holdRepair = StagingIn::whereIn('location', [
            'Temporary hold / repair 1',
            'Temporary hold / repair 2',
            'Temporary hold / repair 3',
        ])->count();

        $statusOptions = StagingIn::whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $ownerOptions = Vendor::query()
            ->whereIn('id', function ($query) {
                $query->select('items.vendor_id')
                    ->from('items')
                    ->join('staging_ins', 'staging_ins.item_id', '=', 'items.id')
                    ->whereNotNull('items.vendor_id');
            })
            ->orderBy('name')
            ->pluck('name');

        $supplierOptions = StagingIn::whereNotNull('supplier_origin')
            ->where('supplier_origin', '!=', '')
            ->distinct()
            ->orderBy('supplier_origin')
            ->pluck('supplier_origin');

        return view('staging_in', compact(
            'totalEntry',
            'totalQty',
            'inboundShipment',
            'holdRepair',
            'statusOptions',
            'ownerOptions',
            'supplierOptions'
        ));
    }

    private function agingBadge(?Carbon $arrivalDate): string
    {
        if (! $arrivalDate) {
            return '';
        }

        $days = (int) $arrivalDate->startOfDay()->diffInDays(now()->startOfDay());

        $color = match (true) {
            $days <= 5 => 'success',
            $days <= 10 => 'warning',
            default => 'danger',
        };

        return '
        <span class="badge bg-'.$color.'-subtle text-'.$color.' mt-1 d-inline-block">
            '.$days.' hari
        </span>';
    }

    /**
     * Server-side DataTables source.
     */
    public function data(Request $request)
    {
        $query = StagingIn::query()
            ->select('staging_ins.*')
            ->leftJoin('items', 'items.id', '=', 'staging_ins.item_id')
            ->leftJoin('vendors', 'vendors.id', '=', 'items.vendor_id')
            ->with('item.vendor')
            ->orderByDesc('staging_ins.id');

        if ($request->filled('location')) {
            $query->where('staging_ins.location', $request->location);
        }

        if ($request->filled('status')) {
            $query->where('staging_ins.status', $request->status);
        }

        if ($request->filled('incoterms')) {
            $query->where('staging_ins.incoterms', $request->incoterms);
        }

        if ($request->filled('item_owner')) {
            $query->where('vendors.name', $request->item_owner);
        }

        if ($request->filled('supplier_origin')) {
            $query->where('staging_ins.supplier_origin', $request->supplier_origin);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('staging_ins.arrival_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('staging_ins.arrival_date', '<=', $request->end_date);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->addColumn('item', function ($row) {
                return '
                <small class="fw-bold">'.e(optional($row->item)->item_code_internal).'</small>
                <div class="text-muted">'.e(optional($row->item)->name).'</div>
            ';
            })

            ->filterColumn('item', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.name', 'like', "%{$keyword}%")
                        ->orWhere('items.item_code_internal', 'like', "%{$keyword}%");
                });
            })

            ->orderColumn('item', 'items.name $1')

            ->filterColumn('supplier_origin', function ($query, $keyword) {
                $query->where('staging_ins.supplier_origin', 'like', "%{$keyword}%");
            })

            ->orderColumn('supplier_origin', 'staging_ins.supplier_origin $1')

            ->addColumn('item_owner', function ($row) {
                return optional(optional($row->item)->vendor)->name;
            })

            ->filterColumn('item_owner', function ($query, $keyword) {
                $query->where('vendors.name', 'like', "%{$keyword}%");
            })

            ->orderColumn('item_owner', 'vendors.name $1')

            ->editColumn('arrival_date', function ($row) {

                if (! $row->arrival_date) {
                    return '-';
                }

                return '
        <div>'.$row->arrival_date->format('d M Y').'</div>
        '.$this->agingBadge($row->arrival_date).'
    ';
            })

            ->editColumn('location', function ($row) {
                return $this->locationBadge($row->location);
            })

            ->editColumn('notes', function ($row) {
                return $row->notes ?: '-';
            })

            ->editColumn('status', function ($row) {
                return $row->status ?: '-';
            })

            ->editColumn('incoterms', function ($row) {
                return $row->incoterms ?: '-';
            })

            ->addColumn('action', function ($row) {

                return '
            <div class="">

                <button
                    class="btn btn-sm bg-success bg-opacity-10 text-success rounded-3 border-0 btnMove mb-1"
                    type="button"
                    data-id="'.$row->id.'"
                    data-code="'.e(optional($row->item)->item_code_internal).'"
                    data-name="'.e(optional($row->item)->name).'"
                    data-po="'.e($row->po_number).'"
                    data-owner="'.e(optional(optional($row->item)->vendor)->name).'"
                    data-supplier="'.e($row->supplier_origin).'"
                    data-qty="'.$row->qty.'"
                    data-location="'.e($row->location).'"
                    data-arrival="'.($row->arrival_date ? $row->arrival_date->format('d M Y') : '-').'"
                    data-bs-toggle="modal"
                    data-bs-target="#moveStagingModal"
                    title="Pindahkan barang">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor"
                            d="M9 3L5 6.99h3V14h2V6.99h3zm7 14.01V10h-2v7.01h-3L15 21l4-3.99z"/>
                    </svg>

                </button>

                <button
                    class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit mb-1"
                    type="button"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#editStagingModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor"
                            d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
                    </svg>

                </button>

                <form action="'.route('stagings-in.destroy', $row->id).'"
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
                'item',
                'location',
                'arrival_date',
                'action',
            ])

            ->make(true);
    }

    /**
     * Build the colored pill badge markup for a staging location value.
     */
    private function locationBadge(?string $location): string
    {
        if (! $location) {
            return '-';
        }

        $normalized = strtolower($location);

        if (str_contains($normalized, 'inbound')) {
            $color = 'info';
            $icon = 'bi-truck';
        } elseif (str_contains($normalized, 'hold') || str_contains($normalized, 'repair')) {
            preg_match('/(\d+)/', $location, $matches);
            $variant = isset($matches[1]) ? ((int) $matches[1] - 1) % 3 : 0;
            $repairColors = ['warning', 'primary', 'danger'];
            $color = $repairColors[$variant];
            $icon = 'bi-wrench';
        } else {
            $palette = ['success', 'secondary', 'dark'];
            $color = $palette[crc32($location) % count($palette)];
            $icon = 'bi-geo-alt';
        }

        return '<span class="badge rounded-pill bg-'.$color.'-subtle text-'.$color.' px-3 py-2">
            <i class="bi '.$icon.' me-1"></i>'.e($location).'
        </span>';
    }

    public function template()
    {
        return Excel::download(new StagingInTemplateExport, 'template_staging_in.xlsx');
    }

    /**
     * Export staging data to Excel, honoring the same filters used by the
     * search box, date range, and location filter on the index page.
     */
    public function export(Request $request)
    {
        return Excel::download(new StagingInExport($request), 'staging_in.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.nullable' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {

            // Hapus semua data lama secara eksplisit sebelum import,
            // tidak lagi bergantung pada event BeforeImport.
            StagingIn::query()->truncate();

            Excel::import(new StagingInImport, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('stagings-in.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('stagings-in.index')
            ->with('success', 'Data staging berhasil diimpor');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => ['nullable', 'max:255'],
            'arrival_date' => ['nullable', 'date'],
            'supplier_origin' => ['nullable', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', Rule::in(StagingIn::LOCATIONS)],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],
            'notes' => ['nullable'],
            'status' => ['nullable'],
        ], [
            'item_id.required' => 'Barang wajib dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        try {
            StagingIn::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return the record as JSON for the AJAX edit modal.
     */
    public function edit(StagingIn $staging)
    {
        $staging->loadMissing('item.vendor');

        return response()->json([
            'id' => $staging->id,
            'po_number' => $staging->po_number,
            'arrival_date' => optional($staging->arrival_date)->format('Y-m-d'),
            'supplier_origin' => $staging->supplier_origin,
            'item_id' => $staging->item_id,
            'item_code' => optional($staging->item)->item_code_internal,
            'item_name' => optional($staging->item)->name,
            'item_owner' => optional(optional($staging->item)->vendor)->name,
            'qty' => $staging->qty,
            'location' => $staging->location,
            'notes' => $staging->notes,
            'status' => $staging->status,
            'incoterms' => $staging->incoterms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StagingIn $staging)
    {
        $validated = $request->validate([
            'po_number' => ['nullable', 'max:255'],
            'arrival_date' => ['nullable', 'date'],
            'supplier_origin' => ['nullable', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', Rule::in(StagingIn::LOCATIONS)],
            'notes' => ['nullable'],
            'status' => ['nullable'],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],

        ], [
            'item_id.required' => 'Barang wajib dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        try {
            $staging->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Staging berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StagingIn $staging)
    {
        try {
            $staging->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search the stocks table for the "Barang" TomSelect used on the
     * add/edit staging forms (mirrors StockMutationController::searchStock).
     */
    public function searchStock(Request $request)
    {
        $keyword = $request->q;

        $items = Item::query()
            ->with('vendor')
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
                    'item_code' => $item->item_code_internal,
                    'item_name' => $item->name,
                    'item_owner' => optional($item->vendor)->name,
                ];
            })
        );
    }

    /**
     * Move a staging item into warehouse stock.
     *
     * Because this is triggered directly from the Staging In data, the
     * corresponding Stock master record and stock mutation history are
     * filled in automatically instead of asking the user to re-enter the
     * item in the manual mutation menu. The moved qty is deducted from the
     * staging entry (or the entry is removed once fully moved), and the
     * remainder stays in Staging In.
     */
    public function moveToStock(Request $request, StagingIn $staging)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'transaction_number' => ['nullable', 'max:100'],
            'notes' => ['nullable'],
        ], [
            'qty.required' => 'Qty dipindah wajib diisi',
            'qty.min' => 'Qty dipindah minimal 1',
            'location.required' => 'Lokasi gudang tujuan wajib dipilih',
            'transaction_date.required' => 'Tanggal wajib diisi',
        ]);

        if ($validated['qty'] > $staging->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Qty dipindah melebihi sisa barang di staging in.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            // item_id staging in sudah merujuk langsung ke master Item.
            $item = $staging->item;

            $location = Location::firstOrCreate([
                'location_name' => trim($validated['location']),
            ]);

            LocationStock::firstOrCreate(
                [
                    'item_id' => $item->id,
                    'location_id' => $location->id,
                ],
                ['quantity' => 0]
            );

            $mutation = StockMutation::create([
                'item_id' => $item->id,
                'location_id' => $location->id,
                'transaction_date' => $validated['transaction_date'],
                'transaction_number' => $validated['transaction_number'] ?: $staging->po_number,
                'description' => $validated['notes'] ?? null,
                'qty_in' => $validated['qty'],
                'qty_out' => 0,
                'qty_balance' => 0,
            ]);

            $this->recalculateLocationStock($item->id, $location->id, $mutation->id);

            $remaining = $staging->qty - $validated['qty'];

            if ($remaining > 0) {
                $staging->update(['qty' => $remaining]);
            } else {
                $staging->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dipindahkan ke stok.',
                'remaining_qty' => max($remaining, 0),
                'deleted' => $remaining <= 0,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move a staging item out directly to a customer (Staging Out).
     *
     * Unlike moveToStock, this does not touch warehouse stock/mutation
     * history - it records a delivery-out entry (staging_outs) instead.
     * The moved qty is deducted from the staging entry (or the entry is
     * removed once fully moved), same as moveToStock.
     */
    public function moveToStagingOut(Request $request, StagingIn $staging)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'so_number' => ['required', 'max:255'],
            'customer' => ['required', 'max:255'],
            'line_item' => ['required', 'max:255'],
            'delivery_instruction_date' => ['required', 'date'],
        ], [
            'qty.required' => 'Qty dipindah wajib diisi',
            'qty.min' => 'Qty dipindah minimal 1',
            'so_number.required' => 'No. SO wajib diisi',
            'customer.required' => 'Customer wajib diisi',
            'line_item.required' => 'Line item wajib diisi',
            'delivery_instruction_date.required' => 'Tanggal delivery instruction wajib diisi',
        ]);

        if ($validated['qty'] > $staging->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Qty dipindah melebihi sisa barang di staging in.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            \DB::listen(function ($query) {
                \Log::info('SQL QUERY', ['sql' => $query->sql, 'bindings' => $query->bindings]);
            });
            StagingOut::create([
                'so_number' => $validated['so_number'],
                'customer' => $validated['customer'],
                'item_id' => $staging->item_id,
                'line_item' => $validated['line_item'],
                'qty' => $validated['qty'],
                'delivery_instruction_date' => $validated['delivery_instruction_date'],
            ]);

            $remaining = $staging->qty - $validated['qty'];

            if ($remaining > 0) {
                $staging->update(['qty' => $remaining]);
            } else {
                $staging->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dipindahkan ke staging out.',
                'remaining_qty' => max($remaining, 0),
                'deleted' => $remaining <= 0,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate the qty_balance chain for a stock+location starting from
     * a given mutation onward, and sync LocationStock's running quantity.
     *
     * Mirrors StockMutationController::recalculateLocationStock so history
     * created from Staging In stays consistent with manually entered
     * mutations (e.g. if the transaction date is backdated before an
     * existing mutation).
     */
    private function recalculateLocationStock($itemId, $locationId, $startMutationId = null)
    {
        if ($startMutationId) {

            $startMutation = StockMutation::find($startMutationId);

            if (! $startMutation) {
                return;
            }

            $previousMutation = StockMutation::where('item_id', $itemId)
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

            if ($previousMutation) {

                $balance = $previousMutation->qty_balance;

            } else {

                $locationStock = LocationStock::where('item_id', $itemId)
                    ->where('location_id', $locationId)
                    ->first();

                $balance = $locationStock ? $locationStock->opening_balance : 0;
            }

            $mutations = StockMutation::where('item_id', $itemId)
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

            $mutations = StockMutation::where('item_id', $itemId)
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
                'item_id' => $itemId,
                'location_id' => $locationId,
            ],
            [
                'quantity' => $balance,
            ]
        );
    }

    /**
     * Return the list of warehouse locations for the "pindahkan ke stok"
     * destination select.
     */
    public function warehouseLocations()
    {
        return response()->json(
            Location::orderBy('location_name')->pluck('location_name')
        );
    }

    /**
     * Remove multiple resources at once.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:stagings,id',
        ]);

        try {
            $count = StagingIn::whereIn('id', $request->ids)->count();

            StagingIn::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => $count.' data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
