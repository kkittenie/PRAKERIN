@props([
    'pageTitle' => 'Page',
    'parentTitle' => null,
    'parentUrl' => null,
    'homeUrl' => null,
])

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-2.5 min-w-0">
        <span class="hidden sm:flex items-center justify-center w-9 h-9 rounded-xl bg-[#FCEEEF] dark:bg-[#8E1E26]/25 border border-[#8E1E26]/15 shrink-0">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-5 h-5">
        </span>
        <h2 class="font-display text-2xl sm:text-[26px] font-bold text-[#3A221B] dark:text-[#FAF5E8] tracking-tight truncate">
            {{ $pageTitle }}
        </h2>
    </div>
    <nav aria-label="Breadcrumb">
        <ol class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#FFFDF9] dark:bg-[#241A16] border border-[#3C2415]/12 dark:border-white/10 shadow-2xs">
            <li>
                <a
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#7D6A60] dark:text-[#C5B5A8] hover:text-[#8E1E26] dark:hover:text-[#EACE80] transition"
                    href="{{ $homeUrl ?? route('dashboard') }}"
                >
                    Beranda
                    <svg class="stroke-current opacity-60" width="14" height="14" viewBox="0 0 17 16" fill="none">
                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </li>
            @if($parentTitle && $parentUrl)
                <li>
                    <a
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#7D6A60] dark:text-[#C5B5A8] hover:text-[#8E1E26] dark:hover:text-[#EACE80] transition"
                        href="{{ $parentUrl }}"
                    >
                        {{ $parentTitle }}
                        <svg class="stroke-current opacity-60" width="14" height="14" viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </li>
            @elseif($parentTitle)
                <li class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#7D6A60] dark:text-[#C5B5A8]">
                    {{ $parentTitle }}
                    <svg class="stroke-current opacity-60" width="14" height="14" viewBox="0 0 17 16" fill="none">
                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </li>
            @endif
            <li class="text-xs font-bold text-[#8E1E26] dark:text-[#EACE80]">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>
</div>
