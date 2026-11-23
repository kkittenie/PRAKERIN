<div class="flex items-center gap-3">
    @if($item->gambar)
        <img src="{{ asset('storage/'.$item->gambar) }}" class="w-11 h-11 aspect-square rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0 shadow-xs">
    @else
        <div class="w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
    @endif
    <a href="{{ route('reward-voucher.show', $item->id) }}" class="font-semibold text-gray-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition">
        {{ $item->nama }}
    </a>
</div>
