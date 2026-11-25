<a href="{{ route('member.promo-event.show', $promoEvent) }}"
   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
   x-show="'{{ addslashes(strtolower($promoEvent->nama_event)) }}'.includes(search.toLowerCase())"
   data-promo-event-id="{{ $promoEvent->id }}"
   class="block">
    <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-[#FAF5E8] flex items-center justify-center p-2.5">
        @if ($promoEvent->produk && $promoEvent->produk->foto)
            <img src="{{ asset('storage/' . $promoEvent->produk->foto) }}" alt="{{ $promoEvent->nama_event }}"
                 class="max-h-full max-w-full object-contain filter drop-shadow-xs" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        @endif
        <div class="{{ ($promoEvent->produk && $promoEvent->produk->foto) ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
            @include('mobile.components.icon-cake', ['class' => 'h-10 w-10'])
        </div>
        <span class="absolute left-2 top-2 rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm">Promo Event</span>
        <span data-badge-diproduksi class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white" style="{{ $promoEvent->perlu_diproduksi ? '' : 'display:none' }}">Sold Out</span>
    </div>

    <div class="pt-2">
        <p class="text-[10px] font-medium text-gray-400">{{ $promoEvent->produk->nama_produk ?? '' }}</p>
        <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $promoEvent->nama_event }}</p>

        <div class="mt-2 flex items-center justify-between gap-1">
            <div class="min-w-0">
                <p data-harga class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($promoEvent->harga_event, 0, ',', '.') }}</p>
            </div>

            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
        </div>
    </div>
</a>
