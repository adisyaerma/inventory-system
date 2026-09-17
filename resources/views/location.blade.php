@extends('master')
@section('title', 'Lokasi')
@section('content')
    <div class="card">
        <div class="card-body">

            {{-- ================= TAB KATEGORI LOKASI ================= --}}
            <ul class="nav nav-tabs mb-4" id="locationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-semua" data-bs-toggle="tab"
                        data-bs-target="#pane-semua" type="button" role="tab">
                        <i class="bi bi-grid-fill me-1"></i> Semua Lokasi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rak" data-bs-toggle="tab" data-bs-target="#pane-rak"
                        type="button" role="tab">
                        <i class="bi bi-diagram-3 me-1"></i> Rak
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-flat" data-bs-toggle="tab" data-bs-target="#pane-flat"
                        type="button" role="tab">
                        <i class="bi bi-archive me-1"></i> Flat Indoor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-hroom" data-bs-toggle="tab" data-bs-target="#pane-hroom"
                        type="button" role="tab">
                        <i class="bi bi-box-seam me-1"></i> H-Room
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-outdoor" data-bs-toggle="tab" data-bs-target="#pane-outdoor"
                        type="button" role="tab">
                        <i class="bi bi-sun me-1"></i> Outdoor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-backside" data-bs-toggle="tab" data-bs-target="#pane-backside"
                        type="button" role="tab">
                        <i class="bi bi-signpost-2 me-1"></i> Backside
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lainlain" data-bs-toggle="tab" data-bs-target="#pane-lainlain"
                        type="button" role="tab">
                        <i class="bi bi-three-dots me-1"></i> Lain-lain
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="locationTabsContent">

                {{-- ============ TAB: SEMUA LOKASI (ringkasan + preview per kategori) ============ --}}
                <div class="tab-pane fade show active" id="pane-semua" role="tabpanel">

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap" style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="summarySearch" class="form-control border-start-0"
                                placeholder="Cari lokasi...">
                        </div>

                        <select id="summaryStatus" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>

                        <button type="button" id="btnResetSummary" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>


                        
                    </div>

                    <h6 class="fw-bold mb-3">Ringkasan Lokasi</h6>
                    <div class="row g-3 mb-4" id="summaryCards">
                        {{-- diisi oleh JS --}}
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Daftar Lokasi per Kategori</h6>
                    </div>

                    <div id="categoryPreview">
                        {{-- diisi oleh JS: 3 blok (Rak, Flat Indoor, H-Room Storage) --}}
                    </div>

                </div>

                {{-- ============ TAB: RAK ============ --}}
                <div class="tab-pane fade" id="pane-rak" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-diagram-3 me-1 text-success"></i> Lokasi Rak</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetRak" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchRak" class="form-control border-start-0"
                                placeholder="Cari lokasi rak...">
                        </div>
                        <select id="statusRak" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarRak"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="rak">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationRak">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="rak"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerRak">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperRak">
                            <span>Tampilkan</span>
                            <select id="lengthRak" class="form-select form-select-sm" style="width:80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB: FLAT INDOOR ============ --}}
                <div class="tab-pane fade" id="pane-flat" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-archive me-1 text-primary"></i> Lokasi Flat Indoor</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetFlat" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchFlat" class="form-control border-start-0"
                                placeholder="Cari lokasi flat indoor...">
                        </div>
                        <select id="statusFlat" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarFlat"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="flat_indoor">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationFlat">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="flat_indoor"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerFlat">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperFlat">
                            <span>Tampilkan</span>
                            <select id="lengthFlat" class="form-select form-select-sm" style="width:80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB: H-ROOM STORAGE ============ --}}
                <div class="tab-pane fade" id="pane-hroom" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-1" style="color:#8b5cf6;"></i> Lokasi H-Room
                            Storage</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetHroom" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchHroom" class="form-control border-start-0"
                                placeholder="Cari lokasi h-room storage...">
                        </div>
                        <select id="statusHroom" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarHroom"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="h_room">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationHroom">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="h_room"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerHroom">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperHroom">
                            <span>Tampilkan</span>
                            <select id="lengthHroom" class="form-select form-select-sm" style="width:80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB: OUTDOOR ============ --}}
                <div class="tab-pane fade" id="pane-outdoor" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-sun me-1 text-warning"></i> Lokasi Outdoor</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetOutdoor" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchOutdoor" class="form-control border-start-0"
                                placeholder="Cari lokasi outdoor...">
                        </div>
                        <select id="statusOutdoor" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarOutdoor"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="outdoor">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationOutdoor">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="outdoor"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerOutdoor">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperOutdoor">
                            <span>Tampilkan</span>
                            <select id="lengthOutdoor" class="form-select form-select-sm" style="width:80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB: BACKSIDE ============ --}}
                <div class="tab-pane fade" id="pane-backside" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-signpost-2 me-1 text-dark"></i> Lokasi Backside</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetBackside" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchBackside" class="form-control border-start-0"
                                placeholder="Cari lokasi backside...">
                        </div>
                        <select id="statusBackside" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarBackside"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="backside">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationBackside">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="backside"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerBackside">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperBackside">
                            <span>Tampilkan</span>
                            <select id="lengthBackside" class="form-select form-select-sm" style="width:80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB: LAIN-LAIN (lokasi yang tidak cocok kategori manapun) ============ --}}
                <div class="tab-pane fade" id="pane-lainlain" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-three-dots me-1 text-secondary"></i> Lokasi Lain-lain</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="resetLainlain" type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button"
                                class="btn btn-primary btn-sm btnOpenAdd">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <div class="input-group input-group-sm shadow-sm flex-nowrap"  style="max-width:260px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchLainlain" class="form-control border-start-0"
                                placeholder="Cari lokasi lain-lain...">
                        </div>
                        <select id="statusLainlain" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                    </div>

                    <div id="bulkActionBarLainlain"
                        class="alert alert-secondary d-none d-flex justify-content-between align-items-center mb-3">
                        <span><span class="selectedCount">0</span> data dipilih</span>
                        <button type="button" class="btn btn-sm btn-danger btnBulkDelete" data-category="lain_lain">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>

                    <table class="table table-bordered" id="locationLainlain">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkAll" data-category="lain_lain"></th>
                                <th>No</th>
                                <th>Nama Lokasi</th>
                                <th>Kode Lokasi</th>
                                <th>Status</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                    <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2"
                        id="footerLainlain">
                        <div class="d-flex align-items-center gap-2" id="lengthWrapperLainlain">
                            <span>Tampilkan</span>
                            <select id="lengthLainlain" class="form-select form-select-sm" style="width:80px">
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
    </div>

    {{-- ================= MODAL TAMBAH (dipakai bersama oleh semua tab) ================= --}}
    <div class="modal fade" id="addLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="formLocation">

                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            Tambah Lokasi <br>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        {{-- Nama Lokasi --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Lokasi
                            </label>

                            <input type="text" name="location_name" class="form-control"
                                placeholder="Contoh: 7-1-1 / F01-01 / H3P01L2" required>
                        </div>

                        {{-- Barcode --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Kode Lokasi
                            </label>

                            <div class="border rounded p-4 text-center">

                                <button type="button" id="btnScan" class="btn btn-outline-secondary rounded">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                        viewBox="0 0 24 24" class="fs-4 me-2">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2"
                                            d="M7 12h10M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2m-4 14h2a2 2 0 0 0 2-2v-2" />
                                    </svg>

                                    Scan Barcode

                                </button>

                                <div class="small text-muted mt-2">
                                    Arahkan kamera ke barcode lokasi
                                </div>
                                <div class="small mt-1 fw-bold">atau</div>
                                <input type="text" id="location_code" name="location_code"
                                    class="form-control form-control-sm mt-2"
                                    placeholder="Masukkan kode lokasi secara manual">

                            </div>

                        </div>

                        <div id="scanner-container" class="mb-3" style="display:none;">

                            <div id="reader"></div>

                        </div>

                        {{-- Status --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <div class="d-flex gap-4">

                                <div class="form-check">

                                    <input class="form-check-input" type="radio" name="status" value="1"
                                        checked>

                                    <label class="form-check-label">
                                        Aktif
                                    </label>

                                </div>

                                <div class="form-check">

                                    <input class="form-check-input" type="radio" name="status"
                                        value="0">

                                    <label class="form-check-label">
                                        Non Aktif
                                    </label>

                                </div>

                            </div>

                        </div>
                        {{-- Description --}}
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">
                                Deskripsi
                            </label>

                            <textarea name="description" class="form-control" rows="3" placeholder="Opsional"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">

                            Batal

                        </button>

                        <button type="submit" class="btn btn-primary btnSaveLocation">
                            <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="1em" height="1em"
                                viewBox="0 0 16 16">
                                <path d="M0 0h16v16H0z" fill="none" />
                                <path fill="currentColor"
                                    d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z" />
                            </svg>

                            Simpan

                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>


    {{-- ================= MODAL EDIT (tunggal, diisi via AJAX) ================= --}}
    <div class="modal fade" id="editLocationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="formEditLocation">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" id="editId">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            Edit Lokasi <br>
                            <small class="fw-light">Ubah data lokasi penyimpanan barang</small>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lokasi</label>
                            <input type="text" name="location_name" id="editLocationName" class="form-control"
                                placeholder="Contoh: 7-1-1 / F01-01 / H3P01L2" required>
                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-bold">Kode Lokasi</label>

                            <div class="border rounded p-4 text-center">

                                <button type="button" class="btn btn-outline-secondary rounded" id="btnScanEdit">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                                        viewBox="0 0 24 24" class="fs-4 me-2">
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2"
                                            d="M7 12h10M3 7V5a2 2 0 0 1 2-2h2M3 17v2a2 2 0 0 0 2 2h2M17 3h2a2 2 0 0 1 2 2v2m-4 14h2a2 2 0 0 0 2-2v-2" />
                                    </svg>

                                    Scan Barcode

                                </button>

                                <div class="small text-muted mt-2">
                                    Arahkan kamera ke barcode lokasi
                                </div>

                                <div class="small mt-1 fw-bold">atau</div>

                                <input type="text" name="location_code" id="editLocationCode"
                                    class="form-control form-control-sm mt-2"
                                    placeholder="Masukkan kode lokasi secara manual">

                            </div>

                        </div>

                        <div class="mb-3" id="scannerContainerEdit" style="display:none;">
                            <div id="readerEdit"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>

                            <div class="d-flex gap-4">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="editStatusAktif"
                                        value="1">
                                    <label class="form-check-label">Aktif</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status"
                                        id="editStatusNonAktif" value="0">
                                    <label class="form-check-label">Non Aktif</label>
                                </div>

                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>

                            <textarea name="description" id="editDescription" class="form-control" rows="3" placeholder="Opsional"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary btnSaveEdit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="1em" height="1em"
                                viewBox="0 0 16 16">
                                <path d="M0 0h16v16H0z" fill="none" />
                                <path fill="currentColor"
                                    d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1z" />
                            </svg>
                            Ubah
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    @push('script')
        <style>
            #locationTabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            #locationTabs .nav-item {
                flex-shrink: 0;
            }

            #locationTabs::-webkit-scrollbar {
                height: 4px;
            }

            #locationTabs::-webkit-scrollbar-thumb {
                background: #ccc;
                border-radius: 4px;
            }

            .dataTables_wrapper {
                padding: 1rem;
            }

            .dt-layout-row {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            #reader,
            #readerEdit {
                width: 100%;
                min-height: 280px;
                border-radius: 10px;
                overflow: hidden;
            }

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

            #summarySearch,
            #searchRak,
            #searchFlat,
            #searchHroom {
                padding-left: 4px;
                border-radius: 4px !important;
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

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // ============ KONFIGURASI KATEGORI ============
            const categoryConfig = {
                rak: {
                    table: 'locationRak',
                    search: 'searchRak',
                    status: 'statusRak',
                    length: 'lengthRak',
                    reset: 'resetRak',
                    lengthWrapper: 'lengthWrapperRak',
                    footer: 'footerRak',
                    bulkBar: 'bulkActionBarRak',
                    label: 'Rak'
                },
                flat_indoor: {
                    table: 'locationFlat',
                    search: 'searchFlat',
                    status: 'statusFlat',
                    length: 'lengthFlat',
                    reset: 'resetFlat',
                    lengthWrapper: 'lengthWrapperFlat',
                    footer: 'footerFlat',
                    bulkBar: 'bulkActionBarFlat',
                    label: 'Flat Indoor'
                },
                h_room: {
                    table: 'locationHroom',
                    search: 'searchHroom',
                    status: 'statusHroom',
                    length: 'lengthHroom',
                    reset: 'resetHroom',
                    lengthWrapper: 'lengthWrapperHroom',
                    footer: 'footerHroom',
                    bulkBar: 'bulkActionBarHroom',
                    label: 'H-Room Storage'
                },
                outdoor: {
                    table: 'locationOutdoor',
                    search: 'searchOutdoor',
                    status: 'statusOutdoor',
                    length: 'lengthOutdoor',
                    reset: 'resetOutdoor',
                    lengthWrapper: 'lengthWrapperOutdoor',
                    footer: 'footerOutdoor',
                    bulkBar: 'bulkActionBarOutdoor',
                    label: 'Outdoor'
                },
                backside: {
                    table: 'locationBackside',
                    search: 'searchBackside',
                    status: 'statusBackside',
                    length: 'lengthBackside',
                    reset: 'resetBackside',
                    lengthWrapper: 'lengthWrapperBackside',
                    footer: 'footerBackside',
                    bulkBar: 'bulkActionBarBackside',
                    label: 'Backside'
                },
                lain_lain: {
                    table: 'locationLainlain',
                    search: 'searchLainlain',
                    status: 'statusLainlain',
                    length: 'lengthLainlain',
                    reset: 'resetLainlain',
                    lengthWrapper: 'lengthWrapperLainlain',
                    footer: 'footerLainlain',
                    bulkBar: 'bulkActionBarLainlain',
                    label: 'Lain-lain'
                }
            };

            const categoryMeta = {
                rak: {
                    color: 'success',
                    icon: 'bi-diagram-3'
                },
                flat_indoor: {
                    color: 'primary',
                    icon: 'bi-archive'
                },
                h_room: {
                    color: 'purple',
                    icon: 'bi-box-seam'
                },
                outdoor: {
                    color: 'warning',
                    icon: 'bi-sun'
                },
                backside: {
                    color: 'dark',
                    icon: 'bi-signpost-2'
                },
                lain_lain: {
                    color: 'secondary',
                    icon: 'bi-three-dots'
                }
            };

            // tables[kategori]  -> instance DataTable
            // selections[kategori] -> Set id yang dicentang untuk bulk delete
            const tables = {};
            const selections = {
                rak: new Set(),
                flat_indoor: new Set(),
                h_room: new Set(),
                outdoor: new Set(),
                backside: new Set(),
                lain_lain: new Set()
            };
        </script>

        <script>
            $(document).ready(function() {

                // ============ INIT 3 TABEL (RAK / FLAT INDOOR / H-ROOM STORAGE) ============
                function moveDataTablesElements(tableId, lengthWrapperId, footerId) {
                    const $info = $('#' + tableId + '_info');
                    if ($info.length && !$('#' + lengthWrapperId).find('.dataTables_info').length) {
                        $info.addClass('text-muted small ms-2').appendTo('#' + lengthWrapperId);
                    }

                    const $paginate = $('#' + tableId + '_paginate');
                    if ($paginate.length && !$('#' + footerId).find('.dataTables_paginate').length) {
                        $paginate.appendTo('#' + footerId);
                    }
                }

                function toggleBulkActionBar(category) {
                    const cfg = categoryConfig[category];
                    const size = selections[category].size;

                    $('#' + cfg.bulkBar).find('.selectedCount').text(size);
                    $('#' + cfg.bulkBar).toggleClass('d-none', size === 0);
                }

                function resetSelection(category) {
                    selections[category].clear();
                    $('.checkAll[data-category="' + category + '"]').prop('checked', false);
                    toggleBulkActionBar(category);
                }

                function initCategoryTable(category) {
                    const cfg = categoryConfig[category];

                    const tbl = $('#' + cfg.table).DataTable({

                        dom: 'rtip',
                        processing: true,
                        serverSide: true,
                        order: [],

                        ajax: {
                            url: "{{ route('locations.data') }}",
                            data: function(d) {
                                d.category = category;
                                d.status = $('#' + cfg.status).val();
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
                                data: 'location_name',
                                name: 'location_name'
                            },
                            {
                                data: 'location_code',
                                name: 'location_code'
                            },
                            {
                                data: 'status',
                                name: 'status'
                            },
                            {
                                data: 'description',
                                name: 'description'
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
                                targets: [2, 5],
                                className: "text-wrap",
                                width: "220px"
                            },
                            {
                                targets: 1,
                                width: "80px"
                            },
                            {
                                targets: [4, 6],
                                className: "text-center"
                            }
                        ],

                        initComplete: function() {
                            moveDataTablesElements(cfg.table, cfg.lengthWrapper, cfg.footer);
                        },

                        drawCallback: function() {
                            moveDataTablesElements(cfg.table, cfg.lengthWrapper, cfg.footer);
                        }

                    });

                    tbl.on('draw.dt', function() {
                        resetSelection(category);
                    });

                    $('#' + cfg.search).on('input', function() {
                        tbl.search(this.value).draw();
                    });

                    $('#' + cfg.length).on('change', function() {
                        tbl.page.len($(this).val()).draw();
                    });

                    $('#' + cfg.status).on('change', function() {
                        tbl.ajax.reload();
                    });

                    $('#' + cfg.reset).on('click', function() {
                        $('#' + cfg.status).val('');
                        $('#' + cfg.search).val('');
                        tbl.search('').draw();
                        tbl.ajax.reload();
                    });

                    // checkbox per baris (delegated, dibatasi ke tabel kategori ini)
                    $('#' + cfg.table).on('change', '.row-checkbox', function() {
                        const id = $(this).val();

                        if (this.checked) {
                            selections[category].add(id);
                        } else {
                            selections[category].delete(id);
                        }

                        toggleBulkActionBar(category);
                    });

                    return tbl;
                }

                tables.rak = initCategoryTable('rak');
                tables.flat_indoor = initCategoryTable('flat_indoor');
                tables.h_room = initCategoryTable('h_room');
                tables.outdoor = initCategoryTable('outdoor');
                tables.backside = initCategoryTable('backside');
                tables.lain_lain = initCategoryTable('lain_lain');

                // Perbaikan bug DataTables: kolom header salah hitung lebar
                // karena tabel Rak/Flat Indoor/H-Room Storage berada di dalam
                // tab yang masih tersembunyi (display:none) saat pertama kali
                // di-init. Hitung ulang lebar kolom setiap tab dibuka.
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    e.target.scrollIntoView({
                        behavior: 'smooth',
                        inline: 'nearest',
                        block: 'nearest'
                    });

                    const targetSelector = $(e.target).data('bs-target');
                    $(targetSelector).find('table.dataTable').each(function() {
                        $(this).DataTable().columns.adjust().draw(false);
                    });
                });

                // Perbaikan bug DataTables yang sama, tapi untuk kasus
                // window di-resize (dibesar/dikecilkan). Tanpa ini, header
                // kolom bisa geser dari body-nya karena scrollX:true tidak
                // otomatis menghitung ulang lebar saat browser di-resize.
                // Di-debounce supaya tidak dipanggil berkali-kali saat
                // resize masih berlangsung.
                let resizeTimer;

                $(window).on('resize', function() {
                    clearTimeout(resizeTimer);

                    resizeTimer = setTimeout(function() {
                        $('.tab-pane.active').find('table.dataTable').each(function() {
                            $(this).DataTable().columns.adjust().draw(false);
                        });
                    }, 200);
                });

                function reloadAllTables() {
                    Object.values(tables).forEach(function(t) {
                        if (t) t.ajax.reload(null, false);
                    });
                    loadSummary();
                }

                // pilih semua per kategori
                $(document).on('change', '.checkAll', function() {
                    const category = $(this).data('category');
                    const checked = this.checked;

                    $('#' + categoryConfig[category].table + ' .row-checkbox').prop('checked', checked)
                        .each(function() {
                            const id = $(this).val();

                            if (checked) {
                                selections[category].add(id);
                            } else {
                                selections[category].delete(id);
                            }
                        });

                    toggleBulkActionBar(category);
                });

                // ================= BULK DELETE per kategori =================
                $(document).on('click', '.btnBulkDelete', function() {

                    const category = $(this).data('category');
                    const ids = Array.from(selections[category]);

                    if (ids.length === 0) return;

                    Swal.fire({
                        title: `Hapus ${ids.length} lokasi?`,
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
                            url: "{{ route('locations.bulk-destroy') }}",
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: ids
                            },

                            success: function(res) {

                                resetSelection(category);
                                reloadAllTables();

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

                // ============ RINGKASAN & DAFTAR PER KATEGORI (tab "Semua Lokasi") ============

                function statusBadge(status) {
                    return status ?
                        '<span class="badge bg-label-success rounded-pill">Aktif</span>' :
                        '<span class="badge bg-label-secondary rounded-pill">Non Aktif</span>';
                }

                function codeCell(code) {
                    if (!code) {
                        return '<span class="badge border border-warning text-warning bg-transparent" style="font-size:10px;">⚠ No Code</span>';
                    }
                    return '<span><i class="bi bi-upc-scan me-1"></i>' + $('<div>').text(code).html() +
                        '</span>';
                }

                function actionCell(id) {
                    return `
                        <div class="d-flex align-items-center gap-1 justify-content-center">
                            <button class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
                                type="button" data-id="${id}" data-bs-toggle="modal" data-bs-target="#editLocationModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ url('locations') }}/${id}" method="POST" class="form-hapus m-0">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>`;
                }

                let lastSummaryResponse = null;
                let summaryDebounce = null;

                function loadSummary() {
                    $.get("{{ route('locations.preview') }}", {
                        search: $('#summarySearch').val(),
                        status: $('#summaryStatus').val()
                    }, function(res) {
                        lastSummaryResponse = res;
                        renderSummaryCards(res);
                        renderCategoryPreview(res);
                    });
                }

                function renderSummaryCards(res) {
                    const order = ['rak', 'flat_indoor', 'h_room', 'outdoor', 'backside', 'lain_lain'];
                    let html = '';

                    order.forEach(function(key) {
                        const cat = res.categories[key];
                        const meta = categoryMeta[key];

                        html += `
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="card border-start border-4 border-${meta.color} h-100">
                                    <div class="card-body">
                                        <div class="text-${meta.color} fw-semibold mb-2">
                                            <i class="bi ${meta.icon} me-1"></i> ${cat.label}
                                        </div>
                                        <div class="fs-3 fw-bold">${cat.total}
                                            <span class="fs-6 fw-normal text-muted">lokasi</span>
                                        </div>
                                        <div class="small text-muted">${cat.percentage}% dari total</div>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#summaryCards').html(html);
                }

                function renderCategoryPreview(res) {
                    const order = [{
                            key: 'rak',
                            tab: 'tab-rak'
                        },
                        {
                            key: 'flat_indoor',
                            tab: 'tab-flat'
                        },
                        {
                            key: 'h_room',
                            tab: 'tab-hroom'
                        },
                        {
                            key: 'outdoor',
                            tab: 'tab-outdoor'
                        },
                        {
                            key: 'backside',
                            tab: 'tab-backside'
                        },
                        {
                            key: 'lain_lain',
                            tab: 'tab-lainlain'
                        }
                    ];

                    let html = '';

                    order.forEach(function(entry) {
                        const cat = res.categories[entry.key];
                        const meta = categoryMeta[entry.key];

                        let rows = '';

                        if (cat.items.length === 0) {
                            rows =
                                '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada data</td></tr>';
                        } else {
                            cat.items.forEach(function(item, idx) {
                                rows += `
                                    <tr>
                                        <td>${idx + 1}</td>
                                        <td>${$('<div>').text(item.location_name).html()}</td>
                                        <td>${codeCell(item.location_code)}</td>
                                        <td class="text-center">${statusBadge(item.status)}</td>
                                        <td>${item.description ? $('<div>').text(item.description).html() : '-'}</td>
                                        <td class="text-center">${actionCell(item.id)}</td>
                                    </tr>`;
                            });
                        }

                        html += `
                            <div class="mb-4 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi ${meta.icon} text-${meta.color}"></i>
                                    <strong>${cat.label} (${cat.total} lokasi)</strong>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-1">
                                        <thead>
                                            <tr>
                                                <th style="width:50px">No</th>
                                                <th>Nama Lokasi</th>
                                                <th>Kode Lokasi</th>
                                                <th class="text-center">Status</th>
                                                <th>Deskripsi</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>${rows}</tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <a href="#" class="btn-goto-tab small" data-target="${entry.tab}">
                                        Lihat semua ${cat.total} lokasi ${cat.label.toLowerCase()} &rarr;
                                    </a>
                                </div>
                            </div>`;
                    });

                    $('#categoryPreview').html(html);
                }

                $(document).on('click', '.btn-goto-tab', function(e) {
                    e.preventDefault();
                    const target = $(this).data('target');
                    $('#' + target).tab('show');
                });

                $('#summarySearch, #summaryStatus').on('input change', function() {
                    clearTimeout(summaryDebounce);
                    summaryDebounce = setTimeout(loadSummary, 300);
                });

                $('#btnResetSummary').on('click', function() {
                    $('#summarySearch').val('');
                    $('#summaryStatus').val('');
                    loadSummary();
                });

                $('#btnExportSummary').on('click', function() {
                    if (!lastSummaryResponse) return;

                    let csv = 'Kategori,Nama Lokasi,Kode Lokasi,Status,Deskripsi\n';

                    Object.values(lastSummaryResponse.categories).forEach(function(cat) {
                        cat.items.forEach(function(item) {
                            const row = [
                                cat.label,
                                item.location_name,
                                item.location_code || '',
                                item.status ? 'Aktif' : 'Non Aktif',
                                item.description || ''
                            ].map(function(v) {
                                return '"' + String(v).replace(/"/g, '""') + '"';
                            }).join(',');

                            csv += row + '\n';
                        });
                    });

                    const blob = new Blob([csv], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'ringkasan-lokasi.csv';
                    link.click();
                });

                loadSummary();

                // ================= TAMBAH (AJAX) =================
                $(document).on('submit', '#formLocation', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    $.ajax({
                        url: "{{ route('locations.store') }}",
                        method: "POST",
                        data: $(this).serialize(),

                        beforeSend: function() {
                            $('.btnSaveLocation').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveLocation').prop('disabled', false);

                            $('#addLocationModal').modal('hide');
                            $('#formLocation')[0].reset();

                            reloadAllTables();

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

                            $('.btnSaveLocation').prop('disabled', false);

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

                        url: "{{ route('locations.edit', ':id') }}".replace(':id', id),
                        type: 'GET',

                        success: function(res) {

                            $('#editId').val(res.id);
                            $('#editLocationName').val(res.location_name);
                            $('#editLocationCode').val(res.location_code);
                            $('#editDescription').val(res.description);

                            if (parseInt(res.status) === 1) {
                                $('#editStatusAktif').prop('checked', true);
                            } else {
                                $('#editStatusNonAktif').prop('checked', true);
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
                $(document).on('submit', '#formEditLocation', function(e) {

                    e.preventDefault();
                    e.stopPropagation();

                    let id = $('#editId').val();

                    $.ajax({

                        url: "{{ route('locations.update', ':id') }}".replace(':id', id),
                        method: 'POST',
                        data: $(this).serialize() + '&_method=PUT',

                        beforeSend: function() {
                            $('.btnSaveEdit').prop('disabled', true);
                        },

                        success: function(response) {

                            $('.btnSaveEdit').prop('disabled', false);

                            $('#editLocationModal').modal('hide');

                            reloadAllTables();

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

                // ================= HAPUS satuan (AJAX) =================
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

                                reloadAllTables();

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        document.querySelector(
                                                '.swal2-container').style
                                            .zIndex = '9999999';
                                    }
                                });

                            },

                            error: function(xhr) {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON?.message ||
                                        'Terjadi kesalahan.'
                                });

                            }

                        });

                    });

                });

                $('#addLocationModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

                $('#editLocationModal').on('hidden.bs.modal', function() {
                    this.querySelector('form').reset();
                });

            });
        </script>

        {{-- ================= BARCODE SCAN: TAMBAH ================= --}}
        <script>
            let html5QrCode = null;
            let isProcessing = false;

            document.getElementById('btnScan').addEventListener('click', function() {

                const inputId = "location_code";
                const container = document.getElementById('scanner-container');

                container.style.display = "block";

                setTimeout(() => {

                    if (html5QrCode) return;

                    html5QrCode = new Html5Qrcode("reader");

                    html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 5,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        async (decodedText) => {

                            if (isProcessing) return;
                            isProcessing = true;

                            const input = document.getElementById(inputId);

                            if (!input) {
                                console.error("Input tidak ditemukan:", inputId);
                                isProcessing = false;
                                return;
                            }

                            try {
                                const res = await fetch(`/check-location-code?code=${decodedText}`);
                                const data = await res.json();

                                await html5QrCode.stop();
                                html5QrCode = null;
                                container.style.display = "none";

                                if (data.exists) {

                                    input.value = "";

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Kode sudah digunakan!',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                } else {

                                    input.value = decodedText;

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'QR valid',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                }

                            } catch (err) {
                                console.error(err);
                            } finally {
                                isProcessing = false;
                            }
                        }
                    ).catch(err => {
                        console.error("Camera error:", err);
                        alert("Kamera tidak bisa dibuka");
                    });

                }, 300);
            });
        </script>

        {{-- ================= BARCODE SCAN: EDIT (modal tunggal) ================= --}}
        <script>
            let html5QrCodeEdit = null;
            let isProcessingEdit = false;

            document.getElementById('btnScanEdit').addEventListener('click', function() {

                const id = $('#editId').val();

                const input = document.getElementById('editLocationCode');
                const container = document.getElementById('scannerContainerEdit');

                if (!input || !container) {
                    console.error('Element tidak ditemukan');
                    return;
                }

                container.style.display = 'block';

                setTimeout(() => {

                    if (html5QrCodeEdit) return;

                    html5QrCodeEdit = new Html5Qrcode('readerEdit');

                    html5QrCodeEdit.start({
                            facingMode: "environment"
                        }, {
                            fps: 5,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        async (decodedText) => {

                            if (isProcessingEdit) return;
                            isProcessingEdit = true;

                            const oldValue = input.value;

                            try {

                                const res = await fetch(
                                    `/check-location-code?code=${encodeURIComponent(decodedText)}&ignore_id=${id}`
                                );

                                const data = await res.json();

                                await html5QrCodeEdit.stop();

                                html5QrCodeEdit = null;
                                container.style.display = 'none';

                                if (data.exists) {

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Kode sudah digunakan!',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });

                                    input.value = oldValue;

                                } else {

                                    input.value = decodedText;

                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'QR valid',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        target: document.body,
                                        didOpen: () => {
                                            document.querySelector('.swal2-container').style
                                                .zIndex = '99999999';
                                        }
                                    });
                                }
                            } catch (err) {

                                console.error(err);

                                if (html5QrCodeEdit) {
                                    try {
                                        await html5QrCodeEdit.stop();
                                    } catch (e) {}
                                    html5QrCodeEdit = null;
                                }

                            } finally {
                                isProcessingEdit = false;
                            }
                        }
                    ).catch(err => {
                        console.error("Camera error:", err);

                        Swal.fire({
                            icon: 'error',
                            title: 'Kamera tidak bisa dibuka'
                        });
                    });

                }, 300);
            });

            document.getElementById('editLocationModal').addEventListener('hidden.bs.modal', async () => {

                if (html5QrCodeEdit) {
                    try {
                        await html5QrCodeEdit.stop();
                    } catch (e) {}

                    html5QrCodeEdit = null;
                }

                isProcessingEdit = false;

                document.getElementById('scannerContainerEdit').style.display = 'none';
            });
        </script>

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
    @endpush
@endsection