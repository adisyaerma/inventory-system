<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StagingOutTemplateExport implements WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'No. SO',
            'Customer',
            'Kode Barang',
            'Line Item',
            'Qty',
            'Tgl Instruksi Kirim',
            'Tgl Picking',
            'No. DO',
            'Tgl Kirim',
        ];
    }

    public function array():array
    {
        return[
            ['','','','','','','','',''],
        ];
    }

    public function registerEvents(): array
    {
        return[
            AfterSheet::class=>function(AfterSheet $event){
                foreach(['A','B','C','D','E','F','G','H','I'] as $column){
                    $event->sheet->getDelegate()
                        ->getStyle($column . '2:'.$column.'1048576')
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);
                }

                $event->sheet->getDelegate()
                    ->getStyle('D2:D1048576')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
            },
        ];
    }
}