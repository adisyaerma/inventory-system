<table>
    <tr>
        <td colspan="6" style="font-size:16px; font-weight:bold;">Laporan Riwayat Barang</td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <td style="font-weight:bold;">Item Code Internal</td>
        <td colspan="2">{{ $item->item_code_internal ?: '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Nama Barang</td>
        <td colspan="2">{{ $item->name ?: '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Vendor</td>
        <td colspan="2">{{ $item->vendor->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Total Stok Saat Ini</td>
        <td colspan="2">{{ number_format($totalQty, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Tanggal Export</td>
        <td colspan="2">{{ now()->format('d-m-Y H:i') }}</td>
    </tr>

    <tr><td></td></tr>

    <tr style="background-color:#f1f1f1; font-weight:bold;">
        <td>No</td>
        <td>Tanggal</td>
        <td>Jenis Aktivitas</td>
        <td>Qty</td>
        <td>Lokasi / Lot</td>
        <td>No. Referensi</td>
        <td>Keterangan</td>
    </tr>

    @forelse ($timeline as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['date'] ?? '-' }}</td>
            <td>{{ $row['label'] ?? '-' }}</td>
            <td>
                {{ in_array($row['type'], ['mutation_out', 'staging_out']) ? '-' : '+' }}{{ $row['qty'] ?? '-' }}
            </td>
            <td>
                {{ $row['location'] ?? '-' }}{{ !empty($row['lot']) ? ' (Lot: '.$row['lot'].')' : '' }}
            </td>
            <td>{{ $row['reference'] ?? '-' }}</td>
            <td>{{ $row['notes'] ?? '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7">Belum ada riwayat untuk barang ini.</td>
        </tr>
    @endforelse
</table>