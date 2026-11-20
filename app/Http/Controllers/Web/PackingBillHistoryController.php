<?php

namespace App\Http\Controllers\Web;

use App\Exports\PackingBillHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\PackingBill;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

/**
 * History Bill Packing: daftar SEMUA bill (1 SO = 1 bill), termasuk bill
 * milik SO yang barangnya sudah dikirim dan tidak ada lagi di Staging Out.
 *
 * Bill disimpan di packing_bills dan tidak ikut hilang saat baris staging
 * out dipindah ke history, jadi halaman ini tidak bergantung pada status
 * staging out. Melihat detail dilakukan di panel kanan; mengubah bill
 * memakai halaman form yang sama dengan di Staging Out (packing-bills.form).
 */
class PackingBillHistoryController extends Controller
{
    /**
     * Status SO untuk sebuah bill, dihitung langsung di SQL:
     * - staging   : SO masih punya baris di Staging Out (belum dikirim)
     * - delivered : tidak ada lagi di Staging Out dan sudah pernah terkirim
     * - other     : sisanya (mis. dihapus sebelum terkirim)
     */
    private const STATUS_SQL = "CASE
        WHEN EXISTS (SELECT 1 FROM staging_outs sx WHERE sx.so_number = packing_bills.so_number) THEN 'staging'
        WHEN EXISTS (SELECT 1 FROM staging_out_histories hx WHERE hx.so_number = packing_bills.so_number AND hx.delivery_date IS NOT NULL) THEN 'delivered'
        ELSE 'other'
    END";

    private const STATUS_META = [
        'staging' => ['label' => 'Di Staging Out', 'color' => 'warning'],
        'delivered' => ['label' => 'Terkirim', 'color' => 'success'],
        'other' => ['label' => 'Tidak aktif', 'color' => 'secondary'],
    ];

    public function index()
    {
        $statusCount = fn (string $status) => PackingBill::whereRaw(self::STATUS_SQL.' = ?', [$status])->count();

        return view('packing_bill_history', [
            'totalBill' => PackingBill::count(),
            'createdToday' => PackingBill::whereDate('created_at', now()->toDateString())->count(),
            'totalNilai' => (float) PackingBill::sum('total'),
            'stagingCount' => $statusCount('staging'),
            'deliveredCount' => $statusCount('delivered'),
            'statusOptions' => collect(self::STATUS_META)->map(fn ($m) => $m['label'])->all(),
        ]);
    }

    /**
     * Query bill + filter status & periode. Dipakai tabel (data) DAN export,
     * supaya hasil export selalu sama dengan yang tampil di tabel.
     */
    private function filteredQuery(Request $request)
    {
        $query = PackingBill::query()
            ->select('packing_bills.*')
            ->selectRaw(self::STATUS_SQL.' as so_status');

        if ($status = $request->input('status')) {
            if (isset(self::STATUS_META[$status])) {
                $query->whereRaw(self::STATUS_SQL.' = ?', [$status]);
            }
        }

        if ($start = $request->input('start_date')) {
            $query->whereDate('packing_bills.tanggal', '>=', $start);
        }

        if ($end = $request->input('end_date')) {
            $query->whereDate('packing_bills.tanggal', '<=', $end);
        }

        return $query;
    }

