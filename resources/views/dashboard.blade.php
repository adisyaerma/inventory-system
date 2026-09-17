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

        .notif-dropdown {
            border-radius: .9rem;
            overflow: hidden;
        }

        .notif-item {
            transition: background-color .15s ease;
            color: inherit;
        }

        .notif-item:hover {
            background-color: #f8f9fb;
        }

        .notif-item:last-of-type {
            border-bottom: none !important;
        }

        .notif-item-highlight {
            background-color: rgba(255, 62, 108, .06);
        }

        .notif-item-highlight:hover {
            background-color: rgba(255, 62, 108, .1);
        }

        .notif-item .status-dot {
            margin-top: .3rem;
        }

        .period-filter-btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .period-filter-btn-sm {
            width: 26px;
            height: 26px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .summary-card-body {
            position: relative;
            padding-bottom: 1rem;
        }

         {
            position: absolute;
            top: .65rem;
            right: .65rem;
        }

        .summary-mutation {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-top: 1rem;
            padding-top: .85rem;
            border-top: 1px dashed #eceef1;
        }

        .summary-mutation-icon {
            width: 26px;
            height: 26px;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .8rem;
        }

        .summary-mutation-value {
            font-weight: 700;
            font-size: .82rem;
            white-space: nowrap;
        }

        .period-filter-menu .dropdown-item.active,
        .period-filter-menu .dropdown-item:active {
            background-color: #696cff;
            color: #fff;
        }

        .date-filter-bar .form-label {
            font-size: .72rem;
            margin-bottom: .25rem;
        }

        .date-filter-bar .btn-check:checked + .btn-outline-primary {
            background-color: #696cff;
            border-color: #696cff;
            color: #fff;
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
                title="Notifikasi">
                <i class="bx bx-bell fs-4"></i>
                @if ($notifCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size:.65rem;">
                        {{ $notifCount > 99 ? '99+' : $notifCount }}
                    </span>
                @endif
            </button>

            <div class="dropdown-menu dropdown-menu-end p-0 shadow notif-dropdown" style="width: 380px; max-height: 480px; overflow-y: auto;">
                <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                    <span class="fw-bold">Notifikasi</span>
                    {{-- <a href="#" class="small text-decoration-none" onclick="event.preventDefault();">
                        Tandai semua dibaca
                    </a> --}}
                </div>

                @forelse ($notifications as $notif)
                    <a href="{{ $notif['url'] }}"
                        class="notif-item d-flex align-items-start gap-3 px-3 py-3 border-bottom text-decoration-none {{ $notif['highlight'] ? 'notif-item-highlight' : '' }}">
                        <div class="icon-box {{ $notif['icon_bg'] }} {{ $notif['icon_color'] }}" style="width:44px;height:44px;">
                            <i class="{{ $notif['icon'] }} fs-5"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold text-dark small mb-1">{{ $notif['title'] }}</div>
                            <div class="text-muted small mb-1">{{ $notif['message'] }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $notif['time'] ?? '-' }}</div>
                        </div>
                        <span class="status-dot {{ $notif['dot'] }} flex-shrink-0"></span>
                    </a>
                @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bx bx-check-circle text-success fs-4 d-block mb-1"></i>
                        Tidak ada notifikasi baru.
                    </div>
                @endforelse

                {{-- <a href="{{ route('stagings-in.index', ['filter' => 'overdue']) }}" class="dropdown-item text-center small text-primary py-3">
                    Lihat semua notifikasi <i class="bx bx-chevron-right"></i>
                </a> --}}
            </div>
        </div>
    </div>

    {{-- ================= FILTER TANGGAL (GLOBAL) =================
         Filter tanggal satuan atau rentang di bagian atas dashboard.
         Saat tombol "Terapkan" ditekan, filter ini berlaku untuk SEMUA
         kartu ringkasan & grafik aktivitas di bawah (menggantikan
         filter periode per kartu selama filter ini aktif). --}}
    <div class="card border-0 shadow-sm mb-4 date-filter-bar">
        <div class="card-body py-3">
            <form action="{{ url()->current() }}" method="GET" class="row gx-3 gy-2 align-items-end">

                <div class="col-auto">
                    <label class="form-label text-muted d-block">Jenis Filter</label>
                    <div class="btn-group" role="group" aria-label="Jenis filter tanggal">
                        <input type="radio" class="btn-check" name="filter_type" id="filterTypeSingle"
                            value="single" autocomplete="off"
                            {{ $filterType !== 'range' ? 'checked' : '' }}
                            onchange="toggleDashboardDateFilterInputs()">
                        <label class="btn btn-outline-primary btn-sm" for="filterTypeSingle">
                            <i class="bx bx-calendar"></i> Tanggal
                        </label>

                        <input type="radio" class="btn-check" name="filter_type" id="filterTypeRange"
                            value="range" autocomplete="off"
                            {{ $filterType === 'range' ? 'checked' : '' }}
                            onchange="toggleDashboardDateFilterInputs()">
                        <label class="btn btn-outline-primary btn-sm" for="filterTypeRange">
                            <i class="bx bx-calendar-week"></i> Rentang
                        </label>
                    </div>
                </div>

                <div class="col-auto" id="singleDateWrapper"
                    style="{{ $filterType === 'range' ? 'display:none;' : '' }}">
                    <label for="filter_date" class="form-label text-muted d-block">Pilih Tanggal</label>
                    <input type="date" class="form-control form-control-sm" style="min-width:160px;"
                        name="filter_date" id="filter_date" value="{{ $filterDate }}">
                </div>

                <div class="col-auto" id="rangeDateWrapper"
                    style="{{ $filterType !== 'range' ? 'display:none;' : '' }}">
                    <div class="d-flex gap-2">
                        <div>
                            <label for="filter_start_date" class="form-label text-muted d-block">Dari Tanggal</label>
                            <input type="date" class="form-control form-control-sm" style="min-width:150px;"
                                name="filter_start_date" id="filter_start_date" value="{{ $filterStartDate }}">
                        </div>
                        <div>
                            <label for="filter_end_date" class="form-label text-muted d-block">Sampai Tanggal</label>
                            <input type="date" class="form-control form-control-sm" style="min-width:150px;"
                                name="filter_end_date" id="filter_end_date" value="{{ $filterEndDate }}">
                        </div>
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bx bx-filter-alt"></i> Terapkan
                    </button>

                    @if ($hasGlobalFilter)
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-x"></i> Reset
                        </a>
                    @endif
                </div>

                @if ($hasGlobalFilter)
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="bx bx-info-circle"></i>
                            Menampilkan data untuk <strong>{{ $filterCaption }}</strong>.
                            Filter periode per kartu di bawah dinonaktifkan sementara — klik
                            "Reset" untuk kembali ke filter periode per kartu.
                        </small>
                    </div>
                @endif

            </form>
        </div>
    </div>

    <script>
        function toggleDashboardDateFilterInputs() {
            const isRange = document.getElementById('filterTypeRange').checked;
            document.getElementById('singleDateWrapper').style.display = isRange ? 'none' : '';
            document.getElementById('rangeDateWrapper').style.display = isRange ? '' : 'none';
        }
    </script>

    {{-- ================= KARTU RINGKASAN ================= --}}
    {{-- Setiap kartu punya filter periode sendiri (dropdown titik-tiga
         di pojok kanan atas kartu), lewat query string terpisah:
         staging_in_period, staging_out_period, stock_period,
         location_period. Klik filter di satu kartu tidak mempengaruhi
         kartu lain. Di bawah angka total, ada strip "mutasi" yang
         menampilkan jumlah riil yang bertambah pada periode terpilih —
         supaya filternya benar-benar kelihatan efeknya, bukan cuma
         mengubah angka persentase. --}}

    <div class="row g-4 mb-4">

    <!-- ================= STAGING IN ================= -->
    <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">

                <!-- HEADER: ISI KIRI + DROPDOWN KANAN -->
                <div class="d-flex align-items-start justify-content-between">

                    <!-- KONTEN KIRI -->
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary-subtle text-primary me-3">
                            <i class="bx bx-package fs-4"></i>
                        </div>

                        <div>
                            <small class="text-muted d-block">
                                Staging In
                            </small>

                            <small class="text-muted d-block"
                                style="font-size:.7rem;">
                                Total Item
                            </small>

                            <h4 class="fw-bold mb-1">
                                {{ number_format($stagingInQty, 0, ',', '.') }}
                            </h4>

                            <small class="{{ $stagingInGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bx {{ $stagingInGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                {{ $stagingInGrowth }}%
                            </small>
                        </div>
                    </div>

                    <!-- DROPDOWN KANAN -->
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn p-0"
                            id="cardOptStagingIn"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            {{ $hasGlobalFilter ? 'disabled' : '' }}
                            title="{{ $hasGlobalFilter ? 'Nonaktif — filter tanggal khusus sedang aktif' : 'Filter periode Staging In' }}">

                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow period-filter-menu"
                            aria-labelledby="cardOptStagingIn">

                            <li>
                                <a class="dropdown-item {{ $stagingInPeriod === 'today' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_in_period' => 'today']) }}">
                                    Hari Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stagingInPeriod === 'week' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_in_period' => 'week']) }}">
                                    Minggu Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stagingInPeriod === 'month' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_in_period' => 'month']) }}">
                                    Bulan Ini
                                </a>
                            </li>

                        </ul>
                    </div>

                </div>

                <!-- RINGKASAN BAWAH -->
                <div class="summary-mutation">
                    <div class="d-flex align-items-center gap-2">
                        <span class="summary-mutation-icon bg-primary-subtle text-primary">
                            <i class="bx bx-log-in-circle"></i>
                        </span>

                        <small class="text-muted" style="font-size:.72rem;">
                            Masuk {{ $stagingInPeriodCaption }}
                        </small>
                    </div>

                    <span class="summary-mutation-value text-primary">
                        +{{ number_format($stagingInAddedInPeriod, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>
    </div>


    <!-- ================= STAGING OUT ================= -->
    <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">

                <!-- HEADER: ISI KIRI + DROPDOWN KANAN -->
                <div class="d-flex align-items-start justify-content-between">

                    <!-- KONTEN KIRI -->
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success-subtle text-success me-3">
                            <i class="bx bx-cart-alt fs-4"></i>
                        </div>

                        <div>
                            <small class="text-muted d-block">
                                Staging Out
                            </small>

                            <small class="text-muted d-block"
                                style="font-size:.7rem;">
                                Total Item
                            </small>

                            <h4 class="fw-bold mb-1">
                                {{ number_format($stagingOutQty, 0, ',', '.') }}
                            </h4>

                            <small class="{{ $stagingOutGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bx {{ $stagingOutGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                {{ $stagingOutGrowth }}%
                            </small>
                        </div>
                    </div>

                    <!-- DROPDOWN KANAN -->
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn p-0"
                            id="cardOptStagingOut"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            {{ $hasGlobalFilter ? 'disabled' : '' }}
                            title="{{ $hasGlobalFilter ? 'Nonaktif — filter tanggal khusus sedang aktif' : 'Filter periode Staging Out' }}">

                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow period-filter-menu"
                            aria-labelledby="cardOptStagingOut">

                            <li>
                                <a class="dropdown-item {{ $stagingOutPeriod === 'today' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_out_period' => 'today']) }}">
                                    Hari Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stagingOutPeriod === 'week' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_out_period' => 'week']) }}">
                                    Minggu Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stagingOutPeriod === 'month' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['staging_out_period' => 'month']) }}">
                                    Bulan Ini
                                </a>
                            </li>

                        </ul>
                    </div>

                </div>

                <!-- RINGKASAN BAWAH -->
                <div class="summary-mutation">
                    <div class="d-flex align-items-center gap-2">
                        <span class="summary-mutation-icon bg-success-subtle text-success">
                            <i class="bx bx-log-out-circle"></i>
                        </span>

                        <small class="text-muted" style="font-size:.72rem;">
                            Keluar {{ $stagingOutPeriodCaption }}
                        </small>
                    </div>

                    <span class="summary-mutation-value text-success">
                        +{{ number_format($stagingOutAddedInPeriod, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>
    </div>


    <!-- ================= STOK TERSEDIA ================= -->
    <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">

                <!-- HEADER: ISI KIRI + DROPDOWN KANAN -->
                <div class="d-flex align-items-start justify-content-between">

                    <!-- KONTEN KIRI -->
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info-subtle text-info me-3">
                            <i class="bx bx-cube fs-4"></i>
                        </div>

                        <div>
                            <small class="text-muted d-block">
                                Stok Tersedia
                            </small>

                            <small class="text-muted d-block"
                                style="font-size:.7rem;">
                                Total Item
                            </small>

                            <h4 class="fw-bold mb-1">
                                {{ number_format($stockTotal, 0, ',', '.') }}
                            </h4>

                            <small class="{{ $stockGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bx {{ $stockGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                {{ $stockGrowth }}%
                            </small>
                        </div>
                    </div>

                    <!-- DROPDOWN KANAN -->
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn p-0"
                            id="cardOptStock"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            {{ $hasGlobalFilter ? 'disabled' : '' }}
                            title="{{ $hasGlobalFilter ? 'Nonaktif — filter tanggal khusus sedang aktif' : 'Filter periode Stok Tersedia' }}">

                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow period-filter-menu"
                            aria-labelledby="cardOptStock">

                            <li>
                                <a class="dropdown-item {{ $stockPeriod === 'today' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['stock_period' => 'today']) }}">
                                    Hari Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stockPeriod === 'week' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['stock_period' => 'week']) }}">
                                    Minggu Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $stockPeriod === 'month' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['stock_period' => 'month']) }}">
                                    Bulan Ini
                                </a>
                            </li>

                        </ul>
                    </div>

                </div>

                <!-- RINGKASAN BAWAH -->
                <div class="summary-mutation">
                    <div class="d-flex align-items-center gap-2">
                        <span class="summary-mutation-icon bg-info-subtle text-info">
                            <i class="bx bx-transfer"></i>
                        </span>

                        <small class="text-muted" style="font-size:.72rem;">
                            Mutasi {{ $stockPeriodCaption }}
                        </small>
                    </div>

                    <span class="summary-mutation-value text-info">
                        +{{ number_format($stockAddedInPeriod, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>
    </div>


    <!-- ================= TOTAL LOKASI ================= -->
    <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">

                <!-- HEADER: ISI KIRI + DROPDOWN KANAN -->
                <div class="d-flex align-items-start justify-content-between">

                    <!-- KONTEN KIRI -->
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-warning-subtle text-warning me-3">
                            <i class="bx bx-map fs-4"></i>
                        </div>

                        <div>
                            <small class="text-muted d-block">
                                Total Lokasi
                            </small>

                            <small class="text-muted d-block"
                                style="font-size:.7rem;">
                                Lokasi Aktif
                            </small>

                            <h4 class="fw-bold mb-1">
                                {{ number_format($activeLocations, 0, ',', '.') }}
                            </h4>

                            <small class="text-muted">
                                dari {{ $totalLocations }} lokasi
                            </small>
                        </div>
                    </div>

                    <!-- DROPDOWN KANAN -->
                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn p-0"
                            id="cardOptLocation"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            {{ $hasGlobalFilter ? 'disabled' : '' }}
                            title="{{ $hasGlobalFilter ? 'Nonaktif — filter tanggal khusus sedang aktif' : 'Filter periode Total Lokasi' }}">

                            <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow period-filter-menu"
                            aria-labelledby="cardOptLocation">

                            <li>
                                <a class="dropdown-item {{ $locationPeriod === 'today' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['location_period' => 'today']) }}">
                                    Hari Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $locationPeriod === 'week' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['location_period' => 'week']) }}">
                                    Minggu Ini
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item {{ $locationPeriod === 'month' ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['location_period' => 'month']) }}">
                                    Bulan Ini
                                </a>
                            </li>

                        </ul>
                    </div>

                </div>

                <!-- RINGKASAN BAWAH -->
                <div class="summary-mutation">
                    <div class="d-flex align-items-center gap-2">
                        <span class="summary-mutation-icon bg-warning-subtle text-warning">
                            <i class="bx bx-plus-circle"></i>
                        </span>

                        <small class="text-muted" style="font-size:.72rem;">
                            Lokasi baru {{ $locationPeriodCaption }}
                        </small>
                    </div>

                    <span class="summary-mutation-value text-warning">
                        +{{ number_format($locationAddedInPeriod, 0, ',', '.') }}
                    </span>
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
                        <small class="text-muted">{{ $chartPeriodLabel }}</small>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <div class="d-flex gap-4">
                            <div class="text-end">
                                <small class="text-muted d-block">Staging In</small>
                                <span class="fw-bold fs-5" style="color:#696cff;">{{ number_format($stagingInSeries->sum(), 0, ',', '.') }}</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Staging Out</small>
                                <span class="fw-bold fs-5" style="color:#71dd37;">{{ number_format($stagingOutSeries->sum(), 0, ',', '.') }}</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Mutasi Stok</small>
                                <span class="fw-bold fs-5" style="color:#03c3ec;">{{ number_format($mutationSeries->sum(), 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button type="button"
                                class="btn btn-icon btn-outline-secondary rounded-circle period-filter-btn"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                {{ $hasGlobalFilter ? 'disabled' : '' }}
                                title="{{ $hasGlobalFilter ? 'Nonaktif — filter tanggal khusus sedang aktif' : 'Filter periode' }}">
                                <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow period-filter-menu">
                                <li>
                                    <a class="dropdown-item {{ $chartPeriod === 'today' ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['chart_period' => 'today']) }}">Hari Ini</a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $chartPeriod === 'week' ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['chart_period' => 'week']) }}">Minggu Ini</a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ $chartPeriod === 'month' ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['chart_period' => 'month']) }}">Bulan Ini</a>
                                </li>
                            </ul>
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
                        <span><span class="status-dot bg-danger me-2"></span>Total So</span>
                        <span class="d-flex align-items-center gap-2">
                            <strong>{{ $stagingOutStatus['total_so'] }}</strong>
                            <span class="status-icon-sm bg-danger-subtle text-danger">
                                <i class="bx bx-chart"></i>
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
                    <div class="alert-card bg-primary-subtle">
                        <i class="bx bx-package text-primary fs-4"></i>
                        <h5 class="fw-bold mt-2 mb-0">{{ $alerts['staging_out_siap_kirim'] }} Item</h5>
                        <small class="text-muted d-block mb-2">Siap Kirim</small>
                        <small class="text-muted d-block mb-2">Sudah picking, menunggu proses pengiriman</small>
                        <a href="{{ route('stagings-out.index', ['status' => 'siap_kirim']) }}" class="small text-decoration-none">Lihat daftar</a>
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

                // ================= CHART.JS: RINGKASAN AKTIVITAS (LINE) =================
                // Semua seri berupa garis, tapi didesain supaya tetap
                // terbaca jelas meskipun 2 seri punya nilai yang sama
                // persis di satu titik (sehingga garisnya berhimpit):
                // - Warna berbeda + pola garis berbeda (solid/putus-putus/
                //   titik-titik) supaya garis yang "ketutup" tetap
                //   kelihatan selang-selingnya.
                // - Bentuk titik data (marker) berbeda per seri.
                // - Area gradient tipis di bawah tiap garis supaya tren
                //   naik/turun lebih mudah dibaca sekilas.
                (function() {
                    const canvas = document.getElementById('chartActivity');
                    const ctx = canvas.getContext('2d');

                    function hexToRgba(hex, alpha) {
                        const r = parseInt(hex.slice(1, 3), 16);
                        const g = parseInt(hex.slice(3, 5), 16);
                        const b = parseInt(hex.slice(5, 7), 16);
                        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
                    }

                    function areaGradient(hex, alphaTop) {
                        const g = ctx.createLinearGradient(0, 0, 0, 320);
                        g.addColorStop(0, hexToRgba(hex, alphaTop));
                        g.addColorStop(1, hexToRgba(hex, 0));
                        return g;
                    }

                    const colorIn = '#696cff';
                    const colorOut = '#71dd37';
                    const colorMutation = '#03c3ec';

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                    label: 'Staging In',
                                    data: @json($stagingInSeries),
                                    borderColor: colorIn,
                                    backgroundColor: areaGradient(colorIn, 0.25),
                                    borderWidth: 3,
                                    borderDash: [],
                                    pointStyle: 'circle',
                                    pointRadius: 4,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: colorIn,
                                    pointBorderWidth: 2.5,
                                    pointHoverRadius: 7,
                                    pointHoverBorderWidth: 3,
                                    tension: 0.4,
                                    fill: 'origin',
                                    order: 3
                                },
                                {
                                    label: 'Staging Out',
                                    data: @json($stagingOutSeries),
                                    borderColor: colorOut,
                                    backgroundColor: areaGradient(colorOut, 0.2),
                                    borderWidth: 3,
                                    borderDash: [],
                                    pointStyle: 'circle',
                                    pointRadius: 4,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: colorOut,
                                    pointBorderWidth: 2.5,
                                    pointHoverRadius: 7,
                                    pointHoverBorderWidth: 3,
                                    tension: 0.4,
                                    fill: 'origin',
                                    order: 2
                                },
                                {
                                    label: 'Mutasi ke Stok',
                                    data: @json($mutationSeries),
                                    borderColor: colorMutation,
                                    backgroundColor: areaGradient(colorMutation, 0.2),
                                    borderWidth: 3,
                                    borderDash: [],
                                    pointStyle: 'circle',
                                    pointRadius: 5,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: colorMutation,
                                    pointBorderWidth: 2.5,
                                    pointHoverRadius: 8,
                                    pointHoverBorderWidth: 3,
                                    tension: 0.4,
                                    fill: 'origin',
                                    order: 1
                                }
                            ]
                        },
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
                                        boxWidth: 8,
                                        boxHeight: 8,
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
                                    usePointStyle: true,
                                    boxPadding: 4,
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