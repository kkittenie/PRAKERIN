@php
    $laba = $item->laba_bersih ?? 0;
@endphp
@if($laba < 0)
    <span class="text-red-600 dark:text-red-400 font-semibold">-Rp {{ number_format(abs($laba), 0, ',', '.') }}</span>
@else
    <span class="text-green-600 dark:text-green-400 font-semibold">Rp {{ number_format($laba, 0, ',', '.') }}</span>
@endif
