<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5a3c27">
    <title>{{ $title ?? 'Member' }} &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-outfit text-gray-800 antialiased">

    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-white shadow-[0_0_60px_-15px_rgba(90,60,39,0.15)] sm:my-0">

        {{-- Top App Header --}}
        @unless(request()->routeIs('member.akun'))
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-gray-100 bg-white/95 px-5 pb-3 pt-4 backdrop-blur">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-800 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-13v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="block text-sm font-bold tracking-tight text-gray-900 leading-tight">Saisho</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-brand-800">Cake & Bakery</span>
                </div>
            </div>
            @yield('header-action')
        </header>
        @endunless

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-5 mt-3 flex items-start gap-2 rounded-2xl bg-brand-50 px-4 py-3 text-xs font-semibold text-brand-800 border border-brand-200">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-brand-800"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error') || $errors->any())
            <div class="mx-5 mt-3 flex items-start gap-2 rounded-2xl bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 border border-red-200">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-red-600"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Page Content --}}
        <main class="flex-1 px-5 pb-28">
            @yield('content')
        </main>

        {{-- Fore App Style Bottom Navigation --}}
        <nav class="fixed bottom-0 left-1/2 z-30 w-full max-w-md -translate-x-1/2 border-t border-gray-100 bg-white/95 px-3 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur">
            <div class="grid grid-cols-4 gap-1 text-center">
                
                {{-- Home / Beranda --}}
                @php $activeHome = request()->routeIs('member.beranda'); @endphp
                <a href="{{ route('member.beranda') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeHome ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeHome ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $activeHome ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Home
                </a>

                {{-- Vouchers / Reward --}}
                @php $activeVoucher = request()->routeIs('member.reward*'); @endphp
                @auth('member')
                    <a href="{{ route('member.reward.index') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeVoucher ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeVoucher ? 'bg-brand-50 text-brand-800' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Vouchers
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold text-gray-400 hover:text-gray-600">
                        <span class="flex h-7 w-7 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Vouchers
                    </a>
                @endauth

                {{-- Your Order / Riwayat --}}
                @php $activeOrder = request()->routeIs('member.riwayat*'); @endphp
                @auth('member')
                    <a href="{{ route('member.riwayat') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeOrder ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeOrder ? 'bg-brand-50 text-brand-800' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Your Order
                    </a>
                @else
                    <a href="{{ route('member.welcome') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold text-gray-400 hover:text-gray-600">
                        <span class="flex h-7 w-7 items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Your Order
                    </a>
                @endauth

                {{-- Account / Akun --}}
                @php $activeAccount = request()->routeIs('member.akun'); @endphp
                <a href="{{ route('member.akun') }}" class="flex flex-col items-center gap-1 rounded-xl py-1 text-[11px] font-bold transition {{ $activeAccount ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $activeAccount ? 'bg-brand-50 text-brand-800' : '' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $activeAccount ? 'currentColor' : 'none' }}" class="h-5 w-5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Account
                </a>

            </div>
        </nav>

        {{-- Login Required Dialog Modal for Guests --}}
        <dialog id="modal-login-required" class="fixed inset-0 my-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 backdrop:bg-gray-900/50 shadow-2xl border-0">
            <div class="p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-800">
                    <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 15v2m0 4h.01M5 11h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Zm2 0V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-gray-900">Login Diperlukan</h3>
                <p id="login-required-msg" class="mt-2 text-xs leading-relaxed text-gray-500">Silakan masuk atau daftar akun Saisho untuk mengakses fitur ini.</p>
                <div class="mt-6 flex flex-col gap-2">
                    <a href="{{ route('member.login') }}" class="w-full rounded-2xl bg-brand-800 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-900">
                        Login Sekarang
                    </a>
                    <button type="button" onclick="document.getElementById('modal-login-required').close()" class="py-2 text-xs font-semibold text-gray-500 hover:text-gray-700">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </dialog>

    </div>

    <script>
        function bukaModalLoginRequired(namaFitur) {
            document.getElementById('login-required-msg').innerText = 'Silakan masuk atau daftar akun Saisho untuk mengakses fitur ' + namaFitur + '.';
            document.getElementById('modal-login-required').showModal();
        }
    </script>
</body>
</html>
