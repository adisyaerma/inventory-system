<?php

namespace App\Exports;

use App\Models\StagingIn;
use App\Models\StagingInHistory;
use App\Models\StagingInHistoryDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export SATU siklus staging_in_history (bukan tabel history secara
 * keseluruhan) beserta seluruh Activity Timeline-nya ke Excel. Ini adalah
 * versi Excel dari panel "History Detail" yang tampil di sisi kanan
 * halaman staging_in_history (lihat StagingInHistoryController::detail()
 * dan renderTimeline() di blade-nya) -- datanya sengaja disamakan supaya
 * yang di-export persis dengan yang dilihat user di panel tsb.
 *
 * Catatan: daftar event COMPLETED_EVENTS/REMOVED_EVENTS di bawah ini
 * SENGAJA duplikat dari StagingInHistoryController (yang private const di
 * sana, jadi tidak bisa dipakai lintas class). Kalau daftar event di
 * controller berubah, sesuaikan juga di sini.
 */
class StagingInHistoryDetailExport implements FromArray, WithEvents
{
    private const COMPLETED_EVENTS = ['moved_to_stock', 'moved_to_staging_out'];

    private const REMOVED_EVENTS = ['deleted', 'bulk_deleted', 'reset_by_import'];

    private const TIMELINE_COLUMNS = 7; // Tanggal, Event, Qty Sebelum, Qty Perubahan, Qty Sesudah, Keterangan, Oleh

    protected StagingInHistory $history;

    public function __construct(StagingInHistory $history)
    {
        $this->history = $history->load([
            'item',
            'creator',
            'details' => function ($query) {
                // Urutan sama seperti di StagingInHistoryController::detail():
                // dari yang paling lama ke yang paling baru.
                $query->reorder()->orderBy('created_at')->orderBy('id');
            },
            'details.performedBy',
        ]);
    }

