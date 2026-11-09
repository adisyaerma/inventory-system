<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Saldo TOTAL satu barang (semua lokasi + lot digabung) setelah setiap mutasi.
 *
 * Hanya untuk TAMPILAN, tidak disimpan di database. qty_balance di
 * stock_mutations tetap saldo per lokasi + lot (dasar stok & stock opname).
 *
 * Aturan hitung (selaras dengan recalculateLocationStock):
 *  - mulai dari jumlah opening_balance semua lokasi/lot milik barang itu,
 *  - mutasi dengan id <= baseline_mutation_id lokasi/lot-nya (riwayat sebelum
 *    stock opname) TIDAK ikut dihitung,
 *  - diurutkan transaction_date lalu id, sama seperti urutan hitung saldo.
 * Hasil baris terakhir = total stok barang saat ini.
 */
class StockTotalBalance
{
    /** @var array<int, array<int, array{total: float, effective: bool}>> */
    protected static array $cache = [];

    /**
     * @return array<int, array{total: float, effective: bool}> dikunci id mutasi
     */
    public static function forItem(int $itemId): array
    {
        if (isset(static::$cache[$itemId])) {
            return static::$cache[$itemId];
        }

        $running = (float) DB::table('location_stock')
            ->where('item_id', $itemId)
            ->sum('opening_balance');

        $rows = DB::table('stock_mutations as m')
            ->leftJoin('location_stock as ls', function ($join) {
                $join->on('ls.item_id', '=', 'm.item_id')
                    ->on('ls.location_id', '=', 'm.location_id')
                    ->whereRaw('(ls.lot = m.lot OR (ls.lot IS NULL AND m.lot IS NULL))');
            })
            ->where('m.item_id', $itemId)
            ->orderBy('m.transaction_date')
            ->orderBy('m.id')
            ->get([
                'm.id',
                'm.qty_in',
                'm.qty_out',
                DB::raw('COALESCE(ls.baseline_mutation_id, 0) as baseline'),
            ]);

        $result = [];

        foreach ($rows as $row) {
            $effective = (int) $row->id > (int) $row->baseline;

            if ($effective) {
                $running += (float) $row->qty_in - (float) $row->qty_out;
            }

            $result[(int) $row->id] = [
                'total' => $running,
                'effective' => $effective,
            ];
        }

        return static::$cache[$itemId] = $result;
    }
}