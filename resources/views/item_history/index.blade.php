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

            <div class="filter-toolbar-card">

                <div class="filter-toolbar-eyebrow">

                    <div class="filter-toolbar-eyebrow-label">
                        <i class="bi bi-sliders"></i>
                        <span>Filter Data</span>
                    </div>

                    <button type="button" class="btn-sm btn border-secondary bg-white border" id="resetItemHistoryFilter"
                        title="Reset Filter">

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

                <div class="filter-toolbar" style="grid-template-columns: max-content max-content; gap: 12px;">

                    <div class="filter-group filter-group-search">

                        <label class="filter-label" for="itemHistorySearch">
                            Cari
                        </label>

                        <div class="input-group input-group-sm shadow-sm" style="width: 350px;">

                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>

                            <input type="text" id="itemHistorySearch" class="form-control border-start-0"
                                placeholder="Cari kode / nama barang...">

                        </div>

                    </div>

                    <div class="filter-group">

                        <label class="filter-label" for="itemHistoryVendor">
                            Vendor
                        </label>

                        <div class="input-group input-group-sm shadow-sm" style="width: 350px;">

                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-building text-muted"></i>
                            </span>

                            <select class="form-select border-start-0" id="itemHistoryVendor">

                                <option value="">Semua Vendor</option>

                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

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
            }

            .filter-toolbar .input-group-text,
            .filter-toolbar .form-control,
            .filter-toolbar .form-select {
                border-color: #dfe3e9;
                font-size: .85rem;
            }

            @media (max-width: 575.98px) {
                .filter-toolbar {
                    grid-template-columns: 1fr !important;
                }
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

            });
        </script>
    @endpush

@endsection
