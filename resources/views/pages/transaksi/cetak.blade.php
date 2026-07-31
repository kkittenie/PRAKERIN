<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $jenis === 'pelanggan' ? 'Pelanggan' : 'Dapur' }} - {{ $transaksi->kode_transaksi }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigtoin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --primary: #d97706;
            --primary-hover: #b45309;
        }

        body {
            margin: 0;
            padding: 32px 16px;
            background-color: var(--bg-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Action Bar Component */
        .action-bar {
            width: 100%;
            max-width: 340px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-decoration: none;
            outline: none;
            box-sizing: border-box;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);
            flex: 1.2;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b45309 0%, #92400e 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            flex: 1;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }

        .btn-secondary:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-close {
            background: transparent;
            color: var(--text-muted);
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            width: 100%;
        }

        .btn-close:hover {
            color: #0f172a;
            background: rgba(0, 0, 0, 0.04);
        }

        /* Struk Receipt Card Container */
        .struk-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            padding: 24px 20px;
            width: 100%;
            max-width: 340px;
            box-sizing: border-box;
        }

        .struk-container {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            width: 58mm;
            margin: 0 auto;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
            color: #000000;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .border-bottom-dashed {
            border-bottom: 1px dashed #000000;
        }

        .border-bottom-solid {
            border-bottom: 2px solid #000000;
        }

        .border-top-dashed {
            border-top: 1px dashed #000000;
        }

        .border-top-solid {
            border-top: 2px solid #000000;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .item-row .item-name {
            flex: 1;
            min-width: 0;
            word-break: break-word;
            padding-left: 16px;
            text-indent: -16px;
        }

        .item-row .item-qty {
            flex-shrink: 0;
            white-space: nowrap;
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

        .label-produksi {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            border: 1px solid #000;
            padding: 0 3px;
            margin-left: 4px;
            border-radius: 3px;
        }

        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }

            .struk-card {
                border: none;
                box-shadow: none;
                padding: 0;
                width: 58mm;
                border-radius: 0;
            }

            .struk-container {
                width: 58mm;
                padding: 2mm;
            }

            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Action Bar (Hidden on print) -->
    <div class="action-bar">
        <div class="btn-group">
            <button class="btn btn-primary" onclick="printStruk()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak</span>
            </button>

            <button class="btn btn-secondary" onclick="window.print()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh PDF</span>
            </button>
        </div>
        <button class="btn btn-close" onclick="window.close(); setTimeout(() => history.back(), 100);">Tutup
            Halaman</button>
    </div>

    <!-- Receipt Card Container -->
    <div class="struk-card">
        <div class="struk-container">
            @if ($jenis === 'pelanggan')
                @php
                    $toko = '';
                    $alamat = 'Jl. Kemboja No.1, Kedungjaya, Kec. Kedawung, Kabupaten Cirebon';
                    $subtotal = (float) $transaksi->subtotal;
                    $diskon = (float) $transaksi->diskon;
                    $diskonPersen = $subtotal > 0 ? round(($diskon / $subtotal) * 100) : 0;
                    $pajak = (float) $transaksi->pajak;
                    $pajakPersen = ($subtotal - $diskon) > 0 ? round(($pajak / ($subtotal - $diskon)) * 100) : 0;

                    // Gabungkan item dengan nama produk yang sama supaya tidak
                    // tercetak sebagai baris terpisah (mis. akibat sebagian
                    // butuh produksi & sebagian ready stok saat transaksi dibuat).
                    $itemsPelanggan = $transaksi->detailTransaksi
                        ->groupBy('nama_produk')
                        ->map(function ($group) {
                            $first = $group->first();
                            return (object) [
                                'nama_produk' => $first->nama_produk,
                                'harga_satuan' => $first->harga_satuan,
                                'jumlah' => $group->sum('jumlah'),
                                'subtotal' => $group->sum('subtotal'),
                                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                            ];
                        })
                        ->values();
                @endphp
                <div class="text-center border-bottom-dashed" style="padding-bottom:10px; margin-bottom:10px;">
                    <img src="{{ asset('images/logo/B&W-logo.svg') }}" alt="Logo"
                        style="width:150px; height:150px; object-fit:contain; margin:-45px auto 5px auto;">

                    <h2 style="margin:-50px 0 0 0; font-size:15px; font-weight:700;">
                        {{ $toko }}
                    </h2>

                    <p style="margin:4px 0 0 0; font-size:10px;">
                        {{ $alamat }}
                    </p>
                </div>
                <div class="margin-bottom-md" style="font-size:11px;">
                    <div>No: <strong>{{ $transaksi->kode_transaksi }}</strong></div>
                    <div>Tgl: {{ $transaksi->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                    <div>Pelanggan: <strong>{{ $transaksi->nama_pembeli ?? 'Pelanggan Umum' }}</strong></div>
                    <div style="text-transform: capitalize;">Bayar: {{ $transaksi->metode_pembayaran }}</div>
                </div>
                <div class="border-top-dashed border-bottom-dashed" style="padding:8px 0;">
                    @foreach ($itemsPelanggan as $index => $item)
                        <div class="item-row margin-bottom-sm">
                            <span class="item-name">
                                {{ $index + 1 }}. {{ $item->nama_produk }}
                            </span>
                        </div>
                        @if ($item->catatan)
                            <div style="padding-left:16px; font-style:italic; color:#666; font-size:10px; margin-bottom:4px;">*
                                Catatan: {{ $item->catatan }}</div>
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
                    <p style="margin:0;">Sampai ketemu di pesanan berikutnya! ^^</p>
                </div>
            @elseif ($jenis === 'dapur')
                @php
                    $itemsDapur = $transaksi->detailTransaksi->filter(function ($item) {
                        return (bool) $item->butuh_produksi;
                    })->values();
                @endphp
                <div class="text-center border-bottom-dashed" style="padding-bottom:10px; margin-bottom:10px;">
                    <h2 style="margin:0; font-size:16px; font-weight:700;">SAISHO CAKE & BAKERY</h2>
                    <p style="margin:4px 0 0 0; font-size:12px; font-weight:700; letter-spacing:1px;">TIKET PRODUKSI</p>
                </div>
                <div class="margin-bottom-md" style="font-size:11px;">
                    <div>No: <strong>{{ $transaksi->kode_transaksi }}</strong></div>
                    <div>Tgl: {{ $transaksi->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                    <div>Pelanggan: <strong>{{ $transaksi->nama_pembeli ?? 'Pelanggan Umum' }}</strong></div>
                </div>
                <div class="border-top-dashed border-bottom-dashed" style="padding:8px 0;">
                    @forelse ($itemsDapur as $index => $item)
                        <div class="item-row margin-bottom-sm" style="font-size:12px;">
                            <span class="item-name"><strong>{{ $index + 1 }}. {{ $item->nama_produk }}</strong></span>
                            <span class="item-qty" style="font-weight:bold;">x{{ $item->jumlah }}</span>
                        </div>
                        @if ($item->catatan)
                            <div style="padding-left:16px; font-style:italic; color:#555; font-size:10px; margin-bottom:4px;">*
                                Catatan: {{ $item->catatan }}</div>
                        @endif
                    @empty
                        <div class="text-center" style="color:#666; font-style:italic;">Tidak ada item untuk dapur</div>
                    @endforelse
                </div>
                @if ($transaksi->pesananDitahan && $transaksi->pesananDitahan->catatan)
                    <div style="padding:8px 0; border-bottom:1px dashed #000;">
                        <strong>📝 Catatan Pesanan:</strong> {{ $transaksi->pesananDitahan->catatan }}
                    </div>
                @endif
                <div class="text-center" style="padding-top:12px; font-size:11px; color:#666;">
                    <p style="margin:0;">Dicetak: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                </div>
            @endif
        </div>
    </div>

    @php
        $dividerDashed = "--------------------------------\n";
        $dividerSolid = "================================\n";

        if (!function_exists('padLineStruk')) {
            function padLineStruk($left, $right, $length = 32)
            {
                $leftLen = mb_strlen($left);
                $rightLen = mb_strlen($right);
                $spaces = $length - $leftLen - $rightLen;
                if ($spaces < 1)
                    $spaces = 1;
                return $left . str_repeat(' ', $spaces) . $right . "\n";
            }
        }

        if (!function_exists('wrapStrukLine')) {
            function wrapStrukLine($text, $indent = 3, $width = 32)
            {
                if (mb_strlen($text) <= $width) {
                    return $text . "\n";
                }
                $indentStr = str_repeat(' ', $indent);
                $result = mb_substr($text, 0, $width) . "\n";
                $remaining = mb_substr($text, $width);
                $availWidth = $width - $indent;
                while (mb_strlen($remaining) > 0) {
                    $chunk = mb_substr($remaining, 0, $availWidth);
                    $result .= $indentStr . $chunk . "\n";
                    $remaining = mb_substr($remaining, mb_strlen($chunk));
                }
                return $result;
            }
        }

        $struk = "";

        if ($jenis === 'pelanggan') {
            $toko = "Saisho Cake & Bakery";
            $alamat = "Jl. Kemboja No.1, Kedungjaya, Cirebon";
            $subtotal = (float) $transaksi->subtotal;
            $diskon = (float) $transaksi->diskon;
            $diskonPersen = $subtotal > 0 ? round(($diskon / $subtotal) * 100) : 0;
            $pajak = (float) $transaksi->pajak;
            $pajakPersen = ($subtotal - $diskon) > 0 ? round(($pajak / ($subtotal - $diskon)) * 100) : 0;

            // Sama seperti versi HTML di atas: gabungkan item dengan nama
            // produk yang sama supaya tidak tercetak sebagai baris terpisah.
            $itemsPelangganTeks = $transaksi->detailTransaksi
                ->groupBy('nama_produk')
                ->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'nama_produk' => $first->nama_produk,
                        'harga_satuan' => $first->harga_satuan,
                        'jumlah' => $group->sum('jumlah'),
                        'subtotal' => $group->sum('subtotal'),
                        'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                    ];
                })
                ->values();

            $struk .= "      " . strtoupper($toko) . "\n";
            $struk .= "  Jl. Kemboja No.1, Kedungjaya\n";
            $struk .= "   Kec. Kedawung, Kab. Cirebon\n";
            $struk .= $dividerDashed;
            $struk .= "No   : " . $transaksi->kode_transaksi . "\n";
            $struk .= "Tgl  : " . $transaksi->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') . "\n";
            $struk .= "Pel  : " . ($transaksi->nama_pembeli ?? 'Pelanggan Umum') . "\n";
            $struk .= "Bayar: " . ucfirst($transaksi->metode_pembayaran) . "\n";
            $struk .= $dividerDashed;

            // Invoice pelanggan mencetak SEMUA item (sudah digabung per nama produk)
            foreach ($itemsPelangganTeks as $index => $item) {
                $no = ($index + 1) . ". ";
                $namaFull = $no . $item->nama_produk;
                $struk .= wrapStrukLine($namaFull, mb_strlen($no));
                if ($item->catatan) {
                    $struk .= "  * Catatan: " . $item->catatan . "\n";
                }
                $leftQty = "  " . $item->jumlah . " x Rp " . number_format($item->harga_satuan, 0, ',', '.');
                $rightSub = "Rp " . number_format($item->subtotal, 0, ',', '.');
                $struk .= padLineStruk($leftQty, $rightSub);
            }

            $struk .= $dividerDashed;
            $struk .= padLineStruk("Subtotal", "Rp " . number_format($subtotal, 0, ',', '.'));

            if ($diskon > 0) {
                $struk .= padLineStruk("Diskon (" . $diskonPersen . "%)", "-Rp " . number_format($diskon, 0, ',', '.'));
            }
            if ($pajak > 0) {
                $struk .= padLineStruk("Pajak (" . $pajakPersen . "%)", "Rp " . number_format($pajak, 0, ',', '.'));
            }

            $struk .= $dividerDashed;
            $struk .= padLineStruk("TOTAL", "Rp " . number_format($transaksi->total_bayar, 0, ',', '.'));
            $struk .= $dividerDashed;
            $struk .= padLineStruk("Diterima", "Rp " . number_format($transaksi->jumlah_diterima, 0, ',', '.'));
            $struk .= padLineStruk("Kembalian", "Rp " . number_format($transaksi->kembalian, 0, ',', '.'));
            $struk .= $dividerDashed;
            $struk .= "Sampai ketemu di pesanan berikutnya! ^^\n";

        } else {
            // DAPUR: hanya item butuh_produksi (snapshot saat transaksi dibuat),
            // berlaku sama untuk transaksi Lunas maupun Belum Lunas.
            $itemsDapur = $transaksi->detailTransaksi->filter(function ($item) {
                return (bool) $item->butuh_produksi;
            })->values();

            $struk .= "      SAISHO CAKE & BAKERY\n";
            $struk .= "         TIKET PRODUKSI\n";
            $struk .= $dividerDashed;
            $struk .= "No   : " . $transaksi->kode_transaksi . "\n";
            $struk .= "Tgl  : " . $transaksi->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') . "\n";
            $struk .= "Pel  : " . ($transaksi->nama_pembeli ?? 'Pelanggan Umum') . "\n";
            $struk .= $dividerDashed;

            $idx = 1;
            foreach ($itemsDapur as $item) {
                $prefix = $idx . ". ";
                $nameFull = $prefix . $item->nama_produk;
                $rightQty = "x" . $item->jumlah;
                $indent = str_repeat(' ', mb_strlen($prefix));

                if (mb_strlen($nameFull) + 1 + mb_strlen($rightQty) <= 32) {
                    $struk .= padLineStruk($nameFull, $rightQty);
                } else {
                    $struk .= wrapStrukLine($nameFull, mb_strlen($prefix));
                    $struk .= padLineStruk($indent, $rightQty);
                }
                if ($item->catatan) {
                    $struk .= "  * Catatan: " . $item->catatan . "\n";
                }
                $idx++;
            }

            if ($itemsDapur->isEmpty()) {
                $struk .= "Tidak ada item untuk dapur\n";
            }

            if ($transaksi->pesananDitahan && $transaksi->pesananDitahan->catatan) {
                $struk .= $dividerDashed;
                $struk .= "Catatan: " . $transaksi->pesananDitahan->catatan . "\n";
            }

            $struk .= $dividerDashed;
            $struk .= "Dicetak: " . now()->locale('id')->isoFormat('D MMM YYYY, HH:mm') . "\n\n\n";
        }
    @endphp

    <!-- Floating Toast Container -->
    <div id="toast-container"
        style="position:fixed; top:20px; right:20px; z-index:99999; display:flex; flex-direction:column; gap:8px; pointer-events:none;">
    </div>

    <script>
        function showToast(pesan, tipe = 'sukses') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            let bgStyle = 'background-color: #16a34a;'; // green
            let icon = '✓';

            if (tipe === 'error') {
                bgStyle = 'background-color: #dc2626;'; // red
                icon = '⚠';
            } else if (tipe === 'info') {
                bgStyle = 'background-color: #b45309;'; // amber
                icon = 'ℹ';
            }

            toast.style.cssText = `
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
        pointer-events: auto;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        ${bgStyle}
    `;

            toast.innerHTML = `
        <span style="font-weight:800; font-size:16px;">${icon}</span>
        <span>${pesan}</span>
    `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function printStruk() {
            if (!window.Android) {
                showToast("Koneksi Bluetooth hanya tersedia di aplikasi Android POS.", "info");
                return;
            }

            let strukText = @json($struk);
            let result = Android.printText(strukText);

            if (result === "true" || result === true) {
                showToast("Berhasil dicetak ke printer Bluetooth!", "sukses");
            } else {
                showToast("Gagal mencetak ke printer Bluetooth.", "error");
            }
        }

        window.onload = function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('print') === 'true') {
                window.print();
            }
        };
    </script>

</body>

</html>