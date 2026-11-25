@php
    $stokFmt = rtrim(rtrim(number_format($item->stok, 2, '.', ''), '0'), '.');
    $minFmt = rtrim(rtrim(number_format($item->stok_minimum, 2, '.', ''), '0'), '.');
    $class = $item->stok <= $item->stok_minimum ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $class }}">{{ $stokFmt }}</span>
<div class="text-[10px] text-gray-400 mt-0.5">Min {{ $minFmt }}</div>
