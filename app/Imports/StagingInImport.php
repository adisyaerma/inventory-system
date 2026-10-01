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
     * Cache indeks lokasi (dibangun sekali per import dari
     * StagingIn::LOCATIONS) — lihat buildLocationIndex().
     */
    private ?array $locationIndex = null;

    /**
     * Ubah teks lokasi apa pun menjadi "kunci" yang seragam, supaya
     * perbedaan cara mengetik tidak berpengaruh:
     *  - huruf besar/kecil                  "TEMPORARY HOLD 2" = "temporary hold 2"
     *  - spasi ganda / spasi di tepi        "temporary  hold 2 "
     *  - tanda baca & pemisah               "Temporary-Hold_2", "Temporary Hold (2)", "Temporary Hold #2"
     *  - huruf & angka yang menempel        "Temporary Hold2"
     *  - angka nol di depan                 "Rack 01" = "Rack 1"
     *  - singkatan umum                     "Temp Hold 2", "Tmp Hold 2"
     *  - spasi non-breaking dari copy-paste (NBSP, zero-width, dll)
     *
     * Hasil: kata-kata huruf kecil dipisah satu spasi, mis. "temporary hold 2".
     */
    private function normalizeLocationKey(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        // Buang karakter tak terlihat yang sering ikut saat copy-paste.
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);

        $value = str_replace('&', ' and ', $value);

        // Pisahkan huruf dan angka yang menempel: "hold2" -> "hold 2".
        $value = preg_replace('/(?<=\p{L})(?=\p{N})|(?<=\p{N})(?=\p{L})/u', ' ', $value);

        // Semua yang bukan huruf/angka (spasi, -, _, /, ., (), #, NBSP, dst)
        // dijadikan satu spasi.
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        $value = trim($value);

        // Angka nol di depan diabaikan: "01" = "1".
        $value = preg_replace('/(?<![\p{L}\p{N}])0+(?=\d)/u', '', $value);

        // Singkatan umum -> bentuk lengkap.
        $value = preg_replace('/\b(?:temp|tmp)\b/u', 'temporary', $value);

        // Kata sambung diabaikan: "Hold & Repair" = "Hold / Repair" = "Hold Repair".
        $value = preg_replace('/\b(?:and|dan|atau|or)\b/u', ' ', $value);
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        return $value;
    }

    /**
     * Bangun indeks pencarian dari StagingIn::LOCATIONS:
     *  - 'full'     : kunci ternormalisasi            "temporary hold 2"
     *  - 'compact'  : tanpa spasi                     "temporaryhold2"
     *  - 'initials' : inisial kata + angka            "th2"
     * Kunci yang bentrok antar-lokasi dibuang (dianggap ambigu), supaya
     * tidak pernah salah memetakan ke lokasi yang keliru.
     */
    private function buildLocationIndex(): array
    {
        $index = ['full' => [], 'compact' => [], 'initials' => [], 'keys' => [], 'tokens' => []];
        $seen = ['full' => [], 'compact' => [], 'initials' => []];

        foreach (StagingIn::LOCATIONS as $valid) {
            $key = $this->normalizeLocationKey((string) $valid);

            if ($key === '') {
                continue;
            }

            $words = explode(' ', $key);

            $initials = '';
            foreach ($words as $word) {
                $initials .= ctype_digit($word) ? $word : mb_substr($word, 0, 1, 'UTF-8');
            }

            $entries = [
                'full' => $key,
                'compact' => str_replace(' ', '', $key),
                'initials' => $initials,
            ];

            foreach ($entries as $type => $k) {
                $seen[$type][$k][$valid] = true;
            }

            $index['keys'][$valid] = $entries['compact'];
            $index['tokens'][$valid] = $words;
        }

        foreach ($seen as $type => $keys) {
            foreach ($keys as $k => $canonicals) {
                // Hanya simpan kunci yang menunjuk tepat ke satu lokasi.
                if (count($canonicals) === 1) {
                    $index[$type][$k] = array_key_first($canonicals);
                }
            }
        }

        return $index;
    }

    /**
     * Cocokkan sel lokasi ke daftar StagingIn::LOCATIONS dengan toleran
     * terhadap cara pengetikan. Urutan pencocokan (berhenti di yang pertama
     * berhasil):
     *   1. kunci ternormalisasi sama persis
     *   2. sama tanpa memedulikan spasi/tanda baca
     *   3. singkatan inisial (mis. "TH2" -> "Temporary Hold 2")
     *   4. kata-kata yang diketik adalah bagian dari nama lokasi (atau
     *      sebaliknya) dengan angka yang sama, mis. "temporary hold 2" ->
     *      "Temporary Hold / Repair 2"
     *   5. typo ringan (selisih <= 2 karakter), HANYA bila angkanya persis
     *      sama dan kandidatnya tunggal — "Hold 1" tidak akan pernah
     *      dicocokkan ke "Hold 2".
     * Mengembalikan nilai kanonik (persis seperti di StagingIn::LOCATIONS),
     * atau null bila kosong / tidak ada yang cocok — baris tetap disimpan
     * tanpa lokasi, tidak pernah ditolak.
     */
    private function resolveLocation(?string $location): ?string
    {
        if ($location === null) {
            return null;
        }

        $this->locationIndex ??= $this->buildLocationIndex();
        $index = $this->locationIndex;

        $key = $this->normalizeLocationKey($location);

        if ($key === '') {
            return null;
        }

        $compact = str_replace(' ', '', $key);

        if (isset($index['full'][$key])) {
            return $index['full'][$key];
        }

        if (isset($index['compact'][$compact])) {
            return $index['compact'][$compact];
        }

        if (isset($index['initials'][$compact])) {
            return $index['initials'][$compact];
        }

        // Nama lokasi di daftar bisa lebih panjang dari yang diketik (mis.
        // "Temporary Hold / Repair 2" diketik "temporary hold 2" atau
        // "repair 2"), atau sebaliknya. Cocok bila kata-kata salah satu sisi
        // ada seluruhnya di sisi lain, ANGKA-nya persis sama, dan hanya ada
        // satu kandidat terbaik.
        $inputWords = explode(' ', $key);
        $inputDigits = array_values(array_filter($inputWords, 'ctype_digit'));
        $inputText = array_values(array_diff($inputWords, $inputDigits));

        // Perbaiki typo per kata ("temporari" -> "temporary") terhadap
        // kosakata nama lokasi; hanya bila kata itu belum dikenal dan
        // koreksinya tunggal.
        $vocabulary = array_unique(array_merge(...array_values($index['tokens'] ?: [[]])));

        $inputText = array_map(function (string $word) use ($vocabulary) {
            $length = mb_strlen($word, 'UTF-8');

            if ($length < 5 || in_array($word, $vocabulary, true)) {
                return $word;
            }

            $matches = array_filter(
                $vocabulary,
                fn ($v) => ! ctype_digit($v) && levenshtein($word, $v) <= 2
            );

            return count($matches) === 1 ? reset($matches) : $word;
        }, $inputText);

        if ($inputText !== []) {
            $best = null;
            $bestScore = 0;
            $tie = false;

            foreach ($index['tokens'] as $canonical => $words) {
                $digits = array_values(array_filter($words, 'ctype_digit'));

                if ($digits !== $inputDigits) {
                    continue;
                }

                $text = array_values(array_diff($words, $digits));
                $common = count(array_intersect($inputText, $text));

                $isSubset = $common === count(array_unique($inputText))
                    || $common === count(array_unique($text));

                if (! $isSubset || $common === 0) {
                    continue;
                }

                if ($common > $bestScore) {
                    $best = $canonical;
                    $bestScore = $common;
                    $tie = false;
                } elseif ($common === $bestScore) {
                    $tie = true;
                }
            }

            if ($best !== null && ! $tie) {
                return $best;
            }
        }

        // Typo ringan: angka harus sama persis, jarak edit kecil, dan
        // pemenangnya harus tunggal.
        if (mb_strlen($compact, 'UTF-8') >= 5) {
            preg_match_all('/\d+/', $compact, $m);
            $digits = $m[0];

            $best = null;
            $bestDistance = PHP_INT_MAX;
            $tie = false;

            foreach ($index['keys'] as $canonical => $candidate) {
                preg_match_all('/\d+/', $candidate, $cm);

                if ($cm[0] !== $digits) {
                    continue;
                }

                $distance = levenshtein($compact, $candidate);

                if ($distance < $bestDistance) {
                    $best = $canonical;
                    $bestDistance = $distance;
                    $tie = false;
                } elseif ($distance === $bestDistance) {
                    $tie = true;
                }
            }

            if ($best !== null && ! $tie && $bestDistance <= 2) {
                return $best;
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

            // Baris dengan lokasi "Outbound" / "Outbound Shipment" (apa pun cara pengetikannya)
            // tidak boleh disimpan ke database — lewati baris ini sepenuhnya.
            if ($rawLocation !== null && in_array(str_replace(' ', '', $this->normalizeLocationKey($rawLocation)), ['outbound', 'outboundshipment'], true)) {
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