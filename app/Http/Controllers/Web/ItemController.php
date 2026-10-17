<?php

namespace App\Http\Controllers\Web;

use App\Exports\ItemsExport;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->get();

        $incoterms = Item::INCOTERMS;

        return view('item', compact('vendors', 'incoterms'));
    }

    public function data(Request $request)
    {
        $query = Item::with('vendor')
            ->orderByDesc('id');

        // filter vendor + incoterms (logika sama dengan export)
        $query->applyFilters($request->only(['vendor_id', 'incoterms']));

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('item_code_internal', fn ($row) => $row->item_code_internal ?: '-')
            ->editColumn('item_code_supplier', fn ($row) => $row->item_code_supplier ?: '-')
            ->editColumn('name', fn ($row) => $row->name ?: '-')
            ->editColumn('vendor', fn ($row) => $row->vendor ? $row->vendor->name : '-')
            ->editColumn('incoterms', fn ($row) => $row->incoterms ?: '-')
            ->editColumn('description', fn ($row) => $row->description ?: '-')

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

            ->addColumn('action', function ($row) {

                return '
<div class="d-flex align-items-center gap-2">

    <button
        class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEditItem"
        type="button"
        data-id="'.$row->id.'">

        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path fill="currentColor"
                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
        </svg>

    </button>

    <form action="'.route('items.destroy', $row->id).'"
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
                'action',
            ])

            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([

            'item_code_internal' => 'required|string|max:255|unique:items,item_code_internal',
            'item_code_supplier' => 'nullable|string|max:255',
            'item_code_customer' => 'nullable|string|max:255',

            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
            'incoterms' => ['nullable', Rule::in(Item::INCOTERMS)],

        ], [

            'item_code_internal.required' => 'Kode internal wajib diisi.',
            'item_code_internal.unique' => 'Barang sudah ada.',

            'name.required' => 'Nama barang wajib diisi.',

            'vendor_id.exists' => 'Vendor tidak ditemukan.',
            'incoterms.in' => 'Incoterms tidak valid.',

        ]);

        Item::create([

            'item_code_internal' => $request->item_code_internal,
            'item_code_supplier' => $request->item_code_supplier,
            'item_code_customer' => $request->item_code_customer,

            'name' => $request->name,
            'description' => $request->description,
            'vendor_id' => $request->vendor_id,
            'incoterms' => $request->incoterms,

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan.',
        ]);
    }

    public function edit(Item $item)
    {
        return response()->json([
            'item' => $item,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([

            'item_code_internal' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'item_code_internal')->ignore($item->id),
            ],
            'item_code_supplier' => 'nullable|string|max:255',
            'item_code_customer' => 'nullable|string|max:255',

            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
            'incoterms' => ['nullable', Rule::in(Item::INCOTERMS)],

        ], [

            'item_code_internal.required' => 'Kode internal wajib diisi.',
            'item_code_internal.unique' => 'Barang sudah ada.',

            'name.required' => 'Nama barang wajib diisi.',

            'vendor_id.exists' => 'Vendor tidak ditemukan.',
            'incoterms.in' => 'Incoterms tidak valid.',

        ]);

        $item->update([

            'item_code_internal' => $request->item_code_internal,
            'item_code_supplier' => $request->item_code_supplier,
            'item_code_customer' => $request->item_code_customer,

            'name' => $request->name,
            'description' => $request->description,
            'vendor_id' => $request->vendor_id,
            'incoterms' => $request->incoterms,

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
        ]);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus.',
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:items,id',
        ]);

        $count = Item::whereIn('id', $request->ids)->count();

        Item::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count.' barang berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        // filter yang sama persis dengan yang dipakai tabel (+ kolom pencarian)
        $filters = $request->only(['vendor_id', 'incoterms', 'search']);

        $fileName = 'barang_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new ItemsExport($filters), $fileName);
    }

    public function mutations(Item $item)
    {
        $mutations = $item->mutations()
            ->oldest('transaction_date')
            ->get();

        return response()->json($mutations);
    }
}