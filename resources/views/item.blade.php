@extends('master')
@section('title', 'Barang')
@section('content')
    {{-- ================= TEMA MODAL (selaras Staging Out) ================= --}}
    <style>
        /* ---------- Kerangka modal ---------- */
        .modal-aesthetic .modal-content {
            border: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            background: #f5f6fb;
            box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
            max-height: calc(100vh - 2rem);
        }

        /* form yang membungkus modal-content (dialog > form > content) */
        .modal-aesthetic .modal-dialog>form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: calc(100vh - 2rem);
        }

        .modal-aesthetic .modal-dialog>form>.modal-content {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
        }

        /* form di dalam modal-content (content > form) */
        .modal-aesthetic .modal-content>form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .modal-aesthetic .modal-body {
            overflow-y: auto;
            min-height: 0;
        }

        /* ---------- Header (hero) ---------- */
        .modal-aesthetic .as-hero {
            align-items: flex-start;
            padding: 1.5rem 2rem 1.25rem;
            background: #fff;
            border: 0;
            border-bottom: 1px solid #eceef5;
            flex-shrink: 0;
        }

        .modal-aesthetic .as-hero-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 1rem;
            font-size: 1.5rem;
            color: #4f46e5;
            background: #eef0ff;
        }

        .modal-aesthetic .as-hero-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .modal-aesthetic .as-hero h4,
        .modal-aesthetic .as-hero h5 {
            color: #2b3350;
            letter-spacing: -.01em;
        }

        .modal-aesthetic .as-hero small {
            color: #8a93a8 !important;
        }

        .modal-aesthetic .as-hero .btn-close {
            margin: 0 0 0 auto;
            padding: .6rem;
            border-radius: .7rem;
            background-color: #f1f2f9;
            background-size: .65rem;
            opacity: .75;
            transition: all .15s;
        }

        .modal-aesthetic .as-hero .btn-close:hover {
            opacity: 1;
            background-color: #e6e8f5;
        }

        /* ---------- Body & footer ---------- */
        .modal-aesthetic .modal-body.as-body {
            padding: 1.5rem 2rem .75rem;
            background: #f5f6fb;
        }

        .modal-aesthetic .modal-footer.as-footer {
            display: flex;
            justify-content: flex-end;
            gap: .6rem;
            padding: 1rem 2rem 1.4rem;
            background: #f5f6fb;
            border: 0;
            flex-shrink: 0;
        }

        .modal-aesthetic .as-btn-save,
        .modal-aesthetic .as-btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            font-size: .875rem;
            line-height: 1.5;
            font-weight: 600;
            cursor: pointer;
            transition: filter .15s, background .15s, box-shadow .15s;
        }

        .modal-aesthetic .as-btn-save {
            padding: .6rem 1.6rem;
            border-radius: .8rem;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65);
        }

        .modal-aesthetic .as-btn-save:hover {
            color: #fff;
            filter: brightness(1.08);
        }

        .modal-aesthetic .as-btn-cancel {
            padding: .6rem 1.3rem;
            border-radius: .8rem;
            color: #5a6482;
            background: #e9ebf5;
        }

        .modal-aesthetic .as-btn-cancel:hover {
            color: #2b3350;
            background: #dfe2f0;
        }

        .modal-aesthetic .as-btn-save:disabled,
        .modal-aesthetic .as-btn-cancel:disabled {
            opacity: .6;
            filter: none;
            pointer-events: none;
        }

        /* ---------- Kartu ---------- */
        .modal-aesthetic .as-card,
        .modal-aesthetic .modal-body .card {
            position: relative;
            background: #fff;
            border: 0 !important;
            border-radius: 1.1rem !important;
            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12) !important;
            overflow: visible;
        }

        .modal-aesthetic .as-card {
            padding: 1.25rem 1.4rem 1.4rem 1.7rem;
        }

        .modal-aesthetic .as-card::before,
        .modal-aesthetic .modal-body .card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 1.1rem;
            bottom: 1.1rem;
            width: 5px;
            border-radius: 0 6px 6px 0;
            background: linear-gradient(180deg, #4f46e5, #db2777);
        }

        .modal-aesthetic .modal-body .card>.card-header {
            background: transparent !important;
            border: 0 !important;
            padding: 1.25rem 1.4rem .25rem 1.7rem !important;
        }

        .modal-aesthetic .modal-body .card>.card-body {
            padding: 1rem 1.4rem 1.4rem 1.7rem !important;
        }

        .modal-aesthetic .as-card-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: 1.1rem;
        }

        .modal-aesthetic .as-card-title strong {
            display: block;
            color: #2b3350;
            font-size: .98rem;
        }

        .modal-aesthetic .as-card-title small {
            color: #8a93a8;
        }

        .modal-aesthetic .as-card-icon,
        .modal-aesthetic .modal-body h5>i {
            width: 2.4rem;
            height: 2.4rem;
            flex-shrink: 0;
            display: inline-grid;
            place-items: center;
            border-radius: .8rem;
            font-size: 1.2rem;
            color: #4f46e5 !important;
            background: #eef0ff;
            margin: 0 !important;
        }

        .modal-aesthetic .modal-body h5>i.text-success {
            color: #db2777 !important;
            background: #fdf0f7;
        }

        .modal-aesthetic .modal-body h5.fw-bold {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: #2b3350;
            font-size: .98rem;
        }

        .modal-aesthetic .bg-light.rounded-3 {
            background: #f3f4ff !important;
            border: 1px dashed #c7cbf0;
            border-radius: .9rem !important;
        }

        .modal-aesthetic hr {
            border-color: #eceef5;
            opacity: 1;
        }

        .modal-aesthetic .alert-primary {
            color: #4f46e5;
            background: linear-gradient(135deg, #eef0ff, #f6f3ff);
            border: 1px dashed #c7cbf0;
            border-radius: .9rem;
        }

        .modal-aesthetic .alert-primary h5 {
            color: #2b3350;
        }

        /* ---------- Label ---------- */
        .modal-aesthetic .form-label,
        .modal-aesthetic .row.fw-semibold.small.text-secondary {
            display: block;
            margin-bottom: .3rem;
            font-size: .68rem !important;
            font-weight: 700 !important;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #8a93a8 !important;
        }

        /* ---------- Input ---------- */
        .modal-aesthetic .form-control,
        .modal-aesthetic .form-select {
            border: 1.5px solid #e6e9f2;
            background-color: #f8f9fd;
            border-radius: .75rem;
            padding: .5rem .8rem;
            font-size: .875rem;
            color: #2b3350;
            box-shadow: none;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }

        .modal-aesthetic .form-select {
            padding-right: 2.25rem;
        }

        .modal-aesthetic .form-control::placeholder {
            color: #a5adc2;
        }

        .modal-aesthetic .form-control:focus,
        .modal-aesthetic .form-select:focus {
            outline: 0;
            background-color: #fff;
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
        }

        .modal-aesthetic .form-control.is-invalid,
        .modal-aesthetic .form-select.is-invalid {
            border-color: #e11d48;
            background-color: #fff5f7;
        }

        .modal-aesthetic .location-item .form-control {
            padding-left: .65rem;
            padding-right: .65rem;
        }

        /* Input group: ikon + field jadi satu kapsul */
        .modal-aesthetic .input-group {
            flex-wrap: nowrap;
            border: 1.5px solid #e6e9f2;
            background: #f8f9fd;
            border-radius: .75rem;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .modal-aesthetic .input-group>.form-control,
        .modal-aesthetic .input-group>.form-select,
        .modal-aesthetic .input-group>.input-group-text {
            border: 0 !important;
            border-radius: 0 !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }

        .modal-aesthetic .input-group>.input-group-text {
            width: 2.6rem;
            justify-content: center;
            padding-right: .2rem;
            font-size: 1.05rem;
            color: #a5adc2;
        }

        .modal-aesthetic .input-group:focus-within {
            background: #fff;
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
        }

        .modal-aesthetic .input-group:has(.is-invalid) {
            border-color: #e11d48;
            background: #fff5f7;
        }

        /* ---------- Tom Select biar senada ---------- */
        .modal-aesthetic .ts-wrapper {
            width: 100%;
        }

        .modal-aesthetic .ts-wrapper.form-select,
        .modal-aesthetic .ts-wrapper.form-select-sm {
            border: 0;
            padding: 0;
            background: none;
            box-shadow: none;
        }

        .modal-aesthetic .ts-wrapper .ts-control {
            min-height: 40px;
            border: 1.5px solid #e6e9f2;
            border-radius: .75rem;
            background: #f8f9fd;
            padding: .45rem .8rem;
            font-size: .875rem;
            color: #2b3350;
            box-shadow: none;
        }

        .modal-aesthetic .ts-wrapper.focus .ts-control {
            background: #fff;
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
        }

        .modal-aesthetic .input-group .ts-wrapper {
            flex: 1 1 auto;
            width: 1%;
        }

        .modal-aesthetic .input-group .ts-wrapper .ts-control,
        .modal-aesthetic .input-group .ts-wrapper.focus .ts-control {
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .modal-aesthetic .ts-dropdown {
            z-index: 1070;
            border: 0;
            border-radius: .75rem;
            box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3);
            overflow: hidden;
        }

        .modal-aesthetic .ts-dropdown .active {
            background: #eef0ff;
            color: #4f46e5;
        }

        /* ---------- Tombol kecil di dalam body ---------- */
        .modal-aesthetic .as-pill-btn {
            border: 0;
            border-radius: 50rem;
            padding: .4rem 1rem;
            font-size: .8rem;
            font-weight: 600;
            color: #4f46e5;
            background: #eef0ff;
            transition: all .15s;
        }

        .modal-aesthetic .as-pill-btn:hover {
            color: #fff;
            background: #4f46e5;
        }

        .modal-aesthetic .btnRemove {
            border: 0;
            border-radius: .65rem;
            color: #e11d48;
            background: #fff1f4;
        }

        .modal-aesthetic .btnRemove:hover {
            color: #fff;
            background: #e11d48;
        }

        .modal-aesthetic .modal-body .btn-outline-secondary {
            border: 1.5px solid #dcdff0;
            border-radius: .7rem;
            font-weight: 600;
            color: #5a6482;
            background: #fff;
        }

        .modal-aesthetic .modal-body .btn-outline-secondary:hover {
            color: #4f46e5;
            border-color: #b9b4f7;
            background: #f6f3ff;
        }

        /* ---------- Modal import (langkah 1, 2, ...) ---------- */
        .modal-aesthetic .modal-body>.d-flex.gap-3 {
            padding: 1.1rem 1.25rem;
            margin-bottom: 1rem;
            background: #fff;
            border-radius: 1.1rem;
            box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12);
        }

        .modal-aesthetic .modal-body>hr {
            display: none;
        }

        .modal-aesthetic .badge.rounded-circle {
            display: inline-grid;
            place-items: center;
            background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
            box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .7);
        }

        .modal-aesthetic .upload-box>.border {
            border: 2px dashed #c7cbe6 !important;
            border-radius: 1rem !important;
            background: #f8f9fd;
            transition: all .15s;
        }

        .modal-aesthetic .upload-box:hover>.border,
        .modal-aesthetic .upload-box.dragover>.border {
            border-color: #7c3aed !important;
            background: #f6f3ff;
            box-shadow: 0 8px 20px -10px rgba(124, 58, 237, .45);
        }

        .modal-aesthetic .upload-box svg {
            width: 2rem;
            height: 2rem;
            color: #7c3aed;
        }

        @media (max-width: 575.98px) {
            .modal-aesthetic .as-hero {
                padding: 1.1rem 1.1rem 1rem;
            }

            .modal-aesthetic .modal-body.as-body {
                padding: 1rem 1rem .5rem;
            }

            .modal-aesthetic .modal-footer.as-footer {
                padding: .9rem 1rem 1.1rem;
            }
        }
    </style>

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

                    <button type="button" id="btnExportItem" class="btn btn-sm btn-success">
                        <i class="bi bi-file-earmark-excel"></i>
                        <span class="d-none d-md-inline ms-1">Export</span>
                    </button>

                </div>

                <div class="modal fade modal-aesthetic" id="addItemModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

                        <div class="modal-content">

                            <form id="addItemForm" action="{{ route('items.store') }}" method="POST">

                                @csrf

                                <div class="modal-header as-hero">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="as-hero-icon"><i class="bx bx-package"></i></div>
                                        <div>
                                            <h4 class="mb-0 fw-bold">Tambah Barang</h4>
                                            <small>Lengkapi informasi barang yang akan ditambahkan.</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body as-body">
