<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        table th,
        table td{
            border:1px solid #000;
            padding:6px;
        }

        h2{
            text-align:center;
        }
    </style>
</head>
<body>

<h2>Laporan Produksi</h2>

<table>

<thead>

<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Produk</th>
    <th>Jumlah</th>
</tr>

</thead>

<tbody>

@foreach($produksi as $item)

<tr>

    <td>{{ $loop->iteration }}</td>

    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>

    <td>{{ $item->produk->nama_produk }}</td>

    <td>{{ $item->jumlah_hasil }}</td>

</tr>

@endforeach

</tbody>

</table>

</body>
</html>