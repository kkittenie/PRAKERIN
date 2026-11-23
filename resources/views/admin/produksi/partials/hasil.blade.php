<span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">{{ $item->jumlah_hasil }} pcs</span>
<div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $item->rincian_ukuran }}</div>
@if($item->punya_rincian_rasa)
    <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $item->rincian_rasa }}</div>
@endif