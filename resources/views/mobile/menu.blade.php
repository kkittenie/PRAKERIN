@extends('mobile.layout', ['title' => 'Menu'])

@section('content')

    <style>[x-cloak] { display: none !important; }</style>

    @php
        $searchIndex = [];
        foreach ($daftarBundling as $bundling) {
            $searchIndex[] = ['nama' => strtolower($bundling->nama_bundling), 'kategori' => 'bundling'];
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

        {{-- Compact header: hanya muncul saat area produk di-scroll.
             Header awal di bawah ini TIDAK diubah ukurannya/posisinya. --}}
        <div x-cloak
             x-show="scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="pointer-events-none absolute inset-x-0 top-0 z-50" x-ref="compactHeader">
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

        <div x-show="!scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative -mx-5 sm:-mx-8 md:-mx-10 -mt-6 overflow-hidden rounded-b-[22px] bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 px-5 pb-8 pt-4 text-white">

            <div class="relative flex items-center justify-center">
                <a href="{{ route('member.beranda') }}"
                   onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-white transition active:bg-white/10">
                    <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
                </a>
                <h1 class="text-theme-sm font-bold text-white">Saisho Cake & Bakery</h1>
            </div>

            {{-- Rotating tagline, ticker-style: dark translucent pill, bakery-only
                 copy in English. Rotation at 4.5s per change so it's easy to read. --}}
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

        <div x-show="!scrolled"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="relative z-10 -mt-8">
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

        {{-- Search bar (permanen) + tombol filter kategori (dropdown) --}}
        @if ($kategoris->isNotEmpty() || $daftarBundling->isNotEmpty())
            <div :style="`margin-top: ${chipsOffset}px`"
                 class="relative flex items-center gap-2 pb-4 transition-[margin] duration-200">

                {{-- Search input --}}
                <div class="flex flex-1 items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari menu..."
                           class="w-full border-0 bg-transparent p-0 text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0">
                </div>

                {{-- Tombol filter kategori --}}
                <div class="relative shrink-0" @click.outside="kategoriDropdown = false">
                    <button type="button" @click="kategoriDropdown = ! kategoriDropdown"
                            :class="activeKategori !== 'semua' ? 'bg-brand-800 border-brand-800 text-white' : 'bg-white border-gray-200 text-gray-500'"
                            class="flex h-[46px] w-[46px] items-center justify-center rounded-2xl border shadow-sm transition active:scale-90">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    {{-- Dropdown kategori --}}
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

                        @if ($daftarBundling->isNotEmpty())
                            <button type="button" @click="activeKategori = 'bundling'; kategoriDropdown = false"
                                    :class="activeKategori === 'bundling' ? 'bg-brand-50 text-brand-800' : 'text-gray-600'"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-bold transition hover:bg-gray-50">
                                Bundling
                                <svg x-show="activeKategori === 'bundling'" class="h-3.5 w-3.5 text-brand-800" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        @endif

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

        {{-- HANYA daftar produk yang scroll. Header, store card, kategori, dan search tetap diam. --}}
        <div x-ref="menuScroller"
             @scroll="
                 const isScrolled = $refs.menuScroller.scrollTop > 10;
                 if (scrolled !== isScrolled) {
                     scrolled = isScrolled;
                     updateChipsOffset();
                     setTimeout(() => updateMenuScrollerHeight(), 240);
                 }
             "
             :style="`max-height: ${menuScrollerMaxHeight}px`"
             class="min-h-0 overflow-y-auto overscroll-contain"
             style="scrollbar-width:none; -webkit-overflow-scrolling:touch;">

        {{-- Menu sections, grouped by category --}}
        @if ($daftarBundling->isNotEmpty())
            <div x-show="(activeKategori === 'semua' || activeKategori === 'bundling') && cocokKategori('bundling')" class="mt-6 first:mt-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Bundling</h2>
                    <span class="text-[11px] text-gray-400">{{ $daftarBundling->count() }} item</span>
                </div>
                <div class="mt-1 border-t border-dashed border-gray-100"></div>

                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($daftarBundling as $bundling)
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
                    @endforeach
                </div>
            </div>
        @endif

        @forelse ($kategoris as $kategori)
            <div x-show="(activeKategori === 'semua' || activeKategori === '{{ $kategori->id }}') && cocokKategori('{{ $kategori->id }}')" class="mt-6 first:mt-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">{{ $kategori->nama_kategori }}</h2>
                    <span class="text-[11px] text-gray-400">{{ $kategori->produk->count() }} item</span>
                </div>
                <div class="mt-1 border-t border-dashed border-gray-100"></div>

                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
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
                           class="block">
                            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-50">
                                @if ($produk->foto)
                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                                         class="h-full w-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                                    @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                </div>
                                @if ($adaPromo)
                                    <span class="absolute left-2 top-2 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm">Promo</span>
                                @endif
                                @if ($habis)
                                    <span class="absolute left-2 bottom-2 rounded-full bg-gray-900/80 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-white">Perlu Diproduksi</span>
                                @endif
                            </div>

                            <div class="pt-2">
                                <p class="text-[10px] font-medium text-gray-400">{{ $kategori->nama_kategori }}</p>
                                <p class="mt-0.5 text-xs font-bold leading-snug text-gray-900 line-clamp-2">{{ $produk->nama_produk }}</p>

                                <div class="mt-2 flex items-center justify-between gap-1">
                                    <div class="min-w-0">
                                        @if ($adaPromo)
                                            <p class="truncate text-[10px] text-gray-400 line-through">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</p>
                                        @endif
                                        <p class="truncate text-xs font-extrabold text-gray-900">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</p>
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
                </div>
            </div>
        @empty
            @if ($daftarBundling->isEmpty())
                <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 text-center text-xs font-medium text-gray-400">
                    Belum ada menu tersedia saat ini.
                </div>
            @endif
        @endforelse

        {{-- Hasil pencarian/filter kosong --}}
        @if ($daftarBundling->isNotEmpty() || $kategoris->isNotEmpty())
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

    @include('mobile.components.floating-cart')

@endsection