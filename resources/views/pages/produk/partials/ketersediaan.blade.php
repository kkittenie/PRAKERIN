@php $varians = $item->produkVarians; @endphp
@if($varians->isNotEmpty())
    @php $jumlahHabis = $varians->filter(fn ($v) => $v->perlu_diproduksi)->count(); @endphp
    @if($jumlahHabis === $varians->count())
        <span class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400">Perlu Diproduksi</span>
    @elseif($jumlahHabis > 0)
        <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400">Sebagian Habis</span>
    @else
        <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Ready</span>
    @endif
@else
    @if($item->stok <= 0 || $item->status_ketersediaan == 'perlu_diproduksi')
        <span class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400">Perlu Diproduksi</span>
    @else
        <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Ready</span>
    @endif
@endif