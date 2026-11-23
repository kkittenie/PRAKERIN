@extends('mobile.layout', ['title' => 'Home'])

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

    // Semua level dinonaktifkan di POS = fitur tier mati, jadi info tier
    // disembunyikan total dari Member App.
    $tierAktif = \App\Models\MemberLevel::adaYangAktif();

    // Semua aturan poin dinonaktifkan = fitur poin mati, jadi semua info poin
    // disembunyikan dari Member App (sama seperti tier).
    $poinAktif = \App\Models\PengaturanMember::adaAturanAktif();
    $progresTier = $tierAktif ? $member?->progres_tier : null;
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
                                        <img src="{{ asset($banner['gambar']) }}" alt="Promo Daisy Bakery" class="absolute inset-0 h-full w-full object-cover object-center">
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
                        <p class="mt-0.5 text-xs text-gray-400">Thank you for being a cherished Daisy Bakery member.</p>

                        @if ($poinAktif || $tierAktif)
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-dashed border-gray-100 pt-4">
                            @if ($poinAktif)
                            <button type="button" onclick="openSheet('sheet-info-poin')"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800 transition active:scale-95">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 p-1 text-brand-700">
                                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                </span>
                                {{ number_format($member->poin, 0, ',', '.') }} Points
                            </button>
                            @endif
                            @if ($tierAktif)
                                <button type="button" onclick="openSheet('sheet-info-tier')"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800 transition active:scale-95">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $warnaBadgeTier }} text-white">
                                        @include('mobile.components.icon-crown', ['class' => 'h-3.5 w-3.5'])
                                    </span>
                                    Tier {{ $member->level }}
                                </button>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Aksi cepat member: tukar poin & riwayat --}}
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white/95 backdrop-blur-lg p-4 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                @include('mobile.components.icon-gift', ['class' => 'h-5 w-5'])
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-bold text-gray-900">Vouchers</span>
                                <span class="block truncate text-[11px] text-gray-400">Explore rewards</span>
                            </span>
                        </a>
                        <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white/95 backdrop-blur-lg p-4 shadow-sm transition hover:border-brand-200 hover:shadow-md">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                                @include('mobile.components.icon-clock', ['class' => 'h-5 w-5'])
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-bold text-gray-900">Activity</span>
                                <span class="block truncate text-[11px] text-gray-400">Orders & stamps</span>
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
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Daisy Stamp Club</p>
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
                                    Stamp card complete, redeem now! 🎉
                                @else
                                    {{ $stampCurrent }}/{{ $stampTarget }} stamps &middot; {{ $stampTarget - $stampCurrent }} more to treat
                                @endif
                            </p>
                        </a>
                    @endisset
                @else
                    {{-- Guest Welcome Card --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#3A221B] via-[#4D2E24] to-[#5A3A28] p-6 text-white shadow-lg">
                        <div class="flex items-center mb-2">
                            <img src="{{ asset('images/logo/logo-horizontal-light.svg') }}" alt="Daisy Bakery" class="h-8 w-auto object-contain">
                        </div>
                        <h2 class="text-xl font-extrabold tracking-tight">Welcome to Daisy Bakery!</h2>
                        <p class="mt-1 text-xs text-brand-100 leading-relaxed">Handcrafted celebration cakes, daily sourdough & fresh viennoiseries. Sign in now to {{ $poinAktif ? 'collect points & unlock member perks' : 'unlock exclusive member perks' }}.</p>
                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('member.login') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-brand-800 shadow-sm hover:bg-brand-50">
                                Sign In
                            </a>
                            <a href="{{ route('member.register') }}" class="rounded-xl border border-white/40 px-4 py-2.5 text-xs font-bold text-white backdrop-blur hover:bg-white/10">
                                Join Now
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Produk pilihan --}}
        <div class="mt-7 lg:mt-10 flex items-center justify-between scroll-fade-up delay-100">
            <h2 class="text-sm lg:text-base font-bold text-gray-900">Featured Bakes</h2>
            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                View all
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
                    <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-[#FAF5E8] flex items-center justify-center p-2.5">
                        @if ($produk->foto)
                            <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                 class="max-h-full max-w-full object-contain filter drop-shadow-xs" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        @endif
                        <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                            @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                        </div>
                        <span data-promo-badge class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm" style="{{ $adaPromo ? '' : 'display:none' }}">Special</span>
                        @if ($habis)
                            <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Sold Out</span>
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
                                            <span class="text-gray-400 italic font-normal text-[10px]">Coming soon</span>
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
                    No products available at the moment.
                </div>
            @endforelse
        </div>

        {{-- Reward unggulan --}}
        <div class="mt-7 lg:mt-10 flex items-center justify-between scroll-fade-up delay-100">
            <h2 class="text-sm lg:text-base font-bold text-gray-900">Special Reward Catalog</h2>
            @auth('member')
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    View all
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @else
                <a href="{{ route('member.welcome') }}" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    View all
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @endauth
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-6 space-y-0 scroll-fade-up delay-150">
            @forelse ($rewardUnggulan as $reward)
                @include('mobile.components.reward-card', ['voucher' => $reward, 'member' => $member])
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    No rewards available at the moment.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — LANDING PAGE STYLE
         ════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block w-full">

        {{-- ━━━ SECTION 1: HERO (Full-bleed, Retro Bakery Scalloped Canvas) ━━━ --}}
        <section id="taste-desktop-hero" class="relative w-full overflow-hidden py-10 lg:py-14 xl:py-16"
                 style="background-color: #3A221B; background-image: repeating-linear-gradient(90deg, #3A221B 0px, #3A221B 32px, #301A14 32px, #301A14 64px);">
            <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                
                {{-- Scalloped Canvas Wrapper ("bingkai cokelat tua bergelombang") --}}
                <div id="hero-scallop-wrapper" class="relative w-full">
                    {{-- Scalloped SVG Background Frame --}}
                    <svg id="hero-scallop-svg" class="pointer-events-none absolute inset-0 h-full w-full select-none"
                         style="filter: drop-shadow(0 24px 50px rgba(18, 9, 7, 0.65));"
                         preserveAspectRatio="none">
                        <path id="hero-scallop-path" fill="#FAF5E8" stroke="#3A221B" stroke-width="1.5" />
                    </svg>

                    {{-- Inner Hero Content --}}
                    <div class="relative z-10 px-6 sm:px-10 lg:px-14 py-10 lg:py-12 flex flex-col items-center">
                        
                        {{-- 1. Top Center: Doodle & Headline --}}
                        <div class="text-center relative max-w-2xl hero-enter">
                            {{-- Cute rolling pin doodle --}}
                            <svg class="w-10 h-5 mx-auto mb-1.5 opacity-80" viewBox="0 0 60 24" fill="none" stroke="var(--color-cocoa-500)" stroke-width="2">
                                <rect x="14" y="5" width="32" height="14" rx="3"/>
                                <path d="M4 12h10M46 12h10M2 10v4M58 10v4" stroke-linecap="round"/>
                            </svg>

                            {{-- Bold playful headline matching logo script & cake focus --}}
                            <h1 class="text-center">
                                <span class="block font-accent text-5xl sm:text-6xl lg:text-[70px] leading-none transition-transform duration-300 hover:scale-[1.02]" style="color: var(--color-burgundy-500);">
                                    Freshly Baked Cakes
                                </span>
                                <span class="block font-display text-2xl sm:text-3xl lg:text-[38px] font-extrabold uppercase tracking-[0.04em] mt-1" style="color: var(--color-cocoa-500);">
                                    EVERY DAY
                                </span>
                            </h1>

                            {{-- Playful angled sticker badge --}}
                            <div class="inline-block -rotate-6 mt-1 ml-28 sm:ml-48 px-3.5 py-1 rounded-md text-[11px] sm:text-xs font-ui font-black shadow-sm uppercase tracking-wider transition-transform hover:rotate-0 duration-200 cursor-default"
                                 style="background-color: var(--color-daisy-gold); color: var(--color-cocoa-500); border: 1px solid rgba(58, 34, 27, 0.15);">
                                Daisy Bakery • Fresh Everyday
                            </div>
                        </div>

                        {{-- 2. Center 3-Zone Layout (Left copy, Center 3D Cake, Right CTA) --}}
                        <div class="w-full mt-4 lg:mt-6 grid grid-cols-12 gap-4 lg:gap-6 items-center">
                            
                            {{-- Left Column: Description & Opening Hours --}}
                            <div class="col-span-3 space-y-4 text-left hero-enter">
                                <p class="font-ui text-sm sm:text-[14.5px] leading-relaxed" style="color: var(--color-cocoa-500);">
                                    Indulge in handcrafted vintage celebration cakes, fluffy chiffon tiers, and artisanal pastries baked fresh every morning with pure butter and real strawberries.
                                </p>

                                <div class="pt-3 space-y-1.5" style="border-top: 1px dashed rgba(74, 46, 39, 0.25);">
                                    <div class="flex items-center gap-2 font-ui text-xs font-semibold" style="color: var(--color-cocoa-500);">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0" style="color: var(--color-cerulean-500);"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span class="lining-nums tabular-nums">Open {{ \App\Support\JamOperasional::buka() }} – {{ \App\Support\JamOperasional::tutup() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 font-ui text-xs font-semibold" style="color: var(--color-cocoa-500);">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0" style="color: var(--color-cerulean-500);"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span>Fresh Baked Daily</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Center Column: The 3D Cake Centerpiece (No text underneath, ample bottom clearance) --}}
                            <div class="col-span-6 flex flex-col items-center justify-center hero-enter">
                                <div id="desktop-hero-3d-wrapper" class="relative w-full h-[440px] lg:h-[480px] flex items-center justify-center select-none">
                                    <canvas id="desktop-hero-3d-canvas" class="w-full h-full block touch-none cursor-grab active:cursor-grabbing"></canvas>
                                </div>
                            </div>

                            {{-- Right Column: Tagline & CTA --}}
                            <div class="col-span-3 flex flex-col items-end text-right space-y-4 hero-enter">
                                <div class="font-ui text-xs font-bold tracking-[0.2em] uppercase" style="color: var(--color-burgundy-500);">
                                    FRESH • TASTY • SWEET
                                </div>

                                <div class="flex flex-col items-end gap-2.5">
                                    <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                                       class="group inline-flex items-center gap-3 rounded-full px-7 py-3.5 font-ui text-xs font-black tracking-widest uppercase shadow-md transition-all duration-200 hover:scale-105 active:scale-95 bg-[#3C2415] hover:bg-[#4E2F1B] text-white">
                                        <span>Explore Cakes & Menu</span>
                                        <svg class="w-4 h-4 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>

                                    @guest('member')
                                        <a href="{{ route('member.register') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 font-ui text-xs font-semibold transition hover:bg-[#3A221B]/10 active:scale-95"
                                           style="color: var(--color-cocoa-500); border: 1.5px solid var(--color-cocoa-500);">
                                            Sign Up as Member
                                        </a>
                                    @endguest

                                    @auth('member')
                                        <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-white/80 border border-[#3A221B]/15 text-left shadow-xs transition hover:shadow-sm">
                                            <div class="w-6 h-6 rounded-full bg-[#EACE80] flex items-center justify-center font-bold text-xs text-[#3A221B]">
                                                {{ strtoupper(substr($member->nama, 0, 1)) }}
                                            </div>
                                            <div class="text-xs font-ui">
                                                <span class="font-bold text-[#3A221B] block leading-tight">Hi, {{ explode(' ', $member->nama)[0] }}!</span>
                                                <span class="text-[10px] text-[#684B34] block leading-tight">Tier {{ $member->level ?? 'Member' }}</span>
                                            </div>
                                        </div>
                                    @endauth
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Script: Dynamic Smooth Scalloped Wavy Path Generator --}}
            <script>
            (() => {
                const wrapper = document.getElementById('hero-scallop-wrapper');
                const path = document.getElementById('hero-scallop-path');
                const svg = document.getElementById('hero-scallop-svg');
                if (!wrapper || !path || !svg) return;

                function createSmoothScallopedBox(w, h, waveWidth = 84, waveDepth = 15, cornerRadius = 52) {
                    const cornerSize = cornerRadius;
                    const straightW = w - 2 * cornerSize;
                    const straightH = h - 2 * cornerSize;

                    const nx = Math.max(4, Math.round(straightW / waveWidth));
                    const sw = straightW / nx;

                    const ny = Math.max(3, Math.round(straightH / waveWidth));
                    const sh = straightH / ny;

                    let d = `M ${cornerSize} 0 `;

                    // Top edge: Smooth undulating bezier waves
                    for (let i = 0; i < nx; i++) {
                        const x0 = cornerSize + i * sw;
                        const x1 = cornerSize + (i + 1) * sw;
                        const cp1x = x0 + sw * 0.28;
                        const cp2x = x0 + sw * 0.72;
                        d += `C ${cp1x.toFixed(1)} ${waveDepth} ${cp2x.toFixed(1)} ${waveDepth} ${x1.toFixed(1)} 0 `;
                    }

                    // Top-right corner
                    d += `C ${(w - cornerSize * 0.45).toFixed(1)} 0 ${w} ${(cornerSize * 0.45).toFixed(1)} ${w} ${cornerSize} `;

                    // Right edge
                    for (let i = 0; i < ny; i++) {
                        const y0 = cornerSize + i * sh;
                        const y1 = cornerSize + (i + 1) * sh;
                        const cp1y = y0 + sh * 0.28;
                        const cp2y = y0 + sh * 0.72;
                        d += `C ${(w - waveDepth).toFixed(1)} ${cp1y.toFixed(1)} ${(w - waveDepth).toFixed(1)} ${cp2y.toFixed(1)} ${w} ${y1.toFixed(1)} `;
                    }

                    // Bottom-right corner
                    d += `C ${w} ${(h - cornerSize * 0.45).toFixed(1)} ${(w - cornerSize * 0.45).toFixed(1)} ${h} ${(w - cornerSize).toFixed(1)} ${h} `;

                    // Bottom edge
                    for (let i = nx; i > 0; i--) {
                        const x0 = cornerSize + i * sw;
                        const x1 = cornerSize + (i - 1) * sw;
                        const cp1x = x0 - sw * 0.28;
                        const cp2x = x0 - sw * 0.72;
                        d += `C ${cp1x.toFixed(1)} ${(h - waveDepth).toFixed(1)} ${cp2x.toFixed(1)} ${(h - waveDepth).toFixed(1)} ${x1.toFixed(1)} ${h} `;
                    }

                    // Bottom-left corner
                    d += `C ${(cornerSize * 0.45).toFixed(1)} ${h} 0 ${(h - cornerSize * 0.45).toFixed(1)} 0 ${(h - cornerSize).toFixed(1)} `;

                    // Left edge
                    for (let i = ny; i > 0; i--) {
                        const y0 = cornerSize + i * sh;
                        const y1 = cornerSize + (i - 1) * sh;
                        const cp1y = y0 - sh * 0.28;
                        const cp2y = y0 - sh * 0.72;
                        d += `C ${waveDepth} ${cp1y.toFixed(1)} ${waveDepth} ${cp2y.toFixed(1)} 0 ${y1.toFixed(1)} `;
                    }

                    // Top-left corner
                    d += `C 0 ${(cornerSize * 0.45).toFixed(1)} ${(cornerSize * 0.45).toFixed(1)} 0 ${cornerSize} 0 Z`;

                    return d;
                }

                function updateScallop() {
                    const w = wrapper.clientWidth;
                    const h = wrapper.clientHeight;
                    if (w <= 0 || h <= 0) return;
                    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
                    path.setAttribute('d', createSmoothScallopedBox(w, h, 80, 15, 52));
                }

                updateScallop();
                window.addEventListener('resize', updateScallop, { passive: true });
                setTimeout(updateScallop, 100);
            })();
            </script>
        </section>

        {{-- ━━━ SECTION 2: A LITTLE TASTE OF OUR SIGNATURE CAKES (Featured Cakes & Category Scallops) ━━━ --}}
        <section id="taste-desktop-taste-showcase" class="relative w-full overflow-hidden" style="background-color: var(--color-surface-base); padding: 5rem 0 4rem;">
            {{-- 2D Floating Strawberries (Staggered playful margin decorations) --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-4 2xl:left-12 top-14 pointer-events-none z-10"
                 aria-hidden="true" style="width: 58px; height: 74px; --rot: -8deg; --duration: 4.2s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-4 2xl:right-12 top-20 pointer-events-none z-10"
                 aria-hidden="true" style="width: 52px; height: 68px; --rot: 14deg; --duration: 3.8s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-6 2xl:left-16 top-1/2 pointer-events-none z-10"
                 aria-hidden="true" style="width: 48px; height: 62px; --rot: 18deg; --duration: 4.8s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-6 2xl:right-16 top-2/3 pointer-events-none z-10"
                 aria-hidden="true" style="width: 50px; height: 65px; --rot: -14deg; --duration: 4.4s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-8 2xl:left-20 bottom-10 pointer-events-none z-10"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: -12deg; --duration: 5.1s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {{-- Header & Tag --}}
                <div class="text-center desktop-reveal">
                    <span class="inline-block px-3.5 py-1 rounded-full text-[11px] font-ui font-black uppercase tracking-widest shadow-xs"
                          style="background-color: var(--color-daisy-gold); color: #3A221B; border: 1px solid rgba(58, 34, 27, 0.15);">
                        signature cakes
                    </span>
                    <h2 class="mt-3 font-display text-3xl sm:text-4xl lg:text-[44px] font-extrabold uppercase tracking-tight" style="color: #3A221B;">
                        A Little Taste of Our Signature Cakes
                    </h2>
                </div>

                {{-- Category Badges with flower/scalloped icons (Dynamically Populated from Database $kategoris) --}}
                @php
                    $scallopColors = [
                        ['bg' => '#E08D79', 'text' => '#8E1E26'],
                        ['bg' => '#EACE80', 'text' => '#745B3F'],
                        ['bg' => '#E5A1B0', 'text' => '#8E1E26'],
                        ['bg' => '#D4A373', 'text' => '#3A221B'],
                    ];
                    $daftarKategori = $kategoris ?? \App\Models\KategoriProduk::with(['produk' => fn ($q) => $q->where('status_aktif', true)->dijualSatuan()])->get();
                @endphp
                <div class="mt-8 flex flex-wrap items-center justify-center gap-8 sm:gap-12 desktop-reveal">
                    @foreach ($daftarKategori as $index => $kategori)
                        @php
                            $color = $scallopColors[$index % count($scallopColors)];
                            $catIcon = $kategori->produk->firstWhere('foto', '!=', null)?->foto;
                        @endphp
                        <a href="{{ route('member.menu', ['kategori' => $kategori->id]) }}"
                           class="group flex flex-col items-center transition hover:scale-110 active:scale-95">
                            <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center">
                                <svg class="absolute inset-0 w-full h-full transition-transform group-hover:rotate-12 duration-300 filter drop-shadow-xs"
                                     style="color: {{ $color['bg'] }};" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                                </svg>
                                @if ($catIcon)
                                    <img src="{{ asset('storage/' . $catIcon) }}" alt="{{ $kategori->nama_kategori }}"
                                         class="relative z-10 w-11 h-11 sm:w-13 sm:h-13 object-contain drop-shadow-xs select-none pointer-events-none">
                                @else
                                    <div class="relative z-10 w-11 h-11 sm:w-13 sm:h-13 rounded-full flex items-center justify-center text-[#3A221B]/40">
                                        @include('mobile.components.icon-cake', ['class' => 'w-6 h-6'])
                                    </div>
                                @endif
                            </div>
                            <span class="mt-2.5 font-ui text-[11px] sm:text-xs font-black tracking-wider uppercase group-hover:underline text-center max-w-[120px] leading-tight"
                                  style="color: {{ $color['text'] }};">
                                {{ $kategori->nama_kategori }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- 3 Featured Product Cards (With delightful hover lift & zoom animations) --}}
                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-stretch">
                    @php
                        $tigaProduk = $produkPilihan->take(3);
                    @endphp
                    @foreach ($tigaProduk as $index => $item)
                        @php
                            $hargaItem = $item->harga_diskon ?: $item->harga_jual;
                        @endphp
                        <div class="group relative flex flex-col justify-between rounded-3xl overflow-hidden shadow-sm select-none desktop-reveal"
                             style="background-color: #F7F2E7; border: 1.5px solid rgba(74, 46, 39, 0.08);">

                            {{-- Rounded top image container with gentle padding & object-contain (Transparent cake fits beautifully without cutoff) --}}
                            <div class="relative w-full aspect-[4/3] overflow-hidden rounded-t-3xl flex items-center justify-center p-6">
                                @if ($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_produk }}"
                                         class="max-h-full max-w-full object-contain filter drop-shadow-md select-none pointer-events-none transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#3C2415]/20">
                                        @include('mobile.components.icon-cake', ['class' => 'w-12 h-12'])
                                    </div>
                                @endif
                            </div>

                            {{-- Card Information & Interactive Actions (Hover animation on button only) --}}
                            <div class="p-5 sm:p-6 flex flex-col justify-between flex-1 gap-3">
                                <div>
                                    <span class="font-ui text-[11px] font-bold uppercase tracking-wider text-[#745B3F]">
                                        {{ $item->kategori->nama_kategori ?? 'Artisan Bakery' }}
                                    </span>
                                    <h3 class="font-ui font-extrabold text-base lg:text-lg text-[#231815] line-clamp-1 mt-0.5">
                                        {{ $item->nama_produk }}
                                    </h3>
                                </div>
                                <div class="pt-3 flex items-center justify-between border-t border-[rgba(74,46,39,0.08)]">
                                    <span class="font-ui font-extrabold text-base sm:text-lg text-[#3A221B] lining-nums tabular-nums">
                                        Rp {{ number_format($hargaItem, 0, ',', '.') }}
                                    </span>
                                    <a href="{{ route('member.produk.show', $item) }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                       class="group/btn inline-flex items-center gap-1.5 rounded-full px-4 py-2 font-ui text-xs font-bold shadow-sm transition-all duration-300 hover:bg-[#8E1E26] hover:scale-105 active:scale-95 text-[#FAF5E8] bg-[#3A221B]">
                                        <span>SHOP</span>
                                        <span class="inline-block transition-transform duration-300 group-hover/btn:rotate-90">+</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Button: View All Menu --}}
                <div class="mt-10 text-center desktop-reveal">
                    <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                       class="group inline-flex items-center gap-3 rounded-full px-8 py-3.5 font-ui text-xs font-black tracking-widest uppercase shadow-md transition-all duration-200 hover:scale-105 active:scale-95 bg-[#3C2415] hover:bg-[#4E2F1B] text-white">
                        <span>VIEW ALL CAKES & MENU</span>
                        <svg class="w-4 h-4 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1.5" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 3: BRAND STORY CARD (We believe every celebration begins with a handcrafted cake...) ━━━ --}}
        <section id="taste-desktop-brand-story" class="relative w-full overflow-hidden py-10 lg:py-14" style="background-color: var(--color-surface-base);">
            {{-- Floating 2D Strawberries in margins --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-4 2xl:left-12 top-1/3 pointer-events-none z-10"
                 aria-hidden="true" style="width: 54px; height: 70px; --rot: 20deg; --duration: 4.6s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-4 2xl:right-12 bottom-1/4 pointer-events-none z-10"
                 aria-hidden="true" style="width: 48px; height: 62px; --rot: -16deg; --duration: 3.9s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="relative w-full overflow-hidden rounded-3xl lg:rounded-[36px] p-8 sm:p-12 lg:p-16 shadow-xl desktop-reveal"
                     style="background-color: #3A221B; background-image: repeating-linear-gradient(90deg, #3A221B 0px, #3A221B 28px, #301A14 28px, #301A14 56px);">
                    
                    {{-- Headline --}}
                    <h2 class="font-display text-3xl sm:text-4xl lg:text-[44px] font-extrabold leading-[1.08] tracking-tight text-white max-w-2xl">
                        We believe every celebration begins with a handcrafted cake made with love.
                    </h2>

                    {{-- Composition Grid --}}
                    <div class="mt-8 lg:mt-12 grid grid-cols-12 gap-6 lg:gap-8 items-center">
                        {{-- Left Chef Duck Mascot (Fills card smoothly without looking pasted over) --}}
                        <div class="col-span-12 sm:col-span-5 lg:col-span-4 relative group">
                            <div class="relative w-full aspect-[2/3] rounded-2xl lg:rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-[#301A14]">
                                <img src="{{ asset('images/decor/chef-duck.png') }}" alt="Daisy Chef Duck"
                                     class="w-full h-full object-cover select-none transition-transform duration-500 group-hover:scale-105">
                            </div>
                        </div>

                        {{-- Right Info & Warm Interior Photo --}}
                        <div class="col-span-12 sm:col-span-7 lg:col-span-8 flex flex-col justify-between space-y-6 sm:pl-4">
                            <div class="rounded-2xl p-6 lg:p-8 backdrop-blur-sm border border-white/10"
                                 style="background-color: rgba(58, 34, 27, 0.65);">
                                <p class="font-ui text-sm sm:text-base leading-relaxed text-[#FAF5E8]/90">
                                    Daisy Bakery began with a deep love for the art of vintage celebration cakes and wholesome artisan baking. Every tier, velvet crumb, and delicate cream ruffle is handcrafted fresh in our kitchen with pure European butter, farm-fresh eggs, and handpicked strawberries — bringing pure joy and timeless sweetness to your special moments.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-2xl overflow-hidden aspect-[4/3] border border-white/10 shadow-md group">
                                    <img src="{{ asset('images/cards/bakery-art-interior.jpg') }}" alt="Daisy Bakery" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-108">
                                </div>
                                <div class="rounded-2xl overflow-hidden aspect-[4/3] border border-white/10 shadow-md group">
                                    <img src="{{ asset('images/cards/bakery-cafe-interior.jpg') }}" alt="Fresh Bakes" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-108">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 4: 2 SCALLOPED SEASONAL / PROMO CARDS (Whipped Cream & Jam Textures with Cutout Cakes) ━━━ --}}
        <section id="taste-desktop-seasonal-promo" class="relative w-full overflow-hidden py-10 lg:py-16 select-none" style="background-color: var(--color-surface-base);">
            {{-- Floating 2D Strawberries in margins --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-5 2xl:left-14 top-1/2 pointer-events-none z-10"
                 aria-hidden="true" style="width: 52px; height: 68px; --rot: -14deg; --duration: 4.1s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-5 2xl:right-14 top-1/3 pointer-events-none z-10"
                 aria-hidden="true" style="width: 54px; height: 70px; --rot: 18deg; --duration: 4.7s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
                    
                    {{-- Offer Card 1: Whipped Cream Swirl Texture + Fraisier Whole Cake (No Card Hover Animation, Button Animation Only) --}}
                    <div class="relative rounded-3xl lg:rounded-[36px] overflow-hidden p-6 sm:p-8 flex flex-col items-center justify-center text-center shadow-xl select-none"
                         style="background: #FFFDF8;">
                        
                        {{-- Background Texture: Whipped Cream Swirls --}}
                        <img src="{{ asset('images/cards/texture-whipped-cream.jpg') }}" alt=""
                             class="absolute inset-0 w-full h-full object-cover filter brightness-[0.97] contrast-[1.02] pointer-events-none select-none">
                        
                        {{-- Scalloped White/Cream Silhouette Container --}}
                        <div class="relative w-full max-w-[420px] aspect-[420/540] flex flex-col items-center justify-between p-7 sm:p-9 text-center">
                            {{-- Scalloped SVG Path Frame --}}
                            <svg class="pointer-events-none absolute inset-0 h-full w-full select-none filter drop-shadow-[0_16px_28px_rgba(0,0,0,0.22)]"
                                 viewBox="0 0 420 540" preserveAspectRatio="none">
                                <path d="M 60 22 C 70.8 12.5, 79.2 12.5, 90 12.5 C 100.8 12.5, 109.2 23.9, 120 22 C 130.8 12.5, 139.2 12.5, 150 12.5 C 160.8 12.5, 169.2 23.9, 180 22 C 190.8 12.5, 199.2 12.5, 210 12.5 C 220.8 12.5, 229.2 23.9, 240 22 C 250.8 12.5, 259.2 12.5, 270 12.5 C 280.8 12.5, 289.2 23.9, 300 22 C 310.8 12.5, 319.2 12.5, 330 12.5 C 340.8 12.5, 349.2 23.9, 360 22 C 382.8 22, 398 37.2, 398 60 C 407.5 70.8, 407.5 79.2, 407.5 90 C 407.5 100.8, 396.1 109.2, 398 120 C 407.5 130.8, 407.5 139.2, 407.5 150 C 407.5 160.8, 396.1 169.2, 398 180 C 407.5 190.8, 407.5 199.2, 407.5 210 C 407.5 220.8, 396.1 229.2, 398 240 C 407.5 250.8, 407.5 259.2, 407.5 270 C 407.5 280.8, 396.1 289.2, 398 300 C 407.5 310.8, 407.5 319.2, 407.5 330 C 407.5 340.8, 396.1 349.2, 398 360 C 407.5 370.8, 407.5 379.2, 407.5 390 C 407.5 400.8, 396.1 409.2, 398 420 C 407.5 430.8, 407.5 439.2, 407.5 450 C 407.5 460.8, 396.1 469.2, 398 480 C 398 502.8, 382.8 518, 360 518 C 349.2 527.5, 340.8 527.5, 330 527.5 C 319.2 527.5, 310.8 516.1, 300 518 C 289.2 527.5, 280.8 527.5, 270 527.5 C 259.2 527.5, 250.8 516.1, 240 518 C 229.2 527.5, 220.8 527.5, 210 527.5 C 199.2 527.5, 190.8 516.1, 180 518 C 169.2 527.5, 160.8 527.5, 150 527.5 C 139.2 527.5, 130.8 516.1, 120 518 C 109.2 527.5, 100.8 527.5, 90 527.5 C 79.2 527.5, 70.8 516.1, 60 518 C 37.2 518, 22 502.8, 22 480 C 12.5 469.2, 12.5 460.8, 12.5 450 C 12.5 439.2, 23.9 430.8, 22 420 C 12.5 409.2, 12.5 400.8, 12.5 390 C 12.5 379.2, 23.9 370.8, 22 360 C 12.5 349.2, 12.5 340.8, 12.5 330 C 12.5 319.2, 23.9 310.8, 22 300 C 12.5 289.2, 12.5 280.8, 12.5 270 C 12.5 259.2, 23.9 250.8, 22 240 C 12.5 229.2, 12.5 220.8, 12.5 210 C 12.5 199.2, 23.9 190.8, 22 180 C 12.5 169.2, 12.5 160.8, 12.5 150 C 12.5 139.2, 23.9 130.8, 22 120 C 12.5 109.2, 12.5 100.8, 12.5 90 C 12.5 79.2, 23.9 70.8, 22 60 C 22 37.2, 37.2 22, 60 22 Z"
                                      fill="#FAF5E8"/>
                            </svg>

                            {{-- Inner Card Content --}}
                            <div class="relative z-10 w-full flex flex-col items-center justify-between h-full py-2">
                                <div>
                                    <span class="inline-block px-3.5 py-1 rounded-md text-[11px] font-ui font-black uppercase tracking-wider text-white shadow-xs"
                                          style="background-color: var(--color-burgundy-500);">
                                        limited offer
                                    </span>

                                    <h3 class="mt-3 font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-[#3A221B] leading-tight">
                                        UP TO 20% OFF<br>STRAWBERRY CROISSANT
                                    </h3>
                                </div>

                                {{-- Cutout Croissant Asset with Soft Contact Shadow --}}
                                <div class="my-1 w-60 sm:w-68 aspect-square flex items-center justify-center select-none">
                                    <img src="{{ asset('images/decor/promo-croissant.png') }}" alt="Artisan Strawberry Cream Croissant"
                                         class="w-full h-full object-contain filter drop-shadow-[0_22px_24px_rgba(58,34,27,0.35)] select-none pointer-events-none scale-135">
                                </div>

                                {{-- Action Button (Animation on Button Only) --}}
                                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                                   class="group inline-flex items-center gap-3 rounded-full px-7 py-3 font-ui text-xs font-black tracking-widest uppercase shadow-md transition-all duration-200 hover:scale-105 active:scale-95 bg-[#3C2415] hover:bg-[#4E2F1B] text-white">
                                    <span>SHOP NOW</span>
                                    <svg class="w-4 h-4 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1.5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Offer Card 2: Strawberry Jam Texture + Dripping Mini Cake (No Card Hover Animation, Button Animation Only) --}}
                    <div class="relative rounded-3xl lg:rounded-[36px] overflow-hidden p-6 sm:p-8 flex flex-col items-center justify-center text-center shadow-xl select-none"
                         style="background: #4A1B28;">
                        
                        {{-- Background Texture: Rich Red Strawberry Jam --}}
                        <img src="{{ asset('images/cards/texture-strawberry-jam.jpg') }}" alt=""
                             class="absolute inset-0 w-full h-full object-cover filter brightness-[0.88] contrast-[1.08] pointer-events-none select-none">
                        
                        {{-- Scalloped White/Cream Silhouette Container --}}
                        <div class="relative w-full max-w-[420px] aspect-[420/540] flex flex-col items-center justify-between p-7 sm:p-9 text-center">
                            {{-- Scalloped SVG Path Frame --}}
                            <svg class="pointer-events-none absolute inset-0 h-full w-full select-none filter drop-shadow-[0_18px_30px_rgba(0,0,0,0.38)]"
                                 viewBox="0 0 420 540" preserveAspectRatio="none">
                                <path d="M 60 22 C 70.8 12.5, 79.2 12.5, 90 12.5 C 100.8 12.5, 109.2 23.9, 120 22 C 130.8 12.5, 139.2 12.5, 150 12.5 C 160.8 12.5, 169.2 23.9, 180 22 C 190.8 12.5, 199.2 12.5, 210 12.5 C 220.8 12.5, 229.2 23.9, 240 22 C 250.8 12.5, 259.2 12.5, 270 12.5 C 280.8 12.5, 289.2 23.9, 300 22 C 310.8 12.5, 319.2 12.5, 330 12.5 C 340.8 12.5, 349.2 23.9, 360 22 C 382.8 22, 398 37.2, 398 60 C 407.5 70.8, 407.5 79.2, 407.5 90 C 407.5 100.8, 396.1 109.2, 398 120 C 407.5 130.8, 407.5 139.2, 407.5 150 C 407.5 160.8, 396.1 169.2, 398 180 C 407.5 190.8, 407.5 199.2, 407.5 210 C 407.5 220.8, 396.1 229.2, 398 240 C 407.5 250.8, 407.5 259.2, 407.5 270 C 407.5 280.8, 396.1 289.2, 398 300 C 407.5 310.8, 407.5 319.2, 407.5 330 C 407.5 340.8, 396.1 349.2, 398 360 C 407.5 370.8, 407.5 379.2, 407.5 390 C 407.5 400.8, 396.1 409.2, 398 420 C 407.5 430.8, 407.5 439.2, 407.5 450 C 407.5 460.8, 396.1 469.2, 398 480 C 398 502.8, 382.8 518, 360 518 C 349.2 527.5, 340.8 527.5, 330 527.5 C 319.2 527.5, 310.8 516.1, 300 518 C 289.2 527.5, 280.8 527.5, 270 527.5 C 259.2 527.5, 250.8 516.1, 240 518 C 229.2 527.5, 220.8 527.5, 210 527.5 C 199.2 527.5, 190.8 516.1, 180 518 C 169.2 527.5, 160.8 527.5, 150 527.5 C 139.2 527.5, 130.8 516.1, 120 518 C 109.2 527.5, 100.8 527.5, 90 527.5 C 79.2 527.5, 70.8 516.1, 60 518 C 37.2 518, 22 502.8, 22 480 C 12.5 469.2, 12.5 460.8, 12.5 450 C 12.5 439.2, 23.9 430.8, 22 420 C 12.5 409.2, 12.5 400.8, 12.5 390 C 12.5 379.2, 23.9 370.8, 22 360 C 12.5 349.2, 12.5 340.8, 12.5 330 C 12.5 319.2, 23.9 310.8, 22 300 C 12.5 289.2, 12.5 280.8, 12.5 270 C 12.5 259.2, 23.9 250.8, 22 240 C 12.5 229.2, 12.5 220.8, 12.5 210 C 12.5 199.2, 23.9 190.8, 22 180 C 12.5 169.2, 12.5 160.8, 12.5 150 C 12.5 139.2, 23.9 130.8, 22 120 C 12.5 109.2, 12.5 100.8, 12.5 90 C 12.5 79.2, 23.9 70.8, 22 60 C 22 37.2, 37.2 22, 60 22 Z"
                                      fill="#FAF5E8"/>
                            </svg>

                            {{-- Inner Card Content --}}
                            <div class="relative z-10 w-full flex flex-col items-center justify-between h-full py-2">
                                <div>
                                    <span class="inline-block px-3.5 py-1 rounded-md text-[11px] font-ui font-black uppercase tracking-wider text-white shadow-xs"
                                          style="background-color: var(--color-burgundy-500);">
                                        new arrival
                                    </span>

                                    <h3 class="mt-3 font-display text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-[#3A221B] leading-tight">
                                        UP TO 20% OFF<br>ARTISAN SLICE TREATS
                                    </h3>
                                </div>

                                {{-- Cutout Cake Asset with Soft Contact Shadow --}}
                                <div class="my-2 w-52 sm:w-60 aspect-square flex items-center justify-center select-none">
                                    <img src="{{ asset('images/decor/promo-cake-1.png') }}" alt="Signature Strawberry Mousse Slice"
                                         class="w-full h-full object-contain filter drop-shadow-[0_20px_22px_rgba(58,34,27,0.35)] select-none pointer-events-none scale-110">
                                </div>

                                {{-- Action Button (Animation on Button Only) --}}
                                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                                   class="group inline-flex items-center gap-3 rounded-full px-7 py-3 font-ui text-xs font-black tracking-widest uppercase shadow-md transition-all duration-200 hover:scale-105 active:scale-95 bg-[#3C2415] hover:bg-[#4E2F1B] text-white">
                                    <span>SHOP NOW</span>
                                    <svg class="w-4 h-4 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1.5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 5A: CONTINUOUS RUNNING TEXT MARQUEE WITH OVERLAID CAKE & SOFT DROP SHADOW ━━━ --}}
        <section id="taste-desktop-ticker-cake" class="relative w-full overflow-hidden py-16 lg:py-24 select-none" style="background-color: var(--color-surface-base);">
            {{-- Floating 2D Strawberries in margins --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-6 2xl:left-14 top-10 pointer-events-none z-10"
                 aria-hidden="true" style="width: 52px; height: 68px; --rot: 12deg; --duration: 4.4s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-6 2xl:right-14 bottom-10 pointer-events-none z-10"
                 aria-hidden="true" style="width: 50px; height: 65px; --rot: -16deg; --duration: 4.8s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            {{-- Relative Full-Height Stage for Running Ticker & Floating Cake (Extra Large Typography & Super Slow Glide) --}}
            <div class="relative w-full min-h-[480px] lg:min-h-[580px] flex items-center justify-center">
                {{-- Continuous Running Text Track (Extra Slow, Huge Size, No Dots) --}}
                <div class="w-full overflow-hidden absolute inset-y-0 flex items-center">
                    <div class="marquee-track flex items-center whitespace-nowrap select-none">
                        <span class="font-display font-black text-8xl sm:text-9xl lg:text-[180px] xl:text-[220px] 2xl:text-[240px] uppercase tracking-wider text-[#3A221B]/80 px-8 leading-none">
                            FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                        <span class="font-display font-black text-8xl sm:text-9xl lg:text-[180px] xl:text-[220px] 2xl:text-[240px] uppercase tracking-wider text-[#3A221B]/80 px-8 leading-none">
                            FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FRESHLY BAKED CAKES&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                    </div>
                </div>

                {{-- Centered Overlaid Cake with Organic Blob & Soft Realistic Drop Shadow --}}
                <div class="relative z-10 flex items-center justify-center pointer-events-none">
                    {{-- Soft Organic Latte Blob behind cake --}}
                    <svg class="absolute w-88 sm:w-[440px] lg:w-[500px] h-88 sm:h-[440px] lg:h-[500px] text-[#F0DEC8]/85 filter blur-[1px] -z-10" viewBox="0 0 200 200" fill="currentColor">
                        <path d="M42.7,-62.9C53.9,-54.1,60.8,-40.5,67.6,-26.2C74.3,-11.9,80.9,3.1,78.2,16.7C75.5,30.3,63.6,42.5,50.7,52.4C37.8,62.3,23.9,70,9.2,71.2C-5.5,72.4,-20.9,67.1,-34.5,58.7C-48,50.3,-59.6,38.8,-66.3,24.7C-73,10.6,-74.8,-6.1,-69.9,-20.3C-65.1,-34.5,-53.6,-46.2,-40.7,-54.5C-27.8,-62.8,-13.9,-67.7,0.9,-69C15.8,-70.3,31.6,-68,42.7,-62.9Z" transform="translate(100 100)" />
                    </svg>

                    {{-- Overlaid Cake with Full Unclipped Height & Soft Realistic Drop Shadow --}}
                    <div class="cake-float-glow pointer-events-auto cursor-pointer transition-transform duration-500 hover:scale-108 p-4">
                        <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="Artisanal Daisy Strawberry Cake"
                             class="w-72 sm:w-88 lg:w-[420px] max-w-full h-auto object-contain select-none filter drop-shadow-[0_32px_28px_rgba(58,34,27,0.50)]">
                    </div>
                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 5B: BAKERY STORE LOCATION & OPENING HOURS NOTE (Pinned Pink Paper Note on Blurred Bakery Art) ━━━ --}}
        <section id="taste-desktop-store-receipt" class="relative w-full overflow-hidden py-10 lg:py-16 select-none" style="background-color: var(--color-surface-base);">
            {{-- Floating 2D Strawberries in margins --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-4 2xl:left-12 top-1/4 pointer-events-none z-10"
                 aria-hidden="true" style="width: 50px; height: 65px; --rot: -14deg; --duration: 4.5s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-4 2xl:right-12 bottom-1/4 pointer-events-none z-10"
                 aria-hidden="true" style="width: 48px; height: 62px; --rot: 15deg; --duration: 4.1s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {{-- Outer Bakery Cafe Container with Blurred Anime Bakery Art --}}
                <div class="relative w-full rounded-3xl lg:rounded-[36px] overflow-hidden shadow-2xl min-h-[540px] lg:min-h-[620px] flex items-center justify-center p-6 sm:p-10 select-none"
                     style="background: #231815;">
                    
                    {{-- Blurred Bakery Illustration Background (gambar diblur biar kertasnya kelihatan) --}}
                    <img src="{{ asset('images/cards/bakery-art-interior.jpg') }}" alt="Bakery Cafe Art"
                         class="absolute inset-0 w-full h-full object-cover filter blur-[8px] brightness-[0.80] contrast-[1.05] scale-110 pointer-events-none select-none">
                    <div class="absolute inset-0 bg-[#231815]/25 pointer-events-none"></div>

                    {{-- The Pinned Canvas Paper Note Asset (Shifted so the white paper is dead center in the card & text matches) --}}
                    <div class="relative z-10 w-full max-w-[460px] sm:max-w-[500px] aspect-[1240/1269] flex items-center justify-center -translate-x-4 sm:-translate-x-5 transition-transform duration-300 hover:scale-[1.01]">
                        {{-- Background Image: Pinned Canvas Paper Note --}}
                        <img src="{{ asset('images/decor/pinned-canvas-note.png') }}" alt="Store Location & Hours Note"
                             class="absolute inset-0 w-full h-full object-contain filter drop-shadow-[0_24px_32px_rgba(0,0,0,0.45)] pointer-events-none select-none">

                        {{-- Text Overlay Centered Directly on the White Paper Canvas Area --}}
                        <div class="relative z-10 w-[74%] max-w-[340px] translate-x-[4%] pt-16 sm:pt-20 pb-8 px-4 text-center select-none text-[#3A221B] space-y-2">
                            <p class="font-ui text-xs font-semibold tracking-wide text-[#8E1E26]">
                                We are waiting for your visit
                            </p>
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span class="font-display text-sm font-black uppercase tracking-wider text-[#3A221B]">Daisy Bakery Store</span>
                            </div>

                            {{-- Hours Box --}}
                            <div class="pt-2 border-t border-dashed border-[#3A221B]/30">
                                <p class="font-ui text-[11px] font-bold uppercase tracking-wider text-[#8E1E26]">
                                    Monday – Sunday
                                </p>
                                <p class="font-display text-2xl font-bold text-[#3A221B] mt-0.5 lining-nums tabular-nums">
                                    {{ \App\Support\JamOperasional::buka() }} – {{ \App\Support\JamOperasional::tutup() }}
                                </p>
                            </div>

                            {{-- Location & Contact Details --}}
                            <div class="pt-1.5 space-y-1.5 text-left font-ui text-[11px] sm:text-xs text-[#3A221B]">
                                <div class="flex items-start justify-between gap-2 pt-1 border-t border-[#3A221B]/20">
                                    <span class="font-black uppercase tracking-wider text-[#8E1E26] shrink-0">FLAGSHIP</span>
                                    <span class="text-right text-[#3A221B] font-semibold">Jl. Jalan 40, Cirebon</span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pt-1 border-t border-[#3A221B]/20">
                                    <span class="font-black uppercase tracking-wider text-[#8E1E26] shrink-0">CAFE</span>
                                    <span class="text-right text-[#3A221B] font-semibold">Daisy Sweet House, Jl. Jalan</span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pt-1 border-t border-[#3A221B]/20">
                                    <span class="font-black uppercase tracking-wider text-[#8E1E26] shrink-0">WHATSAPP</span>
                                    <span class="text-right text-[#3A221B] font-bold lining-nums tabular-nums">+62 8820-0007-68044</span>
                                </div>
                            </div>

                            {{-- Bottom Brand Mark --}}
                            <div class="pt-2 flex items-center justify-between border-t border-dashed border-[#3A221B]/30">
                                <div class="flex items-center">
                                    <img src="{{ asset('images/logo/logo-horizontal.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain">
                                </div>
                                <span class="font-ui text-[10px] font-bold text-[#8E1E26] uppercase">Fresh Daily</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ━━━ SECTION 6: FREQUENTLY ASKED QUESTIONS (FAQ Accordion with Animated Button Click) ━━━ --}}
        <section id="taste-desktop-faq" class="relative w-full overflow-hidden py-14 lg:py-20" style="background-color: var(--color-surface-base);">
            {{-- Flat SVG Strawberries (Staggered playful margin decorations) --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-4 2xl:left-10 bottom-24 pointer-events-none z-10"
                 aria-hidden="true" style="width: 62px; height: 80px; --rot: -12deg; --duration: 4.5s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-4 2xl:right-12 top-16 pointer-events-none z-10"
                 aria-hidden="true" style="width: 54px; height: 70px; --rot: 10deg; --duration: 4.2s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-8 2xl:right-16 bottom-16 pointer-events-none z-10"
                 aria-hidden="true" style="width: 46px; height: 60px; --rot: -22deg; --duration: 5.0s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-10 lg:gap-14 items-start">
                    
                    {{-- Left Column: Headline & Description --}}
                    <div class="col-span-12 lg:col-span-5 desktop-reveal">
                        <span class="inline-block px-3.5 py-1 rounded-full text-[11px] font-ui font-black uppercase tracking-wider text-white"
                              style="background-color: var(--color-burgundy-500);">
                            faqs
                        </span>
                        
                        <h2 class="font-display text-4xl lg:text-5xl font-extrabold uppercase leading-[1.04] tracking-tight mt-3 text-[#3A221B]">
                            FREQUENTLY<br>ASKED QUESTIONS
                        </h2>

                        <p class="mt-5 font-ui text-sm sm:text-[15px] leading-relaxed text-[#684B34] max-w-sm">
                            Everything you need to know about Daisy Bakery cakes, bespoke celebration tiers, everyday fresh bakery slices, and visiting our store.
                        </p>

                        <div class="mt-8">
                            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);"
                               class="group inline-flex items-center gap-3 rounded-full px-8 py-3.5 font-ui text-xs font-black tracking-widest uppercase shadow-md transition-all duration-200 hover:scale-105 active:scale-95 bg-[#3C2415] hover:bg-[#4E2F1B] text-white">
                                <span>SHOP CAKES NOW</span>
                                <svg class="w-4 h-4 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Interactive Accordion Stack with Animated Button Click --}}
                    <div class="col-span-12 lg:col-span-7 space-y-4 desktop-reveal" x-data="{ activeFaq: 1 }">
                        
                        {{-- FAQ 1 --}}
                        <div class="rounded-2xl lg:rounded-[26px] p-5 lg:p-6 transition-all duration-300 hover:shadow-md"
                             style="background-color: #F7F2E7; border: 1px solid rgba(74, 46, 39, 0.08);">
                            <button type="button" @click="activeFaq = (activeFaq === 1 ? null : 1)"
                                    class="w-full flex items-center justify-between text-left gap-4 cursor-pointer group/faq">
                                <span class="font-ui font-bold text-sm lg:text-base text-[#3A221B] transition-colors duration-200 group-hover/faq:text-[#8E1E26]">
                                    What kind of cakes does Daisy Bakery specialize in?
                                </span>
                                <div class="relative w-9 h-9 rounded-full bg-[#3A221B] text-[#FAF5E8] flex items-center justify-center shrink-0 font-bold transition-all duration-300 shadow-sm active:scale-85"
                                     :class="activeFaq === 1 ? 'rotate-180 bg-[#8E1E26] ring-4 ring-[#8E1E26]/20' : 'group-hover/faq:scale-105 group-hover/faq:bg-[#8E1E26]'">
                                    <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" class="transition-opacity duration-200" :class="activeFaq === 1 ? 'opacity-0' : 'opacity-100'"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeFaq === 1" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 pt-3 border-t border-[rgba(74,46,39,0.08)]">
                                <p class="font-ui text-xs lg:text-[14px] leading-relaxed text-[#684B34]">
                                    We specialize in vintage celebration cakes, light-as-air chiffon tiers, decadent strawberry cheese tarts, and French chocolate fudge cakes, all made fresh in our kitchen with pure butter, farm milk, and real strawberries.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="rounded-2xl lg:rounded-[26px] p-5 lg:p-6 transition-all duration-300 hover:shadow-md"
                             style="background-color: #F7F2E7; border: 1px solid rgba(74, 46, 39, 0.08);">
                            <button type="button" @click="activeFaq = (activeFaq === 2 ? null : 2)"
                                    class="w-full flex items-center justify-between text-left gap-4 cursor-pointer group/faq">
                                <span class="font-ui font-bold text-sm lg:text-base text-[#3A221B] transition-colors duration-200 group-hover/faq:text-[#8E1E26]">
                                    Can I order a custom celebration or birthday cake?
                                </span>
                                <div class="relative w-9 h-9 rounded-full bg-[#3A221B] text-[#FAF5E8] flex items-center justify-center shrink-0 font-bold transition-all duration-300 shadow-sm active:scale-85"
                                     :class="activeFaq === 2 ? 'rotate-180 bg-[#8E1E26] ring-4 ring-[#8E1E26]/20' : 'group-hover/faq:scale-105 group-hover/faq:bg-[#8E1E26]'">
                                    <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" class="transition-opacity duration-200" :class="activeFaq === 2 ? 'opacity-0' : 'opacity-100'"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeFaq === 2" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 pt-3 border-t border-[rgba(74,46,39,0.08)]">
                                <p class="font-ui text-xs lg:text-[14px] leading-relaxed text-[#684B34]">
                                    Yes! You can order bespoke celebration cakes 2–3 days in advance directly through our menu or WhatsApp concierge, custom-tailored with your favorite flavors, delicate piped ruffles, and birthday messages.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="rounded-2xl lg:rounded-[26px] p-5 lg:p-6 transition-all duration-300 hover:shadow-md"
                             style="background-color: #F7F2E7; border: 1px solid rgba(74, 46, 39, 0.08);">
                            <button type="button" @click="activeFaq = (activeFaq === 3 ? null : 3)"
                                    class="w-full flex items-center justify-between text-left gap-4 cursor-pointer group/faq">
                                <span class="font-ui font-bold text-sm lg:text-base text-[#3A221B] transition-colors duration-200 group-hover/faq:text-[#8E1E26]">
                                    Do you sell cake slices and fresh pastries every day?
                                </span>
                                <div class="relative w-9 h-9 rounded-full bg-[#3A221B] text-[#FAF5E8] flex items-center justify-center shrink-0 font-bold transition-all duration-300 shadow-sm active:scale-85"
                                     :class="activeFaq === 3 ? 'rotate-180 bg-[#8E1E26] ring-4 ring-[#8E1E26]/20' : 'group-hover/faq:scale-105 group-hover/faq:bg-[#8E1E26]'">
                                    <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" class="transition-opacity duration-200" :class="activeFaq === 3 ? 'opacity-0' : 'opacity-100'"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeFaq === 3" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 pt-3 border-t border-[rgba(74,46,39,0.08)]">
                                <p class="font-ui text-xs lg:text-[14px] leading-relaxed text-[#684B34]">
                                    Yes! Every morning starting at 06:00, our display counters are filled with freshly cut artisan cake slices, blueberry danishes, and flaky croissants for your morning or afternoon coffee treat.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 4 --}}
                        <div class="rounded-2xl lg:rounded-[26px] p-5 lg:p-6 transition-all duration-300 hover:shadow-md"
                             style="background-color: #F7F2E7; border: 1px solid rgba(74, 46, 39, 0.08);">
                            <button type="button" @click="activeFaq = (activeFaq === 4 ? null : 4)"
                                    class="w-full flex items-center justify-between text-left gap-4 cursor-pointer group/faq">
                                <span class="font-ui font-bold text-sm lg:text-base text-[#3A221B] transition-colors duration-200 group-hover/faq:text-[#8E1E26]">
                                    How should I store and serve my Daisy cake?
                                </span>
                                <div class="relative w-9 h-9 rounded-full bg-[#3A221B] text-[#FAF5E8] flex items-center justify-center shrink-0 font-bold transition-all duration-300 shadow-sm active:scale-85"
                                     :class="activeFaq === 4 ? 'rotate-180 bg-[#8E1E26] ring-4 ring-[#8E1E26]/20' : 'group-hover/faq:scale-105 group-hover/faq:bg-[#8E1E26]'">
                                    <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" class="transition-opacity duration-200" :class="activeFaq === 4 ? 'opacity-0' : 'opacity-100'"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeFaq === 4" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 pt-3 border-t border-[rgba(74,46,39,0.08)]">
                                <p class="font-ui text-xs lg:text-[14px] leading-relaxed text-[#684B34]">
                                    Because our cakes are made with 100% natural butter and fresh whipped cream without preservatives, we recommend keeping them chilled. For the best velvet texture, bring the cake to room temperature 15 minutes before serving.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 5 --}}
                        <div class="rounded-2xl lg:rounded-[26px] p-5 lg:p-6 transition-all duration-300 hover:shadow-md"
                             style="background-color: #F7F2E7; border: 1px solid rgba(74, 46, 39, 0.08);">
                            <button type="button" @click="activeFaq = (activeFaq === 5 ? null : 5)"
                                    class="w-full flex items-center justify-between text-left gap-4 cursor-pointer group/faq">
                                <span class="font-ui font-bold text-sm lg:text-base text-[#3A221B] transition-colors duration-200 group-hover/faq:text-[#8E1E26]">
                                    Where can I visit a Daisy Bakery store?
                                </span>
                                <div class="relative w-9 h-9 rounded-full bg-[#3A221B] text-[#FAF5E8] flex items-center justify-center shrink-0 font-bold transition-all duration-300 shadow-sm active:scale-85"
                                     :class="activeFaq === 5 ? 'rotate-180 bg-[#8E1E26] ring-4 ring-[#8E1E26]/20' : 'group-hover/faq:scale-105 group-hover/faq:bg-[#8E1E26]'">
                                    <svg class="w-4 h-4 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <line x1="12" y1="5" x2="12" y2="19" class="transition-opacity duration-200" :class="activeFaq === 5 ? 'opacity-0' : 'opacity-100'"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeFaq === 5" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 pt-3 border-t border-[rgba(74,46,39,0.08)]">
                                <p class="font-ui text-xs lg:text-[14px] leading-relaxed text-[#684B34]">
                                    Our doors are open every day from {{ \App\Support\JamOperasional::buka() }} to {{ \App\Support\JamOperasional::tutup() }} at our Flagship Store and Cafe. You can also order directly through this member app for fast pickup and doorstep delivery.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

        {{-- Interactive Entrance & Scroll Animation Styles & Observer Script --}}
        <style>
            @keyframes heroSoftEntrance {
                0% { opacity: 0; transform: translateY(16px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .hero-enter {
                animation: heroSoftEntrance 0.75s cubic-bezier(0.16, 1, 0.3, 1) both;
            }
            @keyframes floatStrawberry {
                0%, 100% {
                    transform: translateY(0px) rotate(var(--rot, 0deg));
                }
                50% {
                    transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg));
                }
            }
            .desktop-floating-strawberry {
                animation: floatStrawberry var(--duration, 4s) ease-in-out infinite;
                will-change: transform;
            }
            @keyframes tickerLoop {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .marquee-track {
                display: flex;
                width: max-content;
                animation: tickerLoop 160s linear infinite;
                will-change: transform;
            }
            .marquee-track:hover {
                animation-play-state: paused;
            }
            @keyframes floatOverlaidCake {
                0%, 100% {
                    transform: translateY(0px) rotate(0deg);
                }
                50% {
                    transform: translateY(-10px) rotate(1.2deg);
                }
            }
            .cake-float-glow {
                animation: floatOverlaidCake 4.8s ease-in-out infinite;
                will-change: transform;
            }
            .desktop-reveal {
                opacity: 1;
                transform: translateY(0);
                transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
            }
        </style>
        <script>
            (() => {
                if (!window.matchMedia('(min-width: 1024px)').matches) return;
                const cards = document.querySelectorAll('.desktop-reveal');
                cards.forEach(c => c.classList.add('desktop-revealed'));
            })();
        </script>

        {{-- ━━━━ GSAP 3 & SCROLLTRIGGER INTERACTIVE STORYTELLING ENGINE (DESKTOP >= 1024px) ━━━━ --}}
        <script src="{{ asset('vendor/gsap/gsap.min.js') }}"></script>
        <script src="{{ asset('vendor/gsap/ScrollTrigger.min.js') }}"></script>
        <script src="{{ asset('js/desktop-storytelling.js') }}"></script>

    </div>

    @include('mobile.components.floating-cart')

    {{-- Bottom sheet: Info Poin --}}
    @if ($poinAktif)
    <dialog id="sheet-info-poin" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 p-2 text-brand-700">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                </span>
                <h3 class="mt-3 text-center text-theme-md font-bold text-brand-700">Your Daisy Points</h3>
                <p class="mt-1 text-center text-theme-sm text-gray-500">
                    You currently have <span class="font-bold text-gray-800">{{ number_format($member->poin ?? 0, 0, ',', '.') }} points</span>
                </p>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    <ol class="list-decimal space-y-2 pl-4">
                        <li>Points are earned automatically with every completed transaction based on the active member tier rules.</li>
                        <li>Points can be redeemed for exclusive discount vouchers and complimentary bakery treats in the Rewards catalog.</li>
                        <li>Points never expire as long as your membership account remains active.</li>
                    </ol>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); closeSheet('sheet-info-poin'); setTimeout(() => animateNavigate('{{ route('member.reward.index') }}', 'forward'), 150);"
                   class="block w-full rounded-full bg-brand-700 py-3.5 text-center text-theme-sm font-semibold text-white active:bg-brand-800">
                    Redeem Points Now
                </a>
                <button type="button" onclick="closeSheet('sheet-info-poin')" class="mt-1 w-full rounded-full py-3 text-theme-sm font-medium text-gray-500">
                    Close
                </button>
            </div>
        </div>
    </dialog>
    @endif

    {{-- Bottom sheet: Info Tier --}}
    @if ($tierAktif)
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
                        Spend Rp {{ number_format($progresTier['transaksi_dibutuhkan'], 0, ',', '.') }} more to unlock Tier {{ $progresTier['tier_berikutnya'] }}
                    </p>
                    <div class="mt-3">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $warnaBadgeTier }}" style="width: {{ $progresTier['persen'] }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="mt-1 text-center text-theme-sm text-gray-500">You have reached the highest tier ✨</p>
                @endif

                <div class="mt-5 text-theme-sm leading-relaxed text-gray-700">
                    <p class="font-bold text-gray-800">Why level up your tier?</p>
                    <p class="mt-1 text-gray-500">The higher your tier, the more exclusive savings, complimentary bakery perks, and VIP privileges you unlock at Daisy Bakery.</p>
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
                                <span class="text-theme-xs text-gray-400">Min. Rp {{ number_format($tier->minimal_transaksi ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-info-tier')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Got It
                </button>
            </div>
        </div>
    </dialog>
    @endif

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