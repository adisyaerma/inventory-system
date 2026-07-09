<?php

namespace App\Exports;

use App\Models\StockMutation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockMutationExport implements FromArray, WithEvents, WithHeadings
{
    protected array $mergeRanges = [];

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Name',
            'Date',
            'Transaction Type',
            'Transaction Number',
            'Description',
            'Qty In',
            'Qty Out',
            'Qty Balance',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $excelRow = 2;

        $query = StockMutation::with([
            'stock:id,item_code_internal,name',
            'location:id,location_name',
        ]);
        if ($this->request->filled('start_date')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $this->request->start_date
            );
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $this->request->end_date
            );
        }

        if ($this->request->filled('transaction_type')) {
            $query->where(
                'transaction_type',
                $this->request->transaction_type
            );
        }

        if ($this->request->filled('location_id')) {
            $locationId = $this->request->location_id;

            $query->whereHas('stock.locationStocks', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }
        $mutations = $query
            ->select([
                'id',
                'stock_id',
                'transaction_date',
                'transaction_type',
                'transaction_number',
                'description',
                'qty_in',
                'qty_out',
                'qty_balance',
            ])
            ->orderBy('stock_id')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $groups = $mutations->groupBy('stock_id');

        foreach ($groups as $group) {

            $first = true;
            $startRow = $excelRow;

            foreach ($group as $mutation) {

                $rows[] = [
                    $first ? $mutation->stock->item_code_internal : '',
                    $first ? $mutation->stock->name : '',
                    optional($mutation->transaction_date)->format('d/m/Y'),
                    $mutation->transaction_type,
                    $mutation->transaction_number,
                    $mutation->description,
                    $mutation->qty_in,
                    $mutation->qty_out,
                    $mutation->qty_balance,
                ];

                $first = false;
                $excelRow++;
            }

            $endRow = $excelRow - 1;

            if ($endRow> $startRow) {
                $this->mergeRanges[] = [$startRow, $endRow];
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                foreach ($this->mergeRanges as [$start, $end]) {

                    foreach (['A', 'B'] as $column) {

                        $sheet->mergeCells("{$column}{$start}:{$column}{$end}");

                        $alignment = $sheet
                            ->getStyle("{$column}{$start}:{$column}{$end}")
                            ->getAlignment();

                        $alignment->setVertical(Alignment::VERTICAL_CENTER);
                        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:I{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                // Alignment seluruh data
                $sheet->getStyle("A1:I{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Format angka
                $sheet->getStyle("G2:I{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                // Header
                $sheet->getStyle('A1:I1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Lebar kolom manual (lebih cepat daripada ShouldAutoSize)
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(12);
            },

        ];
    }
}
