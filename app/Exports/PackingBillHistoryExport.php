<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Laporan Bill Packing (1 sheet).
 *
 * Susunan: judul + ringkasan filter, lalu tabel per No. SO. Tiap SO tampil
 * sebagai blok: satu baris per material (dikelompokkan per koli), diakhiri
 * baris "Total SO". Paling bawah ada total keseluruhan.
 *
 * Data disusun oleh PackingBillHistoryController::export() sehingga isinya
 * selalu sesuai filter yang sedang dipakai di tabel.
 *
 * $bills = [
 *   ['so', 'customer', 'tanggal', 'status', 'total',
 *    'kolis' => [['no', 'items' => [..], 'lines' => [['nama','qty','satuan','harga','subtotal'], ..]], ..],
 *    'shared' => [ ...baris material bersama... ]],
 *   ..
 * ]
 * $meta  = ['periode', 'status', 'search', 'printed_at']
 */
class PackingBillHistoryExport extends DefaultValueBinder implements FromArray, WithTitle, WithColumnWidths, WithEvents, WithCustomValueBinder
{
    private const NUM_COLS = 12; // A..L

    private array $rows = [];
    private int $headerRow = 0;
    private array $billStartRows = [];
    private array $totalRows = [];
    private ?int $grandRow = null;
    private ?int $emptyRow = null;

    public function __construct(private array $bills, private array $meta)
    {
        $this->build();
    }

    // ------------------------------------------------------------------
    //  Susun isi sheet
    // ------------------------------------------------------------------

    private function build(): void
    {
        $count = count($this->bills);
        $grand = (float) array_sum(array_column($this->bills, 'total'));

        $this->push(['LAPORAN BILL PACKING']);
        $this->push(['Rekap biaya material packing per No. SO']);
        $this->push([
            'Filter  :  Periode Tgl Bill: '.$this->meta['periode']
            .'   |   Status SO: '.$this->meta['status']
            .'   |   Pencarian: '.$this->meta['search'],
        ]);
        $this->push([
            'Dicetak :  '.$this->meta['printed_at']
            .'   |   Jumlah Bill: '.$count
            .'   |   Total Nilai: Rp '.$this->rupiah($grand),
        ]);
        $this->push([]);

        $this->headerRow = $this->push([
            'No', 'No. SO', 'Customer', 'Tgl Bill', 'Status SO', 'Koli', 'Isi Koli',
            'Material', 'Qty', 'Satuan', 'Harga Satuan (Rp)', 'Subtotal (Rp)',
        ]);

        if ($count === 0) {
            $this->emptyRow = $this->push(['Tidak ada data bill untuk filter yang dipilih.']);

            return;
        }

        foreach ($this->bills as $i => $bill) {
            foreach ($this->flattenLines($bill) as $j => $line) {
                $info = $j === 0
                    ? [$i + 1, $bill['so'], $bill['customer'], $bill['tanggal'], $bill['status']]
                    : ['', '', '', '', ''];

                $row = $this->push(array_merge($info, $line));

                if ($j === 0) {
                    $this->billStartRows[] = $row;
                }
            }

            $this->totalRows[] = $this->push([
                '', '', '', '', '', '', 'Total SO '.$bill['so'], '', '', '', '', (float) $bill['total'],
            ]);
        }

        $this->grandRow = $this->push([
            'TOTAL KESELURUHAN ('.$count.' bill)', '', '', '', '', '', '', '', '', '', '', $grand,
        ]);
    }

    /**
     * Baris material satu bill: [koli, isi koli, material, qty, satuan, harga, subtotal].
     */
    private function flattenLines(array $bill): array
    {
        $multi = count($bill['kolis']) > 1;
        $out = [];

        foreach ($bill['kolis'] as $koli) {
            $label = 'Koli '.$koli['no'];
            // Isi koli hanya dicatat kalau SO dipacking lebih dari 1 koli.
            $isi = $multi ? ($koli['items'] ? implode(', ', $koli['items']) : '-') : 'Semua item SO';

            if (! $koli['lines']) {
                $out[] = [$label, $isi, '(belum ada material)', '', '', '', ''];

                continue;
            }

            foreach ($koli['lines'] as $n => $l) {
                $out[] = [
                    $n === 0 ? $label : '',
                    $n === 0 ? $isi : '',
                    $l['nama'], $l['qty'], $l['satuan'] ?: '', $l['harga'], $l['subtotal'],
                ];
            }
        }

        foreach ($bill['shared'] as $n => $l) {
            $out[] = [
                $n === 0 ? 'Bersama' : '',
                $n === 0 ? 'Semua koli' : '',
                $l['nama'], $l['qty'], $l['satuan'] ?: '', $l['harga'], $l['subtotal'],
            ];
        }

        return $out ?: [['-', '-', '(belum ada material)', '', '', '', '']];
    }

