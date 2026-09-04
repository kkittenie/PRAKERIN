@extends('mobile.layout', ['title' => 'Voucher Detail'])

@php
    $cukupPoin = $member->poin >= $reward->poin_dibutuhkan;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $bisaDitukar = $cukupPoin && ! $habis;
    $kurang = max(0, $reward->poin_dibutuhkan - $member->poin);
    $isVoucher = $reward->jenis !== 'produk';
    $isCroissant = str_contains(strtolower($reward->nama ?? ''), 'croissant');
    $icon = $isVoucher ? 'icon-ticket' : ($isCroissant ? 'icon-cake' : 'icon-cup');

    $syaratKetentuan = collect(preg_split('/\r\n|\r|\n/', trim((string) ($reward->syarat_ketentuan ?? ''))))->filter()->values();
    $caraPakai = collect(preg_split('/\r\n|\r|\n/', trim((string) ($reward->cara_pakai ?? ''))))->filter()->values();
    $menuBerlaku = trim((string) ($reward->menu_berlaku ?? ''));

    $eligibleProduk = $reward->produks->map(fn ($p) => [
        'id' => $p->id,
        'nama' => $p->nama_produk,
        'kategori' => $p->kategori->nama_kategori ?? 'Lainnya',
        'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
    ])->values();
    $eligibleKategori = $eligibleProduk->pluck('kategori')->unique()->values();
    $menuDibatasi = $eligibleProduk->isNotEmpty();
