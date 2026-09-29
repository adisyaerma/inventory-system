@extends('master')
@section('title', 'Staging Out')
@section('content')
    <style>
        .stat-card-clickable {
            cursor: pointer;
            transition: box-shadow .15s ease, border-color .15s ease;
        }

        .stat-card-clickable:hover {
            box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .1) !important;
        }

        .stat-card-clickable.stat-card-active {
            border: 1px solid var(--bs-primary) !important;
        }

        /* Select "Lokasi" (staging/packing/outbound) di dalam tabel */
        .select-staging-location {
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            padding: .3rem 1.75rem .3rem .8rem;
            border: 1px solid transparent;
            cursor: pointer;
            min-width: 125px;
            box-shadow: none !important;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, opacity .15s ease;
        }

        .select-staging-location:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15) !important;
        }

        .select-staging-location:disabled {
            opacity: .55;
            cursor: progress;
        }

        .select-staging-location.loc-empty {
            background-color: #f1f3f5;
            color: #6c757d;
            border-color: #dee2e6;
        }

        .select-staging-location.loc-staging {
            background-color: #fff3cd;
            color: #997404;
            border-color: #ffe69c;
        }

        .select-staging-location.loc-packing {
            background-color: #cfe2ff;
            color: #084298;
            border-color: #9ec5fe;
        }

        .select-staging-location.loc-outbound {
            background-color: #d1e7dd;
            color: #0f5132;
            border-color: #a3cfbb;
        }
    </style>

    <div class="row g-4 mb-4">
        <!-- Total Entry -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 stat-card-clickable" id="statCardAll" data-status="">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-primary-subtle text-primary me-4">
                        <i class="bi bi-card-list fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Total Entry</small>
                        <h4 class="fw-bold mb-1">
                            {{ number_format($totalEntry, 0, ',', '.') }}
                        </h4>
                        <small class="text-primary">Semua Data</small>
                    </div>

                </div>
            </div>
        </div>

        <!-- Total Qty -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-success-subtle text-success me-4">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Total Qty</small>
                        <h4 class="fw-bold mb-1">
                            {{ number_format($totalQty, 0, ',', '.') }}
                        </h4>
                        <small class="text-success">Unit Barang</small>
                    </div>

                </div>
            </div>
        </div>

        <!-- Sudah Picking -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 stat-card-clickable" id="statCardPicking" data-status="sudah_picking">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-info-subtle text-info me-4">
                        <i class="bi bi-box-arrow-up fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Sudah Picking</small>
                        <h4 class="fw-bold mb-1">
                            {{ number_format($sudahPicking, 0, ',', '.') }}
                        </h4>
                        <small class="text-info">Jumlah Entry</small>
                    </div>

                </div>
            </div>
        </div>

        <!-- Sudah Dikirim -->
        <div class="col-md-6 col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 stat-card-clickable" id="statCardDikirim" data-status="sudah_dikirim">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-warning-subtle text-warning me-4">
                        <i class="bi bi-truck fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Sudah Dikirim</small>
                        <h4 class="fw-bold mb-1">
                            {{ number_format($sudahDikirim, 0, ',', '.') }}
                        </h4>
                        <small class="text-warning">Jumlah Entry</small>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <div class="card">
        <div class="card-header">
            <div class="float-start">
                <h4 class="mb-0">Staging Out</h4>
                <small class="text-muted">Kelola data staging out</small>
            </div>
            <div class="float-end mt-3">
                <button type="button" class="btn-sm btn border-secondary bg-white border me-1" data-bs-toggle="modal"
                    data-bs-target="#importStagingOutModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Import</span>
                </button>

                <button type="button" class="btn-sm btn border-secondary bg-white border me-1" data-bs-toggle="modal"
                    data-bs-target="#importStagingOutStockModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4M4 6v12a2 2 0 0 0 2 2h14v-4M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4z" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Import dari Stock</span>
                </button>

                <a href="{{ route('stagings-out.export') }}" class="btn-sm btn border-secondary bg-white border me-1"
                    id="exportBtn">
                    <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor"
                            d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Export</span>
                </a>

                <button data-bs-toggle="modal" data-bs-target="#addStagingOut" type="button"
                    class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>

                {{-- ================= MODAL IMPORT ================= --}}
                <div class="modal fade" id="importStagingOutModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form action="{{ route('stagings-out.import') }}" method="POST" enctype="multipart/form-data">
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
                                                Impor Data Staging Out
                                            </h5>

                                            <small class="text-muted">
                                                Import data staging out dari file Excel
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

                                            <a href="{{ route('stagings-out.template') }}"
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
                                                <input type="file" id="excelFileStagingOut" name="file"
                                                    accept=".xlsx,.xls" hidden>

                                                <div class="border border-2 border-primary-subtle rounded p-5 text-center">

                                                    <svg xmlns="http://www.w3.org/2000/svg" class="fs-4 mb-1"
                                                        width="1em" height="1em" viewBox="0 0 24 24">
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

                                                    <div id="selectedFileStagingOut" class="mt-2"
                                                        style="display:none;">
                                                        <small class="text-success fw-semibold">
                                                            ✓ File dipilih: <span id="fileNameStagingOut"></span>
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
                {{-- ================= MODAL IMPORT DARI STOCK ================= --}}
                <div class="modal fade" id="importStagingOutStockModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form action="{{ route('stagings-out.import-stock') }}" method="POST" enctype="multipart/form-data">
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
                                                Impor Staging Out dari Stock
                                            </h5>

                                            <small class="text-muted">
                                                Import staging out yang barangnya diambil dari stok -- lokasi &amp; lot dicari otomatis
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

                                            <a href="{{ route('stagings-out.template') }}"
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
                                                Upload file Excel (.xlsx, .xls) sesuai template. Template-nya
                                                sama seperti import biasa (tidak ada kolom Lokasi) -- lokasi
                                                &amp; lot diambil otomatis dari data stok per Kode Barang.
                                                Baris yang barangnya ada di lebih dari 1 lokasi/lot akan
                                                dilewati dan dilaporkan nomor barisnya, silakan input baris
                                                itu manual lewat form Tambah dengan sumber "Stock".
                                            </p>

                                            <label class="upload-box w-100">
                                                <input type="file" id="excelFileStagingOutStock" name="file"
                                                    accept=".xlsx,.xls" hidden>

                                                <div class="border border-2 border-primary-subtle rounded p-5 text-center">

                                                    <svg xmlns="http://www.w3.org/2000/svg" class="fs-4 mb-1"
                                                        width="1em" height="1em" viewBox="0 0 24 24">
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

                                                    <div id="selectedFileStagingOutStock" class="mt-2"
                                                        style="display:none;">
                                                        <small class="text-success fw-semibold">
                                                            ✓ File dipilih: <span id="fileNameStagingOutStock"></span>
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
                                        Import Data Stock
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="addStagingOut" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="addStagingOutLabel" aria-hidden="true">

                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form action="{{ route('stagings-out.store') }}" method="POST" id="formStagingOut">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Item Staging Out
                                        </h4>

                                        <small class="text-muted">
                                            Tambahkan data pengiriman barang keluar (SO).
                                        </small>

                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body p-4">

                                    {{-- ================= SUMBER BARANG ================= --}}
                                    <div class="mb-4">

                                        <label class="form-label fw-semibold d-block mb-2">
                                            Sumber Barang
                                        </label>

                                        <div class="row g-2">

                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="source_type"
                                                    id="addSourceExternal" value="external" autocomplete="off" checked>
                                                <label class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3"
                                                    for="addSourceExternal">
                                                    <i class="bx bx-package fs-3 mb-1"></i>
                                                    Barang Eksternal
                                                    <small class="text-muted fw-normal">Bukan dari stok gudang</small>
                                                </label>
                                            </div>

                                            <div class="col-6">
                                                <input type="radio" class="btn-check" name="source_type"
                                                    id="addSourceStock" value="stock" autocomplete="off">
                                                <label class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3"
                                                    for="addSourceStock">
                                                    <i class="bx bx-archive-in fs-3 mb-1"></i>
                                                    Dari Stok
                                                    <small class="text-muted fw-normal">Ambil dari lokasi stok</small>
                                                </label>
                                            </div>

                                        </div>

                                    </div>

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
                                                        Lengkapi informasi barang yang akan dikirim.
                                                    </small>

                                                </div>

                                                <div class="card-body px-4 pb-4">


                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            No. SO
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-receipt"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="so_number" placeholder="Contoh: SO-2026-0001">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Customer
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-buildings"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="customer" placeholder="Contoh: PT. ABC">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Kode Barang
                                                        </label>

                                                        <select id="addItemSelect" placeholder="Cari kode / nama barang..."></select>

                                                        <input type="hidden" name="item_id" id="addItemId">

                                                    </div>

                                                    <div id="addStockFields" class="d-none">

                                                        <div class="mb-3">

                                                            <label class="form-label fw-semibold">
                                                                Lokasi
                                                            </label>

                                                            <select class="form-select form-select-sm"
                                                                name="location_id" id="addLocationSelect" disabled>
                                                                <option value="">-- Pilih Lokasi --</option>
                                                            </select>

                                                        </div>

                                                        <div class="mb-3">

                                                            <label class="form-label fw-semibold">
                                                                Lot
                                                            </label>

                                                            <select class="form-select form-select-sm" name="lot"
                                                                id="addLotSelect" disabled>
                                                                <option value="">-- Pilih Lot --</option>
                                                            </select>

                                                            <small class="text-success d-block mt-1"
                                                                id="addQtyMaxHint"></small>

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Line Item
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-package"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="line_item"
                                                                placeholder="Contoh: Nord-Lock Steel Washer">

                                                        </div>

                                                    </div>

                                                    <div class="">

                                                        <label class="form-label fw-semibold">
                                                            Qty
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-cube-alt"></i>
                                                            </span>

                                                            <input type="number" class="form-control form-control-sm"
                                                                name="qty" id="addQtyInput" min="0" placeholder="0">

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        {{-- ================= RIGHT ================= --}}
                                        <div class="col-lg-6">

                                            <div class="card shadow border-0 rounded-4 h-100">

                                                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                                    <h5 class="fw-bold mb-0">
                                                        <i class="bi bi-truck text-success me-2"></i>
                                                        Jadwal &amp; Pengiriman
                                                    </h5>

                                                    <small class="text-muted">
                                                        Tentukan jadwal instruksi, picking, dan pengiriman.
                                                    </small>

                                                </div>

                                                <div class="card-body px-4 pb-4">

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Tgl Instruksi Kirim
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-calendar"></i>
                                                            </span>

                                                            <input type="date" class="form-control form-control-sm"
                                                                name="delivery_instruction_date"
                                                                value="{{ now()->format('Y-m-d') }}">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Tgl Picking
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-calendar-check"></i>
                                                            </span>

                                                            <input type="date" class="form-control form-control-sm"
                                                                name="picking_date">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            No. DO
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-file"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="do_number" placeholder="Opsional">

                                                        </div>

                                                    </div>

                                                    <div>

                                                        <label class="form-label fw-semibold">
                                                            Tgl Resi Pengiriman
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-receipt"></i>
                                                            </span>

                                                            <input type="date" class="form-control form-control-sm"
                                                                name="delivery_receipt_date">

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

                                        Simpan Staging Out

                                    </button>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive text-nowrap">

            <div class="px-3 pt-3">

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

                    /* ===== Rentang tanggal ===== */
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

                    /* ===== Toggle "hanya yang telat" ===== */
                    .overdue-toggle {
                        display: flex;
                        align-items: center;
                        height: calc(1.5em + .5rem + 2px);
                        padding: 0 .75rem;
                        background: #fff;
                        border: 1px solid #dfe3e9;
                        border-radius: .5rem;
                        transition: background-color .15s ease, border-color .15s ease;
                    }

                    .overdue-toggle:has(#filterOverdue:checked) {
                        background: var(--bs-danger-bg-subtle, #f8d7da);
                        border-color: var(--bs-danger-border-subtle, #f1aeb5);
                    }

                    .overdue-toggle .form-check {
                        margin: 0;
                    }

                    .overdue-toggle .form-check-label {
                        color: #6c757d;
                        font-weight: 500;
                        white-space: nowrap;
                    }

                    .overdue-toggle:has(#filterOverdue:checked) .form-check-label {
                        color: var(--bs-danger);
                    }

                    @media (max-width: 1199.98px) {
                        .filter-toolbar {
                            grid-template-columns: repeat(3, 1fr);
                        }
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

                @if ($activeFilter === 'today' || in_array($activeStatus, ['terlambat', 'menunggu_picking', 'siap_kirim', 'selesai']))
                    <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3" id="activeFilterBanner">
                        <span>
                            <i class="bx bx-error me-1"></i>
                            @if ($activeFilter === 'today')
                                Menampilkan pengirimaan yang dijadwalkan hari ini.
                            @elseif ($activeStatus === 'terlambat')
                                Menampilkan staging out yang melewati tanggal kirim.
                            @elseif ($activeStatus === 'menunggu_picking')
                                Menampilkan staging out yang belum mulai proses picking.
                            @elseif ($activeStatus === 'siap_kirim')
                                Menampilkan staging out yang siap dikirim.
                            @elseif ($activeStatus === 'selesai')
                                Menampilkan staging out yang sudah selesai dikirim.
                            @endif
                        </span>
                        <button type="button" class="btn-close" id="clearActiveFilterBtn" aria-label="Hapus filter"></button>
                    </div>
                @endif

                <div class="filter-toolbar-card">

                    <div class="filter-toolbar-eyebrow">
                        <div class="filter-toolbar-eyebrow-label">
                            <i class="bi bi-sliders"></i>
                            <span>Filter Data</span>
                        </div>

                        <button type="button" class="btn-sm btn border-secondary bg-white border" id="resetFilterInline"
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
                                    placeholder="No. SO, customer, kode barang...">
                            </div>
                        </div>

                        <!-- Filter Status Pengiriman -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterStatus">Status</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-truck text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="belum_picking">Belum Picking</option>
                                    <option value="sudah_picking">Sudah Picking</option>
                                    <option value="belum_dikirim">Belum Dikirim</option>
                                    <option value="sudah_dikirim">Sudah Dikirim</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Customer -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterCustomer">Customer</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterCustomer">
                                    <option value="">Semua Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer }}">{{ $customer }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filter Lokasi -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterLocation">Lokasi</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterLocation">
                                    <option value="">Semua Lokasi</option>
                                    <option value="staging">Staging</option>
                                    <option value="packing">Packing</option>
                                    <option value="outbound">Outbound</option>
                                    <option value="belum_diisi">Belum Diisi</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pilihan jenis tanggal yang mau difilter -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterDateType">Jenis Tanggal</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterDateType">
                                    <option value="delivery_instruction_date">Tgl Instruksi Kirim</option>
                                    <option value="picking_date">Tgl Picking</option>
                                    <option value="delivery_receipt_date">Tgl Resi Pengiriman</option>
                                </select>
                            </div>
                        </div>

                        <!-- Rentang Tanggal (mengikuti jenis tanggal yang dipilih) -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterDateRange">Rentang Tanggal</label>
                            <div class="date-range-wrapper">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-calendar-range text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="filterDateRange"
                                        placeholder="Pilih rentang tanggal" title="Rentang Tanggal" readonly
                                        autocomplete="off">
                                </div>

                                <div class="date-range-panel" id="dateRangePanel">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Dari</label>
                                        <input type="date" class="form-control form-control-sm" id="filterStartDate">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="filterEndDate">
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-primary w-100" id="dateRangeApply">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div id="bulkActionBar"
                class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                <span><span id="selectedCount">0</span> data dipilih</span>
                <button type="button" id="btnBulkDelete" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
            </div>

            <table class="table table-bordered" id="stagingOut">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>No. SO</th>
                        <th>Customer</th>
                        <th>Item Code</th>
                        <th>Line Item</th>
                        <th>Qty</th>
                        <th>Tgl Instruksi Kirim</th>
                        <th>Tgl Picking</th>
                        <th>Lokasi</th>
                        <th>No. DO</th>
                        <th>Tgl Resi Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                id="tableFooter">

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

    <div class="modal fade" id="editStagingOutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form id="formEditStagingOut">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Item Staging Out
                            </h4>

                            <small class="text-muted">
                                Ubah data pengiriman barang keluar (SO).
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body p-4">

                        {{-- ================= SUMBER BARANG ================= --}}
                        <div class="mb-4">

                            <label class="form-label fw-semibold d-block mb-2">
                                Sumber Barang
                            </label>

                            <div class="row g-2">

                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="source_type"
                                        id="editSourceExternal" value="external" autocomplete="off" checked>
                                    <label class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3"
                                        for="editSourceExternal">
                                        <i class="bx bx-package fs-3 mb-1"></i>
                                        Barang Eksternal
                                        <small class="text-muted fw-normal">Bukan dari stok gudang</small>
                                    </label>
                                </div>

                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="source_type"
                                        id="editSourceStock" value="stock" autocomplete="off">
                                    <label class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3"
                                        for="editSourceStock">
                                        <i class="bx bx-archive-in fs-3 mb-1"></i>
                                        Dari Stok
                                        <small class="text-muted fw-normal">Ambil dari lokasi stok</small>
                                    </label>
                                </div>

                            </div>

                        </div>

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
                                            Lengkapi informasi barang yang dikirim.
                                        </small>

                                    </div>

                                    <div class="card-body px-4 pb-4">

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                No. SO
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-receipt"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="so_number" id="editSoNumber">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Customer
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-buildings"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="customer" id="editCustomer">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Kode Barang
                                            </label>

                                            <select id="editItemSelect" placeholder="Cari kode / nama barang..."></select>

                                            <input type="hidden" name="item_id" id="editItemId">

                                        </div>

                                        <div id="editStockFields" class="d-none">

                                            <div class="mb-3">

                                                <label class="form-label fw-semibold">
                                                    Lokasi
                                                </label>

                                                <select class="form-select form-select-sm" name="location_id"
                                                    id="editLocationSelect" disabled>
                                                    <option value="">-- Pilih Lokasi --</option>
                                                </select>

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label fw-semibold">
                                                    Lot
                                                </label>

                                                <select class="form-select form-select-sm" name="lot"
                                                    id="editLotSelect" disabled>
                                                    <option value="">-- Pilih Lot --</option>
                                                </select>

                                                <small class="text-success d-block mt-1" id="editQtyMaxHint"></small>

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Line Item
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-package"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="line_item" id="editLineItem">

                                            </div>

                                        </div>

                                        <div class="">

                                            <label class="form-label fw-semibold">
                                                Qty
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-cube-alt"></i>
                                                </span>

                                                <input type="number" class="form-control form-control-sm" name="qty"
                                                    min="0" id="editQty">

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- ================= RIGHT ================= --}}
                            <div class="col-lg-6">

                                <div class="card shadow border-0 rounded-4 h-100">

                                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-truck text-success me-2"></i>
                                            Jadwal &amp; Pengiriman
                                        </h5>

                                        <small class="text-muted">
                                            Tentukan jadwal instruksi, picking, dan pengiriman.
                                        </small>

                                    </div>

                                    <div class="card-body px-4 pb-4">

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Tgl Instruksi Kirim
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-calendar"></i>
                                                </span>

                                                <input type="date" class="form-control form-control-sm"
                                                    name="delivery_instruction_date" id="editDeliveryInstructionDate">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Tgl Picking
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-calendar-check"></i>
                                                </span>

                                                <input type="date" class="form-control form-control-sm"
                                                    name="picking_date" id="editPickingDate">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                No. DO
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-file"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="do_number" id="editDoNumber" placeholder="Opsional">

                                            </div>

                                        </div>

                                        <div>

                                            <label class="form-label fw-semibold">
                                                Tgl Resi Pengiriman
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-receipt"></i>
                                                </span>

                                                <input type="date" class="form-control form-control-sm"
                                                    name="delivery_receipt_date" id="editDeliveryReceiptDate">

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

                        <button type="submit" class="btn  btn-primary px-4 btnSaveEdit">
                            <i class="bx bx-save me-1"></i>
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- ================= MODAL KONFIRMASI TANGGAL PICKING ================= --}}
    <div class="modal fade" id="confirmPickingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-md">

            <div class="modal-content ">

                <form id="formConfirmPicking">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="confirmPickingId">
                    <input type="hidden" name="source_type" id="confirmPickingSourceType">
                    <input type="hidden" name="item_id" id="confirmPickingItemId">
                    <input type="hidden" name="location_id" id="confirmPickingLocationId">
                    <input type="hidden" name="qty" id="confirmPickingQty">
                    <input type="hidden" name="lot" id="confirmPickingLot">


                    {{-- HEADER --}}
                    <div class="modal-header border-0 px-4 pt-4 pb-3">

                        <div class="d-flex align-items-center gap-3">

                            {{-- ICON --}}
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                style="
                                width: 48px;
                                height: 48px;
                                background: rgba(var(--bs-primary-rgb), .12);
                                color: var(--bs-primary);
                            ">

                                <i class="bx bx-package fs-3"></i>

                            </div>


                            {{-- TITLE --}}
                            <div>

                                <h5 class="modal-title fw-bold mb-1">
                                    Konfirmasi Picking
                                </h5>

                                <p class="mb-0 text-muted small">
                                    Tandai barang sebagai sudah selesai picking
                                </p>

                            </div>

                        </div>


                        {{-- CLOSE --}}
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    {{-- BODY --}}
                    <div class="modal-body px-4 pt-2 pb-3">


                        {{-- INFO --}}
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-4" style="background: #f8f9fa;">

                            <div class="text-primary mt-1">

                                <i class="bx bx-info-circle fs-4"></i>

                            </div>


                            <div>

                                <div class="fw-semibold mb-1">
                                    Pastikan tanggal picking
                                </div>

                                <div class="text-muted small">
                                    Pilih tanggal ketika barang selesai
                                    diambil dari lokasi penyimpanan.
                                </div>

                            </div>

                        </div>


                        {{-- TANGGAL PICKING --}}
                        <div class="mb-2">

                            <label for="confirmPickingDate" class="form-label fw-semibold">

                                Tanggal Picking

                            </label>


                            <div class="input-group">

                                <span class="input-group-text bg-transparent">

                                    <i class="bx bx-calendar-check text-primary"></i>

                                </span>


                                <input type="date" class="form-control" name="picking_date" id="confirmPickingDate"
                                    required>

                            </div>


                            <div class="form-text">

                                <i class="bx bx-info-circle me-1"></i>

                                Secara otomatis menggunakan tanggal hari ini.

                            </div>

                        </div>

                    </div>


                    {{-- FOOTER --}}
                    <div class="modal-footer border-0 px-4 pb-4 pt-2">


                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">

                            Batal

                        </button>


                        <button type="submit" class="btn btn-primary px-4 btnSaveConfirmPicking">

                            <i class="bx bx-check-circle me-1"></i>

                            Konfirmasi Picking

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    {{-- ================= MODAL KONFIRMASI TANGGAL RESI PENGIRIMAN ================= --}}
    <div class="modal fade" id="confirmDeliveryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-md">

            <div class="modal-content ">

                <form id="formConfirmDelivery">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="confirmDeliveryId">
                    <input type="hidden" name="source_type" id="confirmDeliverySourceType">
                    <input type="hidden" name="item_id" id="confirmDeliveryItemId">
                    <input type="hidden" name="location_id" id="confirmDeliveryLocationId">
                    <input type="hidden" name="qty" id="confirmDeliveryQty">
                    <input type="hidden" name="lot" id="confirmDeliveryLot">


                    {{-- HEADER --}}
                    <div class="modal-header border-0 px-4 pt-4 pb-3">

                        <div class="d-flex align-items-center gap-3">

                            {{-- ICON --}}
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                style="
                                width: 48px;
                                height: 48px;
                                background: rgba(var(--bs-success-rgb), .12);
                                color: var(--bs-success);
                            ">

                                <i class="bx bxs-truck fs-3"></i>

                            </div>


                            {{-- TITLE --}}
                            <div>

                                <h5 class="modal-title fw-bold mb-1">
                                    Konfirmasi Pengiriman
                                </h5>

                                <p class="mb-0 text-muted small">
                                    Tandai barang sebagai sudah dikirim
                                </p>

                            </div>

                        </div>


                        {{-- CLOSE --}}
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    {{-- BODY --}}
                    <div class="modal-body px-4 pt-2 pb-3">


                        {{-- INFO --}}
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-4" style="background: #f8f9fa;">

                            <div class="text-success mt-1">

                                <i class="bx bx-info-circle fs-4"></i>

                            </div>


                            <div>

                                <div class="fw-semibold mb-1">
                                    Pastikan tanggal resi pengiriman
                                </div>

                                <div class="text-muted small">
                                    Pilih tanggal pada resi ketika barang dikirim.
                                    Setelah dikonfirmasi, data dipindahkan ke history.
                                </div>

                            </div>

                        </div>


                        {{-- TANGGAL RESI PENGIRIMAN --}}
                        <div class="mb-2">

                            <label for="confirmDeliveryDate" class="form-label fw-semibold">

                                Tanggal Resi Pengiriman

                            </label>


                            <div class="input-group">

                                <span class="input-group-text bg-transparent">

                                    <i class="bx bx-calendar-check text-success"></i>

                                </span>


                                <input type="date" class="form-control" name="delivery_receipt_date"
                                    id="confirmDeliveryDate" required>

                            </div>


                            <div class="form-text">

                                <i class="bx bx-info-circle me-1"></i>

                                Secara otomatis menggunakan tanggal hari ini.

                            </div>

                        </div>

                    </div>


                    {{-- FOOTER --}}
                    <div class="modal-footer border-0 px-4 pb-4 pt-2">


                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">

                            Batal

                        </button>


                        <button type="submit" class="btn btn-success px-4 btnSaveConfirmDelivery">

                            <i class="bx bx-check-circle me-1"></i>

                            Konfirmasi Kirim

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    @push('script')
        <style>
            .icon-box {
                border-radius: 18px;
                width: 64px;
                height: 64px;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
            }

            .icon-box i {
                font-size: 30px;
            }

            .upload-box {
                cursor: pointer;
                transition: all .2s ease;
            }

            .upload-box:hover .border {
                background: #f8fafc;
                border-color: #0d6efd !important;
            }

            /* ===== Tombol konfirmasi tanggal picking (tabel) ===== */
            .btn-confirm-picking {
                background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
                color: #fff;
                border: 0;
                border-radius: 50px;
                padding: .35rem .9rem;
                font-weight: 600;
                font-size: .78rem;
                white-space: nowrap;
                box-shadow: 0 .15rem .4rem rgba(67, 97, 238, .35);
                transition: transform .15s ease, box-shadow .15s ease;
            }

            .btn-confirm-picking:hover {
                color: #fff;
                transform: translateY(-1px);
                box-shadow: 0 .3rem .6rem rgba(67, 97, 238, .45);
            }

            .btn-confirm-picking svg {
                vertical-align: -2px;
            }

            /* ===== Modal konfirmasi tanggal picking ===== */
            .confirm-picking-modal .confirm-picking-header {
                background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
            }

            .confirm-picking-modal .confirm-picking-icon {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, .18);
                backdrop-filter: blur(2px);
            }

            .confirm-picking-modal input[type="date"]:focus {
                background-color: #fff !important;
                box-shadow: none;
            }

            /* ===== Tombol konfirmasi tanggal kirim (tabel) ===== */
            .btn-confirm-delivery {
                background: linear-gradient(135deg, #ffb703 0%, #fb8500 100%);
                color: #fff;
                border: 0;
                border-radius: 50px;
                padding: .35rem .9rem;
                font-weight: 600;
                font-size: .78rem;
                white-space: nowrap;
                box-shadow: 0 .15rem .4rem rgba(251, 133, 0, .35);
                transition: transform .15s ease, box-shadow .15s ease;
            }

            .btn-confirm-delivery:hover {
                color: #fff;
                transform: translateY(-1px);
                box-shadow: 0 .3rem .6rem rgba(251, 133, 0, .45);
            }

            .btn-confirm-delivery svg {
                vertical-align: -2px;
            }

            /* ===== Modal konfirmasi tanggal kirim ===== */
            .confirm-delivery-modal .confirm-delivery-header {
                background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            }

            .confirm-delivery-modal .confirm-delivery-icon {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, .18);
                backdrop-filter: blur(2px);
            }

            .confirm-delivery-modal input[type="date"]:focus {
                background-color: #fff !important;
                box-shadow: none;
            }
        </style>

        <script>
            // ============ VARIABEL GLOBAL ============
            let table;
            let appliedStartDate = '';
            let appliedEndDate = '';
            // Filter/status yang datang dari URL (mis. link notifikasi
            // dashboard ?filter=today atau ?status=terlambat). Nilainya
            // dipakai sampai dropdown status diubah manual atau filter
            // direset, supaya link dari dashboard benar-benar memfilter
            // tabel ini, bukan cuma nyasar ke halaman tanpa filter.
            let activeUrlFilter = new URLSearchParams(window.location.search).get('filter') || '';
            let activeUrlStatus = new URLSearchParams(window.location.search).get('status') || '';
        </script>

        <script>
            $(document).ready(function() {

                table = $('#stagingOut').DataTable({

                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],

                    ajax: {
                        url: "{{ route('stagings-out.data') }}",
                        data: function(d) {
                            d.start_date = appliedStartDate;
                            d.end_date = appliedEndDate;
                            d.status = $('#filterStatus').val() || activeUrlStatus;
                            d.customer = $('#filterCustomer').val();
                            d.staging_location = $('#filterLocation').val();
                            d.date_type = $('#filterDateType').val();
                            d.overdue = $('#filterOverdue').is(':checked') ? 1 : 0;
                            d.filter = activeUrlFilter;
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
                            data: 'so_number',
                            name: 'so_number'
                        },
                        {
                            data: 'customer',
                            name: 'customer'
                        },
                        {
                            data: 'item_code',
                            name: 'item_code'
                        },
                        {
                            data: 'line_item',
                            name: 'line_item'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'delivery_instruction_date',
                            name: 'delivery_instruction_date'
                        },
                        {
                            data: 'picking_date',
                            name: 'picking_date'
                        },
                        {
                            data: 'staging_location',
                            name: 'staging_location'
                        },
                        {
                            data: 'do_number',
                            name: 'do_number'
                        },
                        {
                            data: 'delivery_receipt_date',
                            name: 'delivery_receipt_date'
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
                            targets: [3, 4],
                            className: "text-wrap",
                            width: "220px"
                        },
                        {
                            targets: 1,
                            width: "80px"
                        },
                        {
                            targets: 5,
                            className: "text-center"
                        },
                        {
                            targets: [8, 9],
                            className: "text-center"
                        },
                        {
                            targets: [11, 12],
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
                    const $info = $('#stagingOut_info');
                    if ($info.length && !$('#lengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#lengthWrapper');
                    }

                    const $paginate = $('#stagingOut_paginate');
                    if ($paginate.length && !$('#tableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#tableFooter');
                    }
                }

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                    updateExportUrl();
                });

                $('#filterStatus').on('change', function() {
                    activeUrlStatus = '';
                    setActiveStatCard($(this).val());
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterCustomer').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterLocation').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterDateType').on('change', function() {
                    // kalau rentang tanggal sudah diisi, reload pakai kolom tanggal yang baru
                    if (appliedStartDate || appliedEndDate) {
                        table.ajax.reload();
                        updateExportUrl();
                    }
                });

                $('#filterOverdue').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                // Klik card ringkasan langsung filter status
                $('.stat-card-clickable').on('click', function() {
                    const status = $(this).data('status') || '';
                    $('#filterStatus').val(status).trigger('change');
                });

                setActiveStatCard('');

                function setActiveStatCard(status) {
                    $('.stat-card-clickable').removeClass('stat-card-active');
                    if (status === 'sudah_picking') {
                        $('#statCardPicking').addClass('stat-card-active');
                    } else if (status === 'sudah_dikirim') {
                        $('#statCardDikirim').addClass('stat-card-active');
                    } else if (status === '') {
                        $('#statCardAll').addClass('stat-card-active');
                    }
                    // status 'belum_picking' tidak punya card khusus, jadi tidak ada yang di-highlight
                }

                $('#customLength').change(function() {
                    table.page.len($(this).val()).draw();
                });

                $('#resetFilterInline').click(function() {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#filterDateRange').val('');
                    $('#customSearch').val('');
                    $('#filterStatus').val('');
                    setActiveStatCard('');
                    $('#filterCustomer').val('');
                    $('#filterLocation').val('');
                    $('#filterDateType').val('delivery_instruction_date');
                    $('#filterOverdue').prop('checked', false);

                    appliedStartDate = '';
                    appliedEndDate = '';

                    activeUrlFilter = '';
                    activeUrlStatus = '';
                    window.history.replaceState({}, '', window.location.pathname);
                    $('#activeFilterBanner').remove();

                    table.search('').draw();
                    updateExportUrl();
                    table.ajax.reload();
                });

                $('#clearActiveFilterBtn').on('click', function() {
                    activeUrlFilter = '';
                    activeUrlStatus = '';
                    window.history.replaceState({}, '', window.location.pathname);
                    $('#activeFilterBanner').remove();
                    table.ajax.reload();
                });

                $('#excelFileStagingOut').on('change', function() {
                    if (this.files.length > 0) {
                        $('#fileNameStagingOut').text(this.files[0].name);
                        $('#selectedFileStagingOut').show();
                    } else {
                        $('#selectedFileStagingOut').hide();
                    }
                });

                $('#excelFileStagingOutStock').on('change', function() {
                    if (this.files.length > 0) {
                        $('#fileNameStagingOutStock').text(this.files[0].name);
                        $('#selectedFileStagingOutStock').show();
                    } else {
                        $('#selectedFileStagingOutStock').hide();
                    }
                });

                // ================= SUMBER BARANG: Eksternal vs Stok =================
                // Mengatur toggle radio "Eksternal"/"Stok", menampilkan field
                // Lokasi & Lot ketika "Stok" dipilih, mengisi keduanya secara
                // berjenjang (item -> lokasi -> lot), dan membatasi input Qty
                // supaya tidak melebihi sisa stok pada lot yang dipilih.
                function setupSourceType(cfg) {

                    let $stockFields = $(cfg.stockFieldsSel);
                    let $extRadio = $(cfg.extRadioSel);
                    let $stockRadio = $(cfg.stockRadioSel);
                    let $locationSelect = $(cfg.locationSelectSel);
                    let $lotSelect = $(cfg.lotSelectSel);
                    let $qtyInput = $(cfg.qtyInputSel);
                    let $qtyHint = $(cfg.qtyHintSel);

                    function resetLot() {
                        $lotSelect.html('<option value="">-- Pilih Lot --</option>').prop('disabled', true);
                        $qtyHint.text('');
                        $qtyInput.removeAttr('max');
                    }

                    function resetLocationLot() {
                        $locationSelect.html('<option value="">-- Pilih Lokasi --</option>').prop(
                            'disabled', true);
                        resetLot();
                    }

                    function toggleVisibility() {
                        let isStock = $stockRadio.is(':checked');
                        $stockFields.toggleClass('d-none', !isStock);

                        if (!isStock) {
                            resetLocationLot();
                        }
                    }

                    $extRadio.add($stockRadio).on('change', toggleVisibility);

                    function loadLocations(itemId) {
                        resetLocationLot();

                        if (!itemId) {
                            return $.Deferred().resolve([]).promise();
                        }

                        return $.getJSON("{{ route('stagings-out.search-location-for-item') }}", {
                            item_id: itemId
                        }).then(function(res) {
                            $locationSelect.prop('disabled', false);

                            res.forEach(function(loc) {
                                $locationSelect.append($('<option>', {
                                    value: loc.id,
                                    text: loc.text + ' (stok: ' + loc.total_quantity + ')'
                                }));
                            });

                            return res;
                        });
                    }

                    function loadLots(itemId, locationId) {
                        resetLot();

                        if (!itemId || !locationId) {
                            return $.Deferred().resolve([]).promise();
                        }

                        return $.getJSON("{{ route('stagings-out.search-lot-for-item-location') }}", {
                            item_id: itemId,
                            location_id: locationId
                        }).then(function(res) {
                            $lotSelect.prop('disabled', false);

                            res.forEach(function(lotRow) {
                                $lotSelect.append($('<option>', {
                                    value: lotRow.id,
                                    text: lotRow.text + ' (stok: ' + lotRow.quantity + ')',
                                    'data-qty': lotRow.quantity
                                }));
                            });

                            // Kalau cuma ada 1 lot, langsung pilihkan biar user
                            // ga perlu klik lagi (banyak item tidak pakai lot).
                            if (res.length === 1) {
                                $lotSelect.val(res[0].id).trigger('change');
                            }

                            return res;
                        });
                    }

                    $locationSelect.on('change', function() {
                        loadLots(cfg.getItemId(), $(this).val());
                    });

                    $lotSelect.on('change', function() {
                        let qty = $(this).find(':selected').data('qty');

                        if (qty !== undefined && qty !== '' && qty !== null) {
                            $qtyInput.attr('max', qty);
                            $qtyHint.text('Stok tersedia: ' + qty);
                        } else {
                            $qtyInput.removeAttr('max');
                            $qtyHint.text('');
                        }
                    });

                    return {
                        loadLocations: loadLocations,
                        loadLots: loadLots,
                        reset: function() {
                            $extRadio.prop('checked', true);
                            resetLocationLot();
                            $stockFields.addClass('d-none');
                        }
                    };
                }

                let addSourceType = setupSourceType({
                    stockFieldsSel: '#addStockFields',
                    extRadioSel: '#addSourceExternal',
                    stockRadioSel: '#addSourceStock',
                    locationSelectSel: '#addLocationSelect',
                    lotSelectSel: '#addLotSelect',
                    qtyInputSel: '#addQtyInput',
                    qtyHintSel: '#addQtyMaxHint',
                    getItemId: function() {
                        return $('#addItemId').val();
                    }
                });

                let editSourceType = setupSourceType({
                    stockFieldsSel: '#editStockFields',
                    extRadioSel: '#editSourceExternal',
                    stockRadioSel: '#editSourceStock',
                    locationSelectSel: '#editLocationSelect',
                    lotSelectSel: '#editLotSelect',
                    qtyInputSel: '#editQty',
                    qtyHintSel: '#editQtyMaxHint',
                    getItemId: function() {
                        return $('#editItemId').val();
                    }
                });

                // ================= BARANG (TomSelect dari tabel items) =================
                function initItemSelect(selectId, idFieldId, onItemChange) {

                    return new TomSelect(selectId, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: ['text'],
                        preload: true,
                        create: false,
                        maxOptions: 20,
                        placeholder: 'Cari kode / nama barang...',

                        load: function(query, callback) {
                            $.ajax({
                                url: "{{ route('stagings-out.search-stock') }}",
                                type: 'GET',
                                data: {
                                    q: query
                                },
                                success: function(res) {
                                    callback(res);
                                },
                                error: function() {
                                    callback();
                                }
                            });
                        },

                        onChange: function(value) {
                            let data = this.options[value];
                            $(idFieldId).val(data ? data.id : '');

                            if (typeof onItemChange === 'function') {
                                onItemChange(data ? data.id : '');
                            }
                        }
                    });
                }

                let addItemSelect = initItemSelect('#addItemSelect', '#addItemId', function(itemId) {
                    addSourceType.loadLocations(itemId);
                });
                let editItemSelect = initItemSelect('#editItemSelect', '#editItemId', function(itemId) {
                    editSourceType.loadLocations(itemId);
                });

                $('#addStagingOut').on('hidden.bs.modal', function() {
                    addItemSelect.clear();
                    $('#addItemId').val('');
                    addSourceType.reset();
                });

                // ================= TAMBAH (AJAX) =================
                $(document).on('submit', '#formStagingOut', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    // Validasi ringan di sisi client untuk sumber "Stok" —
                    // validasi yang sesungguhnya (dan anti race-condition)
                    // tetap dilakukan di server pada StagingOutController::store().
                    if ($('#addSourceStock').is(':checked')) {

                        if (!$('#addItemId').val()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Barang belum dipilih',
                                text: 'Pilih barang terlebih dahulu untuk sumber stok.'
                            });
                            return;
                        }

                        if (!$('#addLocationSelect').val()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Lokasi belum dipilih',
                                text: 'Pilih lokasi yang memiliki stok barang ini.'
                            });
                            return;
                        }

                        let qtyVal = parseFloat($('#addQtyInput').val() || 0);
                        let maxVal = parseFloat($('#addQtyInput').attr('max'));

                        if (!qtyVal || qtyVal <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Qty belum diisi',
                                text: 'Qty wajib diisi dan lebih dari 0.'
                            });
                            return;
                        }

                        if (!isNaN(maxVal) && qtyVal > maxVal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Qty melebihi stok',
                                text: 'Sisa stok pada lot ini hanya ' + maxVal + '.'
                            });
                            return;
                        }
                    }

                    $.ajax({
                        url: "{{ route('stagings-out.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        success: function(response) {

                            $('#addStagingOut').modal('hide');
                            $('#formStagingOut')[0].reset();
                            addItemSelect.clear();
                            $('#addItemId').val('');
                            addSourceType.reset();

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

                        url: "{{ route('stagings-out.edit', ':id') }}".replace(':id', id),
                        type: 'GET',

                        success: function(res) {

                            $('#editId').val(res.id);
                            $('#editSoNumber').val(res.so_number);
                            $('#editCustomer').val(res.customer);
                            $('#editLineItem').val(res.line_item);
                            $('#editQty').val(res.qty);
                            $('#editDeliveryInstructionDate').val(res.delivery_instruction_date);
                            $('#editPickingDate').val(res.picking_date);
                            $('#editDoNumber').val(res.do_number);
                            $('#editDeliveryReceiptDate').val(res.delivery_receipt_date);

                            $('#editItemId').val(res.item_id);

                            editItemSelect.clear(true);
                            editItemSelect.clearOptions();
                            editItemSelect.loadedSearches = {};
                            editItemSelect.load('');

                            if (res.item_id && (res.item_code || res.item_name)) {

                                let label = [res.item_code, res.item_name]
                                    .filter(Boolean)
                                    .join(' | ');

                                editItemSelect.addOption({
                                    id: res.item_id,
                                    text: label,
                                    item_code: res.item_code,
                                    item_name: res.item_name
                                });

                                editItemSelect.setValue(res.item_id, true);
                            }

                            // ---- Sumber Barang: Eksternal vs Stok ----
                            let isStock = res.source_type === 'stock';

                            $('#editSourceExternal').prop('checked', !isStock);
                            $('#editSourceStock').prop('checked', isStock).trigger('change');

                            if (isStock && res.item_id && res.location_id) {

                                editSourceType.loadLocations(res.item_id).then(function() {

                                    $('#editLocationSelect').val(res.location_id);

                                    return editSourceType.loadLots(res.item_id, res.location_id);

                                }).then(function() {

                                    $('#editLotSelect').val(res.lot || '').trigger('change');

                                });
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
                $(document).on('submit', '#formEditStagingOut', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#editId').val();

                    if ($('#editSourceStock').is(':checked')) {

                        if (!$('#editItemId').val()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Barang belum dipilih',
                                text: 'Pilih barang terlebih dahulu untuk sumber stok.'
                            });
                            return;
                        }

                        if (!$('#editLocationSelect').val()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Lokasi belum dipilih',
                                text: 'Pilih lokasi yang memiliki stok barang ini.'
                            });
                            return;
                        }

                        let qtyVal = parseFloat($('#editQty').val() || 0);
                        let maxVal = parseFloat($('#editQty').attr('max'));

                        if (!qtyVal || qtyVal <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Qty belum diisi',
                                text: 'Qty wajib diisi dan lebih dari 0.'
                            });
                            return;
                        }

                        if (!isNaN(maxVal) && qtyVal > maxVal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Qty melebihi stok',
                                text: 'Sisa stok pada lot ini hanya ' + maxVal + '.'
                            });
                            return;
                        }
                    }

                    $.ajax({

                        url: "{{ route('stagings-out.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveEdit').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveEdit').prop('disabled', false);

                            $('#editStagingOutModal').modal('hide');

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

                $('#editStagingOutModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                    editItemSelect.clear();
                    editItemSelect.clearOptions();
                    editItemSelect.loadedSearches = {};
                    $('#editItemId').val('');
                    editSourceType.reset();
                });

                // ================= KONFIRMASI TANGGAL PICKING: buka modal =================
                $(document).on('click', '.btnConfirmPicking', function() {

                    let id = $(this).data('id');
                    let sourceType = $(this).data('source-type');

                    let today = new Date();
                    let yyyy = today.getFullYear();
                    let mm = String(today.getMonth() + 1).padStart(2, '0');
                    let dd = String(today.getDate()).padStart(2, '0');

                    $('#confirmPickingId').val(id);
                    $('#confirmPickingSourceType').val(sourceType);
                    $('#confirmPickingItemId').val($(this).data('item-id') || '');
                    $('#confirmPickingLocationId').val($(this).data('location-id') || '');
                    $('#confirmPickingQty').val($(this).data('qty') || '');
                    $('#confirmPickingLot').val($(this).data('lot') || '');
                    $('#confirmPickingDate').val(`${yyyy}-${mm}-${dd}`);

                });

                // ================= KONFIRMASI TANGGAL PICKING: simpan (AJAX) =================
                $(document).on('submit', '#formConfirmPicking', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#confirmPickingId').val();

                    $.ajax({

                        url: "{{ route('stagings-out.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveConfirmPicking').prop('disabled', true)
                                .html(
                                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'
                                );
                        },

                        success: function(response) {

                            $('.btnSaveConfirmPicking').prop('disabled', false)
                                .html(
                                '<i class="bi bi-check2-circle me-1"></i> Konfirmasi Picking');

                            $('#confirmPickingModal').modal('hide');

                            table.ajax.reload(null, false);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message ||
                                    'Tanggal picking berhasil dikonfirmasi',
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        },

                        error: function(xhr) {

                            $('.btnSaveConfirmPicking').prop('disabled', false)
                                .html(
                                '<i class="bi bi-check2-circle me-1"></i> Konfirmasi Picking');

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

                $('#confirmPickingModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

                // ================= KONFIRMASI RESI PENGIRIMAN: buka modal =================
                $(document).on('click', '.btnConfirmDelivery', function() {

                    let today = new Date();
                    let yyyy = today.getFullYear();
                    let mm = String(today.getMonth() + 1).padStart(2, '0');
                    let dd = String(today.getDate()).padStart(2, '0');

                    $('#confirmDeliveryId').val($(this).data('id'));
                    $('#confirmDeliverySourceType').val($(this).data('source-type'));
                    $('#confirmDeliveryItemId').val($(this).data('item-id') || '');
                    $('#confirmDeliveryLocationId').val($(this).data('location-id') || '');
                    $('#confirmDeliveryQty').val($(this).data('qty') || '');
                    $('#confirmDeliveryLot').val($(this).data('lot') || '');
                    $('#confirmDeliveryDate').val(`${yyyy}-${mm}-${dd}`);

                });

                // ================= KONFIRMASI RESI PENGIRIMAN: simpan (AJAX) =================
                $(document).on('submit', '#formConfirmDelivery', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#confirmDeliveryId').val();
                    let btnHtml = '<i class="bx bx-check-circle me-1"></i> Konfirmasi Kirim';

                    $.ajax({

                        url: "{{ route('stagings-out.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveConfirmDelivery').prop('disabled', true)
                                .html(
                                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'
                                );
                        },

                        success: function(response) {

                            $('.btnSaveConfirmDelivery').prop('disabled', false).html(btnHtml);

                            $('#confirmDeliveryModal').modal('hide');

                            table.ajax.reload(null, false);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message ||
                                    'Tanggal resi pengiriman berhasil dikonfirmasi',
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    document.querySelector('.swal2-container').style
                                        .zIndex = '9999999';
                                }
                            });

                        },

                        error: function(xhr) {

                            $('.btnSaveConfirmDelivery').prop('disabled', false).html(btnHtml);

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

                $('#confirmDeliveryModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

            });
        </script>

        <script>
            // ============ PANEL RENTANG TANGGAL ============
            const $dateInput = $('#filterDateRange');
            const $panel = $('#dateRangePanel');
            const $startInput = $('#filterStartDate');
            const $endInput = $('#filterEndDate');

            // buka/tutup panel saat input diklik
            $dateInput.on('click', function(e) {
                e.stopPropagation();
                $panel.toggleClass('show');
            });

            // jangan tutup saat klik di dalam panel
            $panel.on('click', function(e) {
                e.stopPropagation();
            });

            // tutup panel kalau klik di luar, buang perubahan yang belum "Terapkan"
            $(document).on('click', function() {
                if ($panel.hasClass('show')) {
                    $startInput.val(appliedStartDate);
                    $endInput.val(appliedEndDate);
                }
                $panel.removeClass('show');
            });

            // terapkan rentang tanggal
            $('#dateRangeApply').on('click', function() {
                const start = $startInput.val();
                const end = $endInput.val();

                if (start && end && start > end) {
                    alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                    return;
                }

                appliedStartDate = start;
                appliedEndDate = end;

                if (start && end) {
                    $dateInput.val(start + ' s/d ' + end);
                } else if (start) {
                    $dateInput.val(start + ' s/d ...');
                } else {
                    $dateInput.val('');
                }

                $panel.removeClass('show');

                table.ajax.reload();
                updateExportUrl();
            });

            function updateExportUrl() {
                let start = appliedStartDate;
                let end = appliedEndDate;
                let search = $('#customSearch').val().trim();
                let status = $('#filterStatus').val();
                let customer = $('#filterCustomer').val();
                let stagingLocation = $('#filterLocation').val();
                let dateType = $('#filterDateType').val();
                let overdue = $('#filterOverdue').is(':checked') ? 1 : 0;

                let url = new URL("{{ route('stagings-out.export') }}");

                if (start) url.searchParams.append('start_date', start);
                if (end) url.searchParams.append('end_date', end);
                if (search) url.searchParams.append('search', search);
                if (status) url.searchParams.append('status', status);
                if (customer) url.searchParams.append('customer', customer);
                if (stagingLocation) url.searchParams.append('staging_location', stagingLocation);
                if (start || end) url.searchParams.append('date_type', dateType);
                if (overdue) url.searchParams.append('overdue', overdue);

                $('#exportBtn').attr('href', url.toString());
            }

            updateExportUrl();
        </script>

        <style>
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

        <style>
            #stagingOut_wrapper {
                padding: 1rem;
            }

            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        </style>

        <style>
            #stagingOut td.text-wrap,
            #stagingOut th.text-wrap {
                white-space: normal !important;
                word-break: break-word;
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
                    const rawErrorText = @json(session('error'));

                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        html: '<pre class="text-start small" style="white-space:pre-wrap;max-height:50vh;overflow-y:auto;">' +
                            rawErrorText.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                            '</pre>',
                        confirmButtonText: 'Tutup',
                        showDenyButton: true,
                        denyButtonText: '📋 Copy',
                        width: 650,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    }).then(function(result) {
                        if (result.isDenied) {
                            navigator.clipboard.writeText(rawErrorText).then(function() {
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
                            }).catch(function() {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Gagal menyalin, coba select manual',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    didOpen: () => {
                                        document.querySelector('.swal2-container').style.zIndex = '9999999';
                                    }
                                });
                            });
                        }
                    });
                });
            </script>
        @endif

        <script>
            let selectedStagingOutIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedStagingOutIds.size);
                $('#bulkActionBar').toggleClass('d-none', selectedStagingOutIds.size === 0);
            }

            function resetStagingOutSelection() {
                selectedStagingOutIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedStagingOutIds.add(id);
                } else {
                    selectedStagingOutIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedStagingOutIds.add(id);
                    } else {
                        selectedStagingOutIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            $('#stagingOut').on('draw.dt', function() {
                resetStagingOutSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedStagingOutIds.size === 0) return;

                Swal.fire({
                    title: `Hapus ${selectedStagingOutIds.size} data?`,
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
                        url: "{{ route('stagings-out.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedStagingOutIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetStagingOutSelection();

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
        </script>

        <script>
            $(document).on('change', '.select-staging-location', function() {

                let $select = $(this);
                let id = $select.data('id');
                let value = $select.val();
                let newClass = $select.find('option:selected').data('class') || 'loc-empty';
                let oldClass = ($select.attr('class').match(/loc-\w+/) || ['loc-empty'])[0];

                $select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('stagings-out.update-location', ':id') }}".replace(':id', id),
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        staging_location: value
                    },

                    success: function(res) {

                        $select.removeClass('loc-empty loc-staging loc-packing loc-outbound')
                            .addClass(newClass);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                            didOpen: () => {
                                document.querySelector('.swal2-container').style
                                    .zIndex = '9999999';
                            }
                        });

                    },

                    error: function(xhr) {

                        $select.removeClass('loc-empty loc-staging loc-packing loc-outbound')
                            .addClass(oldClass);

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                        });

                        table.ajax.reload(null, false);

                    },

                    complete: function() {
                        $select.prop('disabled', false);
                    }

                });

            });
        </script>
    @endpush
@endsection