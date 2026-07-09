<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockMutationTemplateExport implements WithHeadings
{
    /**
     * @return Collection
     */
    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Tanggal',
            'Tipe Transaksi',
            'Nomor',
            'Deskripsi',
            'Qty Masuk',
            'Qty Keluar',
            'Qty Balance'
        ];
    }
}