    public function array(): array
    {
        $h = $this->history;
        $lastDetail = $h->details->last();

        $status = $this->statusMeta($h->is_active, $lastDetail?->event_type);

        $currentQty = $h->is_active
            ? (int) optional(StagingIn::find($h->staging_in_id))->qty
            : (int) ($lastDetail->qty_after ?? 0);

        $rows = [];

        $rows[] = ['STAGING IN HISTORY DETAIL'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = [''];
        $rows[] = ['Kode Barang', optional($h->item)->item_code_internal ?: '-'];
        $rows[] = ['Nama Barang', optional($h->item)->name ?: '-'];
        $rows[] = ['No. PO', $h->po_number ?: '-'];
        $rows[] = ['Status', $status['label']];
        $rows[] = [''];
        $rows[] = ['INFORMASI AWAL'];
        $rows[] = ['Supplier', $h->supplier_origin ?: '-'];
        $rows[] = ['Lokasi', $h->location ?: '-'];
        $rows[] = ['Tanggal Kedatangan', optional($h->arrival_date)->format('d M Y') ?: '-'];
        $rows[] = ['Incoterms', $h->incoterms ?: '-'];
        $rows[] = ['Qty Awal', (int) $h->initial_qty];
        $rows[] = ['Qty Saat Ini', $currentQty];
        $rows[] = ['Dibuat Oleh', optional($h->creator)->name ?: '-'];
        $rows[] = ['Dibuat Pada', $h->created_at->format('d M Y, H:i')];
        $rows[] = [''];
        $rows[] = ['ACTIVITY TIMELINE'];
        $rows[] = ['Tanggal', 'Event', 'Qty Sebelum', 'Qty Perubahan', 'Qty Sesudah', 'Keterangan', 'Oleh'];

        foreach ($h->details as $detail) {
            $rows[] = [
                $detail->created_at->format('d M Y, H:i'),
                StagingInHistoryDetail::EVENT_TYPES[$detail->event_type] ?? $detail->event_type,
                $detail->qty_before,
                $detail->qty_change,
                $detail->qty_after,
                $this->describeMeta($detail).($detail->notes ? ' | Catatan: '.$detail->notes : ''),
                optional($detail->performedBy)->name ?: 'System',
            ];
        }

        if ($h->details->isEmpty()) {
            $rows[] = ['Belum ada aktivitas untuk history ini.'];
        }

        return $rows;
    }

    /**
     * Label status ringkas -- disamakan persis dengan
     * StagingInHistoryController::statusMeta(), tanpa warna badge
     * (tidak relevan untuk Excel).
     */
    private function statusMeta(bool $isActive, ?string $lastEventType): array
    {
        if ($isActive) {
            return ['label' => 'Aktif'];
        }

        if (in_array($lastEventType, self::COMPLETED_EVENTS, true)) {
            return ['label' => 'Selesai'];
        }

        return ['label' => 'Dihapus'];
    }

    /**
     * Ringkasan teks untuk kolom "Keterangan", dibaca dari kolom meta
     * (json) tiap detail -- padanan teks-polos dari fungsi JS
     * metaDescription() di staging_in_history.blade.php.
     */
    private function describeMeta(StagingInHistoryDetail $detail): string
    {
        $meta = is_string($detail->meta) ? json_decode($detail->meta, true) : $detail->meta;
        $meta = $meta ?: [];

        return match ($detail->event_type) {
            'moved_to_stock' => collect([
                isset($meta['transaction_number']) ? 'Transaction: '.$meta['transaction_number'] : null,
                isset($meta['location']) ? 'Lokasi: '.$meta['location'] : null,
            ])->filter()->implode(', '),

            'moved_to_staging_out' => collect([
                isset($meta['so_number']) ? 'SO Number: '.$meta['so_number'] : null,
                isset($meta['customer']) ? 'Customer: '.$meta['customer'] : null,
            ])->filter()->implode(', '),

            'updated' => collect($meta)->map(function ($change, $field) {
                $old = is_array($change) ? ($change['old'] ?? '-') : '-';
                $new = is_array($change) ? ($change['new'] ?? '-') : '-';

                return ucfirst(str_replace('_', ' ', $field)).": {$old} -> {$new}";
            })->implode(', '),

            'created' => 'Qty awal: '.($detail->qty_after ?? '-'),

            default => '',
        };
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Judul utama.
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                /*
                 * PENTING: posisi baris (section title, header tabel
                 * timeline, baris terakhir) SENGAJA dicari langsung dari
                 * isi sheet yang sudah jadi -- BUKAN dari hasil hitung
                 * manual jumlah elemen array di array(). Excel writer
                 * tidak selalu menganggap baris array kosong (`[]`)
                 * sebagai satu baris spreadsheet penuh, jadi hitungan
                 * manual gampang meleset (ini persis penyebab bug
                 * border/warna judul yang dulu geser ke bawah). Dengan
                 * dicari dari isi sheet sungguhan, styling ini akan
                 * selalu tepat di posisi yang benar walau strukturnya
                 * berubah di kemudian hari.
                 */
                $timelineHeaderRow = null;

                foreach ($sheet->getRowIterator() as $row) {

                    $rowIndex = $row->getRowIndex();
                    $colA = $sheet->getCell('A'.$rowIndex)->getValue();
                    $colB = $sheet->getCell('B'.$rowIndex)->getValue();

                    if (in_array($colA, ['INFORMASI AWAL', 'ACTIVITY TIMELINE'], true)) {

                        $sheet->getStyle('A'.$rowIndex)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                        ]);

                        continue;
                    }

                    // Header tabel timeline: satu-satunya baris dengan
                    // kombinasi persis "Tanggal" di kolom A & "Event" di
                    // kolom B.
                    if ($colA === 'Tanggal' && $colB === 'Event') {
                        $timelineHeaderRow = $rowIndex;

                        continue;
                    }

                    // Sebelum masuk tabel timeline: baris "Label: Value"
                    // di blok info (Kode Barang, Nama Barang, ..., Dibuat
                    // Pada) -- kolom A-nya ditebalkan sebagai label.
                    if ($timelineHeaderRow === null && $colA !== null && $colA !== '' && $colB !== null && $colB !== '') {
                        $sheet->getStyle('A'.$rowIndex)->applyFromArray(['font' => ['bold' => true]]);
                    }
                }

                // Baris terakhir yang benar-benar berisi data di sheet
                // ini -- otomatis mengikuti isi tabel timeline yang
                // sesungguhnya, bukan angka hasil hitung manual.
                $lastRow = $sheet->getHighestDataRow();

                if ($timelineHeaderRow !== null) {

                    // Header tabel timeline.
                    $headerRange = 'A'.$timelineHeaderRow.':'.
                        chr(64 + self::TIMELINE_COLUMNS).$timelineHeaderRow;

                    $sheet->getStyle($headerRange)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F1F3F9'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Border seluruh tabel timeline (header s/d baris
                    // terakhir yang benar-benar berisi data).
                    $tableRange = 'A'.$timelineHeaderRow.':'.
                        chr(64 + self::TIMELINE_COLUMNS).$lastRow;

                    $sheet->getStyle($tableRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $sheet->getStyle($tableRange)
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                // Lebar kolom.
                $sheet->getColumnDimension('A')->setWidth(24);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(14);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(50);
                $sheet->getColumnDimension('G')->setWidth(20);
            },

        ];
    }
}