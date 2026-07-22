@extends('master')
@section('title', 'Scan Barcode')
@section('content')
    <div class="row g-3">

        <div class="col-lg-4">

            <div class="card h-100">
                <div class="card-body">

                    <div class="text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">

                            <div class="avatar d-flex align-items-center justify-content-center me-3"
                                style="width:40px; height:40px; background-color:#e3f2fd; border-radius:50%;">

                                <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                                    viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none" />
                                    <path fill="currentColor"
                                        d="M16 10c0-2.21-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4m-6 0c0-1.1.9-2 2-2s2 .9 2 2s-.9 2-2 2s-2-.9-2-2" />
                                    <path fill="currentColor"
                                        d="M11.42 21.81c.17.12.38.19.58.19s.41-.06.58-.19c.3-.22 7.45-5.37 7.42-11.82c0-4.41-3.59-8-8-8s-8 3.59-8 8c-.03 6.44 7.12 11.6 7.42 11.82M12 4c3.31 0 6 2.69 6 6c.02 4.44-4.39 8.43-6 9.74c-1.61-1.31-6.02-5.29-6-9.74c0-3.31 2.69-6 6-6" />
                                </svg>

                            </div>

                            <h4 class="fw-bold mb-0">
                                Scan Lokasi
                            </h4>

                        </div>

                        <small class="text-muted mb-5">
                            Arahkan kamera ke barcode / QR code lokasi untuk scan otomatis
                        </small>

                    </div>
                    <div class="d-flex justify-content-center mb-3 mt-5">

                        <div class="scanner-box">

                            <div id="reader"></div>

                        </div>

                    </div>
                    <div class="text-center">
                        <small class="mt-5 text-muted text-center">Pastikan kode berada di dalam frame</small>
                    </div>
                    <div class="d-flex align-items-center my-3">

                        <div class="flex-grow-1 border-top"></div>

                        <div class="px-3 text-muted fw-bold small">
                            atau
                        </div>

                        <div class="flex-grow-1 border-top"></div>

                    </div>


                    <div class="mb-3">
                        <label class="form-label fw-bold">Input Manual</label>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="text" id="location_code" class="form-control form-control-sm"
                                    placeholder="Masukkan location code / nama lokasi">
                            </div>
                            <div class="col-md-4">
                                <button id="btnCari" class="btn btn-primary w-100 btn-sm">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </div>


                    <div class="card border-0 shadow-sm mt-5" style="background-color:#eef4ff; border-radius:12px;">

                        <div class="card-body d-flex align-items-start py-2 px-2">

                            <div class="me-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:34px; height:34px; background-color:#e3f2fd;">

                                    <i class="bx bx-info-circle text-primary fs-5"></i>

                                </div>
                            </div>

                            <div>
                                <div class="fw-bold text-primary mb-0 small">
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

                <div class="card">

                    <div class="card-body text-center py-5">

                        <i class="bx bx-map fs-1 text-muted"></i>

                        <h5 class="mt-3">
                            Belum ada lokasi dipilih
                        </h5>

                        <p class="text-muted">
                            Scan atau masukkan location code
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection

