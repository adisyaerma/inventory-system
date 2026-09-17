@extends('master')
@section('title', 'Riwayat Barang')
@section('content')

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Barang</h4>
            <small class="text-muted">
                <a href="{{ route('item-history.index') }}" class="text-decoration-none">History Item</a>
                / {{ $item->item_code_internal ?: $item->id }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('item-history.export', $item->id) }}"
                class="btn btn-sm bg-success bg-opacity-10 text-success rounded-3 border-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="currentColor"
                        d="M8.71 7.71L11 5.41V15a1 1 0 0 0 2 0V5.41l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-.33-.21a1 1 0 0 0-.76 0a1 1 0 0 0-.33.21l-4 4a1 1 0 1 0 1.42 1.42M21 14a1 1 0 0 0-1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-4a1 1 0 0 0-1-1" />
                </svg>
                <span class="ms-1">Export Laporan</span>
            </a>
            <a href="{{ route('item-history.index') }}" class="btn btn-sm btn border-secondary bg-white border">
                <i class="bi bi-arrow-left"></i>
                <span class="ms-1">Kembali</span>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $item->name ?: '-' }}</h5>
                    <div class="text-muted small mb-3">
                        {{ $item->item_code_internal ?: '-' }}
                        @if ($item->vendor)
                            &middot; Vendor: {{ $item->vendor->name }}
                        @endif
                    </div>

                    <h6 class="fw-bold small text-uppercase text-muted mb-2">Stok Per Lokasi</h6>
                    <div id="locationBreakdown" class="small text-muted">Memuat...</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box-history bg-primary-subtle text-primary me-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Stok Saat Ini</small>
                        <h4 class="fw-bold mb-0" id="totalQtyValue">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-0 fw-bold">Activity Timeline</h5>
            <small class="text-muted">Gabungan riwayat mutasi, staging in, dan staging out barang ini</small>
        </div>
        <div class="card-body">
            <div id="itemTimeline" class="history-timeline"></div>
            <div id="timelineEmpty" class="text-center text-muted py-5 d-none">
                <i class="bi bi-clock-history" style="font-size:2.5rem;"></i>
                <p class="mt-2 mb-0 small">Belum ada riwayat untuk barang ini.</p>
            </div>
        </div>
    </div>

    @push('script')
        <style>
            .icon-box-history {
                border-radius: 14px;
                width: 52px;
                height: 52px;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
            }

            .history-timeline {
                position: relative;
                padding-left: 28px;
            }

            .history-timeline::before {
                content: '';
                position: absolute;
                left: 9px;
                top: 4px;
                bottom: 4px;
                width: 2px;
                background: #e9ecef;
            }

            .timeline-item {
                position: relative;
                padding-bottom: 1.25rem;
            }

            .timeline-item:last-child {
                padding-bottom: 0;
            }

            .timeline-dot {
                position: absolute;
                left: -28px;
                top: 2px;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: .7rem;
            }

            .timeline-body {
                background: #f8f9fb;
                border: 1px solid #eceef2;
                border-radius: .6rem;
                padding: .6rem .8rem;
            }
        </style>

        <script>
            function formatDateOnly(value) {
                if (!value) return '-';
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                return d.toLocaleDateString('sv-SE');
            }

            const eventStyle = {
                mutation_in: {
                    color: 'success',
                    icon: 'bi-box-arrow-in-down'
                },
                mutation_out: {
                    color: 'danger',
                    icon: 'bi-box-arrow-up'
                },
                staging_in: {
                    color: 'info',
                    icon: 'bi-truck'
                },
                staging_out: {
                    color: 'warning',
                    icon: 'bi-truck-flatbed'
                },
            };

            function renderTimeline(timeline) {
                if (!timeline.length) {
                    $('#timelineEmpty').removeClass('d-none');
                    return;
                }

                let html = '';

                timeline.forEach(function(row) {
                    const style = eventStyle[row.type] || {
                        color: 'secondary',
                        icon: 'bi-dot'
                    };
                    const qtySign = (row.type === 'mutation_out' || row.type === 'staging_out') ? '-' : '+';

                    html += `
                        <div class="timeline-item">
                            <div class="timeline-dot bg-${style.color}-subtle text-${style.color}">
                                <i class="bi ${style.icon}"></i>
                            </div>
                            <div class="timeline-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="fw-semibold small text-${style.color}">${row.label}</span>
                                    <span class="text-muted" style="font-size:.72rem;">${formatDateOnly(row.date)}</span>
                                </div>
                                <div class="small mt-2">
                                    <div>Qty: <span class="fw-semibold">${qtySign}${row.qty ?? '-'}</span></div>
                                    ${row.location ? `<div>Lokasi: ${row.location}${row.lot ? ' (Lot: ' + row.lot + ')' : ''}</div>` : ''}
                                    ${row.reference ? `<div>No. Referensi: <span class="text-primary">${row.reference}</span></div>` : ''}
                                </div>
                                ${row.notes ? `<div class="small text-muted mt-1">${row.notes}</div>` : ''}
                            </div>
                        </div>
                    `;
                });

                $('#itemTimeline').html(html);
            }

            function renderLocations(locations) {
                if (!locations.length) {
                    $('#locationBreakdown').html('Belum ada data lokasi untuk barang ini.');
                    return;
                }

                let html = '<div class="d-flex flex-wrap gap-2">';

                locations.forEach(function(loc) {
                    html += `
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                            ${loc.location}${loc.lot ? ' &middot; Lot: ' + loc.lot : ''} (${loc.quantity})
                        </span>
                    `;
                });

                html += '</div>';

                $('#locationBreakdown').html(html);
            }

            $(document).ready(function() {
                $.get("{{ route('item-history.timeline', $item->id) }}", function(res) {
                    $('#totalQtyValue').text(
                        Number(res.item.total_qty).toLocaleString('id-ID')
                    );
                    renderLocations(res.locations);
                    renderTimeline(res.timeline);
                });
            });
        </script>
    @endpush

@endsection