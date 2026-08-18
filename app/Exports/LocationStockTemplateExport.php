<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LocationStockTemplateExport implements withHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'Item Code Supplier',
            'Item Code Internal',
            'Item Code Customer',
            'Location',
            'Name',
            'Quantity',
            'Description',
            'Vendor',
        ];
    }

    public function array():array
    {
        return[
            ['','','','','','','',''],
        ];
    }

    public function registerEvents(): array
    {
        return[
            AfterSheet::class=>function(AfterSheet $event){
                foreach(['A','B','C','D','E','G','H'] as $column){
                    $event->sheet->getDelegate()
                        ->getStyle($column . '2:'.$column.'1048576')
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);
                }

                $event->sheet->getDelegate()
                    ->getStyle('F2:F1048576')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
            },
        ];
    }
}