<?php

namespace App\Support;

use App\Models\StockMutation;

class StockBaseline
{
    /**
     * ID mutasi terakhir untuk kombinasi item + lokasi + lot.
     *
     * Dipakai sebagai "garis batas" stock opname: mutasi dengan id
     * sampai angka ini dianggap riwayat lama dan tidak ikut dihitung
     * ke saldo. Kalau belum ada mutasi sama sekali, hasilnya 0.
     */
    public static function current($itemId, $locationId, $lot = null): int
    {
        return (int) StockMutation::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('lot', $lot)
            ->max('id');
    }
}