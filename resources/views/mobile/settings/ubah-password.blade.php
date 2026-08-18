@extends('mobile.layout', ['title' => 'Change Password'])

@section('content')

    <style>
        @keyframes stepEnterFromRight {
            0% { opacity: 0; transform: translateX(24px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes stepEnterFromLeft {
            0% { opacity: 0; transform: translateX(-24px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes stepExitToLeft {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(-24px); }
        }
        @keyframes stepExitToRight {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(24px); }
        }
        .step-enter-right { animation: stepEnterFromRight 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .step-enter-left { animation: stepEnterFromLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .step-exit-left { animation: stepExitToLeft 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .step-exit-right { animation: stepExitToRight 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
    </style>

    <div x-data="{
            step: {{ $errors->has('password_baru') ? 2 : 1 }},
            passwordLama: @js(old('password_lama', '')),
            errorLama: '',
            memverifikasi: false,
            menyimpan: false,
            lihatPw: { lama: false, baru: false, konfirmasi: false },

            gotoStep(target, dir) {
                const fromEl = this.$refs['step' + this.step];
                const toEl = this.$refs['step' + target];

                if (dir === 'next') {
                    fromEl.className = 'mt-7 step-exit-left';
                    setTimeout(() => {
                        fromEl.classList.add('hidden');
                        toEl.classList.remove('hidden');
                        toEl.className = 'mt-7 step-enter-right';
                        this.step = target;
                    }, 180);
                } else {
                    fromEl.className = 'mt-7 step-exit-right';
                    setTimeout(() => {
                        fromEl.classList.add('hidden');
                        toEl.classList.remove('hidden');
                        toEl.className = 'mt-7 step-enter-left';
                        this.step = target;
                    }, 180);
                }
            },

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
                            this.gotoStep(2, 'next');
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
                @click="if (step === 2) { gotoStep(1, 'back'); } else { window.location.href = '{{ route('member.settings.index') }}'; }"
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
            <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 1 ? 'bg-brand-800' : 'bg-gray-100'"></span>
            <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 2 ? 'bg-brand-800' : 'bg-gray-100'"></span>
        </div>

        {{-- ==================== STEP 1: PASSWORD LAMA ==================== --}}
        <div x-ref="step1" class="mt-7 {{ $errors->has('password_baru') ? 'hidden' : '' }}">
            <p class="text-sm text-gray-500">Masukkan password lama kamu untuk melanjutkan.</p>

            <div class="mt-5">
                <label for="password_lama" class="text-xs font-semibold text-gray-500">Password Lama</label>
                <div class="relative mt-1.5">
                    <input :type="lihatPw.lama ? 'text' : 'password'" id="password_lama" x-model="passwordLama"
                        @keyup.enter="verifikasiPasswordLama()"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pr-11 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                    <button type="button" @click="lihatPw.lama = ! lihatPw.lama" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                        <svg x-show="! lihatPw.lama" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <svg x-show="lihatPw.lama" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M3 3l18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.36 5.16A9.9 9.9 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.06 4.06M6.6 6.6C4.14 8.1 2 12 2 12a17.7 17.7 0 0 0 5.06 5.94A9.9 9.9 0 0 0 12 19"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <p class="mt-1.5 text-xs text-red-600" x-show="errorLama" x-text="errorLama" x-cloak></p>
            </div>

            <button type="button" @click="verifikasiPasswordLama()" :disabled="memverifikasi"
                class="mt-7 w-full rounded-lg bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white disabled:opacity-60">
                <span x-show="!memverifikasi">Continue</span>
                <span x-show="memverifikasi" x-cloak>Memeriksa...</span>
            </button>
        </div>

        {{-- ==================== STEP 2: PASSWORD BARU ==================== --}}
        <div x-ref="step2" class="mt-7 {{ $errors->has('password_baru') ? '' : 'hidden' }}">
            <p class="text-sm text-gray-500">Password lama terverifikasi. Sekarang buat password baru kamu.</p>

            <form method="POST" action="{{ route('member.settings.ubah-password.update') }}" class="mt-5 space-y-4"
                @submit="menyimpan = true">
                @csrf
                <input type="hidden" name="password_lama" :value="passwordLama">

                <div>
                    <label for="password_baru" class="text-xs font-semibold text-gray-500">Password Baru</label>
                    <div class="relative mt-1.5">
                        <input :type="lihatPw.baru ? 'text' : 'password'" name="password_baru" id="password_baru"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pr-11 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                        <button type="button" @click="lihatPw.baru = ! lihatPw.baru" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                            <svg x-show="! lihatPw.baru" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                            <svg x-show="lihatPw.baru" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M3 3l18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.36 5.16A9.9 9.9 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.06 4.06M6.6 6.6C4.14 8.1 2 12 2 12a17.7 17.7 0 0 0 5.06 5.94A9.9 9.9 0 0 0 12 19"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_baru')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_baru_confirmation" class="text-xs font-semibold text-gray-500">Ulangi Password Baru</label>
                    <div class="relative mt-1.5">
                        <input :type="lihatPw.konfirmasi ? 'text' : 'password'" name="password_baru_confirmation" id="password_baru_confirmation"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pr-11 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                        <button type="button" @click="lihatPw.konfirmasi = ! lihatPw.konfirmasi" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 hover:text-gray-600">
                            <svg x-show="! lihatPw.konfirmasi" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                            <svg x-show="lihatPw.konfirmasi" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M3 3l18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.36 5.16A9.9 9.9 0 0 1 12 5c6.5 0 10 7 10 7a17.5 17.5 0 0 1-3.06 4.06M6.6 6.6C4.14 8.1 2 12 2 12a17.7 17.7 0 0 0 5.06 5.94A9.9 9.9 0 0 0 12 19"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
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