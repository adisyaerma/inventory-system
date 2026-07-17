@extends('master')
@section('title', 'Stok')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Stok</h4>
                <small class="text-muted">Kelola stok data barang di gudang</small>
            </div>
            <div class="float-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn-sm btn border-secondary bg-white border" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Import</span>
                    </button>

                    <a href="{{ route('stock.export') }}" class="btn-sm btn border border-secondary bg-white"
                        id="exportBtn">
                        <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                            viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor"
                                d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Export</span>
                    </a>

                    <button id="btnResetFilter" class="btn-sm btn border-secondary bg-white border">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 21 21">
                            <path d="M0 0h21v21H0z" fill="none" />
                            <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                                <path d="M7.5 6.5h-4v-4" />
                            </g>
                        </svg>
                        <span class="d-none d-md-inline ms-1">Reset Filter</span>
                    </button>

                    <!-- Button -->
                    <button data-bs-toggle="modal" data-bs-target="#addStockModal" type="button"
                        class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Tambah</span>
                    </button>

                </div>

                <div class="modal fade" id="addStockModal" tabindex="-1">

                    <div class="modal-dialog modal-xl">

                        <div class="modal-content">

                            <form id="formStock" action="{{ route('stock.store') }}" method="POST">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Stok Barang
                                        </h4>

                                        <small class="text-muted">
                                            Tambahkan barang beserta lokasi penyimpanannya.
                                        </small>

                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body p-4">

                                    <div class="row g-4">

                                        {{-- ================= LEFT ================= --}}
                                        <div class="col-lg-6">

                                            <div class="card shadow border-0 rounded-4 h-100">

                                                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                                    <h5 class="fw-bold mb-0">
                                                        <i class="bi bi-box-seam text-primary me-2"></i>
                                                        Informasi Barang
                                                    </h5>

                                                    <small class="text-muted">
                                                        Lengkapi informasi barang yang akan ditambahkan.
                                                    </small>

                                                </div>

                                                <div class="card-body px-4 pb-4">

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Item Code Internal
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-barcode"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="item_code_internal"
                                                                placeholder="Masukkan Item Code Internal" required>

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

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

                                                    <div class="mb-3">

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

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Nama Barang
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-package"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="name" placeholder="Masukkan Nama Barang"
                                                                required>

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

                                                            <textarea rows="5" class="form-control form-control-sm" name="description" placeholder="Opsional"></textarea>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        {{-- ================= RIGHT ================= --}}
                                        <div class="col-lg-6">

                                            <div class="card shadow border-0 rounded-4 h-100">

                                                <div
                                                    class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">

                                                    <div>

                                                        <h5 class="fw-bold mb-0">
                                                            <i class="bi bi-geo-alt text-success me-2"></i>
                                                            Lokasi Penyimpanan
                                                        </h5>

                                                        <small class="text-muted">
                                                            Tambahkan satu atau lebih lokasi penyimpanan.
                                                        </small>

                                                    </div>

                                                    <button type="button" id="btnAddLocation"
                                                        class="btn btn-sm btn-primary rounded-pill px-3">

                                                        <i class="bx bx-plus"></i>
                                                        Tambah

                                                    </button>

                                                </div>

                                                <div class="card-body">

                                                    <div id="locationContainer">

                                                        <!-- Header -->
                                                        <div class="row g-2 mb-2 fw-semibold small text-secondary">
                                                            <div class="col-7">
                                                                Lokasi
                                                            </div>

                                                            <div class="col-3">
                                                                Qty
                                                            </div>

                                                            <div class="col-2 text-center">
                                                                Aksi
                                                            </div>
                                                        </div>

                                                        <!-- Record pertama -->
                                                        <div class="row g-2 align-items-center location-item">

                                                            <div class="col-7">

                                                                <select name="locations[0][location]"
                                                                    class="form-select form-select-sm location-select"
                                                                    required>

                                                                    <option value="">Pilih / Ketik Lokasi</option>

                                                                    @foreach ($locations as $location)
                                                                        <option value="{{ $location->location_name }}">
                                                                            {{ $location->location_name }}
                                                                        </option>
                                                                    @endforeach

                                                                </select>

                                                            </div>

                                                            <div class="col-3">

                                                                <input type="number" class="form-control form-control-sm"
                                                                    name="locations[0][quantity]" min="0"
                                                                    placeholder="0" required>

                                                            </div>

                                                            <div class="col-2 d-grid">

                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm btnRemove">

                                                                    <i class="bx bx-trash"></i>

                                                                </button>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </div>

                                <div class="modal-footer border-0 pt-0">

                                    <button type="button" class="btn  btn-outline-secondary" data-bs-dismiss="modal">

                                        Batal

                                    </button>

                                    <button type="submit" class="btn  btn-primary px-4">

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

        <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('stock.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-content border-0 shadow">

                        <div class="modal-header border-0 px-4 pt-4">

                            <div class="d-flex align-items-center gap-3">

                                <div class=" rounded-3 p-2 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="fs-3 text-primary" width="1em"
                                        height="1em" viewBox="0 0 24 24">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                                    </svg>
                                </div>

                                <div>
                                    <h5 class="mb-0 fw-bold">
                                        Impor Data Stok
                                    </h5>

                                    <small class="text-muted">
                                        Import data stok dari file Excel
                                    </small>
                                </div>

                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                        </div>

                        <div class="modal-body">

                            {{-- STEP 1 --}}
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge rounded-circle bg-primary"
                                        style="width:32px;height:32px;line-height:24px;">
                                        1
                                    </span>
                                </div>

                                <div class="w-100">
                                    <h6 class="fw-bold mb-1">
                                        Unduh Template
                                    </h6>

                                    <p class="text-muted small mb-3">
                                        Gunakan template Excel berikut untuk menyiapkan data impor.
                                    </p>

                                    <a href="{{ route('stock.template') }}" class="btn btn-sm btn-outline-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-1 fs-5" width="1em"
                                            height="1em" viewBox="0 0 24 24">
                                            <path d="M0 0h24v24H0z" fill="none" />
                                            <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                                        </svg>

                                        Unduh Template Excel
                                    </a>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- STEP 2 --}}
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="badge rounded-circle bg-primary"
                                        style="width:32px;height:32px;line-height:24px;">
                                        2
                                    </span>
                                </div>

                                <div class="w-100">

                                    <h6 class="fw-bold mb-1">
                                        Upload File
                                    </h6>

                                    <p class="text-muted small mb-3">
                                        Upload file Excel (.xlsx, .xls) sesuai template.
                                    </p>

                                    <label class="upload-box w-100">
                                        <input type="file" id="excelFile" name="file" accept=".xlsx,.xls" required
                                            hidden>

                                        <div class="border border-2 border-primary-subtle rounded p-5 text-center">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="fs-4 mb-1" width="1em"
                                                height="1em" viewBox="0 0 24 24">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path fill="currentColor"
                                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm4 18H6V4h7v5h5z" />
                                            </svg>

                                            <div>
                                                <small class="fw-semibold">
                                                    Drag & drop file di sini
                                                </small>
                                            </div>

                                            <div>
                                                <small class="text-muted">
                                                    atau klik untuk memilih file
                                                </small>
                                            </div>

                                            <small class="text-muted">
                                                Maks. 5MB
                                            </small>

                                            <div id="selectedFile" class="mt-2" style="display:none;">
                                                <small class="text-success fw-semibold">
                                                    ✓ File dipilih: <span id="fileName"></span>
                                                </small>
                                            </div>

                                        </div>
                                    </label>

                                </div>
                            </div>

                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>
                                Import Data
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <div class="container-fluid px-4">

                <style>
                    .filter-toolbar-stock {
                        --gap: 0.5rem;
                    }

                    .filter-toolbar-stock>* {
                        flex: 1 1 calc(50% - var(--gap));
                        min-width: 150px;
                    }

                    @media (max-width: 575.98px) {
                        .filter-toolbar-stock>* {
                            flex: 1 1 100%;
                        }
                    }
                </style>

                <div class="top-row d-flex justify-content-lg-start justify-content-center mb-3">

                    <div class="filter-toolbar-stock d-flex flex-wrap gap-2">

                        <!-- Search -->
                        <div class="input-group input-group-sm shadow-sm flex-nowrap">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="customSearch" class="form-control border-start-0"
                                placeholder="Cari...">
                        </div>

                        <!-- Lokasi -->
                        <select id="filterLocation" class="form-select form-select-sm shadow-sm">
                            <option value="">Semua Lokasi</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                </div>
            </div>

            <table class="table table-bordered" id="stock">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Item Code Internal</th>
                        <th>Item Code Supplier</th>
                        <th>Item Code Customer</th>
                        <th>Nama Barang</th>
                        <th>Deskripsi</th>
                        <th>Lokasi & Qty</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="editStockModal" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <form id="editStockForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Stok Barang
                            </h4>

                            <small class="text-muted">
                                Ubah informasi barang beserta lokasi penyimpanannya.
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body p-4">

                        <div class="row g-4">

                            {{-- ================= LEFT ================= --}}
                            <div class="col-lg-6">

                                <div class="card shadow border-0 rounded-4 h-100">

                                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                        <h5 class="fw-bold mb-0">
                                            <i class="bx bx-package text-primary me-2"></i>
                                            Informasi Barang
                                        </h5>

                                        <small class="text-muted">
                                            Lengkapi informasi barang yang akan ditambahkan.
                                        </small>

                                    </div>

                                    <div class="card-body px-4 pb-4">

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Item Code Internal
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-barcode"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code_internal" id="edit_item_code_internal"
                                                    placeholder="Masukkan Item Code Internal" required>

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Item Code Supplier
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-buildings"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code_supplier" id="edit_item_code_supplier"
                                                    placeholder="Opsional">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Item Code Customer
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-user"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code_customer" id="edit_item_code_customer"
                                                    placeholder="Opsional">

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

                                        <div>

                                            <label class="form-label fw-semibold">
                                                Deskripsi
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text align-items-start pt-2">
                                                    <i class="bx bx-note"></i>
                                                </span>

                                                <textarea rows="5" class="form-control form-control-sm" id="edit_description" name="description"
                                                    placeholder="Opsional"></textarea>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- ================= RIGHT ================= --}}
                            <div class="col-lg-6">

                                <div class="card shadow border-0 rounded-4 h-100">

                                    <div
                                        class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">

                                        <div>

                                            <h5 class="fw-bold mb-0">
                                                <i class="bx bx-map text-success me-2"></i>
                                                Lokasi Penyimpanan
                                            </h5>

                                            <small class="text-muted">
                                                Tambahkan satu atau lebih lokasi penyimpanan.
                                            </small>

                                        </div>

                                        <button type="button" id="btnEditLocation"
                                            class="btn btn-sm btn-primary rounded-pill px-3">

                                            <i class="bx bx-plus"></i>
                                            Tambah

                                        </button>

                                    </div>

                                    <div class="card-body">

                                        <div id="editLocationContainer">

                                            <!-- Header -->
                                            <div class="row g-2 mb-2 fw-semibold small text-secondary">
                                                <div class="col-7">
                                                    Lokasi
                                                </div>

                                                <div class="col-3">
                                                    Qty
                                                </div>

                                                <div class="col-2 text-center">
                                                    Aksi
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>

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
        <script>
            let table;
            $(document).ready(function() {

                // Select2
                $('#filterLocation').select2({
                    width: '100%'
                });

                // DataTable
                table = $('#stock').DataTable({

                    dom: 'rtip',

                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    autoWidth: false,

                    ajax: {
                        url: "{{ route('stock.data') }}",

                        data: function(d) {
                            d.location_id = $('#filterLocation').val();
                        }
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'item_code_internal'
                        },
                        {
                            data: 'item_code_supplier'
                        },
                        {
                            data: 'item_code_customer'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'locations_qty',
                            orderable: false,
                            searchable: false
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
                // Update URL export
                function updateExportUrl() {

                    let locationId = $('#filterLocation').val();
                    let search = $('#customSearch').val().trim();

                    let url = new URL("{{ route('stock.export') }}");

                    if (locationId) {
                        url.searchParams.set('location_id', locationId);
                    }
                    if (search) url.searchParams.append('search', search);

                    $('#exportBtn').attr('href', url.toString());
                }

                $('#filterLocation').change(function() {

                    table.ajax.reload();

                    updateExportUrl();

                });

                $('#customSearch').on('keyup input', updateExportUrl);

                // Reset filter
                $('#btnResetFilter').click(function() {

                    $('#filterLocation').val(null).trigger('change');
                    $('#customSearch').val(''); // fix typo: customSearhch -> customSearch

                    table.search('').draw();
                    updateExportUrl();

                });
                // Inisialisasi pertama
                updateExportUrl();

                $('#formStock').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('stock.store') }}",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function(response) {
                            $('#addStockModal').modal('hide');
                            $('#formStock')[0].reset();
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
                border: 1 px solid #ced4da;
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Import Gagal',
                    html: `{!! nl2br(e(session('error'))) !!}`,
                    width: '500px',
                    confirmButtonText: 'OK'
                });
            </script>
        @endif

        <style>
            #stock_wrapper {
                padding: 1rem;
            }

            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            #reader {
                width: 100%;
                min-height: 280px;
                border-radius: 10px;
                overflow: hidden;
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
            #stock {
                min-width: 1400px;
                /* ubah sesuai kebutuhan */
            }

            #stock th,
            #stock td {
                padding: 12px 16px;
                vertical-align: middle;
            }

            /* Kolom deskripsi dan lokasi lebih lebar */
            #stock th:nth-child(5),
            #stock td:nth-child(5) {
                min-width: 300px;
                white-space: normal;
                word-break: break-word;
            }

            #stock th:nth-child(6),
            #stock td:nth-child(6) {
                min-width: 250px;
                white-space: normal;
                word-break: break-word;
            }

            /* Kolom lainnya tetap satu baris */
            #stock td:not(:nth-child(5)):not(:nth-child(6)),
            #stock th:not(:nth-child(5)):not(:nth-child(6)) {
                white-space: nowrap;
            }
        </style>

        <style>
            /* Desktop */
            .dataTables_wrapper .dt-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: .5rem;
            }

            /* Mobile & Tablet */
            @media (max-width: 991.98px) {

                .dataTables_wrapper .dt-top {
                    justify-content: center !important;
                    text-align: center;
                    gap: .75rem;
                }

                .dataTables_wrapper .dt-top .dataTables_length,
                .dataTables_wrapper .dt-top .dataTables_filter {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    margin: 0;
                }

                .dataTables_wrapper .dt-top .dataTables_filter label,
                .dataTables_wrapper .dt-top .dataTables_length label {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: .5rem;
                    flex-wrap: wrap;
                    margin: 0;
                }

                .dataTables_wrapper .dt-top .dataTables_filter input {
                    width: 220px;
                }
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <script>
            document.getElementById('excelFile').addEventListener('change', function() {

                const file = this.files[0];

                if (file) {
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('selectedFile').style.display = 'block';
                }
            });
        </script>
        <style>
            .upload-box {
                cursor: pointer;
                transition: all .2s ease;
            }

            .upload-box:hover .border {
                background: #f8fafc;
                border-color: #0d6efd !important;
            }

            .upload-box.dragover .border {
                border-color: #0d6efd !important;
                background-color: #eef6ff;
            }
        </style>
        <script>
            const dropZone = document.querySelector('.upload-box');
            const fileInput = document.getElementById('excelFile');
            const fileName = document.getElementById('fileName');
            const selectedFile = document.getElementById('selectedFile');

            function showSelectedFile(file) {
                fileName.textContent = file.name;
                selectedFile.style.display = 'block';
            }

            // Saat memilih file lewat klik
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    showSelectedFile(this.files[0]);
                }
            });

            // Drag masuk
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            // Drag keluar
            dropZone.addEventListener('dragleave', function() {
                this.classList.remove('dragover');
            });

            // Drop file
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');

                const files = e.dataTransfer.files;

                if (files.length > 0) {
                    fileInput.files = files; // isi input file
                    showSelectedFile(files[0]); // tampilkan nama file
                }
            });
        </script>

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
            let index = 1;

            function initTomSelect(element) {

                if (element.tomselect) return;

                new TomSelect(element, {
                    create: true,
                    persist: false,
                    createOnBlur: true,
                    allowEmptyOption: true,
                    placeholder: "Pilih atau ketik lokasi...",
                    dropdownDirection: "up"
                });

            }


            document.querySelectorAll('.location-select').forEach(function(el) {
                initTomSelect(el);
            });


            $('#btnAddLocation').click(function() {

                let html = `

<div class="row g-2 align-items-center location-item mt-2">

    <div class="col-7">

        <select
            name="locations[${index}][location]"
            class="form-select form-select-sm location-select"
            required>

            <option value="">Pilih / Ketik Lokasi</option>

            @foreach ($locations as $location)
                <option value="{{ $location->location_name }}">
                    {{ $location->location_name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="col-3">

        <input
            type="number"
            class="form-control form-control-sm"
            name="locations[${index}][quantity]"
            min="0"
            placeholder="0"
            required>

    </div>

    <div class="col-2 d-grid">

        <button
            type="button"
            class="btn btn-outline-danger btn-sm btnRemove">

            <i class="bx bx-trash"></i>

        </button>

    </div>

</div>

`;

                $('#locationContainer').append(html);

                let select = $('#locationContainer .location-item:last .location-select')[0];

                initTomSelect(select);

                index++;

            });

            $(document).on('click', '.btnRemove', function() {

                if ($('#locationContainer .location-item').length == 1) {

                    alert('Minimal harus ada satu lokasi.');

                    return;

                }

                let item = $(this).closest('.location-item');

                let select = item.find('.location-select')[0];

                if (select.tomselect) {
                    select.tomselect.destroy();
                }

                item.remove();

            });
        </script>

        <script>
            let editIndex = 0;

            function initEditTomSelect(element) {

                if (element.tomselect) return;

                new TomSelect(element, {

                    create: true,

                    persist: false,

                    createOnBlur: true,

                    allowEmptyOption: true,

                    placeholder: "Pilih atau ketik lokasi...",

                    dropdownDirection: "up"

                });

            }

            function addEditLocationRow(location = "", qty = "") {

                let html = `

<div class="row g-2 align-items-center location-item mt-2">

<div class="col-7">

<select
name="locations[${editIndex}][location]"
class="form-select form-select-sm edit-location-select"
required>

<option value="">Pilih / Ketik Lokasi</option>

@foreach ($locations as $location)

<option value="{{ $location->location_name }}">

{{ $location->location_name }}

</option>

@endforeach

</select>

</div>

<div class="col-3">

<input
type="number"
class="form-control form-control-sm"
name="locations[${editIndex}][quantity]"
value="${qty !== '' && qty !== null ? parseFloat(qty) : ''}"
min="0"
required>

</div>

<div class="col-2 d-grid">

<button
type="button"
class="btn btn-outline-danger btn-sm btnEditRemove">

<i class="bx bx-trash"></i>

</button>

</div>

</div>

`;

                $('#editLocationContainer').append(html);

                let select = $('#editLocationContainer .location-item:last .edit-location-select')[0];

                initEditTomSelect(select);

                if (location != "") {

                    select.tomselect.setValue(location);

                }

                editIndex++;

            }

            $(document).on('click', '.btnEditStock', function() {

                let id = $(this).data('id');

                $('#editLocationContainer .location-item').remove();

                editIndex = 0;

                $.ajax({

                    url: '/stock/' + id + '/edit',

                    type: 'GET',

                    success: function(res) {

                        $('#editStockForm').attr('action', '/stock/' + id);

                        $('#edit_item_code_internal').val(res.stock.item_code_internal);
                        $('#edit_item_code_supplier').val(res.stock.item_code_supplier);
                        $('#edit_item_code_customer').val(res.stock.item_code_customer);
                        $('#edit_name').val(res.stock.name);
                        $('#edit_description').val(res.stock.description);

                        res.stock.locations.forEach(function(item) {
                            addEditLocationRow(
                                item.location_name,
                                item.pivot.quantity
                            );
                        });

                        // Tampilkan modal
                        $('#editStockModal').modal('show');

                    }

                });

            });

            $('#btnEditLocation').click(function() {

                addEditLocationRow();

            });

            $(document).on('click', '.btnEditRemove', function() {

                if ($('#editLocationContainer .location-item').length == 1) {

                    alert('Minimal satu lokasi.');

                    return;

                }

                let row = $(this).closest('.location-item');

                let select = row.find('.edit-location-select')[0];

                if (select.tomselect) {

                    select.tomselect.destroy();

                }

                row.remove();

            });
        </script>

        <script>
            $('#editStockForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),

                    beforeSend: function() {
                        $('#editStockForm button[type="submit"]').prop('disabled', true);
                    },

                    success: function(res) {

                        $('#editStockModal').modal('hide');

                        $('#editStockForm button[type="submit"]').prop('disabled', false);

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

                        $('#editStockForm button[type="submit"]').prop('disabled', false);

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
