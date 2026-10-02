<?php

namespace App\Services;

use App\Models\StagingIn;
use App\Models\StagingInHistory;
use App\Models\StagingInHistoryDetail;
use Illuminate\Support\Collection;

/**
 * Titik satu-satunya untuk menulis ke staging_in_histories /
 * staging_in_history_details. Semua method di StagingInController yang
 * mengubah data staging_in WAJIB memanggil salah satu method di sini,
 * di dalam DB::transaction() yang sama, supaya data utama dan history-nya
 * selalu konsisten.
 */
class StagingInHistoryService
{
    /**
     * Catat kejadian "created" — dipanggil sesaat setelah StagingIn::create().
     */
    public function logCreated(StagingIn $staging): StagingInHistoryDetail
    {
        $history = StagingInHistory::firstOrCreateForStaging($staging);

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
     */
    public function logUpdated(StagingIn $staging, array $changes): ?StagingInHistoryDetail
    {
        if (empty($changes)) {
            return null;
        }

        $history = StagingInHistory::firstOrCreateForStaging($staging);

        // Kolom location di header adalah snapshot lokasi TERKINI (bukan
        // hanya lokasi saat pertama dibuat), jadi setiap kali field
        // location berubah lewat event updated, ikut disinkronkan di sini.
        if (array_key_exists('location', $changes)) {
            $history->update(['location' => $changes['location']['new']]);
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
     * Catat kejadian "moved_to_stock".
     */
    public function logMovedToStock(StagingIn $staging, int $qtyMoved, int $remaining, array $meta): StagingInHistoryDetail
    {
        return $this->logMove($staging, 'moved_to_stock', $qtyMoved, $remaining, $meta);
    }

    /**
     * Catat kejadian "moved_to_staging_out".
     */
    public function logMovedToStagingOut(StagingIn $staging, int $qtyMoved, int $remaining, array $meta): StagingInHistoryDetail
    {
        return $this->logMove($staging, 'moved_to_staging_out', $qtyMoved, $remaining, $meta);
    }

    private function logMove(StagingIn $staging, string $eventType, int $qtyMoved, int $remaining, array $meta): StagingInHistoryDetail
    {
        $history = StagingInHistory::firstOrCreateForStaging($staging);

        $detail = $history->details()->create([
            'event_type' => $eventType,
            'qty_before' => $staging->qty,
            'qty_change' => $qtyMoved,
            'qty_after' => $remaining,
            'meta' => $meta,
            'performed_by' => auth()->id(),
        ]);

        // Kalau sisa qty habis, baris staging_in akan ikut dihapus oleh
        // controller setelah ini dipanggil — nonaktifkan header-nya juga
        // supaya jelas kalau data aslinya sudah tidak ada.
        if ($remaining <= 0) {
            $history->update(['is_active' => false]);
        }

        return $detail;
    }

    /**
     * Catat kejadian "deleted" (hapus satu baris lewat tombol hapus biasa).
     * $staging harus di-load beserta datanya SEBELUM benar-benar dihapus.
     */
    public function logDeleted(StagingIn $staging): StagingInHistoryDetail
    {
        $history = StagingInHistory::firstOrCreateForStaging($staging);
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
     * @param  Collection<int, StagingIn>  $stagings
     */
    public function logBulkDeleted($stagings): void
    {
        foreach ($stagings as $staging) {
            $history = StagingInHistory::firstOrCreateForStaging($staging);
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
     * Catat kejadian "reset_by_import" untuk semua baris staging_in yang
     * ada SEBELUM tabel di-truncate oleh proses import.
     *
     * @param  Collection<int, StagingIn>  $stagings
     */
    public function logResetByImport($stagings): void
    {
        foreach ($stagings as $staging) {
            $history = StagingInHistory::firstOrCreateForStaging($staging);
            $history->update(['is_active' => false]);

            $history->details()->create([
                'event_type' => 'reset_by_import',
                'qty_before' => $staging->qty,
                'qty_change' => $staging->qty,
                'qty_after' => 0,
                'notes' => 'Data dihapus otomatis karena import ulang seluruh data staging in.',
                'performed_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Helper untuk StagingInController::update() — bandingkan data lama vs
     * data tervalidasi yang baru, hasilkan array $changes siap pakai untuk
     * logUpdated().
     */
    public function diff(StagingIn $staging, array $validated): array
    {
        $changes = [];

        foreach ($validated as $field => $newValue) {
            $oldValue = $staging->getOriginal($field);

            if ($this->normalize($oldValue) !== $this->normalize($newValue)) {
                $changes[$field] = [
                    'old' => $oldValue instanceof \DateTimeInterface ? $oldValue->format('Y-m-d') : $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    private function normalize($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }
}
