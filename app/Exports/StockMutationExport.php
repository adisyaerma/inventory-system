<?php

namespace App\Exports;

use App\Models\StockMutation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * PENTING - kompatibilitas dengan StockMutationImport:
 *
 * Urutan kolom A-J DI SINI HARUS PERSIS SAMA dengan urutan kolom yang
 * dibaca StockMutationImport (lihat komentar di header class tsb):
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
 */
class StockMutationExport implements FromArray, WithEvents, WithHeadings
{
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

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
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
    }

    public function array(): array
    {
        $rows = [];
        $excelRow = 2;

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

                    $rows[] = [
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

        return $rows;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:K1')
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ]
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

                $sheet->getStyle("A1:K{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle("A1:K{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("H2:J{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');

                $sheet->getStyle('A1:K1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

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