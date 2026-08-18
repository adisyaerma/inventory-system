<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\StagingIn;
use App\Models\StagingOut;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Ambang batas "stok rendah" untuk kartu peringatan. Skema saat ini
     * belum punya kolom reorder-point per item/lokasi, jadi ini dipakai
     * sebagai nilai default sementara. Idealnya nanti diganti dengan
     * kolom reorder_point di tabel items atau location_stock.
     */
    private const LOW_STOCK_THRESHOLD = 10;

    /**
     * Ambang umur (dalam hari) sejak staging in dibuat, di atas mana
     * barang dianggap perlu di-follow up secara aktif.
     */
    private const FOLLOW_UP_DAYS = 7;

    public function index(Request $request)
    {
        $today = Carbon::today();

        // ============================================================
        // 1. KARTU RINGKASAN (Staging In / Staging Out / Stok / Lokasi)
        // ============================================================

        $stagingInQty = StagingIn::sum('qty');
        $stagingInAddedToday = StagingIn::whereDate('created_at', $today)->sum('qty');
        $stagingInGrowth = $this->growthPercent($stagingInQty, $stagingInAddedToday);

        $stagingOutQty = StagingOut::sum('qty');
        $stagingOutAddedToday = StagingOut::whereDate('created_at', $today)->sum('qty');
        $stagingOutGrowth = $this->growthPercent($stagingOutQty, $stagingOutAddedToday);

        $stockTotal = LocationStock::sum('quantity');
        $stockAddedToday = StockMutation::whereDate('created_at', $today)->sum('qty_in');
        $stockGrowth = $this->growthPercent($stockTotal, $stockAddedToday);

        $totalLocations = Location::count();
        $activeLocations = Location::where('status', true)->count();

        // ============================================================
        // 2. GRAFIK AKTIVITAS 7 HARI TERAKHIR
        // ============================================================

        $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $chartLabels = $days->map(fn ($d) => $d->translatedFormat('d M'))->values();

        $stagingInSeries = $days->map(
            fn ($d) => StagingIn::whereDate('created_at', $d)->count()
        )->values();

        $stagingOutSeries = $days->map(
            fn ($d) => StagingOut::whereDate('created_at', $d)->count()
        )->values();

        $mutationSeries = $days->map(
            fn ($d) => StockMutation::whereDate('created_at', $d)->where('qty_in', '>', 0)->count()
        )->values();

        // ============================================================
        // 3. DISTRIBUSI STAGING IN PER INCOTERMS (DONUT)
        // ============================================================

        $stagingInByIncoterms = StagingIn::query()
            ->selectRaw("COALESCE(incoterms, 'Tidak Ditentukan') as incoterms, SUM(qty) as total")
            ->groupBy('incoterms')
            ->orderByDesc('total')
            ->get();

        $incotermsTotal = (float) $stagingInByIncoterms->sum('total');

        // ============================================================
        // 4. STATUS STAGING IN
        //    Enum status di tabel staging_ins tidak punya kategori
        //    "siap masuk stok / sebagian masuk / selesai" seperti mockup
        //    desain, jadi dikelompokkan ulang berdasarkan nilai enum
        //    yang sesungguhnya ada di database.
        // ============================================================

        $stagingInStatus = [
            'siap' => (clone $this->stagingInBase())->whereNull('status')->count(),
            'menunggu' => (clone $this->stagingInBase())->where('status', 'like', 'menunggu%')->count(),
            'bermasalah' => (clone $this->stagingInBase())->whereIn('status', ['rusak', 'tidak lengkap', 'salah ukuran'])->count(),
            'batal' => (clone $this->stagingInBase())->where('status', 'batal')->count(),
        ];

        // ============================================================
        // 5. STATUS STAGING OUT
        //    Tidak ada kolom status eksplisit — diturunkan dari
        //    picking_date / delivery_date, sama seperti logika yang
        //    sudah dipakai di StagingOutController.
        // ============================================================

        $stagingOutStatus = [
            'menunggu_picking' => StagingOut::whereNull('picking_date')->count(),
            'siap_kirim' => StagingOut::whereNotNull('picking_date')->whereNull('delivery_date')->count(),
            'terlambat' => StagingOut::whereDate('delivery_instruction_date', '<', $today)->whereNull('delivery_date')->count(),
            'selesai' => StagingOut::whereNotNull('delivery_date')->count(),
        ];

        // ============================================================
        // 6. TOP 5 ITEM BERDASARKAN STOK
        // ============================================================

        $topItems = LocationStock::query()
            ->selectRaw('item_id, SUM(quantity) as total')
            ->groupBy('item_id')
            ->orderByDesc('total')
            ->with('item')
            ->take(5)
            ->get();

        // ============================================================
        // 7. STAGING IN / OUT TERBARU
        // ============================================================

        $recentStagingIn = StagingIn::with('item.vendor')->latest()->take(5)->get();
        $recentStagingOut = StagingOut::with('item')->latest()->take(5)->get();

        // ============================================================
        // 8. NOTIFIKASI LONCENG: STAGING IN > 7 HARI SEJAK KEDATANGAN
        //    Dipakai untuk dropdown lonceng notifikasi di header, bukan
        //    lagi kartu daftar terpisah di body dashboard.
        // ============================================================

        $followUpStagingIn = StagingIn::with('item.vendor')
            ->whereNotNull('arrival_date')
            ->where('arrival_date', '<=', now()->subDays(self::FOLLOW_UP_DAYS))
            ->oldest('arrival_date')
            ->get()
            ->map(function ($row) {
                $row->days_waiting = (int) Carbon::parse($row->arrival_date)->diffInDays(now());

                return $row;
            });

        $followUpCount = $followUpStagingIn->count();

        // ============================================================
        // 9. PERINGATAN & INFORMASI
        //    "Lokasi Penuh" di mockup butuh kolom kapasitas yang belum
        //    ada di tabel locations, jadi diganti dengan kartu yang bisa
        //    dihitung dari data yang sungguh ada: item yang belum
        //    picking.
        // ============================================================

        $alerts = [
            'staging_in_menunggu' => [
                'count' => (clone $this->stagingInBase())
                    ->where(function ($q) {
                        $q->where('status', 'like', 'menunggu%')->orWhereNull('status');
                    })
                    ->count(),
                'detail' => 'Barang menunggu diproses lebih dari '.self::FOLLOW_UP_DAYS.' hari',
                'overdue_count' => $followUpCount,
            ],
            'staging_out_terlambat' => StagingOut::whereDate('delivery_instruction_date', '<', $today)
                ->whereNull('delivery_date')
                ->count(),
            'stok_rendah' => LocationStock::where('quantity', '>', 0)
                ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
                ->count(),
            'item_belum_picking' => StagingOut::whereNull('picking_date')->count(),
        ];

        return view('dashboard', compact(
            'stagingInQty', 'stagingInGrowth',
            'stagingOutQty', 'stagingOutGrowth',
            'stockTotal', 'stockGrowth',
            'totalLocations', 'activeLocations',
            'chartLabels', 'stagingInSeries', 'stagingOutSeries', 'mutationSeries',
            'stagingInByIncoterms', 'incotermsTotal',
            'stagingInStatus', 'stagingOutStatus',
            'topItems', 'recentStagingIn', 'recentStagingOut',
            'followUpStagingIn', 'followUpCount',
            'alerts'
        ));
    }

    private function stagingInBase()
    {
        return StagingIn::query();
    }

    /**
     * Persentase pertumbuhan kasar: proporsi penambahan hari ini
     * terhadap total sebelum hari ini. Tabel staging bersifat transient
     * (baris dihapus setelah selesai diproses) sehingga ini adalah
     * pendekatan, bukan snapshot historis yang presisi — kalau butuh
     * angka "dari kemarin" yang akurat, sebaiknya ditambahkan tabel
     * snapshot harian terpisah.
     */
    private function growthPercent($total, $addedToday): float
    {
        $baseline = $total - $addedToday;

        if ($baseline <= 0) {
            return $addedToday > 0 ? 100.0 : 0.0;
        }

        return round(($addedToday / $baseline) * 100, 1);
    }
}