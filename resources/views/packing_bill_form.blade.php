@extends('master')
@section('title', 'Bill Packing ' . $payload['so_number'])
@section('content')
    {{-- ================= TEMA (selaras halaman Barang & Staging Out) ================= --}}
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

    <style>
        .pb-wrap .filter-toolbar {
            grid-template-columns: repeat(3, 1fr);
        }

        @media (max-width: 767.98px) {
            .pb-wrap .filter-toolbar {
                grid-template-columns: 1fr;
            }
        }

        .pb-info-value {
            font-size: .95rem;
            font-weight: 700;
            color: #2b3350;
            word-break: break-word;
        }

        .pb-table {
            min-width: 960px;
            margin-bottom: 0;
        }

        .pb-table th,
        .pb-table td {
            padding: 12px 14px;
            vertical-align: middle;
        }

        .pb-table thead th {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #8a93a8;
            white-space: nowrap;
        }

        /* Qty dengan tombol - dan + (bisa tanpa mengetik angka) */
        .pb-qty-group {
            max-width: 150px;
            margin: 0 auto;
            overflow: hidden; /* sudut tombol - dan + ikut membulat mengikuti kapsul */
        }

        .pb-qty-group .pb-qty-btn:first-child {
            border-radius: 0;
        }

        .pb-qty-btn {
            position: relative;
            z-index: 2;
            flex: 0 0 auto;
            width: 2.4rem;
            border: 0;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1;
            color: #4f46e5;
            background: #eef0ff;
            cursor: pointer;
            pointer-events: auto;
            user-select: none;
            transition: background-color .15s, color .15s;
        }

        .pb-qty-btn:hover {
            color: #fff;
            background: #4f46e5;
        }

        .pb-qty-btn:active {
            filter: brightness(.92);
        }

        .pb-qty-group .pb-qty {
            text-align: center;
            font-weight: 600;
            padding-left: .2rem;
            padding-right: .2rem;
        }

        .pb-empty {
            padding: 1.5rem;
            text-align: center;
            color: #8a93a8;
            border: 1px dashed #cfd5e6;
            border-top: 0;
            border-radius: 0 0 .75rem .75rem;
        }

        .pb-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.25rem;
            padding: 1rem 1.25rem;
            background: #f8f9fb;
            border: 1px solid #eceef2;
            border-radius: 1rem;
        }

        .pb-total-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #8a93a8;
        }

        .pb-total {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.01em;
            color: #2b3350;
        }

        .pb-print-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .6rem 1.3rem;
            border: 0;
            border-radius: .8rem;
            font-size: .875rem;
            font-weight: 600;
            color: #0f5132;
            background: #d1e7dd;
            transition: filter .15s, background .15s, color .15s;
        }

        .pb-print-btn:hover {
            color: #fff;
            background: #198754;
        }

        /* ----- Koli: 1 koli tampil polos, 2+ koli tampil sebagai kartu ----- */
        .pb-koli-head {
            display: none;
        }

        #pbKolis.multi .pb-koli {
            margin-bottom: 1.1rem;
            padding: 1rem 1.1rem 1.1rem;
            background: #fff;
            border: 1px solid #e3e6f3;
            border-left: 4px solid #4f46e5;
            border-radius: 1rem;
            box-shadow: 0 8px 20px -14px rgba(43, 51, 80, .4);
        }

        #pbKolis.multi .pb-koli-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: .75rem;
        }

        .pb-koli-badge {
            width: 34px;
            height: 34px;
            border-radius: .7rem;
            display: grid;
            place-items: center;
            font-size: 1.1rem;
            color: #4f46e5;
            background: #eef0ff;
        }

        .pb-koli-title {
            color: #2b3350;
        }

        .pb-koli-subtotal {
            font-size: .85rem;
            font-weight: 700;
            color: #4f46e5;
        }

        /* Isi koli (pilih item di dalam koli) */
        .pb-koli-items {
            display: none;
            align-items: flex-start;
            gap: .7rem;
            margin-bottom: .85rem;
        }

        #pbKolis.multi .pb-koli:not(.pb-shared) .pb-koli-items {
            display: flex;
        }

        .pb-koli-items-label {
            padding-top: .35rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #8a93a8;
        }

        .pb-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .pb-chip {
            position: relative;
            margin: 0;
            cursor: pointer;
        }

        .pb-chip input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .pb-chip span {
            display: inline-block;
            padding: .3rem .8rem;
            border: 1px solid #e3e6f3;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            color: #6b7390;
            background: #f1f3f9;
            transition: background-color .15s, color .15s, border-color .15s;
        }

        .pb-chip:hover span {
            border-color: #4f46e5;
        }

        .pb-chip input:checked+span {
            color: #fff;
            background: #4f46e5;
            border-color: #4f46e5;
        }

        .pb-chip input:focus-visible+span {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }

        /* Material bersama (hanya tampil kalau koli lebih dari 1) */
        .pb-shared {
            display: none;
        }

        #pbKolis.multi .pb-shared {
            display: block;
            border-left-color: #e08a1e;
        }

        .pb-shared .pb-koli-badge {
            color: #b36a0b;
            background: #fff1dc;
        }

        .pb-shared .pb-koli-subtotal {
            color: #b36a0b;
        }

        .pb-unassigned {
            margin-top: .25rem;
            margin-bottom: .5rem;
            padding: .6rem .9rem;
            border-radius: .8rem;
            font-size: .82rem;
            color: #997404;
            background: #fff3cd;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Bill Packing</h4>
                <small class="text-muted">Rincian material packing yang akan diberikan ke customer</small>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-success pb-print-trigger d-none">
                    <i class="bi bi-printer"></i>
                    <span class="d-none d-md-inline ms-1">Cetak / PDF</span>
                </button>
                <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-md-inline ms-1">Kembali</span>
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="container-fluid px-4 pb-wrap modal-aesthetic">

                <div class="filter-toolbar-card">
                    <div class="filter-toolbar-eyebrow">
                        <div class="filter-toolbar-eyebrow-label">
                            <i class="bi bi-receipt"></i>
                            <span>Informasi SO</span>
                        </div>
                    </div>

                    <div class="filter-toolbar">
                        <div class="filter-group">
                            <span class="filter-label">No. SO</span>
                            <div class="pb-info-value">{{ $payload['so_number'] }}</div>
                        </div>
                        <div class="filter-group">
                            <span class="filter-label">Customer</span>
                            <div class="pb-info-value">{{ $payload['customer'] ?: '-' }}</div>
                        </div>
                        <div class="filter-group">
                            <span class="filter-label">Jumlah Item</span>
                            <div class="pb-info-value">{{ count($payload['items']) }} item</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div class="as-card-title mb-0">
                        <div class="as-card-icon"><i class="bx bx-package"></i></div>
                        <div>
                            <strong>Material yang dipakai</strong>
                            <small>Pilih material, lalu isi qty dengan tombol - / + atau ketik angka. Kalau SO dipacking jadi
                                beberapa kardus, klik <b>Tambah Koli</b> lalu pilih isi tiap koli.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <a href="{{ route('packing-materials.index') }}" class="small fw-normal text-muted">
                            Kelola master material <i class="bx bx-link-external"></i>
                        </a>
                        <button type="button" id="pbAddKoli" class="as-pill-btn">
                            <i class="bx bx-box me-1"></i> Tambah Koli
                        </button>
                    </div>
                </div>

                <div id="pbKolis"></div>

                <div id="pbUnassigned" class="pb-unassigned d-none"></div>

                <div class="pb-footer">
                    <div>
                        <div class="pb-total-label">Total Biaya Packing</div>
                        <div class="pb-total" id="pbTotal">Rp 0</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="pb-print-btn pb-print-trigger d-none">
                            <i class="bx bx-printer me-1"></i> Cetak / PDF
                        </button>
                        <button type="button" class="as-btn-save" id="pbSave">
                            <i class="bx bx-save me-1"></i> <span id="pbSaveLabel">Simpan Bill</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    {{-- SweetAlert2 dimuat per halaman (sama seperti halaman Staging Out) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container {
            z-index: 20000 !important;
        }
    </style>
    <script>
        $(function() {
            const DATA = @json($payload);
            const URL_SAVE = @json(route('packing-bills.save'));
            const URL_PRINT = @json(route('packing-bills.print', ':id'));
            const CSRF = @json(csrf_token());

            const state = {
                so: DATA.so_number,
                items: DATA.items || [],
                materials: DATA.materials || [],
                billId: DATA.bill ? DATA.bill.id : null
            };

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const money = n => 'Rp ' + (Number(n) || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
            const fmtQty = n => String(Number(n)).replace('.', ',');
            const parseQty = v => {
                const n = parseFloat(String(v ?? '').trim().replace(',', '.'));
                return isNaN(n) ? 0 : n;
            };

            const $root = $('#pbKolis');
            const koliEls = () => $root.find('.pb-koli').not('.pb-shared');
            const isMulti = () => koliEls().length > 1;

            // Tombol Cetak / PDF langsung muncul begitu bill tersimpan.
            function refreshBillState() {
                $('.pb-print-trigger').toggleClass('d-none', !state.billId);
                $('#pbSaveLabel').text(state.billId ? 'Simpan Perubahan' : 'Simpan Bill');
            }

            // ---------- Kerangka koli / material bersama ----------
            function sectionHtml(kind) {
                const shared = kind === 'shared';
                return `
                <div class="pb-koli ${shared ? 'pb-shared' : ''}">
                    <div class="pb-koli-head">
                        <div class="d-flex align-items-center gap-2">
                            <span class="pb-koli-badge"><i class="bx ${shared ? 'bx-layer' : 'bx-box'}"></i></span>
                            <strong class="pb-koli-title">${shared ? 'Material Bersama' : 'Koli'}</strong>
                            ${shared ? '<span class="text-muted small">Dipakai semua koli, misalnya pallet atau wrapping</span>' : ''}
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="pb-koli-subtotal">Subtotal: Rp 0</span>
                            ${shared ? '' : '<button type="button" class="btn btn-sm btnRemove pb-koli-del" title="Hapus koli"><i class="bx bx-trash"></i></button>'}
                        </div>
                    </div>
                    ${shared ? '' : '<div class="pb-koli-items"><span class="pb-koli-items-label">Isi koli</span><div class="pb-chips"></div></div>'}
                    <div class="table-responsive">
                        <table class="table table-bordered pb-table">
                            <thead>
                                <tr>
                                    <th style="min-width:280px">Material</th>
                                    <th style="width:170px" class="text-center">Qty</th>
                                    <th style="width:90px">Satuan</th>
                                    <th style="width:140px" class="text-end">Harga</th>
                                    <th style="width:150px" class="text-end">Subtotal</th>
                                    <th style="width:60px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="pb-lines"></tbody>
                        </table>
                    </div>
                    <div class="pb-empty d-none">Belum ada material. Klik <b>Tambah Material</b> untuk mengisi.</div>
                    <div class="mt-3">
                        <button type="button" class="as-pill-btn pb-add-line">
                            <i class="bx bx-plus me-1"></i> Tambah Material
                        </button>
                    </div>
                </div>`;
            }

            function chipHtml(id, label, on) {
                return `<label class="pb-chip" title="${esc(label)}"><input type="checkbox" class="pb-koli-item" value="${esc(id)}" ${on ? 'checked' : ''}><span>${esc(label)}</span></label>`;
            }

            // Isi koli: 1 chip per item di SO (+ item lama yang sudah pindah ke history, kalau ada).
            function renderChips($k, checkedIds, extras) {
                const set = new Set((checkedIds || []).map(String));
                let h = '';
                state.items.forEach(i => {
                    h += chipHtml(i.id, i.label, set.has(String(i.id)));
                });
                (extras || []).forEach(e => {
                    if (!state.items.some(i => String(i.id) === String(e.staging_out_id))) {
                        h += chipHtml(e.staging_out_id, e.item_label || 'Item lain', true);
                    }
                });
                $k.find('.pb-chips').html(h);
            }

            const checkedItems = $k => $k.find('.pb-koli-item:checked').map(function() {
                return $(this).val();
            }).get();

            function addKoli(checkedIds, extras) {
                const $k = $(sectionHtml('koli'));
                renderChips($k, checkedIds, extras);
                const $shared = $root.find('.pb-shared');
                if ($shared.length) $k.insertBefore($shared);
                else $root.append($k);
                return $k;
            }

            function renumberKoli() {
                koliEls().each(function(i) {
                    $(this).find('.pb-koli-title').text('Koli ' + (i + 1));
                });
            }

            // Item yang belum masuk koli mana pun (hanya peringatan, tidak menghalangi simpan).
            function updateUnassigned() {
                const $box = $('#pbUnassigned');
                if (!isMulti()) {
                    $box.addClass('d-none');
                    return;
                }
                const used = new Set();
                koliEls().each(function() {
                    checkedItems($(this)).forEach(v => used.add(String(v)));
                });
                const missing = state.items.filter(i => !used.has(String(i.id)));
                if (!missing.length) {
                    $box.addClass('d-none');
                    return;
                }
                $box.removeClass('d-none').html('<i class="bx bx-error-circle me-1"></i> Belum masuk koli mana pun: <b>' +
                    missing.map(i => esc(i.label)).join('</b>, <b>') + '</b>');
            }

            function recalc() {
                let total = 0;
                $root.find('.pb-koli').each(function() {
                    let sub = 0;
                    $(this).find('.pb-line').each(function() {
                        const q = parseQty($(this).find('.pb-qty').val());
                        const h = Number($(this).attr('data-harga')) || 0;
                        const s = q > 0 ? q * h : 0;
                        $(this).find('.pb-sub').text(money(s));
                        sub += s;
                    });
                    $(this).find('.pb-koli-subtotal').text('Subtotal: ' + money(sub));
                    $(this).find('.pb-empty').toggleClass('d-none', $(this).find('.pb-line').length > 0);
                    total += sub;
                });
                $('#pbTotal').text(money(total));
            }

            function refreshMode() {
                const multi = isMulti();

                // Kembali ke 1 koli: material bersama digabung ke koli itu.
                if (!multi) {
                    const $rows = $root.find('.pb-shared .pb-line');
                    if ($rows.length && koliEls().length) {
                        $rows.appendTo(koliEls().first().find('.pb-lines'));
                    }
                }

                renumberKoli();
                $root.toggleClass('multi', multi);
                updateUnassigned();
                recalc();
            }

            // ---------- Baris material ----------
            function addLine($sec, line) {
                line = line || {};
                const matId = line.packing_material_id ? String(line.packing_material_id) : '';

                // Material aktif + (kalau perlu) material lama yang sudah nonaktif.
                const mats = state.materials.slice();
                if (matId && !mats.some(m => String(m.id) === matId)) {
                    mats.push({
                        id: line.packing_material_id,
                        nama: (line.nama_material || 'Material') + ' (nonaktif)',
                        satuan: line.satuan,
                        harga: line.harga
                    });
                }
                const matOpts = mats.map(m =>
                    `<option value="${m.id}" ${String(m.id) === matId ? 'selected' : ''}>${esc(m.nama)}</option>`
                ).join('');

                const harga = line.harga != null ? line.harga : 0;
                const $tr = $(`
                    <tr class="pb-line" data-line-id="${line.id || ''}" data-orig-material="${matId}"
                        data-snap-harga="${harga}" data-snap-unit="${esc(line.satuan || '')}" data-harga="${harga}">
                        <td>
                            <select class="form-select form-select-sm pb-material">
                                <option value="">- Pilih material -</option>${matOpts}
                            </select>
                        </td>
                        <td>
                            <div class="input-group pb-qty-group">
                                <button type="button" class="pb-qty-btn pb-minus" tabindex="-1" title="Kurangi" onclick="window.pbStep(this, -1)">&minus;</button>
                                <input type="text" inputmode="decimal" class="form-control form-control-sm pb-qty"
                                    placeholder="0" value="${line.qty ? fmtQty(line.qty) : ''}">
                                <button type="button" class="pb-qty-btn pb-plus" tabindex="-1" title="Tambah" onclick="window.pbStep(this, 1)">+</button>
                            </div>
                        </td>
                        <td class="pb-unit text-muted">${esc(line.satuan || '-')}</td>
                        <td class="text-end pb-price">${line.harga != null ? money(line.harga) : '-'}</td>
                        <td class="text-end fw-semibold pb-sub">Rp 0</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btnRemove pb-del" title="Hapus baris"><i class="bx bx-trash"></i></button></td>
                    </tr>`);
                $sec.find('.pb-lines').append($tr);
                recalc();
                return $tr;
            }

            // ---------- Muat data awal ----------
            $root.append(sectionHtml('shared'));
            const $sharedSec = $root.find('.pb-shared');
            const bill = DATA.bill;

            if (bill && bill.kolis && bill.kolis.length) {
                const multiBill = bill.kolis.length > 1;
                bill.kolis.forEach(k => {
                    const $k = addKoli(multiBill ? (k.items || []).map(i => i.staging_out_id) : [], multiBill ? k.items : []);
                    (k.lines || []).forEach(l => addLine($k, l));
                    $k.attr('data-koli-id', k.id);
                });
                (bill.shared || []).forEach(l => addLine($sharedSec, l));
            } else {
                addLine(addKoli([]));
            }
            refreshMode();
            refreshBillState();

            // ---------- Event ----------
            $('#pbAddKoli').on('click', function() {
                if (!isMulti()) {
                    // Koli 1 yang tadinya otomatis berisi semua item, sekarang dipilih eksplisit.
                    renderChips(koliEls().first(), state.items.map(i => i.id));
                }
                addLine(addKoli([]));
                refreshMode();
            });

            $(document).on('click', '#pbKolis .pb-add-line', function() {
                addLine($(this).closest('.pb-koli')).find('.pb-material').trigger('focus');
            });

            $(document).on('click', '#pbKolis .pb-del', function() {
                $(this).closest('tr').remove();
                recalc();
            });

            $(document).on('change', '#pbKolis .pb-koli-item', updateUnassigned);

            $(document).on('click', '#pbKolis .pb-koli-del', function() {
                const $k = $(this).closest('.pb-koli');
                const remove = function() {
                    $k.remove();
                    refreshMode();
                };
                if ($k.find('.pb-line').length === 0) {
                    remove();
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus ' + $k.find('.pb-koli-title').text() + '?',
                    text: 'Semua material di koli ini akan dihapus.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(function(r) {
                    if (r.isConfirmed) remove();
                });
            });

            // Tombol - / + : naik/turun 1 per klik, tidak boleh di bawah 0.
            // Dipanggil langsung dari atribut onclick tombol, jadi tidak bergantung
            // pada event delegation / script lain di layout.
            window.pbStep = function(btn, step) {
                const $input = $(btn).closest('.pb-qty-group').find('.pb-qty');
                const next = Math.max(0, Math.round((parseQty($input.val()) + step) * 1000) / 1000);
                $input.val(next > 0 ? fmtQty(next) : '');
                recalc();
            };

            $(document).on('input', '#pbKolis .pb-qty', recalc);

            $(document).on('change', '#pbKolis .pb-material', function() {
                const $tr = $(this).closest('tr');
                const id = String($(this).val() || '');

                if (id && id === $tr.attr('data-orig-material')) {
                    // Material tidak diganti: tetap pakai harga yang sudah tersimpan.
                    $tr.attr('data-harga', $tr.attr('data-snap-harga'));
                    $tr.find('.pb-unit').text($tr.attr('data-snap-unit') || '-');
                } else {
                    const m = state.materials.find(x => String(x.id) === id);
                    $tr.attr('data-harga', m ? m.harga : 0);
                    $tr.find('.pb-unit').text(m ? m.satuan : '-');
                }
                $tr.find('.pb-price').text(id ? money($tr.attr('data-harga')) : '-');
                recalc();
            });

            $(document).on('click', '.pb-print-trigger', function() {
                if (state.billId) window.open(URL_PRINT.replace(':id', state.billId), '_blank');
            });

            // Setelah tersimpan: id koli/baris & harga tersimpan diperbarui di halaman,
            // jadi tidak perlu memuat ulang dan edit berikutnya tetap benar.
            function applySaved(res) {
                const applyLines = ($sec, saved) => {
                    $sec.find('.pb-line').each(function(i) {
                        const l = (saved || [])[i];
                        if (!l) return;
                        $(this).attr({
                            'data-line-id': l.id,
                            'data-orig-material': l.packing_material_id || '',
                            'data-snap-harga': l.harga,
                            'data-snap-unit': l.satuan,
                            'data-harga': l.harga
                        });
                        $(this).find('.pb-unit').text(l.satuan || '-');
                        $(this).find('.pb-price').text(money(l.harga));
                    });
                };

                koliEls().each(function(i) {
                    const k = (res.kolis || [])[i];
                    if (!k) return;
                    $(this).attr('data-koli-id', k.id);
                    applyLines($(this), k.lines);
                });
                applyLines($sharedSec, res.shared);
                recalc();
            }

            // Baris yang benar-benar kosong (tanpa material & qty) dibuang diam-diam;
            // koli yang jadi kosong ikut dibuang (selama koli lebih dari 1).
            function pruneBlank() {
                $root.find('.pb-line').each(function() {
                    if (!$(this).find('.pb-material').val() && !(parseQty($(this).find('.pb-qty').val()) > 0)) {
                        $(this).remove();
                    }
                });
                koliEls().each(function() {
                    if (isMulti() && $(this).find('.pb-line').length === 0) $(this).remove();
                });
                refreshMode();
            }

            $('#pbSave').on('click', function() {
                pruneBlank();

                const multi = isMulti();
                const kolis = [];
                const shared = [];
                let problem = '';

                const collect = function($sec, where) {
                    const out = [];
                    $sec.find('.pb-line').each(function(i) {
                        const $tr = $(this);
                        const label = where + 'baris ' + (i + 1);
                        const mat = $tr.find('.pb-material').val();
                        const qty = parseQty($tr.find('.pb-qty').val());
                        if (!problem) {
                            if (!mat) problem = label + ': pilih material.';
                            else if (!(qty > 0)) problem = label + ': qty harus lebih dari 0.';
                        }
                        out.push({
                            id: $tr.attr('data-line-id') || null,
                            material_id: mat,
                            qty: qty
                        });
                    });
                    return out;
                };

                koliEls().each(function(ki) {
                    const items = multi ? checkedItems($(this)) : [];
                    if (multi && !items.length && !problem) {
                        problem = 'Koli ' + (ki + 1) + ': pilih isi koli (minimal 1 item).';
                    }
                    kolis.push({
                        id: $(this).attr('data-koli-id') || null,
                        items: items,
                        lines: collect($(this), multi ? 'Koli ' + (ki + 1) + ' ' : '')
                    });
                });

                if (multi) {
                    collect($sharedSec, 'Material bersama ').forEach(l => shared.push(l));
                }

                if (problem) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        text: problem
                    });
                    return;
                }

                const totalLines = shared.length + kolis.reduce((n, k) => n + k.lines.length, 0);

                // Tidak ada material sama sekali dan belum pernah ada bill:
                // tidak ada yang perlu disimpan.
                if (!totalLines && !state.billId) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak ada material',
                        text: 'Belum ada material yang diisi, jadi bill tidak dibuat.',
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    return;
                }

                const $btn = $(this).prop('disabled', true);

                $.ajax({
                    url: URL_SAVE,
                    type: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify({
                        so_number: state.so,
                        kolis: kolis,
                        shared: shared
                    })
                }).done(function(res) {
                    state.billId = res.bill_id || null;
                    applySaved(res);
                    refreshBillState();

                    Swal.fire({
                        icon: res.deleted ? 'info' : 'success',
                        title: res.message,
                        text: 'Total: ' + money(res.total),
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }).fail(function(xhr) {
                    let msg = 'Terjadi kesalahan pada server. Coba lagi beberapa saat.';
                    if (xhr.status === 0) msg = 'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.';
                    else if (xhr.status === 419) msg = 'Sesi sudah habis. Muat ulang halaman lalu coba lagi.';
                    else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan bill',
                        text: msg
                    });
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });
        });
    </script>
@endpush