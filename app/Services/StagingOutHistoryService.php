<?php

namespace App\Services;

use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StagingOutHistoryDetail;

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
     */
    private const HEADER_SYNC_FIELDS = ['picking_date', 'do_number', 'delivery_date'];

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
            'performed_by' => auth()->id(),
        ]);
    }

    /**
     * Catat kejadian "updated". $changes berisi field yang benar-benar
     * berubah saja, format: ['field' => ['old' => ..., 'new' => ...]].
     *
     * Kalau salah satu field yang berubah adalah picking_date, do_number,
     * atau delivery_date, kolom status terkini di header ikut disinkronkan
     * — supaya header selalu mencerminkan kondisi terbaru walau field itu
     * diedit lewat form biasa, bukan lewat tombol konfirmasi picking/kirim.
     */
    public function logUpdated(StagingOut $staging, array $changes): ?StagingOutHistoryDetail
    {
        if (empty($changes)) {
            return null;
        }

        $history = StagingOutHistory::firstOrCreateForStaging($staging);

        $headerSync = array_intersect_key(
            array_map(fn ($change) => $change['new'] ?? null, $changes),
            array_flip(self::HEADER_SYNC_FIELDS)
        );

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
                'delivery_date' => (string) $deliveryDate,
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
     * Helper untuk StagingOutController::update() — bandingkan data lama vs
     * data tervalidasi yang baru, hasilkan array $changes siap pakai untuk
     * logUpdated().
     */
    public function diff(StagingOut $staging, array $validated): array
    {
        $changes = [];

        foreach ($validated as $field => $newValue) {
            $oldValue = $staging->getOriginal($field);

            // Bandingkan sebagai string supaya date/casted value tidak
            // dianggap "berubah" gara-gara beda tipe.
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}