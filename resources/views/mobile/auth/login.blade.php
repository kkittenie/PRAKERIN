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
                    <form method="POST" action="{{ route('member.login.store') }}" class="space-y-6"
                        x-data="{ 
                            loginType: '{{ old('login_type', 'no_hp') }}',
                            noHpInput: '{{ old('login_type') === 'no_hp' ? old('identifier') : '' }}',
                            usernameInput: '{{ old('login_type') === 'username' ? old('identifier') : '' }}',
                            syncIdentifier() {
                                this.noHpInput = (this.noHpInput || '').replace(/\D/g, '').replace(/^0+/, '');
                                if (this.loginType === 'no_hp') {
                                    this.$refs.identifier.value = this.noHpInput;
                                } else {
                                    this.$refs.identifier.value = this.usernameInput.trim();
                                }
                            }
                        }"
                        x-init="syncIdentifier()">
                        @csrf
                        <input type="hidden" name="login_type" x-model="loginType">
                        <input type="hidden" name="identifier" x-ref="identifier" value="{{ old('identifier') }}">

                        {{-- Toggle No HP / Username --}}
                        <div class="grid grid-cols-2 gap-2 rounded-2xl bg-gray-100 p-1">
                            <button type="button" @click="loginType = 'no_hp'; $nextTick(() => syncIdentifier());"
                                :class="loginType === 'no_hp' ? 'bg-white shadow-sm text-brand-800' : 'text-gray-500'"
                                class="rounded-xl py-2 text-sm font-semibold transition">No. HP</button>
                            <button type="button" @click="loginType = 'username'; $nextTick(() => syncIdentifier());"
                                :class="loginType === 'username' ? 'bg-white shadow-sm text-brand-800' : 'text-gray-500'"
                                class="rounded-xl py-2 text-sm font-semibold transition">Username</button>
                        </div>

                        <div>
                            {{-- No HP Input --}}
                            <div x-show="loginType === 'no_hp'" x-cloak>
                                <div
                                    class="relative flex items-center border-b-2 border-brand-800 pb-2 pt-1 focus-within:border-brand-800">
                                    <div
                                        class="flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-800">
                                        <span>+62</span>
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-500">
                                            <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <input type="tel" inputmode="numeric" x-model="noHpInput"
                                        :required="loginType === 'no_hp'" :disabled="loginType !== 'no_hp'"
                                        placeholder="882xxxxxxx" @input="syncIdentifier()"
                                        class="ml-3 flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                                    <button type="button" @click="noHpInput = ''; syncIdentifier();"
                                        class="text-gray-400 hover:text-gray-600 p-1">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Username Input --}}
                            <div x-show="loginType === 'username'" x-cloak>
                                <div
                                    class="relative flex items-center border-b-2 border-brand-800 pb-2 pt-1 focus-within:border-brand-800">
                                    <input type="text" x-model="usernameInput"
                                        :required="loginType === 'username'" :disabled="loginType !== 'username'"
                                        placeholder="Username" @input="syncIdentifier()"
                                        class="flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                                </div>
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
                            <label for="password"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kata
                                Sandi</label>
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
                            <a href="{{ route('member.register') }}"
                                onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                class="text-xs font-bold text-brand-800 hover:underline">Belum punya akun? Daftar</a>
                        </div>

                        {{-- Continue Button --}}
                        <button type="submit"
                            class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99]">
                            Continue
                        </button>
                    </form>
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

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('.eye-icon-show').classList.toggle('hidden', !showing);
            btn.querySelector('.eye-icon-hide').classList.toggle('hidden', showing);
            btn.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
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