<a href="{{ route('member.bundling.show', $bundling) }}"
                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           x-show="'{{ addslashes(strtolower($bundling->nama_bundling)) }}'.includes(search.toLowerCase())"
                           class="block">
                            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-50">
                                @if ($bundling->gambar)
                                    <img src="{{ asset('storage/' . $bundling->gambar) }}" alt="{{ $bundling->nama_bundling }}"
                                         class="h-full w-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="{{ $bundling->gambar ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                                    @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                </div>
                                <span class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm">Bundling</span>
                                @if ($bundling->perlu_diproduksi)
                                    <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Perlu Diproduksi</span>
                                @endif
                            </div>

                            <div class="pt-2">
                                <p class="text-[10px] font-medium text-gray-400">Bundling</p>
                                <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $bundling->nama_bundling }}</p>

                                <div class="mt-2 flex items-center justify-between gap-1">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($bundling->harga, 0, ',', '.') }}</p>
                                    </div>

                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>