<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\StagingIn;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\StagingInHistoryService;

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

        $location = Location::with('items.vendor')
            ->where(function ($query) use ($keyword) {
                $query->whereRaw('LOWER(location_code) = ?', [$keyword])
                      ->orWhereRaw('LOWER(location_name) LIKE ?', ['%' . $keyword . '%']);
            })
            ->first();

        if ($location) {
            $items = $location->items
                ->filter(fn ($item) => (float) $item->pivot->quantity > 0)
                ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_code_internal' => $item->item_code_internal,
                    'item_code_supplier' => $item->item_code_supplier,
                    'item_code_customer' => $item->item_code_customer,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->pivot->quantity,
                    'lot' => $item->pivot->lot ?? null,
                    'vendor_name' => $item->vendor->name ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'location',
                'location' => [
                    'id' => $location->id,
                    'location_name' => $location->location_name,
                    'location_code' => $location->location_code,
                    'status' => $location->status,
                ],
                'total_item' => $items->pluck('id')->unique()->count(),
                'total_qty' => $items->sum('quantity'),
                'items' => $items->values(),
            ]);
        }

        $stagingLocation = StagingIn::whereRaw('LOWER(location) = ?', [$keyword])
            ->orWhereRaw('LOWER(location) LIKE ?', ['%' . $keyword . '%'])
            ->value('location');

        if ($stagingLocation) {
            $stagings = StagingIn::whereRaw('LOWER(location) = ?', [strtolower($stagingLocation)])
                ->orderByDesc('arrival_date')
                ->get();

            $items = $stagings->map(function ($staging) {
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
                'total_item' => $items->count(),
                'total_qty' => $items->sum('quantity'),
                'items' => $items->values(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lokasi tidak ditemukan',
        ]);
    }

    /**
     * Daftar lokasi untuk dropdown tujuan (Tom Select) / pencarian hasil scan.
     * q = kata kunci, exact=1 = cocok persis kode/nama, exclude = id lokasi asal.
     */
    public function locationOptions(Request $request)
    {
        $q = strtolower(trim((string) $request->query('q', '')));

        $query = Location::query();

        if ($request->filled('exclude')) {
            $query->where('id', '!=', (int) $request->query('exclude'));
        }

        if ($request->boolean('exact')) {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(location_code) = ?', [$q])
                    ->orWhereRaw('LOWER(location_name) = ?', [$q]);
            });
        } elseif ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(location_code) LIKE ?', ['%'.$q.'%'])
                    ->orWhereRaw('LOWER(location_name) LIKE ?', ['%'.$q.'%']);
            });
        }

        return response()->json(
            $query->orderBy('location_code')
                ->limit(30)
                ->get(['id', 'location_code', 'location_name'])
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'location_code' => $l->location_code,
                    'location_name' => $l->location_name,
                    'label' => $l->location_name ?: $l->location_code,
                ])
                ->values()
        );
    }

    /**
     * Pindahkan stok satu barang (item + lot) dari satu lokasi ke lokasi lain.
     * location_stock sumber berkurang, tujuan bertambah, dan dicatat dua baris
     * stock_mutations (keluar di sumber, masuk di tujuan).
     */
    public function move(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'from_location_id' => 'required|integer|exists:locations,id',
            'lot' => 'nullable|string',
            'to_lot' => 'nullable|string|max:255',
            'qty' => 'required|numeric|gt:0',
            'to_location_id' => 'required_without:to_staging_in|nullable|integer|exists:locations,id',
            'to_staging_in' => 'nullable|boolean',
            'staging_location' => ['nullable', Rule::in(StagingIn::LOCATIONS)],
        ]);

        $lot = ($data['lot'] ?? '') !== '' ? $data['lot'] : null;

        // Lot tujuan: kalau field to_lot tidak dikirim, ikut lot asal (perilaku lama).
        // Kalau dikirim tapi kosong, berarti "Tanpa lot".
        $toLot = $request->has('to_lot')
            ? (trim((string) ($data['to_lot'] ?? '')) !== '' ? trim($data['to_lot']) : null)
            : $lot;
        $qty = round((float) $data['qty'], 2);

        // Tujuan Staging In: stok gudang berkurang, lalu dibuat baris baru di Staging In.
        if ($request->boolean('to_staging_in')) {
            return $this->moveToStagingIn($data, $lot, $qty, $data['staging_location'] ?? null);
        }

        $target = Location::findOrFail($data['to_location_id']);

        if ((int) $target->id === (int) $data['from_location_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tujuan sama dengan lokasi asal.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($data, $lot, $toLot, $qty, $target) {
                $from = LocationStock::where('item_id', $data['item_id'])
                    ->where('location_id', $data['from_location_id'])
                    ->where('lot', $lot)
                    ->lockForUpdate()
                    ->first();

                if (! $from || (float) $from->quantity < $qty) {
                    throw new \RuntimeException('Stok di lokasi asal tidak mencukupi.');
                }

                $to = LocationStock::where('item_id', $data['item_id'])
                    ->where('location_id', $target->id)
                    ->where('lot', $toLot)
                    ->lockForUpdate()
                    ->first();
    
                if (! $to) {
                    $to = LocationStock::create([
                        'item_id' => $data['item_id'],
                        'location_id' => $target->id,
                        'lot' => $toLot,
                        'opening_balance' => 0,
                        'quantity' => 0,
                    ]);
                }

                $fromLocation = Location::find($data['from_location_id']);

                $from->quantity = (float) $from->quantity - $qty;
                $from->save();

                $to->quantity = (float) $to->quantity + $qty;
                $to->save();

                $today = now()->toDateString();

                $lotNote = $lot !== $toLot
                    ? ' (lot '.($lot ?? 'tanpa lot').' → '.($toLot ?? 'tanpa lot').')'
                    : '';

                StockMutation::create([
                    'item_id' => $data['item_id'],
                    'location_id' => $from->location_id,
                    'lot' => $lot,
                    'transaction_date' => $today,
                    'transaction_number' => null,
                    'description' => 'Pindah lokasi ke '.$target->location_name.$lotNote,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'qty_balance' => $from->quantity,
                ]);

                StockMutation::create([
                    'item_id' => $data['item_id'],  
                    'location_id' => $target->id,
                    'lot' => $toLot,
                    'transaction_date' => $today,
                    'transaction_number' => null,
                    'description' => 'Pindah lokasi dari '.($fromLocation->location_name ?? '-').$lotNote,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'qty_balance' => $to->quantity,
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dipindahkan ke '.$target->location_name.'.',
        ]);
    }

    /**
     * Pindahkan stok gudang (item + lot) ke Staging In.
     * location_stock sumber berkurang, dicatat satu mutasi keluar, lalu dibuat
     * satu baris baru di staging_ins (lengkap dengan history Staging In).
     * Staging In tidak punya kolom lot, jadi lot asal dicatat di notes.
     */
    private function moveToStagingIn(array $data, ?string $lot, float $qty, ?string $stagingLocation)
    {
        if (floor($qty) != $qty) {
            return response()->json([
                'success' => false,
                'message' => 'Qty ke Staging In harus bilangan bulat.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($data, $lot, $qty, $stagingLocation) {
                $from = LocationStock::where('item_id', $data['item_id'])
                    ->where('location_id', $data['from_location_id'])
                    ->where('lot', $lot)
                    ->lockForUpdate()
                    ->first();

                if (! $from || (float) $from->quantity < $qty) {
                    throw new \RuntimeException('Stok di lokasi asal tidak mencukupi.');
                }

                $fromLocation = Location::find($data['from_location_id']);
                $fromName = $fromLocation->location_name ?? '-';
                $lotNote = $lot !== null ? ' (lot '.$lot.')' : '';

                $from->quantity = (float) $from->quantity - $qty;
                $from->save();

                StockMutation::create([
                    'item_id' => $data['item_id'],
                    'location_id' => $from->location_id,
                    'lot' => $lot,
                    'transaction_date' => now()->toDateString(),
                    'transaction_number' => null,
                    'description' => 'Pindah ke Staging In'.$lotNote,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'qty_balance' => $from->quantity,
                ]);

                $staging = StagingIn::create([
                    'item_id' => $data['item_id'],
                    'qty' => (int) $qty,
                    'arrival_date' => now()->toDateString(),
                    'location' => $stagingLocation ?: null,
                    'notes' => 'Dipindah dari '.$fromName.$lotNote,
                ]);

                app(StagingInHistoryService::class)->logCreated($staging);
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dipindahkan ke Staging In.',
        ]);
    }

    public function itemLocations(Request $request, Item $item)
    {
        $currentCode = strtolower(trim((string) $request->query('exclude')));

        $locations = LocationStock::with('location')
            ->where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->get()
            ->filter(fn ($row) => $row->location
                && strtolower($row->location->location_code) !== $currentCode)
            ->map(fn ($row) => [
                'location_name' => $row->location->location_name,
                'location_code' => $row->location->location_code,
                'lot' => $row->lot,
                'quantity' => (float) $row->quantity,
            ])
            ->values();

        return response()->json([
            'locations' => $locations,
            'total_qty' => $locations->sum('quantity'),
        ]);
    }
}