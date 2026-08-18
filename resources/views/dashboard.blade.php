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
                title="Staging > 7 hari">
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
                    <a href="{{ route('stagings-in.index') }}" class="dropdown-item px-3 py-2 border-bottom white-space-normal">
                        <div class="fw-semibold small">
                            {{ optional($row->item)->item_code_internal ?? 'Kode tidak diketahui' }}
                        </div>
                        <small class="text-muted">
                           Tgl Datang {{ optional($row->arrival_date)->format('d M Y') }}
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
                    <a href="{{ route('stagings-in.index') }}" class="dropdown-item text-center small text-primary py-2">
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
                    <div id="chartActivity"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Staging In per Incoterms</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">

                    <div id="chartIncotermsDonut" style="max-width: 260px;"></div>

                    <div class="w-100 mt-3">
                        @foreach ($stagingInByIncoterms as $row)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>
                                    <span class="status-dot me-2" style="background: {{ ['#696cff','#71dd37','#ffab00','#8592a3','#03c3ec','#ff3e1d','#a3a3a3'][$loop->index % 7] }};"></span>
                                    {{ $row->incoterms }}
                                </span>
                                <span class="fw-semibold">
                                    {{ number_format($row->total, 0, ',', '.') }}
                                    <small class="text-muted">
                                        ({{ $incotermsTotal > 0 ? round($row->total / $incotermsTotal * 100, 1) : 0 }}%)
                                    </small>
                                </span>
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
                        <a href="{{ route('stagings-in.index') }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-danger-subtle">
                        <i class="bx bx-time text-danger fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['staging_out_terlambat'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Staging Out Terlambat</small>
                        <small class="text-muted d-block mb-2">Melewati tanggal kirim yang sudah ditentukan</small>
                        <a href="{{ route('stagings-out.index') }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-info-subtle">
                        <i class="bx bx-cube text-info fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['stok_rendah'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Stok Rendah</small>
                        <small class="text-muted d-block mb-2">Stok di bawah 10 unit</small>
                        <a href="{{ route('location-stock.index') }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="alert-card bg-secondary-subtle">
                        <i class="bx bx-list-check text-secondary fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['item_belum_picking'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Belum Picking</small>
                        <small class="text-muted d-block mb-2">Staging Out yang belum mulai proses picking</small>
                        <a href="{{ route('stagings-out.index') }}" class="small text-decoration-none">Lihat daftar</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            $(function() {

                // ================= AREA CHART: AKTIVITAS 7 HARI =================
                new ApexCharts(document.querySelector('#chartActivity'), {
                    chart: {
                        type: 'area',
                        height: 320,
                        fontFamily: 'inherit',
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        dropShadow: {
                            enabled: true,
                            top: 6,
                            left: 0,
                            blur: 6,
                            color: '#696cff',
                            opacity: 0.08
                        }
                    },
                    series: [{
                            name: 'Staging In',
                            data: @json($stagingInSeries)
                        },
                        {
                            name: 'Staging Out',
                            data: @json($stagingOutSeries)
                        },
                        {
                            name: 'Mutasi ke Stok',
                            data: @json($mutationSeries)
                        }
                    ],
                    colors: ['#696cff', '#03c3ec', '#ffab00'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.02,
                            stops: [0, 90, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                        lineCap: 'round'
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 6
                        },
                        strokeWidth: 3,
                        strokeColors: '#fff'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: @json($chartLabels),
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        labels: {
                            style: {
                                colors: '#8592a3',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#8592a3',
                                fontSize: '12px'
                            },
                            formatter: function(val) {
                                return Math.round(val);
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        offsetY: 4,
                        markers: {
                            radius: 12
                        },
                        itemMargin: {
                            horizontal: 12
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f3',
                        strokeDashArray: 4,
                        padding: {
                            top: -10,
                            left: 4,
                            right: 4
                        },
                        xaxis: {
                            lines: {
                                show: false
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function(val) {
                                return val + ' aktivitas';
                            }
                        }
                    }
                }).render();

                // ================= DONUT CHART: STAGING IN PER INCOTERMS =================
                let donutLabels = @json($stagingInByIncoterms->pluck('incoterms')->values());
                let donutSeries = @json($stagingInByIncoterms->pluck('total')->values());

                new ApexCharts(document.querySelector('#chartIncotermsDonut'), {
                    chart: {
                        type: 'donut',
                        height: 260
                    },
                    series: donutSeries,
                    labels: donutLabels,
                    colors: ['#696cff', '#71dd37', '#ffab00', '#8592a3', '#03c3ec', '#ff3e1d', '#a3a3a3'],
                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals
                                                .reduce((a, b) => a + b, 0)
                                                .toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }).render();

            });
        </script>
    @endpush

@endsection