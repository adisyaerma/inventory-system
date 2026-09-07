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
use App\Services\StagingInHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Psy\TabCompletion\Matcher\FunctionDefaultParametersMatcher;
use Yajra\DataTables\Facades\DataTables;

class StagingInController extends Controller
{
    /**
     * Ambang umur (hari) staging in dianggap overdue / perlu follow up.
     * Nilainya disamakan dengan DashboardController::FOLLOW_UP_DAYS —
     * kalau salah satu diubah, ubah juga yang satunya.
     */
    private const FOLLOW_UP_DAYS = 7;

    public function __construct(private StagingInHistoryService $history)
    {
    }

    public function index(Request $request)
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
        ))->with('activeFilter', $request->query('filter'));
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

        // Filter khusus dari kartu/notifikasi dashboard: barang yang sudah
        // berada di staging in lebih dari FOLLOW_UP_DAYS hari sejak
        // arrival_date.
        if ($request->query('filter') === 'overdue') {
            $query->whereNotNull('staging_ins.arrival_date')
                ->where('staging_ins.arrival_date', '<=', now()->subDays(self::FOLLOW_UP_DAYS));
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

            // Catat dulu seluruh data lama ke history SEBELUM di-truncate —
            // truncate() menghapus baris secara langsung di database tanpa
            // lewat Eloquent, jadi kalau tidak disnapshot dulu, perubahan
            // ini tidak akan pernah tercatat di mana pun.
            $this->history->logResetByImport(StagingIn::all());

            // Hapus semua data lama secara eksplisit sebelum import,
            // tidak lagi bergantung pada event BeforeImport.
            StagingIn::query()->truncate();

            Excel::import(app(StagingInImport::class), $request->file('file'));
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
            'warehouse_location' => ['nullable', 'string', 'max:255'],
            'lot' => ['nullable', 'string', 'max:255'],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],
            'notes' => ['nullable'],
            'status' => ['nullable'],
        ], [
            'item_id.required' => 'Barang wajib dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        $qty = (int) ($validated['qty'] ?? 0);
        $warehouseLocationName = $validated['warehouse_location'] ?? null;
        unset($validated['warehouse_location']);

        // Barang belum tentu punya lot — kalau tidak diisi (atau memang
        // tidak dikirim sama sekali dari form), dianggap tidak berlot.
        $lot = ! empty($validated['lot']) ? trim($validated['lot']) : null;
        $validated['lot'] = $lot;

        $sourceLocation = null;

        // Kalau "Lokasi Gudang Asal" dipilih, barang ini benar-benar ditarik
        // dari stok gudang tersebut — qty tidak boleh melebihi stok yang
        // sebenarnya tersedia di sana.
        if ($warehouseLocationName) {

            $sourceLocation = Location::where('location_name', $warehouseLocationName)->first();

            if (! $sourceLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi gudang asal tidak ditemukan.',
                ], 422);
            }

            // Kalau barang ini punya lebih dari satu baris stok (lot) di
            // lokasi tersebut, user wajib memilih salah satu lot-nya
            // terlebih dahulu supaya penarikan tidak salah lot.
            $lotRowCount = LocationStock::where('item_id', $validated['item_id'])
                ->where('location_id', $sourceLocation->id)
                ->count();

            if ($lotRowCount > 1 && ! $lot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ini memiliki beberapa lot di lokasi tersebut. Silakan pilih lot terlebih dahulu.',
                ], 422);
            }

            $available = LocationStock::where('item_id', $validated['item_id'])
                ->where('location_id', $sourceLocation->id)
                ->where('lot', $lot)
                ->value('quantity') ?? 0;

            if ($qty > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "Qty melebihi stok yang tersedia di lokasi tersebut (tersedia: {$available} pcs).",
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            if ($sourceLocation) {

                $mutation = StockMutation::create([
                    'item_id' => $validated['item_id'],
                    'location_id' => $sourceLocation->id,
                    'lot' => $lot,
                    'transaction_date' => $validated['arrival_date'] ?? now()->format('Y-m-d'),
                    'transaction_number' => $validated['po_number'] ?? null,
                    'description' => 'Ditarik untuk Staging In'.($validated['po_number'] ? ' ('.$validated['po_number'].')' : ''),
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'qty_balance' => 0,
                ]);

                $this->recalculateLocationStock($validated['item_id'], $sourceLocation->id, $mutation->id, $lot);

                $validated['warehouse_location_id'] = $sourceLocation->id;
                $validated['stock_mutation_id'] = $mutation->id;
            }

            $staging = StagingIn::create($validated);

            $this->history->logCreated($staging);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
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
     * Return the record as JSON for the AJAX edit modal.
     */
    public function edit(StagingIn $staging)
    {
        $staging->loadMissing(['item.vendor', 'warehouseLocation']);

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
            'warehouse_location' => optional($staging->warehouseLocation)->location_name,
            'lot' => $staging->lot,
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
            'warehouse_location' => ['nullable', 'string', 'max:255'],
            'lot' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable'],
            'status' => ['nullable'],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],

        ], [
            'item_id.required' => 'Barang wajib dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        $qty = (int) ($validated['qty'] ?? 0);
        $warehouseLocationName = $validated['warehouse_location'] ?? null;
        unset($validated['warehouse_location']);

        // Barang belum tentu punya lot — kalau tidak diisi, dianggap tidak
        // berlot.
        $lot = ! empty($validated['lot']) ? trim($validated['lot']) : null;
        $validated['lot'] = $lot;

        $newItemId = (int) $validated['item_id'];
        $newLocation = null;

        if ($warehouseLocationName) {

            $newLocation = Location::where('location_name', $warehouseLocationName)->first();

            if (! $newLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi gudang asal tidak ditemukan.',
                ], 422);
            }

            // Kalau barang ini punya lebih dari satu baris stok (lot) di
            // lokasi tersebut, user wajib memilih salah satu lot-nya
            // terlebih dahulu supaya penarikan tidak salah lot.
            $lotRowCount = LocationStock::where('item_id', $newItemId)
                ->where('location_id', $newLocation->id)
                ->count();

            if ($lotRowCount > 1 && ! $lot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ini memiliki beberapa lot di lokasi tersebut. Silakan pilih lot terlebih dahulu.',
                ], 422);
            }
        }

        // Sumber tarikan (barang + lokasi + lot) sama dengan yang sudah
        // tersimpan? Kalau sama, qty lama yang sudah "ditarik" dianggap
        // balik dulu sebelum dibandingkan ke qty baru, supaya menaikkan
        // qty tidak salah dianggap kekurangan stok gara-gara stoknya
        // sendiri.
        $sameSource = $newLocation
            && (int) $staging->warehouse_location_id === $newLocation->id
            && (int) $staging->item_id === $newItemId
            && $staging->lot === $lot;

        if ($newLocation) {

            $available = LocationStock::where('item_id', $newItemId)
                ->where('location_id', $newLocation->id)
                ->where('lot', $lot)
                ->value('quantity') ?? 0;

            if ($sameSource) {
                $available += (int) $staging->qty;
            }

            if ($qty > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "Qty melebihi stok yang tersedia di lokasi tersebut (tersedia: {$available} pcs).",
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $changes = $this->history->diff($staging, $validated);

            $oldMutationId = $staging->stock_mutation_id;
            $oldLocationId = $staging->warehouse_location_id;

            $sourceChanged = ! $sameSource && ($oldMutationId !== null || $newLocation !== null);

            if ($oldMutationId && $sourceChanged) {
                // Barang/lokasi gudang asalnya berubah (atau dihapus) —
                // hapus mutasi lama & hitung ulang stok lokasi lamanya,
                // seolah-olah stok itu tidak pernah ditarik.
                $oldMutation = StockMutation::find($oldMutationId);

                if ($oldMutation) {
                    $this->deleteMutationAndRecalculate($oldMutation);
                }

                $oldMutationId = null;
            }

            if ($newLocation) {

                if (! $sourceChanged && $oldMutationId) {
                    // Sumbernya sama, tinggal sesuaikan qty mutasi yang
                    // sudah ada dan hitung ulang stoknya dari situ.
                    StockMutation::where('id', $oldMutationId)->update([
                        'qty_out' => $qty,
                        'lot' => $lot,
                    ]);

                    $this->recalculateLocationStock($newItemId, $newLocation->id, $oldMutationId, $lot);

                    $validated['stock_mutation_id'] = $oldMutationId;
                } else {
                    // Sumber baru (atau sebelumnya tidak menarik stok
                    // sama sekali) — buat mutasi keluar baru.
                    $mutation = StockMutation::create([
                        'item_id' => $newItemId,
                        'location_id' => $newLocation->id,
                        'lot' => $lot,
                        'transaction_date' => $validated['arrival_date'] ?? now()->format('Y-m-d'),
                        'transaction_number' => $validated['po_number'] ?? null,
                        'description' => 'Ditarik untuk Staging In'.($validated['po_number'] ? ' ('.$validated['po_number'].')' : ''),
                        'qty_in' => 0,
                        'qty_out' => $qty,
                        'qty_balance' => 0,
                    ]);

                    $this->recalculateLocationStock($newItemId, $newLocation->id, $mutation->id, $lot);

                    $validated['stock_mutation_id'] = $mutation->id;
                }

                $validated['warehouse_location_id'] = $newLocation->id;

            } else {
                $validated['warehouse_location_id'] = null;
                $validated['stock_mutation_id'] = null;
            }

            $staging->update($validated);

            $this->history->logUpdated($staging, $changes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staging berhasil diperbarui.',
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
     * Remove the specified resource from storage.
     */
    public function destroy(StagingIn $staging)
    {
        DB::beginTransaction();

        try {
            $this->history->logDeleted($staging);

            // Kalau staging ini menarik stok dari sebuah lokasi gudang,
            // hapus mutasi keluarnya & kembalikan stoknya seolah-olah
            // penarikan itu tidak pernah terjadi.
            if ($staging->stock_mutation_id) {

                $mutation = StockMutation::find($staging->stock_mutation_id);

                if ($mutation) {
                    $this->deleteMutationAndRecalculate($mutation);
                }
            }

            $staging->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
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

            $this->history->logMovedToStock($staging, $validated['qty'], max($remaining, 0), [
                'location' => $location->location_name,
                'transaction_date' => $validated['transaction_date'],
                'transaction_number' => $mutation->transaction_number,
                'stock_mutation_id' => $mutation->id,
                'notes' => $validated['notes'] ?? null,
            ]);

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
            $stagingOut = StagingOut::create([
                'so_number' => $validated['so_number'],
                'customer' => $validated['customer'],
                'item_id' => $staging->item_id,
                'line_item' => $validated['line_item'],
                'qty' => $validated['qty'],
                'delivery_instruction_date' => $validated['delivery_instruction_date'],
            ]);

            $remaining = $staging->qty - $validated['qty'];

            $this->history->logMovedToStagingOut($staging, $validated['qty'], max($remaining, 0), [
                'so_number' => $validated['so_number'],
                'customer' => $validated['customer'],
                'line_item' => $validated['line_item'],
                'delivery_instruction_date' => $validated['delivery_instruction_date'],
                'staging_out_id' => $stagingOut->id,
            ]);

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
    /**
     * Delete a stock mutation (created when staging in pulled stock from a
     * warehouse location) and recalculate that location's running balance,
     * as if the mutation never happened. Mirrors
     * StockMutationController::deleteMutationAndRecalculate.
     */
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

            $this->recalculateLocationStock($itemId, $locationId, $nextMutation->id, $lot);

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

                $balance = $locationStock ? $locationStock->opening_balance : 0;
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

    /**
     * Return the list of warehouse locations that currently have stock
     * for a given item, used to populate "Lokasi Gudang Asal" on the
     * add/edit staging forms (loadItemLocations() di blade).
     */
    public function itemLocations(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);

        // Satu lokasi bisa punya beberapa baris stok (per lot), jadi
        // dijumlahkan dulu per lokasi supaya pilihan "Lokasi Gudang Asal"
        // tetap satu opsi per lokasi. Pemilihan lot spesifiknya dilakukan
        // lewat select Lot terpisah setelah lokasi dipilih (lihat lots()).
        $locations = LocationStock::with('location')
            ->where('item_id', $request->item_id)
            ->get()
            ->filter(fn ($stock) => $stock->location !== null)
            ->groupBy(fn ($stock) => $stock->location->location_name)
            ->map(function ($stocks) {
                return [
                    'name' => $stocks->first()->location->location_name,
                    'quantity' => $stocks->sum('quantity'),
                ];
            })
            ->filter(fn ($loc) => $loc['quantity'] > 0)
            ->sortBy('name')
            ->values();

        return response()->json($locations);
    }

    /**
     * Daftar lot yang tersedia untuk kombinasi barang + lokasi gudang asal
     * yang sedang dipilih di form staging in. Dipakai frontend untuk
     * memunculkan pilihan lot otomatis setelah user memilih "Lokasi Gudang
     * Asal" (mirrors StockMutationController::lots()).
     */
    public function lots(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'location' => 'required',
        ]);

        $location = Location::where('location_name', $request->location)->first();

        if (! $location) {
            return response()->json([]);
        }

        $stocks = LocationStock::where('item_id', $request->item_id)
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
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
            'ids.*' => 'integer|exists:staging_ins,id',
        ]);

        DB::beginTransaction();

        try {
            // Ambil datanya dulu SEBELUM dihapus — setelah delete, data
            // ini sudah tidak bisa diambil lagi untuk disnapshot ke history.
            $stagings = StagingIn::whereIn('id', $request->ids)->get();

            $this->history->logBulkDeleted($stagings);

            // Kembalikan stok untuk tiap staging yang menarik dari lokasi
            // gudang, sebelum entri stagingnya sendiri dihapus.
            foreach ($stagings as $staging) {

                if (! $staging->stock_mutation_id) {
                    continue;
                }

                $mutation = StockMutation::find($staging->stock_mutation_id);

                if ($mutation) {
                    $this->deleteMutationAndRecalculate($mutation);
                }
            }

            StagingIn::whereIn('id', $request->ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $stagings->count().' data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}