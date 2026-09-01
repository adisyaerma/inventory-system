<?php

namespace App\Exports;

use App\Models\StagingOutHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export tabel staging out history ke Excel, menghormati filter yang sama
 * dengan yang dipakai StagingOutHistoryController::data() (status, event,
 * rentang tanggal, dan kata kunci pencarian).
 */
class StagingOutHistoryExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Request $request)
    {
    }

    public function query()
    {
        $query = StagingOutHistory::query()
            ->select('staging_out_histories.*')
            ->leftJoin('items', 'items.id', '=', 'staging_out_histories.item_id')
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
            ])
            ->orderByDesc('staging_out_histories.id');

        if ($this->request->filled('status')) {
            match ($this->request->status) {
                'aktif' => $query->where('staging_out_histories.is_active', true)
                    ->whereNull('staging_out_histories.delivery_date'),
                'selesai' => $query->where('staging_out_histories.is_active', true)
                    ->whereNotNull('staging_out_histories.delivery_date'),
                'dihapus' => $query->where('staging_out_histories.is_active', false),
                default => null,
            };
        }

        if ($this->request->filled('event')) {
            $query->whereHas('details', function ($q) {
                $q->where('event_type', $this->request->event);
            });
        }

        if ($this->request->filled('start_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '<=', $this->request->end_date);
        }

        if ($this->request->filled('search')) {
            $keyword = $this->request->search;

            $query->where(function ($q) use ($keyword) {
                $q->where('items.name', 'like', "%{$keyword}%")
                    ->orWhere('items.item_code_internal', 'like', "%{$keyword}%")
                    ->orWhere('staging_out_histories.so_number', 'like', "%{$keyword}%")
                    ->orWhere('staging_out_histories.customer', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'SO Number',
            'Customer',
            'Line Item',
            'Qty Awal',
            'Tgl Instruksi Kirim',
            'Tgl Picking',
            'DO Number',
            'Tgl Kirim',
            'Status',
        ];
    }

    public function map($row): array
    {
        $status = ! $row->is_active
            ? 'Dihapus'
            : ($row->delivery_date ? 'Selesai' : 'Aktif');

        return [
            $row->item_code,
            $row->item_name,
            $row->so_number,
            $row->customer,
            $row->line_item,
            $row->initial_qty,
            optional($row->delivery_instruction_date)->format('d M Y'),
            optional($row->picking_date)->format('d M Y'),
            $row->do_number,
            optional($row->delivery_date)->format('d M Y'),
            $status,
        ];
    }
}