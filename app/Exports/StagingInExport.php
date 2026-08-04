<?php

namespace App\Exports;

use App\Models\StagingIn;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StagingInExport implements FromArray, WithEvents, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'No. PO',
            'Tanggal Kedatangan',
            'Supplier',
            'Owner',
            'Incoterms',
            'Kode Barang',
            'Nama Barang',
            'Qty',
            'Lokasi',
            'Keterangan',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = [];

        $query = StagingIn::query();

        if ($this->request->filled('location')) {
            $query->where('location', $this->request->location);
        }

        if ($this->request->filled('start_date')) {
            $query->whereDate('arrival_date', '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate('arrival_date', '<=', $this->request->end_date);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                    ->orWhere('item_code', 'ilike', "%{$search}%")
                    ->orWhere('item_name', 'ilike', "%{$search}%")
                    ->orWhere('supplier_origin', 'ilike', "%{$search}%")
                    ->orWhere('item_owner', 'ilike', "%{$search}%")
                    ->orWhere('location', 'ilike', "%{$search}%")
                    ->orWhere('incoterms', 'ilike', "%{$search}%")
                    ->orWhere('notes', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(qty AS TEXT) ilike ?', ["%{$search}%"]);
            });
        }

        $stagings = $query->latest()->get();

        foreach ($stagings as $staging) {
            $rows[] = [
                $staging->po_number,
                optional($staging->arrival_date)->format('d/m/Y'),
                $staging->supplier_origin,
                $staging->item_owner,
                $staging->incoterms,
                $staging->item_code,
                $staging->item_name,
                $staging->qty,
                $staging->location,
                $staging->notes,
                $staging->status,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:K{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle("A1:K{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("H2:H{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getStyle('A1:K1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(20); // No. PO
                $sheet->getColumnDimension('B')->setWidth(18); // Tanggal Kedatangan
                $sheet->getColumnDimension('C')->setWidth(25); // Supplier
                $sheet->getColumnDimension('D')->setWidth(20); // Owner
                $sheet->getColumnDimension('E')->setWidth(15); // Incoterms
                $sheet->getColumnDimension('F')->setWidth(18); // Kode Barang
                $sheet->getColumnDimension('G')->setWidth(35); // Nama Barang
                $sheet->getColumnDimension('H')->setWidth(10); // Qty
                $sheet->getColumnDimension('I')->setWidth(28); // Lokasi
                $sheet->getColumnDimension('J')->setWidth(35); // Keterangan
                $sheet->getColumnDimension('K')->setWidth(20); // Status
            },

        ];
    }
}