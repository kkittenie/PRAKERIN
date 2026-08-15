<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Member &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth Step Transition Animations */
        @keyframes pageEnterFromRight {
            0% {
                opacity: 0;
                transform: translateX(24px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pageEnterFromLeft {
            0% {
                opacity: 0;
                transform: translateX(-24px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pageExitToLeft {
            0% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
                transform: translateX(-24px);
            }
        }

        @keyframes pageExitToRight {
            0% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
                transform: translateX(24px);
            }
        }

        .step-enter-right {
            animation: pageEnterFromRight 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .step-enter-left {
            animation: pageEnterFromLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .step-exit-left {
            animation: pageExitToLeft 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .step-exit-right {
            animation: pageExitToRight 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
</head>

<body class="h-full font-outfit antialiased overflow-x-hidden">
    <div id="app-card"
        class="mx-auto flex min-h-screen w-full max-w-none sm:max-w-xl md:max-w-2xl lg:max-w-3xl flex-col bg-white px-6 sm:px-10 pb-8 pt-6 shadow-2xl step-enter-right sm:my-6 sm:min-h-0 sm:rounded-3xl sm:border sm:border-gray-200/80 transition-all duration-300">

        {{-- Single Form Envelope submitting all 4 data fields to server --}}
        <form id="form-register" method="POST" action="{{ route('member.register.store') }}"
            class="flex flex-col flex-1">
            @csrf
            <input type="hidden" id="otp-hidden" name="otp" value="">

            {{-- Flash messages --}}
            @if (session('error') || $errors->any())
                <div class="mb-4 flex items-start gap-2 rounded-xl bg-red-50 p-3.5 text-xs text-red-700">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 mt-0.5">
                        <path
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            @endif

            {{-- ==================== STEP 1: PHONE NUMBER INPUT ==================== --}}
            <div id="step-1" class="flex flex-col flex-1">
                {{-- Header Bar --}}
                <div class="relative flex items-center justify-center py-2 mb-4">
                    <a href="{{ route('member.welcome') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                        class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </a>
                    <h1 class="text-base font-bold text-brand-800">Register Step 1/3</h1>
                </div>

                <div class="mt-4 flex-1">
                    <h2 class="text-lg font-bold text-brand-800">Input Phone Number</h2>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Masukkan nomor HP kamu untuk menerima kode
                        verifikasi OTP.</p>

                    <div class="mt-8 space-y-6">
                        <div>
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
                                <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}"
                                    required autofocus placeholder="882xxxxxxx"
                                    oninput="this.value = this.value.replace(/\D/g, '').replace(/^0+/, '')"
                                    class="ml-3 flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                                <button type="button" onclick="document.getElementById('no_hp').value=''"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            <p id="phone-error-text" class="mt-2 text-xs text-red-500 font-medium hidden"></p>
                        </div>

                        <button type="button" id="btn-step1-next" onclick="prosesKirimOtpStep1()"
                            class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>Kirim Kode OTP</span>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="text-center text-xs leading-relaxed text-gray-500 mt-6">
                    Sudah punya akun member? <a href="{{ route('member.login') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                        class="font-bold text-brand-800 hover:underline">Masuk di sini</a>
                </p>
            </div>


            {{-- ==================== STEP 2: INPUT OTP CODE ==================== --}}
            <div id="step-2" class="flex flex-col flex-1 hidden">
                {{-- Header Bar --}}
                <div class="relative flex items-center justify-center py-2 mb-4">
                    <button type="button" onclick="goBackToStep(1)"
                        class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <h1 class="text-base font-bold text-brand-800">Register Step 2/3</h1>
                </div>

                <div class="mt-4 flex-1 text-center">

                    <h2 class="mt-4 text-xl font-bold text-brand-800">Verifikasi Kode OTP</h2>
                    <p class="mt-1 text-xs text-gray-500 leading-relaxed px-4">
                        Kode OTP 6-digit telah dikirimkan ke nomor <br><strong id="otp-phone-display"
                            class="text-gray-900 font-bold"></strong>
                    </p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <input id="otp-input-code" type="text" maxlength="6" inputmode="numeric"
                                placeholder="123456" autofocus
                                class="w-full text-center tracking-[0.5em] text-2xl font-extrabold rounded-2xl border-2 border-brand-800 py-3.5 text-gray-900 outline-none focus:ring-4 focus:ring-brand-800/10">
                            <p id="otp-error-text" class="mt-2 text-xs text-red-500 font-semibold hidden"></p>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 px-1">
                            <span>Waktu tersisa: <strong id="timer-text"
                                    class="text-brand-800 font-bold">60s</strong></span>
                            <button type="button" id="btn-resend-otp" onclick="prosesKirimOtpStep1()"
                                class="font-bold text-brand-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                Kirim Ulang OTP
                            </button>
                        </div>

                        <button type="button" onclick="verifikasiStep2Otp()"
                            class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>Verifikasi Kode OTP</span>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>


            {{-- ==================== STEP 3: CREATE PROFILE & PASSWORD ==================== --}}
            <div id="step-3" class="flex flex-col flex-1 hidden">
                {{-- Header Bar --}}
                <div class="relative flex items-center justify-center py-2 mb-4">
                    <button type="button" onclick="goBackToStep(2)"
                        class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <h1 class="text-base font-bold text-brand-800">Register Step 3/3</h1>
                </div>

                <div class="mt-2 flex-1">
                    <h2 class="text-lg font-bold text-brand-800">Buat Profil & Kata Sandi</h2>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Lengkapi nama dan buat kata sandi untuk
                        mengamankan akun member Saisho kamu.</p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label for="nama"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Nama
                                Lengkap</label>
                            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required
                                placeholder="Contoh: Budi Santoso"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                            @error('nama')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="username"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Username</label>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" required
                                minlength="3" maxlength="50" placeholder="Contoh: budisantoso"
                                oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                            <p class="mt-1 text-[11px] text-gray-400">Huruf, angka, titik, atau underscore. Tidak boleh sama
                                dengan member lain.</p>
                            @error('username')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Kata
                                Sandi</label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required minlength="6"
                                    placeholder="Minimal 6 karakter"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 pr-12 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
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
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Konfirmasi
                                Kata Sandi</label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    minlength="6" placeholder="Ulangi kata sandi"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 pr-12 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)"
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
                            <p id="password-error-text" class="mt-1 text-xs text-red-500 font-semibold hidden"></p>
                        </div>

                        <button type="button" onclick="submitRegisterStep3()"
                            class="w-full mt-4 rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>Selesaikan Pendaftaran</span>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        let currentStep = 1;
        let countdownTimer = null;
        let currentNoHp = '';

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
            const dir = sessionStorage.getItem('nav_dir');
            if (dir === 'back') {
                card.classList.add('step-enter-left');
            } else {
                card.classList.add('step-enter-right');
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

        // STEP 1: Kirim OTP AJAX
        function prosesKirimOtpStep1() {
            const noHpInput = document.getElementById('no_hp').value.trim();
            const errEl = document.getElementById('phone-error-text');
            const btn = document.getElementById('btn-step1-next');

            errEl.classList.add('hidden');

            if (!noHpInput) {
                errEl.innerText = 'Masukkan nomor HP kamu terlebih dahulu.';
                errEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;

            btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 text-white"
            viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25"
                cx="12" cy="12" r="10"
                stroke="currentColor"
                stroke-width="4">
            </circle>
            <path class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0
                C5.373 0 0 5.373 0 12h4z">
            </path>
        </svg>
        <span>Mengirim OTP...</span>
    `;

            fetch('{{ route("member.otp.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify({
                    no_hp: noHpInput
                })
            })
                .then(async response => {
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message || 'Gagal mengirim OTP.'
                        );
                    }

                    return data;
                })
                .then(data => {

                    btn.disabled = false;

                    btn.innerHTML = `
            <span>Kirim Kode OTP</span>
            <svg viewBox="0 0 24 24"
                fill="none"
                class="h-4 w-4">
                <path d="M5 12h14m-6-6 6 6-6 6"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>
            </svg>
        `;

                    if (data.success) {

                        currentNoHp = noHpInput;

                        document.getElementById(
                            'otp-phone-display'
                        ).innerText = '+62 ' + noHpInput;

                        switchStep(1, 2, 'next');

                        startTimer60s();

                    } else {

                        errEl.innerText =
                            data.message || 'Gagal mengirim OTP.';

                        errEl.classList.remove('hidden');
                    }
                })
                .catch(error => {

                    btn.disabled = false;

                    btn.innerHTML = `
            <span>Kirim Kode OTP</span>
            <svg viewBox="0 0 24 24"
                fill="none"
                class="h-4 w-4">
                <path d="M5 12h14m-6-6 6 6-6 6"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>
            </svg>
        `;

                    errEl.innerText =
                        error.message || 'Terjadi kesalahan.';

                    errEl.classList.remove('hidden');
                });
        }

        // STEP 2: Verifikasi Kode OTP
        function verifikasiStep2Otp() {
            const otpCode = document.getElementById('otp-input-code').value.trim();
            const errEl = document.getElementById('otp-error-text');

            errEl.classList.add('hidden');

            if (!otpCode) {
                errEl.innerText = 'Masukkan 6 digit kode OTP.';
                errEl.classList.remove('hidden');
                return;
            }

            if (!/^\d{6}$/.test(otpCode)) {
                errEl.innerText = 'Kode OTP harus terdiri dari 6 digit.';
                errEl.classList.remove('hidden');
                return;
            }

            fetch('{{ route("member.otp.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify({
                    no_hp: currentNoHp,
                    otp: otpCode
                })
            })
                .then(async response => {
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'OTP tidak valid.');
                    }

                    return data;
                })
                .then(data => {

                    if (data.success) {

                        // Simpan OTP ke hidden input
                        document.getElementById('otp-hidden').value = otpCode;

                        // OTP benar → lanjut ke Step 3
                        switchStep(2, 3, 'next');

                    } else {
                        errEl.innerText = data.message || 'Kode OTP salah.';
                        errEl.classList.remove('hidden');
                    }

                })
                .catch(error => {
                    errEl.innerText = error.message || 'Kode OTP salah.';
                    errEl.classList.remove('hidden');
                });
        }

        // STEP 3: Submit Complete Form
        function submitRegisterStep3() {
            const nama = document.getElementById('nama').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const passwordConf = document.getElementById('password_confirmation').value;
            const errEl = document.getElementById('password-error-text');
            errEl.classList.add('hidden');

            if (!nama) {
                errEl.innerText = 'Silakan isi nama lengkap.';
                errEl.classList.remove('hidden');
                return;
            }

            if (!username || username.length < 3) {
                errEl.innerText = 'Username minimal 3 karakter.';
                errEl.classList.remove('hidden');
                return;
            }

            if (!/^[a-zA-Z0-9_.]+$/.test(username)) {
                errEl.innerText = 'Username hanya boleh huruf, angka, titik, dan underscore.';
                errEl.classList.remove('hidden');
                return;
            }

            if (!password || password.length < 6) {
                errEl.innerText = 'Kata sandi minimal 6 karakter.';
                errEl.classList.remove('hidden');
                return;
            }

            if (password !== passwordConf) {
                errEl.innerText = 'Konfirmasi kata sandi tidak cocok.';
                errEl.classList.remove('hidden');
                return;
            }

            document.getElementById('form-register').submit();
        }

        // Wizard Switcher with Slide Animation
        function switchStep(fromStep, toStep, dir) {
            const fromEl = document.getElementById(`step-${fromStep}`);
            const toEl = document.getElementById(`step-${toStep}`);

            if (dir === 'next') {
                fromEl.className = 'flex flex-col flex-1 step-exit-left';
                setTimeout(() => {
                    fromEl.classList.add('hidden');
                    toEl.classList.remove('hidden');
                    toEl.className = 'flex flex-col flex-1 step-enter-right';
                    currentStep = toStep;
                }, 180);
            } else {
                fromEl.className = 'flex flex-col flex-1 step-exit-right';
                setTimeout(() => {
                    fromEl.classList.add('hidden');
                    toEl.classList.remove('hidden');
                    toEl.className = 'flex flex-col flex-1 step-enter-left';
                    currentStep = toStep;
                }, 180);
            }
        }

        function goBackToStep(targetStep) {
            switchStep(currentStep, targetStep, 'back');
        }

        function startTimer60s() {
            let seconds = 60;
            const timerEl = document.getElementById('timer-text');
            const resendBtn = document.getElementById('btn-resend-otp');
            resendBtn.disabled = true;

            clearInterval(countdownTimer);
            countdownTimer = setInterval(() => {
                seconds--;
                timerEl.innerText = seconds + 's';
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    timerEl.innerText = '0s';
                    resendBtn.disabled = false;
                }
            }, 1000);
        }
    </script>
</body>

</html>