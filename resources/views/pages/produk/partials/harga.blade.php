@php $varians = $item->detailProduks; @endphp
@if($varians->isNotEmpty())
    @php
        // Produk Tunggal bervarian: rentang harga diambil dari harga tiap varian.
        $daftarHarga = $item->daftarHargaJualTampil();
        $hargaMin = $daftarHarga->min();
        $hargaMax = $daftarHarga->max();
    @endphp
    @if($hargaMin == $hargaMax)
        Rp {{ number_format($hargaMin, 0, ',', '.') }}
    @else
        Rp {{ number_format($hargaMin, 0, ',', '.') }} &ndash; Rp {{ number_format($hargaMax, 0, ',', '.') }}
    @endif
    @unless($item->is_global)
        <div class="text-[11px] font-medium text-gray-400">{{ $varians->count() }} varian</div>
    @endunless
@else
    <span class="text-xs text-gray-400 italic">-</span>
@endif