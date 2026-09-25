<?php

namespace App\Imports;

use App\Imports\Concerns\ParsesExcelDates;
use App\Models\Item;
use App\Models\LocationStock;
use App\Models\StagingOut;
use App\Models\StockMutation;
use App\Services\StagingOutHistoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import khusus untuk Staging Out yang barangnya diambil dari STOK
 * (source_type = stock) -- mirip StagingOutImport biasa, tapi untuk
 * baris di sini Lokasi & Lot TIDAK diminta di Excel, melainkan dicari
 * sendiri dari tabel location_stocks berdasarkan Kode Barang.
 *
 * Template Excel-nya SAMA PERSIS dengan StagingOutImport biasa: No. SO,
 * Customer, Kode Barang, Line Item, Qty, Tgl Instruksi Kirim, Tgl
 * Picking, No. DO, Tgl Resi Pengiriman. Memang tidak ada kolom Lokasi.
 *
 * Aturan pencarian lokasi otomatis:
 * - Kalau barang itu stoknya (qty > 0) ada di TEPAT 1 lokasi, dan di
 *   lokasi itu cuma ada 1 baris lot -> baris diimport sebagai
 *   source_type=stock, stok dikurangi & dicatat sebagai qty_out di
 *   stock_mutations (persis seperti kalau diinput manual lewat form
 *   dengan sumber "Stock", lihat StagingOutController::applyStockOut()).
 * - Kalau barang itu TIDAK punya stok sama sekali, stoknya tersebar di
 *   LEBIH DARI 1 lokasi, atau di 1 lokasi itu ternyata ada LEBIH DARI 1
 *   lot -> baris TIDAK disimpan. Ditolak dan dicatat sebagai error
 *   beserta nomor baris Excel-nya, supaya user bisa cek & input baris
 *   itu manual lewat form (pilih sendiri lokasi/lot-nya).
 */
class StagingOutStockImport implements ToCollection, WithHeadingRow
{
    use ParsesExcelDates;

    /**
     * Human-readable messages for rows that failed to import: baik yang
     * gagal karena error database, maupun yang sengaja dilewati karena
     * lokasi stoknya ambigu/tidak ada.
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
     * Normalize a raw cell value into a clean string or null. Sama
     * seperti di StagingOutImport.
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
     * Parse a qty cell into a non-negative integer. Sama seperti di
     * StagingOutImport.
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
     * Cari Item berdasarkan kode barang -- dicocokkan ke kode internal,
     * customer, ATAUPUN supplier (kalau kode dari salah satu jenis
     * kodenya cocok, itemnya ketemu). Beda dengan StagingOutImport
     * biasa, di sini item TIDAK dibuat otomatis kalau tidak ketemu,
     * karena barang yang diambil dari stok memang harus sudah ada &
     * punya catatan stok -- barang baru tidak mungkin punya stok.
     */
    private function findItemByCode(string $itemCode): ?Item
    {
        return Item::where('item_code_internal', $itemCode)
            ->orWhere('item_code_customer', $itemCode)
            ->orWhere('item_code_supplier', $itemCode)
            ->first();
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
            $deliveryReceiptDate = $this->parseDate($row['tgl_resi_pengiriman'] ?? null);

            // Baris kosong total -- dilewati begitu saja, bukan error.
            if ($soNumber === null && $customer === null && $itemCode === null
                && $lineItem === null && $doNumber === null && $qty === 0
                && $deliveryInstructionDate === null && $pickingDate === null
                && $deliveryReceiptDate === null) {
                continue;
            }

            // Kode Barang wajib ada di sini -- tanpa itu tidak ada cara
            // mencari stoknya sama sekali.
            if ($itemCode === null) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Kode Barang kosong, tidak bisa mencari lokasi stoknya.";

                continue;
            }

            $item = $this->findItemByCode($itemCode);

            if (! $item) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Kode Barang '{$itemCode}' tidak ditemukan di data barang.";

