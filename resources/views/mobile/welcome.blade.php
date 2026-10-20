<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Selamat Datang &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Lebar track & slide diatur lewat inline style (tergantung jumlah slide dari admin). */
        .carousel-track {
            display: flex;
            height: 100%;
            transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .carousel-slide {
            position: relative;
            height: 100%;
        }

        .hero-fade {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 75%;
            pointer-events: none;
            z-index: 1;
            background: linear-gradient(to top, #ffffff 0%, rgba(255, 255, 255, 0.85) 30%, rgba(255, 255, 255, 0) 100%);
        }

        #slide-text {
            transition: opacity 0.2s ease;
        }

        /* Subtle Smooth Page Animations */
        @keyframes pageEnterFromRight {
            0% { opacity: 0; transform: translateX(20px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes pageEnterFromLeft {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        .page-enter-right {
            animation: pageEnterFromRight 0.22s cubic-bezier(0.16, 1, 0.3, 1) ease-out forwards;
        }
        .page-enter-left {
            animation: pageEnterFromLeft 0.22s cubic-bezier(0.16, 1, 0.3, 1) ease-out forwards;
        }

        /* One-time splash entrance the very first time the app is opened this session */
        @keyframes appSplashIn {
            0% { opacity: 0; transform: translateY(14px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .app-splash-in {
            animation: appSplashIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Plain CSS overrides for md+ layout — not dependent on Tailwind build/cache */
        @media (min-width: 768px) {
            body {
                min-height: 100vh !important;
                height: 100vh !important;
                width: 100% !important;
            }

            #app-card {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                align-items: stretch !important;
                min-height: 100vh !important;
                height: 100vh !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            #carousel-viewport {
                aspect-ratio: unset !important;
                height: 100% !important;
                border-radius: 0 !important;
            }

            #carousel-viewport .hero-fade {
                height: 50% !important;
                background: linear-gradient(to top,
                        rgba(0, 0, 0, 0.6) 0%,
                        rgba(0, 0, 0, 0.2) 50%,
                        rgba(0, 0, 0, 0.0) 100%) !important;
            }

            #text-col {
                margin-top: 0 !important;
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                gap: 2.5rem !important;
                padding-left: 3rem !important;
                padding-right: 3rem !important;
                background-color: #ffffff !important;
            }

            #button-row {
                margin-top: 0 !important;
                width: 100% !important;
                max-width: 380px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
        }
    </style>
</head>

@php
    // Slide dari admin (menu Slide Welcome). Kalau belum ada yang aktif, pakai 3 slide bawaan.
    $daftarSlide = collect($slides ?? [])->map(fn ($sl) => [
        'judul' => $sl->judul,
        'deskripsi' => (string) $sl->deskripsi,
        'gambar' => asset('storage/' . $sl->gambar),
    ])->values();

    if ($daftarSlide->isEmpty()) {
        $daftarSlide = collect([
            ['judul' => 'Fresh Bakes, Sweeter Savings', 'deskripsi' => 'Join Saisho Plan and unlock exclusive perks on your favorite breads & cakes', 'gambar' => asset('images/welcome-slide-1.png')],
            ['judul' => 'Everything You Crave, One Tap Away', 'deskripsi' => 'From fresh cakes to artisanal pastries — order it all in Saisho App', 'gambar' => asset('images/welcome-slide-2.png')],
            ['judul' => 'Every Purchase Pays You Back', 'deskripsi' => "Rack up points with every order and turn them into free treats you'll love", 'gambar' => asset('images/welcome-slide-3.png')],
        ]);
    }

    $jumlahSlide = $daftarSlide->count();
    $lebarSlide = 100 / $jumlahSlide;
@endphp

<body class="h-full font-outfit antialiased overflow-x-hidden md:min-h-screen md:h-screen md:w-full">
    <div id="app-card"
        class="mx-auto flex min-h-screen w-full flex-col bg-white shadow-2xl overflow-hidden transition-all duration-300 md:grid md:grid-cols-2 md:min-h-screen md:h-screen md:w-full md:max-w-none md:rounded-none md:shadow-none">
        <div id="carousel-viewport" class="relative w-full aspect-[3/4] sm:aspect-[4/3] md:aspect-auto md:h-full overflow-hidden touch-pan-x bg-[#5a3c27] md:rounded-none">
            <div id="carousel-track" class="carousel-track" style="width: {{ $jumlahSlide * 100 }}%">
                @foreach ($daftarSlide as $slide)
                    <div class="carousel-slide" style="flex: 0 0 {{ $lebarSlide }}%; width: {{ $lebarSlide }}%;"
                        data-title="{{ $slide['judul'] }}" data-desc="{{ $slide['deskripsi'] }}">
                        <img src="{{ $slide['gambar'] }}" alt="{{ $slide['judul'] }}"
                            class="absolute inset-0 w-full h-full object-cover object-center">
                    </div>
                @endforeach
            </div>
            <div class="hero-fade"></div>
        </div>

        <div id="text-col" class="relative z-10 flex flex-1 flex-col -mt-24 justify-between md:mt-0 md:h-full md:justify-center md:gap-8 md:px-4">

            <div id="slide-block">
                <div class="pt-2 pb-1 flex items-center justify-center gap-1.5 {{ $jumlahSlide < 2 ? 'hidden' : '' }}">
                    @foreach ($daftarSlide as $i => $slide)
                        <button type="button" onclick="goToSlide({{ $i }})" id="dot-{{ $i }}"
                            class="rounded-full transition-all duration-300 {{ $i === 0 ? 'h-2 w-2 bg-[#5a3c27]' : 'h-1.5 w-1.5 bg-gray-300' }}"
                            aria-label="Slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>

                <div id="slide-text" class="w-full px-6 pt-2 pb-1 text-center">
                    <h2 id="slide-title" class="text-xl font-extrabold tracking-tight text-[#5a3c27]">{{ $daftarSlide[0]['judul'] }}</h2>
                    <p id="slide-desc" class="mt-1 mb-4 text-xs leading-relaxed text-gray-500 max-w-xs mx-auto whitespace-pre-line">{{ $daftarSlide[0]['deskripsi'] }}</p>
                </div>
            </div>

            <div id="button-row" class="px-6 pb-5 pt-2 flex flex-col items-center gap-2.5 sm:pb-8 md:pb-2">

                <a href="{{ route('member.login') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                    class="w-full sm:w-auto sm:min-w-[200px] rounded-full bg-[#5a3c27] py-3 text-center text-base font-bold text-white shadow-lg transition hover:bg-[#4a3120] active:scale-[0.99]">
                    Login
                </a>

                <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                    class="py-1 text-sm font-semibold text-[#5a3c27] hover:underline">
                    Skip This Step
                </a>
            </div>
        </div>

    </div>

    {{-- Carousel & Page Transition JavaScript --}}
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

        // Handle BFCache (Back-Forward Cache) when user uses device back button
        window.addEventListener('pageshow', (event) => {
            restoreAppCard(event.persisted);
        });

        // Clear inline opacity before page is frozen/hidden in BFCache
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
            card.style.transition = 'all 0.16s cubic-bezier(0.4, 0, 0.2, 1)';
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
            }, 150);
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
            slideText.style.opacity = 0;
            setTimeout(() => {
                slideTitle.textContent = slide.dataset.title;
                slideDesc.textContent = slide.dataset.desc;
                slideText.style.opacity = 1;
            }, 150);
        }

        function goToSlide(index) {
            currentSlide = index;
            track.style.transform = `translateX(-${currentSlide * lebarSlide}%)`;
            updateSlideText(currentSlide);

            for (let i = 0; i < totalSlides; i++) {
                const dot = document.getElementById(`dot-${i}`);
                if (!dot) continue;
                if (i === currentSlide) {
                    dot.className = 'h-2 w-2 rounded-full bg-[#5a3c27] transition-all duration-300';
                } else {
                    dot.className = 'h-1.5 w-1.5 rounded-full bg-gray-300 transition-all duration-300';
                }
            }
        }

        function nextSlide() {
            let next = (currentSlide + 1) % totalSlides;
            goToSlide(next);
        }

        function startAutoSlide() {
            stopAutoSlide();
            if (totalSlides < 2) return; // satu slide: tidak perlu geser otomatis
            autoSlideTimer = setInterval(nextSlide, 4500);
        }

        function stopAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
        }

        let startX = 0;
        let isDragging = false;

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

            if (diffX > 35) {
                goToSlide((currentSlide + 1) % totalSlides);
            } else if (diffX < -35) {
                goToSlide((currentSlide - 1 + totalSlides) % totalSlides);
            }
            isDragging = false;
            startAutoSlide();
        }, { passive: true });

        startAutoSlide();
    </script>
</body>

</html>