@push('script')
    <style>
        .stock-scroll {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
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
            <div class="card">
                <div class="card-body text-center p-5">
                    <i class="bx bx-search-alt fs-1 text-danger"></i>
                    <h5 class="mt-2 text-danger">${message}</h5>
                    <small class="text-muted">Cek kembali kode lokasi</small>
                </div>
            </div>
        `);
            }

            function renderHasil(response) {

                let stocksHtml = '';

                for (let i = 0; i < response.stocks.length; i++) {
                    const stock = response.stocks[i];

                    stocksHtml += `

                <div class="card border border-light shadow-sm mb-3 stock-item">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start">

                            <!-- INFO BARANG -->
                            <div class="flex-grow-1">

                                <small class="text-muted">
                                    ${stock.item_code_internal}
                                </small>

                                <div class=" text-dark fw-semibold mb-2">
                                    ${stock.name}
                                </div>

                            </div>

                            <!-- QTY -->
                            <div class="text-end" width="300">

                                <small class="badge bg-primary p-2">
                                    Qty: ${parseInt(stock.quantity)}
                                </small>

                                <br>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm btnMutation mt-2 d-inline-flex align-items-center gap-1"
                                    data-stock="${stock.id}">

                                    <i class="bx bx-history fs-6"></i>

                                    <span class="small text-nowrap">Lihat Mutasi</span>

                                    <i class="bx bx-chevron-down arrow fs-6"></i>

                                </button>

                            </div>

                        </div>

                        <!-- PANEL MUTASI -->
                        <div
                            class="mutation-panel mt-3"
                            id="mutation-${stock.id}"
                            style="display:none;">

                        </div>

                    </div>
   
                </div>

                `;
                }

                const html = `
        <div class="card border-0 mb-3">

    <div class="card-body">

        <div class="row align-items-center">

            <!-- ========================= -->
            <!-- INFO LOKASI -->
            <!-- ========================= -->
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">

                <div class="d-flex align-items-center">

                    <div class="avatar d-flex align-items-center justify-content-center me-3"
                        style="width:60px;height:60px;background:#e8f5e9;border-radius:50%;">

                        <i class="bx bx-map-pin fs-2 text-success"></i>

                    </div>

                    <div>

                        <small class="text-muted fw-semibold">
                            Lokasi Ditemukan
                        </small>

                        <h3 class="fw-bold mb-1">
                            ${response.location.location_name}
                        </h3>

                        <div class="text-muted">
                            ${response.location.location_code}
                        </div>

                        <span class="badge mt-2 rounded-pill
                            ${response.location.status
                                ? 'bg-label-success'
                                : 'bg-label-secondary'}">

                            ${response.location.status
                                ? 'Aktif'
                                : 'Tidak Aktif'}

                        </span>

                    </div>

                </div>

            </div>
            <div class="col-lg-6 col-md-12">

                <div class="row g-3">

                    <!-- TOTAL ITEM -->
                    <div class="col-6">

                        <div class="card border-0 shadow-sm h-100"
                            style="background:#e8f5e9">

                            <div class="card-body py-3 px-3">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width:45px;height:45px;">

                                        <i class="bx bx-box text-success fs-3"></i>

                                    </div>

                                    <div>

                                        <h4 class="fw-bold text-success mb-0">
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

                        <div class="card border-0 shadow-sm h-100"
                            style="background:#e8f5e9">

                            <div class="card-body py-3 px-3">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width:45px;height:45px;">

                                        <i class="bx bx-layer text-success fs-3"></i>

                                    </div>

                                    <div>

                                        <h4 class="fw-bold text-success mb-0">
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

        <div class="card">

            <div class="p-2 border-bottom">
                <input type="text"
                    id="searchStock"
                    class="form-control form-control-sm"
                    placeholder="Cari barang...">
            </div>

            <div class="card-header py-2 d-flex justify-content-between align-items-center">

                <div class="mb-0 fw-bold mt-2">Daftar Barang di Lokasi Ini</div>

                <span class="badge rounded-pill bg-label-primary">
                    ${response.total_item} Item
                </span>

            </div>

            <div class="card-body p-2">

                <div id="stocksContainer" class="stock-scroll">
                    ${stocksHtml}
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
                <div class="card">
                    <div class="card-body text-center p-4">
                        <div class="spinner-border text-primary"></div>
                        <div class="mt-2">Mencari lokasi...</div>
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
        $(document).on('keyup', '#searchStock', function() {
            let keyword = $(this).val().toLowerCase();

            $('#stocksContainer .stock-item').each(function() {
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

            border-top: 1px solid #eee;

            padding-top: 15px;

        }

        .mutation-table td {

            vertical-align: middle;

        }

        .badge-type {

            font-size: .85rem;

            padding: 5px 10px;

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

            let stockId = button.data('stock');

            let panel = $("#mutation-" + stockId);

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

            $.get("/stock/" + stockId + "/mutations", function(data) {

                let html = '';

                if (data.length === 0) {

                    html = `

            <div class="alert alert-light border mb-0">

                Belum ada 
                .

            </div>

            `;

                } else {

                    html = `

<div class="card border-0 shadow-sm">

<div class="card-header bg-white d-flex justify-content-between align-items-center">
            
<div class="fw-semibold text-primary d-flex align-items-center">

    <i class="bx bx-history fs-5 me-1"></i>

    <span>Riwayat Mutasi</span>

</div>

<a href="/stock-mutation?stock=${stockId}"

class="btn btn-sm btn-outline-primary">

Selengkapnya

<i class="bx bx-link-external ms-2"></i>

</a>

</div>

<div class="table-responsive">

<table class="table table-hover mb-0 mutation-table">

<thead>

<tr>

<th>Tanggal</th>

<th>No Transaksi</th>

<th>Deskripsi</th>

<th class="text-center">Masuk</th>

<th class="text-center">Keluar</th>

<th class="text-center">Saldo</th>

</tr>

</thead>

<tbody>

`;

                    data.forEach(function(item) {

                        let badge = '';

                        let icon = '';

                        if (item.transaction_type === "Receive Item") {

                            badge = 'text-success';

                            icon = 'bx-down-arrow-alt';

                        } else if (item.transaction_type === "Delivery Order") {

                            badge = 'text-danger';

                            icon = 'bx-up-arrow-alt';

                        } else {

                            badge = 'text-primary';

                            icon = 'bx-transfer';

                        }

                        html += `

<tr>

<td>
    ${new Date(item.transaction_date).toLocaleDateString('id-ID')}
</td>

<td>

${item.transaction_number??'-'}

</td>

<td>

${item.description??'-'}

</td>

<td class="text-center text-success fw-semibold">
    ${item.qty_in > 0 ? ('+' + parseFloat(item.qty_in)) : '-'}
</td>

<td class="text-center text-danger fw-semibold">
    ${item.qty_out > 0 ? ('-' + parseFloat(item.qty_out)) : '-'}
</td>

<td class="text-center">
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
