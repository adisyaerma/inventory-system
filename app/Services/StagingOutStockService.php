<?php

namespace App\Services;

use App\Models\LocationStock;
use App\Models\StagingOut;
use App\Models\StockMutation;

/**
 * Logika pembatalan transaksi stok untuk staging out yang barangnya diambil
 * dari stok (source_type = stock).
 *
 * Dipakai bersama oleh StagingOutController (hapus / bulk hapus / ubah /
 * import reset) dan StagingOutHistoryController (hapus history), supaya
 * aturan "batalkan transaksi" persis sama di semua tempat:
 *   1. qty dikembalikan ke location_stocks, dan
 *   2. baris stock_mutations (mutasi keluar) yang tadinya dibuat saat
 *      staging out dibuat IKUT DIHAPUS -- bukan dibuatkan mutasi pembalik.
 *
 * Semua method di sini HARUS dipanggil di dalam DB::transaction() karena
 * memakai lockForUpdate().
 */
class StagingOutStockService
{
    /**
     * Teks description untuk baris stock_mutations: "<no SO> - <customer>".
     * Bagian yang kosong dilewati; kalau dua-duanya kosong dipakai
     * "Staging Out" saja.
     */
    public function description(StagingOut $stagingOut): string
    {
        $parts = array_filter([
            trim((string) $stagingOut->so_number),
            trim((string) $stagingOut->customer),
        ], fn ($part) => $part !== '');

        return $parts ? implode(' - ', $parts) : 'Staging Out';
    }

    /**
     * Apakah baris ini punya transaksi stok yang bisa dibatalkan.
     */
    public function isStockSourced(StagingOut $stagingOut): bool
    {
        return $stagingOut->source_type === 'stock'
            && $stagingOut->item_id
            && $stagingOut->location_id;
    }

    /**
     * Batalkan transaksi stok: kembalikan qty ke location_stock dan hapus
     * baris stock_mutations yang dibuat saat staging out ini dibuat.
     */
    public function reverse(StagingOut $stagingOut): void
    {
        if (! $this->isStockSourced($stagingOut)) {
            return;
        }

        $stock = $this->lockLocationStock(
            (int) $stagingOut->item_id,
            (int) $stagingOut->location_id,
            $stagingOut->lot ?: null
        );

        if (! $stock) {
            $stock = LocationStock::create([
                'item_id' => $stagingOut->item_id,
                'location_id' => $stagingOut->location_id,
                'lot' => $stagingOut->lot,
                'opening_balance' => 0,
                'quantity' => 0,
            ]);
        }

        $stock->quantity = (float) $stock->quantity + (float) $stagingOut->qty;
        $stock->save();

        $this->deleteMutation($stagingOut);
    }

    /**
     * Ambil baris location_stock untuk kombinasi item/lokasi/lot, dikunci
     * (lockForUpdate) supaya aman dari race condition.
     */
    protected function lockLocationStock(int $itemId, int $locationId, ?string $lot)
    {
        return LocationStock::query()
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('lot', $lot)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Cari & hapus baris stock_mutations yang dibuat saat staging out ini
     * mengurangi stok.
     *
     * Baris yang tepat dicari lewat kombinasi item/lokasi/lot + description
     * (no SO - customer) + qty_out, lalu HANYA satu baris terbaru yang
     * dihapus (kalau ada beberapa baris identik, yang terhapus cukup satu,
     * sesuai jumlah staging out-nya).
     *
     * Baris lama (atau hasil import) masih berformat
     * "Staging Out #<id> ...", jadi dicek dulu lewat penanda lama itu --
     * wajib diikuti spasi/akhir teks supaya "#123" tidak ikut kena kalau
     * yang dicari "#1234".
     */
    protected function deleteMutation(StagingOut $stagingOut): void
    {
        $base = fn () => StockMutation::where('item_id', $stagingOut->item_id)
            ->where('location_id', $stagingOut->location_id)
            ->where('lot', $stagingOut->lot);

        // 1. Format lama: "Staging Out #<id> ..."
        $marker = 'Staging Out #'.$stagingOut->id;

        $deleted = $base()
            ->where(function ($q) use ($marker) {
                $q->where('description', $marker)
                    ->orWhere('description', 'like', $marker.' %');
            })
            ->delete();

        if ($deleted > 0) {
            return;
        }

        // 2. Format baru: "<no SO> - <customer>". PostgreSQL tidak
        //    mendukung DELETE ... LIMIT, jadi ambil id-nya dulu.
        $mutationId = $base()
            ->where('description', $this->description($stagingOut))
            ->where('qty_out', $stagingOut->qty)
            ->orderByDesc('id')
            ->value('id');

        if ($mutationId) {
            StockMutation::whereKey($mutationId)->delete();
        }
    }
}