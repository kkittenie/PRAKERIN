@php $varians = $item->produkVarians; @endphp
@if($varians->isNotEmpty())
    @php
        $totalStok = (int) $varians->sum('stok');
        $totalMin = (int) $varians->sum('stok_minimum');
        $jumlahHabis = $varians->filter(fn ($v) => $v->perlu_diproduksi)->count();
        $stokClass = $totalStok <= $totalMin ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
    @endphp
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stokClass }}">{{ $totalStok }}</span>
    <div class="text-xs text-gray-400 mt-0.5 font-medium">{{ $varians->count() }} varian @if($jumlahHabis > 0)&middot; <span class="text-red-500">{{ $jumlahHabis }} habis</span>@endif</div>
@else
    @php
        $stokClass = $item->stok <= $item->stok_minimum ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
    @endphp
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stokClass }}">{{ $item->stok }}</span>
    <div class="text-xs text-gray-400 mt-0.5 font-medium">Min {{ $item->stok_minimum }}</div>
@endif