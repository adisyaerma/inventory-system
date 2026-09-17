<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export laporan riwayat satu item — dirender dari Blade view
 * (item_history.export) supaya bisa gabungin info umum barang +
 * tabel timeline dalam satu file Excel yang rapi.
 */
class ItemHistoryExport implements FromView, ShouldAutoSize
{
    protected Item $item;

    protected $timeline;

    protected float $totalQty;

    public function __construct(Item $item, $timeline, float $totalQty)
    {
        $this->item = $item;
        $this->timeline = $timeline;
        $this->totalQty = $totalQty;
    }

    public function view(): View
    {
        return view('item_history.export', [
            'item' => $this->item,
            'timeline' => $this->timeline,
            'totalQty' => $this->totalQty,
        ]);
    }
}