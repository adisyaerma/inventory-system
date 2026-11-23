@extends('master')
@section('title', 'History Item')
@section('content')

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">History Item</h4>
            <small class="text-muted">Lihat riwayat lengkap mutasi, staging in, dan staging out per barang</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-0 fw-bold">Daftar Barang</h5>
            <small class="text-muted">Klik tombol Riwayat untuk melihat histori lengkap satu barang</small>
        </div>

        <div class="card-body">

            <div class="fx" id="filterBar">

                <div class="fx-top">

                    <div class="fx-search">
                        <i class="bi bi-search fx-search-icon"></i>
                        <input type="text" id="itemHistorySearch" autocomplete="off"
                            placeholder="Cari kode / nama barang..." aria-label="Cari">
                        <button type="button" class="fx-search-clear" id="itemHistorySearchClear"
                            aria-label="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <div class="fx-actions">
                        <span class="fx-count" id="filterCount" aria-live="polite"></span>
                        <button type="button" class="fx-reset" id="resetItemHistoryFilter"
                            title="Kembalikan semua filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset</span>
                        </button>
                    </div>

                </div>

                <div class="fx-chips">

                    <div class="fx-chip">
                        <i class="bi bi-building"></i>
                        <label for="itemHistoryVendor">Vendor</label>
                        <select id="itemHistoryVendor">
                            <option value="">Semua</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-bordered" id="itemHistoryTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Code Internal</th>
                            <th>Nama Barang</th>
                            <th>Deskripsi</th>
                            <th>Vendor</th>
                            <th>Total Stok</th>
                            <th>Aktivitas Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mb-3 ms-2 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                    id="itemHistoryTableFooter">
                    <div class="d-flex align-items-center gap-2" id="itemHistoryLengthWrapper">
                        <span>Tampilkan</span>
                        <select id="itemHistoryLength" class="form-select form-select-sm" style="width:80px">
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

    @push('script')
        <style>
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

            #itemHistoryTable th:nth-child(3),
            #itemHistoryTable td:nth-child(3),
            #itemHistoryTable th:nth-child(4),
            #itemHistoryTable td:nth-child(4) {
                width: 250px !important;
                max-width: 250px !important;
                white-space: normal;
                overflow-wrap: break-word;
            }
        </style>

        <script>
            $(document).ready(function() {

                let itemHistoryTable = $('#itemHistoryTable').DataTable({
                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],

                    ajax: {
                        url: "{{ route('item-history.data') }}",
                        data: function(d) {
                            d.vendor_id = $('#itemHistoryVendor').val();
                        }
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'item_code_internal',
                            name: 'item_code_internal'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'description',
                            name: 'description',
                            orderable: false
                        },
                        {
                            data: 'vendor',
                            name: 'vendor'
                        },
                        {
                            data: 'total_qty',
                            name: 'total_qty',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'last_activity',
                            name: 'last_activity',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],

                    scrollX: true,
                    autoWidth: false,
                    columnDefs: [{
                            targets: [5, 6, 7],
                            className: 'text-center',
                        },
                        {
                            targets: [2, 3],
                            width: "250px",
                        }
                    ],

                    drawCallback: function() {
                        moveItemHistoryTableElements();
                    }
                });

                function moveItemHistoryTableElements() {
                    const $info = $('#itemHistoryTable_info');
                    if ($info.length && !$('#itemHistoryLengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#itemHistoryLengthWrapper');
                    }

                    const $paginate = $('#itemHistoryTable_paginate');
                    if ($paginate.length && !$('#itemHistoryTableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#itemHistoryTableFooter');
                    }
                }

                $('#itemHistorySearch').on('input', function() {
                    itemHistoryTable.search(this.value).draw();
                });

                $('#itemHistoryLength').on('change', function() {
                    itemHistoryTable.page.len($(this).val()).draw();
                });

                $('#itemHistoryVendor').on('change', function() {
                    itemHistoryTable.ajax.reload();
                });

                $('#resetItemHistoryFilter').on('click', function() {
                    $('#itemHistorySearch').val('');
                    $('#itemHistoryVendor').val('');
                    itemHistoryTable.search('').ajax.reload();
                });


                // ============ TAMPILAN FILTER (penanda aktif, hapus pencarian) ============
                function filled($el) {
                    return String($el.val() || '').trim() !== '';
                }

                function refreshFilterState() {
                    let count = 0;

                    const searchFilled = filled($('#itemHistorySearch'));
                    $('.fx-search').toggleClass('has-value', searchFilled);
                    if (searchFilled) count++;

                    const vendorOn = filled($('#itemHistoryVendor'));
                    $('#itemHistoryVendor').closest('.fx-chip').toggleClass('is-active', vendorOn);
                    if (vendorOn) count++;

                    $('#filterBar').toggleClass('has-active', count > 0);
                    $('#filterCount').text(count > 0 ? count + ' filter aktif' : '');
                }

                $('#itemHistorySearch, #itemHistoryVendor').on('input change', refreshFilterState);

                $('#resetItemHistoryFilter').on('click', function() {
                    setTimeout(refreshFilterState, 0);
                });

                $('#itemHistorySearchClear').on('click', function() {
                    $('#itemHistorySearch').val('').trigger('input').trigger('focus');
                });

                refreshFilterState();

            });
        </script>
    @endpush

@endsection