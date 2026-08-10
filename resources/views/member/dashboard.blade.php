@extends('member.layout', ['title' => 'Beranda'])

@php
    $waCs = '6282000768044';
@endphp

@section('content')

    <div>

        {{-- Promo Banner (1 big image, carousel like welcome page) + Greeting Card, stacked Fore-style --}}
        <div class="-mx-5 sm:-mx-8 md:-mx-10 relative">
            {{-- Notification button, floating top-right over the banner --}}
            <button type="button" class="absolute right-4 top-4 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur transition hover:bg-black/40">
                <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>

            @if (count($promoBanners))
                <div id="promo-viewport" class="relative h-80 md:h-[380px] lg:h-[420px] landscape:h-64 md:landscape:h-72 w-full touch-pan-x overflow-hidden rounded-b-[1.5rem]">
                    <div id="promo-track" class="flex h-full transition-transform duration-[450ms] ease-out" style="width: {{ count($promoBanners) * 100 }}%;">
                        @foreach ($promoBanners as $banner)
                            <div class="relative h-full shrink-0" style="width: {{ 100 / count($promoBanners) }}%;">
                                @if (! empty($banner['gambar']))
                                    <img src="{{ asset($banner['gambar']) }}" alt="{{ $banner['judul'] }}" class="absolute inset-0 h-full w-full object-cover object-center">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br {{ $banner['gradient'] }}"></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/0"></div>

                                <div class="relative z-10 flex h-full flex-col justify-end p-5 pb-14">
                                    @if (! empty($banner['badge']))
                                        <span class="mb-auto inline-block w-fit rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand-800 shadow-sm">
                                            {{ $banner['badge'] }}
                                        </span>
                                    @endif

                                    <p class="text-[11px] font-semibold text-white/80">{{ $banner['periode'] }}</p>
                                    <h3 class="mt-1 text-lg font-extrabold leading-tight text-white drop-shadow">{{ $banner['judul'] }}</h3>
                                    <p class="mt-1 text-sm font-bold text-white">
                                        Mulai dari
                                        @if ($banner['harga_coret'])
                                            <span class="ml-1 text-white/60 line-through">{{ $banner['harga_coret'] }}</span>
                                        @endif
                                        <span class="ml-1">{{ $banner['harga'] }}</span>
                                    </p>

                                    <span class="mt-3 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur">
                                        @include('member.components.icon-clock', ['class' => 'h-3.5 w-3.5'])
                                        {{ $banner['waktu'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if (count($promoBanners) > 1)
                        <div id="promo-dots" class="pointer-events-none absolute inset-x-0 bottom-16 z-20 flex items-center justify-center gap-1.5">
                            @foreach ($promoBanners as $i => $banner)
                                <button type="button" onclick="goToPromoSlide({{ $i }})" class="promo-dot pointer-events-auto h-1.5 rounded-full bg-white/50 transition-all {{ $i === 0 ? 'w-4 !bg-white' : 'w-1.5' }}" aria-label="Promo {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
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
                        current = index;
                        track.style.transform = `translateX(-${current * (100 / totalSlides)}%)`;
                        dots.forEach((dot, i) => {
                            dot.classList.toggle('w-4', i === current);
                            dot.classList.toggle('!bg-white', i === current);
                            dot.classList.toggle('w-1.5', i !== current);
                        });
                    };

                    const startAuto = () => {
                        stopAuto();
                        if (totalSlides > 1) {
                            autoTimer = setInterval(() => window.goToPromoSlide((current + 1) % totalSlides), 4500);
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
                        if (diffX > 35) window.goToPromoSlide((current + 1) % totalSlides);
                        else if (diffX < -35) window.goToPromoSlide((current - 1 + totalSlides) % totalSlides);
                        dragging = false;
                        startAuto();
                    }, { passive: true });

                    startAuto();
                })();
            </script>

            {{-- Greeting card, tucked up over the banner image like the Fore app --}}
            <div class="relative z-20 {{ count($promoBanners) ? '-mt-10 md:-mt-14' : 'mt-0' }} px-5 sm:px-8 md:px-10">
                @auth('member')
                    {{-- Kartu sapaan ala "Hi Sofi!" --}}
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
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                                    @include('member.components.icon-cake', ['class' => 'h-3.5 w-3.5'])
                                </span>
                                {{ number_format($member->poin, 0, ',', '.') }} Poin
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 py-1.5 pl-1.5 pr-3 text-xs font-bold text-gray-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-800 text-white">
                                    @include('member.components.icon-crown', ['class' => 'h-3.5 w-3.5'])
                                </span>
                                Tier {{ $member->level }}
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Guest Welcome Card --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#5a3c27] via-[#704b30] to-[#8b5e3c] p-6 text-white shadow-lg">
                        <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur">
                            Guest Mode
                        </span>
                        <h2 class="mt-2 text-xl font-extrabold tracking-tight">Selamat Datang di Saisho!</h2>
                        <p class="mt-1 text-xs text-brand-100 leading-relaxed">Nikmati aneka kue, roti & minuman segar spesial. Login sekarang untuk kumpulkan poin & voucher gratis.</p>
                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('member.login') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-brand-800 shadow-sm hover:bg-brand-50">
                                Login Member
                            </a>
                            <a href="{{ route('member.register') }}" class="rounded-xl border border-white/40 px-4 py-2.5 text-xs font-bold text-white backdrop-blur hover:bg-white/10">
                                Daftar Akun
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Pick Up / Delivery --}}
        <h2 class="mb-3 mt-7 text-sm font-bold text-gray-900">Mau pesan di Saisho?</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="https://wa.me/{{ $waCs }}?text={{ urlencode('Halo Saisho, saya mau pesan untuk diambil di toko (pick up).') }}"
               target="_blank" rel="noopener"
               class="group relative overflow-hidden rounded-2xl border-2 border-brand-300 bg-gradient-to-br from-brand-50/70 to-white p-3.5 transition hover:border-brand-400 hover:shadow-theme-md">
                <span class="pointer-events-none absolute -bottom-5 -right-5 h-16 w-16 rounded-full bg-brand-100/70"></span>
                <span class="pointer-events-none absolute right-8 bottom-9 h-2 w-2 rounded-full bg-brand-200"></span>
                <span class="pointer-events-none absolute right-1 bottom-9 h-2.5 w-2.5 rounded-full bg-brand-200/80"></span>
                <span class="relative block text-base font-extrabold leading-tight text-brand-800">Pick Up</span>
                <p class="relative mt-1 text-[11px] leading-snug text-gray-500">Pesan dulu, ambil<br>tanpa antre</p>
            </a>
            <a href="https://wa.me/{{ $waCs }}?text={{ urlencode('Halo Saisho, saya mau pesan untuk diantar (delivery).') }}"
               target="_blank" rel="noopener"
               class="group relative overflow-hidden rounded-2xl border-2 border-orange-300 bg-gradient-to-br from-orange-50/70 to-white p-3.5 transition hover:border-orange-400 hover:shadow-theme-md">
                <span class="pointer-events-none absolute -bottom-5 -right-5 h-16 w-16 rounded-full bg-orange-100/70"></span>
                <span class="pointer-events-none absolute right-8 bottom-9 h-2 w-2 rounded-full bg-orange-200"></span>
                <span class="pointer-events-none absolute right-1 bottom-9 h-2.5 w-2.5 rounded-full bg-orange-200/80"></span>
                <span class="relative block text-base font-extrabold leading-tight text-orange-700">Delivery</span>
                <p class="relative mt-1 text-[11px] leading-snug text-gray-500">Pesan via WhatsApp,<br>kami antar</p>
            </a>
        </div>
        <p class="mt-2 text-center text-[11px] text-gray-400">Pesan langsung ke Customer Service kami via WhatsApp</p>

        @auth('member')
            {{-- Aksi cepat member: tukar poin & riwayat --}}
            <div class="mt-5 grid grid-cols-2 gap-3">
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-brand-200">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                        @include('member.components.icon-gift', ['class' => 'h-5 w-5'])
                    </span>
                    <span>
                        <span class="block text-xs font-bold text-gray-900">Tukar Poin</span>
                        <span class="block text-[11px] text-gray-400">Lihat katalog</span>
                    </span>
                </a>
                <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-brand-200">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                        @include('member.components.icon-clock', ['class' => 'h-5 w-5'])
                    </span>
                    <span>
                        <span class="block text-xs font-bold text-gray-900">Riwayat</span>
                        <span class="block text-[11px] text-gray-400">Poin & redeem</span>
                    </span>
                </a>
            </div>

            {{-- Program Stamp: ringkasan punch-card yang mengarah ke tab Stamp.
                 Butuh $program (aktif) dan $memberStamp dikirim dari controller dashboard,
                 sama seperti di member.stamps.index — kalau belum dikirim, section ini
                 otomatis disembunyikan lewat @isset di bawah. --}}
            @isset($program)
                @php
                    $stampTarget = max(1, $program->jumlah_stamp_dibutuhkan);
                    $stampCurrent = min($memberStamp->jumlah_stamp ?? 0, $stampTarget);
                    $stampPenuh = ($memberStamp->jumlah_stamp ?? 0) >= $stampTarget;
                    $stampDotCount = min($stampTarget, 8);
                @endphp
                <a href="{{ route('member.stamps.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                   class="mt-3 block overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-4 text-white shadow-sm transition active:scale-[0.99]">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
                                @include('member.components.icon-cake', ['class' => 'h-5 w-5'])
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Program Stamp</p>
                                <p class="truncate text-sm font-extrabold">{{ $program->nama_program }}</p>
                            </div>
                        </div>
                        <span class="flex shrink-0 items-center gap-1 text-xs font-bold text-white">
                            @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                        </span>
                    </div>

                    <div class="mt-3 flex items-center gap-1.5">
                        @for ($i = 1; $i <= $stampDotCount; $i++)
                            @php $terisi = $i <= $stampCurrent; @endphp
                            <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $terisi ? 'bg-white text-brand-800' : 'border-2 border-dashed border-white/35 text-white/35' }}">
                                @include('member.components.icon-cake', ['class' => 'h-3 w-3'])
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
        @endauth

        {{-- Reward unggulan --}}
        <div class="mt-7 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Spesial Katalog Reward</h2>
            @auth('member')
                <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @else
                <a href="{{ route('member.welcome') }}" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @endauth
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 space-y-0">
            @forelse ($rewardUnggulan as $reward)
                @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    Belum ada reward tersedia saat ini.
                </div>
            @endforelse
        </div>
    </div>

@endsection