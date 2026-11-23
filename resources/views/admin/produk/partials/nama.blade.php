<a href="{{ route('produk.show', $item->id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition font-semibold text-gray-900 dark:text-white text-xs sm:text-sm max-w-[180px] block leading-snug line-clamp-2" title="{{ $item->nama_produk }}">
    {{ $item->nama_produk }}
</a>
@unless($item->dijual_satuan)
    <span class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Hanya untuk bundling</span>
@endunless