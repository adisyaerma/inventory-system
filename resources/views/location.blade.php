@extends('master')
@section('title', 'Lokasi')
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-start">
                <h4 class="mb-0">Lokasi Rak</h4>
                <small class="text-muted">Kelola data lokasi rak penyimpanan barang</small>
            </div>
            <div class="float-end mt-3">
                <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button" class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>
                <div class="modal fade" id="addLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content">

                            <form id="formLocation">

                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        Tambah Lokasi Rak <br>
                                        <small class="fw-light">Tambahkan lokasi rak baru untuk penyimpanan barang</small>
                                    </h5>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>
                                </div>

                                <div class="modal-body">

                                    {{-- Nama Rak --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Nama Rak
                                        </label>

                                        <input type="text" name="location_name" class="form-control"
                                            placeholder="Contoh: Rak A1" required>
                                    </div>

                                    {{-- Barcode --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Barcode Rak
                                        </label>

                                        <div class="border rounded p-4 text-center">

                                            <button type="button" id="btnScan" class="btn btn-outline-secondary rounded">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                                    viewBox="0 0 24 24" class="fs-4 me-2">
                                                    <path d="M0 0h24v24H0z" fill="none" />
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M7 12h10M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2m-4 14h2a2 2 0 0 0 2-2v-2" />
                                                </svg>

                                                Scan Barcode

                                            </button>

                                            <div class="small text-muted mt-2">
                                                Arahkan kamera ke barcode rak
                                            </div>
                                            <div class="small mt-1 fw-bold">atau</div>
                                            <input type="text" id="location_code" name="location_code"
                                                class="form-control form-control-sm mt-2"
                                                placeholder="Masukkan kode barcode secara manual">

                                        </div>

                                    </div>

                                    <div id="scanner-container" class="mb-3" style="display:none;">

                                        <div id="reader"></div>

                                    </div>

                                    {{-- Status --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Status
                                        </label>

                                        <div class="d-flex gap-4">

                                            <div class="form-check">

                                                <input class="form-check-input" type="radio" name="status" value="1"
                                                    checked>

                                                <label class="form-check-label">
                                                    Aktif
                                                </label>

                                            </div>

                                            <div class="form-check">

                                                <input class="form-check-input" type="radio" name="status"
                                                    value="0">

                                                <label class="form-check-label">
                                                    Non Aktif
                                                </label>

                                            </div>

                                        </div>

                                    </div>
                                    {{-- Description --}}
                                    <div class="mb-3 mt-3">
                                        <label class="form-label fw-bold">
                                            Deskripsi
                                        </label>

                                        <textarea name="description" class="form-control" rows="3" placeholder="Opsional"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">

                                        Batal

                                    </button>

                                    <button type="submit" class="btn btn-primary btnSaveLocation">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="1em" height="1em"
                                            viewBox="0 0 16 16">
                                            <path d="M0 0h16v16H0z" fill="none" />
                                            <path fill="currentColor"
                                                d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z" />
                                        </svg>

                                        Simpan

                                    </button>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <div class="container-fluid px-4">
                <style>
                    .filter-toolbar {
                        --gap: 0.5rem;
                    }

                    .filter-toolbar #customSearch {
                        padding-left: 0.5rem !important;
                    }

                    .filter-toolbar>* {
                        flex: 1 1 calc(25% - var(--gap));
                        max-width: 260px;
                        min-width: 150px;
                    }

                    .filter-toolbar .btn-reset-wrapper {
                        flex: 0 0 auto;
                    }

                    @media (max-width: 991.98px) {
                        .filter-toolbar>* {
                            flex: 1 1 calc(50% - var(--gap));
                            max-width: 320px;
                        }
                    }

                    @media (max-width: 575.98px) {
                        .filter-toolbar>* {
                            flex: 1 1 calc(50% - var(--gap));
                            max-width: 100%;
                        }

                        .filter-toolbar #resetFilter {
                            width: 100%;
                            justify-content: center;
                        }
                    }
                </style>

                <div class="filter-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">

                    <!-- Search -->
                    <div class="input-group input-group-sm shadow-sm flex-nowrap">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="customSearch" class="form-control border-start-0"
                            placeholder="Cari...">
                    </div>

                    <select id="filterStatus" class="form-select form-select-sm" style="width:180px;">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non Aktif</option>
                    </select>

                    <button id="btnResetFilter" type="button"
                        class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">

                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 21 21">

                            <path d="M0 0h21v21H0z" fill="none" />

                            <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round">

                                <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                                <path d="M7.5 6.5h-4v-4" />

                            </g>

                        </svg>

                        Reset Filter

                    </button>
                </div>
            </div>

            <div id="bulkActionBar"
                class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                <span><span id="selectedCount">0</span> data dipilih</span>
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
            </div>

            <table class="table table-bordered" id="location">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>Nama Rak</th>
                        <th>Barcode Rak</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0"></tbody>
            </table>
            <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2" id="tableFooter">
                    <div class="d-flex align-items-center gap-2" id="lengthWrapper">
                        <span>Tampilkan</span>
                        <select id="customLength" class="fonrm-select form-select-sm" style="width:80px">
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

    {{-- ================= MODAL EDIT (tunggal, diisi via AJAX) ================= --}}
    <div class="modal fade" id="editLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="formEditLocation">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            Edit Lokasi Rak <br>
                            <small class="fw-light">Ubah data lokasi rak penyimpanan barang</small>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Rak</label>
                            <input type="text" name="location_name" id="editLocationName" class="form-control"
                                placeholder="Contoh: Rak A1" required>
                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-bold">Barcode Rak</label>

                            <div class="border rounded p-4 text-center">

                                <button type="button" class="btn btn-outline-secondary rounded" id="btnScanEdit">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                        viewBox="0 0 24 24" class="fs-4 me-2">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2"
                                            d="M7 12h10M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2m-4 14h2a2 2 0 0 0 2-2v-2" />
                                    </svg>

                                    Scan Barcode

                                </button>

                                <div class="small text-muted mt-2">
                                    Arahkan kamera ke barcode rak
                                </div>

                                <div class="small mt-1 fw-bold">atau</div>

                                <input type="text" name="location_code" id="editLocationCode"
                                    class="form-control form-control-sm mt-2"
                                    placeholder="Masukkan kode barcode secara manual">

                            </div>

                        </div>

                        <div class="mb-3" id="scannerContainerEdit" style="display:none;">
                            <div id="readerEdit"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>

                            <div class="d-flex gap-4">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="editStatusAktif"
                                        value="1">
                                    <label class="form-check-label">Aktif</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="editStatusNonAktif"
                                        value="0">
                                    <label class="form-check-label">Non Aktif</label>
                                </div>

                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>

                            <textarea name="description" id="editDescription" class="form-control" rows="3" placeholder="Opsional"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary btnSaveEdit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="1em" height="1em"
                                viewBox="0 0 16 16">
                                <path d="M0 0h16v16H0z" fill="none" />
                                <path fill="currentColor"
                                    d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z" />
                            </svg>
                            Ubah
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    @push('script')
        <style>
            #location_wrapper {
                padding: 1rem;
            }

            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            #reader,
            #readerEdit {
                width: 100%;
                min-height: 280px;
                border-radius: 10px;
                overflow: hidden;
            }

            .dt-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }

            .dt-length,
            .dt-search {
                display: flex;
                align-items: center;
            }

            .dt-search .form-control {
                width: 260px;
                border-radius: 50rem !important;
            }

            .dt-search .form-control:focus {
                box-shadow: none;
            }

            #customSearch {
                padding-left: 42px;
                border-radius: 4px !important;
            }

            @media (max-width: 991.98px) {

                .dt-top {
                    justify-content: center !important;
                    text-align: center;
                }

                .dt-length,
                .dt-search {
                    width: 100%;
                    justify-content: center;
                }

                .dt-length label,
                .dt-search label {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-wrap: wrap;
                    gap: .5rem;
                    margin: 0;
                }

                .dt-search .form-control {
                    width: 220px;
                }
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // ============ VARIABEL GLOBAL ============
            let table;
        </script>

        <script>
            $(document).ready(function() {

                table = $('#location').DataTable({

                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],

                    ajax: {
                        url: "{{ route('locations.data') }}",
                        data: function(d) {
                            d.status = $('#filterStatus').val();
                        }
                    },

                    columns: [{
                            data: 'checkbox',
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
                            data: 'location_name',
                            name: 'location_name'
                        },
                        {
                            data: 'location_code',
                            name: 'location_code'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'description',
                            name: 'description'
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
                            targets: [2, 5],
                            className: "text-wrap",
                            width: "220px"
                        },
                        {
                            targets: 1,
                            width: "80px"
                        },
                        {
                            targets: [4, 6],
                            className: "text-center"
                        }
                    ],

                    initComplete: function() {
                        moveDataTablesElements();
                    },

                    drawCallback: function() {
                        moveDataTablesElements();
                    }

                });

                function moveDataTablesElements() {
                    const $info = $('#location_info');
                    if ($info.length && !$('#lengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#lengthWrapper');
                    }

                    const $paginate = $('#location_paginate');
                    if ($paginate.length && !$('#tableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#tableFooter');
                    }
                }

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                });

                $('#customLength').change(function() {
                    table.page.len($(this).val()).draw();
                });

                $('#filterStatus').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetFilter').on('click', function() {
                    $('#filterStatus').val('');
                    $('#customSearch').val('');
                    table.search('').draw();
                    table.ajax.reload();
                });

                // ================= TAMBAH (AJAX) =================
                $(document).on('submit', '#formLocation', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    $.ajax({
                        url: "{{ route('locations.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        beforeSend: function() {
                            $('.btnSaveLocation').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveLocation').prop('disabled', false);

                            $('#addLocationModal').modal('hide');
                            $('#formLocation')[0].reset();

                            table.ajax.reload(null, false);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        },

                        error: function(xhr) {

                            $('.btnSaveLocation').prop('disabled', false);

                            let message = 'Terjadi kesalahan.';

                            if (xhr.status === 422) {
                                if (xhr.responseJSON.errors) {
                                    message = Object.values(xhr.responseJSON.errors)[0][0];
                                } else if (xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: message,
                                timer: 3000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        }
                    });

                });

                // ================= EDIT: buka modal & isi data =================
                $(document).on('click', '.btnEdit', function() {

                    let id = $(this).data('id');

                    $.ajax({

                        url: "{{ route('locations.edit', ':id') }}".replace(':id', id),
                        type: 'GET',

                        success: function(res) {

                            $('#editId').val(res.id);
                            $('#editLocationName').val(res.location_name);
                            $('#editLocationCode').val(res.location_code);
                            $('#editDescription').val(res.description);

                            if (parseInt(res.status) === 1) {
                                $('#editStatusAktif').prop('checked', true);
                            } else {
                                $('#editStatusNonAktif').prop('checked', true);
                            }

                        },

                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Gagal memuat data.'
                            });
                        }

                    });

                });

                // ================= EDIT: simpan perubahan (AJAX) =================
                $(document).on('submit', '#formEditLocation', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#editId').val();

                    $.ajax({

                        url: "{{ route('locations.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveEdit').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveEdit').prop('disabled', false);

                            $('#editLocationModal').modal('hide');

                            table.ajax.reload(null, false);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        },

                        error: function(xhr) {

                            $('.btnSaveEdit').prop('disabled', false);

                            let message = 'Terjadi kesalahan.';

                            if (xhr.status === 422) {
                                if (xhr.responseJSON.errors) {
                                    message = Object.values(xhr.responseJSON.errors)[0][0];
                                } else if (xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: message,
                                timer: 3000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        }

                    });

                });

                // ================= HAPUS (AJAX) =================
                $(document).on('submit', '.form-hapus', function(e) {

                    e.preventDefault();

                    let form = this;

                    Swal.fire({
                        title: 'Hapus Data?',
                        text: 'Data yang dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {

                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: $(form).attr('action'),
                            type: 'POST',
                            data: $(form).serialize(),

                            success: function(res) {

                                table.ajax.reload(null, false);

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        document.querySelector('.swal2-container').style
                                            .zIndex = '9999999';
                                    }
                                });

                            },

                            error: function(xhr) {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                                });

                            }

                        });

                    });

                });

                $('#addLocationModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

                $('#editLocationModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

            });
        </script>

        {{-- ================= BULK DELETE (AJAX) ================= --}}
        <script>
            let selectedLocationIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedLocationIds.size);
                $('#bulkActionBar').toggleClass('d-none', selectedLocationIds.size === 0);
            }

            function resetLocationSelection() {
                selectedLocationIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedLocationIds.add(id);
                } else {
                    selectedLocationIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedLocationIds.add(id);
                    } else {
                        selectedLocationIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            $('#location').on('draw.dt', function() {
                resetLocationSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedLocationIds.size === 0) return;

                Swal.fire({
                    title: `Hapus ${selectedLocationIds.size} lokasi?`,
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('locations.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedLocationIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetLocationSelection();

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: res.message,
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        },

                        error: function(xhr) {

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                            });

                        }

                    });

                });

            });
        </script>

        {{-- ================= BARCODE SCAN: TAMBAH ================= --}}
        <script>
            let html5QrCode = null;
            let isProcessing = false;

            document.getElementById('btnScan').addEventListener('click', function() {

                const inputId = "location_code";
                const container = document.getElementById('scanner-container');

                container.style.display = "block";

                setTimeout(() => {

                    if (html5QrCode) return;

                    html5QrCode = new Html5Qrcode("reader");

                    html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 5,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        async (decodedText) => {

                            if (isProcessing) return;
                            isProcessing = true;

                            const input = document.getElementById(inputId);

                            if (!input) {
                                console.error("Input tidak ditemukan:", inputId);
                                isProcessing = false;
                                return;
                            }

                            try {
                                const res = await fetch(`/check-location-code?code=${decodedText}`);
                                const data = await res.json();

                                await html5QrCode.stop();
                                html5QrCode = null;
                                container.style.display = "none";

                                if (data.exists) {

                                    input.value = "";

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Kode sudah digunakan!',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                } else {

                                    input.value = decodedText;

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'QR valid',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                }

                            } catch (err) {
                                console.error(err);
                            } finally {
                                isProcessing = false;
                            }
                        }
                    ).catch(err => {
                        console.error("Camera error:", err);
                        alert("Kamera tidak bisa dibuka");
                    });

                }, 300);
            });
        </script>

        {{-- ================= BARCODE SCAN: EDIT (modal tunggal) ================= --}}
        <script>
            let html5QrCodeEdit = null;
            let isProcessingEdit = false;

            document.getElementById('btnScanEdit').addEventListener('click', function() {

                const id = $('#editId').val();

                const input = document.getElementById('editLocationCode');
                const container = document.getElementById('scannerContainerEdit');

                if (!input || !container) {
                    console.error('Element tidak ditemukan');
                    return;
                }

                container.style.display = 'block';

                setTimeout(() => {

                    if (html5QrCodeEdit) return;

                    html5QrCodeEdit = new Html5Qrcode('readerEdit');

                    html5QrCodeEdit.start({
                            facingMode: "environment"
                        }, {
                            fps: 5,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        async (decodedText) => {

                            if (isProcessingEdit) return;
                            isProcessingEdit = true;

                            const oldValue = input.value;

                            try {

                                const res = await fetch(
                                    `/check-location-code?code=${encodeURIComponent(decodedText)}&ignore_id=${id}`
                                );

                                const data = await res.json();

                                await html5QrCodeEdit.stop();

                                html5QrCodeEdit = null;
                                container.style.display = 'none';

                                if (data.exists) {

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Kode sudah digunakan!',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                    input.value = oldValue;

                                } else {

                                    input.value = decodedText;

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'QR valid',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });
                                }
                            } catch (err) {

                                console.error(err);

                                if (html5QrCodeEdit) {
                                    try {
                                        await html5QrCodeEdit.stop();
                                    } catch (e) {}
                                    html5QrCodeEdit = null;
                                }

                            } finally {
                                isProcessingEdit = false;
                            }
                        }
                    ).catch(err => {
                        console.error("Camera error:", err);

                        Swal.fire({
                            icon: 'error',
                            title: 'Kamera tidak bisa dibuka'
                        });
                    });

                }, 300);
            });

            document.getElementById('editLocationModal').addEventListener('hidden.bs.modal', async () => {

                if (html5QrCodeEdit) {
                    try {
                        await html5QrCodeEdit.stop();
                    } catch (e) {}

                    html5QrCodeEdit = null;
                }

                isProcessingEdit = false;

                document.getElementById('scannerContainerEdit').style.display = 'none';
            });
        </script>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: @json(session('success')),
                        showConfirmButton: false,
                        timer: 2000,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });
                });
            </script>
        @endif
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: @json($errors->first()),
                        showConfirmButton: false,
                        timer: 3000,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });
                });
            </script>
        @endif
        @if (session('deleted'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: @json(session('deleted')),
                        showConfirmButton: false,
                        timer: 2000,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });
                });
            </script>
        @endif
    @endpush
@endsection