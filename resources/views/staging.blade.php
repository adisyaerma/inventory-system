@extends('master')
@section('title', 'Staging')
@section('content')
<div class="row g-4 mb-4">

    <!-- Total Entry -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
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
    <div class="col-md-6 col-xl-3">
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

    <!-- Inbound Shipment -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">

                <div class="icon-box bg-info-subtle text-info me-4">
                    <i class="bi bi-box-arrow-in-down fs-4"></i>
                </div>

                <div>
                    <small class="text-muted d-block">Inbound Shipment</small>
                    <h4 class="fw-bold mb-1">
                        {{ number_format($inboundShipment, 0, ',', '.') }}
                    </h4>
                    <small class="text-info">Jumlah Entry</small>
                </div>

            </div>
        </div>
    </div>

    <!-- Hold / Repair -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">

                <div class="icon-box bg-warning-subtle text-warning me-4">
                    <i class="bi bi-tools fs-4"></i>
                </div>

                <div>
                    <small class="text-muted d-block">Hold / Repair</small>
                    <h4 class="fw-bold mb-1">
                        {{ number_format($holdRepair, 0, ',', '.') }}
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
                <h4 class="mb-0">Staging</h4>
                <small class="text-muted">Kelola data staging</small>
            </div>
            <div class="float-end mt-3">
                <button type="button" class="btn-sm btn border-secondary bg-white border me-1" data-bs-toggle="modal"
                    data-bs-target="#importStagingModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Import</span>
                </button>

                <a href="{{ route('stagings.export') }}" class="btn-sm btn border-secondary bg-white border me-1"
                    id="exportBtn">
                    <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor"
                            d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Export</span>
                </a>

                <button type="button" class="btn-sm btn border-secondary bg-white border me-1" id="resetFilter"
                    title="Reset Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 21 21">
                        <path d="M0 0h21v21H0z" fill="none" />
                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                            <path d="M7.5 6.5h-4v-4" />
                        </g>
                    </svg>
                    <span class="d-none d-md-inline ms-1">Reset</span>
                </button>

                <button data-bs-toggle="modal" data-bs-target="#addStaging" type="button" class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>

                {{-- ================= MODAL IMPORT ================= --}}
                <div class="modal fade" id="importStagingModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form action="{{ route('stagings.import') }}" method="POST" enctype="multipart/form-data">
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
                                                Impor Data Staging
                                            </h5>

                                            <small class="text-muted">
                                                Import data staging dari file Excel
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

                                            <a href="{{ route('stagings.template') }}"
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
                                                <input type="file" id="excelFileStaging" name="file"
                                                    accept=".xlsx,.xls"  hidden>

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

                                                    <div id="selectedFileStaging" class="mt-2" style="display:none;">
                                                        <small class="text-success fw-semibold">
                                                            ✓ File dipilih: <span id="fileNameStaging"></span>
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
                <div class="modal fade" id="addStaging" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="addStagingLabel" aria-hidden="true">

                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">

                            <form action="{{ route('stagings.store') }}" method="POST" id="formStaging">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Staging
                                        </h4>

                                        <small class="text-muted">
                                            Tambahkan data staging beserta lokasi penempatannya.
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
                                                        Lengkapi informasi barang yang akan di-staging.
                                                    </small>

                                                </div>

                                                <div class="card-body px-4 pb-4">

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Kode Barang
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-barcode"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="item_code" placeholder="Contoh: NL3195" >

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
                                                                name="item_name"
                                                                placeholder="Contoh: Nord-Lock Steel Washer" >

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Pemilik Barang
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-user"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="item_owner" placeholder="Contoh: PT. XYZ" >

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Asal Supplier
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-buildings"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="supplier_origin" placeholder="Contoh: PT. ABC"
                                                                >

                                                        </div>

                                                    </div>

                                                    <div>

                                                        <label class="form-label fw-semibold">
                                                            No. PO
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-receipt"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="po_number" placeholder="Contoh: PO-2026-0001"
                                                                >

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
                                                        <i class="bi bi-geo-alt text-success me-2"></i>
                                                        Detail Kedatangan &amp; Lokasi
                                                    </h5>

                                                    <small class="text-muted">
                                                        Tentukan jadwal, jumlah, dan lokasi penempatan barang.
                                                    </small>

                                                </div>

                                                <div class="card-body px-4 pb-4">

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Tanggal Kedatangan
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-calendar"></i>
                                                            </span>

                                                            <input type="date" class="form-control form-control-sm"
                                                                name="arrival_date" value="{{ now()->format('Y-m-d') }}"
                                                                >

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Qty
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-cube-alt"></i>
                                                            </span>

                                                            <input type="number" class="form-control form-control-sm"
                                                                name="qty" min="0" placeholder="0" >

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Lokasi
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-current-location"></i>
                                                            </span>

                                                            <select name="location" class="form-select form-select-sm"
                                                                >
                                                                <option value="" selected disabled>Pilih lokasi
                                                                </option>
                                                                @foreach (\App\Models\Staging::LOCATIONS as $location)
                                                                    <option value="{{ $location }}">
                                                                        {{ $location }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>

                                                    </div>

                                                    <div>

                                                        <label class="form-label fw-semibold">
                                                            Keterangan
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text align-items-start pt-2">
                                                                <i class="bx bx-note"></i>
                                                            </span>

                                                            <textarea rows="5" class="form-control form-control-sm" name="notes" placeholder="Opsional"></textarea>

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

                                        Simpan Staging

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
                    .filter-toolbar {
                        --gap: 0.5rem;
                    }

                    .filter-toolbar>* {
                        flex: 1 1 calc(25% - var(--gap));
                        max-width: 260px;
                        min-width: 150px;
                    }

                    @media (max-width: 991.98px) {
                        .filter-toolbar>* {
                            flex: 1 1 calc(50% - var(--gap));
                            max-width: 320px;
                        }
                    }

                    @media (max-width: 575.98px) {
                        .filter-toolbar>* {
                            flex: 1 1 100%;
                            max-width: 100%;
                        }
                    }

                    .date-range-panel {
                        display: none;
                        position: absolute;
                        top: calc(100% + 4px);
                        left: 0;
                        z-index: 1050;
                        background: #fff;
                        border: 1px solid #dee2e6;
                        border-radius: 0.375rem;
                        padding: 12px;
                        width: 220px;
                    }

                    .date-range-panel.show {
                        display: block;
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

                    <!-- Rentang Tanggal Kedatangan -->
                    <div class="date-range-wrapper" style="position: relative;">
                        <input type="text" class="form-control form-control-sm shadow-sm w-100" id="filterDateRange"
                            placeholder="Pilih rentang tanggal" title="Rentang Tanggal Kedatangan" readonly
                            autocomplete="off">

                        <div class="date-range-panel shadow" id="dateRangePanel">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Dari</label>
                                <input type="date" class="form-control form-control-sm" id="filterStartDate">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Sampai</label>
                                <input type="date" class="form-control form-control-sm" id="filterEndDate">
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-primary" id="dateRangeApply">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <select class="form-select form-select-sm shadow-sm" id="filterLocation" title="Lokasi">
                        <option value="">Semua Lokasi</option>
                        @foreach (\App\Models\Staging::LOCATIONS as $locationOption)
                            <option value="{{ $locationOption }}">{{ $locationOption }}</option>
                        @endforeach
                    </select>

                </div>

            </div>

            <table class="table table-bordered" id="staging">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. PO</th>
                        <th>Item</th>
                        <th>Supplier</th>
                        <th>Owner</th>
                        <th>Tgl Kedatangan</th>
                        <th>Qty</th>
                        <th>Lokasi</th>
                        <th>Keterangan</th>
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

    <div class="modal fade" id="editStagingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <form id="formEditStaging">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Staging
                            </h4>

                            <small class="text-muted">
                                Ubah data staging beserta lokasi penempatannya.
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

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
                                            Lengkapi informasi barang yang di-staging.
                                        </small>

                                    </div>

                                    <div class="card-body px-4 pb-4">

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Kode Barang
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-barcode"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_code" id="editItemCode" >

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
                                                    name="item_name" id="editItemName" >

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Pemilik Barang
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-user"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="item_owner" id="editItemOwner" >

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Asal Supplier
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-buildings"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="supplier_origin" id="editSupplierOrigin" >

                                            </div>

                                        </div>

                                        <div>

                                            <label class="form-label fw-semibold">
                                                No. PO
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-receipt"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="po_number" id="editPoNumber" >

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
                                            <i class="bi bi-geo-alt text-success me-2"></i>
                                            Detail Kedatangan &amp; Lokasi
                                        </h5>

                                        <small class="text-muted">
                                            Tentukan jadwal, jumlah, dan lokasi penempatan barang.
                                        </small>

                                    </div>

                                    <div class="card-body px-4 pb-4">

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Tanggal Kedatangan
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-calendar"></i>
                                                </span>

                                                <input type="date" class="form-control form-control-sm"
                                                    name="arrival_date" id="editArrivalDate" >

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Qty
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-cube-alt"></i>
                                                </span>

                                                <input type="number" class="form-control form-control-sm" name="qty"
                                                    min="0" id="editQty" >

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Lokasi
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-current-location"></i>
                                                </span>

                                                <select name="location" class="form-select form-select-sm"
                                                    id="editLocation" >
                                                    @foreach (\App\Models\Staging::LOCATIONS as $location)
                                                        <option value="{{ $location }}">
                                                            {{ $location }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            </div>

                                        </div>

                                        <div>

                                            <label class="form-label fw-semibold">
                                                Keterangan
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text align-items-start pt-2">
                                                    <i class="bx bx-note"></i>
                                                </span>

                                                <textarea rows="5" class="form-control form-control-sm" name="notes" id="editNotes" placeholder="Opsional"></textarea>

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
    </style>
        <script>
            let table;

            $(document).ready(function() {

                table = $('#staging').DataTable({

                    dom: 'rtip',
                    processing: true,
                    serverSide: true,

                    ajax: {
                        url: "{{ route('stagings.data') }}",
                        data: function(d) {
                            d.start_date = $('#filterStartDate').val();
                            d.end_date = $('#filterEndDate').val();
                            d.location = $('#filterLocation').val();
                        }
                    },

                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: 'po_number',
                            name: 'po_number'
                        },
                        {
                            data: 'item',
                            name: 'item',
                            orderable: false
                        },
                        {
                            data: 'supplier_origin',
                            name: 'supplier_origin'
                        },
                        {
                            data: 'item_owner',
                            name: 'item_owner'
                        },
                        {
                            data: 'arrival_date',
                            name: 'arrival_date'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'location',
                            name: 'location'
                        },
                        {
                            data: 'notes',
                            name: 'notes'
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
                            targets: [1, 2, 3, 4],
                            className: "text-wrap",
                            width: "220px"
                        },
                        {
                            targets: 0,
                            width: "80px"
                        },
                        {
                            targets: 6,
                            className: "text-center"
                        },
                        {
                            targets: 9,
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
                    const $info = $('#staging_info');
                    if ($info.length && !$('#lengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#lengthWrapper');
                    }

                    const $paginate = $('#staging_paginate');
                    if ($paginate.length && !$('#tableFooter').find('.dataTables_paginate').length) {
                        $paginate.appendTo('#tableFooter');
                    }
                }

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                    updateExportUrl();
                });

                $('#customLength').change(function() {
                    table.page.len($(this).val()).draw();
                });

                $('#filterStartDate, #filterEndDate, #filterLocation').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#resetFilter').click(function() {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#filterDateRange').val('');
                    $('#filterLocation').val('').trigger('change');
                    $('#customSearch').val('');

                    table.search('').draw();
                    updateExportUrl();
                    table.ajax.reload();
                });

                $('#excelFileStaging').on('change', function() {
                    if (this.files.length > 0) {
                        $('#fileNameStaging').text(this.files[0].name);
                        $('#selectedFileStaging').show();
                    } else {
                        $('#selectedFileStaging').hide();
                    }
                });

                // ================= TAMBAH (AJAX) =================
                $(document).on('submit', '#formStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    $.ajax({
                        url: "{{ route('stagings.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        success: function(response) {

                            $('#addStaging').modal('hide');
                            $('#formStaging')[0].reset();

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

                        url: "{{ route('stagings.edit', ':id') }}".replace(':id', id),
                        type: 'GET',

                        success: function(res) {

                            $('#editId').val(res.id);
                            $('#editPoNumber').val(res.po_number);
                            $('#editArrivalDate').val(res.arrival_date);
                            $('#editSupplierOrigin').val(res.supplier_origin);
                            $('#editItemOwner').val(res.item_owner);
                            $('#editItemCode').val(res.item_code);
                            $('#editItemName').val(res.item_name);
                            $('#editQty').val(res.qty);
                            $('#editLocation').val(res.location);
                            $('#editNotes').val(res.notes);

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
                $(document).on('submit', '#formEditStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#editId').val();

                    $.ajax({

                        url: "{{ route('stagings.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveEdit').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveEdit').prop('disabled', false);

                            $('#editStagingModal').modal('hide');

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

                $('#editStagingModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

            });
        </script>

        <script>
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

            // tutup panel kalau klik di luar
            $(document).on('click', function() {
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
                let start = $('#filterStartDate').val();
                let end = $('#filterEndDate').val();
                let location = $('#filterLocation').val();
                let search = $('#customSearch').val().trim();

                let url = new URL("{{ route('stagings.export') }}");

                if (start) url.searchParams.append('start_date', start);
                if (end) url.searchParams.append('end_date', end);
                if (location) url.searchParams.append('location', location);
                if (search) url.searchParams.append('search', search);

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
            #staging_wrapper {
                padding: 1rem;
            }

            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        </style>

        <style>
            #staging td.text-wrap,
            #staging th.text-wrap {
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        html: '<pre class="text-start small" style="white-space:pre-wrap;max-height:50vh;overflow-y:auto;">' +
                            @json(session('error')).replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                            '</pre>',
                        confirmButtonText: 'Tutup',
                        width: 650,
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
    @endpush
@endsection