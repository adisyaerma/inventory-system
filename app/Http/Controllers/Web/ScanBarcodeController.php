<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\StagingIn;
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

        $keyword = strtolower(trim($request->keyword));

        $location = Location::with('stocks.vendor')
            ->where(function ($query) use ($keyword) {
                $query->whereRaw('LOWER(location_code) = ?', [$keyword])
                      ->orWhereRaw('LOWER(location_name) LIKE ?', ['%' . $keyword . '%']);
            })
            ->first();

        if ($location) {
            $stocks = $location->stocks->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'item_code_internal' => $stock->item_code_internal,
                    'item_code_supplier' => $stock->item_code_supplier,
                    'item_code_customer' => $stock->item_code_customer,
                    'name' => $stock->name,
                    'description' => $stock->description,
                    'quantity' => $stock->pivot->quantity,
                    'vendor_name' => $stock->vendor->name ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'location',
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

        $stagingLocation = StagingIn::whereRaw('LOWER(location) = ?', [$keyword])
            ->orWhereRaw('LOWER(location) LIKE ?', ['%' . $keyword . '%'])
            ->value('location');

        if ($stagingLocation) {
            $stagings = StagingIn::whereRaw('LOWER(location) = ?', [strtolower($stagingLocation)])
                ->orderByDesc('arrival_date')
                ->get();

            $stocks = $stagings->map(function ($staging) {
                return [
                    'id' => $staging->id,
                    'po_number' => $staging->po_number,
                    'arrival_date' => optional($staging->arrival_date)->format('Y-m-d') ?? $staging->arrival_date,
                    'supplier_origin' => $staging->supplier_origin,
                    'item_owner' => $staging->item_owner,
                    'item_code' => $staging->item_code,
                    'name' => $staging->item_name,
                    'quantity' => $staging->qty,
                    'notes' => $staging->notes,
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'staging',
                'location' => [
                    'location_name' => $stagingLocation,
                    'location_code' => null,
                    'status' => true,
                ],
                'total_item' => $stocks->count(),
                'total_qty' => $stocks->sum('quantity'),
                'stocks' => $stocks->values(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lokasi tidak ditemukan',
        ]);
    }
}