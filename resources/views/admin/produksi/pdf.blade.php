<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi</title>
    <style>
        @page { margin: 22px 28px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #b45309;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 17px;
            margin: 0;
            color: #b45309;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #777;
        }

        .meta {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #555;
        }
        .meta .left { display: table-cell; text-align: left; }
        .meta .right { display: table-cell; text-align: right; }

        .card {
            border: 1px solid #d4a373;
            border-radius: 4px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .card-title {
            display: table;
            width: 100%;
            background: #fff7ed;
            padding: 5px 10px;
            border-bottom: 1px solid #d4a373;
            font-size: 11px;
            color: #92400e;
        }
        .card-title .nama {
            display: table-cell;
            font-weight: bold;
        }
        .card-title .tanggal {
            display: table-cell;
            text-align: right;
            font-weight: normal;
            font-size: 9.5px;
            color: #a16207;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #f1e0c8;
        }
        .info-table td {
            padding: 3px 10px;
            vertical-align: top;
            font-size: 9.5px;
            white-space: nowrap;
        }
        .info-table .label {
            color: #999;
            width: 1%;
            padding-right: 4px;
        }
        .info-table .val {
            width: 32%;
        }
        .info-table .val.wrap {
            white-space: normal;
        }

        table.bahan {
            width: calc(100% - 20px);
            margin: 6px 10px 8px 10px;
            border-collapse: collapse;
        }
        table.bahan th, table.bahan td {
            border: 1px solid #e5c9a3;
            padding: 3px 7px;
            font-size: 9.5px;
        }
        table.bahan th {
            background: #fef3e2;
            color: #92400e;
            text-align: left;
        }
        table.bahan tbody tr:nth-child(even) td {
            background: #fffaf3;
        }
        table.bahan td.center { text-align: center; }
        table.bahan td.right { text-align: right; }

        .empty {
            text-align: center;
            color: #999;
            padding: 30px 0;
        }

        .footer {
            position: fixed;
            bottom: -14px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #999;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Produksi</h1>
        <p>Saisho POS</p>
    </div>

    <div class="meta">
        <div class="left">
            @if($periode === 'semua')
                Periode: Semua Data
            @elseif($periode === 'harian')
                Periode: Per Hari &mdash; {{ $tanggalRef->translatedFormat('d F Y') }}
            @elseif($periode === 'mingguan')
                Periode: Per Minggu &mdash; {{ $tanggalRef->copy()->startOfWeek()->format('d-m-Y') }} s/d {{ $tanggalRef->copy()->endOfWeek()->format('d-m-Y') }}
            @else
                Periode: Per Bulan &mdash; {{ $tanggalRef->translatedFormat('F Y') }}
            @endif
            @php
                $statusLabel = ['kadaluarsa' => 'Kadaluwarsa', 'aktif' => 'Belum Kadaluwarsa'][request('status')] ?? null;
                $kataKunci = is_array(request('search')) ? (request('search')['value'] ?? null) : request('search');
            @endphp
            @if($statusLabel) | Status: {{ $statusLabel }} @endif
            @if($kataKunci) | Pencarian: "{{ $kataKunci }}" @endif
        </div>
        <div class="right">
            Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    @forelse ($produksi as $item)
    <div class="card">
        <div class="card-title">
            <span class="nama">{{ $loop->iteration }}. {{ $item->produk->nama_produk ?? '-' }}</span>
            <span class="tanggal">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }} &bull; {{ $item->jam_produksi ? \Carbon\Carbon::parse($item->jam_produksi)->format('H:i') : '-' }}</span>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Jumlah Hasil</td>
                <td class="val">{{ $item->jumlah_hasil }} pcs</td>
                <td class="label">Pesanan</td>
                <td class="val">{{ $item->kebutuhan_produksi ?: 0 }} pcs</td>
                <td class="label">Kadaluwarsa</td>
                <td class="val">{{ $item->kadaluarsa_label }}</td>
            </tr>
            <tr>
                <td class="label">Rincian Ukuran</td>
                <td class="val" colspan="5">Hasil: {{ $item->rincian_ukuran }} &bull; Pesanan: {{ $item->rincian_kebutuhan_ukuran }}@if($item->punya_rincian_rasa)<br>Rasa: {{ $item->rincian_rasa }}@endif</td>
            </tr>
            <tr>
                <td class="label">Dibuat Oleh</td>
                <td class="val" colspan="3">{{ $item->user->name ?? '-' }}</td>
                <td class="label">Keterangan</td>
                <td class="val wrap">{{ $item->keterangan ?: '-' }}</td>
            </tr>
        </table>

        <table class="bahan">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th>Nama Bahan</th>
                    <th style="width:120px;">Jumlah Dipakai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($item->detailBahan as $bahan)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $bahan->bahanBaku->nama_bahan ?? '-' }}</td>
                    <td class="right">{{ $bahan->jumlah_terpakai }} {{ $bahan->bahanBaku->satuan->nama ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="center" style="color:#999;">Tidak ada data bahan baku.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @empty
    <p class="empty">Belum ada data produksi.</p>
    @endforelse

    <div class="footer">
        Laporan ini dihasilkan otomatis oleh sistem Daisy POS
    </div>

</body>
</html>