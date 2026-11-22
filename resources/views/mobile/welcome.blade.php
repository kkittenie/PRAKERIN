@php
    // Slide dari admin (menu Slide Welcome). Kalau belum ada yang aktif, pakai 3 slide bawaan.
    $daftarSlide = collect($slides ?? [])->map(fn ($sl) => [
        'judul' => $sl->judul,
        'deskripsi' => (string) $sl->deskripsi,
        'gambar' => asset('storage/' . $sl->gambar),
    ])->values();

    if ($daftarSlide->isEmpty()) {
        $daftarSlide = collect([
            ['judul' => 'Fresh Bakes, Sweeter Savings', 'deskripsi' => 'Join Daisy Plan and unlock exclusive perks on your favorite breads & cakes', 'gambar' => asset('images/welcome-slide-1.png')],
            ['judul' => 'Everything You Crave, One Tap Away', 'deskripsi' => 'From fresh cakes to artisanal pastries — order it all in Daisy Bakery App', 'gambar' => asset('images/welcome-slide-2.png')],
            ['judul' => 'Every Purchase Pays You Back', 'deskripsi' => "Rack up points with every order and turn them into free treats you'll love", 'gambar' => asset('images/welcome-slide-3.png')],
        ]);
    }

    $jumlahSlide = $daftarSlide->count();
    $lebarSlide = 100 / $jumlahSlide;
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF5E8]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Welcome &middot; {{ config('app.name', 'Daisy Bakery') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-font-display {
            font-family: var(--font-display), "Fraunces", Georgia, serif;
            font-variation-settings: "SOFT" 100, "WONK" 1;
        }
        .auth-font-accent {
            font-family: var(--font-accent), "Cookie", cursive !important;
        }
        .auth-font-ui {
            font-family: var(--font-ui), "DM Sans", sans-serif;
        }
        @keyframes authFloat {
            0%, 100% { transform: translateY(0px) rotate(var(--rot, 0deg)); }
            50% { transform: translateY(-7px) rotate(calc(var(--rot, 0deg) + 3deg)); }
        }
        .auth-float {
            animation: authFloat var(--dur, 4.6s) ease-in-out infinite;
            will-change: transform;
        }

        /* Carousel Track */
        .carousel-track {
            display: flex;
            height: 100%;
            transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
        .carousel-slide {
            position: relative;
            height: 100%;
        }

        #slide-text {
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        /* Page Animations */
        @keyframes pageEnterFromRight {
            0% { opacity: 0; transform: translateX(18px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes pageEnterFromLeft {
            0% { opacity: 0; transform: translateX(-18px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        .page-enter-right { animation: pageEnterFromRight 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .page-enter-left { animation: pageEnterFromLeft 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes appSplashIn {
            0% { opacity: 0; transform: translateY(12px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .app-splash-in { animation: appSplashIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>

<body class="min-h-screen w-full bg-[#FAF5E8] auth-font-ui text-[#231815] antialiased overflow-x-hidden">

    {{-- Wavy Liquid Loading Reveal Animation --}}
    <x-common.wave-loader subtitle="Freshly Baked Everyday" />

    {{-- Full-Bleed Split Layout (Matches Organic Desktop & Airy Cream Mobile Theme) --}}
    <div id="app-card" class="relative min-h-screen w-full flex flex-col md:grid md:grid-cols-12 bg-[#FAF5E8] overflow-hidden">

        {{-- ═══════════════════════════════════════════════════════════════
             LEFT SECTION (Desktop: md:col-span-6 / Mobile: Top ~52vh)
             Carousel Viewport with full-bleed photos + organic seam
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="relative w-full h-[48vh] sm:h-[52vh] md:h-auto md:min-h-screen md:col-span-6 overflow-hidden bg-[#3A221B] select-none">
            
            {{-- Carousel Sliding Viewport --}}
            <div id="carousel-viewport" class="relative w-full h-full overflow-hidden touch-pan-x cursor-grab active:cursor-grabbing">
                <div id="carousel-track" class="carousel-track" style="width: {{ $jumlahSlide * 100 }}%">
                    @foreach ($daftarSlide as $slide)
                        <div class="carousel-slide" style="flex: 0 0 {{ $lebarSlide }}%; width: {{ $lebarSlide }}%;"
                            data-title="{{ $slide['judul'] }}" data-desc="{{ $slide['deskripsi'] }}">
                            <img src="{{ $slide['gambar'] }}" alt="{{ $slide['judul'] }}"
                                class="absolute inset-0 w-full h-full object-cover object-center">
                            {{-- Soft vignette --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/20 pointer-events-none"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Mobile Bottom Organic Wave Divider (Smooth transition to cream card below) --}}
            <svg class="md:hidden pointer-events-none absolute -bottom-[1px] left-0 w-full h-9 sm:h-12 z-20"
                viewBox="0 0 375 40" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M0,40 L375,40 L375,18 C280,38 200,6 120,24 C60,37 25,12 0,22 Z" fill="#FAF5E8" />
            </svg>

            {{-- Desktop Organic Full-Height Vertical SVG Wave Seam --}}
            <svg class="hidden md:block pointer-events-none absolute top-0 -right-[1px] h-full w-20 lg:w-28 z-20"
                viewBox="0 0 110 900" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M 110 0 L 110 900 L 42 900 C 12 815, 76 740, 48 645 C 20 550, 74 475, 40 370 C 8 270, 68 185, 36 92 C 20 45, 34 18, 48 0 Z"
                    fill="#FAF5E8" />
            </svg>


        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             RIGHT SECTION (Desktop: md:col-span-6 / Mobile: Bottom Card)
             Warm Cream Theme (#FAF5E8) + Brand Logo + Slide Text + Actions
             ═══════════════════════════════════════════════════════════════ --}}
        <div id="text-col" class="relative md:col-span-6 flex-1 flex flex-col justify-between bg-[#FAF5E8] px-6 sm:px-10 md:px-10 lg:px-16 xl:px-20 py-6 md:py-10 overflow-hidden">

            {{-- Decorative Whimsical Strawberries (Floating corner elements) --}}
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -right-3 top-20 md:top-12 w-12 h-14 opacity-20 md:opacity-25"
                style="--rot: 16deg; --dur: 4.5s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -left-3 bottom-20 md:bottom-12 w-12 h-14 opacity-20 md:opacity-22"
                style="--rot: -14deg; --dur: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
                class="pointer-events-none select-none absolute right-6 bottom-6 w-16 h-16 opacity-[0.12] rotate-12">

            {{-- Top Navigation Bar --}}
            <div class="relative z-10 flex items-center justify-between w-full max-w-[430px] mx-auto">
            </div>

            {{-- Center Onboarding Content Area --}}
            <div class="relative z-10 w-full max-w-[430px] mx-auto my-auto py-4 md:py-6 text-center">

                {{-- Brand Logo (Medallion + Wordmark) --}}
                <div class="inline-flex items-center justify-center gap-3 mb-6">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 p-2 shadow-[0_8px_20px_rgba(58,34,27,0.08)]">
                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                    </span>
                    <img src="{{ asset('images/logo/logo-wordmark.svg') }}" alt="Daisy Bakery" class="h-8 w-auto object-contain">
                </div>

                {{-- Carousel Indicator Pills --}}
                <div class="flex items-center justify-center gap-2 mb-5 {{ $jumlahSlide < 2 ? 'hidden' : '' }}">
                    @foreach ($daftarSlide as $i => $slide)
                        <button type="button" onclick="goToSlide({{ $i }})" id="dot-{{ $i }}"
                            class="transition-all duration-300 rounded-full {{ $i === 0 ? 'w-8 h-2.5 bg-[#8E1E26] shadow-xs' : 'w-2.5 h-2.5 bg-[#3C2415]/20 hover:bg-[#3C2415]/40' }}"
                            aria-label="Slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>

                {{-- Dynamic Slide Title & Description --}}
                <div id="slide-text" class="w-full px-2 mb-8">
                    <h1 id="slide-title" class="auth-font-display text-[26px] sm:text-[30px] lg:text-[34px] font-extrabold text-[#3A221B] leading-tight tracking-tight">
                        {{ $daftarSlide[0]['judul'] }}
                    </h1>
                    <p id="slide-desc" class="mt-2.5 text-xs sm:text-sm text-[#6B574C] font-medium leading-relaxed max-w-sm mx-auto whitespace-pre-line">
                        {{ $daftarSlide[0]['deskripsi'] }}
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-3 flex flex-col items-center w-full">
                    {{-- Primary: Sign In --}}
                    <a href="{{ route('member.login') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="w-full rounded-full py-3.5 px-6 font-ui text-sm font-bold tracking-wide shadow-md transition-all duration-200 bg-[#8E1E26] hover:bg-[#72151D] text-white text-center hover:shadow-lg active:scale-95">
                        Sign In
                    </a>

                    {{-- Secondary: Join Member Club --}}
                    <a href="{{ route('member.register') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="w-full rounded-full py-3 px-6 font-ui text-xs font-bold tracking-wider uppercase border border-[#3C2415]/16 bg-[#FFFDF9] text-[#3A221B] hover:border-[#8E1E26]/40 hover:text-[#8E1E26] shadow-[0_2px_8px_rgba(58,34,27,0.04)] transition-all text-center active:scale-95">
                        Join Daisy Member Club
                    </a>

                    {{-- Tertiary: Skip to Menu --}}
                    <a href="{{ route('member.beranda') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="pt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[#7D6A60] hover:text-[#8E1E26] transition">
                        <span>Explore Menu as Guest</span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Bottom Footer Note --}}
            <div class="relative z-10 w-full text-center pt-4">
                <p class="text-[11px] font-medium text-[#9A877B]">
                    Open {{ \App\Support\JamOperasional::buka() }} – {{ \App\Support\JamOperasional::tutup() }} &middot; Fresh Artisan Bakes Daily
                </p>
            </div>

        </div>

    </div>

    {{-- Carousel & Smooth Page Transition JavaScript --}}
    <script>
        function restoreAppCard(isBack = false) {
            const card = document.getElementById('app-card');
            if (!card) return;
            card.style.opacity = '';
            card.style.transform = '';
            card.style.transition = '';
            if (isBack) {
                card.classList.remove('app-splash-in', 'page-enter-right');
                card.classList.remove('page-enter-left');
                void card.offsetWidth;
                card.classList.add('page-enter-left');
            }
        }

        window.addEventListener('pageshow', (event) => {
            restoreAppCard(event.persisted);
        });

        window.addEventListener('pagehide', () => {
            restoreAppCard(false);
        });

        window.addEventListener('popstate', () => {
            restoreAppCard(true);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('app-card');
            if (card) {
                restoreAppCard(false);
                const alreadyOpened = sessionStorage.getItem('saisho_app_opened');
                if (!alreadyOpened) {
                    card.classList.add('app-splash-in');
                    sessionStorage.setItem('saisho_app_opened', '1');
                } else {
                    const dir = sessionStorage.getItem('nav_dir');
                    card.classList.add(dir === 'back' ? 'page-enter-left' : 'page-enter-right');
                }
                sessionStorage.removeItem('nav_dir');
            }
        });

        function animateNavigate(url, dir) {
            const card = document.getElementById('app-card');
            if (!card) { window.location.href = url; return; }
            card.style.transition = 'all 0.18s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '0';
            if (dir === 'forward') {
                card.style.transform = 'translateX(-16px)';
                sessionStorage.setItem('nav_dir', 'forward');
            } else {
                card.style.transform = 'translateX(20px)';
                sessionStorage.setItem('nav_dir', 'back');
            }
            setTimeout(() => {
                window.location.href = url;
            }, 160);
            setTimeout(() => { restoreAppCard(false); }, 500);
        }

        let currentSlide = 0;
        const totalSlides = {{ $jumlahSlide }};
        const lebarSlide = 100 / totalSlides;
        const track = document.getElementById('carousel-track');
        const viewport = document.getElementById('carousel-viewport');
        const slideText = document.getElementById('slide-text');
        const slideTitle = document.getElementById('slide-title');
        const slideDesc = document.getElementById('slide-desc');
        let autoSlideTimer = null;

        function updateSlideText(index) {
            const slide = track.children[index];
            if (!slideText || !slide) return;
            slideText.style.opacity = '0';
            slideText.style.transform = 'translateY(6px)';
            setTimeout(() => {
                if (slideTitle) slideTitle.textContent = slide.dataset.title;
                if (slideDesc) slideDesc.textContent = slide.dataset.desc;
                slideText.style.opacity = '1';
                slideText.style.transform = 'translateY(0)';
            }, 180);
        }

        function goToSlide(index) {
            currentSlide = index;
            if (track) {
                track.style.transform = `translateX(-${currentSlide * lebarSlide}%)`;
            }
            updateSlideText(currentSlide);

            for (let i = 0; i < totalSlides; i++) {
                const dot = document.getElementById(`dot-${i}`);
                if (!dot) continue;
                if (i === currentSlide) {
                    dot.className = 'w-8 h-2.5 rounded-full bg-[#8E1E26] shadow-xs transition-all duration-300';
                } else {
                    dot.className = 'w-2.5 h-2.5 rounded-full bg-[#3C2415]/20 hover:bg-[#3C2415]/40 transition-all duration-300';
                }
            }
        }

        function nextSlide() {
            let next = (currentSlide + 1) % totalSlides;
            goToSlide(next);
        }

        function prevSlide() {
            let prev = (currentSlide - 1 + totalSlides) % totalSlides;
            goToSlide(prev);
        }

        function startAutoSlide() {
            stopAutoSlide();
            if (totalSlides < 2) return;
            autoSlideTimer = setInterval(nextSlide, 5000);
        }

        function stopAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
        }

        let startX = 0;
        let isDragging = false;

        if (viewport) {
            viewport.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isDragging = true;
                stopAutoSlide();
            }, { passive: true });

            viewport.addEventListener('touchend', (e) => {
                if (!isDragging) return;
                let endX = e.changedTouches[0].clientX;
                let diffX = startX - endX;

                if (totalSlides < 2) { isDragging = false; return; }

                if (diffX > 40) {
                    nextSlide();
                } else if (diffX < -40) {
                    prevSlide();
                }
                isDragging = false;
                startAutoSlide();
            }, { passive: true });

            // Pause auto-slide on hover
            viewport.addEventListener('mouseenter', stopAutoSlide);
            viewport.addEventListener('mouseleave', startAutoSlide);
        }

        startAutoSlide();
    </script>
</body>

</html>
