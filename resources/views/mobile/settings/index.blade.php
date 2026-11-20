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
    <style>
        @keyframes floatStrawberry {
            0%, 100% {
                transform: translateY(0px) rotate(var(--rot, 0deg));
            }
            50% {
                transform: translateY(-12px) rotate(calc(var(--rot, 0deg) + 4deg));
            }
        }
        .desktop-floating-strawberry {
            animation: floatStrawberry var(--duration, 4.5s) ease-in-out infinite;
            will-change: transform;
        }
    </style>

    <div id="settings-desktop-root" class="hidden lg:block min-h-screen relative overflow-hidden bg-[#FAF5E8] text-[#3C2415] pb-24">
        {{-- Floating Strawberries --}}
        <div class="desktop-floating-strawberry absolute left-6 xl:left-12 2xl:left-20 top-20 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: -8deg; --duration: 4.8s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry absolute right-6 xl:right-12 2xl:right-20 top-24 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 48px; height: 64px; --rot: 10deg; --duration: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        <div class="relative max-w-4xl mx-auto px-6 lg:px-8 pt-8 z-10 space-y-8">
            {{-- Top Navigation Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to Account
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#8E1E26]/10 text-[#8E1E26] text-[11px] font-bold uppercase tracking-wider">
                    SECURITY & SETTINGS
                </span>
            </div>

            {{-- Header --}}
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#8E1E26]/10 border border-[#8E1E26]/20 text-[11px] font-black uppercase tracking-wider text-[#8E1E26]">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 object-contain">
                    <span>MEMBER SECURITY</span>
                </div>
                <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                    Account Settings
                </h1>
                <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                    Update your password and manage security preferences for your Daisy Bakery membership.
                </p>
            </div>

            {{-- 2-Column Responsive Bento Layout --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                {{-- Left Column: Security Status & Concierge --}}
                <div class="md:col-span-5 space-y-5">
                    <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <h3 class="font-display font-bold text-sm text-[#3C2415]">Member Protection</h3>
                                <p class="text-[11px] text-[#3C2415]/65">Active SSL & Password Encryption</p>
                            </div>
                        </div>
                        <p class="text-xs text-[#3C2415]/70 leading-relaxed">
                            Your personal information, reward points, and order history are securely safeguarded.
                        </p>
                    </div>

                    <div class="rounded-[28px] bg-white p-6 border border-[#3C2415]/10 shadow-xs space-y-2">
                        <div class="text-xs font-bold text-[#3C2415] uppercase tracking-wide">Need Support?</div>
                        <p class="text-xs text-[#3C2415]/70 leading-relaxed">
                            Have questions or trouble accessing your account? Our concierge is ready to help.
                        </p>
                        <a href="https://wa.me/6281313365554" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#8E1E26] hover:underline pt-2">
                            Contact Bakery Concierge &rarr;
                        </a>
                    </div>
                </div>

                {{-- Right Column: Settings Action Cards --}}
                <div class="md:col-span-7 space-y-4">
                    <a href="{{ route('member.settings.ubah-password') }}"
                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="group flex items-center justify-between rounded-[28px] border border-[#3C2415]/15 bg-[#F5ECE3] p-6 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5 active:scale-[0.99]">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] group-hover:bg-[#8E1E26] transition shadow-xs">
                                <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                                    <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2" />
                                    <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <div>
                                <span class="block font-display font-bold text-base text-[#3C2415] group-hover:text-[#8E1E26] transition">Change Password</span>
                                <span class="text-xs text-[#3C2415]/65 mt-0.5 block">Update your password to keep your member account secure</span>
                            </div>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-[#3C2415]/40 group-hover:text-[#8E1E26] group-hover:bg-white transition shadow-xs">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection