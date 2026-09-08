@props([
    'code' => '',
    'title' => '',
    'message' => '',
    'image' => null,
    'accent' => 'brand',
])

@php
    $currentYear = date('Y');

    if (auth('web')->check()) {
        $berandaUrl = route('dashboard');
        $berandaLabel = 'Kembali ke Dashboard';
    } elseif (auth('member')->check()) {
        $berandaUrl = route('member.beranda');
        $berandaLabel = 'Kembali ke Beranda';
    } else {
        $berandaUrl = url('/');
        $berandaLabel = 'Kembali ke Beranda';
    }

    $accentMap = [
        'brand' => ['bg' => 'bg-brand-50 dark:bg-brand-500/10', 'text' => 'text-brand-500 dark:text-brand-400'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
    ];
    $accentClasses = $accentMap[$accent] ?? $accentMap['brand'];
@endphp

<div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gray-50 p-6 dark:bg-gray-950">
    {{-- Ornamen grid khas Saisho POS --}}
    <x-common.common-grid-shape />

    <div class="relative z-1 mx-auto flex w-full max-w-[420px] flex-col items-center text-center">

        {{-- Logo Saisho POS --}}
        <a href="{{ $berandaUrl }}" class="mb-8 inline-flex items-center gap-2.5">
            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Saisho POS" class="h-9 w-auto">
            <span class="text-lg font-black tracking-wide text-gray-900 dark:text-white">Saisho Cake & Bakery</span>
        </a>

        <div class="w-full rounded-3xl border border-gray-200 bg-white px-6 py-10 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:px-10">

            @if ($image)
                <img src="{{ asset('images/error/' . $image . '.svg') }}" alt="{{ $code }}" class="mx-auto mb-6 w-full max-w-[220px] dark:hidden">
                <img src="{{ asset('images/error/' . $image . '-dark.svg') }}" alt="{{ $code }}" class="mx-auto mb-6 hidden w-full max-w-[220px] dark:block">
            @else
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full {{ $accentClasses['bg'] }} {{ $accentClasses['text'] }} text-2xl font-bold">
                    {{ $code }}
                </div>
            @endif

            @if ($code)
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] {{ $accentClasses['text'] }}">
                    Error {{ $code }}
                </p>
            @endif

            <h1 class="mb-3 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">
                {{ $title }}
            </h1>

            <p class="mb-8 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                {{ $message }}
            </p>

            <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $berandaUrl }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                    {{ $berandaLabel }}
                </a>

                {{ $slot }}
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-sm text-gray-400 dark:text-gray-500">
            &copy; {{ $currentYear }} Saisho Cake &amp; Bakery
        </p>
    </div>
</div>