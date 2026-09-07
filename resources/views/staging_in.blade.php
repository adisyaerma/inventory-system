@extends('master')
@section('title', 'Staging')
@section('content')
    <div class="row g-4 mb-4">
        <!-- Total Entry -->
        <div class="col-md-6 col-xl-3 col-sm-6">
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

        <!-- Inbound Shipment -->
        <div class="col-md-6 col-xl-3 col-sm-6">
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
        <div class="col-md-6 col-xl-3 col-sm-6">
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
                <h4 class="mb-0">Staging In</h4>
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

                <a href="{{ route('stagings-in.export') }}" class="btn-sm btn border-secondary bg-white border me-1"
                    id="exportBtn">
                    <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor"
                            d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Export</span>
                </a>

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
                        <form action="{{ route('stagings-in.import') }}" method="POST" enctype="multipart/form-data">
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

                                            <a href="{{ route('stagings-in.template') }}"
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

                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form action="{{ route('stagings-in.store') }}" method="POST" id="formStaging">

                                @csrf

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Item Staging
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
                                                            No. PO
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-receipt"></i>
                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="po_number" placeholder="Contoh: PO-2026-0001">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Barang
                                                        </label>

                                                        <select id="addItemSelect"
                                                            placeholder="Cari kode / nama barang..."></select>

                                                        <input type="hidden" name="item_id" id="addItemId">

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
                                                                id="addItemOwner"
                                                                placeholder="Terisi otomatis dari data barang" readonly
                                                                disabled>

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
                                                                name="supplier_origin" placeholder="Contoh: PT. ABC">

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Incoterms
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-world"></i>
                                                            </span>

                                                            <select name="incoterms" class="form-select form-select-sm">
                                                                <option value="" selected>Pilih incoterms</option>
                                                                @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                                                    <option value="{{ $incoterm }}">
                                                                        {{ $incoterm }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>

                                                    </div>
                                                     <div class="">

                                                        <label class="form-label fw-semibold">
                                                            Status
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">
                                                                <i class="bx bx-flag"></i>
                                                            </span>

                                                            <select name="status" class="form-select form-select-sm">
                                                                <option value="" selected>Pilih status
                                                                </option>
                                                                @foreach (\App\Models\StagingIn::STATUSES as $statusItem)
                                                                    <option value="{{ $statusItem }}">
                                                                        {{ $statusItem }}</option>
                                                                @endforeach
                                                            </select>

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
                                                                name="arrival_date" value="{{ now()->format('Y-m-d') }}">

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
                                                                name="qty" min="0" placeholder="0">

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

                                                            <select name="location" class="form-select form-select-sm">
                                                                <option value="" selected disabled>Pilih lokasi
                                                                </option>
                                                                @foreach (\App\Models\StagingIn::LOCATIONS as $location)
                                                                    <option value="{{ $location }}">
                                                                        {{ $location }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>

                                                    </div>

                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">
                                                            Lokasi Gudang Asal
                                                        </label>

                                                        <select id="addWarehouseLocationSelect"
                                                            name="warehouse_location"></select>

                                                        <small class="text-muted d-block mt-1"
                                                            id="addWarehouseLocationStock"></small>


                                                    </div>

                                                    {{-- Lot: hanya muncul kalau barang di lokasi terpilih
                                                        memang punya pelacakan lot --}}
                                                    <div class="mb-3" id="addLotWrapper" style="display:none;">

                                                        <label class="form-label fw-semibold">
                                                            Lot
                                                        </label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="bx bx-purchase-tag"></i>
                                                            </span>

                                                            <select id="addLotSelect" name="lot"></select>
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

                @if ($activeFilter === 'overdue')
                    <div class="alert alert-danger d-flex align-items-center justify-content-between mb-3"
                        id="activeFilterBanner">
                        <span>
                            <i class="bx bx-error me-1"></i>
                            Menampilkan barang staging in yang sudah lebih dari 7 hari.
                        </span>
                        <button type="button" class="btn-close" id="clearActiveFilterBtn"
                            aria-label="Hapus filter"></button>
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
                                    placeholder="No. PO, item, supplier...">
                            </div>
                        </div>

                        <!-- Filter Status -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterStatus">Status</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-flag text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filter Incoterms -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterIncoterms">Incoterms</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-globe text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterIncoterms">
                                    <option value="">Semua Incoterms</option>
                                    @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                        <option value="{{ $incoterm }}">{{ $incoterm }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filter Pemilik Barang -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterOwner">Pemilik Barang</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterOwner">
                                    <option value="">Semua Owner</option>
                                    @foreach ($ownerOptions as $ownerOption)
                                        <option value="{{ $ownerOption }}">{{ $ownerOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Filter Asal Supplier -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterSupplier">Asal Supplier</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterSupplier">
                                    <option value="">Semua Supplier</option>
                                    @foreach ($supplierOptions as $supplierOption)
                                        <option value="{{ $supplierOption }}">{{ $supplierOption }}</option>
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
                                    @foreach (\App\Models\StagingIn::LOCATIONS as $locationOption)
                                        <option value="{{ $locationOption }}">{{ $locationOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Rentang Tanggal Kedatangan -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterDateRange">Rentang Tanggal</label>
                            <div class="date-range-wrapper">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-calendar-range text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="filterDateRange"
                                        placeholder="Pilih rentang tanggal" title="Rentang Tanggal Kedatangan" readonly
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

            <table class="table table-bordered" id="staging">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>No. PO</th>
                        <th>Tgl Kedatangan</th>
                        <th>Supplier</th>
                        <th>Owner</th>
                        <th>Incoterms</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Lokasi</th>
                        <th>Keterangan</th>
                        <th>Status</th>
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

        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form id="formEditStaging">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Edit Item Staging
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
                                                No. PO
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-receipt"></i>
                                                </span>

                                                <input type="text" class="form-control form-control-sm"
                                                    name="po_number" id="editPoNumber">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Barang
                                            </label>

                                            <select id="editItemSelect" placeholder="Cari kode / nama barang..."></select>

                                            <input type="hidden" name="item_id" id="editItemId">

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
                                                    id="editItemOwner" readonly disabled>

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
                                                    name="supplier_origin" id="editSupplierOrigin">

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Incoterms
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-world"></i>
                                                </span>

                                                <select name="incoterms" class="form-select form-select-sm"
                                                    id="editIncoterms">
                                                    <option value="">Pilih incoterms</option>
                                                    @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                                        <option value="{{ $incoterm }}">
                                                            {{ $incoterm }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            </div>

                                        </div>

                                        <div class="">

                                            <label class="form-label fw-semibold">
                                                Status
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-flag"></i>
                                                </span>

                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="" selected>Pilih status
                                                    </option>
                                                    @foreach (\App\Models\StagingIn::STATUSES as $statusItem)
                                                        <option value="{{ $statusItem }}">
                                                            {{ $statusItem }}</option>
                                                    @endforeach
                                                </select>

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
                                                    name="arrival_date" id="editArrivalDate">

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
                                                    min="0" id="editQty">

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
                                                    id="editLocation">
                                                    @foreach (\App\Models\StagingIn::LOCATIONS as $location)
                                                        <option value="{{ $location }}">
                                                            {{ $location }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Lokasi Gudang Asal
                                            </label>

                                            <select id="editWarehouseLocationSelect" name="warehouse_location"></select>

                                            <small class="text-muted d-block mt-1"
                                                id="editWarehouseLocationStock"></small>

                                        </div>

                                        {{-- Lot: hanya muncul kalau barang di lokasi terpilih
                                            memang punya pelacakan lot --}}
                                        <div class="mb-3" id="editLotWrapper" style="display:none;">

                                            <label class="form-label fw-semibold">
                                                Lot
                                            </label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bx bx-purchase-tag"></i>
                                                </span>

                                                <select id="editLotSelect" name="lot"></select>
                                            </div>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">
                                                Status
                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">
                                                    <i class="bx bx-flag"></i>
                                                </span>

                                                <select name="status" class="form-select form-select-sm"
                                                    id="editStatus">
                                                    <option value="" selected>Pilih status</option>
                                                    @foreach (\App\Models\StagingIn::STATUSES as $statusItem)
                                                        <option value="{{ $statusItem }}">
                                                            {{ $statusItem }}
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

    {{-- ================= MODAL PINDAHKAN ITEM ================= --}}
    <div class="modal fade" id="moveStagingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="moveStagingLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form id="formMoveStaging">

                    @csrf

                    <input type="hidden" name="destination" id="moveDestination" value="stock">

                    <div class="modal-header border-0 pb-0">

                        <div>
                            <h4 class="mb-1 fw-bold">Pindahkan Item</h4>
                            <small class="text-muted">
                                Tentukan tujuan perpindahan barang dari staging in.
                            </small>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body p-4">

                        {{-- ================= RINGKASAN ITEM ================= --}}
                        <div class="card border rounded-4 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-start gap-3">

                                <div>
                                    <small class="text-muted d-block" id="moveItemCode"></small>
                                    <div class="fw-bold" id="moveItemName"></div>
                                    <small class="text-muted d-block mt-1" id="moveItemMeta"></small>

                                    <div class="mt-2">
                                        <span class="badge bg-secondary-subtle text-secondary"
                                            id="moveFromLocation"></span>
                                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                        <span class="badge bg-primary-subtle text-primary" id="moveToLocationLabel">Masuk
                                            Stok</span>
                                    </div>
                                </div>

                                <span class="badge bg-dark rounded-pill px-3 py-2 flex-shrink-0" id="moveQtyBadge"></span>

                            </div>
                        </div>

                        {{-- ================= TUJUAN ================= --}}
                        <label class="form-label fw-semibold text-muted small text-uppercase mb-2">
                            Tujuan
                        </label>

                        <div class="row g-3 mb-4">

                            <div class="col-6">
                                <div class="tujuan-option border rounded-4 p-3 h-100 active" data-tujuan="stock">
                                    <div class="icon-box-sm bg-secondary-subtle text-secondary mb-2">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="fw-bold">Masuk Stok</div>
                                    <small class="text-muted">Barang disimpan sebagai stok gudang</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="tujuan-option border rounded-4 p-3 h-100" data-tujuan="out">
                                    <div class="icon-box-sm bg-primary-subtle text-primary mb-2">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div class="fw-bold">Staging Out</div>
                                    <small class="text-muted">Barang langsung dikirim ke customer</small>
                                </div>
                            </div>

                        </div>

                        {{-- ================= QTY (SHARED) ================= --}}
                        <div class="row g-3 mb-1">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Qty dipindah</label>
                                <input type="number" class="form-control form-control-sm" name="qty" id="moveQty"
                                    min="1">
                                <small class="text-muted" id="moveQtyHelp"></small>
                            </div>
                        </div>

                        {{-- ================= FIELD: MASUK STOK ================= --}}
                        <div id="fieldsMasukStok" class="mt-3">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Lokasi Penyimpanan</label>
                                    <select id="moveLocationSelect" name="location"></select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">No. Transaksi</label>
                                    <input type="text" class="form-control form-control-sm" name="transaction_number"
                                        id="" placeholder="Contoh: GRN-2026-0001">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Masuk</label>
                                    <input type="date" class="form-control form-control-sm" name="transaction_date"
                                        id="moveTransactionDate">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Catatan</label>
                                    <textarea rows="3" class="form-control form-control-sm" name="notes" id="moveNotes" placeholder="Opsional"></textarea>
                                </div>

                            </div>
                        </div>

                        {{-- ================= FIELD: STAGING OUT ================= --}}
                        <div id="fieldsStagingOut" class="mt-3 d-none">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">No. SO</label>
                                    <input type="text" class="form-control form-control-sm" name="so_number"
                                        id="moveSoNumber" placeholder="SO-2026-0001">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Customer</label>
                                    <input type="text" class="form-control form-control-sm" name="customer"
                                        id="moveCustomer" placeholder="Nama customer">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Line Item</label>
                                    <input type="text" class="form-control form-control-sm" name="line_item"
                                        id="moveLineItem" placeholder="Nama/keterangan barang">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tgl. Delivery Instruction</label>
                                    <input type="date" class="form-control form-control-sm"
                                        name="delivery_instruction_date" id="moveDeliveryInstructionDate">
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer border-0 pt-0">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary px-4 btnSaveMove">
                            <i class="bx bx-transfer-alt me-1"></i>
                            Pindahkan
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

            .icon-box-sm {
                border-radius: 12px;
                width: 40px;
                height: 40px;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .tujuan-option {
                cursor: pointer;
                transition: all .15s ease;
            }

            .tujuan-option:hover {
                border-color: #0d6efd !important;
            }

            .tujuan-option.active {
                border-color: #0d6efd !important;
                background: #f0f6ff;
                box-shadow: 0 0 0 1px #0d6efd inset;
            }

            .tujuan-option.tujuan-disabled {
                cursor: not-allowed;
                opacity: .6;
            }

            .tujuan-option.tujuan-disabled:hover {
                border-color: inherit !important;
            }
        </style>

        <script>
            // ============ VARIABEL GLOBAL ============
            let table;
            let appliedStartDate = '';
            let appliedEndDate = '';
            // Filter yang datang dari URL (mis. link notifikasi dashboard
            // ?filter=overdue). Dikirim terus ke server sampai user
            // eksplisit mereset filter, supaya link "Barang staging in > 7
            // hari" dari dashboard benar-benar memfilter tabel ini.
            let activeUrlFilter = new URLSearchParams(window.location.search).get('filter') || '';
        </script>

        <script>
            $(document).ready(function() {

                table = $('#staging').DataTable({

                    dom: 'rtip',
                    processing: true,
                    serverSide: true,
                    order: [],

                    ajax: {
                        url: "{{ route('stagings-in.data') }}",
                        data: function(d) {
                            d.start_date = appliedStartDate;
                            d.end_date = appliedEndDate;
                            d.location = $('#filterLocation').val();
                            d.status = $('#filterStatus').val();
                            d.incoterms = $('#filterIncoterms').val();
                            d.item_owner = $('#filterOwner').val();
                            d.supplier_origin = $('#filterSupplier').val();
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
                            data: 'po_number',
                            name: 'po_number'
                        },
                        {
                            data: 'arrival_date',
                            name: 'arrival_date'
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
                            data: 'incoterms',
                            name: 'incoterms'
                        },
                        {
                            data: 'item',
                            name: 'item',
                            orderable: false
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
                            data: 'status',
                            name: 'status'
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
                            targets: [2, 4, 5, 7],
                            className: "text-wrap",
                            width: "220px"
                        },
                        {
                            targets: 1,
                            width: "80px"
                        },
                        {
                            targets: 8,
                            className: "text-center"
                        },
                        {
                            targets: 11,
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

                // filter dropdown auto-reload; tanggal menunggu tombol "Terapkan"
                $('#filterLocation').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterStatus').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterIncoterms').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterOwner').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#filterSupplier').on('change', function() {
                    table.ajax.reload();
                    updateExportUrl();
                });

                $('#resetFilterInline').click(function() {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#filterDateRange').val('');
                    $('#filterLocation').val('');
                    $('#filterStatus').val('');
                    $('#filterIncoterms').val('');
                    $('#filterOwner').val('');
                    $('#filterSupplier').val('');
                    $('#customSearch').val('');

                    appliedStartDate = '';
                    appliedEndDate = '';

                    activeUrlFilter = '';
                    window.history.replaceState({}, '', window.location.pathname);
                    $('#activeFilterBanner').remove();

                    table.search('').draw();
                    updateExportUrl();
                    table.ajax.reload();
                });

                $('#clearActiveFilterBtn').on('click', function() {
                    activeUrlFilter = '';
                    window.history.replaceState({}, '', window.location.pathname);
                    $('#activeFilterBanner').remove();
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

                // ================= LOKASI GUDANG ASAL (sumber penarikan stok) =================
                // Dipakai untuk tahu lokasi gudang mana yang stoknya harus
                // dikurangi & dicatat sebagai mutasi keluar saat staging in
                // ditambahkan/diedit. Pilihannya dibatasi hanya lokasi yang
                // saat ini punya stok untuk barang yang sedang dipilih —
                // diisi otomatis lewat loadItemLocations() setiap kali
                // barangnya berganti.
                function initWarehouseLocationSelect(selectId, stockLabelSelector, itemIdFieldSelector,
                    lotSelectInstance, lotWrapperSelector) {
                    return new TomSelect(selectId, {
                        create: false,
                        placeholder: 'Pilih barang terlebih dahulu...',
                        valueField: 'value',
                        labelField: 'text',
                        searchField: ['text'],
                        options: [],

                        onChange: function(value) {
                            let data = this.options[value];
                            let qty = data ? data.quantity : null;

                            $(stockLabelSelector).text(
                                (qty !== null && qty !== undefined) ?
                                'Stok tersedia di lokasi ini: ' + qty + ' pcs' :
                                ''
                            );

                            // Setiap kali lokasi gudang asalnya berganti (dipilih
                            // manual oleh user), muat ulang pilihan lot untuk
                            // kombinasi barang + lokasi ini.
                            let itemId = $(itemIdFieldSelector).val();
                            loadLots(itemId, value, lotSelectInstance, lotWrapperSelector, null);
                        }
                    });
                }

                // ================= LOT (tidak semua barang punya lot) =================
                function initLotSelect(selectId) {
                    return new TomSelect(selectId, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: ['text'],
                        placeholder: 'Pilih lot...',
                        create: false,
                        allowEmptyOption: true
                    });
                }

                let addLotSelect = initLotSelect('#addLotSelect');
                let editLotSelect = initLotSelect('#editLotSelect');

                let addWarehouseLocationSelect = initWarehouseLocationSelect(
                    '#addWarehouseLocationSelect', '#addWarehouseLocationStock', '#addItemId',
                    addLotSelect, '#addLotWrapper');
                let editWarehouseLocationSelect = initWarehouseLocationSelect(
                    '#editWarehouseLocationSelect', '#editWarehouseLocationStock', '#editItemId',
                    editLotSelect, '#editLotWrapper');

                // Ambil daftar lot untuk kombinasi barang + lokasi gudang asal
                // yang sedang dipilih. Kalau barang ini tidak punya pelacakan
                // lot di lokasi tersebut (atau cuma ada satu lot), field lot
                // langsung disembunyikan / diisi otomatis. Kalau lebih dari
                // satu, user wajib memilih sendiri. `presetLot` dipakai saat
                // modal Edit dibuka supaya lot yang sudah tersimpan langsung
                // terpilih.
                function loadLots(itemId, locationName, lotSelectInstance, lotWrapperSelector, presetLot,
                    callback) {

                    lotSelectInstance.clear(true);
                    lotSelectInstance.clearOptions();

                    if (!itemId || !locationName) {
                        $(lotWrapperSelector).hide();
                        if (callback) callback();
                        return;
                    }

                    $.ajax({
                        url: "{{ route('stagings-in.lots') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId,
                            location: locationName
                        },

                        success: function(res) {

                            res.forEach(function(opt) {
                                lotSelectInstance.addOption(opt);
                            });

                            if (res.length === 0) {

                                // Barang tanpa pelacakan lot di lokasi ini.
                                $(lotWrapperSelector).hide();
                                lotSelectInstance.setValue('', true);

                            } else if (res.length === 1) {

                                $(lotWrapperSelector).show();
                                lotSelectInstance.setValue(res[0].id, true);

                            } else {

                                $(lotWrapperSelector).show();

                                if (presetLot !== undefined && presetLot !== null && presetLot !== '') {
                                    lotSelectInstance.setValue(presetLot, true);
                                }
                                // Lebih dari satu lot & tidak ada preset: jangan
                                // auto-pilih, biarkan user yang menentukan.
                            }

                            if (callback) callback();
                        },

                        error: function() {
                            $(lotWrapperSelector).hide();
                            if (callback) callback();
                        }
                    });
                }

                // Ambil lokasi gudang (+ stoknya) yang dimiliki sebuah barang,
                // lalu isikan sebagai pilihan di TomSelect lokasi gudang asal.
                // `preselectLocationName` dipakai saat modal Edit dibuka, supaya
                // lokasi yang sudah tersimpan langsung terpilih (dan tetap
                // ditampilkan meski stoknya kebetulan sudah 0 saat ini).
                function loadItemLocations(itemId, tomSelectInstance, stockLabelSelector, preselectLocationName,
                    lotSelectInstance, lotWrapperSelector, presetLot) {

                    tomSelectInstance.clear(true);
                    tomSelectInstance.clearOptions();
                    $(stockLabelSelector).text('');
                    lotSelectInstance.clear(true);
                    lotSelectInstance.clearOptions();
                    $(lotWrapperSelector).hide();

                    if (!itemId) {
                        return;
                    }

                    $.ajax({
                        url: "{{ route('stagings-in.item-locations') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId
                        },

                        success: function(res) {

                            let list = res || [];
                            let hasPreselect = false;

                            list.forEach(function(loc) {

                                tomSelectInstance.addOption({
                                    value: loc.name,
                                    text: loc.name + ' (stok: ' + loc.quantity + ')',
                                    quantity: loc.quantity
                                });

                                if (preselectLocationName && loc.name === preselectLocationName) {
                                    hasPreselect = true;
                                }
                            });

                            // Lokasi yang tersimpan di data staging (saat edit)
                            // tetap ditampilkan sebagai opsi walau sudah tidak
                            // ada di daftar stok saat ini (misal stoknya 0),
                            // supaya nilai lama tidak hilang begitu saja.
                            if (preselectLocationName && !hasPreselect) {
                                tomSelectInstance.addOption({
                                    value: preselectLocationName,
                                    text: preselectLocationName + ' (stok: 0)',
                                    quantity: 0
                                });
                            }

                            tomSelectInstance.refreshOptions(false);

                            if (preselectLocationName) {

                                tomSelectInstance.setValue(preselectLocationName, true);

                                let opt = tomSelectInstance.options[preselectLocationName];

                                if (opt && opt.quantity !== null && opt.quantity !== undefined) {
                                    $(stockLabelSelector).text('Stok tersedia di lokasi ini: ' + opt
                                        .quantity + ' pcs');
                                }

                                loadLots(itemId, preselectLocationName, lotSelectInstance,
                                    lotWrapperSelector, presetLot);

                            } else if (list.length === 1) {

                                // Kalau barang cuma ada stok di 1 lokasi,
                                // langsung pilihkan lokasi itu.
                                tomSelectInstance.setValue(list[0].name, true);
                                $(stockLabelSelector).text('Stok tersedia di lokasi ini: ' + list[0]
                                    .quantity + ' pcs');

                                loadLots(itemId, list[0].name, lotSelectInstance, lotWrapperSelector,
                                    null);
                            }
                        },

                        error: function() {
                            tomSelectInstance.refreshOptions(false);
                        }
                    });
                }

                // ================= BARANG (TomSelect dari tabel items) =================
                function initItemSelect(selectId, idFieldId, ownerFieldId, warehouseSelect, stockLabelSelector,
                    lotSelectInstance, lotWrapperSelector) {

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
                                url: "{{ route('stagings-in.search-stock') }}",
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

                            // Pemilik barang terisi otomatis dan read-only,
                            // diambil dari vendor_id milik barang tersebut.
                            $(ownerFieldId).val(data && data.item_owner ? data.item_owner : '');

                            // Setiap kali barangnya berganti, muat ulang pilihan
                            // lokasi gudang asal (dan cascading lot-nya) sesuai
                            // lokasi yang benar-benar punya stok barang ini
                            // sekarang.
                            loadItemLocations(data ? data.id : null, warehouseSelect, stockLabelSelector,
                                null, lotSelectInstance, lotWrapperSelector, null);
                        }
                    });
                }

                let addItemSelect = initItemSelect(
                    '#addItemSelect', '#addItemId', '#addItemOwner',
                    addWarehouseLocationSelect, '#addWarehouseLocationStock',
                    addLotSelect, '#addLotWrapper');

                let editItemSelect = initItemSelect(
                    '#editItemSelect', '#editItemId', '#editItemOwner',
                    editWarehouseLocationSelect, '#editWarehouseLocationStock',
                    editLotSelect, '#editLotWrapper');

                $('#addStaging').on('hidden.bs.modal', function() {
                    addItemSelect.clear();
                    $('#addItemId').val('');
                    $('#addItemOwner').val('');
                    addWarehouseLocationSelect.clear(true);
                    addWarehouseLocationSelect.clearOptions();
                    $('#addWarehouseLocationStock').text('');
                    addLotSelect.clear(true);
                    addLotSelect.clearOptions();
                    $('#addLotWrapper').hide();
                });

                // ================= TAMBAH (AJAX) =================
                $(document).on('submit', '#formStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    // Kalau field lot sedang tampil (lebih dari satu lot untuk
                    // barang + lokasi ini) tapi belum dipilih, jangan submit.
                    if ($('#addLotWrapper').is(':visible') &&
                        addLotSelect.options && Object.keys(addLotSelect.options).length > 1 &&
                        !addLotSelect.getValue()) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Silakan pilih lot terlebih dahulu',
                            timer: 2500,
                            showConfirmButton: false
                        });

                        return;
                    }

                    $.ajax({
                        url: "{{ route('stagings-in.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        success: function(response) {

                            $('#addStaging').modal('hide');
                            $('#formStaging')[0].reset();
                            addItemSelect.clear();
                            $('#addItemId').val('');
                            $('#addItemOwner').val('');

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

                        url: "{{ route('stagings-in.edit', ':id') }}".replace(':id', id),
                        type: 'GET',

                        success: function(res) {

                            $('#editId').val(res.id);
                            $('#editPoNumber').val(res.po_number);
                            $('#editArrivalDate').val(res.arrival_date);
                            $('#editSupplierOrigin').val(res.supplier_origin);
                            $('#editItemOwner').val(res.item_owner);
                            $('#editIncoterms').val(res.incoterms);
                            $('#editQty').val(res.qty);
                            $('#editLocation').val(res.location);
                            $('#editStatus').val(res.status);
                            $('#editNotes').val(res.notes);

                            loadItemLocations(res.item_id, editWarehouseLocationSelect,
                                '#editWarehouseLocationStock', res.warehouse_location,
                                editLotSelect, '#editLotWrapper', res.lot);

                            $('#editItemId').val(res.item_id);

                            editItemSelect.clear(true);
                            editItemSelect.clearOptions();
                            editItemSelect.loadedSearches = {};

                            // Muat ulang seluruh daftar barang di background supaya,
                            // begitu user menghapus barang yang sedang dipilih,
                            // pilihan lain sudah langsung tersedia (tidak perlu
                            // mengetik dulu untuk memicu pencarian).
                            editItemSelect.load('');

                            if (res.item_id && (res.item_code || res.item_name)) {

                                let label = [res.item_code, res.item_name]
                                    .filter(Boolean)
                                    .join(' | ');

                                editItemSelect.addOption({
                                    id: res.item_id,
                                    text: label,
                                    item_code: res.item_code,
                                    item_name: res.item_name,
                                    item_owner: res.item_owner
                                });

                                editItemSelect.setValue(res.item_id, true);
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
                $(document).on('submit', '#formEditStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    // Kalau field lot sedang tampil (lebih dari satu lot untuk
                    // barang + lokasi ini) tapi belum dipilih, jangan submit.
                    if ($('#editLotWrapper').is(':visible') &&
                        editLotSelect.options && Object.keys(editLotSelect.options).length > 1 &&
                        !editLotSelect.getValue()) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Silakan pilih lot terlebih dahulu',
                            timer: 2500,
                            showConfirmButton: false
                        });

                        return;
                    }

                    let id = $('#editId').val();

                    $.ajax({

                        url: "{{ route('stagings-in.update', ':id') }}".replace(':id', id),
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
                    editItemSelect.clear();
                    editItemSelect.clearOptions();
                    editItemSelect.loadedSearches = {};
                    $('#editItemId').val('');
                    $('#editItemOwner').val('');
                    editWarehouseLocationSelect.clear(true);
                    editWarehouseLocationSelect.clearOptions();
                    $('#editWarehouseLocationStock').text('');
                    editLotSelect.clear(true);
                    editLotSelect.clearOptions();
                    $('#editLotWrapper').hide();
                });

                // ================= PINDAHKAN ITEM =================
                let moveLocationSelect;

                function initMoveLocationSelect() {

                    if (moveLocationSelect) return;

                    moveLocationSelect = new TomSelect('#moveLocationSelect', {
                        create: true,
                        persist: false,
                        createOnBlur: true,
                        allowEmptyOption: true,
                        placeholder: 'Pilih atau ketik lokasi gudang...',
                        valueField: 'value',
                        labelField: 'text',
                        searchField: ['text'],
                        preload: true,
                        load: function(query, callback) {
                            $.ajax({
                                url: "{{ route('stagings-in.warehouse-locations') }}",
                                type: 'GET',
                                success: function(res) {
                                    callback((res || []).map(function(name) {
                                        return {
                                            value: name,
                                            text: name
                                        };
                                    }));
                                },
                                error: function() {
                                    callback();
                                }
                            });
                        }
                    });
                }

                // buka modal & isi ringkasan item
                $(document).on('click', '.btnMove', function() {

                    initMoveLocationSelect();

                    let $btn = $(this);
                    let qty = parseInt($btn.data('qty')) || 0;

                    $('#formMoveStaging').data('staging-id', $btn.data('id'));

                    $('#moveItemCode').text($btn.data('code') || '-');
                    $('#moveItemName').text($btn.data('name') || '-');

                    let metaParts = [];
                    if ($btn.data('po')) metaParts.push($btn.data('po'));
                    if ($btn.data('owner')) metaParts.push($btn.data('owner'));
                    if ($btn.data('arrival')) metaParts.push($btn.data('arrival'));
                    $('#moveItemMeta').text(metaParts.join(' · '));

                    $('#moveFromLocation').text($btn.data('location') || '-');
                    $('#moveQtyBadge').text(qty + ' pcs');

                    $('#moveQty').val(qty).attr('max', qty);
                    $('#moveQtyHelp').text('Maksimal ' + qty + ' pcs. Sisa tetap di staging in.');

                    // field masuk stok
                    $('#').val($btn.data('po') || '');
                    $('#moveTransactionDate').val(new Date().toISOString().slice(0, 10));
                    $('#moveNotes').val('');

                    // field staging out
                    $('#moveSoNumber').val('');
                    $('#moveCustomer').val('');
                    $('#moveLineItem').val($btn.data('name') || '');
                    $('#moveDeliveryInstructionDate').val('');

                    $('.tujuan-option').removeClass('active');
                    $('.tujuan-option[data-tujuan="stock"]').addClass('active');
                    $('#moveDestination').val('stock');
                    $('#moveToLocationLabel').text('Masuk Stok');
                    $('#fieldsMasukStok').removeClass('d-none');
                    $('#fieldsStagingOut').addClass('d-none');

                    if (moveLocationSelect) {
                        moveLocationSelect.clear();
                    }

                });

                // toggle kartu tujuan
                $(document).on('click', '.tujuan-option', function() {

                    $('.tujuan-option').removeClass('active');
                    $(this).addClass('active');

                    let tujuan = $(this).data('tujuan');
                    $('#moveDestination').val(tujuan);
                    $('#moveToLocationLabel').text(tujuan === 'stock' ? 'Masuk Stok' :
                        'Staging Out');

                    if (tujuan === 'stock') {
                        $('#fieldsMasukStok').removeClass('d-none');
                        $('#fieldsStagingOut').addClass('d-none');
                    } else {
                        $('#fieldsMasukStok').addClass('d-none');
                        $('#fieldsStagingOut').removeClass('d-none');
                    }

                });

                // simpan perpindahan (AJAX)
                $(document).on('submit', '#formMoveStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $(this).data('staging-id');

                    if (!id) return;

                    let tujuan = $('#moveDestination').val();

                    let url = tujuan === 'stock' ?
                        "{{ route('stagings-in.move-to-stock', ':id') }}".replace(':id',
                            id) :
                        "{{ route('stagings-in.move-to-staging-out', ':id') }}".replace(
                            ':id', id);

                    $.ajax({

                        url: url,
                        method: 'POST',
                        data: $(this).serialize(),

                        beforeSend: function() {
                            $('.btnSaveMove').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveMove').prop('disabled', false);

                            $('#moveStagingModal').modal('hide');

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

                            $('.btnSaveMove').prop('disabled', false);

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

                $('#moveStagingModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                    $('#moveQtyHelp').text('');
                    $('.tujuan-option').removeClass('active');
                    $('.tujuan-option[data-tujuan="stock"]').addClass('active');
                    $('#moveDestination').val('stock');
                    $('#moveToLocationLabel').text('Masuk Stok');
                    $('#fieldsMasukStok').removeClass('d-none');
                    $('#fieldsStagingOut').addClass('d-none');
                    if (moveLocationSelect) moveLocationSelect.clear();
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
                let location = $('#filterLocation').val();
                let status = $('#filterStatus').val();
                let incoterms = $('#filterIncoterms').val();
                let owner = $('#filterOwner').val();
                let supplier = $('#filterSupplier').val();
                let search = $('#customSearch').val().trim();

                let url = new URL("{{ route('stagings-in.export') }}");

                if (start) url.searchParams.append('start_date', start);
                if (end) url.searchParams.append('end_date', end);
                if (location) url.searchParams.append('location', location);
                if (status) url.searchParams.append('status', status);
                if (incoterms) url.searchParams.append('incoterms', incoterms);
                if (owner) url.searchParams.append('item_owner', owner);
                if (supplier) url.searchParams.append('supplier_origin', supplier);
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
            let selectedStagingIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedStagingIds.size);
                $('#bulkActionBar').toggleClass('d-none', selectedStagingIds.size === 0);
            }

            function resetStagingSelection() {
                selectedStagingIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedStagingIds.add(id);
                } else {
                    selectedStagingIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedStagingIds.add(id);
                    } else {
                        selectedStagingIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            $('#staging').on('draw.dt', function() {
                resetStagingSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedStagingIds.size === 0) return;

                Swal.fire({
                    title: `Hapus ${selectedStagingIds.size} data?`,
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
                        url: "{{ route('stagings-in.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedStagingIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetStagingSelection();

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
    @endpush
@endsection