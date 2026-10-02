@if($item->punya_varian_rasa && $item->produkVarianRasas->isNotEmpty())
    @php
        $rasaList = $item->produkVarianRasas
            ->filter(fn($pvr) => $pvr->varianRasa)
            ->pluck('varianRasa.nama_rasa')
            ->toArray();
    @endphp
    @if(count($rasaList))
        <span class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-snug">
            {{ implode(', ', $rasaList) }}
        </span>
    @else
        <span class="text-xs text-gray-400 italic">-</span>
    @endif
@else
    <span class="text-xs text-gray-400 italic">-</span>
@endif
