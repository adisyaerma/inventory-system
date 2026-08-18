@extends('master')
@section('title', 'Stok Lokasi')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Stok Lokasi</h4>
                <small class="text-muted">Kelola penempatan & jumlah stok barang per lokasi gudang</small>
            </div>
            <div class="float-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">

                    <button type="button" class="btn-sm btn border-secondary bg-white border" data-bs-toggle="modal"
                        data-bs-target="#importLocationStockModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Import</span>
                    </button>

                    <a href="{{ route('location-stock.export') }}" class="btn-sm btn border border-secondary bg-white"
                        id="exportBtn">
                        <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                            viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor"
                                d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Export</span>
                    </a>

                    <!-- Button -->
                    <button data-bs-toggle="modal" data-bs-target="#addLocationStockModal" type="button"
                        class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Tambah</span>
                    </button>

                </div>

                <div class="modal fade" id="addLocationStockModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                    <div class="modal-dialog modal-md">

                        <div class="modal-content">

                            <form id="formLocationStock" action="{{ route('location-stock.store') }}" method="POST">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Stok Barang
                                        </h4>

                                        <small class="text-muted">
                                            Pilih barang lalu tentukan lokasi penyimpanannya.
                                        </small>

                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body p-4">

                                    <div class="card shadow border-0 rounded-4">

                                        {{-- ================= HEADER ================= --}}
                                        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                            <h5 class="fw-bold mb-0">
                                                <i class="bi bi-box-seam text-primary me-2"></i>
                                                Pilih Barang
                                            </h5>

                                            <small class="text-muted">
                                                Cari barang berdasarkan kode atau nama.
                                            </small>

                                        </div>


                                        <div class="card-body px-4 pb-4">

                                            {{-- ================= BARANG ================= --}}
                                            <div class="mb-4">

                                                <label class="form-label fw-semibold">
                                                    Barang
                                                </label>

                                                <select id="itemSelect" name="item_id" class="form-select form-select-sm"
                                                    required>

                                                    <option value="">
                                                        Cari barang...
                                                    </option>

                                                </select>

                                            </div>


                                            {{-- ================= INFO BARANG ================= --}}
                                            <div id="selectedItemInfo" class="d-none mb-4">

                                                <div class="p-3 bg-light rounded-3">

                                                    <div class="row g-2 small">

                                                        <div class="col-md-4">
                                                            <span class="text-muted d-block">
                                                                Kode Internal
                                                            </span>

                                                            <span class="fw-semibold" id="selectedItemCode">
                                                            </span>
                                                        </div>


                                                        <div class="col-md-4">
                                                            <span class="text-muted d-block">
                                                                Nama
                                                            </span>

                                                            <span class="fw-semibold" id="selectedItemName">
                                                            </span>
                                                        </div>


                                                        <div class="col-md-4">
                                                            <span class="text-muted d-block">
                                                                Vendor
                                                            </span>

                                                            <span class="fw-semibold" id="selectedItemVendor">
                                                            </span>
                                                        </div>

                                                    </div>

                                                </div>

                                            </div>


                                            {{-- ================= PEMISAH ================= --}}
                                            <hr class="my-4">


                                            {{-- ================= LOKASI ================= --}}
                                            <div>

                                                <div class="d-flex justify-content-between align-items-center mb-3">

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


                                                {{-- ================= LOCATION CONTAINER ================= --}}
                                                <div id="locationContainer">

                                                    {{-- HEADER --}}
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


                                                    {{-- RECORD PERTAMA --}}
                                                    <div class="row g-2 align-items-center location-item">

                                                        {{-- LOKASI --}}
                                                        <div class="col-7">

                                                            <select name="locations[0][location]"
                                                                class="form-select form-select-sm location-select"
                                                                required>

                                                                <option value="">
                                                                    Pilih / Ketik Lokasi
                                                                </option>

                                                                @foreach ($locations as $location)
                                                                    <option value="{{ $location->location_name }}">
                                                                        {{ $location->location_name }}
                                                                    </option>
                                                                @endforeach

                                                            </select>

                                                        </div>


                                                        {{-- QTY --}}
                                                        <div class="col-3">

                                                            <input type="number" class="form-control form-control-sm"
                                                                name="locations[0][quantity]" min="0"
                                                                placeholder="0" required>

                                                        </div>


                                                        {{-- AKSI --}}
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

                                <div class="modal-footer border-0 pt-0">

                                    <button type="button" class="btn  btn-outline-secondary" data-bs-dismiss="modal">

                                        Batal

                                    </button>

                                    <button type="submit" class="btn  btn-primary px-4">

                                        <i class="bx bx-save me-1"></i>

                                        Simpan Stok

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>
            </div>

        </div>

        <div class="modal fade" id="importLocationStockModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('location-stock.import') }}" method="POST" enctype="multipart/form-data">
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
                                        Impor Data Stok Lokasi
                                    </h5>

                                    <small class="text-muted">
                                        Import data stok lokasi dari file Excel
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

                                    <a href="{{ route('location-stock.template') }}"
                                        class="btn btn-sm btn-outline-secondary">
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
                                        <input type="file" id="excelLocationStockFile" name="file"
                                            accept=".xlsx,.xls" required hidden>

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

                                            <div id="selectedLocationStockFile" class="mt-2" style="display:none;">
                                                <small class="text-success fw-semibold">
                                                    ✓ File dipilih: <span id="locationStockFileName"></span>
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

                    <!-- Lokasi -->
                    <div class="filter-group">
                        <label class="filter-label" for="filterLocation">Lokasi</label>
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-geo-alt text-muted"></i>
                            </span>
                            <select id="filterLocation" class="form-select border-start-0 w-100">
                                <option value="">Semua Lokasi</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach
                            </select>
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

            <div class="mb-2 d-none align-items-center gap-2" id="bulkActionBar">
                <span class="small text-muted">
                    <span id="selectedCount">0</span> data dipilih
                </span>
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
            </div>

            <table class="table table-bordered" id="locationStock">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>Item Code Internal</th>
                        <th>Nama Barang</th>
                        <th>Vendor</th>
                        <th>Lokasi & Qty</th>
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

    <div class="modal fade" id="editLocationStockModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

        <div class="modal-dialog modal-md">

            <div class="modal-content">

                <form id="editLocationStockForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Stok Barang
                            </h4>

                            <small class="text-muted">
                                Ubah lokasi penyimpanan & jumlah stok barang.
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body p-4">

                        <div class="card shadow border-0 rounded-4">

                            {{-- ================= BARANG ================= --}}
                            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                <h5 class="fw-bold mb-0">
                                    <i class="bx bx-package text-primary me-2"></i>
                                    Barang
                                </h5>

                                <small class="text-muted">
                                    Barang tidak dapat diubah, hanya lokasi & qty.
                                </small>

                            </div>


                            <div class="card-body px-4 pb-4">

                                {{-- ================= INFO BARANG ================= --}}
                                <div class="p-3 bg-light rounded-3 small">

                                    <div class="row g-3">

                                        <div class="col-md-4">

                                            <span class="text-muted d-block mb-1">
                                                Kode Internal
                                            </span>

                                            <span class="fw-semibold" id="edit_item_code_internal">
                                            </span>

                                        </div>


                                        <div class="col-md-4">

                                            <span class="text-muted d-block mb-1">
                                                Nama
                                            </span>

                                            <span class="fw-semibold" id="edit_item_name">
                                            </span>

                                        </div>


                                        <div class="col-md-4">

                                            <span class="text-muted d-block mb-1">
                                                Vendor
                                            </span>

                                            <span class="fw-semibold" id="edit_item_vendor">
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- ================= PEMISAH ================= --}}
                                <hr class="my-4">


                                {{-- ================= LOKASI ================= --}}
                                <div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">

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


                                    {{-- ================= LOCATION CONTAINER ================= --}}
                                    <div id="editLocationContainer">

                                        {{-- HEADER --}}
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

                $('#filterVendor').select2({
                    width: '100%'
                });

                // DataTable
                table = $('#locationStock').DataTable({

                    dom: 'rtip',

                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    autoWidth: false,

                    ajax: {
                        url: "{{ route('location-stock.data') }}",

                        data: function(d) {
                            d.location_id = $('#filterLocation').val();
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
                            data: 'locations_qty',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }


                    ],
                    scrollX: true,
                    autoWidth: false,

                    columnDefs: [{
                        targets: 3,
                        width: "250px",
                        className: "text-wrap"
                    }],
                })

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                });

                $('#customLength').change(function() {

                    table.page.len($(this).val()).draw();

                })

                // Update URL export
                function updateExportUrl() {

                    let locationId = $('#filterLocation').val();
                    let vendorId = $('#filterVendor').val();
                    let search = $('#customSearch').val().trim();

                    let url = new URL("{{ route('location-stock.export') }}");

                    if (locationId) {
                        url.searchParams.set('location_id', locationId);
                    }
                    if (vendorId) {
                        url.searchParams.set('vendor_id', vendorId);
                    }
                    if (search) url.searchParams.append('search', search);

                    $('#exportBtn').attr('href', url.toString());
                }

                $('#filterLocation').change(function() {

                    table.ajax.reload();

                    updateExportUrl();

                });

                $('#filterVendor').change(function() {

                    table.ajax.reload();

                    updateExportUrl();

                });

                $('#customSearch').on('keyup input', updateExportUrl);

                // Reset filter
                $('#btnResetFilter').click(function() {

                    $('#filterLocation').val(null).trigger('change');
                    $('#filterVendor').val(null).trigger('change');
                    $('#customSearch').val('');

                    table.search('').draw();

                    updateExportUrl();

                });

                // Inisialisasi pertama
                updateExportUrl();

                $('#formLocationStock').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('location-stock.store') }}",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function(response) {
                            $('#addLocationStockModal').modal('hide');
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

        <script>
            document.getElementById('excelLocationStockFile').addEventListener('change', function() {
                if (this.files.length) {
                    document.getElementById('locationStockFileName').textContent = this.files[0].name;
                    document.getElementById('selectedLocationStockFile').style.display = 'block';
                }
            });

            (function() {
                const dropZone = document.querySelector('#importLocationStockModal .upload-box');
                const fileInput = document.getElementById('excelLocationStockFile');
                const fileName = document.getElementById('locationStockFileName');
                const selectedFile = document.getElementById('selectedLocationStockFile');

                function showFile(file) {
                    fileName.textContent = file.name;
                    selectedFile.style.display = 'block';
                }

                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                dropZone.addEventListener('dragleave', function() {
                    this.classList.remove('dragover');
                });

                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');

                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        showFile(e.dataTransfer.files[0]);
                    }
                });
            })();
        </script>

        <style>
            .upload-box {
                cursor: pointer;
            }

            .upload-box:hover .border {
                border-color: #0d6efd !important;
            }

            .upload-box.dragover .border {
                border-color: #0d6efd !important;
                background-color: rgba(13, 110, 253, .05);
            }
        </style>

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
            #locationStock {
                min-width: 1200px;
            }

            #locationStock th,
            #locationStock td {
                padding: 12px 16px;
                vertical-align: middle;
            }

            #locationStock th:nth-child(6),
            #locationStock td:nth-child(6) {
                min-width: 250px;
                white-space: normal;
                word-break: break-word;
            }

            #locationStock th:not(:nth-child(6)),
            #locationStock td:not(:nth-child(6)) {
                white-space: nowrap;
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

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        html: `{!! nl2br(e(session('error'))) !!}`,
                        width: '500px',
                        confirmButtonText: 'OK',
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });
                });
            </script>
        @endif

        <script>
            let selectedLocationStockIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedLocationStockIds.size);
                $('#bulkActionBar').toggleClass('d-none', selectedLocationStockIds.size === 0);
            }

            function resetLocationStockSelection() {
                selectedLocationStockIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedLocationStockIds.add(id);
                } else {
                    selectedLocationStockIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedLocationStockIds.add(id);
                    } else {
                        selectedLocationStockIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            $('#locationStock').on('draw.dt', function() {
                resetLocationStockSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedLocationStockIds.size === 0) return;

                Swal.fire({
                    title: `Hapus ${selectedLocationStockIds.size} data stok?`,
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
                        url: "{{ route('location-stock.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedLocationStockIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetLocationStockSelection();

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
            // ================= SHARED STATE — HANYA SATU KALI UNTUK SELURUH HALAMAN =================
            let allLocations = [
                @foreach ($locations as $location)
                    "{{ $location->location_name }}",
                @endforeach
            ];

            function addGlobalLocation(name) {
                if (!name) return;
                if (!allLocations.includes(name)) {
                    allLocations.push(name);
                }

                $('.edit-location-select, .location-select').each(function() {
                    if (this.tomselect && !this.tomselect.options[name]) {
                        this.tomselect.addOption({
                            value: name,
                            text: name
                        });
                    }
                });
            }
            // ==========================================================================================


            // ================================= TOMSELECT PILIH BARANG (ADD) =================================
            let itemTomSelect;

            document.addEventListener('DOMContentLoaded', function() {

                itemTomSelect = new TomSelect('#itemSelect', {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',

                    create: false,
                    maxOptions: 20,
                    placeholder: 'Cari kode / nama barang...',

                    load: function(query, callback) {

                        fetch(
                                `{{ route('location-stock.search-item') }}?q=${encodeURIComponent(query)}&_=${Date.now()}`
                            )
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Request gagal');
                                }

                                return response.json();
                            })
                            .then(json => {
                                callback(json);
                            })
                            .catch(error => {
                                console.error('Search item error:', error);
                                callback();
                            });
                    },

                    shouldLoad: function(query) {
                        return query.length === 0 || query.length >= 2;
                    },

                    onFocus: function() {
                        // Kalau belum ada item, load ulang
                        if (this.options && Object.keys(this.options).length === 0) {
                            this.load('');
                        }
                    }
                });


                // ==========================================================
                // SETIAP MODAL DIBUKA
                // ==========================================================

                document
                    .getElementById('locationStockModal')
                    .addEventListener('shown.bs.modal', function() {

                        // Bersihkan value sebelumnya
                        itemTomSelect.clear();

                        // Bersihkan option lama
                        itemTomSelect.clearOptions();

                        // Bersihkan cache pencarian Tom Select
                        if (typeof itemTomSelect.clearCache === 'function') {
                            itemTomSelect.clearCache();
                        }

                        // Load ulang daftar item
                        itemTomSelect.load('');
                    });

            });

            // ================================= FORM TAMBAH LOKASI =================================
            let index = 1;

            function initTomSelect(element) {

                if (element.tomselect) return;

                new TomSelect(element, {
                    create: true,
                    persist: false,
                    createOnBlur: true,
                    allowEmptyOption: true,
                    placeholder: "Pilih atau ketik lokasi...",
                    dropdownDirection: "up",

                    onOptionAdd: function(value, data) {
                        addGlobalLocation(value);
                    }
                });

                allLocations.forEach(function(loc) {
                    if (!element.tomselect.options[loc]) {
                        element.tomselect.addOption({
                            value: loc,
                            text: loc
                        });
                    }
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
            <button type="button" class="btn btn-outline-danger btn-sm btnRemove">
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

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Minimal harus ada satu lokasi.',
                        showConfirmButton: false,
                        timer: 2500,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });

                    return;
                }

                let item = $(this).closest('.location-item');
                let select = item.find('.location-select')[0];

                if (select.tomselect) {
                    select.tomselect.destroy();
                }

                item.remove();
            });

            // Reset form Tambah setiap kali modalnya ditutup
            $('#addLocationStockModal').on('hidden.bs.modal', function() {

                $('#locationContainer .location-item').each(function() {
                    let select = $(this).find('.location-select')[0];
                    if (select && select.tomselect) {
                        select.tomselect.destroy();
                    }
                    $(this).remove();
                });

                index = 1;

                $('#formLocationStock')[0].reset();

                if (itemTomSelect) {
                    itemTomSelect.clear();
                    itemTomSelect.clearOptions();
                }

                $('#selectedItemInfo').addClass('d-none');

                // tambahkan kembali 1 baris lokasi kosong sebagai default
                $('#btnAddLocation').click();
            });
            // ==========================================================================================


            // ================================= FORM EDIT LOKASI =================================
            let editIndex = 0;

            function initEditTomSelect(element) {

                if (element.tomselect) return;

                new TomSelect(element, {
                    create: true,
                    persist: false,
                    createOnBlur: true,
                    allowEmptyOption: true,
                    placeholder: "Pilih atau ketik lokasi...",
                    dropdownDirection: "up",

                    onOptionAdd: function(value, data) {
                        addGlobalLocation(value);
                    }
                });

                allLocations.forEach(function(loc) {
                    if (!element.tomselect.options[loc]) {
                        element.tomselect.addOption({
                            value: loc,
                            text: loc
                        });
                    }
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
            <button type="button" class="btn btn-outline-danger btn-sm btnEditRemove">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </div>
    `;

                $('#editLocationContainer').append(html);

                let select = $('#editLocationContainer .location-item:last .edit-location-select')[0];

                initEditTomSelect(select);

                if (location != "") {
                    if (!select.tomselect.options[location]) {
                        select.tomselect.addOption({
                            value: location,
                            text: location
                        });
                    }
                    select.tomselect.setValue(location);
                }

                editIndex++;
            }

            $(document).on('click', '.btnEditLocationStock', function() {

                let id = $(this).data('id');

                $('#editLocationContainer .location-item').each(function() {
                    let select = $(this).find('.edit-location-select')[0];
                    if (select && select.tomselect) {
                        select.tomselect.destroy();
                    }
                    $(this).remove();
                });

                editIndex = 0;

                $.ajax({
                    url: '/location-stock/' + id + '/edit',
                    type: 'GET',
                    success: function(res) {

                        $('#editLocationStockForm').attr('action', '/location-stock/' + id);

                        $('#edit_item_code_internal').text(res.item.item_code_internal ?? '-');
                        $('#edit_item_name').text(res.item.name ?? '-');
                        $('#edit_item_vendor').text(res.item.vendor ? res.item.vendor.name : '-');

                        res.item.locations.forEach(function(loc) {
                            addEditLocationRow(
                                loc.location_name,
                                loc.pivot.quantity
                            );
                        });

                        $('#editLocationStockModal').modal('show');
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
            // ==========================================================================================
        </script>

        <script>
            $('#editLocationStockForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),

                    beforeSend: function() {
                        $('#editLocationStockForm button[type="submit"]').prop('disabled', true);
                    },

                    success: function(res) {

                        $('#editLocationStockModal').modal('hide');

                        $('#editLocationStockForm button[type="submit"]').prop('disabled', false);

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

                        $('#editLocationStockForm button[type="submit"]').prop('disabled', false);

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
