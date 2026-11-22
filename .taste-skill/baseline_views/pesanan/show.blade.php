@extends('mobile.layout', ['title' => 'Order Status'])

@php
    $detailRingkas = ($detailRingkas ?? $transaksi->detailTransaksi ?? $transaksi->detail ?? collect())
        ->reject(function ($item) {
            return ($item->bundling_id || $item->promo_event_id) && $item->produk_id && (float) $item->subtotal == 0;
        })
        // Produk yang sama bisa kepecah jadi 2 baris (porsi ready + porsi perlu
        // diproduksi). Buat member cukup tampil 1 baris, jumlah & subtotal digabung.
        // Cuma tampilan — data di DB (dipakai kasir/dapur) tidak diubah.
        ->groupBy(fn ($item) => implode('|', [
            $item->produk_id,
            $item->detail_produk_id,
            $item->bundling_id,
            $item->promo_event_id,
            $item->nama_produk,
            $item->ukuran,
            $item->varian_rasa,
            (float) $item->harga_satuan,
            $item->catatan,
        ]))
        ->map(function ($group) {
            if ($group->count() === 1) {
                return $group->first();
            }
            $gabungan = clone $group->first();
            $gabungan->jumlah = $group->sum('jumlah');
            $gabungan->subtotal = $group->sum('subtotal');
            return $gabungan;
        })
        ->values();
    $lunas = $transaksi->status_pembayaran === 'Lunas';

    // Cek apakah ada produk biasa (bukan reward stamp) di pesanan ini
    $adaProdukBiasa = $detailRingkas->contains(function ($d) {
        $catatan = strtolower($d->catatan ?? '');
        $isRewardStamp = str_contains($catatan, 'hadiah stamp') || str_contains($catatan, 'reward stamp');
        return ! $isRewardStamp;
    });

    // Pesanan hanya reward stamp jika TIDAK ada produk biasa di keranjang/detail
    $isHanyaRewardStamp = ! $adaProdukBiasa && (
        $transaksi->metode_pembayaran === 'Reward Stamp' ||
        ((float) $transaksi->total_bayar <= 0 && $lunas)
    );

    if ($isHanyaRewardStamp) {
        $steps = [
            \App\Models\Transaksi::STATUS_DIPROSES => 'Preparing',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Completed',
        ];
        $stepIcons = [
            \App\Models\Transaksi::STATUS_DIPROSES => 'mobile.components.icon-package',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'mobile.components.icon-shopping-bag',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'mobile.components.icon-check-circle',
        ];
        $statusSekarang = $transaksi->status_pesanan;
    } else {
        $steps = [
            'menunggu_pembayaran' => 'Awaiting Payment',
            'pembayaran_dikonfirmasi' => 'Payment Confirmed',
            \App\Models\Transaksi::STATUS_DIPROSES => 'Preparing',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Completed',
        ];
        $stepIcons = [
            'menunggu_pembayaran' => 'mobile.components.icon-clock',
            'pembayaran_dikonfirmasi' => 'mobile.components.icon-receipt-check',
            \App\Models\Transaksi::STATUS_DIPROSES => 'mobile.components.icon-package',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'mobile.components.icon-shopping-bag',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'mobile.components.icon-check-circle',
        ];

        if (! $lunas) {
            $statusSekarang = 'menunggu_pembayaran';
        } elseif (! $transaksi->diproses_pada) {
            $statusSekarang = 'pembayaran_dikonfirmasi';
        } else {
            $statusSekarang = $transaksi->status_pesanan;
        }
    }

    $urutan = array_keys($steps);
    $dibatalkan = $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_DIBATALKAN;
    $indexSekarang = array_search($statusSekarang, $urutan, true);
    $selesaiSemua = $lunas && $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL;
@endphp