    public function data(Request $request)
    {
        $query = $this->filteredQuery($request)->withCount(['kolis', 'lines']);

        // Terbaru diubah di atas, kecuali user klik header kolom.
        if (! $request->has('order')) {
            $query->orderByDesc('packing_bills.updated_at')->orderByDesc('packing_bills.id');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('so_number', fn ($row) => '<span class="fw-semibold">'.e($row->so_number).'</span>')
            ->editColumn('customer', fn ($row) => e($row->customer ?: '-'))
            ->editColumn('tanggal', fn ($row) => optional($row->tanggal)->format('d-m-Y') ?: '-')
            ->editColumn('total', fn ($row) => '<span class="fw-semibold">'.$this->rupiah($row->total).'</span>')
            ->addColumn('status', function ($row) {
                $meta = self::STATUS_META[$row->so_status] ?? self::STATUS_META['other'];

                return '<span class="badge bg-'.$meta['color'].'-subtle text-'.$meta['color'].'">'.$meta['label'].'</span>';
            })
            ->addColumn('diubah', function ($row) {
                if (! $row->diubah_oleh) {
                    return '<span class="text-muted small">Belum diubah</span>';
                }

                return '<div class="small">'.optional($row->updated_at)->format('d-m-Y H:i').'</div>'
                    .'<div class="text-muted" style="font-size:.72rem;">'.e($row->diubah_oleh).'</div>';
            })
            ->orderColumn('diubah', 'packing_bills.updated_at $1')
            ->addColumn('action', function ($row) {
                $edit = route('packing-bills.form', ['so_number' => $row->so_number, 'from' => 'history']);
                $print = route('packing-bills.print', $row->id);

                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnViewBill"
                            data-id="'.$row->id.'" title="Lihat detail"><i class="bi bi-eye"></i></button>
                        <a href="'.e($edit).'" class="btn btn-sm bg-warning bg-opacity-10 text-warning rounded-3 border-0"
                            title="Edit bill"><i class="bi bi-pencil-square"></i></a>
                        <a href="'.e($print).'" target="_blank" class="btn btn-sm bg-success bg-opacity-10 text-success rounded-3 border-0"
                            title="Cetak / PDF"><i class="bi bi-printer"></i></a>
                    </div>';
            })
            ->rawColumns(['so_number', 'total', 'status', 'diubah', 'action'])
            ->make(true);
    }

    /**
     * Export laporan bill ke Excel (1 sheet). Filter yang sama dengan tabel:
     * status, periode tanggal bill, dan kata kunci pencarian (No. SO / customer).
     */
    public function export(Request $request)
    {
        $query = $this->filteredQuery($request);

        $keyword = trim((string) $request->input('search'));

        if ($keyword !== '') {
            $like = '%'.addcslashes(mb_strtolower($keyword), '%_\\').'%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(packing_bills.so_number) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(COALESCE(packing_bills.customer, '')) LIKE ?", [$like]);
            });
        }

