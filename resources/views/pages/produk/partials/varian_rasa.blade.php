@php
    $varians = $item->produkVarians;
    $daftarUkuran = $varians->pluck('ukuran')->filter()->unique()->values();
    $daftarVariasi = $varians->pluck('variasi')->filter()->unique()->values();
@endphp
@if($daftarUkuran->isNotEmpty() || $daftarVariasi->isNotEmpty())
    <div class="text-xs leading-snug text-left">
        @if($daftarUkuran->isNotEmpty())
            <div class="text-gray-700 dark:text-gray-300 font-medium"><span class="text-gray-400">Ukuran:</span> {{ $daftarUkuran->implode(', ') }}</div>
        @endif
        @if($daftarVariasi->isNotEmpty())
            <div class="text-gray-700 dark:text-gray-300 font-medium"><span class="text-gray-400">Variasi:</span> {{ $daftarVariasi->implode(', ') }}</div>
        @endif
    </div>
@else
    <span class="text-xs text-gray-400 italic">-</span>
@endif