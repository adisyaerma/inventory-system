<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->get();

        return view('vendor', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ], [
            'name.required' => 'Nama vendor wajib diisi',
        ]);

        Vendor::create($validated);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Data berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'description' => ['nullable'],
        ], [
            'name.required' => 'Nama vendor wajib diisi',
        ]);

        $vendor->update($validated);

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->back()
            ->with('deleted', 'Data berhasil dihapus');
    }
}