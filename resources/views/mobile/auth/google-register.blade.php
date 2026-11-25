<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF5E8]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Registration &middot; {{ config('app.name', 'Daisy Bakery') }}</title>
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
    </style>
</head>

<body class="min-h-screen w-full bg-[#FAF5E8] auth-font-ui text-[#231815] antialiased overflow-x-hidden">

    <x-common.wave-loader subtitle="Almost Done" />

    <div id="app-card" class="relative min-h-screen w-full flex flex-col md:grid md:grid-cols-12 bg-[#FAF5E8] overflow-hidden">

        {{-- Desktop Left Visual Panel: Pure Image + Organic Wave Seam (no text) --}}
        <div class="hidden md:block md:col-span-6 relative min-h-screen overflow-hidden bg-[#3A221B] select-none">
            <img src="{{ \App\Models\WelcomeSlide::urlTerakhir(asset('images/welcome-slide-3.png')) }}" alt="Daisy Member Registration"
                class="absolute inset-0 h-full w-full object-cover object-center">

            <svg class="pointer-events-none absolute top-0 -right-[1px] h-full w-20 lg:w-28 z-20"
                viewBox="0 0 110 900" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M 110 0 L 110 900 L 42 900 C 12 815, 76 740, 48 645 C 20 550, 74 475, 40 370 C 8 270, 68 185, 36 92 C 20 45, 34 18, 48 0 Z"
                    fill="#FAF5E8" />
            </svg>
        </div>

        {{-- Right Panel / Clean Cream Mobile Screen --}}
        <div class="relative md:col-span-6 flex-1 min-h-screen flex flex-col justify-between bg-[#FAF5E8] px-6 sm:px-10 md:px-10 lg:px-16 xl:px-20 py-6 md:py-10 overflow-hidden">

            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="pointer-events-none select-none absolute -right-4 -bottom-4 w-24 h-24 opacity-[0.12] rotate-12">

            <div class="relative z-10 w-full max-w-[420px] mx-auto my-auto py-4">
                {{-- Mobile Mascot Header --}}
                <div class="md:hidden text-center mb-5">
                    <div class="relative inline-flex items-center justify-center mb-1.5">
                        <span class="flex h-18 w-18 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 shadow-md p-2.5">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                        </span>
                    </div>
                </div>

                {{-- Desktop Brand Logo on Right Panel --}}
                <div class="hidden md:flex items-center gap-3 mb-5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/12 p-1.5 shadow-[0_6px_18px_rgba(58,34,27,0.08)]">
                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                    </span>
                    <img src="{{ asset('images/logo/logo-wordmark.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain">
                </div>

                <div class="mb-6 text-center md:text-left">
                    <p id="step-label" class="inline-flex items-center gap-1.5 rounded-full bg-[#FCEEEF] border border-[#8E1E26]/15 px-3 py-1 text-[10.5px] font-black uppercase tracking-wider text-[#8E1E26] mb-2">
                        Step 1 of 2
                    </p>
                    <h1 id="step-title" class="auth-font-display text-2xl sm:text-[28px] font-extrabold text-[#3A221B] tracking-tight">
                        Join Daisy Bakery Club
                    </h1>
                    <p id="step-desc" class="text-xs sm:text-[13px] text-[#7D6A60] mt-1 leading-relaxed">
                        No member account found for this Google email. Review the details below to continue.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 flex items-start gap-2 rounded-2xl bg-[#FCEEEF] border border-[#8E1E26]/25 p-3.5 text-xs font-semibold text-[#8E1E26]">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('member.google.register.store') }}" class="space-y-4" id="form-google-register">
                    @csrf

                    {{-- STEP 1 --}}
                    <div id="step-1" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#6B574C] mb-1.5 pl-3">Email</label>
                            <input type="email" value="{{ $google['email'] }}" readonly disabled
                                class="w-full rounded-full border border-[#3C2415]/12 bg-[#EFE5D6]/65 px-5 py-3.5 text-sm font-semibold text-[#7D6A60] cursor-not-allowed">
                            <a href="{{ route('member.google.redirect', ['pilih_akun' => 1]) }}" data-google-native
                                class="mt-1.5 pl-3 inline-block text-xs font-bold text-[#8E1E26] hover:underline">
                                Use another Google account
                            </a>
                        </div>

                        <div>
                            <label for="nama" class="block text-xs font-semibold text-[#6B574C] mb-1.5 pl-3">Full Name</label>
                            <input id="nama" name="nama" type="text" required maxlength="255"
                                value="{{ old('nama', $google['nama']) }}"
                                class="w-full rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] px-5 py-3.5 text-sm font-semibold text-[#231815] shadow-[0_3px_12px_rgba(58,34,27,0.05)] outline-none focus:border-[#8E1E26] focus:bg-white focus:ring-4 focus:ring-[#8E1E26]/10">
                            @error('nama')<p class="mt-1 pl-3 text-xs font-semibold text-[#8E1E26]">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="username" class="block text-xs font-semibold text-[#6B574C] mb-1.5 pl-3">Username</label>
                            <div class="relative">
                                <input id="username" name="username" type="text" required minlength="3" maxlength="50"
                                    autocomplete="off" value="{{ old('username', $usernameSaran) }}"
                                    oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                    class="w-full rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] px-5 py-3.5 pr-11 text-sm font-semibold text-[#231815] shadow-[0_3px_12px_rgba(58,34,27,0.05)] outline-none focus:border-[#8E1E26] focus:bg-white focus:ring-4 focus:ring-[#8E1E26]/10">
                                <span id="username-icon" class="absolute inset-y-0 right-4 flex items-center text-lg font-bold"></span>
                            </div>
                            <p id="username-hint" class="mt-1 pl-3 text-[11px] text-[#8A766B]">Letters, numbers, dots, or underscores.</p>
                            @error('username')<p class="mt-1 pl-3 text-xs font-semibold text-[#8E1E26]">{{ $message }}</p>@enderror
                        </div>

                        <button type="button" id="btn-next" onclick="keLangkah2()"
                            class="w-full rounded-full bg-[#8E1E26] hover:bg-[#741A21] py-3.5 text-center text-sm font-extrabold text-[#FAF5E8] shadow-[0_10px_22px_-6px_rgba(142,30,38,0.52)] transition active:scale-[0.99] disabled:opacity-60">
                            Continue
                        </button>

                        <a href="{{ route('member.google.batal') }}"
                            class="block text-center text-xs font-bold text-[#7D6A60] hover:text-[#3A221B] hover:underline pt-1">Cancel</a>
                    </div>

                    {{-- STEP 2 --}}
                    <div id="step-2" class="space-y-4 hidden">
                        <div>
                            <label for="no_hp" class="block text-xs font-semibold text-[#6B574C] mb-1.5 pl-3">Mobile Phone Number</label>
                            <div class="relative flex items-center rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] px-3 py-2 shadow-[0_3px_12px_rgba(58,34,27,0.05)] focus-within:border-[#8E1E26] focus-within:ring-4 focus-within:ring-[#8E1E26]/10">
                                <div class="flex items-center gap-1 rounded-full bg-[#F4EAE0] px-3.5 py-1.5 text-sm font-bold text-[#3A221B] shrink-0">
                                    <span>+62</span>
                                </div>
                                <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}"
                                    required placeholder="882xxxxxxx"
                                    oninput="this.value = this.value.replace(/\D/g, '').replace(/^0+/, '')"
                                    class="ml-3 flex-1 bg-transparent text-sm font-semibold text-[#231815] outline-none placeholder:text-[#A6978D] placeholder:font-normal">
                                <button type="button" onclick="document.getElementById('no_hp').value=''"
                                    class="text-[#9A877B] hover:text-[#3A221B] p-1.5 mr-1">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            <p id="phone-error-text" class="mt-2 pl-3 text-xs text-[#8E1E26] font-semibold hidden"></p>
                            @error('no_hp')<p class="mt-2 pl-3 text-xs text-[#8E1E26] font-semibold">{{ $message }}</p>@enderror
                        </div>

                        <div class="rounded-2xl bg-[#FFFDF9] border border-[#3C2415]/12 p-4 text-xs text-[#5C4338] leading-relaxed flex items-start gap-3">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-5 h-5 shrink-0 mt-0.5">
                            <div>
                                <p class="font-bold text-[#3A221B]">Phone Number as Default Password</p>
                                <p class="mt-0.5 text-[#6B574C] text-[11.5px] leading-relaxed">
                                    Your registered mobile phone number will automatically serve as your account password (e.g. <span class="font-bold font-mono text-[#8E1E26]">088xxxxxxxx</span>).
                                </p>
                            </div>
                        </div>

                        <button type="button" id="btn-submit-daftar" onclick="submitDaftarGoogle()"
                            class="w-full rounded-full bg-[#8E1E26] hover:bg-[#741A21] py-3.5 text-center text-sm font-extrabold text-[#FAF5E8] shadow-[0_10px_22px_-6px_rgba(142,30,38,0.52)] transition active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>Create Member Account</span>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <button type="button" onclick="keLangkah1()"
                            class="block w-full text-center text-xs font-bold text-[#7D6A60] hover:text-[#3A221B] hover:underline pt-1">
                            &larr; Back to Step 1
                        </button>
                    </div>
                </form>
            </div>

            <div class="relative z-10 w-full max-w-[420px] mx-auto pt-3">
                <p class="text-center text-[11px] leading-relaxed text-[#9A877B]">
                    By creating an account, you agree to our
                    <a href="{{ route('member.konten.show', 'terms-conditions') }}" class="font-bold text-[#8E1E26] hover:underline">Terms &amp; Conditions</a>
                    and
                    <a href="{{ route('member.konten.show', 'privacy-policy') }}" class="font-bold text-[#8E1E26] hover:underline">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>

    <script>
        const mulaiDiLangkah2 = @json($errors->has('no_hp'));
        const input = document.getElementById('username');
        const icon = document.getElementById('username-icon');
        const hint = document.getElementById('username-hint');
        const btnNext = document.getElementById('btn-next');
        const url = @json(route('member.google.username'));
        let timer = null, seq = 0;

        function show(ok, msg) {
            icon.textContent = ok ? '✓' : '✕';
            icon.className = 'absolute inset-y-0 right-4 flex items-center text-lg font-bold ' + (ok ? 'text-[#2C5835]' : 'text-[#8E1E26]');
            hint.textContent = msg;
            hint.className = 'mt-1 pl-3 text-[11px] font-semibold ' + (ok ? 'text-[#2C5835]' : 'text-[#8E1E26]');
            btnNext.disabled = !ok;
        }

        function cek() {
            const value = input.value.trim();
            const mine = ++seq;
            if (!value) { icon.textContent = ''; return; }
            fetch(url + '?username=' + encodeURIComponent(value), { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(d => { if (mine === seq) show(d.available, d.message); })
                .catch(() => { if (mine === seq) { icon.textContent = ''; btnNext.disabled = false; } });
        }

        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(cek, 350); });

        function setLangkah(n) {
            document.getElementById('step-1').classList.toggle('hidden', n !== 1);
            document.getElementById('step-2').classList.toggle('hidden', n !== 2);
            document.getElementById('step-label').textContent = 'Step ' + n + ' of 2';
            document.getElementById('step-title').textContent = n === 1 ? 'Join Daisy Bakery Club' : 'Mobile Phone Number';
            document.getElementById('step-desc').textContent = n === 1
                ? 'No member account found for this Google account. Please review your details below to continue.'
                : 'Enter your phone number to complete registration. This number will also serve as your initial account password.';
            if (n === 2) document.getElementById('no_hp').focus();
        }

        function keLangkah1() { setLangkah(1); }

        function keLangkah2() {
            const nama = document.getElementById('nama').value.trim();
            const username = input.value.trim();
            if (!nama) { document.getElementById('nama').focus(); return; }
            if (username.length < 3 || btnNext.disabled) { input.focus(); return; }
            setLangkah(2);
        }

        function submitDaftarGoogle() {
            const noHpInput = document.getElementById('no_hp').value.trim();
            const errEl = document.getElementById('phone-error-text');
            const btnSubmit = document.getElementById('btn-submit-daftar');

            errEl.classList.add('hidden');
            if (!noHpInput) {
                errEl.textContent = 'Please enter your phone number first.';
                errEl.classList.remove('hidden');
                document.getElementById('no_hp').focus();
                return;
            }
            if (noHpInput.length < 8) {
                errEl.textContent = 'Phone number must be at least 8 digits.';
                errEl.classList.remove('hidden');
                document.getElementById('no_hp').focus();
                return;
            }
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span>Creating Account...</span>`;
            }
            document.getElementById('form-google-register').submit();
        }

        document.getElementById('form-google-register').addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (document.getElementById('step-2').classList.contains('hidden')) keLangkah2();
            else submitDaftarGoogle();
        });

        window.addEventListener('pageshow', () => {
            const card = document.getElementById('app-card');
            if (card) { card.style.opacity = ''; card.style.transform = ''; }
        });

        cek();
        if (mulaiDiLangkah2) setLangkah(2);
    </script>
@include('mobile.auth._google-native')
</body>

</html>
