<?php

namespace App\Exports;

use App\Models\Staging;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StagingExport implements FromArray, WithEvents, WithHeadings
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
            'Kode Barang',
            'Nama Barang',
            'Supplier',
            'Owner',
            'Tanggal Kedatangan',
            'Qty',
            'Lokasi',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        $rows = [];

        $query = Staging::query();

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
                    ->orWhere('notes', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(qty AS TEXT) ilike ?', ["%{$search}%"]);
            });
        }

        $stagings = $query->latest()->get();

        foreach ($stagings as $staging) {
            $rows[] = [
                $staging->po_number,
                $staging->item_code,
                $staging->item_name,
                $staging->supplier_origin,
                $staging->item_owner,
                optional($staging->arrival_date)->format('d/m/Y'),
                $staging->qty,
                $staging->location,
                $staging->notes,
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

                $sheet->getStyle("A1:I{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle("A1:I{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("G2:G{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getStyle('A1:I1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(28);
                $sheet->getColumnDimension('I')->setWidth(35);
            },

        ];
    }
}