<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    protected int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // urutan & filter sama dengan tabel di halaman Barang
        return Item::query()
            ->with('vendor')
            ->applyFilters($this->filters)
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'Item Code Internal',
            'Item Code Supplier',
            'Item Code Customer',
            'Nama Barang',
            'Vendor',
            'Incoterms',
            'Deskripsi',
        ];
    }

    public function map($item): array
    {
        return [
            ++$this->no,
            $item->item_code_internal,
            $item->item_code_supplier,
            $item->item_code_customer,
            $item->name,
            $item->vendor?->name,
            $item->incoterms,
            $item->description,
        ];
    }
}