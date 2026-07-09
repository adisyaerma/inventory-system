<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockExport implements FromCollection, WithEvents, WithHeadings
{
    protected $locationId;

    public function __construct($locationId = null)
    {
        $this->locationId = $locationId;
    }

    public function collection()
    {
        $query = Stock::select([
            'id',
            'item_code_supplier',
            'item_code_internal',
            'item_code_customer',
            'name',
            'description',
        ])
            ->with([
                'locationStocks:id,stock_id,location_id,quantity',
                'locationStocks.location:id,location_name',
            ]);

        if ($this->locationId) {
            $query->whereHas('locationStocks', function ($q) {
                $q->where('location_id', $this->locationId);
            });
        }

        return $query->get()->map(function ($stock) {

            $locationStocks = $stock->locationStocks;

            if ($this->locationId) {
                $locationStocks = $locationStocks->where(
                    'location_id',
                    $this->locationId
                );
            }

            $locations = $locationStocks
                ->pluck('location.location_name')
                ->implode(', ');

            $quantities = $locationStocks
                ->pluck('quantity')
                ->map(function ($qty) {
                    return fmod((float) $qty, 1) == 0
                        ? (int) $qty
                        : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                })
                ->implode(', ');

            return [
                $stock->item_code_supplier,
                $stock->item_code_internal,
                $stock->item_code_customer,
                $locations,
                $stock->name,
                $quantities,
                $stock->description,
            ];
        });
    }

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
        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:G{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle('A1:G1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A1:G1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A1:G{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(50);
            },

        ];
    }
}