                continue;
            }

            // Semua baris location_stocks yang MASIH ada stoknya untuk
            // barang ini. Lot kosong/null dianggap satu lot tersendiri
            // (sama seperti perlakuan lot di tempat lain pada aplikasi).
            $stocks = LocationStock::where('item_id', $item->id)
                ->where('quantity', '>', 0)
                ->get();

            $distinctLocationCount = $stocks->pluck('location_id')->unique()->count();

            if ($distinctLocationCount === 0) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Barang '{$itemCode}' tidak punya stok di lokasi manapun.";

                continue;
            }

            if ($distinctLocationCount > 1) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Barang '{$itemCode}' punya stok di {$distinctLocationCount} lokasi berbeda. Input baris ini manual lewat form & pilih sendiri lokasinya.";

                continue;
            }

            // Tepat 1 lokasi -- tapi kalau di lokasi itu ternyata ada
            // lebih dari 1 baris lot, tetap ambigu (tidak tahu lot mana
            // yang harus dipakai), jadi tetap ditolak.
            if ($stocks->count() > 1) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Barang '{$itemCode}' ada di 1 lokasi tapi punya {$stocks->count()} lot berbeda. Input baris ini manual lewat form & pilih sendiri lot-nya.";

                continue;
            }

            $stock = $stocks->first();

            if ((float) $stock->quantity < (float) $qty) {
                $this->errors[] = "Baris Excel {$excelRow} dilewati: Stok barang '{$itemCode}' di lokasi tsb cuma {$stock->quantity}, kurang untuk qty {$qty}.";

                continue;
            }

            $locationId = $stock->location_id;
            $lot = $stock->lot;

            try {

                DB::transaction(function () use (
                    $soNumber, $customer, $item, $locationId, $lot, $lineItem, $qty,
                    $deliveryInstructionDate, $pickingDate, $doNumber, $deliveryReceiptDate
                ) {
                    $staging = StagingOut::create([
                        'so_number' => $soNumber,
                        'customer' => $customer,
                        'source_type' => 'stock',
                        'item_id' => $item->id,
                        'location_id' => $locationId,
                        'lot' => $lot,
                        'line_item' => $lineItem,
                        'qty' => $qty,
                        'delivery_instruction_date' => $deliveryInstructionDate,
                        'picking_date' => $pickingDate,
                        'do_number' => $doNumber,
                        'delivery_receipt_date' => $deliveryReceiptDate,
                    ]);

                    // Kunci baris stok-nya dulu (lockForUpdate) supaya
                    // aman dari race condition kalau ada proses lain
                    // yang menyentuh stok item/lokasi/lot yang sama di
                    // waktu bersamaan -- persis seperti
                    // StagingOutController::lockLocationStock().
                    $locked = LocationStock::where('item_id', $item->id)
                        ->where('location_id', $locationId)
                        ->where('lot', $lot)
                        ->lockForUpdate()
                        ->first();

                    if (! $locked || (float) $locked->quantity < (float) $qty) {
                        throw new \RuntimeException('Stok tidak mencukupi (kemungkinan berubah saat proses import berjalan).');
                    }

                    $locked->quantity = (float) $locked->quantity - (float) $qty;
                    $locked->save();

                    StockMutation::create([
                        'item_id' => $item->id,
                        'location_id' => $locationId,
                        'lot' => $lot,
                        'transaction_date' => $deliveryReceiptDate
                            ?? $pickingDate
                            ?? $deliveryInstructionDate
                            ?? now(),
                        'transaction_number' => $soNumber,
                        'description' => 'Staging Out #'.$staging->id.($customer ? ' - '.$customer : '').' (Import Stock)',
                        'qty_in' => 0,
                        'qty_out' => $qty,
                        'qty_balance' => $locked->quantity,
                    ]);

                    $this->history->logCreated($staging);

                    // Sama seperti StagingOutImport: kalau tgl resi
                    // pengiriman sudah terisi di baris Excel-nya, artinya
                    // barang ini SUDAH TERKIRIM — catat sebagai event
                    // "delivered" (bukan "deleted"!) supaya status di
                    // halaman history tampil "Terkirim"/"Selesai". Mutasi
                    // stok (qty_out) di atas TETAP dibiarkan -- itu catatan
                    // stok yang sungguhan sudah keluar. Yang dihapus dari
                    // tabel aktif hanya baris staging_outs-nya.
                    if ($deliveryReceiptDate !== null) {
                        $this->history->logAutoDelivered($staging, $doNumber, $deliveryReceiptDate);

                        $staging->delete();
                    }
                });

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

        // PENTING: SENGAJA TIDAK melempar Exception di sini walaupun ada
        // $this->errors. Baris yang sengaja dilewati (lokasi ambigu,
        // stok kosong, dst) itu BUKAN kegagalan total import -- baris
        // lain yang berhasil (dihitung di $this->imported) sudah
        // ke-INSERT sungguhan ke database di loop atas. Kalau di sini
        // kita lempar Exception, controller akan mengira SELURUH import
        // gagal padahal sebagian sudah berhasil tersimpan. Biarkan
        // $this->imported & $this->errors dibaca oleh controller
        // setelah Excel::import() selesai, supaya pesan yang ditampilkan
        // ke user akurat: sekian berhasil, sekian dilewati (beserta
        // alasan & nomor barisnya).
    }
}