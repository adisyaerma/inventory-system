<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LocationStockImport implements ToCollection, WithHeadingRow
{
    /**
     * Human-readable messages for rows/grup barang yang gagal diimport.
     */
    public array $errors = [];

    /**
     * Jumlah barang (bukan baris) yang berhasil disimpan.
     */
    public int $imported = 0;

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;

        /*
         * Kolom Item Code Internal, Item Code Supplier, Item Code Customer,
         * Name, dan Lot bisa di-merge di Excel untuk barang dengan banyak lokasi.
         *
         * Baris lanjutan hasil merge akan kosong pada kolom tersebut.
         *
         * Kita kelompokkan terlebih dahulu berdasarkan barang.
         */
        $groups = $this->groupRowsByItem($rows);

        foreach ($groups as $group) {

            $firstRow = $group['rows'][0];

            $excelRowLabel = $group['excel_row_start'] === $group['excel_row_end']
                ? (string) $group['excel_row_start']
                : $group['excel_row_start'] . ' s.d. ' . $group['excel_row_end'];

            try {

                DB::transaction(function () use ($group, $firstRow) {

                    /*
                     * ==========================================
                     * VALIDASI DATA BARANG
                     * ==========================================
                     */
                    if (
                        empty($firstRow['item_code_internal']) ||
                        empty($firstRow['name'])
                    ) {
                        throw new \Exception(
                            'Kode barang internal dan nama barang wajib diisi.'
                        );
                    }

                    /*
                     * ==========================================
                     * VENDOR
                     * ==========================================
                     */
                    $vendor = null;

                    if (!empty($firstRow['vendor'])) {

                        $vendorName = trim($firstRow['vendor']);

                        $vendor = Vendor::whereRaw(
                            'LOWER(name) = ?',
                            [strtolower($vendorName)]
                        )->first();

                        if (!$vendor) {
                            $vendor = Vendor::create([
                                'name' => $vendorName,
                            ]);
                        }
                    }

                    /*
                     * ==========================================
                     * CREATE ITEM
                     * ==========================================
                     */
                    $item = Item::create([
                        'vendor_id' => $vendor?->id,
                        'item_code_internal' => trim(
                            $firstRow['item_code_internal']
                        ),
                        'item_code_supplier' => !empty($firstRow['item_code_supplier'])
                            ? trim($firstRow['item_code_supplier'])
                            : null,
                        'item_code_customer' => !empty($firstRow['item_code_customer'])
                            ? trim($firstRow['item_code_customer'])
                            : null,
                        'name' => trim($firstRow['name']),
                        'description' => !empty($firstRow['description'])
                            ? trim($firstRow['description'])
                            : null,
                    ]);

                    /*
                     * ==========================================
                     * KUMPULKAN LOCATION + LOT + QUANTITY
                     * ==========================================
                     *
                     * Satu barang bisa mempunyai banyak lokasi, dan tiap
                     * baris bisa punya lot yang berbeda-beda. Lot dibaca
                     * per baris (bukan cuma dari baris pertama grup),
                     * dan dikelompokkan bersama lokasi-nya supaya lokasi
                     * yang sama dengan lot berbeda tidak tertimpa/
                     * tercampur jadi satu baris.
                     */
                    $locationLotQuantities = [];

                    foreach ($group['rows'] as $row) {

                        $rowLot = null;

                        if (
                            isset($row['lot']) &&
                            trim((string) $row['lot']) !== ''
                        ) {
                            $rowLot = trim((string) $row['lot']);
                        }

                        $entries = $this->expandLocationsForRow(
                            $row['location'] ?? null,
                            $row['quantity'] ?? 0
                        );

                        foreach ($entries as $entry) {

                            $locationName = $entry['location'];
                            $quantity = $entry['quantity'];

                            // Kunci unik per kombinasi lokasi + lot.
                            $key = $locationName . '|||' . ($rowLot ?? '');

                            if (!isset($locationLotQuantities[$key])) {
                                $locationLotQuantities[$key] = [
                                    'location' => $locationName,
                                    'lot' => $rowLot,
                                    'quantity' => 0,
                                ];
                            }

                            $locationLotQuantities[$key]['quantity'] += $quantity;
                        }
                    }

                    /*
                     * Jika tidak ada lokasi
                     */
                    if (empty($locationLotQuantities)) {
                        $locationLotQuantities['Unlocated|||'] = [
                            'location' => 'Unlocated',
                            'lot' => null,
                            'quantity' => 0,
                        ];
                    }

                    /*
                     * ==========================================
                     * SIMPAN LOCATION STOCK
                     * ==========================================
                     */
                    foreach ($locationLotQuantities as $entry) {

                        $location = Location::firstOrCreate(
                            [
                                'location_name' => $entry['location'],
                            ],
                            [
                                'location_code' => null,
                            ]
                        );

                        /*
                         * location_stock:
                         *
                         * item_id
                         * location_id
                         * lot
                         * opening_balance
                         * quantity
                         */
                        $item->locations()->attach($location->id, [
                            'lot' => $entry['lot'],
                            'opening_balance' => $entry['quantity'],
                            'quantity' => $entry['quantity'],
                        ]);
                    }
                });

                $this->imported++;

            } catch (\Throwable $e) {

                /*
                 * Hanya barang ini yang gagal.
                 * Barang lain tetap diproses.
                 */
                $this->errors[] =
                    "Baris Excel {$excelRowLabel} gagal.\n" .
                    'Vendor    : ' . ($firstRow['vendor'] ?? '-') . "\n" .
                    'Item Code : ' . ($firstRow['item_code_internal'] ?? '-') . "\n" .
                    'Nama      : ' . ($firstRow['name'] ?? '-') . "\n" .
                    'Lot       : ' . ($firstRow['lot'] ?? '-') . "\n" .
                    'Location  : ' . ($firstRow['location'] ?? '-') . "\n" .
                    'Quantity  : ' . ($firstRow['quantity'] ?? '-') . "\n" .
                    'Error     : ' . $e->getMessage();
            }
        }

        /*
         * Barang yang valid tetap tersimpan meskipun ada
         * barang lain yang gagal.
         */
        if (!empty($this->errors)) {

            throw new \Exception(
                "Import selesai: {$this->imported} barang berhasil disimpan, "
                . count($this->errors) . ' barang gagal.'
                . "\n\n"
                . implode("\n\n", $this->errors)
            );
        }
    }

    /**
     * Kelompokkan baris Excel menjadi grup per barang.
     *
     * Baris dianggap barang baru jika:
     * - item_code_internal terisi
     * ATAU
     * - name terisi
     *
     * Baris berikutnya yang kosong dianggap sebagai lanjutan
     * dari barang sebelumnya.
     */
    private function groupRowsByItem(Collection $rows): array
    {
        $groups = [];

        $currentIndex = -1;

        foreach ($rows as $index => $row) {

            /*
             * WithHeadingRow:
             * row pertama data Excel dianggap nomor 2.
             */
            $excelRow = $index + 2;

            $hasItemData =
                !empty($row['item_code_internal']) ||
                !empty($row['name']);

            if ($hasItemData || $currentIndex === -1) {

                $groups[] = [
                    'excel_row_start' => $excelRow,
                    'excel_row_end' => $excelRow,
                    'rows' => [$row],
                ];

                $currentIndex++;

            } else {

                $groups[$currentIndex]['rows'][] = $row;

                $groups[$currentIndex]['excel_row_end'] = $excelRow;
            }
        }

        return $groups;
    }

    /**
     * Ubah satu baris Excel:
     *
     * location + quantity
     *
     * menjadi satu atau beberapa pasangan:
     *
     * location => quantity
     *
     * Contoh:
     *
     * 7-11-1 + 25
     * => 7-11-1 : 25
     *
     * 7-11/12-1 + 25
     * => 7-11-1 : 13
     * => 7-12-1 : 12
     */
    private function expandLocationsForRow(
        $locationRaw,
        $qtyRaw
    ): array {

        $locationRaw = trim((string) ($locationRaw ?? ''));

        /*
         * Konversi quantity
         */
        if (is_numeric($qtyRaw)) {

            $qty = (float) $qtyRaw;

        } else {

            $qty = (float) str_replace(
                ',',
                '.',
                trim((string) $qtyRaw)
            );
        }

        /*
         * Location kosong atau "-"
         */
        if ($locationRaw === '' || $locationRaw === '-') {

            return [
                [
                    'location' => 'Unlocated',
                    'quantity' => $qty,
                ]
            ];
        }

        /*
         * Tidak ada "/"
         */
        if (!str_contains($locationRaw, '/')) {

            return [
                [
                    'location' => $locationRaw,
                    'quantity' => $qty,
                ]
            ];
        }

        /*
         * Ada "/"
         */
        $locationNames = $this->expandSlashLocation($locationRaw);

        $shares = $this->splitQuantity(
            $qty,
            count($locationNames)
        );

        $result = [];

        foreach ($locationNames as $i => $name) {

            $result[] = [
                'location' => $name,
                'quantity' => $shares[$i],
            ];
        }

        return $result;
    }

    /**
     * Pecah string lokasi yang mengandung "/".
     *
     * Contoh:
     *
     * 6-11-1/6-12-1
     * =>
     * [
     *     6-11-1,
     *     6-12-1
     * ]
     *
     * 7-11/12-1
     * =>
     * [
     *     7-11-1,
     *     7-12-1
     * ]
     *
     * 6-14/15-1
     * =>
     * [
     *     6-14-1,
     *     6-15-1
     * ]
     */
    private function expandSlashLocation(string $location): array
    {
        $parts = array_map(
            'trim',
            explode('/', $location)
        );

        /*
         * Kalau setiap bagian sudah terlihat seperti
         * kode lokasi lengkap (minimal 2 "-"),
         * gunakan langsung.
         */
        $looksComplete = true;

        foreach ($parts as $part) {

            if (substr_count($part, '-') < 2) {

                $looksComplete = false;

                break;
            }
        }

        if ($looksComplete) {
            return $parts;
        }

        /*
         * Bentuk singkat.
         *
         * Contoh:
         * 7-11/12-1
         */
        $segments = explode('-', $location);

        $slashSegmentIndex = null;

        foreach ($segments as $i => $segment) {

            if (str_contains($segment, '/')) {

                $slashSegmentIndex = $i;

                break;
            }
        }

        /*
         * Fallback
         */
        if ($slashSegmentIndex === null) {
            return $parts;
        }

        /*
         * Pecah alternatif lokasi
         */
        $alternatives = explode(
            '/',
            $segments[$slashSegmentIndex]
        );

        $expanded = [];

        foreach ($alternatives as $alternative) {

            $newSegments = $segments;

            $newSegments[$slashSegmentIndex] =
                trim($alternative);

            $expanded[] = implode(
                '-',
                $newSegments
            );
        }

        return $expanded;
    }

    /**
     * Bagi quantity ke beberapa lokasi sedekat mungkin sama rata.
     *
     * Contoh:
     *
     * 25 / 2
     * => [13, 12]
     *
     * 25 / 3
     * => [9, 8, 8]
     */
    private function splitQuantity(
        float $qty,
        int $count
    ): array {

        if ($count <= 1) {
            return [$qty];
        }

        $qtyInt = (int) round($qty);

        $base = intdiv(
            $qtyInt,
            $count
        );

        $remainder =
            $qtyInt - ($base * $count);

        $parts = [];

        for ($i = 0; $i < $count; $i++) {

            $parts[] =
                $base +
                ($i < $remainder ? 1 : 0);
        }

        return $parts;
    }
}