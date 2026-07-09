<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::latest()->get();

        return view('location', compact('locations'));
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
            'location_code.nullable' => 'Kode lokasi wajib diisi',
            'location_code.unique' => 'Kode lokasi sudah digunakan',
            'status.required' => 'Status wajib dipilih',
        ], );

        Location::create($validated);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Data berhasil disimpan');
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

        $location->update($validated);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Lokasi rak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->back()
            ->with('deleted', 'Data berhasil dihapus');
    }
}
