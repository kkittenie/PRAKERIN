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

    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-gray-50 shadow-[0_0_60px_-15px_rgba(90,60,39,0.25)] sm:my-0">

        {{-- Header --}}
        <header class="sticky top-0 z-20 flex items-center justify-between bg-gray-50/90 px-5 pb-3 pt-5 backdrop-blur">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M4 9h13a3 3 0 0 1 0 6h-1M4 9v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9M4 9V6a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v3M7 3.5v1.2M10 3.5v1.2M13 3.5v1.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <span class="text-theme-sm font-semibold tracking-tight text-gray-800">{{ config('app.name') }}</span>
            </div>
            @yield('header-action')
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-5 mb-3 flex items-start gap-2 rounded-xl bg-success-50 px-4 py-3 text-theme-sm text-success-700">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4.5 w-4.5 shrink-0"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error') || $errors->any())
            <div class="mx-5 mb-3 flex items-start gap-2 rounded-xl bg-error-50 px-4 py-3 text-theme-sm text-error-700">
                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 h-4.5 w-4.5 shrink-0"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 px-5 pb-28">
            @yield('content')
        </main>

        {{-- Bottom nav --}}
        @auth('member')
        <nav class="sticky bottom-0 z-20 border-t border-gray-200 bg-white/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur">
            <div class="grid grid-cols-4">
                @php
                    $navItems = [
                        ['route' => 'member.beranda', 'label' => 'Beranda', 'icon' => 'home'],
                        ['route' => 'member.reward.index', 'label' => 'Reward', 'icon' => 'gift'],
                        ['route' => 'member.riwayat', 'label' => 'Riwayat', 'icon' => 'clock'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}" class="flex flex-col items-center gap-1 rounded-xl py-1.5 text-[11px] font-medium {{ $active ? 'text-brand-600' : 'text-gray-400' }}">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $active ? 'bg-brand-50' : '' }}">
                            @include('member.components.icon-'.$item['icon'], ['class' => 'h-5 w-5'])
                        </span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <button type="button" onclick="document.getElementById('menu-akun').showModal()" class="flex flex-col items-center gap-1 rounded-xl py-1.5 text-[11px] font-medium text-gray-400">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full">
                        @include('member.components.icon-user', ['class' => 'h-5 w-5'])
                    </span>
                    Akun
                </button>
            </div>
        </nav>

        <dialog id="menu-akun" class="w-[calc(100%-2.5rem)] max-w-sm rounded-2xl p-0 backdrop:bg-gray-900/40">
            <div class="p-5">
                <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-400">Akun</p>
                <p class="mt-1 text-theme-xl font-semibold text-gray-800">{{ auth('member')->user()->nama }}</p>
                <p class="text-theme-sm text-gray-500">{{ auth('member')->user()->no_hp }}</p>
                <form method="POST" action="{{ route('member.logout') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-error-50 py-2.5 text-theme-sm font-semibold text-error-600">Keluar</button>
                </form>
                <button type="button" onclick="document.getElementById('menu-akun').close()" class="mt-2 w-full rounded-xl py-2.5 text-theme-sm font-medium text-gray-500">Tutup</button>
            </div>
        </dialog>
        @endauth
    </div>
</body>
</html>
