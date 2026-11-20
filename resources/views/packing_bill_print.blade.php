<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bill Packing {{ $bill->so_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $rp = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $q = fn($n) => rtrim(rtrim(number_format((float) $n, 3, ',', '.'), '0'), ',');
    @endphp
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 2rem;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 13px;
            color: #2b3350;
            background: #f5f6fb;
        }

        .sheet {
            max-width: 820px;
            margin: 0 auto;
            padding: 2.2rem 2.4rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px -12px rgba(43, 51, 80, .3);
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1rem;
            border-bottom: 3px solid #4f46e5;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: -.01em;
        }

        .muted {
            color: #8a93a8;
        }

        .meta {
            margin: 1.2rem 0;
            display: grid;
            grid-template-columns: 130px 1fr;
            row-gap: .35rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: .6rem .6rem;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: left;
            color: #fff;
            background: #1f3a5f;
        }

        td {
            padding: .6rem;
            border-bottom: 1px solid #e6e9f2;
            vertical-align: top;
        }

        .r {
            text-align: right;
        }

        .c {
            text-align: center;
        }

        tr.grp td {
            font-weight: 700;
            color: #4f46e5;
            background: #eef0ff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .isi {
            font-weight: 400;
            color: #6b7390;
        }

        tr.sub td {
            font-weight: 700;
            background: #fafbff;
        }

        tr.total td {
            font-size: 15px;
            font-weight: 800;
            border-bottom: 0;
            border-top: 2px solid #1f3a5f;
            background: #eef0ff;
        }

        .actions {
            max-width: 820px;
            margin: 0 auto 1rem;
            text-align: right;
        }

        .actions button {
            padding: .5rem 1.1rem;
            border: 0;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            background: #4f46e5;
            cursor: pointer;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .sheet {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }

            .actions {
                display: none;
            }

            th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr.total td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="actions"><button onclick="window.print()">Cetak / Simpan PDF</button></div>

    <div class="sheet">
        <div class="head">
            <div>
                <h1>Rincian Biaya Packing</h1>
                <div class="muted">Material yang digunakan untuk packing</div>
            </div>
            <div class="r">
                <div class="muted">No. SO</div>
                <div style="font-size:18px;font-weight:700">{{ $bill->so_number }}</div>
            </div>
        </div>

        <div class="meta">
            <div class="muted">Customer</div>
            <div>{{ $bill->customer ?: '-' }}</div>
            <div class="muted">Tanggal</div>
            <div>{{ optional($bill->tanggal)->format('d M Y') }}</div>
        </div>

        @php
            $kolis = $bill->kolis;
            $multi = $kolis->count() > 1;
            $no = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width:36px" class="c">No</th>
                    <th>Material</th>
                    <th class="r">Qty</th>
                    <th>Satuan</th>
                    <th class="r">Harga</th>
                    <th class="r">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kolis as $koli)
                    @if ($multi)
                        <tr class="grp">
                            <td colspan="6">
                                Koli {{ $loop->iteration }}
                                @if ($koli->items->isNotEmpty())
                                    <span class="isi">- isi: {{ $koli->items->pluck('item_label')->filter()->implode('; ') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                    @foreach ($koli->lines as $line)
                        <tr>
                            <td class="c">{{ ++$no }}</td>
                            <td>{{ $line->nama_material }}</td>
                            <td class="r">{{ $q($line->qty) }}</td>
                            <td>{{ $line->satuan }}</td>
                            <td class="r">{{ $rp($line->harga) }}</td>
                            <td class="r">{{ $rp($line->subtotal) }}</td>
                        </tr>
                    @endforeach
                    @if ($multi)
                        <tr class="sub">
                            <td colspan="5" class="r">Subtotal Koli {{ $loop->iteration }}</td>
                            <td class="r">{{ $rp($koli->lines->sum('subtotal')) }}</td>
                        </tr>
                    @endif
                @endforeach

                @if ($bill->sharedLines->isNotEmpty())
                    <tr class="grp">
                        <td colspan="6">Material Bersama <span class="isi">- dipakai semua koli</span></td>
                    </tr>
                    @foreach ($bill->sharedLines as $line)
                        <tr>
                            <td class="c">{{ ++$no }}</td>
                            <td>{{ $line->nama_material }}</td>
                            <td class="r">{{ $q($line->qty) }}</td>
                            <td>{{ $line->satuan }}</td>
                            <td class="r">{{ $rp($line->harga) }}</td>
                            <td class="r">{{ $rp($line->subtotal) }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub">
                        <td colspan="5" class="r">Subtotal Material Bersama</td>
                        <td class="r">{{ $rp($bill->sharedLines->sum('subtotal')) }}</td>
                    </tr>
                @endif

                <tr class="total">
                    <td colspan="5" class="r">TOTAL BIAYA PACKING</td>
                    <td class="r">{{ $rp($bill->total) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>