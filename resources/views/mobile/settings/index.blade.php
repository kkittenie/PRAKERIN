@extends('mobile.layout', ['title' => 'Settings'])

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        <div class="-mt-6 flex flex-none items-center justify-between px-0 pt-6">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
            <h1 class="text-base font-extrabold text-brand-800">Settings</h1>
            <span class="w-8"></span>
        </div>

        <div class="mt-6 space-y-3">
            <a href="{{ route('member.settings.ubah-password') }}"
                onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 text-sm font-medium text-gray-800 shadow-xs transition active:scale-[0.99] hover:text-brand-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-25 text-brand-800">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="1.8" />
                            <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </span>
                    <span>Change Password</span>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400">
                    <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - FRENCH BAKERY SETTINGS HUB --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block lg:max-w-2xl lg:mx-auto lg:my-10 bg-[#F5ECE3] rounded-[32px] border border-[#3C2415]/15 shadow-sm p-8 text-[#3C2415]">
        {{-- Desktop Header Bar --}}
        <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5 mb-6">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Account
            </a>
            <h1 class="text-xs font-extrabold uppercase tracking-widest text-[#8E1E26]">Account Settings</h1>
        </div>

        <div class="space-y-4">
            <a href="{{ route('member.settings.ubah-password') }}"
                onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                class="group flex items-center justify-between rounded-2xl border border-[#3C2415]/15 bg-white p-5 shadow-xs transition hover:border-[#8E1E26]/40 hover:shadow-md hover:text-[#8E1E26] active:scale-[0.99]">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] group-hover:bg-[#8E1E26] transition shadow-xs">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2" />
                            <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div>
                        <span class="block text-sm font-extrabold text-[#3C2415] group-hover:text-[#8E1E26] transition">Change Password</span>
                        <span class="text-xs text-[#3C2415]/65">Update your password to keep your member account secure</span>
                    </div>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-[#3C2415]/40 group-hover:text-[#8E1E26] transition transform group-hover:translate-x-0.5">
                    <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    </div>

@endsection