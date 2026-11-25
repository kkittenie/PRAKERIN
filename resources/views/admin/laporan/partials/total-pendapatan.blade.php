@if(($item->total_pendapatan ?? 0) < 0)
    <span class="text-red-600 dark:text-red-400 font-semibold">-Rp {{ number_format(abs($item->total_pendapatan), 0, ',', '.') }}</span>
@else
    <span>Rp {{ number_format($item->total_pendapatan ?? 0, 0, ',', '.') }}</span>
@endif