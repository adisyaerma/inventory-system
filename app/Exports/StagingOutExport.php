<?php

namespace App\Exports;

use App\Models\StagingOut;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * PENTING: sengaja TIDAK pakai WithHeadings.
 *
 * WithHeadings selalu menaruh baris heading di baris 1 paling atas --
 * tidak bisa disisipi baris info lain di atasnya. Supaya file export ini
 * bisa dibuka mandiri (tanpa perlu tanya-tanya lagi filter apa yang
 * dipakai saat men-generate-nya), heading tabel sekarang ditulis manual
 * SEBAGAI SALAH SATU BARIS di dalam array(), didahului blok info: judul
 * laporan, tanggal export, dan filter (status/customer/rentang tanggal/
 * overdue/lokasi staging/pencarian) yang sedang aktif saat file ini
 * di-export. Pola sama persis dengan LocationStockExport.
 *
 * Baris kosong pemisah SENGAJA ditulis sebagai [''] (array berisi satu
 * string kosong), BUKAN [] (array benar-benar kosong) -- array yang
 * benar-benar kosong bisa di-collapse/dilewati oleh Excel writer
 * sehingga tidak menempati baris fisik, dan bikin semua style di
 * bawahnya geser.
 */
class StagingOutExport implements FromArray, WithEvents
{
    private const DATA_COLUMNS = 9;

    protected $request;

    /**
     * Baris (1-based) tempat heading tabel ditulis -- diisi saat array()
     * jalan, dipakai lagi di registerEvents() sebagai fallback kalau
     * deteksi otomatis di sheet gagal.
     */
    protected int $headingRowNumber = 0;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Label ramah-baca untuk kode status yang dipakai di filter dropdown.
     */
    private function statusLabel(): string
    {
        return match ($this->request->status) {
            'belum_picking' => 'Belum Picking',
            'sudah_picking' => 'Sudah Picking',
            'sudah_dikirim' => 'Sudah Dikirim',
            default => 'Semua Status',
        };
    }

    /**
     * Label ramah-baca untuk kolom tanggal yang sedang dipakai sebagai
     * acuan filter rentang tanggal (delivery_instruction_date/
     * picking_date/delivery_date).
     */
    private function dateTypeLabel(): string
    {
        return match ($this->request->date_type) {
            'picking_date' => 'Tgl Picking',
            'delivery_date' => 'Tgl Resi Pengiriman',
            default => 'Tgl Instruksi Kirim',
        };
    }

    private function stagingLocationLabel(): string
    {
        if (! $this->request->filled('staging_location')) {
            return 'Semua Lokasi';
        }

        return match ($this->request->staging_location) {
            'belum_diisi' => 'Belum Diisi',
            'staging' => 'Staging',
            'packing' => 'Packing',
            'outbound' => 'Outbound',
            default => 'Semua Lokasi',
        };
    }

    public function array(): array
    {
        $dataRows = [];

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
            $dataRows[] = [
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

        // ===== Blok info: judul, tanggal export, & filter yang dipakai =====
        $dateRangeLabel = '-';

        if ($this->request->filled('start_date') || $this->request->filled('end_date')) {
            $dateRangeLabel = $this->dateTypeLabel().': '
                .($this->request->start_date ?: '...').' s/d '.($this->request->end_date ?: '...');
        }

        $rows = [];

        $rows[] = ['LAPORAN STAGING OUT'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = ['Filter Status', $this->statusLabel()];
        $rows[] = ['Filter Customer', $this->request->filled('customer') ? $this->request->customer : 'Semua Customer'];
        $rows[] = ['Filter Tanggal', $dateRangeLabel];
        $rows[] = ['Filter Overdue', $this->request->overdue == 1 ? 'Ya' : 'Semua'];
        $rows[] = ['Filter Lokasi Staging', $this->stagingLocationLabel()];
        $rows[] = ['Kata Kunci Pencarian', $this->request->filled('search') ? $this->request->search : '-'];
        $rows[] = ['Total Data', count($dataRows)];
        $rows[] = ['']; // spacer -- WAJIB [''] bukan [], lihat docblock class

        $this->headingRowNumber = count($rows) + 1;

        $rows[] = [
            'No. SO',
            'Customer',
            'Kode Barang',
            'Line Item',
            'Qty',
            'Tgl Instruksi Kirim',
            'Tgl Picking',
            'No. DO',
            'Tgl Resi Pengiriman',
        ];

        return array_merge($rows, $dataRows);
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Judul laporan.
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                /*
                 * Cari baris heading tabel dari isi sheet yang sudah jadi
                 * (bukan cuma percaya $this->headingRowNumber begitu
                 * saja) -- pola yang sama dipakai di LocationStockExport
                 * supaya tahan kalau baris kosong ternyata di-collapse
                 * oleh Excel writer.
                 */
                $headingRow = null;

                foreach ($sheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($sheet->getCell('A'.$rowIndex)->getValue() === 'No. SO') {
                        $headingRow = $rowIndex;

                        break;
                    }

                    // Baris info "Label: Value" di atas tabel -- kolom A
                    // (label) ditebalkan supaya gampang dibaca.
                    $colA = $sheet->getCell('A'.$rowIndex)->getValue();
                    $colB = $sheet->getCell('B'.$rowIndex)->getValue();

                    if ($rowIndex > 1 && $colA !== null && $colA !== '' && $colB !== null && $colB !== '') {
                        $sheet->getStyle('A'.$rowIndex)->applyFromArray(['font' => ['bold' => true]]);
                    }
                }

                // Fallback kalau deteksi di atas entah kenapa gagal.
                $headingRow = $headingRow ?? $this->headingRowNumber;

                $lastRow = $sheet->getHighestDataRow();
                $lastCol = chr(64 + self::DATA_COLUMNS); // I

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->applyFromArray([
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

                $tableRange = "A{$headingRow}:{$lastCol}{$lastRow}";

                $sheet->getStyle($tableRange)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle($tableRange)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $firstDataRow = $headingRow + 1;

                $sheet->getStyle("E{$firstDataRow}:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

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