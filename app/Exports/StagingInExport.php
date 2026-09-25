<?php

namespace App\Exports;

use App\Models\StagingIn;
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
 * laporan, tanggal export, dan filter (lokasi/status/incoterms/owner/
 * supplier/tanggal/pencarian) yang sedang aktif saat file ini di-export.
 * Pola sama persis dengan LocationStockExport.
 *
 * Baris kosong pemisah SENGAJA ditulis sebagai [''] (array berisi satu
 * string kosong), BUKAN [] (array benar-benar kosong) -- array yang
 * benar-benar kosong bisa di-collapse/dilewati oleh Excel writer
 * sehingga tidak menempati baris fisik, dan bikin semua style di
 * bawahnya geser.
 */
class StagingInExport implements FromArray, WithEvents
{
    private const DATA_COLUMNS = 11;

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

    public function array(): array
    {
        $dataRows = [];

        $query = StagingIn::query()->with('item.vendor');

        if ($this->request->filled('location')) {
            $query->where('location', $this->request->location);
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('incoterms')) {
            $query->where('incoterms', $this->request->incoterms);
        }

        if ($this->request->filled('item_owner')) {
            $owner = $this->request->item_owner;

            $query->whereHas('item.vendor', function ($q) use ($owner) {
                $q->where('name', $owner);
            });
        }

        if ($this->request->filled('supplier_origin')) {
            $query->where('supplier_origin', $this->request->supplier_origin);
        }

        if ($this->request->filled('start_date')) {
            $query->whereDate('arrival_date', '>=', $this->request->start_date);
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate('arrival_date', '<=', $this->request->end_date);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                    ->orWhere('supplier_origin', 'ilike', "%{$search}%")
                    ->orWhere('location', 'ilike', "%{$search}%")
                    ->orWhere('incoterms', 'ilike', "%{$search}%")
                    ->orWhere('notes', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(qty AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhereHas('item', function ($q2) use ($search) {
                        $q2->where('item_code_internal', 'ilike', "%{$search}%")
                            ->orWhere('name', 'ilike', "%{$search}%")
                            ->orWhereHas('vendor', function ($q3) use ($search) {
                                $q3->where('name', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        $stagings = $query->latest()->get();

        foreach ($stagings as $staging) {
            $dataRows[] = [
                $staging->po_number,
                optional($staging->arrival_date)->format('d/m/Y'),
                $staging->supplier_origin,
                optional(optional($staging->item)->vendor)->name,
                $staging->incoterms,
                optional($staging->item)->item_code_internal,
                optional($staging->item)->name,
                $staging->qty,
                $staging->location,
                $staging->notes,
                $staging->status,
            ];
        }

        // ===== Blok info: judul, tanggal export, & filter yang dipakai =====
        $dateRangeLabel = '-';

        if ($this->request->filled('start_date') || $this->request->filled('end_date')) {
            $dateRangeLabel = ($this->request->start_date ?: '...').' s/d '.($this->request->end_date ?: '...');
        }

        $rows = [];

        $rows[] = ['LAPORAN STAGING IN'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = ['Filter Lokasi', $this->request->filled('location') ? $this->request->location : 'Semua Lokasi'];
        $rows[] = ['Filter Status', $this->request->filled('status') ? $this->request->status : 'Semua Status'];
        $rows[] = ['Filter Incoterms', $this->request->filled('incoterms') ? $this->request->incoterms : 'Semua Incoterms'];
        $rows[] = ['Filter Owner', $this->request->filled('item_owner') ? $this->request->item_owner : 'Semua Owner'];
        $rows[] = ['Filter Supplier', $this->request->filled('supplier_origin') ? $this->request->supplier_origin : 'Semua Supplier'];
        $rows[] = ['Filter Tanggal Kedatangan', $dateRangeLabel];
        $rows[] = ['Kata Kunci Pencarian', $this->request->filled('search') ? $this->request->search : '-'];
        $rows[] = ['Total Data', count($dataRows)];
        $rows[] = ['']; // spacer -- WAJIB [''] bukan [], lihat docblock class

        $this->headingRowNumber = count($rows) + 1;

        $rows[] = [
            'No. PO',
            'Tanggal Kedatangan',
            'Supplier',
            'Owner',
            'Incoterms',
            'Kode Barang',
            'Nama Barang',
            'Qty',
            'Lokasi',
            'Keterangan',
            'Status',
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

                    if ($sheet->getCell('A'.$rowIndex)->getValue() === 'No. PO') {
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
                $lastCol = chr(64 + self::DATA_COLUMNS); // K

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

                $sheet->getStyle("H{$firstDataRow}:H{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getColumnDimension('A')->setWidth(20); // No. PO
                $sheet->getColumnDimension('B')->setWidth(18); // Tanggal Kedatangan
                $sheet->getColumnDimension('C')->setWidth(25); // Supplier
                $sheet->getColumnDimension('D')->setWidth(20); // Owner
                $sheet->getColumnDimension('E')->setWidth(15); // Incoterms
                $sheet->getColumnDimension('F')->setWidth(18); // Kode Barang
                $sheet->getColumnDimension('G')->setWidth(35); // Nama Barang
                $sheet->getColumnDimension('H')->setWidth(10); // Qty
                $sheet->getColumnDimension('I')->setWidth(28); // Lokasi
                $sheet->getColumnDimension('J')->setWidth(35); // Keterangan
                $sheet->getColumnDimension('K')->setWidth(20); // Status
            },

        ];
    }
}