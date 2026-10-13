<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Subtle Smooth Page Animations */
        @keyframes pageEnterFromRight {
            0% {
                opacity: 0;
                transform: translateX(20px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pageEnterFromLeft {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .page-enter-right {
            animation: pageEnterFromRight 0.22s cubic-bezier(0.16, 1, 0.3, 1) ease-out forwards;
        }

        .page-enter-left {
            animation: pageEnterFromLeft 0.22s cubic-bezier(0.16, 1, 0.3, 1) ease-out forwards;
        }

        @keyframes appSplashIn {
            0% {
                opacity: 0;
                transform: translateY(14px) scale(0.97);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .app-splash-in {
            animation: appSplashIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Desktop Layout - Fullscreen 2 Column */
        @media (min-width: 768px) {
            #app-card {
                display: grid;
                grid-template-columns: 1fr 1fr;
                align-items: stretch;
                min-height: 100vh;
                height: 100vh;
                max-width: 100% !important;
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body class="h-full font-outfit antialiased overflow-x-hidden md:min-h-screen md:h-screen md:w-full">
    <div id="app-card"
        class="mx-auto flex min-h-screen w-full flex-col bg-white px-6 sm:px-10 pb-8 pt-6 shadow-2xl transition-all duration-300 md:grid md:grid-cols-2 md:min-h-screen md:h-screen md:px-0 md:py-0 md:shadow-none">

        <!-- Left Column: Desktop Hero Visual Banner -->
        <div class="hidden md:flex relative h-full w-full overflow-hidden bg-[#5a3c27]">
            <img src="{{ asset('images/welcome-slide-1.png') }}" alt="Saisho Bakery"
                class="absolute inset-0 w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-t from-[#2d1a0e] via-[#5a3c27]/70 to-black/30"></div>
            
            <div class="relative z-10 flex flex-col justify-between p-12 text-white h-full">
                <div>
                </div>
                <div class="space-y-2">
                    <h2 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-md">
                        Selamat Datang Kembali!
                    </h2>
                    <p class="text-sm text-white/90 max-w-md leading-relaxed drop-shadow">
                        Masuk untuk mengakses poin reward, promo eksklusif member, dan penawaran menarik dari Saisho.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="w-full flex flex-col justify-between md:h-full md:justify-center md:px-12 lg:px-20 py-8 bg-white flex-1">
            <div>
                {{-- Header Bar --}}
                <div class="relative flex items-center justify-center py-2 mb-4 md:mb-6">
                    <a href="{{ route('member.welcome') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                        class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </a>
                    <h1 class="text-base md:text-lg font-bold text-brand-800">Login</h1>
                </div>

                {{-- Main Content --}}
                <div class="mt-6 md:mt-4 max-w-md mx-auto w-full">
                    @php
                        $isAdmin = ($mode ?? 'member') === 'admin';
                        $formAction = $isAdmin ? route('admin.login.store') : route('member.login.store');
                    @endphp
                    @unless ($isAdmin)
                        {{-- Banner Info Password Nomor HP --}}
                        <div class="mb-5">
                            <button type="button" onclick="bukaModalInfoLogin()"
                                class="w-full flex items-center justify-between gap-3 p-3.5 rounded-2xl bg-amber-50/90 border border-amber-200/80 text-amber-950 text-left hover:bg-amber-100/70 transition-all shadow-sm group">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-amber-200/90 text-amber-800 font-bold">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate">Daftar lewat Google?</p>
                                        <p class="text-[11px] text-gray-600 truncate">Klik untuk melihat informasi</p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold text-brand-800 group-hover:underline shrink-0 flex items-center gap-0.5">
                                    Info
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    @endunless

                    <form method="POST" action="{{ $formAction }}" class="space-y-6">
                        @csrf

                        <div>
                            {{-- Satu kolom: username atau no. HP (admin: username atau email) --}}
                            <div x-data="{ identifier: @js(old('identifier', '')) }"
                                class="relative flex items-center border-b-2 border-brand-800 pb-2 pt-1 focus-within:border-brand-800">
                                <input type="text" name="identifier" x-model="identifier" required autofocus
                                    autocomplete="username" autocapitalize="none" spellcheck="false"
                                    placeholder="{{ $isAdmin ? 'Masukkan username atau email' : 'Masukkan username atau no. HP' }}"
                                    class="flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                                <button type="button" x-show="identifier" x-cloak @click="identifier = ''"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Single Inline Alert Message --}}
                            @if (session('error') || $errors->any())
                                <div class="mt-2.5 flex items-center gap-1.5 text-xs text-red-500 font-medium">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0">
                                        <path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>{{ session('error') ?? $errors->first() }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Password Field --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password"
                                    class="block text-xs font-semibold uppercase tracking-wider text-gray-500">Kata
                                    Sandi</label>
                                @unless ($isAdmin)
                                    <button type="button" onclick="bukaModalInfoLogin()"
                                        class="text-[11px] font-semibold text-brand-800 hover:text-brand-900 hover:underline flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        Info Kata Sandi
                                    </button>
                                @endunless
                            </div>
                            <div class="relative">
                                <input id="password" name="password" type="password" required
                                    placeholder="Masukkan kata sandi"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 pr-12 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                <button type="button" onclick="togglePasswordVisibility('password', this)"
                                    aria-label="Tampilkan kata sandi"
                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                                    <svg class="eye-icon-show h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="currentColor"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" />
                                    </svg>
                                    <svg class="eye-icon-hide hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.36 5.16A11 11 0 0 1 12 5c7 0 11 7 11 7a13.2 13.2 0 0 1-3.22 3.9M6.5 6.64C3.68 8.36 2 11 2 11a13.2 13.2 0 0 0 3.22 3.9A11 11 0 0 0 12 17c.86 0 1.68-.1 2.45-.28"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                            <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600 cursor-pointer">
                                <input type="checkbox" name="ingat_saya"
                                    class="rounded border-gray-300 text-brand-800 focus:ring-brand-800">
                                Ingat saya
                            </label>
                            @unless ($isAdmin)
                                <a href="{{ route('member.register') }}"
                                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                    class="text-xs font-bold text-brand-800 hover:underline">Belum punya akun? Daftar</a>
                            @endunless
                        </div>

                        {{-- Continue Button --}}
                        <button type="submit"
                            class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99]">
                            Continue
                        </button>
                    </form>

                    @if (($mode ?? null) !== 'admin')
                        @include('mobile.auth._google-button')
                    @endif
                </div>
            </div>

            {{-- Bottom Terms Notice --}}
            <p class="text-center text-xs leading-relaxed text-gray-500 mt-6 max-w-md mx-auto w-full">
                By signing in to Saisho app, you agree to the <a
                    href="{{ route('member.konten.show', 'terms-conditions') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                    class="font-bold text-brand-800 hover:underline">Terms & Condition</a> and <a
                    href="{{ route('member.konten.show', 'privacy-policy') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                    class="font-bold text-brand-800 hover:underline">Privacy Policy</a>
            </p>
        </div>

    </div>

    @unless ($isAdmin)
        {{-- Modal Pop-up Informasi Nomor HP Sebagai Kata Sandi --}}
        <div id="modal-info-login"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-200 hidden opacity-0"
            aria-labelledby="modal-info-title" role="dialog" aria-modal="true" onclick="if(event.target === this) tutupModalInfoLogin()">
            <div class="relative w-full max-w-sm transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-2xl transition-all scale-95 duration-200" id="modal-info-card">
                
                {{-- Tombol Close --}}
                <button type="button" onclick="tutupModalInfoLogin()"
                    class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                {{-- Header Icon & Title --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-inner">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2" />
                            <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="2" />
                            <circle cx="12" cy="16" r="1.5" fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">Info Akun & Sandi</span>
                        <h2 id="modal-info-title" class="text-base font-bold text-gray-900 mt-1">Nomor HP Sebagai Password</h2>
                    </div>
                </div>

                {{-- Isi Pesan --}}
                <div class="space-y-3.5 text-xs text-gray-600 leading-relaxed">
                    <div class="rounded-2xl bg-amber-50/80 border border-amber-200/70 p-3.5 text-amber-950">
                        <p class="font-semibold text-xs text-amber-900 mb-1">📌 Bagi Pengguna Akun Google:</p>
                        <p class="text-[11px] leading-relaxed">
                            Jika kamu mendaftar melalui <strong class="text-gray-900 font-bold">Google</strong>, kata sandi akunmu otomatis diatur menggunakan <strong class="text-gray-900 font-bold">Nomor HP</strong> yang kamu masukkan saat registrasi (format lokal diawali angka 0, contoh: <span class="font-bold font-mono text-brand-800 bg-white px-1.5 py-0.5 rounded border border-amber-200">088xxxxxxxx</span>).
                        </p>
                    </div>

                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-xs">Pilihan Cara Masuk:</p>
                        <div class="flex items-start gap-2.5 text-[11px]">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800 font-bold text-[10px] mt-0.5">1</span>
                            <p><strong class="text-gray-800">Lewat Tombol Google:</strong> Klik tombol <em>"Lanjutkan dengan Google"</em> di bawah untuk login instan tanpa input sandi.</p>
                        </div>
                        <div class="flex items-start gap-2.5 text-[11px]">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800 font-bold text-[10px] mt-0.5">2</span>
                            <p><strong class="text-gray-800">Masuk Manual:</strong> Masukkan Username atau No HP kamu, lalu gunakan nomor HP kamu (<span class="font-mono font-semibold text-gray-900">088...</span>) sebagai kata sandinya.</p>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-400 italic pt-1 border-t border-gray-100">
                        * Kamu dapat memperbarui kata sandi kapan saja di menu Pengaturan Akun setelah login.
                    </p>
                </div>

                {{-- Checkbox Jangan Tampilkan Lagi & Tombol --}}
                <div class="mt-5 pt-3 border-t border-gray-100 flex flex-col gap-3">
                    <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer select-none">
                        <input type="checkbox" id="cb-jangan-tampilkan" onchange="handleCheckboxToggle(this.checked)" class="rounded border-gray-300 text-brand-800 focus:ring-brand-800">
                        <span id="label-jangan-tampilkan">Jangan tampilkan pop-up ini lagi secara otomatis</span>
                    </label>
                    <button type="button" onclick="tutupModalInfoLogin()"
                        class="w-full rounded-2xl bg-brand-800 py-3 text-center text-sm font-semibold text-white shadow-md hover:bg-brand-900 active:scale-[0.99] transition">
                        Saya Mengerti
                    </button>
                </div>

            </div>
        </div>
    @endunless

    <script>
        const DISMISS_KEY = 'saisho_login_pw_info_dismissed';

        function getDismissedStatus() {
            try {
                if (localStorage.getItem(DISMISS_KEY) === '1') return true;
            } catch (e) {}

            try {
                return document.cookie.split(';').some(c => c.trim().startsWith(DISMISS_KEY + '=1'));
            } catch (e) {}

            return false;
        }

        function setDismissedStatus(val) {
            try {
                if (val) {
                    localStorage.setItem(DISMISS_KEY, '1');
                } else {
                    localStorage.removeItem(DISMISS_KEY);
                }
            } catch (e) {}

            try {
                if (val) {
                    document.cookie = DISMISS_KEY + '=1; path=/; max-age=' + (365 * 24 * 60 * 60) + '; SameSite=Lax';
                } else {
                    document.cookie = DISMISS_KEY + '=; path=/; max-age=0; SameSite=Lax';
                }
            } catch (e) {}
        }

        function handleCheckboxToggle(isChecked) {
            setDismissedStatus(isChecked);
            const label = document.getElementById('label-jangan-tampilkan');
            if (label) {
                label.textContent = isChecked
                    ? 'Jangan tampilkan lagi'
                    : 'Jangan tampilkan lagi';
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('.eye-icon-show').classList.toggle('hidden', !showing);
            btn.querySelector('.eye-icon-hide').classList.toggle('hidden', showing);
            btn.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        }

        function bukaModalInfoLogin() {
            const modal = document.getElementById('modal-info-login');
            const card = document.getElementById('modal-info-card');
            const cb = document.getElementById('cb-jangan-tampilkan');
            if (!modal) return;

            if (cb) {
                cb.checked = getDismissedStatus();
                const label = document.getElementById('label-jangan-tampilkan');
                if (label) {
                    label.textContent = cb.checked
                        ? 'Jangan tampilkan lagi'
                        : 'Jangan tampilkan lagi';
                }
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if (card) {
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }
            }, 10);
        }

        function tutupModalInfoLogin() {
            const modal = document.getElementById('modal-info-login');
            const card = document.getElementById('modal-info-card');
            const cb = document.getElementById('cb-jangan-tampilkan');

            if (cb && cb.checked) {
                setDismissedStatus(true);
            }

            if (!modal) return;
            modal.classList.add('opacity-0');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

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

            // Auto-show pop-up HANYA JIKA belum di-dismiss oleh pengguna
            const urlParams = new URLSearchParams(window.location.search);
            const forceShow = urlParams.get('buka_info') === '1';
            const dismissed = getDismissedStatus();

            if (forceShow || (!dismissed && !sessionStorage.getItem('saisho_login_modal_shown_session'))) {
                sessionStorage.setItem('saisho_login_modal_shown_session', '1');
                setTimeout(bukaModalInfoLogin, 350);
            }
        });

        function animateNavigate(url, dir) {
            const card = document.getElementById('app-card');
            card.style.transition = 'all 0.16s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '0';
            if (dir === 'back') {
                card.style.transform = 'translateX(20px)';
                sessionStorage.setItem('nav_dir', 'back');
            } else {
                card.style.transform = 'translateX(-16px)';
                sessionStorage.setItem('nav_dir', 'forward');
            }
            setTimeout(() => {
                window.location.href = url;
            }, 150);
        }
    </script>
</body>

</html>