<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    public function index()
    {
        return view('location');
    }

    /**
     * Server-side DataTables source.
     */
    public function data(Request $request)
    {
        $query = Location::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('location_code', function ($row) {
                if (empty($row->location_code)) {
                    return '<span class="badge border border-warning text-warning bg-transparent" style="font-size:10px;">⚠ No Code</span>';
                }

                return '
                <div class="d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="fs-4" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5"
                            d="M6 22H4.4A2.4 2.4 0 0 1 2 19.6V18m16 4h1.6a2.4 2.4 0 0 0 2.4-2.4V18m0-12V4.4A2.4 2.4 0 0 0 19.6 2H18M6 2H4.4A2.4 2.4 0 0 0 2 4.4V6m16 3v6m-4-6v6m-4-6v6M6 9v6"/>
                    </svg>
                    <span>'.e($row->location_code).'</span>
                </div>';
            })

            ->editColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="badge bg-label-success rounded-pill" data-search="Aktif">Aktif</span>';
                }

                return '<span class="badge bg-label-secondary rounded-pill" data-search="Non Aktif">Non Aktif</span>';
            })

            ->editColumn('description', function ($row) {
                return $row->description ?: '-';
            })

            ->addColumn('action', function ($row) {

                return '
            <div class="d-flex align-items-center gap-1">

                <button
                    class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
                    type="button"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#editLocationModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor"
                            d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
                    </svg>

                </button>

                <form action="'.route('locations.destroy', $row->id).'"
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
                'location_code',
                'status',
                'action',
            ])

            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|max:255',
            'location_code' => 'nullable|unique:locations',
            'description' => 'nullable',
            'status' => 'required|boolean',
        ], [
            'location_name.required' => 'Nama lokasi wajib diisi',
            'location_code.unique' => 'Kode lokasi sudah digunakan',
            'status.required' => 'Status wajib dipilih',
        ]);

        try {
            Location::create($validated);

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
    public function edit(Location $location)
    {
        return response()->json([
            'id' => $location->id,
            'location_name' => $location->location_name,
            'location_code' => $location->location_code,
            'status' => (int) $location->status,
            'description' => $location->description,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location_name' => ['required', 'max:255'],
            'location_code' => [
                'nullable',
                Rule::unique('locations', 'location_code')
                    ->ignore($location->id),
            ],
            'description' => ['nullable'],
            'status' => ['required', 'boolean'],
        ], [
            'location_code.unique' => 'Kode lokasi sudah digunakan',
        ]);

        try {
            $location->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi rak berhasil diperbarui.',
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
    public function destroy(Location $location)
    {
        try {
            $location->delete();

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
            'ids.*' => 'integer|exists:locations,id',
        ]);

        try {
            $count = Location::whereIn('id', $request->ids)->count();

            Location::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => $count.' lokasi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}