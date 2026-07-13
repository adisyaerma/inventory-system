@extends('master')
@section('title', 'Stok Mutation')
@section('content')
    <div class="card rounded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Mutasi</h4>
                <small class="text-muted">Kelola mutasi stok data barang di gudang</small>
            </div>
            <div class="float-end">
                <button data-bs-toggle="modal" data-bs-target="#addMutationModal" type="button" class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>
                <div class="modal fade" id="addMutationModal" tabindex="-1" aria-hidden="true">

                    <div class="modal-dialog modal-xl modal-dialog-scrollable">

                        <form action="{{ route('stock-mutation.store') }}" method="POST" id="formMutation">

                            @csrf

                            <div class="modal-content border-0 shadow">

                                <div class="modal-header border-0 pb-0">

                                    <div>

                                        <h4 class="mb-1 fw-bold">
                                            Tambah Mutasi Stok
                                        </h4>

                                        <small class="text-muted">
                                            Tambahkan mutasi barang beserta lokasi penyimpanannya.
                                        </small>

                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body">

                                    <div class="row g-3">

                                        {{-- ================= LEFT =================== --}}

                                        <div class="col-lg-6">

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

                                                            <select id="stockSelect" name="stock_id" required></select>
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
                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">

                                                            Tipe Transaksi

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-arrow-left-right"></i>

                                                            </span>

                                                            <select class="form-select" name="transaction_type" required>

                                                                <option value="">
                                                                    Pilih
                                                                </option>

                                                                <option>Opening Balance</option>
                                                                <option>Delivery Order</option>
                                                                <option>Receive Item</option>
                                                                <option>Item Transfer</option>

                                                            </select>

                                                        </div>

                                                    </div>

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
                                                                placeholder="Contoh: 202606-DO-0777">

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        </div>

                                        {{-- ================= RIGHT =================== --}}

                                        <div class="col-lg-6">

                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                                    <h5 class="fw-bold mb-0">
                                                        <i class="bi bi-pencil-square me-2 text-success"></i>
                                                        Detail Mutasi
                                                    </h5>

                                                </div>

                                                <div class="card-body">

                                                    <div class="row">

                                                        <div class="col-md-4">

                                                            <label class="form-label fw-semibold">

                                                                Jenis

                                                            </label>

                                                            <div class="input-group">

                                                                <span class="input-group-text">

                                                                    <i class="bi bi-arrow-down-up"></i>

                                                                </span>

                                                                <select class="form-select" id="qtyType">

                                                                    <option value="in">
                                                                        Masuk
                                                                    </option>

                                                                    <option value="out">
                                                                        Keluar
                                                                    </option>

                                                                </select>

                                                            </div>

                                                        </div>

                                                        <div class="col-md-8">

                                                            <label class="form-label fw-semibold">

                                                                Qty

                                                            </label>

                                                            <div class="input-group">

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

                                                            Warehouse

                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-text">

                                                                <i class="bi bi-building"></i>

                                                            </span>

                                                            <input type="text" class="form-control form-control-sm"
                                                                name="warehouse" placeholder="WH3 ARISTIDES">

                                                        </div>

                                                    </div>

                                                    <div class="mt-3">

                                                        <label class="form-label fw-semibold">

                                                            Reference

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

                                        </div>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button class="btn btn-light btn-outline-secondary" data-bs-dismiss="modal"
                                        type="button">

                                        Batal

                                    </button>

                                    <button class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i>
                                        Simpan Mutasi

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#importMutationModal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="me-1" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 13v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5" />
                    </svg>
                    Import
                </button>
                <a href="{{ route('mutation.export') }}" class="btn btn-sm btn-success" id="exportBtn">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor"
                            d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                    </svg>

                    Export
                </a>
            </div>

        </div>

        <div class="modal fade" id="importMutationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('stock-mutation.import') }}" method="POST" enctype="multipart/form-data">
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
                                        Impor Data Mutasi Stok
                                    </h5>

                                    <small class="text-muted">
                                        Import data mutasi stok dari file Excel
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

        <div class="table-responsive text-nowrap ">
            <div class="container-fluid px-5">
                <div class="top-row d-flex justify-content-lg-start justify-content-center mb-3">

                    <div class="row g-2 w-100 w-lg-auto">

                        <div class="col-12 col-sm-6 col-lg-auto">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="filterStartDate">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-auto">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="filterEndDate">
                        </div>

                        <div class="col-12 col-md-6 col-lg-auto">
                            <label class="form-label">Jenis Transaksi</label>
                            <select class="form-select form-select-sm" id="filterTransaction">
                                <option value="">Semua</option>

                                @foreach ($transactionTypes as $type)
                                    <option value="{{ $type }}">
                                        {{ $type }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-auto">
                            <label class="form-label">Lokasi</label> <br>
                            <select class="form-select form-select-sm" id="filterLocation">
                                <option value="">Semua</option>

                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" data-name="{{ $location->location_name }}">
                                        {{ $location->location_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-auto d-flex align-items-end">
                            <button
                                class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-1"
                                id="resetFilter">

                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                    viewBox="0 0 21 21">
                                    <path d="M0 0h21v21H0z" fill="none" />
                                    <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                                        <path d="M7.5 6.5h-4v-4" />
                                    </g>
                                </svg>

                                <span>Reset Filter</span>

                            </button>
                        </div>

                    </div>

                </div>
            </div>
            <table class="table table-bordered" id="stockMutation">

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Tanggal</th>

                        <th>Kode Barang</th>

                        <th>Nama Barang</th>

                        <th>Lokasi</th>

                        <th>Tipe Transaksi</th>

                        <th>No. Transaksi</th>

                        <th>Qty Masuk</th>

                        <th>Qty Keluar</th>

                        <th>Qty Akhir</th>

                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody></tbody>

            </table>
        </div>
    </div>

    <div class="modal fade" id="editMutationModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <form id="formEditMutation">

                @csrf
                @method('PUT')

                <div class="modal-content border-0 shadow">

                    <div class="modal-header border-0 pb-0">

                        <div>

                            <h4 class="mb-1 fw-bold">
                                Ubah Mutasi Stok
                            </h4>

                            <small class="text-muted">
                                Ubah mutasi barang beserta lokasi penyimpanannya.
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <div class="row g-3">

                            {{-- ================= LEFT =================== --}}

                            <div class="col-lg-6">

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

                                                <select id="editStock" name="stock_id" required></select>
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
                                        <div class="mb-3">

                                            <label class="form-label fw-semibold">

                                                Tipe Transaksi

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-arrow-left-right"></i>

                                                </span>

                                                <select class="form-select" id="editTransactionType"
                                                    name="transaction_type" required>

                                                    <option value="">
                                                        Pilih
                                                    </option>

                                                    <option>Opening Balance</option>
                                                    <option>Delivery Order</option>
                                                    <option>Receive Item</option>
                                                    <option>Item Transfer</option>

                                                </select>

                                            </div>

                                        </div>

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
                                                    placeholder="202606-DO-0777">

                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>

                            {{-- ================= RIGHT =================== --}}

                            <div class="col-lg-6">

                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">

                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-pencil-square me-2 text-success"></i>
                                            Detail Mutasi
                                        </h5>

                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-4">

                                                <label class="form-label fw-semibold">

                                                    Jenis

                                                </label>

                                                <div class="input-group">

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

                                            <div class="col-md-8">

                                                <label class="form-label fw-semibold">

                                                    Qty

                                                </label>

                                                <div class="input-group">

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

                                                Warehouse

                                            </label>

                                            <div class="input-group">

                                                <span class="input-group-text">

                                                    <i class="bi bi-building"></i>

                                                </span>

                                                <input type="text" id="editWarehouse"
                                                    class="form-control form-control-sm" name="warehouse"
                                                    placeholder="WH3 ARISTIDES">

                                            </div>

                                        </div>

                                        <div class="mt-3">

                                            <label class="form-label fw-semibold">

                                                Reference

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

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-light btn-outline-secondary" data-bs-dismiss="modal" type="button">

                            Batal

                        </button>

                        <button class="btn btn-primary btnSaveEdit">
                            <i class="bx bx-save me-1"></i>
                            Simpan Perubahan

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    @push('script')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $('#filterLocation').select2({
                width: '100%'
            });

            function updateExportUrl() {

                let start = $('#filterStartDate').val();
                let end = $('#filterEndDate').val();
                let transaction = $('#filterTransaction').val();
                let location = $('#filterLocation').val();

                let stock = new URLSearchParams(window.location.search).get('stock');

                let url = new URL("{{ route('mutation.export') }}");

                if (stock) url.searchParams.append('stock', stock);
                if (start) url.searchParams.append('start_date', start);
                if (end) url.searchParams.append('end_date', end);
                if (transaction) url.searchParams.append('transaction_type', transaction);
                if (location) url.searchParams.append('location_id', location);

                $('#exportBtn').attr('href', url.toString());
            }
            $('#filterStartDate').on('change', updateExportUrl);
            $('#filterEndDate').on('change', updateExportUrl);
            $('#filterTransaction').on('change', updateExportUrl);
            $('#filterLocation').on('change', updateExportUrl);

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
            $(document).on('click', '.btnEdit', function() {

                let id = $(this).data('id');

                $.ajax({

                    url: '/stock-mutation/' + id + '/edit',

                    type: 'GET',

                    success: function(res) {
                        console.log(res.transaction_date);
                        $('#edit_id').val(res.id);

                        $('#editTransactionDate').val(res.transaction_date);

                        $('#editLocation').val(res.location).trigger('change');

                        $('#editTransactionType').val(res.transaction_type);

                        $('#editTransactionNumber').val(res.transaction_number);

                        $('#editWarehouse').val(res.warehouse);

                        $('#editReference').val(res.reference);

                        $('#editValue').val(res.value);
                        $('#editValueDisplay').val(
                            new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(res.value)
                        );

                        $('#editDescription').val(res.description);

                        $('#editQtyType').val(res.qty_type);

                        $('#editQty').val(parseInt(res.qty));

                        $('#editQtyIn').val(res.qty_in);

                        $('#editQtyOut').val(res.qty_out);

                        let option = new Option(
                            res.stock_name,
                            res.stock_id,
                            true,
                            true
                        );

                        $('#editStock')
                            .empty()
                            .append(option)
                            .trigger('change');

                    }

                });

            });

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

                        stock_id: $('#editStock').val(),

                        location: $('#editLocation').val(),

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
                            timerProgressBar: true,
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
            $('#value').on('input', function() {

                let angka = $(this).val().replace(/[^0-9]/g, '');

                $(this).val(
                    new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(angka || 0)
                );

            });
            $('#formMutation').submit(function() {

                let value = $('#value').val().replace(/[^0-9]/g, '');

                $('#value').val(value);

            });
        </script>
        <script>
            let table;
            $(document).ready(function() {

                table = $('#stockMutation').DataTable({

                    processing: true,
                    serverSide: true,

                    ajax: {
                        url: "{{ route('stock-mutation.data') }}",
                        data: function(d) {
                            d.start_date = $('#filterStartDate').val();
                            d.end_date = $('#filterEndDate').val();
                            d.transaction_type = $('#filterTransaction').val();
                            d.location_id = $('#filterLocation').val();
                        }
                    },

                    columns: [

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
                            data: 'item_code',
                            name: 'stock.item_code_internal'
                        },

                        {
                            data: 'stock_name',
                            name: 'stock.name'
                        },

                        {
                            data: 'location_name',
                            name: 'location.location_name'
                        },

                        {
                            data: 'transaction_type',
                            name: 'transaction_type'
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
                            data: 'action',
                            name: 'action',
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
                    }]

                });

                $('#filterStartDate,#filterEndDate,#filterTransaction,#filterLocation')
                    .on('change', function() {
                        table.ajax.reload();
                    });

                $('#resetFilter').click(function() {

                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    $('#filterTransaction').val('');
                    $('#filterLocation').val('').trigger('change');

                    table.ajax.reload();

                });

            });

            $('#formMutation').submit(function(e) {

                e.preventDefault();

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
                                document.querySelector('.swal2-container').style.zIndex =
                                    '9999999';
                            }
                        });

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON.message
                        });

                    }

                });

            });
        </script>

        <style>
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
            let stockSelect;
            let locationSelect;

            stockSelect = new TomSelect("#stockSelect", {

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

            function loadCurrentStock() {

                let stock = stockSelect.getValue();

                let location = locationSelect.getValue();

                if (stock == "" || location == "") {

                    $("#currentStock").html("0");

                    return;

                }

                $.get(

                    "{{ route('stock-mutation.current-stock') }}",

                    {

                        stock_id: stock,

                        location: location

                    },

                    function(res) {

                        $("#currentStock").html(parseInt(res.qty));

                        validateQty();

                    }

                );

            }

            stockSelect.on("change", function() {

                let stock = stockSelect.getValue();

                if (!stock) {
                    return;
                }

                $.get(
                    "{{ route('stock-mutation.default-location') }}",

                    {
                        stock_id: stock
                    },
                    function(res) {
                        if (res) {

                            if (!locationSelect.options[res.id]) {
                                locationSelect.addOption(res);
                            }

                            locationSelect.setValue(res.id, true)
                        }

                        loadCurrentStock();
                    }
                )

            });

            locationSelect.on("change", function() {

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

            });

            $("#addMutationModal").on("hidden.bs.modal", function() {

                this.querySelector("form").reset();

                stockSelect.clear();

                locationSelect.clear();

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
            let editStockSelect;
            let editLocationSelect;

            editStockSelect = new TomSelect("#editStock", {
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

            editStockSelect.on("dropdown_open", function() {
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

            $(document).on('click', '.btnEdit', function() {

                let id = $(this).data('id');

                $.ajax({

                    url: '/stock-mutation/' + id + '/edit',
                    type: 'GET',

                    success: function(res) {

                        $('#edit_id').val(res.id);
                        $('#editTransactionDate').val(res.transaction_date);

                        editStockSelect.clear(true);

                        // Jangan clearOptions()

                        editStockSelect.addOption({
                            id: res.stock_id,
                            text: res.stock_name
                        });

                        editStockSelect.setValue(res.stock_id, true);

                        // LOCATION
                        if (!editLocationSelect.options[res.location]) {
                            editLocationSelect.sync();
                            editLocationSelect.setValue(res.location);
                        }

                        editLocationSelect.setValue(res.location, true);

                        $('#editQtyType').val(res.qty_in > 0 ? 'in' : 'out');

                        $('#editQtyInput').val(
                            Number(res.qty_in > 0 ? res.qty_in : res.qty_out)
                        );

                        $('#editTransactionNumber').val(res.transaction_number);
                        $('#editRemark').val(res.remark);
                        $('#editValue').val(res.value);

                        $('#editMutationModal').modal('show');

                        loadCurrentStockEdit();
                    }

                });

            });

            function loadCurrentStockEdit() {

                let stock = editStockSelect.getValue();
                let location = editLocationSelect.getValue();

                if (stock == "" || location == "") {

                    $("#editCurrentStock").html("0");
                    return;

                }

                $.get(
                    "{{ route('stock-mutation.current-stock') }}", {
                        stock_id: stock,
                        location: location
                    },
                    function(res) {

                        $("#editCurrentStock").html(parseInt(res.qty));

                        validateQtyEdit();

                    }
                );

            }

            editStockSelect.on("change", function() {
                loadCurrentStockEdit();
            });

            editLocationSelect.on("change", function() {
                loadCurrentStockEdit();
            });

            $("#editQtyType").on("change", function() {
                validateQtyEdit();
            });

            $("#editQtyInput").on("keyup change", function() {
                validateQtyEdit();
            });

            function validateQtyEdit() {

                let current = Number($("#editCurrentStock").text());
                let input = Number($("#editQtyInput").val());

                if (isNaN(current)) current = 0;
                if (isNaN(input)) input = 0;

                if ($("#editQtyType").val() == "out") {

                    if (input > current) {
                        $("#editQtyInput").addClass("is-invalid");
                    } else {
                        $("#editQtyInput").removeClass("is-invalid");
                    }

                } else {

                    $("#editQtyInput").removeClass("is-invalid");

                }

            }

            $("#editMutationModal").on("hidden.bs.modal", function() {

                this.querySelector("form").reset();

                editStockSelect.clear();
                editStockSelect.clearOptions();

                editLocationSelect.clear();

                $("#editCurrentStock").html("0");

                $("#editQtyInput").removeClass("is-invalid");

            });
        </script>
    @endpush
@endsection
