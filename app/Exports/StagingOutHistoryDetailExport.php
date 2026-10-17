<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export SATU siklus staging_out_history (info awal + status pengiriman +
 * Activity Timeline-nya) ke Excel. Ini versi Excel dari panel "History
 * Detail" di sisi kanan halaman staging_out_history.
 *
 * Berbeda dengan StagingInHistoryDetailExport (yang query & hitung sendiri),
 * class ini TIDAK menerima model, melainkan array payload yang sama persis
 * dengan JSON panel detail -- hasil
 * StagingOutHistoryController::buildDetailPayload(). Dengan begitu logika
 * status, asal barang (stok/eksternal), label event, dsb tidak diduplikasi
 * di sini dan isi Excel selalu identik dengan yang tampil di layar.
 */
class StagingOutHistoryDetailExport implements FromArray, WithEvents
{
    private const TIMELINE_COLUMNS = 7; // Tanggal, Event, Qty Sebelum, Qty Perubahan, Qty Sesudah, Keterangan, Oleh

    /**
     * @param  array  $detail  payload dari StagingOutHistoryController::buildDetailPayload()
     */
    public function __construct(private array $detail)
    {
    }

    public function array(): array
    {
        $d = $this->detail;
        $info = $d['informasi_awal'] ?? [];
        $ship = $d['status_pengiriman'] ?? [];
        $timeline = $d['timeline'] ?? [];

        $rows = [];

        $rows[] = ['STAGING OUT HISTORY DETAIL'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = [''];
        $rows[] = ['Kode Barang', $d['item_code'] ?: '-'];
        $rows[] = ['Nama Barang', $d['item_name'] ?: '-'];
        $rows[] = ['No. SO', $d['so_number'] ?: '-'];
        $rows[] = ['Status', $d['status']['label'] ?? '-'];
        $rows[] = [''];
        $rows[] = ['INFORMASI AWAL'];
        $rows[] = ['Customer', $info['customer'] ?? '-'];
        $rows[] = ['Line Item', $info['line_item'] ?? '-'];
        $rows[] = ['Sumber Barang', $info['sumber_barang'] ?? '-'];
        $rows[] = ['Tgl Instruksi Kirim', $info['delivery_instruction_date'] ?? '-'];
        $rows[] = ['Qty Awal', $info['initial_qty'] ?? '-'];
        $rows[] = ['Qty Saat Ini', $info['current_qty'] ?? '-'];
        $rows[] = ['Dibuat Oleh', $info['created_by'] ?? '-'];
        $rows[] = ['Dibuat Pada', $info['created_at'] ?? '-'];
        $rows[] = [''];
        $rows[] = ['STATUS PENGIRIMAN'];
        $rows[] = ['Tgl Picking', $ship['picking_date'] ?? '-'];
        $rows[] = ['DO Number', $ship['do_number'] ?? '-'];
        $rows[] = ['Tgl Kirim', $ship['delivery_date'] ?? '-'];
        $rows[] = [''];
        $rows[] = ['ACTIVITY TIMELINE'];
        $rows[] = ['Tanggal', 'Event', 'Qty Sebelum', 'Qty Perubahan', 'Qty Sesudah', 'Keterangan', 'Oleh'];

        foreach ($timeline as $item) {
            $notes = ! empty($item['notes']) ? ' | Catatan: '.$item['notes'] : '';

            $rows[] = [
                $item['created_at'] ?? '-',
                $item['label'] ?? ($item['event_type'] ?? '-'),
                $item['qty_before'],
                $item['qty_change'],
                $item['qty_after'],
                $this->describeMeta($item).$notes,
                $item['performed_by'] ?? 'System',
            ];
        }

        if (empty($timeline)) {
            $rows[] = ['Belum ada aktivitas untuk history ini.'];
        }

        return $rows;
    }

    /**
     * Ringkasan teks untuk kolom "Keterangan", dibaca dari meta tiap event
     * -- padanan teks-polos dari fungsi JS metaDescription() di
     * staging_out_history.blade.php.
     */
    private function describeMeta(array $item): string
    {
        $meta = $item['meta'] ?? [];
        $meta = is_string($meta) ? json_decode($meta, true) : $meta;
        $meta = $meta ?: [];

        return match ($item['event_type'] ?? null) {

            'restored' => ! empty($meta['cleared_delivery_receipt_date'])
                ? 'Tgl resi pengiriman dikosongkan (sebelumnya '.$this->formatDate($meta['cleared_delivery_receipt_date']).')'
                : '',

            'picking_confirmed' => ! empty($meta['picking_date'])
                ? 'Tanggal picking: '.$this->formatDate($meta['picking_date'])
                : '',

            'delivered' => collect([
                ! empty($meta['do_number']) ? 'DO Number: '.$meta['do_number'] : null,
                ! empty($meta['delivery_date']) ? 'Tanggal kirim: '.$this->formatDate($meta['delivery_date']) : null,
            ])->filter()->implode(', '),

            'updated' => collect($meta)->map(function ($change, $field) {
                $old = is_array($change) ? ($change['old'] ?? null) : null;
                $new = is_array($change) ? ($change['new'] ?? null) : null;

                return $field.': '
                    .($old === null || $old === '' ? '-' : $this->formatDate($old)).' -> '
                    .($new === null || $new === '' ? '-' : $this->formatDate($new));
            })->implode(', '),

            'created' => $this->describeCreated($item, $meta),

            default => '',
        };
    }

    private function describeCreated(array $item, array $meta): string
    {
        $parts = ['Qty: '.($item['qty_after'] ?? '-').' PCS'];

        if (($meta['source_type'] ?? null) === 'stock') {
            $locationName = $meta['location_name']
                ?? (! empty($meta['location_id']) ? 'Lokasi #'.$meta['location_id'] : '-');

            $parts[] = 'Diambil dari stok: '.$locationName
                .' ('.(! empty($meta['lot']) ? 'Lot '.$meta['lot'] : 'Tanpa Lot').')';
        } elseif (($meta['source_type'] ?? null) === 'external') {
            $parts[] = 'Sumber: Eksternal';
        }

        return implode(', ', $parts);
    }

    /**
     * Nilai berbentuk tanggal (Y-m-d, boleh dengan jam) diubah ke
     * "d M Y"; nilai lain dikembalikan apa adanya.
     */
    private function formatDate($value): string
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T][\d:.]+(?:Z|[+-]\d{2}:?\d{2})?)?$/', $value)) {
            try {
                return Carbon::parse($value)->format('d M Y');
            } catch (\Throwable $e) {
                return $value;
            }
        }

        return (string) $value;
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
                 * Posisi baris (judul section, header tabel timeline, baris
                 * terakhir) dicari langsung dari isi sheet yang sudah jadi,
                 * BUKAN dari hitungan manual jumlah elemen array -- baris
                 * array kosong ([]) tidak selalu dianggap satu baris penuh
                 * oleh writer Excel, jadi hitungan manual gampang meleset.
                 */
                $timelineHeaderRow = null;

                foreach ($sheet->getRowIterator() as $row) {

                    $rowIndex = $row->getRowIndex();
                    $colA = $sheet->getCell('A'.$rowIndex)->getValue();
                    $colB = $sheet->getCell('B'.$rowIndex)->getValue();

                    if (in_array($colA, ['INFORMASI AWAL', 'STATUS PENGIRIMAN', 'ACTIVITY TIMELINE'], true)) {

                        $sheet->getStyle('A'.$rowIndex)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                        ]);

                        continue;
                    }

                    // Header tabel timeline: satu-satunya baris dengan
                    // kombinasi persis "Tanggal" di kolom A & "Event" di B.
                    if ($colA === 'Tanggal' && $colB === 'Event') {
                        $timelineHeaderRow = $rowIndex;

                        continue;
                    }

                    // Sebelum tabel timeline: baris "Label: Value" -- kolom
                    // A ditebalkan sebagai label.
                    if ($timelineHeaderRow === null && $colA !== null && $colA !== '' && $colB !== null && $colB !== '') {
                        $sheet->getStyle('A'.$rowIndex)->applyFromArray(['font' => ['bold' => true]]);
                    }
                }

                $lastRow = $sheet->getHighestDataRow();

                if ($timelineHeaderRow !== null) {

                    $lastCol = chr(64 + self::TIMELINE_COLUMNS);

                    // Header tabel timeline.
                    $sheet->getStyle('A'.$timelineHeaderRow.':'.$lastCol.$timelineHeaderRow)->applyFromArray([
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

                    // Border seluruh tabel timeline.
                    $tableRange = 'A'.$timelineHeaderRow.':'.$lastCol.$lastRow;

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

                    // Kolom Keterangan bisa panjang (mis. banyak field
                    // berubah sekaligus) -- bungkus teksnya.
                    $sheet->getStyle('F'.($timelineHeaderRow + 1).':F'.$lastRow)
                        ->getAlignment()
                        ->setWrapText(true);
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