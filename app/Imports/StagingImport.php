<?php

namespace App\Imports;

use App\Models\Staging;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StagingImport implements ToCollection, WithHeadingRow
{
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

    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(
                Date::excelToDateTimeObject($value)
            );
        }

        $value = trim($value);

        if ($value === '' || $value === '-') {
            return null;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            // Tanggal tidak bisa dibaca -> simpan sebagai kosong, jangan gagalkan baris.
            return null;
        }
    }

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

    /**
     * Match the location cell against the known list of locations,
     * tolerant of case and surrounding whitespace. Returns the canonical
     * value (as defined in Staging::LOCATIONS), or null if it's empty or
     * doesn't match anything — an unmatched location is saved as null
     * rather than rejecting the row.
     */
    private function resolveLocation(?string $location): ?string
    {
        if ($location === null) {
            return null;
        }

        foreach (Staging::LOCATIONS as $valid) {
            if (strcasecmp($valid, $location) === 0) {
                return $valid;
            }
        }

        return null;
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            $poNumber = $this->cleanValue($row['no_po'] ?? null);
            $itemCode = $this->cleanValue($row['kode_barang'] ?? null);
            $itemName = $this->cleanValue($row['nama_barang'] ?? null);
            $supplierOrigin = $this->cleanValue($row['supplier'] ?? null);
            $itemOwner = $this->cleanValue($row['owner'] ?? null);
            $notes = $this->cleanValue($row['keterangan'] ?? null);
            $rawLocation = $this->cleanValue($row['lokasi'] ?? null);
            $qty = $this->parseQty($row['qty'] ?? null);
            $arrivalDate = $this->parseDate($row['tanggal_kedatangan'] ?? null);
            $location = $this->resolveLocation($rawLocation);

            if ($poNumber === null && $itemCode === null && $itemName === null
                && $supplierOrigin === null && $itemOwner === null && $rawLocation === null
                && $notes === null && $arrivalDate === null && $qty === 0) {
                continue;
            }

            try {

                Staging::create([
                    'po_number' => $poNumber,
                    'arrival_date' => $arrivalDate,
                    'supplier_origin' => $supplierOrigin,
                    'item_owner' => $itemOwner,
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'qty' => $qty,
                    'location' => $location,
                    'notes' => $notes,
                ]);

                $this->imported++;

            } catch (\Throwable $e) {

                $this->errors[] =
                    "Baris Excel {$excelRow} gagal.\n".
                    'No. PO      : '.($poNumber ?? '-')."\n".
                    'Kode Barang : '.($itemCode ?? '-')."\n".
                    'Nama Barang : '.($itemName ?? '-')."\n".
                    'Lokasi      : '.($rawLocation ?? '-')."\n".
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