<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Item;
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
     * Hanya di-update setelah sebuah baris BERHASIL disimpan ke database,
     * supaya baris yang gagal tidak merusak perhitungan baris berikutnya.
     */
    private array $runningBalances = [];

    /**
     * Human-readable messages for rows that failed to import.
     */
    public array $errors = [];

    /**
     * Count of rows saved successfully.
     */
    public int $imported = 0;

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
        $this->errors = [];
        $this->imported = 0;

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

        // Simpan urutan baris asli & nomor baris Excel sebagai tie-breaker
        // dan untuk pelaporan error.
        $rows = $rows->values()->map(function ($row, $index) {
            $row['__original_index'] = $index;
            $row['__excel_row'] = $index + 3; // +1 header sheet, +1 baris di-skip, +1 basis 1
            return $row;
        });

        // PENTING: kelompokkan per item lebih dulu, baru urutkan tanggal DI DALAM
        // masing-masing grup. Ini menjamin running balance per item selalu dihitung
        // dalam urutan kronologis yang benar, terlepas dari urutan baris item lain
        // yang tercampur di file Excel.
        $groupedByItem = $rows->groupBy(fn ($row) => trim($row[0] ?? ''));

        foreach ($groupedByItem as $itemCode => $itemRows) {
            if (empty($itemCode)) {
                continue;
            }

            // Sort manual: tanggal dulu (ascending), kalau tanggal sama persis
            // baru fallback ke urutan asli di Excel.
            $sortedRows = $itemRows->sort(function ($a, $b) {
                $tsA = $this->parseDate($a[2])?->timestamp ?? 0;
                $tsB = $this->parseDate($b[2])?->timestamp ?? 0;

                return $tsA <=> $tsB ?: $a['__original_index'] <=> $b['__original_index'];
            })->values();

            foreach ($sortedRows as $row) {
                $this->processRow($row);
            }
        }

        // Baris yang valid tetap tersimpan meski ada baris lain yang gagal.
        if (! empty($this->errors)) {
            throw new \Exception(
                "Import selesai: {$this->imported} baris berhasil disimpan, ".count($this->errors).' baris gagal.'.
                "\n\n".implode("\n\n", $this->errors)
            );
        }
    }

    /**
     * Proses satu baris mutasi. Dibungkus transaksi per-baris supaya jika
     * baris ini gagal, hanya baris ini yang di-rollback — baris lain yang
     * sudah berhasil sebelumnya tetap tersimpan.
     */
    private function processRow($row): void
    {
        if (empty($row[0])) {
            return;
        }

        $itemCode = trim($row[0]);
        $itemName = trim($row[1] ?? '');
        $transactionDate = $row[2] ?? null;
        $transactionNumber = $row[3] ?? null;
        $qtyIn = (float) ($row[5] ?? 0);
        $qtyOut = (float) ($row[6] ?? 0);

        try {

            DB::transaction(function () use (
                $row,
                $itemCode,
                $itemName,
                $transactionDate,
                $transactionNumber,
                $qtyIn,
                $qtyOut
            ) {
                $item = Item::firstOrCreate(
                    ['item_code_internal' => $itemCode],
                    ['name' => $itemName]
                );

                $locationStock = LocationStock::where('item_id', $item->id)
                    ->orderByDesc('quantity')
                    ->first();

                if (! $locationStock) {
                    $unknownLocation = Location::firstOrCreate([
                        'location_name' => '-',
                    ]);

                    $locationStock = LocationStock::firstOrCreate(
                        [
                            'item_id' => $item->id,
                            'location_id' => $unknownLocation->id,
                        ],
                        [
                            'quantity' => 0,
                            'opening_balance' => 0,
                        ]
                    );
                }

                if (! array_key_exists($item->id, $this->runningBalances)) {
                    $this->runningBalances[$item->id] = (float) ($locationStock->opening_balance ?? 0);
                }

                $qtyBalanceExcel = (float) ($row[7] ?? 0);
                $computedBalance = $this->runningBalances[$item->id] + $qtyIn - $qtyOut;

                if (abs($computedBalance - $qtyBalanceExcel) > self::TOLERANCE) {
                    Log::warning('Selisih stok terdeteksi saat import mutasi', [
                        'item_code' => $item->item_code_internal,
                        'transaction_number' => $transactionNumber,
                        'transaction_date' => $transactionDate,
                        'computed_balance' => $computedBalance,
                        'excel_balance' => $qtyBalanceExcel,
                        'selisih' => round($computedBalance - $qtyBalanceExcel, 2),
                    ]);
                }

                StockMutation::create([
                    'item_id' => $item->id,
                    'location_id' => $locationStock->location_id,
                    'transaction_date' => $this->parseDate($transactionDate),
                    'transaction_number' => $transactionNumber,
                    'description' => $row[4] ?? null,
                    'qty_in' => $qtyIn,
                    'qty_out' => $qtyOut,
                    'qty_balance' => $computedBalance,
                ]);

                $locationStock->update([
                    'quantity' => $computedBalance,
                ]);

                // Baris berhasil disimpan -> baru sekarang running balance di-commit.
                $this->runningBalances[$item->id] = $computedBalance;
            });

            $this->imported++;

        } catch (\Throwable $e) {

            $this->errors[] =
                "Baris Excel {$row['__excel_row']} gagal.\n".
                'Item Code         : '.($itemCode ?: '-')."\n".
                'Nama Barang       : '.($itemName ?: '-')."\n".
                'No. Transaksi     : '.($transactionNumber ?: '-')."\n".
                'Tanggal Transaksi : '.($transactionDate ?: '-')."\n".
                'Qty In            : '.$qtyIn."\n".
                'Qty Out           : '.$qtyOut."\n".
                'Error             : '.$e->getMessage();

        }
    }
}