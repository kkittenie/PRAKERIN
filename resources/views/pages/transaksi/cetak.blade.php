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
            @php
                if (!function_exists('formatPersenStruk')) {
                    function formatPersenStruk($v)
                    {
                        $n = round((float) $v, 2);
                        $n = rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
                        return str_replace('.', ',', $n);
                    }
                }
            @endphp
            @if ($jenis === 'pelanggan')
                @php
                    $toko = '';
                    $alamat = 'Jl. Kamboja No. 1 Tuparev, Kedungjaya, Kec. Kedawung, Kabupaten Cirebon';
                    $subtotal = (float) $transaksi->subtotal;
                    $diskon = (float) $transaksi->diskon;
                    $diskonPersen = $subtotal > 0 ? ($diskon / $subtotal) * 100 : 0;
                    $pajak = (float) $transaksi->pajak;
                    $pajakPersen = ($subtotal - $diskon) > 0 ? ($pajak / ($subtotal - $diskon)) * 100 : 0;
                    $itemsPelanggan = $transaksi->detailTransaksi
                        ->filter(function ($item) {
                            // Baris pecahan bahan baku internal (dibuat otomatis untuk
                            // tracking stok per komponen bundling/promo event) punya
                            // produk_id terisi SEKALIGUS bundling_id/promo_event_id
                            // terisi, dan harga & subtotalnya 0 (sudah termasuk di
                            // baris ringkasan bundling/promo event-nya). Baris itu
                            // bukan item yang dijual terpisah ke pelanggan, jadi
                            // jangan ikut ditampilkan di struk.
                            $adalahKomponenInternal = ($item->bundling_id || $item->promo_event_id) && $item->produk_id && (float) $item->harga_satuan == 0 && (float) $item->subtotal == 0;
                            return !$adalahKomponenInternal;
                        })
                        ->groupBy(function ($item) {
                            return $item->nama_produk . ($item->ukuran ? ' (' . $item->ukuran . ')' : '');
                        })
                        ->map(function ($group, $key) {
                            $first = $group->first();
                            return (object) [
                                'nama_produk' => $key,
                                'harga_satuan' => $first->harga_satuan,
                                'jumlah' => $group->sum('jumlah'),
                                'subtotal' => $group->sum('subtotal'),
                                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                                'is_bundling' => !is_null($first->bundling_id),
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
                    @php
                        $diskonItems = [];
                        if (!empty($transaksi->diskon_label)) {
                            $decoded = json_decode($transaksi->diskon_label, true);
                            if (is_array($decoded)) {
                                $diskonItems = $decoded;
                            }
                        }
                    @endphp
                    @if (!empty($diskonItems))
                        @foreach ($diskonItems as $itemDiskon)
                            <div class="flex-between"
                                style="align-items: flex-start; gap: 6px; color: #000000; margin-bottom: 2px;">
                                <span style="flex: 1; min-width: 0; word-break: break-word;">{{ $itemDiskon['nama'] }}</span>
                                <span style="white-space: nowrap; flex-shrink: 0; font-weight: 500;">-Rp
                                    {{ number_format($itemDiskon['nilai'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @elseif ($diskon > 0)
                        <div class="flex-between"
                            style="align-items: flex-start; gap: 6px; color: #000000; margin-bottom: 2px;">
                            <span style="flex: 1; min-width: 0; word-break: break-word;">Diskon
                                ({{ $transaksi->diskon_label ?? formatPersenStruk($diskonPersen) }})</span>
                            <span style="white-space: nowrap; flex-shrink: 0; font-weight: 500;">-Rp
                                {{ number_format($diskon, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($pajak > 0)
                        <div class="flex-between">
                            <span>Pajak ({{ formatPersenStruk($pajakPersen) }}%)</span> <span>Rp
                                {{ number_format($pajak, 0, ',', '.') }}</span>
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
                @if ($transaksi->member)
                    @php
                        $poinSebelumHtml = $transaksi->poin_sebelum ?? $transaksi->member->poin;
                        $poinDidapatHtml = (int) $transaksi->poin_didapat;
                    @endphp
                    <div class="border-bottom-dashed" style="padding:8px 0;">
                        <div class="flex-between">
                            <span>Member:</span>
                            <span>{{ $transaksi->member->nama }}</span>
                        </div>
                        @if ($poinDidapatHtml > 0)
                            <div class="flex-between" style="font-weight:bold;">
                                <span>Poin didapat</span>
                                <span>+{{ number_format($poinDidapatHtml, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex-between" style="font-weight:bold;">
                            <span>Total poin</span>
                            <span>{{ number_format($poinSebelumHtml + $poinDidapatHtml, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
                <div class="text-center" style="padding-top:12px; font-size:11px; color:#666;">
                    <p style="margin:0;">Sampai ketemu di pesanan berikutnya! ^^</p>
                </div>
            @elseif ($jenis === 'dapur')
                @php
                    // Baris header paket (bundling/promo event) nyimpen catatan asli
                    // dari pembeli (mis. rincian rasa) — dipetakan per
                    // bundling_id/promo_event_id supaya bisa ditempelkan lagi ke
                    // baris pecahan produknya di bawah.
                    $headerBundlingDapur = $transaksi->detailTransaksi
                        ->filter(fn ($i) => $i->bundling_id && is_null($i->produk_id))
                        ->keyBy('bundling_id');
                    $headerPromoEventDapur = $transaksi->detailTransaksi
                        ->filter(fn ($i) => $i->promo_event_id && is_null($i->produk_id))
                        ->keyBy('promo_event_id');

                    $itemsDapur = $transaksi->detailTransaksi
                        ->filter(function ($item) {
                            // Baris ringkasan paket (bundling/promo event) punya
                            // bundling_id/promo_event_id terisi TAPI produk_id kosong —
                            // ini cuma nama paketnya, bukan produk yang harus dibikin,
                            // jadi jangan pernah dicetak ke dapur.
                            $adalahBarisPaket = ($item->bundling_id || $item->promo_event_id) && is_null($item->produk_id);
                            if ($adalahBarisPaket) {
                                return false;
                            }

                            // Sisanya: baris produk biasa ATAU baris pecahan produk
                            // asli di dalam paket (produk_id terisi) — ini yang
                            // dicetak kalau butuh produksi, karena nama_produk-nya
                            // sudah nama produk asli, bukan nama paket.
                            return (bool) $item->butuh_produksi;
                        })
                        ->map(function ($item) use ($headerBundlingDapur, $headerPromoEventDapur) {
                            $catatanPaket = null;
                            if ($item->bundling_id && $headerBundlingDapur->has($item->bundling_id)) {
                                $catatanPaket = $headerBundlingDapur->get($item->bundling_id)->catatan;
                            } elseif ($item->promo_event_id && $headerPromoEventDapur->has($item->promo_event_id)) {
                                $catatanPaket = $headerPromoEventDapur->get($item->promo_event_id)->catatan;
                            }
                            return (object) [
                                'nama_produk' => $item->nama_produk . ($item->ukuran ? ' (' . $item->ukuran . ')' : ''),
                                'jumlah' => $item->jumlah,
                                'catatan' => $catatanPaket ?: $item->catatan,
                            ];
                        })
                        // Gabung baris produk yang sama (mis. "pecahan" dari bundling
                        // & promo event berbeda yang sama-sama butuh produksi) jadi
                        // satu baris, jumlahnya dijumlahkan, biar tidak dobel/kepisah
                        // di tiket dapur.
                        ->groupBy('nama_produk')
                        ->map(function ($group, $nama) {
                            return (object) [
                                'nama_produk' => $nama,
                                'jumlah' => $group->sum('jumlah'),
                                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                            ];
                        })
                        ->values();
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
        $strukWidth = 32; 

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

        if (!function_exists('padLineStrukWrap')) {
            function padLineStrukWrap($left, $right, $width = 32)
            {
                $rightLen = mb_strlen($right);
                if (mb_strlen($left) + 1 + $rightLen <= $width) {
                    return padLineStruk($left, $right, $width);
                }

                $result = wrapStrukLine($left, 0, $width);
                $result .= str_repeat(' ', max(0, $width - $rightLen)) . $right . "\n";
                return $result;
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

        if (!function_exists('centerLineStruk')) {
            function centerLineStruk($text, $width = 32)
            {
                $len = mb_strlen($text);
                if ($len >= $width) {
                    return $text . "\n";
                }
                $leftPad = intdiv($width - $len, 2);
                return str_repeat(' ', $leftPad) . $text . "\n";
            }
        }

        if (!function_exists('centerWrapStruk')) {
            function centerWrapStruk($text, $width = 32)
            {
                $words = explode(' ', $text);
                $lines = [];
                $current = '';

                foreach ($words as $word) {
                    $test = $current === '' ? $word : $current . ' ' . $word;
                    if (mb_strlen($test) > $width) {
                        $lines[] = $current;
                        $current = $word;
                    } else {
                        $current = $test;
                    }
                }
                if ($current !== '') {
                    $lines[] = $current;
                }

                $result = '';
                foreach ($lines as $line) {
                    $result .= centerLineStruk($line, $width);
                }
                return $result;
            }
        }

        $struk = "";

        if ($jenis === 'pelanggan') {
            $toko = "Saisho Cake & Bakery";
            $alamat = "Jl. Kamboja No. 1 Tuparev, Kedungjaya, Kec. Kedawung, Kabupaten Cirebon";
            $subtotal = (float) $transaksi->subtotal;
            $diskon = (float) $transaksi->diskon;
            $diskonPersen = $subtotal > 0 ? ($diskon / $subtotal) * 100 : 0;
            $pajak = (float) $transaksi->pajak;
            $pajakPersen = ($subtotal - $diskon) > 0 ? ($pajak / ($subtotal - $diskon)) * 100 : 0;   
            $itemsPelangganTeks = $transaksi->detailTransaksi
                ->filter(function ($item) {
                    // Baris pecahan bahan baku internal (dibuat otomatis untuk
                    // tracking stok per komponen bundling/promo event) punya
                    // produk_id terisi SEKALIGUS bundling_id/promo_event_id
                    // terisi, dan harga & subtotalnya 0 (sudah termasuk di
                    // baris ringkasan bundling/promo event-nya). Baris itu
                    // bukan item yang dijual terpisah ke pelanggan, jadi
                    // jangan ikut ditampilkan di struk.
                    $adalahKomponenInternal = ($item->bundling_id || $item->promo_event_id) && $item->produk_id && (float) $item->harga_satuan == 0 && (float) $item->subtotal == 0;
                    return !$adalahKomponenInternal;
                })
                ->groupBy(function ($item) {
                    return $item->nama_produk . ($item->ukuran ? ' (' . $item->ukuran . ')' : '');
                })
                ->map(function ($group, $key) {
                    $first = $group->first();
                    return (object) [
                        'nama_produk' => $key,
                        'harga_satuan' => $first->harga_satuan,
                        'jumlah' => $group->sum('jumlah'),
                        'subtotal' => $group->sum('subtotal'),
                        'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                        'is_bundling' => !is_null($first->bundling_id),
                    ];
                })
                ->values();

            $struk .= centerWrapStruk($alamat);
            $struk .= $dividerDashed;
            $struk .= "No   : " . $transaksi->kode_transaksi . "\n";
            $struk .= "Tgl  : " . $transaksi->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') . "\n";
            $struk .= "Pel  : " . ($transaksi->nama_pembeli ?? 'Pelanggan Umum') . "\n";
            $struk .= "Bayar: " . ucfirst($transaksi->metode_pembayaran) . "\n";
            $struk .= $dividerDashed;

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

            if (!empty($diskonItems)) {
                foreach ($diskonItems as $itemDiskon) {
                    $struk .= padLineStrukWrap($itemDiskon['nama'], "-Rp " . number_format($itemDiskon['nilai'], 0, ',', '.'));
                }
            } elseif ($diskon > 0) {
                $labelDiskon = $transaksi->diskon_label ?? formatPersenStruk($diskonPersen);
                $struk .= padLineStrukWrap("Diskon (" . $labelDiskon . ")", "-Rp " . number_format($diskon, 0, ',', '.'));
            }
            if ($pajak > 0) {
                $struk .= padLineStruk("Pajak (" . formatPersenStruk($pajakPersen) . "%)", "Rp " . number_format($pajak, 0, ',', '.'));
            }

            $struk .= $dividerDashed;
            $struk .= padLineStruk("TOTAL", "Rp " . number_format($transaksi->total_bayar, 0, ',', '.'));
            $struk .= $dividerDashed;
            $struk .= padLineStruk("Diterima", "Rp " . number_format($transaksi->jumlah_diterima, 0, ',', '.'));
            $struk .= padLineStruk("Kembalian", "Rp " . number_format($transaksi->kembalian, 0, ',', '.'));
            $struk .= $dividerDashed;

            if ($transaksi->member) {
                $poinSebelumTeks = $transaksi->poin_sebelum ?? $transaksi->member->poin;
                $poinDidapatTeks = (int) $transaksi->poin_didapat;

                $struk .= padLineStruk("Member", $transaksi->member->nama);
                $struk .= padLineStruk("Poin sebelum", number_format($poinSebelumTeks, 0, ',', '.'));
                if ($poinDidapatTeks > 0) {
                    $struk .= padLineStruk("Poin didapat", "+" . number_format($poinDidapatTeks, 0, ',', '.'));
                }
                $struk .= padLineStruk("Total poin", number_format($poinSebelumTeks + $poinDidapatTeks, 0, ',', '.'));
                $struk .= $dividerDashed;
            }

            $struk .= centerWrapStruk("Sampai ketemu di pesanan berikutnya! ^^");

        } else {
            // Baris header paket (bundling/promo event) nyimpen catatan asli
            // dari pembeli — dipetakan per bundling_id/promo_event_id supaya
            // bisa ditempelkan lagi ke baris pecahan produknya di bawah.
            $headerBundlingDapur = $transaksi->detailTransaksi
                ->filter(fn ($i) => $i->bundling_id && is_null($i->produk_id))
                ->keyBy('bundling_id');
            $headerPromoEventDapur = $transaksi->detailTransaksi
                ->filter(fn ($i) => $i->promo_event_id && is_null($i->produk_id))
                ->keyBy('promo_event_id');

            $itemsDapur = $transaksi->detailTransaksi
                ->filter(function ($item) {
                    // Baris ringkasan paket (bundling/promo event) punya
                    // bundling_id/promo_event_id terisi TAPI produk_id kosong —
                    // ini cuma nama paketnya, bukan produk yang harus dibikin,
                    // jadi jangan pernah dicetak ke dapur.
                    $adalahBarisPaket = ($item->bundling_id || $item->promo_event_id) && is_null($item->produk_id);
                    if ($adalahBarisPaket) {
                        return false;
                    }

                    // Sisanya: baris produk biasa ATAU baris pecahan produk
                    // asli di dalam paket (produk_id terisi) — ini yang
                    // dicetak kalau butuh produksi, karena nama_produk-nya
                    // sudah nama produk asli, bukan nama paket.
                    return (bool) $item->butuh_produksi;
                })
                ->map(function ($item) use ($headerBundlingDapur, $headerPromoEventDapur) {
                    $catatanPaket = null;
                    if ($item->bundling_id && $headerBundlingDapur->has($item->bundling_id)) {
                        $catatanPaket = $headerBundlingDapur->get($item->bundling_id)->catatan;
                    } elseif ($item->promo_event_id && $headerPromoEventDapur->has($item->promo_event_id)) {
                        $catatanPaket = $headerPromoEventDapur->get($item->promo_event_id)->catatan;
                    }
                    return (object) [
                        'nama_produk' => $item->nama_produk . ($item->ukuran ? ' (' . $item->ukuran . ')' : ''),
                        'jumlah' => $item->jumlah,
                        'catatan' => $catatanPaket ?: $item->catatan,
                    ];
                })
                // Gabung baris produk yang sama (mis. "pecahan" dari bundling &
                // promo event berbeda yang sama-sama butuh produksi) jadi satu
                // baris, jumlahnya dijumlahkan, biar tidak dobel/kepisah di
                // tiket dapur.
                ->groupBy('nama_produk')
                ->map(function ($group, $nama) {
                    return (object) [
                        'nama_produk' => $nama,
                        'jumlah' => $group->sum('jumlah'),
                        'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                    ];
                })
                ->values();

            $struk .= centerLineStruk("SAISHO CAKE & BAKERY");
            $struk .= centerLineStruk("TIKET PRODUKSI");
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
            $struk .= centerWrapStruk("Dicetak: " . now()->locale('id')->isoFormat('D MMM YYYY, HH:mm')) . "\n\n";
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
        function loadLogoAsBase64(callback) {
            const img = new Image();
            img.crossOrigin = "anonymous";

            img.onload = function () {
                let sourceWidth = img.naturalWidth || img.width;
                let sourceHeight = img.naturalHeight || img.height;

                if (!sourceWidth || !sourceHeight) {
                    console.warn('Logo tidak punya ukuran intrinsik yang valid (SVG tanpa width/height), pakai fallback 384x384');
                    sourceWidth = 384;
                    sourceHeight = 384;
                }

                const targetWidth = 384;
                const scale = targetWidth / sourceWidth;
                const targetHeight = Math.round(sourceHeight * scale);

                const canvas = document.createElement('canvas');
                canvas.width = targetWidth;
                canvas.height = targetHeight;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, targetWidth, targetHeight);
                ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

                try {
                    const dataUrl = canvas.toDataURL('image/png');
                    console.log('Logo berhasil dikonversi ke base64, ukuran:', targetWidth + 'x' + targetHeight);
                    callback(dataUrl.split(',')[1]);
                } catch (e) {
                    console.error('Gagal convert logo ke base64 (kemungkinan canvas tainted / CORS):', e);
                    callback(null);
                }
            };

            img.onerror = function (e) {
                console.error('Gagal memuat file logo (cek path asset atau format file):', e);
                callback(null);
            };

            img.src = @json(asset('images/logo/B&W-logo.svg'));
        }

        function printStruk() {
            if (!window.Android) {
                showToast("Koneksi Bluetooth hanya tersedia di aplikasi Android POS.", "info");
                return;
            }

            let strukText = @json($struk);
            let strukJenis = @json($jenis);

            function kirimHasilPrint(result) {
                if (result === "true" || result === true) {
                    showToast("Berhasil dicetak ke printer Bluetooth!", "sukses");
                } else {
                    showToast("Gagal mencetak ke printer Bluetooth.", "error");
                }
            }

            if (strukJenis === 'pelanggan') {
                loadLogoAsBase64(function (base64Logo) {
                    let result;
                    if (base64Logo) {
                        result = Android.printReceiptWithLogo(base64Logo, strukText);
                    } else {
                        result = Android.printText(strukText);
                    }
                    kirimHasilPrint(result);
                });
            } else {
                let result = Android.printText(strukText);
                kirimHasilPrint(result);
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