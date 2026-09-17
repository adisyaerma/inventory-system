@extends('master')
@section('title', 'Barang')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Barang</h4>
                <small class="text-muted">Kelola master data barang</small>
            </div>
            <div class="float-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">

                    <!-- Button -->
                    <button data-bs-toggle="modal" data-bs-target="#addItemModal" type="button"
                        class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Tambah</span>
                    </button>

                </div>

                <div class="modal fade" id="addItemModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">

                            <form id="addItemForm" action="{{ route('items.store') }}" method="POST">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Barang
                                        </h4>

                                        <small class="text-muted">
                                            Lengkapi informasi barang yang akan ditambahkan.
                                        </small>

                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body p-4">

                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Item Code Internal
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bx bx-barcode"></i>
                                            </span>

                                            <input type="text" class="form-control form-control-sm"
                                                name="item_code_internal" placeholder="Masukkan Item Code Internal"
                                                required>

                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-6 mb-3">

                                            <label class="form-label fw-semibold">
                                                Item Code Supplier
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-buildings"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code_supplier" placeholder="Opsional">

                                            </div>

                                        </div>

                                        <div class="col-md-6 mb-3">

                                            <label class="form-label fw-semibold">
                                                Item Code Customer
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-user"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code_customer" placeholder="Opsional">

                                            </div>

                                        </div>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Nama Barang
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bx bx-package"></i>
                                            </span>

                                            <input type="text" class="form-control form-control-sm" name="name"
                                                placeholder="Masukkan Nama Barang" required>

                                        </div>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Vendor
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bx bx-store"></i>
                                            </span>

                                            <select name="vendor_id" class="form-select form-select-sm">
                                                <option value="">Pilih Vendor (Opsional)</option>
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">
                                                        {{ $vendor->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                    </div>

                                    <div>

                                        <label class="form-label fw-semibold">
                                            Deskripsi
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text align-items-start pt-2">
                                                <i class="bx bx-note"></i>
                                            </span>

                                            <textarea rows="4" class="form-control form-control-sm" name="description"
                                                placeholder="Opsional"></textarea>

                                        </div>

                                    </div>

                                </div>

                                <div class="modal-footer border-0 pt-0">

                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Batal
                                    </button>

                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i>
                                        Simpan Barang
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>
            </div>

        </div>

        <div class="container-fluid px-4">

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
                    grid-template-columns: repeat(3, 1fr);
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

                .filter-toolbar .input-group .select2-container {
                    flex: 1 1 auto;
                    width: 1% !important;
                }

                .filter-toolbar .input-group .select2-container .select2-selection--single {
                    height: 31px !important;
                    border: 1px solid #dfe3e9;
                    border-left: none;
                    border-top-left-radius: 0;
                    border-bottom-left-radius: 0;
                    display: flex;
                    align-items: center;
                }

                .filter-toolbar .input-group .select2-container .select2-selection__rendered {
                    line-height: 29px !important;
                    padding-left: .75rem;
                    font-size: .85rem;
                }

                .filter-toolbar .input-group .select2-container .select2-selection__arrow {
                    height: 29px !important;
                }

                .filter-toolbar .input-group:focus-within .select2-container .select2-selection--single {
                    box-shadow: none;
                }

                @media (max-width: 991.98px) {
                    .filter-toolbar {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }

                @media (max-width: 575.98px) {
                    .filter-toolbar {
                        grid-template-columns: 1fr;
                    }
                }
            </style>

            <div class="filter-toolbar-card">

                <div class="filter-toolbar-eyebrow">
                    <div class="filter-toolbar-eyebrow-label">
                        <i class="bi bi-sliders"></i>
                        <span>Filter Data</span>
                    </div>

                    <button type="button" class="btn-sm btn border-secondary bg-white border" id="btnResetFilter"
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

                <div class="filter-toolbar">

                    <!-- Search -->
                    <div class="filter-group filter-group-search">
                        <label class="filter-label" for="customSearch">Cari</label>
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="customSearch" class="form-control border-start-0"
                                placeholder="Cari...">
                        </div>
                    </div>

                    <!-- Vendor -->
                    <div class="filter-group">
                        <label class="filter-label" for="filterVendor">Vendor</label>
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-building text-muted"></i>
                            </span>
                            <select id="filterVendor" class="form-select border-start-0 w-100">
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

            <div class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3" id="bulkActionBar">
                <span class="small text-muted">
                    <span id="selectedCount">0</span> data dipilih
                </span>
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
            </div>

            <table class="table table-bordered" id="item">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>Item Code Internal</th>
                        <th>Nama Barang</th>
                        <th>Vendor</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>

            <div class="mb-3 d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" id="tableFooter">
                <div class="d-flex align-items-center gap-2" id="lengthWrapper">
                    <span>Tampilkan</span>
                    <select id="customLength" class="form-select form-select-sm" style="width:80px">
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

    <div class="modal fade" id="editItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form id="editItemForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Barang
                            </h4>

                            <small class="text-muted">
                                Ubah informasi barang.
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body p-4">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Item Code Internal
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bx bx-barcode"></i>
                                </span>

                                <input type="text" class="form-control form-control-sm" name="item_code_internal"
                                    id="edit_item_code_internal" placeholder="Masukkan Item Code Internal" required>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Item Code Supplier
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bx bx-buildings"></i>
                                    </span>

                                    <input type="text" class="form-control form-control-sm"
                                        name="item_code_supplier" id="edit_item_code_supplier" placeholder="Opsional">

                                </div>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Item Code Customer
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bx bx-user"></i>
                                    </span>

                                    <input type="text" class="form-control form-control-sm"
                                        name="item_code_customer" id="edit_item_code_customer" placeholder="Opsional">

                                </div>

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Nama Barang
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bx bx-package"></i>
                                </span>

                                <input type="text" class="form-control form-control-sm" name="name"
                                    placeholder="Masukkan Nama Barang" id="edit_name" required>

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Vendor
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bx bx-store"></i>
                                </span>

                                <select name="vendor_id" class="form-select form-select-sm" id="edit_vendor_id">
                                    <option value="">Pilih Vendor (Opsional)</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                        </div>

                        <div>

                            <label class="form-label fw-semibold">
                                Deskripsi
                            </label>

                            <div class="input-group">

                                <span class="input-group-text align-items-start pt-2">
                                    <i class="bx bx-note"></i>
                                </span>

                                <textarea rows="4" class="form-control form-control-sm" id="edit_description"
                                    name="description" placeholder="Opsional"></textarea>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer border-0 pt-0">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i>
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    @push('script')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            let table;
            $(document).ready(function() {

                // Select2
                $('#filterVendor').select2({
                    width: '100%'
                });

                // DataTable
                table = $('#item').DataTable({

                    dom: 'rtip',

                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    autoWidth: false,

                    ajax: {
                        url: "{{ route('items.data') }}",

                        data: function(d) {
                            d.vendor_id = $('#filterVendor').val();
                        }
                    },

                    columns: [{
                            data: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'item_code_internal'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'vendor'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]
                })

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                });

                $('#customLength').change(function() {
                    table.page.len($(this).val()).draw();
                });

                $('#filterVendor').change(function() {
                    table.ajax.reload();
                });

                // Reset filter
                $('#btnResetFilter').click(function() {
                    $('#filterVendor').val(null).trigger('change');
                    $('#customSearch').val('');
                    table.search('').draw();
                });

                $('#addItemForm').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('items.store') }}",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function(response) {
                            $('#addItemModal').modal('hide');
                            $('#addItemForm')[0].reset();
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
                                        .zIndex = '9999999'
                                }
                            });
                        },
                        error: function(xhr) {

                            let message = 'Terjadi kesalahan.';

                            if (xhr.status === 422) {
                                message = Object.values(xhr.responseJSON.errors)[0][0];
                            } else if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: message,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        }
                    })
                })

            });
        </script>

        <style>
            .select2-container--default .select2-selection--single {
                height: 30px !important;
                border: 1px solid #ced4da;
                border-radius: .25rem;
                min-width: 140px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 29px !important;
                padding-left: .5rem;
                font-size: .800rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 29px !important;
            }
        </style>

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

        <style>
            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .dt-top {
                gap: 12px;
            }

            @media (max-width:991.98px) {

                .dt-top {
                    justify-content: center !important;
                    text-align: center;
                }

                .dt-top .dt-length,
                .dt-top .dt-search {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }

                .dt-top .dt-length label,
                .dt-top .dt-search label {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: .5rem;
                }

                .dt-top .dt-search input {
                    width: 220px;
                }

            }

            .dt-right {
                display: flex;
                justify-content: flex-end;
            }

            @media (max-width: 767.98px) {
                .dt-right {
                    justify-content: center;
                    width: 100%;
                    margin-top: .5rem;
                }
            }
        </style>

        <style>
            #item {
                min-width: 1200px;
            }

            #item th,
            #item td {
                padding: 12px 16px;
                vertical-align: middle;
            }

            #item th:nth-child(6),
            #item td:nth-child(6) {
                min-width: 250px;
                white-space: normal;
                word-break: break-word;
            }

            #item th:not(:nth-child(6)),
            #item td:not(:nth-child(6)) {
                white-space: nowrap;
            }
        </style>

        <script>
            let selectedItemIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedItemIds.size);
                $('#bulkActionBar').toggleClass('d-none', selectedItemIds.size === 0);
            }

            function resetItemSelection() {
                selectedItemIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedItemIds.add(id);
                } else {
                    selectedItemIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedItemIds.add(id);
                    } else {
                        selectedItemIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            $('#item').on('draw.dt', function() {
                resetItemSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedItemIds.size === 0) return;

                Swal.fire({
                    title: `Hapus ${selectedItemIds.size} barang?`,
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('items.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedItemIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetItemSelection();

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: res.message,
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex =
                                        '9999999';
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

        <script>
            $(document).on('submit', '.form-hapus', function(e) {

                e.preventDefault();

                let form = this;

                Swal.fire({
                    title: 'Hapus Data?',
                    text: 'Data yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Batal'
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
                                        .zIndex =
                                        '9999999';
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

        <script>
            $(document).on('click', '.btnEditItem', function() {

                let id = $(this).data('id');

                $.ajax({
                    url: '/item/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {

                        $('#editItemForm').attr('action', '/item/' + id);

                        $('#edit_item_code_internal').val(res.item.item_code_internal);
                        $('#edit_item_code_supplier').val(res.item.item_code_supplier);
                        $('#edit_item_code_customer').val(res.item.item_code_customer);
                        $('#edit_name').val(res.item.name);
                        $('#edit_description').val(res.item.description);
                        $('#edit_vendor_id').val(res.item.vendor_id).trigger('change');

                        $('#editItemModal').modal('show');
                    }
                });
            });

            $('#editItemForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),

                    beforeSend: function() {
                        $('#editItemForm button[type="submit"]').prop('disabled', true);
                    },

                    success: function(res) {

                        $('#editItemModal').modal('hide');

                        $('#editItemForm button[type="submit"]').prop('disabled', false);

                        table.ajax.reload(null, false);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message,
                            showConfirmButton: false,
                            timer: 2000,
                            didOpen: () => {
                                document.querySelector('.swal2-container').style.zIndex =
                                    '9999999';
                            }
                        });

                    },

                    error: function(xhr) {

                        $('#editItemForm button[type="submit"]').prop('disabled', false);

                        let message = 'Terjadi kesalahan.';

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });

                    }
                });
            });
        </script>
    @endpush
@endsection