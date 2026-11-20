@extends('master')
@section('title', 'Staging Out History')
@section('content')

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Staging Out History</h4>
            <small class="text-muted">Monitor dan lacak seluruh perjalanan data staging out</small>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Total History -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-primary-subtle text-primary me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total History</small>
                        <h4 class="fw-bold mb-1">{{ number_format($totalHistory, 0, ',', '.') }}</h4>
                        @if ($createdToday > 0)
                            <small class="text-primary">
                                <i class="bi bi-arrow-up-short"></i> +{{ $createdToday }} hari ini
                            </small>
                        @else
                            <small class="text-muted">Seluruh riwayat</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Masih Aktif -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-success-subtle text-success me-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Masih Aktif</small>
                        <h4 class="fw-bold mb-1">{{ number_format($activeCount, 0, ',', '.') }}</h4>
                        <small class="text-success">Belum dikirim</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Dikirim -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-info-subtle text-info me-3">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Sudah Dikirim</small>
                        <h4 class="fw-bold mb-1">{{ number_format($deliveredCount, 0, ',', '.') }}</h4>
                        <small class="text-info">DO sudah terbit</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dihapus -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-danger-subtle text-danger me-3">
                        <i class="bi bi-trash fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Dihapus</small>
                        <h4 class="fw-bold mb-1">{{ number_format($removedCount, 0, ',', '.') }}</h4>
                        <small class="text-danger">History terhapus</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ================= KIRI: TABEL HISTORY ================= --}}
        <div class="col-12" id="historyTableCol">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="float-start">
                        <h5 class="mb-0 fw-bold">Staging Out History</h5>
                        <small class="text-muted">Daftar history staging out</small>
                    </div>
                    <div class="float-end mt-3">
                        <span class="text-muted small me-2" id="recordCountLabel"></span>
                        <button type="button" class="btn-sm btn btn-danger d-none me-1" id="bulkRestoreBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3.578 6.487A8 8 0 1 1 2.5 10.5M7.5 6.5h-4v-4" />
                            </svg>
                            <span class="d-none d-md-inline ms-1">Pulihkan (<span id="bulkRestoreCount">0</span>)</span>
                        </button>
                        <button type="button" class="btn-sm btn btn-outline-danger d-none me-1" id="bulkDestroyBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3" />
                            </svg>
                            <span class="d-none d-md-inline ms-1">Hapus (<span id="bulkDestroyCount">0</span>)</span>
                        </button>
                        <a href="{{ route('stagings-out-history.export') }}" class="btn-sm btn border-secondary bg-white border"
                            id="exportHistoryBtn">
                            <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                viewBox="0 0 24 24">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path fill="currentColor"
                                    d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                            </svg>
                            <span class="d-none d-md-inline ms-1">Export</span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <div class="px-3 pt-3">

                        <div class="fx" id="filterBar">

                            <div class="fx-top">

                                <!-- Pencarian -->
                                <div class="fx-search">
                                    <i class="bi bi-search fx-search-icon"></i>
                                    <input type="text" id="historySearch" autocomplete="off" placeholder="Cari No. SO, item, atau customer" aria-label="Cari">
                                    <button type="button" class="fx-search-clear" id="customSearchClear" aria-label="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="fx-actions">
                                    <span class="fx-count" id="filterCount" aria-live="polite"></span>
                                    <button type="button" class="fx-reset" id="resetHistoryFilter" title="Kembalikan semua filter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Reset</span>
                                    </button>
                                </div>

                            </div>

                            <div class="fx-chips">

                                <div class="fx-chip">
                                    <i class="bi bi-flag"></i>
                                    <label for="historyStatus">Status</label>
                                    <select id="historyStatus">
                                        <option value="">Semua</option>
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="fx-chip">
                                    <i class="bi bi-activity"></i>
                                    <label for="historyEvent">Event</label>
                                    <select id="historyEvent">
                                        <option value="">Semua</option>
                                        @foreach ($eventOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="fx-chip fx-chip-date">
                                    <i class="bi bi-calendar-event"></i>
                                    <label for="historyDateRange">Periode</label>

                                    <div class="date-range-wrapper">
                                        <input type="text" id="historyDateRange" placeholder="Semua tanggal" title="Rentang tanggal" aria-label="Rentang tanggal" readonly autocomplete="off">

                                        <div class="date-range-panel" id="historyDateRangePanel">
                                            <div class="fx-presets">
                                                <button type="button" class="fx-preset" data-preset="today">Hari ini</button>
                                                <button type="button" class="fx-preset" data-preset="7d">7 hari terakhir</button>
                                                <button type="button" class="fx-preset" data-preset="month">Bulan ini</button>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Dari</label>
                                                <input type="date" class="form-control form-control-sm" id="historyStartDate">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Sampai</label>
                                                <input type="date" class="form-control form-control-sm" id="historyEndDate">
                                            </div>
                                            <div class="d-flex gap-2 mt-3">
                                                <button type="button" class="btn btn-sm btn-light flex-shrink-0" id="historyDateRangeClear">Hapus</button>
                                                <button type="button" class="btn btn-sm btn-primary w-100" id="historyDateRangeApply">Terapkan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <table class="table table-bordered" id="historyTable">
                            <thead>
                                <tr>
                                    <th style="width:36px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllHistory"
                                            title="Pilih semua baris di halaman ini">
                                    </th>
                                    <th>No</th>
                                    <th>Item &amp; SO</th>
                                    <th>Customer</th>
                                    <th>Line Item</th>
                                    <th>Tgl Instruksi Kirim</th>
                                    <th>Qty</th>
                                    <th>Pengiriman</th>
                                    <th>Status</th>
                                    <th>Aktivitas Terakhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="mb-3 ms-2 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                            id="historyTableFooter">
                            <div class="d-flex align-items-center gap-2" id="historyLengthWrapper">
                                <span>Tampilkan</span>
                                <select id="historyLength" class="form-select form-select-sm" style="width:80px">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span>data</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ================= KANAN: DETAIL PANEL ================= --}}
        <div class="col-lg-4 d-none" id="historyDetailCol">
            <div class="card border-0 shadow-sm" style="position:sticky; top:1rem; max-height:calc(100vh - 2rem);">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4 flex-shrink-0">
                    <div>
                        <h5 class="mb-0 fw-bold">History Detail</h5>
                        <small class="text-muted" id="detailSubtitle">Pilih salah satu data untuk melihat detail</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" class="btn-sm btn border-secondary bg-white border d-none" id="detailExportBtn" title="Export Detail Ini">
                            <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                viewBox="0 0 24 24">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path fill="currentColor"
                                    d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                            </svg>
                            <span class="d-none d-md-inline ms-1">Export</span>
                        </a>
                        <button type="button" class="btn-close d-none" id="closeDetailBtn"></button>
                    </div>
                </div>

                <div class="card-body" style="overflow-y:auto; min-height:0;">
                    <div id="detailEmptyState" class="text-center text-muted py-5">
                        <i class="bi bi-clock-history" style="font-size:2.5rem;"></i>
                        <p class="mt-2 mb-0 small">Klik ikon <i class="bi bi-eye"></i> pada tabel di sebelah kiri untuk
                            melihat detail riwayat.</p>
                    </div>

                    <div id="detailContent" class="d-none">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold mb-0" id="detailItemName">-</h6>
                                <small class="text-muted" id="detailItemSo">-</small>
                            </div>
                            <span id="detailStatusBadge" class="badge">-</span>
                        </div>

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Informasi Awal</h6>
                        <table class="table table-sm table-borderless mb-3">
                            <tbody id="detailInfoAwal">
                            </tbody>
                        </table>

                        {{-- Rincian sumber barang (hanya muncul kalau sumbernya lebih dari satu) --}}
                        <div id="detailSumber" class="mb-3 d-none"></div>

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Status Pengiriman</h6>
                        <table class="table table-sm table-borderless mb-4">
                            <tbody id="detailPengiriman">
                            </tbody>
                        </table>

                        <h6 class="fw-bold small text-uppercase text-muted mb-3">Activity Timeline</h6>
                        <div id="detailTimeline" class="history-timeline"></div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('script')
        {{-- SweetAlert2 belum tentu ke-load dari master layout. Muat sendiri
             KALAU BELUM ADA (synchronous via document.write supaya script di
             bawahnya yang memanggil Swal dijamin jalan setelah ini). --}}
        <script>
            if (typeof Swal === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
            }
        </script>
        <style>
            /* Toast SweetAlert2: paksa latar & warna teks supaya tidak "putih
               tanpa tulisan" kalau CSS tema/layout menimpa warna teksnya. */
            .swal2-container {
                z-index: 9999999 !important;
            }

            .swal2-popup.swal2-toast {
                background: #fff !important;
                color: #566a7f !important;
                box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .2) !important;
            }

            .swal2-popup.swal2-toast .swal2-title,
            .swal2-popup.swal2-toast .swal2-html-container {
                color: #566a7f !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            .icon-box-history {
                border-radius: 14px;
                width: 52px;
                height: 52px;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
            }

            /* ===== Filter bar ===== */
            .fx {
                --fx-accent: var(--bs-primary, #0d6efd);
                --fx-accent-rgb: var(--bs-primary-rgb, 13, 110, 253);
                --fx-line: var(--bs-border-color, #dee2e6);
                --fx-muted: var(--bs-secondary-color, #6c757d);
                --fx-surface: var(--bs-body-bg, #fff);
                margin-bottom: 1rem;
            }

            .fx-top {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: .6rem;
            }

            /* Pencarian */
            .fx-search {
                position: relative;
                flex: 1 1 320px;
                min-width: 0;
            }

            .fx-search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--fx-muted);
                pointer-events: none;
            }

            .fx-search input {
                width: 100%;
                height: 2.6rem;
                padding: 0 2.6rem 0 2.6rem;
                border: 1px solid var(--fx-line);
                border-radius: 999px;
                background: var(--fx-surface);
                color: var(--bs-body-color);
                font-size: .9rem;
                transition: border-color .15s ease, box-shadow .15s ease;
            }

            .fx-search input::placeholder {
                color: var(--fx-muted);
                opacity: .85;
            }

            .fx-search input:focus {
                outline: 0;
                border-color: rgba(var(--fx-accent-rgb), .55);
                box-shadow: 0 0 0 .25rem rgba(var(--fx-accent-rgb), .12);
            }

            .fx-search.has-value input {
                border-color: rgba(var(--fx-accent-rgb), .45);
            }

            .fx-search-clear {
                position: absolute;
                right: .45rem;
                top: 50%;
                transform: translateY(-50%);
                display: none;
                align-items: center;
                justify-content: center;
                width: 1.8rem;
                height: 1.8rem;
                padding: 0;
                border: 0;
                border-radius: 50%;
                background: transparent;
                color: var(--fx-muted);
                font-size: .75rem;
            }

            .fx-search.has-value .fx-search-clear {
                display: inline-flex;
            }

            .fx-search-clear:hover {
                background: rgba(var(--fx-accent-rgb), .1);
                color: var(--fx-accent);
            }

            /* Ringkasan + reset */
            .fx-actions {
                display: flex;
                align-items: center;
                gap: .6rem;
            }

            .fx-count {
                display: none;
                font-size: .8rem;
                font-weight: 600;
                color: var(--fx-accent);
                white-space: nowrap;
            }

            .fx.has-active .fx-count {
                display: inline;
            }

            .fx-reset {
                display: inline-flex;
                align-items: center;
                gap: .4rem;
                height: 2.6rem;
                padding: 0 1.05rem;
                border: 1px solid var(--fx-line);
                border-radius: 999px;
                background: var(--fx-surface);
                color: var(--fx-muted);
                font-size: .85rem;
                font-weight: 500;
                transition: color .15s ease, border-color .15s ease, background-color .15s ease;
            }

            .fx-reset:hover,
            .fx.has-active .fx-reset {
                color: var(--fx-accent);
                border-color: rgba(var(--fx-accent-rgb), .45);
            }

            .fx-reset:hover {
                background: rgba(var(--fx-accent-rgb), .06);
            }

            .fx-reset:focus-visible,
            .fx-search-clear:focus-visible,
            .fx-preset:focus-visible {
                outline: 2px solid rgba(var(--fx-accent-rgb), .6);
                outline-offset: 2px;
            }

            /* Chip filter */
            .fx-chips {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
                margin-top: .7rem;
            }

            .fx-chip {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: .5rem;
                height: 2.3rem;
                padding: 0 .4rem 0 .85rem;
                border: 1px solid var(--fx-line);
                border-radius: 999px;
                background: var(--fx-surface);
                color: var(--bs-body-color);
                transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
            }

            .fx-chip:hover {
                border-color: rgba(var(--fx-accent-rgb), .4);
            }

            .fx-chip:focus-within {
                border-color: rgba(var(--fx-accent-rgb), .6);
                box-shadow: 0 0 0 .22rem rgba(var(--fx-accent-rgb), .12);
            }

            .fx-chip>i {
                color: var(--fx-muted);
                font-size: .95rem;
                line-height: 1;
            }

            .fx-chip label {
                margin: 0;
                font-size: .8rem;
                color: var(--fx-muted);
                white-space: nowrap;
                cursor: pointer;
            }

            .fx-chip select,
            .fx-chip input[type="text"] {
                height: 100%;
                max-width: 11.5rem;
                padding: 0 1.4rem 0 .1rem;
                border: 0;
                outline: 0;
                box-shadow: none;
                background-color: transparent;
                color: var(--bs-body-color);
                font-size: .85rem;
                font-weight: 600;
                text-overflow: ellipsis;
                cursor: pointer;
            }

            .fx-chip select {
                -webkit-appearance: none;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%238a93a3' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.6' d='m4 6 4 4 4-4'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right .3rem center;
                background-size: .8rem;
            }

            .fx-chip select option {
                color: var(--bs-body-color);
                background: var(--fx-surface);
                font-weight: 400;
            }

            .fx-chip input[type="text"]::placeholder {
                color: var(--fx-muted);
                font-weight: 400;
            }

            .fx-chip.is-active {
                background: rgba(var(--fx-accent-rgb), .08);
                border-color: rgba(var(--fx-accent-rgb), .45);
            }

            .fx-chip.is-active>i,
            .fx-chip.is-active label {
                color: var(--fx-accent);
            }

            .fx-sep {
                align-self: center;
                width: 1px;
                height: 1.1rem;
                background: var(--fx-line);
            }

            .fx-chip.is-active .fx-sep {
                background: rgba(var(--fx-accent-rgb), .3);
            }

            /* Rentang tanggal */
            .date-range-wrapper {
                position: relative;
                display: flex;
                align-items: center;
                height: 100%;
            }

            .fx-chip-date input[type="text"] {
                width: 10.5rem;
                padding-right: .6rem;
            }

            .date-range-panel {
                display: none;
                position: absolute;
                top: calc(100% + 10px);
                left: 0;
                z-index: 1050;
                width: 270px;
                padding: 14px;
                background: var(--fx-surface);
                border: 1px solid var(--fx-line);
                border-radius: 1rem;
                box-shadow: 0 .75rem 2rem rgba(20, 20, 43, .14);
            }

            .date-range-panel.show {
                display: block;
                animation: fxPop .14s ease-out;
            }

            @keyframes fxPop {
                from {
                    opacity: 0;
                    transform: translateY(-4px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fx-presets {
                display: flex;
                flex-wrap: wrap;
                gap: .35rem;
                margin-bottom: .75rem;
            }

            .fx-preset {
                padding: .25rem .65rem;
                border: 1px solid var(--fx-line);
                border-radius: 999px;
                background: transparent;
                color: var(--bs-body-color);
                font-size: .76rem;
                transition: border-color .15s ease, background-color .15s ease, color .15s ease;
            }

            .fx-preset:hover {
                border-color: rgba(var(--fx-accent-rgb), .5);
                background: rgba(var(--fx-accent-rgb), .08);
                color: var(--fx-accent);
            }

            /* Banner filter dari dashboard */
            .fx-banner {
                display: flex;
                align-items: center;
                gap: .6rem;
                margin-bottom: .9rem;
                padding: .6rem .6rem .6rem .9rem;
                border: 1px solid rgba(var(--fx-accent-rgb), .3);
                border-radius: .85rem;
                background: rgba(var(--fx-accent-rgb), .07);
                color: var(--bs-body-color);
                font-size: .86rem;
                white-space: normal;
            }

            .fx-banner>i {
                color: var(--fx-accent);
                font-size: 1.05rem;
            }

            .fx-banner>span {
                flex: 1;
            }

            .fx-banner-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.7rem;
                height: 1.7rem;
                padding: 0;
                border: 0;
                border-radius: 50%;
                background: transparent;
                color: var(--fx-muted);
                font-size: .72rem;
            }

            .fx-banner-close:hover {
                background: rgba(var(--fx-accent-rgb), .14);
                color: var(--fx-accent);
            }

            @media (max-width: 575.98px) {
                .fx-actions {
                    width: 100%;
                    justify-content: space-between;
                }

                .fx-chip-date {
                    flex: 1 1 100%;
                }

                .fx-chip-date .date-range-wrapper {
                    flex: 1;
                }

                .fx-chip-date input[type="text"] {
                    width: 100%;
                }

                .date-range-panel {
                    left: -3rem;
                    right: 0;
                    width: auto;
                    min-width: 260px;
                }
            }

            @media (prefers-reduced-motion: reduce) {

                .fx *,
                .date-range-panel.show {
                    transition: none !important;
                    animation: none !important;
                }
            }

            /* ===== Timeline detail kanan ===== */
            .history-timeline {
                position: relative;
                padding-left: 28px;
            }

            .history-timeline::before {
                content: '';
                position: absolute;
                left: 9px;
                top: 4px;
                bottom: 4px;
                width: 2px;
                background: #e9ecef;
            }

            .timeline-item {
                position: relative;
                padding-bottom: 1.25rem;
            }

            .timeline-item:last-child {
                padding-bottom: 0;
            }

            .timeline-dot {
                position: absolute;
                left: -28px;
                top: 2px;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: .7rem;
            }

            .timeline-body {
                background: #f8f9fb;
                border: 1px solid #eceef2;
                border-radius: .6rem;
                padding: .6rem .8rem;
            }
        </style>

        <script>
            // ============ HELPER FORMAT TANGGAL ============
            // Semua tanggal ditampilkan dengan format DD-MM-YYYY (tanggal-bulan-tahun).
            // Backend bisa mengirim ISO UTC (mis. 2026-08-20T17:00:00.000000Z) atau
            // tanggal polos (mis. 2026-08-20). ISO UTC dikonversi ke zona waktu lokal (WIB).
            function pad2(n) {
                return String(n).padStart(2, '0');
            }

            function formatDateOnly(value) {
                if (!value) return '-';
                if (typeof value !== 'string') return value;

                // Tanggal polos YYYY-MM-DD (tanpa jam) -> langsung tukar urutan, tanpa konversi zona waktu
                const plain = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (plain) return `${plain[3]}-${plain[2]}-${plain[1]}`;

                if (!/^\d{4}-\d{2}-\d{2}T/.test(value)) return value;
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                return `${pad2(d.getDate())}-${pad2(d.getMonth() + 1)}-${d.getFullYear()}`;
            }

            function formatDateTime(value) {
                if (!value) return '-';
                if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T/.test(value)) return value;
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                const datePart = formatDateOnly(value);
                const timePart = d.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                return `${datePart} ${timePart}`;
            }

            $(document).ready(function() {

                let historyTable;
                let appliedHistoryStart = '';
                let appliedHistoryEnd = '';

                historyTable = $('#historyTable').DataTable({
                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],

                    ajax: {
                        url: "{{ route('stagings-out-history.data') }}",
                        data: function(d) {
                            d.status = $('#historyStatus').val();
                            d.event = $('#historyEvent').val();
                            d.start_date = appliedHistoryStart;
                            d.end_date = appliedHistoryEnd;
                        }
                    },

                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'item_so',
                            name: 'item_so',
                            orderable: false
                        },
                        {
                            data: 'customer',
                            name: 'customer'
                        },
                        {
                            data: 'line_item',
                            name: 'line_item'
                        },
                        {
                            data: 'delivery_instruction_date',
                            name: 'delivery_instruction_date',
                            render: function(data) {
                                return formatDateOnly(data);
                            }
                        },
                        {
                            data: 'current_qty',
                            name: 'current_qty',
                            orderable: false
                        },
                        {
                            data: 'shipping_status',
                            name: 'shipping_status',
                            orderable: false
                        },
                        {
                            data: 'row_status',
                            name: 'row_status',
                            orderable: false
                        },
                        {
                            data: 'last_activity',
                            name: 'last_activity',
                            orderable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            searchable: false,
                            orderable: false
                        }
                    ],

                    scrollX: true,
                    autoWidth: false,

                    columnDefs: [{
                            targets: [0],
                            className: "text-center",
                            width: "36px"
                        },
                        {
                            targets: [2, 3],
                            className: "text-wrap",
                            width: "200px"
                        },
                        {
                            targets: [5, 6, 7, 8, 10],
                            className: "text-center"
                        }
                    ],

                    initComplete: function() {
                        moveHistoryTableElements();
                    },

                    drawCallback: function() {
                        moveHistoryTableElements();
                        $('#recordCountLabel').text(
                            historyTable.page.info().recordsTotal.toLocaleString('id-ID') + ' Records'
                        );
                    }
                });

                function moveHistoryTableElements() {
                    const $info = $('#historyTable_info');
                    if ($info.length && !$('#historyLengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#historyLengthWrapper');
                    }

                    const $paginate = $('#historyTable_paginate');
                    if ($paginate.length && !$('#historyTableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#historyTableFooter');
                    }
                }

                // ============ CHECKBOX BARIS & BULK DESTROY ============
                // Pulihkan hanya menghitung baris berstatus "Selesai"
                // (data-restorable="1"); Hapus menghitung semua yang tercentang.
                function updateBulkButtons() {
                    const restoreCount = $('.historyCheckbox[data-restorable="1"]:checked').length;
                    const deleteCount = $('.historyCheckbox:checked').length;

                    $('#bulkRestoreCount').text(restoreCount);
                    $('#bulkRestoreBtn').toggleClass('d-none', restoreCount === 0);

                    $('#bulkDestroyCount').text(deleteCount);
                    $('#bulkDestroyBtn').toggleClass('d-none', deleteCount === 0);
                }

                $(document).on('change', '.historyCheckbox', function() {
                    updateBulkButtons();

                    const $selectable = $('.historyCheckbox');
                    const allChecked = $selectable.length > 0 &&
                        $selectable.length === $selectable.filter(':checked').length;
                    $('#selectAllHistory').prop('checked', allChecked);
                });

                $('#selectAllHistory').on('change', function() {
                    $('.historyCheckbox').prop('checked', $(this).is(':checked'));
                    updateBulkButtons();
                });

                // Reset seleksi tiap tabel di-redraw (ganti halaman/filter/search).
                historyTable.on('draw', function() {
                    $('#selectAllHistory').prop('checked', false);
                    updateBulkButtons();
                });

                function showRestoreReportAlert(icon, title, rawText) {
                    Swal.fire({
                        icon: icon,
                        title: title,
                        html: '<pre class="text-start small" style="white-space:pre-wrap;max-height:50vh;overflow-y:auto;">' +
                            rawText.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                            '</pre>',
                        confirmButtonText: 'Tutup',
                        showDenyButton: true,
                        denyButtonText: '\ud83d\udccb Copy',
                        width: 650,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    }).then(function(result) {
                        if (result.isDenied) {
                            navigator.clipboard.writeText(rawText).then(function() {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Teks berhasil disalin',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    didOpen: () => {
                                        document.querySelector('.swal2-container').style.zIndex = '9999999';
                                    }
                                });
                            });
                        }
                    });
                }

                // ============ BULK RESTORE (Selesai -> kembali ke Staging Out) ============
                $('#bulkRestoreBtn').on('click', function() {
                    const ids = $('.historyCheckbox[data-restorable="1"]:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (ids.length === 0) {
                        return;
                    }

                    Swal.fire({
                        icon: 'question',
                        title: 'Pulihkan Data?',
                        html: `Yakin mau memulihkan <b>${ids.length}</b> data terpilih kembali ke Staging Out?` +
                            `<br><small class="text-muted">Tgl resi pengiriman akan dikosongkan sehingga datanya bisa diedit lagi. Stok tidak berubah.</small>`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Pulihkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#0d6efd'
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $('#bulkRestoreBtn').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('stagings-out-history.bulk-restore') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: ids
                            },
                            success: function(res) {
                                historyTable.ajax.reload(null, false);

                                if (res.errors && res.errors.length > 0) {
                                    const rawText = res.message + '\n\n' + res.errors.join('\n\n');
                                    showRestoreReportAlert(
                                        res.restored > 0 ? 'warning' : 'error',
                                        res.restored > 0 ? 'Sebagian Berhasil Dipulihkan' : 'Gagal Dipulihkan',
                                        rawText
                                    );
                                } else {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: res.message || 'Proses berhasil.',
                                        showConfirmButton: false,
                                        timer: 3500,
                                        timerProgressBar: true,
                                        background: '#fff',
                                        color: '#566a7f',
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Memulihkan Data',
                                    text: (xhr.responseJSON && xhr.responseJSON.message) ||
                                        'Terjadi kesalahan saat memulihkan data.'
                                });
                            },
                            complete: function() {
                                $('#bulkRestoreBtn').prop('disabled', false);
                            }
                        });
                    });
                });

                // ============ BULK DESTROY (hapus permanen history) ============
                $('#bulkDestroyBtn').on('click', function() {
                    const ids = $('.historyCheckbox:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (ids.length === 0) {
                        return;
                    }

                    // Hapus = murni hapus record. Stok, stock mutations, dan Staging In
                    // tidak berubah; baris di Staging Out (kalau masih ada) juga tetap.
                    const activeCount = $('.historyCheckbox[data-active="1"]:checked').length;
                    let activeNote = '';

                    if (activeCount > 0) {
                        activeNote = `<br><small class="text-danger">${activeCount} di antaranya masih aktif (datanya masih ada di Staging Out). Hanya history-nya yang dihapus, datanya tetap ada.</small>`;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus History?',
                        html: `Yakin mau menghapus <b>${ids.length}</b> history terpilih beserta seluruh timeline-nya?<br><small class="text-muted">Hanya menghapus record history. Stok tidak berubah. Tindakan ini permanen dan tidak bisa dibatalkan.</small>${activeNote}`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545'
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $('#bulkDestroyBtn').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('stagings-out-history.bulk-destroy') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: ids
                            },
                            success: function(res) {
                                historyTable.ajax.reload(null, false);

                                if (res.errors && res.errors.length > 0) {
                                    const rawText = res.message + '\n\n' + res.errors.join('\n\n');
                                    showRestoreReportAlert(
                                        res.deleted > 0 ? 'warning' : 'error',
                                        res.deleted > 0 ? 'Sebagian Berhasil Dihapus' : 'Gagal Dihapus',
                                        rawText
                                    );
                                } else {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: res.message || 'Data berhasil dihapus.',
                                        showConfirmButton: false,
                                        timer: 3500,
                                        timerProgressBar: true,
                                        background: '#fff',
                                        color: '#566a7f',
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menghapus Data',
                                    text: (xhr.responseJSON && xhr.responseJSON.message) ||
                                        'Terjadi kesalahan saat menghapus data.'
                                });
                            },
                            complete: function() {
                                $('#bulkDestroyBtn').prop('disabled', false);
                            }
                        });
                    });
                });

                $('#historySearch').on('input', function() {
                    historyTable.search(this.value).draw();
                    updateHistoryExportUrl();
                });

                $('#historyLength').change(function() {
                    historyTable.page.len($(this).val()).draw();
                });

                $('#historyStatus, #historyEvent').on('change', function() {
                    historyTable.ajax.reload();
                    updateHistoryExportUrl();
                });

                $('#resetHistoryFilter').click(function() {
                    $('#historySearch').val('');
                    $('#historyStatus').val('');
                    $('#historyEvent').val('');
                    $('#historyDateRange').val('');
                    $('#historyStartDate').val('');
                    $('#historyEndDate').val('');
                    appliedHistoryStart = '';
                    appliedHistoryEnd = '';
                    historyTable.search('').ajax.reload();
                    updateHistoryExportUrl();
                });

                // ============ PANEL RENTANG TANGGAL ============
                const $dateInput = $('#historyDateRange');
                const $panel = $('#historyDateRangePanel');
                const $startInput = $('#historyStartDate');
                const $endInput = $('#historyEndDate');

                $dateInput.on('click', function(e) {
                    e.stopPropagation();
                    $panel.toggleClass('show');
                });

                $panel.on('click', function(e) {
                    e.stopPropagation();
                });

                $(document).on('click', function() {
                    if ($panel.hasClass('show')) {
                        $startInput.val(appliedHistoryStart);
                        $endInput.val(appliedHistoryEnd);
                    }
                    $panel.removeClass('show');
                });

                $('#historyDateRangeApply').on('click', function() {
                    const start = $startInput.val();
                    const end = $endInput.val();

                    if (start && end && start > end) {
                        alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                        return;
                    }

                    appliedHistoryStart = start;
                    appliedHistoryEnd = end;

                    if (start && end) {
                        $dateInput.val(formatDateOnly(start) + ' s/d ' + formatDateOnly(end));
                    } else if (start) {
                        $dateInput.val(formatDateOnly(start) + ' s/d ...');
                    } else {
                        $dateInput.val('');
                    }

                    $panel.removeClass('show');
                    historyTable.ajax.reload();
                    updateHistoryExportUrl();
                });

                function updateHistoryExportUrl() {
                    let url = new URL("{{ route('stagings-out-history.export') }}");

                    if (appliedHistoryStart) url.searchParams.append('start_date', appliedHistoryStart);
                    if (appliedHistoryEnd) url.searchParams.append('end_date', appliedHistoryEnd);
                    if ($('#historyStatus').val()) url.searchParams.append('status', $('#historyStatus').val());
                    if ($('#historyEvent').val()) url.searchParams.append('event', $('#historyEvent').val());
                    if ($('#historySearch').val().trim()) url.searchParams.append('search', $('#historySearch').val()
                        .trim());

                    $('#exportHistoryBtn').attr('href', url.toString());
                }

                updateHistoryExportUrl();

                // ============ DETAIL PANEL (KANAN) ============
                const eventStyle = {
                    created: {
                        color: 'primary',
                        icon: 'bi-plus-lg'
                    },
                    updated: {
                        color: 'warning',
                        icon: 'bi-pencil'
                    },
                    picking_confirmed: {
                        color: 'info',
                        icon: 'bi-check2-square'
                    },
                    delivered: {
                        color: 'success',
                        icon: 'bi-truck'
                    },
                    deleted: {
                        color: 'danger',
                        icon: 'bi-trash'
                    },
                    bulk_deleted: {
                        color: 'danger',
                        icon: 'bi-trash'
                    },
                    reset_by_import: {
                        color: 'danger',
                        icon: 'bi-arrow-counterclockwise'
                    },
                    restored: {
                        color: 'info',
                        icon: 'bi-arrow-counterclockwise'
                    },
                    sources_set: {
                        color: 'primary',
                        icon: 'bi-diagram-3'
                    },
                };

                function escapeHtml(value) {
                    return $('<div>').text(value ?? '').html();
                }

                function metaDescription(item) {
                    const meta = item.meta || {};

                    // Barang diambil dari beberapa sumber: satu penjelasan sederhana.
                    if (item.event_type === 'sources_set') {
                        const sum = item.source_summary;

                        if (!sum) return '';

                        return `<div class="small mt-2">
                            <div>Total <span class="fw-semibold">${sum.total} PCS</span> diambil dari <span class="fw-semibold">${sum.count} sumber</span>:</div>
                            ${sum.items.map(i => `<div>&bull; <span class="fw-semibold">${i.qty} PCS</span> dari ${escapeHtml(i.text)}</div>`).join('')}
                        </div>`;
                    }

                    if (item.event_type === 'restored') {
                        return meta.cleared_delivery_receipt_date ?
                            `<div class="small mt-2">Tgl resi pengiriman dikosongkan (sebelumnya <span class="fw-semibold">${formatDateOnly(meta.cleared_delivery_receipt_date)}</span>)</div>` :
                            '';
                    }

                    if (item.event_type === 'picking_confirmed') {
                        return meta.picking_date ?
                            `<div class="small mt-2">Tanggal picking: <span class="fw-semibold">${formatDateOnly(meta.picking_date)}</span></div>` :
                            '';
                    }

                    if (item.event_type === 'delivered') {
                        return `
                            <div class="small mt-2">
                                ${meta.do_number ? `<div>DO Number: <span class="text-primary">${meta.do_number}</span></div>` : ''}
                                ${meta.delivery_date ? `<div>Tanggal kirim: ${formatDateOnly(meta.delivery_date)}</div>` : ''}
                            </div>`;
                    }

                    if (item.event_type === 'updated' && Array.isArray(item.changes) && item.changes.length) {
                        const dash = '<span class="text-muted">kosong</span>';
                        const rows = item.changes.map(function(c) {
                            const oldVal = c.old === null || c.old === undefined ? dash : escapeHtml(formatDateOnly(c.old));
                            const newVal = c.new === null || c.new === undefined ? dash : escapeHtml(formatDateOnly(c.new));
                            return `<div>${escapeHtml(c.label)}: <span class="text-muted">${oldVal}</span> &rarr; <span class="fw-semibold">${newVal}</span></div>`;
                        }).join('');

                        return `<div class="small mt-2">${rows}${item.split_note ? `<div class="text-muted fst-italic mt-1">${escapeHtml(item.split_note)}</div>` : ''}</div>`;
                    }

                    if (item.event_type === 'updated') {
                        let rows = Object.keys(meta).map(function(field) {
                            const change = meta[field];
                            const oldVal = formatDateOnly(change.old ?? '-');
                            const newVal = formatDateOnly(change.new ?? '-');
                            return `<div>${field}: <span class="text-muted">${oldVal}</span> &rarr; <span class="fw-semibold">${newVal}</span></div>`;
                        }).join('');
                        return rows ? `<div class="small mt-2">${rows}</div>` : '';
                    }

                    if (item.event_type === 'created') {
                        let source = '';

                        if (meta.source_type === 'stock') {
                            const locationName = meta.location_name || (meta.location_id ? `Lokasi #${meta.location_id}` : '-');
                            source = `<div>Diambil dari stok: <span class="fw-semibold">${escapeHtml(locationName)}</span> (${meta.lot ? 'Lot ' + escapeHtml(meta.lot) : 'Tanpa Lot'})</div>`;
                        } else if (meta.source_type === 'external') {
                            source = '<div>Sumber: Eksternal</div>';
                        }

                        return `<div class="small mt-2"><div>Qty: ${item.qty_after ?? '-'} PCS</div>${source}</div>`;
                    }

                    return '';
                }

                // Warna per jenis sumber: Stok = biru muda, Staging In = biru tua, lainnya = abu-abu.
                const sourceColor = {
                    stock: 'info',
                    staging_in: 'primary'
                };

                // Tabel rincian sumber barang di bawah "Informasi Awal".
                function renderSources(sources) {
                    const $box = $('#detailSumber');

                    if (!sources || sources.length < 2) {
                        $box.addClass('d-none').empty();
                        return;
                    }

                    const total = sources.reduce((sum, s) => sum + Number(s.qty || 0), 0);

                    const rows = sources.map(function(src) {
                        const color = sourceColor[src.type] || 'secondary';

                        return `<tr>
                            <td class="text-muted small">${src.no}</td>
                            <td>
                                <span class="badge bg-${color}-subtle text-${color}">${escapeHtml(src.type_label)}</span>
                                ${src.detail ? `<div class="small text-muted mt-1">${escapeHtml(src.detail)}</div>` : ''}
                            </td>
                            <td class="text-end fw-semibold">${src.qty} PCS</td>
                        </tr>`;
                    }).join('');

                    $box.html(`
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Rincian Sumber Barang
                            <span class="badge bg-warning-subtle text-warning ms-1">${sources.length} sumber</span></h6>
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th style="width:32px;">No</th><th>Diambil dari</th><th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                            <tfoot>
                                <tr class="fw-bold"><td></td><td>Total</td><td class="text-end">${total} PCS</td></tr>
                            </tfoot>
                        </table>`).removeClass('d-none');
                }

                function renderTimeline(timeline) {
                    let html = '';

                    timeline.forEach(function(item) {
                        const style = eventStyle[item.event_type] || {
                            color: 'secondary',
                            icon: 'bi-dot'
                        };

                        html += `
                            <div class="timeline-item">
                                <div class="timeline-dot bg-${style.color}-subtle text-${style.color}">
                                    <i class="bi ${style.icon}"></i>
                                </div>
                                <div class="timeline-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="fw-semibold small text-${style.color}">${item.label}</span>
                                        <span class="text-muted" style="font-size:.72rem;">${formatDateTime(item.created_at)}</span>
                                    </div>
                                    ${metaDescription(item)}
                                    ${item.notes ? `<div class="small text-muted mt-1">${item.notes}</div>` : ''}
                                    <div class="text-muted mt-1" style="font-size:.72rem;">Oleh ${item.performed_by}</div>
                                </div>
                            </div>
                        `;
                    });

                    $('#detailTimeline').html(html);
                }

                function loadHistoryDetail(id) {
                    $.get(`{{ url('stagings-out-history') }}/${id}/detail`, function(res) {

                        // Tampilkan panel detail & sesuaikan lebar tabel
                        $('#historyTableCol').removeClass('col-12').addClass('col-lg-8');
                        $('#historyDetailCol').removeClass('d-none');

                        if (historyTable) {
                            historyTable.columns.adjust();
                        }

                        $('#detailEmptyState').addClass('d-none');
                        $('#detailContent').removeClass('d-none');
                        $('#closeDetailBtn').removeClass('d-none');

                        $('#detailExportBtn')
                            .attr('href', `{{ url('stagings-out-history') }}/${id}/export-detail`)
                            .removeClass('d-none');

                        $('#detailSubtitle').text(`${res.item_code}`);
                        $('#detailItemName').text(res.item_name ?? '-');
                        $('#detailItemSo').text(res.so_number ?? '-');

                        $('#detailStatusBadge')
                            .attr('class', `badge bg-${res.status.color}-subtle text-${res.status.color}`)
                            .text(res.status.label);

                        const info = res.informasi_awal;

                        // Sumber lebih dari satu: ringkasan satu kalimat di sini, rinciannya
                        // ada di tabel "Rincian Sumber Barang" di bawahnya.
                        const sources = info.sumber_list || [];
                        const sumberRow = sources.length > 1 ?
                            `<tr>
                                <td class="text-muted small align-top">Sumber Barang</td>
                                <td class="text-end small">${escapeHtml(info.sumber_ringkas || info.sumber_barang)}</td>
                            </tr>` :
                            `<tr><td class="text-muted small">Sumber Barang</td><td class="text-end small">${escapeHtml(info.sumber_barang)}</td></tr>`;

                        $('#detailInfoAwal').html(`
                            <tr><td class="text-muted small">Customer</td><td class="text-end small">${info.customer}</td></tr>
                            <tr><td class="text-muted small">Line Item</td><td class="text-end small">${info.line_item}</td></tr>
                            ${sumberRow}
                            <tr><td class="text-muted small">Tgl Instruksi Kirim</td><td class="text-end small">${formatDateOnly(info.delivery_instruction_date)}</td></tr>
                            <tr><td class="text-muted small">Initial Qty</td><td class="text-end small">${info.initial_qty}</td></tr>
                            <tr><td class="text-muted small">Qty Saat Ini</td><td class="text-end small">${info.current_qty}</td></tr>
                            <tr><td class="text-muted small">Created At</td><td class="text-end small">${formatDateTime(info.created_at)}</td></tr>
                        `);

                        renderSources(sources);

                        const pengiriman = res.status_pengiriman;
                        $('#detailPengiriman').html(`
                            <tr><td class="text-muted small">Tgl Picking</td><td class="text-end small">${formatDateOnly(pengiriman.picking_date)}</td></tr>
                            <tr><td class="text-muted small">DO Number</td><td class="text-end small">${pengiriman.do_number}</td></tr>
                            <tr><td class="text-muted small">Tgl Kirim</td><td class="text-end small">${formatDateOnly(pengiriman.delivery_date)}</td></tr>
                        `);

                        renderTimeline(res.timeline);
                    });
                }

                $(document).on('click', '.btnViewHistory', function() {
                    loadHistoryDetail($(this).data('id'));
                });

                $('#closeDetailBtn').on('click', function() {
                    $('#detailContent').addClass('d-none');
                    $('#detailEmptyState').removeClass('d-none');
                    $(this).addClass('d-none');
                    $('#detailExportBtn').addClass('d-none');
                    $('#detailSubtitle').text('Pilih salah satu data untuk melihat detail');

                    // Sembunyikan panel detail & kembalikan tabel ke full width
                    $('#historyDetailCol').addClass('d-none');
                    $('#historyTableCol').removeClass('col-lg-8').addClass('col-12');

                    if (historyTable) {
                        historyTable.columns.adjust();
                    }
                });

            });
        </script>
        <script>
            // ============ TAMPILAN FILTER (penanda aktif, hapus pencarian, preset tanggal) ============
            (function() {
                const $bar = $('#filterBar');
                const chipFilters = ['#historyStatus', '#historyEvent'];

                function filled($el) {
                    return String($el.val() || '').trim() !== '';
                }

                function refreshFilterState() {
                    let count = 0;

                    const searchFilled = filled($('#historySearch'));
                    $('.fx-search').toggleClass('has-value', searchFilled);
                    if (searchFilled) count++;

                    chipFilters.forEach(function(sel) {
                        const on = filled($(sel));
                        $(sel).closest('.fx-chip').toggleClass('is-active', on);
                        if (on) count++;
                    });

                    const dateOn = filled($('#historyDateRange'));
                    $('#historyDateRange').closest('.fx-chip').toggleClass('is-active', dateOn);
                    if (dateOn) count++;

                    $bar.toggleClass('has-active', count > 0);
                    $('#filterCount').text(count > 0 ? count + ' filter aktif' : '');
                }

                $('#historySearch, ' + chipFilters.join(', ')).on('input change', refreshFilterState);

                // Handler lama mengisi nilai lewat .val() (tanpa event), jadi cek ulang sesudahnya.
                $('#historyDateRangeApply, #resetHistoryFilter').on('click', function() {
                    setTimeout(refreshFilterState, 0);
                });

                $('#customSearchClear').on('click', function() {
                    $('#historySearch').val('').trigger('input').trigger('focus');
                });

                function ymd(d) {
                    const p = (n) => String(n).padStart(2, '0');
                    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
                }

                $('.fx-preset').on('click', function() {
                    const now = new Date();
                    let from = new Date(now);
                    const preset = $(this).data('preset');

                    if (preset === '7d') {
                        from.setDate(now.getDate() - 6);
                    } else if (preset === 'month') {
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                    }

                    $('#historyStartDate').val(ymd(from));
                    $('#historyEndDate').val(ymd(now));
                    $('#historyDateRangeApply').trigger('click');
                });

                $('#historyDateRangeClear').on('click', function() {
                    $('#historyStartDate').val('');
                    $('#historyEndDate').val('');
                    $('#historyDateRangeApply').trigger('click');
                });

                refreshFilterState();
            })();
        </script>
    @endpush

@endsection