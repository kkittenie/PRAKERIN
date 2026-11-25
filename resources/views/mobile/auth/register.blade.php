<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF5E8]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join Membership &middot; {{ config('app.name', 'Daisy Bakery') }}</title>
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
        @keyframes slideInRight { 0% { opacity: 0; transform: translateX(20px); } 100% { opacity: 1; transform: translateX(0); } }
        @keyframes slideOutLeft { 0% { opacity: 1; transform: translateX(0); } 100% { opacity: 0; transform: translateX(-20px); } }
        @keyframes slideInLeft { 0% { opacity: 0; transform: translateX(-20px); } 100% { opacity: 1; transform: translateX(0); } }
        @keyframes slideOutRight { 0% { opacity: 1; transform: translateX(0); } 100% { opacity: 0; transform: translateX(20px); } }
        .step-enter-right { animation: slideInRight 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .step-exit-left { animation: slideOutLeft 0.2s cubic-bezier(0.4, 0, 1, 1) forwards; }
        .step-enter-left { animation: slideInLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .step-exit-right { animation: slideOutRight 0.2s cubic-bezier(0.4, 0, 1, 1) forwards; }
    </style>
</head>

<body class="min-h-screen w-full bg-[#FAF5E8] auth-font-ui text-[#231815] antialiased overflow-x-hidden">

    {{-- Wavy Liquid Loading Reveal Animation --}}
    <x-common.wave-loader subtitle="Join Daisy Club" />

    <div id="app-card" class="relative min-h-screen w-full flex flex-col md:grid md:grid-cols-12 bg-[#FAF5E8] step-enter-right overflow-hidden">

        {{-- ═══════════════════════════════════════════════════════════════
             DESKTOP LEFT VISUAL PANEL (>= 768px)
             Pure full-bleed patisserie image + organic vertical wave seam (no text)
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="hidden md:block md:col-span-6 relative min-h-screen overflow-hidden bg-[#3A221B] select-none">
            <img src="{{ \App\Models\WelcomeSlide::urlTerakhir(asset('images/welcome-slide-3.png')) }}"
                alt="Daisy Bakery Club"
                class="absolute inset-0 h-full w-full object-cover object-center">

            {{-- Organic Full-Height Vertical SVG Wave Seam --}}
            <svg class="pointer-events-none absolute top-0 -right-[1px] h-full w-20 lg:w-28 z-20"
                viewBox="0 0 110 900" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M 110 0 L 110 900 L 42 900 C 12 815, 76 740, 48 645 C 20 550, 74 475, 40 370 C 8 270, 68 185, 36 92 C 20 45, 34 18, 48 0 Z"
                    fill="#FAF5E8" />
            </svg>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             RIGHT PANEL (Desktop) / CLEAN AIRY CREAM SCREEN (Mobile)
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="relative md:col-span-6 flex-1 min-h-screen flex flex-col justify-between bg-[#FAF5E8] px-6 sm:px-10 md:px-10 lg:px-16 xl:px-20 py-6 md:py-10 overflow-hidden">

            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -right-3 top-20 md:top-12 w-12 h-14 opacity-20 md:opacity-25"
                style="--rot: 16deg; --dur: 4.5s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -left-3 bottom-24 md:bottom-12 w-12 h-14 opacity-20 md:opacity-22"
                style="--rot: -14deg; --dur: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt=""
                class="pointer-events-none select-none absolute right-6 bottom-6 w-16 h-16 opacity-[0.12] rotate-12">

            {{-- Top Back Row --}}
            <div class="relative z-10 flex items-center justify-between w-full max-w-[430px] mx-auto">
                <a href="{{ route('member.welcome') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="inline-flex items-center gap-1.5 rounded-full border border-[#3C2415]/14 bg-[#FFFDF9] px-3.5 py-2 text-xs font-bold text-[#3A221B] shadow-[0_2px_8px_rgba(58,34,27,0.05)] transition hover:border-[#8E1E26]/35 hover:text-[#8E1E26] active:scale-95">
                    <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Back</span>
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#8E1E26]/18 bg-[#FCEEEF] px-3 py-1.5 text-[11px] font-bold text-[#8E1E26]">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5">
                    <span>Join Club</span>
                </span>
            </div>

            {{-- Center Area --}}
            <div class="relative z-10 w-full max-w-[420px] mx-auto my-auto py-6">

                @if (session('error') || $errors->any())
                    <div class="mb-5 flex items-start gap-2.5 rounded-2xl bg-[#FCEEEF] border border-[#8E1E26]/25 px-4 py-3 text-xs font-semibold text-[#8E1E26]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 mt-0.5">
                            <path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                {{-- Mobile Mascot + Calligraphic Title --}}
                <div class="md:hidden text-center mb-6">
                    <div class="relative inline-flex items-center justify-center mb-2">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 shadow-[0_10px_24px_rgba(58,34,27,0.10)] p-2.5">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                        </span>
                        <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
                            class="absolute -right-2 -bottom-1 w-6 h-6 rotate-12 drop-shadow-xs">
                    </div>
                    <h1 class="auth-font-accent text-[54px] leading-[1.02] text-[#3A221B]">
                        Join Daisy!
                    </h1>
                    <p class="mt-1 text-xs font-medium text-[#7D6A60] max-w-xs mx-auto">
                        Sign up with Google to collect {{ \App\Models\PengaturanMember::adaAturanAktif() ? 'points, ' : '' }}stamps &amp; bakery vouchers.
                    </p>
                </div>

                {{-- Desktop Brand Logo + Friendly Greeting (Logo on Right Section!) --}}
                <div class="hidden md:block text-center mb-7">
                    <a href="{{ route('member.welcome') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                        class="inline-flex items-center justify-center gap-3 mb-5 group">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 p-1.5 shadow-[0_6px_18px_rgba(58,34,27,0.08)] transition group-hover:scale-105">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                        </span>
                        <img src="{{ asset('images/logo/logo-wordmark.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain">
                    </a>
                    <h1 class="auth-font-display text-[30px] lg:text-[34px] font-extrabold text-[#3A221B] leading-tight tracking-tight">
                        Hello! Welcome Aboard
                    </h1>
                    <p class="mt-1.5 text-sm font-medium text-[#6B574C]">
                        We are glad to welcome you
                    </p>
                </div>

                {{-- Member Perks Soft Cream Card --}}
                <div class="mb-6 relative overflow-hidden rounded-[26px] border border-[#3C2415]/12 bg-[#FFFDF9] p-5 shadow-[0_6px_22px_rgba(58,34,27,0.06)] space-y-3">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="pointer-events-none absolute -right-3 -bottom-3 h-20 w-20 opacity-15 rotate-12 select-none">
                    <p class="text-[10.5px] font-black uppercase tracking-wider text-[#8E1E26] flex items-center gap-1.5">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5 object-contain">
                        <span>Member Club Benefits</span>
                    </p>
                    <ul class="relative z-10 space-y-2.5 text-xs sm:text-[13px] text-[#3A221B] font-medium">
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#8E1E26] text-[#FAF5E8] text-[10px] font-bold">✓</span>
                            <span>Order ahead &amp; skip the counter line</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#8E1E26] text-[#FAF5E8] text-[10px] font-bold">✓</span>
                            <span>Collect bakery stamps for free pastries &amp; treats</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#8E1E26] text-[#FAF5E8] text-[10px] font-bold">✓</span>
                            <span>Exclusive member-only discount vouchers</span>
                        </li>
                    </ul>
                </div>

                {{-- Primary Pill Google Sign-Up CTA --}}
                <a href="{{ route('member.google.redirect') }}" data-google-native
                    class="group flex w-full items-center justify-center gap-3 rounded-full bg-[#8E1E26] hover:bg-[#741A21] px-6 py-4 text-sm sm:text-[15px] font-extrabold text-[#FAF5E8] shadow-[0_10px_24px_-6px_rgba(142,30,38,0.52)] transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.99]">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white shadow-2xs shrink-0">
                        <svg class="h-4 w-4" viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z" />
                            <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z" />
                            <path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.9-6.1A24 24 0 0 0 0 24c0 3.9.9 7.5 2.6 10.8l7.9-6.1z" />
                            <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.9 2.3-8.4 2.3-6.3 0-11.6-4.1-13.5-9.8l-7.9 6.1C6.5 42.6 14.6 48 24 48z" />
                        </svg>
                    </span>
                    <span>Sign up with Google</span>
                </a>

                {{-- ========================================================================= --}}
                {{-- FORM REGISTER NOMOR HP (DIHIDE SEMENTARA SESUAI PERMINTAAN, JANGAN DIHAPUS) --}}
                {{-- ========================================================================= --}}
                <div id="container-form-register-no-hp" class="hidden" style="display: none;">
                <form id="form-register" method="POST" action="{{ route('member.register.store') }}">
                    @csrf
                    <input type="hidden" id="otp-hidden" name="otp" value="">

                    <div id="step-1" class="space-y-6">
                        <div class="relative flex items-center justify-center py-2 mb-4 md:mb-6">
                            <a href="{{ route('member.welcome') }}"
                                onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                                class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] text-[#8E1E26] transition hover:bg-white active:scale-95">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <h1 class="text-base md:text-lg font-bold text-[#8E1E26]">Register Step 1/3</h1>
                        </div>

                        <div>
                            <h2 class="auth-font-display text-lg font-bold text-[#3A221B]">Input Phone Number</h2>
                            <p class="text-xs text-[#706864] mt-1 leading-relaxed">Enter your mobile phone number to receive an SMS OTP verification code.</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <div class="relative flex items-center rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] px-3 py-2.5 focus-within:border-[#8E1E26]">
                                    <div class="flex items-center gap-1 rounded-full bg-[#F4EAE0] px-3 py-1.5 text-sm font-bold text-[#3A221B]">
                                        <span>+62</span>
                                    </div>
                                    <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}"
                                        required autofocus placeholder="882xxxxxxx"
                                        oninput="this.value = this.value.replace(/\D/g, '').replace(/^0+/, '')"
                                        class="ml-3 flex-1 bg-transparent text-base font-semibold text-[#231815] outline-none placeholder:text-[#A6978D] placeholder:font-normal">
                                    <button type="button" onclick="document.getElementById('no_hp').value=''"
                                        class="text-[#8A6B63] hover:text-[#3A221B] p-1">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                <p id="phone-error-text" class="mt-2 text-xs text-[#8E1E26] font-semibold hidden"></p>
                            </div>

                            <button type="button" id="btn-step1-next" onclick="prosesKirimOtpStep1()"
                                class="w-full rounded-full bg-[#8E1E26] py-3.5 text-center text-base font-bold text-[#FAF5E8] shadow-md transition hover:bg-[#741A21] active:scale-[0.99] flex items-center justify-center gap-2">
                                <span>Send OTP Code</span>
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="step-2" class="space-y-6 hidden">
                        <div class="relative flex items-center justify-center py-2 mb-4 md:mb-6">
                            <button type="button" onclick="goBackToStep(1)"
                                class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] text-[#8E1E26] transition hover:bg-white active:scale-95">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <h1 class="text-base md:text-lg font-bold text-[#8E1E26]">Register Step 2/3</h1>
                        </div>

                        <div class="text-center">
                            <h2 class="auth-font-display text-xl font-bold text-[#3A221B]">Verify OTP Code</h2>
                            <p class="mt-1 text-xs text-[#706864] leading-relaxed px-4">
                                6-digit OTP code has been sent to <br><strong id="otp-phone-display" class="text-[#3A221B] font-bold"></strong>
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <input id="otp-input-code" type="text" maxlength="6" inputmode="numeric"
                                    placeholder="123456" autofocus
                                    class="w-full text-center tracking-[0.5em] text-2xl font-extrabold rounded-full border-2 border-[#8E1E26] bg-[#FFFDF9] py-3.5 text-[#231815] outline-none focus:ring-4 focus:ring-[#8E1E26]/15">
                                <p id="otp-error-text" class="mt-2 text-xs text-[#8E1E26] font-semibold hidden"></p>
                            </div>

                            <div class="flex items-center justify-between text-xs text-[#706864] px-1">
                                <span>Time remaining: <strong id="timer-text" class="text-[#8E1E26] font-bold">60s</strong></span>
                                <button type="button" id="btn-resend-otp" onclick="prosesKirimOtpStep1()"
                                    class="font-bold text-[#8E1E26] disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    Resend OTP
                                </button>
                            </div>

                            <button type="button" onclick="verifikasiStep2Otp()"
                                class="w-full rounded-full bg-[#8E1E26] py-3.5 text-center text-base font-bold text-[#FAF5E8] shadow-md transition hover:bg-[#741A21] active:scale-[0.99] flex items-center justify-center gap-2">
                                <span>Verify OTP Code</span>
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="step-3" class="space-y-4 hidden">
                        <div class="relative flex items-center justify-center py-2 mb-4 md:mb-6">
                            <button type="button" onclick="goBackToStep(2)"
                                class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] text-[#8E1E26] transition hover:bg-white active:scale-95">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <h1 class="text-base md:text-lg font-bold text-[#8E1E26]">Register Step 3/3</h1>
                        </div>

                        <div>
                            <h2 class="auth-font-display text-lg font-bold text-[#3A221B]">Create Profile &amp; Password</h2>
                            <p class="text-xs text-[#706864] mt-1 leading-relaxed">Complete your name and create a password to protect your Daisy Bakery member account.</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="nama" class="block text-xs font-semibold text-[#6B574C] mb-1 pl-3">Full Name</label>
                                <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required placeholder="e.g. Jane Doe"
                                    class="w-full rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] px-5 py-3 text-sm text-[#231815] outline-none focus:border-[#8E1E26] focus:ring-2 focus:ring-[#8E1E26]/12">
                                @error('nama')<p class="mt-1 text-xs text-[#8E1E26]">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="username" class="block text-xs font-semibold text-[#6B574C] mb-1 pl-3">Username</label>
                                <input id="username" name="username" type="text" value="{{ old('username') }}" required minlength="3" maxlength="50" placeholder="e.g. janedoe"
                                    oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                    class="w-full rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] px-5 py-3 text-sm text-[#231815] outline-none focus:border-[#8E1E26] focus:ring-2 focus:ring-[#8E1E26]/12">
                                <p class="mt-1 pl-3 text-[11px] text-[#8A6B63]">Letters, numbers, periods, or underscores.</p>
                                @error('username')<p class="mt-1 text-xs text-[#8E1E26]">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-semibold text-[#6B574C] mb-1 pl-3">Password</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" required minlength="6" placeholder="Minimum 6 characters"
                                        class="w-full rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] px-5 py-3 pr-12 text-sm text-[#231815] outline-none focus:border-[#8E1E26] focus:ring-2 focus:ring-[#8E1E26]/12">
                                    <button type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Show password"
                                        class="absolute inset-y-0 right-1.5 flex w-10 items-center justify-center text-[#8A6B63] hover:text-[#3A221B]">
                                        <svg class="eye-icon-show h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" />
                                        </svg>
                                        <svg class="eye-icon-hide hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.36 5.16A11 11 0 0 1 12 5c7 0 11 7 11 7a13.2 13.2 0 0 1-3.22 3.9M6.5 6.64C3.68 8.36 2 11 2 11a13.2 13.2 0 0 0 3.22 3.9A11 11 0 0 0 12 17c.86 0 1.68-.1 2.45-.28" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')<p class="mt-1 text-xs text-[#8E1E26]">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold text-[#6B574C] mb-1 pl-3">Confirm Password</label>
                                <div class="relative">
                                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6" placeholder="Repeat password"
                                        class="w-full rounded-full border border-[#3C2415]/15 bg-[#FFFDF9] px-5 py-3 pr-12 text-sm text-[#231815] outline-none focus:border-[#8E1E26] focus:ring-2 focus:ring-[#8E1E26]/12">
                                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Show password"
                                        class="absolute inset-y-0 right-1.5 flex w-10 items-center justify-center text-[#8A6B63] hover:text-[#3A221B]">
                                        <svg class="eye-icon-show h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" />
                                        </svg>
                                        <svg class="eye-icon-hide hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.36 5.16A11 11 0 0 1 12 5c7 0 11 7 11 7a13.2 13.2 0 0 1-3.22 3.9M6.5 6.64C3.68 8.36 2 11 2 11a13.2 13.2 0 0 0 3.22 3.9A11 11 0 0 0 12 17c.86 0 1.68-.1 2.45-.28" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                <p id="password-error-text" class="mt-1 text-xs text-[#8E1E26] font-semibold hidden"></p>
                            </div>

                            <button type="button" onclick="submitRegisterStep3()"
                                class="w-full mt-4 rounded-full bg-[#8E1E26] py-3.5 text-center text-base font-bold text-[#FAF5E8] shadow-md transition hover:bg-[#741A21] active:scale-[0.99] flex items-center justify-center gap-2">
                                <span>Complete Registration</span>
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
                </div>
            </div>

            {{-- Bottom Account Switch & Terms --}}
            <div class="relative z-10 w-full max-w-[420px] mx-auto text-center space-y-2 pt-3">
                <p class="text-xs text-[#7D6A60]">
                    Already have an account?
                    <a href="{{ route('member.login') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                        class="inline-flex items-center gap-0.5 font-extrabold text-[#3A221B] hover:text-[#8E1E26] transition ml-1">
                        <span>Sign in</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </a>
                </p>

                <p class="text-[11px] leading-relaxed text-[#9A877B]">
                    By signing up, you agree to our
                    <a href="{{ route('member.konten.show', 'terms-conditions') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="font-bold text-[#8E1E26] hover:underline">Terms &amp; Conditions</a>
                    and
                    <a href="{{ route('member.konten.show', 'privacy-policy') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="font-bold text-[#8E1E26] hover:underline">Privacy Policy</a>.
                </p>
            </div>
        </div>

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
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        }

        function restoreAppCard(isBack = false) {
            const card = document.getElementById('app-card');
            if (!card) return;
            card.style.opacity = '';
            card.style.transform = '';
            card.style.transition = '';
            if (isBack) {
                card.classList.remove('step-enter-right', 'step-enter-left');
                void card.offsetWidth;
                card.classList.add('step-enter-left');
            }
        }

        window.addEventListener('pageshow', (event) => { restoreAppCard(event.persisted); });
        window.addEventListener('pagehide', () => { restoreAppCard(false); });
        window.addEventListener('popstate', () => { restoreAppCard(true); });

        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('app-card');
            if (card) {
                restoreAppCard(false);
                const dir = sessionStorage.getItem('nav_dir');
                card.classList.add(dir === 'back' ? 'step-enter-left' : 'step-enter-right');
                sessionStorage.removeItem('nav_dir');
            }
        });

        function animateNavigate(url, dir) {
            const card = document.getElementById('app-card');
            if (!card) { window.location.href = url; return; }
            card.style.transition = 'all 0.16s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '0';
            if (dir === 'back') {
                card.style.transform = 'translateX(20px)';
                sessionStorage.setItem('nav_dir', 'back');
            } else {
                card.style.transform = 'translateX(-16px)';
                sessionStorage.setItem('nav_dir', 'forward');
            }
            setTimeout(() => { window.location.href = url; }, 150);
            setTimeout(() => { restoreAppCard(false); }, 500);
        }

        function prosesKirimOtpStep1() {
            const noHpInput = document.getElementById('no_hp').value.trim();
            const errEl = document.getElementById('phone-error-text');
            const btn = document.getElementById('btn-step1-next');

            errEl.classList.add('hidden');
            if (!noHpInput) {
                errEl.innerText = 'Please enter your phone number first.';
                errEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span>Sending OTP...</span>`;

            fetch('{{ route("member.otp.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ no_hp: noHpInput })
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Failed to send OTP.');
                    return data;
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<span>Send OTP Code</span><svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
                    if (data.success) {
                        currentNoHp = noHpInput;
                        document.getElementById('otp-phone-display').innerText = '+62 ' + noHpInput;
                        switchStep(1, 2, 'next');
                        startTimer60s();
                    } else {
                        errEl.innerText = data.message || 'Failed to send OTP.';
                        errEl.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = `<span>Send OTP Code</span><svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
                    errEl.innerText = error.message || 'An unexpected error occurred.';
                    errEl.classList.remove('hidden');
                });
        }

        function verifikasiStep2Otp() {
            const otpCode = document.getElementById('otp-input-code').value.trim();
            const errEl = document.getElementById('otp-error-text');
            errEl.classList.add('hidden');

            if (!otpCode) { errEl.innerText = 'Please enter the 6-digit OTP code.'; errEl.classList.remove('hidden'); return; }
            if (!/^\d{6}$/.test(otpCode)) { errEl.innerText = 'OTP code must be exactly 6 digits.'; errEl.classList.remove('hidden'); return; }

            fetch('{{ route("member.otp.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ no_hp: currentNoHp, otp: otpCode })
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Invalid OTP code.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('otp-hidden').value = otpCode;
                        switchStep(2, 3, 'next');
                    } else {
                        errEl.innerText = data.message || 'Invalid OTP code.';
                        errEl.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    errEl.innerText = error.message || 'Invalid OTP code.';
                    errEl.classList.remove('hidden');
                });
        }

        function submitRegisterStep3() {
            const nama = document.getElementById('nama').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const passwordConf = document.getElementById('password_confirmation').value;
            const errEl = document.getElementById('password-error-text');
            errEl.classList.add('hidden');

            if (!nama) { errEl.innerText = 'Please enter your full name.'; errEl.classList.remove('hidden'); return; }
            if (!username || username.length < 3) { errEl.innerText = 'Username must be at least 3 characters.'; errEl.classList.remove('hidden'); return; }
            if (!/^[a-zA-Z0-9_.]+$/.test(username)) { errEl.innerText = 'Username can only contain letters, numbers, dots, and underscores.'; errEl.classList.remove('hidden'); return; }
            if (!password || password.length < 6) { errEl.innerText = 'Password must be at least 6 characters.'; errEl.classList.remove('hidden'); return; }
            if (password !== passwordConf) { errEl.innerText = 'Password confirmation does not match.'; errEl.classList.remove('hidden'); return; }

            document.getElementById('form-register').submit();
        }

        function switchStep(fromStep, toStep, dir) {
            const fromEl = document.getElementById(`step-${fromStep}`);
            const toEl = document.getElementById(`step-${toStep}`);
            const fromClass = fromStep === 3 ? 'space-y-4' : 'space-y-6';
            const toClass = toStep === 3 ? 'space-y-4' : 'space-y-6';

            if (dir === 'next') {
                fromEl.className = `${fromClass} step-exit-left`;
                setTimeout(() => {
                    fromEl.classList.add('hidden');
                    toEl.classList.remove('hidden');
                    toEl.className = `${toClass} step-enter-right`;
                    currentStep = toStep;
                }, 180);
            } else {
                fromEl.className = `${fromClass} step-exit-right`;
                setTimeout(() => {
                    fromEl.classList.add('hidden');
                    toEl.classList.remove('hidden');
                    toEl.className = `${toClass} step-enter-left`;
                    currentStep = toStep;
                }, 180);
            }
        }

        function goBackToStep(targetStep) { switchStep(currentStep, targetStep, 'back'); }

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
@include('mobile.auth._google-native')
</body>

</html>