    /** Tambah satu baris, kembalikan nomor barisnya di Excel (mulai 1). */
    private function push(array $cells): int
    {
        $this->rows[] = array_pad($cells, self::NUM_COLS, '');

        return count($this->rows);
    }

    private function rupiah(float $n): string
    {
        return number_format($n, fmod($n, 1.0) == 0.0 ? 0 : 2, ',', '.');
    }

    // ------------------------------------------------------------------
    //  Maatwebsite
    // ------------------------------------------------------------------

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Bill Packing';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6, 'B' => 20, 'C' => 26, 'D' => 12, 'E' => 15, 'F' => 11,
            'G' => 24, 'H' => 30, 'I' => 9, 'J' => 10, 'K' => 18, 'L' => 18,
        ];
    }

    /**
     * Semua teks disimpan sebagai teks (kode barang "0012" tidak jadi 12,
     * teks berawalan "=" tidak dianggap rumus). Angka tetap angka.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->style($event->sheet->getDelegate());
            },
        ];
    }

    // ------------------------------------------------------------------
    //  Tampilan
    // ------------------------------------------------------------------

    private function style(Worksheet $s): void
    {
        $h = $this->headerRow;
        $first = $h + 1;
        $last = count($this->rows);
        $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFBFBF']];

        $s->setShowGridlines(false);

        // Judul & ringkasan filter
        foreach ([1, 2, 3, 4] as $r) {
            $s->mergeCells("A{$r}:L{$r}");
        }
        $s->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1F3864']]]);
        $s->getRowDimension(1)->setRowHeight(28);
        $s->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['argb' => 'FF595959']]]);
        $s->getStyle('A3:L4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
            'font' => ['size' => 10],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $s->getRowDimension(3)->setRowHeight(22);
        $s->getRowDimension(4)->setRowHeight(22);

        // Header tabel
        $s->getStyle("A{$h}:L{$h}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => $thin],
        ]);
        $s->getRowDimension($h)->setRowHeight(32);

        if ($this->emptyRow) {
            $r = $this->emptyRow;
            $s->mergeCells("A{$r}:L{$r}");
            $s->getStyle("A{$r}:L{$r}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF7F7F7F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => $thin],
            ]);
        } else {
            $dataEnd = $this->grandRow - 1;
            $body = "A{$first}:L{$dataEnd}";

            $s->getStyle($body)->applyFromArray([
                'borders' => ['allBorders' => $thin],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
            ]);

            // Teks panjang turun baris, angka rata kanan, kolom pendek rata tengah
            $s->getStyle("C{$first}:C{$dataEnd}")->getAlignment()->setWrapText(true);
            $s->getStyle("G{$first}:H{$dataEnd}")->getAlignment()->setWrapText(true);
            foreach (['A', 'D', 'E', 'F'] as $col) {
                $s->getStyle("{$col}{$first}:{$col}{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $s->getStyle("I{$first}:I{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Awal tiap bill: garis pemisah tebal, No. SO tebal
            foreach ($this->billStartRows as $r) {
                $s->getStyle("A{$r}:L{$r}")->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF7F7F7F');
                $s->getStyle("B{$r}")->getFont()->setBold(true);
            }

            // Baris "Total SO"
            foreach ($this->totalRows as $r) {
                $s->mergeCells("G{$r}:K{$r}");
                $s->getStyle("A{$r}:L{$r}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDEBF7']],
                    'borders' => [
                        'top' => $thin,
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9DC3E6']],
                    ],
                ]);
                $s->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Total keseluruhan
            $g = $this->grandRow;
            $s->mergeCells("A{$g}:K{$g}");
            $s->getStyle("A{$g}:L{$g}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $s->getStyle("A{$g}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $s->getRowDimension($g)->setRowHeight(24);

            // Harga & subtotal
            $s->getStyle("K{$first}:L{$g}")->getNumberFormat()->setFormatCode('#,##0.00');
            $s->getStyle("K{$first}:L{$g}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Header tabel tetap terlihat saat scroll
        $s->freezePane('A'.$first);

        // Cetak: A4 landscape, 1 halaman lebar, header diulang tiap halaman
        $s->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($h, $h);
        $s->getPageMargins()->setTop(0.5)->setBottom(0.6)->setLeft(0.4)->setRight(0.4);
        $s->getHeaderFooter()->setOddFooter('&L&8Laporan Bill Packing&R&8Halaman &P dari &N');
    }
}