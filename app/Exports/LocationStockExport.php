<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Location;
use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * PENTING: sengaja TIDAK pakai WithHeadings lagi.
 *
 * WithHeadings selalu menaruh baris heading di baris 1 paling atas --
 * tidak bisa disisipi baris info lain di atasnya. Supaya file export ini
 * bisa dibuka mandiri (tanpa perlu tanya-tanya lagi filter apa yang
 * dipakai saat men-generate-nya), heading tabel sekarang ditulis manual
 * SEBAGAI SALAH SATU BARIS di dalam collection(), didahului blok info:
 * judul laporan, tanggal export, dan filter (lokasi/vendor/kata kunci)
 * yang sedang aktif saat file ini di-export.
 *
 * Baris kosong pemisah SENGAJA ditulis sebagai [''] (array berisi satu
 * string kosong), BUKAN [] (array benar-benar kosong) -- array yang
 * benar-benar kosong bisa di-collapse/dilewati oleh Excel writer
 * sehingga tidak menempati baris fisik, dan bikin semua style di bawahnya
 * geser (lihat catatan yang sama di StagingInHistoryDetailExport).
 *
 * SATU BARIS PER LOKASI: kalau 1 item stoknya tersebar di lebih dari 1
 * lokasi/lot, dulu Lokasi/Lot/Qty digabung koma dalam 1 baris. Sekarang
 * dipecah jadi 1 baris per lokasi, dan kolom identitas item (Item Code
 * Supplier s/d Description, serta Total Qty) di-merge jadi 1 sel
 * gabungan yang tingginya menyesuaikan jumlah baris lokasi item itu --
 * lihat $itemRowSpans & bagian merge di registerEvents().
 */
class LocationStockExport implements FromCollection, WithEvents
{
    private const DATA_COLUMNS = 10;

    protected $request;

    /**
     * Baris (1-based) tempat heading tabel ditulis -- diisi saat
     * collection() jalan, dipakai lagi di registerEvents() sebagai
     * fallback kalau deteksi otomatis di sheet gagal.
     */
    protected int $headingRowNumber = 0;

    /**
     * Jumlah baris data (1 baris = 1 lokasi/lot) per item, BERURUTAN
     * sama seperti item-nya ditulis di collection() -- dipakai di
     * registerEvents() untuk tahu rentang baris mana saja yang perlu
     * di-merge untuk kolom-kolom identitas item (Item Code Supplier s/d
     * Description, dan Total Qty), karena kolom-kolom itu sama untuk
     * semua baris lokasi milik 1 item yang sama.
     *
     * @var int[]
     */
    protected array $itemRowSpans = [];

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Item::select([
            'id',
            'item_code_supplier',
            'item_code_internal',
            'item_code_customer',
            'name',
            'description',
            'vendor_id',
        ])
            ->with([
                // 'lot' ditambahkan -- sebelumnya tidak ikut di-select,
                // jadi informasi lot barang per lokasi hilang total di
                // hasil export.
                'locationStocks:id,item_id,location_id,lot,quantity',
                'locationStocks.location:id,location_name',
                'vendor:id,name',
            ])
            // Barang yang belum punya stok di lokasi manapun tidak
            // di-export -- kalau tidak difilter, baris untuk barang
            // begini akan tampil dengan Lokasi/Lot/Qty kosong karena
            // relasi locationStocks-nya memang kosong.
            ->has('locationStocks');

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

        $formatQty = function ($qty) {
            return fmod((float) $qty, 1) == 0
                ? (int) $qty
                : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
        };

        // Sebelumnya: 1 baris per item, Lokasi/Lot/Qty digabung koma.
        // Sekarang: 1 baris per LOKASI -- kalau 1 item ada di 3 lokasi,
        // jadi 3 baris. Kolom identitas item (Item Code s/d Description,
        // dan Total Qty) ditulis ulang di setiap baris di sini, lalu
        // di-merge jadi 1 sel gabungan per item di registerEvents() --
        // $this->itemRowSpans mencatat berapa baris tiap item supaya
        // event AfterSheet tahu rentang mana yang harus di-merge.
        $this->itemRowSpans = [];
        $dataRows = [];

