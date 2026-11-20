@extends('mobile.layout', ['title' => 'Account'])

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px)                     --}}
    {{-- ========================================== --}}
    <div class="space-y-6 lg:hidden">

        <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-3 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10 mb-2">
            <h1 class="text-center text-lg font-extrabold text-gray-900">Account</h1>
        </div>

        @auth('member')
            @php $member = auth('member')->user(); @endphp
            <a href="{{ route('member.profil') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="relative flex items-center gap-4 rounded-3xl bg-gradient-to-r from-[#5a3c27] to-[#8b5e3c] p-4 text-white shadow-md transition active:scale-[0.99]">
                <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-white/20 ring-2 ring-white/30">
                    @if ($member->foto)
                        <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}" class="h-full w-full object-cover">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 text-white"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-bold uppercase">{{ $member->nama }}</p>
                    <p class="text-xs text-white/80">{{ $member->username ? '@' . $member->username : $member->no_hp }}</p>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-white/80"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <a href="{{ route('member.login') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="relative block overflow-hidden rounded-2xl bg-gradient-to-r from-[#5a3c27] to-[#8b5e3c] p-5 text-white shadow-sm transition active:scale-[0.99]">
                <div class="flex items-center justify-between">
                    <span class="text-base font-bold">Login now</span>
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </a>
        @endauth

        {{-- Legal & Support Links --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="{{ route('member.konten.show', 'help-center') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Help Center</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="{{ route('member.konten.show', 'terms-conditions') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Terms and Conditions</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="{{ route('member.konten.show', 'privacy-policy') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Privacy Policy</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            @auth('member')
                <a href="{{ route('member.settings.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                    <span>Settings</span>
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endauth
        </div>

        {{-- Social Media Section --}}
        <div class="pt-2">
            <h3 class="text-base font-bold text-gray-900 mb-3">Social Media</h3>
            <div class="space-y-3 divide-y divide-gray-100">
                <div class="flex items-center justify-between pt-1">
                    <span class="text-sm font-medium text-gray-800">Saisho Bakery</span>
                    <div class="flex items-center gap-2">
                        {{-- Instagram --}}
                        <a href="https://www.instagram.com/saishobakery?igsh=bTcyMDNibmNleGIz" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Need Help Section --}}
        <div class="pt-2">
            <h3 class="text-base font-bold text-gray-900">Need Help?</h3>
            <p class="text-xs text-gray-500 mt-0.5">Our Customer Service is ready to assist you</p>

            <a href="https://wa.me/6281313365554" target="_blank" class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.81 9.81 0 0 0 12.04 2zm5.8 14.16c-.24.68-1.2 1.25-1.95 1.41-.51.11-1.18.2-3.43-.74-2.88-1.19-4.73-4.13-4.88-4.32-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.14 1.02-2.43.24-.26.63-.38.84-.38.21 0 .42 0 .6.01.19.01.45-.07.7.53.26.63.89 2.17.97 2.33.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.15.15-.31.31-.13.62.18.31.8 1.32 1.72 2.14 1.18 1.05 2.18 1.38 2.49 1.53.31.16.5.13.68-.08.19-.21.79-.92 1-.1.24.18.24.31.4.52 1.32.21.21 2.22.42 2.51.52.29.1.47.16.53.27.06.11.06.63-.18 1.31z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-gray-900">Saisho Customer Service</span>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        {{-- Version & Logout --}}
        @auth('member')
            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                <span class="text-xs text-gray-400"></span>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-red-600 hover:text-red-700">Logout</button>
                </form>
            </div>
        @endauth
    </div>


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - BESPOKE FRENCH BAKERY MEMBER HUB (NO HERO SECTION) --}}
    {{-- VIP Member Card, Loyalty Overview, and Bento Navigation. --}}
    {{-- ========================================== --}}
    <div id="account-desktop-root" class="relative hidden lg:block w-full bg-[#FAF5E8] min-h-screen text-[#3C2415] overflow-hidden">

        <style>
            @keyframes floatStrawberry {
                0%, 100% {
                    transform: translateY(0px) rotate(var(--rot, 0deg));
                }
                50% {
                    transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg));
                }
            }
            .desktop-floating-strawberry {
                animation: floatStrawberry var(--duration, 4s) ease-in-out infinite;
                will-change: transform;
            }
        </style>

        {{-- 2D Floating Strawberries in Outer Gutters --}}
        {{-- Left gutter strawberries (Top to Bottom) --}}
        <div class="desktop-floating-strawberry hidden lg:block absolute left-2 xl:left-6 2xl:left-12 top-8 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: -14deg; --duration: 4.2s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-4 xl:left-10 2xl:left-16 top-48 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 36px; height: 48px; --rot: 10deg; --duration: 5.1s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-2 xl:left-5 2xl:left-11 top-96 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: -6deg; --duration: 4.6s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-4 xl:left-9 2xl:left-16 top-[32%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 40px; height: 52px; --rot: 15deg; --duration: 4.9s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-2 xl:left-6 2xl:left-12 top-[46%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 48px; height: 64px; --rot: -12deg; --duration: 4.4s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-5 xl:left-10 2xl:left-18 top-[60%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 38px; height: 50px; --rot: 8deg; --duration: 5.3s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-3 xl:left-7 2xl:left-14 top-[72%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: -10deg; --duration: 4.7s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-5 xl:left-11 2xl:left-18 top-[84%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: 14deg; --duration: 4.5s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute left-2 xl:left-6 2xl:left-12 top-[94%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 62px; --rot: -8deg; --duration: 5.0s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        {{-- Right gutter strawberries (Top to Bottom) --}}
        <div class="desktop-floating-strawberry hidden lg:block absolute right-2 xl:right-6 2xl:right-12 top-12 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: 12deg; --duration: 3.9s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-4 xl:right-9 2xl:right-16 top-56 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 48px; height: 64px; --rot: -8deg; --duration: 4.8s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-2 xl:right-5 2xl:right-11 top-104 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 38px; height: 50px; --rot: 16deg; --duration: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-5 xl:right-10 2xl:right-18 top-[35%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: -14deg; --duration: 4.3s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-2 xl:right-6 2xl:right-12 top-[49%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: 10deg; --duration: 4.7s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-4 xl:right-9 2xl:right-16 top-[63%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 48px; height: 64px; --rot: -6deg; --duration: 5.0s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-2 xl:right-6 2xl:right-12 top-[75%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 38px; height: 50px; --rot: 12deg; --duration: 4.6s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-5 xl:right-10 2xl:right-18 top-[86%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: -12deg; --duration: 4.4s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden lg:block absolute right-3 xl:right-7 2xl:right-14 top-[95%] pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 62px; --rot: 14deg; --duration: 4.9s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        {{-- Clean Desktop Account Hub Header (NO HERO SECTION) --}}
        <div class="relative z-10 max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-10 pb-4 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#3C2415]/5 border border-[#3C2415]/10 text-xs font-bold uppercase tracking-wider text-[#8E1E26]">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 object-contain inline-block">
                <span>DAISY MEMBER CLUB</span>
            </div>
            <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                Account & Profile
            </h1>
            <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                Manage your artisanal membership privileges, track loyalty bakes, review active reward vouchers, and explore sweet community perks.
            </p>
        </div>

        {{-- ACCOUNT SHOWCASE --}}
        <section id="account-desktop-showcase" class="relative w-full bg-[#FAF5E8]">
            <div id="account-showcase-content" class="relative max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-2 pb-24 z-10 space-y-10">

                @auth('member')
                    @php
                        $member = auth('member')->user();
                        $programAktif = \App\Models\StampProgram::aktifSekarang();
                        $memberStamp = $programAktif ? $member->stamps()->where('stamp_program_id', $programAktif->id)->first() : null;
                        $jumlahStampMember = $memberStamp ? (int) $memberStamp->jumlah_stamp : 0;
                    @endphp

                    {{-- VIP MEMBER IDENTITY HERO CARD --}}
                    <div class="relative overflow-hidden rounded-[32px] bg-[#F5ECE3] p-8 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                        {{-- Background Warm Glow Effect & Strawberry Illustration --}}
                        <div class="pointer-events-none absolute -right-20 -top-20 w-80 h-80 rounded-full bg-radial from-[#8E1E26]/10 to-transparent blur-2xl"></div>
                        <div class="pointer-events-none absolute right-8 -top-3 w-14 h-18 opacity-80 select-none rotate-12 transition-transform duration-300 hover:rotate-18">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                        </div>

                        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            {{-- Profile Avatar & Personal Info --}}
                            <div class="flex items-center gap-5 sm:gap-6 min-w-0">
                                <div class="relative shrink-0">
                                    <span class="flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center overflow-hidden rounded-full border-3 border-[#3C2415]/20 bg-white shadow-md">
                                        @if ($member->foto)
                                            <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="flex h-full w-full items-center justify-center bg-[#3C2415] text-[#FAF5E8]">
                                                <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-[#FAF5E8]">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </span>
                                    <span class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-white border border-[#3C2415]/15 shadow-sm p-1">
                                        <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain">
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h2 class="font-display font-black text-2xl sm:text-3xl uppercase tracking-tight text-[#3C2415] truncate">
                                            {{ $member->nama }}
                                        </h2>
                                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-[#8E1E26] text-white text-[11px] font-bold uppercase tracking-wider shadow-xs">
                                            ★ {{ $member->level ?? 'Club Member' }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#3C2415]/75 font-medium">
                                        @if ($member->username)
                                            <span class="font-bold text-[#8E1E26]">{{ '@' . $member->username }}</span>
                                            <span>&bull;</span>
                                        @endif
                                        <span>{{ $member->no_hp }}</span>
                                        @if ($member->email)
                                            <span>&bull;</span>
                                            <span class="truncate max-w-[220px]">{{ $member->email }}</span>
                                        @endif
                                    </div>

                                    <div class="pt-2 flex flex-wrap items-center gap-3">
                                        <span class="text-[11px] text-[#3C2415]/60 font-medium">
                                            Member since {{ $member->created_at->format('d F Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Direct Actions: Edit Profile & Change Password --}}
                            <div class="flex sm:flex-row md:flex-col lg:flex-row items-center gap-3 shrink-0">
                                <a href="{{ route('member.profil') }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] text-xs font-bold uppercase tracking-wider shadow-xs transition active:scale-95">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>Edit Profile</span>
                                </a>

                                <a href="{{ route('member.settings.ubah-password') }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-[#3C2415]/20 text-[#3C2415] hover:bg-[#FAF5E8] hover:border-[#3C2415]/40 text-xs font-bold uppercase tracking-wider shadow-xs transition active:scale-95">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                        <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span>Change Password</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- LOYALTY OVERVIEW: 3 HIGHLIGHT CARDS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Total Points Card --}}
                        <div class="relative overflow-hidden rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                            <div class="pointer-events-none absolute -right-6 -bottom-6 w-24 h-24 opacity-10 select-none rotate-12">
                                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain">
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                </span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#8E1E26] bg-[#8E1E26]/10 px-3 py-1 rounded-full flex items-center gap-1.5">
                                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3 h-3 object-contain">
                                    <span>Loyalty Balance</span>
                                </span>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#3C2415]/70">Available Points</p>
                            <p class="mt-1 font-display font-black text-3xl sm:text-4xl text-[#3C2415]">
                                {{ number_format($member->poin, 0, ',', '.') }} <span class="text-base font-bold text-[#8E1E26]">PTS</span>
                            </p>
                            <div class="mt-4 pt-3 border-t border-[#3C2415]/10 flex items-center justify-between">
                                <span class="text-xs text-[#3C2415]/60">Earn points on every bake</span>
                                <a href="{{ route('member.reward.index') }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="text-xs font-bold text-[#8E1E26] hover:underline flex items-center gap-1">
                                    Redeem Rewards &rarr;
                                </a>
                            </div>
                        </div>

                        {{-- Collected Stamps Card --}}
                        <div class="relative overflow-hidden rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                            <div class="pointer-events-none absolute -right-6 -bottom-6 w-24 h-24 opacity-10 select-none -rotate-12">
                                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain">
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white border border-[#3C2415]/15 shadow-xs">
                                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Stamp Card" class="w-6 h-6 object-contain">
                                </span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#3C2415] bg-[#3C2415]/10 px-3 py-1 rounded-full flex items-center gap-1.5">
                                    <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-3 h-3 object-contain">
                                    <span>Stamp Program</span>
                                </span>
                            </div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#3C2415]/70">Collected Stamps</p>
                            <p class="mt-1 font-display font-black text-3xl sm:text-4xl text-[#3C2415]">
                                {{ $jumlahStampMember }} <span class="text-base font-bold text-[#3C2415]/60">STAMPS</span>
                            </p>
                            <div class="mt-4 pt-3 border-t border-[#3C2415]/10 flex items-center justify-between">
                                <span class="text-xs text-[#3C2415]/60">Unlock free pastries</span>
                                <a href="{{ route('member.reward.index', ['tab' => 'stamp']) }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="text-xs font-bold text-[#8E1E26] hover:underline flex items-center gap-1">
                                    View Stamp Card &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- BENTO GRID: 6 MAIN ACCOUNT SHORTCUTS --}}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#8E1E26]/10 text-[10px] font-black uppercase tracking-wider text-[#8E1E26] mb-1.5">
                                    <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-3 h-3 object-contain">
                                    <span>DAISY BAKERY HUB</span>
                                </div>
                                <h3 class="font-display font-black text-xl uppercase tracking-tight text-[#3C2415]">Account Services</h3>
                                <p class="text-xs text-[#3C2415]/70">Quick access to orders, loyalty cards, and store policies.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            {{-- 1. Order History --}}
                            <a href="{{ route('member.riwayat') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:bg-[#8E1E26] transition-colors">
                                            @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                        </span>
                                        <span class="text-[#3C2415]/40 group-hover:text-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                        Orders & Activity
                                    </h4>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/70 leading-relaxed">
                                        Track live bakery orders in real time, check pickup codes, and view past invoices.
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#3C2415]/10 text-xs font-bold text-[#8E1E26]">
                                    Review Invoices &rarr;
                                </div>
                            </a>

                            {{-- 2. Vouchers & Rewards --}}
                            <a href="{{ route('member.reward.index') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:bg-[#8E1E26] transition-colors">
                                            @include('mobile.components.icon-ticket', ['class' => 'h-5 w-5'])
                                        </span>
                                        <span class="text-[#3C2415]/40 group-hover:text-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                        Vouchers & Rewards
                                    </h4>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/70 leading-relaxed">
                                        Redeem reward points for pastry discounts, free bread coupons, and celebration gifts.
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#3C2415]/10 text-xs font-bold text-[#8E1E26]">
                                    Browse Vouchers &rarr;
                                </div>
                            </a>

                            {{-- 4. Help Center & FAQ --}}
                            <a href="{{ route('member.konten.show', 'help-center') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:bg-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-[#3C2415]/40 group-hover:text-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                        Help Center & FAQ
                                    </h4>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/70 leading-relaxed">
                                        Answers about pickup timings, pastry storage, custom cake lead times, and payment methods.
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#3C2415]/10 text-xs font-bold text-[#8E1E26]">
                                    Read Store Guides &rarr;
                                </div>
                            </a>

                            {{-- 5. Terms & Conditions --}}
                            <a href="{{ route('member.konten.show', 'terms-conditions') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:bg-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/>
                                                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-[#3C2415]/40 group-hover:text-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                        Terms & Conditions
                                    </h4>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/70 leading-relaxed">
                                        Official terms governing pickup orders, voucher expiry rules, and bakery club memberships.
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#3C2415]/10 text-xs font-bold text-[#8E1E26]">
                                    Review Policy &rarr;
                                </div>
                            </a>

                            {{-- 6. Privacy Policy --}}
                            <a href="{{ route('member.konten.show', 'privacy-policy') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:border-[#8E1E26]/40 hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:bg-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-[#3C2415]/40 group-hover:text-[#8E1E26] transition-colors">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                    <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                        Privacy Policy
                                    </h4>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/70 leading-relaxed">
                                        How Daisy Bakery protects your personal data, credentials, and digital payment receipts.
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-[#3C2415]/10 text-xs font-bold text-[#8E1E26]">
                                    Privacy Guidelines &rarr;
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- CONCIERGE & SOCIAL COMMUNITY ROW --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- WhatsApp Direct Concierge --}}
                        <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-xs">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.81 9.81 0 0 0 12.04 2zm5.8 14.16c-.24.68-1.2 1.25-1.95 1.41-.51.11-1.18.2-3.43-.74-2.88-1.19-4.73-4.13-4.88-4.32-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.14 1.02-2.43.24-.26.63-.38.84-.38.21 0 .42 0 .6.01.19.01.45-.07.7.53.26.63.89 2.17.97 2.33.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.15.15-.31.31-.13.62.18.31.8 1.32 1.72 2.14 1.18 1.05 2.18 1.38 2.49 1.53.31.16.5.13.68-.08.19-.21.79-.92 1-.1.24.18.24.31.4.52 1.32.21.21 2.22.42 2.51.52.29.1.47.16.53.27.06.11.06.63-.18 1.31z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415]">Bakery Concierge</h4>
                                        <span class="text-[11px] font-semibold text-emerald-700">Quick Response via WhatsApp</span>
                                    </div>
                                </div>
                                <p class="text-xs text-[#3C2415]/75 leading-relaxed">
                                    Have a special occasion cake inquiry, dietary requirement, or need urgent order assistance? Our friendly bakery concierge is ready to help.
                                </p>
                            </div>

                            @php
                                $pesanWaDesk = "Hello Daisy Bakery, I am " . $member->nama . " (Member: " . $member->kode_member . "). I would like to inquire about bakery services and pre-orders. Thank you!";
                            @endphp
                            <div class="mt-5">
                                <a href="https://wa.me/62882000768044?text={{ urlencode($pesanWaDesk) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center gap-2 w-full py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider shadow-xs transition active:scale-95">
                                    <span>Chat with Bakery Concierge</span>
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Official Instagram Community --}}
                        <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white shadow-xs">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415]">Follow Our Bakes</h4>
                                        <span class="text-[11px] font-semibold text-rose-700">@daisybakery &bull; Fresh Daily Stories</span>
                                    </div>
                                </div>
                                <p class="text-xs text-[#3C2415]/75 leading-relaxed">
                                    Catch fresh morning sourdough pulls, artisan pastry tutorials, seasonal menu reveals, and exclusive member giveaways on our Instagram.
                                </p>
                            </div>

                            <div class="mt-5">
                                <a href="https://www.instagram.com/safiralmr?igsh=bTcyMDNibmNleGIz" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center gap-2 w-full py-3 px-5 rounded-2xl bg-gradient-to-r from-rose-600 via-pink-600 to-purple-600 hover:opacity-95 text-white text-xs font-bold uppercase tracking-wider shadow-xs transition active:scale-95">
                                    <span>Follow @daisybakery</span>
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SIGN OUT BAR --}}
                    <div class="relative overflow-hidden rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="pointer-events-none absolute -right-6 -bottom-6 w-28 h-28 opacity-10 select-none rotate-12">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain">
                        </div>
                        <div class="relative z-10 space-y-0.5 text-center sm:text-left">
                            <p class="text-xs font-bold text-[#3C2415] flex items-center gap-1.5 justify-center sm:justify-start">
                                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 object-contain inline-block">
                                <span>Signed in as <span class="text-[#8E1E26]">{{ $member->nama }}</span> ({{ $member->email ?? $member->no_hp }})</span>
                            </p>
                            <p class="text-[11px] text-[#3C2415]/60">You can safely sign out of your account on this device at any time.</p>
                        </div>

                        <form method="POST" action="{{ route('member.logout') }}" class="relative z-10">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 text-red-700 font-bold text-xs uppercase tracking-wider transition shadow-xs active:scale-95 cursor-pointer">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>

                    {{-- Decorative Bottom Strawberry Accent Cluster --}}
                    <div class="relative pt-6 pb-2 flex items-center justify-center gap-4 sm:gap-6 pointer-events-none select-none z-10">
                        <div class="desktop-floating-strawberry" style="width: 36px; height: 48px; --rot: -14deg; --duration: 4.8s;">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                        </div>
                        <div class="desktop-floating-strawberry" style="width: 44px; height: 58px; --rot: 8deg; --duration: 4.2s;">
                            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                        </div>
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#8E1E26]/10 border border-[#8E1E26]/20 text-[#8E1E26] text-[11px] font-black uppercase tracking-wider shadow-xs">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 object-contain">
                            <span>DAISY BAKERY • ARTISAN BAKED WITH LOVE</span>
                            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-3.5 h-3.5 object-contain">
                        </div>
                        <div class="desktop-floating-strawberry" style="width: 44px; height: 58px; --rot: -10deg; --duration: 4.5s;">
                            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                        </div>
                        <div class="desktop-floating-strawberry" style="width: 36px; height: 48px; --rot: 12deg; --duration: 5.0s;">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                        </div>
                    </div>

                @else
                    {{-- GUEST INVITATION CARD --}}
                    <div class="relative overflow-hidden rounded-[32px] bg-[#F5ECE3] p-8 sm:p-12 border border-[#3C2415]/15 shadow-sm text-center">
                        <div class="max-w-2xl mx-auto space-y-4">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#8E1E26]/10 border border-[#8E1E26]/20 text-[#8E1E26] text-xs font-bold uppercase tracking-widest">
                                WELCOME TO DAISY BAKERY
                            </span>

                            <h2 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                                Join Our Artisan Member Club
                            </h2>

                            <p class="text-sm text-[#3C2415]/75 leading-relaxed">
                                Sign in to enjoy exclusive member rewards, collect digital loyalty points on every sourdough loaf, and unlock sweet savings on signature celebration cakes.
                            </p>

                            <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                                <a href="{{ route('member.login') }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold uppercase tracking-wider shadow-md transition active:scale-95">
                                    <span>Sign In to Account</span>
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>

                                <a href="{{ route('member.register') }}"
                                   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-white border border-[#3C2415]/20 hover:bg-[#FAF5E8] text-[#3C2415] text-xs font-bold uppercase tracking-wider shadow-xs transition active:scale-95">
                                    <span>Create New Membership</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- PUBLIC QUICK LINKS FOR GUESTS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <a href="{{ route('member.konten.show', 'help-center') }}"
                           class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                            <div>
                                <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                    Help Center & FAQ
                                </h4>
                                <p class="mt-1.5 text-xs text-[#3C2415]/70">Everything you need to know about bakery pre-orders and pickup.</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-[#8E1E26]">Learn More &rarr;</span>
                        </a>

                        <a href="{{ route('member.konten.show', 'terms-conditions') }}"
                           class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                            <div>
                                <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                    Terms & Conditions
                                </h4>
                                <p class="mt-1.5 text-xs text-[#3C2415]/70">Store ordering policies, pickup timeframes, and cancellation rules.</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-[#8E1E26]">Read Terms &rarr;</span>
                        </a>

                        <a href="{{ route('member.konten.show', 'privacy-policy') }}"
                           class="group flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md">
                            <div>
                                <h4 class="font-display font-bold text-base uppercase tracking-tight text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">
                                    Privacy Policy
                                </h4>
                                <p class="mt-1.5 text-xs text-[#3C2415]/70">How we protect customer personal data and digital credentials.</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-[#8E1E26]">Read Policy &rarr;</span>
                        </a>
                    </div>
                @endauth

                {{-- Bottom Corner Accent Strawberries --}}
                <div class="desktop-floating-strawberry hidden lg:block absolute left-4 xl:left-12 2xl:left-20 bottom-8 pointer-events-none select-none z-0"
                     aria-hidden="true" style="width: 52px; height: 68px; --rot: -12deg; --duration: 4.3s;">
                    <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                </div>
                <div class="desktop-floating-strawberry hidden lg:block absolute right-4 xl:right-12 2xl:right-20 bottom-8 pointer-events-none select-none z-0"
                     aria-hidden="true" style="width: 52px; height: 68px; --rot: 14deg; --duration: 4.1s;">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain filter drop-shadow-xs">
                </div>

            </div>
        </section>
    </div>

@endsection