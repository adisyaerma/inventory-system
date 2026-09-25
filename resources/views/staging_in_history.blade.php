@extends('master')
@section('title', 'Staging In History')
@section('content')

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Staging In History</h4>
            <small class="text-muted">Monitor dan lacak seluruh perjalanan data staging in</small>
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
                        <small class="text-success">Masih di staging in</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Dipindah -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-info-subtle text-info me-3">
                        <i class="bi bi-arrow-left-right fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Sudah Dipindah</small>
                        <h4 class="fw-bold mb-1">{{ number_format($movedCount, 0, ',', '.') }}</h4>
                        <small class="text-info">Ke Stock / Staging Out</small>
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
                        <h5 class="mb-0 fw-bold">Staging In History</h5>
                        <small class="text-muted">Daftar history staging in</small>
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
                        <a href="{{ route('stagings-in-history.export') }}" class="btn-sm btn border-secondary bg-white border"
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

                        <div class="filter-toolbar-card">

                            <div class="filter-toolbar-eyebrow">
                                <div class="filter-toolbar-eyebrow-label">
                                    <i class="bi bi-sliders"></i>
                                    <span>Filter Data</span>
                                </div>

                                <button type="button" class="btn-sm btn border-secondary bg-white border"
                                    id="resetHistoryFilter" title="Reset Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 21 21">
                                        <path d="M0 0h21v21H0z" fill="none" />
                                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                                            <path d="M7.5 6.5h-4v-4" />
                                        </g>
                                    </svg>
                                </button>
                            </div>

                            <div class="filter-toolbar filter-toolbar-history">

                                <!-- Search -->
                                <div class="filter-group filter-group-search">
                                    <label class="filter-label" for="historySearch">Cari</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" id="historySearch" class="form-control border-start-0"
                                            placeholder="Cari PO Number, item, supplier...">
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="filter-group">
                                    <label class="filter-label" for="historyStatus">Status</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-flag text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="historyStatus">
                                            <option value="">Semua Status</option>
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Event Terakhir -->
                                <div class="filter-group">
                                    <label class="filter-label" for="historyEvent">Event Terakhir</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-activity text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="historyEvent">
                                            <option value="">Semua Event</option>
                                            @foreach ($eventOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Periode -->
                                <div class="filter-group">
                                    <label class="filter-label" for="historyDateRange">Periode</label>
                                    <div class="date-range-wrapper">
                                        <div class="input-group input-group-sm shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-calendar-range text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="historyDateRange"
                                                placeholder="Pilih periode" readonly autocomplete="off">
                                        </div>

                                        <div class="date-range-panel" id="historyDateRangePanel">
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Dari</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="historyStartDate">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Sampai</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="historyEndDate">
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-2">
                                                <button type="button" class="btn btn-sm btn-primary w-100"
                                                    id="historyDateRangeApply">
                                                    Terapkan
                                                </button>
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
                                        <input type="checkbox" class="form-check-input" id="selectAllHistoryRestore"
                                            title="Pilih semua baris berstatus Dihapus di halaman ini">
                                    </th>
                                    <th>No</th>
                                    <th>Item &amp; PO</th>
                                    <th>Supplier</th>
                                    <th>Tgl Kedatangan</th>
                                    <th>Qty Awal</th>
                                    <th>Qty Saat Ini</th>
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
                                <small class="text-muted" id="detailItemPo">-</small>
                            </div>
                            <span id="detailStatusBadge" class="badge">-</span>
                        </div>

                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Informasi Awal</h6>
                        <table class="table table-sm table-borderless mb-4">
                            <tbody id="detailInfoAwal">
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
        {{-- Halaman ini sebelumnya read-only & tidak pernah pakai SweetAlert2,
             jadi library-nya belum tentu ke-load dari master layout. Muat
             sendiri di sini KALAU BELUM ADA (dicek typeof Swal), pakai
             document.write supaya tetap synchronous -- baris <script> lain
             di bawah (yang manggil Swal) dijamin jalan SETELAH ini selesai
             dimuat, tanpa perlu nunggu event/callback tambahan. --}}
        <script>
            if (typeof Swal === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
            }
        </script>
        <style>
            .icon-box-history {
                border-radius: 14px;
                width: 52px;
                height: 52px;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
            }

            .filter-toolbar-card {
                background: #f8f9fb;
                border: 1px solid #eceef2;
                border-radius: 1rem;
                padding: 1rem 1.1rem;
                margin-bottom: 1rem;
            }

            .filter-toolbar-eyebrow {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                margin-bottom: .75rem;
                color: #6c757d;
                font-size: .8rem;
                font-weight: 600;
                letter-spacing: .02em;
                text-transform: uppercase;
            }

            .filter-toolbar-eyebrow .filter-toolbar-eyebrow-label {
                display: flex;
                align-items: center;
                gap: .5rem;
            }

            .filter-toolbar-eyebrow i {
                color: var(--bs-primary);
            }

            .filter-toolbar {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: .75rem 1rem;
                align-items: end;
            }

            .filter-label {
                display: block;
                font-size: .72rem;
                font-weight: 600;
                color: #8a93a3;
                text-transform: uppercase;
                letter-spacing: .02em;
                margin-bottom: .3rem;
            }

            .filter-toolbar .input-group {
                border-radius: .5rem;
                overflow: hidden;
                transition: box-shadow .15s ease;
            }

            .filter-toolbar .input-group:focus-within {
                box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
            }

            .filter-toolbar .input-group-text,
            .filter-toolbar .form-control,
            .filter-toolbar .form-select {
                border-color: #dfe3e9;
            }

            .filter-toolbar .form-select,
            .filter-toolbar .form-control {
                font-size: .85rem;
            }

            .date-range-wrapper {
                position: relative;
            }

            .date-range-panel {
                display: none;
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                z-index: 1050;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: .75rem;
                box-shadow: 0 .5rem 1.5rem rgba(20, 20, 43, .12);
                padding: 14px;
                width: 240px;
                opacity: 0;
                transform: translateY(-4px);
                transition: opacity .12s ease, transform .12s ease;
            }

            .date-range-panel.show {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            @media (max-width: 1199.98px) {
                .filter-toolbar-history {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 575.98px) {
                .filter-toolbar-history {
                    grid-template-columns: 1fr;
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
            // Backend mengirim tanggal dalam format ISO UTC (mis. 2026-08-20T17:00:00.000000Z).
            // Fungsi ini mengonversinya ke tanggal lokal (WIB) dan menampilkannya
            // dalam format yang rapi, bukan string ISO mentah.
            function formatDateOnly(value) {
                if (!value) return '-';
                if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T/.test(value)) return value;
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                return d.toLocaleDateString('sv-SE'); // -> YYYY-MM-DD (mengikuti zona waktu lokal browser)
            }

            function formatDateTime(value) {
                if (!value) return '-';
                if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T/.test(value)) return value;
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                const datePart = d.toLocaleDateString('sv-SE');
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
                        url: "{{ route('stagings-in-history.data') }}",
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
                            data: 'item_po',
                            name: 'item_po',
                            orderable: false
                        },
                        {
                            data: 'supplier_origin',
                            name: 'supplier_origin'
                        },
                        {
                            data: 'arrival_date',
                            name: 'arrival_date',
                            render: function(data) {
                                return formatDateOnly(data);
                            }
                        },
                        {
                            data: 'initial_qty',
                            name: 'initial_qty'
                        },
                        {
                            data: 'current_qty',
                            name: 'current_qty',
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
                            targets: [5, 6, 7, 9],
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


                // ============ BULK RESTORE (data berstatus "Dihapus") ============
                function updateBulkRestoreButton() {
                    const count = $('.historyRestoreCheckbox:checked').length;
                    $('#bulkRestoreCount').text(count);
                    $('#bulkRestoreBtn').toggleClass('d-none', count === 0);
                }

                $(document).on('change', '.historyRestoreCheckbox', function() {
                    updateBulkRestoreButton();

                    const $selectable = $('.historyRestoreCheckbox');
                    const allChecked = $selectable.length > 0 &&
                        $selectable.length === $selectable.filter(':checked').length;
                    $('#selectAllHistoryRestore').prop('checked', allChecked);
                });

                $('#selectAllHistoryRestore').on('change', function() {
                    $('.historyRestoreCheckbox').prop('checked', $(this).is(':checked'));
                    updateBulkRestoreButton();
                });

                // Baris & centangan berganti tiap kali tabel di-redraw (ganti
                // halaman/filter/search) -- reset seleksi supaya tidak nyangkut
                // ID dari halaman sebelumnya yang sudah tidak terlihat.
                historyTable.on('draw', function() {
                    $('#selectAllHistoryRestore').prop('checked', false);
                    updateBulkRestoreButton();
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

                $('#bulkRestoreBtn').on('click', function() {
                    const ids = $('.historyRestoreCheckbox:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (ids.length === 0) {
                        return;
                    }

                    Swal.fire({
                        icon: 'question',
                        title: 'Pulihkan Data?',
                        html: `Yakin mau memulihkan <b>${ids.length}</b> data terpilih kembali ke Staging In?`,
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
                            url: "{{ route('stagings-in-history.bulk-restore') }}",
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
                                        title: res.message,
                                        showConfirmButton: false,
                                        timer: 2000
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
                        $dateInput.val(start + ' s/d ' + end);
                    } else if (start) {
                        $dateInput.val(start + ' s/d ...');
                    } else {
                        $dateInput.val('');
                    }

                    $panel.removeClass('show');
                    historyTable.ajax.reload();
                    updateHistoryExportUrl();
                });

                function updateHistoryExportUrl() {
                    let url = new URL("{{ route('stagings-in-history.export') }}");

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
                    moved_to_stock: {
                        color: 'success',
                        icon: 'bi-arrow-repeat'
                    },
                    moved_to_staging_out: {
                        color: 'info',
                        icon: 'bi-box-arrow-up-right'
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
                };

                function metaDescription(item) {
                    const meta = item.meta || {};

                    if (item.event_type === 'moved_to_stock') {
                        return `
                            <div class="small mt-2">
                                <div class="d-flex justify-content-between"><span class="text-muted">Qty Sebelum</span><span>${item.qty_before ?? '-'} PCS</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Qty Dipindahkan</span><span class="text-danger">-${item.qty_change ?? '-'} PCS</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Qty Setelah</span><span>${item.qty_after ?? '-'} PCS</span></div>
                                ${meta.transaction_number ? `<div class="mt-1">Transaction: <span class="text-primary">${meta.transaction_number}</span></div>` : ''}
                                ${meta.location ? `<div>Lokasi: ${meta.location}</div>` : ''}
                            </div>`;
                    }

                    if (item.event_type === 'moved_to_staging_out') {
                        return `
                            <div class="small mt-2">
                                <div class="d-flex justify-content-between"><span class="text-muted">Qty Dipindahkan</span><span class="text-danger">-${item.qty_change ?? '-'} PCS</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Qty Setelah</span><span>${item.qty_after ?? '-'} PCS</span></div>
                                ${meta.so_number ? `<div class="mt-1">SO Number: <span class="text-primary">${meta.so_number}</span></div>` : ''}
                                ${meta.customer ? `<div>Customer: ${meta.customer}</div>` : ''}
                            </div>`;
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
                        return `<div class="small mt-2">Qty: ${item.qty_after ?? '-'} PCS</div>`;
                    }

                    return '';
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
                    $.get(`{{ url('stagings-in-history') }}/${id}/detail`, function(res) {

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
                            .attr('href', `{{ url('stagings-in-history') }}/${id}/export-detail`)
                            .removeClass('d-none');

                        $('#detailSubtitle').text(`${res.item_code}`);
                        $('#detailItemName').text(res.item_name ?? '-');
                        $('#detailItemPo').text(res.po_number ?? '-');

                        $('#detailStatusBadge')
                            .attr('class', `badge bg-${res.status.color}-subtle text-${res.status.color}`)
                            .text(res.status.label);

                        const info = res.informasi_awal;
                        $('#detailInfoAwal').html(`
                            <tr><td class="text-muted small">Supplier</td><td class="text-end small">${info.supplier}</td></tr>
                            <tr><td class="text-muted small">Lokasi</td><td class="text-end small">${info.location ?? '-'}</td></tr>
                            <tr><td class="text-muted small">Arrival Date</td><td class="text-end small">${formatDateOnly(info.arrival_date)}</td></tr>
                            <tr><td class="text-muted small">Incoterms</td><td class="text-end small">${info.incoterms}</td></tr>
                            <tr><td class="text-muted small">Initial Qty</td><td class="text-end small">${info.initial_qty}</td></tr>
                            <tr><td class="text-muted small">Qty Saat Ini</td><td class="text-end small">${info.current_qty}</td></tr>
                            <tr><td class="text-muted small">Created At</td><td class="text-end small">${formatDateTime(info.created_at)}</td></tr>
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
    @endpush

@endsection