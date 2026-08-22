@if($item->stok <= 0 || $item->status_ketersediaan == 'perlu_diproduksi')
    <span class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400">Perlu Diproduksi</span>
@else
    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Ready</span>
@endif
