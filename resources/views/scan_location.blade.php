@extends('master')
@section('title', 'Scan Barcode')
@section('content')
    <div class="row g-3">

        <div class="col-lg-4">

            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body">

                    <div class="text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">

                            <div class="avatar d-flex align-items-center justify-content-center me-3"
                                style="width:44px; height:44px; background:linear-gradient(135deg,#e3f2fd,#dbeafe); border-radius:14px;">

                                <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none" />
                                    <path fill="currentColor"
                                        d="M16 10c0-2.21-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4m-6 0c0-1.1.9-2 2-2s2 .9 2 2s-.9 2-2 2s-2-.9-2-2" />
                                    <path fill="currentColor"
                                        d="M11.42 21.81c.17.12.38.19.58.19s.41-.06.58-.19c.3-.22 7.45-5.37 7.42-11.82c0-4.41-3.59-8-8-8s-8 3.59-8 8c-.03 6.44 7.12 11.6 7.42 11.82M12 4c3.31 0 6 2.69 6 6c.02 4.44-4.39 8.43-6 9.74c-1.61-1.31-6.02-5.29-6-9.74c0-3.31 2.69-6 6-6" />
                                </svg>

                            </div>

                            <h4 class="fw-bold mb-0 text-start">
                                Scan Lokasi
                            </h4>

                        </div>

                        <small class="text-muted d-block mb-4">
                            Arahkan kamera ke barcode / QR code lokasi untuk scan otomatis
                        </small>

                    </div>
                    <div class="d-flex justify-content-center mb-3">

                        <div class="scanner-box">

                            <div id="reader"></div>

                            <div class="scanner-corner tl"></div>
                            <div class="scanner-corner tr"></div>
                            <div class="scanner-corner bl"></div>
                            <div class="scanner-corner br"></div>

                        </div>

                    </div>
                    <div class="text-center">
                        <small class="text-muted d-flex align-items-center justify-content-center gap-1">
                            <i class="bx bx-scan"></i> Pastikan kode berada di dalam frame
                        </small>
                    </div>
                    <div class="d-flex align-items-center my-4">

                        <div class="flex-grow-1 border-top"></div>

                        <div class="px-3 text-muted fw-semibold small text-uppercase" style="letter-spacing:.05em;">
                            atau
                        </div>

                        <div class="flex-grow-1 border-top"></div>

                    </div>


                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted" style="letter-spacing:.03em;">Input Manual</label>
                        <div class="input-group location-input-group">
                            <span class="input-group-text bg-light border-end-0" style="border-radius:10px 0 0 10px;">
                                <i class="bx bx-map-pin text-muted"></i>
                            </span>
                            <input type="text" id="location_code" class="form-control border-start-0 ps-2"
                                placeholder="Kode / nama lokasi">
                            <button id="btnCari" class="btn btn-primary d-flex align-items-center gap-1" style="border-radius:0 10px 10px 0;">
                                <i class="bx bx-search"></i>
                                <span>Cari</span>
                            </button>
                        </div>
                        <style>
                            .location-input-group .form-control {
                                border-radius: 0;
                                box-shadow: none !important;
                            }
                            .location-input-group:focus-within {
                                border-radius: 10px;
                                box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
                            }
                        </style>
                    </div>


                    <div class="card border-0 mt-4" style="background:linear-gradient(135deg,#eef4ff,#f3f0ff); border-radius:14px;">

                        <div class="card-body d-flex align-items-start py-3 px-3">

                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:36px; height:36px; background:#fff;">

                                    <i class="bx bx-info-circle text-primary fs-5"></i>

                                </div>
                            </div>

                            <div>
                                <div class="fw-semibold text-primary mb-0 small">
                                    Tips
                                </div>

                                <div class="text-muted small" style="line-height:1.4;">
                                    Gunakan scanner atau input manual jika kamera tidak tersedia.
                                </div>
                            </div>

                        </div>

                    </div>


                </div>
            </div>

        </div>

        <div class="col-lg-8">

            <div id="hasil">

                <div class="card border-0 shadow-sm" style="border-radius:16px; border:1px dashed #dee2e6 !important;">

                    <div class="card-body text-center py-5">

                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                            style="width:72px;height:72px;border-radius:50%;background:#f4f6fb;">
                            <i class="bx bx-map-alt fs-1 text-muted"></i>
                        </div>

                        <h5 class="mb-1">
                            Belum ada lokasi dipilih
                        </h5>

                        <p class="text-muted mb-0">
                            Scan barcode/QR code atau masukkan location code secara manual
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
        <div class="modal fade" id="moveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="d-flex flex-column overflow-hidden" style="background:#f5f6fb">

                    <div class="as-hero flex-shrink-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="as-hero-icon"><i class="bx bx-transfer-alt"></i></div>
                                <div>
                                    <h4 class="mb-0 fw-bold">Pindahkan Barang</h4>
                                    <small>Pindahkan stok barang ke lokasi lain.</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <div class="as-body flex-grow-1 overflow-auto">

                        <div id="moveAlert" class="as-alert d-none"></div>

                        <div class="as-summary">
                            <div>
                                <div class="fw-bold" id="moveItemName">-</div>
                                <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                    <span class="as-chip as-chip-gray"><i class="bx bx-map-pin me-1"></i><span id="moveFromName">-</span></span>
                                    <i class="bx bx-right-arrow-alt text-muted"></i>
                                    <span class="as-chip as-chip-indigo">Lokasi Tujuan</span>
                                    <span class="as-chip as-chip-gray" id="moveLotWrap"><i class="bx bx-layer me-1"></i>Lot <span id="moveLot" class="ms-1"></span></span>
                                </div>
                            </div>
                            <span class="as-qty"><span id="moveAvail">0</span> stok</span>
                        </div>

                        <div class="as-card mb-3">
                            <div class="as-card-title">
                                <div class="as-card-icon is-pink"><i class="bx bx-transfer-alt"></i></div>
                                <div>
                                    <strong>Detail Perpindahan</strong>
                                    <small>Isi jumlah dan pilih atau scan lokasi tujuan.</small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="as-label">Qty dipindah</label>
                                    <div class="as-field">
                                        <i class="bx bx-cube-alt"></i>
                                        <input type="number" class="as-input" id="moveQty" min="0.01" step="0.01">
                                    </div>
                                    <button type="button" class="as-pill-btn mt-2" id="btnMoveAll">Pindahkan semua</button>
                                </div>

                                <div class="col-md-7">
                                    <label class="as-label">Lokasi Tujuan</label>
                                    <div class="d-flex gap-2 align-items-start">
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <select id="moveTarget" placeholder="Cari kode / nama lokasi..."></select>
                                        </div>
                                        <button type="button" class="as-cam-btn" id="btnMoveCam" title="Scan barcode lokasi">
                                            <i class="bx bx-camera"></i>
                                        </button>
                                    </div>
                                    <small class="as-help">Ketik untuk mencari, atau scan barcode lokasi dengan kamera.</small>
                                    <div id="moveScanStatus" class="as-scan-status d-none"></div>
                                </div>
                            </div>

                            <div id="moveCamWrap" class="d-none text-center mt-4">
                                <div class="d-flex justify-content-center">
                                    <div class="scanner-box" style="max-width:220px;">
                                        <div id="moveReader"></div>
                                        <div class="scanner-corner tl"></div>
                                        <div class="scanner-corner tr"></div>
                                        <div class="scanner-corner bl"></div>
                                        <div class="scanner-corner br"></div>
                                    </div>
                                </div>
                                <small class="as-help">Arahkan kamera ke barcode lokasi tujuan</small>
                            </div>
                        </div>
                    </div>

                    <div class="as-footer flex-shrink-0">
                        <button type="button" class="as-btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="as-btn-save" id="btnConfirmMove">
                            <i class="bx bx-transfer-alt me-1"></i> Pindahkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .item-scroll {
            max-height: 480px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
        }

        .item-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .item-scroll::-webkit-scrollbar-thumb {
            background: #dde3ec;
            border-radius: 10px;
        }

        .item-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .item-card {
            border-radius: 14px !important;
            border: 1px solid #edf0f5 !important;
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }

        .item-card:hover {
            box-shadow: 0 6px 18px rgba(15, 23, 42, .07) !important;
            border-color: #e2e8f5 !important;
            transform: translateY(-1px);
        }

        .item-accent {
            width: 5px;
            border-radius: 6px;
            align-self: stretch;
            flex-shrink: 0;
        }

        .qty-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: .8rem;
            padding: .4rem .7rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .74rem;
            font-weight: 600;
            color: #64748b;
            background: #f5f7fb;
            border-radius: 999px;
            padding: .18rem .55rem;
        }

        .lot-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .74rem;
            font-weight: 600;
            color: #7c3aed;
            background: #f3ebff;
            border-radius: 999px;
            padding: .18rem .55rem;
        }
    </style>
    <script>
        $(document).ready(function() {

            let lastScan = '';
            let isLoading = false;
            let xhrRequest = null;

            const beep = new Audio('https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg');

            function renderNotFound(message = "Data tidak ditemukan") {
                $('#hasil').html(`
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center py-5">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:72px;height:72px;border-radius:50%;background:#fdecec;">
                        <i class="bx bx-search-alt fs-1 text-danger"></i>
                    </div>
                    <h5 class="mb-1 text-danger">${message}</h5>
                    <p class="text-muted mb-0">Cek kembali kode lokasi yang di-scan / diketik</p>
                </div>
            </div>
        `);
            }

            function fmtQty(v) {
                const n = Number(v);
                if (isNaN(n)) return v ?? '-';
                return n.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            }

            function moveButtonHtml(item, lot, qty, location, small) {
                return `
                                    <button
                                        type="button"
                                        class="btn btn-outline-warning btn-sm btnMove d-inline-flex align-items-center gap-1"
                                        style="border-radius:999px;${small ? 'padding:.15rem .6rem;' : ''}"
                                        data-item="${item.id}"
                                        data-name="${String(item.name ?? '').replace(/"/g, '&quot;')}"
                                        data-lot="${lot ?? ''}"
                                        data-qty="${Number(qty)}"
                                        data-from-id="${location.id ?? ''}"
                                        data-from-name="${String(location.location_name ?? '').replace(/"/g, '&quot;')}">

                                        <i class="bx bx-transfer fs-6"></i>
                                        <span class="small text-nowrap">Pindahkan</span>

                                    </button>`;
            }

            function renderHasil(response) {

                const isStaging = response.type === 'staging';

                let itemsHtml = '';

                if (response.items.length === 0) {
                    itemsHtml = `
                <div class="text-center text-muted py-5">
                    <i class="bx bx-package fs-1 d-block mb-2"></i>
                    Tidak ada barang di lokasi ini
                </div>
                `;
                }

                const seenItems = new Set();

                for (let i = 0; i < response.items.length; i++) {
                    const item = response.items[i];

                    if (isStaging) {

                        // ==== ITEM DARI TABEL STAGING ====
                        itemsHtml += `

                <div class="card shadow-sm mb-2 item-card">
                    <div class="card-body p-3">
                        <div class="d-flex gap-3">

                            <div class="item-accent" style="background:#f59e0b;"></div>

                            <div class="flex-grow-1 d-flex justify-content-between align-items-start gap-3">

                                <!-- INFO BARANG STAGING -->
                                <div class="flex-grow-1">

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="meta-chip"><i class='bx bx-purchase-tag'></i> ${item.po_number ?? '-'}</span>
                                        <span class="meta-chip"><i class='bx bx-barcode'></i> ${item.item_code ?? '-'}</span>
                                    </div>

                                    <div class="text-dark fw-semibold mb-1">
                                        ${item.name ?? '-'}
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                        <span><i class="bx bx-buildings me-1"></i>${item.supplier_origin ?? '-'}</span>
                                        <span><i class="bx bx-user me-1"></i>${item.item_owner ?? '-'}</span>
                                        <span><i class="bx bx-calendar me-1"></i>${item.arrival_date ?? '-'}</span>
                                    </div>

                                    ${item.notes ? `<div class="text-muted small fst-italic mt-2"><i class="bx bx-note me-1"></i>${item.notes}</div>` : ''}

                                </div>

                                <!-- QTY -->
                                <div class="text-end flex-shrink-0">
                                    <span class="qty-pill" style="background:#fef3e2; color:#b45309;">
                                        <i class='bx bx-cube'></i> ${parseInt(item.quantity)}
                                    </span>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                `;

                    } else {
                        // item dari tabel lokasi: 1 kartu per barang, semua lot digabung
                        if (seenItems.has(item.id)) continue;
                        seenItems.add(item.id);

                        const lots = response.items.filter(x => x.id === item.id);
                        const totalQty = lots.reduce((sum, x) => sum + Number(x.quantity), 0);
                        const multiLot = lots.length > 1;

                        const lotListHtml = multiLot ? `
                                <div class="mt-3" style="border:1px solid #e9ecf5;border-radius:.9rem;overflow:hidden;background:#fbfcff;">
                                    <div class="px-3 py-2 text-muted text-uppercase fw-semibold"
                                        style="font-size:.68rem;letter-spacing:.06em;background:#f4f6fb;">
                                        Rincian per lot
                                    </div>
                                    ${lots.map((l, idx) => {
                                        return `
                                    <div class="d-flex align-items-center gap-3 px-3 py-2" style="${idx > 0 ? 'border-top:1px solid #eef0f6;' : ''}">
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0" style="min-width:104px;">
                                            <i class="bx bx-layer" style="color:#7c3aed;"></i>
                                            <span class="fw-semibold small text-dark">${l.lot ? 'Lot ' + l.lot : 'Tanpa lot'}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-auto" style="min-width:64px;">
                                            <span class="fw-bold text-dark">${fmtQty(l.quantity)}</span>
                                            <span class="text-muted" style="font-size:.72rem;">pcs</span>
                                        </div>
                                        ${moveButtonHtml(item, l.lot, l.quantity, response.location, true)}
                                    </div>`;
                                    }).join('')}
                                </div>` : '';

                        itemsHtml += `

                <div class="card shadow-sm mb-2 item-card">
                    <div class="card-body p-3">
                        <div class="d-flex gap-3">

                            <div class="item-accent" style="background:#2563eb;"></div>

                            <div class="flex-grow-1">

                                <div class="d-flex justify-content-between align-items-start gap-3">

                                    <!-- INFO BARANG -->
                                    <div class="flex-grow-1">

                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="meta-chip"><i class='bx bx-store'></i> ${item.vendor_name ?? '-'}</span>
                                            <span class="meta-chip"><i class='bx bx-barcode'></i> ${item.item_code_internal}</span>
                                            ${!multiLot && item.lot ? `<span class="lot-chip"><i class='bx bx-layer'></i> Lot ${item.lot}</span>` : ''}
                                            ${multiLot ? `<span class="lot-chip"><i class='bx bx-layer'></i> ${lots.length} lot</span>` : ''}
                                        </div>

                                        <div class="text-dark fw-semibold">
                                            ${item.name}
                                        </div>

                                    </div>

                                    <!-- QTY -->
                                    <div class="text-end flex-shrink-0">
                                        <span class="qty-pill" style="background:#e8f0fe; color:#1d4ed8;">
                                            <i class='bx bx-cube'></i> ${fmtQty(totalQty)}
                                        </span>
                                    </div>

                                </div>

                                ${lotListHtml}

                                <div class="${multiLot ? 'mt-3' : 'mt-2'} d-flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm btnMutation d-inline-flex align-items-center gap-1"
                                        style="border-radius:999px;"
                                        data-item="${item.id}">

                                        <i class="bx bx-history fs-6"></i>
                                        <span class="small text-nowrap">Lihat Mutasi</span>
                                        <i class="bx bx-chevron-down arrow fs-6"></i>

                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-success btn-sm btnLocations d-inline-flex align-items-center gap-1"
                                        style="border-radius:999px;"
                                        data-item="${item.id}"
                                        data-exclude="${response.location.location_code ?? ''}">

                                        <i class="bx bx-map-pin fs-6"></i>
                                        <span class="small text-nowrap">Lokasi Lain</span>
                                        <i class="bx bx-chevron-down arrow fs-6"></i>

                                    </button>

                                    ${!multiLot ? moveButtonHtml(item, item.lot, item.quantity, response.location, false) : ''}
                                </div>

                            </div>

                        </div>

                        <!-- PANEL MUTASI -->
                        <div
                            class="mutation-panel mt-3"
                            id="mutation-${item.id}"
                            style="display:none;">

                        </div>

                        <!-- PANEL LOKASI LAIN -->
                        <div
                            class="mutation-panel mt-3"
                            id="locations-${item.id}"
                            style="display:none;">

                        </div>

                    </div>
                </div>

                `;

                    }
                }

                const html = `
        <div class="card border-0 shadow-sm mb-3" style="border-radius:16px; background:${isStaging ? 'linear-gradient(135deg,#fff8ec,#fff)' : 'linear-gradient(135deg,#eefaf0,#fff)'};">

    <div class="card-body p-4">

        <div class="row align-items-center g-3">

            <!-- ========================= -->
            <!-- INFO LOKASI -->
            <!-- ========================= -->
            <div class="col-lg-6 col-md-12">

                <div class="d-flex align-items-center">

                    <div class="d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                        style="width:58px;height:58px;background:#fff;border-radius:16px;box-shadow:0 4px 14px rgba(15,23,42,.08);">

                        <i class="bx ${isStaging ? 'bx-package' : 'bx-map-pin'} fs-2 ${isStaging ? 'text-warning' : 'text-success'}"></i>

                    </div>

                    <div>

                        <small class="text-muted fw-semibold text-uppercase" style="letter-spacing:.03em; font-size:.72rem;">
                            ${isStaging ? 'Area Staging Ditemukan' : 'Lokasi Ditemukan'}
                        </small>

                        <h3 class="fw-bold mb-1 mt-1">
                            ${response.location.location_name}
                        </h3>

                        <span class="badge rounded-pill
                            ${response.location.status
                                ? 'bg-label-success'
                                : 'bg-label-secondary'}">

                            <i class="bx ${response.location.status ? 'bx-check-circle' : 'bx-minus-circle'} me-1"></i>
                            ${response.location.status
                                ? 'Aktif'
                                : 'Tidak Aktif'}

                        </span>

                    </div>

                </div>

            </div>
            <div class="col-lg-6 col-md-12">

                <div class="row g-2">

                    <!-- TOTAL ITEM -->
                    <div class="col-6">

                        <div class="h-100" style="background:rgba(255,255,255,.7); border-radius:14px; backdrop-filter:blur(2px);">

                            <div class="p-3">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                        style="width:42px;height:42px; background:#fff;">

                                        <i class="bx bx-box ${isStaging ? 'text-warning' : 'text-success'} fs-4"></i>

                                    </div>

                                    <div>

                                        <h4 class="fw-bold mb-0">
                                            ${response.total_item}
                                        </h4>

                                        <small class="text-muted">
                                            Total Item
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- TOTAL QTY -->
                    <div class="col-6">

                        <div class="h-100" style="background:rgba(255,255,255,.7); border-radius:14px; backdrop-filter:blur(2px);">

                            <div class="p-3">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                        style="width:42px;height:42px; background:#fff;">

                                        <i class="bx bx-layer ${isStaging ? 'text-warning' : 'text-success'} fs-4"></i>

                                    </div>

                                    <div>

                                        <h4 class="fw-bold mb-0">
                                            ${response.total_qty}
                                        </h4>

                                        <small class="text-muted">
                                            Total Qty
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

        <div class="card border-0 shadow-sm" style="border-radius:16px;">

            <div class="p-3 border-bottom">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                        <i class="bx bx-search text-muted"></i>
                    </span>
                    <input type="text"
                        id="searchItem"
                        class="form-control border-start-0 ps-0"
                        style="border-radius:0 10px 10px 0;"
                        placeholder="Cari nama, kode, vendor, atau lot...">
                </div>
            </div>

            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">

                <div class="fw-semibold d-flex align-items-center gap-2">
                    <i class="bx bx-list-ul text-muted"></i>
                    Daftar Barang di Lokasi Ini
                </div>

                <span class="badge rounded-pill bg-label-primary">
                    ${response.total_item} Item
                </span>

            </div>

            <div class="card-body p-3">

                <div id="itemsContainer" class="item-scroll">
                    ${itemsHtml}
                </div>

            </div>

        </div>
        `;

                $('#hasil').html(html);
            }

            function cariLokasi(keyword) {

                if (!keyword) {
                    renderNotFound("Kode/nama lokasi masih kosong");
                    return;
                }

                if (isLoading) return;

                isLoading = true;

                if (xhrRequest) {
                    xhrRequest.abort();
                }

                xhrRequest = $.ajax({
                    url: "{{ route('scan.location.search') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        keyword: keyword
                    },

                    beforeSend: function() {
                        $('#hasil').html(`
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary mb-2"></div>
                        <div class="text-muted">Mencari lokasi...</div>
                    </div>
                </div>
            `);
                    },

                    success: function(response) {

                        if (!response.success) {
                            renderNotFound(response.message || "Lokasi tidak ditemukan");
                            return;
                        }

                        window.lastKeyword = keyword;
                        renderHasil(response);

                        beep.currentTime = 0;
                        beep.play();

                    },

                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error ' + xhr.status,
                            html: '<pre style="text-align:left">' + xhr.responseText + '</pre>'
                        });
                    },

                    complete: function() {
                        isLoading = false;
                    }
                });
            }

            window.refreshHasil = function() {
                if (window.lastKeyword) cariLokasi(window.lastKeyword);
            };

            $('#btnCari').on('click', function() {
                cariLokasi($('#location_code').val());
            });

            $('#location_code').on('keypress', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    $('#btnCari').click();
                }
            });

            const html5QrCode = new Html5Qrcode("reader");

            Html5Qrcode.getCameras()
                .then(devices => {

                    if (!devices.length) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kamera tidak ditemukan'
                        });
                        return;
                    }

                    let cameraId = devices[0].id;

                    devices.forEach(device => {
                        const label = (device.label || '').toLowerCase();
                        if (label.includes('back') || label.includes('rear') || label.includes(
                                'environment')) {
                            cameraId = device.id;
                        }
                    });

                    window.mainCameraId = cameraId;

                    window.startMainScanner = function() {
                        return html5QrCode.start(
                            cameraId, {
                                fps: 5,
                                qrbox: 200,
                                aspectRatio: 1
                            },
                            function(decodedText) {

                                if (decodedText === lastScan) return;

                                lastScan = decodedText;

                                $('#location_code').val(decodedText);

                            },
                            function() {}
                        );
                    };

                    window.stopMainScanner = function() {
                        return html5QrCode.stop().catch(function() {});
                    };

                    window.startMainScanner();

                })
                .catch(err => {
                    console.log(err);
                    renderNotFound("Kamera tidak tersedia");
                });

        });
    </script>

    <style>
        .scanner-box {
            width: 100%;
            max-width: 280px;
            aspect-ratio: 1 / 1;
            position: relative;
        }

        .scanner-corner {
            position: absolute;
            width: 26px;
            height: 26px;
            border: 3px solid #2563eb;
            z-index: 2;
            pointer-events: none;
        }

        .scanner-corner.tl { top: -4px; left: -4px; border-right: none; border-bottom: none; border-radius: 8px 0 0 0; }
        .scanner-corner.tr { top: -4px; right: -4px; border-left: none; border-bottom: none; border-radius: 0 8px 0 0; }
        .scanner-corner.bl { bottom: -4px; left: -4px; border-right: none; border-top: none; border-radius: 0 0 0 8px; }
        .scanner-corner.br { bottom: -4px; right: -4px; border-left: none; border-top: none; border-radius: 0 0 8px 0; }

        #reader {
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 12px;
        }

        /* ---------- Modal pindah lokasi (gaya sama dengan modal Pindahkan Item di Staging In) ---------- */
        #moveModal .modal-content {
            border: 0;
            border-radius: 1.4rem;
            overflow: hidden;
            box-shadow: 0 25px 60px -12px rgba(30, 41, 90, .35);
        }
        #moveModal .as-hero { padding: 1.5rem 2rem 1.25rem; background: #fff; border-bottom: 1px solid #eceef5; }
        #moveModal .as-hero-icon { width: 48px; height: 48px; border-radius: 1rem; display: grid; place-items: center; font-size: 1.5rem; color: #4f46e5; background: #eef0ff; }
        #moveModal .as-hero h4 { color: #2b3350; letter-spacing: -.01em; }
        #moveModal .as-hero small { color: #8a93a8; }
        #moveModal .as-body { padding: 1.5rem 2rem .5rem; background: #f5f6fb; }
        #moveModal .as-alert { margin-bottom: 1rem; padding: .7rem 1rem; border-radius: .8rem; font-size: .85rem; font-weight: 600; color: #be123c; background: #fff1f4; border: 1px solid #fecdd6; }
        #moveModal .as-label { display: block; margin-bottom: .3rem; font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8a93a8; }
        #moveModal .as-field { position: relative; }
        #moveModal .as-field > i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: #a5adc2; font-size: 1.05rem; pointer-events: none; }
        #moveModal .as-input { width: 100%; border: 1.5px solid #e6e9f2; background: #f8f9fd; border-radius: .75rem; padding: .5rem .8rem; font-size: .875rem; color: #2b3350; transition: border-color .15s, box-shadow .15s, background .15s; }
        #moveModal .as-field > i + .as-input { padding-left: 2.35rem; }
        #moveModal .as-input:focus { outline: 0; background: #fff; border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124, 58, 237, .12); }
        #moveModal .as-help { display: block; margin-top: .35rem; font-size: .74rem; color: #8a93a8; }
        #moveModal .as-pill-btn { border: 0; border-radius: 50rem; padding: .35rem .9rem; font-size: .75rem; font-weight: 600; color: #4f46e5; background: #eef0ff; }
        #moveModal .as-pill-btn:hover { color: #fff; background: #4f46e5; }
        #moveModal .as-scan-status { display: inline-flex; align-items: center; gap: .4rem; margin-top: .5rem; padding: .3rem .8rem; border-radius: 50rem; font-size: .76rem; font-weight: 600; }
        #moveModal .as-scan-status.is-wait { color: #4f46e5; background: #eef0ff; }
        #moveModal .as-scan-status.is-ok { color: #047857; background: #e7f8f0; }
        #moveModal .as-scan-status.is-fail { color: #be123c; background: #fff1f4; }
        #moveModal .as-cam-btn { flex-shrink: 0; width: 40px; height: 40px; display: grid; place-items: center; border: 0; border-radius: .75rem; font-size: 1.2rem; color: #4f46e5; background: #eef0ff; transition: all .15s; }
        #moveModal .as-cam-btn:hover { color: #fff; background: #4f46e5; }
        #moveModal .as-summary { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; padding: 1.1rem 1.4rem; background: #fff; border-radius: 1.1rem; box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12); }
        #moveModal .as-chip { display: inline-flex; align-items: center; padding: .25rem .75rem; border-radius: 50rem; font-size: .74rem; font-weight: 600; }
        #moveModal .as-chip-gray { color: #5a6482; background: #eceef6; }
        #moveModal .as-chip-indigo { color: #4f46e5; background: #eef0ff; }
        #moveModal .as-qty { flex-shrink: 0; padding: .45rem 1rem; border-radius: 50rem; font-size: .85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 8px 18px -8px rgba(79, 70, 229, .6); }
        #moveModal .as-card { position: relative; background: #fff; border-radius: 1.1rem; padding: 1.25rem 1.4rem 1.4rem 1.7rem; box-shadow: 0 2px 10px -4px rgba(43, 51, 80, .12); }
        #moveModal .as-card::before { content: ""; position: absolute; left: 0; top: 1.1rem; bottom: 1.1rem; width: 5px; border-radius: 0 6px 6px 0; background: linear-gradient(180deg, #4f46e5, #db2777); }
        #moveModal .as-card-title { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.1rem; }
        #moveModal .as-card-title strong { display: block; color: #2b3350; font-size: .98rem; }
        #moveModal .as-card-title small { color: #8a93a8; }
        #moveModal .as-card-icon { width: 2.4rem; height: 2.4rem; flex-shrink: 0; display: grid; place-items: center; border-radius: .8rem; font-size: 1.2rem; color: #4f46e5; background: #eef0ff; }
        #moveModal .as-card-icon.is-pink { color: #db2777; background: #fdf0f7; }
        #moveModal .as-footer { display: flex; justify-content: flex-end; gap: .6rem; padding: 1rem 2rem 1.4rem; background: #f5f6fb; }
        #moveModal .as-btn-save { border: 0; border-radius: .8rem; padding: .6rem 1.6rem; font-weight: 600; color: #fff; background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 10px 22px -8px rgba(79, 70, 229, .65); }
        #moveModal .as-btn-save:hover { color: #fff; filter: brightness(1.08); }
        #moveModal .as-btn-save:disabled { opacity: .6; }
        #moveModal .as-btn-cancel { border: 0; border-radius: .8rem; padding: .6rem 1.3rem; font-weight: 600; color: #5a6482; background: #e9ebf5; }
        #moveModal .ts-wrapper .ts-control { min-height: 40px; border: 1.5px solid #e6e9f2; border-radius: .75rem; background: #f8f9fd; padding: .45rem .8rem; box-shadow: none; }
        #moveModal .ts-wrapper.focus .ts-control { background: #fff; border-color: #7c3aed; box-shadow: 0 0 0 4px rgba(124, 58, 237, .12); }
        .ts-dropdown.move-dd { border-radius: .75rem; border: 0; box-shadow: 0 16px 36px -10px rgba(43, 51, 80, .3); overflow: hidden; }
        .ts-dropdown.move-dd .active { background: #eef0ff; color: #4f46e5; }

        #moveReader {
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 12px;
        }

        #moveReader video,
        #moveReader canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 12px;
        }

        .swal2-container { z-index: 3000 !important; }
        .ts-dropdown { z-index: 2500 !important; }

        #reader video,
        #reader canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 12px;
        }
    </style>

    <script>
        $(document).on('keyup', '#searchItem', function() {
            let keyword = $(this).val().toLowerCase();

            $('#itemsContainer .item-card').each(function() {
                let text = $(this).text().toLowerCase();

                if (text.includes(keyword)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    </script>

    <style>
        .btnMutation .arrow {

            transition: .3s;

        }

        .btnMutation.open .arrow {

            transform: rotate(180deg);

        }

        .mutation-panel {

            border-top: 1px dashed #e9ecf2;

            padding-top: 15px;

        }

        .mutation-table td {

            vertical-align: middle;

        }

        .type-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .type-in {

            color: #16a34a;

            font-weight: 600;

        }

        .type-out {

            color: #dc2626;

            font-weight: 600;

        }

        .type-transfer {

            color: #7c3aed;

            font-weight: 600;

        }

        .loading-mutation {

            padding: 25px;

            text-align: center;

        }
    </style>

    <script>
        $(document).on('click', '.btnMutation', function() {

            let button = $(this);

            let itemId = button.data('item');

            let panel = $("#mutation-" + itemId);

            if (panel.is(":visible")) {

                panel.slideUp(200);

                button.removeClass("open");

                button.find(".arrow")
                    .removeClass("bx-chevron-up")
                    .addClass("bx-chevron-down");

                return;

            }

            panel.html(`

        <div class="loading-mutation">

            <div class="spinner-border text-primary"></div>

            <div class="small text-muted mt-2">

                Mengambil data mutasi...

            </div>

        </div>

    `);

            panel.slideDown(200);

            button.addClass("open");

            button.find(".arrow")
                .removeClass("bx-chevron-down")
                .addClass("bx-chevron-up");

            $.get("/item/" + itemId + "/mutations", function(data) {

                let html = '';

                if (data.length === 0) {

                    html = `

            <div class="d-flex align-items-center gap-2 text-muted py-3 px-1">
                <i class="bi bi-inbox fs-4"></i>
                <span class="small">Belum ada mutasi untuk barang ini.</span>
            </div>

            `;

                } else {

                    html = `

<div class="card border-0" style="background:#f8f9fc; border-radius:14px;">

<div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">

<div class="fw-semibold text-primary d-flex align-items-center">

    <i class="bx bx-history fs-5 me-1"></i>

    <span>Riwayat Mutasi</span>

</div>

<a href="/stock-mutation?item=${itemId}"

class="btn btn-sm btn-outline-primary" style="border-radius:999px;">

Selengkapnya

<i class="bx bx-link-external ms-2"></i>

</a>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle mb-0 mutation-table">

<thead>

<tr class="text-muted small text-uppercase" style="letter-spacing:.03em;">

<th class="border-0">Tipe</th>

<th class="border-0">Tanggal</th>

<th class="border-0">No Transaksi</th>

<th class="border-0">Deskripsi</th>

<th class="border-0 text-center">Masuk</th>

<th class="border-0 text-center">Keluar</th>

<th class="border-0 text-center">Saldo</th>

</tr>

</thead>

<tbody>

`;

                    data.forEach(function(item) {

                        let colorClass = '';

                        let bgColor = '';

                        let icon = '';

                        if (item.transaction_trype === "Receive Item") {

                            colorClass = 'type-in';

                            bgColor = '#e9f9ee';

                            icon = 'bx-down-arrow-alt';

                        } else if (item.transaction_type === "Delivery Order") {

                            colorClass = 'type-out';

                            bgColor = '#fdecec';

                            icon = 'bx-up-arrow-alt';

                        } else {

                            colorClass = 'type-transfer';

                            bgColor = '#f3ebff';

                            icon = 'bx-transfer';

                        }

                        html += `

<tr>

<td>
    <span class="type-icon ${colorClass}" style="background:${bgColor};" title="${item.transaction_type ?? '-'}">
        <i class="bx ${icon}"></i>
    </span>
</td>

<td>
    ${new Date(item.transaction_date).toLocaleDateString('id-ID')}
</td>

<td>

${item.transaction_number??'-'}

</td>

<td class="text-muted">

${item.description??'-'}

</td>

<td class="text-center text-success fw-semibold">
    ${item.qty_in > 0 ? ('+' + parseFloat(item.qty_in)) : '-'}
</td>

<td class="text-center text-danger fw-semibold">
    ${item.qty_out > 0 ? ('-' + parseFloat(item.qty_out)) : '-'}
</td>

<td class="text-center fw-semibold">
    ${parseFloat(item.qty_balance)}
</td>

</tr>

`;

                    });

                    html += `

</tbody>

</table>

</div>

</div>

`;

                }

                panel.html(html);

            });

        });
    </script>

    <script>
        $(document).on('click', '.btnLocations', function() {

            let button = $(this);
            let itemId = button.data('item');
            let exclude = button.data('exclude') || '';
            let panel = $("#locations-" + itemId);

            if (panel.is(":visible")) {
                panel.slideUp(200);
                button.removeClass("open");
                button.find(".arrow").removeClass("bx-chevron-up").addClass("bx-chevron-down");
                return;
            }

            panel.html(`
                <div class="loading-mutation">
                    <div class="spinner-border text-success"></div>
                    <div class="small text-muted mt-2">Mengambil data lokasi...</div>
                </div>
            `);

            panel.slideDown(200);
            button.addClass("open");
            button.find(".arrow").removeClass("bx-chevron-down").addClass("bx-chevron-up");

            $.get("/item/" + itemId + "/locations", {
                exclude: exclude
            }, function(data) {

                if (!data.locations.length) {
                    panel.html(`
                        <div class="d-flex align-items-center gap-2 text-muted py-3 px-1">
                            <i class="bx bx-map-alt fs-4"></i>
                            <span class="small">Barang ini tidak ada di lokasi lain.</span>
                        </div>
                    `);
                    return;
                }

                let html = `
                    <div class="card border-0" style="background:#f8f9fc; border-radius:14px;">
                        <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                            <div class="fw-semibold text-success d-flex align-items-center">
                                <i class="bx bx-map-pin fs-5 me-1"></i>
                                <span>Lokasi Lain</span>
                            </div>
                            <span class="qty-pill" style="background:#e9f9ee; color:#15803d;">
                                <i class='bx bx-cube'></i> Total ${parseFloat(data.total_qty)}
                            </span>
                        </div>
                        <div class="card-body pt-2">
                `;

                data.locations.forEach(function(loc) {
                    html += `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-semibold text-dark">${loc.location_name}</span>
                                <span class="meta-chip"><i class='bx bx-barcode'></i> ${loc.location_code ?? '-'}</span>
                                ${loc.lot ? `<span class="lot-chip"><i class='bx bx-layer'></i> Lot ${loc.lot}</span>` : ''}
                            </div>
                            <span class="qty-pill" style="background:#e8f0fe; color:#1d4ed8;">
                                <i class='bx bx-cube'></i> ${parseFloat(loc.quantity)}
                            </span>
                        </div>
                    `;
                });

                html += `</div></div>`;
                panel.html(html);
            });
        });
    </script>
    <script>
        $(function() {
            const modalEl = document.getElementById('moveModal');
            const moveModal = new bootstrap.Modal(modalEl, { focus: false });
            const optionsUrl = "{{ route('scan.location.options') }}";

            let moveScanner = null;
            let mainStopped = false;
            let moveData = null;
            let moveLastScan = '';

            const ts = new TomSelect('#moveTarget', {
                valueField: 'id',
                labelField: 'label',
                searchField: ['location_name', 'location_code'],
                maxOptions: 30,
                preload: 'focus',
                loadThrottle: 250,
                dropdownParent: 'body',
                onInitialize: function() { this.dropdown.classList.add('move-dd'); },
                shouldLoad: function() { return true; },
                load: function(query, callback) {
                    $.get(optionsUrl, { q: query, exclude: moveData ? moveData.from_location_id : '' })
                        .done(function(res) { callback(res); })
                        .fail(function() { callback(); });
                },
                render: {
                    option: function(d, esc) {
                        return '<div class="py-1">' + esc(d.location_name || d.location_code || '-') + '</div>';
                    },
                    item: function(d, esc) {
                        return '<div>' + esc(d.location_name || d.location_code || '-') + '</div>';
                    },
                    no_results: function() {
                        return '<div class="no-results p-2 small text-muted">Lokasi tidak ditemukan</div>';
                    }
                }
            });

            function showAlert(msg) {
                $('#moveAlert').text(msg).removeClass('d-none');
            }

            function setScanStatus(type, html) {
                $('#moveScanStatus')
                    .removeClass('d-none is-wait is-ok is-fail')
                    .addClass('is-' + type)
                    .html(html);
            }

            function clearScanStatus() {
                $('#moveScanStatus').addClass('d-none').empty();
            }

            function hideAlert() {
                $('#moveAlert').addClass('d-none').text('');
            }

            function resetTarget() {
                ts.clear(true);
                ts.clearOptions();
                ts.loadedSearches = {};
            }

            function stopCamera() {
                if (!moveScanner) return Promise.resolve();
                const sc = moveScanner;
                moveScanner = null;
                return sc.stop().catch(function() {}).then(function() {
                    $('#moveCamWrap').addClass('d-none');
                });
            }

            function resumeMain() {
                if (mainStopped && window.startMainScanner) {
                    mainStopped = false;
                    window.startMainScanner();
                }
            }

            function startCamera() {
                $('#moveCamWrap').removeClass('d-none');

                const begin = function() {
                    moveScanner = new Html5Qrcode('moveReader');
                    moveScanner.start(
                        window.mainCameraId ? window.mainCameraId : { facingMode: 'environment' },
                        { fps: 5, qrbox: 180, aspectRatio: 1 },
                        function(text) {
                            if (text === moveLastScan) return;
                            moveLastScan = text;

                            // Tampilkan kode yang terbaca langsung di input lokasi tujuan.
                            ts.setTextboxValue(text);
                            setScanStatus('wait', '<i class="bx bx-loader-alt bx-spin"></i> Kode terbaca: ' + $('<div>').text(text).html() + ' &mdash; mencari lokasi...');
                            lookupTarget(text);
                        },
                        function() {}
                    ).catch(function() {
                        moveScanner = null;
                        $('#moveCamWrap').addClass('d-none');
                        showAlert('Kamera tidak dapat dibuka.');
                        resumeMain();
                    });
                };

                if (window.stopMainScanner) {
                    mainStopped = true;
                    window.stopMainScanner().then(begin);
                } else {
                    begin();
                }
            }

            function lookupTarget(text) {
                const safe = $('<div>').text(text).html();

                $.get(optionsUrl, { q: text, exact: 1, exclude: moveData.from_location_id })
                    .done(function(res) {
                        if (!res.length) {
                            moveLastScan = '';
                            setScanStatus('fail', '<i class="bx bx-error-circle"></i> Kode terbaca: ' + safe + ' &mdash; lokasi tidak ditemukan');
                            return;
                        }

                        hideAlert();
                        ts.addOption(res[0]);
                        ts.setValue(res[0].id);
                        setScanStatus('ok', '<i class="bx bx-check-circle"></i> Kode terbaca: ' + safe + ' &mdash; ' + $('<div>').text(res[0].location_name || '').html());
                        stopCamera().then(resumeMain);
                    })
                    .fail(function() {
                        moveLastScan = '';
                        setScanStatus('fail', '<i class="bx bx-error-circle"></i> Kode terbaca: ' + safe + ' &mdash; gagal mencari lokasi');
                    });
            }

            $(document).on('click', '.btnMove', function() {
                const d = $(this).data();

                moveData = {
                    item_id: d.item,
                    from_location_id: d.fromId,
                    lot: d.lot === '' || d.lot === undefined ? '' : String(d.lot),
                    max: Number(d.qty)
                };

                hideAlert();
                clearScanStatus();
                resetTarget();
                moveLastScan = '';

                $('#moveItemName').text(d.name || '-');
                $('#moveFromName').text(d.fromName || '-');
                moveData.lot !== '' ? ($('#moveLot').text(moveData.lot), $('#moveLotWrap').show()) : $('#moveLotWrap').hide();
                $('#moveAvail').text(Number(d.qty).toLocaleString('id-ID', { maximumFractionDigits: 2 }));
                $('#moveQty').val(d.qty).attr('max', d.qty);

                moveModal.show();
            });

            $('#btnMoveAll').on('click', function() {
                $('#moveQty').val(moveData.max);
            });

            $('#btnMoveCam').on('click', function() {
                if (moveScanner) {
                    stopCamera().then(resumeMain);
                } else {
                    hideAlert();
                    startCamera();
                }
            });

            modalEl.addEventListener('hidden.bs.modal', function() {
                stopCamera().then(resumeMain);
            });

            $('#btnConfirmMove').on('click', function() {
                const qty = parseFloat($('#moveQty').val());
                const target = ts.getValue();

                hideAlert();

                if (!target) {
                    showAlert('Pilih atau scan lokasi tujuan terlebih dahulu.');
                    return;
                }

                if (!qty || qty <= 0 || qty > moveData.max) {
                    showAlert('Qty tidak valid. Maksimal ' + moveData.max + '.');
                    return;
                }

                const btn = $(this).prop('disabled', true);

                $.ajax({
                    url: "{{ route('scan.location.move') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        item_id: moveData.item_id,
                        from_location_id: moveData.from_location_id,
                        lot: moveData.lot,
                        qty: qty,
                        to_location_id: target
                    },
                    success: function(res) {
                        modalEl.addEventListener('hidden.bs.modal', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil dipindahkan',
                                text: res.message,
                                timer: 2200,
                                showConfirmButton: false
                            });
                            if (window.refreshHasil) window.refreshHasil();
                        }, { once: true });

                        moveModal.hide();
                    },
                    error: function(xhr) {
                        const r = xhr.responseJSON || {};
                        const msg = r.message
                            || (r.errors ? Object.values(r.errors).flat()[0] : '')
                            || 'Terjadi kesalahan (' + xhr.status + ').';

                        showAlert(msg);
                        Swal.fire({ icon: 'error', title: 'Gagal memindahkan', text: msg });
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush