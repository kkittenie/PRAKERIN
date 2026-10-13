@php $kodes = $item->detailProduks->pluck('kode_barcode')->filter()->values(); @endphp
@if($kodes->isEmpty())
    <span class="text-xs text-gray-400 italic">-</span>
@else
    <div class="text-xs leading-snug text-left">
        @foreach($kodes->take(3) as $kode)
            <div>{{ $kode }}</div>
        @endforeach
        @if($kodes->count() > 3)
            <div class="text-[11px] text-gray-400">+{{ $kodes->count() - 3 }} lainnya</div>
        @endif
    </div>
@endif