<?php

namespace App\Imports;

use App\Imports\Concerns\ParsesExcelDates;
use App\Models\StagingOut;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StagingOutImport implements ToCollection, WithHeadingRow
{
    use ParsesExcelDates;

    /**
     * Human-readable messages for rows that failed to import
     * (only unexpected/database errors end up here now — missing fields
     * no longer block a row from being saved).
     */
    public array $errors = [];

    /**
     * Count of rows saved successfully.
     */
    public int $imported = 0;

    /**
     * Normalize a raw cell value into a clean string or null.
     *
     * Handles the messiness of data copy-pasted from other sheets:
     * - Trims stray whitespace.
     * - Reverts Excel's auto-numeric-conversion (e.g. a code "3195" that
     *   Excel silently turned into the float 3195.0) back to a plain string.
     * - Treats common "empty" placeholders ('-', '--', 'n/a', etc.) as null.
     */
    private function cleanValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            // 3195.0 -> "3195", 6690387432.0 -> "6690387432"
            $value = rtrim(rtrim(sprintf('%.4f', $value), '0'), '.');
        }

        $value = trim((string) $value);

        if ($value === '' || in_array(strtolower($value), ['-', '--', 'n/a', 'na', 'null', '#n/a'], true)) {
            return null;
        }

        return $value;
    }

    /**
     * Parse a qty cell into a non-negative integer. Tolerant of floats,
     * thousands separators, stray whitespace, and empty/placeholder values.
     * Empty/unparsable stays 0 (the column default), never blocks the row.
     */
    private function parseQty($value): int
    {
        $value = $this->cleanValue($value);

        if ($value === null) {
            return 0;
        }

        $value = str_replace(',', '', $value);

        if (! is_numeric($value)) {
            return 0;
        }

        return max(0, (int) round((float) $value));
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            $soNumber = $this->cleanValue($row['no_so'] ?? null);
            $customer = $this->cleanValue($row['customer'] ?? null);
            $itemCode = $this->cleanValue($row['kode_barang'] ?? null);
            $lineItem = $this->cleanValue($row['line_item'] ?? null);
            $doNumber = $this->cleanValue($row['no_do'] ?? null);
            $qty = $this->parseQty($row['qty'] ?? null);
            $deliveryInstructionDate = $this->parseDate($row['tgl_instruksi_kirim'] ?? null);
            $pickingDate = $this->parseDate($row['tgl_picking'] ?? null);
            $deliveryDate = $this->parseDate($row['tgl_kirim'] ?? null);

            if ($soNumber === null && $customer === null && $itemCode === null
                && $lineItem === null && $doNumber === null && $qty === 0
                && $deliveryInstructionDate === null && $pickingDate === null
                && $deliveryDate === null) {
                continue;
            }

            if ($doNumber === null){
                continue;
            }

            try {

                StagingOut::create([
                    'so_number' => $soNumber,
                    'customer' => $customer,
                    'item_code' => $itemCode,
                    'line_item' => $lineItem,
                    'qty' => $qty,
                    'delivery_instruction_date' => $deliveryInstructionDate,
                    'picking_date' => $pickingDate,
                    'do_number' => $doNumber,
                    'delivery_date' => $deliveryDate,
                ]);

                $this->imported++;

            } catch (\Throwable $e) {

                $this->errors[] =
                    "Baris Excel {$excelRow} gagal.\n".
                    'No. SO      : '.($soNumber ?? '-')."\n".
                    'Customer    : '.($customer ?? '-')."\n".
                    'Kode Barang : '.($itemCode ?? '-')."\n".
                    'Line Item   : '.($lineItem ?? '-')."\n".
                    'No. DO      : '.($doNumber ?? '-')."\n".
                    'Qty         : '.$qty."\n".
                    'Error       : '.$e->getMessage();

            }
        }

        if (! empty($this->errors)) {
            throw new \Exception(
                "Import selesai: {$this->imported} baris berhasil disimpan, ".count($this->errors).' baris gagal.'.
                "\n\n".implode("\n\n", $this->errors)
            );
        }
    }
}
