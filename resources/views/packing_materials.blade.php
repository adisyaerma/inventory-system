@extends('master')
@section('title', 'Master Material Packing')
@section('content')
    {{-- ================= TEMA MODAL (selaras halaman Barang & Staging Out) ================= --}}
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
                <h4 class="mb-0">Master Material Packing</h4>
                <small class="text-muted">Kelola daftar material dan harga yang dipakai di Bill Packing</small>
            </div>
            <div class="float-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" id="btnAddMaterial" class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                        <span class="d-none d-md-inline ms-1">Tambah</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="">
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

                    <div class="filter-toolbar material-filter">

                        <div class="filter-group filter-group-search">
                            <label class="filter-label" for="customSearch">Cari</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="customSearch" class="form-control border-start-0"
                                    placeholder="Cari kode, nama, atau satuan...">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="filterStatus">Status</label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-toggle-on text-muted"></i>
                                </span>
                                <select id="filterStatus" class="form-select border-start-0">
                                    <option value="">Semua Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <table class="table table-bordered" id="material">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Material</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materials as $m)
                            <tr>
                                <td></td>
                                <td class="fw-semibold">{{ $m->kode }}</td>
                                <td>{{ $m->nama }}</td>
                                <td>{{ $m->satuan }}</td>
                                <td class="text-end" data-order="{{ (float) $m->harga }}">
                                    Rp {{ number_format($m->harga, 0, ',', '.') }}
                                </td>
                                <td class="text-center" data-search="{{ $m->aktif ? 'aktif' : 'nonaktif' }}">
                                    @if ($m->aktif)
                                        <span class="badge bg-success-subtle text-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button"
                                            class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEditMaterial"
                                            data-id="{{ $m->id }}" data-kode="{{ $m->kode }}"
                                            data-nama="{{ $m->nama }}" data-satuan="{{ $m->satuan }}"
                                            data-harga="{{ (float) $m->harga }}" data-aktif="{{ $m->aktif ? 1 : 0 }}"
                                            title="Ubah">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5"
                                                viewBox="0 0 24 24">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path fill="currentColor"
                                                    d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0 btnDeleteMaterial"
                                            data-id="{{ $m->id }}" data-nama="{{ $m->nama }}" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5"
                                                viewBox="0 0 24 24">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path fill="currentColor"
                                                    d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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

    {{-- ================= MODAL TAMBAH / UBAH MATERIAL ================= --}}
    <div class="modal fade modal-aesthetic" id="materialModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="materialForm" onsubmit="return false;">
                    <input type="hidden" id="mId">

                    <div class="modal-header as-hero">
                        <div class="d-flex align-items-center gap-3">
                            <div class="as-hero-icon"><i class="bx bx-package" id="materialHeroIcon"></i></div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="materialModalTitle">Tambah Material</h4>
                                <small id="materialModalSub">Lengkapi informasi material packing.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body as-body">
                        <div class="as-card">
                            <div class="as-card-title">
                                <div class="as-card-icon"><i class="bx bx-box"></i></div>
                                <div>
                                    <strong>Informasi Material</strong>
                                    <small>Isi kode, nama, satuan, dan harga per satuan.</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="mKode"
                                        placeholder="Contoh: MAT-001">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Material</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-package"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="mNama"
                                        placeholder="Contoh: Carton Box">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-ruler"></i></span>
                                        <input type="text" class="form-control form-control-sm" id="mSatuan"
                                            placeholder="pcs / meter / roll">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Harga per Satuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text fw-semibold" style="font-size:.8rem">Rp</span>
                                        <input type="number" min="0" step="any"
                                            class="form-control form-control-sm" id="mHarga" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="mAktif" checked>
                                <label class="form-check-label" for="mAktif">Aktif (bisa dipilih di bill)</label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer as-footer">
                        <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="as-btn-save" id="btnSaveMaterial">
                            <i class="bx bx-save me-1"></i>
                            <span id="btnSaveLabel">Simpan Material</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
        /* Filter ringkas: kolom tidak melebar, ikon & select selalu sebaris dan sama tinggi */
        .filter-toolbar.material-filter {
            grid-template-columns: minmax(200px, 280px) minmax(170px, 220px);
            justify-content: start;
        }

        .material-filter .input-group {
            flex-wrap: nowrap;
        }

        .material-filter .input-group-text {
            justify-content: center;
            min-width: 2.4rem;
        }

        @media (max-width: 575.98px) {
            .filter-toolbar.material-filter {
                grid-template-columns: 1fr;
            }
        }

        #material {
            min-width: 900px;
        }

        #material th,
        #material td {
            padding: 12px 16px;
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
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
            const URL_STORE = @json(route('packing-materials.store'));
            const URL_ITEM = @json(route('packing-materials.update', ':id'));
            const CSRF = @json(csrf_token());
            const modal = new bootstrap.Modal(document.getElementById('materialModal'));

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            // ---------------- DataTable (cari, filter status, jumlah data per halaman) ----------------
            const table = $('#material').DataTable({
                dom: 'rtip',
                scrollX: true,
                autoWidth: false,
                order: [],
                columnDefs: [{
                    targets: [0, 6],
                    orderable: false,
                    searchable: false
                }],
                language: {
                    emptyTable: 'Belum ada material. Klik Tambah untuk mulai.',
                    zeroRecords: 'Tidak ada material yang cocok.'
                }
            });

            // Nomor urut mengikuti hasil pencarian/halaman yang sedang tampil.
            table.on('draw.dt', function() {
                const start = table.page.info().start;
                table.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = start + i + 1;
                });
            }).draw(false);

            $('#customSearch').on('input', function() {
                table.search(this.value).draw();
            });

            $('#customLength').on('change', function() {
                table.page.len($(this).val()).draw();
            });

            $('#filterStatus').on('change', function() {
                const v = $(this).val();
                table.column(5).search(v ? '^' + v + '$' : '', true, false).draw();
            });

            $('#btnResetFilter').on('click', function() {
                $('#customSearch').val('');
                $('#filterStatus').val('');
                table.search('').column(5).search('').draw();
            });

            // ---------------- Alert ----------------
            // Pesan error yang jelas untuk semua jenis kegagalan (validasi, sesi habis, server, koneksi).
            function errorMessages(xhr) {
                if (xhr.status === 0) return ['Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.'];
                if (xhr.status === 419) return ['Sesi sudah habis. Muat ulang halaman lalu coba lagi.'];
                if (xhr.status === 403) return ['Kamu tidak punya akses untuk aksi ini.'];
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    return Object.values(xhr.responseJSON.errors).flat();
                }
                if (xhr.responseJSON && xhr.responseJSON.message) return [xhr.responseJSON.message];
                return ['Terjadi kesalahan pada server. Coba lagi beberapa saat.'];
            }

            function alertError(title, xhr) {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    html: errorMessages(xhr).map(esc).join('<br>')
                });
            }

            // Alert sukses tampil sebentar, baru halaman dimuat ulang supaya tabel terbaru terlihat.
            function alertSuccess(message, icon, title) {
                return Swal.fire({
                    icon: icon || 'success',
                    title: title || 'Berhasil',
                    text: message,
                    timer: 1800,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });
            }

            // ---------------- Form tambah / ubah ----------------
            function openForm(d) {
                $('#materialModalTitle').text(d ? 'Ubah Material' : 'Tambah Material');
                $('#materialModalSub').text(d ? 'Ubah informasi material packing.' :
                    'Lengkapi informasi material packing.');
                $('#materialHeroIcon').attr('class', d ? 'bx bx-edit' : 'bx bx-package');
                $('#btnSaveLabel').text(d ? 'Simpan Perubahan' : 'Simpan Material');
                $('#mId').val(d ? d.id : '');
                $('#mKode').val(d ? d.kode : '');
                $('#mNama').val(d ? d.nama : '');
                $('#mSatuan').val(d ? d.satuan : '');
                $('#mHarga').val(d ? d.harga : '');
                $('#mAktif').prop('checked', d ? String(d.aktif) === '1' : true);
                modal.show();
            }

            $('#btnAddMaterial').on('click', () => openForm(null));

            $(document).on('click', '.btnEditMaterial', function() {
                openForm({
                    id: $(this).attr('data-id'),
                    kode: $(this).attr('data-kode'),
                    nama: $(this).attr('data-nama'),
                    satuan: $(this).attr('data-satuan'),
                    harga: $(this).attr('data-harga'),
                    aktif: $(this).attr('data-aktif')
                });
            });

            $('#materialForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#mId').val();

                // Cek isian dulu supaya user langsung tahu apa yang kurang.
                let problem = '';
                if (!$('#mKode').val().trim()) problem = 'Kode wajib diisi.';
                else if (!$('#mNama').val().trim()) problem = 'Nama material wajib diisi.';
                else if (!$('#mSatuan').val().trim()) problem = 'Satuan wajib diisi.';
                else if ($('#mHarga').val() === '' || Number($('#mHarga').val()) < 0) problem =
                    'Harga wajib diisi dan tidak boleh minus.';

                if (problem) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        text: problem
                    });
                    return;
                }

                const $btn = $('#btnSaveMaterial').prop('disabled', true);

                $.ajax({
                    url: id ? URL_ITEM.replace(':id', id) : URL_STORE,
                    type: id ? 'PUT' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    data: {
                        kode: $('#mKode').val().trim(),
                        nama: $('#mNama').val().trim(),
                        satuan: $('#mSatuan').val().trim(),
                        harga: $('#mHarga').val(),
                        aktif: $('#mAktif').is(':checked') ? 1 : 0
                    }
                }).done(function(res) {
                    modal.hide();
                    alertSuccess(res.message);
                }).fail(function(xhr) {
                    alertError(id ? 'Gagal memperbarui material' : 'Gagal menambah material', xhr);
                }).always(function() {
                    $btn.prop('disabled', false);
                });
            });

            // ---------------- Hapus ----------------
            $(document).on('click', '.btnDeleteMaterial', function() {
                const id = $(this).attr('data-id');
                const nama = $(this).attr('data-nama');

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus material?',
                    html: 'Material <b>' + esc(nama) + '</b> akan dihapus.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    // Penghapusan dijalankan di dalam dialog ini; kalau gagal, pesannya muncul
                    // di dialog yang sama dan user bisa mencoba lagi atau batal.
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                url: URL_ITEM.replace(':id', id),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': CSRF,
                                    'Accept': 'application/json'
                                }
                            }).done(resolve).fail(function(xhr) {
                                Swal.showValidationMessage(errorMessages(xhr).join(' '));
                                resolve(false);
                            });
                        });
                    }
                }).then(function(r) {
                    if (!r.isConfirmed || !r.value) return;

                    if (r.value.deactivated) {
                        alertSuccess(r.value.message, 'info', 'Dinonaktifkan');
                    } else {
                        alertSuccess(r.value.message, 'success', 'Terhapus');
                    }
                });
            });
        });
    </script>
@endpush