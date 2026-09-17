<?php

namespace App\Exports;

use App\Models\StagingOut;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StagingOutExport implements FromArray, WithEvents, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

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
            'Tgl Resi Pengiriman',];
    }

    public function array(): array
    {
        $rows = [];

        $query = StagingOut::query()->with('item');

        if ($this->request->status === 'belum_picking') {
            $query->whereNull('picking_date');
        } elseif ($this->request->status === 'sudah_picking') {
            $query->whereNotNull('picking_date')->whereNull('delivery_date');
        } elseif ($this->request->status === 'sudah_dikirim') {
            $query->whereNotNull('delivery_date');
        }

        if ($this->request->filled('customer')) {
            $query->where('customer', $this->request->customer);
        }

        $dateColumn = in_array($this->request->date_type, ['delivery_instruction_date', 'picking_date', 'delivery_date'])
            ? $this->request->date_type
            : 'delivery_instruction_date';

        if ($this->request->filled('start_date')) {
            $query->whereDate($dateColumn, '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate($dateColumn, '<=', $this->request->end_date);
        }

        if ($this->request->overdue == 1) {
            $query->whereDate('delivery_instruction_date', '<', now())->whereNull('delivery_date');
        }

        if ($this->request->filled('staging_location')) {
            if ($this->request->staging_location === 'belum_diisi') {
                $query->whereNull('staging_outs.staging_location');
            } elseif (in_array($this->request->staging_location, ['staging', 'packing', 'outbound'])) {
                $query->where('staging_outs.staging_location', $this->request->staging_location);
            }
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'ilike', "%{$search}%")
                    ->orWhere('customer', 'ilike', "%{$search}%")
                    ->orWhere('line_item', 'ilike', "%{$search}%")
                    ->orWhere('do_number', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(qty AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhereHas('item', function ($q2) use ($search) {
                        $q2->where('item_code_internal', 'ilike', "%{$search}%")
                            ->orWhere('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $stagings = $query->latest()->get();

        foreach ($stagings as $staging) {
            $rows[] = [
                $staging->so_number,
                $staging->customer,
                optional($staging->item)->item_code_internal,
                $staging->line_item,
                $staging->qty,
                optional($staging->delivery_instruction_date)->format('d/m/Y'),
                optional($staging->picking_date)->format('d/m/Y'),
                $staging->do_number,
                optional($staging->delivery_receipt_date)->format('d/m/Y'),
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

                 $sheet->getStyle('A1:I1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

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

                $sheet->getStyle("E2:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getStyle('A1:I1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(20); // No. SO
                $sheet->getColumnDimension('B')->setWidth(25); // Customer
                $sheet->getColumnDimension('C')->setWidth(18); // Kode Barang
                $sheet->getColumnDimension('D')->setWidth(35); // Line Item
                $sheet->getColumnDimension('E')->setWidth(10); // Qty
                $sheet->getColumnDimension('F')->setWidth(20); // Tgl Instruksi Kirim
                $sheet->getColumnDimension('G')->setWidth(18); // Tgl Picking
                $sheet->getColumnDimension('H')->setWidth(20); // No. DO
                $sheet->getColumnDimension('I')->setWidth(18); // Tgl Kirim
            },

        ];
    }
}
