<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Item;
use App\Models\Vendor;
use App\Support\StockBaseline;
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
     * Jumlah barang (bukan baris) yang berhasil disimpan -- termasuk
     * barang yang item_code_internal-nya SUDAH ADA di database (item
     * baru tidak dibuat, tapi lokasi & qty-nya tetap disimpan/diperbarui).
     */
    public int $imported = 0;

    /**
     * Dari $imported di atas, berapa yang benar-benar barang BARU
     * (record Item baru dibuat).
     */
    public int $newItemsCreated = 0;

    /**
     * Pesan informasi untuk barang yang item_code_internal-nya SUDAH ADA
     * di database. Item BARU tidak dibuat lagi (supaya nama barang tidak
     * dobel), tapi data lokasi + lot + quantity-nya TETAP diproses dan
     * disimpan/diperbarui ke item yang sudah ada tsb. Ini bukan
     * kegagalan, cuma catatan informatif.
     */
    public array $existingItemNotices = [];

    public function collection(Collection $rows)
    {
        $this->errors = [];
        $this->imported = 0;
        $this->newItemsCreated = 0;
        $this->existingItemNotices = [];

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

                /*
                 * ==========================================
                 * VALIDASI DATA BARANG
                 * ==========================================
                 *
                 * Hanya item_code_internal yang wajib diisi.
                 * Nama barang boleh kosong (kolom name nullable).
                 */
                if (empty($firstRow['item_code_internal'])) {
                    throw new \Exception(
                        'Kode barang internal wajib diisi.'
                    );
                }

                $itemCodeInternal = trim($firstRow['item_code_internal']);

                /*
                 * ==========================================
                 * CEK DUPLIKAT ITEM CODE INTERNAL
                 * ==========================================
                 *
                 * Jika item_code_internal sudah ada di database, Item
                 * BARU TIDAK dibuat lagi (supaya nama barang tidak
                 * dobel) -- item yang sudah ada dipakai ulang. TAPI
                 * data lokasi + lot + quantity di baris Excel ini tetap
                 * diproses & disimpan/diperbarui untuk item tsb, karena
                 * itu bagian penting dari import (stok per lokasi),
                 * bukan sekadar data barangnya.
                 */
                $existingItem = Item::whereRaw(
                    'LOWER(item_code_internal) = ?',
                    [strtolower($itemCodeInternal)]
                )->first();

                $isNewItem = ! $existingItem;

                if (! $isNewItem) {
                    $this->existingItemNotices[] =
                        "Baris Excel {$excelRowLabel}.\n" .
                        'Item Code : ' . $itemCodeInternal . "\n" .
                        'Nama      : ' . ($firstRow['name'] ?? '-') . "\n" .
                        'Catatan   : item_code_internal sudah ada di database, item baru tidak dibuat, tapi data lokasi & qty tetap disimpan/diperbarui untuk item yang sudah ada.';
                }

                DB::transaction(function () use ($group, $firstRow, $itemCodeInternal, $existingItem, $isNewItem) {

                    if ($isNewItem) {

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
                            'item_code_internal' => $itemCodeInternal,
                            'item_code_supplier' => !empty($firstRow['item_code_supplier'])
                                ? trim($firstRow['item_code_supplier'])
                                : null,
                            'item_code_customer' => !empty($firstRow['item_code_customer'])
                                ? trim($firstRow['item_code_customer'])
                                : null,
                            'name' => !empty($firstRow['name'])
                                ? trim($firstRow['name'])
                                : null,
                            'description' => !empty($firstRow['description'])
                                ? trim($firstRow['description'])
                                : null,
                        ]);

                    } else {

                        /*
                         * ==========================================
                         * PAKAI ITEM YANG SUDAH ADA
                         * ==========================================
                         *
                         * Tidak membuat Item baru & tidak mengubah data
                         * Item yang sudah ada (nama, vendor, dst) --
                         * hanya dipakai referensinya supaya lokasi &
                         * qty di bawah bisa disimpan ke item yang benar.
                         */
                        $item = $existingItem;
                    }

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
                         *
                         * Dulu pakai attach() -- itu HANYA aman untuk
                         * item yang baru dibuat (belum punya baris
                         * location_stock sama sekali). Sekarang item
                         * yang SUDAH ADA juga bisa lewat sini, jadi
                         * kombinasi item+lokasi+lot ini bisa jadi sudah
                         * punya baris sebelumnya. updateOrCreate() aman
                         * untuk dua kasus: kombinasi baru -> dibuat,
                         * kombinasi yang sudah ada -> quantity &
                         * opening_balance-nya diperbarui sesuai nilai
                         * dari file Excel ini.
                         */
                        LocationStock::updateOrCreate(
                            [
                                'item_id' => $item->id,
                                'location_id' => $location->id,
                                'lot' => $entry['lot'],
                            ],
                            [
                                'opening_balance' => $entry['quantity'],
                                'quantity' => $entry['quantity'],
                                /*
                                 * Garis batas stock opname: semua mutasi
                                 * yang sudah ada sampai saat ini dianggap
                                 * riwayat lama dan TIDAK ikut dihitung ke
                                 * saldo. Hitungan saldo mulai dari qty
                                 * hasil import ini.
                                 */
                                'baseline_mutation_id' => StockBaseline::current(
                                    $item->id,
                                    $location->id,
                                    $entry['lot']
                                ),
                            ]
                        );
                    }
                });

                if ($isNewItem) {
                    $this->newItemsCreated++;
                }

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
         *
         * Barang yang item_code_internal-nya SUDAH ADA
         * ($this->existingItemNotices) itu NORMAL, bukan kegagalan --
         * item barunya tidak dibuat, tapi lokasi & qty-nya tetap
         * disimpan (lihat $this->imported vs $this->newItemsCreated).
         * Jadi tidak didaftar satu-satu di pesan exception ini, cukup
         * jumlahnya saja. Controller tetap bisa membaca
         * $this->existingItemNotices untuk detail lengkapnya kalau
         * perlu. Hanya barang yang BENAR-BENAR gagal ($this->errors)
         * yang membuat exception dilempar dan didetailkan di sini.
         */
        if (!empty($this->errors)) {

            $summary =
                "Import selesai: {$this->imported} barang berhasil disimpan "
                . "({$this->newItemsCreated} barang baru, "
                . (count($this->existingItemNotices)) . ' barang sudah ada sebelumnya namun lokasi/qty-nya tetap diperbarui), '
                . count($this->errors) . ' barang gagal.';

            throw new \Exception(
                $summary . "\n\n" . implode("\n\n", $this->errors)
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
     * Mendukung beberapa format:
     *
     * 1) Lokasi tunggal
     *    7-11-1 + 25
     *    => 7-11-1 : 25
     *
     * 2) Lokasi bentuk singkat pakai "/" (dibagi rata dari kolom Quantity)
     *    7-11/12-1 + 25
     *    => 7-11-1 : 13
     *    => 7-12-1 : 12
     *
     * 3) Beberapa lokasi dipisah koma, MASING-MASING punya quantity
     *    sendiri di dalam kurung (kolom Quantity diabaikan untuk baris
     *    ini karena tiap lokasi sudah eksplisit)
     *    B7 (60ea), O2 (4ea)
     *    => B7 : 60
     *    => O2 : 4
     *
     * 4) Kode lokasi format RxPyLz dipakai APA ADANYA sebagai
     *    location_name (angka nol di depan dipertahankan, huruf
     *    dijadikan kapital)
     *    R7P01L1 => R7P01L1
     */
    private function expandLocationsForRow(
        $locationRaw,
        $qtyRaw
    ): array {

        $locationRaw = trim((string) ($locationRaw ?? ''));

        $qty = $this->parseQuantity($qtyRaw);

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
         * ==========================================
         * PECAH BERDASARKAN KOMA
         * ==========================================
         *
         * Tiap bagian BISA punya quantity sendiri di dalam kurung,
         * misalnya "B7 (60ea)" atau "R2P14L3 (1)".
         */
        $segments = array_map('trim', explode(',', $locationRaw));

        $parsedSegments = [];
        $hasExplicitQty = false;

        foreach ($segments as $segment) {

            if ($segment === '') {
                continue;
            }

            $explicitQty = null;

            // Cocokkan "<kode lokasi> (<quantity>...)" di akhir bagian.
            if (preg_match('/^(.*?)\(([^)]*)\)\s*$/', $segment, $m)) {

                $codePart = trim($m[1]);
                $qtyPart = trim($m[2]);

                // Ambil angka pertama dari isi kurung, mis. "60ea" => 60.
                if (preg_match('/-?\d+(?:[.,]\d+)?/', $qtyPart, $qm)) {

                    $explicitQty = (float) str_replace(
                        ',',
                        '.',
                        $qm[0]
                    );

                    $hasExplicitQty = true;
                }

            } else {

                $codePart = $segment;
            }

            if ($codePart === '') {
                continue;
            }

            $parsedSegments[] = [
                'code' => $codePart,
                'qty' => $explicitQty,
            ];
        }

        if (empty($parsedSegments)) {

            return [
                [
                    'location' => 'Unlocated',
                    'quantity' => $qty,
                ]
            ];
        }

        $result = [];

        if ($hasExplicitQty) {

            /*
             * Minimal satu bagian punya quantity eksplisit di kurung.
             * Setiap bagian dipakai apa adanya dengan quantity-nya
             * masing-masing (bagian tanpa kurung dianggap 0, karena
             * tidak ada dasar quantity yang jelas untuknya).
             */
            foreach ($parsedSegments as $segmentData) {

                $segmentQty = $segmentData['qty'] ?? 0;

                $expandedNames = $this->expandLocationCode(
                    $segmentData['code']
                );

                $shares = $this->splitQuantity(
                    $segmentQty,
                    count($expandedNames)
                );

                foreach ($expandedNames as $i => $name) {

                    $result[] = [
                        'location' => $name,
                        'quantity' => $shares[$i],
                    ];
                }
            }

        } else {

            /*
             * Tidak ada quantity eksplisit sama sekali -> perilaku lama,
             * kolom Quantity dibagi rata ke semua lokasi hasil ekspansi.
             */
            $allNames = [];

            foreach ($parsedSegments as $segmentData) {

                foreach ($this->expandLocationCode($segmentData['code']) as $name) {
                    $allNames[] = $name;
                }
            }

            $shares = $this->splitQuantity(
                $qty,
                count($allNames)
            );

            foreach ($allNames as $i => $name) {

                $result[] = [
                    'location' => $name,
                    'quantity' => $shares[$i],
                ];
            }
        }

        return $result;
    }

    /**
     * Ubah SATU kode lokasi (tanpa quantity) menjadi satu atau
     * beberapa nama lokasi final yang disimpan di location_name.
     *
     * - Format RxPyLz (mis. R7P01L1) => dipakai apa adanya, hanya
     *   dijadikan huruf kapital supaya konsisten (r7p01l1 => R7P01L1).
     *   Angka nol di depan TIDAK dibuang.
     * - Mengandung "/" => dipecah lewat expandSlashLocation()
     * - Selain itu dipakai apa adanya (mis. B7, O2, F3)
     */
    private function expandLocationCode(string $code): array
    {
        $code = trim($code);

        if ($code === '' || $code === '-') {
            return ['Unlocated'];
        }

        if (preg_match('/^R\d+P\d+L\d+$/i', $code)) {

            return [strtoupper($code)];
        }

        if (str_contains($code, '/')) {
            return $this->expandSlashLocation($code);
        }

        return [$code];
    }

    /**
     * Parse nilai quantity mentah dari Excel menjadi float.
     * Mendukung koma sebagai pemisah desimal.
     */
    private function parseQuantity($qtyRaw): float
    {
        if (is_numeric($qtyRaw)) {
            return (float) $qtyRaw;
        }

        return (float) str_replace(
            ',',
            '.',
            trim((string) $qtyRaw)
        );
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