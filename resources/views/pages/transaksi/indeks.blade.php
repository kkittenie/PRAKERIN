@extends('layouts.app')

@section('content')
    <div x-data="aplikasiKasir()" x-init="inisialisasi()"
        class="bg-gray-50 dark:bg-gray-950 min-h-screen lg:min-h-0 lg:h-[calc(100vh-64px)] text-gray-800 dark:text-gray-200 -m-4 md:-m-6 p-4 md:p-6 lg:overflow-hidden flex flex-col"
        x-cloak>

        <!-- Toast Notifikasi -->
        <div class="fixed top-5 right-5 z-99999 flex flex-col gap-2">
            <template x-for="toast in toasts" :key="toast.id">
                <div :class="{
                                                                                                                                'bg-green-600': toast.tipe === 'sukses',
                                                                                                                                'bg-red-600': toast.tipe === 'error',
                                                                                                                                'bg-amber-700': toast.tipe === 'info'
                                                                                                                             }"
                    class="text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3 min-w-[280px] transform transition-all duration-300 animate-slide-in">
                    <span class="font-semibold text-lg" x-text="toast.tipe === 'sukses' ? '✓' : '⚠'"></span>
                    <span class="text-sm" x-text="toast.pesan"></span>
                </div>
            </template>
        </div>

        <!-- ========== HALAMAN 1: BERANDA KASIR ========== -->
        <div x-show="halamanAktif === 'beranda'" class="flex-1 flex flex-col gap-4 lg:overflow-hidden min-h-0">

            <!-- Header Bar -->
            <div
                class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-xs border border-gray-100 dark:border-gray-800 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-600 text-white p-2.5 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Kasir POS - Transaksi
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kelola Penjualan Mudah</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" x-model="kataKunciPencarian" @input="halamanProduk = 1"
                            placeholder="Cari nama atau kode produk..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm bg-gray-50 dark:bg-gray-800 focus:bg-white dark:focus:bg-gray-900 dark:text-white transition-all">
                    </div>
                    <button @click="bukaModalDaftarDitahan()"
                        class="relative flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold rounded-lg border border-amber-100 dark:border-amber-500/25 transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Daftar Belum Bayar</span>
                        <span
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xxs font-bold px-2 py-0.5 rounded-full"
                            x-show="totalDitahan > 0" x-text="totalDitahan"></span>
                    </button>
                </div>
            </div>

            <!-- Layout Utama -->
            <div class="grid grid-cols-12 gap-4 items-stretch flex-1 min-h-0 lg:overflow-hidden">

                <!-- Kiri: Filter + Produk -->
                <div class="col-span-12 lg:col-span-8 flex flex-col gap-4 lg:h-full lg:overflow-hidden min-h-0">

                    <!-- Bar Kategori & Filter -->
                    <div class="flex flex-wrap items-center justify-between gap-4 shrink-0">
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                            <button @click="modeTampilan = 'produk'; kategoriTerpilih = 'semua'; halamanProduk = 1"
                                :class="modeTampilan === 'produk' && kategoriTerpilih === 'semua' ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap">Semua
                                Produk</button>
                            <template x-for="kategori in daftarKategori" :key="kategori.id">
                                <button @click="modeTampilan = 'produk'; kategoriTerpilih = kategori.id; halamanProduk = 1"
                                    :class="modeTampilan === 'produk' && kategoriTerpilih === kategori.id ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap"
                                    x-text="kategori.nama_kategori"></button>
                            </template>
                            <button @click="modeTampilan = 'bundling'; halamanProduk = 1"
                                :class="modeTampilan === 'bundling' ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap flex items-center gap-1.5">
                                <span>Bundling</span>
                            </button>
                            <button @click="modeTampilan = 'promo_event'; halamanProduk = 1"
                                :class="modeTampilan === 'promo_event' ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap flex items-center gap-1.5">
                                <span>Promo Event</span>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Items per page selector -->
                            <div
                                class="flex items-center gap-1.5 bg-white dark:bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-800 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0">
                                <span>Tampilkan:</span>
                                <select x-model="itemsPerPage" @change="halamanProduk = 1"
                                    class="bg-transparent font-bold text-amber-600 dark:text-amber-400 focus:outline-none cursor-pointer">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="semua">Semua</option>
                                </select>
                            </div>

                            <!-- Filter Status Ketersediaan (mode produk) -->
                            <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-lg border border-gray-100 dark:border-gray-800 shrink-0"
                                x-show="modeTampilan === 'produk'">
                                <button @click="filterKetersediaan = 'semua'; halamanProduk = 1"
                                    :class="filterKetersediaan === 'semua' ? 'bg-slate-900 dark:bg-gray-800 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Semua</button>
                                <button @click="filterKetersediaan = 'stok_ada'; halamanProduk = 1"
                                    :class="filterKetersediaan === 'stok_ada' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Stok Tersedia</button>
                                <button @click="filterKetersediaan = 'perlu_diproduksi'; halamanProduk = 1"
                                    :class="filterKetersediaan === 'perlu_diproduksi' ? 'bg-amber-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Perlu
                                    Diproduksi</button>
                                <button @click="filterKetersediaan = 'promo'; halamanProduk = 1"
                                    :class="filterKetersediaan === 'promo' ? 'bg-rose-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Promo</button>
                            </div>

                            <!-- Filter Status Ketersediaan (mode bundling) -->
                            <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-lg border border-gray-100 dark:border-gray-800 shrink-0"
                                x-show="modeTampilan === 'bundling'">
                                <button @click="filterBundling = 'semua'; halamanProduk = 1"
                                    :class="filterBundling === 'semua' ? 'bg-slate-900 dark:bg-gray-800 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Semua</button>
                                <button @click="filterBundling = 'tersedia'; halamanProduk = 1"
                                    :class="filterBundling === 'tersedia' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Stok Tersedia</button>
                                <button @click="filterBundling = 'habis'; halamanProduk = 1"
                                    :class="filterBundling === 'habis' ? 'bg-amber-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Perlu
                                    Diproduksi</button>
                            </div>

                            <!-- Filter Status Ketersediaan (mode promo_event) -->
                            <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-lg border border-gray-100 dark:border-gray-800 shrink-0"
                                x-show="modeTampilan === 'promo_event'">
                                <button @click="filterPromoEvent = 'semua'; halamanProduk = 1"
                                    :class="filterPromoEvent === 'semua' ? 'bg-slate-900 dark:bg-gray-800 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Semua</button>
                                <button @click="filterPromoEvent = 'tersedia'; halamanProduk = 1"
                                    :class="filterPromoEvent === 'tersedia' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Stok Tersedia</button>
                                <button @click="filterPromoEvent = 'habis'; halamanProduk = 1"
                                    :class="filterPromoEvent === 'habis' ? 'bg-amber-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                    class="px-3 py-1.5 rounded-md text-xs font-bold transition">Perlu
                                    Diproduksi</button>
                            </div>
                        </div>
                    </div>

                    <!-- Scrollable grid wrapper -->
                    <div class="flex-1 lg:overflow-y-auto pr-1 min-h-0 custom-scrollbar">

                        <!-- ===== MODE PRODUK ===== -->
                        <div x-show="modeTampilan === 'produk' && produkTersaring.length === 0"
                            class="h-full flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-14 h-14 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Produk tidak ditemukan</p>
                        </div>

                        <div x-show="modeTampilan === 'produk' && produkTersaring.length > 0"
                            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                            <template x-for="produk in produkTersaring"
                                :key="(produk.is_bundling ? 'bd_' : (produk.is_promo_event ? 'pe_' : 'pr_')) + produk.id">
                                <div class="min-w-0">
                                    <!-- Kartu Bundling (kalau item ini adalah bundling) -->
                                    <template x-if="produk.is_bundling">
                                        <div
                                            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                            <div @click="tambahBundlingKeKeranjang(produk)"
                                                title="Klik untuk tambah ke keranjang"
                                                class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden cursor-pointer">
                                                <img :src="getFotoUrl(produk.gambar)" x-on:error="handleFotoError($event)"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    alt="Bundling">
                                                <div class="absolute top-2 left-2 z-5">
                                                    <template x-if="!bundlingPerluDiproduksi(produk)">
                                                        <span
                                                            class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                                    </template>
                                                    <template x-if="bundlingPerluDiproduksi(produk)">
                                                        <span
                                                            class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Perlu
                                                            Diproduksi</span>
                                                    </template>
                                                </div>
                                                <div class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                                    :class="bundlingPerluDiproduksi(produk) ? 'bg-amber-600/90 text-white font-bold' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'"
                                                    x-text="bundlingPerluDiproduksi(produk) ? 'Perlu Diproduksi' : ('Stok: ' + stokBundlingTersedia(produk))">
                                                </div>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                                <div>
                                                    <p
                                                        class="text-xxs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                                                        Bundling</p>
                                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2 min-h-[2.5rem]"
                                                        x-text="produk.nama_bundling"></h3>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500"
                                                        x-text="produk.kode_bundling">
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                                    <div class="h-5"></div>
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                            x-text="formatRupiah(produk.harga)"></span>
                                                        <button @click="tambahBundlingKeKeranjang(produk)"
                                                            class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Kartu Promo Event (kalau item ini adalah promo event) -->
                                    <template x-if="produk.is_promo_event">
                                        <div
                                            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                            <div @click="tambahPromoEventKeKeranjang(produk)"
                                                title="Klik untuk tambah ke keranjang"
                                                class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden cursor-pointer">
                                                <img :src="getFotoUrl(produk.produk ? produk.produk.foto : null)" x-on:error="handleFotoError($event)"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    alt="Promo Event">
                                                <div class="absolute top-2 left-2 z-5">
                                                    <template x-if="!promoEventPerluDiproduksi(produk)">
                                                        <span
                                                            class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                                    </template>
                                                    <template x-if="promoEventPerluDiproduksi(produk)">
                                                        <span
                                                            class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Perlu
                                                            Diproduksi</span>
                                                    </template>
                                                </div>
                                                <div class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                                    :class="promoEventPerluDiproduksi(produk) ? 'bg-amber-600/90 text-white font-bold' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'"
                                                    x-text="promoEventPerluDiproduksi(produk) ? 'Perlu Diproduksi' : ('Stok: ' + stokPromoEventTersedia(produk))">
                                                </div>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                                <div>
                                                    <p
                                                        class="text-xxs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                                                        Promo Event</p>
                                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2 min-h-[2.5rem]"
                                                        x-text="produk.produk ? produk.produk.nama_produk : produk.nama_event"></h3>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate"
                                                        x-text="produk.nama_event"></p>
                                                </div>
                                                <div
                                                    class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                                    <div class="h-5"></div>
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                            x-text="formatRupiah(produk.harga_event)"></span>
                                                        <button @click="tambahPromoEventKeKeranjang(produk)"
                                                            class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Kartu Produk biasa (kalau bukan bundling / promo event) -->
                                    <template x-if="!produk.is_bundling && !produk.is_promo_event">
                                        <div
                                            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                            <div @click="tambahKeKeranjang(produk)" title="Klik untuk tambah ke keranjang"
                                                class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden cursor-pointer">
                                                <img :src="getFotoUrl(produk.foto)" x-on:error="handleFotoError($event)"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    alt="Produk">
                                                <div class="absolute top-2 left-2 z-5">
                                                    <template x-if="!butuhProduksi(produk)">
                                                        <span
                                                            class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                                    </template>
                                                    <template x-if="butuhProduksi(produk)">
                                                        <span
                                                            class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Perlu
                                                            Diproduksi</span>
                                                    </template>
                                                </div>
                                                <div class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                                    :class="!butuhProduksi(produk) && produk.stok > 0 && produk.stok <= produk.stok_minimum ? 'bg-red-600/90 text-white font-bold animate-pulse' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'">
                                                    <span
                                                        x-text="butuhProduksi(produk) ? 'Perlu Diproduksi' : (produk.stok <= produk.stok_minimum && produk.stok > 0 ? '⚠️ Sisa ' + produk.stok : 'Stok: ' + produk.stok)"></span>
                                                </div>
                                            </div>
                                            <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                                <div>
                                                    <p class="text-xxs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider"
                                                        x-text="produk.kategori?.nama_kategori || 'Pastry'"></p>
                                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2 min-h-[2.5rem]"
                                                        x-text="produk.nama_produk"></h3>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500"
                                                        x-text="produk.kode_produk">
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                                    <div class="h-5 flex flex-wrap items-center gap-1">
                                                        <template
                                                            x-if="produk.promos_aktif_semua && produk.promos_aktif_semua.length > 0">
                                                            <div class="flex flex-wrap items-center gap-1">
                                                                <template x-for="p in produk.promos_aktif_semua"
                                                                    :key="p.id">
                                                                    <span
                                                                        class="text-xxs font-extrabold px-1.5 py-0.5 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-450 rounded animate-pulse"
                                                                        x-text="p.jenis_potongan === 'nominal' ? ('-Rp ' + Math.round(p.nominal_potongan/1000) + 'rb') : ('-' + formatPersen(p.persentase) + '%')"></span>
                                                                </template>
                                                                <span
                                                                    class="text-xs text-gray-450 dark:text-gray-500 line-through"
                                                                    x-text="formatRupiah(produk.harga_jual)"></span>
                                                            </div>
                                                        </template>
                                                        <template
                                                            x-if="(!produk.promos_aktif_semua || produk.promos_aktif_semua.length === 0) && produk.promo_aktif">
                                                            <div class="flex flex-wrap items-center gap-1.5">
                                                                <span
                                                                    class="text-xxs font-extrabold px-1.5 py-0.5 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-450 rounded animate-pulse"
                                                                    x-text="produk.promo_aktif.jenis_potongan === 'nominal' ? ('-Rp ' + Math.round(produk.promo_aktif.nominal_potongan/1000) + 'rb') : ('-' + formatPersen(produk.promo_aktif.persentase) + '%')"></span>
                                                                <span
                                                                    class="text-xs text-gray-450 dark:text-gray-500 line-through"
                                                                    x-text="formatRupiah(produk.harga_jual)"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <template x-if="produk.punya_ukuran == 1 || produk.punya_ukuran === true">
                                                        <div class="grid grid-cols-2 gap-1.5 items-center">
                                                            <!-- Kecil -->
                                                            <button type="button" @click.stop="tambahKeKeranjang(produk, 'Kecil')"
                                                                title="Tambah Ukuran Kecil"
                                                                class="flex flex-col items-center justify-center py-1.5 px-1 bg-amber-50/80 hover:bg-amber-600 dark:bg-amber-500/10 dark:hover:bg-amber-600 rounded-lg transition border border-amber-200/60 dark:border-amber-500/20 group/sz text-center">
                                                                <div class="flex items-center justify-center gap-1 w-full leading-none">
                                                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-amber-800/90 dark:text-amber-300 group-hover/sz:text-white/90">Kecil</span>
                                                                    <span class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400 group-hover/sz:text-white">+</span>
                                                                </div>
                                                                <span class="font-extrabold text-[11px] text-amber-900 dark:text-amber-200 group-hover/sz:text-white leading-tight mt-0.5" x-text="formatRupiah(produk.harga_kecil)"></span>
                                                            </button>
                                                            <!-- Besar -->
                                                            <button type="button" @click.stop="tambahKeKeranjang(produk, 'Besar')"
                                                                title="Tambah Ukuran Besar"
                                                                class="flex flex-col items-center justify-center py-1.5 px-1 bg-amber-50/80 hover:bg-amber-600 dark:bg-amber-500/10 dark:hover:bg-amber-600 rounded-lg transition border border-amber-200/60 dark:border-amber-500/20 group/sz text-center">
                                                                <div class="flex items-center justify-center gap-1 w-full leading-none">
                                                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-amber-800/90 dark:text-amber-300 group-hover/sz:text-white/90">Besar</span>
                                                                    <span class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400 group-hover/sz:text-white">+</span>
                                                                </div>
                                                                <span class="font-extrabold text-[11px] text-amber-900 dark:text-amber-200 group-hover/sz:text-white leading-tight mt-0.5" x-text="formatRupiah(produk.harga_besar)"></span>
                                                            </button>
                                                        </div>
                                                    </template>

                                                    <template x-if="!(produk.punya_ukuran == 1 || produk.punya_ukuran === true)">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                                x-text="formatRupiah(produk.promo_aktif ? produk.harga_diskon : produk.harga_jual)"></span>
                                                            <button @click.stop="tambahKeKeranjang(produk)"
                                                                title="Tambah ke keranjang"
                                                                class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- ===== MODE BUNDLING ===== -->
                        <div x-show="modeTampilan === 'bundling' && produkTersaring.length === 0"
                            class="h-full flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-14 h-14 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Belum ada paket bundling</p>
                        </div>
                        <div x-show="modeTampilan === 'bundling' && produkTersaring.length > 0"
                            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                            <template x-for="bundling in produkTersaring" :key="bundling.id">
                                <div
                                    class="min-w-0 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                    <div @click="tambahBundlingKeKeranjang(bundling)" title="Klik untuk tambah ke keranjang"
                                        class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden cursor-pointer">
                                        <img :src="getFotoUrl(bundling.gambar)" x-on:error="handleFotoError($event)"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            alt="Bundling">
                                        <div class="absolute top-2 left-2 z-5">
                                            <template x-if="!bundlingPerluDiproduksi(bundling)">
                                                <span
                                                    class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                            </template>
                                            <template x-if="bundlingPerluDiproduksi(bundling)">
                                                <span
                                                    class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Perlu
                                                    Diproduksi</span>
                                            </template>
                                        </div>
                                        <div class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                            :class="bundlingPerluDiproduksi(bundling) ? 'bg-amber-600/90 text-white font-bold' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'"
                                            x-text="bundlingPerluDiproduksi(bundling) ? 'Perlu Diproduksi' : ('Stok: ' + stokBundlingTersedia(bundling))">
                                        </div>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                        <div>
                                            <p
                                                class="text-xxs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                                                Bundling</p>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2 min-h-[2.5rem]"
                                                x-text="bundling.nama_bundling"></h3>
                                            <p class="text-xs text-gray-400 dark:text-gray-500"
                                                x-text="bundling.kode_bundling"></p>
                                        </div>
                                        <div
                                            class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                            <div class="h-5"></div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                    x-text="formatRupiah(bundling.harga)"></span>
                                                <button @click="tambahBundlingKeKeranjang(bundling)"
                                                    class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>


                                                <!-- ===== MODE PROMO EVENT ===== -->
                        <div x-show="modeTampilan === 'promo_event' && produkTersaring.length === 0"
                            class="h-full flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-14 h-14 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Belum ada promo event yang berlaku</p>
                        </div>
                        <div x-show="modeTampilan === 'promo_event' && produkTersaring.length > 0"
                            class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                            <template x-for="promoEvent in produkTersaring" :key="promoEvent.id">
                                <div
                                    class="min-w-0 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                    <div @click="tambahPromoEventKeKeranjang(promoEvent)" title="Klik untuk tambah ke keranjang"
                                        class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden cursor-pointer">
                                        <img :src="getFotoUrl(promoEvent.produk ? promoEvent.produk.foto : null)" x-on:error="handleFotoError($event)"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            alt="Promo Event">
                                        <div class="absolute top-2 left-2 z-5">
                                            <template x-if="!promoEventPerluDiproduksi(promoEvent)">
                                                <span
                                                    class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                            </template>
                                            <template x-if="promoEventPerluDiproduksi(promoEvent)">
                                                <span
                                                    class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Perlu
                                                    Diproduksi</span>
                                            </template>
                                        </div>
                                        <div class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                            :class="promoEventPerluDiproduksi(promoEvent) ? 'bg-amber-600/90 text-white font-bold' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'"
                                            x-text="promoEventPerluDiproduksi(promoEvent) ? 'Perlu Diproduksi' : ('Stok: ' + stokPromoEventTersedia(promoEvent))">
                                        </div>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                        <div>
                                            <p
                                                class="text-xxs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                                                Promo Event</p>
                                            <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2 min-h-[2.5rem]"
                                                x-text="promoEvent.produk ? promoEvent.produk.nama_produk : promoEvent.nama_event"></h3>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate"
                                                x-text="promoEvent.nama_event"></p>
                                        </div>
                                        <div
                                            class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                            <div class="h-5"></div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                    x-text="formatRupiah(promoEvent.harga_event)"></span>
                                                <button @click="tambahPromoEventKeKeranjang(promoEvent)"
                                                    class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>

                    <!-- Product Pagination Bar (dipakai bareng untuk produk & bundling) -->
                    <div x-show="itemsPerPage !== 'semua' && totalHalamanProduk > 1"
                        class="flex flex-wrap items-center justify-between bg-white dark:bg-gray-900 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 shrink-0 text-xs gap-2">
                        <span class="text-gray-500 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-800 dark:text-white"
                                x-text="produkDitampilkan.length"></span> dari
                            <span class="font-bold text-gray-800 dark:text-white" x-text="semuaItemModeAktif.length"></span>
                                <span x-text="modeTampilan === 'bundling' ? 'bundling' : (modeTampilan === 'promo_event' ? 'promo event' : 'produk')"></span>
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button @click="if(halamanProduk > 1) halamanProduk--" :disabled="halamanProduk <= 1"
                                class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                                &laquo; Prev
                            </button>
                            <span class="px-2 font-bold text-amber-600 dark:text-amber-400"
                                x-text="halamanProduk + ' / ' + totalHalamanProduk"></span>
                            <button @click="if(halamanProduk < totalHalamanProduk) halamanProduk++"
                                :disabled="halamanProduk >= totalHalamanProduk"
                                class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                                Next &raquo;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Keranjang -->
                <div
                    class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs flex flex-col lg:h-full lg:overflow-hidden min-h-0">
                    <!-- Info Pembeli -->
                    <div
                        class="p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 rounded-t-xl shrink-0">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider">Nama
                                Pembeli</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <template x-if="namaPembeli.trim() !== ''">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="w-10 h-10 bg-amber-100 text-amber-700 font-bold flex items-center justify-center rounded-full shrink-0"
                                        x-text="namaPembeli.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()">
                                    </div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate flex-1"
                                        x-text="namaPembeli">
                                    </h4>
                                    <button @click="bukaModalPembeli()"
                                        class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition shrink-0">Ubah</button>
                                </div>
                            </template>
                            <template x-if="namaPembeli.trim() === ''">
                                <button @click="bukaModalPembeli()"
                                    class="w-full flex items-center gap-3 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/25 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Klik untuk isi nama
                                        pembeli</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Pilih Member -->
                    <div class="p-3 border-b border-gray-100 dark:border-gray-800 shrink-0" x-data="{
                                                                                                                        cariMember: '',
                                                                                                                        dropdownTerbuka: false,
                                                                                                                        get memberTersaring() {
                                                                                                                        if (!this.cariMember.trim()) return $data.daftarMember;
                                                                                                                        const q = this.cariMember.toLowerCase();
                                                                                                                        return $data.daftarMember.filter(m =>
                                                                                                                        m.nama.toLowerCase().includes(q) || m.no_hp.includes(q)
                                                                                                                        );
                                                                                                                        },
                                                                                                                        pilihMemberItem(member) {
                                if ($data.voucherTerpilih && $data.memberIdTerpilih !== member.id) {
                                    $data.voucherTerpilih = null;
                                }
                                if ($data.memberIdTerpilih !== member.id) {
                                    $data.keranjang = $data.keranjang.filter(i => !i.is_reward_stamp);
                                }
                                $data.memberIdTerpilih = member.id;
                                $data.memberTerpilih = member;
                                $data.namaPembeli = member.nama;
                                this.cariMember = '';
                                this.dropdownTerbuka = false;
                                },
                                                                                                                        hapusMember() {
                                                                                                                        $data.memberIdTerpilih = '';
                                                                                                                        $data.memberTerpilih = null;
                                                                                                                        $data.voucherTerpilih = null;
                                                                                                                        $data.keranjang = $data.keranjang.filter(i => !i.is_reward_stamp);
                                                                                                                        this.cariMember = '';
                                                                                                                        },
                                                                                                                        getLevelColor(level) {
                                                                                                                        if (level === 'Gold') return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400';
                                                                                                                        if (level === 'Silver') return 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300';
                                                                                                                        return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
                                                                                                                        }
                                                                                                                        }"
                        @click.outside="dropdownTerbuka = false">
                        <label
                            class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-2">Member
                            </label>

                        <!-- Jika member sudah terpilih -->
                        <template x-if="$data.memberTerpilih">
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/25 rounded-lg">
                                <div class="w-7 h-7 bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center rounded-full shrink-0"
                                    x-text="$data.memberTerpilih.nama.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate"
                                        x-text="$data.memberTerpilih.nama"></p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400"
                                        x-text="$data.memberTerpilih.no_hp + ' · ' + $data.memberTerpilih.poin + ' poin'">
                                    </p>
                                </div>
                                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold shrink-0"
                                    :class="getLevelColor($data.memberTerpilih.level)"
                                    x-text="$data.memberTerpilih.level"></span>
                                <button @click="hapusMember()"
                                    class="p-1 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-md transition shrink-0"
                                    title="Hapus member">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Input pencarian jika belum pilih member -->
                        <template x-if="!$data.memberTerpilih">
                            <div class="relative">
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 dark:text-gray-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" x-model="cariMember" @focus="dropdownTerbuka = true"
                                        @input="dropdownTerbuka = true" placeholder="Cari nama atau no. HP member..."
                                        class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500 transition placeholder:text-gray-400 dark:placeholder:text-gray-600">
                                </div>

                                <!-- Dropdown hasil pencarian -->
                                <div x-show="dropdownTerbuka" x-transition.opacity.duration.150ms
                                    class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto custom-scrollbar">

                                    <!-- Tanpa Member -->
                                    <button @click="hapusMember(); dropdownTerbuka = false"
                                        class="w-full px-3 py-2 text-left text-xs text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                        <span>Tanpa Member (Pelanggan Umum)</span>
                                    </button>

                                    <div class="border-t border-gray-100 dark:border-gray-800"></div>

                                    <!-- Hasil pencarian member -->
                                    <template x-for="member in memberTersaring" :key="member.id">
                                        <button @click="pilihMemberItem(member)"
                                            class="w-full px-3 py-2 text-left hover:bg-amber-50 dark:hover:bg-amber-500/10 transition flex items-center gap-2.5">
                                            <div class="w-7 h-7 bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] font-bold flex items-center justify-center rounded-full shrink-0"
                                                x-text="member.nama.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-800 dark:text-white truncate"
                                                    x-text="member.nama"></p>
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500"
                                                    x-text="member.no_hp + ' · ' + member.poin + ' poin'"></p>
                                            </div>
                                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold shrink-0"
                                                :class="getLevelColor(member.level)" x-text="member.level"></span>
                                        </button>
                                    </template>

                                    <!-- Tidak ditemukan -->
                                    <template x-if="memberTersaring.length === 0 && cariMember.trim() !== ''">
                                        <div class="px-3 py-4 text-center">
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Tidak ada member ditemukan
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Voucher Member: hanya tampil kalau member sudah dipilih -->
                    <div class="p-3 border-b border-gray-100 dark:border-gray-800 shrink-0" x-show="memberIdTerpilih"
                        x-cloak>
                        <label
                            class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-2">Voucher</label>

                        <!-- Voucher belum dipilih -->
                        <template x-if="!voucherTerpilih">
                            <button type="button" @click="bukaModalVoucher()"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg border text-xs font-bold transition bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/25 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Gunakan Voucher</span>
                            </button>
                        </template>

                        <!-- Voucher sudah dipilih -->
                        <template x-if="voucherTerpilih">
                            <div class="flex flex-col gap-1">
                                <div
                                    class="flex items-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/25 rounded-lg">
                                    <div
                                        class="w-7 h-7 bg-rose-600 text-white flex items-center justify-center rounded-full shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate"
                                            x-text="voucherTerpilih.nama"></p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            <span x-text="voucherTerpilih.kode_redeem"></span> ·
                                            <span
                                                x-text="voucherDiskon > 0 ? ('-' + formatRupiah(voucherDiskon)) : 'Belum aktif'"></span>
                                        </p>
                                    </div>
                                    <button @click="hapusVoucherTerpilih()"
                                        class="p-1 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-md transition shrink-0"
                                        title="Hapus voucher">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <template
                                    x-if="voucherTerpilih && voucherSubtotalBerlaku <= 0">
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400">Produk di keranjang
                                        belum ada yang berhak dapat voucher ini.</p>
                                </template>
                                <template
                                    x-if="voucherTerpilih && voucherSubtotalBerlaku > 0 && subtotalGross < (parseFloat(voucherTerpilih.min_pembelian) || 0)">
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400">Minimal belanja <span
                                            x-text="formatRupiah(voucherTerpilih.min_pembelian)"></span> supaya
                                        voucher ini aktif.</p>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Reward Stamp Member: hanya tampil kalau member sudah dipilih -->
                    <div class="p-3 border-b border-gray-100 dark:border-gray-800 shrink-0" x-show="memberIdTerpilih"
                        x-cloak>
                        <label
                            class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-2">Reward
                            Stamp</label>

                        <!-- Reward stamp belum dipilih -->
                        <template x-if="!rewardStampDiKeranjang">
                            <button type="button" @click="bukaModalRewardStamp()"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg border text-xs font-bold transition bg-purple-50 dark:bg-purple-500/10 border-purple-200 dark:border-purple-500/25 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                <span>Gunakan Reward Stamp</span>
                            </button>
                        </template>

                        <!-- Reward stamp sudah dipilih -->
                        <template x-if="rewardStampDiKeranjang">
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/25 rounded-lg">
                                <div
                                    class="w-7 h-7 bg-purple-600 text-white flex items-center justify-center rounded-full shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate"
                                        x-text="rewardStampDiKeranjang.nama_produk"></p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Gratis · Reward Stamp</p>
                                </div>
                                <button @click="hapusRewardStampTerpilih()"
                                    class="p-1 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-md transition shrink-0"
                                    title="Hapus reward stamp">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Modal Pilih Reward Stamp -->
                    <div x-show="modalRewardStampTerbuka"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4"
                        x-transition x-cloak>
                        <div @click.away="tutupModalRewardStamp()"
                            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800 flex flex-col max-h-[90vh]">
                            <div
                                class="bg-purple-700 dark:bg-purple-900 text-white p-4 flex items-center justify-between shrink-0">
                                <div>
                                    <h3 class="font-extrabold text-sm">Gunakan Reward Stamp</h3>
                                    <p class="text-white/80 text-xs mt-0.5"
                                        x-text="memberTerpilih ? memberTerpilih.nama : ''"></p>
                                </div>
                                <button @click="tutupModalRewardStamp()"
                                    class="text-white hover:text-gray-200 font-bold text-lg leading-none">&times;</button>
                            </div>

                            <div class="p-5 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
                                <!-- Input kode manual -->
                                <div>
                                    <label
                                        class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-1.5">Masukkan
                                        Kode Reward Stamp</label>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="kodeRewardStampManual"
                                            @input="kodeRewardStampManual = kodeRewardStampManual.toUpperCase()"
                                            @keydown.enter="validasiKodeRewardStampManual()"
                                            placeholder="Contoh: STP-1234"
                                            class="flex-1 px-3 py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 uppercase">
                                        <button type="button" @click="validasiKodeRewardStampManual()"
                                            :disabled="rewardStampKodeLoading || !kodeRewardStampManual.trim()"
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-lg transition shrink-0">
                                            <span x-show="!rewardStampKodeLoading">Pakai</span>
                                            <span x-show="rewardStampKodeLoading">...</span>
                                        </button>
                                    </div>
                                    <template x-if="rewardStampModalError">
                                        <p class="text-[10px] text-red-500 mt-1.5" x-text="rewardStampModalError"></p>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                    <span
                                        class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">atau
                                        pilih</span>
                                    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                </div>

                                <!-- Daftar reward stamp yang sudah diklaim member (menunggu diambil) -->
                                <div class="flex flex-col gap-2">
                                    <template x-if="rewardStampDaftarLoading">
                                        <p class="text-xs text-center text-gray-400 py-4">Memuat reward stamp...</p>
                                    </template>

                                    <template x-if="!rewardStampDaftarLoading && daftarRewardStampAktif.length === 0">
                                        <p class="text-xs text-center text-gray-400 py-4">Member ini belum punya
                                            reward stamp yang menunggu diambil. Reward baru muncul di sini setelah
                                            member menukar stamp lewat portal member.</p>
                                    </template>

                                    <template x-for="redemption in daftarRewardStampAktif" :key="redemption.id">
                                        <button type="button" @click="pilihRewardStampItem(redemption)"
                                            class="w-full text-left flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 hover:bg-purple-50/50 dark:hover:bg-purple-950/20 cursor-pointer transition">
                                            <div
                                                class="w-8 h-8 bg-purple-600 text-white flex items-center justify-center rounded-full shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span
                                                        class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                                        x-text="redemption.nama_produk"></span>
                                                    <span
                                                        class="text-xs font-extrabold text-purple-600 dark:text-purple-400 shrink-0">Gratis</span>
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                                    <span x-text="'Jumlah: ' + redemption.jumlah"></span>
                                                    <span x-text="redemption.kode_redeem"></span>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Pilih/Gunakan Voucher -->
                    <div x-show="modalVoucherTerbuka"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4"
                        x-transition x-cloak>
                        <div @click.away="tutupModalVoucher()"
                            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800 flex flex-col max-h-[90vh]">
                            <div class="bg-rose-700 dark:bg-rose-900 text-white p-4 flex items-center justify-between shrink-0">
                                <div>
                                    <h3 class="font-extrabold text-sm">Gunakan Voucher</h3>
                                    <p class="text-white/80 text-xs mt-0.5" x-text="memberTerpilih ? memberTerpilih.nama : ''"></p>
                                </div>
                                <button @click="tutupModalVoucher()"
                                    class="text-white hover:text-gray-200 font-bold text-lg leading-none">&times;</button>
                            </div>

                            <div class="p-5 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
                                <!-- Input kode manual -->
                                <div>
                                    <label
                                        class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-1.5">Masukkan
                                        Kode Voucher</label>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="kodeVoucherManual"
                                            @input="kodeVoucherManual = kodeVoucherManual.toUpperCase()"
                                            @keydown.enter="validasiKodeVoucherManual()"
                                            placeholder="Contoh: ABC-1234"
                                            class="flex-1 px-3 py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-rose-500 uppercase">
                                        <button type="button" @click="validasiKodeVoucherManual()"
                                            :disabled="voucherKodeLoading || !kodeVoucherManual.trim()"
                                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-lg transition shrink-0">
                                            <span x-show="!voucherKodeLoading">Pakai</span>
                                            <span x-show="voucherKodeLoading">...</span>
                                        </button>
                                    </div>
                                    <template x-if="voucherModalError">
                                        <p class="text-[10px] text-red-500 mt-1.5" x-text="voucherModalError"></p>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">atau
                                        pilih</span>
                                    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                </div>

                                <!-- Daftar voucher aktif member -->
                                <div class="flex flex-col gap-2">
                                    <template x-if="voucherDaftarLoading">
                                        <p class="text-xs text-center text-gray-400 py-4">Memuat voucher...</p>
                                    </template>

                                    <template x-if="!voucherDaftarLoading && daftarVoucherAktif.length === 0">
                                        <p class="text-xs text-center text-gray-400 py-4">Member ini belum punya voucher
                                            aktif.</p>
                                    </template>

                                    <template x-for="voucher in daftarVoucherAktif" :key="voucher.id">
                                        <button type="button" @click="pilihVoucherItem(voucher)"
                                            class="w-full text-left flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 hover:bg-rose-50/50 dark:hover:bg-rose-950/20 cursor-pointer transition">
                                            <div
                                                class="w-8 h-8 bg-rose-600 text-white flex items-center justify-center rounded-full shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                                        x-text="voucher.nama"></span>
                                                    <span class="text-xs font-extrabold text-rose-600 dark:text-rose-400 shrink-0"
                                                        x-text="voucher.jenis_potongan === 'nominal' ? ('-' + formatRupiah(voucher.nominal_potongan)) : ('-' + formatPersen(voucher.persentase) + '%')"></span>
                                                </div>
                                                <div class="flex items-center gap-2 text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                                    <span x-text="voucher.kode_redeem"></span>
                                                    <template x-if="voucher.min_pembelian > 0">
                                                        <span>· Min. <strong class="text-gray-600 dark:text-gray-300"
                                                                x-text="formatRupiah(voucher.min_pembelian)"></strong></span>
                                                    </template>
                                                    <template x-if="voucher.berlaku_sampai">
                                                        <span>· Berlaku s/d <strong class="text-gray-600 dark:text-gray-300"
                                                                x-text="voucher.berlaku_sampai"></strong></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Keranjang -->
                    <div
                        class="p-3 border-b border-gray-100 dark:border-gray-800 overflow-y-auto flex-1 flex flex-col gap-2 min-h-0 custom-scrollbar">
                        <div x-show="keranjang.length === 0"
                            class="flex-1 flex flex-col items-center justify-center py-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-gray-400 dark:text-gray-500 text-xs font-semibold">Keranjang masih kosong</p>
                        </div>
                        <template x-for="item in keranjang" :key="item.key">
                            <div
                                class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-800/40 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shrink-0">
                                        <img :src="getFotoUrl(item.foto)" x-on:error="handleFotoError($event)"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h5 class="font-bold text-gray-900 dark:text-white text-xs truncate"
                                                x-text="item.nama_produk"></h5>
                                            <template x-if="item.ukuran">
                                                <span
                                                    class="shrink-0 text-[9px] font-bold text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/40 px-1.5 py-0.5 rounded-full border border-amber-300 dark:border-amber-700/50"
                                                    x-text="'Ukuran: ' + item.ukuran"></span>
                                            </template>
                                            <template x-if="item.is_reward_stamp">
                                                <span
                                                    class="shrink-0 text-[9px] font-bold text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-500/10 px-1.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-500/25">Reward
                                                    Stamp</span>
                                            </template>
                                            <template x-if="item.is_reward_stamp && itemButuhProduksi(item)">
                                                <span
                                                    class="shrink-0 text-[9px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-500/25">Perlu
                                                    Diproduksi</span>
                                            </template>
                                            <template x-if="!item.is_reward_stamp && itemButuhProduksi(item)">
                                                <span
                                                    class="shrink-0 text-[9px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-500/25">Perlu
                                                    Diproduksi</span>
                                            </template>
                                            <template x-if="!item.is_reward_stamp && !itemButuhProduksi(item)">
                                                <span
                                                    class="shrink-0 text-[9px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/25">Ready
                                                    Stock</span>
                                            </template>
                                        </div>
                                        <div class="flex flex-col gap-0.5 mt-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xxs font-bold text-amber-700 dark:text-amber-400"
                                                    x-text="item.is_reward_stamp ? 'Gratis' : formatRupiah(item.harga_jual_asli)"></span>
                                                <template
                                                    x-if="!item.is_reward_stamp && apakahBarisPertamaProduk(item) && item.promos_tersedia && item.promos_tersedia.length > 0">
                                                    <button type="button" @click="bukaModalPilihPromo(item)"
                                                        class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded border transition"
                                                        :class="item.promos_terpilih_ids && item.promos_terpilih_ids.length > 0 ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/25 hover:bg-rose-100' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 border-gray-200 dark:border-gray-700'"
                                                        title="Klik untuk memilih promo">
                                                        <span>🏷️</span>
                                                        <span
                                                            x-text="item.promos_terpilih_ids.length + '/' + item.promos_tersedia.length + ' Promo'"></span>
                                                        <svg class="w-2.5 h-2.5 ml-0.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                <template
                                                    x-if="!item.is_reward_stamp && apakahBarisPertamaProduk(item) && (!item.promos_tersedia || item.promos_tersedia.length === 0) && item.promo_aktif">
                                                    <span
                                                        class="text-[9px] font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-1 rounded"
                                                        x-text="item.promo_aktif.jenis_potongan === 'nominal' ? ('Promo Rp ' + Math.round(item.promo_aktif.nominal_potongan/1000) + 'rb') : ('Promo ' + formatPersen(item.promo_aktif.persentase) + '%')"></span>
                                                </template>
                                            </div>
                                            <span class="text-xxs text-gray-400 dark:text-gray-500">Total: <span
                                                    class="font-semibold text-gray-700 dark:text-gray-300"
                                                    x-text="item.is_reward_stamp ? 'Gratis' : formatRupiah(item.harga_jual_asli * item.jumlah)"></span></span>
                                        </div>
                                    </div>
                                    <template x-if="!item.is_reward_stamp">
                                        <div
                                            class="flex items-center border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-900 shrink-0">
                                            <button @click="kurangiJumlah(item)"
                                                class="px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold transition text-xs">-</button>
                                            <span class="px-2 text-xs font-bold text-gray-800 dark:text-gray-200"
                                                x-text="item.jumlah"></span>
                                            <button @click="tambahJumlah(item)"
                                                class="px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold transition text-xs">+</button>
                                        </div>
                                    </template>
                                    <template x-if="item.is_reward_stamp">
                                        <span
                                            class="px-2 py-1 text-xs font-bold text-purple-700 dark:text-purple-400 shrink-0"
                                            x-text="'x' + item.jumlah"></span>
                                    </template>
                                    <button @click="hapusItem(item)"
                                        class="p-1 hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 dark:text-red-400 rounded-md transition hover:text-red-700 ml-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400 dark:text-gray-500 text-[10px]">📝</span>
                                    <input type="text" x-model="item.catatan"
                                        placeholder="Catatan item (cth: Less sugar, extra hot)"
                                        class="w-full pl-6 pr-2 py-1 text-[10px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Pajak -->
                    <div x-show="pajakAktif"
                        class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/20 dark:bg-gray-800/10 flex flex-col gap-1.5 shrink-0">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">Pajak
                                    (%)</label>
                                <input type="text" inputmode="decimal" x-model="pajakPersen"
                                    @input="pajakPersen = pajakPersen.replace(/[^0-9,]/g, '').replace(/(,.*),/g, '$1')"
                                    placeholder="Masukan pajak (%)"
                                    class="w-full px-2 py-1 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="px-3 py-2 flex flex-col gap-1.5 shrink-0">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span class="font-semibold text-gray-800 dark:text-white"
                                x-text="formatRupiah(subtotal)"></span>
                        </div>
                        <template x-for="dItem in rincianDiskonList" :key="dItem.nama">
                            <div
                                class="flex items-center justify-between text-xs text-rose-600 dark:text-rose-400 font-semibold">
                                <span x-text="dItem.nama"></span>
                                <span class="font-semibold" x-text="'-' + formatRupiah(dItem.nilai)"></span>
                            </div>
                        </template>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                            x-show="pajakAktif && pajak > 0">
                            <span>Pajak (<span x-text="pajakPersen"></span>%)</span>
                            <span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(pajak)"></span>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-800">
                            <span class="font-bold text-gray-900 dark:text-white text-sm">Total Tagihan</span>
                            <span class="font-black text-amber-700 dark:text-amber-400 text-lg"
                                x-text="formatRupiah(totalTagihan)"></span>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="px-3 pb-3 pt-0 grid grid-cols-3 gap-2 shrink-0">
                        <button @click="tahanOrderan()" :disabled="keranjang.length === 0"
                            class="col-span-1 py-3 px-2 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold rounded-xl transition text-xs flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Bayar Nanti</span>
                        </button>
                        <button @click="lanjutKePembayaran()" :disabled="keranjang.length === 0"
                            class="col-span-2 py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span>Proses Bayar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== HALAMAN 2: LAYAR PEMBAYARAN ========== -->
        <div x-show="halamanAktif === 'pembayaran'" class="flex flex-col gap-6" x-transition>
            <div
                class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-xl shadow-xs border border-gray-100 dark:border-gray-800">
                <button @click="kembaliKeKasir()"
                    class="flex items-center gap-2 text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 font-bold transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali Ke Kasir</span>
                </button>
                <h2 class="font-extrabold text-lg text-gray-900 dark:text-white">Layar Pembayaran</h2>
                <div class="w-24"></div>
            </div>

            <div class="grid grid-cols-12 gap-6 items-start">
                <!-- Kiri: Numpad -->
                <div class="col-span-12 lg:col-span-7 flex flex-col gap-6">
                    <div
                        class="grid grid-cols-3 gap-4 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div class="flex flex-col gap-1">
                            <span class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total
                                Tagihan</span>
                            <span class="text-lg font-black text-gray-900 dark:text-white"
                                x-text="formatRupiah(totalTagihan)"></span>
                        </div>
                        <div class="flex flex-col gap-1 border-x border-gray-100 dark:border-gray-800 px-4">
                            <span class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Uang
                                Diterima</span>
                            <span class="text-lg font-black text-green-600 dark:text-green-400"
                                x-text="formatRupiah(jumlahDiterimaNum)"></span>
                        </div>
                        <div class="flex flex-col gap-1 pl-2">
                            <span
                                class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Kembalian</span>
                            <span class="text-lg font-black"
                                :class="kembalianNum >= 0 ? 'text-amber-700 dark:text-amber-400' : 'text-red-500 dark:text-red-400'"
                                x-text="formatRupiah(kembalianNum)"></span>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs flex flex-col gap-6">
                        <div>
                            <label
                                class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-3">Metode
                                Pembayaran</label>
                            <div class="flex gap-4">
                                <button @click="metodePembayaran = 'Tunai'"
                                    :class="metodePembayaran === 'Tunai' ? 'bg-amber-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="flex-1 py-3 px-5 rounded-xl font-bold tracking-wide transition text-sm">TUNAI
                                    (CASH)</button>
                                <button @click="metodePembayaran = 'QRIS'"
                                    :class="metodePembayaran === 'QRIS' ? 'bg-amber-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="flex-1 py-3 px-5 rounded-xl font-bold tracking-wide transition text-sm">QRIS (QR
                                    CODE)</button>
                            </div>
                        </div>

                        <!-- Mode Tunai -->
                        <div x-show="metodePembayaran === 'Tunai'" class="flex flex-col gap-6" x-transition>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label
                                        class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Input
                                        Uang Diterima (Rp)</label>
                                </div>
                                <div class="relative flex items-center">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-4 font-black text-gray-400 text-lg pointer-events-none select-none">Rp</span>
                                    <input x-ref="inputJumlahDiterima" type="text" inputmode="numeric" pattern="[0-9]*"
                                        :value="jumlahDiterimaTampilan" @input="handleInputJumlahDiterima($event)"
                                        @keydown.enter.prevent="if (kembalianNum >= 0) selesaikanPembayaran()"
                                        placeholder="0"
                                        class="w-full pl-12 pr-10 py-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-right font-black text-2xl text-slate-800 dark:text-white tracking-wide focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition">
                                    <button type="button" x-show="jumlahDiterima !== ''"
                                        @click="jumlahDiterima = ''; $nextTick(() => $refs.inputJumlahDiterima.focus())"
                                        title="Hapus input"
                                        class="absolute right-3 text-gray-400 hover:text-red-500 transition p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <button @click="setJumlahDiterimaCepat(totalTagihan)"
                                        class="px-3.5 py-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold rounded-lg text-xs transition border border-amber-200/50 dark:border-amber-500/20">Uang
                                        Pas</button>
                                    <button @click="tambahNominalCepat(20000)"
                                        class="px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition border border-gray-200/50 dark:border-gray-700/50">+Rp20k</button>
                                    <button @click="tambahNominalCepat(50000)"
                                        class="px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition border border-gray-200/50 dark:border-gray-700/50">+Rp50k</button>
                                    <button @click="tambahNominalCepat(100000)"
                                        class="px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition border border-gray-200/50 dark:border-gray-700/50">+Rp100k</button>
                                    <button @click="jumlahDiterima = ''; $nextTick(() => $refs.inputJumlahDiterima.focus())"
                                        class="px-3.5 py-2 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 font-bold rounded-lg text-xs transition border border-red-200/50 dark:border-red-500/20 ml-auto">Reset</button>
                                </div>
                            </div>
                        </div>

                        <!-- Mode QRIS -->
                        <div x-show="metodePembayaran === 'QRIS'"
                            class="flex flex-col items-center justify-center text-center gap-5 py-4" x-transition>
                            <div
                                class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 p-6 rounded-2xl border border-amber-100 dark:border-amber-500/20 flex flex-col items-center gap-4">
                                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&color=5a3c27&data=' + encodeURIComponent(qrisPayload)"
                                        class="w-44 h-44" alt="QRIS QR Code">
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-black text-gray-800 dark:text-white tracking-wider flex items-center gap-1.5">
                                        <img src="{{ asset('images/logo/logo.svg') }}" class="w-auto h-10 object-contain"
                                            alt="Saisho Logo">
                                    </span>
                                </div>
                            </div>
                            <div class="max-w-xs">
                                <h4 class="font-extrabold text-gray-950 dark:text-white text-base">Scan QRIS Untuk Bayar
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">Silakan tunjukkan
                                    QR
                                    Code di atas kepada pelanggan. Total pembayaran sebesar <span
                                        class="font-bold text-amber-600 dark:text-amber-400"
                                        x-text="formatRupiah(totalTagihan)"></span>.</p>
                            </div>
                            <button @click="kembaliKeKasir()"
                                class="w-full max-w-xs py-3 bg-slate-200 dark:bg-gray-800 hover:bg-slate-300 dark:hover:bg-gray-700 text-slate-800 dark:text-gray-300 font-bold text-xs rounded-xl active:scale-95 transition text-center">Batal</button>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Rincian Belanja -->
                <div
                    class="col-span-12 lg:col-span-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs p-6 flex flex-col gap-6">
                    <div>
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-lg mb-1">Rincian Belanja</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pelanggan: <span
                                class="font-bold text-gray-800 dark:text-gray-200" x-text="namaPembeli || '-'"></span></p>
                    </div>
                    <div
                        class="border-y border-gray-100 dark:border-gray-800 py-4 flex flex-col gap-3 max-h-[300px] overflow-y-auto">
                        <template x-for="item in keranjang" :key="item.key">
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-gray-900 dark:text-white">
                                        <span x-text="item.nama_produk"></span>
                                        <template x-if="item.ukuran">
                                            <span class="text-amber-600 dark:text-amber-400 font-extrabold text-[10px] ml-1" x-text="'(' + item.ukuran + ')'"></span>
                                        </template>
                                    </p>
                                    <p class="text-xxs text-gray-400 dark:text-gray-500">
                                        <span x-text="item.jumlah"></span> x <span
                                            x-text="formatRupiah(item.harga_jual_asli)"></span>
                                    </p>
                                </div>
                                <span class="font-bold text-xs text-gray-700 dark:text-gray-300 shrink-0"
                                    x-text="formatRupiah(item.harga_jual_asli * item.jumlah)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex flex-col gap-2 text-xs">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Subtotal</span><span
                                class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(subtotal)"></span>
                        </div>
                        <template x-for="dItem in rincianDiskonList" :key="dItem.nama">
                            <div class="flex justify-between text-rose-600 dark:text-rose-400 font-semibold">
                                <span x-text="dItem.nama"></span>
                                <span class="font-semibold" x-text="'-' + formatRupiah(dItem.nilai)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400" x-show="pajak > 0"><span>Pajak
                                (<span x-text="pajakPersen"></span>%)</span><span
                                class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(pajak)"></span>
                        </div>
                        <div
                            class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm text-gray-900 dark:text-white">
                            <span>Total Pembayaran</span><span class="text-amber-700 dark:text-amber-400 text-base"
                                x-text="formatRupiah(totalTagihan)"></span>
                        </div>
                    </div>
                    <button @click="selesaikanPembayaran()" :disabled="kembalianNum < 0"
                        class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 disabled:shadow-none disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== MODAL: DAFTAR PESANAN BELUM BAYAR ========== -->
        <div x-show="modalDitahanTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-3xl overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="bg-amber-800 dark:bg-amber-900 text-white p-5 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-base">Daftar Pesanan Belum Bayar</h3>
                        <p class="text-white text-xs mt-0.5">Pesanan belum lunas yang menunggu pembayaran.</p>
                    </div>
                </div>
                <div class="p-6 max-h-[450px] overflow-y-auto flex flex-col gap-4 custom-scrollbar">
                    <template x-if="daftarDitahan.length === 0">
                        <div class="text-center py-10">
                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Tidak ada pesanan yang belum
                                dibayar.</p>
                        </div>
                    </template>
                    <template x-for="pesanan in daftarDitahan" :key="pesanan.id">
                        <div
                            class="border border-gray-100 dark:border-gray-800 rounded-xl p-4 bg-gray-50 dark:bg-gray-800/40 flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-extrabold text-sm text-gray-900 dark:text-white"
                                            x-text="pesanan.nama_pembeli"></span>
                                        <span
                                            class="px-2 py-0.5 bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400 text-[10px] font-bold rounded-full">Belum
                                            Lunas</span>
                                        <template x-if="pesanan.transaksi">
                                            <span
                                                class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-mono rounded-full"
                                                x-text="pesanan.transaksi.kode_transaksi"></span>
                                        </template>
                                    </div>
                                    <p class="text-xxs text-gray-400 dark:text-gray-500 mt-0.5"
                                        x-text="formatTanggal(pesanan.created_at)"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Item: <span
                                            class="font-semibold text-gray-700 dark:text-gray-300"
                                            x-text="pesanan.item_pesanan.map(i => i.nama_produk + ' (' + i.jumlah + ')').join(', ')"></span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-show="pesanan.catatan">
                                        Catatan:
                                        <span class="italic text-gray-600 dark:text-gray-400"
                                            x-text="pesanan.catatan"></span>
                                    </p>
                                </div>
                                <span class="font-extrabold text-sm text-amber-700 dark:text-amber-400 shrink-0"
                                    x-text="formatRupiah(pesanan.total)"></span>
                            </div>
                            <div
                                class="flex items-center gap-2 flex-wrap border-t border-gray-100 dark:border-gray-800 pt-3">
                                <button @click="cetakStrukDitahan(pesanan, 'pelanggan')"
                                    class="px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Struk
                                </button>
                                <button @click="cetakStrukDitahan(pesanan, 'dapur')"
                                    class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Tiket Produksi
                                </button>
                                <button @click="mulaiLunaskan(pesanan)"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Lunaskan
                                </button>
                                <button @click="pulihkanPesanan(pesanan.id)"
                                    class="px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Lanjutkan Pesanan
                                </button>
                                <button @click="hapusPesananDitahan(pesanan.id)"
                                    class="p-1.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-500 dark:text-red-400 rounded-lg transition ml-auto"
                                    title="Hapus pesanan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                    <button @click="modalDitahanTerbuka = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Tutup</button>
                </div>
            </div>
        </div>

        <!-- ========== MODAL: INPUT NAMA PEMBELI ========== -->
        <div x-show="modalPembeliTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
            <div @click.away="modalPembeliTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="bg-amber-800 dark:bg-amber-900 text-white p-5">
                    <h3 class="font-extrabold text-base">Nama Pembeli</h3>
                    <p class="text-white text-xs mt-0.5">Masukkan nama pembeli untuk pesanan ini.</p>
                </div>
                <div class="p-6">
                    <label class="text-xs font-bold text-gray-600 dark:text-gray-400 block mb-1">Nama Pembeli <span
                            class="text-red-500">*</span></label>
                    <input type="text" x-model="inputNamaPembeli" @keydown.enter="simpanPembeli()"
                        placeholder="Contoh: Budi Santoso"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-850 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                        autofocus>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                    <button @click="modalPembeliTerbuka = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button @click="simpanPembeli()"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Simpan</button>
                </div>
            </div>
        </div>

        <!-- ========== MODAL: PEMBAYARAN SUKSES + CETAK STRUK ========== -->
        <div x-show="modalSuksesTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
            <div
                class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xl w-full max-w-sm overflow-hidden p-6 flex flex-col items-center text-center gap-6">
                <div
                    class="w-16 h-16 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-3xl animate-bounce">
                    ✓</div>
                <div>
                    <template x-if="suksesData.status_pembayaran === 'Lunas'">
                        <div>
                            <h3 class="font-extrabold text-gray-900 dark:text-white text-lg">Pembayaran Sukses!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Transaksi telah berhasil dicatat.</p>
                        </div>
                    </template>
                    <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
                        <div>
                            <h3 class="font-extrabold text-gray-900 dark:text-white text-lg">Pesanan disimpan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pesanan tercatat sebagai "Belum
                                Dibayar". Cetak tiket produksi di bawah.</p>
                        </div>
                    </template>
                    <span
                        class="inline-block mt-3 px-3 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20 font-mono text-xs rounded-full font-bold"
                        x-text="suksesData.kode_transaksi"></span>
                    <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
                        <span
                            class="inline-block ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400 text-[10px] font-bold rounded-full">Belum
                            Lunas</span>
                    </template>
                </div>
                <template x-if="suksesData.status_pembayaran === 'Lunas'">
                    <div
                        class="w-full bg-gray-50 dark:bg-gray-950 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col gap-2 text-xs">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Total
                                Pembayaran</span><span class="font-semibold text-gray-800 dark:text-white"
                                x-text="formatRupiah(suksesData.total_bayar)"></span></div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Uang Diterima</span><span
                                class="font-semibold text-gray-800 dark:text-white"
                                x-text="formatRupiah(suksesData.jumlah_diterima)"></span></div>
                        <div
                            class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm">
                            <span>Uang Kembalian</span><span class="text-green-600 dark:text-green-400"
                                x-text="formatRupiah(suksesData.kembalian)"></span>
                        </div>
                    </div>
                </template>
                <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
                    <div
                        class="w-full bg-amber-50 dark:bg-amber-500/5 rounded-xl p-4 border border-amber-200 dark:border-amber-500/20 flex flex-col gap-2 text-xs">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Total Tagihan</span><span
                                class="font-bold text-amber-700 dark:text-amber-400"
                                x-text="formatRupiah(suksesData.total_bayar)"></span></div>
                        <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Status</span><span
                                class="font-bold text-red-600 dark:text-red-400">Belum Lunas</span></div>
                    </div>
                </template>
                <!-- Tombol Cetak Struk -->
                <div class="w-full flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="cetakStruk('pelanggan')"
                            class="py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Struk Pelanggan</span>
                        </button>
                        <button @click="cetakStruk('dapur')"
                            class="py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Tiket Produksi</span>
                        </button>
                    </div>
                </div>
                <button @click="tutupModalSukses()"
                    class="w-full py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition text-sm"
                    x-text="suksesData.status_pembayaran === 'Lunas' ? 'Transaksi Baru' : 'Kembali'"></button>
            </div>
        </div>

        <!-- ========== MODAL: KONFIRMASI HAPUS PESANAN DITAHAN ========== -->
        <div x-show="modalKonfirmasiHapusTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4"
            x-transition x-cloak>
            <div @click.away="modalKonfirmasiHapusTerbuka = false; modalDitahanTerbuka = true"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div
                        class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                        ⚠</div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Pesanan Belum Bayar?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Pesanan yang dihapus tidak dapat dipulihkan
                        kembali.</p>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button @click="modalKonfirmasiHapusTerbuka = false; modalDitahanTerbuka = true"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button @click="eksekusiHapusPesananDitahan()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
                </div>
            </div>
        </div>

        <!-- ========== MODAL: PILIH PROMO ITEM ========== -->
        <div x-show="modalPilihPromoTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4"
            x-transition x-cloak>
            <div @click.away="modalPilihPromoTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="bg-amber-800 dark:bg-amber-900 text-white p-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm">Pilih Promo Produk</h3>
                        <p class="text-white/80 text-xs mt-0.5"
                            x-text="itemPromoTerpilih ? itemPromoTerpilih.nama_produk : ''">
                        </p>
                    </div>
                    <button @click="modalPilihPromoTerbuka = false"
                        class="text-white hover:text-gray-200 font-bold text-lg leading-none">&times;</button>
                </div>

                <div class="p-5 flex flex-col gap-4 max-h-[380px] overflow-y-auto custom-scrollbar">
                    <div
                        class="flex items-center justify-between text-xs pb-2 border-b border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400">Promo yang tersedia:</span>
                        <div class="flex items-center gap-2"
                            x-show="itemPromoTerpilih && itemPromoTerpilih.promos_tersedia && itemPromoTerpilih.promos_tersedia.length > 1">
                            <button type="button" @click="pilihSemuaPromoItem(itemPromoTerpilih, true)"
                                class="text-amber-700 dark:text-amber-400 font-bold hover:underline">Pilih Semua</button>
                            <span class="text-gray-300 dark:text-gray-700">|</span>
                            <button type="button" @click="pilihSemuaPromoItem(itemPromoTerpilih, false)"
                                class="text-gray-500 font-medium hover:underline">Hapus Semua</button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5">
                        <template x-for="promo in (itemPromoTerpilih ? itemPromoTerpilih.promos_tersedia : [])"
                            :key="promo.id">
                            <label
                                class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 cursor-pointer transition">
                                <input type="checkbox" :checked="itemPromoTerpilih.promos_terpilih_ids.includes(promo.id)"
                                    @change="togglePromoItem(itemPromoTerpilih, promo.id)"
                                    class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-amber-600 focus:ring-amber-500">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white"
                                            x-text="promo.nama_promo"></span>
                                        <span class="text-xs font-extrabold text-amber-700 dark:text-amber-400 shrink-0"
                                            x-text="promo.jenis_potongan === 'nominal' ? ('-Rp ' + Math.round(promo.nominal_potongan/1000) + 'rb') : ('-' + formatPersen(promo.persentase) + '%')"></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                                        <span>Tipe: <strong class="text-gray-600 dark:text-gray-300"
                                                x-text="promo.jenis_potongan"></strong></span>
                                        <template x-if="promo.min_pembelian > 0">
                                            <span>Min. Belanja: <strong class="text-gray-600 dark:text-gray-300"
                                                    x-text="formatRupiah(promo.min_pembelian)"></strong></span>
                                        </template>
                                    </div>
                                </div>
                            </label>
                        </template>

                        <template
                            x-if="!itemPromoTerpilih || !itemPromoTerpilih.promos_tersedia || itemPromoTerpilih.promos_tersedia.length === 0">
                            <p class="text-xs text-center text-gray-400 py-4">Tidak ada promo aktif untuk produk ini.</p>
                        </template>
                    </div>
                </div>

                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider block font-semibold">Total Diskon
                            Item Ini</span>
                        <span class="font-extrabold text-sm text-amber-700 dark:text-amber-400"
                            x-text="itemPromoTerpilih ? ('-' + formatRupiah(hitungDiskonItem(itemPromoTerpilih))) : 'Rp 0'"></span>
                    </div>
                    <button type="button" @click="modalPilihPromoTerbuka = false"
                        class="px-5 py-2 bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs rounded-xl transition shadow-xs">
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== MODAL: PILIH UKURAN PRODUK ========== -->
        <div x-show="modalPilihUkuranTerbuka"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4"
            x-transition x-cloak>
            <div @click.away="modalPilihUkuranTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="bg-amber-800 dark:bg-amber-900 text-white p-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-sm">Pilih Ukuran Produk</h3>
                        <p class="text-white/80 text-xs mt-0.5"
                            x-text="produkUkuranTerpilih ? produkUkuranTerpilih.nama_produk : ''">
                        </p>
                    </div>
                    <button @click="modalPilihUkuranTerbuka = false"
                        class="text-white hover:text-gray-200 font-bold text-lg leading-none">&times;</button>
                </div>

                <div class="p-5 flex flex-col gap-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Pilih varian ukuran untuk produk ini:</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="pilihUkuran('Kecil')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-amber-600 bg-amber-50/50 dark:bg-amber-950/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                            <span class="font-bold text-sm text-amber-900 dark:text-amber-300">Kecil</span>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 mt-1"
                                x-text="formatRupiah(produkUkuranTerpilih?.harga_kecil || 0)"></span>
                        </button>
                        <button type="button" @click="pilihUkuran('Besar')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-amber-600 bg-amber-50/50 dark:bg-amber-950/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                            <span class="font-bold text-sm text-amber-900 dark:text-amber-300">Besar</span>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 mt-1"
                                x-text="formatRupiah(produkUkuranTerpilih?.harga_besar || 0)"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function aplikasiKasir() {
            return {
                halamanAktif: 'beranda',
                daftarProduk: @json($daftarProduk),
                daftarKategori: @json($daftarKategori),
                daftarBundling: @json($daftarBundling),
                daftarPromoEvent: @json($daftarPromoEvent),
                daftarMember: @json($daftarMember),
                kataKunciPencarian: '',
                kategoriTerpilih: 'semua',
                modeTampilan: 'produk',
                filterKetersediaan: 'semua',
                filterBundling: 'semua',
                filterPromoEvent: 'semua',
                itemsPerPage: '10',
                halamanProduk: 1,

                // Keranjang
                keranjang: [],
                namaPembeli: '',
                memberIdTerpilih: '',
                memberTerpilih: null,
                catatan: '',
                diskonPersen: '',
                pajakAktif: {{ $pengaturanPajak->status_aktif ? 'true' : 'false' }},
                pajakPersenDefault: '{{ $pengaturanPajak->status_aktif ? $pengaturanPajak->persentase : '0' }}',
                pajakPersen: '{{ $pengaturanPajak->status_aktif ? $pengaturanPajak->persentase : '0' }}',

                // Voucher member
                voucherTerpilih: null,
                daftarVoucherAktif: [],
                voucherDaftarLoading: false,
                kodeVoucherManual: '',
                voucherKodeLoading: false,
                voucherModalError: '',

                // Reward Stamp member — daftar reward yang sudah diklaim di portal
                // member (status menunggu_diambil), kasir tinggal pilih satu untuk
                // ditautkan ke transaksi ini. Sama persis polanya dengan voucher.
                modalRewardStampTerbuka: false,
                daftarRewardStampAktif: [],
                rewardStampDaftarLoading: false,
                kodeRewardStampManual: '',
                rewardStampKodeLoading: false,
                rewardStampModalError: '',

                // Input pembeli modal
                inputNamaPembeli: '',

                // Pembayaran
                jumlahDiterima: '',
                metodePembayaran: 'Tunai',

                // Ditahan
                daftarDitahan: @json($daftarDitahan),

                // Modals
                modalDitahanTerbuka: false,
                modalPembeliTerbuka: false,
                modalSuksesTerbuka: false,
                modalKonfirmasiHapusTerbuka: false,
                modalPilihPromoTerbuka: false,
                modalPilihUkuranTerbuka: false,
                modalVoucherTerbuka: false,
                itemPromoTerpilih: null,
                produkUkuranTerpilih: null,
                idPesananAkanDihapus: null,

                // Data sukses (untuk struk)
                suksesData: {
                    kode_transaksi: '',
                    total_bayar: 0,
                    jumlah_diterima: 0,
                    kembalian: 0,
                    nama_pembeli: '',
                    keranjang: [],
                    subtotal: 0,
                    diskon_persen: 0,
                    nilai_diskon: 0,
                    pajak_persen: 0,
                    pajak: 0,
                    metode_pembayaran: 'Tunai',
                    catatan: '',
                    tanggal: '',
                    status_pembayaran: ''
                },

                pesananSedangDilunaskan: null,

                toasts: [],
                toastIdCounter: 0,

                inisialisasi() {
                    // Cek pertama kali saat halaman dibuka
                    this.perbaruiPromoKadaluwarsa();

                    // Jalan tiap 5 detik: kalau ada promo yang selesai_berlaku-nya
                    // sudah lewat dari waktu sekarang, badge/harga diskonnya otomatis
                    // hilang dari layar tanpa perlu refresh browser.
                    this._intervalPromoKadaluwarsa = setInterval(() => {
                        this.perbaruiPromoKadaluwarsa();
                    }, 5000);
                },

                // Menentukan apakah sebuah objek promo masih berlaku dibanding waktu sekarang (jam di browser kasir)
                _promoMasihBerlaku(promo, sekarang) {
                    if (!promo || !promo.status_aktif) return false;
                    if (promo.mulai_berlaku && new Date(promo.mulai_berlaku) > sekarang) return false;
                    if (promo.selesai_berlaku && new Date(promo.selesai_berlaku) <= sekarang) return false;
                    return true;
                },

                // Menghitung ulang promo mana yang paling menguntungkan (sama seperti logika di backend)
                _pilihPromoTerbaik(daftarPromo, hargaJual) {
                    if (!daftarPromo || daftarPromo.length === 0) return null;
                    const nilaiPromo = (p) => p.jenis_potongan === 'nominal'
                        ? parseFloat(p.nominal_potongan || 0)
                        : (parseFloat(p.persentase || 0) / 100) * hargaJual;
                    return daftarPromo.reduce((terbaik, p) => (
                        !terbaik || nilaiPromo(p) > nilaiPromo(terbaik) ? p : terbaik
                    ), null);
                },

                perbaruiPromoKadaluwarsa() {
                    const sekarang = new Date();

                    // 1. Grid/daftar produk di halaman kasir
                    this.daftarProduk.forEach(produk => {
                        if (produk.promos_aktif_semua && produk.promos_aktif_semua.length > 0) {
                            const jumlahSemula = produk.promos_aktif_semua.length;
                            produk.promos_aktif_semua = produk.promos_aktif_semua.filter(p => this._promoMasihBerlaku(p, sekarang));

                            if (produk.promos_aktif_semua.length !== jumlahSemula) {
                                produk.promo_aktif = this._pilihPromoTerbaik(produk.promos_aktif_semua, produk.harga_jual);
                                produk.harga_diskon = produk.promo_aktif
                                    ? Math.max(0, produk.harga_jual - (produk.promo_aktif.jenis_potongan === 'nominal'
                                        ? parseFloat(produk.promo_aktif.nominal_potongan || 0)
                                        : (parseFloat(produk.promo_aktif.persentase || 0) / 100) * produk.harga_jual))
                                    : produk.harga_jual;
                            }
                        } else if (produk.promo_aktif && !this._promoMasihBerlaku(produk.promo_aktif, sekarang)) {
                            produk.promo_aktif = null;
                            produk.harga_diskon = produk.harga_jual;
                        }
                    });

                    // 2. Item yang sudah terlanjur ada di keranjang, biar checkbox & badge "Promo" ikut hilang
                    this.keranjang.forEach(item => {
                        if (item.promos_tersedia && item.promos_tersedia.length > 0) {
                            const idSebelumnya = item.promos_tersedia.map(p => p.id);
                            item.promos_tersedia = item.promos_tersedia.filter(p => this._promoMasihBerlaku(p, sekarang));
                            const idSekarang = item.promos_tersedia.map(p => p.id);

                            if (idSekarang.length !== idSebelumnya.length && item.promos_terpilih_ids) {
                                item.promos_terpilih_ids = item.promos_terpilih_ids.filter(id => idSekarang.includes(id));
                            }
                        }
                        if (item.promo_aktif && !this._promoMasihBerlaku(item.promo_aktif, sekarang)) {
                            item.promo_aktif = null;
                        }
                    });
                },

                parsePersen(v) {
                    if (typeof v === 'number') return v;
                    const n = parseFloat(String(v ?? '').replace(',', '.'));
                    return isNaN(n) ? 0 : n;
                },

                // Voucher member
                bukaModalVoucher() {
                    if (!this.memberIdTerpilih) return;
                    this.voucherModalError = '';
                    this.kodeVoucherManual = '';
                    this.modalVoucherTerbuka = true;
                    this.ambilDaftarVoucherAktif();
                },

                tutupModalVoucher() {
                    this.modalVoucherTerbuka = false;
                },

                ambilDaftarVoucherAktif() {
                    if (!this.memberIdTerpilih) { this.daftarVoucherAktif = []; return; }
                    this.daftarVoucherAktif = [];
                    this.voucherDaftarLoading = true;
                    fetch(`/transaksi/voucher/member/${this.memberIdTerpilih}`)
                        .then(r => r.json())
                        .then(r => { this.daftarVoucherAktif = (r.status === 'success') ? r.data : []; })
                        .catch(() => { this.daftarVoucherAktif = []; })
                        .finally(() => { this.voucherDaftarLoading = false; });
                },

                pilihVoucherItem(voucher) {
                    this.voucherTerpilih = voucher;
                    this.kodeVoucherManual = '';
                    this.voucherModalError = '';
                    this.modalVoucherTerbuka = false;
                    this.tambahToast('Voucher "' + voucher.nama + '" dipakai.', 'sukses');
                },

                validasiKodeVoucherManual() {
                    const kode = this.kodeVoucherManual.trim();
                    if (!kode || this.voucherKodeLoading) return;

                    if (!this.memberIdTerpilih) {
                        this.voucherModalError = 'Pilih member terlebih dahulu sebelum memakai voucher.';
                        return;
                    }

                    this.voucherModalError = '';
                    this.voucherKodeLoading = true;

                    fetch("{{ route('transaksi.voucher.validasi') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ kode: kode, member_id: this.memberIdTerpilih })
                    })
                        .then(r => r.json().then(data => ({ ok: r.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok && data.status === 'success') {
                                this.pilihVoucherItem(data.data);
                            } else {
                                this.voucherModalError = data.pesan || 'Kode voucher tidak valid.';
                            }
                        })
                        .catch(() => { this.voucherModalError = 'Gagal memvalidasi kode voucher.'; })
                        .finally(() => { this.voucherKodeLoading = false; });
                },

                hapusVoucherTerpilih() {
                    this.voucherTerpilih = null;
                },

                // Reward Stamp member
                get rewardStampDiKeranjang() {
                    return this.keranjang.find(i => i.is_reward_stamp) || null;
                },

                bukaModalRewardStamp() {
                    if (!this.memberIdTerpilih) return;
                    this.rewardStampModalError = '';
                    this.kodeRewardStampManual = '';
                    this.modalRewardStampTerbuka = true;
                    this.ambilDaftarRewardStampAktif();
                },

                tutupModalRewardStamp() {
                    this.modalRewardStampTerbuka = false;
                },

                ambilDaftarRewardStampAktif() {
                    if (!this.memberIdTerpilih) { this.daftarRewardStampAktif = []; return; }
                    this.daftarRewardStampAktif = [];
                    this.rewardStampDaftarLoading = true;
                    fetch(`/transaksi/reward-stamp/member/${this.memberIdTerpilih}`)
                        .then(r => r.json())
                        .then(r => { this.daftarRewardStampAktif = (r.status === 'success') ? r.data : []; })
                        .catch(() => { this.daftarRewardStampAktif = []; })
                        .finally(() => { this.rewardStampDaftarLoading = false; });
                },

                pilihRewardStampItem(redemption) {
                    // Hanya boleh satu reward stamp per transaksi — ganti kalau sudah ada.
                    this.keranjang = this.keranjang.filter(i => !i.is_reward_stamp);

                    this.keranjang.push({
                        key: 'reward_stamp_' + redemption.id,
                        stamp_redemption_id: redemption.id,
                        kode_redeem: redemption.kode_redeem,
                        produk_id: null,
                        is_reward_stamp: true,
                        nama_produk: redemption.nama_produk,
                        harga_jual: 0,
                        harga_jual_asli: 0,
                        foto: redemption.gambar,
                        jumlah: redemption.jumlah,
                        butuh_produksi: !!redemption.butuh_produksi,
                        catatan: 'Reward Stamp'
                    });

                    this.kodeRewardStampManual = '';
                    this.rewardStampModalError = '';
                    this.modalRewardStampTerbuka = false;
                    this.tambahToast('Reward "' + redemption.nama_produk + '" ditambahkan ke keranjang.', 'sukses');
                },

                validasiKodeRewardStampManual() {
                    const kode = this.kodeRewardStampManual.trim();
                    if (!kode || this.rewardStampKodeLoading) return;

                    if (!this.memberIdTerpilih) {
                        this.rewardStampModalError = 'Pilih member terlebih dahulu sebelum memakai reward stamp.';
                        return;
                    }

                    this.rewardStampModalError = '';
                    this.rewardStampKodeLoading = true;

                    fetch("{{ route('transaksi.reward-stamp.validasi') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ kode: kode, member_id: this.memberIdTerpilih })
                    })
                        .then(r => r.json().then(data => ({ ok: r.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok && data.status === 'success') {
                                this.pilihRewardStampItem(data.data);
                            } else {
                                this.rewardStampModalError = data.pesan || 'Kode reward stamp tidak valid.';
                            }
                        })
                        .catch(() => { this.rewardStampModalError = 'Gagal memvalidasi kode reward stamp.'; })
                        .finally(() => { this.rewardStampKodeLoading = false; });
                },

                hapusRewardStampTerpilih() {
                    this.keranjang = this.keranjang.filter(i => !i.is_reward_stamp);
                    this.tambahToast('Reward stamp dihapus dari keranjang.', 'info');
                },

                // Bentuk ulang relasi voucher (dari transaksi.voucher yang di-eager-load backend)
                // jadi struktur yang sama dengan voucherTerpilih supaya bisa dipakai ulang
                // baik saat melanjutkan pesanan ditahan ke keranjang maupun saat langsung dilunaskan.
                _formatVoucherDariRelasi(voucher) {
                    if (!voucher) return null;
                    const reward = voucher.reward || {};
                    return {
                        id: voucher.id,
                        kode_redeem: voucher.kode_redeem,
                        nama: reward.nama || 'Voucher',
                        deskripsi: reward.deskripsi || null,
                        jenis_potongan: reward.jenis_potongan || 'nominal',
                        nominal_potongan: parseFloat(reward.nominal_potongan || 0),
                        persentase: parseFloat(reward.persentase || 0),
                        maksimal_potongan: (reward.maksimal_potongan !== null && reward.maksimal_potongan !== undefined)
                            ? parseFloat(reward.maksimal_potongan) : null,
                        min_pembelian: parseFloat(reward.min_pembelian || 0),
                        berlaku_sampai: voucher.berlaku_sampai || null,
                    };
                },

                get qrisPayload() {
                    // Base QRIS format for static/dynamic merchant
                    const baseMerchant = "00020101021226380010co.saishos0115936000020012345520458125303360";
                    const amtStr = this.totalTagihan.toString();
                    const amtPart = "54" + amtStr.length.toString().padStart(2, '0') + amtStr;
                    const rest = "5802ID5907saishoS6007Jakarta6304";
                    const payloadWithoutCrc = baseMerchant + amtPart + rest;

                    // Calculate CRC-16 CCITT
                    let crc = 0xFFFF;
                    for (let c = 0; c < payloadWithoutCrc.length; c++) {
                        crc ^= payloadWithoutCrc.charCodeAt(c) << 8;
                        for (let i = 0; i < 8; i++) {
                            if ((crc & 0x8000) !== 0) {
                                crc = (crc << 1) ^ 0x1021;
                            } else {
                                crc = crc << 1;
                            }
                        }
                    }
                    const crcHex = (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
                    return payloadWithoutCrc + crcHex;
                },

                getFotoUrl(foto) {
                    if (foto) {
                        if (foto.startsWith('http://') || foto.startsWith('https://')) return foto;
                        return '/storage/' + foto;
                    }
                    return '/images/logo/logo-icon.svg';
                },
                handleFotoError(event) {
                    event.target.onerror = null;
                    event.target.src = '/images/logo/logo-icon.svg';
                },

                butuhProduksi(produk) {
                    return produk.status_ketersediaan === 'perlu_diproduksi' || this.stokUntukUkuran(produk, null) <= 0;
                },

                // Ambil stok sesuai ukuran (Besar/Kecil) — kalau produk tidak
                // punya varian ukuran, kembalikan stok gabungan seperti biasa.
                stokUntukUkuran(produk, ukuran) {
                    if (!produk) return 0;
                    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
                    if (punyaUkuran) {
                        if (ukuran === 'Besar') return parseFloat(produk.stok_besar) || 0;
                        if (ukuran === 'Kecil') return parseFloat(produk.stok_kecil) || 0;
                        return (parseFloat(produk.stok_besar) || 0) + (parseFloat(produk.stok_kecil) || 0);
                    }
                    return parseFloat(produk.stok) || 0;
                },

                itemButuhProduksi(item) {
                    if (item.is_bundling) {
                        if (typeof item.butuh_produksi !== 'undefined' && item.butuh_produksi !== null) {
                            return !!item.butuh_produksi;
                        }
                        if (item.items && item.items.length > 0) {
                            return item.items.some(k => {
                                if (typeof k.butuh_produksi !== 'undefined') return !!k.butuh_produksi;
                                const p = this.daftarProduk.find(x => x.id === k.produk_id);
                                return p ? (p.status_ketersediaan === 'perlu_diproduksi' || (parseFloat(p.stok) || 0) <= 0) : false;
                            });
                        }
                        return false;
                    }
                    if (typeof item.butuh_produksi !== 'undefined' && item.butuh_produksi !== null) {
                        return !!item.butuh_produksi;
                    }
                    return item.status_ketersediaan === 'perlu_diproduksi';
                },

                apakahBarisPertamaProduk(item) {
                    const index = this.keranjang.findIndex(i => i.produk_id === item.produk_id);
                    return index !== -1 && this.keranjang[index] === item;
                },

                buatItemKeranjang(produk, jumlah, perluProduksi, ukuran = null) {
                    let hargaAsli = parseFloat(produk.harga_jual || 0);
                    if (produk.punya_ukuran) {
                        if (ukuran === 'Besar') {
                            hargaAsli = parseFloat(produk.harga_besar || 0);
                        } else if (ukuran === 'Kecil') {
                            hargaAsli = parseFloat(produk.harga_kecil || 0);
                        } else {
                            hargaAsli = parseFloat(produk.harga_kecil || produk.harga_besar || 0);
                            ukuran = 'Kecil';
                        }
                    }
                    const promosTersedia = (produk.promos_aktif_semua && produk.promos_aktif_semua.length > 0)
                        ? produk.promos_aktif_semua
                        : (produk.promo_aktif ? [produk.promo_aktif] : []);
                    const barisLain = this.keranjang.find(i => i.produk_id === produk.id && (i.ukuran || null) === (ukuran || null));
                    const promosTerpilihIds = barisLain
                        ? [...barisLain.promos_terpilih_ids]
                        : promosTersedia.map(p => p.id);

                    return {
                        key: produk.id + '_' + (ukuran || 'std') + '_' + (perluProduksi ? 'produksi' : 'ready'),
                        produk_id: produk.id,
                        nama_produk: produk.nama_produk,
                        ukuran: ukuran,
                        harga_jual: hargaAsli,
                        harga_jual_asli: hargaAsli,
                        promo_aktif: produk.promo_aktif,
                        promos_tersedia: promosTersedia,
                        promos_terpilih_ids: promosTerpilihIds,
                        foto: produk.foto,
                        jumlah: jumlah,
                        stok: this.stokUntukUkuran(produk, ukuran),
                        status_ketersediaan: produk.status_ketersediaan,
                        butuh_produksi: perluProduksi,
                        catatan: ''
                    };
                },
                normalisasiItemKeranjang(items) {
                    return (items || []).map(item => {
                        const produk = this.daftarProduk.find(p => p.id === item.produk_id);
                        const promosTersedia = (item.is_bundling || item.is_promo_event)
                            ? (item.promos_tersedia || [])
                            : (item.promos_tersedia || (produk && produk.promos_aktif_semua && produk.promos_aktif_semua.length > 0 ? produk.promos_aktif_semua : (item.promo_aktif ? [item.promo_aktif] : [])));
                        const promosTerpilihIds = item.promos_terpilih_ids || promosTersedia.map(p => p.id);
                        let foto = item.foto;
                        if (item.is_bundling && !foto) {
                            const bundling = this.daftarBundling.find(b => b.id === item.bundling_id);
                            if (bundling) foto = bundling.gambar;
                        }
                        if (item.is_promo_event && !foto) {
                            const promoEvent = this.daftarPromoEvent.find(pe => pe.id === item.promo_event_id);
                            if (promoEvent && promoEvent.produk) foto = promoEvent.produk.foto;
                        }

                        return {
                            ...item,
                            foto,
                            key: item.key || (item.produk_id + '_' + (item.butuh_produksi ? 'produksi' : 'ready')),
                            promos_tersedia: promosTersedia,
                            promos_terpilih_ids: promosTerpilihIds
                        };
                    });
                },

                get semuaProdukTersaring() {
                    const produkList = this.daftarProduk.filter(p => {
                        const cocokKategori = this.kategoriTerpilih === 'semua' || p.kategori_id === this.kategoriTerpilih;
                        const cocokCari = p.nama_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase()) || p.kode_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase());

                        let cocokKetersediaan = false;
                        if (this.filterKetersediaan === 'semua') {
                            cocokKetersediaan = true;
                        } else if (this.filterKetersediaan === 'promo') {
                            cocokKetersediaan = p.promo_aktif !== null;
                        } else if (this.filterKetersediaan === 'perlu_diproduksi') {
                            cocokKetersediaan = this.butuhProduksi(p);
                        } else if (this.filterKetersediaan === 'stok_ada') {
                            cocokKetersediaan = !this.butuhProduksi(p);
                        }

                        return cocokKategori && cocokCari && cocokKetersediaan;
                    }).map(p => ({ ...p, is_bundling: false }));

                    let bundlingList = [];
                    if (this.kategoriTerpilih === 'semua' && this.filterKetersediaan === 'semua') {
                        bundlingList = this.daftarBundling
                            .filter(b => {
                                return b.nama_bundling.toLowerCase().includes(this.kataKunciPencarian.toLowerCase())
                                    || (b.kode_bundling || '').toLowerCase().includes(this.kataKunciPencarian.toLowerCase());
                            })
                            .map(b => ({ ...b, is_bundling: true }));
                    }

                    let promoEventList = [];
                    if (this.kategoriTerpilih === 'semua' && this.filterKetersediaan === 'semua') {
                        promoEventList = this.daftarPromoEvent
                            .filter(pe => pe.nama_event.toLowerCase().includes(this.kataKunciPencarian.toLowerCase()))
                            .map(pe => ({ ...pe, is_bundling: false, is_promo_event: true }));
                    }

                    return [...produkList, ...bundlingList, ...promoEventList];
                },

                get semuaBundlingTersaring() {
                    return this.daftarBundling.filter(b => {
                        const cocokCari = b.nama_bundling.toLowerCase().includes(this.kataKunciPencarian.toLowerCase())
                            || (b.kode_bundling || '').toLowerCase().includes(this.kataKunciPencarian.toLowerCase());

                        let cocokFilter = true;
                        if (this.filterBundling === 'tersedia') {
                            cocokFilter = !this.bundlingPerluDiproduksi(b);
                        } else if (this.filterBundling === 'habis') {
                            cocokFilter = this.bundlingPerluDiproduksi(b);
                        }

                        return cocokCari && cocokFilter;
                    });
                },

                get semuaPromoEventTersaring() {
                    return this.daftarPromoEvent.filter(pe => {
                        const cocokCari = pe.nama_event.toLowerCase().includes(this.kataKunciPencarian.toLowerCase());

                        let cocokFilter = true;
                        if (this.filterPromoEvent === 'tersedia') {
                            cocokFilter = !this.promoEventPerluDiproduksi(pe);
                        } else if (this.filterPromoEvent === 'habis') {
                            cocokFilter = this.promoEventPerluDiproduksi(pe);
                        }

                        return cocokCari && cocokFilter;
                    });
                },

                get semuaItemModeAktif() {
                    if (this.modeTampilan === 'bundling') return this.semuaBundlingTersaring;
                    if (this.modeTampilan === 'promo_event') return this.semuaPromoEventTersaring;
                    return this.semuaProdukTersaring;
                },

                stokBundlingTersedia(bundling) {
                    if (!bundling.items || bundling.items.length === 0) return 0;
                    let min = null;
                    bundling.items.forEach(it => {
                        const p = this.daftarProduk.find(x => x.id === it.produk_id);
                        const stokP = p ? (parseFloat(p.stok) || 0) : 0;
                        const bisaDibuat = Math.floor(stokP / Math.max(1, it.qty));
                        min = (min === null) ? bisaDibuat : Math.min(min, bisaDibuat);
                    });
                    return min ?? 0;
                },

                bundlingPerluDiproduksi(bundling) {
                    if (bundling.perlu_diproduksi) return true;

                    if (!bundling.items || bundling.items.length === 0) return false;
                    return bundling.items.some(it => {
                        const p = this.daftarProduk.find(x => x.id === it.produk_id);
                        if (!p) return true;
                        return p.status_ketersediaan === 'perlu_diproduksi' || (parseFloat(p.stok) || 0) <= 0 || (parseFloat(p.stok) || 0) < it.qty;
                    });
                },

                stokPromoEventTersedia(promoEvent) {
                    const p = this.daftarProduk.find(x => x.id === promoEvent.produk_id);
                    const stokP = p ? (parseFloat(p.stok) || 0) : 0;
                    return Math.floor(stokP / Math.max(1, promoEvent.isi_event));
                },

                promoEventPerluDiproduksi(promoEvent) {
                    const p = this.daftarProduk.find(x => x.id === promoEvent.produk_id);
                    if (!p) return true;
                    return p.status_ketersediaan === 'perlu_diproduksi' || (parseFloat(p.stok) || 0) <= 0 || (parseFloat(p.stok) || 0) < promoEvent.isi_event;
                },

                get totalHalamanProduk() {
                    if (this.itemsPerPage === 'semua') return 1;
                    const perPage = parseInt(this.itemsPerPage) || 10;
                    return Math.max(1, Math.ceil(this.semuaItemModeAktif.length / perPage));
                },

                get produkTersaring() {
                    const list = this.semuaItemModeAktif;
                    if (this.itemsPerPage === 'semua') return list;
                    const perPage = parseInt(this.itemsPerPage) || 10;
                    const start = (this.halamanProduk - 1) * perPage;
                    return list.slice(start, start + perPage);
                },

                get produkDitampilkan() {
                    return this.produkTersaring;
                },

                get activePromoInfo() {
                    if (this.keranjang.length === 0) {
                        return { diskonNominal: 0, diskonPersen: 0, namaNominal: '', namaPersen: '', persenEfektif: 0, diskon: 0, promoCount: 0 };
                    }

                    let totalDiskonNominal = 0;
                    let totalDiskonPersen = 0;
                    let basisPersen = 0;
                    const namaNominalSet = new Set();
                    const namaPersenSet = new Set();
                    const produkSudahDihitung = new Set();

                    this.keranjang.forEach(item => {
                        if (produkSudahDihitung.has(item.produk_id)) return;
                        produkSudahDihitung.add(item.produk_id);

                        if (!item.promos_tersedia || item.promos_tersedia.length === 0 || !item.promos_terpilih_ids || item.promos_terpilih_ids.length === 0) {
                            return;
                        }

                        const subtotalProduk = this.subtotalProduk(item.produk_id);
                        let diskonProdukNominal = 0;
                        let diskonProdukPersen = 0;
                        let persenDipakaiProduk = false;

                        item.promos_tersedia.forEach(promo => {
                            if (!item.promos_terpilih_ids.includes(promo.id)) return;

                            const minPembelian = parseFloat(promo.min_pembelian || 0);
                            const nominalPotongan = parseFloat(promo.nominal_potongan || 0);
                            let syaratTerpenuhi;
                            if (minPembelian > 0) {
                                syaratTerpenuhi = this.subtotalGross >= minPembelian || subtotalProduk >= minPembelian;
                            } else if (promo.jenis_potongan === 'nominal') {
                                syaratTerpenuhi = subtotalProduk >= nominalPotongan || this.subtotalGross >= nominalPotongan;
                            } else {
                                syaratTerpenuhi = true;
                            }

                            if (!syaratTerpenuhi) return;

                            if (promo.jenis_potongan === 'nominal') {
                                diskonProdukNominal += nominalPotongan;
                                namaNominalSet.add(promo.nama_promo);
                            } else {
                                const persentase = parseFloat(promo.persentase || 0);
                                diskonProdukPersen += (persentase / 100) * subtotalProduk;
                                namaPersenSet.add(promo.nama_promo);
                                persenDipakaiProduk = true;
                            }
                        });

                        if (diskonProdukNominal + diskonProdukPersen > subtotalProduk) {
                            if (diskonProdukNominal >= subtotalProduk) {
                                diskonProdukNominal = subtotalProduk;
                                diskonProdukPersen = 0;
                            } else {
                                diskonProdukPersen = subtotalProduk - diskonProdukNominal;
                            }
                        }

                        totalDiskonNominal += diskonProdukNominal;
                        totalDiskonPersen += diskonProdukPersen;
                        if (persenDipakaiProduk) basisPersen += subtotalProduk;
                    });

                    const persenEfektif = basisPersen > 0 ? (totalDiskonPersen / basisPersen) * 100 : 0;

                    return {
                        diskonNominal: Math.round(totalDiskonNominal),
                        diskonPersen: Math.round(totalDiskonPersen),
                        namaNominal: Array.from(namaNominalSet).join(', '),
                        namaPersen: Array.from(namaPersenSet).join(', '),
                        persenEfektif: persenEfektif,
                        diskon: Math.round(totalDiskonNominal + totalDiskonPersen),
                        promoCount: namaNominalSet.size + namaPersenSet.size
                    };
                },

                bukaModalPilihPromo(item) {
                    this.itemPromoTerpilih = item;
                    this.modalPilihPromoTerbuka = true;
                },
                togglePromoItem(item, promoId) {
                    if (!item || !item.promos_terpilih_ids) return;
                    const idx = item.promos_terpilih_ids.indexOf(promoId);
                    const terpilihBaru = idx > -1
                        ? item.promos_terpilih_ids.filter(id => id !== promoId)
                        : [...item.promos_terpilih_ids, promoId];
                    this.keranjang
                        .filter(i => i.produk_id === item.produk_id)
                        .forEach(i => { i.promos_terpilih_ids = [...terpilihBaru]; });
                },
                pilihSemuaPromoItem(item, status) {
                    if (!item) return;
                    const terpilihBaru = status ? (item.promos_tersedia || []).map(p => p.id) : [];
                    this.keranjang
                        .filter(i => i.produk_id === item.produk_id)
                        .forEach(i => { i.promos_terpilih_ids = [...terpilihBaru]; });
                },
                hitungDiskonItem(item) {
                    if (!item || !item.promos_tersedia || !item.promos_terpilih_ids) return 0;
                    const subtotalProduk = this.subtotalProduk(item.produk_id);
                    let diskonNominal = 0;
                    let diskonPersen = 0;
                    item.promos_tersedia.forEach(promo => {
                        if (item.promos_terpilih_ids.includes(promo.id)) {
                            const minPembelian = parseFloat(promo.min_pembelian || 0);
                            const nominalPotongan = parseFloat(promo.nominal_potongan || 0);

                            let syaratTerpenuhi;
                            if (minPembelian > 0) {
                                syaratTerpenuhi = subtotalProduk >= minPembelian || this.subtotalGross >= minPembelian;
                            } else if (promo.jenis_potongan === 'nominal') {
                                syaratTerpenuhi = subtotalProduk >= nominalPotongan || this.subtotalGross >= nominalPotongan;
                            } else {
                                syaratTerpenuhi = true;
                            }

                            if (syaratTerpenuhi) {
                                if (promo.jenis_potongan === 'nominal') {
                                    diskonNominal += nominalPotongan;
                                } else {
                                    diskonPersen += (parseFloat(promo.persentase || 0) / 100) * subtotalProduk;
                                }
                            }
                        }
                    });
                    const nominalFinal = Math.min(diskonNominal, subtotalProduk);
                    const persenFinal = Math.min(diskonPersen, Math.max(0, subtotalProduk - nominalFinal));
                    return Math.round(nominalFinal + persenFinal);
                },
                subtotalProduk(produkId) {
                    return this.keranjang
                        .filter(i => i.produk_id === produkId)
                        .reduce((s, i) => s + (i.harga_jual_asli * i.jumlah), 0);
                },

                get subtotalGross() {
                    return this.keranjang.reduce((s, i) => s + (i.harga_jual_asli * i.jumlah), 0);
                },

                get subtotal() {
                    return this.subtotalGross;
                },

                get diskonPromo() {
                    return this.activePromoInfo.diskon;
                },

                get nilaiDiskon() {
                    const diskonManual = Math.round(this.subtotalGross * ((this.parsePersen(this.diskonPersen) || 0) / 100));
                    return this.diskonPromo + diskonManual + this.voucherDiskon;
                },
                get voucherSubtotalBerlaku() {
                    if (!this.voucherTerpilih) return 0;

                    const produkIds = this.voucherTerpilih.produk_ids || [];
                    if (produkIds.length === 0) return this.subtotalGross;

                    return this.keranjang
                        .filter(i => produkIds.includes(i.produk_id))
                        .reduce((s, i) => s + (i.harga_jual_asli * i.jumlah), 0);
                },

                get voucherDiskon() {
                    if (!this.voucherTerpilih) return 0;

                    const subtotalBerlaku = this.voucherSubtotalBerlaku;
                    if (subtotalBerlaku <= 0) return 0;

                    const minPembelian = parseFloat(this.voucherTerpilih.min_pembelian || 0);
                    if (this.subtotalGross < minPembelian) return 0;

                    let nilai = 0;
                    if (this.voucherTerpilih.jenis_potongan === 'nominal') {
                        nilai = parseFloat(this.voucherTerpilih.nominal_potongan || 0);
                    } else {
                        nilai = (parseFloat(this.voucherTerpilih.persentase || 0) / 100) * subtotalBerlaku;
                        const maksimal = parseFloat(this.voucherTerpilih.maksimal_potongan || 0);
                        if (maksimal > 0) nilai = Math.min(nilai, maksimal);
                    }

                    const diskonManual = Math.round(this.subtotalGross * ((this.parsePersen(this.diskonPersen) || 0) / 100));
                    const sisaKuota = Math.max(0, this.subtotalGross - this.diskonPromo - diskonManual);

                    return Math.round(Math.min(nilai, subtotalBerlaku, sisaKuota));
                },
                get rincianDiskonList() {
                    if (this.keranjang.length === 0) return [];

                    const promoMap = new Map();
                    const produkSudahDihitung = new Set();

                    this.keranjang.forEach(item => {
                        if (produkSudahDihitung.has(item.produk_id)) return;
                        produkSudahDihitung.add(item.produk_id);

                        if (!item.promos_tersedia || item.promos_tersedia.length === 0 || !item.promos_terpilih_ids || item.promos_terpilih_ids.length === 0) {
                            return;
                        }

                        const subtotalProduk = this.subtotalProduk(item.produk_id);
                        const promoDiscounts = [];
                        let totalNominal = 0;
                        let totalPersen = 0;

                        item.promos_tersedia.forEach(promo => {
                            if (!item.promos_terpilih_ids.includes(promo.id)) return;

                            const minPembelian = parseFloat(promo.min_pembelian || 0);
                            const nominalPotongan = parseFloat(promo.nominal_potongan || 0);
                            let syaratTerpenuhi;
                            if (minPembelian > 0) {
                                syaratTerpenuhi = subtotalProduk >= minPembelian || this.subtotalGross >= minPembelian;
                            } else if (promo.jenis_potongan === 'nominal') {
                                syaratTerpenuhi = subtotalProduk >= nominalPotongan || this.subtotalGross >= nominalPotongan;
                            } else {
                                syaratTerpenuhi = true;
                            }

                            if (!syaratTerpenuhi) return;

                            let hitung = 0;
                            if (promo.jenis_potongan === 'nominal') {
                                hitung = nominalPotongan;
                                totalNominal += hitung;
                            } else {
                                hitung = (parseFloat(promo.persentase || 0) / 100) * subtotalProduk;
                                totalPersen += hitung;
                            }

                            promoDiscounts.push({ promo, hitung });
                        });

                        const jatahNominal = Math.min(totalNominal, subtotalProduk);
                        const faktorNominal = totalNominal > 0 ? jatahNominal / totalNominal : 1;
                        const sisaJatahPersen = Math.max(0, subtotalProduk - jatahNominal);
                        const faktorPersen = (totalPersen > sisaJatahPersen && totalPersen > 0) ? sisaJatahPersen / totalPersen : 1;

                        promoDiscounts.forEach(({ promo, hitung }) => {
                            const faktor = promo.jenis_potongan === 'nominal' ? faktorNominal : faktorPersen;
                            const diskonFinal = hitung * faktor;
                            if (!promoMap.has(promo.id)) {
                                promoMap.set(promo.id, {
                                    id: promo.id,
                                    nama: promo.nama_promo,
                                    jenis: promo.jenis_potongan,
                                    persentase: promo.persentase,
                                    nilai: 0
                                });
                            }
                            promoMap.get(promo.id).nilai += diskonFinal;
                        });
                    });

                    const result = [];
                    promoMap.forEach(promoData => {
                        const roundedNilai = Math.round(promoData.nilai);
                        if (roundedNilai > 0) {
                            let labelNama = promoData.nama;
                            if (promoData.jenis === 'persentase' && promoData.persentase) {
                                labelNama += ' (' + this.formatPersen(promoData.persentase) + '%)';
                            }
                            result.push({
                                nama: 'Diskon (' + labelNama + ')',
                                nilai: roundedNilai
                            });
                        }
                    });

                    const manualPersen = this.parsePersen(this.diskonPersen);
                    if (manualPersen > 0) {
                        const nilaiManual = Math.round(this.subtotalGross * (manualPersen / 100));
                        if (nilaiManual > 0) {
                            result.push({
                                nama: 'Diskon Tambahan (' + this.formatPersen(manualPersen) + '%)',
                                nilai: nilaiManual
                            });
                        }
                    }

                    if (this.voucherTerpilih && this.voucherDiskon > 0) {
                        result.push({
                            nama: 'Voucher (' + this.voucherTerpilih.kode_redeem + ')',
                            nilai: this.voucherDiskon
                        });
                    }

                    return result;
                },
                get diskonLabel() {
                    return JSON.stringify(this.rincianDiskonList);
                },

                get pajak() {
                    if (!this.pajakAktif) return 0;
                    const base = this.subtotal - this.nilaiDiskon;
                    return Math.max(0, Math.round(base * (this.parsePersen(this.pajakPersen) / 100)));
                },
                get totalTagihan() {
                    return Math.max(0, this.subtotal - this.nilaiDiskon + this.pajak);
                },
                get jumlahDiterimaNum() {
                    if (this.metodePembayaran === 'QRIS') {
                        return this.totalTagihan;
                    }
                    return parseFloat(this.jumlahDiterima) || 0;
                },
                get kembalianNum() {
                    if (this.metodePembayaran === 'QRIS') {
                        return 0;
                    }
                    return this.jumlahDiterimaNum - this.totalTagihan;
                },
                get totalDitahan() { return this.daftarDitahan.length; },
                get jumlahDiterimaTampilan() {
                    if (!this.jumlahDiterima) return '';
                    return new Intl.NumberFormat('id-ID').format(this.jumlahDiterimaNum);
                },

                bukaModalPilihUkuran(produk) {
                    this.produkUkuranTerpilih = produk;
                    this.modalPilihUkuranTerbuka = true;
                },
                pilihUkuran(ukuran) {
                    if (this.produkUkuranTerpilih) {
                        const p = this.produkUkuranTerpilih;
                        this.modalPilihUkuranTerbuka = false;
                        this.produkUkuranTerpilih = null;
                        this.tambahKeKeranjang(p, ukuran);
                    }
                },
                tambahKeKeranjang(produk, ukuran = null) {
                    const isPunyaUkuran = !produk.is_bundling && (produk.punya_ukuran == 1 || produk.punya_ukuran === true);
                    if (isPunyaUkuran && !ukuran) {
                        this.bukaModalPilihUkuran(produk);
                        return;
                    }

                    const manualProduksi = produk.status_ketersediaan === 'perlu_diproduksi';

                    if (manualProduksi) {
                        const item = this.keranjang.find(i => i.produk_id === produk.id && (i.ukuran || null) === (ukuran || null) && i.butuh_produksi);
                        if (item) {
                            item.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemKeranjang(produk, 1, true, ukuran));
                        }
                        this.tambahToast('Produk ditambahkan (Perlu Diproduksi).', 'sukses');
                        return;
                    }

                    const totalReadyDipakai = this.keranjang
                        .filter(i => i.produk_id === produk.id && (i.ukuran || null) === (ukuran || null) && !i.butuh_produksi)
                        .reduce((sum, i) => sum + i.jumlah, 0);
                    const stokTersedia = this.stokUntukUkuran(produk, ukuran);

                    if (totalReadyDipakai < stokTersedia) {
                        const readyItem = this.keranjang.find(i => i.produk_id === produk.id && (i.ukuran || null) === (ukuran || null) && !i.butuh_produksi);
                        if (readyItem) {
                            readyItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemKeranjang(produk, 1, false, ukuran));
                        }
                        this.tambahToast('Produk ditambahkan.', 'sukses');
                    } else {
                        const produksiItem = this.keranjang.find(i => i.produk_id === produk.id && (i.ukuran || null) === (ukuran || null) && i.butuh_produksi);
                        if (produksiItem) {
                            produksiItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemKeranjang(produk, 1, true, ukuran));
                        }
                        this.tambahToast('Stok ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                    }
                },

                buatItemBundlingKeranjang(bundling, jumlah, perluProduksi) {
                    return {
                        key: 'bundling_' + bundling.id + '_' + (perluProduksi ? 'produksi' : 'ready'),
                        bundling_id: bundling.id,
                        is_bundling: true,
                        nama_produk: bundling.nama_bundling,
                        harga_jual: parseFloat(bundling.harga),
                        harga_jual_asli: parseFloat(bundling.harga),
                        foto: bundling.gambar,
                        items: (bundling.items || []).map(i => {
                            const p = this.daftarProduk.find(x => x.id === i.produk_id);
                            const stokP = p ? (parseFloat(p.stok) || 0) : 0;
                            const subButuh = p ? (p.status_ketersediaan === 'perlu_diproduksi' || stokP <= 0 || stokP < i.qty) : true;
                            return { produk_id: i.produk_id, qty: i.qty, butuh_produksi: subButuh };
                        }),
                        jumlah: jumlah,
                        butuh_produksi: perluProduksi,
                        catatan: ''
                    };
                },

                tambahBundlingKeKeranjang(bundling) {
                    const manualProduksi = this.bundlingPerluDiproduksi(bundling);

                    if (manualProduksi) {
                        const item = this.keranjang.find(i => i.is_bundling && i.bundling_id === bundling.id && i.butuh_produksi);
                        if (item) {
                            item.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemBundlingKeranjang(bundling, 1, true));
                        }
                        this.tambahToast('Bundling ditambahkan (Perlu Diproduksi).', 'sukses');
                        return;
                    }

                    const totalReadyDipakai = this.keranjang
                        .filter(i => i.is_bundling && i.bundling_id === bundling.id && !i.butuh_produksi)
                        .reduce((sum, i) => sum + i.jumlah, 0);
                    const stokTersedia = this.stokBundlingTersedia(bundling);

                    if (totalReadyDipakai < stokTersedia) {
                        const readyItem = this.keranjang.find(i => i.is_bundling && i.bundling_id === bundling.id && !i.butuh_produksi);
                        if (readyItem) {
                            readyItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemBundlingKeranjang(bundling, 1, false));
                        }
                        this.tambahToast('Bundling ditambahkan.', 'sukses');
                    } else {
                        const produksiItem = this.keranjang.find(i => i.is_bundling && i.bundling_id === bundling.id && i.butuh_produksi);
                        if (produksiItem) {
                            produksiItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemBundlingKeranjang(bundling, 1, true));
                        }
                        this.tambahToast('Stok bundling ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                    }
                },

                buatItemPromoEventKeranjang(promoEvent, jumlah, perluProduksi) {
                    return {
                        key: 'promo_event_' + promoEvent.id + '_' + (perluProduksi ? 'produksi' : 'ready'),
                        promo_event_id: promoEvent.id,
                        produk_id: promoEvent.produk_id,
                        isi_event: promoEvent.isi_event,
                        is_promo_event: true,
                        nama_produk: promoEvent.nama_event,
                        harga_jual: parseFloat(promoEvent.harga_event),
                        harga_jual_asli: parseFloat(promoEvent.harga_event),
                        foto: promoEvent.produk ? promoEvent.produk.foto : null,
                        jumlah: jumlah,
                        butuh_produksi: perluProduksi,
                        catatan: ''
                    };
                },

                tambahPromoEventKeKeranjang(promoEvent) {
                    const manualProduksi = this.promoEventPerluDiproduksi(promoEvent);

                    if (manualProduksi) {
                        const item = this.keranjang.find(i => i.is_promo_event && i.promo_event_id === promoEvent.id && i.butuh_produksi);
                        if (item) {
                            item.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemPromoEventKeranjang(promoEvent, 1, true));
                        }
                        this.tambahToast('Promo event ditambahkan (Perlu Diproduksi).', 'sukses');
                        return;
                    }

                    const totalReadyDipakai = this.keranjang
                        .filter(i => i.is_promo_event && i.promo_event_id === promoEvent.id && !i.butuh_produksi)
                        .reduce((sum, i) => sum + i.jumlah, 0);
                    const stokTersedia = this.stokPromoEventTersedia(promoEvent);

                    if (totalReadyDipakai < stokTersedia) {
                        const readyItem = this.keranjang.find(i => i.is_promo_event && i.promo_event_id === promoEvent.id && !i.butuh_produksi);
                        if (readyItem) {
                            readyItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemPromoEventKeranjang(promoEvent, 1, false));
                        }
                        this.tambahToast('Promo event ditambahkan.', 'sukses');
                    } else {
                        const produksiItem = this.keranjang.find(i => i.is_promo_event && i.promo_event_id === promoEvent.id && i.butuh_produksi);
                        if (produksiItem) {
                            produksiItem.jumlah++;
                        } else {
                            this.keranjang.push(this.buatItemPromoEventKeranjang(promoEvent, 1, true));
                        }
                        this.tambahToast('Stok promo event ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                    }
                },

                tambahJumlah(item) {
                    if (item.is_bundling) {
                        if (item.butuh_produksi) {
                            item.jumlah++;
                            return;
                        }

                        const bundling = this.daftarBundling.find(b => b.id === item.bundling_id);
                        if (!bundling) { item.jumlah++; return; }

                        const stokTersedia = this.stokBundlingTersedia(bundling);
                        const totalReadyDipakai = this.keranjang
                            .filter(i => i.is_bundling && i.bundling_id === item.bundling_id && !i.butuh_produksi)
                            .reduce((sum, i) => sum + i.jumlah, 0);

                        if (totalReadyDipakai < stokTersedia) {
                            item.jumlah++;
                        } else {
                            const produksiItem = this.keranjang.find(i => i.is_bundling && i.bundling_id === item.bundling_id && i.butuh_produksi);
                            if (produksiItem) {
                                produksiItem.jumlah++;
                            } else {
                                this.keranjang.push(this.buatItemBundlingKeranjang(bundling, 1, true));
                            }
                            this.tambahToast('Stok bundling ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                        }
                        return;
                    }

                    if (item.is_promo_event) {
                        if (item.butuh_produksi) {
                            item.jumlah++;
                            return;
                        }

                        const promoEvent = this.daftarPromoEvent.find(pe => pe.id === item.promo_event_id);
                        if (!promoEvent) { item.jumlah++; return; }

                        const stokTersedia = this.stokPromoEventTersedia(promoEvent);
                        const totalReadyDipakai = this.keranjang
                            .filter(i => i.is_promo_event && i.promo_event_id === item.promo_event_id && !i.butuh_produksi)
                            .reduce((sum, i) => sum + i.jumlah, 0);

                        if (totalReadyDipakai < stokTersedia) {
                            item.jumlah++;
                        } else {
                            const produksiItem = this.keranjang.find(i => i.is_promo_event && i.promo_event_id === item.promo_event_id && i.butuh_produksi);
                            if (produksiItem) {
                                produksiItem.jumlah++;
                            } else {
                                this.keranjang.push(this.buatItemPromoEventKeranjang(promoEvent, 1, true));
                            }
                            this.tambahToast('Stok promo event ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                        }
                        return;
                    }

                    if (item.butuh_produksi) {
                        item.jumlah++;
                        return;
                    }

                    const produk = this.daftarProduk.find(p => p.id === item.produk_id);
                    const stokTersedia = this.stokUntukUkuran(produk, item.ukuran);
                    const totalReadyDipakai = this.keranjang
                        .filter(i => i.produk_id === item.produk_id && (i.ukuran || null) === (item.ukuran || null) && !i.butuh_produksi)
                        .reduce((sum, i) => sum + i.jumlah, 0);

                    if (totalReadyDipakai < stokTersedia) {
                        item.jumlah++;
                    } else {
                        const produksiItem = this.keranjang.find(i => i.produk_id === item.produk_id && (i.ukuran || null) === (item.ukuran || null) && i.butuh_produksi);
                        if (produksiItem) {
                            produksiItem.jumlah++;
                        } else if (produk) {
                            this.keranjang.push(this.buatItemKeranjang(produk, 1, true, item.ukuran));
                        }
                        this.tambahToast('Stok ready habis, kelebihan masuk ke Perlu Diproduksi.', 'info');
                    }
                },
                kurangiJumlah(item) { if (item.jumlah > 1) item.jumlah--; else this.hapusItem(item); },

                hapusItem(item) { this.keranjang = this.keranjang.filter(i => i.key !== item.key); this.tambahToast('Item dihapus.', 'info'); },

                // Pembeli
                bukaModalPembeli() {
                    this.inputNamaPembeli = this.namaPembeli;
                    this.modalPembeliTerbuka = true;
                },
                simpanPembeli() {
                    if (this.inputNamaPembeli.trim() === '') { this.tambahToast('Nama pembeli tidak boleh kosong!', 'error'); return; }
                    this.namaPembeli = this.inputNamaPembeli.trim();
                    this.modalPembeliTerbuka = false;
                    this.tambahToast('Nama pembeli disimpan.', 'sukses');
                },
                pilihMember() {
                    if (!this.memberIdTerpilih) { this.memberTerpilih = null; return; }
                    this.memberTerpilih = this.daftarMember.find(m => m.id == this.memberIdTerpilih);
                    if (this.memberTerpilih && this.namaPembeli.trim() === '') {
                        this.namaPembeli = this.memberTerpilih.nama;
                    }
                },

                // Input Pembayaran
                handleInputJumlahDiterima(e) {
                    const rawDigits = e.target.value.replace(/\D/g, '').replace(/^0+/, '');
                    this.jumlahDiterima = rawDigits;
                    e.target.value = this.jumlahDiterimaTampilan;
                },
                setJumlahDiterimaCepat(n) {
                    this.jumlahDiterima = Math.round(n).toString();
                    this.$nextTick(() => { if (this.$refs.inputJumlahDiterima) this.$refs.inputJumlahDiterima.focus(); });
                },
                tambahNominalCepat(n) {
                    this.jumlahDiterima = ((parseFloat(this.jumlahDiterima) || 0) + n).toString();
                    this.$nextTick(() => { if (this.$refs.inputJumlahDiterima) this.$refs.inputJumlahDiterima.focus(); });
                },

                // Ke pembayaran
                lanjutKePembayaran() {
                    if (this.keranjang.length === 0) { this.tambahToast('Keranjang masih kosong!', 'error'); return; }
                    if (this.namaPembeli.trim() === '') { this.tambahToast('Nama pembeli harus diisi terlebih dahulu!', 'error'); this.bukaModalPembeli(); return; }
                    this.jumlahDiterima = '';
                    this.halamanAktif = 'pembayaran';
                    this.$nextTick(() => { if (this.$refs.inputJumlahDiterima) this.$refs.inputJumlahDiterima.focus(); });
                },

                // Hold Order
                tahanOrderan() {
                    if (this.keranjang.length === 0) return;
                    if (this.namaPembeli.trim() === '') { this.tambahToast('Nama pembeli harus diisi terlebih dahulu!', 'error'); this.bukaModalPembeli(); return; }

                    const dataStruk = {
                        nama_pembeli: this.namaPembeli,
                        keranjang: JSON.parse(JSON.stringify(this.keranjang)),
                        subtotal: this.subtotal,
                        diskon_persen: this.diskonPersen || 0,
                        nilai_diskon: this.nilaiDiskon,
                        pajak_persen: this.pajakPersen,
                        diskon_label: this.diskonLabel,
                        pajak: this.pajak,
                        total_bayar: this.totalTagihan,
                        jumlah_diterima: 0,
                        kembalian: 0,
                        metode_pembayaran: 'Belum Bayar',
                        status_pembayaran: 'Belum Lunas',
                        catatan: this.catatan || '',
                        tanggal: new Date().toLocaleString('id-ID')
                    };

                    fetch("{{ route('transaksi.tahan') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({
                            nama_pembeli: this.namaPembeli,
                            member_id: this.memberIdTerpilih || null,
                            voucher_id: this.voucherTerpilih ? this.voucherTerpilih.id : null,
                            stamp_redemption_id: this.rewardStampDiKeranjang ? this.rewardStampDiKeranjang.stamp_redemption_id : null,
                            item_pesanan: this.keranjang,
                            subtotal: this.subtotal,
                            pajak: this.pajak,
                            diskon: this.nilaiDiskon,
                            diskon_persen: this.diskonPersen || 0,
                            diskon_label: this.diskonLabel,
                            total: this.totalTagihan,
                            catatan: this.catatan || null
                        })
                    })
                        .then(r => r.json())
                        .then(r => {
                            if (r.status === 'success') {
                                this.kurangiStokFrontend(this.keranjang);
                                this.tambahToast('Pesanan disimpan.', 'sukses');
                                this.suksesData = { ...dataStruk, kode_transaksi: r.kode_transaksi };
                                this.modalSuksesTerbuka = true;
                                this.bersihkanKeranjang();
                                this.ambilDaftarDitahanDariServer();
                            }
                            else this.tambahToast(r.pesan, 'error');
                        })
                        .catch(() => this.tambahToast('Gagal menahan pesanan.', 'error'));
                },

                bukaModalDaftarDitahan() { this.ambilDaftarDitahanDariServer(); this.modalDitahanTerbuka = true; },

                ambilDaftarDitahanDariServer() {
                    fetch("{{ route('transaksi.daftar-ditahan') }}").then(r => r.json()).then(r => { if (r.status === 'success') this.daftarDitahan = r.data; });
                },

                pulihkanPesanan(id) {
                    fetch(`/transaksi/pulihkan/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } })
                        .then(r => r.json())
                        .then(r => {
                            if (r.status === 'success') {
                                const d = r.data;
                                this.keranjang = this.normalisasiItemKeranjang(d.item_pesanan);
                                this.namaPembeli = d.nama_pembeli;
                                this.catatan = d.catatan || '';
                                this.diskonPersen = d.diskon_persen || '';
                                this.modalDitahanTerbuka = false;

                                const memberId = d.transaksi ? d.transaksi.member_id : null;
                                this.memberIdTerpilih = memberId || '';
                                this.memberTerpilih = memberId
                                    ? (this.daftarMember.find(m => m.id === memberId) || null)
                                    : null;

                                const voucher = d.transaksi ? d.transaksi.voucher : null;
                                this.voucherTerpilih = this._formatVoucherDariRelasi(voucher);

                                if (d.item_pesanan) {
                                    this.kembalikanStokFrontend(d.item_pesanan);
                                }

                                this.pesananSedangDilunaskan = null;
                                this.tambahToast('Pesanan dilanjutkan.', 'sukses');
                                this.ambilDaftarDitahanDariServer();
                                this.halamanAktif = 'beranda';
                            } else this.tambahToast(r.pesan, 'error');
                        });
                },

                hapusPesananDitahan(id) {
                    this.idPesananAkanDihapus = id;
                    this.modalKonfirmasiHapusTerbuka = true;
                },

                eksekusiHapusPesananDitahan() {
                    if (!this.idPesananAkanDihapus) return;
                    const id = this.idPesananAkanDihapus;
                    const pesanan = this.daftarDitahan.find(p => p.id === id);
                    fetch(`/transaksi/hapus-ditahan/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } })
                        .then(r => r.json())
                        .then(r => {
                            if (r.status === 'success') {
                                if (pesanan && pesanan.item_pesanan) {
                                    this.kembalikanStokFrontend(pesanan.item_pesanan);
                                }
                                this.tambahToast('Pesanan dihapus.', 'info');
                                this.ambilDaftarDitahanDariServer();
                            } else {
                                this.tambahToast(r.pesan, 'error');
                            }
                            this.modalKonfirmasiHapusTerbuka = false;
                            this.modalDitahanTerbuka = true;
                            this.idPesananAkanDihapus = null;
                        });
                },

                kembaliKeKasir() {
                    if (this.pesananSedangDilunaskan) {
                        this.pulihkanPesanan(this.pesananSedangDilunaskan.id);
                    } else {
                        this.halamanAktif = 'beranda';
                    }
                },

                // Pembayaran Final
                selesaikanPembayaran() {
                    if (this.kembalianNum < 0) { this.tambahToast('Uang yang diterima kurang!', 'error'); return; }

                    const dataStruk = {
                        nama_pembeli: this.namaPembeli,
                        keranjang: JSON.parse(JSON.stringify(this.keranjang)),
                        subtotal: this.subtotal,
                        diskon_persen: this.diskonPersen || 0,
                        nilai_diskon: this.nilaiDiskon,
                        pajak_persen: this.pajakPersen,
                        diskon_label: this.diskonLabel,
                        pajak: this.pajak,
                        total_bayar: this.totalTagihan,
                        jumlah_diterima: this.jumlahDiterimaNum,
                        kembalian: this.kembalianNum,
                        metode_pembayaran: this.metodePembayaran,
                        catatan: this.catatan,
                        tanggal: new Date().toLocaleString('id-ID'),
                        status_pembayaran: 'Lunas'
                    };

                    if (this.pesananSedangDilunaskan) {
                        fetch(`/transaksi/lunaskan/${this.pesananSedangDilunaskan.id}`, {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                            body: JSON.stringify({
                                jumlah_diterima: this.jumlahDiterimaNum,
                                kembalian: this.kembalianNum,
                                metode_pembayaran: this.metodePembayaran,
                                member_id: this.memberIdTerpilih || null,
                                voucher_id: this.voucherTerpilih ? this.voucherTerpilih.id : null,
                                diskon_label: this.diskonLabel
                            })
                        })
                            .then(r => r.json())
                            .then(r => {
                                if (r.status === 'success') {
                                    this.suksesData = { ...dataStruk, kode_transaksi: r.kode_transaksi };
                                    this.modalSuksesTerbuka = true;
                                    this.bersihkanKeranjang();
                                    this.pesananSedangDilunaskan = null;
                                    this.halamanAktif = 'beranda';
                                    this.ambilDaftarDitahanDariServer();
                                } else this.tambahToast(r.pesan, 'error');
                            })
                            .catch(() => this.tambahToast('Gagal melunaskan transaksi!', 'error'));
                    } else {
                        fetch("{{ route('transaksi.bayar') }}", {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                            body: JSON.stringify({
                                nama_pembeli: this.namaPembeli,
                                member_id: this.memberIdTerpilih || null,
                                voucher_id: this.voucherTerpilih ? this.voucherTerpilih.id : null,
                                stamp_redemption_id: this.rewardStampDiKeranjang ? this.rewardStampDiKeranjang.stamp_redemption_id : null,
                                item_pesanan: this.keranjang,
                                subtotal: this.subtotal,
                                pajak: this.pajak,
                                diskon: this.nilaiDiskon,
                                diskon_persen: this.diskonPersen || 0,
                                diskon_label: this.diskonLabel,
                                total_bayar: this.totalTagihan,
                                jumlah_diterima: this.jumlahDiterimaNum,
                                kembalian: this.kembalianNum,
                                metode_pembayaran: this.metodePembayaran
                            })
                        })
                            .then(r => r.json())
                            .then(r => {
                                if (r.status === 'success') {
                                    this.kurangiStokFrontend(this.keranjang);
                                    this.suksesData = { ...dataStruk, kode_transaksi: r.kode_transaksi };
                                    this.modalSuksesTerbuka = true;
                                    this.bersihkanKeranjang();
                                    this.halamanAktif = 'beranda';
                                } else this.tambahToast(r.pesan, 'error');
                            })
                            .catch(() => this.tambahToast('Gagal memproses transaksi!', 'error'));
                    }
                },

                // Cetak struk
                cetakStruk(jenis) {
                    const d = this.suksesData;
                    if (!d.kode_transaksi) {
                        this.tambahToast('Tidak ada transaksi untuk dicetak.', 'error');
                        return;
                    }

                    if (jenis === 'dapur') {
                        const itemsDapur = d.keranjang.filter(item => this.itemButuhProduksi(item));
                        if (itemsDapur.length === 0) {
                            this.tambahToast('Tidak ada item untuk dicetak ke dapur.', 'info');
                            return;
                        }
                    }

                    window.open(`/transaksi/cetak/${d.kode_transaksi}?jenis=${jenis}&print=true`, '_blank', 'width=400,height=700');
                },

                tutupModalSukses() { this.modalSuksesTerbuka = false; },

                mulaiLunaskan(pesanan) {
                    this.pesananSedangDilunaskan = pesanan;
                    this.keranjang = this.normalisasiItemKeranjang(JSON.parse(JSON.stringify(pesanan.item_pesanan)));
                    this.namaPembeli = pesanan.nama_pembeli;
                    this.diskonPersen = pesanan.diskon_persen || '';
                    this.pajakPersen = pesanan.pajak && pesanan.subtotal && (parseFloat(pesanan.subtotal) > parseFloat(pesanan.diskon || 0)) ? String(Math.round((parseFloat(pesanan.pajak) / (parseFloat(pesanan.subtotal) - parseFloat(pesanan.diskon || 0))) * 100)) : '5';
                    this.catatan = pesanan.catatan || '';
                    if (pesanan.transaksi && pesanan.transaksi.member_id) {
                        this.memberIdTerpilih = pesanan.transaksi.member_id;
                        this.memberTerpilih = this.daftarMember.find(m => m.id == this.memberIdTerpilih);
                    } else {
                        this.memberIdTerpilih = '';
                        this.memberTerpilih = null;
                    }
                    this.voucherTerpilih = this._formatVoucherDariRelasi(pesanan.transaksi ? pesanan.transaksi.voucher : null);
                    this.modalDitahanTerbuka = false;
                    this.jumlahDiterima = '';
                    this.halamanAktif = 'pembayaran';
                    this.$nextTick(() => { if (this.$refs.inputJumlahDiterima) this.$refs.inputJumlahDiterima.focus(); });
                },

                cetakStrukDitahan(pesanan, jenis = 'dapur') {
                    this.suksesData = {
                        nama_pembeli: pesanan.nama_pembeli,
                        keranjang: JSON.parse(JSON.stringify(pesanan.item_pesanan)),
                        subtotal: pesanan.subtotal,
                        diskon_persen: pesanan.diskon_persen || 0,
                        nilai_diskon: pesanan.diskon || 0,
                        pajak_persen: pesanan.pajak && pesanan.subtotal && (parseFloat(pesanan.subtotal) > parseFloat(pesanan.diskon || 0)) ? Math.round((parseFloat(pesanan.pajak) / (parseFloat(pesanan.subtotal) - parseFloat(pesanan.diskon || 0))) * 100) : 5,
                        pajak: pesanan.pajak,
                        total_bayar: pesanan.total,
                        jumlah_diterima: 0,
                        kembalian: 0,
                        metode_pembayaran: 'Belum Bayar',
                        status_pembayaran: 'Belum Lunas',
                        catatan: pesanan.catatan || '',
                        tanggal: new Date(pesanan.created_at).toLocaleString('id-ID'),
                        kode_transaksi: pesanan.transaksi ? pesanan.transaksi.kode_transaksi : ''
                    };
                    this.cetakStruk(jenis);
                },

                bersihkanKeranjang() {
                    this.keranjang = [];
                    this.namaPembeli = '';
                    this.catatan = '';
                    this.diskonPersen = '';
                    this.pajakPersen = this.pajakPersenDefault;
                    this.jumlahDiterima = '';
                    this.memberIdTerpilih = '';
                    this.memberTerpilih = null;
                    this.voucherTerpilih = null;
                    this.daftarRewardStampAktif = [];
                },

                // Tambah stok lokal (Besar/Kecil sesuai ukuran) + kolom stok
                // gabungan, supaya tampilan kartu produk tetap sinkron tanpa
                // perlu reload data dari server.
                _sesuaikanStokLokal(produk, ukuran, delta) {
                    if (!produk) return;
                    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
                    if (punyaUkuran) {
                        if (ukuran === 'Besar') {
                            produk.stok_besar = (parseFloat(produk.stok_besar) || 0) + delta;
                        } else if (ukuran === 'Kecil') {
                            produk.stok_kecil = (parseFloat(produk.stok_kecil) || 0) + delta;
                        }
                    }
                    produk.stok = (parseFloat(produk.stok) || 0) + delta;
                },

                kembalikanStokFrontend(itemPesanan) {
                    (itemPesanan || []).forEach(item => {
                        if (item.is_bundling) {
                            (item.items || []).forEach(komponen => {
                                if (!komponen.butuh_produksi) {
                                    const p = this.daftarProduk.find(x => x.id === komponen.produk_id);
                                    if (p) p.stok = parseFloat(p.stok) + (parseFloat(komponen.qty) * parseFloat(item.jumlah));
                                }
                            });
                            return;
                        }
                        if (item.is_promo_event) {
                            if (!item.butuh_produksi) {
                                const p = this.daftarProduk.find(x => x.id === item.produk_id);
                                if (p) p.stok = parseFloat(p.stok) + (parseFloat(item.isi_event) * parseFloat(item.jumlah));
                            }
                            return;
                        }
                        const p = this.daftarProduk.find(x => x.id === item.produk_id);
                        if (p && !this.itemButuhProduksi(item)) {
                            this._sesuaikanStokLokal(p, item.ukuran, parseFloat(item.jumlah));
                        }
                    });
                },
                kurangiStokFrontend(itemPesanan) {
                    (itemPesanan || []).forEach(item => {
                        if (item.is_bundling) {
                            (item.items || []).forEach(komponen => {
                                if (!komponen.butuh_produksi) {
                                    const p = this.daftarProduk.find(x => x.id === komponen.produk_id);
                                    if (p) p.stok = parseFloat(p.stok) - (parseFloat(komponen.qty) * parseFloat(item.jumlah));
                                }
                            });
                            return;
                        }
                        if (item.is_promo_event) {
                            if (!item.butuh_produksi) {
                                const p = this.daftarProduk.find(x => x.id === item.produk_id);
                                if (p) p.stok = parseFloat(p.stok) - (parseFloat(item.isi_event) * parseFloat(item.jumlah));
                            }
                            return;
                        }
                        const p = this.daftarProduk.find(x => x.id === item.produk_id);
                        if (p && !this.itemButuhProduksi(item)) {
                            this._sesuaikanStokLokal(p, item.ukuran, -parseFloat(item.jumlah));
                        }
                    });
                },

                formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); },

                formatPersen(v) {
                    const n = parseFloat(v) || 0;
                    const dibulatkan = Math.round(n * 100) / 100; 
                    return dibulatkan.toString().replace('.', ',');
                },

                formatTanggal(d) { return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }); },
                tambahToast(pesan, tipe = 'info') { const id = this.toastIdCounter++; this.toasts.push({ id, pesan, tipe }); setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000); }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }

        @keyframes slide-in {
            from {
                transform: translateY(1rem);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-slide-in {
            animation: slide-in 0.2s ease-out forwards;
        }

        .text-xxs {
            font-size: 0.65rem;
        }
    </style>
@endpush