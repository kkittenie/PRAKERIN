@extends('mobile.layout', ['title' => 'Cara Pembayaran'])

@section('content')

    <style>
        .checkout-sticky-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>

    <div
        class="checkout-sticky-header relative -mx-5 -mt-6 flex h-14 items-center justify-center border-b border-gray-100 px-5 sm:-mx-8 md:-mx-10 lg:hidden">
        <a href="{{ route('member.pesanan.show', $transaksi) }}"
           onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
           class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
            <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
        </a>

        <h1 class="text-sm font-bold text-gray-900">Payment Instructions</h1>
    </div>

    <div
        x-data="qrisDownloader('QRIS-{{ $transaksi->kode_transaksi }}.png')"
        class="mt-6 text-center lg:hidden"
    >
        {{-- Toast di-teleport ke <body> --}}
        <template x-teleport="body">
            <div
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 -translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-3"
                class="fixed top-4 inset-x-5 z-[999] mx-auto flex w-auto max-w-sm items-start gap-2 rounded-2xl border px-4 py-3 text-left text-xs font-semibold shadow-lg"
                :class="toast.variant === 'success' ? 'bg-brand-50 text-brand-800 border-brand-200' : 'bg-red-50 text-red-800 border-red-200'"
                x-cloak
            >
                <svg x-show="toast.variant === 'success'" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-brand-800"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg x-show="toast.variant === 'error'" viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-red-600"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span x-text="toast.message"></span>
            </div>
        </template>

        <div class="lg:col-span-6 text-center">
            <img x-ref="qrisImage" src="{{ $qrisUrl }}" alt="QRIS Code"
                 class="mx-auto h-60 w-60 rounded-2xl border border-gray-100 object-contain">

            <button type="button" @click="download()" :disabled="downloading"
                    class="mx-auto mt-3 inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1.5 text-[11px] font-semibold text-gray-600 transition active:bg-gray-100 disabled:opacity-60">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span x-text="downloading ? 'Downloading...' : 'Download QR'"></span>
            </button>

            <p class="mt-4 text-xs font-medium text-gray-400">Total Amount Due</p>
            <p class="mt-1 text-2xl font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-gray-400">{{ $transaksi->kode_transaksi }}</p>
        </div>

        <div class="lg:col-span-6">
            <div class="mx-5 lg:mx-0 mt-6 lg:mt-0 rounded-2xl border border-dashed border-gray-200 p-4 text-left">
                <p class="text-xs font-bold text-gray-900">Payment Steps</p>
                <ol class="mt-2 space-y-1.5 text-[11px] text-gray-500">
                    <li>1. Open your mobile banking or e-wallet application.</li>
                    <li>2. Select Scan QRIS and scan the code above (or download the QR code first).</li>
                    <li>3. Verify the amount matches your total bill, then proceed with payment.</li>
                    <li>4. Return to your order status page and upload your payment receipt.</li>
                </ol>
            </div>

            <a href="{{ route('member.pesanan.show', $transaksi) }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="mx-5 lg:mx-0 mt-6 block rounded-xl bg-brand-800 py-2.5 text-center text-xs font-bold text-white transition active:bg-brand-900">
                Paid, Upload Payment Receipt
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE QRIS PAYMENT
         ════════════════════════════════════════════════════════════════ --}}
    <div x-data="qrisDownloader('QRIS-{{ $transaksi->kode_transaksi }}.png')"
         class="hidden lg:block w-full bg-[#FAF5E8] py-10 px-8 lg:px-12 xl:px-16 min-h-screen text-[#3C2415]">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('member.pesanan.show', $transaksi) }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#3C2415]/60 hover:text-[#8E1E26] transition-colors mb-6">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Back to Order Status</span>
            </a>

            <div class="mb-8 border-b border-[#3C2415]/10 pb-6">
                <span class="inline-block px-3.5 py-1 rounded-full bg-[#EADCC8] text-[10px] font-bold tracking-widest uppercase text-[#3C2415] mb-2">
                    PAYMENT INSTRUCTIONS
                </span>
                <h1 class="font-display font-black text-3xl sm:text-4xl tracking-tight text-[#3C2415]">
                    Scan QRIS Payment
                </h1>
                <p class="text-xs text-[#3C2415]/60 mt-1">Transaction Ref: {{ $transaksi->kode_transaksi }}</p>
            </div>

            <div class="grid grid-cols-12 gap-8 items-start">
                <div class="col-span-5 bg-[#F5ECE3] rounded-[28px] p-8 border border-[#3C2415]/10 text-center shadow-sm">
                    <div class="inline-block p-4 rounded-2xl bg-white border border-[#3C2415]/10 shadow-sm">
                        <img x-ref="qrisImage" src="{{ $qrisUrl }}" alt="Kode QRIS"
                             class="h-64 w-64 object-contain mx-auto">
                    </div>

                    <button type="button" @click="download()" :disabled="downloading"
                            class="mx-auto mt-5 inline-flex items-center gap-2 rounded-full bg-[#3C2415] px-6 py-2.5 text-xs font-bold text-[#FAF5E8] shadow-sm hover:bg-[#4A2D1B] transition-colors disabled:opacity-60 cursor-pointer">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <span x-text="downloading ? 'Downloading...' : 'Download QR Code'"></span>
                    </button>

                    <div class="mt-6 pt-5 border-t border-[#3C2415]/10">
                        <span class="text-xs uppercase tracking-wider text-[#3C2415]/60 font-bold block">Total Amount Due</span>
                        <span class="font-display font-black text-3xl text-[#3C2415] block mt-1">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="col-span-7 space-y-6">
                    <div class="bg-[#F5ECE3] rounded-[28px] p-8 border border-[#3C2415]/10 shadow-sm">
                        <h3 class="font-display font-bold text-base text-[#3C2415] mb-4 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8] text-xs">
                                ℹ
                            </span>
                            Payment Steps
                        </h3>
                        <ol class="space-y-3 text-xs text-[#3C2415]/80 list-decimal list-inside">
                            <li class="leading-relaxed">Open your banking application or digital wallet (BCA Mobile, Livin' by Mandiri, GoPay, OVO, Dana, ShopeePay, etc.).</li>
                            <li class="leading-relaxed">Choose <strong>Scan QRIS</strong> and point your camera to the QR code on the left (or download the QR code to scan from your gallery).</li>
                            <li class="leading-relaxed">Ensure the merchant name displayed is <strong>Daisy Bakery</strong> and the payment amount is exactly <strong>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</strong>.</li>
                            <li class="leading-relaxed">Complete the PIN authorization, save or screenshot the successful transaction receipt.</li>
                            <li class="leading-relaxed">Return to your order status page and upload your receipt for cashier confirmation.</li>
                        </ol>
                    </div>

                    <a href="{{ route('member.pesanan.show', $transaksi) }}"
                       onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                       class="w-full py-4 px-8 rounded-full bg-[#8E1E26] hover:bg-[#72181E] text-[#FAF5E8] font-bold text-xs uppercase tracking-widest shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>I HAVE PAID · UPLOAD RECEIPT NOW</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    {{-- ════════════════════════════════════════════════════════════════
         END DESKTOP VIEW
         ════════════════════════════════════════════════════════════════ --}}

    <script>
        function qrisDownloader(filename) {
            return {
                downloading: false,
                toast: { show: false, variant: 'success', message: '' },

                showToast(message, variant = 'success') {
                    clearTimeout(this._toastTimer);
                    this.toast = { show: true, variant, message };
                    this._toastTimer = setTimeout(() => { this.toast.show = false; }, 2500);
                },

                async download() {
                    if (this.downloading) return;

                    const img = this.$refs.qrisImage;

                    // Gambar gagal dimuat (404 / file QRIS belum ada di server)
                    if (!img.complete || img.naturalWidth === 0) {
                        this.showToast('QR code image could not be loaded. Please contact store admin.', 'error');
                        return;
                    }

                    this.downloading = true;

                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        canvas.getContext('2d').drawImage(img, 0, 0);

                        // Kalau dibuka dari WebView aplikasi Android (bukan browser HP),
                        // navigator.share & blob-download nggak jalan di sana. Jadi kalau
                        // jembatan native "AndroidQR" ada, kirim gambarnya (base64) ke
                        // Kotlin/Java lewat JavascriptInterface, biar Android yang nyimpen
                        // filenya langsung ke galeri/Pictures pakai MediaStore.
                        if (window.Android && typeof window.Android.saveQr === 'function') {
                            const dataUrl = canvas.toDataURL('image/png');
                            const result = window.Android.saveQr(dataUrl, filename);
                            this.downloading = false;
                            if (result === 'false') {
                                this.showToast('Failed to download QR code, please try again.', 'error');
                            } else {
                                this.showToast('QR code downloaded successfully!', 'success');
                            }
                            return;
                        }

                        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
                        this.downloading = false;

                        if (!blob) {
                            this.showToast('Failed to download QR code, please try again.', 'error');
                            return;
                        }

                        // Download langsung lewat blob URL (tanpa share sheet),
                        // file langsung masuk ke folder Download browser.
                        const blobUrl = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = blobUrl;
                        link.download = filename;
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);

                        this.showToast('QR code downloaded successfully!', 'success');
                    } catch (err) {
                        this.downloading = false;
                        console.error('Download QR error:', err);
                        this.showToast('Failed to download QR code, please try again.', 'error');
                    }
                },
            };
        }
    </script>

@endsection