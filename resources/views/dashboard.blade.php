@extends('master')
@section('title', 'Dashboard')
@section('content')

    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem 0;
            border-bottom: 1px solid #f1f1f3;
        }

        .status-row:last-of-type {
            border-bottom: none;
        }

        .status-icon-sm {
            width: 32px;
            height: 32px;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-card {
            border-radius: .75rem;
            padding: 1.1rem;
            height: 100%;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <small class="text-muted">Ringkasan data staging dan stok</small>
        </div>

        <div class="dropdown">
            <button type="button"
                class="btn btn-icon btn-outline-secondary rounded-circle position-relative"
                style="width:44px;height:44px;"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                title="Staging In > 7 hari">
                <i class="bx bx-bell fs-4"></i>
                @if ($followUpCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size:.65rem;">
                        {{ $followUpCount > 99 ? '99+' : $followUpCount }}
                    </span>
                @endif
            </button>

            <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 340px; max-height: 420px; overflow-y: auto;">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <span class="fw-semibold">Staging In &gt; 7 Hari</span>
                    @if ($followUpCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $followUpCount }}</span>
                    @endif
                </div>

                @forelse ($followUpStagingIn->take(10) as $row)
                    <a href="{{ route('stagings-in.index', ['filter' => 'overdue']) }}" class="dropdown-item px-3 py-2 border-bottom white-space-normal">
                        <div class="fw-semibold small">
                            {{ optional($row->item)->item_code_internal ?? 'Kode tidak diketahui' }}
                            <span class="text-muted fw-normal">— {{ optional($row->item)->name }}</span>
                        </div>
                        <small class="text-muted">
                            PO {{ $row->po_number ?: '-' }}
                            &middot; Tgl Datang {{ optional($row->arrival_date)->format('d M Y') }}
                            &middot; {{ $row->days_waiting }} hari
                        </small>
                    </a>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bx bx-check-circle text-success fs-4 d-block mb-1"></i>
                        Tidak ada staging in yang lebih dari 7 hari.
                    </div>
                @endforelse

                @if ($followUpCount > 10)
                    <a href="{{ route('stagings-in.index', ['filter' => 'overdue']) }}" class="dropdown-item text-center small text-primary py-2">
                        Lihat {{ $followUpCount - 10 }} item lainnya
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= KARTU RINGKASAN ================= --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary me-3">
                        <i class="bx bx-package fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Staging In</small>
                        <small class="text-muted d-block" style="font-size:.7rem;">Total Item</small>
                        <h4 class="fw-bold mb-1">{{ number_format($stagingInQty, 0, ',', '.') }}</h4>
                        <small class="{{ $stagingInGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bx {{ $stagingInGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                            {{ $stagingInGrowth }}% dari kemarin
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-success-subtle text-success me-3">
                        <i class="bx bx-cart-alt fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Staging Out</small>
                        <small class="text-muted d-block" style="font-size:.7rem;">Total Item</small>
                        <h4 class="fw-bold mb-1">{{ number_format($stagingOutQty, 0, ',', '.') }}</h4>
                        <small class="{{ $stagingOutGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bx {{ $stagingOutGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                            {{ $stagingOutGrowth }}% dari kemarin
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-info-subtle text-info me-3">
                        <i class="bx bx-cube fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Stok Tersedia</small>
                        <small class="text-muted d-block" style="font-size:.7rem;">Total Item</small>
                        <h4 class="fw-bold mb-1">{{ number_format($stockTotal, 0, ',', '.') }}</h4>
                        <small class="{{ $stockGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bx {{ $stockGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                            {{ $stockGrowth }}% dari kemarin
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-warning-subtle text-warning me-3">
                        <i class="bx bx-map fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Lokasi</small>
                        <small class="text-muted d-block" style="font-size:.7rem;">Lokasi Aktif</small>
                        <h4 class="fw-bold mb-1">{{ number_format($activeLocations, 0, ',', '.') }}</h4>
                        <small class="text-muted">dari {{ $totalLocations }} lokasi</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= GRAFIK AKTIVITAS + DISTRIBUSI STOK ================= --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-7 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-0">Ringkasan Aktivitas</h5>
                        <small class="text-muted">7 hari terakhir</small>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="text-end">
                            <small class="text-muted d-block">Staging In</small>
                            <span class="fw-bold fs-5" style="color:#696cff;">{{ number_format($stagingInSeries->sum(), 0, ',', '.') }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Staging Out</small>
                            <span class="fw-bold fs-5" style="color:#03c3ec;">{{ number_format($stagingOutSeries->sum(), 0, ',', '.') }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Mutasi Stok</small>
                            <span class="fw-bold fs-5" style="color:#ffab00;">{{ number_format($mutationSeries->sum(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <canvas id="chartActivity" height="130"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Staging In per Incoterms</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">

                    <div class="position-relative" style="width: 240px; height: 240px;">
                        <canvas id="chartIncotermsDonut" width="240" height="240"></canvas>
                    </div>

                    <div class="w-100 mt-4">
                        @foreach ($stagingInByIncoterms as $row)
                            @php $pct = $incotermsTotal > 0 ? round($row->total / $incotermsTotal * 100, 1) : 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="d-flex align-items-center">
                                        <span class="status-dot me-2" style="background: {{ ['#696cff','#03c3ec','#71dd37','#ffab00','#ff3e6c','#7367f0','#20c997'][$loop->index % 7] }};"></span>
                                        {{ $row->incoterms }}
                                    </span>
                                    <span class="fw-semibold">
                                        {{ number_format($row->total, 0, ',', '.') }}
                                        <small class="text-muted">({{ $pct }}%)</small>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px; background:#f1f1f3;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $pct }}%; background: {{ ['#696cff','#03c3ec','#71dd37','#ffab00','#ff3e6c','#7367f0','#20c997'][$loop->index % 7] }}; border-radius: 4px;">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($stagingInByIncoterms->isEmpty())
                            <p class="text-muted mb-0 text-center">Belum ada data staging in.</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- ================= STATUS STAGING IN / OUT + TOP ITEM ================= --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Status Staging In</h5>
                </div>
                <div class="card-body px-4">

                    <div class="status-row">
                        <span><span class="status-dot bg-warning me-2"></span>Menunggu</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingInStatus['menunggu'] }}</strong>
                            <span class="status-icon-sm bg-warning-subtle text-warning">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-success me-2"></span>Siap Diproses</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingInStatus['siap'] }}</strong>
                            <span class="status-icon-sm bg-success-subtle text-success">
                                <i class="bx bx-check"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-danger me-2"></span>Bermasalah</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingInStatus['bermasalah'] }}</strong>
                            <span class="status-icon-sm bg-danger-subtle text-danger">
                                <i class="bx bx-error"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-secondary me-2"></span>Dibatalkan</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingInStatus['batal'] }}</strong>
                            <span class="status-icon-sm bg-secondary-subtle text-secondary">
                                <i class="bx bx-x"></i>
                            </span>
                        </span>
                    </div>

                </div>
                <div class="card-footer bg-white border-0 px-4 pb-3">
                    <a href="{{ route('stagings-in.index') }}" class="text-decoration-none">
                        Lihat semua staging in <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Status Staging Out</h5>
                </div>
                <div class="card-body px-4">

                    <div class="status-row">
                        <span><span class="status-dot bg-warning me-2"></span>Menunggu Picking</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingOutStatus['menunggu_picking'] }}</strong>
                            <span class="status-icon-sm bg-warning-subtle text-warning">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-info me-2"></span>Siap Kirim</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingOutStatus['siap_kirim'] }}</strong>
                            <span class="status-icon-sm bg-info-subtle text-info">
                                <i class="bx bx-package"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-danger me-2"></span>Terlambat</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingOutStatus['terlambat'] }}</strong>
                            <span class="status-icon-sm bg-danger-subtle text-danger">
                                <i class="bx bx-error"></i>
                            </span>
                        </span>
                    </div>

                    <div class="status-row">
                        <span><span class="status-dot bg-success me-2"></span>Selesai</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingOutStatus['selesai'] }}</strong>
                            <span class="status-icon-sm bg-success-subtle text-success">
                                <i class="bx bx-check"></i>
                            </span>
                        </span>
                    </div>

                </div>
                <div class="card-footer bg-white border-0 px-4 pb-3">
                    <a href="{{ route('stagings-out.index') }}" class="text-decoration-none">
                        Lihat semua staging out <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Top 5 Item Berdasarkan Stok</h5>
                </div>
                <div class="card-body px-4">

                    @forelse ($topItems as $index => $row)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-muted">{{ $index + 1 }}</span>
                                <div>
                                    <div class="fw-semibold">{{ optional($row->item)->item_code_internal ?? '—' }}</div>
                                    <small class="text-muted">{{ optional($row->item)->name }}</small>
                                </div>
                            </div>
                            <span class="fw-bold">{{ number_format($row->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada data stok.</p>
                    @endforelse

                </div>
                <div class="card-footer bg-white border-0 px-4 pb-3">
                    <a href="{{ route('items.index') }}" class="text-decoration-none">
                        Lihat semua item <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= TABEL TERBARU ================= --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Staging In Terbaru</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th class="ps-4">No. PO</th>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Tgl Datang</th>
                                <th>Qty</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentStagingIn as $row)
                                <tr>
                                    <td class="ps-4">{{ $row->po_number ?: '-' }}</td>
                                    <td>{{ optional($row->item)->item_code_internal ?: '-' }}</td>
                                    <td>{{ $row->supplier_origin ?: '-' }}</td>
                                    <td>{{ optional($row->arrival_date)->format('d M Y') ?: '-' }}</td>
                                    <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                                    <td class="pe-4">
                                        <span class="badge bg-label-secondary">{{ $row->status ?: 'Siap Diproses' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 px-4 py-3">
                    <a href="{{ route('stagings-in.index') }}" class="text-decoration-none">
                        Lihat semua <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Staging Out Terbaru</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th class="ps-4">No. SO</th>
                                <th>Customer</th>
                                <th>Tgl DI</th>
                                <th>Qty</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentStagingOut as $row)
                                <tr>
                                    <td class="ps-4">{{ $row->so_number ?: '-' }}</td>
                                    <td>{{ $row->customer ?: '-' }}</td>
                                    <td>{{ optional($row->delivery_instruction_date)->format('d M Y') ?: '-' }}</td>
                                    <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                                    <td class="pe-4">
                                        @if ($row->delivery_date)
                                            <span class="badge bg-label-success">Selesai</span>
                                        @elseif ($row->picking_date)
                                            <span class="badge bg-label-info">Siap Kirim</span>
                                        @else
                                            <span class="badge bg-label-warning">Menunggu Picking</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 px-4 py-3">
                    <a href="{{ route('stagings-out.index') }}" class="text-decoration-none">
                        Lihat semua <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= PERINGATAN & INFORMASI ================= --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">Peringatan &amp; Informasi</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-warning-subtle">
                        <i class="bx bx-bell text-warning fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['staging_in_menunggu']['overdue_count'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Staging In Menunggu</small>
                        <small class="text-muted d-block mb-2">{{ $alerts['staging_in_menunggu']['detail'] }}</small>
                        <a href="{{ route('stagings-in.index', ['filter' => 'overdue']) }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-danger-subtle">
                        <i class="bx bx-time text-danger fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['staging_out_terlambat'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Staging Out Terlambat</small>
                        <small class="text-muted d-block mb-2">Melewati tanggal kirim yang sudah ditentukan</small>
                        <a href="{{ route('stagings-out.index', ['status' => 'terlambat']) }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-info-subtle">
                        <i class="bx bx-cube text-info fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['stok_rendah'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Stok Rendah</small>
                        <small class="text-muted d-block mb-2">Stok di bawah 10 unit</small>
                        <a href="{{ route('location-stock.index', ['filter' => 'low_stock']) }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-secondary-subtle">
                        <i class="bx bx-list-check text-secondary fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['item_belum_picking'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Belum Picking</small>
                        <small class="text-muted d-block mb-2">Staging Out yang belum mulai proses picking</small>
                        <a href="{{ route('stagings-out.index', ['status' => 'menunggu_picking']) }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            $(function() {

                // ================= COMBO CHART: AKTIVITAS 7 HARI =================
                // ================= CHART.JS: AKTIVITAS 7 HARI =================
                (function() {
                    const ctx = document.getElementById('chartActivity').getContext('2d');

                    // Gradient vertikal untuk tiap batang, dari warna pekat ke transparan
                    function barGradient(ctx, colorTop, colorBottom) {
                        const g = ctx.createLinearGradient(0, 0, 0, 300);
                        g.addColorStop(0, colorTop);
                        g.addColorStop(1, colorBottom);
                        return g;
                    }

                    const gradIn = barGradient(ctx, '#8385ff', 'rgba(105,108,255,0.15)');
                    const gradOut = barGradient(ctx, '#3fd7f5', 'rgba(3,195,236,0.15)');

                    // Plugin custom: glow lembut di belakang garis Mutasi ke Stok
                    const glowLinePlugin = {
                        id: 'glowLine',
                        beforeDatasetsDraw(chart) {
                            const {
                                ctx
                            } = chart;
                            ctx.save();
                            ctx.shadowColor = 'rgba(255,171,0,0.55)';
                            ctx.shadowBlur = 12;
                            ctx.shadowOffsetY = 4;
                        },
                        afterDatasetsDraw(chart) {
                            chart.ctx.restore();
                        }
                    };

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                    label: 'Staging In',
                                    data: @json($stagingInSeries),
                                    backgroundColor: gradIn,
                                    borderRadius: {
                                        topLeft: 10,
                                        topRight: 10
                                    },
                                    borderSkipped: false,
                                    maxBarThickness: 22,
                                    order: 2
                                },
                                {
                                    label: 'Staging Out',
                                    data: @json($stagingOutSeries),
                                    backgroundColor: gradOut,
                                    borderRadius: {
                                        topLeft: 10,
                                        topRight: 10
                                    },
                                    borderSkipped: false,
                                    maxBarThickness: 22,
                                    order: 2
                                },
                                {
                                    label: 'Mutasi ke Stok',
                                    data: @json($mutationSeries),
                                    type: 'line',
                                    borderColor: '#ffab00',
                                    backgroundColor: '#ffab00',
                                    borderWidth: 3,
                                    tension: 0.45,
                                    fill: false,
                                    pointRadius: 5,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#ffab00',
                                    pointBorderWidth: 3,
                                    pointHoverRadius: 7,
                                    order: 1
                                }
                            ]
                        },
                        plugins: [glowLinePlugin],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            animation: {
                                duration: 900,
                                easing: 'easeOutQuart'
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'start',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        boxWidth: 8,
                                        padding: 18,
                                        font: {
                                            size: 12
                                        },
                                        color: '#697a8d'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#2b2c40',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: {
                                        weight: '600'
                                    },
                                    callbacks: {
                                        label: function(item) {
                                            return ' ' + item.dataset.label + ': ' + item.formattedValue + ' aktivitas';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: false,
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#8592a3',
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#f1f1f3',
                                        borderDash: [4, 4],
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#8592a3',
                                        font: {
                                            size: 12
                                        },
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                })();

                // ================= CHART.JS: STAGING IN PER INCOTERMS =================
                (function() {
                    const donutLabels = @json($stagingInByIncoterms->pluck('incoterms')->values());
                    const donutSeries = @json($stagingInByIncoterms->pluck('total')->values());
                    const donutColors = ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e6c', '#7367f0', '#20c997'];
                    const donutTotal = donutSeries.reduce((a, b) => a + b, 0);

                    // Plugin custom: tulis total besar di tengah lubang doughnut
                    const centerTextPlugin = {
                        id: 'centerText',
                        beforeDraw(chart) {
                            if (!chart.config.data.datasets.length || !donutTotal) return;

                            const {
                                ctx,
                                chartArea: {
                                    left,
                                    right,
                                    top,
                                    bottom
                                }
                            } = chart;
                            const x = (left + right) / 2;
                            const y = (top + bottom) / 2;

                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            ctx.font = '700 22px inherit, sans-serif';
                            ctx.fillStyle = '#2b2c40';
                            ctx.fillText(donutTotal.toLocaleString('id-ID'), x, y - 8);

                            ctx.font = '500 12px inherit, sans-serif';
                            ctx.fillStyle = '#8592a3';
                            ctx.fillText('Total', x, y + 14);

                            ctx.restore();
                        }
                    };

                    new Chart(document.getElementById('chartIncotermsDonut').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: donutLabels,
                            datasets: [{
                                data: donutSeries,
                                backgroundColor: donutColors,
                                hoverBackgroundColor: donutColors,
                                borderColor: '#fff',
                                borderWidth: 3,
                                borderRadius: 6,
                                spacing: 3,
                                hoverOffset: 10
                            }]
                        },
                        plugins: [centerTextPlugin],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            resizeDelay: 0,
                            cutout: '72%',
                            animation: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#2b2c40',
                                    padding: 12,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(item) {
                                            const pct = donutTotal > 0 ? ((item.raw / donutTotal) * 100).toFixed(1) : 0;
                                            return ' ' + item.label + ': ' + item.formattedValue + ' (' + pct + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })();

            });
        </script>
    @endpush

@endsection