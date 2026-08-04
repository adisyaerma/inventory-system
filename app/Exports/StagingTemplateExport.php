<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StagingTemplateExport implements WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
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

    public function array():array
    {
        return[
            ['','','','','','','','','','',''],
        ];
    }

    public function registerEvents(): array
    {
        return[
            AfterSheet::class=>function(AfterSheet $event){
                foreach(['A','B','C','D','E','F','G','H','I','J','K'] as $column){
                    $event->sheet->getDelegate()
                        ->getStyle($column . '2:'.$column.'1048576')
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);
                }

                $event->sheet->getDelegate()
                    ->getStyle('G2:G1048576')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
            },
        ];
    }
}