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

/**
 * Kolom Excel (lihat StockMutationTemplateExport):
 *
 * 0 Kode Barang
 * 1 Nama Barang
 * 2 Lokasi
 * 3 Lot
 * 4 Tanggal
 * 5 Nomor
 * 6 Deskripsi
 * 7 Qty Masuk
 * 8 Qty Keluar
 * 9 Qty Balance
 *
 * Kolom Lokasi bisa ditulis dalam kode gudang, mis. "R8P07L2", dan akan
 * dikonversi otomatis menjadi format location_name di database, yaitu
 * "8-7-2" (rak-posisi-level). Kalau nilainya sudah dalam format lain
 * (tidak cocok pola R{rak}P{posisi}L{level}), dipakai apa adanya.
 */
class StockMutationImport implements ToCollection
{
    /**
     * Toleransi selisih (floating point) sebelum dianggap mismatch.
     */
    private const TOLERANCE = 0.01;

    /**
     * Menyimpan running balance per kombinasi item + lokasi + lot selama
     * proses import berjalan. Hanya di-update setelah sebuah baris
     * BERHASIL disimpan ke database, supaya baris yang gagal tidak
     * merusak perhitungan baris berikutnya.
     *
     * Key: "{item_id}|{location_id}|{lot}"
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

    /**
     * Normalisasi nilai lot mentah dari Excel menjadi string trimmed
     * atau null (kalau kosong). Dipakai supaya "" dan null selalu
     * diperlakukan sama di seluruh proses import.
     */
    private function normalizeLot($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Konversi kode lokasi gudang mentah, mis. "R8P07L2", menjadi format
     * location_name yang dipakai di tabel locations, mis. "8-7-2"
     * (rak-posisi-level). Angka posisi dilucuti leading zero-nya
     * ("07" -> "7"). Kalau string tidak cocok pola R{rak}P{posisi}L{level}
     * (misalnya sudah dalam format "8-7-2", atau nama lokasi bebas lain),
     * nilainya dikembalikan apa adanya setelah di-trim.
     */
    private function normalizeLocationCode(string $raw): string
    {
        $raw = trim($raw);

        if (preg_match('/^R(\d+)P(\d+)L(\d+)$/i', $raw, $m)) {
            return $m[1].'-'.((int) $m[2]).'-'.$m[3];
        }

        return $raw;
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        $rows = $rows->skip(1);

        $lastItemCode = null;
        $lastItemName = null;
        $lastLocation = null;
        $lastLot = '__UNSET__'; // sentinel: baris pertama tanpa lot tetap null, bukan warisan yang salah
        $lastDate = null;
        $lastTransactionNumber = null;

        $rows = $rows->map(function ($row) use (
            &$lastItemCode,
            &$lastItemName,
            &$lastLocation,
            &$lastLot,
            &$lastDate,
            &$lastTransactionNumber
        ) {
            // Baris item baru (kode barang terisi) mereset konteks lot,
            // supaya lot barang sebelumnya tidak "bocor" ke barang lain.
            if (! empty(trim($row[0] ?? ''))) {
                $lastItemCode = trim($row[0]);
                $lastLot = '__UNSET__';
            } else {
                $row[0] = $lastItemCode;
            }

            if (! empty(trim($row[1] ?? ''))) {
                $lastItemName = trim($row[1]);
            } else {
                $row[1] = $lastItemName;
            }

            if (! empty(trim($row[2] ?? ''))) {
                $lastLocation = trim($row[2]);
            } else {
                $row[2] = $lastLocation;
            }

            // Lot boleh betul-betul kosong (barang tanpa lot). Kolom
            // hanya diwariskan dari baris di atasnya kalau baris ini
            // adalah baris lanjutan hasil merge (kode barang & lokasi
            // sama-sama kosong).
            $rowHasOwnContext =
                ! empty(trim($row[0] ?? '')) ||
                ! empty(trim($row[2] ?? ''));

            if ($lastLot === '__UNSET__' || $rowHasOwnContext) {
                $lastLot = $this->normalizeLot($row[3] ?? null);
            }

            $row[3] = $lastLot;

            if (! empty($row[4])) {
                $lastDate = $row[4];
            } else {
                $row[4] = $lastDate;
            }

            if (! empty(trim($row[5] ?? ''))) {
                $lastTransactionNumber = trim($row[5]);
            } else {
                $row[5] = $lastTransactionNumber;
            }

            return $row;
        });

        $rows = $rows->filter(function ($row) {
            return ! str_contains(
                strtolower($row[6] ?? ''),
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

        // PENTING: kelompokkan per kombinasi item + lokasi + lot lebih
        // dulu, baru urutkan tanggal DI DALAM masing-masing grup. Ini
        // menjamin running balance selalu dihitung dalam urutan
        // kronologis yang benar per kombinasi item+lokasi+lot, terlepas
        // dari urutan baris lain yang tercampur di file Excel. Kalau
        // item yang sama muncul di lokasi/lot berbeda, saldonya tetap
        // dihitung terpisah dan tidak saling mempengaruhi.
        //
        // Lokasi dinormalisasi (mis. "R8P07L2" -> "8-7-2") di titik ini
        // supaya baris yang menulis kode lokasi dalam bentuk berbeda
        // untuk lokasi yang sama tetap dikelompokkan bersama.
        $groupedByItemLocationLot = $rows->groupBy(function ($row) {
            $itemCode = trim($row[0] ?? '');
            $location = $this->normalizeLocationCode(trim($row[2] ?? ''));
            $lot = $this->normalizeLot($row[3] ?? null);

            return $itemCode.'|||'.$location.'|||'.($lot ?? '');
        });

        foreach ($groupedByItemLocationLot as $groupKey => $groupRows) {

            $itemCode = trim($groupRows->first()[0] ?? '');

            if (empty($itemCode)) {
                continue;
            }

            // Sort manual: tanggal dulu (ascending), kalau tanggal sama persis
            // baru fallback ke urutan asli di Excel.
            $sortedRows = $groupRows->sort(function ($a, $b) {
                $tsA = $this->parseDate($a[4])?->timestamp ?? 0;
                $tsB = $this->parseDate($b[4])?->timestamp ?? 0;

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
        $locationRaw = trim($row[2] ?? '');
        $locationName = $this->normalizeLocationCode($locationRaw);
        $lot = $this->normalizeLot($row[3] ?? null);
        $transactionDate = $row[4] ?? null;
        $transactionNumber = $row[5] ?? null;
        $qtyIn = (float) ($row[7] ?? 0);
        $qtyOut = (float) ($row[8] ?? 0);

        try {

            if ($locationName === '') {
                throw new \Exception('Lokasi wajib diisi.');
            }

            DB::transaction(function () use (
                $row,
                $itemCode,
                $itemName,
                $locationName,
                $lot,
                $transactionDate,
                $transactionNumber,
                $qtyIn,
                $qtyOut
            ) {
                $item = Item::firstOrCreate(
                    ['item_code_internal' => $itemCode],
                    ['name' => $itemName]
                );

                $location = Location::firstOrCreate([
                    'location_name' => $locationName,
                ]);

                // Baris ini secara eksplisit menyebutkan lokasi + lot,
                // jadi kita cari/buat baris location_stock yang PERSIS
                // untuk kombinasi item + lokasi + lot ini — tidak lagi
                // menebak lokasi mana pun yang stoknya paling banyak.
                $locationStock = LocationStock::firstOrCreate(
                    [
                        'item_id' => $item->id,
                        'location_id' => $location->id,
                        'lot' => $lot,
                    ],
                    [
                        'quantity' => 0,
                        'opening_balance' => 0,
                    ]
                );

                $balanceKey = $item->id.'|'.$location->id.'|'.($lot ?? '');

                // Saldo awal diambil dari quantity stok TERKINI (bukan
                // opening_balance yang statis), supaya import mutasi baru
                // melanjutkan dari sisa stok yang sudah ada saat ini,
                // bukan mengulang dari saldo awal setiap kali diimport.
                if (! array_key_exists($balanceKey, $this->runningBalances)) {
                    $this->runningBalances[$balanceKey] = (float) ($locationStock->quantity ?? 0);
                }

                $qtyBalanceExcel = (float) ($row[9] ?? 0);
                $computedBalance = $this->runningBalances[$balanceKey] + $qtyIn - $qtyOut;

                if (abs($computedBalance - $qtyBalanceExcel) > self::TOLERANCE) {
                    Log::warning('Selisih stok terdeteksi saat import mutasi', [
                        'item_code' => $item->item_code_internal,
                        'location' => $location->location_name,
                        'lot' => $lot,
                        'transaction_number' => $transactionNumber,
                        'transaction_date' => $transactionDate,
                        'computed_balance' => $computedBalance,
                        'excel_balance' => $qtyBalanceExcel,
                        'selisih' => round($computedBalance - $qtyBalanceExcel, 2),
                    ]);
                }

                StockMutation::create([
                    'item_id' => $item->id,
                    'location_id' => $location->id,
                    'lot' => $lot,
                    'transaction_date' => $this->parseDate($transactionDate),
                    'transaction_number' => $transactionNumber,
                    'description' => $row[6] ?? null,
                    'qty_in' => $qtyIn,
                    'qty_out' => $qtyOut,
                    'qty_balance' => $computedBalance,
                ]);

                $locationStock->update([
                    'quantity' => $computedBalance,
                ]);

                // Baris berhasil disimpan -> baru sekarang running balance di-commit.
                $this->runningBalances[$balanceKey] = $computedBalance;
            });

            $this->imported++;

        } catch (\Throwable $e) {

            $this->errors[] =
                "Baris Excel {$row['__excel_row']} gagal.\n".
                'Item Code         : '.($itemCode ?: '-')."\n".
                'Nama Barang       : '.($itemName ?: '-')."\n".
                'Lokasi            : '.($locationName ?: '-').' (mentah: '.($locationRaw ?: '-').")\n".
                'Lot               : '.($lot ?: '-')."\n".
                'No. Transaksi     : '.($transactionNumber ?: '-')."\n".
                'Tanggal Transaksi : '.($transactionDate ?: '-')."\n".
                'Qty In            : '.$qtyIn."\n".
                'Qty Out           : '.$qtyOut."\n".
                'Error             : '.$e->getMessage();

        }
    }
}