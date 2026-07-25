@extends('master')
@section('title', 'Vendor')
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-start">
                <h4 class="mb-0">Vendor</h4>
                <small class="text-muted">Kelola data vendor</small>
            </div>
            <div class="float-end mt-3">
                <button data-bs-toggle="modal" data-bs-target="#addVendor" type="button" class="btn btn-primary btn-sm">
                    <svg class="me-1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"
                        viewBox="0 0 24 24">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                    </svg>
                    Tambah
                </button>
                <div class="modal fade" id="addVendor" tabindex="-1">

                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content">

                            <form action="{{ route('vendors.store') }}" method="POST">

                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        Tambah Vendor <br>
                                        <small class="fw-light">Tambahkan vendor baru</small>
                                    </h5>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>
                                </div>

                                <div class="modal-body">

                                    {{-- Nama Vendor --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Nama Vendor
                                        </label>

                                        <input type="text" name="name" class="form-control"
                                            placeholder="Contoh: PT. ABC" required>
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
           

            <table class="table table-bordered" id="location">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Vendor</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($vendors as $vendor)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->description ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnScanEdit" data-bs-toggle="modal"
                                        data-bs-target="#editVendorModal{{ $vendor->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" class="fs-5"
                                            height="1em" viewBox="0 0 24 24">
                                            <path d="M0 0h24v24H0z" fill="none" />
                                            <path fill="currentColor"
                                                d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST"
                                        class="form-hapus m-0">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0">
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
            <div class="mb-3 ms-4 d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2" id="tableFooter">
                    <div class="d-flex align-items-center gap-2" id="lengthWrapper">
                        <span>Tampilkan</span>
                        <select id="customLength" class="fonrm-select form-select-sm" style="width:80px">
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

    @foreach ($vendors as $vendor)
        <div class="modal fade" id="editVendorModal{{ $vendor->id }}" tabindex="-1">

            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">

                    <form action="{{ route('vendors.update', $vendor->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                Edit Vendor <br>
                                <small class="fw-light">Ubah data vendor</small>
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Vendor</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ $vendor->name }}" placeholder="Contoh: Rak A1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>

                                <textarea name="description" class="form-control" rows="3" placeholder="Opsional">{{ $vendor->description }}</textarea>
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

                    dom: 'rtip',
                    pageLength: 10,

                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ]
                });

                $('#customSearch').on('input', function() {
                    table.search(this.value).draw();
                    updateExportUrl();
                });

                $('#customLength').change(function() {

                    table.page.len($(this).val()).draw();

                })

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