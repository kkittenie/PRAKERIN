<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Selamat Datang &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .carousel-track {
            display: flex;
            height: 100%;
            width: 300%;
            transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .carousel-slide {
            position: relative;
            flex: 0 0 33.333333%;
            width: 33.333333%;
            height: 100%;
        }

        .hero-fade {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 82%;
            pointer-events: none;
            z-index: 1;
            background: linear-gradient(to top,
                    #ffffff 0%,
                    rgba(255, 255, 255, 0.99) 6%,
                    rgba(255, 255, 255, 0.96) 12%,
                    rgba(255, 255, 255, 0.90) 18%,
                    rgba(255, 255, 255, 0.82) 25%,
                    rgba(255, 255, 255, 0.72) 32%,
                    rgba(255, 255, 255, 0.60) 40%,
                    rgba(255, 255, 255, 0.48) 48%,
                    rgba(255, 255, 255, 0.36) 56%,
                    rgba(255, 255, 255, 0.25) 64%,
                    rgba(255, 255, 255, 0.16) 72%,
                    rgba(255, 255, 255, 0.09) 80%,
                    rgba(255, 255, 255, 0.04) 88%,
                    rgba(255, 255, 255, 0.0) 100%);
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
    </style>
</head>

<body class="h-full font-outfit antialiased overflow-x-hidden">
    <div id="app-card"
        class="mx-auto flex min-h-screen max-w-md flex-col bg-white shadow-[0_0_60px_-15px_rgba(90,60,39,0.2)] overflow-hidden">
        <div id="carousel-viewport" class="relative w-full aspect-[3/4] overflow-hidden touch-pan-x bg-[#5a3c27]">
            <div id="carousel-track" class="carousel-track">

                {{-- Slide 1 --}}
                <div class="carousel-slide" data-title="Save More with Saisho Plan"
                    data-desc="Subscribe to Saisho Plan for a rewarding coffee, bakery & cake experience">
                    <img src="{{ asset('images/welcome-slide-1.png') }}" alt="Save More with Saisho Plan"
                        class="absolute inset-0 w-full h-full object-cover object-center">
                </div>

                {{-- Slide 2 --}}
                <div class="carousel-slide" data-title="All Your Favorites in One App"
                    data-desc="Order fresh cakes, artisanal pastries and specialty coffee easily in Saisho App">
                    <img src="{{ asset('images/welcome-slide-2.png') }}" alt="All Your Favorites in One App"
                        class="absolute inset-0 w-full h-full object-cover object-center">
                </div>

                {{-- Slide 3 --}}
                <div class="carousel-slide" data-title="Collect Points & Redeem Rewards"
                    data-desc="Earn points on every single purchase and exchange them for free delicious treats">
                    <img src="{{ asset('images/welcome-slide-3.png') }}" alt="Collect Points & Redeem Rewards"
                        class="absolute inset-0 w-full h-full object-cover object-center">
                </div>

            </div>
            <div class="hero-fade"></div>
        </div>

        <div class="relative z-10 flex flex-1 flex-col -mt-24">

            <div class="pt-2 pb-1 flex items-center justify-center gap-1.5">
                <button type="button" onclick="goToSlide(0)" id="dot-0"
                    class="h-2 w-2 rounded-full bg-[#5a3c27] transition-all duration-300" aria-label="Slide 1"></button>
                <button type="button" onclick="goToSlide(1)" id="dot-1"
                    class="h-1.5 w-1.5 rounded-full bg-gray-300 transition-all duration-300"
                    aria-label="Slide 2"></button>
                <button type="button" onclick="goToSlide(2)" id="dot-2"
                    class="h-1.5 w-1.5 rounded-full bg-gray-300 transition-all duration-300"
                    aria-label="Slide 3"></button>
            </div>

            <div id="slide-text" class="w-full px-6 pt-2 pb-1 text-center">
                <h2 id="slide-title" class="text-xl font-extrabold tracking-tight text-[#5a3c27]">Save More with Saisho
                    Plan</h2>
                <p id="slide-desc" class="mt-1 mb-4 text-xs leading-relaxed text-gray-500 max-w-xs mx-auto">Subscribe to
                    Saisho Plan for a rewarding coffee, bakery & cake experience</p>
            </div>

            <div class="mt-auto px-6 pb-5 pt-2 flex flex-col items-center gap-2.5">

                <a href="{{ route('member.login') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                    class="w-full rounded-full bg-[#5a3c27] py-3 text-center text-base font-bold text-white shadow-lg transition hover:bg-[#4a3120] active:scale-[0.99]">
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
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('app-card');
            const alreadyOpened = sessionStorage.getItem('saisho_app_opened');
            if (!alreadyOpened) {
                card.classList.add('app-splash-in');
                sessionStorage.setItem('saisho_app_opened', '1');
            } else {
                const dir = sessionStorage.getItem('nav_dir');
                card.classList.add(dir === 'back' ? 'page-enter-left' : 'page-enter-right');
            }
            sessionStorage.removeItem('nav_dir');
        });

        function animateNavigate(url, dir) {
            const card = document.getElementById('app-card');
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
        }

        let currentSlide = 0;
        const totalSlides = 3;
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
            track.style.transform = `translateX(-${currentSlide * 33.333333}%)`;
            updateSlideText(currentSlide);

            for (let i = 0; i < totalSlides; i++) {
                const dot = document.getElementById(`dot-${i}`);
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