@section('content')

    <div x-data="{}"
         x-init="{{ $dibatalkan || $selesaiSemua ? '' : "setInterval(() => { fetch('" . route('member.pesanan.status', $transaksi) . "').then(r => r.json()).then(d => { if (d.status_pesanan !== '{$transaksi->status_pesanan}' || d.status_pembayaran !== '{$transaksi->status_pembayaran}' || d.bukti_pembayaran !== " . json_encode($transaksi->bukti_pembayaran) . " || d.bukti_dikonfirmasi_pada !== " . json_encode(optional($transaksi->bukti_dikonfirmasi_pada)->toJSON()) . " || d.catatan_penolakan !== " . json_encode($transaksi->catatan_penolakan) . " || d.diproses_pada !== " . json_encode(optional($transaksi->diproses_pada)->toJSON()) . " || d.kode_pickup !== " . json_encode($transaksi->kode_pickup) . ") { fetch(window.location.href).then(res => res.text()).then(html => { const doc = new DOMParser().parseFromString(html, 'text/html'); const newEl = doc.querySelector('main') || doc.body; const currentEl = document.querySelector('main') || document.body; if (newEl && currentEl) currentEl.innerHTML = newEl.innerHTML; }); } }); }, 4000)" }}">

        <div class="relative flex items-center justify-center lg:hidden">
            <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition active:bg-gray-50">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-sm font-bold text-gray-900">Order Status</h1>
        </div>

        <div class="lg:hidden">

            {{-- Kolom Kiri: Status & Items --}}
            <div>
                @if ($dibatalkan)
                    <div class="mt-6 lg:mt-0 rounded-2xl border border-red-100 bg-red-50 p-4 text-center">
                        <p class="text-xs font-bold text-red-600">Order Cancelled</p>
                        <p class="mt-1 text-[11px] text-red-400">{{ $transaksi->kode_transaksi }}</p>
                        @if ($transaksi->alasan_pembatalan)
                            <p class="mt-3 text-[11px] text-red-600 whitespace-pre-line">
                                <span class="font-bold">Reason:</span> {{ $transaksi->alasan_pembatalan }}
                            </p>
                        @endif
                        {{-- Info refund cuma relevan kalau member sudah bayar / upload bukti --}}
                        @if ($lunas || $transaksi->bukti_pembayaran)
                            @php
                                $pesanCs = "Halo Daisy Bakery, order {$transaksi->kode_transaksi} was cancelled. I would like to inquire about refund.";
                            @endphp
                            <p class="mt-2 text-[11px] font-medium text-red-500">
                                Contact <a href="https://wa.me/62882000768044?text={{ urlencode($pesanCs) }}" target="_blank" rel="noopener" class="font-bold">customer service</a> for refund inquiries.
                            </p>
                        @endif
                    </div>
                @endif

                @if (! $dibatalkan && $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL && $transaksi->kode_pickup)
                    <div class="mt-6 lg:mt-0 rounded-3xl bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 p-6 text-center text-white">
                        <p class="text-xs font-bold uppercase tracking-wide text-white/70">Present this pickup code to cashier</p>
                        <p class="mt-2 text-4xl font-extrabold tracking-[0.3em]">{{ $transaksi->kode_pickup }}</p>
                        <p class="mt-3 text-[11px] text-white/60">{{ $transaksi->kode_transaksi }}</p>
                    </div>
                @endif

                {{-- Progress status --}}
                @unless ($dibatalkan)
                    <div x-data="pesananTimeline({{ $indexSekarang !== false ? $indexSekarang : -1 }}, {{ $transaksi->id }})"
                         x-cloak
                         class="mt-6 flex items-start">
                        @foreach ($steps as $key => $label)
                            @php $idx = array_search($key, $urutan, true); @endphp
                            <div class="flex min-w-0 flex-1 flex-col items-center text-center">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                      :class="[selesai({{ $idx }}) ? 'bg-brand-800 text-white' : 'bg-gray-200 text-gray-400', (baru({{ $idx }}) && mounted) ? 'animate-step-pop' : '']"
                                      :style="baru({{ $idx }}) ? `animation-delay: {{ $idx }}00ms` : ''">
                                    @include($stepIcons[$key], ['class' => 'h-4 w-4'])
                                </span>
                                <p class="mt-2 max-w-[76px] text-[10px] font-bold leading-tight transition-colors duration-500"
                                   :class="selesai({{ $idx }}) ? 'text-brand-800' : 'text-gray-400'">{{ $label }}</p>
                            </div>
                            @if (! $loop->last)
                                <div class="relative mt-4 h-0.5 min-w-[8px] flex-1 shrink rounded-full bg-gray-200">
                                    <div class="absolute inset-y-0 left-0 h-full rounded-full bg-brand-800"
                                         :class="lineSelesai({{ $idx }}) ? 'w-full' : 'w-0'"
                                         :style="lineBaru({{ $idx }}) ? `transition: width 650ms cubic-bezier(.4,0,.2,1) {{ $idx }}00ms` : 'transition: none'"></div>
                                    <span :class="(lineBaru({{ $idx }}) && mounted) ? 'animate-line-walk' : 'invisible'"
                                          :style="`animation-delay: {{ $idx }}00ms`"
                                          class="absolute top-1/2 z-10 h-2.5 w-2.5 rounded-full bg-brand-800 ring-4 ring-white"></span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <p class="mt-4 text-center text-[11px] font-medium text-gray-400">
                        @if ($statusSekarang === 'menunggu_pembayaran')
                            @if (! $transaksi->bukti_pembayaran)
                                Waiting for payment receipt.
                            @elseif (! $transaksi->bukti_dikonfirmasi_pada)
                                Receipt uploaded. Tap "Confirm Payment" to submit to store.
                            @else
                                Waiting for store cashier to verify your payment.
                            @endif
                        @elseif ($statusSekarang === 'pembayaran_dikonfirmasi')
                            Payment confirmed. Your bakes will begin preparation shortly.
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_DIPROSES)
                            Your order is currently being prepared in the kitchen.
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                            Your order is ready! Please collect at the cashier.
                        @else
                            Order collected. Thank you for visiting Daisy Bakery!
                        @endif
                    </p>
                @endunless

                <div class="mt-6">
                    <h2 class="text-sm font-bold text-gray-900">Order Details</h2>
                    <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100">
                        @foreach ($detailRingkas as $detail)
                            <div class="flex items-center justify-between p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-gray-900">
                                        {{ $detail->jumlah }}x {{ $detail->nama_produk }}
                                        @if (!empty($detail->ukuran))
                                            <span class="text-brand-800 font-extrabold text-[11px] ml-1">({{ $detail->ukuran }})</span>
                                        @endif
                                        @if (!empty($detail->varian_rasa))
                                            <span class="text-amber-800 font-extrabold text-[11px] ml-1">({{ $detail->varian_rasa }})</span>
                                        @endif
                                    </p>
                                    @if ($detail->catatan)
                                        <p class="truncate text-[11px] text-gray-400">{{ $detail->catatan }}</p>
                                    @endif
                                </div>
                                <p class="shrink-0 text-xs font-bold text-gray-700">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 space-y-1.5 rounded-2xl border border-dashed border-gray-200 p-4">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-semibold text-gray-800">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $diskonItemsShow = [];
                            if (!empty($transaksi->diskon_label)) {
                                $decoded = json_decode($transaksi->diskon_label, true);
                                if (is_array($decoded)) {
                                    $diskonItemsShow = $decoded;
                                }
                            }
                            $stripDiskonPrefix = fn(string $nama): string =>
                                preg_replace('/^(?:Diskon|Voucher)\s*\((.+)\)$/', '$1', $nama);
                        @endphp
                        @if (!empty($diskonItemsShow))
                            @foreach ($diskonItemsShow as $itemDiskon)
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>{{ $stripDiskonPrefix($itemDiskon['nama']) }}</span>
                                    <span class="font-semibold text-red-500">- Rp {{ number_format($itemDiskon['nilai'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @elseif ($transaksi->diskon > 0)
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Discount</span>
                                <span class="font-semibold text-red-500">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-dashed border-gray-200 pt-1.5 text-sm">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="!mt-2 flex justify-between text-[11px] text-gray-400">
                            <span>Payment Status</span>
                            <span class="font-semibold {{ $transaksi->status_pembayaran === 'Lunas' ? 'text-green-600' : 'text-amber-600' }}">{{ $transaksi->status_pembayaran === 'Lunas' ? 'Paid' : 'Unpaid' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pembayaran --}}
            <div>

        {{-- Bukti Pembayaran: pembayaran dilakukan member sendiri di luar
             aplikasi (transfer/QRIS manual), foto ini yang jadi bukti buat
             diverifikasi kasir sebelum status ditandai Lunas. --}}
        @if (! $dibatalkan && $transaksi->status_pembayaran !== 'Lunas')
            @php $terkonfirmasi = ! is_null($transaksi->bukti_dikonfirmasi_pada); @endphp
            <div class="mt-4" x-data="{ mengupload: false, tampilSukses: {{ session('success') ? 'true' : 'false' }} }">
                {{-- Cara Pembayaran: pindah ke halaman terpisah (bukan modal
                     lagi) supaya animasi transisinya sama kayak navigasi
                     halaman lain (animateNavigate). Otomatis ilang begitu
                     status_pembayaran jadi Lunas (dikonfirmasi/di-acc kasir)
                     — lihat @if pembungkus blok ini. --}}
                {{-- Timer batas waktu pembayaran (berhenti setelah member konfirmasi pembayaran, bukan saat upload bukti). Waktu habis -> server
                     membatalkan pesanan otomatis (stok & voucher dikembalikan). --}}
                @if ($transaksi->menungguBuktiBayar())
                    <div x-data="timerBatasBayar({{ $transaksi->sisaDetikBayar() }}, '{{ route('member.pesanan.status', $transaksi) }}')"
                         class="mb-4 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-3.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-amber-600 shadow-xs">
                            @include('mobile.components.icon-clock', ['class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-amber-700">Payment Time Limit</p>
                            <p class="text-[11px] leading-snug text-amber-800">
                                Upload receipt and tap "Confirm Payment" before time expires, or the order will be cancelled automatically.
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-sm font-extrabold tabular-nums"
                              :class="sisa <= 60 ? 'text-red-600' : 'text-amber-700'"
                              x-text="tampil">{{ gmdate('i:s', $transaksi->sisaDetikBayar()) }}</span>
                    </div>
                @endif

                <h2 class="text-sm font-bold text-gray-900">Payment Method</h2>
                <a href="{{ route('member.pesanan.cara-pembayaran', $transaksi) }}"
                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                   class="mt-2 flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-white p-3 text-left transition active:bg-gray-50">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                            <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                            <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                            <path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-bold text-gray-900">Transfer / QRIS</span>
                        <span class="block text-[11px] text-gray-400">Tap to view QRIS code</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none">
                        <path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>

                <h2 class="mt-5 text-sm font-bold text-gray-900">Payment Receipt</h2>

                @if (session('success'))
                    <p x-show="tampilSukses" x-init="setTimeout(() => tampilSukses = false, 4000)"
                       x-transition:leave="transition ease-in duration-300"
                       x-transition:leave-start="opacity-100 translate-y-0"
                       x-transition:leave-end="opacity-0 -translate-y-1"
                       class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-green-600">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 shrink-0"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ session('success') }}</span>
                    </p>
                @endif
                @if (session('error'))
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ session('error') }}</p>
                @endif
                @error('bukti_pembayaran')
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror

                {{-- Alasan penolakan: dikasih kasir waktu bukti sebelumnya ditolak.
                     Cuma tampil selama member belum upload ulang (bukti_pembayaran
                     null lagi), sama kayak logic di panel admin. --}}
                @if ($transaksi->catatan_penolakan && ! $transaksi->bukti_pembayaran)
                    <div class="mt-2 flex items-start gap-2 rounded-2xl border border-red-100 bg-red-50 p-3">
                        <svg viewBox="0 0 24 24" fill="none" class="mt-1 h-4 w-4 shrink-0 text-red-500"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L14.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <div class="text-[11px] text-red-600">
                            <span class="font-bold">Payment receipt rejected:</span>
                            <div class="whitespace-pre-line mt-0.5">{{ $transaksi->catatan_penolakan }}</div>
                            <div class="mt-1 font-medium">Please re-upload a valid payment receipt.</div>
                        </div>
                    </div>
                @endif

                @if ($terkonfirmasi)
                    <div class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-2.5">
                        <button type="button" onclick="openSheet('modal-bukti-pembayaran')"
                                class="group relative h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-gray-200">
                            <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                 class="h-full w-full object-cover">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-white opacity-0 transition group-active:opacity-100"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="absolute bottom-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-black/50 text-white">
                                <svg viewBox="0 0 24 24" fill="none" class="h-2.5 w-2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                        <span class="text-[11px] font-semibold text-green-600">Receipt submitted, waiting for store cashier to verify.</span>
                    </div>

                    {{-- Modal lihat bukti pembayaran ukuran penuh --}}
                    <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Payment Receipt</h3>
                                <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                        class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                            <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                 class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                        </div>
                    </dialog>
                @else
                    <form method="POST" action="{{ route('member.pesanan.upload-bukti', $transaksi) }}"
                          enctype="multipart/form-data" class="mt-2"
                          @submit="mengupload = true">
                        @csrf

                        <label for="bukti_pembayaran"
                               class="relative flex cursor-pointer items-center gap-3 overflow-hidden rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 p-2.5 text-left transition active:bg-gray-100">

                            @if ($transaksi->bukti_pembayaran)
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openSheet('modal-bukti-pembayaran')"
                                        class="group relative z-10 h-12 w-12 shrink-0 overflow-hidden rounded-xl">
                                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                         class="h-full w-full object-cover">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 text-white opacity-0 transition group-active:opacity-100"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="absolute bottom-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-black/50 text-white">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-2.5 w-2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </button>
                                <span class="text-[11px] font-semibold text-brand-800">Tap to change receipt</span>
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 16V4m0 0L7 9m5-5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="flex flex-col items-start">
                                    <span class="text-xs font-bold text-gray-700">Upload Transfer / QRIS Receipt</span>
                                    <span class="text-[11px] text-gray-400">JPG or PNG</span>
                                </span>
                            @endif

                            <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/*"
                                   class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                   @change="if ($event.target.files.length) { $event.target.form.submit(); }">

                            <span x-show="mengupload" x-cloak
                                  class="absolute inset-0 flex items-center justify-center bg-white/80">
                                <svg class="h-5 w-5 animate-spin text-brand-800" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                </svg>
                            </span>
                        </label>
                    </form>

                    @if ($transaksi->bukti_pembayaran)
                        {{-- Modal lihat bukti pembayaran ukuran penuh --}}
                        <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-900">Payment Receipt</h3>
                                    <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                     class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                            </div>
                        </dialog>

                        <form method="POST" action="{{ route('member.pesanan.konfirmasi-bukti', $transaksi) }}" class="mt-2">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-brand-800 py-2.5 text-xs font-bold text-white transition active:bg-brand-900">
                                Confirm Payment
                            </button>
                        </form>
                    @endif
                @endif

                @if (! $transaksi->bukti_pembayaran)
                    <p class="mt-2 text-[11px] text-gray-400">Upload receipt so cashier can verify your payment.</p>
                @elseif (! $terkonfirmasi)
                    <p class="mt-2 text-[11px] text-gray-400">
                        Once confirmed, tap "Confirm Payment" — receipt cannot be modified after confirmation.
                    </p>
                @endif
            </div>
        @elseif ($transaksi->bukti_pembayaran)
            <div class="mt-4">
                <h2 class="text-sm font-bold text-gray-900">Payment Receipt</h2>

                <button type="button" onclick="openSheet('modal-bukti-pembayaran')"
                        class="group relative mt-2 h-20 w-20 overflow-hidden rounded-2xl border border-gray-100">
                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Payment receipt"
                         class="h-full w-full object-cover">
                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white opacity-0 transition group-active:opacity-100">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="absolute bottom-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            </div>

            {{-- Modal lihat bukti pembayaran ukuran penuh --}}
            <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900">Payment Receipt</h3>
                        <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                         class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                </div>
            </dialog>
        @endif

            </div>{{-- end lg:col-span-5 --}}
        </div>{{-- end lg:grid --}}

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE ORDER STATUS
         Matches Daisy Bakery theme: Warm #FAF5E8 cream canvas,
         biscuit cards, display typography, and 2-column layout.
         ════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block w-full bg-[#FAF5E8] py-10 px-8 lg:px-12 xl:px-16 min-h-screen text-[#3C2415]">
        {{-- Breadcrumb back link --}}
        <div class="max-w-6xl mx-auto mb-8">
            <a href="{{ route('member.riwayat') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#3C2415]/60 hover:text-[#8E1E26] transition-colors mb-4">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Back to Orders &amp; Activity</span>
            </a>

            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 border-b border-[#3C2415]/10 pb-6">
                <div>
                    <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl tracking-tight text-[#3C2415]">
                        Order Status
                    </h1>
                </div>
                <div class="text-left md:text-right">
                    <span class="text-xs uppercase tracking-wider text-[#3C2415]/60 font-bold block">Transaction Code</span>
                    <span class="font-display font-bold text-lg text-[#3C2415] block">{{ $transaksi->kode_transaksi }}</span>
                    <span class="text-xs text-[#3C2415]/60 block mt-0.5">{{ $transaksi->created_at->format('d M Y · H:i') }} WIB</span>
                </div>
            </div>
        </div>

        @if ($dibatalkan)
            <div class="max-w-6xl mx-auto mb-8 bg-[#8E1E26]/5 border border-[#8E1E26]/20 rounded-[28px] p-6 lg:p-8 text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#8E1E26]/10 text-[#8E1E26] mb-3">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
                    </svg>
                </div>
                <h2 class="font-display font-black text-xl text-[#8E1E26]">Order Cancelled</h2>
                <p class="mt-1 text-sm text-[#3C2415]/70">{{ $transaksi->kode_transaksi }}</p>
                @if ($transaksi->alasan_pembatalan)
                    <p class="mt-3 text-xs text-[#8E1E26] max-w-lg mx-auto bg-white/60 py-2 px-4 rounded-xl border border-[#8E1E26]/10">
                        <span class="font-bold">Reason:</span> {{ $transaksi->alasan_pembatalan }}
                    </p>
                @endif
                @if ($lunas || $transaksi->bukti_pembayaran)
                    @php
                        $pesanCs = "Halo Daisy Bakery, pesanan {$transaksi->kode_transaksi} saya dibatalkan. Saya ingin menanyakan pengembalian dana.";
                    @endphp
                    <p class="mt-4 text-xs font-medium text-[#3C2415]/70">
                        Need assistance? Contact our <a href="https://wa.me/62882000768044?text={{ urlencode($pesanCs) }}" target="_blank" rel="noopener" class="font-bold text-[#8E1E26] underline">Customer Support via WhatsApp</a>.
                    </p>
                @endif
            </div>
        @else
            {{-- Stepper Progress --}}
            <div class="max-w-6xl mx-auto mb-8 bg-[#F5ECE3] rounded-[28px] p-6 lg:p-8 border border-[#3C2415]/10 shadow-sm">
                <div class="flex items-center justify-between relative">
                    @foreach ($steps as $key => $label)
                        @php
                            $idx = array_search($key, $urutan, true);
                            $isDone = ($indexSekarang !== false && $idx <= $indexSekarang);
                            $isCurrent = ($indexSekarang !== false && $idx === $indexSekarang);
                            
                            $englishLabels = [
                                'menunggu_pembayaran' => 'Awaiting Payment',
                                'pembayaran_dikonfirmasi' => 'Payment Confirmed',
                                \App\Models\Transaksi::STATUS_DIPROSES => 'Preparing Bakes',
                                \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
                                \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Order Completed',
                            ];
                            $displayLabel = $englishLabels[$key] ?? $label;
                        @endphp
                        <div class="flex flex-col items-center text-center relative z-10 flex-1">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center transition-all duration-300 {{ $isDone ? ($isCurrent ? 'bg-[#8E1E26] text-white ring-4 ring-[#8E1E26]/20 shadow-md' : 'bg-[#3C2415] text-[#FAF5E8]') : 'bg-[#FAF5E8] text-[#3C2415]/30 border border-[#3C2415]/15' }}">
                                @if ($isDone && !$isCurrent)
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                @else
                                    @include($stepIcons[$key], ['class' => 'h-5 w-5'])
                                @endif
                            </div>
                            <p class="mt-3 text-xs font-display font-bold leading-tight {{ $isDone ? ($isCurrent ? 'text-[#8E1E26]' : 'text-[#3C2415]') : 'text-[#3C2415]/35' }}">
                                {{ $displayLabel }}
                            </p>
                            @if ($isCurrent)
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-[#8E1E26]/10 text-[9px] font-bold uppercase tracking-wider text-[#8E1E26]">In Progress</span>
                            @endif
                        </div>

                        @if (! $loop->last)
                            @php
                                $lineDone = ($indexSekarang !== false && $idx < $indexSekarang);
                            @endphp
                            <div class="flex-1 h-1 mx-2 relative -mt-7 rounded-full {{ $lineDone ? 'bg-[#3C2415]' : 'bg-[#3C2415]/10' }} transition-colors duration-500"></div>
                        @endif
                    @endforeach
                </div>

                {{-- Status Message Banner --}}
                <div class="mt-8 pt-5 border-t border-[#3C2415]/10 flex items-center justify-center text-center">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-[#FAF5E8] border border-[#3C2415]/10 text-xs font-medium text-[#3C2415]">
                        <span class="flex h-2 w-2 rounded-full {{ $selesaiSemua ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                        @if ($statusSekarang === 'menunggu_pembayaran')
                            @if (! $transaksi->bukti_pembayaran)
                                <span>Awaiting payment receipt. Please complete your transfer or QRIS scan below and upload your receipt.</span>
                            @elseif (! $transaksi->bukti_dikonfirmasi_pada)
                                <span>Receipt uploaded! Please click <strong>&quot;Confirm Payment&quot;</strong> to notify our bakery.</span>
                            @else
                                <span>Receipt submitted. Our cashier is currently reviewing and verifying your transaction.</span>
                            @endif
                        @elseif ($statusSekarang === 'pembayaran_dikonfirmasi')
                            <span>Payment confirmed! Your order will soon enter the kitchen preparation queue.</span>
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_DIPROSES)
                            <span>Our artisan bakers are currently preparing and baking your fresh order.</span>
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                            <span class="text-[#8E1E26] font-bold">Your fresh bakes are ready! Please present your pickup code at the cashier counter.</span>
                        @else
                            <span class="text-emerald-700 font-bold">Order completed. Thank you for baking with Daisy Bakery!</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (! $dibatalkan && $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL && $transaksi->kode_pickup)
            <div class="max-w-6xl mx-auto mb-8 rounded-[28px] bg-gradient-to-br from-[#3C2415] via-[#4A2D1B] to-[#3C2415] p-8 text-center text-[#FAF5E8] shadow-lg relative overflow-hidden border border-[#EADCC8]/20">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/5 pointer-events-none"></div>
                <div class="absolute -left-8 -bottom-8 w-32 h-32 rounded-full bg-white/5 pointer-events-none"></div>
                <span class="inline-block px-3.5 py-1 rounded-full bg-[#FAF5E8]/15 border border-[#FAF5E8]/20 text-[10px] font-bold tracking-widest uppercase text-[#FAF5E8] mb-3">
                    STORE PICKUP PASS
                </span>
                <h3 class="text-xs uppercase tracking-widest text-[#FAF5E8]/70 font-bold">Show this pickup code to cashier</h3>
                <p class="mt-3 font-display font-black text-5xl sm:text-6xl tracking-[0.25em] text-[#FAF5E8] drop-shadow-sm">{{ $transaksi->kode_pickup }}</p>
                <p class="mt-3 text-xs text-[#FAF5E8]/60">Order Reference: {{ $transaksi->kode_transaksi }}</p>
            </div>
        @endif

        <div class="max-w-6xl mx-auto lg:grid lg:grid-cols-12 lg:gap-8 lg:items-start">

            {{-- Kolom Kiri: Order Items & Pricing Breakdown --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-[#F5ECE3] rounded-[28px] p-6 lg:p-8 border border-[#3C2415]/10 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8]">
                                @include('mobile.components.icon-shopping-bag', ['class' => 'h-3.5 w-3.5'])
                            </span>
                            <h2 class="font-display font-black text-lg text-[#3C2415]">Order Items</h2>
                        </div>
                        <span class="text-xs font-bold text-[#3C2415]/60">{{ $detailRingkas->count() }} item{{ $detailRingkas->count() > 1 ? 's' : '' }}</span>
                    </div>

                    {{-- Items list --}}
                    <div class="space-y-3">
                        @foreach ($detailRingkas as $detail)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/8">
                                <div class="min-w-0 pr-4">
                                    <p class="font-display font-bold text-sm text-[#3C2415] truncate">
                                        {{ $detail->jumlah }}× {{ $detail->nama_produk }}
                                        @if (!empty($detail->ukuran))
                                            <span class="inline-block ml-1 px-2 py-0.5 rounded-full bg-[#EADCC8] text-[#3C2415] text-[10px] font-bold">{{ $detail->ukuran }}</span>
                                        @endif
                                        @if (!empty($detail->varian_rasa))
                                            <span class="inline-block ml-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-bold">{{ $detail->varian_rasa }}</span>
                                        @endif
                                    </p>
                                    @if ($detail->catatan)
                                        <p class="text-xs italic text-[#3C2415]/60 mt-1">&quot;{{ $detail->catatan }}&quot;</p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="font-display font-bold text-sm text-[#3C2415]">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pricing Receipt Breakdown --}}
                    <div class="mt-6 pt-5 border-t border-dashed border-[#3C2415]/20 space-y-2.5">
                        <div class="flex justify-between text-xs text-[#3C2415]/70">
                            <span>Subtotal</span>
                            <span class="font-bold text-[#3C2415]">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                        </div>

                        @php
                            $diskonItemsShowDesktop = [];
                            if (!empty($transaksi->diskon_label)) {
                                $decodedDesktop = json_decode($transaksi->diskon_label, true);
                                if (is_array($decodedDesktop)) {
                                    $diskonItemsShowDesktop = $decodedDesktop;
                                }
                            }
                            $stripDiskonPrefixDesktop = fn(string $nama): string =>
                                preg_replace('/^(?:Diskon|Voucher)\s*\((.+)\)$/', '$1', $nama);
                        @endphp
                        @if (!empty($diskonItemsShowDesktop))
                            @foreach ($diskonItemsShowDesktop as $itemDiskonDesktop)
                                <div class="flex justify-between text-xs text-[#3C2415]/70">
                                    <span>{{ $stripDiskonPrefixDesktop($itemDiskonDesktop['nama']) }}</span>
                                    <span class="font-bold text-[#8E1E26]">- Rp {{ number_format($itemDiskonDesktop['nilai'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @elseif ($transaksi->diskon > 0)
                            <div class="flex justify-between text-xs text-[#3C2415]/70">
                                <span>Discount</span>
                                <span class="font-bold text-[#8E1E26]">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-dashed border-[#3C2415]/20 flex items-center justify-between">
                            <span class="font-display font-black text-sm uppercase tracking-wide text-[#3C2415]">Total Payment</span>
                            <span class="font-display font-black text-2xl text-[#3C2415]">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                        </div>

                        <div class="pt-3 flex items-center justify-between">
                            <span class="text-xs text-[#3C2415]/60 font-medium">Payment Status</span>
                            @if ($lunas)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>PAID (LUNAS)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span>AWAITING PAYMENT</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Store Location Card --}}
                <div class="bg-[#FAF5E8] rounded-[28px] p-6 border border-[#3C2415]/10 shadow-sm flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-[#3C2415]/50 block">STORE PICKUP LOCATION</span>
                        <h4 class="font-display font-bold text-base text-[#3C2415] mt-0.5">Daisy Bakery Store</h4>
                        <p class="text-xs text-[#3C2415]/70 mt-0.5">Jl. Cendrawasih 42, Bandung · Kitchen Open 06:00 – 20:00 WIB</p>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Payment, Timer, and Verification --}}
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                @if (! $dibatalkan && $transaksi->status_pembayaran !== 'Lunas')
                    {{-- Timer Batas Waktu Pembayaran --}}
                    @if ($transaksi->menungguBuktiBayar())
                        <div x-data="timerBatasBayar({{ $transaksi->sisaDetikBayar() }}, '{{ route('member.pesanan.status', $transaksi) }}')"
                             class="bg-amber-50 rounded-[28px] p-5 border border-amber-200/80 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-white">
                                        @include('mobile.components.icon-clock', ['class' => 'h-4 w-4'])
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-900">Payment Deadline</span>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-sm font-black tabular-nums border border-amber-200 shadow-xs"
                                      :class="sisa <= 60 ? 'text-red-600' : 'text-amber-800'"
                                      x-text="tampil">{{ gmdate('i:s', $transaksi->sisaDetikBayar()) }}</span>
                            </div>
                            <p class="text-xs text-amber-800/80 leading-relaxed">
                                Please upload your receipt and click <strong>&quot;Confirm Payment&quot;</strong> before time expires to avoid automatic cancellation.
                            </p>
                        </div>
                    @endif

                    {{-- Cara Pembayaran (Transfer / QRIS) --}}
                    <div class="bg-[#F5ECE3] rounded-[28px] p-6 border border-[#3C2415]/10 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 17v4"/>
                                </svg>
                            </span>
                            <h3 class="font-display font-bold text-sm uppercase tracking-wide text-[#3C2415]">Payment Method</h3>
                        </div>

                        <button type="button" onclick="openSheet('modal-qris-desktop')"
                                class="w-full flex items-center justify-between p-4 rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/10 hover:border-[#8E1E26]/40 transition-all group text-left">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#3C2415] text-[#FAF5E8] transition-transform group-hover:scale-105">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
                                        <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
                                        <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <span class="font-display font-bold text-sm text-[#3C2415] block">Bank Transfer / QRIS</span>
                                    <span class="text-xs text-[#3C2415]/60 block">Click to view QRIS barcode &amp; instructions</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                View QR →
                            </span>
                        </button>
                    </div>

                    {{-- Bukti Pembayaran Dropzone & Confirm --}}
                    @php $terkonfirmasiDesktop = ! is_null($transaksi->bukti_dikonfirmasi_pada); @endphp
                    <div class="bg-[#F5ECE3] rounded-[28px] p-6 border border-[#3C2415]/10 shadow-sm"
                         x-data="{ menguploadDesktop: false }">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                            </span>
                            <h3 class="font-display font-bold text-sm uppercase tracking-wide text-[#3C2415]">Payment Receipt Proof</h3>
                        </div>

                        {{-- Alerts --}}
                        @if (session('success'))
                            @php
                                $suksesMsg = trim((string) session('success'));
                                $mapSukses = [
                                    'Bukti pembayaran dikonfirmasi. Menunggu verifikasi dari toko.' => 'Payment receipt confirmed. Waiting for store verification.',
                                    'Bukti pembayaran dikonfirmasi. Menunggu verifikasi dari toko' => 'Payment receipt confirmed. Waiting for store verification.',
                                    'Pesanan berhasil dibuat! Kami akan proses pesananmu segera.' => 'Order placed successfully! We will prepare your order shortly.',
                                    'Pesanan berhasil dibuat! Tunjukkan kode pickup ke kasir saat mengambil.' => 'Order placed successfully! Present your pickup code to cashier when collecting.',
                                    'Bukti pembayaran berhasil diupload.' => 'Payment receipt uploaded successfully.',
                                    'Bukti pembayaran berhasil diupload' => 'Payment receipt uploaded successfully.',
                                    'Batas waktu pembayaran habis, pesanan dibatalkan otomatis.' => 'Payment time limit has expired, order was automatically cancelled.',
                                    'Batas waktu pembayaran sudah habis, pesanan dibatalkan otomatis.' => 'Payment time limit has expired, order was automatically cancelled.',
                                ];
                                $suksesMsg = $mapSukses[$suksesMsg] ?? $suksesMsg;
                            @endphp
                            <div class="mb-4 flex items-center gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-semibold">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>{{ $suksesMsg }}</span>
                            </div>
                        @endif
                        @if (session('error'))
                            @php
                                $errMsg = trim((string) session('error'));
                                $mapError = [
                                    'Batas waktu pembayaran sudah habis, pesanan dibatalkan otomatis.' => 'Payment time limit has expired, order was automatically cancelled.',
                                    'Batas waktu pembayaran habis, pesanan dibatalkan otomatis.' => 'Payment time limit has expired, order was automatically cancelled.',
                                    'Pesanan ini sudah dibatalkan, tidak bisa upload bukti pembayaran.' => 'This order has already been cancelled, cannot upload payment receipt.',
                                    'Bukti pembayaran sudah dikonfirmasi, tidak bisa diubah lagi.' => 'Payment receipt has already been confirmed and cannot be modified.',
                                    'Bukti pembayaran sudah dikonfirmasi sebelumnya.' => 'Payment receipt has already been confirmed.',
                                    'Upload bukti pembayaran dulu sebelum konfirmasi.' => 'Please upload your payment receipt before confirming.',
                                    'Pesanan ini sudah dibatalkan.' => 'This order has already been cancelled.',
                                ];
                                $errMsg = $mapError[$errMsg] ?? $errMsg;
                            @endphp
                            <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 font-semibold">
                                {{ $errMsg }}
                            </div>
                        @endif
                        @error('bukti_pembayaran')
                            <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 font-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($transaksi->catatan_penolakan && ! $transaksi->bukti_pembayaran)
                            <div class="mb-4 p-3.5 rounded-2xl border border-red-200 bg-red-50 text-xs text-red-700">
                                <span class="font-bold block mb-1">Previous Receipt Rejected:</span>
                                <p class="whitespace-pre-line">{{ $transaksi->catatan_penolakan }}</p>
                                <p class="mt-1.5 font-medium text-red-600">Please re-upload a clear transfer receipt.</p>
                            </div>
                        @endif

                        @if ($terkonfirmasiDesktop)
                            <div class="p-4 rounded-2xl bg-[#FAF5E8] border border-emerald-200 flex items-center gap-4">
                                <button type="button" onclick="openSheet('modal-bukti-pembayaran-desktop')"
                                        class="group relative h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-[#3C2415]/15 shadow-xs">
                                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti Pembayaran"
                                         class="h-full w-full object-cover">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                                        </svg>
                                    </span>
                                </button>
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs font-bold text-emerald-800 block">Receipt Submitted</span>
                                    <span class="text-xs text-[#3C2415]/70 block mt-0.5">Currently waiting for store cashier to verify and accept.</span>
                                </div>
                            </div>
                        @else
                            {{-- Form Upload Bukti --}}
                            <form method="POST" action="{{ route('member.pesanan.upload-bukti', $transaksi) }}"
                                  enctype="multipart/form-data" class="mt-2"
                                  @submit="menguploadDesktop = true">
                                @csrf

                                <label for="bukti_pembayaran_desktop"
                                       class="relative flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-[#3C2415]/20 bg-[#FAF5E8] hover:bg-[#FAF5E8]/80 cursor-pointer transition-all text-center">
                                    @if ($transaksi->bukti_pembayaran)
                                        <div class="flex items-center gap-3">
                                            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openSheet('modal-bukti-pembayaran-desktop')"
                                                    class="group relative h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-[#3C2415]/15">
                                                <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti Pembayaran"
                                                     class="h-full w-full object-cover">
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                                                    </svg>
                                                </span>
                                            </button>
                                            <div class="text-left">
                                                <span class="text-xs font-bold text-[#3C2415] block">Receipt Uploaded</span>
                                                <span class="text-xs text-[#8E1E26] font-semibold underline block mt-0.5">Click to replace image</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#EADCC8] text-[#3C2415] mb-2">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                <polyline points="17 8 12 3 7 8"/>
                                                <line x1="12" y1="3" x2="12" y2="15"/>
                                            </svg>
                                        </span>
                                        <span class="text-xs font-bold text-[#3C2415] block">Upload Transfer / QRIS Receipt</span>
                                        <span class="text-[11px] text-[#3C2415]/60 block mt-0.5">Click to browse JPG or PNG (max 5MB)</span>
                                    @endif

                                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran_desktop" accept="image/*"
                                           class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                           @change="if ($event.target.files.length) { $event.target.form.submit(); }">

                                    <span x-show="menguploadDesktop" x-cloak
                                          class="absolute inset-0 flex items-center justify-center bg-[#FAF5E8]/90 rounded-2xl">
                                        <svg class="h-6 w-6 animate-spin text-[#8E1E26]" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                        </svg>
                                    </span>
                                </label>
                            </form>

                            {{-- Confirm Button if proof uploaded --}}
                            @if ($transaksi->bukti_pembayaran)
                                <form method="POST" action="{{ route('member.pesanan.konfirmasi-bukti', $transaksi) }}" class="mt-4">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-3.5 px-6 rounded-full bg-[#8E1E26] hover:bg-[#72181E] text-[#FAF5E8] font-bold text-xs uppercase tracking-widest shadow-md transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                                        <span>CONFIRM PAYMENT</span>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </form>
                                <p class="text-[11px] text-center text-[#3C2415]/60 mt-2">
                                    Once confirmed, our cashier will immediately verify your payment.
                                </p>
                            @endif
                        @endif
                    </div>
                @elseif ($lunas)
                    {{-- Paid Confirmation Card --}}
                    <div class="bg-emerald-50 rounded-[28px] p-6 border border-emerald-200 shadow-sm text-center">
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 mb-3">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <h3 class="font-display font-black text-lg text-emerald-950">Payment Verified &amp; Paid</h3>
                        <p class="text-xs text-emerald-800 mt-1">
                            Your payment of <strong>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</strong> has been verified.
                        </p>
                        @if ($transaksi->bukti_pembayaran)
                            <button type="button" onclick="openSheet('modal-bukti-pembayaran-desktop')"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-emerald-200 text-xs font-bold text-emerald-900 shadow-xs hover:bg-emerald-50 transition-colors">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                                </svg>
                                <span>View Receipt</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        {{-- Modals for Desktop --}}
        {{-- 1. Desktop QRIS Modal --}}
        <dialog id="modal-qris-desktop" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-lg rounded-[28px] p-0 shadow-2xl border border-[#3C2415]/10 bg-[#FAF5E8] backdrop:bg-[#3C2415]/40 backdrop:backdrop-blur-sm">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                            </svg>
                        </span>
                        <h3 class="font-display font-black text-lg text-[#3C2415]">Scan QRIS Payment</h3>
                    </div>
                    <button type="button" onclick="closeSheet('modal-qris-desktop')"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#3C2415]/5 hover:bg-[#3C2415]/10 text-[#3C2415] transition-colors">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="text-center mt-6">
                    <div class="inline-block p-3 rounded-2xl bg-white border border-[#3C2415]/10 shadow-sm">
                        <img src="{{ \App\Models\QrisImage::urlAktif() }}" alt="Kode QRIS"
                             class="h-60 w-60 object-contain mx-auto">
                    </div>

                    <div class="mt-4">
                        <span class="text-xs uppercase tracking-wider text-[#3C2415]/60 font-bold block">Total Amount Due</span>
                        <span class="font-display font-black text-2xl text-[#3C2415] block mt-0.5">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                        <span class="text-xs text-[#3C2415]/60 block mt-0.5">{{ $transaksi->kode_transaksi }}</span>
                    </div>

                    <div class="mt-6 text-left p-4 rounded-2xl bg-[#F5ECE3] border border-[#3C2415]/10">
                        <span class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] block mb-2">Payment Steps:</span>
                        <ol class="space-y-1.5 text-xs text-[#3C2415]/80 list-decimal list-inside">
                            <li>Open your banking app or e-wallet (BCA, Mandiri, GoPay, OVO, Dana).</li>
                            <li>Select <strong>Scan QRIS</strong> and point camera to the QR code above.</li>
                            <li>Ensure payment nominal matches exactly <strong>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</strong>.</li>
                            <li>Take a screenshot of the successful transfer, then upload it on this page.</li>
                        </ol>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('member.pesanan.cara-pembayaran', $transaksi) }}"
                           class="px-5 py-2.5 rounded-full border border-[#3C2415]/20 text-xs font-bold text-[#3C2415] hover:bg-[#3C2415]/5 transition-colors">
                            Full Instructions Page
                        </a>
                        <button type="button" onclick="closeSheet('modal-qris-desktop')"
                                class="px-6 py-2.5 rounded-full bg-[#3C2415] text-[#FAF5E8] text-xs font-bold hover:bg-[#4A2D1B] transition-colors">
                            Got It, Close
                        </button>
                    </div>
                </div>
            </div>
        </dialog>

        {{-- 2. Desktop Receipt Full View Modal --}}
        <dialog id="modal-bukti-pembayaran-desktop" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-lg rounded-[28px] p-0 shadow-2xl border border-[#3C2415]/10 bg-[#FAF5E8] backdrop:bg-[#3C2415]/40 backdrop:backdrop-blur-sm">
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4 mb-4">
                    <h3 class="font-display font-black text-lg text-[#3C2415]">Payment Receipt Proof</h3>
                    <button type="button" onclick="closeSheet('modal-bukti-pembayaran-desktop')"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#3C2415]/5 hover:bg-[#3C2415]/10 text-[#3C2415] transition-colors">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @if ($transaksi->bukti_pembayaran)
                    <div class="rounded-2xl overflow-hidden border border-[#3C2415]/10 bg-white p-2">
                        <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti Pembayaran"
                             class="w-full max-h-[70vh] object-contain rounded-xl mx-auto">
                    </div>
                @endif
            </div>
        </dialog>
    </div>
    {{-- ════════════════════════════════════════════════════════════════
         END DESKTOP VIEW
         ════════════════════════════════════════════════════════════════ --}}

    </div>

    <style>
        /* Animasi step "maju" di timeline status pesanan — cuma dipicu buat
           step yang baru aja jadi selesai dibanding kunjungan sebelumnya
           (lihat pesananTimeline() di bawah), bukan tiap kali halaman dibuka. */
        @keyframes step-pop {
            0% { transform: scale(0.55); opacity: 0.4; }
            60% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-step-pop {
            animation: step-pop 500ms cubic-bezier(.34,1.56,.64,1) both;
        }

        /* Titik yang "berjalan" menyusuri garis dari satu step ke step
           berikutnya — muncul, meluncur dari kiri ke kanan mengikuti garis
           yang lagi keisi, lalu menghilang pas nyampe ujung. */
        @keyframes line-walk {
            0% { left: 0%; opacity: 0; transform: translate(-50%, -50%) scale(0.6); }
            12% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            88% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            100% { left: 100%; opacity: 0; transform: translate(-50%, -50%) scale(0.6); }
        }
        .animate-line-walk {
            animation: line-walk 650ms cubic-bezier(.4,0,.2,1) both;
        }
    </style>

    <script>
        // Bandingin index step sekarang vs terakhir kali halaman ini dibuka
        // (disimpan di localStorage per transaksi) supaya cuma step yang
        // baru maju yang dianimasikan — bukan seluruh timeline tiap reload.
        // Hitung mundur batas waktu upload bukti pembayaran. Dihitung dari sisa
        // detik versi SERVER (bukan jam HP) supaya tidak terpengaruh jam HP yang
        // salah. Saat habis, panggil endpoint status (server membatalkan otomatis
        // pesanan yang kedaluwarsa) lalu muat ulang halaman.
        function timerBatasBayar(sisaAwal, statusUrl) {
            return {
                sisa: sisaAwal,
                akhir: Date.now() + sisaAwal * 1000,
                timer: null,
                selesai: false,
                get tampil() {
                    const m = Math.floor(this.sisa / 60);
                    const s = this.sisa % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
                init() {
                    this.hitung();
                    this.timer = setInterval(() => this.hitung(), 1000);
                },
                destroy() {
                    clearInterval(this.timer);
                },
                hitung() {
                    this.sisa = Math.max(0, Math.ceil((this.akhir - Date.now()) / 1000));
                    if (this.sisa <= 0 && ! this.selesai) {
                        this.selesai = true;
                        clearInterval(this.timer);
                        fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                            .catch(() => {})
                            .finally(() => window.location.reload());
                    }
                },
            };
        }

        function pesananTimeline(currentIndex, transaksiId) {
            return {
                currentIndex,
                prevIndex: -1,
                mounted: false,
                init() {
                    const key = `pesanan_progress_${transaksiId}`;
                    const stored = localStorage.getItem(key);
                    this.prevIndex = stored !== null ? parseInt(stored, 10) : -1;
                    if (this.currentIndex >= 0) {
                        localStorage.setItem(key, this.currentIndex);
                    }
                    // Kasih jeda semenit browser buat gambar dulu state
                    // "kosong"-nya, baru diflip ke penuh — supaya transisi
                    // CSS-nya beneran keliatan tumbuh, bukan langsung penuh
                    // pas render pertama.
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        this.mounted = true;
                    }));
                },
                selesai(idx) {
                    return this.currentIndex >= 0 && idx <= this.currentIndex;
                },
                baru(idx) {
                    return this.selesai(idx) && idx > this.prevIndex;
                },
                lineDone(idx) {
                    return this.currentIndex >= 0 && idx < this.currentIndex;
                },
                lineBaru(idx) {
                    return this.lineDone(idx) && idx >= this.prevIndex;
                },
                lineSelesai(idx) {
                    if (! this.lineDone(idx)) return false;
                    return this.lineBaru(idx) ? this.mounted : true;
                },
            };
        }
    </script>

@endsection