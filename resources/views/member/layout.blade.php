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
    </style>
</head>
<body class="h-full overflow-x-hidden bg-gray-50 font-outfit text-gray-800 antialiased flex flex-col">

    <div id="app-card" class="mx-auto flex flex-1 w-full max-w-none 2xl:max-w-6xl flex-col bg-white sm:border-x sm:border-gray-200/80">

        {{-- Flash messages (toast, fixed, auto-dismiss). Dilewati di halaman
             Status Pesanan karena halaman itu sudah punya alert sendiri
             (Alpine, inline di dekat form bukti pembayaran) — biar nggak
             muncul dobel. --}}
        @if ((session('success') || session('error') || $errors->any()) && ! request()->routeIs('member.pesanan.show'))
            <div id="flash-toast"
                 class="toast-flash fixed top-4 left-1/2 z-50 flex w-[calc(100%-2.5rem)] max-w-sm items-start gap-2 rounded-2xl px-4 py-3 text-xs font-semibold shadow-lg border
                 {{ session('success') ? 'bg-brand-50 text-brand-800 border-brand-200' : 'bg-red-50 text-red-800 border-red-200' }}">
                @if (session('success'))
                    <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-brand-800"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-red-600"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @endif
                <span>{{ session('success') ?? session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="flex flex-1 flex-col {{ request()->routeIs('member.profil') ? '' : 'px-5 sm:px-8 md:px-10' }} {{ request()->routeIs('member.beranda') ? '' : 'pt-6' }} {{ request()->routeIs('member.profil') ? 'pb-4' : 'pb-6' }}">
            @yield('content')
        </main>

        {{-- Fore App Style Bottom Navigation --}}
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
        @unless(request()->routeIs('member.profil') || request()->routeIs('member.produk.show') || request()->routeIs('member.checkout.index'))
        <nav class="sticky bottom-0 z-30 w-full bg-white/95 backdrop-blur-md border-t border-gray-200/80 px-4 sm:px-8 py-2">
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
                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeAccount ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $activeAccount ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Account
                </a>

            </div>
        </nav>
        @endunless

        {{-- Login Required Dialog Modal for Guests --}}
        <dialog id="modal-login-required" class="modal-center fixed inset-0 my-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
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
                                @include('member.components.icon-cup', ['class' => 'h-7 w-7'])
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
                    <div id="tk-bundle-section" class="mt-4 hidden rounded-2xl bg-amber-50/70 p-4">
                        <p class="text-xs font-extrabold text-gray-900">Lengkapi Pesanan, Hemat Lebih Banyak!</p>

                        <div class="mt-3 flex items-center gap-3 rounded-2xl bg-white p-2.5 shadow-sm">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-50">
                                <img id="tk-saran-foto" src="" alt="" class="hidden h-full w-full object-cover">
                                <div id="tk-saran-foto-fallback"
                                    class="flex h-full w-full items-center justify-center text-brand-200">
                                    @include('member.components.icon-cup', ['class' => 'h-6 w-6'])
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p id="tk-saran-nama" class="truncate text-xs font-bold text-gray-900"></p>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span id="tk-saran-harga-final" class="text-xs font-extrabold text-brand-700"></span>
                                    <span id="tk-saran-harga-asli"
                                        class="hidden text-[11px] text-gray-400 line-through"></span>
                                </div>
                            </div>
                            <button type="button" id="tk-saran-tambah" onclick="tambahSaranSilang()"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                aria-label="Tambah ke keranjang">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

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

    <script>
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
                    alert((data.message) || 'Gagal menambahkan ke keranjang.');
                    return;
                }

                tampilkanSheetTambahKeranjang(data);
            } catch (e) {
                alert('Terjadi kesalahan, coba lagi.');
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

            const bundleSection = document.getElementById('tk-bundle-section');
            const tombolSaran = document.getElementById('tk-saran-tambah');

            if (data.suggestion) {
                bundleSection.classList.remove('hidden');
                document.getElementById('tk-saran-nama').innerText = data.suggestion.nama;
                document.getElementById('tk-saran-harga-final').innerText =
                    'Rp ' + Number(data.suggestion.harga_final).toLocaleString('id-ID');

                const hargaAsliEl = document.getElementById('tk-saran-harga-asli');
                if (data.suggestion.ada_promo) {
                    hargaAsliEl.innerText = 'Rp ' + Number(data.suggestion.harga_asli).toLocaleString('id-ID');
                    hargaAsliEl.classList.remove('hidden');
                } else {
                    hargaAsliEl.classList.add('hidden');
                }

                const saranFoto = document.getElementById('tk-saran-foto');
                const saranFotoFallback = document.getElementById('tk-saran-foto-fallback');
                if (data.suggestion.foto_url) {
                    saranFoto.src = data.suggestion.foto_url;
                    saranFoto.classList.remove('hidden');
                    saranFotoFallback.classList.add('hidden');
                } else {
                    saranFoto.classList.add('hidden');
                    saranFotoFallback.classList.remove('hidden');
                }

                tombolSaran.dataset.addUrl = data.suggestion.add_url;
                tombolSaran.disabled = false;
                tombolSaran.classList.remove('bg-gray-300');
                tombolSaran.classList.add('bg-brand-800');
                tombolSaran.innerHTML = '<svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';
            } else {
                bundleSection.classList.add('hidden');
            }

            openSheet('sheet-tambah-keranjang');
        }

        async function tambahSaranSilang() {
            const btn = document.getElementById('tk-saran-tambah');
            const url = btn.dataset.addUrl;
            if (!url || btn.disabled) return;
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: new URLSearchParams({ jumlah: 1 }),
                });

                const data = await res.json();

                if (!data.success) {
                    alert((data.message) || 'Gagal menambahkan ke keranjang.');
                    btn.disabled = false;
                    return;
                }

                __tkCheckoutUrl = data.checkout_url;
                __tkMenuUrl = data.menu_url;

                btn.classList.remove('bg-brand-800');
                btn.classList.add('bg-gray-300');
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            } catch (e) {
                btn.disabled = false;
            }
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
            document.getElementById('login-required-msg').innerText = 'Silakan masuk atau daftar akun Saisho untuk mengakses fitur ' + namaFitur + '.';
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

        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('app-card');
            if (card) {
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

            // Auto-dismiss flash toast
            const toast = document.getElementById('flash-toast');
            if (toast) {
                setTimeout(() => {
                    toast.classList.add('toast-hide');
                    toast.addEventListener('animationend', () => toast.remove(), { once: true });
                }, 3000);
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