        $bills = $query
            ->with([
                'kolis' => fn ($q) => $q->orderBy('no_koli'),
                'kolis.items',
                'kolis.lines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'sharedLines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('packing_bills.tanggal')
            ->orderBy('packing_bills.so_number')
            ->get();

        $line = fn ($l) => [
            'nama' => $l->nama_material,
            'qty' => (float) $l->qty,
            'satuan' => $l->satuan,
            'harga' => (float) $l->harga,
            'subtotal' => (float) $l->subtotal,
        ];

        $data = [];

        foreach ($bills as $bill) {
            $data[] = [
                'so' => (string) $bill->so_number,
                'customer' => $bill->customer ?: '-',
                'tanggal' => optional($bill->tanggal)->format('d-m-Y') ?: '-',
                'status' => self::STATUS_META[$bill->so_status]['label'] ?? '-',
                'total' => (float) $bill->total,
                'kolis' => $bill->kolis->map(fn ($k) => [
                    'no' => $k->no_koli,
                    'items' => $k->items->pluck('item_label')->all(),
                    'lines' => $k->lines->map($line)->all(),
                ])->all(),
                'shared' => $bill->sharedLines->map($line)->all(),
            ];
        }

        $meta = [
            'periode' => $this->periodLabel($request->input('start_date'), $request->input('end_date')),
            'status' => self::STATUS_META[$request->input('status')]['label'] ?? 'Semua',
            'search' => $keyword !== '' ? '"'.$keyword.'"' : '-',
            'printed_at' => now()->format('d-m-Y H:i'),
        ];

        return Excel::download(
            new PackingBillHistoryExport($data, $meta),
            'laporan_bill_packing_'.now()->format('Y-m-d_His').'.xlsx'
        );
    }

    private function periodLabel(?string $start, ?string $end): string
    {
        $fmt = function (?string $date) {
            try {
                return $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : null;
            } catch (\Throwable $e) {
                return null;
            }
        };

        $start = $fmt($start);
        $end = $fmt($end);

        return match (true) {
            $start && $end => $start.' s/d '.$end,
            (bool) $start => 'mulai '.$start,
            (bool) $end => 'sampai '.$end,
            default => 'Semua tanggal',
        };
    }

    /**
     * Detail satu bill untuk panel di sisi kanan (dipanggil lewat AJAX).
     */
    public function detail(PackingBill $packingBill)
    {
        $bill = PackingBill::with([
            'kolis' => fn ($q) => $q->orderBy('no_koli'),
            'kolis.items',
            'kolis.lines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'sharedLines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ])->findOrFail($packingBill->id);

        $lines = fn ($collection) => $collection->map(fn ($l) => [
            'nama_material' => $l->nama_material,
            'satuan' => $l->satuan,
            'qty' => (float) $l->qty,
            'harga' => (float) $l->harga,
            'subtotal' => (float) $l->subtotal,
        ])->values();

        $kolis = $bill->kolis->map(fn ($k) => [
            'no_koli' => $k->no_koli,
            // Isi koli hanya tercatat kalau SO dipacking lebih dari 1 koli.
            'items' => $k->items->pluck('item_label')->values(),
            'lines' => $lines($k->lines),
            'subtotal' => (float) $k->lines->sum('subtotal'),
        ])->values();

        $status = $this->statusOf($bill->so_number);

        return response()->json([
            'id' => $bill->id,
            'so_number' => $bill->so_number,
            'customer' => $bill->customer ?: '-',
            'tanggal' => optional($bill->tanggal)->format('d-m-Y') ?: '-',
            'dibuat_oleh' => $bill->dibuat_oleh ?: '-',
            'diubah_oleh' => $bill->diubah_oleh ?: null,
            'diubah_pada' => $bill->diubah_oleh ? optional($bill->updated_at)->format('d-m-Y H:i') : null,
            'status' => ['key' => $status] + self::STATUS_META[$status],
            'items' => $this->soItemCodes($bill->so_number),
            'multi' => $bill->kolis->count() > 1,
            'kolis' => $kolis,
            'shared' => [
                'lines' => $lines($bill->sharedLines),
                'subtotal' => (float) $bill->sharedLines->sum('subtotal'),
            ],
            'total' => (float) $bill->total,
            'urls' => [
                'edit' => route('packing-bills.form', ['so_number' => $bill->so_number, 'from' => 'history']),
                'print' => route('packing-bills.print', $bill->id),
            ],
        ]);
    }

    private function statusOf(string $so): string
    {
        if (StagingOut::where('so_number', $so)->exists()) {
            return 'staging';
        }

        if (StagingOutHistory::where('so_number', $so)->whereNotNull('delivery_date')->exists()) {
            return 'delivered';
        }

        return 'other';
    }

    /**
     * Kode barang di SO ini (yang masih di Staging Out + yang sudah terkirim).
     */
    private function soItemCodes(string $so): array
    {
        $codes = [];

        foreach (StagingOut::with('item')->where('so_number', $so)->get() as $row) {
            $codes[] = optional($row->item)->item_code_internal;
        }

        $shipped = StagingOutHistory::with('item')->where('so_number', $so)
            ->whereNotNull('delivery_date')->get();

        foreach ($shipped as $history) {
            $codes[] = optional($history->item)->item_code_internal;
        }

        // Array biasa (bukan koleksi Eloquent) supaya tidak diperlakukan sebagai model.
        return collect($codes)->filter()->unique()->values()->all();
    }

    private function rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}