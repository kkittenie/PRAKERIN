<span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">
    {{ \App\Models\Satuan::formatAngka($item->jumlah, $item->bahanBaku->satuan?->tipe) }} {{ $item->bahanBaku->satuan?->nama ?? '-' }}
</span>