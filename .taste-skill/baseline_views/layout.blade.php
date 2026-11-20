<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5a3c27">
    <title>{{ $title ?? 'Member' }} &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
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

        @keyframes appSplashIn {
            0% { opacity: 0; transform: translateY(14px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .app-splash-in {
            animation: appSplashIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .desktop-fade-up, .scroll-fade-up {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .desktop-zoom-in, .scroll-zoom-in {
            opacity: 0;
            transform: scale(0.94) translateY(18px);
            transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .desktop-fade-up.is-visible,
        .scroll-fade-up.is-visible,
        .desktop-zoom-in.is-visible,
        .scroll-zoom-in.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .delay-100 { transition-delay: 100ms; animation-delay: 100ms; }
        .delay-150 { transition-delay: 150ms; animation-delay: 150ms; }
        .delay-200 { transition-delay: 200ms; animation-delay: 200ms; }
        .delay-250 { transition-delay: 250ms; animation-delay: 250ms; }
        .delay-300 { transition-delay: 300ms; animation-delay: 300ms; }
        .delay-350 { transition-delay: 350ms; animation-delay: 350ms; }
        .delay-400 { transition-delay: 400ms; animation-delay: 400ms; }

        dialog.sheet {
            transform: translateY(100%);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1), opacity 0.25s ease;
        }
        dialog.sheet[data-open="true"] {
            transform: translateY(0);
            opacity: 1;
        }
        dialog.sheet::backdrop {
            background: rgba(16, 24, 40, 0);
            transition: background 0.25s ease;
        }
        dialog.sheet[data-open="true"]::backdrop {
            background: rgba(16, 24, 40, .4);
        }

        /* Centered confirmation dialogs pop in with a slight overshoot. */
        dialog.modal-center {
            transform: scale(0.92) translateY(6px);
            opacity: 0;
            transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.18s ease;
        }
        dialog.modal-center[data-open="true"] {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
        dialog.modal-center::backdrop {
            background: rgba(16, 24, 40, 0);
            transition: background 0.2s ease;
        }
        dialog.modal-center[data-open="true"]::backdrop {
            background: rgba(16, 24, 40, .4);
        }

        .expandable-row {
            transition: background-color 0.15s ease;
        }
        .expandable-row:active {
            background-color: #f9fafb;
        }
        .expandable-row.is-open {
            background-color: #faf8f5;
        }
        .expandable-row .row-chevron {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
        }
        .expandable-row.is-open .row-chevron {
            transform: rotate(90deg);
        }

        @keyframes stampPop {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.22); opacity: 1; }
            65% { transform: scale(0.92); }
            80% { transform: scale(1.08); }
            92% { transform: scale(0.98); }
            100% { transform: scale(1); opacity: 1; }
        }
        .stamp-pop {
            animation: stampPop 0.55s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
        }

        @keyframes stampCelebrate {
            0% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.15) rotate(-10deg); }
            50% { transform: scale(1.05) rotate(8deg); }
            75% { transform: scale(1.15) rotate(-6deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        .stamp-card-penuh .stamp-badge-icon {
            animation: stampCelebrate 1s ease-in-out 0.5s 1;
        }

        @keyframes toastIn {
            0% { opacity: 0; transform: translate(-50%, -12px); }
            100% { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes toastOut {
            0% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -12px); }
        }
        .toast-flash {
            animation: toastIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .toast-flash.toast-hide {
            animation: toastOut 0.2s ease forwards;
        }

        .page-sticky-header {
            position: sticky;
            top: 0;
            z-index: 30;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        @media (orientation: landscape) {
            dialog.sheet > div,
            dialog.modal-center > div {
                max-height: 85vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Desktop-only layout overrides (>= 1024px) */
        @media (min-width: 1024px) {
            /* Remove JS-driven max-height on menu scroller to prevent double-scroll */
            [x-ref="menuScroller"] {
                max-height: none !important;
                overflow: visible !important;
            }

            /* Transform expandable bottom sheets (dialog.sheet) into clean centered modals on desktop */
            dialog.sheet {
                inset: 0 !important;
                margin: auto !important;
                max-width: 520px !important;
                max-height: 85vh !important;
                width: calc(100% - 2rem) !important;
                height: fit-content !important;
                border-radius: 2rem !important;
                transform: scale(0.94) !important;
                opacity: 0;
                transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.18s ease;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
                overflow: hidden !important;
            }
            dialog.sheet[data-open="true"] {
                transform: scale(1) !important;
                opacity: 1;
            }
            /* Hide mobile-only checkout header bar on desktop since top desktop navbar is present */
            .checkout-sticky-header {
                display: none !important;
            }

            /* Hide bottom sheet drag handle on desktop modals */
            dialog.sheet > div > div.flex.justify-center,
            dialog.sheet .sheet-handle,
            dialog.sheet .drag-handle {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-full overflow-x-hidden bg-gray-50 font-outfit text-gray-800 antialiased flex flex-col">

    <div id="app-card" class="mx-auto flex flex-1 w-full max-w-none 2xl:max-w-6xl flex-col bg-white sm:border-x sm:border-gray-200/80 lg:max-w-none lg:border-x-0">

        @php
            $activeHome = request()->routeIs('member.beranda');
            $activeVoucher = request()->routeIs('member.reward*');
            $activeMenu = request()->routeIs('member.menu') || request()->routeIs('member.bundling.show');
            $activeOrder = request()->routeIs('member.riwayat*');
            $activeAccount = request()->routeIs('member.akun');
            $currentTabIndex = match (true) {
                $activeHome => 0,
                $activeVoucher => 1,
                $activeMenu => 2,
                $activeOrder => 3,
                $activeAccount => 4,
                default => -1,
            };
        @endphp

        {{-- Desktop Navigation Header (Active only on lg: screens >= 1024px) --}}
        <header class="hidden lg:flex relative sticky top-0 z-40 w-full items-center justify-between border-b border-gray-200/80 bg-white/95 px-8 lg:px-12 py-3.5 backdrop-blur-md">
            <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Saisho Logo" class="h-8 w-auto">
                <span class="text-lg font-extrabold text-brand-800 tracking-tight">Saisho</span>
            </a>

            {{-- Centered Navbar Menu Links --}}
            <nav class="absolute left-1/2 -translate-x-1/2 flex items-center gap-1.5">
                <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 0);" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $activeHome ? 'bg-brand-50 text-brand-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg viewBox="0 0 24 24" fill="{{ $activeHome ? 'currentColor' : 'none' }}" class="h-4 w-4"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Beranda
                </a>
                @auth('member')
                    <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 1);" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $activeVoucher ? 'bg-brand-50 text-brand-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Vouchers
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition text-gray-600 hover:bg-gray-100 hover:text-gray-900">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Vouchers
                    </a>
                @endauth
                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $activeMenu ? 'bg-brand-50 text-brand-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Pesan
                </a>
                @auth('member')
                    <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 3);" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition {{ $activeOrder ? 'bg-brand-50 text-brand-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Activity
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition text-gray-600 hover:bg-gray-100 hover:text-gray-900">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Activity
                    </a>
                @endauth
            </nav>

            <div class="flex items-center gap-3">
                @php
                    $headerCartItems = \App\Support\MemberCart::items();
                    $headerCartJumlah = collect($headerCartItems)->sum('jumlah');
                    $headerCartTotal = collect($headerCartItems)->sum(fn($item) => $item['harga_satuan'] * $item['jumlah']);
                @endphp

                @if ($headerCartJumlah > 0 && !request()->routeIs('member.checkout*'))
                    <a href="{{ route('member.checkout.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                       class="flex items-center gap-2.5 rounded-2xl bg-brand-800 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-brand-900 transition active:scale-95">
                        <span class="relative flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-orange-500 px-1 text-[9px] font-extrabold text-white">
                                {{ $headerCartJumlah }}
                            </span>
                        </span>
                        <span>Rp {{ number_format($headerCartTotal, 0, ',', '.') }}</span>
                    </a>
                @endif

                @auth('member')
                    @php
                        $authMember = auth('member')->user();
                        $memberNotifikasis = \App\Support\NotificationHelper::getNotifikasiMember($authMember->id, 10);
                        $unreadNotifCount = \App\Support\NotificationHelper::hitungBelumDibacaMember($authMember->id);
                    @endphp

                    {{-- Notification Bell Dropdown Button --}}
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                                class="relative flex h-9 w-9 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-600 hover:border-brand-300 hover:text-brand-800 transition shadow-sm">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @if ($unreadNotifCount > 0)
                                <span id="navbar-notif-badge" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-extrabold text-white animate-pulse">
                                    {{ $unreadNotifCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2.5 w-80 sm:w-96 rounded-3xl border border-gray-100 bg-white p-4 shadow-xl z-50 text-left"
                             style="display: none;">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold text-gray-900">Notifikasi</span>
                                    @if($unreadNotifCount > 0)
                                        <span id="navbar-notif-count-text" class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-800">{{ $unreadNotifCount }} Baru</span>
                                    @endif
                                </div>
                                @if($unreadNotifCount > 0)
                                    <button type="button" onclick="tandaiSemuaDibacaNavbar()" class="text-[11px] font-bold text-brand-800 hover:underline">
                                        Tandai dibaca
                                    </button>
                                @endif
                            </div>

                            <div class="mt-3 max-h-80 overflow-y-auto space-y-2">
                                @forelse ($memberNotifikasis as $notif)
                                    @php
                                        $targetUrl = $notif->url ?: route('member.notifikasi.index');
                                    @endphp
                                    <a href="{{ $targetUrl }}"
                                       onclick="event.preventDefault(); tandaiNotifDibacaNavbar({{ $notif->id }}, '{{ $targetUrl }}');"
                                       class="flex items-start gap-3 rounded-2xl p-3 transition {{ ! $notif->is_read ? 'bg-brand-50/60 hover:bg-brand-50 border border-brand-100' : 'bg-gray-50/60 hover:bg-gray-100' }}">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $notif->icon_type === 'success' ? 'bg-emerald-100 text-emerald-700' : ($notif->type === 'stamp' ? 'bg-brand-100 text-brand-800' : ($notif->type === 'pesanan' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700')) }}">
                                            @if ($notif->type === 'stamp')
                                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @elseif ($notif->type === 'pesanan')
                                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4M3 6h18M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <p class="truncate text-xs font-bold text-gray-900">{{ $notif->title }}</p>
                                                @unless($notif->is_read)
                                                    <span class="h-2 w-2 shrink-0 rounded-full bg-brand-800"></span>
                                                @endunless
                                            </div>
                                            <p class="mt-0.5 text-[11px] leading-snug text-gray-600 line-clamp-2">{{ $notif->desc }}</p>
                                            <span class="mt-1 block text-[10px] text-gray-400 font-medium">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="py-6 text-center">
                                        <p class="text-xs font-semibold text-gray-500">Belum ada notifikasi</p>
                                        <p class="mt-1 text-[11px] text-gray-400">Pembaruan pesanan & promo akan muncul di sini.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3 border-t border-gray-100 pt-2.5 text-center">
                                <a href="{{ route('member.notifikasi.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="text-xs font-bold text-brand-800 hover:text-brand-900 transition">
                                    Lihat Semua Notifikasi &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 4);" class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-bold text-gray-800 hover:border-brand-300 hover:text-brand-800 transition">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-50 text-brand-800">
                            @if ($authMember->foto)
                                <img src="{{ asset('storage/' . $authMember->foto) }}" alt="{{ $authMember->nama }}" class="h-full w-full object-cover">
                            @else
                                <svg viewBox="0 0 24 24" fill="{{ $activeAccount ? 'currentColor' : 'none' }}" class="h-3.5 w-3.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </span>
                        <span>{{ explode(' ', $authMember->nama)[0] }}</span>
                    </a>
                @else
                    <a href="{{ route('member.login') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="rounded-xl bg-brand-800 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-900 transition">
                        Masuk / Daftar
                    </a>
                @endauth
            </div>
        </header>

        {{-- Flash messages (toast, fixed, auto-dismiss). Dilewati di halaman
             Status Pesanan karena halaman itu sudah punya alert sendiri
             (Alpine, inline di dekat form bukti pembayaran) — biar nggak
             muncul dobel. --}}
        @if ((session('success') || session('error') || $errors->any()) && ! request()->routeIs('member.pesanan.show'))
            <div id="flash-toast"
                 class="toast-flash fixed top-4 left-1/2 z-50 flex w-[calc(100%-2.5rem)] max-w-md items-center justify-between gap-3 rounded-2xl px-4 py-3 text-xs font-semibold shadow-xl border
                 {{ session('success') ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-red-50 text-red-900 border-red-200' }}">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if (session('success'))
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-emerald-600"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-red-600"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @endif
                    <span class="truncate">{{ session('success') ?? session('error') ?? $errors->first() }}</span>
                </div>
                <button type="button" onclick="closeFlashToast()" class="shrink-0 text-gray-400 hover:text-gray-700 p-0.5">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="flex flex-1 flex-col {{ request()->routeIs('member.profil') ? '' : 'px-5 sm:px-8 md:px-10' }} {{ request()->routeIs('member.beranda') ? '' : 'pt-6' }} {{ request()->routeIs('member.profil') ? 'pb-4' : 'pb-6' }} {{ request()->routeIs('member.beranda') ? 'lg:w-full lg:max-w-none lg:px-0 lg:pt-0 lg:pb-0' : 'lg:mx-auto lg:w-full lg:max-w-7xl lg:px-12' }} {{ request()->routeIs('member.profil') ? 'lg:pt-6 lg:pb-12' : (request()->routeIs('member.beranda') ? '' : 'lg:pb-12') }}">
            @yield('content')
        </main>

        {{-- Fore App Style Bottom Navigation (Mobile Only) --}}
        @unless(request()->routeIs('member.profil') || request()->routeIs('member.produk.show') || request()->routeIs('member.checkout.index'))
        <nav class="sticky bottom-0 z-30 w-full bg-white/95 backdrop-blur-md border-t border-gray-200/80 px-4 sm:px-8 py-2 lg:hidden">
            <div class="grid grid-cols-5 items-end gap-1 text-center">

                {{-- Home / Beranda --}}
                <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 0);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeHome ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeHome ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $activeHome ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Home
                </a>

                {{-- Vouchers / Reward --}}
                @auth('member')
                    <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 1);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeVoucher ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeVoucher ? 'bg-brand-50 text-brand-800' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Vouchers
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold text-gray-400 hover:text-gray-600">
                        <span class="flex h-7 w-7 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Vouchers
                    </a>
                @endauth

                {{-- Pesan / Pick Up (highlighted, center) --}}
                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);" class="relative flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeMenu ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $activeMenu ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Pesan
                </a>

                {{-- Your Order / Riwayat --}}
                @auth('member')
                    <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 3);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeOrder ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeOrder ? 'bg-brand-50 text-brand-800' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Activity
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold text-gray-400 hover:text-gray-600">
                        <span class="flex h-7 w-7 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Activity
                    </a>
                @endauth

                {{-- Account / Akun --}}
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 4);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeAccount ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full {{ $activeAccount ? 'bg-brand-50 text-brand-800' : '' }}">
                        @if (auth('member')->check() && auth('member')->user()->foto)
                            <img src="{{ asset('storage/' . auth('member')->user()->foto) }}" alt="{{ auth('member')->user()->nama }}" class="h-full w-full object-cover">
                        @else
                            <svg viewBox="0 0 24 24" fill="{{ $activeAccount ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @endif
                    </span>
                    Account
                </a>

            </div>
        </nav>
        @endunless

        {{-- Desktop Footer (Visible ONLY on lg: screens >= 1024px) --}}
        <footer class="hidden lg:block border-t border-stone-800/80 bg-[#1c130d] text-stone-300 mt-auto w-full">
            <div class="mx-auto max-w-7xl px-8 lg:px-12 py-12">
                <div class="grid grid-cols-12 gap-8 lg:gap-12">
                    
                    {{-- Column 1: Brand & Contact Info (5 cols) --}}
                    <div class="col-span-12 lg:col-span-5 space-y-4">
                        <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="inline-flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 p-2 backdrop-blur-sm border border-white/10">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Saisho Logo" class="h-6 w-auto">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl font-black text-white tracking-tight">Saisho</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-400">Bakery</span>
                            </div>
                        </a>
                        <p class="text-xs leading-relaxed text-stone-400 max-w-sm">
                            Menyajikan kelezatan roti, kue artisan, dan kopi berkualitas tinggi segar setiap hari untuk menemani setiap momen istimewa Anda.
                        </p>
                        
                        <!-- <div class="pt-2 space-y-2">
                            <div class="flex items-center gap-2.5 text-xs text-stone-300">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white/5 text-brand-400 border border-white/5">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><path d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span>halo@saishobakery.com</span>
                            </div>
                        </div> -->
                    </div>

                    {{-- Column 2: Quick Links (2 cols) --}}
                    <div class="col-span-12 sm:col-span-4 lg:col-span-2 space-y-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Jelajahi</h4>
                        <ul class="space-y-2 text-xs">
                            <li>
                                <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 0);" class="hover:text-brand-400 transition">Beranda</a>
                            </li>
                            <li>
                                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);" class="hover:text-brand-400 transition">Menu & Order</a>
                            </li>
                            @auth('member')
                                <li>
                                    <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 1);" class="hover:text-brand-400 transition">Voucher & Rewards</a>
                                </li>
                                <li>
                                    <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 3);" class="hover:text-brand-400 transition">Aktivitas Pesanan</a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="hover:text-brand-400 transition">Voucher & Rewards</a>
                                </li>
                                <li>
                                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="hover:text-brand-400 transition">Aktivitas Pesanan</a>
                                </li>
                            @endauth
                        </ul>
                    </div>

                    {{-- Column 3: Information & Static Content (2 cols) --}}
                    <div class="col-span-12 sm:col-span-4 lg:col-span-2 space-y-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Informasi</h4>
                        <ul class="space-y-2 text-xs">
                            <li>
                                <a href="{{ route('member.konten.show', 'terms-conditions') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="hover:text-brand-400 transition">Syarat & Ketentuan</a>
                            </li>
                            <li>
                                <a href="{{ route('member.konten.show', 'privacy-policy') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="hover:text-brand-400 transition">Kebijakan Privasi</a>
                            </li>
                        </ul>
                    </div>

                    {{-- Column 4: Perks & Socials (3 cols) --}}
                    <div class="col-span-12 sm:col-span-4 lg:col-span-3 space-y-4">
                        <div>
                            <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-stone-400 mb-2">Ikuti Kami</h4>
                            <div class="flex items-center gap-2">
                                <a href="https://www.instagram.com/saishobakery?igsh=bTcyMDNibmNleGIz" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 text-stone-300 hover:bg-brand-400 hover:text-stone-900 transition border border-white/5">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Bottom Bar --}}
                <div class="mt-12 border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-[11px] text-stone-400">
                    <p>&copy; {{ date('Y') }} Saisho Cake & Bakery. All rights reserved.</p>
                    <p class="text-stone-400/80">Freshly baked & brewed daily with passion.</p>
                </div>
            </div>
        </footer>

        {{-- Login Required Dialog Modal for Guests --}}
        <dialog id="modal-login-required" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
            <div class="p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-800">
                    <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 15v2m0 4h.01M5 11h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Zm2 0V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-gray-900">Login Diperlukan</h3>
                <p id="login-required-msg" class="mt-2 text-xs leading-relaxed text-gray-500">Silakan masuk atau daftar akun Saisho untuk mengakses fitur ini.</p>
                <div class="mt-6 flex flex-col gap-2">
                    <a href="{{ route('member.login') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="w-full rounded-2xl bg-brand-800 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-900">
                        Login Sekarang
                    </a>
                    <button type="button" onclick="closeSheet('modal-login-required')" class="py-2 text-xs font-semibold text-gray-500 hover:text-gray-700">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </dialog>

        {{-- Sheet konfirmasi "Ditambahkan ke Keranjang" (fore-app style) --}}
        <dialog id="sheet-tambah-keranjang"
            class="sheet fixed inset-x-0 bottom-0 top-auto m-0 mx-auto max-h-[88vh] w-full max-w-md rounded-t-3xl border-0 p-0 shadow-2xl sm:max-w-xl md:max-w-2xl">

            <div class="flex max-h-[88vh] flex-col">

                {{-- Handle --}}
                <div class="flex shrink-0 justify-center pb-1 pt-3">
                    <span class="h-1 w-10 rounded-full bg-gray-200"></span>
                </div>

                <div class="overflow-y-auto px-5 pb-2 pt-2">

                    <h3 class="text-center text-sm font-extrabold text-brand-700">
                        Pilihan Menarik Lainnya
                    </h3>

                    {{-- Kartu item yang baru ditambahkan --}}
                    <div class="mt-4 flex items-center gap-3 rounded-2xl border border-gray-100 p-3">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-gray-50">
                            <img id="tk-item-foto" src="" alt="" class="hidden h-full w-full object-cover">
                            <div id="tk-item-foto-fallback"
                                class="flex h-full w-full items-center justify-center text-brand-200">
                                @include('mobile.components.icon-cup', ['class' => 'h-7 w-7'])
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p id="tk-item-nama" class="truncate text-sm font-bold text-gray-900"></p>
                                <span id="tk-item-jumlah"
                                    class="flex h-6 min-w-6 shrink-0 items-center justify-center rounded-full border border-gray-200 px-1.5 text-[11px] font-bold text-gray-600"></span>
                            </div>
                            <p id="tk-item-varian" class="mt-0.5 text-xs text-gray-400"></p>
                            <p class="mt-1 flex items-center gap-1 text-xs font-bold text-brand-700">
                                <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                    <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="3"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Berhasil ditambahkan ke keranjang!
                            </p>
                        </div>
                    </div>

                    {{-- Cross-sell / bundle up --}}

                </div>

                {{-- Aksi --}}
                <div class="shrink-0 border-t border-gray-100 px-5 pb-5 pt-3"
                    style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom));">
                    <button type="button" id="tk-btn-lihat-keranjang" onclick="lihatKeranjangDariSheet()"
                        class="w-full rounded-2xl bg-brand-800 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                        Lihat Keranjang
                    </button>
                    <button type="button" onclick="tambahItemLainDariSheet()"
                        class="mt-2 w-full rounded-2xl border border-brand-800 py-3 text-sm font-bold text-brand-800 transition active:scale-[0.99]">
                        Tambah Item Lain
                    </button>
                </div>

            </div>
        </dialog>

    </div>


    {{-- Alert global (Alpine.js). Pengganti alert() bawaan browser.
         Panggil dari JS: appAlert('pesan', 'Judul opsional')
         atau dari Alpine/HTML: $dispatch('app-alert', { message: '...', title: '...' })
         Pakai <dialog> + showModal() supaya tetap muncul di atas bottom sheet yang sedang terbuka. --}}
    <dialog x-data="{ judul: 'Notice', pesan: '' }"
            x-ref="dlg"
            x-on:app-alert.window="judul = $event.detail.title || 'Notice'; pesan = $event.detail.message || ''; if (! $refs.dlg.open) $refs.dlg.showModal()"
            x-on:click.self="$refs.dlg.close()"
            class="m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl border-0 bg-white p-0 shadow-2xl backdrop:bg-black/40">
        <div class="p-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <h3 class="mt-3 text-base font-bold text-gray-900" x-text="judul"></h3>
            <p class="mt-1.5 text-sm leading-relaxed text-gray-500" x-text="pesan"></p>
            <button type="button" x-on:click="$refs.dlg.close()"
                    class="mt-5 w-full rounded-2xl bg-[#5a3c27] py-3 text-xs font-bold text-white shadow-md hover:bg-[#4a3020] active:scale-[0.99] cursor-pointer">
                OK
            </button>
        </div>
    </dialog>

    <script>
        window.appAlert = function (message, title) {
            window.dispatchEvent(new CustomEvent('app-alert', { detail: { message: message, title: title } }));
        };
        window.tampilkanAlert = window.appAlert;
        window.alert = function (message) {
            window.appAlert(message, 'Notice');
        };

        let __tkCheckoutUrl = null;
        let __tkMenuUrl = null;

        async function kirimTambahKeranjang(form) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: new FormData(form),
                });

                if (res.status === 401 || res.status === 419) {
                    window.location.reload();
                    return;
                }

                const data = await res.json();

                if (!data.success) {
                    appAlert((data.message) || 'Failed to add item to cart.');
                    return;
                }

                tampilkanSheetTambahKeranjang(data);
            } catch (e) {
                appAlert('Something went wrong. Please try again.');
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        function tampilkanSheetTambahKeranjang(data) {
            __tkCheckoutUrl = data.checkout_url;
            __tkMenuUrl = data.menu_url;

            document.getElementById('tk-item-nama').innerText = data.item.nama;
            document.getElementById('tk-item-jumlah').innerText = 'x' + data.item.jumlah;

            const varianTxt = data.item.ukuran || '';
            const varianEl = document.getElementById('tk-item-varian');
            varianEl.innerText = varianTxt;
            varianEl.classList.toggle('hidden', !varianTxt);

            const fotoEl = document.getElementById('tk-item-foto');
            const fotoFallback = document.getElementById('tk-item-foto-fallback');
            if (data.item.foto_url) {
                fotoEl.src = data.item.foto_url;
                fotoEl.classList.remove('hidden');
                fotoFallback.classList.add('hidden');
            } else {
                fotoEl.classList.add('hidden');
                fotoFallback.classList.remove('hidden');
            }

            openSheet('sheet-tambah-keranjang');
        }

        function lihatKeranjangDariSheet() {
            closeSheet('sheet-tambah-keranjang');
            if (__tkCheckoutUrl) {
                setTimeout(() => animateNavigate(__tkCheckoutUrl, 'forward'), 150);
            }
        }

        function tambahItemLainDariSheet() {
            closeSheet('sheet-tambah-keranjang');
            if (__tkMenuUrl) {
                setTimeout(() => animateNavigate(__tkMenuUrl, 'back'), 150);
            }
        }

        function bukaModalLoginRequired(namaFitur) {
            document.getElementById('login-required-msg').innerText = 'Please sign in or create a Daisy Bakery account to access ' + namaFitur + '.';
            openSheet('modal-login-required');
        }
        function openSheet(id) {
            const dialog = document.getElementById(id);
            if (!dialog || dialog.open) return;
            dialog.showModal();
            requestAnimationFrame(() => {
                requestAnimationFrame(() => dialog.setAttribute('data-open', 'true'));
            });
        }

        function closeSheet(id) {
            const dialog = document.getElementById(id);
            if (!dialog || !dialog.open) return;
            dialog.removeAttribute('data-open');
            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                dialog.removeEventListener('transitionend', onEnd);
                if (dialog.open) dialog.close();
            };
            const onEnd = (e) => { if (e.target === dialog) finish(); };
            dialog.addEventListener('transitionend', onEnd);
            setTimeout(finish, 320);  
        }

         function bindSheetDialog(dialog) {
            if (dialog.dataset.sheetBound) return;
            dialog.dataset.sheetBound = '1';
            dialog.addEventListener('cancel', (e) => {
                e.preventDefault();
                closeSheet(dialog.id);
            });
            dialog.addEventListener('close', () => {
                dialog.removeAttribute('data-open');
                document.querySelectorAll(`[data-sheet-target="${dialog.id}"]`).forEach((btn) => {
                    btn.classList.remove('is-open');
                });
            });
             dialog.addEventListener('click', (e) => {
                if (!dialog.open) return;
                const kotak = dialog.getBoundingClientRect();
                const diLuarSheet = e.clientX < kotak.left || e.clientX > kotak.right
                    || e.clientY < kotak.top || e.clientY > kotak.bottom;
                if (diLuarSheet) closeSheet(dialog.id);
            });
        }
        window.bindSheetDialogs = (root = document) => {
            root.querySelectorAll('dialog.sheet, dialog.modal-center').forEach(bindSheetDialog);
        };
        window.bindSheetDialogs();

         function openExpandableRow(btn, sheetId) {
            btn.classList.add('is-open');
            openSheet(sheetId);
        }

         window.__currentTabIndex = {{ $currentTabIndex }};

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

            // Auto-dismiss flash toast after 6 seconds
            const toast = document.getElementById('flash-toast');
            if (toast) {
                setTimeout(() => closeFlashToast(), 6000);
            }
        });

        function closeFlashToast() {
            const toast = document.getElementById('flash-toast');
            if (!toast) return;
            toast.classList.add('toast-hide');
            setTimeout(() => { if (toast && toast.parentNode) toast.remove(); }, 250);
        }

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
            setTimeout(() => { window.location.href = url; }, 150);
            setTimeout(() => { restoreAppCard(false); }, 500);
        }

        function animateNavigateTab(url, targetIndex) {
            if (targetIndex === window.__currentTabIndex) return; 
            const dir = targetIndex > window.__currentTabIndex ? 'forward' : 'back';
            animateNavigate(url, dir);
        }

        function clearNavbarNotifBadge() {
            document.querySelectorAll('#navbar-notif-badge, #navbar-notif-count-text, [data-navbar-notif-dot]').forEach(el => el.remove());
        }

        function tandaiNotifDibacaNavbar(id, url) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(`{{ url('mobile/notifikasi') }}/${id}/baca`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            }).catch(() => {});

            if (url) {
                animateNavigate(url, 'forward');
            }
        }

        function tandaiSemuaDibacaNavbar() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            clearNavbarNotifBadge();
            document.querySelectorAll('[data-notif-dot]').forEach((el) => el.remove());
            document.querySelectorAll('[data-notif-item]').forEach((el) => el.classList.remove('bg-brand-50/40', 'border-brand-200', 'bg-brand-50/60'));
            const btn = document.getElementById('btn-baca-semua');
            if (btn) btn.classList.add('invisible');

            fetch("{{ route('member.notifikasi.baca-semua') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            }).catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            const animElements = document.querySelectorAll('.desktop-fade-up, .desktop-zoom-in, .scroll-fade-up, .scroll-zoom-in');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '0px 0px -30px 0px'
                });
                animElements.forEach(el => observer.observe(el));
            } else {
                animElements.forEach(el => el.classList.add('is-visible'));
            }
        });
    </script>
</body>
</html>