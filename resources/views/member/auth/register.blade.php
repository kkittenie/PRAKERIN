<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-outfit antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-white px-6 pb-8 pt-4 shadow-[0_0_60px_-15px_rgba(90,60,39,0.15)]">
        
        {{-- Header Bar --}}
        <div class="relative flex items-center justify-center py-2">
            <a href="{{ route('member.login') }}" class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <h1 class="text-base font-bold text-brand-800">Register Member</h1>
        </div>

        {{-- Flash messages --}}
        @if (session('error') || $errors->any())
            <div class="mt-4 flex items-start gap-2 rounded-xl bg-red-50 p-3.5 text-xs text-red-700">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 mt-0.5"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Form --}}
        <div class="mt-6 flex-1">
            <h2 class="text-lg font-bold text-brand-800">Create Account</h2>
            <p class="text-xs text-gray-500 mt-1">Dapatkan poin, reward menarik & voucher belanja gratis di Saisho.</p>

            <form id="form-register" method="POST" action="{{ route('member.register.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" id="otp-input-hidden" name="otp" value="">

                <div>
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Nama Lengkap</label>
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required placeholder="Contoh: Budi Santoso"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                    @error('nama') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Nomor HP</label>
                    <div class="relative flex items-center border-b-2 border-brand-800 pb-1.5 pt-1">
                        <div class="flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                            <span>+62</span>
                        </div>
                        <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}" required placeholder="882000768044"
                               class="ml-3 flex-1 bg-transparent text-sm font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                    </div>
                    @error('no_hp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Kata Sandi</label>
                    <input id="password" name="password" type="password" required minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6" placeholder="Ulangi kata sandi"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                </div>

                <button type="button" onclick="mintaOtp()" class="w-full mt-4 rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99]">
                    Kirim Kode OTP
                </button>
            </form>
        </div>

        {{-- Bottom Login Link --}}
        <p class="text-center text-xs leading-relaxed text-gray-500 mt-6">
            Sudah punya akun member? <a href="{{ route('member.login') }}" class="font-bold text-brand-800 hover:underline">Masuk di sini</a>
        </p>

    </div>

    {{-- OTP Modal Overlay --}}
    <dialog id="modal-otp" class="fixed inset-0 my-auto w-[calc(100%-2rem)] max-w-sm rounded-3xl p-0 backdrop:bg-gray-900/60 shadow-2xl border-0">
        <div class="p-6 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 18v-6m0 0V6m0 6h6m-6 0H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            
            <h3 class="mt-3 text-lg font-bold text-gray-900">Verifikasi Kode OTP</h3>
            <p id="otp-phone-text" class="mt-1 text-xs text-gray-500">Kode OTP telah dikirimkan via SMS/WA ke nomor Anda.</p>

            {{-- Demo OTP Notice --}}
            <div id="otp-demo-badge" class="my-3 rounded-xl bg-amber-50 border border-amber-200 p-2.5 text-xs text-amber-800 font-semibold">
                Kode OTP Demo: <span id="otp-code-demo" class="text-base font-bold text-brand-800 tracking-widest">123456</span>
            </div>

            <div class="mt-4">
                <input id="otp-input-code" type="text" maxlength="6" inputmode="numeric" placeholder="123456" autofocus
                       class="w-full text-center tracking-[0.5em] text-2xl font-bold rounded-2xl border-2 border-brand-800 py-3 text-gray-900 outline-none focus:ring-4 focus:ring-brand-800/10">
                <p id="otp-error-text" class="mt-1 text-xs text-red-500 hidden font-medium"></p>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span>Waktu tersisa: <strong id="timer-text">60s</strong></span>
                <button type="button" id="btn-resend-otp" onclick="mintaOtp()" class="font-bold text-brand-800 disabled:opacity-50" disabled>Kirim Ulang</button>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <button type="button" onclick="verifikasiDanDaftar()" class="w-full rounded-2xl bg-brand-800 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-900">
                    Verifikasi & Daftar
                </button>
                <button type="button" onclick="document.getElementById('modal-otp').close()" class="py-2 text-xs font-semibold text-gray-500 hover:text-gray-700">
                    Batal
                </button>
            </div>
        </div>
    </dialog>

    <script>
        let countdownTimer = null;

        function mintaOtp() {
            const nama = document.getElementById('nama').value.trim();
            const noHp = document.getElementById('no_hp').value.trim();
            const password = document.getElementById('password').value;
            const passwordConf = document.getElementById('password_confirmation').value;

            if (!nama || !noHp || !password) {
                alert('Silakan lengkapi semua kolom pendaftaran terlebih dahulu.');
                return;
            }

            if (password !== passwordConf) {
                alert('Konfirmasi kata sandi tidak cocok.');
                return;
            }

            fetch('{{ route("member.otp.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ no_hp: noHp })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otp-phone-text').innerText = data.message;
                    document.getElementById('otp-code-demo').innerText = data.otp || '123456';
                    document.getElementById('modal-otp').showModal();
                    startCountdown();
                } else {
                    alert(data.message || 'Gagal mengirim OTP');
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('otp-code-demo').innerText = '123456';
                document.getElementById('modal-otp').showModal();
                startCountdown();
            });
        }

        function startCountdown() {
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

        function verifikasiDanDaftar() {
            const otpVal = document.getElementById('otp-input-code').value.trim();
            if (!otpVal) {
                document.getElementById('otp-error-text').innerText = 'Masukkan kode OTP terlebih dahulu';
                document.getElementById('otp-error-text').classList.remove('hidden');
                return;
            }
            document.getElementById('otp-input-hidden').value = otpVal;
            document.getElementById('form-register').submit();
        }
    </script>
</body>
</html>
