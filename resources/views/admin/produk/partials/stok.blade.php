@php $varians = $item->detailProduks; @endphp
@if($varians->isNotEmpty())
    @php
        $totalStok = (int) $varians->sum('stok');
        $totalMin = (int) $varians->sum('stok_minimum');
        $jumlahHabis = $varians->filter(fn ($v) => $v->perlu_diproduksi)->count();
        $stokClass = $totalStok <= $totalMin ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
    @endphp
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stokClass }}">{{ $totalStok }}</span>
    @unless($item->is_global)
        <div class="text-xs text-gray-400 mt-0.5 font-medium">{{ $varians->count() }} varian @if($jumlahHabis > 0)&middot; <span class="text-red-500">{{ $jumlahHabis }} habis</span>@endif</div>
    @else
        {{-- Stok Bersama: stok satu untuk semua varian (topping dsb.), jadi cukup badge jumlah varian. --}}
        @if($item->jumlahVarianBersama() > 0)
            <div class="mt-1">
                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $item->jumlahVarianBersama() }} varian</span>
            </div>
        @endif
    @endunless
@else
    <span class="text-xs text-gray-400 italic">-</span>
@endif