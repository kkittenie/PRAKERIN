<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode - {{ $produksi->produk->nama_produk }}</title>

    <!-- Google Fonts (sama seperti struk/tiket) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- URL cdnjs pakai folder huruf kecil "jsbarcode", bukan "JsBarcode" -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>

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

        /* Action Bar */
        .action-bar {
            width: 100%;
            max-width: 340px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
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
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b45309 0%, #92400e 100%);
            transform: translateY(-1px);
        }

        .btn-close {
            background: transparent;
            color: var(--text-muted);
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-close:hover {
            color: #0f172a;
            background: rgba(0, 0, 0, 0.04);
        }

        .info-text {
            width: 100%;
            max-width: 340px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .label-list {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        /* Label Card (mirip struk-card, ukuran disesuaikan buat stiker) */
        .label-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            padding: 16px;
            width: 100%;
            max-width: 260px;
            box-sizing: border-box;
            margin-bottom: 16px;
        }

        .label-content {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            text-align: center;
            color: #000000;
        }

        .label-toko {
            font-size: 11px;
            font-weight: 700;
            padding-bottom: 8px;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000000;
        }

        .label-nama-produk {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .label-harga {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .label-barcode svg {
            max-width: 100%;
            height: auto;
        }

        .label-kadaluarsa {
            font-size: 10px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #000000;
        }

        @media print {
            @page {
                size: auto;
                margin: 5mm;
            }

            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
                display: block;
            }

            .action-bar,
            .info-text {
                display: none !important;
            }

            .label-list {
                display: block;
                text-align: left;
            }

            .label-card {
                display: inline-block;
                vertical-align: top;
                width: 50mm;
                min-height: 38mm;
                border: 1px dashed #cbd5e1;
                box-shadow: none;
                border-radius: 0;
                padding: 2mm;
                margin: 0 3mm 3mm 0;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body onload="renderSemuaBarcode()">

    <!-- Action Bar (hilang saat print) -->
    <div class="action-bar">
        <button class="btn btn-primary" onclick="window.print()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak {{ $totalLabel }} Label</span>
        </button>
        <button class="btn btn-close" onclick="window.close(); setTimeout(() => history.back(), 100);">Tutup
            Halaman</button>
    </div>

    <p class="info-text">
        {{ $totalLabel }} label untuk <strong>{{ $produksi->produk->nama_produk }}</strong>
        (hasil produksi: {{ $produksi->jumlah_hasil }} pcs)
    </p>

    <div class="label-list">
        @php $labelIndex = 0; @endphp
        @foreach ($labelItems as $item)
            @for ($i = 0; $i < $item['jumlah']; $i++)
                <div class="label-card">
                    <div class="label-content">
                        <div class="label-toko">SAISHO CAKE &amp; BAKERY</div>
                        <div class="label-nama-produk">
                            {{ $produksi->produk->nama_produk }}
                            @if ($item['nama_ukuran'] || !empty($item['nama_rasa']))
                                ({{ collect([$item['nama_ukuran'], $item['nama_rasa'] ?? null])->filter()->implode(' · ') }})
                            @endif
                        </div>
                        <div class="label-harga">Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</div>
                        <div class="label-barcode">
                            <svg id="barcode-{{ $labelIndex }}"></svg>
                        </div>
                        <div class="label-kadaluarsa">EXP: {{ $produksi->kadaluarsa_label_cetak }}</div>
                    </div>
                </div>
                @php $labelIndex++; @endphp
            @endfor
        @endforeach
    </div>

    <script>
        function renderSemuaBarcode() {
            const daftarBarcode = @json(
                collect($labelItems)->flatMap(fn ($item) => array_fill(0, $item['jumlah'], $item['kode_barcode']))->values()
            );

            daftarBarcode.forEach(function (kode, i) {
                JsBarcode(`#barcode-${i}`, kode, {
                    format: "CODE128",
                    height: 40,
                    width: 1.5,
                    fontSize: 10,
                    margin: 4,
                });
            });
        }

        window.onload = function () {
            renderSemuaBarcode();
            const params = new URLSearchParams(window.location.search);
            if (params.get('print') === 'true') {
                window.print();
            }
        };
    </script>

</body>

</html>