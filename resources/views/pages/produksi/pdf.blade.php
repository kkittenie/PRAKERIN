<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi</title>
    <style>
        @page { margin: 25px 30px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #b45309;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #b45309;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #777;
        }

        .meta {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
            color: #555;
        }
        .meta .left { display: table-cell; text-align: left; }
        .meta .right { display: table-cell; text-align: right; }

        .card {
            border: 1px solid #d4a373;
            border-radius: 4px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .card-title {
            background: #fff7ed;
            padding: 6px 10px;
            font-weight: bold;
            border-bottom: 1px solid #d4a373;
            font-size: 12px;
            color: #92400e;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 10px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .info-table .label {
            color: #888;
            width: 110px;
        }

        .badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            padding: 1px 8px;
            border-radius: 10px;
            font-weight: bold;
        }

        table.bahan {
            width: calc(100% - 20px);
            margin: 6px 10px 10px 10px;
            border-collapse: collapse;
        }
        table.bahan th, table.bahan td {
            border: 1px solid #e5c9a3;
            padding: 4px 8px;
            font-size: 10px;
        }
        table.bahan th {
            background: #fef3e2;
            color: #92400e;
            text-align: left;
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
            bottom: -15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
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
            @if($periode === 'harian')
                Periode: Per Hari &mdash; {{ $tanggalRef->translatedFormat('d F Y') }}
            @elseif($periode === 'mingguan')
                Periode: Per Minggu &mdash; {{ $tanggalRef->copy()->startOfWeek()->format('d-m-Y') }} s/d {{ $tanggalRef->copy()->endOfWeek()->format('d-m-Y') }}
            @else
                Periode: Per Bulan &mdash; {{ $tanggalRef->translatedFormat('F Y') }}
            @endif
        </div>
        <div class="right">
            Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}
        </div>
    </div>

    @forelse ($produksi as $item)
    <div class="card">
        <div class="card-title">
            {{ $loop->iteration }}. {{ $item->produk->nama_produk ?? '-' }}
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Tanggal Produksi</td>
                <td>: {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                <td class="label">Jumlah Hasil</td>
                <td>: <span class="badge">{{ $item->jumlah_hasil }} pcs</span></td>
            </tr>
            <tr>
                <td class="label">Jam Produksi</td>
                <td>: {{ $item->jam_produksi ? \Carbon\Carbon::parse($item->jam_produksi)->format('H:i') : '-' }}</td>
                <td class="label">Dibuat Oleh</td>
                <td>: {{ $item->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kadaluarsa</td>
                <td>: {{ $item->kadaluarsa_label }}</td>
                <td class="label">Keterangan</td>
                <td>: {{ $item->keterangan ?: '-' }}</td>
            </tr>
        </table>

        <table class="bahan">
            <thead>
                <tr>
                    <th style="width:35px;">No</th>
                    <th>Nama Bahan</th>
                    <th style="width:140px;">Jumlah Dipakai</th>
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
    <p class="empty">Belum ada data produksi untuk periode ini.</p>
    @endforelse

    <div class="footer">
        Laporan ini dihasilkan otomatis oleh sistem Saisho POS
    </div>

</body>
</html>