<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $jenis === 'pelanggan' ? 'Pelanggan' : 'Dapur' }} - {{ $transaksi->kode_transaksi }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .struk-container {
            font-family: 'Courier New', monospace;
            width: 58mm;
            margin: auto;
            padding: 2mm;
            font-size: 11px;
            color: #000000;
            box-sizing: border-box;
        }
        .text-center {
            text-align: center;
        }
        .border-bottom-dashed {
            border-bottom: 1px dashed #000;
        }
        .border-bottom-solid {
            border-bottom: 2px solid #000;
        }
        .border-top-dashed {
            border-top: 1px dashed #000;
        }
        .border-top-solid {
            border-top: 2px solid #000;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        .margin-bottom-sm {
            margin-bottom: 4px;
        }
        .margin-bottom-md {
            margin-bottom: 8px;
        }
        .margin-bottom-lg {
            margin-bottom: 10px;
        }
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
            body {
                background-color: #ffffff;
                margin: 0;
            }
            .struk-container {
                width: 58mm;
                padding: 2mm;
            }
        }
    </style>
</head>
<body>

<div class="struk-container">
    @if ($jenis === 'pelanggan')
        @php
            $toko = 'Saisho Cake & Bakery';
            $alamat = 'Jl. Kemboja No.1, Kedungjaya, Kec. Kedawung, Kabupaten Cirebon';
            $subtotal = (float) $transaksi->subtotal;
            $diskon = (float) $transaksi->diskon;
            $diskonPersen = $subtotal > 0 ? round(($diskon / $subtotal) * 100) : 0;
            $pajak = (float) $transaksi->pajak;
            $pajakPersen = ($subtotal - $diskon) > 0 ? round(($pajak / ($subtotal - $diskon)) * 100) : 0;
        @endphp
        <div class="text-center border-bottom-dashed" style="padding-bottom:10px; margin-bottom:10px;">
            <h2 style="margin:0; font-size:16px;">{{ $toko }}</h2>
            <p style="margin:4px 0 0 0; font-size:11px;">{{ $alamat }}</p>
        </div>
        <div class="margin-bottom-md" style="font-size:11px;">
            <div>No: <strong>{{ $transaksi->kode_transaksi }}</strong></div>
            <div>Tgl: {{ $transaksi->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
            <div>Pelanggan: <strong>{{ $transaksi->nama_pembeli ?? 'Pelanggan Umum' }}</strong></div>
            <div style="text-transform: capitalize;">Bayar: {{ $transaksi->metode_pembayaran }}</div>
        </div>
        <div class="border-top-dashed border-bottom-dashed" style="padding:8px 0;">
            @foreach ($transaksi->detailTransaksi as $index => $item)
                <div class="flex-between margin-bottom-sm">
                    <span>{{ $index + 1 }}. {{ $item->nama_produk }}</span>
                </div>
                @if ($item->catatan)
                    <div style="padding-left:16px; font-style:italic; color:#666; font-size:10px; margin-bottom:4px;">* Catatan: {{ $item->catatan }}</div>
                @endif
                <div class="flex-between" style="margin-bottom:6px; padding-left:16px; color:#555;">
                    <span>{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-bottom-dashed" style="padding:8px 0;">
            <div class="flex-between">
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if ($diskon > 0)
                <div class="flex-between">
                    <span>Diskon ({{ $diskonPersen }}%)</span>
                    <span>-Rp {{ number_format($diskon, 0, ',', '.') }}</span>
                </div>
            @endif
            @if ($pajak > 0)
                <div class="flex-between">
                    <span>Pajak ({{ $pajakPersen }}%)</span>
                    <span>Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex-between" style="font-weight:bold; font-size:14px; margin-top:6px;">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="border-bottom-dashed" style="padding:8px 0;">
            <div class="flex-between">
                <span>Diterima</span>
                <span>Rp {{ number_format($transaksi->jumlah_diterima, 0, ',', '.') }}</span>
            </div>
            <div class="flex-between" style="font-weight:bold;">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="text-center" style="padding-top:12px; font-size:11px; color:#666;">
            <p style="margin:0;">Terima kasih sudah mampir! 🌷</p>
            <p style="margin:4px 0 0 0;">Semoga harimu semanis pastry kami</p>
            <p style="margin:0;">( ˶ˆᗜˆ˵ ) ♡</p>
        </div>
    @elseif ($jenis === 'dapur')
        @php
            if ($transaksi->status_pembayaran === 'Belum Lunas') {
                $itemsDapur = $transaksi->detailTransaksi;
            } else {
                $itemsDapur = $transaksi->detailTransaksi->filter(function ($item) {
                    return $item->produk && $item->produk->status_ketersediaan === 'pesan_dulu';
                });
            }
        @endphp
        <div class="text-center border-bottom-solid" style="padding-bottom:10px; margin-bottom:10px;">
            <h2 style="margin:0; font-size:18px; letter-spacing:2px;">🍳 ORDER DAPUR</h2>
        </div>
        <div class="margin-bottom-lg" style="font-size:12px;">
            <div>No: <strong>{{ $transaksi->kode_transaksi }}</strong></div>
            <div>Tgl: {{ $transaksi->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
            <div>Pelanggan: <strong style="font-size:14px;">{{ $transaksi->nama_pembeli ?? 'Pelanggan Umum' }}</strong></div>
        </div>
        <div class="border-top-solid border-bottom-solid" style="padding:10px 0;">
            @forelse ($itemsDapur as $index => $item)
                <div class="flex-between" style="align-items:center; margin-bottom:8px; font-size:14px;">
                    <span><strong>{{ $index + 1 }}. {{ $item->nama_produk }}</strong></span>
                    <span style="background:#000; color:#fff; padding:2px 10px; border-radius:4px; font-weight:bold; font-size:16px;">x{{ $item->jumlah }}</span>
                </div>
                @if ($item->catatan)
                    <div style="padding-left:16px; font-style:italic; color:#555; font-size:11px; margin-top:-6px; margin-bottom:8px;">* Catatan: {{ $item->catatan }}</div>
                @endif
            @empty
                <div class="text-center" style="color:#666; font-style:italic;">Tidak ada item untuk dapur</div>
            @endforelse
        </div>
        @if ($transaksi->pesananDitahan && $transaksi->pesananDitahan->catatan)
            <div style="padding:8px 0; border-bottom:1px dashed #000;">
                <strong>📝 Catatan:</strong> {{ $transaksi->pesananDitahan->catatan }}
            </div>
        @endif
        <div class="text-center" style="padding-top:12px; font-size:11px; color:#666;">
            <p style="margin:0;">Dicetak: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    console.log(typeof Android);

    if(window.Android){
        console.log("Android bridge ketemu");

        let printers = Android.getPairedPrinters();

        console.log(printers);
    }

});
</script>


<script>
window.onload = function() {
    const params = new URLSearchParams(window.location.search);

    if(params.get('print') === 'true'){
        window.print();
    }
}
</script>

</body>
</html>
