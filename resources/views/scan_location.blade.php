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
@endsection

@push('script')
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
                        // item dari tabel lokasi (input manual)
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
                                            ${item.lot ? `<span class="lot-chip"><i class='bx bx-layer'></i> Lot ${item.lot}</span>` : ''}
                                        </div>

                                        <div class="text-dark fw-semibold">
                                            ${item.name}
                                        </div>

                                    </div>

                                    <!-- QTY -->
                                    <div class="text-end flex-shrink-0">
                                        <span class="qty-pill" style="background:#e8f0fe; color:#1d4ed8;">
                                            <i class='bx bx-cube'></i> ${parseInt(item.quantity)}
                                        </span>
                                    </div>

                                </div>

                                <div class="mt-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm btnMutation d-inline-flex align-items-center gap-1"
                                        style="border-radius:999px;"
                                        data-item="${item.id}">

                                        <i class="bx bx-history fs-6"></i>
                                        <span class="small text-nowrap">Lihat Mutasi</span>
                                        <i class="bx bx-chevron-down arrow fs-6"></i>

                                    </button>
                                </div>

                            </div>

                        </div>

                        <!-- PANEL MUTASI -->
                        <div
                            class="mutation-panel mt-3"
                            id="mutation-${item.id}"
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

                    html5QrCode.start(
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
                <i class="bx bx-inbox fs-4"></i>
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

                        if (item.transaction_type === "Receive Item") {

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
@endpush