@extends('mobile.layout', ['title' => 'Beranda'])

@php
    $waCs = '6282000768044';

    $warnaBadgeTier = match ($member?->warna_badge_level ?? 'gray') {
        'yellow' => 'bg-yellow-600',
        'slate' => 'bg-slate-500',
        'amber' => 'bg-amber-700',
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'purple' => 'bg-purple-600',
        'red' => 'bg-red-600',
        default => 'bg-gray-500',
    };

    $warnaTierMap = fn ($warna) => match ($warna) {
        'yellow' => 'bg-yellow-600',
        'slate' => 'bg-slate-500',
        'amber' => 'bg-amber-700',
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'purple' => 'bg-purple-600',
        'red' => 'bg-red-600',
        default => 'bg-gray-500',
    };

    $progresTier = $member?->progres_tier;
@endphp

@section('content')

    <div>

        {{-- Hero Section (Mobile: original stacked overlap, Desktop: 2-column grid) --}}
        <div class="-mx-5 sm:mx-0 relative lg:grid lg:grid-cols-12 lg:gap-6 lg:items-start">
            {{-- Notification button (Mobile only floating) --}}
            <a href="{{ route('member.notifikasi.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                class="absolute right-4 top-4 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur transition hover:bg-black/40 lg:hidden">
                <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span id="notif-unread-dot" class="hidden absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full bg-red-500 ring-2 ring-black/30"></span>
            </a>

            @if (count($promoBanners))
                <div class="relative lg:col-span-7 xl:col-span-8">
                    {{-- Mobile height: h-80 md:h-[380px] (exact original mobile height), Desktop height: lg:h-[350px] xl:h-[380px] --}}
                    <div id="promo-viewport" class="relative h-80 md:h-[380px] lg:h-[350px] xl:h-[380px] landscape:h-64 md:landscape:h-72 w-full touch-pan-x overflow-hidden rounded-b-[1.5rem] sm:rounded-3xl shadow-sm">
                        <div id="promo-track" class="flex h-full w-full transition-transform duration-[450ms] ease-out">
                            @foreach ($promoBanners as $banner)
                                <div class="relative h-full w-full shrink-0 flex-none">
                                    @if (! empty($banner['gambar']))
                                        <img src="{{ asset($banner['gambar']) }}" alt="Promo Saisho" class="absolute inset-0 h-full w-full object-cover object-center">
                                    @else
                                        <div class="absolute inset-0 bg-gradient-to-br {{ $banner['gradient'] }}"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if (count($promoBanners) > 1)
                            <div id="promo-dots" class="pointer-events-none absolute inset-x-0 bottom-16 lg:bottom-4 z-20 flex items-center justify-center gap-1.5">
                                @foreach ($promoBanners as $i => $banner)
                                    <button type="button" onclick="goToPromoSlide({{ $i }})" class="promo-dot pointer-events-auto rounded-full bg-white/50 transition-all {{ $i === 0 ? 'h-2.5 w-2.5 !bg-white' : 'h-1.5 w-1.5' }}" aria-label="Promo {{ $i + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <script>
                (() => {
                    const viewport = document.getElementById('promo-viewport');
                    const track = document.getElementById('promo-track');
                    if (!viewport || !track) return;

                    const totalSlides = track.children.length;
                    const dots = document.querySelectorAll('#promo-dots .promo-dot');
                    let current = 0;
                    let autoTimer = null;

                    window.goToPromoSlide = (index) => {
                        current = (index + totalSlides) % totalSlides;
                        track.style.transform = `translateX(-${current * 100}%)`;
                        dots.forEach((dot, i) => {
                            dot.classList.toggle('h-2.5', i === current);
                            dot.classList.toggle('w-2.5', i === current);
                            dot.classList.toggle('!bg-white', i === current);
                            dot.classList.toggle('h-1.5', i !== current);
                            dot.classList.toggle('w-1.5', i !== current);
                        });
                    };

                    const startAuto = () => {
                        stopAuto();
                        if (totalSlides > 1) {
                            autoTimer = setInterval(() => window.goToPromoSlide(current + 1), 4500);
                        }
                    };
                    const stopAuto = () => { if (autoTimer) clearInterval(autoTimer); };

                    let startX = 0;
                    let dragging = false;

                    viewport.addEventListener('touchstart', (e) => {
                        startX = e.touches[0].clientX;
                        dragging = true;
                        stopAuto();
                    }, { passive: true });

                    viewport.addEventListener('touchend', (e) => {
                        if (!dragging) return;
                        const diffX = startX - e.changedTouches[0].clientX;
                        if (diffX > 35) window.goToPromoSlide(current + 1);
                        else if (diffX < -35) window.goToPromoSlide(current - 1);
                        dragging = false;
                        startAuto();
                    }, { passive: true });

                    let mouseStartX = 0;
                    let mouseDragging = false;

                    viewport.addEventListener('mousedown', (e) => {
                        mouseStartX = e.clientX;
                        mouseDragging = true;
                        stopAuto();
                    });

                    viewport.addEventListener('mousemove', (e) => {
                        if (!mouseDragging) return;
                        e.preventDefault();
                    });

                    window.addEventListener('mouseup', (e) => {
                        if (!mouseDragging) return;
                        const diffX = mouseStartX - e.clientX;
                        if (diffX > 35) window.goToPromoSlide(current + 1);
                        else if (diffX < -35) window.goToPromoSlide(current - 1);
                        mouseDragging = false;
                        startAuto();
                    });

                    viewport.addEventListener('mouseleave', () => {
                        if (mouseDragging) {
                            mouseDragging = false;
                            startAuto();
                        }
                    });

                    startAuto();
                })();
            </script>

            {{-- Right Column on Desktop (Greeting Card + Quick Actions + Stamp), stacked on Mobile --}}
            <div class="relative z-20 {{ count($promoBanners) ? '-mt-10 md:-mt-14 lg:mt-0' : 'mt-0' }} px-5 sm:px-8 md:px-10 lg:px-0 lg:col-span-5 xl:col-span-4 flex flex-col gap-3">
                @auth('member')
                    <div class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white p-5 shadow-lg shadow-gray-900/10">
                        <svg class="pointer-events-none absolute right-4 top-3 h-14 w-16 text-brand-200" viewBox="0 0 90 60" fill="none">
                            <circle cx="70" cy="16" r="8" fill="currentColor" opacity=".5"/>
                            <circle cx="52" cy="38" r="5" fill="currentColor" opacity=".7"/>
                            <circle cx="80" cy="42" r="4" fill="currentColor" opacity=".6"/>
                            <path d="M40 10l2 5 5 2-5 2-2 5-2-5-5-2 5-2 2-5Z" fill="currentColor" opacity=".8"/>
                        </svg>

                        <p class="text-lg font-extrabold text-gray-900">Hi {{ explode(' ', $member->nama)[0] }}!</p>
                        <p class="mt-0.5 text-xs text-gray-400">Terima kasih sudah jadi pelanggan setia Saisho.</p>

                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-dashed border-gray-100 pt-4">
                            <button type="button" onclick="openSheet('sheet-info-poin')"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800 transition active:scale-95">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 p-1 text-brand-700">
                                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                </span>
                                {{ number_format($member->poin, 0, ',', '.') }} Poin
                            </button>
                            <button type="button" onclick="openSheet('sheet-info-tier')"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800 transition active:scale-95">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $warnaBadgeTier }} text-white">
                                    @include('mobile.components.icon-crown', ['class' => 'h-3.5 w-3.5'])
                                </span>
                                Tier {{ $member->level }}
                            </button>
                        </div>
                    </div>

                    {{-- Aksi cepat member: tukar poin & riwayat --}}
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white/95 backdrop-blur-lg p-4 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                @include('mobile.components.icon-gift', ['class' => 'h-5 w-5'])
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-bold text-gray-900">Tukar Poin</span>
                                <span class="block truncate text-[11px] text-gray-400">Lihat katalog</span>
                            </span>
                        </a>
                        <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white/95 backdrop-blur-lg p-4 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                @include('mobile.components.icon-clock', ['class' => 'h-5 w-5'])
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-bold text-gray-900">Riwayat</span>
                                <span class="block truncate text-[11px] text-gray-400">Poin & redeem</span>
                            </span>
                        </a>
                    </div>

                    @isset($program)
                        @php
                            $stampTarget = max(1, $program->jumlah_stamp_dibutuhkan);
                            $stampCurrent = min($memberStamp->jumlah_stamp ?? 0, $stampTarget);
                            $stampPenuh = ($memberStamp->jumlah_stamp ?? 0) >= $stampTarget;
                            $stampDotCount = min($stampTarget, 8);
                        @endphp
                        <a href="{{ route('member.stamps.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           class="block overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-4 text-white shadow-sm transition active:scale-[0.99]">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
                                        @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Program Stamp</p>
                                        <p class="truncate text-sm font-extrabold">{{ $program->nama_program }}</p>
                                    </div>
                                </div>
                                <span class="flex shrink-0 items-center gap-1 text-xs font-bold text-white">
                                    @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-1.5">
                                @for ($i = 1; $i <= $stampDotCount; $i++)
                                    @php $terisi = $i <= $stampCurrent; @endphp
                                    <span class="stamp-dot relative flex h-6 w-6 items-center justify-center rounded-full {{ $terisi ? 'stamp-dot-filled bg-white text-brand-800' : 'border-2 border-dashed border-white/35 text-white/35' }}"
                                          @if ($terisi) style="animation-delay: {{ ($i - 1) * 0.12 }}s;" @endif>
                                        @include('mobile.components.icon-cake', ['class' => 'h-3 w-3'])
                                    </span>
                                @endfor
                                @if ($stampTarget > $stampDotCount)
                                    <span class="ml-1 text-[11px] font-semibold text-white/60">+{{ $stampTarget - $stampDotCount }}</span>
                                @endif
                            </div>

                            <p class="mt-3 text-xs font-semibold {{ $stampPenuh ? 'text-white' : 'text-white/75' }}">
                                @if ($stampPenuh)
                                    Stamp penuh, yuk tukar! 🎉
                                @else
                                    {{ $stampCurrent }}/{{ $stampTarget }} stamp &middot; {{ $stampTarget - $stampCurrent }} lagi untuk tukar hadiah
                                @endif
                            </p>
                        </a>
                    @endisset
                @else
                    {{-- Guest Welcome Card --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#5a3c27] via-[#704b30] to-[#8b5e3c] p-6 text-white shadow-lg">
                        <h2 class="mt-2 text-xl font-extrabold tracking-tight">Selamat Datang di Saisho!</h2>
                        <p class="mt-1 text-xs text-brand-100 leading-relaxed">Nikmati aneka kue, roti & minuman segar spesial. Login sekarang untuk kumpulkan poin & voucher gratis.</p>
                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('member.login') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-brand-800 shadow-sm hover:bg-brand-50">
                                Masuk ke Akun
                            </a>
                            <a href="{{ route('member.register') }}" class="rounded-xl border border-white/40 px-4 py-2.5 text-xs font-bold text-white backdrop-blur hover:bg-white/10">
                                Daftar Sekarang
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Produk pilihan --}}
        <div class="mt-7 lg:mt-10 flex items-center justify-between">
            <h2 class="text-sm lg:text-base font-bold text-gray-900">Produk Pilihan</h2>
            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                Lihat semua
                @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
            </a>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 lg:gap-5">
            @forelse ($produkPilihan as $produk)
                @php
                    $hargaAsli = $produk->harga_jual;
                    $hargaFinal = $produk->harga_diskon;
                    $adaPromo = $hargaFinal < $hargaAsli;
                    $habis = $produk->punya_ukuran
                        ? $produk->habisSemuaUkuran()
                        : ($produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0);
                @endphp
                <a href="{{ route('member.produk.show', $produk) }}"
                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                   class="block"
                   @unless($produk->punya_ukuran) data-produk-id="{{ $produk->id }}" @endunless>
                    <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-50">
                        @if ($produk->foto)
                            <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                 class="h-full w-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        @endif
                        <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                            @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                        </div>
                        <span data-promo-badge class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm" style="{{ $adaPromo ? '' : 'display:none' }}">Promo</span>
                        @if ($habis)
                            <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Perlu Diproduksi</span>
                        @endif
                    </div>

                    <div class="pt-2">
                        <p class="text-[10px] font-medium text-gray-400">{{ $produk->kategori->nama_kategori ?? '' }}</p>
                        <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $produk->nama_produk }}</p>

                        <div class="mt-2 flex items-center justify-between gap-1">
                            <div class="min-w-0">
                                @if ($produk->punya_ukuran)
                                    @php
                                        $daftarHarga = collect($produk->daftar_ukuran)->pluck('harga');
                                        $hargaMin = $daftarHarga->min();
                                        $hargaMax = $daftarHarga->max();
                                    @endphp
                                    <p class="truncate text-xs font-extrabold text-gray-900">
                                        @if($daftarHarga->isEmpty())
                                            <span class="text-gray-400 italic font-normal text-[10px]">Segera hadir</span>
                                        @elseif($hargaMin == $hargaMax)
                                            Rp {{ number_format($hargaMin, 0, ',', '.') }}
                                        @else
                                            Rp {{ number_format($hargaMin, 0, ',', '.') }} - Rp {{ number_format($hargaMax, 0, ',', '.') }}
                                        @endif
                                    </p>
                                @else
                                    <p data-harga-asli class="truncate text-[10px] text-gray-400 line-through" style="{{ $adaPromo ? '' : 'display:none' }}">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</p>
                                    <p data-harga-final class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</p>
                                @endif
                            </div>

                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    Belum ada produk tersedia saat ini.
                </div>
            @endforelse
        </div>

        {{-- Reward unggulan --}}
        <div class="mt-7 lg:mt-10 flex items-center justify-between">
            <h2 class="text-sm lg:text-base font-bold text-gray-900">Spesial Katalog Reward</h2>
            @auth('member')
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @else
                <a href="{{ route('member.welcome') }}" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @endauth
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-6 space-y-0">
            @forelse ($rewardUnggulan as $reward)
                @include('mobile.components.reward-card', ['reward' => $reward, 'member' => $member])
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    Belum ada reward tersedia saat ini.
                </div>
            @endforelse
        </div>
    </div>

    @include('mobile.components.floating-cart')

    {{-- Bottom sheet: Info Poin --}}
    <dialog id="sheet-info-poin" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 p-2 text-brand-700">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                </span>
                <h3 class="mt-3 text-center text-theme-md font-bold text-brand-700">Poin Kamu</h3>
                <p class="mt-1 text-center text-theme-sm text-gray-500">
                    Saat ini kamu punya <span class="font-bold text-gray-800">{{ number_format($member->poin ?? 0, 0, ',', '.') }} poin</span>
                </p>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    <ol class="list-decimal space-y-2 pl-4">
                        <li>Poin didapat otomatis setiap transaksi selesai, sesuai aturan perolehan poin yang berlaku saat itu (menyesuaikan jumlah belanja).</li>
                        <li>Poin bisa ditukar dengan voucher diskon atau produk gratis di halaman Tukar Poin.</li>
                        <li>Poin tidak hangus selama akun member kamu tetap aktif.</li>
                    </ol>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); closeSheet('sheet-info-poin'); setTimeout(() => animateNavigate('{{ route('member.reward.index') }}', 'forward'), 150);"
                   class="block w-full rounded-full bg-brand-700 py-3.5 text-center text-theme-sm font-semibold text-white active:bg-brand-800">
                    Tukar Poin Sekarang
                </a>
                <button type="button" onclick="closeSheet('sheet-info-poin')" class="mt-1 w-full rounded-full py-3 text-theme-sm font-medium text-gray-500">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom sheet: Info Tier --}}
    <dialog id="sheet-info-tier" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full {{ $warnaBadgeTier }} text-white">
                    @include('mobile.components.icon-crown', ['class' => 'h-6 w-6'])
                </span>
                <h3 class="mt-3 text-center text-theme-md font-bold text-brand-700">Tier {{ $member->level ?? '-' }}</h3>

                @if ($progresTier)
                    <p class="mt-1 text-center text-theme-sm text-gray-500">
                        {{ $progresTier['poin_dibutuhkan'] }} poin lagi menuju Tier {{ $progresTier['tier_berikutnya'] }}
                    </p>
                    <div class="mt-3">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $warnaBadgeTier }}" style="width: {{ $progresTier['persen'] }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="mt-1 text-center text-theme-sm text-gray-500">Kamu sudah berada di tier tertinggi ✨</p>
                @endif

                <div class="mt-5 text-theme-sm leading-relaxed text-gray-700">
                    <p class="font-bold text-gray-800">Kenapa perlu naik tier?</p>
                    <p class="mt-1 text-gray-500">Semakin tinggi tier kamu, semakin banyak keuntungan dan promo eksklusif yang bisa kamu nikmati di Saisho.</p>
                </div>

                @if (isset($semuaTier) && $semuaTier->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($semuaTier as $tier)
                            @php
                                $iniTierSaatIni = $tier->nama_level === ($member->level ?? null);
                            @endphp
                            <div class="flex items-center justify-between rounded-xl border {{ $iniTierSaatIni ? 'border-brand-300 bg-brand-50' : 'border-gray-100' }} px-3 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $warnaTierMap($tier->warna_badge) }} text-white">
                                        @include('mobile.components.icon-crown', ['class' => 'h-3.5 w-3.5'])
                                    </span>
                                    <span class="text-theme-sm font-semibold {{ $iniTierSaatIni ? 'text-brand-700' : 'text-gray-800' }}">{{ $tier->nama_level }}</span>
                                </div>
                                <span class="text-theme-xs text-gray-400">Min. {{ number_format($tier->minimal_poin, 0, ',', '.') }} poin</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-info-tier')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

    <script>
        // Badge titik merah di lonceng notifikasi — polling ringan tiap 20
        // detik selagi halaman beranda kebuka (fetch JSON kecil, bukan reload
        // halaman) supaya member tau ada notif baru walau nggak dikirim push.
        (() => {
            const dot = document.getElementById('notif-unread-dot');
            if (!dot) return;

            const cekUnread = () => {
                fetch("{{ route('member.notifikasi.unread-count') }}", {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then((res) => res.ok ? res.json() : null)
                    .then((data) => {
                        if (!data) return;
                        dot.classList.toggle('hidden', data.unread <= 0);
                    })
                    .catch(() => {});
            };

            cekUnread();
            setInterval(cekUnread, 20000);
        })();

        window.daftarkanFcmToken = function (token) {
            if (!token) return;
            fetch("{{ route('member.notifikasi.token') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ token: token, platform: 'android' }),
            }).catch(() => {});
        };

        if (window.Android && typeof window.Android.getFcmToken === 'function') {
            const token = window.Android.getFcmToken();
            if (token) window.daftarkanFcmToken(token);
        }

        // Live update badge & harga "Promo" di grid Produk Pilihan: kalau promo
        // kadaluwarsa/dihapus atau ada promo baru dari admin, langsung kelihatan
        // di layar member tanpa perlu reload halaman.
        (function () {
            const kartuProduk = document.querySelectorAll('[data-produk-id]');
            if (!kartuProduk.length) return;
            const produkIds = Array.from(kartuProduk).map(el => el.dataset.produkId);

            const formatRupiah = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

            const sinkronPromo = () => {
                fetch("{{ route('member.promo-status') }}?produk_ids=" + produkIds.join(','))
                    .then(r => r.json())
                    .then(r => {
                        if (r.status !== 'success') return;
                        kartuProduk.forEach(el => {
                            const data = r.data.produk[el.dataset.produkId];
                            if (!data) return;

                            const badge = el.querySelector('[data-promo-badge]');
                            const hargaAsli = el.querySelector('[data-harga-asli]');
                            const hargaFinal = el.querySelector('[data-harga-final]');

                            if (badge) badge.style.display = data.ada_promo ? '' : 'none';
                            if (hargaAsli) hargaAsli.style.display = data.ada_promo ? '' : 'none';
                            if (hargaFinal) hargaFinal.textContent = formatRupiah(data.harga_final);
                            if (hargaAsli) hargaAsli.textContent = formatRupiah(data.harga_asli);
                        });
                    })
                    .catch(() => {});
            };

            setInterval(sinkronPromo, 15000);
        })();
    </script>

@endsection