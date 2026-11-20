<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PackingBillLine;
use App\Models\PackingMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackingMaterialController extends Controller
{
    public function index()
    {
        return view('packing_materials', [
            'materials' => PackingMaterial::orderByDesc('aktif')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        PackingMaterial::create($this->validated($request));

        return response()->json(['success' => true, 'message' => 'Material berhasil ditambahkan.']);
    }

    public function update(Request $request, PackingMaterial $packingMaterial)
    {
        $packingMaterial->update($this->validated($request, $packingMaterial));

        return response()->json(['success' => true, 'message' => 'Material berhasil diperbarui.']);
    }

    /**
     * Material yang sudah pernah dipakai di bill tidak dihapus (supaya riwayat
     * tetap utuh), cukup dinonaktifkan.
     */
    public function destroy(PackingMaterial $packingMaterial)
    {
        if (PackingBillLine::where('packing_material_id', $packingMaterial->id)->exists()) {
            $packingMaterial->update(['aktif' => false]);

            return response()->json([
                'success' => true,
                'deactivated' => true,
                'message' => 'Material sudah pernah dipakai di bill, jadi dinonaktifkan (tidak dihapus).',
            ]);
        }

        $packingMaterial->delete();

        return response()->json(['success' => true, 'message' => 'Material berhasil dihapus.']);
    }

    protected function validated(Request $request, ?PackingMaterial $material = null): array
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:255', Rule::unique('packing_materials', 'kode')->ignore($material?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'satuan' => ['required', 'string', 'max:30'],
            'harga' => ['required', 'numeric', 'min:0'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'kode.required' => 'Kode wajib diisi.',
            'kode.unique' => 'Kode sudah dipakai.',
            'nama.required' => 'Nama material wajib diisi.',
            'satuan.required' => 'Satuan wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
        ]);

        $data['aktif'] = $request->boolean('aktif', true);

        return $data;
    }
}