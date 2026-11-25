@if($item->perlu_diproduksi)
    <span class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-xs text-orange-700 dark:text-orange-400">Sedang Diproduksi</span>
@else
    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-700 dark:text-emerald-400">Ready</span>
@endif
