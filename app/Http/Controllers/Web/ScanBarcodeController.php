<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class ScanBarcodeController extends Controller
{
    public function index()
    {
        return view('scan_location');
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required',
        ]);

        $keyword = $request->keyword;

        $location = Location::with('stocks')
            ->where(function ($query) use ($keyword) {
                $query->where('location_code', $keyword)
                      ->orWhere('location_name', 'like', '%' . $keyword . '%');
            })
            ->first();

        if (! $location) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan',
            ]);
        }

        $stocks = $location->stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'item_code_internal' => $stock->item_code_internal,
                'item_code_supplier' => $stock->item_code_supplier,
                'item_code_customer' => $stock->item_code_customer,
                'name' => $stock->name,
                'description' => $stock->description,
                'quantity' => $stock->pivot->quantity,
            ];
        });

        return response()->json([
            'success' => true,
            'location' => [
                'location_name' => $location->location_name,
                'location_code' => $location->location_code,
                'status' => $location->status,
            ],
            'total_item' => $stocks->count(),
            'total_qty' => $stocks->sum('quantity'),
            'stocks' => $stocks->values(),
        ]);
    }
}