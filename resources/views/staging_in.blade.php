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
                <button type="button" class="btn-sm btn btn-outline-danger d-none me-1" id="btnBulkDelete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Hapus (<span id="selectedCount">0</span>)</span>
                </button>

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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fs-3 text-primary"
                                                width="1em" height="1em" viewBox="0 0 24 24">
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
                                                Pilih Mode Import
                                            </h6>

                                            <p class="text-muted small mb-3">
                                                Tentukan apa yang terjadi pada data staging in yang sudah ada.
                                            </p>

                                            <div class="form-check mb-2 p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="mode"
                                                    id="importModeAppend" value="append" checked>
                                                <label class="form-check-label w-100" for="importModeAppend">
                                                    <span class="fw-semibold d-block">Tambahkan saja</span>
                                                    <small class="text-muted">
                                                        Data lama tetap ada, baris dari file Excel ditambahkan
                                                        sebagai data baru.
                                                    </small>
                                                </label>
                                            </div>

                                            <div class="form-check p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="mode"
                                                    id="importModeReset" value="reset">
                                                <label class="form-check-label w-100" for="importModeReset">
                                                    <span class="fw-semibold d-block">Reset &amp; ganti semua</span>
                                                    <small class="text-muted">
                                                        Semua data staging in yang ada saat ini dihapus, lalu
                                                        diganti sepenuhnya dengan data dari file Excel.
                                                    </small>
                                                </label>
                                            </div>

                                            <div id="importResetWarning" class="alert alert-warning small mt-2 mb-0"
                                                style="display:none;">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Semua data staging in yang ada saat ini akan dihapus dan diganti
                                                dengan data dari file yang diupload. Data lama masih bisa
                                                dipulihkan lewat riwayat (history) jika diperlukan.
                                            </div>

                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    {{-- STEP 3 --}}
                                    <div class="d-flex gap-3">
                                        <div>
                                            <span class="badge rounded-circle bg-primary"
                                                style="width:32px;height:32px;line-height:24px;">
                                                3
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

                    <style>
                        #addStaging .modal-content {
                            border: 0;
                            border-radius: 1.5rem;
                            overflow: hidden;
                            box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
                        }

                        /* ---------- HERO (header + No. PO + tanggal) ---------- */
                        #addStaging .as-hero {
                            padding: 1.5rem 2rem 1.25rem;
                            background: #fff;
                            border-bottom: 1px solid #eceef5;
                        }

                        #addStaging .as-hero-icon {
                            width: 48px;
                            height: 48px;
                            border-radius: 1rem;
                            display: grid;
                            place-items: center;
                            font-size: 1.5rem;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStaging .as-hero h4 {
                            color: #2b3350;
                            letter-spacing: -.01em;
                        }

                        #addStaging .as-hero small {
                            color: #8a93a8;
                        }

                        #addStaging .as-po-card {
                            margin: 1.25rem 2rem 0;
                            padding: 1.1rem 1.25rem;
                            background: #fff;
                            border-radius: 1.1rem;
                            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                        }

                        /* ---------- BODY ---------- */
                        #addStaging .as-body {
                            padding: 1.5rem 2rem .5rem;
                            background: #f5f6fb;
                        }

                        #addStaging .as-label {
                            display: block;
                            margin-bottom: .3rem;
                            font-size: .68rem;
                            font-weight: 700;
                            letter-spacing: .06em;
                            text-transform: uppercase;
                            color: #8a93a8;
                        }

                        #addStaging .as-field {
                            position: relative;
                        }

                        #addStaging .as-field>i {
                            position: absolute;
                            left: .8rem;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #a5adc2;
                            font-size: 1.05rem;
                            pointer-events: none;
                        }

                        #addStaging .as-input,
                        #addStaging .as-select {
                            width: 100%;
                            border: 1.5px solid #e6e9f2;
                            background: #f8f9fd;
                            border-radius: .75rem;
                            padding: .5rem .8rem;
                            font-size: .875rem;
                            color: #2b3350;
                            transition: border-color .15s, box-shadow .15s, background .15s;
                        }

                        #addStaging .as-field>i+.as-input,
                        #addStaging .as-field>i+.as-select {
                            padding-left: 2.35rem;
                        }

                        #addStaging .as-input:focus,
                        #addStaging .as-select:focus {
                            outline: 0;
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #addStaging .as-input-lg {
                            padding-top: .65rem;
                            padding-bottom: .65rem;
                            font-weight: 600;
                        }

                        #addStaging .as-hint {
                            display: inline-flex;
                            align-items: center;
                            gap: .3rem;
                            margin-top: .35rem;
                            font-size: .72rem;
                            color: #7c3aed;
                        }

                        /* ---------- DAFTAR BARANG ---------- */
                        #addStaging .as-section-title {
                            display: flex;
                            align-items: center;
                            gap: .6rem;
                            font-weight: 700;
                            color: #2b3350;
                        }

                        #addStaging .as-count {
                            min-width: 1.6rem;
                            height: 1.6rem;
                            padding: 0 .5rem;
                            display: inline-grid;
                            place-items: center;
                            border-radius: 50rem;
                            font-size: .75rem;
                            color: #fff;
                            background: linear-gradient(135deg, #4f46e5, #7c3aed);
                        }

                        #addStaging .add-item-row {
                            position: relative;
                            background: #fff;
                            border-radius: 1.1rem;
                            padding: 1.1rem 1.25rem 1.25rem 1.6rem;
                            margin-bottom: 1rem;
                            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                            transition: box-shadow .2s;
                            animation: asRowIn .28s ease both;
                        }

                        #addStaging .add-item-row::before {
                            content: "";
                            position: absolute;
                            left: 0;
                            top: 1rem;
                            bottom: 1rem;
                            width: 5px;
                            border-radius: 0 6px 6px 0;
                            background: linear-gradient(180deg, #4f46e5, #db2777);
                        }

                        #addStaging .add-item-row:hover {
                            box-shadow: 0 10px 28px -10px rgba(79, 70, 229, .3);
                        }

                        #addStaging .add-item-row:focus-within {
                            z-index: 5;
                        }

                        @keyframes asRowIn {
                            from {
                                opacity: 0;
                                transform: translateY(8px);
                            }

                            to {
                                opacity: 1;
                                transform: none;
                            }
                        }

                        #addStaging .as-row-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: .5rem;
                            font-weight: 700;
                            font-size: .85rem;
                            color: #2b3350;
                        }

                        #addStaging .as-row-badge .row-number {
                            width: 1.8rem;
                            height: 1.8rem;
                            display: grid;
                            place-items: center;
                            border-radius: .6rem;
                            font-size: .8rem;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStaging .btnRemoveItemRow {
                            width: 2rem;
                            height: 2rem;
                            padding: 0;
                            display: grid;
                            place-items: center;
                            border: 0;
                            border-radius: .65rem;
                            color: #e11d48;
                            background: #fff1f4;
                        }

                        #addStaging .btnRemoveItemRow:hover:not(:disabled) {
                            color: #fff;
                            background: #e11d48;
                        }

                        #addStaging .btnRemoveItemRow:disabled {
                            opacity: .35;
                        }

                        #addStaging .as-add-btn {
                            width: 100%;
                            padding: .8rem;
                            border: 2px dashed #c7cbe6;
                            border-radius: 1rem;
                            background: transparent;
                            color: #5b57d6;
                            font-weight: 600;
                            font-size: .875rem;
                            transition: all .15s;
                        }

                        #addStaging .as-add-btn:hover {
                            border-color: #7c3aed;
                            background: #fff;
                            color: #7c3aed;
                            box-shadow: 0 8px 20px -10px rgba(124, 58, 237, .45);
                        }

                        #addStaging .as-pill-btn {
                            border: 0;
                            border-radius: 50rem;
                            padding: .4rem 1rem;
                            font-size: .8rem;
                            font-weight: 600;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStaging .as-pill-btn:hover {
                            color: #fff;
                            background: #4f46e5;
                        }

                        /* ---------- FOOTER ---------- */
                        #addStaging .as-footer {
                            display: flex;
                            justify-content: flex-end;
                            gap: .6rem;
                            padding: 1rem 2rem 1.4rem;
                            background: #f5f6fb;
                        }

                        #addStaging .as-btn-save {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.6rem;
                            font-weight: 600;
                            color: #fff;
                            background: linear-gradient(135deg, #4f46e5, #7c3aed);
                            box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65);
                        }

                        #addStaging .as-btn-save:hover {
                            color: #fff;
                            filter: brightness(1.08);
                        }

                        #addStaging .as-btn-cancel {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.3rem;
                            font-weight: 600;
                            color: #5a6482;
                            background: #e9ebf5;
                        }

                        /* ---------- TOM SELECT biar senada ---------- */
                        #addStaging .ts-wrapper .ts-control {
                            min-height: 40px;
                            border: 1.5px solid #e6e9f2;
                            border-radius: .75rem;
                            background: #f8f9fd;
                            padding: .45rem .8rem;
                            box-shadow: none;
                        }

                        #addStaging .ts-wrapper.focus .ts-control {
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #addStaging .ts-dropdown {
                            z-index: 1070;
                            border-radius: .75rem;
                            border: 0;
                            box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3);
                            overflow: hidden;
                        }

                        #addStaging .ts-dropdown .active {
                            background: #eef0ff;
                            color: #4f46e5;
                        }
                    </style>

                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <form action="{{ route('stagings-in.store') }}" method="POST" id="formStaging"
                                class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                                @csrf

                                {{-- ================= HERO: No. PO & Tanggal (diisi 1x) ================= --}}
                                <div class="as-hero flex-shrink-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="as-hero-icon"><i class="bx bx-package"></i></div>
                                            <div>
                                                <h4 class="mb-0 fw-bold" id="addStagingLabel">Tambah Item Staging</h4>
                                                <small>
                                                    Satu PO, banyak barang — isi sekali saja.
                                                </small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                </div>

                                <div class="as-po-card flex-shrink-0">
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="as-label">No. PO</label>
                                            <div class="as-field">
                                                <i class="bx bx-receipt"></i>
                                                <input type="text" class="as-input as-input-lg" name="po_number"
                                                    placeholder="Contoh: PO-2026-0001">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="as-label">Tanggal Kedatangan</label>
                                            <div class="as-field">
                                                <i class="bx bx-calendar"></i>
                                                <input type="date" class="as-input as-input-lg" id="addArrivalDate"
                                                    value="{{ now()->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ================= DAFTAR BARANG ================= --}}
                                <div class="as-body flex-grow-1 overflow-auto">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="as-section-title">
                                            <span>Daftar Barang</span>
                                            <span class="as-count" id="addItemCount">1</span>
                                        </div>
                                        <button type="button" class="as-pill-btn btnAddItemRow">
                                            <i class="bx bx-plus"></i> Tambah Barang
                                        </button>
                                    </div>

                                    <div id="addItemRows"></div>

                                    <button type="button" class="as-add-btn btnAddItemRow mb-3">
                                        <i class="bx bx-plus-circle me-1"></i> Tambah barang lainnya
                                    </button>

                                </div>

                                <div class="as-footer flex-shrink-0">
                                    <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="as-btn-save" id="btnSaveStaging">
                                        <i class="bx bx-save me-1"></i> Simpan Staging
                                    </button>
                                </div>

                            </form>

                            {{-- Template 1 baris barang. __INDEX__ diganti lewat JS. --}}
                            <template id="addItemRowTemplate">
                                <div class="add-item-row">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="as-row-badge">
                                            <span class="row-number"></span> Barang
                                        </span>
                                        <button type="button" class="btnRemoveItemRow" title="Hapus barang ini">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-lg-6">
                                            <label class="as-label">Barang</label>
                                            <select class="item-select" name="items[__INDEX__][item_id]"
                                                placeholder="Cari kode / nama barang..."></select>
                                        </div>

                                        <div class="col-6 col-lg-2">
                                            <label class="as-label">Qty</label>
                                            <div class="as-field">
                                                <i class="bx bx-cube-alt"></i>
                                                <input type="number" class="as-input" name="items[__INDEX__][qty]"
                                                    min="0" placeholder="0">
                                            </div>
                                        </div>

                                        <div class="col-6 col-lg-4">
                                            <label class="as-label">Tgl Kedatangan</label>
                                            <input type="date" class="as-input item-arrival"
                                                name="items[__INDEX__][arrival_date]">
                                        </div>

                                        <div class="col-md-6 col-lg-3">
                                            <label class="as-label">Asal Supplier</label>
                                            <div class="as-field">
                                                <i class="bx bx-buildings"></i>
                                                <input type="text" class="as-input"
                                                    name="items[__INDEX__][supplier_origin]" placeholder="PT. ABC">
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3">
                                            <label class="as-label">Incoterms</label>
                                            <div class="as-field">
                                                <i class="bx bx-world"></i>
                                                <select class="as-select" name="items[__INDEX__][incoterms]">
                                                    <option value="" selected>Pilih incoterms</option>
                                                    @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                                        <option value="{{ $incoterm }}">{{ $incoterm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3">
                                            <label class="as-label">Status</label>
                                            <div class="as-field">
                                                <i class="bx bx-flag"></i>
                                                <select class="as-select" name="items[__INDEX__][status]">
                                                    <option value="" selected>Pilih status</option>
                                                    @foreach (\App\Models\StagingIn::allStatusOptions() as $statusItem)
                                                        <option value="{{ $statusItem }}">{{ $statusItem }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3">
                                            <label class="as-label">Lokasi</label>
                                            <div class="as-field">
                                                <i class="bx bx-current-location"></i>
                                                <select class="as-select" name="items[__INDEX__][location]">
                                                    <option value="" selected>Pilih lokasi</option>
                                                    @foreach (\App\Models\StagingIn::LOCATIONS as $location)
                                                        <option value="{{ $location }}">{{ $location }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Keterangan</label>
                                            <div class="as-field">
                                                <i class="bx bx-note"></i>
                                                <textarea class="as-input" name="items[__INDEX__][notes]" rows="2" placeholder="Opsional"></textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive text-nowrap">

            <div class="px-3 pt-3">

                <style>
                    .as-field:has(textarea) {
                        align-items: flex-start !important;
                    }

                    .as-field:has(textarea) i.bx {
                        position: absolute !important;
                        top: 12px !important;
                        bottom: auto !important;
                        inset-block: 12px auto !important;
                        margin: 0 !important;
                        transform: none !important;
                        translate: none !important;
                    }

                    .as-field textarea.as-input {
                        resize: vertical;
                        min-height: 64px;
                    }

                    .as-field textarea.as-input {
                        resize: vertical;
                        min-height: 60px;
                    }

                    .as-field textarea.as-input {
                        resize: vertical;
                        min-height: 60px;
                    }

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

                    /* Banner filter dari dashboard */
                    .fx-banner {
                        display: flex;
                        align-items: center;
                        gap: .6rem;
                        margin-bottom: .9rem;
                        padding: .6rem .6rem .6rem .9rem;
                        border: 1px solid rgba(var(--fx-accent-rgb), .3);
                        border-radius: .85rem;
                        background: rgba(var(--fx-accent-rgb), .07);
                        color: var(--bs-body-color);
                        font-size: .86rem;
                        white-space: normal;
                    }

                    .fx-banner>i {
                        color: var(--fx-accent);
                        font-size: 1.05rem;
                    }

                    .fx-banner>span {
                        flex: 1;
                    }

                    .fx-banner-close {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 1.7rem;
                        height: 1.7rem;
                        padding: 0;
                        border: 0;
                        border-radius: 50%;
                        background: transparent;
                        color: var(--fx-muted);
                        font-size: .72rem;
                    }

                    .fx-banner-close:hover {
                        background: rgba(var(--fx-accent-rgb), .14);
                        color: var(--fx-accent);
                    }

                    @media (max-width: 575.98px) {
                        .fx-actions {
                            width: 100%;
                            justify-content: space-between;
                        }

                        .fx-chip-date {
                            flex: 1 1 100%;
                        }

                        .fx-chip-date .date-range-wrapper {
                            flex: 1;
                        }

                        .fx-chip-date input[type="text"] {
                            width: 100%;
                        }

                        .date-range-panel {
                            left: -3rem;
                            right: 0;
                            width: auto;
                            min-width: 260px;
                        }
                    }

                    @media (prefers-reduced-motion: reduce) {

                        .fx *,
                        .date-range-panel.show {
                            transition: none !important;
                            animation: none !important;
                        }
                    }
                </style>

                @if ($activeFilter === 'overdue')
                    <div class="fx-banner" id="activeFilterBanner" role="status">
                        <i class="bx bx-filter-alt"></i>
                        <span>Menampilkan barang staging in yang sudah lebih dari 7 hari.</span>
                        <button type="button" class="fx-banner-close" id="clearActiveFilterBtn"
                            aria-label="Hapus filter"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endif

                <div class="fx" id="filterBar">

                    <div class="fx-top">

                        <!-- Pencarian -->
                        <div class="fx-search">
                            <i class="bi bi-search fx-search-icon"></i>
                            <input type="text" id="customSearch" autocomplete="off"
                                placeholder="Cari No. PO, item, atau supplier" aria-label="Cari">
                            <button type="button" class="fx-search-clear" id="customSearchClear"
                                aria-label="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                        </div>

                        <div class="fx-actions">
                            <span class="fx-count" id="filterCount" aria-live="polite"></span>
                            <button type="button" class="fx-reset" id="resetFilterInline" title="Kembalikan semua filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset</span>
                            </button>
                        </div>

                    </div>

                    <div class="fx-chips">

                        
                        <!-- Tanggal kedatangan -->
                        <div class="fx-chip fx-chip-date">
                            <i class="bi bi-calendar-event"></i>
                            <label for="filterDateRange">Kedatangan</label>

                            <div class="date-range-wrapper">
                                <input type="text" id="filterDateRange" placeholder="Semua tanggal"
                                    title="Rentang tanggal kedatangan" aria-label="Rentang tanggal kedatangan" readonly
                                    autocomplete="off">

                                <div class="date-range-panel" id="dateRangePanel">
                                    <div class="fx-presets">
                                        <button type="button" class="fx-preset" data-preset="today">Hari ini</button>
                                        <button type="button" class="fx-preset" data-preset="7d">7 hari terakhir</button>
                                        <button type="button" class="fx-preset" data-preset="month">Bulan ini</button>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Dari</label>
                                        <input type="date" class="form-control form-control-sm" id="filterStartDate">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="filterEndDate">
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="button" class="btn btn-sm btn-light flex-shrink-0"
                                            id="dateRangeClear">Hapus</button>
                                        <button type="button" class="btn btn-sm btn-primary w-100"
                                            id="dateRangeApply">Terapkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="fx-chip">
                            <i class="bi bi-flag"></i>
                            <label for="filterStatus">Status</label>
                            <select id="filterStatus">
                                <option value="">Semua</option>
                                @foreach ($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Incoterms -->
                        <div class="fx-chip">
                            <i class="bi bi-globe"></i>
                            <label for="filterIncoterms">Incoterms</label>
                            <select id="filterIncoterms">
                                <option value="">Semua</option>
                                @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                    <option value="{{ $incoterm }}">{{ $incoterm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pemilik Barang -->
                        <div class="fx-chip">
                            <i class="bi bi-person"></i>
                            <label for="filterOwner">Pemilik</label>
                            <select id="filterOwner">
                                <option value="">Semua</option>
                                @foreach ($ownerOptions as $ownerOption)
                                    <option value="{{ $ownerOption }}">{{ $ownerOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Asal Supplier -->
                        <div class="fx-chip">
                            <i class="bi bi-building"></i>
                            <label for="filterSupplier">Supplier</label>
                            <select id="filterSupplier">
                                <option value="">Semua</option>
                                @foreach ($supplierOptions as $supplierOption)
                                    <option value="{{ $supplierOption }}">{{ $supplierOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lokasi -->
                        <div class="fx-chip">
                            <i class="bi bi-geo-alt"></i>
                            <label for="filterLocation">Lokasi</label>
                            <select id="filterLocation">
                                <option value="">Semua</option>
                                @foreach (\App\Models\StagingIn::LOCATIONS as $locationOption)
                                    <option value="{{ $locationOption }}">{{ $locationOption }}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                </div>


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

        <style>
            #editStagingModal .modal-content,
            #moveStagingModal .modal-content {
                border: 0;
                border-radius: 1.5rem;
                overflow: hidden;
                box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
            }

            #editStagingModal .as-hero,
            #moveStagingModal .as-hero {
                padding: 1.5rem 2rem 1.25rem;
                background: #fff;
                border-bottom: 1px solid #eceef5;
            }

            #editStagingModal .as-hero-icon,
            #moveStagingModal .as-hero-icon {
                width: 48px;
                height: 48px;
                border-radius: 1rem;
                display: grid;
                place-items: center;
                font-size: 1.5rem;
                color: #4f46e5;
                background: #eef0ff;
            }

            #editStagingModal .as-hero h4,
            #moveStagingModal .as-hero h4 {
                color: #2b3350;
                letter-spacing: -.01em;
            }

            #editStagingModal .as-hero small,
            #moveStagingModal .as-hero small {
                color: #8a93a8;
            }

            #editStagingModal .as-po-card,
            #moveStagingModal .as-po-card {
                margin: 1.25rem 2rem 0;
                padding: 1.1rem 1.25rem;
                background: #fff;
                border-radius: 1.1rem;
                box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
            }

            #editStagingModal .as-body,
            #moveStagingModal .as-body {
                padding: 1.5rem 2rem .5rem;
                background: #f5f6fb;
            }

            #editStagingModal .as-label,
            #moveStagingModal .as-label {
                display: block;
                margin-bottom: .3rem;
                font-size: .68rem;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #8a93a8;
            }

            #editStagingModal .as-field,
            #moveStagingModal .as-field {
                position: relative;
            }

            #editStagingModal .as-field>i,
            #moveStagingModal .as-field>i {
                position: absolute;
                left: .8rem;
                top: 50%;
                transform: translateY(-50%);
                color: #a5adc2;
                font-size: 1.05rem;
                pointer-events: none;
            }

            #editStagingModal .as-input,
            #editStagingModal .as-select,
            #moveStagingModal .as-input,
            #moveStagingModal .as-select {
                width: 100%;
                border: 1.5px solid #e6e9f2;
                background: #f8f9fd;
                border-radius: .75rem;
                padding: .5rem .8rem;
                font-size: .875rem;
                color: #2b3350;
                transition: border-color .15s, box-shadow .15s, background .15s;
            }

            #editStagingModal .as-field>i+.as-input,
            #editStagingModal .as-field>i+.as-select,
            #moveStagingModal .as-field>i+.as-input,
            #moveStagingModal .as-field>i+.as-select {
                padding-left: 2.35rem;
            }

            #editStagingModal .as-input:focus,
            #editStagingModal .as-select:focus,
            #moveStagingModal .as-input:focus,
            #moveStagingModal .as-select:focus {
                outline: 0;
                background: #fff;
                border-color: #7c3aed;
                box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
            }

            #editStagingModal .as-input-lg,
            #moveStagingModal .as-input-lg {
                padding-top: .65rem;
                padding-bottom: .65rem;
                font-weight: 600;
            }

            #editStagingModal .as-hint,
            #moveStagingModal .as-hint {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                margin-top: .35rem;
                font-size: .72rem;
                color: #7c3aed;
            }

            #editStagingModal .as-section-title,
            #moveStagingModal .as-section-title {
                display: flex;
                align-items: center;
                gap: .6rem;
                font-weight: 700;
                color: #2b3350;
            }

            #editStagingModal .as-count,
            #moveStagingModal .as-count {
                min-width: 1.6rem;
                height: 1.6rem;
                padding: 0 .5rem;
                display: inline-grid;
                place-items: center;
                border-radius: 50rem;
                font-size: .75rem;
                color: #fff;
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
            }

            #editStagingModal .add-item-row,
            #moveStagingModal .add-item-row {
                position: relative;
                background: #fff;
                border-radius: 1.1rem;
                padding: 1.1rem 1.25rem 1.25rem 1.6rem;
                margin-bottom: 1rem;
                box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                transition: box-shadow .2s;
                animation: asRowIn .28s ease both;
            }

            #editStagingModal .add-item-row::before,
            #moveStagingModal .add-item-row::before {
                content: "";
                position: absolute;
                left: 0;
                top: 1rem;
                bottom: 1rem;
                width: 5px;
                border-radius: 0 6px 6px 0;
                background: linear-gradient(180deg, #4f46e5, #db2777);
            }

            #editStagingModal .add-item-row:hover,
            #moveStagingModal .add-item-row:hover {
                box-shadow: 0 10px 28px -10px rgba(79, 70, 229, .3);
            }

            #editStagingModal .add-item-row:focus-within,
            #moveStagingModal .add-item-row:focus-within {
                z-index: 5;
            }

            #editStagingModal .as-row-badge,
            #moveStagingModal .as-row-badge {
                display: inline-flex;
                align-items: center;
                gap: .5rem;
                font-weight: 700;
                font-size: .85rem;
                color: #2b3350;
            }

            #editStagingModal .as-row-badge .row-number,
            #moveStagingModal .as-row-badge .row-number {
                width: 1.8rem;
                height: 1.8rem;
                display: grid;
                place-items: center;
                border-radius: .6rem;
                font-size: .8rem;
                color: #4f46e5;
                background: #eef0ff;
            }

            #editStagingModal .btnRemoveItemRow,
            #moveStagingModal .btnRemoveItemRow {
                width: 2rem;
                height: 2rem;
                padding: 0;
                display: grid;
                place-items: center;
                border: 0;
                border-radius: .65rem;
                color: #e11d48;
                background: #fff1f4;
            }

            #editStagingModal .btnRemoveItemRow:hover:not(:disabled),
            #moveStagingModal .btnRemoveItemRow:hover:not(:disabled) {
                color: #fff;
                background: #e11d48;
            }

            #editStagingModal .btnRemoveItemRow:disabled,
            #moveStagingModal .btnRemoveItemRow:disabled {
                opacity: .35;
            }

            #editStagingModal .as-add-btn,
            #moveStagingModal .as-add-btn {
                width: 100%;
                padding: .8rem;
                border: 2px dashed #c7cbe6;
                border-radius: 1rem;
                background: transparent;
                color: #5b57d6;
                font-weight: 600;
                font-size: .875rem;
                transition: all .15s;
            }

            #editStagingModal .as-add-btn:hover,
            #moveStagingModal .as-add-btn:hover {
                border-color: #7c3aed;
                background: #fff;
                color: #7c3aed;
                box-shadow: 0 8px 20px -10px rgba(124, 58, 237, .45);
            }

            #editStagingModal .as-pill-btn,
            #moveStagingModal .as-pill-btn {
                border: 0;
                border-radius: 50rem;
                padding: .4rem 1rem;
                font-size: .8rem;
                font-weight: 600;
                color: #4f46e5;
                background: #eef0ff;
            }

            #editStagingModal .as-pill-btn:hover,
            #moveStagingModal .as-pill-btn:hover {
                color: #fff;
                background: #4f46e5;
            }

            #editStagingModal .as-footer,
            #moveStagingModal .as-footer {
                display: flex;
                justify-content: flex-end;
                gap: .6rem;
                padding: 1rem 2rem 1.4rem;
                background: #f5f6fb;
            }

            #editStagingModal .as-btn-save,
            #moveStagingModal .as-btn-save {
                border: 0;
                border-radius: .8rem;
                padding: .6rem 1.6rem;
                font-weight: 600;
                color: #fff;
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
                box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65);
            }

            #editStagingModal .as-btn-save:hover,
            #moveStagingModal .as-btn-save:hover {
                color: #fff;
                filter: brightness(1.08);
            }

            #editStagingModal .as-btn-cancel,
            #moveStagingModal .as-btn-cancel {
                border: 0;
                border-radius: .8rem;
                padding: .6rem 1.3rem;
                font-weight: 600;
                color: #5a6482;
                background: #e9ebf5;
            }

            #editStagingModal .ts-wrapper .ts-control,
            #moveStagingModal .ts-wrapper .ts-control {
                min-height: 40px;
                border: 1.5px solid #e6e9f2;
                border-radius: .75rem;
                background: #f8f9fd;
                padding: .45rem .8rem;
                box-shadow: none;
            }

            #editStagingModal .ts-wrapper.focus .ts-control,
            #moveStagingModal .ts-wrapper.focus .ts-control {
                background: #fff;
                border-color: #7c3aed;
                box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
            }

            #editStagingModal .ts-dropdown,
            #moveStagingModal .ts-dropdown {
                z-index: 1070;
                border-radius: .75rem;
                border: 0;
                box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3);
                overflow: hidden;
            }

            #editStagingModal .ts-dropdown .active,
            #moveStagingModal .ts-dropdown .active {
                background: #eef0ff;
                color: #4f46e5;
            }

            /* ---------- Kartu seksi (edit & pindah) ---------- */
            #editStagingModal .as-card,
            #moveStagingModal .as-card {
                position: relative;
                background: #fff;
                border-radius: 1.1rem;
                padding: 1.25rem 1.4rem 1.4rem 1.7rem;
                box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
            }

            #editStagingModal .as-card::before,
            #moveStagingModal .as-card::before {
                content: "";
                position: absolute;
                left: 0;
                top: 1.1rem;
                bottom: 1.1rem;
                width: 5px;
                border-radius: 0 6px 6px 0;
                background: linear-gradient(180deg, #4f46e5, #db2777);
            }

            #editStagingModal .as-card-title,
            #moveStagingModal .as-card-title {
                display: flex;
                align-items: center;
                gap: .7rem;
                margin-bottom: 1.1rem;
            }

            #editStagingModal .as-card-title strong,
            #moveStagingModal .as-card-title strong {
                display: block;
                color: #2b3350;
                font-size: .98rem;
            }

            #editStagingModal .as-card-title small,
            #moveStagingModal .as-card-title small {
                color: #8a93a8;
            }

            #editStagingModal .as-card-icon,
            #moveStagingModal .as-card-icon {
                width: 2.4rem;
                height: 2.4rem;
                flex-shrink: 0;
                display: grid;
                place-items: center;
                border-radius: .8rem;
                font-size: 1.2rem;
                color: #4f46e5;
                background: #eef0ff;
            }

            #editStagingModal .as-card-icon.is-pink,
            #moveStagingModal .as-card-icon.is-pink {
                color: #db2777;
                background: #fdf0f7;
            }

            #editStagingModal .as-field.as-field-top>i,
            #moveStagingModal .as-field.as-field-top>i {
                top: .85rem;
                transform: none;
            }

            #editStagingModal textarea.as-input,
            #moveStagingModal textarea.as-input {
                resize: vertical;
            }

            #editStagingModal .as-input:disabled,
            #editStagingModal .as-input[readonly] {
                background: #eef0f7;
                color: #6b7385;
                cursor: not-allowed;
            }

            #moveStagingModal .as-help {
                display: block;
                margin-top: .35rem;
                font-size: .74rem;
                color: #8a93a8;
            }

            /* ---------- Ringkasan item (pindah) ---------- */
            #moveStagingModal .as-summary {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1.25rem;
                padding: 1.1rem 1.4rem;
                background: #fff;
                border-radius: 1.1rem;
                box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
            }

            #moveStagingModal .as-chip {
                display: inline-flex;
                align-items: center;
                padding: .25rem .75rem;
                border-radius: 50rem;
                font-size: .74rem;
                font-weight: 600;
            }

            #moveStagingModal .as-chip-gray {
                color: #5a6482;
                background: #eceef6;
            }

            #moveStagingModal .as-chip-indigo {
                color: #4f46e5;
                background: #eef0ff;
            }

            #moveStagingModal .as-qty {
                flex-shrink: 0;
                padding: .45rem 1rem;
                border-radius: 50rem;
                font-size: .85rem;
                font-weight: 700;
                color: #fff;
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
                box-shadow: 0 8px 18px -8px rgba(79, 70, 229, .6);
            }

            /* ---------- Pilihan tujuan (pindah) ---------- */
            #moveStagingModal .tujuan-option {
                height: 100%;
                padding: 1rem 1.1rem;
                background: #fff;
                border: 2px solid #e6e9f2 !important;
                border-radius: 1rem;
                cursor: pointer;
                transition: all .15s;
            }

            #moveStagingModal .tujuan-option:hover {
                border-color: #b9b4f7 !important;
            }

            #moveStagingModal .tujuan-option.active {
                border-color: #7c3aed !important;
                background: #f6f3ff;
                box-shadow: 0 10px 22px -12px rgba(124, 58, 237, .55);
            }

            #moveStagingModal .tujuan-option .as-tujuan-icon {
                width: 2.5rem;
                height: 2.5rem;
                margin-bottom: .6rem;
                display: grid;
                place-items: center;
                border-radius: .85rem;
                font-size: 1.25rem;
                color: #4f46e5;
                background: #eef0ff;
            }

            #moveStagingModal .tujuan-option[data-tujuan="out"] .as-tujuan-icon {
                color: #db2777;
                background: #fdf0f7;
            }

            #moveStagingModal .tujuan-option strong {
                display: block;
                color: #2b3350;
            }

            #moveStagingModal .tujuan-option small {
                color: #8a93a8;
            }
        </style>

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <form id="formEditStaging" class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="as-hero flex-shrink-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="as-hero-icon"><i class="bx bx-edit"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold">Edit Item Staging</h4>
                                    <small>Ubah data staging beserta lokasi penempatannya.</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <div class="as-body flex-grow-1 overflow-auto">
                        <div class="row g-4 mb-3">

                            {{-- ================= LEFT ================= --}}
                            <div class="col-lg-6">
                                <div class="as-card h-100">

                                    <div class="as-card-title">
                                        <div class="as-card-icon"><i class="bx bx-package"></i></div>
                                        <div>
                                            <strong>Informasi Barang</strong>
                                            <small>Lengkapi informasi barang yang di-staging.</small>
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label class="as-label">No. PO</label>
                                            <div class="as-field">
                                                <i class="bx bx-receipt"></i>
                                                <input type="text" class="as-input" name="po_number"
                                                    id="editPoNumber">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Barang</label>
                                            <select id="editItemSelect" placeholder="Cari kode / nama barang..."></select>
                                            <input type="hidden" name="item_id" id="editItemId">
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Pemilik Barang</label>
                                            <div class="as-field">
                                                <i class="bx bx-user"></i>
                                                <input type="text" class="as-input" id="editItemOwner" readonly
                                                    disabled>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Asal Supplier</label>
                                            <div class="as-field">
                                                <i class="bx bx-buildings"></i>
                                                <input type="text" class="as-input" name="supplier_origin"
                                                    id="editSupplierOrigin">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Incoterms</label>
                                            <div class="as-field">
                                                <i class="bx bx-world"></i>
                                                <select name="incoterms" class="as-select" id="editIncoterms">
                                                    <option value="">Pilih incoterms</option>
                                                    @foreach (\App\Models\StagingIn::INCOTERMS as $incoterm)
                                                        <option value="{{ $incoterm }}">{{ $incoterm }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Status</label>
                                            <div class="as-field">
                                                <i class="bx bx-flag"></i>
                                                <select name="status" class="as-select" id="editStatus">
                                                    <option value="">Pilih status</option>
                                                    @foreach (\App\Models\StagingIn::allStatusOptions() as $statusItem)
                                                        <option value="{{ $statusItem }}">{{ $statusItem }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- ================= RIGHT ================= --}}
                            <div class="col-lg-6">
                                <div class="as-card h-100">

                                    <div class="as-card-title">
                                        <div class="as-card-icon is-pink"><i class="bx bx-map-pin"></i></div>
                                        <div>
                                            <strong>Detail Kedatangan &amp; Lokasi</strong>
                                            <small>Tentukan jadwal, jumlah, dan lokasi penempatan barang.</small>
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="as-label">Tanggal Kedatangan</label>
                                            <div class="as-field">
                                                <i class="bx bx-calendar"></i>
                                                <input type="date" class="as-input" name="arrival_date"
                                                    id="editArrivalDate">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Qty</label>
                                            <div class="as-field">
                                                <i class="bx bx-cube-alt"></i>
                                                <input type="number" class="as-input" name="qty" min="0"
                                                    id="editQty">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Lokasi</label>
                                            <div class="as-field">
                                                <i class="bx bx-current-location"></i>
                                                <select name="location" class="as-select" id="editLocation">
                                                    @foreach (\App\Models\StagingIn::LOCATIONS as $location)
                                                        <option value="{{ $location }}">{{ $location }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Keterangan</label>
                                            <div class="as-field as-field-top">
                                                <i class="bx bx-note"></i>
                                                <textarea rows="5" class="as-input" name="notes" id="editNotes" placeholder="Opsional"></textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="as-footer flex-shrink-0">
                        <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="as-btn-save btnSaveEdit">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- ================= MODAL PINDAHKAN ITEM ================= --}}
    <div class="modal fade" id="moveStagingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="moveStagingLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <form id="formMoveStaging" class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                    @csrf

                    <input type="hidden" name="destination" id="moveDestination" value="stock">

                    <div class="as-hero flex-shrink-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="as-hero-icon"><i class="bx bx-transfer-alt"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold" id="moveStagingLabel">Pindahkan Item</h4>
                                    <small>Tentukan tujuan perpindahan barang dari staging in.</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <div class="as-body flex-grow-1 overflow-auto">

                        {{-- ================= RINGKASAN ITEM ================= --}}
                        <div class="as-summary">
                            <div>
                                <small class="text-muted d-block" id="moveItemCode"></small>
                                <div class="fw-bold" id="moveItemName"></div>
                                <small class="text-muted d-block mt-1" id="moveItemMeta"></small>

                                <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                    <span class="as-chip as-chip-gray" id="moveFromLocation"></span>
                                    <i class="bx bx-right-arrow-alt text-muted"></i>
                                    <span class="as-chip as-chip-indigo" id="moveToLocationLabel">Masuk Stok</span>
                                </div>
                            </div>

                            <span class="as-qty" id="moveQtyBadge"></span>
                        </div>

                        {{-- ================= TUJUAN ================= --}}
                        <label class="as-label">Tujuan</label>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="tujuan-option active" data-tujuan="stock">
                                    <div class="as-tujuan-icon"><i class="bx bx-building-house"></i></div>
                                    <strong>Masuk Stok</strong>
                                    <small>Barang disimpan sebagai stok gudang</small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="tujuan-option" data-tujuan="out">
                                    <div class="as-tujuan-icon"><i class="bx bx-trip"></i></div>
                                    <strong>Staging Out</strong>
                                    <small>Barang langsung dikirim ke customer</small>
                                </div>
                            </div>
                        </div>

                        {{-- ================= DETAIL PERPINDAHAN ================= --}}
                        <div class="as-card mb-3">

                            <div class="as-card-title">
                                <div class="as-card-icon is-pink"><i class="bx bx-transfer-alt"></i></div>
                                <div>
                                    <strong>Detail Perpindahan</strong>
                                    <small>Isi jumlah dan data tujuan barang.</small>
                                </div>
                            </div>

                            {{-- QTY (shared) --}}
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="as-label">Qty dipindah</label>
                                    <div class="as-field">
                                        <i class="bx bx-cube-alt"></i>
                                        <input type="number" class="as-input" name="qty" id="moveQty"
                                            min="1">
                                    </div>
                                    <small class="as-help" id="moveQtyHelp"></small>
                                </div>
                            </div>

                            {{-- FIELD: MASUK STOK --}}
                            <div id="fieldsMasukStok" class="mt-3">
                                <div class="row g-3">

                                    <div class="col-md-7">
                                        <label class="as-label">Lokasi Penyimpanan</label>
                                        <select id="moveLocationSelect" name="location"></select>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="as-label">Tanggal Masuk</label>
                                        <input type="date" class="as-input" name="transaction_date"
                                            id="moveTransactionDate" value="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="as-label">
                                            Lot <span class="fw-normal text-lowercase">(opsional)</span>
                                        </label>
                                        <select id="moveLotSelect" name="lot"></select>
                                        <small class="as-help">
                                            Pilih lot yang sudah ada di lokasi ini, atau ketik lot baru. Boleh dikosongkan.
                                        </small>
                                    </div>

                                    <div class="col-12">
                                        <label class="as-label">Catatan</label>
                                        <div class="as-field as-field-top">
                                            <i class="bx bx-note"></i>
                                            <textarea rows="3" class="as-input" name="notes" id="moveNotes" placeholder="Opsional"></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- FIELD: STAGING OUT --}}
                            <div id="fieldsStagingOut" class="mt-3 d-none">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="as-label">No. SO</label>
                                        <div class="as-field">
                                            <i class="bx bx-receipt"></i>
                                            <input type="text" class="as-input" name="so_number" id="moveSoNumber"
                                                placeholder="SO-2026-0001">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="as-label">Customer</label>
                                        <div class="as-field">
                                            <i class="bx bx-buildings"></i>
                                            <input type="text" class="as-input" name="customer" id="moveCustomer"
                                                placeholder="Nama customer">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="as-label">Line Item</label>
                                        <div class="as-field">
                                            <i class="bx bx-hash"></i>
                                            <input type="text" class="as-input" name="line_item" id="moveLineItem">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="as-label">Delivery Instruction</label>
                                        <input type="date" class="as-input" value="{{ date('Y-m-d') }}"
                                            name="delivery_instruction_date" id="moveDeliveryInstructionDate">
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="as-footer flex-shrink-0">
                        <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="as-btn-save btnSaveMove">
                            <i class="bx bx-transfer-alt me-1"></i> Pindahkan
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

            /* Select "Lokasi" yang bisa diubah langsung dari baris tabel */
            .select-location {
                border-radius: 999px;
                font-size: .75rem;
                font-weight: 600;
                padding: .3rem 1.75rem .3rem .8rem;
                border: 1px solid transparent;
                cursor: pointer;
                min-width: 200px;
                box-shadow: none !important;
                transition: background-color .15s ease, color .15s ease, border-color .15s ease, opacity .15s ease;
            }

            .select-location:focus {
                box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15) !important;
            }

            .select-location:disabled {
                opacity: .55;
                cursor: progress;
            }

            .select-location.loc-empty {
                background-color: #f1f3f5;
                color: #6c757d;
                border-color: #dee2e6;
            }

            .select-location.loc-info {
                background-color: #cff4fc;
                color: #055160;
                border-color: #9eeaf9;
            }

            .select-location.loc-warning {
                background-color: #fff3cd;
                color: #997404;
                border-color: #ffe69c;
            }

            .select-location.loc-primary {
                background-color: #cfe2ff;
                color: #084298;
                border-color: #9ec5fe;
            }

            .select-location.loc-danger {
                background-color: #f8d7da;
                color: #842029;
                border-color: #f1aeb5;
            }

            .select-location.loc-success {
                background-color: #d1e7dd;
                color: #0f5132;
                border-color: #a3cfbb;
            }

            .select-location.loc-secondary {
                background-color: #e2e3e5;
                color: #41464b;
                border-color: #c4c8cb;
            }

            .select-location.loc-dark {
                background-color: #ced4da;
                color: #212529;
                border-color: #adb5bd;
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
                            name: 'staging_ins.arrival_date'
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
                            targets: [2, 4, 5, 7, 10],
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

                // Tampilkan peringatan hanya saat mode "Reset & ganti semua" dipilih.
                $('input[name="mode"]').on('change', function() {
                    $('#importResetWarning').toggle($('#importModeReset').is(':checked'));
                });

                // ================= BARANG (TomSelect dari tabel items) =================
                function initItemSelect(selectId, idFieldId, ownerFieldId) {

                    return new TomSelect(selectId, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: ['text', 'item_code', 'item_name', 'item_code_supplier'],
                        loadThrottle: 250,
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
                        }
                    });
                }

                let editItemSelect = initItemSelect('#editItemSelect', '#editItemId', '#editItemOwner');

                // ================= ALERT HELPER (SweetAlert) =================
                // Satu pintu untuk semua notifikasi form: selalu tampil di atas modal,
                // menampilkan SEMUA masalah sekaligus (bukan cuma yang pertama),
                // menandai kolom yang bermasalah, dan punya fallback kalau
                // SweetAlert gagal dimuat.
                function escHtml(text) {
                    return $('<div>').text(text == null ? '' : text).html();
                }

                function swalAvailable() {
                    return typeof Swal !== 'undefined';
                }

                function showToast(icon, title, timer) {
                    if (!swalAvailable()) {
                        alert(title);
                        return;
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: title,
                        timer: timer || 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }

                function problemsHtml(intro, groups) {
                    let html = '<div class="swal-problems">';

                    if (intro) {
                        html += '<p class="swal-problems-intro">' + escHtml(intro) + '</p>';
                    }

                    groups.forEach(function(g) {
                        html += '<div class="swal-problem-group">';

                        if (g.title) {
                            html += '<div class="swal-problem-title">' + escHtml(g.title) + '</div>';
                        }

                        html += '<ul>' + g.items.map(function(item) {
                            return '<li>' + escHtml(item) + '</li>';
                        }).join('') + '</ul></div>';
                    });

                    return html + '</div>';
                }

                function showProblems(opts) {
                    if (!swalAvailable()) {
                        const lines = [];
                        opts.groups.forEach(function(g) {
                            if (g.title) lines.push(g.title + ':');
                            g.items.forEach(function(item) {
                                lines.push('- ' + item);
                            });
                        });
                        alert((opts.title || 'Data belum lengkap') + '\n\n' + lines.join('\n'));
                        return Promise.resolve({
                            isConfirmed: true
                        });
                    }

                    return Swal.fire({
                        icon: opts.icon || 'warning',
                        title: opts.title || 'Data belum lengkap',
                        html: problemsHtml(opts.intro, opts.groups),
                        confirmButtonText: opts.confirmText || 'Lengkapi Data',
                        confirmButtonColor: '#4f46e5',
                        allowOutsideClick: false,
                        heightAuto: false,
                        scrollbarPadding: false
                    });
                }

                function showSimpleAlert(icon, title, text, confirmText, footer) {
                    if (!swalAvailable()) {
                        alert(title + '\n\n' + text);
                        return Promise.resolve({
                            isConfirmed: true
                        });
                    }

                    return Swal.fire({
                        icon: icon,
                        title: title,
                        text: text,
                        footer: footer || undefined,
                        confirmButtonText: confirmText || 'Mengerti',
                        confirmButtonColor: '#4f46e5',
                        allowOutsideClick: false,
                        heightAuto: false,
                        scrollbarPadding: false
                    });
                }

                // Data yang tidak wajib tapi masih kosong: tanya dulu, jangan diam-diam disimpan.
                function confirmEmpty(intro, groups) {
                    if (!swalAvailable()) {
                        return Promise.resolve(window.confirm(intro + '\n\n' + groups[0].items.join('\n')));
                    }

                    return Swal.fire({
                        icon: 'question',
                        title: 'Ada data yang masih kosong',
                        html: problemsHtml(intro, groups),
                        showCancelButton: true,
                        reverseButtons: true,
                        confirmButtonText: 'Tetap Simpan',
                        cancelButtonText: 'Lengkapi Dulu',
                        confirmButtonColor: '#4f46e5',
                        allowOutsideClick: false,
                        heightAuto: false,
                        scrollbarPadding: false
                    }).then(function(result) {
                        return result.isConfirmed;
                    });
                }

                function markInvalid($el) {
                    $el = $($el);
                    $el.addClass('as-invalid');
                    $el.siblings('.ts-wrapper').addClass('as-invalid-wrap');
                }

                function clearInvalid() {
                    $('.as-invalid').removeClass('as-invalid');
                    $('.as-invalid-wrap').removeClass('as-invalid-wrap');
                }

                function focusFirst($el) {
                    $el = $($el);

                    if (!$el.length) return;

                    const ts = $el[0].tomselect;
                    const target = ts ? ts.wrapper : $el[0];

                    if (target && target.scrollIntoView) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }

                    setTimeout(function() {
                        if (ts) ts.focus();
                        else $el.trigger('focus');
                    }, 250);
                }

                // Kolom yang tadinya merah jadi normal lagi begitu user mengisinya.
                $(document).on('input change', '.as-invalid', function() {
                    $(this).removeClass('as-invalid');
                    $(this).siblings('.ts-wrapper').removeClass('as-invalid-wrap');
                });

                $(document).on('hidden.bs.modal', '.modal', clearInvalid);

                // checks: [{ el, target?, when?, validate: ($el, val) => 'pesan error' | null }]
                function collectChecks(checks) {
                    clearInvalid();

                    const found = [];

                    checks.forEach(function(c) {
                        if (c.when && !c.when()) return;

                        const $el = $(c.el);
                        const raw = $el.val();
                        const msg = c.validate($el, $.trim(raw == null ? '' : String(raw)));

                        if (msg) {
                            found.push({
                                message: msg,
                                $el: $(c.target || c.el)
                            });
                        }
                    });

                    return found;
                }

                // true = ada masalah (alert sudah ditampilkan), proses simpan harus dihentikan.
                function reportChecks(found, title) {
                    if (!found.length) return false;

                    found.forEach(function(f) {
                        markInvalid(f.$el);
                    });

                    showProblems({
                        title: title || 'Data belum lengkap',
                        intro: 'Lengkapi data berikut sebelum menyimpan:',
                        groups: [{
                            title: null,
                            items: found.map(function(f) {
                                return f.message;
                            })
                        }]
                    }).then(function() {
                        focusFirst(found[0].$el);
                    });

                    return true;
                }

                function requiredCheck(message) {
                    return function($el, val) {
                        return val === '' ? message : null;
                    };
                }

                // Qty angka bulat >= 1, dan tidak boleh melebihi atribut max (kalau ada).
                function qtyCheck(label, maxLabel) {
                    return function($el, val) {
                        if (val === '') return label + ' belum diisi.';
                        if (isNaN(Number(val)) || !Number.isInteger(Number(val))) return label +
                            ' harus berupa angka bulat.';
                        if (Number(val) < 1) return label + ' minimal 1.';

                        const max = parseFloat($el.attr('max'));

                        if (!isNaN(max) && Number(val) > max) {
                            return label + ' melebihi ' + (maxLabel || 'stok yang tersedia') + '. Maksimal: ' +
                                max + '.';
                        }

                        return null;
                    };
                }

                // list: [{row|null, message}] -> grup per "Barang ke-N"
                function rowGroups(list) {
                    const general = [];
                    const perRow = {};

                    list.forEach(function(p) {
                        if (p.row == null) {
                            general.push(p.message);
                        } else {
                            (perRow[p.row] = perRow[p.row] || []).push(p.message);
                        }
                    });

                    const hasRows = Object.keys(perRow).length > 0;
                    const groups = [];

                    if (general.length) {
                        groups.push({
                            title: hasRows ? 'Data umum' : null,
                            items: general
                        });
                    }

                    Object.keys(perRow).sort(function(a, b) {
                        return a - b;
                    }).forEach(function(k) {
                        groups.push({
                            title: 'Barang ke-' + k,
                            items: perRow[k]
                        });
                    });

                    return groups;
                }

                // list: [{field, row|null}] -> "Customer belum diisi pada barang ke-1, 3"
                function missingGroups(list, totalRows) {
                    const byField = {};
                    const order = [];

                    list.forEach(function(p) {
                        if (!byField[p.field]) {
                            byField[p.field] = {
                                rows: [],
                                general: false
                            };
                            order.push(p.field);
                        }

                        if (p.row == null) byField[p.field].general = true;
                        else byField[p.field].rows.push(p.row);
                    });

                    return [{
                        title: null,
                        items: order.map(function(field) {
                            const d = byField[field];

                            if (d.general) return field + ' belum diisi';

                            const all = totalRows > 1 && d.rows.length === totalRows;

                            return field + ' belum diisi pada ' +
                                (all ? 'semua barang' : 'barang ke-' + d.rows.join(', '));
                        })
                    }];
                }

                function looksTechnical(msg) {
                    return !msg || msg.length > 220 ||
                        /SQLSTATE|Exception|Stack trace|vendor[\\\/]|\.php|Call to|Undefined|Trying to/i.test(msg);
                }

                // Pesan error dari server -> alert yang jelas & ramah.
                function showAjaxError(xhr) {
                    const json = xhr.responseJSON || {};
                    const status = xhr.status;
                    const msg = json.message;

                    if (status === 422 && json.errors) {
                        const general = [];
                        const perRow = [];

                        $.each(json.errors, function(key, msgs) {
                            const m = key.match(/^items\.(\d+)\./);

                            $.each(msgs, function(_, text) {
                                if (m) perRow.push({
                                    row: parseInt(m[1], 10) + 1,
                                    message: text
                                });
                                else general.push({
                                    row: null,
                                    message: text
                                });
                            });
                        });

                        // buang pesan kembar
                        const seen = {};
                        const all = general.concat(perRow).filter(function(p) {
                            const k = p.row + '|' + p.message;
                            if (seen[k]) return false;
                            seen[k] = true;
                            return true;
                        });

                        return showProblems({
                            title: 'Data belum bisa disimpan',
                            intro: 'Periksa kembali data berikut:',
                            groups: rowGroups(all),
                            confirmText: 'Mengerti'
                        });
                    }

                    if (status === 0) {
                        return showSimpleAlert('error', 'Tidak ada koneksi',
                            'Tidak dapat terhubung ke server. Periksa koneksi internet kamu, lalu coba lagi.');
                    }

                    if (status === 419 || status === 401) {
                        return showSimpleAlert('warning', 'Sesi sudah berakhir',
                            'Halaman sudah terlalu lama dibuka atau kamu sudah logout. Muat ulang halaman, lalu coba lagi.',
                            'Muat Ulang').then(function() {
                            location.reload();
                        });
                    }

                    if (status === 403) {
                        return showSimpleAlert('error', 'Tidak punya akses',
                            'Akun kamu tidak memiliki izin untuk melakukan aksi ini.');
                    }

                    if (status === 404) {
                        return showSimpleAlert('warning', 'Data tidak ditemukan',
                            'Data ini mungkin sudah dihapus atau dipindahkan oleh pengguna lain. Muat ulang halaman untuk melihat data terbaru.'
                        );
                    }

                    if (msg && !looksTechnical(msg)) {
                        return showSimpleAlert(status === 422 ? 'warning' : 'error',
                            status === 422 ? 'Data belum bisa disimpan' : 'Data gagal disimpan', msg);
                    }

                    return showSimpleAlert('error', 'Terjadi kesalahan di server',
                        'Data belum tersimpan. Coba lagi beberapa saat lagi; kalau masih berulang, hubungi admin.',
                        'Mengerti', msg ? 'Detail: ' + msg.substring(0, 180) : '');
                }

                // Nomor di name="items[N]" tidak selalu urut kalau ada baris yang dihapus.
                // Dirapikan sebelum divalidasi/dikirim supaya "Barang ke-N" dari server
                // sama persis dengan urutan di layar.
                function reindexAddRows() {
                    $('#addItemRows').children('.add-item-row').each(function(i) {
                        $(this).find('[name^="items["]').each(function() {
                            this.name = this.name.replace(/^items\[\d+\]/, 'items[' + i + ']');
                        });
                    });
                }

                // ================= TAMBAH: 1 PO, BANYAK BARANG =================
                const $addRows = $('#addItemRows');
                const addRowTemplate = $('#addItemRowTemplate').html();
                let addRowCounter = 0;

                function createRowItemSelect(el) {
                    return new TomSelect(el, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: ['text', 'item_code', 'item_name', 'item_code_supplier'],
                        loadThrottle: 250,
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
                        }
                    });
                }

                function refreshAddRows() {
                    const $rows = $addRows.children('.add-item-row');

                    $rows.each(function(i) {
                        $(this).find('.row-number').text(i + 1);
                    });

                    $('#addItemCount').text($rows.length);

                    // Minimal harus ada 1 barang.
                    $rows.find('.btnRemoveItemRow').prop('disabled', $rows.length === 1);
                }

                function addItemRow() {
                    const index = addRowCounter++;
                    const $row = $(addRowTemplate.replace(/__INDEX__/g, index));

                    // Baris baru langsung memakai tanggal kedatangan di atas.
                    $row.find('.item-arrival').val($('#addArrivalDate').val());

                    $addRows.append($row);

                    $row.data('ts', createRowItemSelect($row.find('.item-select')[0]));

                    refreshAddRows();

                    return $row;
                }

                function resetAddForm() {
                    $addRows.children('.add-item-row').each(function() {
                        const ts = $(this).data('ts');
                        if (ts) ts.destroy();
                    });

                    $addRows.empty();
                    $('#formStaging')[0].reset();

                    addItemRow();
                }

                // Tanggal kedatangan diisi 1x -> semua baris ikut menyesuaikan.
                // Setelah itu tiap baris tetap bisa diubah manual.
                $('#addArrivalDate').on('input change', function() {
                    $addRows.find('.item-arrival').val(this.value);
                });

                $(document).on('click', '.btnAddItemRow', function() {
                    const $row = addItemRow();
                    $row[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                });

                $(document).on('click', '.btnRemoveItemRow', function() {
                    const $row = $(this).closest('.add-item-row');
                    const ts = $row.data('ts');

                    if (ts) ts.destroy();
                    $row.remove();

                    refreshAddRows();
                });

                $('#addStaging').on('hidden.bs.modal', resetAddForm);

                resetAddForm();

                // ================= TAMBAH: validasi & simpan (AJAX) =================
                // Wajib (menghentikan simpan): Barang & Qty >= 1 di tiap baris.
                // Boleh kosong tapi ditanyakan dulu: No. PO, Tgl Kedatangan, Lokasi.
                function collectAddProblems() {
                    clearInvalid();

                    const blocking = [];
                    const soft = [];
                    const $rows = $addRows.children('.add-item-row');

                    const $po = $('#formStaging [name="po_number"]');

                    if (!$.trim($po.val())) {
                        soft.push({
                            field: 'No. PO',
                            row: null,
                            $el: $po
                        });
                    }

                    $rows.each(function(i) {
                        const $row = $(this);
                        const no = i + 1;

                        const $item = $row.find('.item-select');
                        const $qty = $row.find('input[name$="[qty]"]');
                        const $date = $row.find('.item-arrival');
                        const $loc = $row.find('select[name$="[location]"]');

                        if (!$item.val()) {
                            blocking.push({
                                row: no,
                                message: 'Barang belum dipilih. Cari dan pilih barang dari daftar.',
                                $el: $item
                            });
                        }

                        const qtyMsg = qtyCheck('Qty')($qty, $.trim($qty.val()));

                        if (qtyMsg) {
                            blocking.push({
                                row: no,
                                message: qtyMsg,
                                $el: $qty
                            });
                        }

                        if (!$date.val()) {
                            soft.push({
                                field: 'Tgl Kedatangan',
                                row: no,
                                $el: $date
                            });
                        }

                        if (!$loc.val()) {
                            soft.push({
                                field: 'Lokasi',
                                row: no,
                                $el: $loc
                            });
                        }
                    });

                    return {
                        blocking: blocking,
                        soft: soft,
                        total: $rows.length
                    };
                }

                function saveAddForm($form) {
                    const $btn = $('#btnSaveStaging').prop('disabled', true);

                    $.ajax({
                        url: "{{ route('stagings-in.store') }}",
                        method: "POST",
                        data: $form.serialize(),

                        success: function(response) {
                            $('#addStaging').modal('hide'); // form di-reset oleh hidden.bs.modal
                            table.ajax.reload(null, false);
                            showToast('success', response.message, 2500);
                        },

                        error: function(xhr) {
                            showAjaxError(xhr);
                        },

                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                }

                $(document).on('submit', '#formStaging', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    const $form = $(this);

                    reindexAddRows();

                    const r = collectAddProblems();

                    if (r.blocking.length) {
                        r.blocking.forEach(function(p) {
                            markInvalid(p.$el);
                        });

                        showProblems({
                            title: 'Data belum lengkap',
                            intro: 'Lengkapi data berikut sebelum menyimpan:',
                            groups: rowGroups(r.blocking)
                        }).then(function() {
                            focusFirst(r.blocking[0].$el);
                        });

                        return;
                    }

                    if (r.soft.length) {
                        confirmEmpty('Data berikut belum diisi. Mau tetap disimpan?',
                            missingGroups(r.soft, r.total)).then(function(ok) {
                            if (ok) {
                                saveAddForm($form);
                            } else {
                                r.soft.forEach(function(p) {
                                    markInvalid(p.$el);
                                });
                                focusFirst(r.soft[0].$el);
                            }
                        });

                        return;
                    }

                    saveAddForm($form);

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

                    let id = $('#editId').val();

                    if (reportChecks(collectChecks([{
                                el: '#editItemId',
                                target: '#editItemSelect',
                                validate: requiredCheck(
                                    'Barang belum dipilih. Cari dan pilih barang dari daftar.')
                            },
                            {
                                el: '#editQty',
                                validate: function($el, val) {
                                    if (val === '') return null; // kosong = tidak mengubah qty
                                    if (isNaN(Number(val)) || !Number.isInteger(Number(val)))
                                        return 'Qty harus berupa angka bulat.';
                                    if (Number(val) < 0) return 'Qty tidak boleh negatif.';
                                    return null;
                                }
                            }
                        ]))) return;

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

                            showAjaxError(xhr);

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
                });

                // ================= PINDAHKAN ITEM =================
                let moveLocationSelect;
                let moveLotSelect;

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
                        },
                        onChange: function(value) {
                            loadLotsForLocation(value);
                        }
                    });
                }

                function initMoveLotSelect() {

                    if (moveLotSelect) return;

                    moveLotSelect = new TomSelect('#moveLotSelect', {
                        create: true,
                        persist: false,
                        createOnBlur: true,
                        allowEmptyOption: true,
                        placeholder: 'Opsional - pilih atau ketik lot baru...',
                        valueField: 'value',
                        labelField: 'text',
                        searchField: ['text']
                    });
                }

                // Lot itu turunan dari lokasi: 1 barang bisa ada di banyak
                // lokasi, 1 lokasi bisa punya banyak lot, masing-masing
                // dengan qty sendiri. Jadi daftar lot dimuat ulang setiap
                // kali lokasi tujuan berubah.
                function loadLotsForLocation(locationName) {

                    if (!moveLotSelect) return;

                    let itemId = $('#formMoveStaging').data('item-id');

                    moveLotSelect.clear();
                    moveLotSelect.clearOptions();

                    if (!locationName || !itemId) return;

                    $.ajax({
                        url: "{{ route('stagings-in.location-lots') }}",
                        type: 'GET',
                        data: {
                            item_id: itemId,
                            location: locationName
                        },
                        success: function(res) {
                            (res || []).forEach(function(lot) {
                                moveLotSelect.addOption({
                                    value: lot,
                                    text: lot
                                });
                            });
                            moveLotSelect.refreshOptions(false);
                        }
                    });
                }

                // buka modal & isi ringkasan item
                $(document).on('click', '.btnMove', function() {

                    initMoveLocationSelect();
                    initMoveLotSelect();

                    let $btn = $(this);
                    let qty = parseInt($btn.data('qty')) || 0;

                    $('#formMoveStaging').data('staging-id', $btn.data('id'));
                    $('#formMoveStaging').data('item-id', $btn.data('item-id'));

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
                    $('#moveTransactionDate').val(new Date().toISOString().slice(0, 10));
                    $('#moveNotes').val('');

                    // field staging out
                    $('#moveSoNumber').val('');
                    $('#moveCustomer').val('');
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

                    if (moveLotSelect) {
                        moveLotSelect.clear();
                        moveLotSelect.clearOptions();
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

                    if (reportChecks(collectChecks([{
                                el: '#moveQty',
                                validate: qtyCheck('Qty yang dipindah', 'sisa barang di staging in')
                            },
                            {
                                el: '#moveLocationSelect',
                                when: function() {
                                    return tujuan === 'stock';
                                },
                                validate: requiredCheck('Lokasi gudang tujuan belum dipilih.')
                            },
                            {
                                el: '#moveTransactionDate',
                                when: function() {
                                    return tujuan === 'stock';
                                },
                                validate: requiredCheck('Tanggal transaksi belum diisi.')
                            },
                            {
                                el: '#moveSoNumber',
                                when: function() {
                                    return tujuan !== 'stock';
                                },
                                validate: requiredCheck('No. SO belum diisi.')
                            },
                            {
                                el: '#moveLineItem',
                                when: function() {
                                    return tujuan !== 'stock';
                                },
                                validate: requiredCheck('Line item belum diisi.')
                            },
                            {
                                el: '#moveDeliveryInstructionDate',
                                when: function() {
                                    return tujuan !== 'stock';
                                },
                                validate: requiredCheck('Tanggal instruksi kirim belum diisi.')
                            }
                        ]))) return;

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

                            showAjaxError(xhr);

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
                    if (moveLotSelect) {
                        moveLotSelect.clear();
                        moveLotSelect.clearOptions();
                    }
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

        <script>
            // ============ TAMPILAN FILTER (penanda aktif, hapus pencarian, preset tanggal) ============
            (function() {
                const $bar = $('#filterBar');
                const chipFilters = ['#filterStatus', '#filterIncoterms', '#filterOwner', '#filterSupplier',
                    '#filterLocation'
                ];

                function filled($el) {
                    return String($el.val() || '').trim() !== '';
                }

                function refreshFilterState() {
                    let count = 0;

                    const searchFilled = filled($('#customSearch'));
                    $('.fx-search').toggleClass('has-value', searchFilled);
                    if (searchFilled) count++;

                    chipFilters.forEach(function(sel) {
                        const on = filled($(sel));
                        $(sel).closest('.fx-chip').toggleClass('is-active', on);
                        if (on) count++;
                    });

                    // Filter dari link dashboard (mis. ?filter=overdue) tidak ada di dropdown,
                    // tapi tetap dihitung sebagai filter aktif.
                    if (typeof activeUrlFilter !== 'undefined' && activeUrlFilter) {
                        count++;
                    }

                    const dateOn = filled($('#filterDateRange'));
                    $('#filterDateRange').closest('.fx-chip').toggleClass('is-active', dateOn);
                    if (dateOn) count++;

                    $bar.toggleClass('has-active', count > 0);
                    $('#filterCount').text(count > 0 ? count + ' filter aktif' : '');
                }

                $('#customSearch, ' + chipFilters.join(', ')).on('input change', refreshFilterState);

                // Handler lama mengisi nilai lewat .val() (tanpa event), jadi cek ulang sesudahnya.
                $('#dateRangeApply, #resetFilterInline, #clearActiveFilterBtn').on('click', function() {
                    setTimeout(refreshFilterState, 0);
                });

                $('#customSearchClear').on('click', function() {
                    $('#customSearch').val('').trigger('input').trigger('focus');
                });

                // Preset rentang tanggal: isi Dari/Sampai lalu pakai tombol Terapkan yang sudah ada.
                function ymd(d) {
                    const p = (n) => String(n).padStart(2, '0');
                    return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
                }

                $('.fx-preset').on('click', function() {
                    const now = new Date();
                    let from = new Date(now);
                    const preset = $(this).data('preset');

                    if (preset === '7d') {
                        from.setDate(now.getDate() - 6);
                    } else if (preset === 'month') {
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                    }

                    $('#filterStartDate').val(ymd(from));
                    $('#filterEndDate').val(ymd(now));
                    $('#dateRangeApply').trigger('click');
                });

                $('#dateRangeClear').on('click', function() {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#dateRangeApply').trigger('click');
                });

                refreshFilterState();
            })();
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

        {{-- Alert (SweetAlert) & penanda kolom bermasalah --}}
        <style>
            /* Selalu di atas modal Bootstrap, backdrop, dan navbar */
            .swal2-container {
                z-index: 2000000 !important;
            }

            .swal-problems {
                text-align: left;
                font-size: .92rem;
            }

            .swal-problems-intro {
                margin: 0 0 .75rem;
                color: #6b7385;
            }

            .swal-problem-group {
                margin-bottom: .6rem;
                padding: .7rem .95rem;
                border-radius: .75rem;
                background: #f7f8fc;
                border-left: 4px solid #f59e0b;
            }

            .swal-problem-title {
                margin-bottom: .25rem;
                font-weight: 700;
                color: #2b3350;
            }

            .swal-problem-group ul {
                margin: 0;
                padding-left: 1.1rem;
            }

            .swal-problem-group li {
                margin: .15rem 0;
            }

            .as-invalid {
                border-color: #e11d48 !important;
                background-color: #fff5f7 !important;
                box-shadow: 0 0 0 4px rgba(225, 29, 72, .10) !important;
            }

            .as-invalid-wrap .ts-control {
                border-color: #e11d48 !important;
                background-color: #fff5f7 !important;
                box-shadow: 0 0 0 4px rgba(225, 29, 72, .10) !important;
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
                $('#btnBulkDelete').toggleClass('d-none', selectedStagingIds.size === 0);
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
                    icon: 'warning',
                    title: 'Hapus Data?',
                    html: `Yakin mau menghapus <b>${selectedStagingIds.size}</b> data terpilih?<br><small class="text-muted">Tindakan ini permanen dan tidak bisa dibatalkan.</small>`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $('#btnBulkDelete').prop('disabled', true);

                    $.ajax({
                        complete: function() {
                            $('#btnBulkDelete').prop('disabled', false);
                        },
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
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                                background: '#fff',
                                color: '#566a7f',
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
                                title: 'Gagal Menghapus Data',
                                text: (xhr.responseJSON && xhr.responseJSON.message) ||
                                    'Terjadi kesalahan saat menghapus data.'
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
            $(document).on('change', '.select-location', function() {

                let $select = $(this);
                let id = $select.data('id');
                let value = $select.val();
                let newClass = 'loc-' + ($select.find('option:selected').data('color') || 'empty');
                let oldClass = ($select.attr('class').match(/loc-\w+/) || ['loc-empty'])[0];

                $select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('stagings-in.update-location', ':id') }}".replace(':id', id),
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        location: value
                    },

                    success: function(res) {

                        $select.removeClass(oldClass).addClass(newClass);

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

                        $select.removeClass(newClass).addClass(oldClass);

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