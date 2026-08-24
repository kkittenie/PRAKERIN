@if($item->maksimal_redeem)
    {{ $item->jumlah_redeem ?? 0 }} / {{ $item->maksimal_redeem }}
@else
    <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">Tidak terbatas</span>
@endif
