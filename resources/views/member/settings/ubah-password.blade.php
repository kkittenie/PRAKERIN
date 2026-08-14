@extends('member.layout', ['title' => 'Change Password'])

@section('content')

    <div x-data="{
            step: {{ $errors->has('password_baru') ? 2 : 1 }},
            passwordLama: '',
            errorLama: '',
            memverifikasi: false,
            menyimpan: false,

            verifikasiPasswordLama() {
                this.errorLama = '';

                if (!this.passwordLama) {
                    this.errorLama = 'Masukkan password lama kamu.';
                    return;
                }

                this.memverifikasi = true;

                fetch('{{ route('member.settings.verifikasi-password-lama') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                    },
                    body: JSON.stringify({ password_lama: this.passwordLama })
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Password lama salah.');
                        return data;
                    })
                    .then(data => {
                        this.memverifikasi = false;
                        if (data.success) {
                            this.step = 2;
                        } else {
                            this.errorLama = data.message || 'Password lama salah.';
                        }
                    })
                    .catch(error => {
                        this.memverifikasi = false;
                        this.errorLama = error.message || 'Terjadi kesalahan.';
                    });
            }
        }">

        <div class="-mt-6 flex flex-none items-center justify-between px-0 pt-6">
            <button type="button"
                @click="if (step === 2) { step = 1; } else { window.location.href = '{{ route('member.settings.index') }}'; }"
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="m14 6-6 6 6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <h1 class="text-base font-extrabold text-brand-800">Change Password</h1>
            <span class="w-8"></span>
        </div>

        {{-- Step indicator --}}
        <div class="mt-6 flex items-center gap-2 px-0.5">
            <span class="h-1.5 flex-1 rounded-full" :class="step >= 1 ? 'bg-brand-800' : 'bg-gray-100'"></span>
            <span class="h-1.5 flex-1 rounded-full" :class="step >= 2 ? 'bg-brand-800' : 'bg-gray-100'"></span>
        </div>

        {{-- ==================== STEP 1: PASSWORD LAMA ==================== --}}
        <div x-show="step === 1" x-cloak class="mt-7">
            <p class="text-sm text-gray-500">Masukkan password lama kamu untuk melanjutkan.</p>

            <div class="mt-5">
                <label for="password_lama" class="text-xs font-semibold text-gray-500">Password Lama</label>
                <input type="password" id="password_lama" x-model="passwordLama"
                    @keyup.enter="verifikasiPasswordLama()"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                <p class="mt-1.5 text-xs text-red-600" x-show="errorLama" x-text="errorLama" x-cloak></p>
            </div>

            <button type="button" @click="verifikasiPasswordLama()" :disabled="memverifikasi"
                class="mt-7 w-full rounded-lg bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white disabled:opacity-60">
                <span x-show="!memverifikasi">Continue</span>
                <span x-show="memverifikasi" x-cloak>Memeriksa...</span>
            </button>
        </div>

        {{-- ==================== STEP 2: PASSWORD BARU ==================== --}}
        <div x-show="step === 2" x-cloak class="mt-7">
            <p class="text-sm text-gray-500">Password lama terverifikasi. Sekarang buat password baru kamu.</p>

            <form method="POST" action="{{ route('member.settings.ubah-password.update') }}" class="mt-5 space-y-4"
                @submit="menyimpan = true">
                @csrf
                <input type="hidden" name="password_lama" :value="passwordLama">

                <div>
                    <label for="password_baru" class="text-xs font-semibold text-gray-500">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                    @error('password_baru')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_baru_confirmation" class="text-xs font-semibold text-gray-500">Ulangi Password Baru</label>
                    <input type="password" name="password_baru_confirmation" id="password_baru_confirmation"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                </div>

                @error('password_lama')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" :disabled="menyimpan"
                    class="w-full rounded-lg bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white disabled:opacity-60">
                    <span x-show="!menyimpan">Save New Password</span>
                    <span x-show="menyimpan" x-cloak>Menyimpan...</span>
                </button>
            </form>
        </div>

    </div>

@endsection