<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingInExport;
use App\Exports\StagingInTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StagingInImport;
use App\Models\StagingIn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
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

        return view('staging_in', compact(
            'totalEntry',
            'totalQty',
            'inboundShipment',
            'holdRepair'
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
        $query = StagingIn::query()->orderByDesc('id');

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('arrival_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('arrival_date', '<=', $request->end_date);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->addColumn('item', function ($row) {
                return '
                <small class="fw-bold">'.e($row->item_code).'</small>
                <div class="text-muted">'.e($row->item_name).'</div>
            ';
            })

            ->filterColumn('item', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('item_name', 'like', "%{$keyword}%")
                        ->orWhere('item_code', 'like', "%{$keyword}%");
                });
            })

            ->orderColumn('item', 'item_name $1')

            ->filterColumn('supplier_origin', function ($query, $keyword) {
                $query->where('supplier_origin', 'like', "%{$keyword}%");
            })

            ->orderColumn('supplier_origin', 'supplier_origin $1')

            ->filterColumn('item_owner', function ($query, $keyword) {
                $query->where('item_owner', 'like', "%{$keyword}%");
            })

            ->orderColumn('item_owner', 'item_owner $1')

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
            <div class="d-flex align-items-center gap-1">

                <button
                    class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
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
                'arrival_date',   // <-- tambahkan ini
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
        return Excel::download(new StagingInExport($request), 'staging.xlsx');
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
            'item_owner' => ['nullable', 'max:255'],
            'item_code' => ['nullable', 'max:255'],
            'item_name' => ['required', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', Rule::in(StagingIn::LOCATIONS)],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],
            'notes' => ['nullable'],
            'status' => ['nullable'],
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
        return response()->json([
            'id' => $staging->id,
            'po_number' => $staging->po_number,
            'arrival_date' => optional($staging->arrival_date)->format('Y-m-d'),
            'supplier_origin' => $staging->supplier_origin,
            'item_owner' => $staging->item_owner,
            'item_code' => $staging->item_code,
            'item_name' => $staging->item_name,
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
            'item_owner' => ['nullable', 'max:255'],
            'item_code' => ['nullable', 'max:255'],
            'item_name' => ['required', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', Rule::in(StagingIn::LOCATIONS)],
            'notes' => ['nullable'],
            'status' => ['nullable'],
            'incoterms' => ['nullable', Rule::in(StagingIn::INCOTERMS)],

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
