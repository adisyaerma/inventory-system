@extends('master')
@section('title', 'Lokasi')
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-start">
                <h4 class="mb-0">Lokasi Rak</h4>
                <small class="text-muted">Kelola data lokasi rak penyimpanan barang</small>
            </div>
            <div class="float-end mt-3">
                <button data-bs-toggle="modal" data-bs-target="#addLocationModal" type="button" class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>
                <div class="modal fade" id="addLocationModal" tabindex="-1">

                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content">

                            <form action="{{ route('locations.store') }}" method="POST">

                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        Tambah Lokasi Rak <br>
                                        <small class="fw-light">Tambahkan lokasi rak baru untuk penyimpanan barang</small>
                                    </h5>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>
                                </div>

                                <div class="modal-body">

                                    {{-- Nama Rak --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Nama Rak
                                        </label>

                                        <input type="text" name="location_name" class="form-control"
                                            placeholder="Contoh: Rak A1" required>
                                    </div>

                                    {{-- Barcode --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Barcode Rak
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
                                                Arahkan kamera ke barcode rak
                                            </div>
                                            <div class="small mt-1 fw-bold">atau</div>
                                            <input type="text" id="location_code" name="location_code"
                                                class="form-control form-control-sm mt-2"
                                                placeholder="Masukkan kode barcode secara manual">

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

                                    <button type="submit" class="btn btn-primary">
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
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <div class="container-fluid px-4">
                <div class="top-row d-flex justify-content-lg-start justify-content-center mb-3">

                    <div class="d-flex flex-wrap justify-content-lg-start justify-content-center gap-2">

                        <select id="filterStatus" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>

                        <button id="btnResetFilter"
                            class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">

                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 21 21">

                                <path d="M0 0h21v21H0z" fill="none" />

                                <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path d="M3.578 6.487A8 8 0 1 1 2.5 10.5" />
                                    <path d="M7.5 6.5h-4v-4" />

                                </g>

                            </svg>

                            Reset Filter

                        </button>

                    </div>

                </div>
            </div>

            <table class="table table-bordered" id="location">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Rak</th>
                        <th>Barcode Rak</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($locations as $location)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $location->location_name }}</td>
                            <td>
                                @if (empty($location->location_code))
                                    <span class="badge border border-warning text-warning bg-transparent"
                                        style="font-size:10px;">
                                        ⚠ No Code
                                    </span>
                                @else
                                    <div class="d-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fs-4" width="1em"
                                            height="1em" viewBox="0 0 24 24">
                                            <path d="M0 0h24v24H0z" fill="none" />
                                            <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5"
                                                d="M6 22H4.4A2.4 2.4 0 0 1 2 19.6V18m16 4h1.6a2.4 2.4 0 0 0 2.4-2.4V18m0-12V4.4A2.4 2.4 0 0 0 19.6 2H18M6 2H4.4A2.4 2.4 0 0 0 2 4.4V6m16 3v6m-4-6v6m-4-6v6M6 9v6" />
                                        </svg>

                                        <span>{{ $location->location_code }}</span>


                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($location->status)
                                    <span class="badge bg-label-success rounded-pill" data-search="Aktif">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary rounded-pill" data-search="Non Aktif">
                                        Non Aktif
                                    </span>
                                @endif
                            </td>
                            <td>{{ $location->description ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-sm btn-outline-warning btnScanEdit" data-bs-toggle="modal"
                                        data-bs-target="#editLocationModal{{ $location->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" class="fs-5"
                                            height="1em" viewBox="0 0 24 24">
                                            <path d="M0 0h24v24H0z" fill="none" />
                                            <path fill="currentColor"
                                                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('locations.destroy', $location->id) }}" method="POST"
                                        class="form-hapus m-0">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" class="fs-5"
                                                height="1em" viewBox="0 0 24 24">
                                                <path d="M0 0h24v24H0z" fill="none" />
                                                <path fill="currentColor"
                                                    d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($locations as $location)
        <div class="modal fade" id="editLocationModal{{ $location->id }}" tabindex="-1">

            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">

                    <form action="{{ route('locations.update', $location->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                Edit Lokasi Rak <br>
                                <small class="fw-light">Ubah data lokasi rak penyimpanan barang</small>
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Rak</label>
                                <input type="text" name="location_name" class="form-control"
                                    value="{{ $location->location_name }}" placeholder="Contoh: Rak A1" required>
                            </div>

                            <div class="mb-3">

                                <label class="form-label fw-bold">Barcode Rak</label>

                                <div class="border rounded p-4 text-center">

                                    <button type="button" class="btn btn-outline-secondary rounded btn-scan-edit"
                                        data-id="{{ $location->id }}">

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
                                        Arahkan kamera ke barcode rak
                                    </div>

                                    <div class="small mt-1 fw-bold">atau</div>

                                    <input type="text" name="location_code" id="location_code_{{ $location->id }}"
                                        class="form-control form-control-sm mt-2" value="{{ $location->location_code }}"
                                        placeholder="Masukkan kode barcode secara manual">

                                </div>

                            </div>

                            <div class="mb-3 scanner-container-edit" id="scanner_container_{{ $location->id }}"
                                style="display:none;">
                                <div id="reader_{{ $location->id }}"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>

                                <div class="d-flex gap-4">

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" value="1"
                                            {{ $location->status == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label">Aktif</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" value="0"
                                            {{ $location->status == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label">Non Aktif</label>
                                    </div>

                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>

                                <textarea name="description" class="form-control" rows="3" placeholder="Opsional">{{ $location->description }}</textarea>
                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>

                            <button type="submit" class="btn btn-primary">
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
    @endforeach

    @push('script')
        <script>
            $(document).ready(function() {

                let table = $('#location').DataTable({
                    dom: '<"dt-top d-flex justify-content-between align-items-center flex-wrap mb-2"lf>rtip',
                    scrollX: true,

                    pageLength: 10,

                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ]
                });

                $.fn.dataTable.ext.search.push(function(settings, data) {

                    if (settings.nTable.id !== 'location') {
                        return true;
                    }

                    let selected = $('#filterStatus').val();

                    let status = $('<div>')
                        .html(data[3])
                        .text()
                        .trim();

                    if (selected === '') {
                        return true;
                    }

                    return status === selected;
                });

                $('#btnResetFilter').on('click', function() {

                    $('#filterStatus').val('');

                    table.search('').draw();

                    $('.dt-search input').val('');
                });

                $('#filterStatus').on('change', function() {
                    table.draw();
                });

            });
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

            #customSearch {
                padding-left: 42px;
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

        <style>
            #location_wrapper {
                padding: 1rem;
            }

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
        </style>


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

        <script>
            let html5QrCodeEdit = null;
            let isProcessingEdit = false;

            document.querySelectorAll('.btn-scan-edit').forEach(button => {

                button.addEventListener('click', function() {

                    const id = this.dataset.id;

                    const input = document.getElementById('location_code_' + id);
                    const container = document.getElementById('scanner_container_' + id);

                    if (!input || !container) {
                        console.error('Element tidak ditemukan');
                        return;
                    }

                    container.style.display = 'block';

                    setTimeout(() => {

                        if (html5QrCodeEdit) return;

                        html5QrCodeEdit = new Html5Qrcode('reader_' + id);

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
                                                document.querySelector(
                                                        '.swal2-container').style
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
                                                document.querySelector(
                                                        '.swal2-container').style
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
            });


            document.querySelectorAll('[id^="editLocationModal"]').forEach(modal => {

                modal.addEventListener('hidden.bs.modal', async () => {

                    if (html5QrCodeEdit) {
                        try {
                            await html5QrCodeEdit.stop();
                        } catch (e) {}

                        html5QrCodeEdit = null;
                    }

                    isProcessingEdit = false;

                    modal.querySelectorAll('.scanner-container-edit').forEach(el => {
                        el.style.display = 'none';
                    });
                });

            });
        </script>

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

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.querySelectorAll('.form-hapus').forEach(form => {

                    form.addEventListener('submit', function(e) {

                        e.preventDefault();

                        Swal.fire({
                            title: 'Hapus Data?',
                            text: 'Data yang dihapus tidak dapat dikembalikan.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Hapus',
                            confirmButtonColor: 'red',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {

                            if (result.isConfirmed) {
                                form.submit();
                            }

                        });

                    });

                });

            });
        </script>
    @endpush
@endsection
