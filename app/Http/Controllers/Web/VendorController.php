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
            'name' => [
                'required',
                'max:255',
                Rule::unique('vendors')->where(
                    fn ($query) => $query->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
                ),
            ],
            'description' => ['nullable'],
        ], [
            'name.required' => 'Nama vendor wajib diisi',
            'name.unique' => 'Vendor sudah ada',
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
            'name' => [
                'required',
                'max:255',
                Rule::unique('vendors')->where(
                    fn ($query) => $query->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
                )->ignore($vendor->id),
            ],
            'description' => ['nullable'],
        ], [
        'name.required' => 'Nama vendor wajib diisi',
        'name.unique' => 'Vendor sudah ada',
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