@endphp

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        {{-- App-bar style header: back button on the left, title centered --}}
        <div class="relative -mx-5 sm:mx-0 -mt-6 flex items-center justify-center border-b border-gray-100 px-5 py-4 sm:rounded-t-3xl">
            <a href="{{ route('member.reward.index') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-theme-sm font-bold text-gray-900">Voucher Detail</h1>
        </div>

        {{-- Hero image: uses the reward image uploaded from the admin panel. --}}
        <div class="relative -mx-5 sm:mx-0 h-36 overflow-hidden bg-gradient-to-br {{ $isVoucher ? 'from-brand-100 to-brand-200' : 'from-orange-100 to-orange-200' }} md:h-48 sm:rounded-2xl">
            @if ($reward->gambar)
                <img
                    src="{{ asset('storage/' . $reward->gambar) }}"
                    alt="{{ $reward->nama }}"
                    class="h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/10"></div>
            @else
                <span class="absolute -left-6 -top-6 h-28 w-28 rounded-full bg-white/40"></span>
                <span class="absolute -right-8 top-8 h-20 w-20 rounded-full bg-white/30"></span>
                <span class="absolute bottom-0 right-10 h-16 w-16 rounded-full bg-white/25"></span>

                <span class="relative mx-auto flex h-full w-full items-center justify-center">
                    <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/70 shadow-sm {{ $isVoucher ? 'text-brand-700' : 'text-orange-600' }}">
                        @include('mobile.components.'.$icon, ['class' => 'h-9 w-9'])
                    </span>
                </span>
            @endif
        </div>

        {{-- Title & description --}}
        <div class="pt-5 md:grid md:grid-cols-2 md:gap-8 md:items-start">
            <div>
            <h2 class="text-theme-lg font-bold leading-tight text-gray-900">{{ $reward->nama }}</h2>
            <p class="mt-2 text-theme-sm leading-relaxed text-gray-500">
                {{ $reward->deskripsi ?: ($isVoucher ? 'Voucher diskon spesial dari Saisho.' : 'Tukar poin kamu dengan produk gratis ini.') }}
            </p>

            <div class="mt-4 border-t border-dashed border-gray-200"></div>

            {{-- Valid Until / Min Transaction --}}
            <div class="mt-3 grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100">
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Valid Until</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                        {{ $reward->berlaku_hingga ? \Illuminate\Support\Carbon::parse($reward->berlaku_hingga)->translatedFormat('d M Y') : 'Selama stok ada' }}
                    </p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Min Transaction</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                        {{ $reward->minimal_transaksi ? 'Rp'.number_format($reward->minimal_transaksi, 0, ',', '.') : '–' }}
                    </p>
                </div>
            </div>

            {{-- Poin kamu vs dibutuhkan --}}
            <div class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-4 py-3">
                <div>
                    <p class="text-theme-xs text-gray-400">Dibutuhkan</p>
                    <p class="text-theme-sm font-bold text-gray-800">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin</p>
                </div>
                <div class="text-right">
                    <p class="text-theme-xs text-gray-400">Poin kamu</p>
                    <p class="text-theme-sm font-bold {{ $cukupPoin ? 'text-success-600' : 'text-error-500' }}">{{ number_format($member->poin, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Expandable rows --}}
            <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                <button type="button" data-sheet-target="sheet-menu-berlaku" onclick="openExpandableRow(this, 'sheet-menu-berlaku')"
                        class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Menu yang Berlaku</span>
                    <span class="row-chevron">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
                </button>
                <button type="button" data-sheet-target="sheet-syarat-ketentuan" onclick="openExpandableRow(this, 'sheet-syarat-ketentuan')"
                        class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Syarat & Ketentuan</span>
                    <span class="row-chevron">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
                </button>
                <button type="button" data-sheet-target="sheet-cara-pakai" onclick="openExpandableRow(this, 'sheet-cara-pakai')"
                        class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Cara Pakai</span>
                    <span class="row-chevron">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
                </button>
            </div>
        </div>

        {{-- Redeem button mobile --}}
        <div class="sticky bottom-20 mt-5">
            @if ($habis)
                <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Stok sudah habis</button>
            @elseif (! $cukupPoin)
                <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Kurang {{ number_format($kurang, 0, ',', '.') }} poin lagi</button>
            @else
                <button type="button" onclick="openSheet('konfirmasi-tukar')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-800">
                    Tukar Sekarang
                </button>
            @endif
        </div>
    </div>
    </div>

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - REDESIGNED CARD --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block lg:max-w-4xl lg:mx-auto lg:my-6 lg:bg-white lg:rounded-3xl lg:border lg:border-gray-200/80 lg:shadow-xl lg:overflow-hidden">
        {{-- Desktop Top Back Bar --}}
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 bg-gray-50/50">
            <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-gray-600 hover:text-brand-800 transition">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali ke Katalog Reward
            </a>
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-800">Detail Voucher & Reward</span>
        </div>

        <div class="grid grid-cols-12 gap-8 p-8 items-start">
            {{-- Left Column: Hero Image & Redeem Action Card --}}
            <div class="col-span-5 space-y-6">
                {{-- Hero Banner / Image Card --}}
                <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-br {{ $isVoucher ? 'from-brand-100 to-brand-200' : 'from-orange-100 to-orange-200' }} aspect-[4/3] shadow-sm flex items-center justify-center">
                    @if ($reward->gambar)
                        <img src="{{ asset('storage/' . $reward->gambar) }}" alt="{{ $reward->nama }}" class="h-full w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-black/10"></div>
                    @else
                        <span class="flex h-24 w-24 items-center justify-center rounded-3xl bg-white/80 shadow-md {{ $isVoucher ? 'text-brand-800' : 'text-orange-600' }}">
                            @include('mobile.components.'.$icon, ['class' => 'h-12 w-12'])
                        </span>
                    @endif

                    <span class="absolute top-3 left-3 rounded-full px-3 py-1 text-[11px] font-extrabold tracking-wide text-white shadow-sm {{ $isVoucher ? 'bg-brand-800' : 'bg-orange-500' }}">
                        {{ $isVoucher ? 'Voucher Diskon' : 'Produk Gratis' }}
                    </span>
                </div>

                {{-- Points & Redeem Card --}}
                <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200/60 pb-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Poin Dibutuhkan</p>
                            <p class="text-xl font-black text-brand-800 mt-0.5">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} <span class="text-xs font-bold text-brand-700">Poin</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Saldo Poin Kamu</p>
                            <p class="text-sm font-black {{ $cukupPoin ? 'text-emerald-600' : 'text-red-500' }} mt-0.5">{{ number_format($member->poin, 0, ',', '.') }} Poin</p>
                        </div>
                    </div>

                    {{-- CTA Redeem Button --}}
                    @if ($habis)
                        <button disabled class="w-full rounded-xl bg-gray-200 py-3.5 text-xs font-extrabold uppercase tracking-wider text-gray-500 cursor-not-allowed">Stok Sudah Habis</button>
                    @elseif (! $cukupPoin)
                        <button disabled class="w-full rounded-xl bg-gray-200 py-3.5 text-xs font-extrabold uppercase tracking-wider text-gray-500 cursor-not-allowed">Kurang {{ number_format($kurang, 0, ',', '.') }} Poin Lagi</button>
                    @else
                        <button type="button" onclick="openSheet('konfirmasi-tukar')"
                                class="w-full rounded-xl bg-brand-800 py-3.5 text-xs font-extrabold uppercase tracking-wider text-white shadow-md hover:bg-brand-900 transition active:scale-95 cursor-pointer">
                            Tukar Sekarang
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right Column: Information & Details --}}
            <div class="col-span-7 space-y-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight leading-snug">{{ $reward->nama }}</h2>
                    <p class="mt-2 text-xs leading-relaxed text-gray-600 font-medium">
                        {{ $reward->deskripsi ?: ($isVoucher ? 'Voucher diskon spesial dari Saisho Bakery.' : 'Tukarkan poin member kamu untuk mendapatkan produk pilihan secara gratis.') }}
                    </p>
                </div>

                {{-- Key Stats Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-xs">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Masa Berlaku</p>
                        <p class="mt-1 text-xs font-extrabold text-gray-900">
                            {{ $reward->berlaku_hingga ? \Illuminate\Support\Carbon::parse($reward->berlaku_hingga)->translatedFormat('d M Y') : 'Selama stok tersedia' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-xs">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Minimal Transaksi</p>
                        <p class="mt-1 text-xs font-extrabold text-gray-900">
                            {{ $reward->minimal_transaksi ? 'Rp '.number_format($reward->minimal_transaksi, 0, ',', '.') : 'Tanpa minimal belanja' }}
                        </p>
                    </div>
                </div>

                {{-- Expandable info rows (open bottom-sheet modals) --}}
                <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs mt-2">
                    <button type="button" onclick="openSheet('sheet-menu-berlaku')"
                            class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-gray-50/80 group cursor-pointer">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="text-xs font-extrabold text-gray-900">Menu yang Berlaku</span>
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" onclick="openSheet('sheet-syarat-ketentuan')"
                            class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-gray-50/80 group cursor-pointer">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <span class="text-xs font-extrabold text-gray-900">Syarat & Ketentuan</span>
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" onclick="openSheet('sheet-cara-pakai')"
                            class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-gray-50/80 group cursor-pointer">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-800">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>
                            </span>
                            <span class="text-xs font-extrabold text-gray-900">Cara Pakai</span>
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom sheet: Menu yang Berlaku --}}
    <dialog id="sheet-menu-berlaku" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto {{ $menuDibatasi ? 'h-[85vh]' : 'max-h-[82vh]' }} rounded-t-3xl border-0 p-0">
        @if ($menuDibatasi)
            <div class="flex h-full flex-col"
                 x-data="{
                    produk: @js($eligibleProduk),
                    kategoriList: @js($eligibleKategori),
                    cari: '',
                    kategoriAktif: 'Semua',
                    get hasil() {
                        return this.produk.filter(p => {
                            const cocokKategori = this.kategoriAktif === 'Semua' || p.kategori === this.kategoriAktif;
                            const cocokCari = !this.cari.trim() || p.nama.toLowerCase().includes(this.cari.toLowerCase()) || p.kategori.toLowerCase().includes(this.cari.toLowerCase());
                            return cocokKategori && cocokCari;
                        });
                    },
                    get hasilPerKategori() {
                        const urutan = [];
                        const grup = {};
                        this.hasil.forEach(p => {
                            if (!grup[p.kategori]) {
                                grup[p.kategori] = [];
                                urutan.push(p.kategori);
                            }
                            grup[p.kategori].push(p);
                        });
                        return urutan.map(kat => ({ kategori: kat, items: grup[kat] }));
                    }
                 }">
                <div class="flex shrink-0 justify-center pb-1 pt-3">
                    <span class="h-1 w-10 rounded-full bg-gray-200"></span>
                </div>

                <div class="shrink-0 px-6 pt-3">
                    <h3 class="text-center text-theme-md font-bold text-brand-700">Menu yang Berlaku</h3>

                    {{-- Search --}}
                    <div class="relative mt-4">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="text" x-model="cari" placeholder="Cari kategori atau nama produk"
                               class="w-full rounded-full border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-theme-sm text-gray-700 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-1 focus:ring-brand-300">
                    </div>

                    {{-- Category pills --}}
                    <div class="mt-3 flex gap-2 overflow-x-auto pb-1" style="scrollbar-width:none;">
                        <button type="button" @click="kategoriAktif = 'Semua'"
                                :class="kategoriAktif === 'Semua' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-500'"
                                class="shrink-0 rounded-full border px-4 py-1.5 text-theme-xs font-semibold whitespace-nowrap">
                            Semua
                        </button>
                        <template x-for="kat in kategoriList" :key="kat">
                            <button type="button" @click="kategoriAktif = kat"
                                    :class="kategoriAktif === kat ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-500'"
                                    class="shrink-0 rounded-full border px-4 py-1.5 text-theme-xs font-semibold whitespace-nowrap"
                                    x-text="kat"></button>
                        </template>
                    </div>
                </div>

                {{-- List, grouped by category --}}
                <div class="mt-3 flex-1 overflow-y-auto px-6 pb-2">
                    <template x-if="hasil.length === 0">
                        <p class="py-8 text-center text-theme-sm text-gray-400">Produk tidak ditemukan.</p>
                    </template>

                    <template x-for="grup in hasilPerKategori" :key="grup.kategori">
                        <div class="mb-4">
                            <p class="mb-2 text-theme-sm font-bold text-gray-800" x-text="grup.kategori"></p>
                            <div class="divide-y divide-gray-100 rounded-2xl border border-gray-100">
                                <template x-for="item in grup.items" :key="item.id">
                                    <div class="flex items-center gap-3 px-3 py-2.5">
                                        <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                                            <template x-if="item.foto">
                                                <img :src="item.foto" class="h-full w-full object-cover">
                                            </template>
                                        </div>
                                        <p class="text-theme-sm font-medium text-gray-800" x-text="item.nama"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="shrink-0 px-6 pb-6 pt-4">
                    <button type="button" onclick="closeSheet('sheet-menu-berlaku')"
                            class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                        Mengerti
                    </button>
                </div>
            </div>
        @else
            <div class="flex max-h-[82vh] flex-col">
                <div class="flex shrink-0 justify-center pb-1 pt-3">
                    <span class="h-1 w-10 rounded-full bg-gray-200"></span>
                </div>
                <div class="overflow-y-auto px-6 pb-2 pt-3">
                    <h3 class="text-center text-theme-md font-bold text-brand-700">Menu yang Berlaku</h3>
                    <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                        @if ($menuBerlaku)
                            <p>{{ $menuBerlaku }}</p>
                        @else
                            <p>Berlaku untuk semua menu di outlet Saisho, selama stok produk masih tersedia.</p>
                        @endif
                    </div>
                </div>
                <div class="shrink-0 px-6 pb-6 pt-4">
                    <button type="button" onclick="closeSheet('sheet-menu-berlaku')"
                            class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                        Mengerti
                    </button>
                </div>
            </div>
        @endif
    </dialog>

    {{-- Bottom sheet: Syarat & Ketentuan (matches the Terms & Conditions example) --}}
    <dialog id="sheet-syarat-ketentuan" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Syarat & Ketentuan</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    @if ($syaratKetentuan->isNotEmpty())
                        <ol class="list-decimal space-y-2 pl-4">
                            @foreach ($syaratKetentuan as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ol class="list-decimal space-y-2 pl-4">
                            <li>Reward ini hanya berlaku untuk penukaran melalui aplikasi Saisho.</li>
                            <li>Reward ini hanya dapat digunakan di outlet Saisho yang berlaku.</li>
                            <li>Reward ini berlaku untuk jangka waktu yang ditentukan, selama poin dan stok masih tersedia.</li>
                            <li>Reward ini tidak dapat digabung dengan promo atau reward lain.</li>
                            <li>Poin yang sudah dipotong untuk penukaran tidak dapat dikembalikan atau dibatalkan.</li>
                            <li>Kami dapat mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan terlebih dahulu.</li>
                        </ol>
                    @endif
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-syarat-ketentuan')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom sheet: Cara Pakai --}}
    <dialog id="sheet-cara-pakai" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Cara Pakai</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    @if ($caraPakai->isNotEmpty())
                        <ol class="list-decimal space-y-2 pl-4">
                            @foreach ($caraPakai as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ol class="list-decimal space-y-2 pl-4">
                            <li>Tekan tombol "Tukar Sekarang" lalu konfirmasi penukaran reward.</li>
                            <li>Kode redeem akan muncul di halaman Riwayat setelah penukaran berhasil.</li>
                            <li>Tunjukkan kode redeem tersebut ke kasir saat kamu checkout di outlet.</li>
                            <li>Kasir akan memverifikasi dan memproses reward kamu langsung di kasir.</li>
                        </ol>
                    @endif
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-cara-pakai')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

    {{-- Confirmation dialog before spending poin — bottom sheet, same as the
         expandable rows above (Menu yang Berlaku, Syarat & Ketentuan, Cara Pakai) --}}
    <dialog id="konfirmasi-tukar" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
        <div class="flex flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="px-6 pb-2 pt-3 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                    @include('mobile.components.icon-gift', ['class' => 'h-6 w-6'])
                </span>
                <p class="mt-3 text-theme-md font-semibold text-gray-800">Tukar {{ $reward->nama }}?</p>
                <p class="mt-1 text-theme-sm text-gray-500">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin akan dipotong dari saldo kamu dan tidak bisa dibatalkan.</p>
            </div>

            <form method="POST" action="{{ route('member.reward.tukar', $reward) }}" class="shrink-0 space-y-2 px-6 pb-6 pt-4">
                @csrf
                <button type="submit" class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">Ya, Tukar Sekarang</button>
                <button type="button" onclick="closeSheet('konfirmasi-tukar')" class="w-full rounded-full py-3 text-theme-sm font-medium text-gray-500">Batal</button>
            </form>
        </div>
    </dialog>

@endsection