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
                    data-bs-target="#importStagingOutModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                    </svg>
                    <span class="d-none d-md-inline ms-1">Import</span>
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
                                                Pilih Mode Import
                                            </h6>

                                            <p class="text-muted small mb-3">
                                                Tentukan asal barang pada data staging out yang diimpor.
                                            </p>

                                            <div class="form-check mb-2 p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="source"
                                                    id="importSourceExternal" value="external" checked>
                                                <label class="form-check-label w-100" for="importSourceExternal">
                                                    <span class="fw-semibold d-block">Eksternal</span>
                                                    <small class="text-muted">
                                                        Data diimpor apa adanya dari file Excel. Pilih di bawah
                                                        apakah data ditambahkan atau mengganti semua data yang ada.
                                                    </small>
                                                </label>
                                            </div>

                                            <div class="form-check p-3 border rounded">
                                                <input class="form-check-input" type="radio" name="source"
                                                    id="importSourceStock" value="stock">
                                                <label class="form-check-label w-100" for="importSourceStock">
                                                    <span class="fw-semibold d-block">Dari Stok</span>
                                                    <small class="text-muted">
                                                        Barang diambil dari stok. Lokasi &amp; lot dicari otomatis
                                                        dari data stok per Kode Barang, dan stok ikut berkurang.
                                                        Data lama tetap ada.
                                                    </small>
                                                </label>
                                            </div>

                                            <div id="importSourceStockInfo" class="alert alert-info small mt-2 mb-0"
                                                style="display:none;">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Template sama seperti mode Eksternal (tanpa kolom Lokasi). Baris
                                                yang barangnya ada di lebih dari 1 lokasi/lot, atau stoknya
                                                kosong/kurang, akan dilewati dan dilaporkan nomor barisnya.
                                                Baris itu bisa diinput manual lewat form Tambah dengan sumber
                                                "Stock".
                                            </div>

                                            {{-- PILIHAN MODE: tambah / reset (hanya untuk sumber Eksternal) --}}
                                            <div id="importModeWrapper" class="mt-3">
                                                <h6 class="fw-semibold mb-2">Apa yang dilakukan terhadap data yang sudah ada?</h6>

                                                <div class="form-check mb-2 p-3 border rounded">
                                                    <input class="form-check-input" type="radio" name="mode"
                                                        id="importModeAppend" value="append">
                                                    <label class="form-check-label w-100" for="importModeAppend">
                                                        <span class="fw-semibold d-block">Tambahkan</span>
                                                        <small class="text-muted">
                                                            Data lama tetap ada. Baris dari file ditambahkan sebagai data
                                                            baru. Import file yang sama dua kali akan membuat data dobel.
                                                        </small>
                                                    </label>
                                                </div>

                                                <div class="form-check p-3 border rounded">
                                                    <input class="form-check-input" type="radio" name="mode"
                                                        id="importModeReset" value="reset">
                                                    <label class="form-check-label w-100" for="importModeReset">
                                                        <span class="fw-semibold d-block">Reset &amp; ganti semua</span>
                                                        <small class="text-muted">
                                                            Semua data staging out yang ada saat ini dihapus dan diganti
                                                            dengan data dari file.
                                                        </small>
                                                    </label>
                                                </div>

                                                <div id="importModeResetInfo" class="alert alert-warning small mt-2 mb-0"
                                                    style="display:none;">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Seluruh data staging out yang ada akan dihapus (tercatat di history
                                                    sebagai "reset by import"). Pastikan file Excel berisi data yang lengkap.
                                                </div>

                                                <div id="importModeError" class="text-danger small mt-2"
                                                    style="display:none;">
                                                    Pilih dulu: data ditambahkan atau di-reset.
                                                </div>
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
                <div class="modal fade" id="addStagingOut" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="addStagingOutLabel" aria-hidden="true">

                    <style>
                        #addStagingOut .modal-content {
                            border: 0;
                            border-radius: 1.5rem;
                            overflow: hidden;
                            box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
                        }

                        /* ---------- HERO (header + No. PO + tanggal) ---------- */
                        #addStagingOut .as-hero {
                            padding: 1.5rem 2rem 1.25rem;
                            background: #fff;
                            border-bottom: 1px solid #eceef5;
                        }

                        #addStagingOut .as-hero-icon {
                            width: 48px;
                            height: 48px;
                            border-radius: 1rem;
                            display: grid;
                            place-items: center;
                            font-size: 1.5rem;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStagingOut .as-hero h4 {
                            color: #2b3350;
                            letter-spacing: -.01em;
                        }

                        #addStagingOut .as-hero small {
                            color: #8a93a8;
                        }

                        #addStagingOut .as-po-card {
                            margin: 1.25rem 2rem 0;
                            padding: 1.1rem 1.25rem;
                            background: #fff;
                            border-radius: 1.1rem;
                            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                        }

                        /* ---------- BODY ---------- */
                        #addStagingOut .as-body {
                            padding: 1.5rem 2rem .5rem;
                            background: #f5f6fb;
                        }

                        #addStagingOut .as-label {
                            display: block;
                            margin-bottom: .3rem;
                            font-size: .68rem;
                            font-weight: 700;
                            letter-spacing: .06em;
                            text-transform: uppercase;
                            color: #8a93a8;
                        }

                        #addStagingOut .as-field {
                            position: relative;
                        }

                        #addStagingOut .as-field>i {
                            position: absolute;
                            left: .8rem;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #a5adc2;
                            font-size: 1.05rem;
                            pointer-events: none;
                        }

                        #addStagingOut .as-input,
                        #addStagingOut .as-select {
                            width: 100%;
                            border: 1.5px solid #e6e9f2;
                            background: #f8f9fd;
                            border-radius: .75rem;
                            padding: .5rem .8rem;
                            font-size: .875rem;
                            color: #2b3350;
                            transition: border-color .15s, box-shadow .15s, background .15s;
                        }

                        #addStagingOut .as-field>i+.as-input,
                        #addStagingOut .as-field>i+.as-select {
                            padding-left: 2.35rem;
                        }

                        #addStagingOut .as-input:focus,
                        #addStagingOut .as-select:focus {
                            outline: 0;
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #addStagingOut .as-input-lg {
                            padding-top: .65rem;
                            padding-bottom: .65rem;
                            font-weight: 600;
                        }

                        #addStagingOut .as-hint {
                            display: inline-flex;
                            align-items: center;
                            gap: .3rem;
                            margin-top: .35rem;
                            font-size: .72rem;
                            color: #7c3aed;
                        }

                        /* ---------- DAFTAR BARANG ---------- */
                        #addStagingOut .as-section-title {
                            display: flex;
                            align-items: center;
                            gap: .6rem;
                            font-weight: 700;
                            color: #2b3350;
                        }

                        #addStagingOut .as-count {
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

                        #addStagingOut .add-item-row {
                            position: relative;
                            background: #fff;
                            border-radius: 1.1rem;
                            padding: 1.1rem 1.25rem 1.25rem 1.6rem;
                            margin-bottom: 1rem;
                            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                            transition: box-shadow .2s;
                            animation: asRowIn .28s ease both;
                        }

                        #addStagingOut .add-item-row::before {
                            content: "";
                            position: absolute;
                            left: 0;
                            top: 1rem;
                            bottom: 1rem;
                            width: 5px;
                            border-radius: 0 6px 6px 0;
                            background: linear-gradient(180deg, #4f46e5, #db2777);
                        }

                        #addStagingOut .add-item-row:hover {
                            box-shadow: 0 10px 28px -10px rgba(79, 70, 229, .3);
                        }

                        #addStagingOut .add-item-row:focus-within {
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

                        #addStagingOut .as-row-badge {
                            display: inline-flex;
                            align-items: center;
                            gap: .5rem;
                            font-weight: 700;
                            font-size: .85rem;
                            color: #2b3350;
                        }

                        #addStagingOut .as-row-badge .row-number {
                            width: 1.8rem;
                            height: 1.8rem;
                            display: grid;
                            place-items: center;
                            border-radius: .6rem;
                            font-size: .8rem;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStagingOut .btnRemoveItemRow {
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

                        #addStagingOut .btnRemoveItemRow:hover:not(:disabled) {
                            color: #fff;
                            background: #e11d48;
                        }

                        #addStagingOut .btnRemoveItemRow:disabled {
                            opacity: .35;
                        }

                        #addStagingOut .as-add-btn {
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

                        #addStagingOut .as-add-btn:hover {
                            border-color: #7c3aed;
                            background: #fff;
                            color: #7c3aed;
                            box-shadow: 0 8px 20px -10px rgba(124, 58, 237, .45);
                        }

                        #addStagingOut .as-pill-btn {
                            border: 0;
                            border-radius: 50rem;
                            padding: .4rem 1rem;
                            font-size: .8rem;
                            font-weight: 600;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #addStagingOut .as-pill-btn:hover {
                            color: #fff;
                            background: #4f46e5;
                        }

                        /* ---------- FOOTER ---------- */
                        #addStagingOut .as-footer {
                            display: flex;
                            justify-content: flex-end;
                            gap: .6rem;
                            padding: 1rem 2rem 1.4rem;
                            background: #f5f6fb;
                        }

                        #addStagingOut .as-btn-save {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.6rem;
                            font-weight: 600;
                            color: #fff;
                            background: linear-gradient(135deg, #4f46e5, #7c3aed);
                            box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65);
                        }

                        #addStagingOut .as-btn-save:hover {
                            color: #fff;
                            filter: brightness(1.08);
                        }

                        #addStagingOut .as-btn-cancel {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.3rem;
                            font-weight: 600;
                            color: #5a6482;
                            background: #e9ebf5;
                        }

                        /* ---------- TOM SELECT biar senada ---------- */
                        #addStagingOut .ts-wrapper .ts-control {
                            min-height: 40px;
                            border: 1.5px solid #e6e9f2;
                            border-radius: .75rem;
                            background: #f8f9fd;
                            padding: .45rem .8rem;
                            box-shadow: none;
                        }

                        #addStagingOut .ts-wrapper.focus .ts-control {
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #addStagingOut .ts-dropdown {
                            z-index: 1070;
                            border-radius: .75rem;
                            border: 0;
                            box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3);
                            overflow: hidden;
                        }

                        #addStagingOut .ts-dropdown .active {
                            background: #eef0ff;
                            color: #4f46e5;
                        }

                        /* ---------- Toggle sumber barang per baris ---------- */
                        #addStagingOut .as-seg {
                            display: inline-flex;
                            gap: 2px;
                            padding: 3px;
                            border-radius: .8rem;
                            background: #eef0f7;
                        }

                        #addStagingOut .as-seg input {
                            position: absolute;
                            opacity: 0;
                            pointer-events: none;
                        }

                        #addStagingOut .as-seg label {
                            display: inline-flex;
                            align-items: center;
                            gap: .35rem;
                            margin: 0;
                            padding: .3rem .85rem;
                            border-radius: .6rem;
                            font-size: .78rem;
                            font-weight: 600;
                            color: #7a839c;
                            cursor: pointer;
                            transition: all .15s;
                        }

                        #addStagingOut .as-seg input:checked+label {
                            color: #4f46e5;
                            background: #fff;
                            box-shadow: 0 2px 8px -2px rgba(43, 51, 80, .25);
                        }

                        #addStagingOut .as-stock-box {
                            padding: .9rem 1rem;
                            border-radius: .9rem;
                            background: #f3f4ff;
                            border: 1px dashed #c7cbf0;
                        }
                    </style>

                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">

                            <form action="{{ route('stagings-out.store') }}" method="POST" id="formStagingOut"
                                class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                                @csrf

                                {{-- ================= HEADER (putih polos) ================= --}}
                                <div class="as-hero flex-shrink-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="as-hero-icon"><i class="bx bx-package"></i></div>
                                            <div>
                                                <h4 class="mb-0 fw-bold" id="addStagingOutLabel">Tambah Item Staging Out</h4>
                                                <small>Satu SO, banyak barang — isi sekali saja.</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                </div>

                                {{-- ================= No. SO & Tgl Instruksi Kirim (diisi 1x) ================= --}}
                                <div class="as-po-card flex-shrink-0">
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="as-label">No. SO</label>
                                            <div class="as-field">
                                                <i class="bx bx-receipt"></i>
                                                <input type="text" class="as-input as-input-lg" name="so_number"
                                                    placeholder="Contoh: SO-2026-0001">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="as-label">Tgl Instruksi Kirim</label>
                                            <div class="as-field">
                                                <i class="bx bx-calendar"></i>
                                                <input type="date" class="as-input as-input-lg" id="addInstructionDate"
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
                                    <button type="submit" class="as-btn-save" id="btnSaveStagingOut">
                                        <i class="bx bx-save me-1"></i> Simpan Staging Out
                                    </button>
                                </div>

                            </form>

                            {{-- Template 1 baris barang. __INDEX__ diganti lewat JS. --}}
                            <template id="addItemRowTemplate">
                                <div class="add-item-row">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            <span class="as-row-badge">
                                                <span class="row-number"></span> Barang
                                            </span>

                                            <div class="as-seg">
                                                <input type="radio" name="items[__INDEX__][source_type]"
                                                    id="srcExt___INDEX__" value="external" class="src-external" checked>
                                                <label for="srcExt___INDEX__"><i class="bx bx-package"></i> Eksternal</label>

                                                <input type="radio" name="items[__INDEX__][source_type]"
                                                    id="srcStock___INDEX__" value="stock" class="src-stock">
                                                <label for="srcStock___INDEX__"><i class="bx bx-archive-in"></i> Dari Stok</label>
                                            </div>
                                        </div>

                                        <button type="button" class="btnRemoveItemRow" title="Hapus barang ini">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-lg-6">
                                            <label class="as-label">Kode Barang</label>
                                            <select class="item-select" name="items[__INDEX__][item_id]"
                                                placeholder="Cari kode / nama barang..."></select>
                                        </div>

                                        <div class="col-6 col-lg-3">
                                            <label class="as-label">Line Item</label>
                                            <div class="as-field">
                                                <i class="bx bx-hash"></i>
                                                <input type="text" class="as-input" name="items[__INDEX__][line_item]"
                                                    placeholder="Contoh: 10">
                                            </div>
                                        </div>

                                        <div class="col-6 col-lg-3">
                                            <label class="as-label">Qty</label>
                                            <div class="as-field">
                                                <i class="bx bx-cube-alt"></i>
                                                <input type="number" class="as-input row-qty"
                                                    name="items[__INDEX__][qty]" min="0" placeholder="0">
                                            </div>
                                        </div>

                                        {{-- Lokasi & Lot: hanya muncul kalau sumbernya "Dari Stok" --}}
                                        <div class="col-12 stock-fields d-none">
                                            <div class="as-stock-box">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="as-label">Lokasi</label>
                                                        <select class="as-select row-location"
                                                            name="items[__INDEX__][location_id]" disabled>
                                                            <option value="">-- Pilih Lokasi --</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="as-label">Lot</label>
                                                        <select class="as-select row-lot" name="items[__INDEX__][lot]"
                                                            disabled>
                                                            <option value="">-- Pilih Lot --</option>
                                                        </select>
                                                        <small class="text-success d-block mt-1 row-qty-hint"></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-4">
                                            <label class="as-label">Customer</label>
                                            <div class="as-field">
                                                <i class="bx bx-buildings"></i>
                                                <input type="text" class="as-input" name="items[__INDEX__][customer]"
                                                    placeholder="Contoh: PT. ABC">
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-4">
                                            <label class="as-label">Tgl Instruksi Kirim</label>
                                            <input type="date" class="as-input row-instruction-date"
                                                name="items[__INDEX__][delivery_instruction_date]">
                                        </div>

                                        <div class="col-md-6 col-lg-4">
                                            <label class="as-label">Tgl Picking</label>
                                            <input type="date" class="as-input"
                                                name="items[__INDEX__][picking_date]">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">No. DO</label>
                                            <div class="as-field">
                                                <i class="bx bx-file"></i>
                                                <input type="text" class="as-input" name="items[__INDEX__][do_number]"
                                                    placeholder="Opsional">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Tgl Resi Pengiriman</label>
                                            <input type="date" class="as-input"
                                                name="items[__INDEX__][delivery_receipt_date]">
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

        <style>
                        #editStagingOutModal .modal-content {
                            border: 0;
                            border-radius: 1.5rem;
                            overflow: hidden;
                            box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
                        }

                        #editStagingOutModal .as-hero {
                            padding: 1.5rem 2rem 1.25rem;
                            background: #fff;
                            border-bottom: 1px solid #eceef5;
                        }

                        #editStagingOutModal .as-hero-icon {
                            width: 48px;
                            height: 48px;
                            border-radius: 1rem;
                            display: grid;
                            place-items: center;
                            font-size: 1.5rem;
                            color: #4f46e5;
                            background: #eef0ff;
                        }

                        #editStagingOutModal .as-hero h4 {
                            color: #2b3350;
                            letter-spacing: -.01em;
                        }

                        #editStagingOutModal .as-hero small {
                            color: #8a93a8;
                        }

                        #editStagingOutModal .as-body {
                            padding: 1.5rem 2rem .5rem;
                            background: #f5f6fb;
                        }

                        #editStagingOutModal .as-label {
                            display: block;
                            margin-bottom: .3rem;
                            font-size: .68rem;
                            font-weight: 700;
                            letter-spacing: .06em;
                            text-transform: uppercase;
                            color: #8a93a8;
                        }

                        #editStagingOutModal .as-field {
                            position: relative;
                        }

                        #editStagingOutModal .as-field>i {
                            position: absolute;
                            left: .8rem;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #a5adc2;
                            font-size: 1.05rem;
                            pointer-events: none;
                        }

                        #editStagingOutModal .as-input,
                        #editStagingOutModal .as-select {
                            width: 100%;
                            border: 1.5px solid #e6e9f2;
                            background: #f8f9fd;
                            border-radius: .75rem;
                            padding: .5rem .8rem;
                            font-size: .875rem;
                            color: #2b3350;
                            transition: border-color .15s, box-shadow .15s, background .15s;
                        }

                        #editStagingOutModal .as-field>i+.as-input,
                        #editStagingOutModal .as-field>i+.as-select {
                            padding-left: 2.35rem;
                        }

                        #editStagingOutModal .as-input:focus,
                        #editStagingOutModal .as-select:focus {
                            outline: 0;
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #editStagingOutModal .as-input-lg {
                            padding-top: .65rem;
                            padding-bottom: .65rem;
                            font-weight: 600;
                        }

                        #editStagingOutModal .as-footer {
                            display: flex;
                            justify-content: flex-end;
                            gap: .6rem;
                            padding: 1rem 2rem 1.4rem;
                            background: #f5f6fb;
                        }

                        #editStagingOutModal .as-btn-save {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.6rem;
                            font-weight: 600;
                            color: #fff;
                            background: linear-gradient(135deg, #4f46e5, #7c3aed);
                            box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65);
                        }

                        #editStagingOutModal .as-btn-save:hover {
                            color: #fff;
                            filter: brightness(1.08);
                        }

                        #editStagingOutModal .as-btn-cancel {
                            border: 0;
                            border-radius: .8rem;
                            padding: .6rem 1.3rem;
                            font-weight: 600;
                            color: #5a6482;
                            background: #e9ebf5;
                        }

                        #editStagingOutModal .ts-wrapper .ts-control {
                            min-height: 40px;
                            border: 1.5px solid #e6e9f2;
                            border-radius: .75rem;
                            background: #f8f9fd;
                            padding: .45rem .8rem;
                            box-shadow: none;
                        }

                        #editStagingOutModal .ts-wrapper.focus .ts-control {
                            background: #fff;
                            border-color: #7c3aed;
                            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
                        }

                        #editStagingOutModal .ts-dropdown {
                            z-index: 1070;
                            border-radius: .75rem;
                            border: 0;
                            box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3);
                            overflow: hidden;
                        }

                        #editStagingOutModal .ts-dropdown .active {
                            background: #eef0ff;
                            color: #4f46e5;
                        }

                        #editStagingOutModal .as-hint {
                            display: inline-flex;
                            align-items: center;
                            gap: .3rem;
                            margin-top: .35rem;
                            font-size: .72rem;
                            color: #7c3aed;
                        }

                        #editStagingOutModal .as-help {
                            display: block;
                            margin-top: .35rem;
                            font-size: .74rem;
                            color: #8a93a8;
                        }

                        /* Kartu seksi */
                        #editStagingOutModal .as-card {
                            position: relative;
                            background: #fff;
                            border-radius: 1.1rem;
                            padding: 1.25rem 1.4rem 1.4rem 1.7rem;
                            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
                        }

                        #editStagingOutModal .as-card::before {
                            content: "";
                            position: absolute;
                            left: 0;
                            top: 1.1rem;
                            bottom: 1.1rem;
                            width: 5px;
                            border-radius: 0 6px 6px 0;
                            background: linear-gradient(180deg, #4f46e5, #db2777);
                        }

                        #editStagingOutModal .as-card-title {
                            display: flex;
                            align-items: center;
                            gap: .7rem;
                            margin-bottom: 1.1rem;
                        }

                        #editStagingOutModal .as-card-title strong {
                            display: block;
                            color: #2b3350;
                            font-size: .98rem;
                        }

                        #editStagingOutModal .as-card-title small {
                            color: #8a93a8;
                        }

                        #editStagingOutModal .as-card-icon {
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

                        #editStagingOutModal .as-card-icon.is-pink {
                            color: #db2777;
                            background: #fdf0f7;
                        }

                        /* Kotak Lokasi & Lot (khusus Dari Stok) */
                        #editStagingOutModal .as-stock-box {
                            padding: .9rem 1rem;
                            border-radius: .9rem;
                            background: #f3f4ff;
                            border: 1px dashed #c7cbf0;
                        }

                        /* Pilihan sumber barang */
                        #editStagingOutModal .as-source {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: .8rem;
                            margin-bottom: 1.25rem;
                        }

                        #editStagingOutModal .as-source>div {
                            position: relative;
                        }

                        #editStagingOutModal .as-source input {
                            position: absolute;
                            opacity: 0;
                            pointer-events: none;
                        }

                        #editStagingOutModal .as-source label {
                            display: flex;
                            align-items: center;
                            gap: .85rem;
                            height: 100%;
                            margin: 0;
                            padding: .9rem 1.1rem;
                            background: #fff;
                            border: 2px solid #e6e9f2;
                            border-radius: 1rem;
                            cursor: pointer;
                            transition: all .15s;
                        }

                        #editStagingOutModal .as-source label:hover {
                            border-color: #b9b4f7;
                        }

                        #editStagingOutModal .as-source input:checked+label {
                            border-color: #7c3aed;
                            background: #f6f3ff;
                            box-shadow: 0 10px 22px -12px rgba(124, 58, 237, .55);
                        }

                        #editStagingOutModal .as-source-icon {
                            width: 2.5rem;
                            height: 2.5rem;
                            flex-shrink: 0;
                            display: grid;
                            place-items: center;
                            border-radius: .85rem;
                            font-size: 1.25rem;
                            color: #4f46e5;
                            background: #eef0ff;
                            transition: all .15s;
                        }

                        #editStagingOutModal .as-source input:checked+label .as-source-icon {
                            color: #fff;
                            background: linear-gradient(135deg, #4f46e5, #7c3aed);
                        }

                        #editStagingOutModal .as-source strong {
                            display: block;
                            color: #2b3350;
                            font-size: .9rem;
                        }

                        #editStagingOutModal .as-source small {
                            color: #8a93a8;
                        }
        </style>

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <form id="formEditStagingOut" class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="as-hero flex-shrink-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="as-hero-icon"><i class="bx bx-edit"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold">Edit Item Staging Out</h4>
                                    <small>Ubah data pengiriman barang keluar (SO).</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <div class="as-body flex-grow-1 overflow-auto">

                        {{-- ================= SUMBER BARANG ================= --}}
                        <label class="as-label">Sumber Barang</label>

                        <div class="as-source">
                            <div>
                                <input type="radio" name="source_type" id="editSourceExternal" value="external"
                                    autocomplete="off" checked>
                                <label for="editSourceExternal">
                                    <span class="as-source-icon"><i class="bx bx-package"></i></span>
                                    <span>
                                        <strong>Barang Eksternal</strong>
                                        <small>Bukan dari stok gudang</small>
                                    </span>
                                </label>
                            </div>

                            <div>
                                <input type="radio" name="source_type" id="editSourceStock" value="stock"
                                    autocomplete="off">
                                <label for="editSourceStock">
                                    <span class="as-source-icon"><i class="bx bx-archive-in"></i></span>
                                    <span>
                                        <strong>Dari Stok</strong>
                                        <small>Ambil dari lokasi stok</small>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="row g-4 mb-3">

                            {{-- ================= LEFT ================= --}}
                            <div class="col-lg-6">
                                <div class="as-card h-100">

                                    <div class="as-card-title">
                                        <div class="as-card-icon"><i class="bx bx-package"></i></div>
                                        <div>
                                            <strong>Informasi Barang</strong>
                                            <small>Lengkapi informasi barang yang dikirim.</small>
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label class="as-label">No. SO</label>
                                            <div class="as-field">
                                                <i class="bx bx-receipt"></i>
                                                <input type="text" class="as-input" name="so_number"
                                                    id="editSoNumber">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Customer</label>
                                            <div class="as-field">
                                                <i class="bx bx-buildings"></i>
                                                <input type="text" class="as-input" name="customer"
                                                    id="editCustomer">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Kode Barang</label>
                                            <select id="editItemSelect" placeholder="Cari kode / nama barang..."></select>
                                            <input type="hidden" name="item_id" id="editItemId">
                                        </div>

                                        <div class="col-12 d-none" id="editStockFields">
                                            <div class="as-stock-box">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="as-label">Lokasi</label>
                                                        <select class="as-select" name="location_id"
                                                            id="editLocationSelect" disabled>
                                                            <option value="">-- Pilih Lokasi --</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="as-label">Lot</label>
                                                        <select class="as-select" name="lot" id="editLotSelect"
                                                            disabled>
                                                            <option value="">-- Pilih Lot --</option>
                                                        </select>
                                                        <small class="text-success d-block mt-1"
                                                            id="editQtyMaxHint"></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Line Item</label>
                                            <div class="as-field">
                                                <i class="bx bx-hash"></i>
                                                <input type="text" class="as-input" name="line_item"
                                                    id="editLineItem">
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

                                    </div>
                                </div>
                            </div>

                            {{-- ================= RIGHT ================= --}}
                            <div class="col-lg-6">
                                <div class="as-card h-100">

                                    <div class="as-card-title">
                                        <div class="as-card-icon is-pink"><i class="bx bx-trip"></i></div>
                                        <div>
                                            <strong>Jadwal &amp; Pengiriman</strong>
                                            <small>Tentukan jadwal instruksi, picking, dan pengiriman.</small>
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="as-label">Tgl Instruksi Kirim</label>
                                            <input type="date" class="as-input" name="delivery_instruction_date"
                                                id="editDeliveryInstructionDate">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="as-label">Tgl Picking</label>
                                            <input type="date" class="as-input" name="picking_date"
                                                id="editPickingDate">
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">No. DO</label>
                                            <div class="as-field">
                                                <i class="bx bx-file"></i>
                                                <input type="text" class="as-input" name="do_number"
                                                    id="editDoNumber" placeholder="Opsional">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="as-label">Tgl Resi Pengiriman</label>
                                            <input type="date" class="as-input" name="delivery_receipt_date"
                                                id="editDeliveryReceiptDate">
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

                // Tampilkan catatan hanya saat "Langsung dari Stok" dipilih, dan
                // tampilkan pilihan mode (tambah/reset) hanya untuk sumber
                // Eksternal. Radio mode di-disable saat sumber Stok supaya
                // tidak ikut terkirim (import dari stok selalu hanya menambah).
                function syncImportModeVisibility() {
                    let isStock = $('#importSourceStock').is(':checked');

                    $('#importSourceStockInfo').toggle(isStock);
                    $('#importModeWrapper').toggle(!isStock);
                    $('#importModeWrapper input[name="mode"]').prop('disabled', isStock);

                    if (isStock) {
                        $('#importModeError').hide();
                    }
                }

                $('#importStagingOutModal input[name="source"]').on('change', syncImportModeVisibility);

                $('#importStagingOutModal input[name="mode"]').on('change', function() {
                    $('#importModeError').hide();
                    $('#importModeResetInfo').toggle($('#importModeReset').is(':checked'));
                });

                // Wajib memilih mode dulu sebelum import (kecuali sumber Stok).
                $('#importStagingOutModal form').on('submit', function(e) {
                    let isStock = $('#importSourceStock').is(':checked');

                    if (!isStock && !$('#importStagingOutModal input[name="mode"]:checked').length) {
                        e.preventDefault();
                        $('#importModeError').show();
                    }
                });

                // Kembalikan modal ke kondisi awal setiap kali ditutup.
                $('#importStagingOutModal').on('hidden.bs.modal', function() {
                    $('#importSourceExternal').prop('checked', true);
                    $('#importStagingOutModal input[name="mode"]').prop('checked', false);
                    $('#importModeResetInfo').hide();
                    $('#importModeError').hide();
                    syncImportModeVisibility();
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

                let editItemSelect = initItemSelect('#editItemSelect', '#editItemId', function(itemId) {
                    editSourceType.loadLocations(itemId);
                });

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
                        if (isNaN(Number(val)) || !Number.isInteger(Number(val))) return label + ' harus berupa angka bulat.';
                        if (Number(val) < 1) return label + ' minimal 1.';

                        const max = parseFloat($el.attr('max'));

                        if (!isNaN(max) && Number(val) > max) {
                            return label + ' melebihi ' + (maxLabel || 'stok yang tersedia') + '. Maksimal: ' + max + '.';
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
                            'Data ini mungkin sudah dihapus atau dipindahkan oleh pengguna lain. Muat ulang halaman untuk melihat data terbaru.');
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

                // ================= TAMBAH: 1 SO, BANYAK BARANG =================
                const $addRows = $('#addItemRows');
                const addRowTemplate = $('#addItemRowTemplate').html();
                let addRowCounter = 0;

                // Logika per baris: toggle Eksternal/Stok, Lokasi -> Lot, dan batas Qty.
                function initOutRow($row) {

                    const $stockRadio = $row.find('.src-stock');
                    const $extRadio = $row.find('.src-external');
                    const $stockFields = $row.find('.stock-fields');
                    const $loc = $row.find('.row-location');
                    const $lot = $row.find('.row-lot');
                    const $qty = $row.find('.row-qty');
                    const $hint = $row.find('.row-qty-hint');
                    const $item = $row.find('.item-select');

                    function currentItemId() {
                        return $item.val() || '';
                    }

                    function resetLot() {
                        $lot.html('<option value="">-- Pilih Lot --</option>').prop('disabled', true);
                        $hint.text('');
                        $qty.removeAttr('max');
                    }

                    function resetLocationLot() {
                        $loc.html('<option value="">-- Pilih Lokasi --</option>').prop('disabled', true);
                        resetLot();
                    }

                    function loadLocations(itemId) {
                        resetLocationLot();

                        if (!itemId) return;

                        $.getJSON("{{ route('stagings-out.search-location-for-item') }}", {
                            item_id: itemId
                        }).then(function(res) {
                            $loc.prop('disabled', false);

                            res.forEach(function(loc) {
                                $loc.append($('<option>', {
                                    value: loc.id,
                                    text: loc.text + ' (stok: ' + loc.total_quantity + ')'
                                }));
                            });
                        });
                    }

                    function loadLots(itemId, locationId) {
                        resetLot();

                        if (!itemId || !locationId) return;

                        $.getJSON("{{ route('stagings-out.search-lot-for-item-location') }}", {
                            item_id: itemId,
                            location_id: locationId
                        }).then(function(res) {
                            $lot.prop('disabled', false);

                            res.forEach(function(lotRow) {
                                $lot.append($('<option>', {
                                    value: lotRow.id,
                                    text: lotRow.text + ' (stok: ' + lotRow.quantity + ')',
                                    'data-qty': lotRow.quantity
                                }));
                            });

                            // Cuma 1 lot -> langsung dipilihkan.
                            if (res.length === 1) {
                                $lot.val(res[0].id).trigger('change');
                            }
                        });
                    }

                    $extRadio.add($stockRadio).on('change', function() {
                        const isStock = $stockRadio.is(':checked');
                        $stockFields.toggleClass('d-none', !isStock);

                        if (isStock) {
                            loadLocations(currentItemId());
                        } else {
                            resetLocationLot();
                        }
                    });

                    $loc.on('change', function() {
                        loadLots(currentItemId(), $(this).val());
                    });

                    $lot.on('change', function() {
                        const q = $(this).find(':selected').data('qty');

                        if (q !== undefined && q !== '' && q !== null) {
                            $qty.attr('max', q);
                            $hint.text('Stok tersedia: ' + q);
                        } else {
                            $qty.removeAttr('max');
                            $hint.text('');
                        }
                    });

                    const ts = new TomSelect($item[0], {
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
                            if ($stockRadio.is(':checked')) {
                                loadLocations(value || '');
                            }
                        }
                    });

                    $row.data('ts', ts);
                }

                function refreshAddRows() {
                    const $rows = $addRows.children('.add-item-row');

                    $rows.each(function(i) {
                        $(this).find('.row-number').text(i + 1);
                    });

                    $('#addItemCount').text($rows.length);
                    $rows.find('.btnRemoveItemRow').prop('disabled', $rows.length === 1);
                }

                function addItemRow() {
                    const index = addRowCounter++;
                    const $row = $(addRowTemplate.replace(/__INDEX__/g, index));

                    // Baris baru langsung memakai tgl instruksi kirim di atas.
                    $row.find('.row-instruction-date').val($('#addInstructionDate').val());

                    $addRows.append($row);
                    initOutRow($row);
                    refreshAddRows();

                    return $row;
                }

                function resetAddForm() {
                    $addRows.children('.add-item-row').each(function() {
                        const ts = $(this).data('ts');
                        if (ts) ts.destroy();
                    });

                    $addRows.empty();
                    $('#formStagingOut')[0].reset();

                    addItemRow();
                }

                // Tgl instruksi kirim diisi 1x -> semua baris ikut menyesuaikan,
                // setelah itu tiap baris tetap bisa diubah manual.
                $('#addInstructionDate').on('input change', function() {
                    $addRows.find('.row-instruction-date').val(this.value);
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

                $('#addStagingOut').on('hidden.bs.modal', resetAddForm);

                resetAddForm();

                // ================= TAMBAH: validasi & simpan (AJAX) =================
                // Wajib (menghentikan simpan): Barang & Qty >= 1 di tiap baris, plus
                // Lokasi/Lot/stok untuk sumber "Dari Stok".
                // Boleh kosong tapi ditanyakan dulu: No. SO, Customer, Tgl Instruksi Kirim.
                function collectAddProblems() {
                    clearInvalid();

                    const blocking = [];
                    const soft = [];
                    const $rows = $addRows.children('.add-item-row');

                    const $so = $('#formStagingOut [name="so_number"]');

                    if (!$.trim($so.val())) {
                        soft.push({
                            field: 'No. SO',
                            row: null,
                            $el: $so
                        });
                    }

                    $rows.each(function(i) {
                        const $row = $(this);
                        const no = i + 1;

                        const isStock = $row.find('.src-stock').is(':checked');
                        const $item = $row.find('.item-select');
                        const $qty = $row.find('.row-qty');
                        const $loc = $row.find('.row-location');
                        const $lot = $row.find('.row-lot');
                        const $cust = $row.find('input[name$="[customer]"]');
                        const $date = $row.find('.row-instruction-date');

                        if (!$item.val()) {
                            blocking.push({
                                row: no,
                                message: 'Barang belum dipilih. Cari dan pilih barang dari daftar.',
                                $el: $item
                            });
                        }

                        if (isStock && $item.val() && !$loc.val()) {
                            const noStock = $loc.find('option').length <= 1 && !$loc.prop('disabled');

                            blocking.push({
                                row: no,
                                message: noStock ?
                                    'Barang ini tidak memiliki stok di lokasi mana pun. Ganti barang atau pilih sumber Eksternal.' :
                                    'Lokasi belum dipilih. Pilih lokasi yang memiliki stok barang ini.',
                                $el: $loc
                            });
                        }

                        if (isStock && $loc.val() && !$lot.prop('disabled') &&
                            $lot.find('option').length > 1 && $lot[0].selectedIndex === 0) {
                            blocking.push({
                                row: no,
                                message: 'Lot belum dipilih. Pilih lot yang akan dikeluarkan.',
                                $el: $lot
                            });
                        }

                        const qtyMsg = qtyCheck('Qty', 'sisa stok pada lot ini')($qty, $.trim($qty.val()));

                        if (qtyMsg) {
                            blocking.push({
                                row: no,
                                message: qtyMsg,
                                $el: $qty
                            });
                        }

                        if (!$.trim($cust.val())) {
                            soft.push({
                                field: 'Customer',
                                row: no,
                                $el: $cust
                            });
                        }

                        if (!$date.val()) {
                            soft.push({
                                field: 'Tgl Instruksi Kirim',
                                row: no,
                                $el: $date
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
                    const $btn = $('#btnSaveStagingOut').prop('disabled', true);

                    $.ajax({
                        url: "{{ route('stagings-out.store') }}",
                        method: "POST",
                        data: $form.serialize(),

                        success: function(response) {
                            $('#addStagingOut').modal('hide'); // form di-reset oleh hidden.bs.modal
                            table.ajax.reload(null, false);
                            showToast('success', response.message, 3000);
                        },

                        error: function(xhr) {
                            showAjaxError(xhr);
                        },

                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                }

                $(document).on('submit', '#formStagingOut', function(e) {

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

                    const editIsStock = function() {
                        return $('#editSourceStock').is(':checked');
                    };

                    if (reportChecks(collectChecks([{
                            el: '#editItemId',
                            target: '#editItemSelect',
                            when: editIsStock,
                            validate: requiredCheck('Barang belum dipilih. Pilih barang terlebih dahulu untuk sumber stok.')
                        },
                        {
                            el: '#editLocationSelect',
                            when: editIsStock,
                            validate: requiredCheck('Lokasi belum dipilih. Pilih lokasi yang memiliki stok barang ini.')
                        },
                        {
                            el: '#editQty',
                            when: editIsStock,
                            validate: qtyCheck('Qty', 'sisa stok pada lot ini')
                        }
                    ]))) return;

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

                            showAjaxError(xhr);

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

                    if (reportChecks(collectChecks([{
                            el: '#confirmPickingDate',
                            validate: requiredCheck('Tanggal picking belum diisi.')
                        }]), 'Tanggal belum diisi')) return;

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

                            showAjaxError(xhr);

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

                    if (reportChecks(collectChecks([{
                            el: '#confirmDeliveryDate',
                            validate: requiredCheck('Tanggal resi pengiriman belum diisi.')
                        }]), 'Tanggal belum diisi')) return;
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

                            showAjaxError(xhr);

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
                $('#btnBulkDelete').toggleClass('d-none', selectedStagingOutIds.size === 0);
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
                    icon: 'warning',
                    title: 'Hapus Data?',
                    html: `Yakin mau menghapus <b>${selectedStagingOutIds.size}</b> data terpilih?<br><small class="text-muted">Tindakan ini permanen dan tidak bisa dibatalkan.</small>`,
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