@if(is_null($item->stok))
    <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">Tidak terbatas</span>
@else
    {{ $item->stok }}
@endif
