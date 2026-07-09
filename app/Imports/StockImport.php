<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            $excelRow = $index + 2; 

            try {

                $stock = Stock::create([
                    'item_code_internal' => $row['item_code_internal'],
                    'item_code_supplier' => $row['item_code_supplier'] ?? null,
                    'item_code_customer' => $row['item_code_customer'] ?? null,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                ]);

                $locations = array_map('trim', preg_split('/[\/,]/', $row['location'] ?? ''));
                $quantities = array_map('trim', preg_split('/[\/,]/', $row['quantity'] ?? ''));

                if (count($locations) === 1 && $locations[0] === '') {
                    $locations = ['-'];
                }

                foreach ($locations as $i => $locationName) {

                    if ($locationName === '') {
                        $locationName = '-';
                    }

                    $location = Location::firstOrCreate(
                        ['location_name' => $locationName],
                        ['location_code' => null]
                    );

                    $qty = isset($quantities[$i]) ? (float) $quantities[$i] : 0;

                    $stock->locations()->attach($location->id, [
                        'quantity' => $qty,
                    ]);
                }

            } catch (\Exception $e) {

                throw new \Exception(
                    "Baris Excel {$excelRow} gagal.\n".
                    'Item Code : '.$row['item_code_internal']."\n".
                    'Nama      : '.$row['name']."\n".
                    'Location  : '.$row['location']."\n".
                    'Quantity  : '.$row['quantity']."\n\n".
                    'Error Database : '.$e->getMessage()
                );

            }
        }
    }
}
