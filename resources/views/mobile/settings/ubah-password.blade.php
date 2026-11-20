@extends('mobile.layout', ['title' => 'Change Password'])

@section('content')

    @php
        $langsungBuat = $belumPunyaPassword ?? false; // akun Google tanpa password
        $mulaiStep2 = $langsungBuat || $errors->has('password_baru');
    @endphp

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

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div class="lg:hidden" x-data="{
            step: {{ $mulaiStep2 ? 2 : 1 }},
            tanpaPasswordLama: @js($langsungBuat),
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
                @click="if (step === 2 && !tanpaPasswordLama) { gotoStep(1, 'back'); } else { window.location.href = '{{ route('member.settings.index') }}'; }"
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="m14 6-6 6 6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <h1 class="text-base font-extrabold text-brand-800">{{ $langsungBuat ? 'Buat Password' : 'Change Password' }}</h1>
            <span class="w-8"></span>
        </div>

        @unless ($langsungBuat)
        <div class="mt-6 flex items-center gap-2 px-0.5">
            <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 1 ? 'bg-brand-800' : 'bg-gray-100'"></span>
            <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 2 ? 'bg-brand-800' : 'bg-gray-100'"></span>
        </div>
        @endunless

        <div x-ref="step1" class="mt-7 {{ $mulaiStep2 ? 'hidden' : '' }}">
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

        <div x-ref="step2" class="mt-7 {{ $mulaiStep2 ? '' : 'hidden' }}">

            <form method="POST" action="{{ route('member.settings.ubah-password.update') }}" class="mt-5 space-y-4"
                @submit="menyimpan = true">
                @csrf
                @unless ($langsungBuat)
                    <input type="hidden" name="password_lama" :value="passwordLama">
                @endunless

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


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - FRENCH BAKERY PASSWORD HUB --}}
    {{-- ========================================== --}}
    <div id="password-desktop-root" class="hidden lg:block w-full bg-[#FAF5E8] min-h-[calc(100vh-140px)] text-[#3C2415] py-10 px-6 sm:px-8 lg:px-12"
        x-data="{
            step: {{ $mulaiStep2 ? 2 : 1 }},
            tanpaPasswordLama: @js($langsungBuat),
            passwordLama: @js(old('password_lama', '')),
            errorLama: '',
            memverifikasi: false,
            menyimpan: false,
            lihatPw: { lama: false, baru: false, konfirmasi: false },

            gotoStep(target, dir) {
                const fromEl = this.$refs['stepDesk' + this.step];
                const toEl = this.$refs['stepDesk' + target];

                if (dir === 'next') {
                    fromEl.className = 'step-exit-left';
                    setTimeout(() => {
                        fromEl.classList.add('hidden');
                        toEl.classList.remove('hidden');
                        toEl.className = 'step-enter-right';
                        this.step = target;
                    }, 180);
                } else {
                    fromEl.className = 'step-exit-right';
                    setTimeout(() => {
                        fromEl.classList.add('hidden');
                        toEl.classList.remove('hidden');
                        toEl.className = 'step-enter-left';
                        this.step = target;
                    }, 180);
                }
            },

            verifikasiPasswordLama() {
                this.errorLama = '';

                if (!this.passwordLama) {
                    this.errorLama = 'Please enter your current password.';
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
                        if (!response.ok) throw new Error(data.message || 'Incorrect current password.');
                        return data;
                    })
                    .then(data => {
                        this.memverifikasi = false;
                        if (data.success) {
                            this.gotoStep(2, 'next');
                        } else {
                            this.errorLama = data.message || 'Incorrect current password.';
                        }
                    })
                    .catch(error => {
                        this.memverifikasi = false;
                        this.errorLama = error.message || 'An error occurred. Please try again.';
                    });
            }
        }">
        <div class="max-w-4xl mx-auto space-y-8">

            {{-- Top Navigation & Metadata Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.settings.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Back to Settings</span>
                </a>

                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#8E1E26]/10 text-[#8E1E26] font-bold uppercase tracking-wider text-[11px]">
                        SECURITY & CREDENTIALS
                    </span>
                    <span class="text-[#3C2415]/40">&bull;</span>
                    <span class="text-[#3C2415]/60 font-medium">Bcrypt Encrypted</span>
                </div>
            </div>

            {{-- Clean Page Header (NO HERO SECTION) --}}
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#3C2415]/5 border border-[#3C2415]/10 text-xs font-bold uppercase tracking-wider text-[#8E1E26]">
                    ACCOUNT SECURITY
                </div>
                <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                    {{ $langsungBuat ? 'Create Password' : 'Change Password' }}
                </h1>
                <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                    Update your secret password regularly to keep your member account, order history, and accumulated points secure.
                </p>
            </div>

            {{-- 2-Column Responsive Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Left Column: Security Tips & Recommendations --}}
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                    <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                    <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-display font-bold text-sm uppercase tracking-tight text-[#3C2415]">Password Standards</h3>
                                <p class="text-[11px] text-[#8E1E26] font-semibold">Account Safeguards</p>
                            </div>
                        </div>

                        <div class="space-y-2.5 text-xs text-[#3C2415]/80 pt-2 border-t border-[#3C2415]/10">
                            <div class="flex items-start gap-2.5">
                                <span class="text-[#8E1E26] font-bold">✓</span>
                                <span>Minimum 8 characters in length</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="text-[#8E1E26] font-bold">✓</span>
                                <span>Combine upper & lower case letters</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="text-[#8E1E26] font-bold">✓</span>
                                <span>Include at least one number or symbol</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="text-[#8E1E26] font-bold">✓</span>
                                <span>Avoid repeating simple words or birthdays</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#3C2415]/10">
                            <p class="text-[11px] text-[#3C2415]/65 leading-relaxed">
                                Our staff will never request your password via phone, email, or WhatsApp.
                            </p>
                        </div>
                    </div>

                    {{-- Concierge Help Box --}}
                    <div class="rounded-2xl bg-white p-5 border border-[#3C2415]/10 shadow-xs space-y-2">
                        <div class="text-xs font-bold text-[#3C2415] uppercase tracking-wide">Forgot Current Password?</div>
                        <p class="text-[11px] text-[#3C2415]/70 leading-relaxed">
                            If you no longer recall your current password, contact our Bakery Concierge for identity verification and recovery.
                        </p>
                        <a href="https://wa.me/6281313365554" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#8E1E26] hover:underline pt-1">
                            Contact Concierge &rarr;
                        </a>
                    </div>
                </div>

                {{-- Right Column: Step Form Card --}}
                <div class="lg:col-span-7">
                    <div class="rounded-[28px] bg-[#F5ECE3] p-8 border border-[#3C2415]/15 shadow-sm space-y-6">
                        @unless ($langsungBuat)
                            {{-- Step Indicator Bar --}}
                            <div class="space-y-2 border-b border-[#3C2415]/10 pb-5">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span :class="step >= 1 ? 'text-[#8E1E26]' : 'text-[#3C2415]/40'">1. Current Password</span>
                                    <span :class="step >= 2 ? 'text-[#8E1E26]' : 'text-[#3C2415]/40'">2. New Password</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 1 ? 'bg-[#8E1E26]' : 'bg-[#3C2415]/15'"></span>
                                    <span class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="step >= 2 ? 'bg-[#8E1E26]' : 'bg-[#3C2415]/15'"></span>
                                </div>
                            </div>
                        @endunless

                        {{-- ==================== STEP 1: CURRENT PASSWORD ==================== --}}
                        <div x-ref="stepDesk1" class="{{ $mulaiStep2 ? 'hidden' : '' }} space-y-5">
                            <div>
                                <h3 class="font-display font-bold text-base text-[#3C2415]">Confirm Identity</h3>
                                <p class="text-xs text-[#3C2415]/70 mt-0.5">Please provide your current password to continue.</p>
                            </div>

                            <div>
                                <label for="password_lama_desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">Current Password</label>
                                <div class="relative">
                                    <input :type="lihatPw.lama ? 'text' : 'password'" id="password_lama_desk" x-model="passwordLama"
                                        @keyup.enter="verifikasiPasswordLama()"
                                        placeholder="Enter current password"
                                        class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3.5 pr-11 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10 shadow-xs">
                                    <button type="button" @click="lihatPw.lama = ! lihatPw.lama" tabindex="-1"
                                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#3C2415]/40 hover:text-[#3C2415] cursor-pointer">
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
                                <p class="mt-1.5 text-xs font-semibold text-red-600" x-show="errorLama" x-text="errorLama" x-cloak></p>
                            </div>

                            <div class="pt-4 border-t border-[#3C2415]/10 flex justify-end">
                                <button type="button" @click="verifikasiPasswordLama()" :disabled="memverifikasi"
                                    class="rounded-xl bg-[#3C2415] hover:bg-[#8E1E26] px-8 py-3.5 text-xs font-extrabold uppercase tracking-wider text-[#FAF5E8] shadow-md transition active:scale-95 disabled:opacity-60 cursor-pointer">
                                    <span x-show="!memverifikasi">Continue &rarr;</span>
                                    <span x-show="memverifikasi" x-cloak>Verifying...</span>
                                </button>
                            </div>
                        </div>

                        {{-- ==================== STEP 2: NEW PASSWORD ==================== --}}
                        <div x-ref="stepDesk2" class="{{ $mulaiStep2 ? '' : 'hidden' }}">
                            <form method="POST" action="{{ route('member.settings.ubah-password.update') }}" class="space-y-5"
                                @submit="menyimpan = true">
                                @csrf
                                @unless ($langsungBuat)
                                    <input type="hidden" name="password_lama" :value="passwordLama">
                                @endunless

                                <div>
                                    <h3 class="font-display font-bold text-base text-[#3C2415]">Set New Password</h3>
                                    <p class="text-xs text-[#3C2415]/70 mt-0.5">Choose a secure password meeting all guidelines.</p>
                                </div>

                                <div>
                                    <label for="password_baru_desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">New Password</label>
                                    <div class="relative">
                                        <input :type="lihatPw.baru ? 'text' : 'password'" name="password_baru" id="password_baru_desk"
                                            placeholder="Enter new password"
                                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3.5 pr-11 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10 shadow-xs">
                                        <button type="button" @click="lihatPw.baru = ! lihatPw.baru" tabindex="-1"
                                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#3C2415]/40 hover:text-[#3C2415] cursor-pointer">
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
                                        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_baru_confirmation_desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">Confirm New Password</label>
                                    <div class="relative">
                                        <input :type="lihatPw.konfirmasi ? 'text' : 'password'" name="password_baru_confirmation" id="password_baru_confirmation_desk"
                                            placeholder="Retype new password"
                                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3.5 pr-11 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10 shadow-xs">
                                        <button type="button" @click="lihatPw.konfirmasi = ! lihatPw.konfirmasi" tabindex="-1"
                                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-[#3C2415]/40 hover:text-[#3C2415] cursor-pointer">
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
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror

                                <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                    @unless ($langsungBuat)
                                        <button type="button" @click="gotoStep(1, 'back')"
                                            class="text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition cursor-pointer">
                                            &larr; Back to Step 1
                                        </button>
                                    @else
                                        <span></span>
                                    @endunless
                                    <button type="submit" :disabled="menyimpan"
                                        class="rounded-xl bg-[#3C2415] hover:bg-[#8E1E26] px-8 py-3.5 text-xs font-extrabold uppercase tracking-wider text-[#FAF5E8] shadow-md transition active:scale-95 disabled:opacity-60 cursor-pointer">
                                        <span x-show="!menyimpan">Save New Password</span>
                                        <span x-show="menyimpan" x-cloak>Saving...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
