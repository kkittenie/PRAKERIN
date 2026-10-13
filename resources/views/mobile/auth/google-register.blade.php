<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Pendaftaran &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media (min-width: 768px) {
            #app-card {
                display: grid;
                grid-template-columns: 1fr 1fr;
                align-items: stretch;
                min-height: 100vh;
                height: 100vh;
                max-width: 100% !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body class="h-full font-outfit antialiased overflow-x-hidden md:min-h-screen md:h-screen md:w-full">
    <div id="app-card"
        class="mx-auto flex min-h-screen w-full flex-col bg-white px-6 sm:px-10 pb-8 pt-6 shadow-2xl md:grid md:grid-cols-2 md:min-h-screen md:h-screen md:px-0 md:py-0 md:shadow-none">

        {{-- Kiri: banner (desktop) --}}
        <div class="hidden md:flex relative h-full w-full overflow-hidden bg-[#5a3c27]">
            <img src="{{ asset('images/welcome-slide-3.png') }}" alt="Saisho Member Registration"
                class="absolute inset-0 w-full h-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-t from-[#2d1a0e] via-[#5a3c27]/70 to-black/30"></div>
            <div class="relative z-10 flex flex-col justify-end p-12 text-white h-full">
                <div class="space-y-2">
                    <h2 class="text-3xl font-extrabold tracking-tight drop-shadow-md">Satu langkah lagi!</h2>
                    <p class="text-sm text-white/90 max-w-md leading-relaxed drop-shadow">
                        Akun Google kamu belum terhubung ke member Saisho. Lengkapi data singkat ini untuk mulai
                        mengumpulkan poin reward dan stamp spesial.
                    </p>
                </div>
            </div>
        </div>

        {{-- Kanan: form --}}
        <div class="w-full flex flex-col justify-between md:h-full md:justify-center md:px-12 lg:px-20 py-8 bg-white flex-1">
            <div class="max-w-md mx-auto w-full">

                <div class="mb-6">
                    <p id="step-label" class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Langkah 1 dari 2</p>
                    <h1 id="step-title" class="text-xl font-bold text-brand-800">Daftar ke Saisho</h1>
                    <p id="step-desc" class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Belum ada akun member untuk akun Google ini. Periksa data di bawah lalu lanjut.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 flex items-start gap-2 rounded-xl bg-red-50 p-3.5 text-xs text-red-700">
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('member.google.register.store') }}" class="space-y-5" id="form-google-register">
                    @csrf

                    {{-- ============ LANGKAH 1: data akun ============ --}}
                    <div id="step-1" class="space-y-5">
                    {{-- Email dari Google (terkunci) --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Email</label>
                        <input type="email" value="{{ $google['email'] }}" readonly disabled
                            class="w-full rounded-2xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm text-gray-500 cursor-not-allowed">
                        <a href="{{ route('member.google.redirect', ['pilih_akun' => 1]) }}"
                            class="mt-1 inline-block text-xs font-semibold text-brand-800 hover:underline">
                            Gunakan akun Google lain
                        </a>
                    </div>

                    {{-- Nama (terisi dari Google, boleh diubah) --}}
                    <div>
                        <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Nama Lengkap</label>
                        <input id="nama" name="nama" type="text" required maxlength="255"
                            value="{{ old('nama', $google['nama']) }}"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                        @error('nama')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Username (disarankan, dicek ketersediaannya) --}}
                    <div>
                        <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Username</label>
                        <div class="relative">
                            <input id="username" name="username" type="text" required minlength="3" maxlength="50"
                                autocomplete="off" value="{{ old('username', $usernameSaran) }}"
                                oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 pr-11 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                            <span id="username-icon" class="absolute inset-y-0 right-3 flex items-center text-lg font-bold"></span>
                        </div>
                        <p id="username-hint" class="mt-1 text-[11px] text-gray-400">Huruf, angka, titik, atau underscore.</p>
                        @error('username')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <button type="button" id="btn-next" onclick="keLangkah2()"
                        class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99] disabled:opacity-60">
                        Lanjut
                    </button>

                    <a href="{{ route('member.google.batal') }}"
                        class="block text-center text-xs font-semibold text-gray-500 hover:underline">Batal</a>
                    </div>

                    {{-- ============ LANGKAH 2: nomor HP ============ --}}
                    <div id="step-2" class="space-y-5 hidden">
                        {{-- Nomor HP --}}
                        <div>
                            <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                                Nomor Handphone
                            </label>
                            <div class="relative flex items-center border-b-2 border-brand-800 pb-2 pt-1 focus-within:border-brand-800">
                                <div class="flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-800 shrink-0">
                                    <span>+62</span>
                                </div>
                                <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}"
                                    required placeholder="882xxxxxxx"
                                    oninput="this.value = this.value.replace(/\D/g, '').replace(/^0+/, '')"
                                    class="ml-3 flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                                <button type="button" onclick="document.getElementById('no_hp').value=''"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            <p id="phone-error-text" class="mt-2 text-xs text-red-500 font-medium hidden"></p>
                            @error('no_hp')<p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p>@enderror
                        </div>

                        {{-- Info Nomor HP Sebagai Password --}}
                        <div class="rounded-2xl bg-amber-50/90 border border-amber-200/80 p-3.5 text-xs text-amber-900 leading-relaxed flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div>
                                <p class="font-bold text-amber-950">Nomor HP Sebagai Kata Sandi</p>
                                <p class="mt-0.5 text-amber-800/90 text-[11px] leading-relaxed">
                                    Nomor HP yang kamu daftarkan akan otomatis menjadi kata sandi akunmu (contoh: <span class="font-bold font-mono">088xxxxxxxx</span>). Kamu dapat menggunakannya saat masuk dengan Username atau No HP.
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Selesaikan Pendaftaran (Tanpa OTP) --}}
                        <div>
                            <button type="button" id="btn-submit-daftar" onclick="submitDaftarGoogle()"
                                class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99] flex items-center justify-center gap-2">
                                <span>Buat Akun Member</span>
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <button type="button" onclick="keLangkah1()"
                            class="block w-full text-center text-xs font-semibold text-gray-500 hover:underline pt-1">
                            &larr; Kembali ke Langkah 1
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 max-w-md mx-auto w-full">
                <p class="text-center text-xs leading-relaxed text-gray-500">
                    Dengan membuat akun, kamu menyetujui
                    <a href="{{ route('member.konten.show', 'terms-conditions') }}" class="font-bold text-brand-800 hover:underline">Terms & Condition</a>
                    dan
                    <a href="{{ route('member.konten.show', 'privacy-policy') }}" class="font-bold text-brand-800 hover:underline">Privacy Policy</a>.
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

        // ---- cek username langsung (centang hijau ala GitHub) ----
        function show(ok, msg) {
            icon.textContent = ok ? '✓' : '✕';
            icon.className = 'absolute inset-y-0 right-3 flex items-center text-lg font-bold ' + (ok ? 'text-green-600' : 'text-red-500');
            hint.textContent = msg;
            hint.className = 'mt-1 text-[11px] ' + (ok ? 'text-green-600' : 'text-red-500');
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

        // ---- langkah ----
        function setLangkah(n) {
            document.getElementById('step-1').classList.toggle('hidden', n !== 1);
            document.getElementById('step-2').classList.toggle('hidden', n !== 2);
            document.getElementById('step-label').textContent = 'Langkah ' + n + ' dari 2';
            document.getElementById('step-title').textContent = n === 1 ? 'Daftar ke Saisho' : 'Nomor Handphone';
            document.getElementById('step-desc').textContent = n === 1
                ? 'Belum ada akun member untuk akun Google ini. Periksa data di bawah lalu lanjut.'
                : 'Masukkan nomor HP kamu untuk melengkapi pendaftaran. Nomor HP ini juga akan menjadi kata sandi awal akun kamu.';
            if (n === 2) {
                document.getElementById('no_hp').focus();
            }
        }

        function keLangkah1() { setLangkah(1); }

        function keLangkah2() {
            const nama = document.getElementById('nama').value.trim();
            const username = input.value.trim();
            if (!nama) { document.getElementById('nama').focus(); return; }
            if (username.length < 3 || btnNext.disabled) { input.focus(); return; }
            setLangkah(2);
        }

        // ---- Submit Pendaftaran Google (Tanpa OTP) ----
        function submitDaftarGoogle() {
            const noHpInput = document.getElementById('no_hp').value.trim();
            const errEl = document.getElementById('phone-error-text');
            const btnSubmit = document.getElementById('btn-submit-daftar');

            errEl.classList.add('hidden');

            if (!noHpInput) {
                errEl.textContent = 'Masukkan nomor HP kamu terlebih dahulu.';
                errEl.classList.remove('hidden');
                document.getElementById('no_hp').focus();
                return;
            }

            if (noHpInput.length < 8) {
                errEl.textContent = 'Nomor HP minimal 8 digit.';
                errEl.classList.remove('hidden');
                document.getElementById('no_hp').focus();
                return;
            }

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Mendaftarkan Akun...</span>
                `;
            }

            document.getElementById('form-google-register').submit();
        }

        // Enter di langkah 1 -> lanjut, di langkah 2 -> submit
        document.getElementById('form-google-register').addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (document.getElementById('step-2').classList.contains('hidden')) {
                keLangkah2();
            } else {
                submitDaftarGoogle();
            }
        });

        cek();
        if (mulaiDiLangkah2) setLangkah(2);
    </script>
</body>

</html>