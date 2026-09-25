<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Location;
use App\Models\StockMutation;
use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * PENTING - kompatibilitas dengan StockMutationImport:
 *
 * Urutan kolom A-J DI BARIS DATA HARUS PERSIS SAMA dengan urutan kolom
 * yang dibaca StockMutationImport (lihat komentar di header class tsb):
 *
 * 0 Kode Barang  (Item Code)
 * 1 Nama Barang  (Item Name)
 * 2 Lokasi       (Location)
 * 3 Lot
 * 4 Tanggal      (Date)
 * 5 Nomor        (Transaction Number)
 * 6 Deskripsi    (Description)
 * 7 Qty Masuk    (Qty In)
 * 8 Qty Keluar   (Qty Out)
 * 9 Qty Balance
 *
 * Kolom "Vendor" TIDAK dimasukkan di antara kolom-kolom di atas (dulu ada
 * di index 2 dan bikin Location/Lot geser -- ini yang bikin hasil export
 * tidak bisa langsung dipakai ulang sebagai file import). Vendor
 * ditaruh di kolom PALING BELAKANG (index 10 / kolom K) sebagai info
 * tambahan untuk dibaca manusia; StockMutationImport hanya membaca
 * index 0-9 jadi kolom ini otomatis diabaikan saat file hasil export
 * ini di-upload lagi sebagai file import.
 *
 * Baris juga diurutkan & di-merge per kombinasi item+lokasi+lot (bukan
 * cuma per item), supaya pola "sel kosong = warisan dari baris di
 * atasnya" cocok dengan logic forward-fill di StockMutationImport::collection().
 *
 * PENTING JUGA: sengaja TIDAK pakai WithHeadings.
 *
 * WithHeadings selalu menaruh baris heading di baris 1 paling atas --
 * tidak bisa disisipi baris info lain di atasnya. Supaya file export ini
 * bisa dibuka mandiri (tanpa perlu tanya-tanya lagi filter apa yang
 * dipakai saat men-generate-nya), heading tabel sekarang ditulis manual
 * SEBAGAI SALAH SATU BARIS di dalam array(), didahului blok info: judul
 * laporan, tanggal export, dan filter (item/lokasi/vendor/tipe transaksi/
 * rentang tanggal/pencarian) yang sedang aktif saat file ini di-export.
 * Pola sama persis dengan LocationStockExport.
 *
 * KARENA StockMutationImport HANYA MEMBACA MULAI DARI BARIS DATA
 * (bukan baris 1 tetap), blok info & heading di atas TIDAK mengganggu
 * kompatibilitas import selama file yang mau di-import ulang memang
 * hasil export ini (StockMutationImport mencari baris headernya
 * sendiri, sama seperti registerEvents() di bawah mencari baris
 * heading untuk styling). Kalau StockMutationImport versi yang dipakai
 * masih mengasumsikan header ada di baris 1, sesuaikan juga file
 * tersebut supaya konsisten.
 *
 * Baris kosong pemisah SENGAJA ditulis sebagai [''] (array berisi satu
 * string kosong), BUKAN [] (array benar-benar kosong) -- array yang
 * benar-benar kosong bisa di-collapse/dilewati oleh Excel writer
 * sehingga tidak menempati baris fisik, dan bikin semua style +
 * merge range di bawahnya geser.
 */
class StockMutationExport implements FromArray, WithEvents
{
    private const DATA_COLUMNS = 11;

    /**
     * Merge range untuk kolom yang konstan per ITEM (Item Code, Item
     * Name, Vendor): [startRow, endRow][]
     */
    protected array $itemMergeRanges = [];

    /**
     * Merge range untuk kolom yang konstan per kombinasi ITEM + LOKASI +
     * LOT (Location, Lot): [startRow, endRow][]
     */
    protected array $locationLotMergeRanges = [];

    /**
     * Baris (1-based) tempat heading tabel ditulis -- diisi saat array()
     * jalan, dipakai lagi di registerEvents() sebagai fallback kalau
     * deteksi otomatis di sheet gagal.
     */
    protected int $headingRowNumber = 0;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $dataRows = [];

        $query = StockMutation::with([
            'item:id,item_code_internal,name,vendor_id',
            'item.vendor:id,name',
            'location:id,location_name',
        ]);

