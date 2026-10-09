@extends('master')
@section('title', 'Stok Mutation')
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


    <div class="row g-5 mb-5">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-warning-subtle text-warning me-4">
                        <i class="bi bi-box-arrow-in-down fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Barang Masuk</small>
                        <h5 class="fw-bold mb-1">{{ number_format($barangMasuk, 0, ',', '.') }}</h5>
                        <small class="text-warning">
                            Bulan Ini
                        </small>
                    </div>

                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-danger-subtle text-danger me-4">
                        <i class="bi bi-box-arrow-up fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Barang Keluar</small>
                        <h5 class="fw-bold mb-1">{{ number_format($barangKeluar, 0, ',', '.') }}</h5>
                        <small class="text-danger">
                            Bulan Ini
                        </small>
                    </div>

                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-primary-subtle text-primary me-4">
                        <svg class="fs-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                            viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <g fill="none">
                                <path
                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                <path fill="currentColor"
                                    d="M20 14a1 1 0 0 1 .117 1.993L20 16H6.414l2.293 2.293a1 1 0 0 1-1.32 1.497l-.094-.083l-3.83-3.83c-.665-.664-.239-1.783.663-1.871L4.241 14zm-4.707-9.707a1 1 0 0 1 1.32-.083l.094.083l3.83 3.83c.665.664.239 1.783-.663 1.871l-.115.006H4a1 1 0 0 1-.117-1.993L4 8h13.586l-2.293-2.293a1 1 0 0 1 0-1.414" />
                            </g>
                        </svg>

                    </div>

                    <div>
                        <small class="text-muted d-block">Total Mutasi</small>
                        <h5 class="fw-bold mb-1">{{ number_format($totalMutasi, '0', ',', '.') }}</h5>
                        <small class="text-primary">
                            Update Hari Ini
                        </small>
                    </div>

                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-info-subtle text-info me-4">
                        <i class="bi bi-bar-chart fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Total Stok</small>
                        <h5 class="fw-bold mb-1">{{ number_format($totalStok, 0, ',', '.') }}</h5>
                        <small class="text-info">
                            Update Hari Ini
                        </small>
                    </div>

                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">

                    <div class="icon-box bg-success-subtle text-success me-4">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>

                    <div>
                        <small class="text-muted d-block">Rentang Tanggal</small>
                        <h6 class="fw-bold mb-1" id="summaryDateRange">Semua Tanggal</h6>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <div class="card rounded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Mutasi</h4>
                <small class="text-muted">Kelola mutasi stok data barang di gudang</small>
            </div>
            <div class="float-end">
                <div class="modal fade modal-aesthetic" id="addMutationModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">

                        <form action="{{ route('stock-mutation.store') }}" method="POST" id="formMutation">

                            @csrf

                            <div class="modal-content border-0 shadow">

                                <div class="modal-header as-hero">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="as-hero-icon"><i class="bx bx-transfer"></i></div>
                                        <div>
                                            <h4 class="mb-0 fw-bold">Tambah Mutasi Stok</h4>
                                            <small>Tambahkan mutasi barang beserta lokasi penyimpanannya.</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body as-body">

                                    <div class="row g-3">

                                        {{-- ================= LEFT =================== --}}

                                        <div class="col">

                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                                    <h5 class="fw-bold mb-0">
                                                        <i class="bi bi-box-seam me-2 text-primary"></i>
                                                        Informasi Transaksi
                                                    </h5>

                                                </div>
                                                <div class="card-body">

                                                    {{-- Tanggal --}}
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Tanggal</label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="bi bi-calendar-event"></i>
                                                            </span>

                                                            <input type="date" class="form-control form-control-sm"
                                                                name="transaction_date" value="{{ date('Y-m-d') }}"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Barang</label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="bi bi-box"></i>
                                                            </span>

                                                            <select id="itemSelect" name="item_id" required></select>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Lokasi</label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="bi bi-geo-alt"></i>
                                                            </span>

                                                            <select id="locationSelect" name="location" required>
                                                                <option value=""></option>

                                                                @foreach ($locations as $location)
                                                                    <option value="{{ $location->location_name }}">
                                                                        {{ $location->location_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    {{-- Lot --}}
                                                    <div class="mb-3" id="lotWrapper" style="display:none;">
                                                        <label class="form-label fw-semibold">Lot</label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="bi bi-tag"></i>
                                                            </span>

                                                            <select id="lotSelect" name="lot"></select>
                                                        </div>
                                                    </div>

                                                    {{-- Stock --}}
                                                    <div class="mb-3">

                                                        <div class="alert alert-primary py-2 mb-0">

                                                            <div class="d-flex align-items-center">

                                                                <i class="bi bi-stack fs-4 me-2"></i>

                                                                <div>

                                                                    <small class="text-muted">

                                                                        Stock Saat Ini

                                                                    </small>

                                                                    <h5 class="mb-0 fw-bold" id="currentStock">

                                                                        0

                                                                    </h5>

                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    {{-- Tipe --}}
                                                    {{-- <div class="mb-3">

                                                        <label class="form-label fw-semibold">

                                                            Tipe Transaksi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-arrow-left-right"></i>

                                                            </span>

                                                            <select class="form-select" name="transaction_type">

                                                                <option value="">
                                                                    Pilih
                                                                </option>

                                                                <option>Opening Balance</option>
                                                                <option>Delivery Order</option>
                                                                <option>Receive Item</option>
                                                                <option>Item Transfer</option>

                                                            </select>

                                                        </div>

                                                    </div> --}}

                                                    {{-- Nomor --}}
                                                    <div>

                                                        <label class="form-label fw-semibold">

                                                            Nomor Transaksi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-upc-scan"></i>

                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="transaction_number"
                                                                placeholder="Contoh: 202606-DO-0777" required>

                                                        </div>

                                                    </div>

                                                    <div class="row g-3 mt-2">

                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-semibold">Jenis</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="bi bi-arrow-down-up"></i>
                                                                </span>
                                                                <select class="form-select" id="qtyType">
                                                                    <option value="in">Masuk</option>
                                                                    <option value="out">Keluar</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-semibold">Qty</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="bi bi-123"></i>
                                                                </span>
                                                                <input type="number" class="form-control" id="qtyInput"
                                                                    min="1" step="1">
                                                            </div>

                                                            <div class="invalid-feedback">
                                                                Qty melebihi stock.
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <input type="hidden" id="qtyIn" name="qty_in" value="0">
                                                    <input type="hidden" id="qtyOut" name="qty_out" value="0">

                                                    <div class="mt-3">

                                                        <label class="form-label fw-semibold">

                                                            Deskripsi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text align-items-start pt-2">

                                                                <i class="bi bi-chat-left-text"></i>

                                                            </span>

                                                            <textarea rows="5" class="form-control form-control-sm" name="description" placeholder="Opsional"></textarea>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- ================= RIGHT =================== --}}

                                        {{-- <div class="col-lg-6">

                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                                    <h5 class="fw-bold mb-0">
                                                        <i class="bi bi-pencil-square me-2 text-success"></i>
                                                        Detail Mutasi
                                                    </h5>

                                                </div>

                                                <div class="card-body">

                                                    <div class="d-flex flex-nowrap gap-3 align-items-start jenis-qty-row">

                                                        <div class="jenis-col">
                                                            <label class="form-label fw-semibold">
                                                                Jenis
                                                            </label>
                                                            <div class="input-group flex-nowrap">
                                                                <span class="input-group-text">
                                                                    <i class="bi bi-arrow-down-up"></i>
                                                                </span>
                                                                <select class="form-select" id="qtyType">
                                                                    <option value="in">Masuk</option>
                                                                    <option value="out">Keluar</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="qty-col">
                                                            <label class="form-label fw-semibold">
                                                                Qty
                                                            </label>
                                                            <div class="input-group flex-nowrap">
                                                                <span class="input-group-text">
                                                                    <i class="bi bi-123"></i>
                                                                </span>
                                                                <input type="number" class="form-control form-control-sm"
                                                                    min="1" step="1" id="qtyInput">
                                                            </div>

                                                            <div class="invalid-feedback">
                                                                Qty melebihi stock.
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <input type="hidden" id="qtyIn" name="qty_in" value="0">
                                                    <input type="hidden" id="qtyOut" name="qty_out" value="0">

                                                    <div class="mt-3">

                                                        <label class="form-label fw-semibold">

                                                            Referensi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-link-45deg"></i>

                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="reference"
                                                                placeholder="FREEPORT INDONESIA (KEEP STOCK) - IDR">

                                                        </div>

                                                    </div>

                                                    <div class="mt-3">

                                                        <label class="form-label fw-semibold">

                                                            Nilai

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-cash-stack"></i>

                                                            </span>

                                                            <input type="text" min="0" id="value"
                                                                class="form-control form-control-sm" name="value"
                                                                placeholder="Contoh: Rp 100.000">

                                                        </div>

                                                    </div>

                                                    <div class="mt-3">

                                                        <label class="form-label fw-semibold">

                                                            Deskripsi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text align-items-start pt-2">

                                                                <i class="bi bi-chat-left-text"></i>

                                                            </span>

                                                            <textarea rows="5" class="form-control form-control-sm" name="description" placeholder="Opsional"></textarea>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div> --}}

                                    </div>

                                </div>

                                <div class="modal-footer as-footer">

                                    <button class="as-btn-cancel" data-bs-dismiss="modal"
                                        type="button">

                                        Batal

                                    </button>

                                    <button class="as-btn-save">
                                        <i class="bx bx-save me-1"></i>
                                        Simpan Mutasi

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>  
                <div class="d-flex flex-wrap gap-2 justify-content-end">

                    <button type="button" class="btn-sm btn btn-outline-danger d-none" id="btnBulkDelete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Hapus (<span id="selectedCount">0</span>)</span>
                    </button>

                    <button type="button" class="btn border-secondary bg-white border btn-sm" data-bs-toggle="modal"
                        data-bs-target="#importMutationModal">
                        <svg class="text-secondary" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                            viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor"
                                d="M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1m-9.71 1.71a1 1 0 0 0 .33.21a.94.94 0 0 0 .76 0a1 1 0 0 0 .33-.21l4-4a1 1 0 0 0-1.42-1.42L13 12.59V3a1 1 0 0 0-2 0v9.59l-2.29-2.3a1 1 0 1 0-1.42 1.42Z" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Import</span>
                    </button>

                    <a href="{{ route('mutation.export') }}" class="btn btn-sm border border-secondary bg-white"
                        id="exportBtn">
                        <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                            viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor"
                                d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Export</span>
                    </a>

                    <button data-bs-toggle="modal" data-bs-target="#addMutationModal" type="button"
                        class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                        <span class=" ms-1">Tambah</span>
                    </button>

                </div>
            </div>

        </div>

        <div class="modal fade modal-aesthetic" id="importMutationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('stock-mutation.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-content border-0 shadow">

                        <div class="modal-header as-hero">

                            <div class="d-flex align-items-center gap-3">

                                <div class="as-hero-icon">
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
                                        Impor Data Mutasi Stok
                                    </h5>

                                    <small class="text-muted">
                                        Import data mutasi stok dari file Excel
                                    </small>
                                </div>

                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                        </div>

                        <div class="modal-body as-body">

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
                                        Download template Excel terlebih dahulu, kemudian isi data mutasi sesuai format yang
                                        telah disediakan.
                                    </p>

                                    <a href="{{ route('stock-mutation.template') }}"
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
                                        <input type="file" id="mutationFile" name="file" accept=".xlsx,.xls"
                                            required hidden>

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

                                            <div id="selectedMutationFile" class="mt-2" style="display:none;">
                                                <small class="text-success fw-semibold">
                                                    ✓ File dipilih: <span id="fileName"></span>
                                                </small>
                                            </div>

                                        </div>
                                    </label>

                                </div>
                            </div>

                        </div>

                        <div class="modal-footer as-footer">
                            <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit" class="as-btn-save">
                                <i class="bi bi-upload me-1"></i>
                                Import Data
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <div class="container-fluid px-5">

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

                <div class="filter-toolbar-card">

                    <div class="filter-toolbar-eyebrow">
                        <div class="filter-toolbar-eyebrow-label">
                            <i class="bi bi-sliders"></i>
                            <span>Filter Data</span>
                        </div>

                        <button type="button" class="btn-sm btn border-secondary bg-white border" id="resetFilter"
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

                        <!-- Rentang Tanggal -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterDateRange">Rentang Tanggal</label>
                            <div class="date-range-wrapper" style="position: relative;">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-calendar-range text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="filterDateRange"
                                        placeholder="Pilih rentang tanggal" title="Rentang Tanggal" readonly
                                        autocomplete="off">
                                </div>

                                <div class="date-range-panel shadow" id="dateRangePanel">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Dari</label>
                                        <input type="date" class="form-control form-control-sm" id="filterStartDate">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1 text-muted">Sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="filterEndDate">
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-primary w-100"
                                            id="dateRangeApply">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Transaksi -->
                        {{-- <div class="filter-group">
                            <label class="filter-label" for="filterTransaction">Jenis Transaksi</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-arrow-left-right text-muted"></i>
                                </span>
                                <select class="form-select border-start-0" id="filterTransaction" title="Jenis Transaksi">
                                    <option value="">Semua Transaksi</option>
                                    @foreach ($transactionTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        <!-- Lokasi (Select2, searchable) -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterLocation">Lokasi</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                </span>
                                <select class="form-select border-start-0 w-100" id="filterLocation">
                                    <option value="">Semua Lokasi</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" data-name="{{ $location->location_name }}">
                                            {{ $location->location_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Vendor (Select2, searchable) -->
                        <div class="filter-group">
                            <label class="filter-label" for="filterVendor">Vendor</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-building text-muted"></i>
                                </span>
                                <select class="form-select border-start-0 w-100" id="filterVendor">
                                    <option value="">Semua Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                </div>

                <table class="table table-bordered" id="stockMutation">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Vendor</th>
                            <th>Lokasi & Lot</th>
                            <th>No. Transaksi</th>
                            <th>Qty Masuk</th>
                            <th>Qty Keluar</th>
                            <th>Qty Akhir</th>
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
    </div>

    <div class="modal fade modal-aesthetic" id="editMutationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">

        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">

            <form id="formEditMutation">

                @csrf
                @method('PUT')

                <div class="modal-content border-0 shadow">

                    <div class="modal-header as-hero">
                        <div class="d-flex align-items-center gap-3">
                            <div class="as-hero-icon"><i class="bx bx-edit"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold">Ubah Mutasi Stok</h4>
                                <small>Ubah mutasi barang beserta lokasi penyimpanannya.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body as-body">

                        <div class="row g-3">

                            {{-- ================= LEFT =================== --}}

                            <div class="col">

                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-box-seam me-2 text-primary"></i>
                                            Informasi Transaksi
                                        </h5>

                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" id="edit_id">

                                        {{-- Tanggal --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Tanggal</label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-calendar-event"></i>
                                                </span>

                                                <input type="date" id="editTransactionDate"
                                                    class="form-control form-control-sm" name="transaction_date"
                                                    value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Barang</label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-box"></i>
                                                </span>

                                                <select id="editItem" name="item_id" required></select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Lokasi</label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-geo-alt"></i>
                                                </span>

                                                <select id="editLocation" name="location" required>
                                                    <option value=""></option>

                                                    @foreach ($locations as $location)
                                                        <option value="{{ $location->location_name }}">
                                                            {{ $location->location_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Lot --}}
                                        <div class="mb-3" id="editLotWrapper" style="display:none;">
                                            <label class="form-label fw-semibold">Lot</label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-tag"></i>
                                                </span>

                                                <select id="editLot" name="lot"></select>
                                            </div>
                                        </div>

                                        {{-- Stock --}}
                                        <div class="mb-3">

                                            <div class="alert alert-primary py-2 mb-0">

                                                <div class="d-flex align-items-center">

                                                    <i class="bi bi-stack fs-4 me-2"></i>

                                                    <div>

                                                        <small class="text-muted">

                                                            Stock Saat Ini

                                                        </small>

                                                        <h5 class="mb-0 fw-bold" id="editCurrentStock">

                                                            0

                                                        </h5>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        {{-- Tipe --}}
                                        {{-- <div class="mb-3">

                                            <label class="form-label fw-semibold">

                                                Tipe Transaksi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-arrow-left-right"></i>

                                                </span>

                                                <select class="form-select" id="editTransactionType"
                                                    name="transaction_type">

                                                    <option value="">
                                                        Pilih
                                                    </option>

                                                    <option>Opening Balance</option>
                                                    <option>Delivery Order</option>
                                                    <option>Receive Item</option>
                                                    <option>Item Transfer</option>

                                                </select>

                                            </div>

                                        </div> --}}

                                        {{-- Nomor --}}
                                        <div>

                                            <label class="form-label fw-semibold">

                                                Nomor Transaksi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-upc-scan"></i>

                                                </span>

                                                <input type="text" id="editTransactionNumber"
                                                    class="form-control form-control-sm" name="transaction_number"
                                                    placeholder="202606-DO-0777" required>

                                            </div>

                                        </div>

                                        <div class="mt-3 d-flex flex-nowrap gap-3 align-items-start jenis-qty-row">

                                            <div class="jenis-col">
                                                <label class="form-label fw-semibold">
                                                    Jenis
                                                </label>
                                                <div class="input-group flex-nowrap">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-arrow-down-up"></i>
                                                    </span>
                                                    <select class="form-select" id="editQtyType">
                                                        <option value="in">
                                                            Masuk
                                                        </option>
                                                        <option value="out">
                                                            Keluar
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="qty-col">
                                                <label class="form-label fw-semibold">
                                                    Qty
                                                </label>
                                                <div class="input-group flex-nowrap">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-123"></i>
                                                    </span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        min="1" step="1" id="editQty">
                                                </div>

                                                <div class="invalid-feedback">
                                                    Qty melebihi stock.
                                                </div>
                                            </div>

                                        </div>
                                        <input type="hidden" id="editQtyIn" name="qty_in" value="0">
                                        <input type="hidden" id="editQtyOut" name="qty_out" value="0">

                                        <div class="mt-3">

                                            <label class="form-label fw-semibold">

                                                Deskripsi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text align-items-start pt-2">

                                                    <i class="bi bi-chat-left-text"></i>

                                                </span>

                                                <textarea rows="5" id="editDescription" placeholder="Opsional" class="form-control form-control-sm"
                                                    name="description"></textarea>

                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- ================= RIGHT =================== --}}

                            {{-- <div class="col-lg-6">

                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-pencil-square me-2 text-success"></i>
                                            Detail Mutasi
                                        </h5>

                                    </div>

                                    <div class="card-body">

                                        <div class="d-flex flex-nowrap gap-3 align-items-start jenis-qty-row">

                                            <div class="jenis-col">
                                                <label class="form-label fw-semibold">
                                                    Jenis
                                                </label>
                                                <div class="input-group flex-nowrap">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-arrow-down-up"></i>
                                                    </span>
                                                    <select class="form-select" id="editQtyType">
                                                        <option value="in">
                                                            Masuk
                                                        </option>
                                                        <option value="out">
                                                            Keluar
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="qty-col">
                                                <label class="form-label fw-semibold">
                                                    Qty
                                                </label>
                                                <div class="input-group flex-nowrap">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-123"></i>
                                                    </span>
                                                    <input type="number" class="form-control form-control-sm"
                                                        min="1" step="1" id="editQty">
                                                </div>

                                                <div class="invalid-feedback">
                                                    Qty melebihi stock.
                                                </div>
                                            </div>

                                        </div>



                                        <input type="hidden" id="editQtyIn" name="qty_in" value="0">
                                        <input type="hidden" id="editQtyOut" name="qty_out" value="0">


                                        <div class="mt-3">

                                            <label class="form-label fw-semibold">

                                                Referensi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-link-45deg"></i>

                                                </span>

                                                <input type="text" id="editReference"
                                                    class="form-control form-control-sm" name="reference"
                                                    placeholder="FREEPORT INDONESIA (KEEP STOCK) - IDR">

                                            </div>

                                        </div>

                                        <div class="mt-3">

                                            <label class="form-label fw-semibold">

                                                Nilai

                                            </label>

                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-cash-stack"></i>
                                                </span>

                                                <input type="text" id="editValueDisplay"
                                                    class="form-control form-control-sm">

                                                <input type="hidden" id="editValue" name="value">
                                            </div>

                                        </div>

                                        <div class="mt-3">

                                            <label class="form-label fw-semibold">

                                                Deskripsi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text align-items-start pt-2">

                                                    <i class="bi bi-chat-left-text"></i>

                                                </span>

                                                <textarea rows="5" id="editDescription" placeholder="Opsional" class="form-control form-control-sm"
                                                    name="description"></textarea>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div> --}}

                        </div>

                    </div>

                    <div class="modal-footer as-footer">

                        <button class="as-btn-cancel" data-bs-dismiss="modal" type="button">

                            Batal

                        </button>

                        <button class="as-btn-save btnSaveEdit">
                            <i class="bx bx-save me-1"></i>
                            Simpan Perubahan

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    @push('script')

        <style>
            /* Container utama: jangan pernah wrap ke baris baru */
            .jenis-qty-row {
                flex-wrap: nowrap !important;
            }

            /* Jenis: lebar tetap mengikuti konten, tidak pernah menyusut */
            .jenis-col {
                flex: 0 0 auto !important;
                width: auto !important;
            }

            .jenis-col .form-select {
                flex: 0 0 auto !important;
                width: auto !important;
            }

            /* Qty: ambil sisa ruang, dan inilah yang menyusut duluan */
            .qty-col {
                flex: 1 1 auto !important;
                min-width: 0 !important;
            }

            .qty-col .form-control {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                width: 1% !important;
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

            .card {
                border-radius: 8px !important;
            }

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

            .card small {
                font-size: .85rem;
            }

            .card h4 {
                font-size: 1.6rem;
                margin: 4px 0;
            }

            /* Samakan tinggi select2 dengan form-select-sm lainnya */
            .select2-container .select2-selection--single {
                height: 31px !important;
                display: flex;
                align-items: center;
                border-radius: 0.25rem;
                border: 1px solid #dee2e6;
                font-size: 0.875rem;
            }

            .select2-container .select2-selection__rendered {
                line-height: 29px !important;
            }

            .select2-container .select2-selection__arrow {
                height: 29px !important;
            }

            /*
             * Select2 mengganti <select> dengan elemen barunya sendiri
             * (.select2-container) yang BUKAN .form-control, jadi tidak
             * otomatis ikut aturan flex Bootstrap di dalam .input-group.
             * Tanpa ini, lebarnya "meluber" dan malah didorong turun ke
             * baris baru oleh flex-wrap bawaan .input-group, bukan
             * sejajar dengan ikonnya.
             */
            .filter-toolbar .input-group {
                flex-wrap: nowrap;
            }

            .filter-toolbar .input-group .select2-container {
                flex: 1 1 auto;
                min-width: 0;
            }

            .filter-toolbar .input-group .select2-selection--single {
                border-top-left-radius: 0 !important;
                border-bottom-left-radius: 0 !important;
                border-left: none !important;
            }

        </style>
        <script>
            // ============ VARIABEL GLOBAL FILTER TANGGAL ============
            let appliedStartDate = '';
            let appliedEndDate = '';

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
                    $('#summaryDateRange').text(formatTanggal(start) + ' - ' + formatTanggal(end));
                } else if (start) {
                    $dateInput.val(start + ' s/d ...');
                    $('#summaryDateRange').text('Mulai ' + formatTanggal(start));
                } else {
                    $dateInput.val('');
                    $('#summaryDateRange').text('Semua Tanggal');
                }

                $panel.removeClass('show');

                table.ajax.reload();
                updateExportUrl();
            });

            // helper untuk format tanggal jadi lebih enak dibaca, misal "16 Jul 2026"
            function formatTanggal(dateStr) {
                const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const d = new Date(dateStr);
                return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
            }
        </script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $('#filterLocation').select2({
                width: '100%'
            });

            $('#filterVendor').select2({
                width: '100%'
            });

            function updateExportUrl() {
                let start = appliedStartDate;
                let end = appliedEndDate;
                let transaction = $('#filterTransaction').val();
                let location = $('#filterLocation').val();
                let vendor = $('#filterVendor').val();
                let search = $('#customSearch').val().trim(); // trim di sini

                let item = new URLSearchParams(window.location.search).get('item');

                let url = new URL("{{ route('mutation.export') }}");

                if (item) url.searchParams.append('item', item);
                if (start) url.searchParams.append('start_date', start);
                if (end) url.searchParams.append('end_date', end);
                if (transaction) url.searchParams.append('transaction_type', transaction);
                if (location) url.searchParams.append('location_id', location);
                if (vendor) url.searchParams.append('vendor_id', vendor);
                if (search) url.searchParams.append('search', search);

                $('#exportBtn').attr('href', url.toString());
            }

            $('#filterTransaction').on('change', updateExportUrl);
            $('#filterLocation').on('change', updateExportUrl);
            $('#customSearch').on('keyup input', updateExportUrl); // trigger tiap ketik

            updateExportUrl();
        </script>
        <style>
            /* Samakan tinggi dengan form-select-sm Bootstrap */
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
        <script>
            $('#editValueDisplay').on('input', function() {

                let angka = $(this).val().replace(/[^\d]/g, '');

                $('#editValue').val(angka);

                if (angka === '') {
                    $(this).val('');
                    return;
                }

                $(this).val(new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(angka));

            });
        </script>
        <script>
            $('#editQtyType').on('change', function() {

                let qty = $('#editQty').val();

                if ($(this).val() == "in") {

                    $('#editQtyIn').val(qty);

                    $('#editQtyOut').val(0);

                } else {

                    $('#editQtyOut').val(qty);

                    $('#editQtyIn').val(0);

                }

            });

            $('#editQty').on('keyup change', function() {

                let qty = $(this).val();

                if ($('#editQtyType').val() == "in") {

                    $('#editQtyIn').val(qty);

                    $('#editQtyOut').val(0);

                } else {

                    $('#editQtyOut').val(qty);

                    $('#editQtyIn').val(0);

                }

            });

            $('#formEditMutation').submit(function(e) {

                e.preventDefault();

                if ($('#editLotWrapper').is(':visible') &&
                    editLotSelect.options && Object.keys(editLotSelect.options).length > 1 &&
                    !editLotSelect.getValue()) {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Silahkan pilih lot terlebih dahulu',
                        showConfirmButton: false,
                        timer: 2500,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });

                    return false;
                }

                let id = $('#edit_id').val();
                console.log({
                    qtyType: $('#editQtyType').val(),
                    qty: $('#editQty').val(),
                    qtyIn: $('#editQtyIn').val(),
                    qtyOut: $('#editQtyOut').val()
                });

                $.ajax({

                    url: '/stock-mutation/' + id,

                    type: 'POST',

                    data: {

                        _token: $('meta[name="csrf-token"]').attr('content'),

                        _method: 'PUT',

                        transaction_date: $('#editTransactionDate').val(),

                        item_id: $('#editItem').val(),

                        location: $('#editLocation').val(),

                        lot: $('#editLot').val(),

                        transaction_type: $('#editTransactionType').val(),

                        transaction_number: $('#editTransactionNumber').val(),

                        warehouse: $('#editWarehouse').val(),

                        reference: $('#editReference').val(),

                        value: $('#editValue').val(),

                        description: $('#editDescription').val(),

                        qty_in: $('#editQtyIn').val(),

                        qty_out: $('#editQtyOut').val()

                    },

                    beforeSend: function() {

                        $('.btnSaveEdit').prop('disabled', true);

                    },

                    success: function(res) {

                        $('#editMutationModal').modal('hide');

                        $('.btnSaveEdit').prop('disabled', false);

                        table.ajax.reload(null, false);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                            didOpen: () => {
                                document.querySelector('.swal2-container').style.zIndex =
                                    '9999999';
                            }
                        });

                    },
                    error: function(xhr) {

                        $('.btnSaveEdit').prop('disabled', false);

                        console.log(xhr.status);
                        console.log(xhr.responseText);

                        let message = 'Terjadi kesalahan pada server.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: message,
                            showConfirmButton: false,
                            timer: 2500,
                            didOpen: () => {
                                document.querySelector('.swal2-container').style.zIndex =
                                    '9999999';
                            }
                        });
                    }

                });

            });
        </script>
        <script>
            let table;

            $(document).ready(function() {

                table = $('#stockMutation').DataTable({

                    dom: 'rtip',
                    processing: true,
                    serverSide: true,

                    ajax: {
                        url: "{{ route('stock-mutation.data') }}",
                        data: function(d) {
                            d.start_date = appliedStartDate;
                            d.end_date = appliedEndDate;
                            d.transaction_type = $('#filterTransaction').val();
                            d.location_id = $('#filterLocation').val();
                            d.vendor_id = $('#filterVendor').val();
                            d.item = new URLSearchParams(window.location.search).get('item');
                        }
                    },

                    columns: [{
                            data: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'transaction_date',
                            name: 'transaction_date'
                        },
                        {
                            data: 'barang',
                            name: 'barang',
                            orderable: false
                        },
                        {
                            data: 'vendor',
                            name: 'vendor'
                        },
                        {
                            data: 'location_name',
                            name: 'location.location_name'
                        },
                        {
                            data: 'transaction_number',
                            name: 'transaction_number'
                        },
                        {
                            data: 'qty_in',
                            name: 'qty_in'
                        },
                        {
                            data: 'qty_out',
                            name: 'qty_out'
                        },
                        {
                            data: 'qty_balance',
                            name: 'qty_balance'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],

                    scrollX: true,
                    autoWidth: false,
                    columnDefs: [{
                        targets: [3, 10],
                        width: "250px",
                        className: "text-wrap"
                    }],

                    initComplete: function() {
                        moveDataTablesElements();
                    },
                    drawCallback: function() {
                        moveDataTablesElements();
                    }

                });

                function moveDataTablesElements() {
                    const $info = $('#stockMutation_info');
                    if ($info.length && !$('#lengthWrapper').find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#lengthWrapper');
                    }

                    const $paginate = $('#stockMutation_paginate');
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

                // hanya jenis transaksi, lokasi & vendor yang auto-reload; tanggal menunggu tombol "Terapkan"
                $('#filterTransaction,#filterLocation,#filterVendor')
                    .on('change', function() {
                        table.ajax.reload();
                        updateExportUrl();
                    });

                $('#resetFilter').click(function() {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#filterDateRange').val('');
                    $('#summaryDateRange').text('Semua Tanggal');
                    $('#filterTransaction').val('').trigger('change');
                    $('#filterLocation').val('').trigger('change');
                    $('#filterVendor').val('').trigger('change');
                    $('#customSearch').val('');

                    appliedStartDate = '';
                    appliedEndDate = '';

                    table.search('').draw();
                    updateExportUrl();
                    table.ajax.reload();
                });

                // sinkronisasi qty_in / qty_out sebelum submit
                function syncQty() {
                    const type = $('#qtyType').val();
                    const qty = parseFloat($('#qtyInput').val()) || 0;

                    if (type === 'in') {
                        $('#qtyIn').val(qty);
                        $('#qtyOut').val(0);
                    } else {
                        $('#qtyIn').val(0);
                        $('#qtyOut').val(qty);
                    }
                }

                $(document).on('change input', '#qtyType, #qtyInput', syncQty);

                // ====== PERBAIKAN UTAMA: gunakan event delegation ======
                $(document).on('submit', '#formMutation', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    syncQty();

                    $.ajax({
                        url: "{{ route('stock-mutation.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        success: function(response) {

                            $('#addMutationModal').modal('hide');
                            $('#formMutation')[0].reset();

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

                        },
                    });

                    return false; // safety net tambahan, mencegah submit native
                });

            });
        </script>

        <style>
            .dataTables_filter,
            .dataTables_length {
                display: none;
            }

            .dataTables_paginate {
                display: none;
            }

            #stockMutation {
                width: 100% !important;
            }

            #stockMutation td.text-wrap,
            #stockMutation th.text-wrap {
                white-space: normal !important;
                word-break: break-word;
            }

            /* Beri jarak seluruh area DataTable */
            #stock_wrapper {
                padding: 1rem;
            }

            /* Jika pakai DataTables 2.x */
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

            @media (max-width: 768px) {

                .dt-top {
                    flex-direction: column;
                    align-items: stretch !important;
                }

                .dt-left {
                    width: 100%;
                }

                .dt-right {
                    width: 100%;
                }

                .dt-right button {
                    width: 100%;
                }

                #filterLocation {
                    width: 100%;
                }

                #customSearch {
                    width: 100%;
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
        <style>
            .upload-box {
                cursor: pointer;
                transition: all .2s ease;
            }

            .upload-box:hover .border {
                background: #f8fafc;
                border-color: #0d6efd !important;
            }
        </style>

        <script>
            const dropZone = document.querySelector('.upload-box');
            const fileInput = document.getElementById('mutationFile');
            const fileName = document.getElementById('fileName');
            const selectedFile = document.getElementById('selectedMutationFile');

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
        <script>
            document.getElementById('mutationFile').addEventListener('change', function() {

                const file = this.files[0];

                if (file) {
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('selectedMutationFile').style.display = 'block';
                }
            });
        </script>

        <script>
            let selectedMutationIds = new Set();

            function toggleBulkActionBar() {
                $('#selectedCount').text(selectedMutationIds.size);
                $('#btnBulkDelete').toggleClass('d-none', selectedMutationIds.size === 0);
            }

            function resetMutationSelection() {
                selectedMutationIds.clear();
                $('#checkAll').prop('checked', false);
                toggleBulkActionBar();
            }

            $(document).on('change', '.row-checkbox', function() {
                let id = $(this).val();

                if (this.checked) {
                    selectedMutationIds.add(id);
                } else {
                    selectedMutationIds.delete(id);
                }

                toggleBulkActionBar();
            });

            $(document).on('change', '#checkAll', function() {
                let checked = this.checked;

                $('.row-checkbox').prop('checked', checked).each(function() {
                    let id = $(this).val();

                    if (checked) {
                        selectedMutationIds.add(id);
                    } else {
                        selectedMutationIds.delete(id);
                    }
                });

                toggleBulkActionBar();
            });

            // Reset seleksi tiap kali tabel digambar ulang (ganti halaman, filter, reload)
            $('#stockMutation').on('draw.dt', function() {
                resetMutationSelection();
            });

            $('#btnBulkDelete').on('click', function() {

                if (selectedMutationIds.size === 0) return;

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Mutasi?',
                    html: `Yakin mau menghapus <b>${selectedMutationIds.size}</b> mutasi terpilih?<br><small class="text-muted">Tindakan ini permanen dan tidak bisa dibatalkan.</small>`,
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
                        url: "{{ route('stock-mutation.bulk-destroy') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: Array.from(selectedMutationIds)
                        },

                        success: function(res) {

                            table.ajax.reload(null, false);
                            resetMutationSelection();

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
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),

                        success: function(res) {
                            console.log(res);

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
            let itemSelect;
            let locationSelect;
            let lotSelect;

            itemSelect = new TomSelect("#itemSelect", {

                valueField: "id",

                labelField: "text",

                searchField: ["text"],

                preload: true,

                create: false,

                maxOptions: 20,

                load: function(query, callback) {

                    $.ajax({

                        url: "{{ route('stock-mutation.search-stock') }}",

                        type: "GET",

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

            locationSelect = new TomSelect("#locationSelect", {
                placeholder: "Pilih atau ketik lokasi...",
                allowEmptyOption: true,
                create: true,
                persist: false,
                createOnBlur: true,
                items: []
            });

            lotSelect = new TomSelect("#lotSelect", {
                valueField: "id",
                labelField: "text",
                searchField: ["text"],
                placeholder: "Pilih lot...",
                create: false,
                allowEmptyOption: true
            });

            function loadCurrentStock() {

                let item = itemSelect.getValue();

                let location = locationSelect.getValue();

                let lot = lotSelect.getValue();

                if (item == "" || location == "") {

                    $("#currentStock").html("0");

                    return;

                }

                $.get(

                    "{{ route('stock-mutation.current-stock') }}",

                    {

                        item_id: item,

                        location: location,

                        lot: lot

                    },

                    function(res) {

                        $("#currentStock").html(parseInt(res.qty));

                        validateQty();

                    }

                );

            }

            // Ambil daftar lot untuk kombinasi barang + lokasi yang sedang
            // dipilih. Kalau cuma ada satu lot (atau tidak ada lot sama
            // sekali), field lot langsung diisi otomatis / disembunyikan.
            // Kalau lebih dari satu, user wajib pilih sendiri.
            function loadLots(callback) {

                let item = itemSelect.getValue();
                let location = locationSelect.getValue();

                lotSelect.clear(true);
                lotSelect.clearOptions();

                if (item == "" || location == "") {
                    $("#lotWrapper").hide();
                    loadCurrentStock();
                    if (callback) callback();
                    return;
                }

                $.get(

                    "{{ route('stock-mutation.lots') }}",

                    {
                        item_id: item,
                        location: location
                    },

                    function(res) {

                        res.forEach(function(opt) {
                            lotSelect.addOption(opt);
                        });

                        if (res.length === 0) {

                            // Barang tanpa pelacakan lot di lokasi ini.
                            $("#lotWrapper").hide();
                            lotSelect.setValue("", true);

                        } else if (res.length === 1) {

                            $("#lotWrapper").show();
                            lotSelect.setValue(res[0].id, true);

                        } else {

                            $("#lotWrapper").show();
                            // Lebih dari satu lot: jangan auto-pilih,
                            // biarkan user yang menentukan.

                        }

                        loadCurrentStock();

                        if (callback) callback();

                    }

                );

            }

            itemSelect.on("change", function() {

                let item = itemSelect.getValue();

                if (!item) {
                    return;
                }

                $.get(
                    "{{ route('stock-mutation.default-location') }}",

                    {
                        item_id: item
                    },
                    function(res) {
                        if (res) {

                            if (!locationSelect.options[res.id]) {
                                locationSelect.addOption(res);
                            }

                            locationSelect.setValue(res.id, true)
                        }

                        loadLots();
                    }
                )

            });

            locationSelect.on("change", function() {

                loadLots();

            });

            lotSelect.on("change", function() {

                loadCurrentStock();

            });

            $("#qtyType").on("change", function() {

                updateQty();

                validateQty();

            });


            $("#qtyInput").on("keyup change", function() {

                updateQty();

                validateQty();

            });


            function updateQty() {

                let qty = Number($("#qtyInput").val());

                if (isNaN(qty)) {

                    qty = 0;

                }

                if ($("#qtyType").val() == "in") {

                    $("#qtyIn").val(qty);

                    $("#qtyOut").val(0);

                } else {

                    $("#qtyOut").val(qty);

                    $("#qtyIn").val(0);

                }

            }

            function validateQty() {

                let current = Number($("#currentStock").text());

                let input = Number($("#qtyInput").val());

                if (isNaN(current)) {

                    current = 0;

                }

                if (isNaN(input)) {

                    input = 0;

                }

                if ($("#qtyType").val() == "out") {

                    if (input > current) {

                        $("#qtyInput").addClass("is-invalid");

                    } else {

                        $("#qtyInput").removeClass("is-invalid");

                    }

                } else {

                    $("#qtyInput").removeClass("is-invalid");

                }

            }

            $("#formMutation").submit(function(e) {

                updateQty();
                validateQty();

                if ($("#qtyInput").hasClass("is-invalid")) {

                    e.preventDefault();

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "Qty keluar melebihi stok",
                        showConfirmButton: false,
                        timer: 2500,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });

                    return false;
                }

                // Kalau field lot sedang tampil (lebih dari satu lot untuk
                // barang + lokasi ini) tapi belum dipilih, wajibkan dulu.
                if ($("#lotWrapper").is(":visible") &&
                    lotSelect.options && Object.keys(lotSelect.options).length > 1 &&
                    !lotSelect.getValue()) {

                    e.preventDefault();

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "Silakan pilih lot terlebih dahulu",
                        showConfirmButton: false,
                        timer: 2500,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '9999999';
                        }
                    });

                    return false;
                }

            });

            $("#addMutationModal").on("hidden.bs.modal", function() {

                this.querySelector("form").reset();

                itemSelect.clear();

                locationSelect.clear();

                lotSelect.clear(true);
                lotSelect.clearOptions();
                $("#lotWrapper").hide();

                $("#currentStock").html("0");

                $("#qtyInput").removeClass("is-invalid");

                $("#qtyIn").val(0);

                $("#qtyOut").val(0);

            });
        </script>

        <style>
            /* Wrapper */
            .input-group .ts-wrapper {
                flex: 1 1 auto;
                width: 1%;
            }

            /* Control */
            .input-group .ts-control {
                min-height: 38px;
                padding: .375rem .75rem;

                border: 1px solid #dee2e6 !important;
                border-left: 1px solid #dee2e6 !important;

                border-radius: 0 .375rem .375rem 0 !important;
                box-shadow: none !important;
            }

            /* Focus */
            .input-group .ts-wrapper.focus .ts-control {
                border-color: #86b7fe !important;
            }

            /* Hilangkan double border di antara icon & select */
            .input-group .input-group-text {
                border-right: 1px solid #dee2e6;
            }

            /* Dropdown */
            .ts-dropdown {
                border-radius: .5rem;
            }

            .modal-content {
                border: 0;
                border-radius: 18px;
            }

            .modal-header {
                border-bottom: 1px solid #edf2f7;
            }

            .card {
                border-radius: 16px;
            }

            .input-group-text {
                border-color: #dee2e6;
                width: 45px;
                justify-content: center;
            }


            .form-control:focus,
            .form-select:focus {
                border-color: #86b7fe;
            }

            .form-label {
                margin-bottom: 6px;
            }

            .alert {
                border-radius: 14px;
            }

            textarea {
                resize: none;
            }
        </style>

        <script>
            let edititemSelect;
            let editLocationSelect;
            let editLotSelect;

            edititemSelect = new TomSelect("#editItem", {
                valueField: "id",
                labelField: "text",
                searchField: ["text"],
                preload: "focus",
                create: false,
                maxOptions: 20,

                load: function(query, callback) {
                    $.ajax({
                        url: "{{ route('stock-mutation.search-stock') }}",
                        type: "GET",
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

            edititemSelect.on("dropdown_open", function() {
                this.load("");
            });

            editLocationSelect = new TomSelect("#editLocation", {
                placeholder: "Pilih atau ketik lokasi...",
                allowEmptyOption: true,
                create: true,
                persist: false,
                createOnBlur: true,
                items: []
            });

            editLotSelect = new TomSelect("#editLot", {
                valueField: "id",
                labelField: "text",
                searchField: ["text"],
                placeholder: "Pilih lot...",
                create: false,
                allowEmptyOption: true
            });

            // Ambil daftar lot untuk kombinasi barang + lokasi yang sedang
            // dipilih di modal edit. presetLot (opsional) dipakai saat
            // membuka data mutasi lama, supaya lot yang sudah tersimpan
            // langsung terpilih.
            function editLoadLots(presetLot, callback) {

                let item = edititemSelect.getValue();
                let location = editLocationSelect.getValue();

                editLotSelect.clear(true);
                editLotSelect.clearOptions();

                if (item == "" || location == "") {
                    $("#editLotWrapper").hide();
                    loadCurrentStockEdit();
                    if (callback) callback();
                    return;
                }

                $.get(

                    "{{ route('stock-mutation.lots') }}",

                    {
                        item_id: item,
                        location: location
                    },

                    function(res) {

                        res.forEach(function(opt) {
                            editLotSelect.addOption(opt);
                        });

                        if (res.length === 0) {

                            $("#editLotWrapper").hide();
                            editLotSelect.setValue("", true);

                        } else if (res.length === 1) {

                            $("#editLotWrapper").show();
                            editLotSelect.setValue(res[0].id, true);

                        } else {

                            $("#editLotWrapper").show();

                            if (presetLot !== undefined && presetLot !== null && presetLot !== "") {
                                editLotSelect.setValue(presetLot, true);
                            }

                        }

                        loadCurrentStockEdit();

                        if (callback) callback();

                    }

                );

            }

            $(document).on('click', '.btnEdit', function() {

                let id = $(this).data('id');

                $.ajax({

                    url: '/stock-mutation/' + id + '/edit',
                    type: 'GET',

                    success: function(res) {

                        $('#edit_id').val(res.id);
                        $('#editTransactionDate').val(res.transaction_date);
                        $('#editTransactionNumber').val(res.transaction_number);
                        $('#editDescription').val(res.description);

                        // BARANG
                        edititemSelect.clear(true);

                        // Jangan clearOptions()

                        if (!edititemSelect.options[res.item_id]) {
                            edititemSelect.addOption({
                                id: res.item_id,
                                text: res.item_name
                            });
                        }

                        edititemSelect.setValue(res.item_id, true);

                        // LOKASI
                        editLocationSelect.setValue(res.location, true);

                        // LOT — sekarang item & lokasi sudah benar-benar
                        // ter-set di TomSelect, baru muat daftar lot untuk
                        // kombinasi ini dan pilihkan lot yang sudah
                        // tersimpan sebelumnya di data mutasi ini.
                        editLoadLots(res.lot);

                        // QTY
                        $('#editQtyType').val(res.qty_type);
                        $('#editQty').val(parseInt(res.qty));
                        $('#editQtyIn').val(res.qty_in);
                        $('#editQtyOut').val(res.qty_out);

                        $('#editMutationModal').modal('show');
                    }

                });

            });

            function loadCurrentStockEdit() {

                let item = edititemSelect.getValue();
                let location = editLocationSelect.getValue();
                let lot = editLotSelect.getValue();

                if (item == "" || location == "") {

                    $("#editCurrentStock").html("0");
                    return;

                }

                $.get(
                    "{{ route('stock-mutation.current-stock') }}", {
                        item_id: item,
                        location: location,
                        lot: lot
                    },
                    function(res) {

                        $("#editCurrentStock").html(parseInt(res.qty));

                        validateQtyEdit();

                    }
                );

            }

            edititemSelect.on("change", function() {
                editLoadLots();
            });

            editLocationSelect.on("change", function() {
                editLoadLots();
            });

            editLotSelect.on("change", function() {
                loadCurrentStockEdit();
            });

            $("#editQtyType").on("change", function() {
                validateQtyEdit();
            });

            $("#editQty").on("keyup change", function() {
                validateQtyEdit();
            });

            function validateQtyEdit() {

                let current = Number($("#editCurrentStock").text());
                let input = Number($("#editQty").val());

                if (isNaN(current)) current = 0;
                if (isNaN(input)) input = 0;

                if ($("#editQtyType").val() == "out") {

                    if (input > current) {
                        $("#editQty").addClass("is-invalid");
                    } else {
                        $("#editQty").removeClass("is-invalid");
                    }

                } else {

                    $("#editQty").removeClass("is-invalid");

                }

            }

            $("#editMutationModal").on("hidden.bs.modal", function() {

                this.querySelector("form").reset();

                edititemSelect.clear();
                edititemSelect.clearOptions();

                editLocationSelect.clear();

                editLotSelect.clear(true);
                editLotSelect.clearOptions();
                $("#editLotWrapper").hide();

                $("#editCurrentStock").html("0");

                $("#editQty").removeClass("is-invalid");

            });
        </script>
    @endpush
@endsection