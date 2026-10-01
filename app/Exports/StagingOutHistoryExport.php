<?php

namespace App\Exports;

use App\Models\StagingOutHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Subquery event TERAKHIR per history -- sama persis dengan
        // StagingOutHistoryController::baseQuery(), supaya filter "event"
        // berarti "event terakhir" (bukan "pernah punya event ini").
        $latestDetail = DB::table('staging_out_history_details as d')
            ->select('d.staging_out_history_id', 'd.event_type')
            ->whereRaw('d.id = (
                select max(d2.id) from staging_out_history_details d2
                where d2.staging_out_history_id = d.staging_out_history_id
            )');

        $query = StagingOutHistory::query()
            ->select('staging_out_histories.*')
            ->leftJoin('items', 'items.id', '=', 'staging_out_histories.item_id')
            ->leftJoinSub($latestDetail, 'ld', function ($join) {
                $join->on('ld.staging_out_history_id', '=', 'staging_out_histories.id');
            })
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
            ])
            ->orderByDesc('staging_out_histories.id');

        self::applyFilters($query, $this->request);
        self::applySearch($query, $this->request->input('search'));

        return $query;
    }

    /**
     * SATU-SATUNYA tempat definisi filter status / event / rentang tanggal.
     * Dipanggil oleh export ini DAN oleh StagingOutHistoryController::data(),
     * sehingga tabel & file Excel dijamin memakai logika yang sama.
     *
     * Query wajib sudah di-join ke `items` dan subquery `ld`
     * (lihat baseQuery() di controller / query() di atas).
     */
    public static function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            match ($request->status) {
                'aktif' => $query->where('staging_out_histories.is_active', true)
                    ->whereNull('staging_out_histories.delivery_date'),
                // Selesai = delivery_date terisi, apapun is_active-nya.
                'selesai' => $query->whereNotNull('staging_out_histories.delivery_date'),
                // Dihapus = hilang sebelum sempat terkirim.
                'dihapus' => $query->where('staging_out_histories.is_active', false)
                    ->whereNull('staging_out_histories.delivery_date'),
                default => null,
            };
        }

        if ($request->filled('event')) {
            $query->where('ld.event_type', $request->event);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('staging_out_histories.delivery_instruction_date', '<=', $request->end_date);
        }
    }

    /**
     * Pencarian kata kunci -- juga dipakai bersama oleh tabel & export.
     * `ilike` supaya tidak case-sensitive di PostgreSQL (sama seperti
     * perilaku pencarian bawaan DataTables).
     */
    public static function applySearch($query, $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('items.name', 'ilike', "%{$keyword}%")
                ->orWhere('items.item_code_internal', 'ilike', "%{$keyword}%")
                ->orWhere('staging_out_histories.so_number', 'ilike', "%{$keyword}%")
                ->orWhere('staging_out_histories.customer', 'ilike', "%{$keyword}%")
                ->orWhere('staging_out_histories.line_item', 'ilike', "%{$keyword}%");
        });
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