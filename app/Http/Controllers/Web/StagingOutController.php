<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutExport;
use App\Exports\StagingOutTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StagingOutImport;
use App\Imports\StagingOutStockImport;
use App\Models\Item;
use App\Models\LocationStock;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StockMutation;
use App\Services\StagingOutHistoryService;
use App\Services\StagingOutStockService;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StagingOutController extends Controller
{
    public function __construct(protected StagingOutHistoryService $history)
    {
    }

    public function index(Request $request)
    {
        $totalEntry = StagingOut::count();

        $totalQty = StagingOut::sum('qty');
        $customers = StagingOut::select('customer')->distinct()->orderBy('customer')->pluck('customer');

        $sudahPicking = StagingOut::whereNotNull('picking_date')->count();

        $sudahDikirim = StagingOut::whereNotNull('delivery_receipt_date')->count();

        $belumPicking = StagingOut::whereNull('picking_date')->count();

        $belumDikirim = StagingOut::whereNull('delivery_receipt_date')->count();

        return view('staging_out', compact(
            'totalEntry',
            'totalQty',
            'sudahPicking',
            'sudahDikirim',
            'customers'
        ))->with([
            'activeFilter' => $request->query('filter'),
            'activeStatus' => $request->query('status'),
        ]);
    }

    /**
     * Server-side DataTables source.
     */
    public function data(Request $request)
    {
        $query = StagingOut::query()
            ->select('staging_outs.*')
            ->leftJoin('items', 'items.id', '=', 'staging_outs.item_id')
            ->with('item')
            ->orderByDesc('staging_outs.id');

        // 1. Status
        //    Menerima dua "kosakata": yang lama (belum_picking / sudah_picking
        //    / belum_dikirim / sudah_dikirim) dan istilah Indonesia yang
        //    dipakai kartu status & notifikasi di dashboard (menunggu_picking
        //    / siap_kirim / terlambat / selesai), supaya link dari dashboard
        //    benar-benar memfilter data, bukan cuma nyasar ke halaman kosong.
        if (in_array($request->status, ['belum_picking', 'menunggu_picking'])) {
            $query->whereNull('staging_outs.picking_date');

        } elseif ($request->status === 'sudah_picking') {
            $query->whereNotNull('staging_outs.picking_date');

        } elseif ($request->status === 'siap_kirim') {
            $query->whereNotNull('staging_outs.picking_date')
                ->whereNull('staging_outs.delivery_receipt_date');

        } elseif ($request->status === 'belum_dikirim') {
            $query->whereNull('staging_outs.delivery_receipt_date');

        } elseif (in_array($request->status, ['sudah_dikirim', 'selesai'])) {
            $query->whereNotNull('staging_outs.delivery_receipt_date');

        } elseif ($request->status === 'terlambat') {
            $query->whereDate('staging_outs.delivery_instruction_date', '<', now())
                ->whereNull('staging_outs.delivery_receipt_date');
        }

        // 2. Customer
        if ($request->filled('customer')) {
            $query->where('staging_outs.customer', $request->customer);
        }

        // 2b. Lokasi (staging/packing/outbound). Nilai "belum_diisi" dipakai
        //     untuk mencari baris yang lokasinya masih kosong.
        if ($request->filled('staging_location')) {
            if ($request->staging_location === 'belum_diisi') {
                $query->whereNull('staging_outs.staging_location');
            } elseif (in_array($request->staging_location, ['staging', 'packing', 'outbound'])) {
                $query->where('staging_outs.staging_location', $request->staging_location);
            }
        }

        // 3. Rentang tanggal — HANYA satu kolom, sesuai date_type yang dipilih
        $dateColumn = in_array($request->date_type, ['delivery_instruction_date', 'picking_date', 'delivery_receipt_date'])
            ? $request->date_type
            : 'delivery_instruction_date';

        if ($request->filled('start_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '<=', $request->end_date);
        }

        // 4. Overdue
        if ($request->overdue == 1) {
            $query->whereDate('staging_outs.delivery_instruction_date', '<', now())->whereNull('staging_outs.delivery_receipt_date');
        }

        // 5. Pengiriman dijadwalkan hari ini (dipakai notifikasi dashboard)
        if ($request->query('filter') === 'today') {
            $query->whereDate('staging_outs.delivery_instruction_date', now()->toDateString())
                ->whereNull('staging_outs.delivery_receipt_date');
        }

        return DataTables::eloquent($query)

            ->addIndexColumn()

            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">';
            })

            ->editColumn('item_code', function ($row) {
                if (! $row->item) {
                    return '-';
                }

                return '
                <small class="fw-bold">'.e($row->item->item_code_internal).'</small>
                <div class="text-muted">'.e($row->item->name).'</div>
            ';
            })
            ->filterColumn('item_code', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('items.name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('item_code', 'items.item_code_internal $1')
            ->editColumn('line_item', function ($row) {
                return $row->line_item ?: '-';
            })
            ->filterColumn('line_item', function ($query, $keyword) {
                $query->where('staging_outs.line_item', 'like', "%{$keyword}%");
            })
            ->orderColumn('line_item', 'staging_outs.line_item $1')

            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('staging_outs.customer', 'like', "%{$keyword}%");
            })

            ->orderColumn('customer', 'staging_outs.customer $1')

            // Badge kecil supaya kelihatan mana entry "stok" vs "eksternal"
            // langsung dari kolom kode barang, tanpa nambah kolom baru.
            ->editColumn('source_type', function ($row) {
                return $row->source_type === 'stock'
                    ? '<span class="badge bg-info-subtle text-info">Stok</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Eksternal</span>';
            })

            ->editColumn('delivery_instruction_date', function ($row) {
                return optional($row->delivery_instruction_date)->format('d M Y');
            })

            ->editColumn('picking_date', function ($row) {

                if ($row->picking_date) {
                    return optional($row->picking_date)->format('d M Y');
                }

                return '
                <button type="button"
                    class="btn btn-sm btn-confirm-picking btnConfirmPicking"
                    data-id="'.$row->id.'"
                    data-source-type="'.e($row->source_type).'"
                    data-item-id="'.e($row->item_id).'"
                    data-location-id="'.e($row->location_id).'"
                    data-qty="'.e($row->qty).'"
                    data-lot="'.e($row->lot).'"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmPickingModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="me-1">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor" d="M20 3H4a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1M9 17l-4-4l1.41-1.41L9 14.17l7.59-7.59L18 8z"/>
                    </svg>
                    Konfirmasi Picking
                </button>';
            })

            // Lokasi (staging/packing/outbound): hanya bisa diubah langsung
            // dari select di baris tabel, dan hanya muncul setelah picking
            // sudah dikonfirmasi (picking_date terisi). Warnanya mengikuti
            // status yang dipilih (lihat class loc-* di CSS blade).
            ->editColumn('staging_location', function ($row) {

                if (! $row->picking_date) {
                    return '-';
                }

                $options = [
                    '' => ['label' => '- Pilih -', 'class' => 'loc-empty'],
                    'staging' => ['label' => '🟡 Staging', 'class' => 'loc-staging'],
                    'packing' => ['label' => '🔵 Packing', 'class' => 'loc-packing'],
                    'outbound' => ['label' => '🟢 Outbound', 'class' => 'loc-outbound'],
                ];

                $current = $row->staging_location ?? '';
                $currentClass = $options[$current]['class'] ?? 'loc-empty';

                $html = '<select class="form-select form-select-sm select-staging-location '.$currentClass.'" data-id="'.$row->id.'">';

                foreach ($options as $value => $opt) {
                    $selected = $current === $value ? ' selected' : '';
                    $html .= '<option value="'.$value.'" data-class="'.$opt['class'].'"'.$selected.'>'.$opt['label'].'</option>';
                }

                $html .= '</select>';

                return $html;
            })

            ->editColumn('do_number', function ($row) {
                return $row->do_number ?: '-';
            })

            // Tgl Resi Pengiriman: kalau sudah terisi tampil sebagai tanggal.
            // Kalau masih kosong DAN picking sudah dikonfirmasi, tampil
            // tombol "Konfirmasi Kirim" (mirip tombol Konfirmasi Picking).
            // Sebelum picking dikonfirmasi barang belum siap kirim, jadi
            // cukup tampil "-".
            ->editColumn('delivery_receipt_date', function ($row) {

                if ($row->delivery_receipt_date) {
                    return optional($row->delivery_receipt_date)->format('d M Y');
                }

                if (! $row->picking_date) {
                    return '-';
                }

                return '
                <button type="button"
                    class="btn btn-sm btn-confirm-delivery btnConfirmDelivery"
                    data-id="'.$row->id.'"
                    data-source-type="'.e($row->source_type).'"
                    data-item-id="'.e($row->item_id).'"
                    data-location-id="'.e($row->location_id).'"
                    data-qty="'.e($row->qty).'"
                    data-lot="'.e($row->lot).'"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeliveryModal">

                    <i class="bx bxs-truck me-1"></i>
                    Konfirmasi Kirim
                </button>';
            })

            ->addColumn('action', function ($row) {

                return '
            <div class="d-flex align-items-center gap-1">

                <button
                    class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
                    type="button"
                    data-id="'.$row->id.'"
                    data-bs-toggle="modal"
                    data-bs-target="#editStagingOutModal">

                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path fill="currentColor"
                            d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
                    </svg>

                </button>

                <form action="'.route('stagings-out.destroy', $row->id).'"
                      method="POST"
                      class="form-hapus m-0">

                    '.csrf_field().'
                    '.method_field('DELETE').'

                    <button type="submit"
                        class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0">

                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path fill="currentColor"
                                d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>

                    </button>

                </form>

            </div>';
            })

            ->rawColumns([
                'checkbox',
                'item_code',
                'source_type',
                'picking_date',
                'staging_location',
                'delivery_receipt_date',
                'action',
            ])

            ->make(true);
    }

    /**
     * Update kolom staging_location (staging/packing/outbound) saja, dipicu
     * dari select langsung di baris tabel. Tidak lewat modal tambah/edit,
     * dan hanya boleh diubah kalau picking_date sudah terisi.
     */
    public function updateLocation(Request $request, StagingOut $stagingOut)
    {
        $validated = $request->validate([
            'staging_location' => ['nullable', 'in:staging,packing,outbound'],
        ], [
            'staging_location.in' => 'Lokasi tidak valid.',
        ]);

        if (! $stagingOut->picking_date) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi hanya bisa diubah setelah tanggal picking diisi.',
            ], 422);
        }

        $stagingOut->update([
            'staging_location' => $validated['staging_location'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi berhasil diperbarui.',
        ]);
    }

    /**
     * Search the items table for the "Kode Barang" TomSelect used on the
     * add/edit staging out forms (mirrors StagingInController::searchStock).
     *
     * Kata kunci dicocokkan ke SEMUA jenis kode barang (internal,
     * customer, supplier), bukan cuma item_code_internal -- supaya kalau
     * user ketik kode dari sisi customer/supplier yang beda dari kode
     * internal, itemnya tetap ketemu.
     */
    public function searchStock(Request $request)
    {
        $keyword = $request->q;

        $items = Item::query()
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('item_code_customer', 'like', "%{$keyword}%")
                        ->orWhere('item_code_supplier', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(
            $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->item_code_internal.' | '.$item->name,
                    'item_code' => $item->item_code_internal,
                    'item_name' => $item->name,
                ];
            })
        );
    }

    /**
     * Untuk source_type = stock: daftar lokasi yang PUNYA stok (qty > 0)
     * dari item yang dipilih. Dipakai TomSelect "Lokasi" pada modal
     * tambah/edit staging out.
     */
    public function searchLocationForItem(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
        ]);

        $locations = LocationStock::with('location')
            ->where('item_id', $request->item_id)
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('location_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'id' => $first->location_id,
                    'text' => optional($first->location)->location_name ?? ('Lokasi #'.$first->location_id),
                    'total_quantity' => (float) $rows->sum('quantity'),
                ];
            })
            ->values();

        return response()->json($locations);
    }

    /**
     * Untuk source_type = stock: daftar lot (beserta sisa qty) dari
     * kombinasi item + lokasi yang sudah dipilih. Dipakai TomSelect "Lot".
     */
    public function searchLotForItemLocation(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        $lots = LocationStock::where('item_id', $request->item_id)
            ->where('location_id', $request->location_id)
            ->where('quantity', '>', 0)
            ->orderBy('lot')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->lot ?? '',
                    'text' => $row->lot ?: '(Tanpa Lot)',
                    'quantity' => (float) $row->quantity,
                ];
            });

        return response()->json($lots);
    }

    public function template()
    {
        return Excel::download(new StagingOutTemplateExport, 'template_staging_out.xlsx');
    }

    /**
     * Export staging out data to Excel, honoring the same filters used by the
     * search box and date range on the index page.
     */
    public function export(Request $request)
    {
        $filename = 'staging_out_'.now()->format('d-m-Y').'.xlsx';

        return Excel::download(new StagingOutExport($request), $filename);
    }

    public function import(Request $request)
    {
        // Pilihan "Langsung dari Stok" di modal import dialihkan ke
        // importStock(), karena aturannya berbeda (tidak truncate, lokasi &
        // lot dicari otomatis, baris ambigu dilewati).
        if ($request->input('source') === 'stock') {
            return $this->importStock($request);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'mode' => ['required', Rule::in(['reset', 'append'])],
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
            'mode.required' => 'Pilih mode import terlebih dahulu (reset atau tambahkan)',
            'mode.in' => 'Mode import tidak valid',
        ]);

        $isReset = $request->mode === 'reset';

        try {

            if ($isReset) {

                // Catat dulu seluruh data lama ke history SEBELUM dihapus,
                // dan hapus di dalam transaction yang sama supaya history
                // dan data utamanya selalu konsisten.
                //
                // PENTING: pakai delete(), BUKAN truncate(). truncate()
                // me-reset counter auto-increment ID, sehingga baris baru
                // bisa mendapat ID yang dulu pernah dipakai baris lama dan
                // history barunya "nyasar" ke thread history lama.
                // delete() tidak menyentuh counter, jadi ID baru selalu
                // lanjut dan tidak pernah bentrok.
                DB::transaction(function () {
                    $existing = StagingOut::all();

                    $this->history->logResetByImport($existing);

                    // Baris yang barangnya diambil dari stok ikut dihapus,
                    // jadi transaksinya harus dibatalkan juga: qty
                    // dikembalikan ke stok & mutasi keluarnya dihapus.
                    foreach ($existing as $staging) {
                        if ($staging->source_type === 'stock' && $staging->item_id && $staging->location_id) {
                            $this->reverseStockOut($staging);
                        }
                    }

                    StagingOut::query()->delete();
                });
            }

            // Mode 'append' sengaja TIDAK menghapus data lama — baris dari
            // file Excel ditambahkan sebagai data baru di atas data yang
            // sudah ada.
            Excel::import(app(StagingOutImport::class), $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('stagings-out.index')
            ->with('success', $isReset
                ? 'Data staging lama berhasil diganti dengan data dari file'
                : 'Data staging berhasil ditambahkan dari file');
    }

    /**
     * Import khusus untuk staging out yang barangnya diambil dari STOK
     * (source_type = stock). Lihat StagingOutStockImport untuk aturan
     * lengkap pencarian lokasi/lot otomatisnya.
     *
     * SENGAJA TIDAK men-truncate data staging_outs yang sudah ada
     * (beda dengan import() biasa di atas) -- setiap baris di sini
     * adalah transaksi pengurangan stok sungguhan (location_stocks
     * dikurangi & dicatat di stock_mutations), jadi truncate akan
     * menghapus riwayat staging out tanpa mengembalikan stok yang sudah
     * terlanjur dikurangi. Import ini sifatnya menambah baris baru saja.
     *
     * Baris yang lokasi/lot-nya ambigu (atau stoknya kosong/kurang)
     * SENGAJA dilewati, bukan dianggap gagal total -- baris lain yang
     * berhasil tetap dilaporkan sukses (lihat pembagian pesan sukses/
     * error di bawah, dibangun dari $import->imported & $import->errors
     * SETELAH Excel::import() selesai, bukan dari Exception).
     */
    public function importStock(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        $import = app(StagingOutStockImport::class);

        try {
            // $import di sini disimpan dulu ke variabel (bukan langsung
            // app(...) di dalam call) supaya $import->imported &
            // $import->errors bisa dibaca lagi setelah proses selesai --
            // StagingOutStockImport SENGAJA tidak melempar Exception cuma
            // karena ada baris yang dilewati, jadi baris yang berhasil
            // tetap harus dilaporkan sebagai sukses, bukan ikut dianggap
            // gagal total.
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            // Ini murni error tak terduga (file rusak/corrupt, dsb) --
            // baris yang sengaja dilewati (lokasi ambigu, dll) tidak
            // pernah sampai sini.
            return redirect()
                ->route('stagings-out.index')
                ->with('error', $e->getMessage());
        }

        // Tidak ada satupun baris yang berhasil ATAUPUN dilewati -- besar
        // kemungkinan file kosong / tidak sesuai template.
        if ($import->imported === 0 && empty($import->errors)) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', 'Tidak ada baris yang bisa diimpor. Pastikan file tidak kosong dan formatnya sesuai template.');
        }

        // Semua baris berhasil, tidak ada yang dilewati.
        if (empty($import->errors)) {
            return redirect()
                ->route('stagings-out.index')
                ->with('success', "{$import->imported} baris staging out dari stok berhasil diimpor.");
        }

        // Sebagian (atau semua) baris dilewati -- tetap laporkan berapa
        // yang berhasil, plus daftar baris yang dilewati beserta
        // alasan & nomor baris Excel-nya supaya bisa dicek/diperbaiki.
        $summary = "{$import->imported} baris berhasil diimpor, ".count($import->errors).' baris dilewati:';

        return redirect()
            ->route('stagings-out.index')
            ->with('error', $summary."\n\n".implode("\n\n", $import->errors));
    }

    /**
     * Aturan validasi dasar untuk store/update. Validasi tambahan yang
     * bergantung pada source_type ditangani lewat Validator::after() di
     * masing-masing method, supaya pesan errornya spesifik.
     */
    protected function baseValidationRules(): array
    {
        return [
            'so_number' => ['nullable', 'max:255'],
            'customer' => ['nullable', 'max:255'],
            'source_type' => ['required', 'in:external,stock'],
            'item_id' => ['nullable', 'exists:items,id'],
            'line_item' => ['nullable', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'lot' => ['nullable', 'max:255'],
            'delivery_instruction_date' => ['nullable', 'date'],
            'picking_date' => ['nullable', 'date'],
            'do_number' => ['nullable', 'max:255'],
            'delivery_receipt_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Validasi tambahan khusus source_type = stock: item, lokasi, dan qty
     * wajib diisi, dan qty tidak boleh melebihi qty yang tersedia di
     * location_stock untuk kombinasi item/lokasi/lot yang dipilih.
     *
     * $current dipakai saat update, supaya qty yang SEDANG dipakai staging
     * out itu sendiri ikut dihitung balik sebagai stok "tersedia" (karena
     * nanti mutasi lamanya akan di-reverse dulu sebelum diterapkan ulang).
     */
    protected function validateStockAvailability(ValidatorContract $validator, Request $request, ?StagingOut $current = null): void
    {
        $validator->after(function ($validator) use ($request, $current) {
            if ($request->source_type !== 'stock') {
                return;
            }

            if (! $request->filled('item_id')) {
                $validator->errors()->add('item_id', 'Barang wajib dipilih untuk sumber stok.');
            }

            if (! $request->filled('location_id')) {
                $validator->errors()->add('location_id', 'Lokasi wajib dipilih untuk sumber stok.');
            }

            if (! $request->filled('qty') || (int) $request->qty < 1) {
                $validator->errors()->add('qty', 'Qty wajib diisi dan lebih dari 0 untuk sumber stok.');
            }

            if (! $request->filled('item_id') || ! $request->filled('location_id') || ! $request->filled('qty')) {
                return;
            }

            $stock = LocationStock::where('item_id', $request->item_id)
                ->where('location_id', $request->location_id)
                ->where('lot', $request->lot ?: null)
                ->first();

            $available = (float) optional($stock)->quantity;

            // Saat edit, staging out ini sendiri masih "menahan" qty lama di
            // kombinasi item/lokasi/lot yang SAMA — qty itu perlu dianggap
            // tersedia lagi karena mutasi lamanya akan direverse dulu.
            if ($current
                && $current->source_type === 'stock'
                && $current->item_id == $request->item_id
                && $current->location_id == $request->location_id
                && $current->lot == ($request->lot ?: null)
            ) {
                $available += (float) $current->qty;
            }

            if ((float) $request->qty > $available) {
                $validator->errors()->add(
                    'qty',
                    'Qty melebihi stok yang tersedia. Sisa stok: '.rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.')
                );
            }
        });
    }

    /**
     * Ambil baris location_stock untuk kombinasi item/lokasi/lot, dikunci
     * (lockForUpdate) supaya aman dari race condition saat dua staging out
     * dibuat/diubah bersamaan untuk stok yang sama.
     */
    protected function lockLocationStock(int $itemId, int $locationId, ?string $lot)
    {
        return LocationStock::query()
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('lot', $lot)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Kurangi qty di location_stock lalu catat sebagai qty_out di
     * stock_mutations. Dipanggil saat staging out (source_type = stock)
     * dibuat, atau saat diedit dan datanya masih/menjadi sumber stok.
     */
    protected function applyStockOut(StagingOut $stagingOut): void
    {
        $stock = $this->lockLocationStock((int) $stagingOut->item_id, (int) $stagingOut->location_id, $stagingOut->lot ?: null);

        if (! $stock || (float) $stock->quantity < (float) $stagingOut->qty) {
            throw new \RuntimeException('Stok tidak mencukupi untuk item/lokasi/lot yang dipilih.');
        }

        $stock->quantity = (float) $stock->quantity - (float) $stagingOut->qty;
        $stock->save();

        StockMutation::create([
            'item_id' => $stagingOut->item_id,
            'location_id' => $stagingOut->location_id,
            'lot' => $stagingOut->lot,
            'transaction_date' => $stagingOut->delivery_receipt_date
                ?? $stagingOut->picking_date
                ?? $stagingOut->delivery_instruction_date
                ?? now(),
            'transaction_number' => $stagingOut->so_number,
            'description' => $this->stockOutDescription($stagingOut),
            'qty_in' => 0,
            'qty_out' => $stagingOut->qty,
            'qty_balance' => $stock->quantity,
        ]);
    }

    /**
     * Kebalikan dari applyStockOut(): mengembalikan qty ke location_stock
     * DAN menghapus baris stock_mutations yang tadinya dibuat
     * applyStockOut() untuk staging out ini -- BUKAN membuat mutasi
     * pembalik baru. Logikanya ada di StagingOutStockService supaya
     * dipakai sama persis oleh halaman History (hapus history).
     *
     * Dipakai saat staging out (yang sumbernya stock) diedit datanya,
     * dihapus (satuan / massal), atau ikut terhapus oleh import reset.
     * Untuk kasus EDIT, urutannya di update(): reverseStockOut() ->
     * $stagingOut->update(...) -> applyStockOut().
     */
    protected function reverseStockOut(StagingOut $stagingOut): void
    {
        app(StagingOutStockService::class)->reverse($stagingOut);
    }

    /**
     * Teks description untuk baris stock_mutations: "<no SO> - <customer>".
     */
    protected function stockOutDescription(StagingOut $stagingOut): string
    {
        return app(StagingOutStockService::class)->description($stagingOut);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->baseValidationRules(), [
            'item_id.exists' => 'Barang tidak ditemukan',
            'location_id.exists' => 'Lokasi tidak ditemukan',
            'source_type.required' => 'Sumber barang wajib dipilih',
            'source_type.in' => 'Sumber barang tidak valid',
        ]);

        $this->validateStockAvailability($validator, $request);

        $validated = $validator->validate();

        // Barang eksternal tidak menyentuh stok — kosongkan lokasi/lot
        // biar tidak ada data nyasar walaupun user sempat mengisinya lalu
        // ganti pilihan ke "Eksternal".
        if ($validated['source_type'] === 'external') {
            $validated['location_id'] = null;
            $validated['lot'] = null;
        }

        try {
            $justDelivered = DB::transaction(function () use ($validated) {
                $staging = StagingOut::create($validated);

                // Stok tetap dipotong walau langsung selesai, karena
                // barangnya memang sudah keluar (mutasi stok tidak ikut
                // dihapus).
                if ($staging->source_type === 'stock') {
                    $this->applyStockOut($staging);
                }

                $this->history->logCreated($staging);

                // Tgl resi pengiriman sudah terisi sejak awal berarti barang
                // sudah terkirim: cukup riwayatnya saja yang disimpan.
                // Dicatat sebagai "delivered" (status Selesai), BUKAN
                // logDeleted(), lalu barisnya dihapus dari tabel aktif.
                $delivered = ! empty($validated['delivery_receipt_date']);

                if ($delivered) {
                    $this->history->logAutoDelivered(
                        $staging,
                        $staging->do_number,
                        $staging->delivery_receipt_date
                    );

                    // logCreated() tidak menyinkronkan picking_date ke
                    // header, jadi diisi di sini kalau form-nya mengisi.
                    if (! empty($validated['picking_date'])) {
                        StagingOutHistory::firstOrCreateForStaging($staging)
                            ->update(['picking_date' => $validated['picking_date']]);
                    }

                    $staging->delete();
                }

                return $delivered;
            });

            return response()->json([
                'success' => true,
                'message' => $justDelivered
                    ? 'Data tersimpan dan langsung dipindahkan ke history (sudah terkirim).'
                    : 'Data berhasil disimpan',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return the record as JSON for the AJAX edit modal.
     */
    public function edit(StagingOut $stagingOut)
    {
        $stagingOut->loadMissing('item');

        return response()->json([
            'id' => $stagingOut->id,
            'so_number' => $stagingOut->so_number,
            'customer' => $stagingOut->customer,
            'source_type' => $stagingOut->source_type,
            'item_id' => $stagingOut->item_id,
            'item_code' => optional($stagingOut->item)->item_code_internal,
            'item_name' => optional($stagingOut->item)->name,
            'location_id' => $stagingOut->location_id,
            'lot' => $stagingOut->lot,
            'line_item' => $stagingOut->line_item,
            'qty' => $stagingOut->qty,
            'delivery_instruction_date' => optional($stagingOut->delivery_instruction_date)->format('Y-m-d'),
            'picking_date' => optional($stagingOut->picking_date)->format('Y-m-d'),
            'do_number' => $stagingOut->do_number,
            'delivery_receipt_date' => optional($stagingOut->delivery_receipt_date)->format('Y-m-d'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StagingOut $stagingOut)
    {
        $validator = Validator::make($request->all(), $this->baseValidationRules(), [
            'item_id.exists' => 'Barang tidak ditemukan',
            'location_id.exists' => 'Lokasi tidak ditemukan',
            'source_type.required' => 'Sumber barang wajib dipilih',
            'source_type.in' => 'Sumber barang tidak valid',
        ]);

        $this->validateStockAvailability($validator, $request, $stagingOut);

        $validated = $validator->validate();

        if ($validated['source_type'] === 'external') {
            $validated['location_id'] = null;
            $validated['lot'] = null;
        }

        try {
            $justDelivered = DB::transaction(function () use ($validated, $stagingOut) {
                // Hitung perubahan SEBELUM update() — setelah update(),
                // getOriginal() sudah ikut ter-sync ke nilai baru.
                $changes = $this->history->diff($stagingOut, $validated);

                // Tangkap kondisi "baru saja dikirim": tgl resi pengiriman
                // yang tadinya kosong, sekarang diisi lewat update ini —
                // juga harus dicek SEBELUM update() menimpa nilai aslinya.
                $justDelivered = is_null($stagingOut->delivery_receipt_date)
                    && array_key_exists('delivery_receipt_date', $validated)
                    && ! is_null($validated['delivery_receipt_date']);

                // Jika baris ini SEBELUMNYA memotong stok, kembalikan dulu
                // qty-nya sebelum diupdate — supaya perubahan qty/lokasi/lot
                // (atau ganti jadi "external") tidak meninggalkan stok yang
                // sudah telanjur terpotong tapi tidak lagi tercatat di sini.
                $wasStock = $stagingOut->source_type === 'stock' && $stagingOut->item_id && $stagingOut->location_id;

                if ($wasStock) {
                    $this->reverseStockOut($stagingOut);
                }

                $stagingOut->update($validated);

                // Terapkan lagi pemotongan stok dengan data yang baru, kalau
                // baris ini (masih/menjadi) bersumber dari stok.
                if ($stagingOut->source_type === 'stock') {
                    $this->applyStockOut($stagingOut);
                }

                $this->history->logUpdated($stagingOut, $changes);

                // Begitu tgl resi pengiriman terisi, data tidak lagi relevan di
                // tabel aktif staging out — cukup riwayatnya saja yang
                // disimpan. Dicatat sebagai "delivered" (status Selesai),
                // BUKAN logDeleted() yang artinya dibuang/dibatalkan.
                // Log dulu baru dihapus, supaya history tetap utuh.
                if ($justDelivered) {
                    $this->history->logAutoDelivered(
                        $stagingOut,
                        $stagingOut->do_number,
                        $stagingOut->delivery_receipt_date
                    );

                    $stagingOut->delete();
                }

                return $justDelivered;
            });

            return response()->json([
                'success' => true,
                'message' => $justDelivered
                    ? 'Tanggal kirim berhasil dikonfirmasi. Data dipindahkan ke history.'
                    : 'Data berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StagingOut $stagingOut)
    {
        try {
            DB::transaction(function () use ($stagingOut) {
                // Batalkan staging out yang sumbernya stok berarti barangnya
                // batal keluar — kembalikan qty ke location_stock dulu
                // sebelum baris ini dihapus.
                if ($stagingOut->source_type === 'stock' && $stagingOut->item_id && $stagingOut->location_id) {
                    $this->reverseStockOut($stagingOut);
                }

                // Catat history SEBELUM baris aslinya benar-benar hilang.
                $this->history->logDeleted($stagingOut);

                $stagingOut->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove multiple resources at once.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_outs,id',
        ]);

        try {
            $count = DB::transaction(function () use ($request) {
                // Load dulu baris-barisnya SEBELUM dihapus — logBulkDeleted()
                // butuh data aslinya (qty, dst), bukan cuma ID.
                $stagings = StagingOut::whereIn('id', $request->ids)->get();

                foreach ($stagings as $staging) {
                    if ($staging->source_type === 'stock' && $staging->item_id && $staging->location_id) {
                        $this->reverseStockOut($staging);
                    }
                }

                $this->history->logBulkDeleted($stagings);

                StagingOut::whereIn('id', $request->ids)->delete();

                return $stagings->count();
            });

            return response()->json([
                'success' => true,
                'message' => $count.' data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}