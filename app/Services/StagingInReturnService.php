<?php

namespace App\Services;

use App\Models\StagingIn;
use App\Models\StagingInHistory;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;

/**
 * Menjaga qty Staging In tetap sinkron dengan baris Staging Out yang
 * asalnya dari "Pindahkan ke Staging Out".
 *
 * - link()     : dipanggil saat pindah, mencatat asal + snapshot staging in.
 * - giveBack() : staging out dihapus -> qty dikembalikan ke staging in.
 * - syncQty()  : qty staging out diedit -> staging in ikut menyesuaikan.
 *
 * Kalau baris staging in-nya sudah terhapus (qty-nya habis dipindah),
 * baris dibuat ulang dari snapshot.
 */
class StagingInReturnService
{
    public function __construct(protected StagingInHistoryService $history)
    {
    }

    public function link(StagingOut $out, StagingIn $in): void
    {
        // Assign langsung (bukan mass assignment) supaya tidak perlu ubah $fillable.
        $out->staging_in_id = $in->id;
        $out->staging_in_snapshot = json_encode([
            'po_number' => $in->po_number,
            'item_id' => $in->item_id,
            'arrival_date' => optional($in->arrival_date)->format('Y-m-d'),
            'supplier_origin' => $in->supplier_origin,
            'incoterms' => $in->incoterms,
            'status' => $in->status,
            'location' => $in->location,
            'notes' => $in->notes,
        ]);
        $out->save();

        $this->recordOnHistory($out);
    }

    /**
     * Catat asal Staging In di meta event "created" history staging out,
     * supaya sambungan ini bisa dipulihkan setelah baris staging out-nya
     * selesai/terhapus. (Kalau history baru dibuat SETELAH link(), meta-nya
     * sudah terisi oleh StagingOutHistoryService::sourceMeta().)
     */
    protected function recordOnHistory(StagingOut $out): void
    {
        $history = StagingOutHistory::where('staging_out_id', $out->id)->first();

        $created = $history
            ? $history->details()->reorder()->where('event_type', 'created')->orderBy('id')->first()
            : null;

        if (! $created) {
            return;
        }

        $meta = is_string($created->meta) ? json_decode($created->meta, true) : $created->meta;
        $meta = is_array($meta) ? $meta : [];

        $meta['staging_in_id'] = (int) $out->staging_in_id;
        $meta['staging_in_snapshot'] = json_decode((string) $out->staging_in_snapshot, true);

        $created->meta = $meta;
        $created->save();
    }

    /** Kembalikan $qty ke staging in asal. */
    public function giveBack(StagingOut $out, int $qty): void
    {
        if (! $out->staging_in_id || $qty <= 0) {
            return;
        }

        $in = StagingIn::lockForUpdate()->find($out->staging_in_id);

        if ($in) {
            $changes = $this->history->diff($in, ['qty' => (int) $in->qty + $qty]);
            $in->update(['qty' => (int) $in->qty + $qty]);
            $this->history->logUpdated($in, $changes);

            return;
        }

        $snapshot = json_decode((string) $out->staging_in_snapshot, true);

        if (! is_array($snapshot)) {
            throw new \RuntimeException('Data staging in asal tidak ditemukan dan tidak bisa dibuat ulang.');
        }

        $oldInId = $out->staging_in_id;

        $in = StagingIn::create($snapshot + ['qty' => $qty]);

        $oldHistory = StagingInHistory::where('staging_in_id', $oldInId)->first();

        if ($oldHistory) {
            // Pakai record history lama: arahkan ke baris baru & aktifkan lagi.
            $oldHistory->update(['staging_in_id' => $in->id, 'is_active' => true]);

            $this->history->logUpdated($in, [
                'qty' => ['old' => 0, 'new' => $qty],
            ]);
        } else {
            $this->history->logCreated($in);
        }

        // Semua staging out lain yang berasal dari baris lama ikut diarahkan
        // ke baris baru, supaya tidak membuat baris staging in dobel.
        StagingOut::where('staging_in_id', $out->staging_in_id)
            ->update(['staging_in_id' => $in->id]);

        $out->staging_in_id = $in->id;
    }

    /** Ambil tambahan $qty dari staging in asal (qty staging out dinaikkan). */
    protected function takeMore(StagingOut $out, int $qty): void
    {
        $in = StagingIn::lockForUpdate()->find($out->staging_in_id);
        $remaining = $in ? (int) $in->qty : 0;

        if ($qty > $remaining) {
            throw new \RuntimeException(
                "Qty melebihi sisa barang di staging in. Sisa di staging in: {$remaining}, tambahan yang dibutuhkan: {$qty}."
            );
        }

        if ($qty === $remaining) {
            $this->history->logDeleted($in);
            $in->delete();

            return;
        }

        $changes = $this->history->diff($in, ['qty' => $remaining - $qty]);
        $in->update(['qty' => $remaining - $qty]);
        $this->history->logUpdated($in, $changes);
    }

    /** Samakan staging in dengan perubahan qty staging out ($old -> $new). */
    public function syncQty(StagingOut $out, int $old, int $new): void
    {
        if (! $out->staging_in_id || $old === $new) {
            return;
        }

        $new > $old
            ? $this->takeMore($out, $new - $old)
            : $this->giveBack($out, $old - $new);
    }
}