        if ($this->request->filled('item')) {
            $query->where('item_id', $this->request->item);
        }
        if ($this->request->filled('start_date')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $this->request->start_date
            );
        }

        if ($this->request->filled('end_date')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $this->request->end_date
            );
        }

        // Diselaraskan dengan filter lokasi di tabel (StockMutationController::data()):
        // langsung where() ke location_id milik mutasi itu sendiri. Sebelumnya di
        // sini pakai whereHas('item.locationStocks', ...) yang artinya "item ini
        // PERNAH/MASIH punya stok di lokasi tsb" -- itu cuma nge-filter ITEM-nya,
        // bukan mutasinya, jadi hasil export bisa beda (dan malah tampil SEMUA
        // lokasi milik item itu) dibanding yang tampil di tabel saat difilter
        // lokasi tertentu.
        if ($this->request->filled('location_id')) {
            $query->where('location_id', $this->request->location_id);
        }

        if ($this->request->filled('vendor_id')) {
            $vendorId = $this->request->vendor_id;

            $query->whereHas('item', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        }

        if ($this->request->filled('transaction_type')) {
            $query->where('transaction_type', $this->request->transaction_type);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(transaction_date AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhere('transaction_number', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(qty_in AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(qty_out AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(qty_balance AS TEXT) ilike ?', ["%{$search}%"])
                    ->orWhereHas('item', function ($sq) use ($search) {
                        $sq->where('item_code_internal', 'ilike', "%{$search}%")
                            ->orWhere('name', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('location', function ($lq) use ($search) {
                        $lq->where('location_name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Urutan: item -> lokasi -> lot -> tanggal -> id. Ini WAJIB supaya
        // baris dengan kombinasi item+lokasi+lot yang sama selalu
        // bersebelahan (kontigu) di hasil export, sama seperti asumsi
        // pengelompokan StockMutationImport (yang mengelompokkan ulang
        // baris berdasarkan item+lokasi+lot lalu sort tanggal DI DALAM
        // grup tsb). Kalau urutannya cuma item->tanggal seperti
        // sebelumnya, baris lokasi/lot berbeda bisa saling menyelip dan
        // pola forward-fill "sel kosong = warisan baris atas" jadi rusak.
        $mutations = $query
            ->select([
                'id',
                'item_id',
                'location_id',
                'lot',
                'transaction_date',
                'transaction_number',
                'description',
                'qty_in',
                'qty_out',
                'qty_balance',
            ])
            ->orderBy('item_id')
            ->orderBy('location_id')
            ->orderBy('lot')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // ===== Blok info: judul, tanggal export, & filter yang dipakai =====
        $itemLabel = '-';

        if ($this->request->filled('item')) {
            $item = Item::find($this->request->item);
            $itemLabel = $item
                ? trim($item->item_code_internal.' - '.$item->name)
                : $this->request->item;
        } else {
            $itemLabel = 'Semua Barang';
        }

        $locationLabel = $this->request->filled('location_id')
            ? optional(Location::find($this->request->location_id))->location_name
            : null;

        $vendorLabel = $this->request->filled('vendor_id')
            ? optional(Vendor::find($this->request->vendor_id))->name
            : null;

        $dateRangeLabel = '-';

        if ($this->request->filled('start_date') || $this->request->filled('end_date')) {
            $dateRangeLabel = ($this->request->start_date ?: '...').' s/d '.($this->request->end_date ?: '...');
        }

        $rows = [];

        $rows[] = ['LAPORAN MUTASI STOK'];
        $rows[] = ['Tanggal Export', now()->format('d M Y, H:i')];
        $rows[] = ['Filter Barang', $itemLabel];
        $rows[] = ['Filter Lokasi', $locationLabel ?: 'Semua Lokasi'];
        $rows[] = ['Filter Vendor', $vendorLabel ?: 'Semua Vendor'];
        $rows[] = ['Filter Tipe Transaksi', $this->request->filled('transaction_type') ? $this->request->transaction_type : 'Semua Tipe'];
        $rows[] = ['Filter Tanggal', $dateRangeLabel];
        $rows[] = ['Kata Kunci Pencarian', $this->request->filled('search') ? $this->request->search : '-'];
        $rows[] = ['Total Data', $mutations->count()];
        $rows[] = ['']; // spacer -- WAJIB [''] bukan [], lihat docblock class

        $this->headingRowNumber = count($rows) + 1;

        $rows[] = [
            'Item Code',
            'Item Name',
            'Location',
            'Lot',
            'Date',
            'Transaction Number',
            'Description',
            'Qty In',
            'Qty Out',
            'Qty Balance',
            'Vendor',
        ];

        // Baris data mulai persis setelah baris heading.
        $excelRow = count($rows) + 1;

        $itemGroups = $mutations->groupBy('item_id');

        foreach ($itemGroups as $itemMutations) {

            $itemStartRow = $excelRow;
            $firstInItem = true;

            // Sub-grup per kombinasi lokasi+lot DI DALAM item ini, supaya
            // kolom Location & Lot hanya ditulis ulang saat kombinasinya
            // benar-benar berubah (baris lain di kombinasi yang sama
            // dikosongkan & di-merge).
            $locationLotGroups = $itemMutations->groupBy(function ($mutation) {
                return $mutation->location_id.'|||'.($mutation->lot ?? '');
            });

            foreach ($locationLotGroups as $locationLotMutations) {

                $subStartRow = $excelRow;
                $firstInSubGroup = true;

                foreach ($locationLotMutations as $mutation) {

                    $dataRows[] = [
                        $firstInItem ? $mutation->item->item_code_internal : '',
                        $firstInItem ? $mutation->item->name : '',
                        $firstInSubGroup ? optional($mutation->location)->location_name : '',
                        $firstInSubGroup ? ($mutation->lot ?? '') : '',
                        optional($mutation->transaction_date)->format('d/m/Y'),
                        $mutation->transaction_number,
                        $mutation->description,
                        $mutation->qty_in,
                        $mutation->qty_out,
                        $mutation->qty_balance,
                        $firstInItem ? ($mutation->item->vendor->name ?? '-') : '',
                    ];

                    $firstInItem = false;
                    $firstInSubGroup = false;
                    $excelRow++;
                }

                $subEndRow = $excelRow - 1;

                if ($subEndRow > $subStartRow) {
                    $this->locationLotMergeRanges[] = [$subStartRow, $subEndRow];
                }
            }

            $itemEndRow = $excelRow - 1;

            if ($itemEndRow > $itemStartRow) {
                $this->itemMergeRanges[] = [$itemStartRow, $itemEndRow];
            }
        }

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

                    if ($sheet->getCell('A'.$rowIndex)->getValue() === 'Item Code') {
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

                // Item Code, Item Name, Vendor konstan per ITEM.
                foreach ($this->itemMergeRanges as [$start, $end]) {

                    foreach (['A', 'B', 'K'] as $column) {

                        $sheet->mergeCells("{$column}{$start}:{$column}{$end}");

                        $alignment = $sheet
                            ->getStyle("{$column}{$start}:{$column}{$end}")
                            ->getAlignment();

                        $alignment->setVertical(Alignment::VERTICAL_CENTER);
                        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Location, Lot konstan per kombinasi ITEM + LOKASI + LOT.
                foreach ($this->locationLotMergeRanges as [$start, $end]) {

                    foreach (['C', 'D'] as $column) {

                        $sheet->mergeCells("{$column}{$start}:{$column}{$end}");

                        $alignment = $sheet
                            ->getStyle("{$column}{$start}:{$column}{$end}")
                            ->getAlignment();

                        $alignment->setVertical(Alignment::VERTICAL_CENTER);
                        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                $lastRow = $sheet->getHighestRow();

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

                $sheet->getStyle("H{$firstDataRow}:J{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getColumnDimension('A')->setWidth(18); // Item Code
                $sheet->getColumnDimension('B')->setWidth(35); // Item Name
                $sheet->getColumnDimension('C')->setWidth(25); // Location
                $sheet->getColumnDimension('D')->setWidth(15); // Lot
                $sheet->getColumnDimension('E')->setWidth(15); // Date
                $sheet->getColumnDimension('F')->setWidth(25); // Transaction Number
                $sheet->getColumnDimension('G')->setWidth(40); // Description
                $sheet->getColumnDimension('H')->setWidth(12); // Qty In
                $sheet->getColumnDimension('I')->setWidth(12); // Qty Out
                $sheet->getColumnDimension('J')->setWidth(12); // Qty Balance
                $sheet->getColumnDimension('K')->setWidth(25); // Vendor
            },

        ];
    }
}