<div class="as-card">
<div class="as-card-title">
<div class="as-card-icon"><i class="bx bx-package"></i></div>
<div><strong>Informasi Barang</strong><small>Isi kode, nama, vendor, dan deskripsi barang.</small></div>
</div>


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

                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Incoterms
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bx bx-transfer"></i>
                                            </span>

                                            <select name="incoterms" class="form-select form-select-sm">
                                                <option value="">Pilih Incoterms (Opsional)</option>
                                                @foreach ($incoterms as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
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
</div>

                                <div class="modal-footer as-footer">

                                    <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">
                                        Batal
                                    </button>

                                    <button type="submit" class="as-btn-save">
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

        /* Select2 (searchable) di dalam chip */
        .fx-chip .select2-container { flex: 1 1 auto; min-width: 8.5rem; max-width: 13rem; width: auto !important; }
        .fx-chip .select2-container--default .select2-selection--single { display: flex; align-items: center; height: 100%; min-height: 2.1rem; border: 0; background: transparent; box-shadow: none; }
        .fx-chip .select2-container--default .select2-selection--single .select2-selection__rendered { padding: 0 1.3rem 0 .1rem !important; font-size: .85rem; font-weight: 600; line-height: 1.5rem !important; color: var(--bs-body-color); }
        .fx-chip .select2-container--default .select2-selection--single .select2-selection__arrow { top: 0 !important; right: 0 !important; height: 100% !important; }

            </style>

            <div class="fx" id="filterBar">

                <div class="fx-top">

                    <!-- Pencarian -->
                    <div class="fx-search">
                        <i class="bi bi-search fx-search-icon"></i>
                        <input type="text" id="customSearch" autocomplete="off" placeholder="Cari kode, nama barang, atau vendor" aria-label="Cari">
                        <button type="button" class="fx-search-clear" id="customSearchClear" aria-label="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <div class="fx-actions">
                        <span class="fx-count" id="filterCount" aria-live="polite"></span>
                        <button type="button" class="fx-reset" id="btnResetFilter" title="Kembalikan semua filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset</span>
                        </button>
                    </div>

                </div>

                <div class="fx-chips">

                    <div class="fx-chip">
                        <i class="bi bi-building"></i>
                        <label for="filterVendor">Vendor</label>
                        <select id="filterVendor">
                            <option value="">Semua Vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fx-chip">
                        <i class="bi bi-arrow-left-right"></i>
                        <label for="filterIncoterms">Incoterms</label>
                        <select id="filterIncoterms">
                            <option value="">Semua</option>
                            @foreach ($incoterms as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                            <option value="NON_VHS">Non VHS</option>
                        </select>
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
                        <th>Item Code Supplier</th>
                        <th>Nama Barang</th>
                        <th>Vendor</th>
                        <th>Incoterms</th>
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

    <div class="modal fade modal-aesthetic" id="editItemModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

            <div class="modal-content">

                <form id="editItemForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="modal-header as-hero">
                        <div class="d-flex align-items-center gap-3">
                            <div class="as-hero-icon"><i class="bx bx-edit"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold">Edit Barang</h4>
                                <small>Ubah informasi barang.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body as-body">
<div class="as-card">
<div class="as-card-title">
<div class="as-card-icon"><i class="bx bx-package"></i></div>
<div><strong>Informasi Barang</strong><small>Perbarui kode, nama, vendor, dan deskripsi barang.</small></div>
</div>


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
                                    placeholder="Masukkan Nama Barang" id="edit_name" >

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

                        <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Incoterms
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">
                                                <i class="bx bx-transfer"></i>
                                            </span>

                                            <select name="incoterms" class="form-select form-select-sm" id="edit_incoterms">
                                                <option value="">Pilih Incoterms (Opsional)</option>
                                                @foreach ($incoterms as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
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
</div>

                    <div class="modal-footer as-footer">

                        <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="as-btn-save">
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

                $('#filterIncoterms').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity
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
                            d.incoterms = $('#filterIncoterms').val();
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
                            data: 'item_code_supplier'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'vendor'
                        },
                        {
                            data: 'incoterms'
                        },
                        {
                            data: 'description'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    scrollX: true,
                    autoWidth: false,

                    columnDefs:[{
                        targets: [4],
                        className: 'text-wrap',
                        width: "220px"
                    }]
                })

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                });

                $('#customLength').change(function() {
                    table.page.len($(this).val()).draw();
                });

                $('#filterVendor, #filterIncoterms').change(function() {
                    table.ajax.reload();
                });

                // Export: ikut semua filter yang sedang aktif (vendor, incoterms, pencarian)
                $('#btnExportItem').click(function() {
                    let params = $.param({
                        vendor_id: $('#filterVendor').val() || '',
                        incoterms: $('#filterIncoterms').val() || '',
                        search: table.search() || ''
                    });

                    window.location.href = "{{ route('items.export') }}?" + params;
                });

                // Reset filter
                $('#btnResetFilter').click(function() {
                    $('#filterVendor').val(null).trigger('change');
                    $('#filterIncoterms').val('').trigger('change');
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

            #item th:nth-child(8),
            #item td:nth-child(8) {
                min-width: 250px;
                white-space: normal;
                word-break: break-word;
            }

            #item th:not(:nth-child(8)),
            #item td:not(:nth-child(8)) {
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
                        $('#edit_incoterms').val(res.item.incoterms || '');

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
        <script>
            // ============ TAMPILAN FILTER (penanda aktif, hapus pencarian, preset tanggal) ============
            (function() {
                const $bar = $('#filterBar');
                const chipFilters = ['#filterVendor', '#filterIncoterms'];

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

                    $bar.toggleClass('has-active', count > 0);
                    $('#filterCount').text(count > 0 ? count + ' filter aktif' : '');
                }

                $('#customSearch, ' + chipFilters.join(', ')).on('input change', refreshFilterState);

                // Handler lama mengisi nilai lewat .val() (tanpa event), jadi cek ulang sesudahnya.
                $('#btnResetFilter').on('click', function() {
                    setTimeout(refreshFilterState, 0);
                });

                $('#customSearchClear').on('click', function() {
                    $('#customSearch').val('').trigger('input').trigger('focus');
                });

                refreshFilterState();
            })();
        </script>
    @endpush
@endsection