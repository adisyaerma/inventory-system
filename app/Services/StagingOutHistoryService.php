<?php

namespace App\Services;

use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StagingOutHistoryDetail;
use Illuminate\Support\Facades\DB;

/**
 * Titik satu-satunya untuk menulis ke staging_out_histories /
 * staging_out_history_details. Semua method di StagingOutController yang
 * mengubah data staging_out WAJIB memanggil salah satu method di sini,
 * di dalam DB::transaction() yang sama, supaya data utama dan history-nya
 * selalu konsisten.
 *
 * Beda dengan StagingInHistoryService: tidak ada logMove() karena
 * staging_out tidak pernah berpindah ke mana pun. Sebagai gantinya ada
 * logPickingConfirmed() dan logDelivered() yang menandai progres
 * pengiriman, dan keduanya (beserta logUpdated()) menyinkronkan kolom
 * status terkini (picking_date / do_number / delivery_date) di header.
 */
class StagingOutHistoryService
{
    /**
     * Field status terkini yang disimpan langsung di header, disinkronkan
     * setiap kali muncul di $changes pada logUpdated().
     *
     * Format: 'kolom di staging_outs' => 'kolom di header history'.
     * Kolom staging_outs.delivery_date sudah diganti delivery_receipt_date
     * (tgl resi pengiriman), sedangkan header history tetap menyimpannya
     * di kolom delivery_date -- yang dibaca StagingOutHistoryController
     * untuk menentukan status "Terkirim/Selesai".
     */
    private const HEADER_SYNC_MAP = [
        'picking_date' => 'picking_date',
        'do_number' => 'do_number',
        'delivery_receipt_date' => 'delivery_date',
    ];

