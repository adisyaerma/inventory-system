<?php

namespace App\Imports;

use App\Imports\Concerns\ParsesExcelDates;
use App\Models\Item;
use App\Models\StagingIn;
use App\Models\Vendor;
use App\Services\StagingInHistoryService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StagingInImport implements ToCollection, WithHeadingRow
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

    public function __construct(private StagingInHistoryService $history)
    {
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

        foreach (StagingIn::LOCATIONS as $valid) {
            if (strcasecmp($valid, $location) === 0) {
                return $valid;
            }
        }

        return null;
    }

    /**
     * Match the incoterms cell against the known list of incoterms,
     * tolerant of case and surrounding whitespace. Returns the canonical
     * value (as defined in Staging::INCOTERMS), or null if it's empty or
     * doesn't match anything — an unmatched incoterms is saved as null
     * rather than rejecting the row.
     */
    private function resolveIncoterms(?string $incoterms): ?string
    {
        if ($incoterms === null) {
            return null;
        }

        foreach (StagingIn::INCOTERMS as $valid) {
            if (strcasecmp($valid, $incoterms) === 0) {
                return $valid;
            }
        }

        return null;
    }

    /**
     * Match the status cell against the known list of statuses,
     * tolerant of case and surrounding whitespace. Returns the canonical
     * value (as defined in StagingIn::STATUSES) when it matches; if it's
     * empty, returns null. If it doesn't match anything, the raw value is
     * kept as-is and saved as a new status — it's never rejected or
     * silently dropped.
     */
    private function resolveStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        foreach (StagingIn::STATUSES as $valid) {
            if (strcasecmp($valid, $status) === 0) {
                return $valid;
            }
        }

        // Status belum dikenal di daftar STATUSES — simpan apa adanya
        // sebagai jenis status baru, bukan dibuang jadi null.
        return $status;
    }

    /**
     * Resolve the Item that a row's kode_barang/nama_barang/owner cells
     * refer to, creating it (and its vendor) on the fly when it doesn't
     * exist yet in the items table — the id is always returned so the
     * row can still be saved, never blocking the import.
     *
     * Matching is done on item_code_internal first (falling back to the
     * item name when no code was provided). If an owner is given and the
     * matched/created item doesn't have a vendor yet, the vendor is
     * filled in too.
     */
    private function resolveItemId(?string $itemCode, ?string $itemName, ?string $itemOwner): ?int
    {
        if ($itemCode === null && $itemName === null) {
            return null;
        }

        $vendorId = null;

        if ($itemOwner !== null) {
            $vendorId = Vendor::firstOrCreate(['name' => $itemOwner])->id;
        }

        $item = Item::firstOrCreate(
            ['item_code_internal' => $itemCode],
            [
                'name' => $itemName ?: $itemCode,
                'vendor_id' => $vendorId,
            ]
        );

        // Item sudah ada sebelumnya tapi belum punya vendor — lengkapi
        // dari data owner di baris import ini.
        if ($vendorId && ! $item->vendor_id) {
            $item->update(['vendor_id' => $vendorId]);
        }

        return $item->id;
    }

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        /*
         * Setiap baris HANYA memakai isi selnya sendiri. Sel yang kosong
         * berarti kosong (null) -- TIDAK diwarisi dari baris di atasnya.
         */
        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            $poNumber = $this->cleanValue($row['no_po'] ?? null);
            $itemCode = $this->cleanValue($row['kode_barang'] ?? null);
            $itemName = $this->cleanValue($row['nama_barang'] ?? null);
            $supplierOrigin = $this->cleanValue($row['supplier'] ?? null);
            $itemOwner = $this->cleanValue($row['owner'] ?? null);
            $notes = $this->cleanValue($row['keterangan'] ?? null);
            $rawLocation = $this->cleanValue($row['lokasi'] ?? null);
            $rawStatus = $this->cleanValue($row['status'] ?? null);
            $rawIncoterms = $this->cleanValue($row['incoterms'] ?? null);
            $qty = $this->parseQty($row['qty'] ?? null);
            $arrivalDate = $this->parseDate($row['tanggal_kedatangan'] ?? null);
            $location = $this->resolveLocation($rawLocation);
            $incoterms = $this->resolveIncoterms($rawIncoterms);
            $status = $this->resolveStatus($rawStatus);

            if ($poNumber === null && $itemCode === null && $itemName === null
                && $supplierOrigin === null && $itemOwner === null && $rawLocation === null
                && $notes === null && $arrivalDate === null && $qty === 0 && $rawStatus === null
                && $rawIncoterms === null) {
                continue;
            }

            // Baris dengan lokasi "Outbound" / "Outbound Shipment" (case-insensitive)
            // tidak boleh disimpan ke database — lewati baris ini sepenuhnya.
            if ($rawLocation !== null && in_array(strtolower($rawLocation), ['outbound', 'outbound shipment'], true)) {
                continue;
            }

            try {

                $itemId = $this->resolveItemId($itemCode, $itemName, $itemOwner);

                $staging = StagingIn::create([
                    'po_number' => $poNumber,
                    'arrival_date' => $arrivalDate,
                    'supplier_origin' => $supplierOrigin,
                    'item_id' => $itemId,
                    'qty' => $qty,
                    'location' => $location,
                    'incoterms' => $incoterms,
                    'status' => $status,
                    'notes' => $notes,
                ]);

                $this->history->logCreated($staging);

                $this->imported++;

            } catch (\Throwable $e) {

                $this->errors[] =
                    "Baris Excel {$excelRow} gagal.\n".
                    'No. PO      : '.($poNumber ?? '-')."\n".
                    'Kode Barang : '.($itemCode ?? '-')."\n".
                    'Nama Barang : '.($itemName ?? '-')."\n".
                    'Lokasi      : '.($rawLocation ?? '-')."\n".
                    'Incoterms   : '.($rawIncoterms ?? '-')."\n".
                    'Status      : '.($rawStatus ?? '-')."\n".
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