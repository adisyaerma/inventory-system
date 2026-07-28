<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Stock;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StockMutationImport implements ToCollection
{
    /**
     * Toleransi selisih (floating point) sebelum dianggap mismatch.
     */
    private const TOLERANCE = 0.01;

    /**
     * Menyimpan running balance per stock_id selama proses import berjalan.
     */
    private array $runningBalances = [];

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
        $lastTransactionNumber = null;

        $rows = $rows->map(function ($row) use (
            &$lastItemCode,
            &$lastItemName,
            &$lastDate,
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
                $lastTransactionNumber = trim($row[3]);
            } else {
                $row[3] = $lastTransactionNumber;
            }

            return $row;
        });

        $rows = $rows->filter(function ($row) {
            return ! str_contains(
                strtolower($row[4] ?? ''),
                'item balance'
            );
        });

        // Simpan urutan baris asli sebagai tie-breaker (kalau ada tanggal yang sama persis).
        $rows = $rows->values()->map(function ($row, $index) {
            $row['__original_index'] = $index;

            return $row;
        });

        // PENTING: kelompokkan per item lebih dulu, baru urutkan tanggal DI DALAM
        // masing-masing grup. Ini menjamin running balance per item selalu dihitung
        // dalam urutan kronologis yang benar, terlepas dari urutan baris item lain
        // yang tercampur di file Excel.
        //
        // (Sebelumnya kode ini melakukan sort tanggal secara global memakai
        // ->sortBy([$closure]) — bentuk array itu memicu jalur multi-column sort
        // Laravel yang ternyata TIDAK menghasilkan urutan kronologis yang benar,
        // sehingga qty_balance per baris ikut salah walau quantity akhir tetap
        // benar secara kebetulan karena penjumlahan bersifat komutatif.)
        $groupedByItem = $rows->groupBy(fn ($row) => trim($row[0] ?? ''));

        DB::transaction(function () use ($groupedByItem) {
            foreach ($groupedByItem as $itemCode => $itemRows) {
                if (empty($itemCode)) {
                    continue;
                }

                // Sort manual pakai usort-style comparator: tanggal dulu (ascending),
                // kalau tanggal sama persis baru fallback ke urutan asli di Excel.
                // Ini cara paling eksplisit dan aman untuk multi-key sort di Laravel Collection.
                $sortedRows = $itemRows->sort(function ($a, $b) {
                    $tsA = $this->parseDate($a[2])?->timestamp ?? 0;
                    $tsB = $this->parseDate($b[2])?->timestamp ?? 0;

                    return $tsA <=> $tsB ?: $a['__original_index'] <=> $b['__original_index'];
                })->values();

                foreach ($sortedRows as $row) {
                    $this->processRow($row);
                }
            }
        });
    }

    private function processRow($row): void
    {
        if (empty($row[0])) {
            return;
        }

        $stock = Stock::firstOrCreate(
            ['item_code_internal' => trim($row[0])],
            ['name' => trim($row[1] ?? '')]
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
                    'opening_balance' => 0,
                ]
            );
        }

        // Inisialisasi running balance dari opening_balance HANYA sekali,
        // saat stock ini pertama kali muncul dalam proses import.
        if (! array_key_exists($stock->id, $this->runningBalances)) {
            $this->runningBalances[$stock->id] = (float) ($locationStock->opening_balance ?? 0);
        }

        $qtyIn = (float) ($row[5] ?? 0);
        $qtyOut = (float) ($row[6] ?? 0);
        $qtyBalanceExcel = (float) ($row[7] ?? 0);

        // Hitung balance berjalan: opening_balance + akumulasi (in - out)
        $this->runningBalances[$stock->id] += $qtyIn - $qtyOut;
        $computedBalance = $this->runningBalances[$stock->id];

        // Validasi terhadap balance yang tertulis di Excel (tidak menghentikan proses,
        // hanya dicatat supaya ketahuan kalau ada selisih / data tidak konsisten).
        if (abs($computedBalance - $qtyBalanceExcel) > self::TOLERANCE) {
            Log::warning('Selisih stok terdeteksi saat import mutasi', [
                'item_code' => $stock->item_code_internal,
                'transaction_number' => $row[3] ?? null,
                'transaction_date' => $row[2] ?? null,
                'computed_balance' => $computedBalance,
                'excel_balance' => $qtyBalanceExcel,
                'selisih' => round($computedBalance - $qtyBalanceExcel, 2),
            ]);
        }

        StockMutation::create([
            'stock_id' => $stock->id,
            'location_id' => $locationStock->location_id,
            'transaction_date' => $this->parseDate($row[2]),
            'transaction_number' => $row[3] ?? null,
            'description' => $row[4] ?? null,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'qty_balance' => $computedBalance,
        ]);

        $locationStock->update([
            'quantity' => $computedBalance,
        ]);
    }
}