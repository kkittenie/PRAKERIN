@if ($item->kode_pickup)
    <span class="font-mono text-sm font-bold tracking-[0.2em] text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md">{{ $item->kode_pickup }}</span>
@else
    <span class="text-xs text-gray-400">-</span>
@endif