        foreach ($query->get() as $item) {

            $locationStocks = $item->locationStocks;

            if ($this->request->filled('location_id')) {
                $locationStocks = $locationStocks->where(
                    'location_id',
                    $this->request->location_id
                );
            }

            // Item yang setelah difilter ternyata tidak punya baris stok
            // tersisa (mis. sub-filter location_id di atas) dilewati --
            // seharusnya jarang terjadi karena query utama sudah pakai
            // ->has('locationStocks'), tapi tetap dijaga.
            if ($locationStocks->isEmpty()) {
                continue;
            }

            $totalQty = $formatQty($locationStocks->sum('quantity'));

            $rowCount = 0;

            foreach ($locationStocks as $stock) {
                $dataRows[] = [
                    $item->item_code_supplier,
                    $item->item_code_internal,
                    $item->item_code_customer,
                    $item->name,
                    $item->vendor ? $item->vendor->name : '-',
                    $item->description,
                    optional($stock->location)->location_name ?: '-',
                    $stock->lot ?: '-',
                    $formatQty($stock->quantity),
                    $totalQty,
                ];

                $rowCount++;
            }

            $this->itemRowSpans[] = $rowCount;
        }

        // ===== Blok info: judul, tanggal export, & filter yang dipakai =====
        $locationName = $this->request->filled('location_id')
            ? optional(Location::find($this->request->location_id))->location_name
            : null;

        $vendorName = $this->request->filled('vendor_id')
            ? optional(Vendor::find($this->request->vendor_id))->name
            : null;

        $rows = [];

        $rows[] = ['LAPORAN STOK BARANG PER LOKASI'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = ['Filter Lokasi', $locationName ?: 'Semua Lokasi'];
        $rows[] = ['Filter Vendor', $vendorName ?: 'Semua Vendor'];
        $rows[] = ['Kata Kunci Pencarian', $this->request->filled('search') ? $this->request->search : '-'];
        // count($dataRows) sekarang jumlah BARIS (1 baris = 1 lokasi),
        // bukan jumlah item lagi -- jumlah item yang benar ada di
        // count($this->itemRowSpans).
        $rows[] = ['Total Barang', count($this->itemRowSpans)];
        $rows[] = ['']; // spacer -- lihat catatan di docblock class, WAJIB [''] bukan []

        $this->headingRowNumber = count($rows) + 1;

        $rows[] = [
            'Item Code Supplier',
            'Item Code Internal',
            'Item Code Customer',
            'Name',
            'Vendor',
            'Description',
            'Lokasi',
            'Lot',
            'Qty per Lokasi',
            'Total Qty',
        ];

        $rows = array_merge($rows, $dataRows);

        return collect($rows);
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
                 * saja) -- pola yang sama dipakai di
                 * StagingInHistoryDetailExport supaya tahan kalau baris
                 * kosong ternyata di-collapse oleh Excel writer.
                 */
                $headingRow = null;

                foreach ($sheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($sheet->getCell('A'.$rowIndex)->getValue() === 'Item Code Supplier') {
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
                $lastCol = chr(64 + self::DATA_COLUMNS);

                $sheet->getStyle('A'.$headingRow.':'.$lastCol.$headingRow)
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

                $tableRange = 'A'.$headingRow.':'.$lastCol.$lastRow;

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

                // Merge kolom identitas item (A s/d F, dan J = Total Qty)
                // untuk setiap item yang barisnya lebih dari 1 (lebih dari
                // 1 lokasi) -- Lokasi/Lot/Qty per Lokasi (G/H/I) TIDAK
                // ikut di-merge karena memang beda tiap baris.
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'J'];
                $currentRow = $headingRow + 1;

                foreach ($this->itemRowSpans as $rowCount) {
                    if ($rowCount > 1) {
                        $mergeFirstRow = $currentRow;
                        $mergeLastRow = $currentRow + $rowCount - 1;

                        foreach ($mergeColumns as $col) {
                            $sheet->mergeCells($col.$mergeFirstRow.':'.$col.$mergeLastRow);
                        }
                    }

                    $currentRow += $rowCount;
                }

                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(12);
            },

        ];
    }
}