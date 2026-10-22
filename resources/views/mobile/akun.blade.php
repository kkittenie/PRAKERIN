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
    {{-- DESKTOP VIEW (>= 1024px) - REDESIGNED CARD --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block lg:max-w-4xl lg:mx-auto lg:my-6 lg:bg-white lg:rounded-3xl lg:border lg:border-gray-200/80 lg:shadow-xl lg:p-8 lg:space-y-8">
        {{-- Desktop Page Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-5">
            <div>
                <h1 class="text-xl font-black text-brand-800 tracking-tight">Akun Saya</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola profil, informasi akun, dan pusat bantuan Saisho</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8 items-start">
            {{-- Left Column (Profile & Navigation Links) --}}
            <div class="col-span-7 space-y-6">
                {{-- Profile Card --}}
                @auth('member')
                    <a href="{{ route('member.profil') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="group relative flex items-center gap-4 overflow-hidden rounded-2xl bg-gradient-to-r from-[#5a3c27] via-[#6f4b32] to-[#8b5e3c] p-5 text-white shadow-md transition hover:shadow-lg active:scale-[0.99]">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/40 bg-white/20 shadow-sm">
                            @if ($member->foto)
                                <img src="{{ asset('storage/'.$member->foto) }}" alt="{{ $member->nama }}" class="h-full w-full object-cover">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7 text-white"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-base font-extrabold uppercase tracking-wide">{{ $member->nama }}</p>
                            <p class="text-xs text-white/80 font-medium">{{ $member->username ? '@' . $member->username : $member->no_hp }}</p>
                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-brand-200 font-semibold group-hover:underline">
                                Edit Profil &rarr;
                            </span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-white/80 transition transform group-hover:translate-x-1"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @else
                    <a href="{{ route('member.login') }}" class="relative block overflow-hidden rounded-2xl bg-gradient-to-r from-[#5a3c27] to-[#8b5e3c] p-5 text-white shadow-md transition hover:shadow-lg active:scale-[0.99]">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-base font-bold block">Masuk ke Akun Member</span>
                                <span class="text-xs text-white/80">Nikmati promo eksklusif{{ \App\Models\PengaturanMember::adaAturanAktif() ? ' & kumpulkan poin' : '' }}</span>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </a>
                @endauth

                {{-- Quick Links Options --}}
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('member.konten.show', 'help-center') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="group flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-200/80 text-sm font-semibold text-gray-800 hover:border-brand-300 hover:text-brand-800 hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800 group-hover:bg-brand-800 group-hover:text-white transition">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <span>Pusat Bantuan</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400 group-hover:text-brand-800 transition transform group-hover:translate-x-0.5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>

                    <a href="{{ route('member.konten.show', 'terms-conditions') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="group flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-200/80 text-sm font-semibold text-gray-800 hover:border-brand-300 hover:text-brand-800 hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800 group-hover:bg-brand-800 group-hover:text-white transition">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <span>Syarat & Ketentuan</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400 group-hover:text-brand-800 transition transform group-hover:translate-x-0.5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>

                    <a href="{{ route('member.konten.show', 'privacy-policy') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                        class="group flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-200/80 text-sm font-semibold text-gray-800 hover:border-brand-300 hover:text-brand-800 hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800 group-hover:bg-brand-800 group-hover:text-white transition">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Kebijakan Privasi</span>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400 group-hover:text-brand-800 transition transform group-hover:translate-x-0.5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>

                    @auth('member')
                        <a href="{{ route('member.settings.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                            class="group flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-200/80 text-sm font-semibold text-gray-800 hover:border-brand-300 hover:text-brand-800 hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800 group-hover:bg-brand-800 group-hover:text-white transition">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                </span>
                                <span>Pengaturan Akun</span>
                            </div>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400 group-hover:text-brand-800 transition transform group-hover:translate-x-0.5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Right Column (Support & Social Links) --}}
            <div class="col-span-5 space-y-6">
                {{-- Need Help Section --}}
                <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs">
                    <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wide">Butuh Bantuan?</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Tim Layanan Pelanggan Saisho siap membantu kendala Anda.</p>

                    @php
                        $pesanWa = "Halo Saisho Bakery, saya" . (auth('member')->check() ? ' ' . $member->nama : '') . " ingin bertanya seputar layanan Saisho. Mohon bantuannya ya 🙏";
                    @endphp
                    <a href="https://wa.me/6281313365554?text={{ urlencode($pesanWa) }}" target="_blank"
                        class="mt-4 flex items-center justify-between rounded-xl border border-green-200 bg-green-50/50 p-3.5 shadow-xs transition hover:bg-green-50 active:scale-[0.99]">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500 text-white shadow-xs">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.81 9.81 0 0 0 12.04 2zm5.8 14.16c-.24.68-1.2 1.25-1.95 1.41-.51.11-1.18.2-3.43-.74-2.88-1.19-4.73-4.13-4.88-4.32-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.14 1.02-2.43.24-.26.63-.38.84-.38.21 0 .42 0 .6.01.19.01.45-.07.7.53.26.63.89 2.17.97 2.33.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.15.15-.31.31-.13.62.18.31.8 1.32 1.72 2.14 1.18 1.05 2.18 1.38 2.49 1.53.31.16.5.13.68-.08.19-.21.79-.92 1-.1.24.18.24.31.4.52 1.32.21.21 2.22.42 2.51.52.29.1.47.16.53.27.06.11.06.63-.18 1.31z"/></svg>
                            </span>
                            <div>
                                <span class="block text-xs font-extrabold text-gray-900">Saisho Customer Service</span>
                                <span class="text-[11px] text-green-700 font-semibold">Respon Cepat via WhatsApp</span>
                            </div>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-green-700"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                {{-- Social Media Section --}}
                <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs">
                    <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wide mb-3">Ikuti Media Sosial Kami</h3>
                    <a href="https://www.instagram.com/saishobakery?igsh=bTcyMDNibmNleGIz" target="_blank"
                        class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-gray-100 transition">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white shadow-xs">
                                <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">@saishobakery</span>
                        </div>
                    </a>
                </div>

                {{-- Logout Button --}}
                @auth('member')
                    <div class="pt-2 flex justify-end">
                        <form method="POST" action="{{ route('member.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-2xl bg-red-50 border border-red-200 px-6 py-3 text-xs font-extrabold text-red-600 hover:bg-red-600 hover:text-white hover:border-red-600 transition shadow-xs active:scale-95">
                                Keluar Akun
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </div>

@endsection