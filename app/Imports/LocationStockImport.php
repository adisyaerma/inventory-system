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

        // Kolom Item Code Internal, Item Code Supplier, Item Code Customer, dan
        // Name di-merge di Excel untuk barang dengan banyak lokasi. Saat dibaca,
        // baris "lanjutan" (bukan baris pertama merge) akan tampil kosong pada
        // kolom-kolom tersebut. Di sini kita kelompokkan dulu baris-baris Excel
        // menjadi grup per barang: satu grup = satu barang + semua baris lokasi
        // miliknya (baik dari merge maupun dari tanda "/" pada satu baris).
        $groups = $this->groupRowsByItem($rows);

        foreach ($groups as $group) {

            $firstRow = $group['rows'][0];
            $excelRowLabel = $group['excel_row_start'] === $group['excel_row_end']
                ? (string) $group['excel_row_start']
                : $group['excel_row_start'].' s.d. '.$group['excel_row_end'];

            try {

                DB::transaction(function () use ($group, $firstRow) {

                    if (empty($firstRow['item_code_internal']) || empty($firstRow['name'])) {
                        throw new \Exception('Kode barang internal dan nama barang wajib diisi.');
                    }

                    $vendor = null;

                    if (! empty($firstRow['vendor'])) {
                        $vendorName = trim($firstRow['vendor']);

                        $vendor = Vendor::whereRaw(
                            'LOWER(name) = ?',
                            [strtolower($vendorName)]
                        )->first();

                        if (! $vendor) {
                            $vendor = Vendor::create([
                                'name' => $vendorName,
                            ]);
                        }
                    }

                    $item = Item::create([
                        'vendor_id' => $vendor?->id,
                        'item_code_internal' => $firstRow['item_code_internal'],
                        'item_code_supplier' => $firstRow['item_code_supplier'] ?? null,
                        'item_code_customer' => $firstRow['item_code_customer'] ?? null,
                        'name' => $firstRow['name'],
                        'description' => $firstRow['description'] ?? null,
                    ]);

                    // Kumpulkan semua pasangan lokasi => qty untuk barang ini.
                    // Satu baris Excel bisa menghasilkan lebih dari satu lokasi
                    // jika kolom Location mengandung tanda "/".
                    $locationQuantities = [];

                    foreach ($group['rows'] as $row) {

                        $entries = $this->expandLocationsForRow(
                            $row['location'] ?? null,
                            $row['quantity'] ?? 0
                        );

                        foreach ($entries as $entry) {
                            $name = $entry['location'];

                            $locationQuantities[$name] =
                                ($locationQuantities[$name] ?? 0) + $entry['quantity'];
                        }
                    }

                    if (empty($locationQuantities)) {
                        $locationQuantities['Unlocated'] = 0;
                    }

                    foreach ($locationQuantities as $locationName => $qty) {

                        $location = Location::firstOrCreate(
                            ['location_name' => $locationName],
                            ['location_code' => null]
                        );

                        $item->locations()->attach($location->id, [
                            'opening_balance' => $qty,
                            'quantity' => $qty,
                        ]);
                    }
                });

                $this->imported++;

            } catch (\Throwable $e) {

                // Hanya barang ini yang gagal, barang lain tetap lanjut diproses.
                $this->errors[] =
                    "Baris Excel {$excelRowLabel} gagal.\n".
                    'Vendor    : '.($firstRow['vendor'] ?? '-')."\n".
                    'Item Code : '.($firstRow['item_code_internal'] ?? '-')."\n".
                    'Nama      : '.($firstRow['name'] ?? '-')."\n".
                    'Location  : '.($firstRow['location'] ?? '-')."\n".
                    'Quantity  : '.($firstRow['quantity'] ?? '-')."\n".
                    'Error     : '.$e->getMessage();

            }
        }

        // Barang yang valid tetap tersimpan meski ada barang lain yang gagal.
        if (! empty($this->errors)) {
            throw new \Exception(
                "Import selesai: {$this->imported} barang berhasil disimpan, "
                .count($this->errors).' barang gagal.'.
                "\n\n".implode("\n\n", $this->errors)
            );
        }
    }

    /**
     * Kelompokkan baris-baris Excel menjadi grup per barang.
     *
     * Baris dianggap baris "baru" (awal barang) jika kolom item_code_internal
     * atau name terisi. Baris yang keduanya kosong dianggap baris lanjutan
     * (hasil merge cell) dari barang yang sedang berjalan, dan hanya berisi
     * lokasi + quantity tambahan untuk barang tersebut.
     */
    private function groupRowsByItem(Collection $rows): array
    {
        $groups = [];
        $currentIndex = -1;

        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            $hasItemData = ! empty($row['item_code_internal']) || ! empty($row['name']);

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
     * Ubah satu baris Excel (location + quantity) menjadi satu atau lebih
     * pasangan lokasi => qty.
     *
     * - Location kosong atau "-" => lokasi "Unlocated".
     * - Location tanpa "/" => satu lokasi, qty apa adanya.
     * - Location mengandung "/" => dianggap 2 (atau lebih) lokasi sekaligus,
     *   dan quantity-nya dibagi rata (tidak harus persis sama, tanpa desimal).
     */
    private function expandLocationsForRow($locationRaw, $qtyRaw): array
    {
        $locationRaw = trim((string) ($locationRaw ?? ''));
        $qty = is_numeric($qtyRaw) ? (float) $qtyRaw : (float) str_replace(',', '.', trim((string) $qtyRaw));

        if ($locationRaw === '' || $locationRaw === '-') {
            return [['location' => 'Unlocated', 'quantity' => $qty]];
        }

        if (! str_contains($locationRaw, '/')) {
            return [['location' => $locationRaw, 'quantity' => $qty]];
        }

        $locationNames = $this->expandSlashLocation($locationRaw);

        $shares = $this->splitQuantity($qty, count($locationNames));

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
     * Pecah satu string lokasi yang mengandung "/" menjadi beberapa nama
     * lokasi lengkap. Mendukung dua gaya penulisan yang dipakai di template:
     *
     * 1. Bentuk lengkap di kedua sisi, mis. "6-11-1/6-12-1"
     *    => ["6-11-1", "6-12-1"]
     * 2. Bentuk singkat, hanya satu segmen yang berbeda, mis. "7-11/12-1"
     *    (artinya "7-11-1" dan "7-12-1") atau "6-14/15-1"
     *    (artinya "6-14-1" dan "6-15-1")
     */
    private function expandSlashLocation(string $location): array
    {
        $parts = array_map('trim', explode('/', $location));

        // Kalau setiap bagian sudah terlihat seperti kode lokasi lengkap
        // (punya minimal 2 tanda "-"), pakai langsung apa adanya.
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

        // Bentuk singkat: cari segmen (dipisah "-") yang mengandung "/",
        // lalu jadikan kode lokasi lengkap untuk tiap alternatifnya.
        $segments = explode('-', $location);

        $slashSegmentIndex = null;

        foreach ($segments as $i => $segment) {
            if (str_contains($segment, '/')) {
                $slashSegmentIndex = $i;
                break;
            }
        }

        if ($slashSegmentIndex === null) {
            // Fallback: tidak sesuai pola yang dikenali, pakai hasil split "/" apa adanya.
            return $parts;
        }

        $alternatives = explode('/', $segments[$slashSegmentIndex]);

        $expanded = [];

        foreach ($alternatives as $alternative) {
            $newSegments = $segments;
            $newSegments[$slashSegmentIndex] = trim($alternative);
            $expanded[] = implode('-', $newSegments);
        }

        return $expanded;
    }

    /**
     * Bagi quantity ke $count lokasi sedekat mungkin sama rata, tanpa
     * memaksakan hasil desimal. Sisa pembagian dibagikan satu-satu ke
     * lokasi pertama. Contoh: 25 dibagi 2 => [13, 12].
     */
    private function splitQuantity(float $qty, int $count): array
    {
        if ($count <= 1) {
            return [$qty];
        }

        $qtyInt = (int) round($qty);
        $base = intdiv($qtyInt, $count);
        $remainder = $qtyInt - ($base * $count);

        $parts = [];

        for ($i = 0; $i < $count; $i++) {
            $parts[] = $base + ($i < $remainder ? 1 : 0);
        }

        return $parts;
    }
}