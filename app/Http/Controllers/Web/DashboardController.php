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

    /**
     * Nilai filter periode yang valid untuk tiap kartu ringkasan & grafik
     * aktivitas. Setiap kartu (Staging In, Staging Out, Stok Tersedia,
     * Total Lokasi) serta grafik Ringkasan Aktivitas punya query string
     * filter periodenya masing-masing dan saling independen:
     *   ?staging_in_period=...
     *   ?staging_out_period=...
     *   ?stock_period=...
     *   ?location_period=...
     *   ?chart_period=...
     */
    private const PERIODS = ['today', 'week', 'month'];

    private const PERIOD_LABELS = [
        'today' => 'Hari Ini',
        'week' => 'Minggu Ini',
        'month' => 'Bulan Ini',
    ];

    private const PERIOD_CAPTIONS = [
        'today' => 'hari ini',
        'week' => 'minggu ini',
        'month' => 'bulan ini',
    ];

    public function index(Request $request)
    {
        $today = Carbon::today();

        // ============================================================
        // 0. FILTER TANGGAL GLOBAL (di bagian atas dashboard)
        //    Filter satuan (satu tanggal) atau rentang tanggal, dipilih
        //    lewat form + tombol "Terapkan". Kalau filter ini aktif
        //    (query string filter_type + filter_date, atau filter_type
        //    + filter_start_date/filter_end_date valid), rentang tanggal
        //    ini dipakai untuk SEMUA kartu ringkasan & grafik aktivitas,
        //    menggantikan filter periode per kartu (today/week/month) di
        //    bawah supaya seluruh dashboard konsisten menampilkan data
        //    pada tanggal/rentang yang sama.
        // ============================================================

        $globalDateFilter = $this->resolveGlobalDateFilter($request);
        $hasGlobalFilter = $globalDateFilter !== null;

        $filterType = $request->get('filter_type', 'single');
        $filterDate = $request->get('filter_date', '');
        $filterStartDate = $request->get('filter_start_date', '');
        $filterEndDate = $request->get('filter_end_date', '');
        $filterCaption = null;

        // ============================================================
        // 0b. FILTER PERIODE PER KARTU (Hari Ini / Minggu Ini / Bulan Ini)
        //    Masing-masing kartu ringkasan (dan grafik aktivitas) resolve
        //    filternya sendiri lewat resolvePeriod(), dari query string
        //    miliknya sendiri. Klik filter di satu kartu tidak akan
        //    mengubah kartu lain karena key query string-nya berbeda dan
        //    request()->fullUrlWithQuery() di view hanya menimpa key itu.
        //    Ini hanya dipakai kalau filter tanggal global TIDAK aktif.
        // ============================================================

        if ($hasGlobalFilter) {
            [$globalStart, $globalEnd, $globalCaption] = $globalDateFilter;
            $filterCaption = $globalCaption;

            $stagingInPeriod = $stagingOutPeriod = $stockPeriod = $locationPeriod = 'custom';
            $stagingInPeriodCaption = $stagingOutPeriodCaption = $stockPeriodCaption = $locationPeriodCaption = $globalCaption;

            $stagingInPeriodStart = $stagingOutPeriodStart = $stockPeriodStart = $locationPeriodStart = $globalStart;
            $stagingInPeriodEnd = $stagingOutPeriodEnd = $stockPeriodEnd = $locationPeriodEnd = $globalEnd;

            $chartPeriodStart = $globalStart;
            $chartPeriodEnd = $globalEnd;
            // Kalau rentangnya cuma 1 hari, grafik tetap pakai granularitas
            // per jam (sama seperti perilaku "Hari Ini"); kalau lebih dari
            // 1 hari, dipakai granularitas per hari (lihat buildActivitySeries()).
            $chartPeriod = $globalStart->isSameDay($globalEnd) ? 'today' : 'custom';
            $chartPeriodLabel = $globalCaption;
        } else {
            [$stagingInPeriod, $stagingInPeriodStart, $stagingInPeriodEnd, $stagingInPeriodCaption] =
                $this->resolvePeriod($request, 'staging_in_period', $today);

            [$stagingOutPeriod, $stagingOutPeriodStart, $stagingOutPeriodEnd, $stagingOutPeriodCaption] =
                $this->resolvePeriod($request, 'staging_out_period', $today);

            [$stockPeriod, $stockPeriodStart, $stockPeriodEnd, $stockPeriodCaption] =
                $this->resolvePeriod($request, 'stock_period', $today);

            [$locationPeriod, $locationPeriodStart, $locationPeriodEnd, $locationPeriodCaption] =
                $this->resolvePeriod($request, 'location_period', $today);

            [$chartPeriod, $chartPeriodStart, $chartPeriodEnd] =
                $this->resolvePeriod($request, 'chart_period', $today);

            $chartPeriodLabel = self::PERIOD_LABELS[$chartPeriod];
        }

        // ============================================================
        // 1. KARTU RINGKASAN (Staging In / Staging Out / Stok / Lokasi)
        //    Angka utama tetap total keseluruhan (snapshot saat ini —
        //    tabel staging & stok memang bersifat transient/berjalan,
        //    bukan angka historis). Yang berubah mengikuti filter
        //    periode masing-masing kartu adalah komponen pertumbuhan
        //    di bawahnya.
        // ============================================================

        $stagingInQty = StagingIn::sum('qty');
        $stagingInAddedInPeriod = StagingIn::whereBetween('created_at', [$stagingInPeriodStart, $stagingInPeriodEnd])->sum('qty');
        $stagingInGrowth = $this->growthPercent($stagingInQty, $stagingInAddedInPeriod);

        $stagingOutQty = StagingOut::sum('qty');
        $stagingOutAddedInPeriod = StagingOut::whereBetween('created_at', [$stagingOutPeriodStart, $stagingOutPeriodEnd])->sum('qty');
        $stagingOutGrowth = $this->growthPercent($stagingOutQty, $stagingOutAddedInPeriod);

        $stockTotal = LocationStock::sum('quantity');
        $stockAddedInPeriod = StockMutation::whereBetween('created_at', [$stockPeriodStart, $stockPeriodEnd])->sum('qty_in');
        $stockGrowth = $this->growthPercent($stockTotal, $stockAddedInPeriod);

        $totalLocations = Location::count();
        $activeLocations = Location::where('status', true)->count();
        $locationAddedInPeriod = Location::whereBetween('created_at', [$locationPeriodStart, $locationPeriodEnd])->count();
        $locationGrowth = $this->growthPercent($totalLocations, $locationAddedInPeriod);

        // ============================================================
        // 2. GRAFIK AKTIVITAS — granularitas menyesuaikan periode:
        //    - Hari Ini  -> per jam (00:00 s.d. 23:00)
        //    - Minggu Ini -> per hari, Senin s.d. Minggu berjalan
        //    - Bulan Ini  -> per hari, tanggal 1 s.d. akhir bulan
        // ============================================================

        [$chartLabels, $stagingInSeries, $stagingOutSeries, $mutationSeries] =
            $this->buildActivitySeries($chartPeriod, $chartPeriodStart, $chartPeriodEnd);

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
        //    picking_date / delivery_receipt_date (tgl resi pengiriman; terisi = sudah dikirim), sama seperti logika yang
        //    sudah dipakai di StagingOutController.
        // ============================================================

        $stagingOutStatus = [
            'menunggu_picking' => StagingOut::whereNull('picking_date')->count(),
            'siap_kirim' => StagingOut::whereNotNull('picking_date')->whereNull('delivery_receipt_date')->count(),
            'total_so' => StagingOut::count(),
            'selesai' => StagingOut::whereNotNull('delivery_receipt_date')->count(),
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
        // 9. NOTIFIKASI LONCENG (4 KATEGORI SESUAI DESAIN MOCKUP)
        //    Setiap kategori hanya muncul kalau datanya benar-benar ada
        //    (count > 0 / ada baris terkait), supaya lonceng tidak
        //    menampilkan kartu kosong. "time" dihitung dari data asli
        //    yang paling relevan untuk kategori tersebut, bukan dari
        //    tabel notifikasi terpisah (skema belum punya tabel itu).
        // ============================================================

        $notifications = collect();

        // 9a. Barang staging in > 7 hari (reuse data follow-up di atas)
        if ($followUpCount > 0) {
            $oldestOverdue = $followUpStagingIn->first();

            $notifications->push([
                'url' => route('stagings-in.index', ['filter' => 'overdue']),
                'icon' => 'bx bx-error',
                'icon_bg' => 'bg-danger-subtle',
                'icon_color' => 'text-danger',
                'dot' => 'bg-danger',
                'highlight' => true,
                'title' => 'Barang Staging In Lebih dari 7 Hari',
                'message' => $followUpCount.' barang telah berada di staging in lebih dari 7 hari.',
                'time' => ($oldestOverdue && $oldestOverdue->arrival_date)
                    ? Carbon::parse($oldestOverdue->arrival_date)->addDays(self::FOLLOW_UP_DAYS)->diffForHumans()
                    : null,
            ]);
        }

        // 9b. Stok hampir habis
        $lowStockCount = LocationStock::where('quantity', '>', 0)
            ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
            ->count();

        if ($lowStockCount > 0) {
            $lastLowStock = LocationStock::where('quantity', '>', 0)
                ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
                ->latest('updated_at')
                ->first();

            $notifications->push([
                'url' => route('location-stock.index', ['filter' => 'low_stock']),
                'icon' => 'bx bx-cube',
                'icon_bg' => 'bg-warning-subtle',
                'icon_color' => 'text-warning',
                'dot' => 'bg-danger',
                'highlight' => false,
                'title' => 'Stok Hampir Habis',
                'message' => $lowStockCount.' item memiliki stok kurang dari minimum.',
                'time' => optional(optional($lastLowStock)->updated_at)->diffForHumans(),
            ]);
        }

        // 9c. Pengiriman dijadwalkan hari ini (instruksi kirim hari ini,
        //     belum ada tanggal resi pengiriman)
        $deliveriesToday = StagingOut::whereDate('delivery_instruction_date', $today)
            ->whereNull('delivery_receipt_date')
            ->latest('updated_at')
            ->get();

        if ($deliveriesToday->count() > 0) {
            $notifications->push([
                'url' => route('stagings-out.index', ['filter' => 'today']),
                'icon' => 'bx bxs-truck',
                'icon_bg' => 'bg-info-subtle',
                'icon_color' => 'text-info',
                'dot' => 'bg-primary',
                'highlight' => false,
                'title' => 'Pengiriman Hari Ini',
                'message' => $deliveriesToday->count().' pengiriman dijadwalkan hari ini.',
                'time' => optional(optional($deliveriesToday->first())->updated_at)->diffForHumans(),
            ]);
        }

        // 9d. Staging out selesai (terakhir dikirim)
        $lastCompletedStagingOut = StagingOut::whereNotNull('delivery_receipt_date')
            ->latest('delivery_receipt_date')
            ->first();

        if ($lastCompletedStagingOut) {
            $notifications->push([
                'url' => route('stagings-out.index', ['status' => 'selesai']),
                'icon' => 'bx bx-check-circle',
                'icon_bg' => 'bg-success-subtle',
                'icon_color' => 'text-success',
                'dot' => 'bg-primary',
                'highlight' => false,
                'title' => 'Staging Out Selesai',
                'message' => ($lastCompletedStagingOut->so_number ?: 'Staging out').' berhasil dikirim.',
                'time' => optional($lastCompletedStagingOut->delivery_receipt_date)->diffForHumans(),
            ]);
        }

        $notifCount = $notifications->count();

        // ============================================================
        // 10. PERINGATAN & INFORMASI
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
            'staging_out_siap_kirim' => StagingOut::whereNotNull('picking_date')
                ->whereNull('delivery_receipt_date')
                ->count(),
            'stok_rendah' => LocationStock::where('quantity', '>', 0)
                ->where('quantity', '<', self::LOW_STOCK_THRESHOLD)
                ->count(),
            'item_belum_picking' => StagingOut::whereNull('picking_date')->count(),
        ];

        return view('dashboard', compact(
            'hasGlobalFilter', 'filterType', 'filterDate', 'filterStartDate', 'filterEndDate', 'filterCaption',
            'stagingInPeriod', 'stagingInPeriodCaption',
            'stagingOutPeriod', 'stagingOutPeriodCaption',
            'stockPeriod', 'stockPeriodCaption',
            'locationPeriod', 'locationPeriodCaption',
            'chartPeriod', 'chartPeriodLabel',
            'stagingInQty', 'stagingInGrowth', 'stagingInAddedInPeriod',
            'stagingOutQty', 'stagingOutGrowth', 'stagingOutAddedInPeriod',
            'stockTotal', 'stockGrowth', 'stockAddedInPeriod',
            'totalLocations', 'activeLocations', 'locationGrowth', 'locationAddedInPeriod',
            'chartLabels', 'stagingInSeries', 'stagingOutSeries', 'mutationSeries',
            'stagingInByIncoterms', 'incotermsTotal',
            'stagingInStatus', 'stagingOutStatus',
            'topItems', 'recentStagingIn', 'recentStagingOut',
            'followUpStagingIn', 'followUpCount',
            'notifications', 'notifCount',
            'alerts'
        ));
    }

    private function stagingInBase()
    {
        return StagingIn::query();
    }

    /**
     * Resolve filter tanggal global di bagian atas dashboard (satuan atau
     * rentang). Mengembalikan [start, end, caption] kalau filter valid &
     * lengkap diisi, atau null kalau filter tidak aktif/tidak valid
     * (sehingga dashboard fallback ke filter periode per kartu seperti
     * biasa).
     */
    private function resolveGlobalDateFilter(Request $request): ?array
    {
        $type = $request->get('filter_type');

        if ($type === 'single' && $request->filled('filter_date')) {
            try {
                $date = Carbon::parse($request->get('filter_date'))->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }

            return [
                $date->copy(),
                $date->copy()->endOfDay(),
                $date->translatedFormat('d M Y'),
            ];
        }

        if ($type === 'range' && $request->filled('filter_start_date') && $request->filled('filter_end_date')) {
            try {
                $start = Carbon::parse($request->get('filter_start_date'))->startOfDay();
                $end = Carbon::parse($request->get('filter_end_date'))->endOfDay();
            } catch (\Throwable $e) {
                return null;
            }

            // Kalau user kebalik isi dari/sampai tanggal, tukar otomatis
            // supaya query tetap jalan dan hasilnya tetap masuk akal.
            if ($start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            $caption = $start->isSameDay($end)
                ? $start->translatedFormat('d M Y')
                : $start->translatedFormat('d M Y').' - '.$end->translatedFormat('d M Y');

            return [$start, $end, $caption];
        }

        return null;
    }

    /**
     * Resolve filter periode untuk satu kartu/section dari query string
     * miliknya sendiri ($queryKey), independen dari kartu/section lain.
     * Mengembalikan [period, periodStart, periodEnd, periodCaption].
     */
    private function resolvePeriod(Request $request, string $queryKey, Carbon $today): array
    {
        $period = $request->get($queryKey, 'week');

        if (! in_array($period, self::PERIODS, true)) {
            $period = 'week';
        }

        [$start, $end] = $this->periodRange($period, $today);

        return [$period, $start, $end, self::PERIOD_CAPTIONS[$period]];
    }

    /**
     * Rentang tanggal (awal, akhir) untuk periode filter yang dipilih,
     * relatif terhadap hari ini.
     */
    private function periodRange(string $period, Carbon $today): array
    {
        switch ($period) {
            case 'today':
                return [$today->copy(), $today->copy()->endOfDay()];

            case 'month':
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];

            case 'week':
            default:
                return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
        }
    }

    /**
     * Bangun label sumbu-x beserta 3 seri data (staging in, staging out,
     * mutasi ke stok) untuk grafik Ringkasan Aktivitas, dengan
     * granularitas yang menyesuaikan periode filter.
     *
     * Catatan: query per-bucket (bukan satu query GROUP BY) dipertahankan
     * senada dengan pola yang sudah dipakai sebelumnya di controller ini;
     * untuk volume data yang besar, ini bisa dioptimalkan jadi satu query
     * agregat per tabel.
     */
    private function buildActivitySeries(string $period, Carbon $start, Carbon $end): array
    {
        if ($period === 'today') {
            $hours = collect(range(0, 23));

            $labels = $hours->map(fn ($h) => sprintf('%02d:00', $h))->values();

            // whereBetween batas jam dipakai (bukan whereRaw('HOUR(...)'))
            // supaya query ini portable lintas driver database (MySQL,
            // PostgreSQL, SQLite, dll) — HOUR() adalah fungsi khusus MySQL
            // dan tidak dikenali PostgreSQL.
            $stagingIn = $hours->map(
                fn ($h) => StagingIn::whereBetween('created_at', $this->hourRange($start, $h))->count()
            )->values();

            $stagingOut = $hours->map(
                fn ($h) => StagingOut::whereBetween('created_at', $this->hourRange($start, $h))->count()
            )->values();

            $mutation = $hours->map(
                fn ($h) => StockMutation::whereBetween('created_at', $this->hourRange($start, $h))
                    ->where('qty_in', '>', 0)
                    ->count()
            )->values();

            return [$labels, $stagingIn, $stagingOut, $mutation];
        }

        $days = collect(\Carbon\CarbonPeriod::create($start, $end));

        $labels = $days->map(fn ($d) => $d->translatedFormat('d M'))->values();

        $stagingIn = $days->map(
            fn ($d) => StagingIn::whereDate('created_at', $d)->count()
        )->values();

        $stagingOut = $days->map(
            fn ($d) => StagingOut::whereDate('created_at', $d)->count()
        )->values();

        $mutation = $days->map(
            fn ($d) => StockMutation::whereDate('created_at', $d)->where('qty_in', '>', 0)->count()
        )->values();

        return [$labels, $stagingIn, $stagingOut, $mutation];
    }

    /**
     * Batas awal & akhir untuk jam ke-$hour pada tanggal $day, dipakai
     * lewat whereBetween (bukan fungsi HOUR() ala MySQL) supaya query
     * tetap portable ke PostgreSQL, SQLite, dsb.
     */
    private function hourRange(Carbon $day, int $hour): array
    {
        $start = $day->copy()->setTime($hour, 0, 0);
        $end = $day->copy()->setTime($hour, 59, 59);

        return [$start, $end];
    }

    /**
     * Persentase pertumbuhan kasar: proporsi penambahan dalam periode
     * terpilih (hari ini/minggu ini/bulan ini) terhadap total di luar
     * periode itu. Tabel staging bersifat transient (baris dihapus
     * setelah selesai diproses) sehingga ini adalah pendekatan, bukan
     * snapshot historis yang presisi — kalau butuh angka historis yang
     * akurat, sebaiknya ditambahkan tabel snapshot harian terpisah.
     */
    private function growthPercent($total, $addedInPeriod): float
    {
        $baseline = $total - $addedInPeriod;

        if ($baseline <= 0) {
            return $addedInPeriod > 0 ? 100.0 : 0.0;
        }

        return round(($addedInPeriod / $baseline) * 100, 1);
    }
}