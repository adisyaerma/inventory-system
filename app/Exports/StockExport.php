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
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
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
            'vendor_id',
        ])
            ->with([
                'locationStocks:id,stock_id,location_id,quantity',
                'locationStocks.location:id,location_name',
                'vendor:id,name',
            ]);

        if ($this->request->filled('location_id')) {
            $locationId = $this->request->location_id;

            $query->whereHas('locationStocks', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($this->request->filled('vendor_id')) {
            $query->where('vendor_id', $this->request->vendor_id);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_code_supplier', 'ilike', "%{$search}%")
                    ->orWhere('item_code_internal', 'ilike', "%{$search}%")
                    ->orWhere('item_code_customer', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('locationStocks.location', function ($lq) use ($search) {
                        $lq->where('location_name', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('vendor', function ($vq) use ($search) {
                        $vq->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        return $query->get()->map(function ($stock) {

            $locationStocks = $stock->locationStocks;

            if ($this->request->filled('location_id')) {
                $locationStocks = $locationStocks->where(
                    'location_id',
                    $this->request->location_id
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
                $stock->vendor ? $stock->vendor->name : '-',
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
            'Vendor',
        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:H1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                $sheet->getStyle("A1:H{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle('A1:H1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A1:H1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A1:H{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(50);
                $sheet->getColumnDimension('H')->setWidth(25);
            },

        ];
    }
}
