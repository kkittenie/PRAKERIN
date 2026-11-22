@php
    $isAdmin = ($mode ?? 'member') === 'admin';
    $formAction = $isAdmin ? route('admin.login.store') : route('member.login.store');
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF5E8]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $isAdmin ? 'Staff Sign In' : 'Sign In' }} &middot; {{ config('app.name', 'Daisy Bakery') }}</title>
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
        @keyframes pageEnterFromRight {
            0% { opacity: 0; transform: translateX(16px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes pageEnterFromLeft {
            0% { opacity: 0; transform: translateX(-16px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        .page-enter-right { animation: pageEnterFromRight 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .page-enter-left { animation: pageEnterFromLeft 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes appSplashIn {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .app-splash-in { animation: appSplashIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>

<body class="min-h-screen w-full bg-[#FAF5E8] auth-font-ui text-[#231815] antialiased overflow-x-hidden">

    {{-- Wavy Liquid Loading Reveal Animation (Member Only) --}}
    @if(!$isAdmin)
        <x-common.wave-loader subtitle="Freshly Baked Everyday" />
    @endif

    {{-- Full-Bleed Split Layout (Desktop matches wavy organic split reference; Mobile matches clean airy cream reference) --}}
    <div id="app-card" class="relative min-h-screen w-full flex flex-col md:grid md:grid-cols-12 bg-[#FAF5E8] overflow-hidden">

        {{-- ═══════════════════════════════════════════════════════════════
             DESKTOP LEFT VISUAL PANEL (>= 768px)
             Pure full-bleed patisserie image + organic SVG wave seam (no text)
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="hidden md:block md:col-span-6 relative min-h-screen overflow-hidden bg-[#3A221B] select-none">
            <img src="{{ \App\Models\WelcomeSlide::urlPertama(asset('images/welcome-slide-1.png')) }}"
                alt="Daisy Bakery Artisan Patisserie"
                class="absolute inset-0 h-full w-full object-cover object-center">

            {{-- Organic Full-Height Vertical SVG Wave Seam (Exact Desktop Reference Signature!) --}}
            <svg class="pointer-events-none absolute top-0 -right-[1px] h-full w-20 lg:w-28 z-20"
                viewBox="0 0 110 900" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M 110 0 L 110 900 L 42 900 C 12 815, 76 740, 48 645 C 20 550, 74 475, 40 370 C 8 270, 68 185, 36 92 C 20 45, 34 18, 48 0 Z"
                    fill="#FAF5E8" />
            </svg>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             RIGHT COLUMN (Desktop) / FULL AIRY CREAM SCREEN (Mobile)
             Matches Mobile Reference ("Hello again!" + mascot + soft pills)
             and Desktop Reference ("Hello! Welcome Back" + pill inputs)
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="relative md:col-span-6 flex-1 min-h-screen flex flex-col justify-between bg-[#FAF5E8] px-6 sm:px-10 md:px-10 lg:px-16 xl:px-20 py-6 md:py-10 overflow-hidden">

            {{-- Gentle Decorative Strawberries (subtle corner whimsy like reference doodles) --}}
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -right-3 top-20 md:top-12 w-12 h-14 opacity-20 md:opacity-25"
                style="--rot: 16deg; --dur: 4.5s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt=""
                class="auth-float pointer-events-none select-none absolute -left-3 bottom-24 md:bottom-12 w-12 h-14 opacity-20 md:opacity-22"
                style="--rot: -14deg; --dur: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
                class="pointer-events-none select-none absolute right-6 bottom-6 w-16 h-16 opacity-[0.12] rotate-12">

            {{-- Top Navigation Row --}}
            <div class="relative z-10 flex items-center justify-between w-full max-w-[430px] mx-auto">
                <a href="{{ route('member.welcome') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="inline-flex items-center gap-1.5 rounded-full border border-[#3C2415]/14 bg-[#FFFDF9] px-3.5 py-2 text-xs font-bold text-[#3A221B] shadow-[0_2px_8px_rgba(58,34,27,0.05)] transition hover:border-[#8E1E26]/35 hover:text-[#8E1E26] active:scale-95">
                    <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Back</span>
                </a>

                @unless ($isAdmin)
                    <button type="button" onclick="bukaModalInfoLogin()"
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#8E1E26]/18 bg-[#FCEEEF] px-3 py-1.5 text-[11px] font-bold text-[#8E1E26] transition hover:bg-[#F8DCE0]">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5">
                        <span>Google Login Info</span>
                    </button>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#8E1E26]/18 bg-[#FCEEEF] px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-[#8E1E26]">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5">
                        <span>Staff Portal</span>
                    </span>
                @endunless
            </div>

            {{-- Center Form Area --}}
            <div class="relative z-10 w-full max-w-[420px] mx-auto my-auto py-6">

                {{-- Mobile Mascot Emblem + Calligraphic "Hello again!" (Exact Mobile Reference Vibe!) --}}
                <div class="md:hidden text-center mb-7">
                    <div class="relative inline-flex items-center justify-center mb-2">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 shadow-[0_10px_24px_rgba(58,34,27,0.10)] p-2.5">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                        </span>
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                            class="absolute -right-2 -bottom-1 w-6 h-6 rotate-12 drop-shadow-xs">
                    </div>
                    <h1 class="auth-font-accent text-[54px] leading-[1.02] text-[#3A221B] tracking-normal">
                        {{ $isAdmin ? 'Staff Portal' : 'Hello again!' }}
                    </h1>
                    <p class="mt-1 text-xs font-medium text-[#7D6A60]">
                        {{ $isAdmin ? 'Sign in to Daisy Bakery POS workspace' : 'Sign in to your Daisy Bakery account' }}
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
                        {{ $isAdmin ? 'Hello! Staff Sign In' : 'Hello! Welcome Back' }}
                    </h1>
                    <p class="mt-1.5 text-sm font-medium text-[#6B574C]">
                        {{ $isAdmin ? 'Ready for today\'s fresh bakes' : 'We are glad to see you again' }}
                    </p>
                </div>

                {{-- Inline Error Alert --}}
                @if (session('error') || $errors->any())
                    <div class="mb-5 flex items-start gap-2.5 rounded-2xl bg-[#FCEEEF] border border-[#8E1E26]/25 px-4 py-3 text-xs font-semibold text-[#8E1E26]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 mt-0.5">
                            <path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ $formAction }}" class="space-y-4">
                    @csrf

                    {{-- Identifier Field (Soft Cream Pill Input) --}}
                    <div>
                        <label for="identifier" class="block text-xs font-semibold text-[#6B574C] mb-1.5 pl-3">
                            {{ $isAdmin ? 'Username or email' : 'Username or phone' }}
                        </label>
                        <div x-data="{ identifier: @js(old('identifier', '')) }"
                            class="relative flex items-center rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] px-5 py-3.5 shadow-[0_3px_12px_rgba(58,34,27,0.05)] transition focus-within:border-[#8E1E26] focus-within:ring-4 focus-within:ring-[#8E1E26]/10 focus-within:bg-white">
                            <input id="identifier" type="text" name="identifier" x-model="identifier" required autofocus
                                autocomplete="username" autocapitalize="none" spellcheck="false"
                                placeholder="{{ $isAdmin ? 'admin@daisybakery.id' : 'Enter username or phone number' }}"
                                class="w-full bg-transparent text-sm font-semibold text-[#231815] outline-none placeholder:text-[#A6978D] placeholder:font-normal">
                            <button type="button" x-show="identifier" x-cloak @click="identifier = ''"
                                class="ml-2 text-[#9A877B] hover:text-[#3A221B] transition" aria-label="Clear">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Password Field (Soft Cream Pill Input) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5 px-3">
                            <label for="password" class="block text-xs font-semibold text-[#6B574C]">
                                Password
                            </label>
                            @unless ($isAdmin)
                                <button type="button" onclick="bukaModalInfoLogin()"
                                    class="text-[11px] font-bold text-[#8E1E26] hover:underline">
                                    Password info?
                                </button>
                            @endunless
                        </div>
                        <div class="relative flex items-center rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] pl-5 pr-12 py-3.5 shadow-[0_3px_12px_rgba(58,34,27,0.05)] transition focus-within:border-[#8E1E26] focus-within:ring-4 focus-within:ring-[#8E1E26]/10 focus-within:bg-white">
                            <input id="password" name="password" type="password" required
                                placeholder="••••••••"
                                class="w-full bg-transparent text-sm font-semibold text-[#231815] outline-none placeholder:text-[#A6978D] placeholder:font-normal">
                            <button type="button" onclick="togglePasswordVisibility('password', this)"
                                aria-label="Show password"
                                class="absolute inset-y-0 right-1.5 flex w-10 items-center justify-center text-[#8A766B] hover:text-[#3A221B] transition">
                                <svg class="eye-icon-show h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" />
                                </svg>
                                <svg class="eye-icon-hide hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.36 5.16A11 11 0 0 1 12 5c7 0 11 7 11 7a13.2 13.2 0 0 1-3.22 3.9M6.5 6.64C3.68 8.36 2 11 2 11a13.2 13.2 0 0 0 3.22 3.9A11 11 0 0 0 12 17c.86 0 1.68-.1 2.45-.28" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 pl-3 text-xs text-[#8E1E26] font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me Row --}}
                    <div class="flex items-center justify-between px-2 pt-1">
                        <label class="group inline-flex items-center gap-2.5 text-xs font-semibold text-[#5C4338] hover:text-[#3A221B] cursor-pointer select-none transition-colors">
                            <span class="relative flex h-4.5 w-4.5 items-center justify-center">
                                <input type="checkbox" name="ingat_saya" id="ingat_saya" value="1" {{ old('ingat_saya') ? 'checked' : '' }}
                                    class="peer sr-only">
                                <span class="absolute inset-0 rounded-[6px] border border-[#3C2415]/25 bg-[#FFFDF9] shadow-2xs transition-all duration-200 group-hover:border-[#8E1E26]/50 peer-checked:border-[#8E1E26] peer-checked:bg-[#8E1E26] peer-checked:shadow-[0_2px_8px_rgba(142,30,38,0.25)] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8E1E26]/30"></span>
                                <svg class="pointer-events-none relative z-10 h-3 w-3 text-[#FAF5E8] opacity-0 scale-50 transition-all duration-150 peer-checked:opacity-100 peer-checked:scale-100"
                                    viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2.5 6.2L4.75 8.4L9.5 3.6" />
                                </svg>
                            </span>
                            <span class="leading-none pt-0.5">Remember me</span>
                        </label>
                    </div>

                    {{-- Primary Pill Submit Button --}}
                    <button type="submit"
                        class="w-full mt-2 rounded-full bg-[#8E1E26] hover:bg-[#741A21] active:scale-[0.99] py-3.5 text-center text-sm font-extrabold tracking-wide text-[#FAF5E8] shadow-[0_10px_22px_-6px_rgba(142,30,38,0.52)] transition cursor-pointer">
                        Log in
                    </button>
                </form>

                {{-- Google Social Login --}}
                @if (($mode ?? null) !== 'admin')
                    @include('mobile.auth._google-button')
                @endif
            </div>

            {{-- Clean Bottom Account Switch & Terms --}}
            <div class="relative z-10 w-full max-w-[420px] mx-auto text-center space-y-2 pt-3">
                @unless ($isAdmin)
                    <p class="text-xs text-[#7D6A60]">
                        Don't have an account?
                        <a href="{{ route('member.register') }}"
                            onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                            class="inline-flex items-center gap-0.5 font-extrabold text-[#3A221B] hover:text-[#8E1E26] transition ml-1">
                            <span>Sign up</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </p>
                @endunless

                <p class="text-[11px] leading-relaxed text-[#9A877B]">
                    By signing in, you agree to our
                    <a href="{{ route('member.konten.show', 'terms-conditions') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="font-bold text-[#8E1E26] hover:underline">Terms &amp; Conditions</a>
                    and
                    <a href="{{ route('member.konten.show', 'privacy-policy') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="font-bold text-[#8E1E26] hover:underline">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>

    @unless ($isAdmin)
        {{-- Modal Pop-up Informasi Nomor HP Sebagai Kata Sandi --}}
        <div id="modal-info-login"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#231815]/60 backdrop-blur-sm transition-all duration-200 hidden opacity-0"
            aria-labelledby="modal-info-title" role="dialog" aria-modal="true" onclick="if(event.target === this) tutupModalInfoLogin()">
            <div class="relative w-full max-w-sm transform overflow-hidden rounded-[28px] border border-[#3C2415]/15 bg-[#FAF5E8] p-6 text-left shadow-2xl transition-all scale-95 duration-200" id="modal-info-card">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="pointer-events-none select-none absolute -right-3 -bottom-3 w-20 h-20 opacity-15 rotate-12">

                <button type="button" onclick="tutupModalInfoLogin()"
                    class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] text-[#6B574C] hover:text-[#3A221B] transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#FCEEEF] border border-[#8E1E26]/15">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-6 w-6">
                    </div>
                    <div class="pr-6">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#8E1E26] bg-[#FCEEEF] px-2.5 py-0.5 rounded-full border border-[#8E1E26]/15">Account &amp; Password Info</span>
                        <h2 id="modal-info-title" class="auth-font-display text-base font-bold text-[#3A221B] mt-1 leading-snug">Mobile Number as Default Password</h2>
                    </div>
                </div>

                <div class="relative z-10 space-y-3.5 text-xs text-[#5C4338] leading-relaxed">
                    <div class="rounded-2xl bg-[#FFFDF9] border border-[#3C2415]/12 p-3.5">
                        <p class="font-bold text-xs text-[#3A221B] mb-1">For Google Account Users:</p>
                        <p class="text-[11.5px] leading-relaxed text-[#6B574C]">
                            If you registered with <strong class="text-[#3A221B]">Google</strong>, your account password is automatically set to the <strong class="text-[#3A221B]">Mobile Phone Number</strong> entered during signup (e.g. <span class="font-bold font-mono text-[#8E1E26] bg-[#FCEEEF] px-1.5 py-0.5 rounded">088xxxxxxxx</span>).
                        </p>
                    </div>

                    <div class="space-y-2">
                        <p class="font-bold text-[#3A221B] text-xs">How to Sign In:</p>
                        <div class="flex items-start gap-2.5 text-[11.5px]">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#3A221B] text-[#FAF5E8] font-bold text-[10px] mt-0.5">1</span>
                            <p><strong class="text-[#3A221B]">Via Google Button:</strong> Tap <em>"Continue with Google"</em> for instant one-click login without typing a password.</p>
                        </div>
                        <div class="flex items-start gap-2.5 text-[11.5px]">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#3A221B] text-[#FAF5E8] font-bold text-[10px] mt-0.5">2</span>
                            <p><strong class="text-[#3A221B]">Manual Sign In:</strong> Enter your Username or Phone Number, then use your mobile number (<span class="font-mono font-semibold text-[#3A221B]">088...</span>) as your password.</p>
                        </div>
                    </div>

                    <p class="text-[11px] text-[#8A766B] italic pt-1 border-t border-dashed border-[#3C2415]/15">
                        * You can update your password anytime under Account Settings after signing in.
                    </p>
                </div>

                <div class="relative z-10 mt-5 pt-3 border-t border-[#3C2415]/10 flex flex-col gap-3">
                    <label class="group flex items-center gap-2.5 text-xs font-medium text-[#6B574C] hover:text-[#3A221B] cursor-pointer select-none transition-colors">
                        <span class="relative flex h-4.5 w-4.5 shrink-0 items-center justify-center">
                            <input type="checkbox" id="cb-jangan-tampilkan" onchange="handleCheckboxToggle(this.checked)"
                                class="peer sr-only">
                            <span class="absolute inset-0 rounded-[6px] border border-[#3C2415]/25 bg-[#FFFDF9] shadow-2xs transition-all duration-200 group-hover:border-[#8E1E26]/50 peer-checked:border-[#8E1E26] peer-checked:bg-[#8E1E26] peer-checked:shadow-[0_2px_8px_rgba(142,30,38,0.25)] peer-focus-visible:ring-2 peer-focus-visible:ring-[#8E1E26]/30"></span>
                            <svg class="pointer-events-none relative z-10 h-3 w-3 text-[#FAF5E8] opacity-0 scale-50 transition-all duration-150 peer-checked:opacity-100 peer-checked:scale-100"
                                viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2.5 6.2L4.75 8.4L9.5 3.6" />
                            </svg>
                        </span>
                        <span id="label-jangan-tampilkan" class="leading-tight">Do not show this message again automatically</span>
                    </label>
                    <button type="button" onclick="tutupModalInfoLogin()"
                        class="w-full rounded-full bg-[#3A221B] py-3 text-center text-xs font-extrabold uppercase tracking-wider text-[#FAF5E8] shadow-md hover:bg-[#231815] active:scale-[0.99] transition">
                        Got It
                    </button>
                </div>
            </div>
        </div>
    @endunless

    <script>
        const DISMISS_KEY = 'saisho_login_pw_info_dismissed';

        function getDismissedStatus() {
            try { if (localStorage.getItem(DISMISS_KEY) === '1') return true; } catch (e) {}
            try { return document.cookie.split(';').some(c => c.trim().startsWith(DISMISS_KEY + '=1')); } catch (e) {}
            return false;
        }

        function setDismissedStatus(val) {
            try {
                if (val) localStorage.setItem(DISMISS_KEY, '1');
                else localStorage.removeItem(DISMISS_KEY);
            } catch (e) {}
            try {
                if (val) document.cookie = DISMISS_KEY + '=1; path=/; max-age=' + (365 * 24 * 60 * 60) + '; SameSite=Lax';
                else document.cookie = DISMISS_KEY + '=; path=/; max-age=0; SameSite=Lax';
            } catch (e) {}
        }

        function handleCheckboxToggle(isChecked) {
            setDismissedStatus(isChecked);
            const label = document.getElementById('label-jangan-tampilkan');
            if (label) {
                label.textContent = isChecked ? 'Do not show again' : 'Do not show again';
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.querySelector('.eye-icon-show').classList.toggle('hidden', !showing);
            btn.querySelector('.eye-icon-hide').classList.toggle('hidden', showing);
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        }

        function bukaModalInfoLogin() {
            const modal = document.getElementById('modal-info-login');
            const card = document.getElementById('modal-info-card');
            const cb = document.getElementById('cb-jangan-tampilkan');
            if (!modal) return;

            if (cb) {
                cb.checked = getDismissedStatus();
                const label = document.getElementById('label-jangan-tampilkan');
                if (label) label.textContent = cb.checked ? 'Do not show again' : 'Do not show again';
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

            if (cb && cb.checked) setDismissedStatus(true);
            if (!modal) return;
            modal.classList.add('opacity-0');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            setTimeout(() => { modal.classList.add('hidden'); }, 200);
        }

        function restoreAppCard(isBack = false) {
            const card = document.getElementById('app-card');
            if (!card) return;
            card.style.opacity = '';
            card.style.transform = '';
            card.style.transition = '';
            if (isBack) {
                card.classList.remove('app-splash-in', 'page-enter-right', 'page-enter-left');
                void card.offsetWidth;
                card.classList.add('page-enter-left');
            }
        }

        window.addEventListener('pageshow', (event) => { restoreAppCard(event.persisted); });
        window.addEventListener('pagehide', () => { restoreAppCard(false); });
        window.addEventListener('popstate', () => { restoreAppCard(true); });

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

            const urlParams = new URLSearchParams(window.location.search);
            const forceShow = urlParams.get('buka_info') === '1';
            const dismissed = getDismissedStatus();

            if (forceShow || (!dismissed && !sessionStorage.getItem('saisho_login_modal_shown_session'))) {
                sessionStorage.setItem('saisho_login_modal_shown_session', '1');
                setTimeout(bukaModalInfoLogin, 1150);
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
    </script>
</body>

</html>
