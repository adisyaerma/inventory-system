<?php

namespace App\Http\Controllers\Web;

use App\Exports\ItemHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\LocationStock;
use App\Models\StagingInHistoryDetail;
use App\Models\StagingOutHistoryDetail;
use App\Models\StockMutation;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ItemHistoryController extends Controller
{
    /**
     * Halaman daftar item — mirip tabel Items/Stok Lokasi, tapi dengan
     * tombol "Riwayat" yang mengarah ke history lengkap barang tersebut.
     */
    public function index(Request $request)
    {
        $vendors = Vendor::orderBy('name')->get();

        return view('item_history.index', compact('vendors'));
    }

    /**
     * Data untuk DataTables di halaman daftar item (menu History Item).
     */
    public function data(Request $request)
    {
        $query = Item::query()
            ->with('vendor')
            ->orderByDesc('id');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->editColumn('item_code_internal', fn ($row) => $row->item_code_internal ?: '-')
            ->editColumn('name', fn ($row) => $row->name ?: '-')

            ->editColumn('description', function ($row) {

                if (empty($row->description)) {
                    return '-';
                }

                // Tampilkan versi ringkas di tabel, teks lengkapnya
                // tetap bisa dibaca lewat tooltip (title).
                $short = \Illuminate\Support\Str::limit($row->description, 80);

                return '<span title="'.e($row->description).'">'.e($short).'</span>';
            })

            ->addColumn('vendor', fn ($row) => $row->vendor->name ?? '-')

            ->filterColumn('vendor', function ($query, $keyword) {
                $query->whereHas('vendor', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })

            // Total stok barang ini di semua lokasi (dijumlah dari location_stock).
            ->addColumn('total_qty', function ($row) {
                $qty = LocationStock::where('item_id', $row->id)->sum('quantity');

                return number_format($qty, 0, ',', '.');
            })

            // Tanggal transaksi mutasi terakhir untuk barang ini — sekadar
            // sinyal cepat "kapan terakhir barang ini bergerak", bukan
            // gabungan staging in/out (itu ada lengkap di halaman detail).
            ->addColumn('last_activity', function ($row) {
                $lastDate = StockMutation::where('item_id', $row->id)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->value('transaction_date');

                return $lastDate ? Carbon::parse($lastDate)->format('d-m-Y') : '-';
            })

            ->addColumn('action', function ($row) {
                $url = route('item-history.show', $row->id);

                return '
<a href="'.$url.'"
   class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-6" viewBox="0 0 24 24">
        <path d="M0 0h24v24H0z" fill="none"/>
        <path fill="currentColor" d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89l.07.14L9 12H6a7 7 0 1 1 2.05 4.95l-1.42 1.42A9 9 0 1 0 13 3m-1 5v5l4.28 2.54l.72-1.21l-3.5-2.08V8z"/>
    </svg>
    <span class="d-none d-md-inline ms-1">Riwayat</span>
</a>';
            })

            ->rawColumns([
                'action',
                'description',
            ])

            ->make(true);
    }

    /**
     * Halaman detail riwayat satu item. Diakses lewat:
     * - Menu "History Item" (tombol Riwayat)
     * - Klik nama/kode barang di halaman Stok Lokasi
     * - Klik nama/kode barang di halaman Stok Mutasi
     */
    public function show(Item $item)
    {
        $item->load('vendor');

        return view('item_history.detail', compact('item'));
    }

    /**
     * Data timeline (JSON) untuk halaman detail — dipakai oleh JS di
     * item_history.detail untuk render timeline & breakdown lokasi.
     */
    public function timeline(Item $item)
    {
        $data = $this->buildHistoryData($item);

        return response()->json([
            'item' => [
                'item_code_internal' => $item->item_code_internal,
                'name' => $item->name,
                'vendor' => $item->vendor->name ?? '-',
                'total_qty' => $data['total_qty'],
            ],
            'locations' => $data['locations'],
            'timeline' => $data['timeline'],
        ]);
    }

    /**
     * Export laporan riwayat item ini ke Excel — data yang diexport
     * persis sama dengan yang tampil di timeline halaman detail.
     */
    public function export(Item $item)
    {
        $item->load('vendor');

        $data = $this->buildHistoryData($item);

        // item_code_internal bisa mengandung karakter "/" atau "\" (mis.
        // kode barang berformat "123/456") — kalau ditempel apa adanya ke
        // nama file, HTTP header Content-Disposition menolaknya karena
        // dua karakter itu dianggap pemisah path. Ganti dulu dengan "-"
        // supaya aman dipakai sebagai nama file.
        $safeCode = str_replace(
            ['/', '\\'],
            '-',
            $item->item_code_internal ?: $item->id
        );

        $fileName = 'Riwayat - '.$safeCode.'.xlsx';

        return Excel::download(
            new ItemHistoryExport($item, $data['timeline'], $data['total_qty']),
            $fileName
        );
    }

    /**
     * Kumpulkan & gabungkan data lokasi + timeline (mutasi, staging in,
     * staging out) untuk satu item. Dipakai bareng oleh timeline() (JSON,
     * ditampilkan di halaman detail) dan export() (Excel) supaya kedua
     * fitur ini selalu menampilkan data yang identik.
     */
    private function buildHistoryData(Item $item): array
    {
        $locations = LocationStock::with('location')
            ->where('item_id', $item->id)
            ->get()
            ->map(function ($row) {
                return [
                    'location' => $row->location->location_name ?? '-',
                    'lot' => $row->lot,
                    'quantity' => (float) $row->quantity,
                ];
            })
            ->values();

        // Ambil SEMUA event staging in dulu (dipakai dua kali di bawah:
        // untuk mencocokkan mutasi yang berasal dari "moved_to_stock",
        // dan untuk baris timeline staging in itu sendiri).
        $stagingInDetails = StagingInHistoryDetail::with('history')
            ->whereHas('history', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })
            ->get();

        // "moved_to_stock" SELALU membuat satu baris StockMutation (qty_in)
        // bareng-bareng di StagingInController::moveToStock(), dan id
        // mutation-nya disimpan di meta.stock_mutation_id. Supaya tidak
        // tampil dobel di timeline (sekali sebagai "dipindah ke stok",
        // sekali lagi sebagai "mutasi masuk"), kita cocokkan di sini —
        // nanti HANYA baris mutasi yang ditampilkan, tapi labelnya
        // diperjelas bahwa itu berasal dari staging in.
        $movedToStockByMutationId = $stagingInDetails
            ->where('event_type', 'moved_to_stock')
            ->filter(fn ($d) => ! empty($d->meta['stock_mutation_id']))
            ->keyBy(fn ($d) => $d->meta['stock_mutation_id']);

        $mutationRows = StockMutation::with('location')
            ->where('item_id', $item->id)
            ->get();

        $stagingOutDetails = StagingOutHistoryDetail::with('history')
            ->whereHas('history', function ($q) use ($item) {
                $q->where('item_id', $item->id);
            })
            ->get();

        // applyStockOut() SELALU membuat satu baris mutasi keluar (qty_out)
        // untuk staging out yang sumbernya stok, dengan transaction_number =
        // no SO. Karena description mutasi tidak lagi memuat ID staging out,
        // pencocokan dilakukan lewat no SO + qty, satu-lawan-satu (satu
        // mutasi hanya dipasangkan dengan satu event "created").
        //
        // Hasilnya:
        // - $stagingOutByMutationId: id mutasi => event staging out asalnya,
        //   dipakai untuk memberi label "dari Staging Out" pada baris mutasi.
        // - $createdCoveredByMutation: event "created" yang sudah terwakili
        //   baris mutasi, jadi tidak ditampilkan lagi (tidak dobel).
        // Staging out yang tidak punya mutasi (mis. berasal dari staging in)
        // tidak cocok dengan apa pun dan tetap tampil apa adanya.
        $mutationOutIdsByKey = [];

        foreach ($mutationRows->filter(fn ($m) => $m->qty_out > 0 && ! empty($m->transaction_number))->sortBy('created_at') as $m) {
            $mutationOutIdsByKey[$m->transaction_number.'|'.(float) $m->qty_out][] = $m->id;
        }

        $stagingOutByMutationId = [];
        $createdCoveredByMutation = [];

        foreach ($stagingOutDetails->where('event_type', 'created')->sortBy('created_at') as $detail) {
            $soNumber = $detail->history?->so_number;

            if (empty($soNumber)) {
                continue;
            }

            $key = $soNumber.'|'.(float) $detail->qty_after;

            if (! empty($mutationOutIdsByKey[$key])) {
                $mutationId = array_shift($mutationOutIdsByKey[$key]);

                $stagingOutByMutationId[$mutationId] = $detail;
                $createdCoveredByMutation[$detail->id] = true;
            }
        }

        $mutations = $mutationRows
            ->map(function ($row) use ($movedToStockByMutationId, $stagingOutByMutationId) {
                $isIn = $row->qty_in > 0;

                $fromStagingIn = $isIn ? $movedToStockByMutationId->get($row->id) : null;

                $label = $isIn ? 'Barang Masuk (Mutasi)' : 'Barang Keluar (Mutasi)';
                $notes = $row->description;

                if ($fromStagingIn) {
                    $label = 'Mutasi Masuk (dari Staging In)';

                    $poNumber = $fromStagingIn->history?->po_number;

                    $notes = trim(
                        'Dipindahkan dari Staging In'.($poNumber ? ' (PO: '.$poNumber.')' : '').
                        ($row->description ? ' - '.$row->description : '')
                    );
                }

                $fromStagingOut = ! $isIn ? ($stagingOutByMutationId[$row->id] ?? null) : null;

                if ($fromStagingOut) {
                    $label = 'Mutasi Keluar (dari Staging Out)';

                    $soHistory = $fromStagingOut->history;

                    $notes = trim(
                        'Dikeluarkan lewat Staging Out'.
                        ($soHistory?->customer ? ' - Customer: '.$soHistory->customer : '').
                        ($soHistory?->do_number ? ' | DO: '.$soHistory->do_number : '')
                    );
                }

                return [
                    'date' => optional($row->transaction_date)->toDateString(),
                    // Dipakai buat sorting HARUS pakai created_at (waktu
                    // sistem saat baris ini benar-benar dibuat), bukan
                    // transaction_date. transaction_date itu tanggal bisnis
                    // yang diinput manual (cuma tanggal, jamnya 00:00) —
                    // kalau dipakai untuk sorting, bisa salah urutan
                    // dibanding event staging in/out yang sort_key-nya pakai
                    // created_at presisi detik.
                    'sort_key' => optional($row->created_at)->timestamp
                        ?? optional($row->transaction_date)->timestamp
                        ?? 0,
                    'source' => 'mutation',
                    'type' => $isIn ? 'mutation_in' : 'mutation_out',
                    'label' => $label,
                    'qty' => $isIn ? $row->qty_in : $row->qty_out,
                    'location' => $row->location->location_name ?? '-',
                    'lot' => $row->lot,
                    'reference' => $row->transaction_number,
                    'notes' => $notes,
                ];
            });

        // Diambil dari staging_in_history_details (log permanen), BUKAN dari
        // tabel staging_ins langsung — staging_ins bersifat "live", qty-nya
        // di-update setiap kali sebagian dipindah (lihat
        // StagingInController::moveToStock/moveToStagingOut), jadi kalau
        // dibaca langsung dari situ, history akan menampilkan sisa qty
        // terakhir, bukan kejadian aslinya. Tiap baris di history_details
        // adalah satu kejadian yang tidak pernah diubah lagi, jadi qty
        // aslinya (mis. "10 masuk", lalu "2 dipindah") tetap tercatat apa
        // adanya walau qty sisa di staging_ins sudah berubah.
        //
        // "updated" & "deleted" disembunyikan karena cuma perubahan
        // administratif, bukan pergerakan barang. "moved_to_stock" juga
        // disembunyikan di sini karena sudah direpresentasikan lewat baris
        // mutasi di atas (lihat $movedToStockByMutationId).
        $hiddenStagingInEvents = ['updated', 'deleted', 'moved_to_stock'];

        $stagingIns = $stagingInDetails
            ->reject(fn ($d) => in_array($d->event_type, $hiddenStagingInEvents))
            ->map(function ($row) {
                $history = $row->history;

                $qty = match ($row->event_type) {
                    'created' => $row->qty_after,
                    default => $row->qty_change ?? $row->qty_after,
                };

                return [
                    'date' => optional($row->created_at)->toDateString(),
                    'sort_key' => optional($row->created_at)->timestamp ?? 0,
                    'source' => 'staging_in',
                    'type' => 'staging_in_'.$row->event_type,
                    'label' => 'Staging In - '.$row->event_label,
                    'qty' => $qty,
                    'location' => $history->location ?? '-',
                    'lot' => $row->meta['lot'] ?? null,
                    'reference' => $history->po_number,
                    'notes' => $row->notes ?: trim(
                        ($history->supplier_origin ? 'Supplier: '.$history->supplier_origin.' ' : '')
                    ),
                ];
            })
            ->values();

        // Sama seperti staging in di atas: diambil dari
        // staging_out_history_details (log permanen), bukan tabel
        // staging_outs langsung.
        //
        // Yang disembunyikan:
        // - "updated" & "deleted": cuma perubahan administratif.
        // - "delivered": info barang sudah dikirim tidak perlu tampil di
        //   history item (barangnya sudah tercatat keluar lewat mutasi).
        // - "created" yang sudah punya baris mutasi keluar (lihat di
        //   bawah), supaya satu barang keluar tidak tampil dobel.
        $hiddenStagingOutEvents = ['updated', 'deleted', 'delivered'];

        $stagingOuts = $stagingOutDetails
            ->reject(fn ($d) => in_array($d->event_type, $hiddenStagingOutEvents)
                || isset($createdCoveredByMutation[$d->id]))
            ->map(function ($row) {
                $history = $row->history;

                $qty = match ($row->event_type) {
                    'created' => $row->qty_after,
                    default => $row->qty_change ?? $row->qty_after,
                };

                return [
                    'date' => optional($row->created_at)->toDateString(),
                    'sort_key' => optional($row->created_at)->timestamp ?? 0,
                    'source' => 'staging_out',
                    'type' => 'staging_out_'.$row->event_type,
                    'label' => 'Staging Out - '.$row->event_label,
                    'qty' => $qty,
                    'location' => null,
                    'lot' => null,
                    'reference' => $history->so_number,
                    'notes' => $row->notes ?: trim(
                        ($history->customer ? 'Customer: '.$history->customer.' ' : '').
                        ($history->do_number ? '| DO: '.$history->do_number : '')
                    ),
                ];
            });

        $timeline = $mutations
            ->concat($stagingIns)
            ->concat($stagingOuts)
            ->sortBy('sort_key')
            ->values();

        return [
            'locations' => $locations,
            'timeline' => $timeline,
            'total_qty' => $locations->sum('quantity'),
        ];
    }
}