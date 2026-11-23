@extends('master')
@section('title', 'Bill Packing History')
@section('content')

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Bill Packing History</h4>
            <small class="text-muted">Semua bill packing per No. SO, termasuk SO yang barangnya sudah dikirim</small>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Total Bill -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-bill bg-primary-subtle text-primary me-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Bill</small>
                        <h4 class="fw-bold mb-1">{{ number_format($totalBill, 0, ',', '.') }}</h4>
                        @if ($createdToday > 0)
                            <small class="text-primary"><i class="bi bi-arrow-up-short"></i> +{{ $createdToday }} hari ini</small>
                        @else
                            <small class="text-muted">Seluruh bill</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Nilai -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-bill bg-success-subtle text-success me-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Nilai Packing</small>
                        <h4 class="fw-bold mb-1">Rp {{ number_format($totalNilai, 0, ',', '.') }}</h4>
                        <small class="text-muted">Jumlah semua bill</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Di Staging Out -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-bill bg-warning-subtle text-warning me-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Di Staging Out</small>
                        <h4 class="fw-bold mb-1">{{ number_format($stagingCount, 0, ',', '.') }}</h4>
                        <small class="text-warning">SO belum dikirim</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terkirim -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-bill bg-info-subtle text-info me-3">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Terkirim</small>
                        <h4 class="fw-bold mb-1">{{ number_format($deliveredCount, 0, ',', '.') }}</h4>
                        <small class="text-info">SO sudah dikirim</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ================= KIRI: TABEL BILL ================= --}}
        <div class="col-12" id="billTableCol">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="float-start">
                        <h5 class="mb-0 fw-bold">Daftar Bill</h5>
                        <small class="text-muted">Klik <i class="bi bi-eye"></i> untuk melihat rincian, atau
                            <i class="bi bi-pencil-square"></i> untuk mengubah bill</small>
                    </div>
                    <div class="float-end mt-3">
                        <span class="text-muted small me-2" id="recordCountLabel"></span>
                        <a href="{{ route('packing-bills.export') }}" class="btn-sm btn border-secondary bg-white border"
                            id="exportBillBtn" title="Export laporan sesuai filter">
                            <i class="bi bi-file-earmark-excel text-success"></i>
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
                                    <input type="text" id="billSearch" autocomplete="off"
                                        placeholder="Cari No. SO atau customer..." aria-label="Cari">
                                    <button type="button" class="fx-search-clear" id="billSearchClear"
                                        aria-label="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="fx-actions">
                                    <span class="fx-count" id="filterCount" aria-live="polite"></span>
                                    <button type="button" class="fx-reset" id="resetBillFilter"
                                        title="Kembalikan semua filter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Reset</span>
                                    </button>
                                </div>

                            </div>

                            <div class="fx-chips">

                                <!-- Status SO -->
                                <div class="fx-chip">
                                    <i class="bi bi-flag"></i>
                                    <label for="billStatus">Status SO</label>
                                    <select id="billStatus">
                                        <option value="">Semua</option>
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tanggal Bill -->
                                <div class="fx-chip fx-chip-date">
                                    <i class="bi bi-calendar-event"></i>
                                    <label for="billDateRange">Tanggal Bill</label>

                                    <div class="date-range-wrapper">
                                        <input type="text" id="billDateRange" placeholder="Semua tanggal"
                                            title="Rentang tanggal" aria-label="Rentang tanggal" readonly
                                            autocomplete="off">

                                        <div class="date-range-panel" id="billDateRangePanel">
                                            <div class="fx-presets">
                                                <button type="button" class="fx-preset" data-preset="today">Hari ini</button>
                                                <button type="button" class="fx-preset" data-preset="7d">7 hari terakhir</button>
                                                <button type="button" class="fx-preset" data-preset="month">Bulan ini</button>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Dari</label>
                                                <input type="date" class="form-control form-control-sm" id="billStartDate">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Sampai</label>
                                                <input type="date" class="form-control form-control-sm" id="billEndDate">
                                            </div>
                                            <div class="d-flex gap-2 mt-3">
                                                <button type="button" class="btn btn-sm btn-light flex-shrink-0"
                                                    id="billDateRangeClear">Hapus</button>
                                                <button type="button" class="btn btn-sm btn-primary w-100"
                                                    id="billDateRangeApply">Terapkan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <table class="table table-bordered" id="billTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. SO</th>
                                    <th>Customer</th>
                                    <th>Tgl Bill</th>
                                    <th>Koli</th>
                                    <th>Material</th>
                                    <th>Total</th>
                                    <th>Status SO</th>
                                    <th>Terakhir Diubah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="mb-3 ms-2 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                            id="billTableFooter">
                            <div class="d-flex align-items-center gap-2" id="billLengthWrapper">
                                <span>Tampilkan</span>
                                <select id="billLength" class="form-select form-select-sm" style="width:80px">
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

        {{-- ================= KANAN: DETAIL BILL ================= --}}
        <div class="col-lg-4 d-none" id="billDetailCol">
            <div class="card border-0 shadow-sm" style="position:sticky; top:1rem; max-height:calc(100vh - 2rem);">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4 flex-shrink-0">
                    <div>
                        <h5 class="mb-0 fw-bold">Detail Bill</h5>
                        <small class="text-muted" id="detailSubtitle">-</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" class="btn-sm btn btn-warning d-none" id="detailEditBtn" title="Edit bill ini">
                            <i class="bi bi-pencil-square"></i>
                            <span class="d-none d-md-inline ms-1">Edit</span>
                        </a>
                        <a href="#" target="_blank" class="btn-sm btn border-secondary bg-white border d-none"
                            id="detailPrintBtn" title="Cetak / PDF">
                            <i class="bi bi-printer text-success"></i>
                            <span class="d-none d-md-inline ms-1">Cetak</span>
                        </a>
                        <button type="button" class="btn-close" id="closeDetailBtn"></button>
                    </div>
                </div>

                <div class="card-body" style="overflow-y:auto; min-height:0;">
                    <div id="detailLoading" class="text-center text-muted py-5 d-none">
                        <div class="spinner-border spinner-border-sm"></div>
                        <p class="mt-2 mb-0 small">Memuat bill...</p>
                    </div>

                    <div id="detailContent" class="d-none">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold mb-0" id="detailSo">-</h6>
                                <small class="text-muted" id="detailCustomer">-</small>
                            </div>
                            <span id="detailStatusBadge" class="badge">-</span>
                        </div>

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Informasi Bill</h6>
                        <table class="table table-sm table-borderless mb-3">
                            <tbody id="detailInfo"></tbody>
                        </table>

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Rincian Material</h6>
                        <div id="detailKolis"></div>

                        <div class="bill-detail-total">
                            <span>Total Biaya Packing</span>
                            <strong id="detailTotal">Rp 0</strong>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('script')
        <style>
            .icon-box-bill {
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

            /* ---------- Panel detail bill ---------- */
            .bill-koli {
                background: #f8f9fb;
                border: 1px solid #eceef2;
                border-radius: .75rem;
                padding: .7rem .85rem;
                margin-bottom: .75rem;
            }

            .bill-koli.shared {
                background: #fff8ec;
                border-color: #f6e3bd;
            }

            .bill-koli-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: .5rem;
                margin-bottom: .4rem;
                font-size: .85rem;
            }

            .bill-koli-sub {
                font-weight: 600;
                color: #566a7f;
                font-size: .8rem;
            }

            .bill-chip {
                display: inline-block;
                background: #fff;
                border: 1px solid #dfe3e9;
                border-radius: 999px;
                padding: .05rem .55rem;
                font-size: .72rem;
                margin: 0 .25rem .25rem 0;
            }

            .bill-line {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
                padding: .35rem 0;
                border-top: 1px dashed #e3e6ec;
                font-size: .82rem;
            }

            .bill-line:first-of-type {
                border-top: 0;
            }

            .bill-line-sub {
                color: #8a93a3;
                font-size: .74rem;
            }

            .bill-detail-total {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: .5rem;
                padding: .75rem .9rem;
                border-radius: .75rem;
                background: #eef3ff;
                color: #2f4ea2;
                font-size: .9rem;
            }

            .bill-detail-total strong {
                font-size: 1.05rem;
            }
        </style>

        <script>
            $(function() {
                const URL_DATA = @json(route('packing-bills.data'));
                const URL_DETAIL = @json(route('packing-bills.detail', ':id'));

                const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
                const rupiah = n => 'Rp ' + (Number(n) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
                const fmtQty = n => String(Number(n)).replace('.', ',');
                const fmtDate = v => {
                    const m = String(v || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
                    return m ? `${m[3]}-${m[2]}-${m[1]}` : (v || '-');
                };

                let appliedStart = '';
                let appliedEnd = '';

                // Link Export selalu membawa filter yang sedang aktif di tabel.
                function updateBillExportUrl() {
                    const url = new URL(@json(route('packing-bills.export')));
                    const search = $('#billSearch').val().trim();
                    const status = $('#billStatus').val();

                    if (appliedStart) url.searchParams.append('start_date', appliedStart);
                    if (appliedEnd) url.searchParams.append('end_date', appliedEnd);
                    if (status) url.searchParams.append('status', status);
                    if (search) url.searchParams.append('search', search);

                    $('#exportBillBtn').attr('href', url.toString());
                }

                const billTable = $('#billTable').DataTable({
                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],
                    scrollX: true,
                    autoWidth: false,

                    ajax: {
                        url: URL_DATA,
                        data: function(d) {
                            d.status = $('#billStatus').val();
                            d.start_date = appliedStart;
                            d.end_date = appliedEnd;
                        }
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'so_number',
                            name: 'so_number'
                        },
                        {
                            data: 'customer',
                            name: 'customer'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            searchable: false
                        },
                        {
                            data: 'kolis_count',
                            name: 'kolis_count',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'lines_count',
                            name: 'lines_count',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'total',
                            name: 'total',
                            searchable: false
                        },
                        {
                            data: 'status',
                            name: 'status',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'diubah',
                            name: 'diubah',
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            searchable: false,
                            orderable: false
                        }
                    ],

                    columnDefs: [{
                            targets: [2],
                            className: 'text-wrap',
                            width: '200px'
                        },
                        {
                            targets: [3, 4, 5, 7, 8, 9],
                            className: 'text-center'
                        },
                        {
                            targets: [6],
                            className: 'text-end'
                        }
                    ],

                    initComplete: moveTableElements,
                    drawCallback: function() {
                        moveTableElements();
                        $('#recordCountLabel').text(
                            billTable.page.info().recordsTotal.toLocaleString('id-ID') + ' Records');
                    }
                });

                function moveTableElements() {
                    const $info = $('#billTable_info');
                    if ($info.length && !$('#billLengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#billLengthWrapper');
                    }

                    const $paginate = $('#billTable_paginate');
                    if ($paginate.length && !$('#billTableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#billTableFooter');
                    }
                }

                // ============ FILTER ============
                let searchTimer;
                $('#billSearch').on('input', function() {
                    const value = this.value;
                    updateBillExportUrl();
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => billTable.search(value).draw(), 250);
                });

                $('#billLength').on('change', function() {
                    billTable.page.len($(this).val()).draw();
                });

                $('#billStatus').on('change', function() {
                    updateBillExportUrl();
                    billTable.ajax.reload();
                });

                $('#resetBillFilter').on('click', function() {
                    $('#billSearch').val('');
                    $('#billStatus').val('');
                    $('#billDateRange, #billStartDate, #billEndDate').val('');
                    appliedStart = '';
                    appliedEnd = '';
                    updateBillExportUrl();
                    billTable.search('').ajax.reload();
                });

                // Panel rentang tanggal
                const $dateInput = $('#billDateRange');
                const $panel = $('#billDateRangePanel');
                const $startInput = $('#billStartDate');
                const $endInput = $('#billEndDate');

                $dateInput.on('click', function(e) {
                    e.stopPropagation();
                    $panel.toggleClass('show');
                });
                $panel.on('click', e => e.stopPropagation());
                $(document).on('click', function() {
                    if ($panel.hasClass('show')) {
                        $startInput.val(appliedStart);
                        $endInput.val(appliedEnd);
                    }
                    $panel.removeClass('show');
                });

                $('#billDateRangeApply').on('click', function() {
                    const start = $startInput.val();
                    const end = $endInput.val();

                    if (start && end && start > end) {
                        alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                        return;
                    }

                    appliedStart = start;
                    appliedEnd = end;

                    if (start && end) $dateInput.val(fmtDate(start) + ' s/d ' + fmtDate(end));
                    else if (start) $dateInput.val(fmtDate(start) + ' s/d ...');
                    else if (end) $dateInput.val('... s/d ' + fmtDate(end));
                    else $dateInput.val('');

                    $panel.removeClass('show');
                    updateBillExportUrl();
                    billTable.ajax.reload();
                });

                updateBillExportUrl();

                // ============ TAMPILAN FILTER (penanda aktif, hapus pencarian, preset tanggal) ============
                function filled($el) {
                    return String($el.val() || '').trim() !== '';
                }

                function refreshFilterState() {
                    let count = 0;

                    const searchFilled = filled($('#billSearch'));
                    $('.fx-search').toggleClass('has-value', searchFilled);
                    if (searchFilled) count++;

                    const statusOn = filled($('#billStatus'));
                    $('#billStatus').closest('.fx-chip').toggleClass('is-active', statusOn);
                    if (statusOn) count++;

                    const dateOn = filled($('#billDateRange'));
                    $('#billDateRange').closest('.fx-chip').toggleClass('is-active', dateOn);
                    if (dateOn) count++;

                    $('#filterBar').toggleClass('has-active', count > 0);
                    $('#filterCount').text(count > 0 ? count + ' filter aktif' : '');
                }

                $('#billSearch, #billStatus').on('input change', refreshFilterState);

                // Handler lain mengisi nilai lewat .val() (tanpa event), jadi cek ulang sesudahnya.
                $('#billDateRangeApply, #resetBillFilter').on('click', function() {
                    setTimeout(refreshFilterState, 0);
                });

                $('#billSearchClear').on('click', function() {
                    $('#billSearch').val('').trigger('input').trigger('focus');
                });

                // Preset rentang tanggal: isi Dari/Sampai lalu pakai tombol Terapkan.
                function ymd(d) {
                    const p = n => String(n).padStart(2, '0');
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

                    $startInput.val(ymd(from));
                    $endInput.val(ymd(now));
                    $('#billDateRangeApply').trigger('click');
                });

                $('#billDateRangeClear').on('click', function() {
                    $startInput.val('');
                    $endInput.val('');
                    $('#billDateRangeApply').trigger('click');
                });

                refreshFilterState();

                // ============ DETAIL BILL ============
                function linesHtml(lines) {
                    if (!lines || !lines.length) {
                        return '<div class="text-muted small">Belum ada material.</div>';
                    }

                    return lines.map(l => `
                        <div class="bill-line">
                            <div>
                                <div>${esc(l.nama_material)}</div>
                                <div class="bill-line-sub">${fmtQty(l.qty)} ${esc(l.satuan || '')} &times; ${rupiah(l.harga)}</div>
                            </div>
                            <div class="fw-semibold text-nowrap">${rupiah(l.subtotal)}</div>
                        </div>`).join('');
                }

                function koliHtml(title, subtotal, items, lines, shared) {
                    const chips = (items || []).map(i => `<span class="bill-chip">${esc(i)}</span>`).join('');
                    return `
                        <div class="bill-koli ${shared ? 'shared' : ''}">
                            <div class="bill-koli-head">
                                <strong><i class="bi ${shared ? 'bi-layers' : 'bi-box'} me-1"></i>${esc(title)}</strong>
                                <span class="bill-koli-sub">${rupiah(subtotal)}</span>
                            </div>
                            ${chips ? `<div class="mb-2"><span class="text-muted small me-1">Isi koli:</span>${chips}</div>` : ''}
                            ${linesHtml(lines)}
                        </div>`;
                }

                function renderDetail(res) {
                    $('#detailSubtitle').text(res.so_number);
                    $('#detailSo').text(res.so_number);
                    $('#detailCustomer').text(res.customer);
                    $('#detailStatusBadge')
                        .attr('class', `badge bg-${res.status.color}-subtle text-${res.status.color}`)
                        .text(res.status.label);

                    const itemsText = res.items.length ? res.items.map(esc).join(', ') : '-';
                    const diubah = res.diubah_oleh ?
                        `${esc(res.diubah_pada)}<div class="text-muted" style="font-size:.72rem;">oleh ${esc(res.diubah_oleh)}</div>` :
                        '<span class="text-muted">Belum pernah diubah</span>';

                    $('#detailInfo').html(`
                        <tr><td class="text-muted small">Tgl Bill</td><td class="text-end small">${esc(res.tanggal)}</td></tr>
                        <tr><td class="text-muted small">Item di SO</td><td class="text-end small">${itemsText}</td></tr>
                        <tr><td class="text-muted small">Jumlah Koli</td><td class="text-end small">${res.kolis.length}</td></tr>
                        <tr><td class="text-muted small">Dibuat Oleh</td><td class="text-end small">${esc(res.dibuat_oleh)}</td></tr>
                        <tr><td class="text-muted small align-top">Terakhir Diubah</td><td class="text-end small">${diubah}</td></tr>
                    `);

                    let html = '';
                    res.kolis.forEach(k => {
                        const title = res.multi ? `Koli ${k.no_koli}` : 'Material yang dipakai';
                        html += koliHtml(title, k.subtotal, k.items, k.lines, false);
                    });
                    if (res.shared.lines.length) {
                        html += koliHtml('Material Bersama', res.shared.subtotal, [], res.shared.lines, true);
                    }
                    $('#detailKolis').html(html || '<div class="text-muted small">Belum ada material.</div>');

                    $('#detailTotal').text(rupiah(res.total));
                    $('#detailEditBtn').attr('href', res.urls.edit).removeClass('d-none');
                    $('#detailPrintBtn').attr('href', res.urls.print).removeClass('d-none');
                }

                function showDetailPanel() {
                    $('#billTableCol').removeClass('col-12').addClass('col-lg-8');
                    $('#billDetailCol').removeClass('d-none');
                    billTable.columns.adjust();
                }

                function hideDetailPanel() {
                    $('#billDetailCol').addClass('d-none');
                    $('#billTableCol').removeClass('col-lg-8').addClass('col-12');
                    billTable.columns.adjust();
                }

                $(document).on('click', '.btnViewBill', function() {
                    const id = $(this).data('id');

                    showDetailPanel();
                    $('#detailContent').addClass('d-none');
                    $('#detailLoading').removeClass('d-none').html(
                        '<div class="spinner-border spinner-border-sm"></div><p class="mt-2 mb-0 small">Memuat bill...</p>');
                    $('#detailSubtitle').text('Memuat...');
                    $('#detailEditBtn, #detailPrintBtn').addClass('d-none');

                    $.get(URL_DETAIL.replace(':id', id))
                        .done(function(res) {
                            renderDetail(res);
                            $('#detailLoading').addClass('d-none');
                            $('#detailContent').removeClass('d-none');
                        })
                        .fail(function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText || '';
                            $('#detailLoading').html(
                                '<div class="text-danger small">Gagal memuat bill (HTTP ' + xhr.status + ').' +
                                (msg ? '<br>' + esc(msg) : '') + '</div>');
                        });
                });

                $('#closeDetailBtn').on('click', hideDetailPanel);
            });
        </script>
    @endpush

@endsection