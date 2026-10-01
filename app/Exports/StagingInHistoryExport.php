<?php

namespace App\Exports;

use App\Models\StagingInHistory;
use App\Models\StagingInHistoryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel untuk halaman Staging In History.
 *
 * Menghormati filter yang sama dengan tabel di halaman (status, event
 * terakhir, rentang tanggal kedatangan, dan kata kunci pencarian), karena
 * query & filter di sini sengaja dibuat sejalan dengan
 * StagingInHistoryController::baseQuery() / data().
 */
class StagingInHistoryExport implements FromQuery, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    use Exportable;

    /**
     * Event yang dianggap "menyelesaikan" siklus staging in.
     */
    private const COMPLETED_EVENTS = ['moved_to_stock', 'moved_to_staging_out'];

    /**
     * Event yang dianggap "menghapus" siklus staging in.
     */
    private const REMOVED_EVENTS = ['deleted', 'bulk_deleted', 'reset_by_import'];

    /**
     * Warna font per status, dipakai saat mewarnai baris di registerEvents().
     */
    private const STATUS_COLORS = [
        'Aktif' => 'FF198754',   // hijau (bs-success)
        'Selesai' => 'FF6C757D', // abu-abu (bs-secondary)
        'Dihapus' => 'FFDC3545', // merah (bs-danger)
    ];

    public function __construct(private Request $request)
    {
    }

    /**
     * Query dasar + filter, disamakan dengan
     * StagingInHistoryController::baseQuery() & data() supaya hasil export
     * selalu konsisten dengan apa yang tampil di tabel.
     */
    public function query()
    {
        $latestDetail = DB::table('staging_in_history_details as d')
            ->select(
                'd.staging_in_history_id',
                'd.event_type',
                'd.qty_after',
                'd.meta',
                'd.created_at as event_at'
            )
            ->whereRaw('d.id = (
                select max(d2.id) from staging_in_history_details d2
                where d2.staging_in_history_id = d.staging_in_history_id
            )');

        $query = StagingInHistory::query()
            ->select('staging_in_histories.*')
            ->with('creator')
            ->leftJoin('items', 'items.id', '=', 'staging_in_histories.item_id')
            ->leftJoin('staging_ins', 'staging_ins.id', '=', 'staging_in_histories.staging_in_id')
            ->leftJoinSub($latestDetail, 'ld', function ($join) {
                $join->on('ld.staging_in_history_id', '=', 'staging_in_histories.id');
            })
            ->addSelect([
                'items.item_code_internal as item_code',
                'items.name as item_name',
                'staging_ins.qty as running_qty',
                'ld.event_type as last_event_type',
                'ld.qty_after as last_qty_after',
                'ld.meta as last_meta',
                'ld.event_at as last_event_at',
            ])
            ->orderByDesc('staging_in_histories.id');

        self::applyFilters($query, $this->request);
        self::applySearch($query, $this->request->input('search'));

        return $query;
    }

    /**
     * SATU-SATUNYA tempat definisi filter status / event / rentang tanggal.
     * Dipanggil oleh export ini DAN oleh StagingInHistoryController::data().
     * Query wajib sudah di-join ke `items` dan subquery `ld`.
     */
    public static function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            match ($request->status) {
                'aktif' => $query->where('staging_in_histories.is_active', true),
                'selesai' => $query->where('staging_in_histories.is_active', false)
                    ->whereIn('ld.event_type', self::COMPLETED_EVENTS),
                'dihapus' => $query->where('staging_in_histories.is_active', false)
                    ->whereIn('ld.event_type', self::REMOVED_EVENTS),
                default => null,
            };
        }

        if ($request->filled('event')) {
            $query->where('ld.event_type', $request->event);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('staging_in_histories.arrival_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('staging_in_histories.arrival_date', '<=', $request->end_date);
        }
    }

    /**
     * Pencarian kata kunci -- dipakai bersama oleh tabel & export.
     * `ilike` supaya tidak case-sensitive di PostgreSQL.
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
                ->orWhere('staging_in_histories.po_number', 'ilike', "%{$keyword}%")
                ->orWhere('staging_in_histories.supplier_origin', 'ilike', "%{$keyword}%");
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'No. PO',
            'Supplier',
            'Lokasi',
            'Tgl Kedatangan',
            'Qty Awal',
            'Qty Saat Ini',
            'Sisa (%)',
            'Incoterms',
            'Status',
            'Event Terakhir',
            'Catatan Aktivitas Terakhir',
            'Waktu Event Terakhir',
            'Dibuat Oleh',
            'Dibuat Pada',
        ];
    }

    public function map($row): array
    {
        static $number = 0;
        $number++;

        $currentQty = $row->is_active
            ? (int) $row->running_qty
            : (int) ($row->last_qty_after ?? 0);

        $initialQty = (int) $row->initial_qty;
        $percent = $initialQty > 0 ? min(100, round(($currentQty / $initialQty) * 100)) : 0;

        $status = $this->statusLabel($row->is_active, $row->last_event_type);

        return [
            $number,
            $row->item_code ?: '-',
            $row->item_name ?: '-',
            $row->po_number ?: '-',
            $row->supplier_origin ?: '-',
            $row->location ?: '-',
            $row->arrival_date ? Carbon::parse($row->arrival_date)->format('d-m-Y') : '-',
            $initialQty,
            $currentQty,
            $percent / 100,
            $row->incoterms ?: '-',
            $status,
            $row->last_event_type
                ? (StagingInHistoryDetail::EVENT_TYPES[$row->last_event_type] ?? $row->last_event_type)
                : '-',
            $this->eventSubtitle($row->last_event_type, $row->last_meta) ?: '-',
            $row->last_event_at ? Carbon::parse($row->last_event_at)->format('d-m-Y H:i') : '-',
            optional($row->creator)->name ?: '-',
            $row->created_at->format('d-m-Y H:i'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '0%',
        ];
    }

    public function title(): string
    {
        return 'Staging In History';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => '0000000'],
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Tinggi baris header & freeze pane di bawah header supaya
                // header tetap kelihatan saat scroll.
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->freezePane('A2');

                // Aktifkan filter dropdown di header.
                $sheet->setAutoFilter("A1:{$highestColumn}1");

                // Border tipis untuk seluruh area data + header.
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD9D9D9'],
                        ],
                    ],
                ]);

                // Rata tengah untuk kolom No, Qty Awal, Qty Saat Ini, Sisa,
                // Status, dan Event Terakhir.
                foreach (['A', 'H', 'I', 'J', 'L', 'M'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Zebra striping + pewarnaan status per baris data.
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF8F9FB'],
                            ],
                        ]);
                    }

                    $statusValue = $sheet->getCell("L{$row}")->getValue();
                    $color = StagingInHistoryExport::STATUS_COLORS[$statusValue] ?? null;

                    if ($color) {
                        $sheet->getStyle("L{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => $color]],
                        ]);
                    }
                }
            },
        ];
    }

    /**
     * Label status ringkas (tanpa warna) untuk 1 baris history — versi
     * teks polos dari StagingInHistoryController::statusMeta().
     */
    private function statusLabel(bool $isActive, ?string $lastEventType): string
    {
        if ($isActive) {
            return 'Aktif';
        }

        if (in_array($lastEventType, self::COMPLETED_EVENTS, true)) {
            return 'Selesai';
        }

        return 'Dihapus';
    }

    /**
     * Ringkasan 1 baris untuk event terakhir, dibaca dari kolom meta (json)
     * — sejalan dengan StagingInHistoryController::eventSubtitle().
     */
    private function eventSubtitle(?string $eventType, $meta): ?string
    {
        if (! $eventType) {
            return null;
        }

        $meta = is_string($meta) ? json_decode($meta, true) : $meta;
        $meta = $meta ?: [];

        return match ($eventType) {
            'updated' => collect($meta)->keys()
                ->map(fn ($field) => ucfirst(str_replace('_', ' ', $field)).' diubah')
                ->implode(', ') ?: null,
            'moved_to_stock' => isset($meta['location']) ? 'Ke lokasi '.$meta['location'] : null,
            'moved_to_staging_out' => isset($meta['customer']) ? 'Ke '.$meta['customer'] : null,
            default => null,
        };
    }
}