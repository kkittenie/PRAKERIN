@extends('mobile.layout', ['title' => 'Status Pesanan'])

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
            \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
        ];
        $stepIcons = [
            \App\Models\Transaksi::STATUS_DIPROSES => 'mobile.components.icon-package',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'mobile.components.icon-shopping-bag',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'mobile.components.icon-check-circle',
        ];
        $statusSekarang = $transaksi->status_pesanan;
    } else {
        $steps = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'pembayaran_dikonfirmasi' => 'Pembayaran Dikonfirmasi',
            \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
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

        <div class="relative flex items-center justify-center">
            <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition active:bg-gray-50">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-sm font-bold text-gray-900">Status Pesanan</h1>
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-8 lg:items-start lg:mt-6">

            {{-- Kolom Kiri: Status & Items --}}
            <div class="lg:col-span-7">
                @if ($dibatalkan)
                    <div class="mt-6 lg:mt-0 rounded-2xl border border-red-100 bg-red-50 p-4 text-center">
                        <p class="text-xs font-bold text-red-600">Pesanan Dibatalkan</p>
                        <p class="mt-1 text-[11px] text-red-400">{{ $transaksi->kode_transaksi }}</p>
                        @if ($transaksi->alasan_pembatalan)
                            <p class="mt-3 text-[11px] text-red-600">
                                <span class="font-bold">Alasan:</span> {{ $transaksi->alasan_pembatalan }}
                            </p>
                        @endif
                        {{-- Info refund cuma relevan kalau member sudah bayar / upload bukti --}}
                        @if ($lunas || $transaksi->bukti_pembayaran)
                            @php
                                $pesanCs = "Halo Saisho, pesanan {$transaksi->kode_transaksi} saya dibatalkan. Saya ingin menanyakan pengembalian dana.";
                            @endphp
                            <p class="mt-2 text-[11px] font-medium text-red-500">
                                Hubungi <a href="https://wa.me/6281313365554?text={{ urlencode($pesanCs) }}" target="_blank" rel="noopener" class="underline">customer service</a> untuk pengembalian dana.
                            </p>
                        @endif
                    </div>
                @endif

                @if (! $dibatalkan && $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL && $transaksi->kode_pickup)
                    <div class="mt-6 lg:mt-0 rounded-3xl bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 p-6 text-center text-white">
                        <p class="text-xs font-bold uppercase tracking-wide text-white/70">Tunjukkan kode ini ke kasir</p>
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
                                Menunggu bukti pembayaran.
                            @elseif (! $transaksi->bukti_dikonfirmasi_pada)
                                Bukti sudah diupload. Tap "Konfirmasi Pembayaran" untuk dikirim ke toko.
                            @else
                                Menunggu pembayaran kamu diverifikasi oleh toko.
                            @endif
                        @elseif ($statusSekarang === 'pembayaran_dikonfirmasi')
                            Pembayaran kamu sudah dikonfirmasi toko. Pesanan akan segera mulai diproses.
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_DIPROSES)
                            Pesanan kamu sedang disiapkan oleh toko.
                        @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                            Pesanan sudah siap! Silakan ambil di kasir.
                        @else
                            Pesanan sudah kamu ambil. Terima kasih!
                        @endif
                    </p>
                @endunless

                <div class="mt-6">
                    <h2 class="text-sm font-bold text-gray-900">Detail Pesanan</h2>
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
                                <span>Diskon</span>
                                <span class="font-semibold text-red-500">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-dashed border-gray-200 pt-1.5 text-sm">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                        </div>
                        <div class="!mt-2 flex justify-between text-[11px] text-gray-400">
                            <span>Status Pembayaran</span>
                            <span class="font-semibold {{ $transaksi->status_pembayaran === 'Lunas' ? 'text-green-600' : 'text-amber-600' }}">{{ $transaksi->status_pembayaran }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pembayaran --}}
            <div class="lg:col-span-5 lg:sticky lg:top-24">

        {{-- Bukti Pembayaran: pembayaran dilakukan member sendiri di luar
             aplikasi (transfer/QRIS manual), foto ini yang jadi bukti buat
             diverifikasi kasir sebelum status ditandai Lunas. --}}
        @if ($transaksi->status_pembayaran !== 'Lunas')
            @php $terkonfirmasi = ! is_null($transaksi->bukti_dikonfirmasi_pada); @endphp
            <div class="mt-4" x-data="{ mengupload: false, tampilSukses: {{ session('success') ? 'true' : 'false' }} }">
                {{-- Cara Pembayaran: pindah ke halaman terpisah (bukan modal
                     lagi) supaya animasi transisinya sama kayak navigasi
                     halaman lain (animateNavigate). Otomatis ilang begitu
                     status_pembayaran jadi Lunas (dikonfirmasi/di-acc kasir)
                     — lihat @if pembungkus blok ini. --}}
                <h2 class="text-sm font-bold text-gray-900">Cara Pembayaran</h2>
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
                        <span class="block text-[11px] text-gray-400">Tap untuk lihat kode QRIS</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none">
                        <path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>

                <h2 class="mt-5 text-sm font-bold text-gray-900">Bukti Pembayaran</h2>

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
                        <p class="text-[11px] text-red-600">
                            <span class="font-bold">Bukti pembayaran ditolak:</span>
                            {{ $transaksi->catatan_penolakan }} Silakan upload ulang bukti pembayaran yang benar.
                        </p>
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
                        <span class="text-[11px] font-semibold text-green-600">Bukti sudah dikonfirmasi, menunggu verifikasi toko.</span>
                    </div>

                    {{-- Modal lihat bukti pembayaran ukuran penuh --}}
                    <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
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
                                <span class="text-[11px] font-semibold text-brand-800">Tap untuk ganti bukti</span>
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 16V4m0 0L7 9m5-5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="flex flex-col items-start">
                                    <span class="text-xs font-bold text-gray-700">Upload bukti transfer / QRIS</span>
                                    <span class="text-[11px] text-gray-400">JPG atau PNG</span>
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
                                    <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
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
                                Konfirmasi Pembayaran
                            </button>
                        </form>
                    @endif
                @endif

                @if (! $transaksi->bukti_pembayaran)
                    <p class="mt-2 text-[11px] text-gray-400">Upload bukti agar kasir bisa verifikasi.</p>
                @elseif (! $terkonfirmasi)
                    <p class="mt-2 text-[11px] text-gray-400">
                        Setelah yakin fotonya benar, tap "Konfirmasi Pembayaran" — bukti tidak bisa diganti lagi setelah dikonfirmasi.
                    </p>
                @endif
            </div>
        @elseif ($transaksi->bukti_pembayaran)
            <div class="mt-4">
                <h2 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h2>

                <button type="button" onclick="openSheet('modal-bukti-pembayaran')"
                        class="group relative mt-2 h-20 w-20 overflow-hidden rounded-2xl border border-gray-100">
                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
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
                        <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
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