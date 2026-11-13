@php
    $label = $shift->labelSelisih();
    $warna = match ($label) {
        'Sesuai' => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        'Lebih' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
        'Kurang' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
        default => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    };
@endphp
@if ($label === null)
    <span class="text-gray-400">-</span>
@else
    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $warna }}">
        @if ($label === 'Sesuai')
            Sesuai
        @else
            {{ $label }} Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
        @endif
    </span>
@endif