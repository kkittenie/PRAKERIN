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

    {{-- ════════════════════════════════════════════════════════════════
         MOBILE VIEW (< 1024px) — 100% ORIGINAL, UNTOUCHED
         ════════════════════════════════════════════════════════════════ --}}
    <div class="lg:hidden">

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
        <div class="mt-7 lg:mt-10 flex items-center justify-between scroll-fade-up delay-100">
            <h2 class="text-sm lg:text-base font-bold text-gray-900">Produk Pilihan</h2>
            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                Lihat semua
                @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
            </a>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 lg:gap-5 scroll-fade-up delay-150">
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
        <div class="mt-7 lg:mt-10 flex items-center justify-between scroll-fade-up delay-100">
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

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-6 space-y-0 scroll-fade-up delay-150">
            @forelse ($rewardUnggulan as $reward)
                @include('mobile.components.reward-card', ['voucher' => $reward, 'member' => $member])
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    Belum ada reward tersedia saat ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — LANDING PAGE STYLE
         ════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block w-full">

        {{-- ━━━ SECTION 1: HERO BANNER ━━━ --}}
        <section class="relative w-full overflow-hidden bg-gradient-to-br from-[#2c1810] via-[#3d2418] to-[#5a3c27]">
            {{-- Background deco --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -left-20 -top-20 h-80 w-80 rounded-full bg-brand-400 blur-3xl"></div>
                <div class="absolute right-0 bottom-0 h-60 w-60 rounded-full bg-amber-400 blur-3xl"></div>
                <div class="absolute left-1/2 top-1/3 h-40 w-40 rounded-full bg-brand-300 blur-2xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-12 py-20 xl:py-24">
                <div class="grid grid-cols-12 gap-10 items-center">
                    {{-- Left: Tagline & CTA --}}
                    <div class="col-span-7 space-y-6 desktop-fade-up delay-100">

                        <h1 class="text-5xl xl:text-6xl font-black text-white leading-[1.1] tracking-tight">
                            Fresh Bakes,<br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-amber-300 to-brand-400">Sweeter Savings</span>
                        </h1>

                        <p class="text-base text-stone-300 leading-relaxed max-w-lg font-medium">
                            Nikmati kelezatan donat, kue spesial, dan pastry berkualitas tinggi yang dibuat segar setiap hari. Kumpulkan poin & stamp di setiap transaksi!
                        </p>

                        <div class="flex items-center gap-4 pt-2">
                            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                               class="inline-flex items-center gap-2.5 rounded-2xl bg-white px-7 py-4 text-sm font-extrabold text-brand-900 shadow-lg hover:bg-brand-50 transition active:scale-[0.98]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Lihat Menu
                            </a>
                            @guest('member')
                                <a href="{{ route('member.register') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="inline-flex items-center gap-2 rounded-2xl border-2 border-white/25 px-7 py-[14px] text-sm font-extrabold text-white backdrop-blur hover:bg-white/10 transition">
                                    Daftar Member
                                </a>
                            @endguest
                        </div>

                        {{-- Trust badges --}}
                        <div class="flex items-center gap-6 pt-4 border-t border-white/10">
                            <div class="flex items-center gap-2 text-stone-400">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-brand-400"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="text-xs font-bold">Buka 08.00 – 18.00</span>
                            </div>
                            <div class="flex items-center gap-2 text-stone-400">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-brand-400"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="text-xs font-bold">Fresh Baked Daily</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Promo Carousel or Member Card --}}
                    <div class="col-span-5 space-y-5 desktop-zoom-in delay-200">
                        @if (count($promoBanners))
                            <div id="desktop-hero-promo" class="relative aspect-[696/571] w-full overflow-hidden rounded-3xl shadow-2xl shadow-black/30 border border-white/10">
                                <div id="desktop-hero-track" class="flex h-full w-full transition-transform duration-[450ms] ease-out">
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
                                    <div class="absolute inset-x-0 bottom-4 z-20 flex items-center justify-center gap-1.5">
                                        @foreach ($promoBanners as $i => $banner)
                                            <button type="button" onclick="goToDesktopHeroSlide({{ $i }})" class="desktop-hero-dot rounded-full bg-white/50 transition-all {{ $i === 0 ? 'h-2.5 w-2.5 !bg-white' : 'h-1.5 w-1.5' }}" aria-label="Promo {{ $i + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <script>
                            (() => {
                                const track = document.getElementById('desktop-hero-track');
                                const dots = document.querySelectorAll('.desktop-hero-dot');
                                if (!track) return;
                                const total = track.children.length;
                                let cur = 0;
                                window.goToDesktopHeroSlide = (i) => {
                                    cur = (i + total) % total;
                                    track.style.transform = `translateX(-${cur * 100}%)`;
                                    dots.forEach((d, j) => {
                                        d.classList.toggle('h-2.5', j === cur);
                                        d.classList.toggle('w-2.5', j === cur);
                                        d.classList.toggle('!bg-white', j === cur);
                                        d.classList.toggle('h-1.5', j !== cur);
                                        d.classList.toggle('w-1.5', j !== cur);
                                    });
                                };
                                if (total > 1) setInterval(() => window.goToDesktopHeroSlide(cur + 1), 5000);
                            })();
                            </script>
                        @endif

                        @auth('member')
                            {{-- Compact member greeting --}}
                            <div class="relative overflow-hidden rounded-2xl border border-white/15 bg-white/10 backdrop-blur-md p-5 text-white">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-brand-300 border border-white/10">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-extrabold">Hi, {{ explode(' ', $member->nama)[0] }}! 👋</p>
                                        <p class="text-[11px] text-white/60">Selamat datang kembali di Saisho</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
                                    <button type="button" onclick="openSheet('sheet-info-poin')" class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/10 py-1.5 pl-1.5 pr-3 text-[11px] font-bold transition hover:bg-white/20">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-400/30 p-0.5">
                                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                        </span>
                                        {{ number_format($member->poin, 0, ',', '.') }} Poin
                                    </button>
                                    <button type="button" onclick="openSheet('sheet-info-tier')" class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/10 py-1.5 pl-1.5 pr-3 text-[11px] font-bold transition hover:bg-white/20">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full {{ $warnaBadgeTier }} text-white">
                                            @include('mobile.components.icon-crown', ['class' => 'h-3 w-3'])
                                        </span>
                                        Tier {{ $member->level }}
                                    </button>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 2: WHY CHOOSE US ━━━ --}}
        <section class="bg-white py-16 xl:py-20">
            <div class="mx-auto max-w-7xl px-8 lg:px-12">
                <div class="text-center mb-12 desktop-fade-up delay-100">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 border border-brand-100 px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-widest text-brand-800 mb-4">Kenapa Saisho?</span>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Kelezatan yang Bisa Kamu Rasakan</h2>
                    <p class="mt-2 text-sm text-gray-500 max-w-xl mx-auto">Kami berkomitmen menghadirkan produk bakery terbaik dengan bahan pilihan, dibuat segar setiap hari untuk pelanggan setia kami.</p>
                </div>

                <div class="grid grid-cols-3 gap-8">
                    {{-- Card 1 --}}
                    <div class="group relative rounded-3xl border border-gray-100 bg-gradient-to-b from-white to-gray-50/50 p-8 shadow-sm hover:shadow-lg hover:border-brand-200 transition-all duration-300 desktop-fade-up delay-150">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 text-brand-800 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-gray-900">Segar Setiap Hari</h3>
                        <p class="mt-2 text-xs leading-relaxed text-gray-500">Semua produk kami dibuat fresh dari dapur setiap pagi. Tidak ada produk basi — freshness is our promise.</p>
                    </div>

                    {{-- Card 2 --}}
                    <div class="group relative rounded-3xl border border-gray-100 bg-gradient-to-b from-white to-gray-50/50 p-8 shadow-sm hover:shadow-lg hover:border-amber-200 transition-all duration-300 desktop-fade-up delay-250">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 text-amber-700 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-gray-900">Bahan Berkualitas</h3>
                        <p class="mt-2 text-xs leading-relaxed text-gray-500">Hanya menggunakan bahan-bahan pilihan terbaik: tepung premium, mentega asli, dan bahan alami tanpa pengawet.</p>
                    </div>

                    {{-- Card 3 --}}
                    <div class="group relative rounded-3xl border border-gray-100 bg-gradient-to-b from-white to-gray-50/50 p-8 shadow-sm hover:shadow-lg hover:border-emerald-200 transition-all duration-300 desktop-fade-up delay-350">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-700 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6M12 2v10M12 2l4 4M12 2L8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-gray-900">Loyalty Rewards</h3>
                        <p class="mt-2 text-xs leading-relaxed text-gray-500">Kumpulkan poin & stamp dari setiap transaksi, tukarkan dengan voucher diskon dan produk gratis pilihan kamu.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 3: PRODUK PILIHAN ━━━ --}}
        <section class="bg-gray-50/60 py-16 xl:py-20 border-y border-gray-100 desktop-fade-up delay-200">
            <div class="mx-auto max-w-7xl px-8 lg:px-12">
                <div class="text-center mb-10 max-w-2xl mx-auto">
                    <h2 class="text-3xl xl:text-4xl font-black text-gray-900 tracking-tight">Produk Favorit Kami</h2>
                    <p class="mt-2 text-sm text-gray-500">Dipilih khusus dari koleksi terbaik Saisho Bakery untuk Anda</p>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                    @forelse ($produkPilihan as $produk)
                        @php
                            $hargaAsli = $produk->harga_jual;
                            $hargaFinal = $produk->harga_diskon;
                            $adaPromo = $hargaFinal < $hargaAsli;
                            $habis = $produk->punya_ukuran
                                ? $produk->habisSemuaUkuran()
                                : ($produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0);
                        @endphp
                        <div class="w-full max-w-[270px] sm:w-[250px] lg:w-[270px] shrink-0">
                            <a href="{{ route('member.produk.show', $produk) }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group block rounded-2xl border border-gray-100 bg-white overflow-hidden shadow-sm hover:shadow-xl hover:border-brand-200 transition-all duration-300 h-full"
                               @unless($produk->punya_ukuran) data-produk-id="{{ $produk->id }}" @endunless>
                                <div class="relative aspect-square w-full overflow-hidden bg-gray-50">
                                    @if ($produk->foto)
                                        <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                             class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @endif
                                    <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                                        @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                    </div>
                                    <span data-promo-badge class="absolute left-2.5 top-2.5 rounded-full bg-orange-500 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm" style="{{ $adaPromo ? '' : 'display:none' }}">Promo</span>
                                    @if ($habis)
                                        <span class="absolute left-2.5 bottom-2.5 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Perlu Diproduksi</span>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <p class="text-[10px] font-medium text-gray-400">{{ $produk->kategori->nama_kategori ?? '' }}</p>
                                    <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $produk->nama_produk }}</p>
                                    <div class="mt-2.5 flex items-center justify-between gap-1">
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
                                                        Rp {{ number_format($hargaMin, 0, ',', '.') }} - {{ number_format($hargaMax, 0, ',', '.') }}
                                                    @endif
                                                </p>
                                            @else
                                                <p data-harga-asli class="truncate text-[10px] text-gray-400 line-through" style="{{ $adaPromo ? '' : 'display:none' }}">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</p>
                                                <p data-harga-final class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm group-hover:bg-brand-900 transition">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 text-center text-xs font-medium text-gray-400">
                            Belum ada produk tersedia saat ini.
                        </div>
                    @endforelse
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                       class="inline-flex items-center gap-2 rounded-2xl bg-brand-800 px-7 py-3.5 text-xs font-extrabold text-white shadow-md hover:bg-brand-900 transition active:scale-[0.98]">
                        Lihat Semua Menu
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 4: TENTANG SAISHO ━━━ --}}
        <section class="bg-white py-16 xl:py-20 desktop-fade-up delay-250">
            <div class="mx-auto max-w-7xl px-8 lg:px-12">
                <div class="grid grid-cols-12 gap-12 items-center">
                    {{-- Left: Illustration --}}
                    <div class="col-span-5">
                        <div class="relative rounded-3xl bg-gradient-to-br from-brand-100 via-brand-50 to-amber-50 p-10 xl:p-14 border border-brand-100/50 shadow-sm">
                            {{-- Decorative bakery icons --}}
                            <div class="grid grid-cols-3 gap-6">
                                <div class="flex flex-col items-center gap-3 rounded-2xl bg-white/80 p-5 shadow-sm border border-brand-100/40">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-800">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/></svg>
                                    </span>
                                    <span class="text-[10px] font-bold text-brand-800 text-center">Roti Artisan</span>
                                </div>
                                <div class="flex flex-col items-center gap-3 rounded-2xl bg-white/80 p-5 shadow-sm border border-amber-100/40">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                        @include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])
                                    </span>
                                    <span class="text-[10px] font-bold text-amber-800 text-center">Kue & Pastry</span>
                                </div>
                                <div class="flex flex-col items-center gap-3 rounded-2xl bg-white/80 p-5 shadow-sm border border-emerald-100/40">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        @include('mobile.components.icon-cup', ['class' => 'h-6 w-6'])
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-800 text-center">Minuman</span>
                                </div>
                            </div>
                            {{-- Decorative circles --}}
                            <div class="absolute -left-4 -top-4 h-16 w-16 rounded-full bg-brand-200/40 blur-xl"></div>
                            <div class="absolute -right-4 -bottom-4 h-20 w-20 rounded-full bg-amber-200/40 blur-xl"></div>
                        </div>
                    </div>

                    {{-- Right: About text --}}
                    <div class="col-span-7 space-y-6">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight leading-snug">Saisho Cake & Bakery</h2>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Saisho hadir sebagai bakery pilihan yang mengutamakan kelezatan dan kualitas di setiap produknya. Dengan passion mendalam terhadap seni membuat roti dan kue, kami menyajikan berbagai varian produk artisan yang dibuat segar setiap hari menggunakan bahan-bahan terbaik.
                        </p>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Mulai dari croissant renyah, aneka cake lembut, hingga pastry premium — semuanya dirancang untuk menemani momen spesial Anda. Kami percaya bahwa setiap gigitan harus membawa kebahagiaan.
                        </p>

                        <div class="grid grid-cols-3 gap-5 pt-4 border-t border-gray-100">
                            <div class="text-center rounded-2xl bg-gray-50 p-4 border border-gray-100">
                                <p class="text-2xl font-black text-brand-800">08:00 – 18:00</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-1">Jam Operasional</p>
                            </div>
                            <div class="text-center rounded-2xl bg-gray-50 p-4 border border-gray-100">
                                <p class="text-2xl font-black text-brand-800">100%</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-1">Fresh Baked Daily</p>
                            </div>
                            <div class="text-center rounded-2xl bg-gray-50 p-4 border border-gray-100">
                                <p class="text-2xl font-black text-brand-800">∞</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-1">Kelezatan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 5: PROGRAM MEMBER ━━━ --}}
        <section class="relative bg-gradient-to-br from-[#2c1810] via-[#3d2418] to-[#5a3c27] py-16 xl:py-20 overflow-hidden desktop-fade-up delay-300">
            {{-- Background deco --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute right-0 top-0 h-60 w-60 rounded-full bg-brand-400 blur-3xl"></div>
                <div class="absolute left-10 bottom-10 h-40 w-40 rounded-full bg-amber-400 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-8 lg:px-12">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-black text-white tracking-tight">Lebih Sering Belanja, Makin Banyak Untung</h2>
                    <p class="mt-2 text-sm text-stone-400 max-w-xl mx-auto">Daftar jadi member Saisho dan nikmati berbagai keuntungan eksklusif dari setiap transaksi kamu.</p>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    {{-- Points --}}
                    <div class="rounded-3xl bg-white/5 border border-white/10 p-7 backdrop-blur-sm hover:bg-white/10 transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-400/20 text-brand-300 border border-brand-400/20">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-7 w-7 object-contain">
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-white">Saisho Points</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-400">Setiap transaksi menghasilkan poin yang bisa ditukar dengan voucher diskon dan produk gratis dari katalog reward.</p>
                        @auth('member')
                            <div class="mt-4 rounded-xl bg-white/10 px-4 py-2.5 border border-white/10">
                                <span class="text-[11px] font-bold text-brand-300">Poin kamu: {{ number_format($member->poin, 0, ',', '.') }}</span>
                            </div>
                        @endauth
                    </div>

                    {{-- Stamp --}}
                    <div class="rounded-3xl bg-white/5 border border-white/10 p-7 backdrop-blur-sm hover:bg-white/10 transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400/20 text-amber-300 border border-amber-400/20">
                            @include('mobile.components.icon-cake', ['class' => 'h-7 w-7'])
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-white">Program Stamp</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-400">Kumpulkan stamp dari setiap belanja dengan minimum pembelian tertentu. Stamp penuh? Tukar dengan hadiah gratis!</p>
                        @isset($program)
                            @auth('member')
                                <div class="mt-4 rounded-xl bg-white/10 px-4 py-2.5 border border-white/10">
                                    <span class="text-[11px] font-bold text-amber-300">{{ $stampCurrent ?? 0 }}/{{ $stampTarget ?? 0 }} stamp terkumpul</span>
                                </div>
                            @endauth
                        @endisset
                    </div>

                    {{-- Tier --}}
                    <div class="rounded-3xl bg-white/5 border border-white/10 p-7 backdrop-blur-sm hover:bg-white/10 transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-400/20 text-emerald-300 border border-emerald-400/20">
                            @include('mobile.components.icon-crown', ['class' => 'h-7 w-7'])
                        </div>
                        <h3 class="mt-5 text-base font-extrabold text-white">Member Tier</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-400">Naik tier secara otomatis berdasarkan total poin. Tier lebih tinggi = promo eksklusif dan keuntungan lebih banyak.</p>
                        @auth('member')
                            <div class="mt-4 rounded-xl bg-white/10 px-4 py-2.5 border border-white/10">
                                <span class="text-[11px] font-bold text-emerald-300">Level kamu: Tier {{ $member->level }}</span>
                            </div>
                        @endauth
                    </div>
                </div>

                @guest('member')
                    <div class="mt-10 text-center">
                        <a href="{{ route('member.register') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           class="inline-flex items-center gap-2.5 rounded-2xl bg-white px-8 py-4 text-sm font-extrabold text-brand-900 shadow-lg hover:bg-brand-50 transition active:scale-[0.98]">
                            Daftar Member Sekarang — Gratis!
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                @endguest
            </div>
        </section>

        {{-- ━━━ SECTION 6: KATALOG REWARD ━━━ --}}
        <section class="bg-white py-16 xl:py-20 border-t border-gray-100 desktop-fade-up delay-350">
            <div class="mx-auto max-w-7xl px-8 lg:px-12">
                <div class="text-center mb-10 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 border border-brand-100 px-4 py-1.5 text-[10px] font-extrabold uppercase tracking-widest text-brand-800 mb-3">Rewards & Vouchers</span>
                    <h2 class="text-3xl xl:text-4xl font-black text-gray-900 tracking-tight">Spesial Katalog Reward</h2>
                    <p class="mt-2 text-sm text-gray-500">Tukarkan poin member kamu dengan berbagai penawaran menarik</p>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                    @forelse ($rewardUnggulan as $reward)
                        <div class="w-full max-w-sm sm:w-[350px] shrink-0">
                            @include('mobile.components.reward-card', ['voucher' => $reward, 'member' => $member])
                        </div>
                    @empty
                        <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-gray-50 p-8 text-center text-xs font-medium text-gray-400">
                            Belum ada reward tersedia saat ini.
                        </div>
                    @endforelse
                </div>

                <div class="mt-10 text-center">
                    @auth('member')
                        <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 1);"
                           class="inline-flex items-center gap-2 rounded-2xl bg-brand-800 px-7 py-3.5 text-xs font-extrabold text-white shadow-md hover:bg-brand-900 transition active:scale-[0.98]">
                            Lihat Semua Reward
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @else
                        <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           class="inline-flex items-center gap-2 rounded-2xl bg-brand-800 px-7 py-3.5 text-xs font-extrabold text-white shadow-md hover:bg-brand-900 transition active:scale-[0.98]">
                            Login untuk Lihat Reward
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </section>

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