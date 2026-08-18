<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutExport;
use App\Exports\StagingOutTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StagingOutImport;
use App\Models\Item;
use App\Models\StagingOut;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StagingOutController extends Controller
{
    public function index()
    {
        $totalEntry = StagingOut::count();

        $totalQty = StagingOut::sum('qty');
        $customers = StagingOut::select('customer')->distinct()->orderBy('customer')->pluck('customer');

        $sudahPicking = StagingOut::whereNotNull('picking_date')->count();

        $sudahDikirim = StagingOut::whereNotNull('delivery_date')->count();

        $belumPicking = StagingOut::whereNull('picking_date')->count();

        $belumDikirim = StagingOut::whereNull('delivery_date')->count();

        return view('staging_out', compact(
            'totalEntry',
            'totalQty',
            'sudahPicking',
            'sudahDikirim',
            'customers'
        ));
    }

    /**
     * Server-side DataTables source.
     */
    public function data(Request $request)
    {
        $query = StagingOut::query()
            ->select('staging_outs.*')
            ->leftJoin('items', 'items.id', '=', 'staging_outs.item_id')
            ->with('item')
            ->orderByDesc('staging_outs.id');

        // 1. Status
        if ($request->status === 'belum_picking') {
            $query->whereNull('staging_outs.picking_date');

        } elseif ($request->status === 'sudah_picking') {
            $query->whereNotNull('staging_outs.picking_date');

        } elseif ($request->status === 'belum_dikirim') {
            $query->whereNull('staging_outs.delivery_date');

        } elseif ($request->status === 'sudah_dikirim') {
            $query->whereNotNull('staging_outs.delivery_date');
        }

        // 2. Customer
        if ($request->filled('customer')) {
            $query->where('staging_outs.customer', $request->customer);
        }

        // 3. Rentang tanggal — HANYA satu kolom, sesuai date_type yang dipilih
        $dateColumn = in_array($request->date_type, ['delivery_instruction_date', 'picking_date', 'delivery_date'])
            ? $request->date_type
            : 'delivery_instruction_date';

        if ($request->filled('start_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '<=', $request->end_date);
        }

        // 4. Overdue
        if ($request->overdue == 1) {
            $query->whereDate('staging_outs.delivery_instruction_date', '<', now())->whereNull('staging_outs.delivery_date');
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('item_code', function ($row) {
                if (! $row->item) {
                    return '-';
                }

                return '
                <small class="fw-bold">'.e($row->item->item_code_internal).'</small>
                <div class="text-muted">'.e($row->item->name).'</div>
            ';
            })
            ->filterColumn('item_code', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('items.name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('item_code', 'items.item_code_internal $1')
            ->editColumn('line_item', function ($row) {
                return $row->line_item ?: '-';
            })
            ->filterColumn('line_item', function ($query, $keyword) {
                $query->where('staging_outs.line_item', 'like', "%{$keyword}%");
            })
            ->orderColumn('line_item', 'staging_outs.line_item $1')

            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('staging_outs.customer', 'like', "%{$keyword}%");
            })

            ->orderColumn('customer', 'staging_outs.customer $1')

            ->editColumn('delivery_instruction_date', function ($row) {
                return optional($row->delivery_instruction_date)->format('d M Y');
            })

            ->editColumn('picking_date', function ($row) {

                if ($row->picking_date) {
                    return optional($row->picking_date)->format('d M Y');
                }

                return '
                <button type="button"
                    class="btn btn-sm btn-confirm-picking btnConfirmPicking"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmPickingModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="me-1">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor" d="M20 3H4a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1M9 17l-4-4l1.41-1.41L9 14.17l7.59-7.59L18 8z"/>
                    </svg>
                    Konfirmasi Picking
                </button>';
            })

            ->editColumn('delivery_date', function ($row) {

                if ($row->delivery_date) {
                    return optional($row->delivery_date)->format('d M Y');
                }

                return '
                <button type="button"
                    class="btn btn-sm btn-confirm-delivery btnConfirmDelivery"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeliveryModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="me-1">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor" d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5m13.5-9l1.96 2.5H17V9.5zM18 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5"/>
                    </svg>
                    Konfirmasi Kirim
                </button>';
            })

            ->editColumn('do_number', function ($row) {
                return $row->do_number ?: '-';
            })

            ->addColumn('action', function ($row) {

                return '
            <div class="d-flex align-items-center gap-1">

                <button
                    class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
                    type="button"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#editStagingOutModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor"
                            d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
                    </svg>

                </button>

                <form action="'.route('stagings-out.destroy', $row->id).'"
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
                'item_code',
                'picking_date',
                'delivery_date',
                'action',
            ])

            ->make(true);
    }

    /**
     * Search the items table for the "Kode Barang" TomSelect used on the
     * add/edit staging out forms (mirrors StagingInController::searchStock).
     */
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
                    'item_code' => $item->item_code_internal,
                    'item_name' => $item->name,
                ];
            })
        );
    }

    public function template()
    {
        return Excel::download(new StagingOutTemplateExport, 'template_staging_out.xlsx');
    }

    /**
     * Export staging out data to Excel, honoring the same filters used by the
     * search box and date range on the index page.
     */
    public function export(Request $request)
    {
        return Excel::download(new StagingOutExport($request), 'staging_out.xlsx');
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
            StagingOut::query()->truncate();

            Excel::import(new StagingOutImport, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('stagings-out.index')
            ->with('success', 'Data staging berhasil diimpor');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => ['nullable', 'max:255'],
            'customer' => ['nullable', 'max:255'],
            'item_id' => ['nullable', 'exists:items,id'],
            'line_item' => ['nullable', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'delivery_instruction_date' => ['nullable', 'date'],
            'picking_date' => ['nullable', 'date'],
            'do_number' => ['nullable', 'max:255'],
            'delivery_date' => ['nullable', 'date'],
        ], [
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        try {
            StagingOut::create($validated);

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
    public function edit(StagingOut $stagingOut)
    {
        $stagingOut->loadMissing('item');

        return response()->json([
            'id' => $stagingOut->id,
            'so_number' => $stagingOut->so_number,
            'customer' => $stagingOut->customer,
            'item_id' => $stagingOut->item_id,
            'item_code' => optional($stagingOut->item)->item_code_internal,
            'item_name' => optional($stagingOut->item)->name,
            'line_item' => $stagingOut->line_item,
            'qty' => $stagingOut->qty,
            'delivery_instruction_date' => optional($stagingOut->delivery_instruction_date)->format('Y-m-d'),
            'picking_date' => optional($stagingOut->picking_date)->format('Y-m-d'),
            'do_number' => $stagingOut->do_number,
            'delivery_date' => optional($stagingOut->delivery_date)->format('Y-m-d'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StagingOut $stagingOut)
    {
        $validated = $request->validate([
            'so_number' => ['nullable', 'max:255'],
            'customer' => ['nullable', 'max:255'],
            'item_id' => ['nullable', 'exists:items,id'],
            'line_item' => ['nullable', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'delivery_instruction_date' => ['nullable', 'date'],
            'picking_date' => ['nullable', 'date'],
            'do_number' => ['nullable', 'max:255'],
            'delivery_date' => ['nullable', 'date'],
        ], [
            'item_id.exists' => 'Barang tidak ditemukan',
        ]);

        try {
            $stagingOut->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
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
    public function destroy(StagingOut $stagingOut)
    {
        try {
            $stagingOut->delete();

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
     * Remove multiple resources at once.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_outs,id',
        ]);

        try {
            $count = StagingOut::whereIn('id', $request->ids)->count();

            StagingOut::whereIn('id', $request->ids)->delete();
 
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