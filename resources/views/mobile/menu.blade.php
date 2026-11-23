@extends('mobile.layout', ['title' => 'Menu'])

@section('content')

    <style>[x-cloak] { display: none !important; }</style>

    @php
        $searchIndex = [];
        foreach ($daftarBundling as $bundling) {
            $searchIndex[] = ['nama' => strtolower($bundling->nama_bundling), 'kategori' => (string) ($bundling->kategori_id ?: 'lainnya')];
        }
        foreach ($daftarPromoEvent as $promoEvent) {
            $searchIndex[] = ['nama' => strtolower($promoEvent->nama_event), 'kategori' => (string) ($promoEvent->kategori_id ?: 'lainnya')];
        }
        foreach ($kategoris as $kategori) {
            foreach ($kategori->produk as $produk) {
                $searchIndex[] = ['nama' => strtolower($produk->nama_produk), 'kategori' => (string) $kategori->id];
            }
        }
    @endphp

    <script type="application/json" id="menu-search-index">{!! json_encode($searchIndex ?? []) !!}</script>

    <div class="lg:hidden" x-data="{
            search: '',
            activeKategori: 'semua',
            kategoriDropdown: false,
            scrolled: false,
            menuScrollerMaxHeight: 0,
            chipsOffset: 16,
            updateMenuScrollerHeight() {
                this.$nextTick(() => {
                    const el = this.$refs.menuScroller;
                    if (!el) return;
                    const top = el.getBoundingClientRect().top;
                    this.menuScrollerMaxHeight = Math.max(180, window.innerHeight - top - 72);
                });
            },
            updateChipsOffset() {
                this.$nextTick(() => {
                    const header = this.$refs.compactHeader;
                    const tinggiHeader = (this.scrolled && header) ? header.offsetHeight : 0;
                    this.chipsOffset = tinggiHeader > 0 ? tinggiHeader - 8 : 16;
                });
            },
            menuIndex: JSON.parse(document.getElementById('menu-search-index').textContent),
            cocokKategori(id) {
                return this.menuIndex.some(item => item.kategori === id && item.nama.includes(this.search.toLowerCase()));
            },
            adaProdukTampil() {
                return this.menuIndex.some(item =>
                    (this.activeKategori === 'semua' || item.kategori === this.activeKategori) &&
                    item.nama.includes(this.search.toLowerCase())
                );
            },
            taglines: ['Freshly Baked Every Day', 'Skip The Line, Order Ahead', 'Your Favorite Cakes &amp; Bakes', 'Order Now, Pick Up Later'],
            taglineIdx: 0,
         }"
         x-init="
             setInterval(() => taglineIdx = (taglineIdx + 1) % taglines.length, 4500);
             $nextTick(() => {
                 updateMenuScrollerHeight();
                 window.addEventListener('resize', () => { updateMenuScrollerHeight(); updateChipsOffset(); });
             });
         ">

         {{-- Compact scroll header (Mobile only) --}}
         <div x-cloak
             x-show="scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="pointer-events-none absolute inset-x-0 top-0 z-50 lg:hidden" x-ref="compactHeader">
            <div class="bg-[#FAF5E8] px-5 pb-4 pt-3 shadow-md border-b border-[#3C2415]/10 sm:px-8 md:px-10">
                <div class="mx-auto flex max-w-screen-xl items-start gap-3">
                    <a href="{{ route('member.beranda') }}"
                       onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                       class="pointer-events-auto mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-800 transition active:bg-gray-100">
                        <span class="rotate-180">
                            @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                        </span>
                    </a>

                    <div class="min-w-0 flex-1 text-left">
                        <p class="text-[13px] font-medium leading-tight text-gray-800">
                            Pick up from the store
                        </p>
                        <p class="mt-1 truncate text-[18px] font-extrabold leading-tight text-gray-900">
                            {{ $storeNama }}
                        </p>
                        <p class="mt-1 text-[14px] font-medium leading-snug text-gray-500">
                            {{ $storeAlamat }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Brown banner + store info card (Mobile only) --}}
        <div x-show="!scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative -mx-5 sm:-mx-8 md:-mx-10 -mt-6 overflow-hidden rounded-b-[22px] bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 px-5 pb-8 pt-4 text-white lg:hidden">

            <div class="relative flex items-center justify-center gap-2.5">
                <a href="{{ route('member.beranda') }}"
                   onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-white transition active:bg-white/10">
                    <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
                </a>
                <div class="flex items-center justify-center gap-2.5">
                    <span class="flex h-8.5 w-8.5 shrink-0 items-center justify-center rounded-full bg-[#FAF5E8] p-1.5 shadow-xs border border-white/25">
                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Mascot" class="h-full w-full object-contain">
                    </span>
                    <img src="{{ asset('images/logo/logo-wordmark-light.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain">
                </div>
            </div>

            {{-- Rotating tagline --}}
            <div class="relative mx-auto mt-4 h-7 w-56 max-w-full overflow-hidden rounded-full bg-black/25"
                 style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                <template x-for="(tagline, i) in taglines" :key="i">
                    <p x-show="taglineIdx === i"
                       x-transition:enter="transition ease-out duration-700"
                       x-transition:enter-start="opacity-0 -translate-y-2"
                       x-transition:enter-end="opacity-100 translate-y-0"
                       x-transition:leave="transition ease-in duration-500"
                       x-transition:leave-start="opacity-100 translate-y-0"
                       x-transition:leave-end="opacity-0 translate-y-2"
                       x-text="tagline"
                       class="absolute inset-0 flex items-center justify-center text-center text-[10px] font-bold tracking-wide text-white"></p>
                </template>
            </div>
        </div>

        {{-- Store info card (Mobile only) --}}
        <div x-show="!scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="relative z-10 -mt-8 lg:hidden">
            <div class="flex items-center gap-3 rounded-3xl border border-[#3C2415]/10 bg-[#FAF5E8] px-4 py-3.5 shadow-md shadow-[#3C2415]/10">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white p-2 border border-[#3C2415]/10 shadow-xs">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Mascot" class="h-full w-full object-contain">
                </span>
                <div class="min-w-0 flex-1">
                    <h4 class="font-display font-bold text-base text-[#3A221B] leading-tight">Daisy Bakery</h4>
                    <p class="mt-1 text-[13px] font-medium leading-snug text-[#706864]">{{ $storeAlamat }}</p>
                </div>
            </div>
        </div>

        {{-- Desktop Hero Banner & Header Container --}}
        <div class="hidden lg:block sticky top-16 z-30 bg-white/95 backdrop-blur-md pt-1 pb-4 mb-6">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#5a3c27] via-[#6f4b32] to-[#8b5e3c] text-white shadow-lg p-7">
                <svg class="pointer-events-none absolute right-0 top-0 h-full w-1/2 text-white/5" viewBox="0 0 400 300" fill="none">
                    <circle cx="300" cy="150" r="180" stroke="currentColor" stroke-width="40"/>
                    <circle cx="300" cy="150" r="80" stroke="currentColor" stroke-width="20"/>
                </svg>

                <div class="relative z-10 flex items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3.5 py-1 text-xs font-bold text-white backdrop-blur-md">
                            ✨ Freshly Baked Daily
                        </span>
                        <h1 class="mt-2.5 text-2xl lg:text-3xl font-black tracking-tight leading-tight">
                            Order Online & In-Store Pickup
                        </h1>
                        <p class="mt-1.5 text-xs lg:text-sm text-brand-100 font-medium leading-relaxed">
                            Savor our handcrafted cakes, artisanal breads, and signature pastries freshly baked each morning.
                        </p>
                    </div>

                    {{-- Store Location Pill on Desktop --}}
                    <div class="shrink-0 rounded-2xl border border-white/25 bg-white/15 p-4 backdrop-blur-md max-w-xs shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-800 shadow-sm">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 10.5V20a1 1 0 0 0 1 1h4v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5h4a1 1 0 0 0 1-1v-9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2.5 10 12 3l9.5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h4 class="font-display font-bold text-sm text-white leading-tight">Daisy Bakery</h4>
                                <p class="text-[11px] text-white/80 truncate mt-0.5 font-medium">{{ $storeAlamat }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compact Search Bar (Desktop) --}}
            <div class="mt-4 flex items-center justify-between">
                <div class="flex w-full max-w-md items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm focus-within:border-brand-800 focus-within:ring-4 focus-within:ring-brand-800/10 transition">
                    <svg class="h-4.5 w-4.5 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Search cakes, pastries, breads, or promotions..."
                           class="w-full border-0 bg-transparent p-0 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                    <button type="button" x-show="search.length > 0" @click="search = ''" class="text-gray-400 hover:text-gray-600">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <span class="text-xs font-semibold text-gray-400">Select a category on the left to filter the menu</span>
            </div>
        </div>

        @if (count($searchIndex) > 0)
            <div :style="window.innerWidth < 1024 ? `margin-top: ${chipsOffset}px` : ''"
                 class="relative flex items-center gap-2 pb-4 transition-[margin] duration-200 lg:hidden">

                {{-- Search input (Mobile only) --}}
                <div class="flex flex-1 items-center gap-2.5 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Search menu..."
                           class="w-full border-0 bg-transparent p-0 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                </div>

                {{-- Tombol filter kategori (Mobile only) --}}
                <div class="relative shrink-0 lg:hidden" @click.outside="kategoriDropdown = false">
                    <button type="button" @click="kategoriDropdown = ! kategoriDropdown"
                            :class="activeKategori !== 'semua' ? 'bg-brand-800 border-brand-800 text-white' : 'bg-white border-gray-200 text-gray-500'"
                            class="flex h-[46px] w-[46px] items-center justify-center rounded-2xl border shadow-sm transition active:scale-90">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    {{-- Dropdown kategori (Mobile only) --}}
                    <div x-show="kategoriDropdown" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 top-[calc(100%+8px)] z-30 w-52 overflow-hidden rounded-2xl border border-gray-100 bg-white py-1.5 shadow-xl">

                        <button type="button" @click="activeKategori = 'semua'; kategoriDropdown = false"
                                :class="activeKategori === 'semua' ? 'bg-brand-50 text-brand-800' : 'text-gray-600'"
                                class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-bold transition hover:bg-gray-50">
                            All Items
                            <svg x-show="activeKategori === 'semua'" class="h-3.5 w-3.5 text-brand-800" viewBox="0 0 24 24" fill="none">
                                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        @foreach ($kategoris as $kategori)
                            <button type="button" @click="activeKategori = '{{ $kategori->id }}'; kategoriDropdown = false"
                                    :class="activeKategori === '{{ $kategori->id }}' ? 'bg-brand-50 text-brand-800' : 'text-gray-600'"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-bold transition hover:bg-gray-50">
                                {{ $kategori->nama_kategori }}
                                <svg x-show="activeKategori === '{{ $kategori->id }}'" class="h-3.5 w-3.5 text-brand-800" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Desktop Grid: Sidebar Kategori on Left, Product List on Right --}}
        <div class="lg:grid lg:grid-cols-12 lg:gap-6 lg:items-start">
            {{-- Desktop Sticky Sidebar Navigation --}}
            <aside class="hidden lg:block lg:col-span-3 xl:col-span-2 sticky top-[310px] space-y-1">
                <p class="px-3 pb-2 text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Categories</p>
                
                <button type="button" @click="activeKategori = 'semua'"
                        :class="activeKategori === 'semua' ? 'bg-brand-50 text-brand-800 font-extrabold' : 'text-gray-600 hover:bg-gray-50 font-semibold'"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs transition">
                    <span>All Items</span>
                    <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500" :class="activeKategori === 'semua' ? 'bg-brand-100 text-brand-700' : ''">{{ count($searchIndex) }}</span>
                </button>

                @foreach ($kategoris as $kategori)
                    <button type="button" @click="activeKategori = '{{ $kategori->id }}'"
                            :class="activeKategori === '{{ $kategori->id }}' ? 'bg-brand-50 text-brand-800 font-extrabold' : 'text-gray-600 hover:bg-gray-50 font-semibold'"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs transition">
                        <span>{{ $kategori->nama_kategori }}</span>
                        <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500" :class="activeKategori === '{{ $kategori->id }}' ? 'bg-brand-100 text-brand-700' : ''">{{ $kategori->produk->count() + $daftarBundling->where('kategori_id', $kategori->id)->count() + $daftarPromoEvent->where('kategori_id', $kategori->id)->count() }}</span>
                    </button>
                @endforeach
            </aside>

            {{-- Right Product Content Area --}}
            <div class="lg:col-span-9 xl:col-span-10">

        <div x-ref="menuScroller"
             @scroll="
                 if (window.Android && typeof window.Android.laporkanStatusScroll === 'function') {
                     window.Android.laporkanStatusScroll($refs.menuScroller.scrollTop > 0);
                 }
                 const isScrolled = $refs.menuScroller.scrollTop > 10;
                 if (scrolled !== isScrolled) {
                     scrolled = isScrolled;
                     updateChipsOffset();
                     setTimeout(() => updateMenuScrollerHeight(), 240);
                 }
             "
             :style="window.innerWidth >= 1024 ? '' : `max-height: ${menuScrollerMaxHeight}px`"
             class="min-h-0 overflow-y-auto overscroll-contain lg:max-h-[calc(100vh-340px)] lg:overflow-y-auto lg:pr-2"
             style="-webkit-overflow-scrolling:touch;">

        {{-- Menu sections, grouped by category --}}
        @foreach ($kategoris as $kategori)
            <div x-show="(activeKategori === 'semua' || activeKategori === '{{ $kategori->id }}') && cocokKategori('{{ $kategori->id }}')" class="mt-6 first:mt-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">{{ $kategori->nama_kategori }}</h2>
                    <span class="text-[11px] text-gray-400">{{ $kategori->produk->count() + $daftarBundling->where('kategori_id', $kategori->id)->count() + $daftarPromoEvent->where('kategori_id', $kategori->id)->count() }} items</span>
                </div>
                <div class="mt-1 border-t border-dashed border-gray-100"></div>

                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 lg:gap-4" data-grid-kategori="{{ $kategori->id }}">
                    @foreach ($kategori->produk as $produk)
                        @php
                            $hargaAsli = $produk->harga_tampil_asli;
                            $hargaFinal = $produk->harga_tampil_final;
                            $adaPromo = $produk->ada_promo_tampil;
                            $habis = $produk->habis_tampil;
                        @endphp
                        <a href="{{ route('member.produk.show', $produk) }}"
                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                           x-show="'{{ addslashes(strtolower($produk->nama_produk)) }}'.includes(search.toLowerCase())"
                           data-produk-id="{{ $produk->id }}"
                           class="block">
                            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-[#FAF5E8] flex items-center justify-center p-2.5">
                                @if ($produk->foto)
                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                         class="max-h-full max-w-full object-contain filter drop-shadow-xs" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                                    @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                </div>
                                <span data-promo-badge class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm" style="{{ $adaPromo ? '' : 'display:none' }}">Special</span>
                                @if ($habis)
                                    <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Sold Out</span>
                                @endif
                            </div>

                            <div class="pt-2">
                                <p class="text-[10px] font-medium text-gray-400">{{ $kategori->nama_kategori }}</p>
                                <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $produk->nama_produk }}</p>

                                <div class="mt-2 flex items-center justify-between gap-1">
                                    <div class="min-w-0">
                                        <p data-harga-asli class="truncate text-[10px] text-gray-400 line-through" style="{{ $adaPromo ? '' : 'display:none' }}">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</p>
                                        <p data-harga-final class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</p>
                                    </div>

                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach

                    {{-- Bundling & Promo Event ikut tampil di kategori yang dipilih admin --}}
                    @foreach ($daftarBundling->where('kategori_id', $kategori->id) as $bundling)
                        @include('mobile.components.kartu-bundling', ['bundling' => $bundling])
                    @endforeach
                    @foreach ($daftarPromoEvent->where('kategori_id', $kategori->id) as $promoEvent)
                        @include('mobile.components.kartu-promo-event', ['promoEvent' => $promoEvent])
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Bundling / Promo Event lama yang belum punya kategori: hanya tampil di "Semua Menu" --}}
        @php
            $bundlingTanpaKategori = $daftarBundling->whereNull('kategori_id');
            $promoEventTanpaKategori = $daftarPromoEvent->whereNull('kategori_id');
        @endphp
        @if ($bundlingTanpaKategori->isNotEmpty() || $promoEventTanpaKategori->isNotEmpty())
            <div x-show="activeKategori === 'semua' && cocokKategori('lainnya')" class="mt-6 first:mt-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Other Delights</h2>
                    <span class="text-[11px] text-gray-400">{{ $bundlingTanpaKategori->count() + $promoEventTanpaKategori->count() }} items</span>
                </div>
                <div class="mt-1 border-t border-dashed border-gray-100"></div>

                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 lg:gap-4" data-grid-kategori="lainnya">
                    @foreach ($bundlingTanpaKategori as $bundling)
                        @include('mobile.components.kartu-bundling', ['bundling' => $bundling])
                    @endforeach
                    @foreach ($promoEventTanpaKategori as $promoEvent)
                        @include('mobile.components.kartu-promo-event', ['promoEvent' => $promoEvent])
                    @endforeach
                </div>
            </div>
        @endif

        @if (count($searchIndex) === 0)
            <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 text-center text-xs font-medium text-gray-400">
                No menu items available at the moment.
            </div>
        @endif

        {{-- Hasil pencarian/filter kosong --}}
        @if (count($searchIndex) > 0)
            <div x-show="! adaProdukTampil()" x-cloak class="mt-8 flex flex-col items-center rounded-2xl border border-gray-100 bg-white px-6 py-10 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <p class="mt-3 text-sm font-bold text-gray-700">No products found</p>
                <p class="mt-1 text-xs text-gray-400">Try a different keyword or select another category.</p>
            </div>
        @endif

        <p x-show="adaProdukTampil()" class="mt-6 pb-5 text-center text-[11px] text-gray-400">Tap any item to view details &amp; customize</p>
        </div>
        </div>
    </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE MENU SHOWCASE
         Matches reference: Deep Cocoa Curved Hero Canopy with Golden Swirls,
         Rotating "Discover More" Badge, Scallop Category Badges,
         and 3-Column Soft Biscuit Product Cards
         ════════════════════════════════════════════════════════════════ --}}
    @php
        $scallopColors = [
            '#EACE80', // Daisy Gold
            '#E5A1B0', // Soft Rose Pink
            '#D4A574', // Warm Biscuit
            '#C2D2E3', // Cerulean Blue
            '#C9D6B8', // Sage Pistachio
            '#E3A9A0', // Peach
        ];
    @endphp

    <div class="hidden lg:block w-full bg-[#FAF5E8]"
         x-data="{
             desktopTab: (new URLSearchParams(window.location.search)).get('kategori') || 'all',
             searchDesktop: '',
             menuIndex: (() => {
                 try {
                     const el = document.getElementById('menu-search-index');
                     return el ? JSON.parse(el.textContent) : [];
                 } catch(e) {
                     return [];
                 }
             })(),
             isSearching() {
                 return this.searchDesktop.trim().length > 0;
             },
             matchesSearch(name) {
                 if (!this.isSearching()) return true;
                 return name.toLowerCase().includes(this.searchDesktop.toLowerCase().trim());
             },
             cocokKategori(kategoriId) {
                 if (!this.isSearching()) return true;
                 const q = this.searchDesktop.toLowerCase().trim();
                 return this.menuIndex.some(item => String(item.kategori) === String(kategoriId) && item.nama.includes(q));
             },
             hasMatchingProducts() {
                 if (!this.menuIndex || this.menuIndex.length === 0) return false;
                 const q = this.searchDesktop.toLowerCase().trim();
                 return this.menuIndex.some(item => {
                     const matchesCat = (this.desktopTab === 'all' || String(item.kategori) === String(this.desktopTab));
                     const matchesQuery = !this.isSearching() || item.nama.includes(q);
                     return matchesCat && matchesQuery;
                 });
             }
         }">

        {{-- 1. DEEP COCOA MELTING HERO CANOPY --}}
        <section id="menu-desktop-hero" class="relative w-full text-[#FAF5E8] overflow-visible">
            {{-- Top Dark Cocoa Banner (Navbar & Narrative) --}}
            <div class="relative w-full bg-[#3C2415] pt-10 pb-8 sm:pb-9 lg:pb-10 xl:pb-12 text-center overflow-hidden">
                {{-- Background Line Art Bakery Doodles (Cake on Left, Croissant on Right) --}}
                @include('mobile.components.hero-bakery-doodles')

                {{-- Centered Title & Narrative --}}
                <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
                    <h1 class="font-display font-black text-5xl lg:text-6xl xl:text-7xl uppercase tracking-tight text-[#FAF5E8] flex items-center justify-center gap-3.5 drop-shadow-md">
                        <span>OUR</span>
                        <span class="text-[#8E1E26]">FRESH MENU</span>
                    </h1>

                    <p class="mt-4 font-ui text-sm sm:text-base text-[#FAF5E8]/75 max-w-xl mx-auto leading-relaxed">
                        Explore our signature vintage celebration cakes, golden flaky croissants, and wholesome artisan sourdough baked with honest ingredients.
                    </p>
                </div>
            </div>

            {{-- Organic Melting Canopy Bottom SVG on Cream Background with Big Cake & Stamp Nested Inside --}}
            <div class="relative w-full bg-[#FAF5E8] overflow-visible select-none z-20 -mt-1">
                <svg viewBox="0 0 1440 360" fill="none" class="w-full h-64 sm:h-72 lg:h-[350px] xl:h-[380px] block pointer-events-none" preserveAspectRatio="none">
                    <path d="M 0 0 L 1440 0 L 1440 140 C 1360 160, 1310 110, 1260 110 C 1120 110, 960 345, 720 345 C 480 345, 320 110, 180 110 C 130 110, 80 160, 0 140 Z" fill="#3C2415"/>
                </svg>

                {{-- Subtle Ambient Warm Bakery Glow Behind Cake --}}
                <div id="menu-desktop-glow" class="pointer-events-none absolute left-1/2 top-[100px] sm:top-[105px] lg:top-[110px] xl:top-[115px] -translate-x-1/2 -translate-y-1/2 w-[460px] sm:w-[520px] lg:w-[580px] xl:w-[640px] h-[280px] sm:h-[320px] lg:h-[360px] xl:h-[390px] rounded-full blur-3xl opacity-70 z-10"
                     style="background: radial-gradient(circle, rgba(255, 230, 150, 0.45) 0%, rgba(220, 100, 50, 0.22) 42%, rgba(60, 36, 21, 0) 72%);">
                </div>

                {{-- Prominent Centerpiece: Big Vintage Strawberry Celebration Cake (Generously Scaled, Clear from Narrative) --}}
                <div id="menu-desktop-hero-cake" class="absolute left-1/2 -top-2 sm:-top-3 lg:-top-4 xl:-top-5 -translate-x-1/2 pointer-events-none select-none z-20 flex flex-col items-center">
                    <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="Artisanal Daisy Strawberry Celebration Cake"
                         class="w-[230px] sm:w-[260px] lg:w-[290px] xl:w-[310px] h-auto object-contain filter drop-shadow-[0_22px_28px_rgba(20,10,5,0.72)] drop-shadow-[0_8px_14px_rgba(20,10,5,0.52)]">
                </div>

                {{-- Compact Cute Rotating "DISCOVER MORE" Stamp Badge Nested Deep in the Melting Scoop (No Cake Collision) --}}
                <div id="menu-desktop-stamp" class="absolute left-1/2 bottom-5 sm:bottom-6 lg:bottom-7 xl:bottom-8 -translate-x-1/2 z-30">
                    <button type="button"
                            @click="document.getElementById('menu-showcase-content').scrollIntoView({ behavior: 'smooth' })"
                            class="relative w-14 h-14 sm:w-15 sm:h-15 lg:w-16 lg:h-16 flex items-center justify-center group focus:outline-none transition-transform hover:scale-105 active:scale-95 cursor-pointer pointer-events-auto"
                            title="Scroll down to menu">
                        <div class="absolute inset-0 rounded-full bg-[#FAF5E8] border-2 border-[#3C2415]/20 shadow-lg group-hover:border-[#8E1E26]/40 transition-colors"></div>
                        
                        {{-- Rotating Circular Text --}}
                        <svg class="w-11 h-11 sm:w-12 sm:h-12 lg:w-13 lg:h-13 animate-[spin_14s_linear_infinite]" viewBox="0 0 100 100">
                            <path id="circlePathDesktopMenu" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" fill="none" />
                            <text class="text-[9px] font-bold tracking-[0.24em] uppercase fill-[#3C2415]">
                                <textPath href="#circlePathDesktopMenu" startOffset="0%">
                                    DISCOVER MORE • DISCOVER MORE •
                                </textPath>
                            </text>
                        </svg>

                        {{-- Centered Arrow --}}
                        <div class="absolute flex items-center justify-center text-[#8E1E26] group-hover:translate-y-0.5 transition-transform duration-200">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12l7 7 7-7"/>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        {{-- 2. CATEGORY FILTER TABS (SCALLOP BADGES), SEARCH & SHOWCASE --}}
        <section id="menu-desktop-showcase" class="relative w-full bg-[#FAF5E8] overflow-hidden">
            {{-- Strawberry Keyframe Styles (Consistent with Beranda) --}}
            <style>
                @keyframes floatStrawberry {
                    0%, 100% {
                        transform: translateY(0px) rotate(var(--rot, 0deg));
                    }
                    50% {
                        transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg));
                    }
                }
                .desktop-floating-strawberry {
                    animation: floatStrawberry var(--duration, 4s) ease-in-out infinite;
                    will-change: transform;
                }
            </style>

            {{-- 2D Floating Strawberries in Outer Gutters (Positioned Elegantly Away From Screen Edges) --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-8 xl:left-12 2xl:left-20 top-12 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 48px; height: 64px; --rot: -10deg; --duration: 4.2s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-8 xl:left-auto xl:right-12 2xl:right-20 top-20 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: 14deg; --duration: 3.8s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-10 xl:left-14 2xl:left-24 top-1/4 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 42px; height: 56px; --rot: 18deg; --duration: 4.8s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-10 xl:left-auto xl:right-14 2xl:right-24 top-1/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: -14deg; --duration: 4.4s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-9 xl:left-13 2xl:left-22 top-1/2 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 46px; height: 60px; --rot: -12deg; --duration: 5.0s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-9 xl:left-auto xl:right-13 2xl:right-22 top-2/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 48px; height: 64px; --rot: 12deg; --duration: 4.1s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-8 xl:left-12 2xl:left-20 bottom-24 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: 8deg; --duration: 4.6s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-8 xl:left-auto xl:right-12 2xl:right-20 bottom-16 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 46px; height: 60px; --rot: -16deg; --duration: 4.3s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div id="menu-showcase-content" class="relative max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-10 pb-24 z-10">

            {{-- Category Scallop Badges (Dynamically Populated from Database $kategoris) --}}
            <div id="menu-scallop-tabs" class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 lg:gap-10">
                {{-- TAB: ALL --}}
                <button type="button"
                        @click="desktopTab = 'all'"
                        class="group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer">
                    <div class="relative w-16 h-16 sm:w-18 sm:h-18 lg:w-20 lg:h-20 flex items-center justify-center">
                        <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                             :class="desktopTab === 'all' ? 'text-[#8E1E26] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                             viewBox="0 0 100 100" fill="currentColor">
                            <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                        </svg>
                        <div class="relative z-10 w-11 h-11 sm:w-12 sm:h-12 lg:w-13 lg:h-13 rounded-full bg-[#3C2415]/10 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="All Menu" class="w-10 h-10 object-contain">
                        </div>
                    </div>
                    <span class="mt-2.5 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                          :class="desktopTab === 'all' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                        ALL
                    </span>
                </button>

                {{-- DYNAMIC TABS FOR EACH DATABASE CATEGORY --}}
                @foreach ($kategoris as $index => $kategori)
                    @php
                        $tabColor = $scallopColors[$index % count($scallopColors)];
                        $catIcon = $kategori->produk->firstWhere('foto', '!=', null)?->foto;
                    @endphp
                    <button type="button"
                            @click="desktopTab = '{{ $kategori->id }}'"
                            class="group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer">
                        <div class="relative w-16 h-16 sm:w-18 sm:h-18 lg:w-20 lg:h-20 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab == '{{ $kategori->id }}' ? 'scale-105 rotate-6' : 'stroke-2 stroke-[#3C2415]/15 group-hover:rotate-12'"
                                 :style="desktopTab == '{{ $kategori->id }}' ? 'color: {{ $tabColor }}' : 'color: #F5ECE3'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-11 h-11 sm:w-12 sm:h-12 lg:w-13 lg:h-13 rounded-full overflow-hidden shadow-xs flex items-center justify-center bg-white/60">
                                @if ($catIcon)
                                    <img src="{{ asset('storage/' . $catIcon) }}" alt="{{ $kategori->nama_kategori }}" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="{{ $kategori->nama_kategori }}" class="w-9 h-9 object-contain">
                                @endif
                            </div>
                        </div>
                        <span class="mt-2.5 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab == '{{ $kategori->id }}' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            {{ $kategori->nama_kategori }}
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Vintage Biscuit Search Input --}}
            <div id="menu-desktop-search" class="mt-10 max-w-md mx-auto">
                <div class="flex items-center gap-3 rounded-full bg-[#F5ECE3] border border-[#3C2415]/15 px-5 py-2.5 shadow-xs focus-within:border-[#8E1E26] focus-within:ring-2 focus-within:ring-[#8E1E26]/20 transition-all">
                    <svg class="h-4.5 w-4.5 shrink-0 text-[#3C2415]/50" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" x-model="searchDesktop" placeholder="Search fresh breads, pastries, cakes..."
                           class="w-full border-0 bg-transparent p-0 text-xs sm:text-sm font-medium text-[#3C2415] placeholder:text-[#3C2415]/40 focus:outline-none focus:ring-0">
                    <button type="button" x-show="searchDesktop.length > 0" @click="searchDesktop = ''" class="text-[#3C2415]/40 hover:text-[#3C2415]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>

            {{-- DYNAMIC CATEGORY SECTIONS (FROM DATABASE $kategoris) --}}
            <div class="mt-16 space-y-20">
                @foreach ($kategoris as $kategori)
                    @php
                        $kategoriBundling = $daftarBundling->where('kategori_id', $kategori->id);
                        $kategoriPromoEvent = $daftarPromoEvent->where('kategori_id', $kategori->id);
                        $hasItems = $kategori->produk->isNotEmpty() || $kategoriBundling->isNotEmpty() || $kategoriPromoEvent->isNotEmpty();
                    @endphp

                    @if ($hasItems)
                        <div x-show="(desktopTab === 'all' || desktopTab == '{{ $kategori->id }}') && cocokKategori('{{ $kategori->id }}')"
                             class="menu-category-section space-y-8">
                            <h2 class="font-display font-black text-3xl lg:text-4xl uppercase tracking-wider text-[#3C2415] text-center">
                                {{ $kategori->nama_kategori }}
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 xl:gap-10">
                                {{-- 1. Regular Category Products --}}
                                @foreach ($kategori->produk as $produk)
                                    @php
                                        $hargaAsli = $produk->harga_tampil_asli;
                                        $hargaFinal = $produk->harga_tampil_final;
                                        $adaPromo = $produk->ada_promo_tampil;
                                        $habis = $produk->habis_tampil;
                                    @endphp
                                    <div x-show="matchesSearch('{{ addslashes($produk->nama_produk) }}')"
                                         data-produk-id="{{ $produk->id }}"
                                         class="rounded-[32px] bg-[#F5ECE3] p-7 transition-shadow duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-md">
                                        
                                        <a href="{{ route('member.produk.show', $produk) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="block group/card">
                                            <div class="relative h-56 w-full mb-4 flex items-center justify-center">
                                                @if ($produk->foto)
                                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                                         class="max-h-full max-w-full object-contain mix-blend-multiply transition-transform duration-500 group-hover/card:scale-105 select-none pointer-events-none"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                @endif
                                                <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-[#3C2415]/20">
                                                    @include('mobile.components.icon-cup', ['class' => 'h-16 w-16'])
                                                </div>

                                                <span data-promo-badge class="absolute left-2.5 top-2.5 rounded-full bg-[#8E1E26] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-sm"
                                                      style="{{ $adaPromo ? '' : 'display:none' }}">Promo</span>
                                                @if ($habis)
                                                    <span class="absolute left-2.5 bottom-2.5 rounded-full bg-[#3C2415]/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-xs">Sold Out</span>
                                                @endif
                                            </div>

                                            <p class="text-[11px] font-semibold text-[#9C8C80] uppercase tracking-wider mb-1">{{ $kategori->nama_kategori }}</p>
                                            <h3 class="font-display font-bold text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">{{ $produk->nama_produk }}</h3>
                                        </a>

                                        <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p data-harga-asli class="truncate text-xs text-[#9C8C80] line-through font-sans" style="{{ $adaPromo ? '' : 'display:none' }}">
                                                    Rp {{ number_format($hargaAsli, 0, ',', '.') }}
                                                </p>
                                                <p data-harga-final class="truncate text-base lg:text-lg font-extrabold text-[#3C2415] font-sans">
                                                    Rp {{ number_format($hargaFinal, 0, ',', '.') }}
                                                </p>
                                            </div>

                                            <a href="{{ route('member.produk.show', $produk) }}"
                                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                               class="group/btn inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                                <span>SHOP</span>
                                                <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- 2. Category Bundling Items (If any) --}}
                                @foreach ($kategoriBundling as $bundling)
                                    <div x-show="matchesSearch('{{ addslashes($bundling->nama_bundling) }}')"
                                         class="rounded-[32px] bg-[#F5ECE3] border border-[#3C2415]/10 p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-lg hover:-translate-y-1">
                                        <a href="{{ route('member.bundling.show', $bundling) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="block group/card">
                                            <div class="relative h-56 w-full mb-4 rounded-2xl bg-white/50 border border-[#3C2415]/5 flex items-center justify-center p-4 overflow-hidden">
                                                @if ($bundling->foto)
                                                    <img src="{{ asset('storage/' . $bundling->foto) }}" alt="{{ $bundling->nama_bundling }}"
                                                         class="max-h-full max-w-full object-contain filter drop-shadow-md transition-transform duration-500 group-hover/card:scale-105 select-none pointer-events-none">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-[#3C2415]/20">
                                                        @include('mobile.components.icon-cup', ['class' => 'h-16 w-16'])
                                                    </div>
                                                @endif

                                                <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#8E1E26] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                                                    <span>🎁</span> Value Bundle
                                                </span>

                                                @if ($bundling->harga_asli > $bundling->harga)
                                                    <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-[#2E6F40] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                        Save {{ $bundling->hemat_persen }}%
                                                    </span>
                                                @endif

                                                @if ($bundling->perlu_diproduksi)
                                                    <span class="absolute left-3 bottom-3 rounded-full bg-[#3C2415]/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                                                        Sold Out
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="text-[11px] font-black text-[#8E1E26] uppercase tracking-wider">
                                                    BUNDLING • {{ $bundling->jumlah_produk }} PCS
                                                </span>
                                                @if ($bundling->items->isNotEmpty())
                                                    <span class="text-[10px] text-[#7C6558] font-bold">
                                                        {{ $bundling->items->count() }} Varieties
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-display font-black text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">
                                                {{ $bundling->nama_bundling }}
                                            </h3>

                                            @if ($bundling->items->isNotEmpty())
                                                <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-1 italic">
                                                    Includes: {{ $bundling->items->map(fn($it) => ($it->qty > 1 ? $it->qty . 'x ' : '') . ($it->produk?->nama_produk ?? 'Bake'))->take(2)->join(', ') }}{{ $bundling->items->count() > 2 ? ' +' . ($bundling->items->count() - 2) . ' more' : '' }}
                                                </p>
                                            @elseif ($bundling->deskripsi)
                                                <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-2">
                                                    {{ $bundling->deskripsi }}
                                                </p>
                                            @endif
                                        </a>

                                        <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                @if ($bundling->harga_asli > $bundling->harga)
                                                    <p class="truncate text-xs text-[#9C8C80] line-through font-sans">
                                                        Rp {{ number_format($bundling->harga_asli, 0, ',', '.') }}
                                                    </p>
                                                @endif
                                                <p class="truncate text-base lg:text-xl font-display font-black text-[#3C2415]">
                                                    Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                                                </p>
                                            </div>

                                            <a href="{{ route('member.bundling.show', $bundling) }}"
                                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                               class="group/btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                                <span>SHOP</span>
                                                <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- 3. Category Promo Event Items (If any) --}}
                                @foreach ($kategoriPromoEvent as $promoEvent)
                                    <div x-show="matchesSearch('{{ addslashes($promoEvent->nama_event) }}')"
                                         class="rounded-[32px] bg-[#F5ECE3] border border-[#3C2415]/10 p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-lg hover:-translate-y-1">
                                        <a href="{{ route('member.promo-event.show', $promoEvent) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="block group/card">
                                            <div class="relative h-56 w-full mb-4 rounded-2xl bg-white/50 border border-[#3C2415]/5 flex items-center justify-center p-4 overflow-hidden">
                                                @if ($promoEvent->foto)
                                                    <img src="{{ asset('storage/' . $promoEvent->foto) }}" alt="{{ $promoEvent->nama_event }}"
                                                         class="max-h-full max-w-full object-contain filter drop-shadow-md transition-transform duration-500 group-hover/card:scale-105 select-none pointer-events-none">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-[#3C2415]/20">
                                                        @include('mobile.components.icon-cup', ['class' => 'h-16 w-16'])
                                                    </div>
                                                @endif

                                                <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#8E1E26] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                                                    <span>★</span> Special Event
                                                </span>

                                                @if ($promoEvent->harga_asli > $promoEvent->harga_event)
                                                    <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-[#2E6F40] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                        Save {{ $promoEvent->hemat_persen }}%
                                                    </span>
                                                @endif

                                                @if ($promoEvent->perlu_diproduksi)
                                                    <span class="absolute left-3 bottom-3 rounded-full bg-[#3C2415]/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                                                        Sold Out
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <span class="text-[11px] font-black text-[#8E1E26] uppercase tracking-wider">
                                                    EVENT PROMO • {{ $promoEvent->isi_event }} PCS PACK
                                                </span>
                                                @if ($promoEvent->produk)
                                                    <span class="text-[10px] text-[#7C6558] font-bold truncate max-w-[120px]">
                                                        {{ $promoEvent->produk->nama_produk }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-display font-black text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">
                                                {{ $promoEvent->nama_event }}
                                            </h3>

                                            <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-1 italic">
                                                Special pack of {{ $promoEvent->isi_event }}x {{ $promoEvent->produk?->nama_produk ?? 'Fresh Bake' }}
                                            </p>
                                        </a>

                                        <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                @if ($promoEvent->harga_asli > $promoEvent->harga_event)
                                                    <p class="truncate text-xs text-[#9C8C80] line-through font-sans">
                                                        Rp {{ number_format($promoEvent->harga_asli, 0, ',', '.') }}
                                                    </p>
                                                @endif
                                                <p class="truncate text-base lg:text-xl font-display font-black text-[#3C2415]">
                                                    Rp {{ number_format($promoEvent->harga_event, 0, ',', '.') }}
                                                </p>
                                            </div>

                                            <a href="{{ route('member.promo-event.show', $promoEvent) }}"
                                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                               class="group/btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                                <span>SHOP</span>
                                                <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                {{-- Bundling / Promo Event without Category (Displayed under 'Menu Lainnya' on ALL tab) --}}
                @php
                    $desktopBundlingLainnya = $daftarBundling->whereNull('kategori_id');
                    $desktopPromoEventLainnya = $daftarPromoEvent->whereNull('kategori_id');
                @endphp
                @if ($desktopBundlingLainnya->isNotEmpty() || $desktopPromoEventLainnya->isNotEmpty())
                    <div x-show="desktopTab === 'all' && cocokKategori('lainnya')"
                         class="menu-category-section space-y-8">
                        <h2 class="font-display font-black text-3xl lg:text-4xl uppercase tracking-wider text-[#3C2415] text-center">
                            MORE SPECIALS
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 xl:gap-10">
                            @foreach ($desktopBundlingLainnya as $bundling)
                                <div x-show="matchesSearch('{{ addslashes($bundling->nama_bundling) }}')"
                                     class="rounded-[32px] bg-[#F5ECE3] border border-[#3C2415]/10 p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-lg hover:-translate-y-1">
                                    <a href="{{ route('member.bundling.show', $bundling) }}"
                                       onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                       class="block group/card">
                                        <div class="relative h-56 w-full mb-4 rounded-2xl bg-white/50 border border-[#3C2415]/5 flex items-center justify-center p-4 overflow-hidden">
                                            @if ($bundling->foto)
                                                <img src="{{ asset('storage/' . $bundling->foto) }}" alt="{{ $bundling->nama_bundling }}"
                                                     class="max-h-full max-w-full object-contain filter drop-shadow-md transition-transform duration-500 group-hover/card:scale-105 select-none pointer-events-none">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-[#3C2415]/20">
                                                    @include('mobile.components.icon-cup', ['class' => 'h-16 w-16'])
                                                </div>
                                            @endif

                                            <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#8E1E26] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                                                <span>🎁</span> Value Bundle
                                            </span>

                                            @if ($bundling->harga_asli > $bundling->harga)
                                                <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-[#2E6F40] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                    Save {{ $bundling->hemat_persen }}%
                                                </span>
                                            @endif

                                            @if ($bundling->perlu_diproduksi)
                                                <span class="absolute left-3 bottom-3 rounded-full bg-[#3C2415]/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                                                    Sold Out
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-[11px] font-black text-[#8E1E26] uppercase tracking-wider">
                                                BUNDLING • {{ $bundling->jumlah_produk }} PCS
                                            </span>
                                            @if ($bundling->items->isNotEmpty())
                                                <span class="text-[10px] text-[#7C6558] font-bold">
                                                    {{ $bundling->items->count() }} Varieties
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="font-display font-black text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">
                                            {{ $bundling->nama_bundling }}
                                        </h3>

                                        @if ($bundling->items->isNotEmpty())
                                            <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-1 italic">
                                                Includes: {{ $bundling->items->map(fn($it) => ($it->qty > 1 ? $it->qty . 'x ' : '') . ($it->produk?->nama_produk ?? 'Bake'))->take(2)->join(', ') }}{{ $bundling->items->count() > 2 ? ' +' . ($bundling->items->count() - 2) . ' more' : '' }}
                                            </p>
                                        @elseif ($bundling->deskripsi)
                                            <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-2">
                                                {{ $bundling->deskripsi }}
                                            </p>
                                        @endif
                                    </a>

                                    <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            @if ($bundling->harga_asli > $bundling->harga)
                                                <p class="truncate text-xs text-[#9C8C80] line-through font-sans">
                                                    Rp {{ number_format($bundling->harga_asli, 0, ',', '.') }}
                                                </p>
                                            @endif
                                            <p class="truncate text-base lg:text-xl font-display font-black text-[#3C2415]">
                                                Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <a href="{{ route('member.bundling.show', $bundling) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="group/btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                            <span>SHOP</span>
                                            <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach

                            @foreach ($desktopPromoEventLainnya as $promoEvent)
                                <div x-show="matchesSearch('{{ addslashes($promoEvent->nama_event) }}')"
                                     class="rounded-[32px] bg-[#F5ECE3] border border-[#3C2415]/10 p-7 transition-all duration-300 relative flex flex-col justify-between shadow-[0_4px_20px_rgba(74,46,39,0.04)] hover:shadow-lg hover:-translate-y-1">
                                    <a href="{{ route('member.promo-event.show', $promoEvent) }}"
                                       onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                       class="block group/card">
                                        <div class="relative h-56 w-full mb-4 rounded-2xl bg-white/50 border border-[#3C2415]/5 flex items-center justify-center p-4 overflow-hidden">
                                            @if ($promoEvent->foto)
                                                <img src="{{ asset('storage/' . $promoEvent->foto) }}" alt="{{ $promoEvent->nama_event }}"
                                                     class="max-h-full max-w-full object-contain filter drop-shadow-md transition-transform duration-500 group-hover/card:scale-105 select-none pointer-events-none">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-[#3C2415]/20">
                                                    @include('mobile.components.icon-cup', ['class' => 'h-16 w-16'])
                                                </div>
                                            @endif

                                            <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#8E1E26] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                                                <span>★</span> Special Event
                                            </span>

                                            @if ($promoEvent->harga_asli > $promoEvent->harga_event)
                                                <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-[#2E6F40] px-2.5 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                                    Save {{ $promoEvent->hemat_persen }}%
                                                </span>
                                            @endif

                                            @if ($promoEvent->perlu_diproduksi)
                                                <span class="absolute left-3 bottom-3 rounded-full bg-[#3C2415]/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                                                    Sold Out
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-[11px] font-black text-[#8E1E26] uppercase tracking-wider">
                                                EVENT PROMO • {{ $promoEvent->isi_event }} PCS PACK
                                            </span>
                                            @if ($promoEvent->produk)
                                                <span class="text-[10px] text-[#7C6558] font-bold truncate max-w-[120px]">
                                                    {{ $promoEvent->produk->nama_produk }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="font-display font-black text-lg lg:text-xl text-[#3C2415] leading-snug line-clamp-2">
                                            {{ $promoEvent->nama_event }}
                                        </h3>

                                        <p class="text-xs text-[#7C6558] mt-1.5 line-clamp-1 italic">
                                            Special pack of {{ $promoEvent->isi_event }}x {{ $promoEvent->produk?->nama_produk ?? 'Fresh Bake' }}
                                        </p>
                                    </a>

                                    <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            @if ($promoEvent->harga_asli > $promoEvent->harga_event)
                                                <p class="truncate text-xs text-[#9C8C80] line-through font-sans">
                                                    Rp {{ number_format($promoEvent->harga_asli, 0, ',', '.') }}
                                                </p>
                                            @endif
                                            <p class="truncate text-base lg:text-xl font-display font-black text-[#3C2415]">
                                                Rp {{ number_format($promoEvent->harga_event, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <a href="{{ route('member.promo-event.show', $promoEvent) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="group/btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                            <span>SHOP</span>
                                            <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="group/btn inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-[#FAF5E8] text-xs font-bold transition-all duration-200 active:scale-95 shadow-xs">
                                            <span>SHOP</span>
                                            <span class="inline-block transition-transform duration-200 group-hover/btn:rotate-90 text-sm leading-none font-bold">+</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Empty State: Displayed when no products match the search query --}}
                <div x-show="!hasMatchingProducts()"
                     x-cloak
                     class="py-16 px-6 text-center max-w-xl mx-auto">
                    <div class="relative mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-[#F5ECE3] border-2 border-[#3C2415]/10 shadow-sm text-[#8E1E26]">
                        {{-- Daisy Bakery Scalloped Badge / Search Icon --}}
                        <svg class="h-10 w-10 text-[#3C2415]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <line x1="8" y1="11" x2="14" y2="11" stroke="#8E1E26" stroke-width="2.5"></line>
                        </svg>
                    </div>

                    <h3 class="font-display font-black text-2xl lg:text-3xl text-[#3C2415] tracking-wide mb-3">
                        No Products Found
                    </h3>

                    <p class="font-sans text-sm lg:text-base text-[#7C6558] font-medium leading-relaxed max-w-md mx-auto mb-8">
                        <template x-if="isSearching() && desktopTab !== 'all'">
                            <span>We couldn’t find any treats matching <span class="font-bold text-[#8E1E26]" x-text="'“' + searchDesktop + '”'"></span> in this category. Try exploring all categories or searching with a different keyword.</span>
                        </template>
                        <template x-if="isSearching() && desktopTab === 'all'">
                            <span>Sorry, we couldn’t find any freshly baked goods matching <span class="font-bold text-[#8E1E26]" x-text="'“' + searchDesktop + '”'"></span>. Try a different keyword or browse our full menu.</span>
                        </template>
                        <template x-if="!isSearching()">
                            <span>There are no menu items available in this category at the moment.</span>
                        </template>
                    </p>

                    <div class="flex items-center justify-center gap-3">
                        <button type="button"
                                @click="searchDesktop = ''; desktopTab = 'all'"
                                class="group inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-white text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-md">
                            <span>VIEW ALL MENU</span>
                            <svg class="h-3.5 w-3.5 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

        {{-- ━━━━ GSAP 3 & SCROLLTRIGGER INTERACTIVE STORYTELLING ENGINE (DESKTOP >= 1024px) ━━━━ --}}
        <script src="{{ asset('vendor/gsap/gsap.min.js') }}"></script>
        <script src="{{ asset('vendor/gsap/ScrollTrigger.min.js') }}"></script>
        <script src="{{ asset('js/desktop-storytelling.js') }}"></script>
    </div>

    @include('mobile.components.floating-cart')

    <script>
        // Live update: badge & harga promo produk, plus tambah/update/hapus kartu
        // Promo Event otomatis — tanpa reload — kalau admin ubah data di dashboard.
        (function () {
            const formatRupiah = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

            const sinkronPromoProduk = (data) => {
                document.querySelectorAll('[data-produk-id]').forEach(el => {
                    const info = data[el.dataset.produkId];
                    if (!info) return;

                    const badge = el.querySelector('[data-promo-badge]');
                    const hargaAsli = el.querySelector('[data-harga-asli]');
                    const hargaFinal = el.querySelector('[data-harga-final]');

                    if (badge) badge.style.display = info.ada_promo ? '' : 'none';
                    if (hargaAsli) {
                        hargaAsli.style.display = info.ada_promo ? '' : 'none';
                        hargaAsli.textContent = formatRupiah(info.harga_asli);
                    }
                    if (hargaFinal) hargaFinal.textContent = formatRupiah(info.harga_final);
                });
            };

            const buatKartuPromoEvent = (pe) => {
                const a = document.createElement('a');
                a.href = pe.url;
                a.dataset.promoEventId = pe.id;
                a.className = 'block';
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (typeof animateNavigate === 'function') animateNavigate(a.href, 'forward');
                    else window.location.href = a.href;
                });

                const fotoHtml = pe.produk_foto
                    ? `<img src="${pe.produk_foto}" alt="${pe.nama_event}" class="h-full w-full object-cover">`
                    : `<div class="flex h-full w-full items-center justify-center text-brand-200"></div>`;

                a.innerHTML = `
                    <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-50">
                        ${fotoHtml}
                        <span class="absolute left-2 top-2 rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm">Promo Event</span>
                        <span data-badge-diproduksi class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white" style="${pe.perlu_diproduksi ? '' : 'display:none'}">Sedang Diproduksi</span>
                    </div>
                    <div class="pt-2">
                        <p class="text-[10px] font-medium text-gray-400">${pe.produk_nama}</p>
                        <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">${pe.nama_event}</p>
                        <div class="mt-2 flex items-center justify-between gap-1">
                            <div class="min-w-0">
                                <p data-harga class="truncate text-xs font-extrabold text-gray-900">${formatRupiah(pe.harga_event)}</p>
                            </div>
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                `;
                return a;
            };

            const sinkronPromoEvent = (daftar) => {
                const idAktif = daftar.map(pe => String(pe.id));

                // Hapus kartu yang sudah tidak berlaku (kadaluwarsa/dinonaktifkan)
                document.querySelectorAll('[data-promo-event-id]').forEach(el => {
                    if (!idAktif.includes(el.dataset.promoEventId)) el.remove();
                });

                // Update kartu yang masih ada, tambahkan yang baru ke grid kategorinya
                daftar.forEach(pe => {
                    let el = document.querySelector(`[data-promo-event-id="${pe.id}"]`);
                    if (!el) {
                        const grid = document.querySelector(`[data-grid-kategori="${pe.kategori_id || 'lainnya'}"]`);
                        if (!grid) return; // kategorinya belum tampil di halaman ini: muncul setelah halaman dimuat ulang
                        grid.appendChild(buatKartuPromoEvent(pe));
                        return;
                    }
                    const badge = el.querySelector('[data-badge-diproduksi]');
                    const harga = el.querySelector('[data-harga]');
                    if (badge) badge.style.display = pe.perlu_diproduksi ? '' : 'none';
                    if (harga) harga.textContent = formatRupiah(pe.harga_event);
                });
            };

            const sinkron = () => {
                const produkIds = Array.from(document.querySelectorAll('[data-produk-id]')).map(el => el.dataset.produkId);
                fetch("{{ route('member.promo-status') }}?produk_ids=" + produkIds.join(','))
                    .then(r => r.json())
                    .then(r => {
                        if (r.status !== 'success') return;
                        sinkronPromoProduk(r.data.produk);
                        sinkronPromoEvent(r.data.promo_event);
                    })
                    .catch(() => {});
            };

            setInterval(sinkron, 15000);
        })();
    </script>

@endsection