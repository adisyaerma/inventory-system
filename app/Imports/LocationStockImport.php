<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LocationStockImport implements ToCollection, WithHeadingRow
{
    /**
     * Human-readable messages for rows that failed to import.
     */
    public array $errors = [];

    /**
     * Count of rows saved successfully.
     */
    public int $imported = 0;

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            try {

                if (empty($row['item_code_internal']) || empty($row['name'])) {
                    throw new \Exception('Kode barang internal dan nama barang wajib diisi.');
                }

                $vendor = null;

                if (! empty($row['vendor'])) {
                    $vendorName = trim($row['vendor']);

                    $vendor = Vendor::whereRaw(
                        'LOWER(name) = ?',
                        [strtolower($vendorName)]
                    )->first();

                    if (! $vendor) {
                        $vendor = Vendor::create([
                            'name' => $vendorName,
                        ]);
                    }
                }

                $item = Item::create([
                    'vendor_id' => $vendor?->id,
                    'item_code_internal' => $row['item_code_internal'],
                    'item_code_supplier' => $row['item_code_supplier'] ?? null,
                    'item_code_customer' => $row['item_code_customer'] ?? null,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                ]);

                $locations = array_map(
                    'trim',
                    preg_split('/[\\/,]/', $row['location'] ?? '')
                );

                $quantities = array_map(
                    'trim',
                    preg_split('/[\\/,]/', $row['quantity'] ?? '')
                );

                if (count($locations) === 1 && $locations[0] === '') {
                    $locations = ['Unlocated'];
                }

                foreach ($locations as $i => $locationName) {

                    // Jika lokasi kosong atau "-" maka gunakan "Unlocated"
                    if ($locationName === '' || $locationName === '-') {
                        $locationName = 'Unlocated';
                    }

                    $location = Location::firstOrCreate(
                        ['location_name' => $locationName],
                        ['location_code' => null]
                    );

                    $qty = isset($quantities[$i])
                        ? (float) $quantities[$i]
                        : 0;

                    $item->locations()->attach($location->id, [
                        'opening_balance' => $qty,
                        'quantity' => $qty,
                    ]);
                }

                $this->imported++;

            } catch (\Throwable $e) {

                // Hanya baris ini yang gagal, baris lain tetap lanjut diproses.
                $this->errors[] =
                    "Baris Excel {$excelRow} gagal.\n".
                    'Vendor    : '.($row['vendor'] ?? '-')."\n".
                    'Item Code : '.($row['item_code_internal'] ?? '-')."\n".
                    'Nama      : '.($row['name'] ?? '-')."\n".
                    'Location  : '.($row['location'] ?? '-')."\n".
                    'Quantity  : '.($row['quantity'] ?? '-')."\n".
                    'Error     : '.$e->getMessage();

            }
        }

        // Baris yang valid tetap tersimpan meski ada baris lain yang gagal.
        if (! empty($this->errors)) {
            throw new \Exception(
                "Import selesai: {$this->imported} baris berhasil disimpan, "
                .count($this->errors).' baris gagal.'.
                "\n\n".implode("\n\n", $this->errors)
            );
        }
    }
}