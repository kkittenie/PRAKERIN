@extends('mobile.layout', ['title' => 'Voucher Saya'])

@section('content')

    {{-- ═══════════════════════════════════════════════════════════════════════
         MOBILE LAYOUT (hidden on lg+)
         ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:hidden">
        <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-3 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
            <h1 class="text-center text-lg font-extrabold text-gray-900">Voucher Saya</h1>
        </div>

        {{-- Fore-style underline tabs --}}
        <div class="relative mt-3 grid grid-cols-2 border-b border-gray-100">
            @foreach (['voucher' => 'Voucher Saya', 'stamp' => 'Stamp'] as $key => $label)
                <button type="button" data-tab="{{ $key }}" onclick="switchVoucherTab('{{ $key }}')"
                        class="voucher-tab relative pb-3 text-center text-[13px] font-bold transition {{ $key === 'voucher' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    {{ $label }}
                </button>
            @endforeach
            <span id="voucher-tab-indicator"
                  class="pointer-events-none absolute -bottom-px left-0 h-[2.5px] w-1/2 rounded-full bg-brand-800 transition-transform duration-300 ease-out"
                  style="transform: translateX(0%);"></span>
        </div>

        {{-- Swipeable panes --}}
        <div id="voucher-tab-viewport" class="relative flex flex-1 flex-col overflow-hidden">
            <div id="voucher-tab-track" class="flex flex-1 transition-transform duration-300 ease-out">

                {{-- Pane: Voucher Saya --}}
                <div class="voucher-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="0">
                    <button type="button" id="stamp-teaser-card" onclick="switchVoucherTab('stamp')"
                            class="mb-4 flex w-full items-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-4 text-left text-white shadow-sm transition active:scale-[0.99]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15 p-2">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                        </span>
                        <div class="min-w-0 flex-1">
                            <p id="stamp-teaser-title" class="truncate text-sm font-extrabold">Program Stamp</p>
                            <p id="stamp-teaser-subtitle" class="mt-0.5 text-[11px] text-white/75">Kumpulin stamp, dapatkan hadiah gratis</p>
                        </div>
                        <span class="shrink-0 text-white/60">
                            @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                        </span>
                    </button>

                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Daftar Voucher</h2>
                        <span class="text-xs text-gray-400">{{ $vouchers->count() }} voucher</span>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 space-y-0">
                        @forelse ($vouchers as $voucher)
                            @include('mobile.components.reward-card', ['voucher' => $voucher, 'member' => $member])
                        @empty
                            @include('mobile.components.empty-state', [
                                'title' => 'Belum Ada Voucher',
                                'description' => 'Voucher yang tersedia untuk kamu akan otomatis muncul di sini.',
                                'icon' => 'icon-ticket',
                                'iconBg' => 'bg-brand-50',
                                'iconColor' => 'text-brand-800',
                            ])
                        @endforelse
                    </div>
                </div>

                {{-- Pane: Stamp --}}
                <div class="voucher-tab-pane flex shrink-0 flex-col bg-white" data-pane-index="1">
                    <div id="stamp-pane-content" class="flex flex-1 flex-col">
                        <div class="flex flex-1 flex-col items-center justify-center py-10 text-center">
                            <svg class="h-6 w-6 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <p class="mt-3 text-xs text-gray-400">Memuat stamp...</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         DESKTOP LAYOUT (hidden below lg) — BESPOKE VINTAGE BAKERY STYLE
         Matches Menu layout: Melting cocoa canopy hero, centerpiece celebration cake,
         rotating discover stamp, floating 2D strawberries, and scallop-badge tabs.
         ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="vouchers-desktop-root" class="hidden lg:block w-full bg-[#FAF5E8]"
         x-data="{
             desktopTab: (new URLSearchParams(window.location.search).get('tab') || 'voucher')
         }">

        {{-- 1. DEEP COCOA MELTING HERO CANOPY WITH CAKE --}}
        <section id="vouchers-desktop-hero" class="relative w-full text-[#FAF5E8] overflow-visible">
            {{-- Top Dark Cocoa Banner (Navbar & Narrative) --}}
            <div class="relative w-full bg-[#3C2415] pt-10 pb-8 sm:pb-9 lg:pb-10 xl:pb-12 text-center overflow-hidden">
                {{-- Background Line Art Bakery Doodles (Cake on Left, Croissant on Right) --}}
                <div class="pointer-events-none absolute inset-0 opacity-18 select-none overflow-hidden">
                    {{-- Left Tiered Celebration Cake Line Art Doodle --}}
                    <svg class="absolute -left-6 lg:left-2 2xl:left-8 top-6 w-72 lg:w-80 h-72 lg:h-80 text-white" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 40 160 Q 100 172 160 160 Q 100 178 40 160 Z"/>
                        <path d="M 85 167 L 76 186 L 124 186 L 115 167"/>
                        <path d="M 70 186 Q 100 190 130 186"/>
                        <path d="M 46 124 L 46 154 Q 100 168 154 154 L 154 124"/>
                        <path d="M 46 124 Q 100 138 154 124 Q 100 110 46 124 Z"/>
                        <path d="M 46 154 Q 55 160 64 156 Q 73 162 82 158 Q 91 163 100 163 Q 109 163 118 158 Q 127 162 136 156 Q 145 160 154 154"/>
                        <path d="M 68 84 L 68 116 Q 100 128 132 116 L 132 84"/>
                        <path d="M 68 84 Q 100 96 132 84 Q 100 72 68 84 Z"/>
                        <path d="M 68 88 Q 75 100 82 92 Q 89 103 96 94 Q 104 105 111 94 Q 118 102 125 92 Q 129 98 132 88"/>
                        <path d="M 98 62 L 98 78 L 102 78 L 102 62 Z"/>
                        <path d="M 100 52 Q 95 58 100 62 Q 105 58 100 52 Z"/>
                        <circle cx="82" cy="74" r="7"/>
                        <path d="M 82 67 Q 87 56 93 58"/>
                        <circle cx="118" cy="74" r="7"/>
                        <path d="M 118 67 Q 123 56 129 58"/>
                        <path d="M 32 65 L 34 73 L 42 75 L 34 77 L 32 85 L 30 77 L 22 75 L 30 73 Z"/>
                        <path d="M 168 62 L 170 68 L 176 70 L 170 72 L 168 78 L 166 72 L 160 70 L 166 68 Z"/>
                    </svg>

                    {{-- Right Authentic Curved Crescent Croissant Line Art Doodle --}}
                    <svg class="absolute -right-6 lg:right-2 2xl:right-8 top-6 w-72 lg:w-80 h-72 lg:h-80 text-white" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 32 132 C 22 96 46 54 96 46 C 146 38 178 76 170 126 C 156 112 140 102 120 98 C 96 94 70 102 48 120 C 38 125 34 130 32 132 Z"/>
                        <path d="M 76 50 C 85 74 87 102 76 128 C 92 120 108 120 122 126 C 130 102 128 74 118 48"/>
                        <path d="M 58 60 C 66 82 68 105 58 126"/>
                        <path d="M 98 47 C 104 70 105 95 98 122"/>
                        <path d="M 138 60 C 144 82 142 105 134 124"/>
                        <path d="M 32 132 C 28 138 30 144 36 142 C 42 140 46 134 48 126"/>
                        <path d="M 170 126 C 174 133 172 139 166 139 C 160 139 155 133 151 125"/>
                        <path d="M 86 68 Q 92 84 86 98"/>
                        <path d="M 108 66 Q 114 84 108 97"/>
                        <path d="M 85 32 Q 91 22 85 12"/>
                        <path d="M 105 28 Q 111 18 105 8"/>
                        <path d="M 125 34 Q 131 24 125 14"/>
                    </svg>
                </div>

                {{-- Centered Title & Narrative --}}
                <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">

                    <h1 class="font-display font-black text-5xl lg:text-6xl xl:text-7xl uppercase tracking-tight text-[#FAF5E8] flex items-center justify-center gap-3.5 drop-shadow-md">
                        <span>OUR</span>
                        <span class="text-[#8E1E26]">SWEET REWARDS</span>
                    </h1>

                    <p class="mt-4 font-ui text-sm sm:text-base text-[#FAF5E8]/75 max-w-xl mx-auto leading-relaxed">
                        Enjoy handcrafted member savings on our signature celebration cakes, flaky viennoiseries, and daily baked artisan sourdough.
                    </p>
                </div>
            </div>

            {{-- Organic Melting Canopy Bottom SVG on Cream Background with Big Cake & Stamp Nested Inside --}}
            <div class="relative w-full bg-[#FAF5E8] overflow-visible select-none z-20 -mt-1">
                <svg viewBox="0 0 1440 360" fill="none" class="w-full h-64 sm:h-72 lg:h-[350px] xl:h-[380px] block pointer-events-none" preserveAspectRatio="none">
                    <path d="M 0 0 L 1440 0 L 1440 140 C 1360 160, 1310 110, 1260 110 C 1120 110, 960 345, 720 345 C 480 345, 320 110, 180 110 C 130 110, 80 160, 0 140 Z" fill="#3C2415"/>
                </svg>

                {{-- Subtle Ambient Warm Bakery Glow Behind Cake --}}
                <div id="vouchers-desktop-glow" class="pointer-events-none absolute left-1/2 top-[100px] sm:top-[105px] lg:top-[110px] xl:top-[115px] -translate-x-1/2 -translate-y-1/2 w-[460px] sm:w-[520px] lg:w-[580px] xl:w-[640px] h-[280px] sm:h-[320px] lg:h-[360px] xl:h-[390px] rounded-full blur-3xl opacity-70 z-10"
                     style="background: radial-gradient(circle, rgba(255, 230, 150, 0.45) 0%, rgba(220, 100, 50, 0.22) 42%, rgba(60, 36, 21, 0) 72%);">
                </div>

                {{-- Prominent Centerpiece: Big Vintage Strawberry Celebration Cake --}}
                <div id="vouchers-desktop-hero-cake" class="absolute left-1/2 -top-2 sm:-top-3 lg:-top-4 xl:-top-5 -translate-x-1/2 pointer-events-none select-none z-20 flex flex-col items-center">
                    <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="Artisanal Daisy Strawberry Celebration Cake"
                         class="w-[230px] sm:w-[260px] lg:w-[290px] xl:w-[310px] h-auto object-contain filter drop-shadow-[0_22px_28px_rgba(20,10,5,0.72)] drop-shadow-[0_8px_14px_rgba(20,10,5,0.52)]">
                </div>

                {{-- Compact Cute Rotating "DISCOVER MORE" Stamp Badge Nested Deep in the Melting Scoop --}}
                <div id="vouchers-desktop-stamp" class="absolute left-1/2 bottom-5 sm:bottom-6 lg:bottom-7 xl:bottom-8 -translate-x-1/2 z-30">
                    <button type="button"
                            @click="document.getElementById('vouchers-showcase-content').scrollIntoView({ behavior: 'smooth' })"
                            class="relative w-14 h-14 sm:w-15 sm:h-15 lg:w-16 lg:h-16 flex items-center justify-center group focus:outline-none transition-transform hover:scale-105 active:scale-95 cursor-pointer pointer-events-auto"
                            title="Scroll down to vouchers">
                        <div class="absolute inset-0 rounded-full bg-[#FAF5E8] border-2 border-[#3C2415]/20 shadow-lg group-hover:border-[#8E1E26]/40 transition-colors"></div>

                        {{-- Rotating Circular Text --}}
                        <svg class="w-11 h-11 sm:w-12 sm:h-12 lg:w-13 lg:h-13 animate-[spin_14s_linear_infinite]" viewBox="0 0 100 100">
                            <path id="circlePathDesktopVouchers" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" fill="none" />
                            <text class="text-[9px] font-bold tracking-[0.24em] uppercase fill-[#3C2415]">
                                <textPath href="#circlePathDesktopVouchers" startOffset="0%">
                                    DISCOVER MORE • DISCOVER MORE •
                                </textPath>
                            </text>
                        </svg>

                        {{-- Centered Arrow --}}
                        <div class="absolute flex items-center justify-center text-[#8E1E26] group-hover:translate-y-0.5 transition-transform duration-200">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12l7 7 7-7"/>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        {{-- 2. VOUCHERS SHOWCASE & SCALLOP FILTER TABS (MATCHING MENU STYLE) --}}
        <section id="vouchers-desktop-showcase" class="relative w-full bg-[#FAF5E8] overflow-hidden">
            {{-- Strawberry Keyframe Styles (Consistent with Menu & Beranda) --}}
            <style>
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
            </style>

            {{-- 2D Floating Strawberries in Outer Gutters --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-8 xl:left-12 2xl:left-20 top-12 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 48px; height: 64px; --rot: -10deg; --duration: 4.2s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-8 xl:left-auto xl:right-12 2xl:right-20 top-20 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: 14deg; --duration: 3.8s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-10 xl:left-14 2xl:left-24 top-1/4 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 42px; height: 56px; --rot: 18deg; --duration: 4.8s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-10 xl:left-auto xl:right-14 2xl:right-24 top-1/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: -14deg; --duration: 4.4s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-9 xl:left-13 2xl:left-22 top-1/2 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 46px; height: 60px; --rot: -12deg; --duration: 5.0s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-9 xl:left-auto xl:right-13 2xl:right-22 top-2/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 48px; height: 64px; --rot: 12deg; --duration: 4.1s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div id="vouchers-showcase-content" class="relative max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-10 pb-24 z-10">

                {{-- Scallop Filter Tabs (Matching Menu Page's Distinct Scallop Badges) --}}
                <div id="vouchers-scallop-tabs" class="flex flex-wrap items-center justify-center gap-8 sm:gap-12 lg:gap-16">
                    {{-- TAB 1: ALL VOUCHERS --}}
                    <button type="button"
                            @click="desktopTab = 'voucher'; if (typeof window.switchDesktopTab === 'function') window.switchDesktopTab('voucher');"
                            class="desktop-filter-tab group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="voucher">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'voucher' ? 'text-[#8E1E26] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <span class="text-[#3C2415]">
                                    @include('mobile.components.icon-ticket', ['class' => 'w-7 h-7'])
                                </span>
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'voucher' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            VOUCHERS ({{ $vouchers->count() }})
                        </span>
                    </button>

                    {{-- TAB 2: DAISY STAMP CLUB --}}
                    <button type="button"
                            @click="desktopTab = 'stamp'; if (typeof window.switchDesktopTab === 'function') window.switchDesktopTab('stamp');"
                            class="desktop-filter-tab group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="stamp">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'stamp' ? 'text-[#4A2D1A] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Stamp Club" class="w-8 h-8 object-contain">
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'stamp' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            DAISY STAMP CLUB
                        </span>
                    </button>

                    {{-- TAB 3: HOW TO USE --}}
                    <button type="button"
                            @click="desktopTab = 'guide'; if (typeof window.switchDesktopTab === 'function') window.switchDesktopTab('guide');"
                            class="desktop-filter-tab group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="guide">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'guide' ? 'text-[#3C2415] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <span class="text-[#3C2415]">
                                    @include('mobile.components.icon-gift', ['class' => 'w-7 h-7'])
                                </span>
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'guide' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            HOW TO USE
                        </span>
                    </button>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     PANE 1: VOUCHERS CATALOG (FRENCH BAKERY CARDS LIKE MENU)
                     ═══════════════════════════════════════════════════════════ --}}
                <div id="desktop-pane-voucher"
                     x-show="desktopTab === 'voucher'"
                     class="desktop-pane mt-16 space-y-10">

                    <div class="text-center space-y-2">
                        <h2 class="font-display font-black text-3xl sm:text-4xl text-[#3C2415] uppercase tracking-wide">
                            EXCLUSIVE VOUCHERS
                        </h2>
                        <p class="text-xs sm:text-sm text-[#3C2415]/70 max-w-lg mx-auto font-medium leading-relaxed">
                            Select any eligible voucher during online checkout or present your member number in-store.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                        @forelse ($vouchers as $voucher)
                            @php
                                $tersedia = $voucher->tersedia ?? true;
                                $alasan = $voucher->alasan_nonaktif ?? null;
                                $sisaJatah = $voucher->sisa_jatah ?? 1;
                                $batas = $voucher->batas_penggunaan_per_member ?: 1;

                                $nominal = (float) $voucher->nominal_potongan;
                                $persen = (float) $voucher->persentase;
                                $minSpend = (float) $voucher->min_pembelian;

                                $textSearch = ($voucher->nama ?? '') . ' ' . ($voucher->deskripsi ?? '');
                                if ($nominal <= 0 && $persen <= 0) {
                                    if (preg_match('/(\d+[\.,]?\d*)\s*%/i', $textSearch, $pm)) {
                                        $persen = (float) str_replace(',', '.', $pm[1]);
                                    } elseif (preg_match('/(?:Rp|IDR)\s*([\d\.]+)/i', $textSearch, $nm)) {
                                        $nominal = (float) str_replace('.', '', $nm[1]);
                                    }
                                }
                                if ($minSpend <= 0) {
                                    if (preg_match('/minimal\s+belanja\s+(?:Rp|IDR)?\s*([\d\.]+)/i', $textSearch, $sm)) {
                                        $minSpend = (float) str_replace('.', '', $sm[1]);
                                    }
                                }

                                if ($persen > 0) {
                                    $discountLabel = rtrim(rtrim(number_format($persen, 1, '.', ''), '0'), '.') . '% OFF';
                                    $discountBadge = $discountLabel;
                                } elseif ($nominal > 0) {
                                    $discountLabel = 'Rp ' . number_format($nominal, 0, ',', '.') . ' OFF';
                                    $discountBadge = ($nominal >= 1000)
                                        ? 'Rp ' . rtrim(rtrim(number_format($nominal / 1000, 1, '.', ''), '0'), '.') . 'K OFF'
                                        : 'Rp ' . number_format($nominal, 0, ',', '.') . ' OFF';
                                } else {
                                    $discountLabel = 'Special Perk';
                                    $discountBadge = 'Special Perk';
                                }
                            @endphp

                            {{-- Warm French Almond Card Style (Matching Menu Product Cards) --}}
                            <div class="rounded-[32px] bg-[#F5ECE3] p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-xl hover:-translate-y-1 border border-[#3C2415]/10 group/card">
                                <div>
                                    {{-- Card Media / Value Visual Area --}}
                                    <div class="relative h-48 w-full overflow-hidden rounded-2xl mb-4 bg-white/70 shadow-xs flex flex-col items-center justify-center p-5 text-center relative border border-[#3C2415]/8">
                                        <div class="pointer-events-none absolute -right-4 -bottom-4 w-28 h-28 opacity-10 select-none">
                                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain filter invert">
                                        </div>

                                        <span class="absolute left-3 top-3 rounded-full bg-[#8E1E26] px-3 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-sm">
                                            Member Exclusive
                                        </span>

                                        <p class="font-display font-black text-3xl lg:text-4xl text-[#3C2415] tracking-tight leading-none mt-3">
                                            {{ $discountBadge }}
                                        </p>
                                        <p class="text-[11px] font-semibold text-[#8E1E26] mt-2">
                                            {{ $minSpend > 0 ? 'Min. spend Rp ' . number_format($minSpend, 0, ',', '.') : 'No Minimum Spend' }}
                                        </p>
                                    </div>

                                    <p class="text-[11px] font-semibold text-[#9C8C80] uppercase tracking-wider mb-1">MEMBER VOUCHER</p>
                                    <h3 class="font-display font-bold text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">
                                        {{ $voucher->nama }}
                                    </h3>
                                    <p class="text-xs text-[#3C2415]/70 mt-1.5 line-clamp-2 leading-relaxed">
                                        {{ $voucher->deskripsi }}
                                    </p>
                                </div>

                                {{-- Card Bottom Row --}}
                                <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="text-[10px] uppercase tracking-wider font-bold block text-[#9C8C80]">
                                            Quota: {{ $sisaJatah }}/{{ $batas }}
                                        </span>
                                        <p class="text-xs font-extrabold {{ $tersedia ? 'text-emerald-700' : 'text-red-600' }} truncate">
                                            {{ $tersedia ? 'Ready to Use' : 'Unavailable' }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('member.reward.show', $voucher) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="group/btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                            <span>DETAILS</span>
                                            <span class="inline-block transition-transform duration-200 group-hover/btn:translate-x-0.5 text-sm leading-none font-bold">&rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-16 text-center space-y-4">
                                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] border border-[#3C2415]/15 text-[#3C2415]/40 shadow-xs">
                                    @include('mobile.components.icon-ticket', ['class' => 'h-8 w-8'])
                                </span>
                                <div>
                                    <h3 class="font-display font-bold text-base text-[#3C2415]">No Active Vouchers</h3>
                                    <p class="text-xs text-[#3C2415]/60 mt-1">Exclusive vouchers for your member tier will automatically appear here.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     PANE 2: DAISY STAMP CLUB (LOYALTY CARD & TREATS)
                     ═══════════════════════════════════════════════════════════ --}}
                <div id="desktop-pane-stamp"
                     x-show="desktopTab === 'stamp'"
                     class="desktop-pane mt-16 space-y-12">

                    @if (! $program)
                        <div class="rounded-[32px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center space-y-4 shadow-sm">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] text-amber-600">
                                @include('mobile.components.icon-gift', ['class' => 'h-8 w-8'])
                            </span>
                            <h3 class="font-display font-black text-xl text-[#3C2415]">No Active Stamp Program</h3>
                            <p class="text-xs sm:text-sm text-[#3C2415]/70 max-w-md mx-auto">
                                Stay tuned! Our seasonal stamp collection program will launch again soon with free artisan pastries.
                            </p>
                        </div>
                    @else
                        @php
                            $target = max(1, $program->jumlah_stamp_dibutuhkan);
                            $current = min($memberStamp->jumlah_stamp ?? 0, $target);
                            $penuh = ($memberStamp->jumlah_stamp ?? 0) >= $target;
                            $kuotaHabis = $program->maksimal_redeem_tercapai ?? false;
                            $bisaTukar = $penuh && ! $kuotaHabis;
                        @endphp

                        {{-- Expansive Desktop Punch Card --}}
                        <div class="relative w-full rounded-[36px] bg-[#3C2415] text-[#FAF5E8] p-8 lg:p-12 shadow-2xl border border-white/10 overflow-hidden">
                            <div class="pointer-events-none absolute -right-12 -bottom-12 w-64 h-64 opacity-10 select-none">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain filter invert">
                            </div>

                            <div class="relative z-10 flex flex-wrap items-start justify-between gap-6 pb-8 border-b border-[#FAF5E8]/15">
                                <div class="space-y-2 max-w-xl">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FAF5E8]/15 border border-[#FAF5E8]/20 text-[10px] font-black uppercase tracking-wider text-amber-200">
                                        <span>★ DAISY STAMP CLUB</span>
                                    </div>
                                    <h2 class="font-display font-black text-2xl lg:text-3xl text-[#FAF5E8] tracking-tight">
                                        {{ $program->nama_program }}
                                    </h2>
                                    <p class="text-xs sm:text-sm text-[#FAF5E8]/80 font-medium leading-relaxed">
                                        Every order min. Rp {{ number_format($program->minimal_pembelian, 0, ',', '.') }} = 1 digital stamp. Collect {{ $target }} stamps for a free bakery treat!
                                    </p>
                                </div>

                                <div class="bg-[#FAF5E8]/15 border border-[#FAF5E8]/20 rounded-2xl px-6 py-4 text-center backdrop-blur-xs">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-[#FAF5E8]/70 block">STAMPS COLLECTED</span>
                                    <p class="font-display font-black text-3xl text-amber-200 mt-0.5">
                                        {{ $current }} <span class="text-sm font-sans font-bold text-[#FAF5E8]">/ {{ $target }}</span>
                                    </p>
                                    <span class="text-[11px] font-semibold text-emerald-300 block mt-1">
                                        {{ $penuh ? '🎉 Card Complete!' : ($target - $current) . ' stamps to treat' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 5 Large Horizontal Stamp Punch Circles --}}
                            <div class="relative z-10 py-10">
                                <div class="flex items-center justify-between gap-4 max-w-4xl mx-auto">
                                    @for ($i = 1; $i <= $target; $i++)
                                        @php $terisi = $i <= $current; @endphp
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="w-20 h-20 lg:w-24 lg:h-24 rounded-full flex items-center justify-center transition-all duration-300 relative
                                                        {{ $terisi
                                                            ? 'bg-gradient-to-br from-[#FAF5E8] to-[#FFFDF9] text-[#3C2415] shadow-lg ring-4 ring-amber-300/40 stamp-pop'
                                                            : 'border-2 border-dashed border-[#FAF5E8]/35 bg-[#FAF5E8]/5 text-[#FAF5E8]/40' }}">
                                                @if ($terisi)
                                                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-10 w-10 lg:h-12 lg:w-12 object-contain">
                                                    <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white text-[10px] font-bold shadow-xs">
                                                        ✓
                                                    </span>
                                                @else
                                                    <div class="text-center">
                                                        <span class="text-xs font-bold block opacity-60">#{{ $i }}</span>
                                                        <span class="text-[9px] font-semibold uppercase tracking-wider block opacity-40">Stamp</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $terisi ? 'text-amber-200' : 'text-[#FAF5E8]/40' }}">
                                                {{ $terisi ? 'Collected' : 'Empty' }}
                                            </span>
                                        </div>

                                        @if ($i < $target)
                                            <div class="flex-1 h-0.5 border-t-2 border-dashed {{ $i < $current ? 'border-amber-300/60' : 'border-[#FAF5E8]/20' }} mb-5"></div>
                                        @endif
                                    @endfor
                                </div>
                            </div>

                            <div class="relative z-10 pt-6 border-t border-[#FAF5E8]/15 flex items-center justify-between">
                                <p class="text-xs font-semibold text-[#FAF5E8]/90">
                                    @if ($penuh)
                                        <strong class="text-amber-200">Congratulations!</strong> Choose your free artisan treat below.
                                    @else
                                        Collect <strong>{{ $target - $current }} more stamps</strong> to redeem your reward.
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Available Redeem Treats (Matching Menu Card Style) --}}
                        <div class="space-y-6">
                            <div class="text-center space-y-2">
                                <h3 class="font-display font-black text-2xl lg:text-3xl text-[#3C2415] uppercase tracking-wide">
                                    FREE ARTISAN PASTRIES TO REDEEM
                                </h3>
                                <p class="text-xs text-[#3C2415]/70">Redeem with your 5 collected stamps.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                                @foreach ($program->hadiah as $hadiah)
                                    @php
                                        $produk = $hadiah->produk;
                                        $detail = $hadiah->detailProduk;
                                        $foto = $detail?->foto ?? $produk?->foto;
                                        $namaProduk = $produk?->nama_produk ?? 'Artisan Pastry';
                                    @endphp
                                    <div class="rounded-[32px] bg-[#F5ECE3] p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-xl hover:-translate-y-1 border border-[#3C2415]/10">
                                        <div>
                                            <div class="relative h-52 w-full overflow-hidden rounded-2xl mb-4 bg-white/40 shadow-xs flex items-center justify-center">
                                                @if ($foto)
                                                    <img src="{{ asset('storage/' . $foto) }}" alt="{{ $namaProduk }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-[#3C2415]/20">
                                                        @include('mobile.components.icon-cake', ['class' => 'h-16 w-16'])
                                                    </div>
                                                @endif
                                                <span class="absolute left-2.5 top-2.5 rounded-full bg-[#8E1E26] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                                    {{ $target }} Stamps
                                                </span>
                                            </div>

                                            <p class="text-[11px] font-semibold text-[#9C8C80] uppercase tracking-wider mb-1">STAMP REWARD</p>
                                            <h4 class="font-display font-bold text-lg text-[#3C2415]">{{ $namaProduk }}</h4>
                                            @if ($detail)
                                                <p class="text-xs text-[#3C2415]/60 mt-0.5">{{ $detail->nama_varian }}</p>
                                            @endif
                                            <p class="text-xs text-[#3C2415]/75 mt-2 line-clamp-2">
                                                Freshly baked artisan treat. 100% French Normandy butter.
                                            </p>
                                        </div>

                                        <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                            <span class="text-xs font-extrabold text-[#3C2415]">Free with Stamp</span>
                                            @if ($bisaTukar)
                                                <button type="button"
                                                        onclick="bukaKonfirmasiTukarStamp({{ $produk->id }}, {{ $hadiah->jumlah }}, '{{ addslashes($namaProduk) }}', {{ $detail?->id ? $detail->id : 'null' }})"
                                                        class="group/btn inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-[#8E1E26] hover:bg-[#72181E] text-white text-xs font-bold transition-all duration-200 active:scale-95 shadow-sm cursor-pointer">
                                                    <span>REDEEM</span>
                                                    <span class="text-sm font-bold">&rarr;</span>
                                                </button>
                                            @else
                                                <span class="text-xs font-bold text-[#3C2415]/40 bg-[#3C2415]/5 px-3 py-1.5 rounded-full">
                                                    Need {{ $target - $current }} Stamps
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     PANE 3: HOW TO USE & BAKERY GUIDE
                     ═══════════════════════════════════════════════════════════ --}}
                <div id="desktop-pane-guide"
                     x-show="desktopTab === 'guide'"
                     class="desktop-pane mt-16 space-y-10">

                    <div class="text-center space-y-2">
                        <h2 class="font-display font-black text-3xl sm:text-4xl text-[#3C2415] uppercase tracking-wide">
                            HOW TO ENJOY PERKS
                        </h2>
                        <p class="text-xs sm:text-sm text-[#3C2415]/70 max-w-lg mx-auto font-medium leading-relaxed">
                            Everything you need to know about vouchers, stamps, and artisan treats at Daisy Bakery.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="rounded-[32px] bg-[#F5ECE3] p-8 space-y-4 border border-[#3C2415]/10 shadow-sm">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] font-display font-bold text-lg">
                                1
                            </span>
                            <h3 class="font-display font-bold text-base text-[#3C2415]">Exclusive Vouchers</h3>
                            <p class="text-xs text-[#3C2415]/75 font-medium leading-relaxed">
                                Vouchers are automatically assigned to your member profile. During online checkout, choose your preferred coupon to apply instant savings.
                            </p>
                        </div>

                        <div class="rounded-[32px] bg-[#F5ECE3] p-8 space-y-4 border border-[#3C2415]/10 shadow-sm">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#8E1E26] text-[#FAF5E8] font-display font-bold text-lg">
                                2
                            </span>
                            <h3 class="font-display font-bold text-base text-[#3C2415]">Collecting Digital Stamps</h3>
                            <p class="text-xs text-[#3C2415]/75 font-medium leading-relaxed">
                                Every purchase of Rp 25.000 or more earns 1 digital stamp. Once your punch card reaches 5 stamps, unlock any featured artisan treat for free!
                            </p>
                        </div>

                        <div class="rounded-[32px] bg-[#F5ECE3] p-8 space-y-4 border border-[#3C2415]/10 shadow-sm">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] font-display font-bold text-lg">
                                3
                            </span>
                            <h3 class="font-display font-bold text-base text-[#3C2415]">In-Store Counter Pickup</h3>
                            <p class="text-xs text-[#3C2415]/75 font-medium leading-relaxed">
                                Prefer visiting us in person? Simply mention your phone number or show your voucher barcode at the cashier to claim your discounts and rewards.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-[32px] bg-[#3C2415] text-[#FAF5E8] p-8 sm:p-10 flex flex-wrap items-center justify-between gap-6 shadow-xl">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-[#FAF5E8]">
                                @include('mobile.components.icon-clock', ['class' => 'h-7 w-7'])
                            </span>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-amber-200 block">STORE &amp; OVEN HOURS</span>
                                <h4 class="font-display font-bold text-lg text-white mt-0.5">Daisy Bakery Store Bandung</h4>
                                <p class="text-xs text-[#FAF5E8]/70 mt-0.5">Jl. Cendrawasih 42, Bandung &bull; Open Daily 06:00 – 20:00 WIB</p>
                            </div>
                        </div>

                        <a href="{{ route('member.menu') }}"
                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           class="rounded-full bg-[#8E1E26] hover:bg-[#72181E] px-8 py-3 text-xs font-bold uppercase tracking-wider text-white transition-colors shadow-md">
                            Browse Bakery Menu &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </section>

        {{-- ━━━━ GSAP 3 & SCROLLTRIGGER INTERACTIVE STORYTELLING ENGINE (DESKTOP >= 1024px) ━━━━ --}}
        <script src="{{ asset('vendor/gsap/gsap.min.js') }}"></script>
        <script src="{{ asset('vendor/gsap/ScrollTrigger.min.js') }}"></script>
        <script src="{{ asset('js/desktop-storytelling.js') }}"></script>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SHARED SCRIPTS
         ═══════════════════════════════════════════════════════════════════════ --}}
    <script>
        window.bukaKonfirmasiTukarStamp = function (produkId, jumlah, namaProduk, detailId) {
            const hiddenInput = document.getElementById('konfirmasi-tukar-stamp-produk-id');
            const hiddenDetail = document.getElementById('konfirmasi-tukar-stamp-detail-id');
            const namaSpan = document.getElementById('konfirmasi-tukar-stamp-nama');
            if (hiddenInput) hiddenInput.value = produkId;
            if (hiddenDetail) hiddenDetail.value = detailId || '';
            if (namaSpan) namaSpan.textContent = jumlah + 'x ' + namaProduk;
            openSheet('konfirmasi-tukar-stamp');
        };

        window.bukaKonfirmasiTukarStampFromBtn = function (btn) {
            if (!btn) return;
            const produkId = btn.dataset.produkId;
            const jumlah = btn.dataset.jumlah;
            const namaProduk = btn.dataset.namaProduk;
            window.bukaKonfirmasiTukarStamp(produkId, jumlah, namaProduk, btn.dataset.detailId);
        };

        (function () {
            // ─── Mobile swipeable tabs ───
            const tabs = ['voucher', 'stamp'];
            const requested = new URLSearchParams(window.location.search).get('tab');
            let current = Math.max(0, tabs.indexOf(requested));
            let stampLoaded = false;

            const track = document.getElementById('voucher-tab-track');
            const indicator = document.getElementById('voucher-tab-indicator');
            const buttons = document.querySelectorAll('.voucher-tab');
            const panes = document.querySelectorAll('.voucher-tab-pane');
            const viewport = document.getElementById('voucher-tab-viewport');

            if (track && indicator && viewport) {
                const paneWidthPercent = 100 / tabs.length;
                track.style.width = (tabs.length * 100) + '%';
                panes.forEach((pane, i) => {
                    pane.style.width = paneWidthPercent + '%';
                    pane.style.pointerEvents = i === current ? 'auto' : 'none';
                });

                function paint() {
                    track.style.transform = `translateX(-${current * paneWidthPercent}%)`;
                    panes.forEach((pane, i) => {
                        pane.style.pointerEvents = i === current ? 'auto' : 'none';
                        pane.style.zIndex = i === current ? '1' : '0';
                    });
                    indicator.style.transform = `translateX(${current * 100}%)`;
                    buttons.forEach((btn, i) => {
                        btn.classList.toggle('text-brand-800', i === current);
                        btn.classList.toggle('text-gray-400', i !== current);
                    });
                    if (tabs[current] === 'stamp') {
                        if (!stampLoaded) {
                            loadStampPane(true);
                        } else {
                            animateStampDots();
                        }
                    }
                }

                window.switchVoucherTab = function (name) {
                    const idx = tabs.indexOf(name);
                    if (idx === -1 || idx === current) return;
                    current = idx;
                    paint();
                };

                track.style.transition = 'none';

                let startX = 0;
                let dragging = false;
                viewport.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; dragging = true; }, { passive: true });
                viewport.addEventListener('touchend', (e) => {
                    if (!dragging) return;
                    dragging = false;
                    const diffX = startX - e.changedTouches[0].clientX;
                    if (diffX > 40 && current < tabs.length - 1) { current += 1; paint(); }
                    else if (diffX < -40 && current > 0) { current -= 1; paint(); }
                }, { passive: true });

                paint();
                requestAnimationFrame(() => requestAnimationFrame(() => { track.style.transition = ''; }));
                if (!stampLoaded) loadStampPane(false);
            }

            // ─── Desktop filter tabs ───
            const desktopTabs = ['voucher', 'stamp', 'guide'];
            let desktopCurrent = Math.max(0, desktopTabs.indexOf(requested));
            let desktopStampLoaded = false;

            window.switchDesktopTab = function (name) {
                const idx = desktopTabs.indexOf(name);
                if (idx === -1) return;
                desktopCurrent = idx;

                const root = document.getElementById('vouchers-desktop-root');
                if (root && window.Alpine) {
                    try {
                        const data = Alpine.$data(root);
                        if (data && 'desktopTab' in data) {
                            data.desktopTab = name;
                        }
                    } catch (e) {}
                }

                document.querySelectorAll('.desktop-pane').forEach(pane => {
                    pane.classList.add('hidden');
                    pane.style.display = 'none';
                });
                const target = document.getElementById('desktop-pane-' + name);
                if (target) {
                    target.classList.remove('hidden');
                    target.style.display = 'block';
                }

                if (name === 'stamp' && !desktopStampLoaded) {
                    loadDesktopStampPane();
                } else if (name === 'stamp') {
                    animateDesktopStampDots();
                }
            };

            function loadDesktopStampPane() {
                desktopStampLoaded = true;
                fetch("{{ route('member.stamps.index') }}")
                    .then(res => res.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fragment = doc.getElementById('stamp-tab-content');
                        const target = document.getElementById('desktop-stamp-pane-content');
                        if (fragment && target) {
                            target.innerHTML = fragment.innerHTML;
                            window.bindSheetDialogs(target);
                            updateDesktopStampTeaser(doc);
                            animateDesktopStampDots();
                        } else if (target) {
                            target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-[#3C2415]/60">Failed to load stamp program, please try again.</p>';
                        }
                    })
                    .catch(() => {
                        desktopStampLoaded = false;
                        const target = document.getElementById('desktop-stamp-pane-content');
                        if (target) target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-[#3C2415]/60">Failed to load stamp program, please try again.</p>';
                    });
            }

            function animateDesktopStampDots() {
                const container = document.getElementById('desktop-pane-stamp') || document.getElementById('desktop-stamp-pane-content');
                if (!container) return;
                container.querySelectorAll('.stamp-dot-fill:not(.stamp-pop), .stamp-pop').forEach((dot, idx) => {
                    dot.style.animationDelay = (idx * 0.08) + 's';
                });
            }

            function updateDesktopStampTeaser(docOrEl) {
                const card = document.getElementById('desktop-stamp-teaser-card');
                if (!card) return;
                const data = docOrEl.querySelector('#stamp-program-data');
                if (!data) { card.closest('#desktop-stamp-sidebar-content').innerHTML = ''; return; }
                const nama = data.dataset.namaProgram;
                const cur = data.dataset.current;
                const tgt = data.dataset.target;
                const penuh = data.dataset.penuh === '1';
                document.getElementById('desktop-stamp-teaser-title').textContent = nama;
                document.getElementById('desktop-stamp-teaser-subtitle').textContent = penuh
                    ? 'Card Complete! Redeem your free treat 🎉'
                    : `${cur}/${tgt} stamps collected`;
            }

            function loadStampPane(shouldAnimate = false) {
                stampLoaded = true;
                fetch("{{ route('member.stamps.index') }}")
                    .then((res) => res.text())
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fragment = doc.getElementById('stamp-tab-content');
                        const target = document.getElementById('stamp-pane-content');
                        if (fragment && target) {
                            target.innerHTML = fragment.innerHTML;
                            window.bindSheetDialogs(target);
                            updateStampTeaser(target);
                            if (shouldAnimate) animateStampDots();
                        } else if (target) {
                            target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                        }

                        if (!desktopStampLoaded) {
                            desktopStampLoaded = true;
                            const desktopTarget = document.getElementById('desktop-stamp-pane-content');
                            if (fragment && desktopTarget) {
                                desktopTarget.innerHTML = fragment.innerHTML;
                            }
                            updateDesktopStampTeaser(doc);
                        }
                    })
                    .catch(() => {
                        stampLoaded = false;
                        const target = document.getElementById('stamp-pane-content');
                        if (target) target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                    });
            }

            function animateStampDots() {
                const container = document.getElementById('stamp-pane-content');
                if (!container) return;
                container.querySelectorAll('.stamp-dot-fill:not(.stamp-pop)').forEach((dot) => {
                    dot.classList.add('stamp-pop');
                });
            }

            function updateStampTeaser(paneEl) {
                const card = document.getElementById('stamp-teaser-card');
                if (!card) return;
                const data = paneEl.querySelector('#stamp-program-data');
                if (!data) { card.remove(); return; }
                const nama = data.dataset.namaProgram;
                const cur = data.dataset.current;
                const tgt = data.dataset.target;
                const penuh = data.dataset.penuh === '1';
                document.getElementById('stamp-teaser-title').textContent = nama;
                document.getElementById('stamp-teaser-subtitle').textContent = penuh
                    ? 'Stamp penuh, yuk tukar! 🎉'
                    : `${cur}/${tgt} stamp terkumpul`;
            }

            if (!desktopStampLoaded && document.getElementById('desktop-stamp-sidebar')) {
                loadDesktopStampPane();
            }

            if (requested && desktopTabs.indexOf(requested) > 0) {
                switchDesktopTab(requested);
            }
        })();
    </script>

    {{-- Top-level single confirmation dialog for stamp redeem --}}
    <dialog id="konfirmasi-tukar-stamp" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0 z-50">
        <div class="flex shrink-0 justify-center pb-1 pt-3">
            <span class="h-1 w-10 rounded-full bg-gray-200"></span>
        </div>
        <div class="p-5 pt-2 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                @include('mobile.components.icon-gift', ['class' => 'h-7 w-7'])
            </span>
            <h3 class="mt-3 text-base font-bold text-gray-900">Redeem <span id="konfirmasi-tukar-stamp-nama"></span>?</h3>
            <p class="mt-1 text-xs text-gray-500">Your collected stamps will be redeemed for this artisan treat.</p>

            <form method="POST" action="{{ route('member.stamps.redeem') }}" class="mt-6 pb-2">
                @csrf
                <input type="hidden" name="produk_id" id="konfirmasi-tukar-stamp-produk-id">
                <input type="hidden" name="detail_produk_id" id="konfirmasi-tukar-stamp-detail-id">
                <input type="hidden" name="metode_ambil" id="konfirmasi-tukar-stamp-metode" value="aplikasi">

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="closeSheet('konfirmasi-tukar-stamp')"
                        class="w-full rounded-2xl border border-gray-200 bg-white py-3 text-xs font-bold text-gray-600 hover:bg-gray-50 active:scale-[0.99] cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="w-full rounded-2xl bg-[#5a3c27] py-3 text-xs font-bold text-white shadow-md hover:bg-[#4a3020] active:scale-[0.99] cursor-pointer">
                        Yes, Redeem
                    </button>
                </div>
            </form>
        </div>
    </dialog>

@endsection