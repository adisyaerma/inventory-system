<?php

namespace App\Imports;

use App\Imports\Concerns\ParsesExcelDates;
use App\Models\Item;
use App\Models\StagingOut;
use App\Services\StagingOutHistoryService;
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

    public function __construct(private StagingOutHistoryService $history)
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
     * Resolve the Item that a row's kode_barang cell refers to, creating
     * it on the fly when it doesn't exist yet in the items table — the
     * id is always returned so the row can still be saved, never
     * blocking the import. The sheet only carries a code (no separate
     * item name column), so a newly created item's name falls back to
     * the code itself.
     */
    private function resolveItemId(?string $itemCode): ?int
    {
        if ($itemCode === null) {
            return null;
        }

        return Item::firstOrCreate(
            ['item_code_internal' => $itemCode],
            [
                'name' => null,
            ]
        )->id;
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
            // Kolom "Tgl Resi Pengiriman" di file Excel dipetakan ke
            // delivery_receipt_date. delivery_date sendiri memang tidak
            // diisi lewat import sama sekali.
            $deliveryReceiptDate = $this->parseDate($row['tgl_resi_pengiriman'] ?? null);

            if ($soNumber === null && $customer === null && $itemCode === null
                && $lineItem === null && $doNumber === null && $qty === 0
                && $deliveryInstructionDate === null && $pickingDate === null
                && $deliveryReceiptDate === null) {
                continue;
            }

            // Catatan: No DO dulu wajib diisi supaya baris disimpan.
            // Sekarang TIDAK LAGI — baris tetap disimpan walau No DO
            // kosong (null), selama baris ini bukan baris kosong total
            // (sudah ditangani oleh pengecekan di atas).

            try {

                $itemId = $this->resolveItemId($itemCode);

                $staging = StagingOut::create([
                    'so_number' => $soNumber,
                    'customer' => $customer,
                    'item_id' => $itemId,
                    'line_item' => $lineItem,
                    'qty' => $qty,
                    'delivery_instruction_date' => $deliveryInstructionDate,
                    'picking_date' => $pickingDate,
                    'do_number' => $doNumber,
                    'delivery_receipt_date' => $deliveryReceiptDate,
                ]);

                $this->history->logCreated($staging);

                // Catatan: dulu ada logika yang otomatis menganggap baris
                // "sudah selesai" (lalu dipindah ke history & dihapus dari
                // tabel aktif) begitu tanggal kirim di Excel terisi. Sekarang
                // TIDAK LAGI berlaku untuk delivery_receipt_date — resi
                // pengiriman terisi bukan berarti pengirimannya sudah
                // dikonfirmasi (delivery_date). Baris tetap disimpan di
                // tabel aktif staging_outs sampai delivery_date-nya sendiri
                // dikonfirmasi lewat menu konfirmasi kirim.

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