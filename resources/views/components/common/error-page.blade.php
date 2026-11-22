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

<div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-[#FAF5E8] p-6 dark:bg-[#1A120F]">
    {{-- Decorative strawberries --}}
    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="pointer-events-none select-none absolute top-10 left-10 w-16 h-16 opacity-20 -rotate-12">
    <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="pointer-events-none select-none absolute bottom-10 right-10 w-20 h-20 opacity-20 rotate-12">

    <div class="relative z-1 mx-auto flex w-full max-w-[430px] flex-col items-center text-center">

        {{-- Logo Daisy Bakery --}}
        <a href="{{ $berandaUrl }}" class="mb-7 inline-flex items-center gap-2.5">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#FFFDF9] border border-[#3C2415]/15 p-1.5 shadow-xs">
                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
            </span>
            <img src="{{ asset('images/logo/logo-wordmark.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain block dark:hidden">
            <img src="{{ asset('images/logo/logo-wordmark-light.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain hidden dark:block">
        </a>

        <div class="w-full rounded-[28px] border border-[#3C2415]/14 bg-[#FFFDF9] px-6 py-10 shadow-[0_12px_32px_-12px_rgba(58,34,27,0.14)] dark:border-white/10 dark:bg-[#241A16] sm:px-10">

            @if ($image)
                <img src="{{ asset('images/error/' . $image . '.svg') }}" alt="{{ $code }}" class="mx-auto mb-6 w-full max-w-[220px] dark:hidden">
                <img src="{{ asset('images/error/' . $image . '-dark.svg') }}" alt="{{ $code }}" class="mx-auto mb-6 hidden w-full max-w-[220px] dark:block">
            @else
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FCEEEF] text-[#8E1E26] text-2xl font-bold">
                    {{ $code }}
                </div>
            @endif

            @if ($code)
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-[#8E1E26] dark:text-[#EACE80]">
                    Error {{ $code }}
                </p>
            @endif

            <h1 class="mb-3 font-display text-2xl font-bold text-[#3A221B] dark:text-[#FAF5E8]">
                {{ $title }}
            </h1>

            <p class="mb-8 text-sm text-[#6B574C] dark:text-[#C5B5A8] sm:text-base">
                {{ $message }}
            </p>

            <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $berandaUrl }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#8E1E26] px-5 py-3 text-sm font-bold text-[#FAF5E8] shadow-xs transition hover:bg-[#741A21] sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                    {{ $berandaLabel }}
                </a>

                {{ $slot }}
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-7 text-xs font-medium text-[#8A766B]">
            &copy; {{ $currentYear }} Daisy Bakery
        </p>
    </div>
</div>