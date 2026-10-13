@php $varians = $item->detailProduks; @endphp
@if($varians->isNotEmpty())
    @php
        $hargaMin = $varians->min('harga_jual');
        $hargaMax = $varians->max('harga_jual');
    @endphp
    @if($hargaMin == $hargaMax)
        Rp {{ number_format($hargaMin, 0, ',', '.') }}
    @else
        Rp {{ number_format($hargaMin, 0, ',', '.') }} &ndash; Rp {{ number_format($hargaMax, 0, ',', '.') }}
    @endif
    <div class="text-[11px] font-medium text-gray-400">{{ $varians->count() }} varian</div>
@else
    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
@endif