<div class="inline-flex flex-col items-center justify-center gap-1">
    {{-- Badge metode pembayaran: tampil di atas status pada mode responsif tab/mobile --}}
    <span class="badge-metode-responsive items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ strtolower($item->metode_pembayaran ?? '') === 'tunai' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400' }}">
        {{ strtolower($item->metode_pembayaran ?? '') === 'qris' ? 'QRIS' : ucfirst($item->metode_pembayaran ?? '-') }}
    </span>

    {{-- Badge status pembayaran --}}
    @if(strtolower($item->status_pembayaran ?? '') === 'lunas')
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">Lunas</span>
    @else
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">Belum Bayar</span>
    @endif
</div>
