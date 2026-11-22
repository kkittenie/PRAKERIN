@props([
    'title' => '',
    'desc' => '',
])

<div {{ $attributes->merge(['class' => 'rounded-[22px] border border-[#3C2415]/12 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] shadow-[0_2px_10px_-2px_rgba(58,34,27,0.06)]']) }}>
    @if($title || $desc)
    <div class="px-6 py-5">
        @if($title)
        <h3 class="font-display text-lg font-bold text-[#3A221B] dark:text-[#FAF5E8]">
            {{ $title }}
        </h3>
        @endif
        @if($desc)
            <p class="mt-1 text-sm text-[#7D6A60] dark:text-[#C5B5A8]">
                {{ $desc }}
            </p>
        @endif
    </div>
    @endif

    <div class="p-4 {{ ($title || $desc) ? 'border-t border-[#3C2415]/10 dark:border-white/10' : '' }} sm:p-6">
        <div class="space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
