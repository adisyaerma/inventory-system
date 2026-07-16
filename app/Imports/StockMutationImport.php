<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Stock;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StockMutationImport implements ToCollection
{
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(
                Date::excelToDateTimeObject($value)
            );
        }

        $value = trim($value);

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value);
        }

        return Carbon::parse($value);
    }

    public function collection(Collection $rows)
    {
        $rows = $rows->skip(1);

        $lastItemCode = null;
        $lastItemName = null;
        $lastDate = null;
        $lastTransactionType = null;
        $lastTransactionNumber = null;

        $rows = $rows->map(function ($row) use (
            &$lastItemCode,
            &$lastItemName,
            &$lastDate,
            &$lastTransactionType,
            &$lastTransactionNumber
        ) {

            if (! empty(trim($row[0] ?? ''))) {
                $lastItemCode = trim($row[0]);
            } else {
                $row[0] = $lastItemCode;
            }

            if (! empty(trim($row[1] ?? ''))) {
                $lastItemName = trim($row[1]);
            } else {
                $row[1] = $lastItemName;
            }

            if (! empty($row[2])) {
                $lastDate = $row[2];
            } else {
                $row[2] = $lastDate;
            }

            if (! empty(trim($row[3] ?? ''))) {
                $lastTransactionType = trim($row[3]);
            } else {
                $row[3] = $lastTransactionType;
            }

            if (! empty(trim($row[4] ?? ''))) {
                $lastTransactionNumber = trim($row[4]);
            } else {
                $row[4] = $lastTransactionNumber;
            }

            return $row;
        });

        $rows = $rows->filter(function ($row) {
            return ! str_contains(
                strtolower($row[5] ?? ''),
                'item balance'
            );
        });

        $rows = $rows->sortBy(function ($row) {
            return $this->parseDate($row[2])?->timestamp ?? 0;
        });

        $defaultLocation = Location::orderBy('id')->first();

        foreach ($rows as $row) {

            if (empty($row[0])) {
                continue;
            }

            $stock = Stock::firstOrCreate(
                [
                    'item_code_internal' => trim($row[0]),
                ],
                [
                    'name' => trim($row[1] ?? ''),
                ]
            );

            $locationStock = LocationStock::where('stock_id', $stock->id)
                ->orderByDesc('quantity')
                ->first();

            if (! $locationStock) {

                $unknownLocation = Location::firstOrCreate([
                    'location_name' => '-',
                ]);

                $locationStock = LocationStock::firstOrCreate(
                    [
                        'stock_id' => $stock->id,
                        'location_id' => $unknownLocation->id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );
            }

            $qtyIn = (float) ($row[6] ?? 0);
            $qtyOut = (float) ($row[7] ?? 0);
            $qtyBalance = (float) ($row[8] ?? 0); 

            StockMutation::create([
                'stock_id' => $stock->id,
                'location_id' => $locationStock->location_id,
                'transaction_date' => $this->parseDate($row[2]),
                'transaction_type' => $row[3] ?? null,
                'transaction_number' => $row[4] ?? null,
                'description' => $row[5] ?? null,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'qty_balance' => $qtyBalance,
            ]);

            $locationStock->update([
                'quantity' => $qtyBalance,
            ]);
        }
    }
}