    /**
     * Catat kejadian "created" — dipanggil sesaat setelah StagingOut::create().
     */
    public function logCreated(StagingOut $staging): StagingOutHistoryDetail
    {
        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        return $history->details()->create([
            'event_type' => 'created',
            'qty_before' => 0,
            'qty_change' => $staging->qty,
            'qty_after' => $staging->qty,
            // Snapshot asal barang (eksternal / stok + lokasi + lot), supaya
            // halaman history tetap bisa menampilkan "diambil dari lokasi
            // mana" walau baris staging_out aslinya sudah dihapus.
            'meta' => $this->sourceMeta($staging),
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Snapshot asal barang untuk disimpan di meta event "created".
     * Format stok : ['source_type' => 'stock', 'location_id', 'location_name', 'lot']
     * Format lain : ['source_type' => 'external']
     */
    public function sourceMeta(StagingOut $staging): array
    {
        if ($staging->source_type !== 'stock') {
            return ['source_type' => 'external'];
        }

        return [
            'source_type' => 'stock',
            'location_id' => $staging->location_id,
            'location_name' => $this->locationName($staging->location_id),
            'lot' => $staging->lot ?: null,
        ];
    }

    /**
     * Nama lokasi dari tabel locations (null kalau id kosong / tidak ada).
     */
    public function locationName($locationId): ?string
    {
        if (! $locationId) {
            return null;
        }

        return DB::table('locations')->where('id', $locationId)->value('location_name');
    }

    /**
     * Catat kejadian "updated". $changes berisi field yang benar-benar
     * berubah saja, format: ['field' => ['old' => ..., 'new' => ...]].
     *
     * Kalau salah satu field yang berubah adalah picking_date, do_number,
     * atau delivery_receipt_date, kolom status terkini di header ikut disinkronkan
     * — supaya header selalu mencerminkan kondisi terbaru walau field itu
     * diedit lewat form biasa, bukan lewat tombol konfirmasi picking/kirim.
     */
    public function logUpdated(StagingOut $staging, array $changes): ?StagingOutHistoryDetail
    {
        if (empty($changes)) {
            return null;
        }

        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        $headerSync = [];

        foreach (self::HEADER_SYNC_MAP as $stagingField => $headerColumn) {
            if (array_key_exists($stagingField, $changes)) {
                $headerSync[$headerColumn] = $changes[$stagingField]['new'] ?? null;
            }
        }

        if (! empty($headerSync)) {
            $history->update($headerSync);
        }

        return $history->details()->create([
            'event_type' => 'updated',
            'qty_before' => $changes['qty']['old'] ?? $staging->qty,
            'qty_after' => $changes['qty']['new'] ?? $staging->qty,
            'meta' => $changes,
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "picking_confirmed" — dipanggil saat picking_date
     * diisi/dikonfirmasi lewat tombol konfirmasi picking (bukan lewat
     * form edit biasa).
     */
    public function logPickingConfirmed(StagingOut $staging, $pickingDate): StagingOutHistoryDetail
    {
        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        $history->update(['picking_date' => $pickingDate]);

        return $history->details()->create([
            'event_type' => 'picking_confirmed',
            'meta' => ['picking_date' => (string) $pickingDate],
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "delivered" — dipanggil saat do_number & delivery_date
     * diisi/dikonfirmasi lewat tombol konfirmasi kirim.
     */
    public function logDelivered(StagingOut $staging, string $doNumber, $deliveryDate): StagingOutHistoryDetail
    {
        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        $history->update([
            'do_number' => $doNumber,
            'delivery_date' => $deliveryDate,
        ]);

        return $history->details()->create([
            'event_type' => 'delivered',
            'meta' => [
                'do_number' => $doNumber,
                'delivery_date' => $this->toDateString($deliveryDate),
            ],
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "deleted" (hapus satu baris lewat tombol hapus biasa).
     * $staging harus di-load beserta datanya SEBELUM benar-benar dihapus.
     */
    public function logDeleted(StagingOut $staging): StagingOutHistoryDetail
    {
        $history = StagingOutHistory::firstOrCreateForStaging($staging);
        $history->update(['is_active' => false]);

        return $history->details()->create([
            'event_type' => 'deleted',
            'qty_before' => $staging->qty,
            'qty_change' => $staging->qty,
            'qty_after' => 0,
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "delivered" SEKALIGUS menandai record sudah selesai
     * (auto-archive). Dipakai di dua tempat:
     *  - import Excel (StagingOutImport & StagingOutStockImport) yang
     *    barisnya SUDAH punya tgl resi pengiriman terisi sejak awal, jadi
     *    baris staging_out yang bersangkutan TIDAK PERNAH sempat aktif di
     *    tabel utama: begitu dibuat, langsung dihapus lagi.
     *  - StagingOutController::update() saat tgl resi pengiriman baru saja
     *    diisi (tombol "Konfirmasi Kirim" / form edit): baris dipindahkan
     *    ke history dan dihapus dari tabel aktif, tapi statusnya harus
     *    tetap "Selesai", bukan "Dihapus".
     *
     * Beda dengan logDelivered() biasa (dipakai tombol "konfirmasi kirim"
     * manual di StagingOutController, barisnya TETAP ada & aktif di
     * staging_outs): di sini is_active langsung di-set false, karena
     * baris aslinya memang akan langsung dihapus sesudah ini dipanggil.
     *
     * SENGAJA tidak menyusul dengan logDeleted() — status "selesai
     * terkirim" harus tetap kebaca sebagai "Terkirim"/"Selesai" di UI
     * (lihat StagingOutHistoryController::shippingStatusMarkup() &
     * statusMeta(), keduanya cek delivery_date LEBIH DULU sebelum
     * is_active), bukan "Dihapus" — walau baris staging_out aslinya
     * memang ikut hilang dari tabel aktif.
     */
    public function logAutoDelivered(StagingOut $staging, ?string $doNumber, $deliveryDate): StagingOutHistoryDetail
    {
        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        $history->update([
            'do_number' => $doNumber,
            'delivery_date' => $deliveryDate,
            'is_active' => false,
        ]);

        return $history->details()->create([
            'event_type' => 'delivered',
            'meta' => [
                'do_number' => $doNumber,
                'delivery_date' => $this->toDateString($deliveryDate),
            ],
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "bulk_deleted" untuk banyak baris sekaligus.
     * $stagings harus koleksi yang SUDAH di-load sebelum di-destroy.
     *
     * @param  \Illuminate\Support\Collection<int, StagingOut>  $stagings
     */
    public function logBulkDeleted($stagings): void
    {
        foreach ($stagings as $staging) {
            $history = StagingOutHistory::firstOrCreateForStaging($staging);
            $history->update(['is_active' => false]);

            $history->details()->create([
                'event_type' => 'bulk_deleted',
                'qty_before' => $staging->qty,
                'qty_change' => $staging->qty,
                'qty_after' => 0,
                'performed_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Catat kejadian "reset_by_import" untuk semua baris staging_out yang
     * ada SEBELUM tabel di-truncate oleh proses import.
     *
     * @param  \Illuminate\Support\Collection<int, StagingOut>  $stagings
     */
    public function logResetByImport($stagings): void
    {
        foreach ($stagings as $staging) {
            $history = StagingOutHistory::firstOrCreateForStaging($staging);
            $history->update(['is_active' => false]);

            $history->details()->create([
                'event_type' => 'reset_by_import',
                'qty_before' => $staging->qty,
                'qty_change' => $staging->qty,
                'qty_after' => 0,
                'notes' => 'Data dihapus otomatis karena import ulang seluruh data staging out.',
                'performed_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Ubah tanggal (Carbon/DateTime/string) jadi string Y-m-d untuk meta.
     */
    private function toDateString($value): string
    {
        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : (string) $value;
    }

    /**
     * Helper untuk StagingOutController::update() — bandingkan data lama vs
     * data tervalidasi yang baru, hasilkan array $changes siap pakai untuk
     * logUpdated().
     */
    public function diff(StagingOut $staging, array $validated): array
    {
        $changes = [];

        foreach ($validated as $field => $newValue) {
            $oldValue = $staging->getOriginal($field);

            // Bandingkan dalam bentuk yang sudah dinormalisasi. Kolom tanggal
            // di-cast jadi Carbon, jadi (string) $oldValue menghasilkan
            // "2026-09-20 00:00:00" sedangkan input form "2026-09-20" —
            // kalau dibandingkan mentah, kolom yang tidak diubah pun
            // dianggap "berubah".
            $old = $this->normalizeForDiff($oldValue);
            $new = $this->normalizeForDiff($newValue);

            if ($old !== $new) {
                $changes[$field] = [
                    'old' => $old === '' ? null : $old,
                    'new' => $new === '' ? null : $new,
                ];
            }
        }

        return $changes;
    }

    /**
     * Samakan bentuk nilai sebelum dibandingkan: null/'' jadi '', tanggal
     * (Carbon/DateTime atau string tanggal-jam) jadi Y-m-d kalau jamnya
     * 00:00:00, lainnya jadi string biasa.
     */
    private function normalizeForDiff($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s') === '00:00:00'
                ? $value->format('Y-m-d')
                : $value->format('Y-m-d H:i:s');
        }

        $string = trim((string) $value);

        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]00:00:00(\.0+)?(Z|[+-]00:?00)?$/', $string, $m)) {
            return $m[1];
        }

        return $string;
    }
}