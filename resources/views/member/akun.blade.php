@extends('member.layout', ['title' => 'Account'])

@section('content')

    <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-3 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
        <h1 class="text-center text-lg font-extrabold text-gray-900">Account</h1>
    </div>

    <div class="mt-5 space-y-6">

        {{-- Profile Card --}}
        @auth('member')
            <a href="{{ route('member.profil') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');" class="relative flex items-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-[#5a3c27] to-[#8b5e3c] p-5 text-white shadow-sm transition active:scale-[0.99]">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/40 bg-white/20">
                    @if ($member->foto)
                        <img src="{{ asset('storage/'.$member->foto) }}" alt="{{ $member->nama }}" class="h-full w-full object-cover">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6 text-white"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-bold uppercase">{{ $member->nama }}</p>
                    <p class="text-xs text-white/80">{{ $member->no_hp }}</p>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-white/80"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <a href="{{ route('member.login') }}" class="relative block overflow-hidden rounded-2xl bg-gradient-to-r from-[#5a3c27] to-[#8b5e3c] p-5 text-white shadow-sm transition active:scale-[0.99]">
                <div class="flex items-center justify-between">
                    <span class="text-base font-bold">Login now</span>
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </a>
        @endauth

        {{-- Legal & Support Links --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Help Center</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Terms and Conditions</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 text-sm font-medium text-gray-800 hover:text-brand-800 shadow-xs">
                <span>Privacy Policy</span>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
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
                        {{-- Facebook --}}
                        <a href="https://facebook.com" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        {{-- YouTube --}}
                        <a href="https://youtube.com" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        {{-- X --}}
                        <a href="https://x.com" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-full bg-black text-white">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Need Help Section --}}
        <div class="pt-2">
            <h3 class="text-base font-bold text-gray-900">Need Help?</h3>
            <p class="text-xs text-gray-500 mt-0.5">Our Customer Service is ready to assist you</p>

            <a href="https://wa.me/6282000768044" target="_blank" class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.81 9.81 0 0 0 12.04 2zm5.8 14.16c-.24.68-1.2 1.25-1.95 1.41-.51.11-1.18.2-3.43-.74-2.88-1.19-4.73-4.13-4.88-4.32-.14-.19-1.18-1.57-1.18-3 0-1.43.75-2.14 1.02-2.43.24-.26.63-.38.84-.38.21 0 .42 0 .6.01.19.01.45-.07.7.53.26.63.89 2.17.97 2.33.08.16.13.35.03.56-.1.21-.16.34-.31.52-.16.18-.33.4-.47.54-.15.15-.31.31-.13.62.18.31.8 1.32 1.72 2.14 1.18 1.05 2.18 1.38 2.49 1.53.31.16.5.13.68-.08.19-.21.79-.92 1-.1.24.18.24.31.4.52 1.32.21.21 2.22.42 2.51.52.29.1.47.16.53.27.06.11.06.63-.18 1.31z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-gray-900">Saisho Customer Service (chat only)</span>
                </div>
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        {{-- Version & Logout --}}
        @auth('member')
            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                <span class="text-xs text-gray-400">Version 1.0.0</span>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-red-600 hover:text-red-700">Logout</button>
                </form>
            </div>
        @endauth

    </div>

@endsection