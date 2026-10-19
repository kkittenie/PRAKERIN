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

    <div x-data="{
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
            <div class="bg-white px-5 pb-4 pt-3 shadow-sm sm:px-8 md:px-10">
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

            <div class="relative flex items-center justify-center">
                <a href="{{ route('member.beranda') }}"
                   onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-white transition active:bg-white/10">
                    <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
                </a>
                <h1 class="text-theme-sm font-bold text-white">Saisho Cake & Bakery</h1>
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
            <div class="flex items-center gap-3 rounded-3xl border border-white/10 bg-white px-4 py-4 shadow-lg shadow-black/20">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path d="M4 10.5V20a1 1 0 0 0 1 1h4v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5h4a1 1 0 0 0 1-1v-9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2.5 10 12 3l9.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold leading-tight text-gray-900">{{ $storeNama }}</p>
                    <p class="mt-0.5 text-[14px] leading-snug text-gray-400">{{ $storeAlamat }}</p>
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
                            Pesan Online & Ambil di Outlet
                        </h1>
                        <p class="mt-1.5 text-xs lg:text-sm text-brand-100 font-medium leading-relaxed">
                            Nikmati kelezatan donat, kue spesial, dan pastry pilihan dari Saisho Bakery.
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
                                <p class="text-xs font-extrabold text-white truncate">{{ $storeNama }}</p>
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
                    <input type="text" x-model="search" placeholder="Cari roti, cake, kopi, atau promo..."
                           class="w-full border-0 bg-transparent p-0 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                    <button type="button" x-show="search.length > 0" @click="search = ''" class="text-gray-400 hover:text-gray-600">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <span class="text-xs font-semibold text-gray-400">Pilih kategori di sebelah kiri untuk menyaring menu</span>
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
                    <input type="text" x-model="search" placeholder="Cari menu..."
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
                            Semua Menu
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
                <p class="px-3 pb-2 text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Kategori</p>
                
                <button type="button" @click="activeKategori = 'semua'"
                        :class="activeKategori === 'semua' ? 'bg-brand-50 text-brand-800 font-extrabold' : 'text-gray-600 hover:bg-gray-50 font-semibold'"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs transition">
                    <span>Semua Menu</span>
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
                    <span class="text-[11px] text-gray-400">{{ $kategori->produk->count() + $daftarBundling->where('kategori_id', $kategori->id)->count() + $daftarPromoEvent->where('kategori_id', $kategori->id)->count() }} item</span>
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
                            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-50">
                                @if ($produk->foto)
                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                         class="h-full w-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                                    @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                </div>
                                <span data-promo-badge class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm" style="{{ $adaPromo ? '' : 'display:none' }}">Promo</span>
                                @if ($habis)
                                    <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Perlu Diproduksi</span>
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
                    <h2 class="text-sm font-bold text-gray-900">Menu Lainnya</h2>
                    <span class="text-[11px] text-gray-400">{{ $bundlingTanpaKategori->count() + $promoEventTanpaKategori->count() }} item</span>
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
                Belum ada menu tersedia saat ini.
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
                <p class="mt-3 text-sm font-bold text-gray-700">Produk tidak ditemukan</p>
                <p class="mt-1 text-xs text-gray-400">Coba kata kunci lain atau pilih kategori berbeda.</p>
            </div>
        @endif

        <p x-show="adaProdukTampil()" class="mt-6 pb-5 text-center text-[11px] text-gray-400">Tap menu untuk lihat detail &amp; pesan</p>
        </div>
        </div>
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
                        <span data-badge-diproduksi class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white" style="${pe.perlu_diproduksi ? '' : 'display:none'}">Perlu Diproduksi</span>
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