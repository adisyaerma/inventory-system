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

                        <div class="filter-toolbar-card">
                            <div class="filter-toolbar-eyebrow">
                                <div class="filter-toolbar-eyebrow-label">
                                    <i class="bi bi-sliders"></i>
                                    <span>Filter Data</span>
                                </div>

                                <button type="button" class="btn-sm btn border-secondary bg-white border" id="resetBillFilter"
                                    title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </div>

                            <div class="filter-toolbar filter-toolbar-bill">

                                <!-- Search -->
                                <div class="filter-group">
                                    <label class="filter-label" for="billSearch">Cari</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" id="billSearch" class="form-control border-start-0"
                                            placeholder="Cari No. SO atau customer...">
                                    </div>
                                </div>

                                <!-- Status SO -->
                                <div class="filter-group">
                                    <label class="filter-label" for="billStatus">Status SO</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-flag text-muted"></i>
                                        </span>
                                        <select class="form-select border-start-0" id="billStatus">
                                            <option value="">Semua Status</option>
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Periode (tanggal bill) -->
                                <div class="filter-group">
                                    <label class="filter-label" for="billDateRange">Tanggal Bill</label>
                                    <div class="date-range-wrapper">
                                        <div class="input-group input-group-sm shadow-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-calendar-range text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="billDateRange"
                                                placeholder="Pilih periode" readonly autocomplete="off">
                                        </div>

                                        <div class="date-range-panel" id="billDateRangePanel">
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Dari</label>
                                                <input type="date" class="form-control form-control-sm" id="billStartDate">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1 text-muted">Sampai</label>
                                                <input type="date" class="form-control form-control-sm" id="billEndDate">
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-2">
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
                gap: .75rem 1rem;
                align-items: end;
            }

            .filter-toolbar-bill {
                grid-template-columns: 2fr 1fr 1fr;
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

            @media (max-width: 991.98px) {
                .filter-toolbar-bill {
                    grid-template-columns: 1fr;
                }
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