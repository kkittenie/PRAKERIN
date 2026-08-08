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
        /* Page-to-page transition (tab switch / navigation) */
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

        /* Bottom-sheet dialogs (e.g. voucher T&C, stamp confirm) slide up + fade,
           and their backdrop fades in behind them. Toggled via [data-open] so we
           can animate the *close* too (native dialog.close() is instant). */
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

        /* Expandable rows (Menu Berlaku / Syarat & Ketentuan / Cara Pakai, etc.) */
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

        /* Stamp dots pop in one-by-one when a filled stamp card is shown/updated. */
        @keyframes stampPop {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.18); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .stamp-filled {
            animation: stampPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        /* The most recently earned stamp pops in a bit bigger and keeps a soft
           glow ring pulsing around it for a couple of beats, so it reads as
           "this one just got filled" instead of blending into the older dots. */
        @keyframes stampPopLatest {
            0% { transform: scale(0); opacity: 0; }
            55% { transform: scale(1.3); opacity: 1; }
            75% { transform: scale(0.95); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes stampGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.55); }
            50% { box-shadow: 0 0 0 7px rgba(255, 255, 255, 0); }
        }
        .stamp-filled-latest {
            animation: stampPopLatest 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both,
                       stampGlow 1.4s ease-out 0.5s 2;
        }

        /* When the punch-card just became full, give the header icon a little
           celebratory wiggle so "stamp penuh" feels rewarding, not just a label change. */
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
    </style>
</head>
<body class="h-full bg-gray-50 font-outfit text-gray-800 antialiased">

    <div id="app-card" class="mx-auto flex min-h-screen max-w-md flex-col bg-white shadow-[0_0_60px_-15px_rgba(90,60,39,0.15)] sm:my-0">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-5 mt-3 flex items-start gap-2 rounded-2xl bg-brand-50 px-4 py-3 text-xs font-semibold text-brand-800 border border-brand-200">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-brand-800"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error') || $errors->any())
            <div class="mx-5 mt-3 flex items-start gap-2 rounded-2xl bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 border border-red-200">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-red-600"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="flex flex-1 flex-col {{ request()->routeIs('member.profil') ? '' : 'px-5' }} {{ request()->routeIs('member.beranda') ? '' : 'pt-6' }} {{ request()->routeIs('member.profil') ? 'pb-4' : 'pb-28' }}">
            @yield('content')
        </main>

        {{-- Fore App Style Bottom Navigation --}}
        @php
            $activeHome = request()->routeIs('member.beranda');
            $activeVoucher = request()->routeIs('member.reward*');
            $activeOrder = request()->routeIs('member.riwayat*');
            $activeAccount = request()->routeIs('member.akun');
            $currentTabIndex = match (true) {
                $activeHome => 0,
                $activeVoucher => 1,
                $activeOrder => 2,
                $activeAccount => 3,
                default => -1,
            };
        @endphp
        @unless(request()->routeIs('member.profil'))
        <nav class="fixed bottom-0 left-1/2 z-30 w-full max-w-md -translate-x-1/2 border-t border-gray-100 bg-white/95 px-3 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur">
            <div class="grid grid-cols-4 gap-1 text-center">
                
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

                {{-- Your Order / Riwayat --}}
                @auth('member')
                    <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 2);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeOrder ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeOrder ? 'bg-brand-50 text-brand-800' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Your Order
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold text-gray-400 hover:text-gray-600">
                        <span class="flex h-7 w-7 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Your Order
                    </a>
                @endauth

                {{-- Account / Akun --}}
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigateTab(this.href, 3);" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeAccount ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeAccount ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $activeAccount ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Account
                </a>

            </div>
        </nav>
        @endunless

        {{-- Login Required Dialog Modal for Guests --}}
        <dialog id="modal-login-required" class="modal-center fixed inset-0 my-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0">
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

    </div>

    <script>
        function bukaModalLoginRequired(namaFitur) {
            document.getElementById('login-required-msg').innerText = 'Silakan masuk atau daftar akun Saisho untuk mengakses fitur ' + namaFitur + '.';
            openSheet('modal-login-required');
        }

        // Animated open/close for any <dialog class="sheet"> or <dialog class="modal-center">.
        // showModal()/close() themselves are instant, so we toggle a [data-open] attribute
        // one frame after showModal() (to animate in) and wait for the transition to finish
        // before actually calling close() (to animate out).
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
            setTimeout(finish, 320); // safety net if no transition property changed
        }

        // Let ESC / Android back-button also animate out instead of closing instantly,
        // and reset any expandable-row chevron that opened this sheet once it's shut.
        // Exposed as window.bindSheetDialogs so pages that inject dialogs later (e.g. the
        // fetched Stamp pane on the Vouchers page) can bind their new ones the same way.
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
        }
        window.bindSheetDialogs = (root = document) => {
            root.querySelectorAll('dialog.sheet, dialog.modal-center').forEach(bindSheetDialog);
        };
        window.bindSheetDialogs();

        // Opens a sheet from an "expandable row" button, highlighting the row
        // (and rotating its chevron via CSS) until the sheet is closed.
        function openExpandableRow(btn, sheetId) {
            btn.classList.add('is-open');
            openSheet(sheetId);
        }

        // Which bottom-nav tab this page currently is (0=Home,1=Vouchers,2=Order,3=Account,-1=none)
        window.__currentTabIndex = {{ $currentTabIndex }};

        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('app-card');
            if (!card) return;

            const alreadyOpened = sessionStorage.getItem('saisho_app_opened');
            if (!alreadyOpened) {
                // True first screen this session -> a slightly richer "app is launching" entrance
                card.classList.add('app-splash-in');
                sessionStorage.setItem('saisho_app_opened', '1');
            } else {
                // Navigating between pages/tabs inside the app -> directional slide + fade
                const dir = sessionStorage.getItem('nav_dir');
                card.classList.add(dir === 'back' ? 'page-enter-left' : 'page-enter-right');
            }
            sessionStorage.removeItem('nav_dir');
        });

        // Generic animated navigation: fades/slides the app card out, then follows the link
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
        }

        function animateNavigateTab(url, targetIndex) {
            if (targetIndex === window.__currentTabIndex) return; 
            const dir = targetIndex > window.__currentTabIndex ? 'forward' : 'back';
            animateNavigate(url, dir);
        }
    </script>
</body>
</html>