<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ strtolower($item->metode_pembayaran ?? '') === 'tunai' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400' }}">
    {{ strtolower($item->metode_pembayaran ?? '') === 'qris' ? 'QRIS' : ucfirst($item->metode_pembayaran ?? '-') }}
</span>
