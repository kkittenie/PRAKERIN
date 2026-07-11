@extends('layouts.app')

@section('content')
<div x-data="aplikasiKasir()" x-init="inisialisasi()" class="bg-gray-50 dark:bg-gray-950 min-h-screen lg:h-[calc(100vh-64px)] text-gray-800 dark:text-gray-200 -m-4 md:-m-6 p-4 md:p-6 lg:overflow-hidden flex flex-col" x-cloak>
    
    <!-- Toast Notifikasi -->
    <div class="fixed top-5 right-5 z-99999 flex flex-col gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div :class="{
                    'bg-green-600': toast.tipe === 'sukses',
                    'bg-red-600': toast.tipe === 'error',
                    'bg-blue-600': toast.tipe === 'info'
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
        <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-xs border border-gray-100 dark:border-gray-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 text-white p-2.5 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Kasir POS - Transaksi</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Aplikasi Kasir Toko Pastry Modern</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" x-model="kataKunciPencarian" placeholder="Cari nama atau kode produk..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm bg-gray-50 dark:bg-gray-800 focus:bg-white dark:focus:bg-gray-900 dark:text-white transition-all">
                </div>
                <button @click="bukaModalDaftarDitahan()" class="relative flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 font-semibold rounded-lg border border-indigo-100 dark:border-indigo-500/25 transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Daftar Ditahan</span>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xxs font-bold px-2 py-0.5 rounded-full" x-show="totalDitahan > 0" x-text="totalDitahan"></span>
                </button>
            </div>
        </div>

        <!-- Layout Utama -->
        <div class="grid grid-cols-12 gap-4 items-stretch flex-1 min-h-0 lg:overflow-hidden">
            
            <!-- Kiri: Produk -->
            <div class="col-span-12 lg:col-span-8 flex flex-col gap-4 h-full lg:overflow-hidden min-h-0">
                <div class="flex flex-col gap-4 shrink-0">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                            <button @click="kategoriTerpilih = 'semua'" :class="kategoriTerpilih === 'semua' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'" class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap">Semua Produk</button>
                            <template x-for="kategori in daftarKategori" :key="kategori.id">
                                <button @click="kategoriTerpilih = kategori.id" :class="kategoriTerpilih === kategori.id ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'" class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap" x-text="kategori.nama_kategori"></button>
                            </template>
                        </div>

                        <!-- Filter Status Ketersediaan -->
                        <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-lg border border-gray-100 dark:border-gray-800 shrink-0">
                            <button @click="filterKetersediaan = 'semua'" :class="filterKetersediaan === 'semua' ? 'bg-slate-900 dark:bg-gray-800 text-white shadow-xs' : 'bg-transparent text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" class="px-3 py-1.5 rounded-md text-xs font-bold transition">Semua</button>
                            <button @click="filterKetersediaan = 'stok_ada'" :class="filterKetersediaan === 'stok_ada' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" class="px-3 py-1.5 rounded-md text-xs font-bold transition">Ready Stock</button>
                            <button @click="filterKetersediaan = 'pesan_dulu'" :class="filterKetersediaan === 'pesan_dulu' ? 'bg-amber-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" class="px-3 py-1.5 rounded-md text-xs font-bold transition">Pre-Order</button>
                        </div>
                    </div>
                </div>
                
                <!-- Scrollable grid wrapper -->
                <div class="flex-1 lg:overflow-y-auto pr-1 min-h-0 custom-scrollbar">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                        <template x-for="produk in produkTersaring" :key="produk.id">
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                <div x-show="produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= 0" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-10 flex flex-col items-center justify-center p-2 text-center">
                                    <span class="bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 font-bold px-3 py-1 rounded-full text-xs">Habis</span>
                                </div>
                                <div class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden">
                                    <img :src="produk.foto ? '/' + produk.foto : '/images/logo/logo-icon.svg'" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Produk">
                                    <div class="absolute top-2 left-2 z-5">
                                        <template x-if="produk.status_ketersediaan === 'stok_ada'">
                                            <span class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                        </template>
                                        <template x-if="produk.status_ketersediaan === 'pesan_dulu'">
                                            <span class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Pre-Order</span>
                                        </template>
                                    </div>
                                    <div class="absolute bottom-2 right-2 bg-slate-900/75 dark:bg-gray-950/75 text-white text-xs font-semibold px-2 py-0.5 rounded">
                                        <span x-text="produk.status_ketersediaan === 'pesan_dulu' ? 'Pre-Order' : 'Stok: ' + produk.stok"></span>
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                    <div>
                                        <p class="text-xxs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider" x-text="produk.kategori?.nama_kategori || 'Pastry'"></p>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2" x-text="produk.nama_produk"></h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500" x-text="produk.kode_produk"></p>
                                    </div>
                                    <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-50 dark:border-gray-800">
                                        <span class="font-bold text-indigo-700 dark:text-indigo-400 text-sm" x-text="formatRupiah(produk.harga_jual)"></span>
                                        <button @click="tambahKeKeranjang(produk)" :disabled="produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= 0" class="p-2 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-600 dark:hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white rounded-lg transition disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Kanan: Keranjang -->
            <div class="col-span-12 lg:col-span-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs flex flex-col lg:max-h-[calc(100vh-200px)] lg:overflow-hidden min-h-0 self-start lg:sticky lg:top-0">
                
                <!-- Info Pembeli -->
                <div class="p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 rounded-t-xl shrink-0">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider">Nama Pembeli</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <template x-if="namaPembeli.trim() !== ''">
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-10 h-10 bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center rounded-full shrink-0" x-text="namaPembeli.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()"></div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate flex-1" x-text="namaPembeli"></h4>
                                <button @click="bukaModalPembeli()" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition shrink-0">Ubah</button>
                            </div>
                        </template>
                        <template x-if="namaPembeli.trim() === ''">
                            <button @click="bukaModalPembeli()" class="w-full flex items-center gap-3 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/25 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Klik untuk isi nama pembeli</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Daftar Keranjang -->
                <div class="p-3 border-b border-gray-100 dark:border-gray-800 overflow-y-auto flex-1 flex flex-col gap-2 min-h-0 custom-scrollbar">
                    <div x-show="keranjang.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <p class="text-gray-400 dark:text-gray-500 text-xs font-semibold">Keranjang masih kosong</p>
                    </div>
                    <template x-for="item in keranjang" :key="item.produk_id">
                        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/40 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                            <div class="w-12 h-12 rounded overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shrink-0">
                                <img :src="item.foto ? '/' + item.foto : '/images/logo/logo-icon.svg'" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h5 class="font-bold text-gray-900 dark:text-white text-xs truncate" x-text="item.nama_produk"></h5>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xxs font-bold text-indigo-700 dark:text-indigo-400" x-text="formatRupiah(item.harga_jual)"></span>
                                    <span class="text-xxs text-gray-400 dark:text-gray-500">Total: <span class="font-semibold text-gray-700 dark:text-gray-350" x-text="formatRupiah(item.harga_jual * item.jumlah)"></span></span>
                                </div>
                            </div>
                            <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-900 shrink-0">
                                <button @click="kurangiJumlah(item)" class="px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold transition text-xs">-</button>
                                <span class="px-2 text-xs font-bold text-gray-800 dark:text-gray-200" x-text="item.jumlah"></span>
                                <button @click="tambahJumlah(item)" class="px-2 py-0.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold transition text-xs">+</button>
                            </div>
                            <button @click="hapusItem(item)" class="p-1 hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 dark:text-red-400 rounded-md transition hover:text-red-700 ml-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Diskon & Pajak -->
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/20 dark:bg-gray-800/10 flex flex-col gap-1.5 shrink-0">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">Diskon (%)</label>
                            <input type="number" x-model.number="diskonPersen" min="0" max="100" placeholder="0" class="w-full px-2 py-1 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">Pajak (%)</label>
                            <input type="number" x-model.number="pajakPersen" min="0" placeholder="10" class="w-full px-2 py-1 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">Catatan Tambahan</label>
                        <input type="text" x-model="catatan" placeholder="Catatan pesanan..." class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Rincian Biaya -->
                <div class="px-3 py-2 flex flex-col gap-1.5 shrink-0">
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400" x-show="nilaiDiskon > 0">
                        <span>Diskon (<span x-text="diskonPersen"></span>%)</span>
                        <span class="font-semibold text-red-600 dark:text-red-400" x-text="'-' + formatRupiah(nilaiDiskon)"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400" x-show="pajak > 0">
                        <span>Pajak (<span x-text="pajakPersen"></span>%)</span>
                        <span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(pajak)"></span>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-800">
                        <span class="font-bold text-gray-900 dark:text-white text-sm">Total Tagihan</span>
                        <span class="font-black text-indigo-700 dark:text-indigo-400 text-lg" x-text="formatRupiah(totalTagihan)"></span>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="px-3 pb-3 pt-0 grid grid-cols-3 gap-2 shrink-0">
                    <button @click="tahanOrderan()" :disabled="keranjang.length === 0" class="col-span-1 py-3 px-2 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-bold rounded-xl transition text-xs flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Tahan</span>
                    </button>
                    <button @click="lanjutKePembayaran()" :disabled="keranjang.length === 0" class="col-span-2 py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Proses Bayar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== HALAMAN 2: LAYAR PEMBAYARAN ========== -->
    <div x-show="halamanAktif === 'pembayaran'" class="flex flex-col gap-6" x-transition>
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-xl shadow-xs border border-gray-100 dark:border-gray-800">
            <button @click="halamanAktif = 'beranda'" class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                <span>Kembali Ke Kasir</span>
            </button>
            <h2 class="font-extrabold text-lg text-gray-900 dark:text-white">Layar Pembayaran</h2>
            <div class="w-24"></div>
        </div>

        <div class="grid grid-cols-12 gap-6 items-start">
            <!-- Kiri: Numpad -->
            <div class="col-span-12 lg:col-span-7 flex flex-col gap-6">
                <div class="grid grid-cols-3 gap-4 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs">
                    <div class="flex flex-col gap-1">
                        <span class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-lg font-black text-gray-900 dark:text-white" x-text="formatRupiah(totalTagihan)"></span>
                    </div>
                    <div class="flex flex-col gap-1 border-x border-gray-100 dark:border-gray-800 px-4">
                        <span class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Uang Diterima</span>
                        <span class="text-lg font-black text-green-600 dark:text-green-400" x-text="formatRupiah(jumlahDiterimaNum)"></span>
                    </div>
                    <div class="flex flex-col gap-1 pl-2">
                        <span class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Kembalian</span>
                        <span class="text-lg font-black" :class="kembalianNum >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500 dark:text-red-400'" x-text="formatRupiah(kembalianNum)"></span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs flex flex-col gap-6">
                    <div>
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-3">Metode Pembayaran</label>
                        <div class="flex gap-4">
                            <button @click="metodePembayaran = 'Tunai'" :class="metodePembayaran === 'Tunai' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'" class="flex-1 py-3 px-5 rounded-xl font-bold tracking-wide transition text-sm">TUNAI (CASH)</button>
                            <button @click="metodePembayaran = 'Kartu'" :class="metodePembayaran === 'Kartu' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'" class="flex-1 py-3 px-5 rounded-xl font-bold tracking-wide transition text-sm">KARTU (DEBIT)</button>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-2">Input Uang Diterima (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 font-black text-gray-400 text-lg">Rp</span>
                            <input type="text" :value="jumlahDiterimaTampilan" readonly class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-right font-black text-2xl text-slate-800 dark:text-white tracking-wide focus:outline-none">
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button @click="setJumlahDiterimaCepat(totalTagihan)" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 font-bold rounded-lg text-xs transition">Uang Pas</button>
                            <button @click="tambahNominalCepat(20000)" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition">+Rp20k</button>
                            <button @click="tambahNominalCepat(50000)" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition">+Rp50k</button>
                            <button @click="tambahNominalCepat(100000)" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition">+Rp100k</button>
                        </div>
                    </div>

                    <!-- Numpad -->
                    <div class="grid grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <button @click="tekanNumpad('1')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">1</button>
                        <button @click="tekanNumpad('2')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">2</button>
                        <button @click="tekanNumpad('3')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">3</button>
                        <button @click="tekanNumpad('4')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">4</button>
                        <button @click="tekanNumpad('5')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">5</button>
                        <button @click="tekanNumpad('6')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">6</button>
                        <button @click="tekanNumpad('7')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">7</button>
                        <button @click="tekanNumpad('8')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">8</button>
                        <button @click="tekanNumpad('9')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">9</button>
                        <button @click="tekanNumpad('000')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">000</button>
                        <button @click="tekanNumpad('0')" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">0</button>
                        <button @click="hapusKarakterNumpad()" class="py-4 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-800 dark:text-white font-bold text-xl rounded-lg shadow-xxs active:scale-95 transition flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                        </button>
                        <button @click="jumlahDiterima = ''" class="py-4 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 border border-gray-200 dark:border-gray-800 text-red-600 dark:text-red-400 font-extrabold text-xl rounded-lg shadow-xxs active:scale-95 transition">C</button>
                        <button @click="halamanAktif = 'beranda'" class="col-span-2 py-4 bg-slate-200 dark:bg-gray-800 hover:bg-slate-300 dark:hover:bg-gray-700 text-slate-800 dark:text-gray-300 font-bold text-base rounded-lg active:scale-95 transition text-center">Batal</button>
                    </div>
                </div>
            </div>

            <!-- Kanan: Rincian Belanja -->
            <div class="col-span-12 lg:col-span-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs p-6 flex flex-col gap-6">
                <div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-lg mb-1">Rincian Belanja</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pelanggan: <span class="font-bold text-gray-800 dark:text-gray-200" x-text="namaPembeli || '-'"></span></p>
                </div>
                <div class="border-y border-gray-100 dark:border-gray-800 py-4 flex flex-col gap-3 max-h-[300px] overflow-y-auto">
                    <template x-for="item in keranjang" :key="item.produk_id">
                        <div class="flex justify-between items-start gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs text-gray-900 dark:text-white" x-text="item.nama_produk"></p>
                                <p class="text-xxs text-gray-400 dark:text-gray-500"><span x-text="item.jumlah"></span> x <span x-text="formatRupiah(item.harga_jual)"></span></p>
                            </div>
                            <span class="font-bold text-xs text-gray-700 dark:text-gray-300 shrink-0" x-text="formatRupiah(item.harga_jual * item.jumlah)"></span>
                        </div>
                    </template>
                </div>
                <div class="flex flex-col gap-2 text-xs">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Subtotal</span><span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(subtotal)"></span></div>
                    <div class="flex justify-between text-red-600 dark:text-red-400" x-show="nilaiDiskon > 0"><span>Diskon (<span x-text="diskonPersen"></span>%)</span><span class="font-semibold" x-text="'-' + formatRupiah(nilaiDiskon)"></span></div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400" x-show="pajak > 0"><span>Pajak (<span x-text="pajakPersen"></span>%)</span><span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(pajak)"></span></div>
                    <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm text-gray-900 dark:text-white"><span>Total Pembayaran</span><span class="text-indigo-700 dark:text-indigo-400 text-base" x-text="formatRupiah(totalTagihan)"></span></div>
                </div>
                <button @click="selesaikanPembayaran()" :disabled="kembalianNum < 0" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 disabled:shadow-none disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    <span>Konfirmasi Pembayaran</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========== MODAL: DAFTAR PESANAN DITAHAN ========== -->
    <div x-show="modalDitahanTerbuka" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
        <div @click.away="modalDitahanTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="bg-slate-900 dark:bg-gray-950 text-white p-5 flex items-center justify-between">
                <div><h3 class="font-extrabold text-base">Daftar Pesanan Ditahan</h3><p class="text-slate-400 dark:text-gray-400 text-xs mt-0.5">Pesanan yang ditunda pemrosesannya.</p></div>
                <button @click="modalDitahanTerbuka = false" class="text-slate-400 hover:text-white transition font-bold text-lg">&times;</button>
            </div>
            <div class="p-6 max-h-[400px] overflow-y-auto flex flex-col gap-4">
                <template x-if="daftarDitahan.length === 0">
                    <div class="text-center py-10">
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Tidak ada pesanan ditahan.</p>
                    </div>
                </template>
                <template x-for="pesanan in daftarDitahan" :key="pesanan.id">
                    <div class="border border-gray-100 dark:border-gray-800 rounded-lg p-4 bg-gray-50 dark:bg-gray-850/40 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-sm text-gray-900 dark:text-white" x-text="pesanan.nama_pembeli"></span>
                                <span class="text-xxs text-gray-400 dark:text-gray-500" x-text="formatTanggal(pesanan.created_at)"></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Item: <span class="font-semibold text-gray-700 dark:text-gray-350" x-text="pesanan.item_pesanan.map(i => i.nama_produk + ' (' + i.jumlah + ')').join(', ')"></span></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-show="pesanan.catatan">Catatan: <span class="italic text-gray-600 dark:text-gray-450" x-text="pesanan.catatan"></span></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-extrabold text-sm text-indigo-700 dark:text-indigo-400 mr-2" x-text="formatRupiah(pesanan.total)"></span>
                            <button @click="pulihkanPesanan(pesanan.id)" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Pulihkan</button>
                            <button @click="hapusPesananDitahan(pesanan.id)" class="p-2 bg-red-50 dark:bg-red-500/10 hover:bg-red-600 text-red-600 dark:text-red-400 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button @click="modalDitahanTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ========== MODAL: INPUT NAMA PEMBELI ========== -->
    <div x-show="modalPembeliTerbuka" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
        <div @click.away="modalPembeliTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="bg-slate-900 dark:bg-gray-950 text-white p-5">
                <h3 class="font-extrabold text-base">Nama Pembeli</h3>
                <p class="text-slate-400 dark:text-gray-400 text-xs mt-0.5">Masukkan nama pembeli untuk pesanan ini.</p>
            </div>
            <div class="p-6">
                <label class="text-xs font-bold text-gray-600 dark:text-gray-400 block mb-1">Nama Pembeli <span class="text-red-500">*</span></label>
                <input type="text" x-model="inputNamaPembeli" @keydown.enter="simpanPembeli()" placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-850 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" autofocus>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                <button @click="modalPembeliTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                <button @click="simpanPembeli()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Simpan</button>
            </div>
        </div>
    </div>

    <!-- ========== MODAL: PEMBAYARAN SUKSES + CETAK STRUK ========== -->
    <div x-show="modalSuksesTerbuka" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xl w-full max-w-sm overflow-hidden p-6 flex flex-col items-center text-center gap-6">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-3xl animate-bounce">✓</div>
            <div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-lg">Pembayaran Sukses!</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Transaksi telah berhasil dicatat.</p>
                <span class="inline-block mt-3 px-3 py-1 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 font-mono text-xs rounded-full font-bold" x-text="suksesData.kode_transaksi"></span>
            </div>
            <div class="w-full bg-gray-50 dark:bg-gray-950 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col gap-2 text-xs">
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Total Pembayaran</span><span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(suksesData.total_bayar)"></span></div>
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Uang Diterima</span><span class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(suksesData.jumlah_diterima)"></span></div>
                <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm"><span>Uang Kembalian</span><span class="text-green-600 dark:text-green-400" x-text="formatRupiah(suksesData.kembalian)"></span></div>
            </div>
            <!-- Tombol Cetak Struk -->
            <div class="w-full grid grid-cols-2 gap-3">
                <button @click="cetakStruk('pelanggan')" class="py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Struk Pelanggan
                </button>
                <button @click="cetakStruk('dapur')" class="py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Order Dapur
                </button>
            </div>
            <button @click="tutupModalSukses()" class="w-full py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition text-sm">Transaksi Baru</button>
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
        kataKunciPencarian: '',
        kategoriTerpilih: 'semua',
        filterKetersediaan: 'semua',

        // Keranjang
        keranjang: [],
        namaPembeli: '',
        catatan: '',
        diskonPersen: '',
        pajakPersen: 10,

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
            tanggal: ''
        },

        toasts: [],
        toastIdCounter: 0,

        inisialisasi() {},

        get produkTersaring() {
            return this.daftarProduk.filter(p => {
                const cocokKategori = this.kategoriTerpilih === 'semua' || p.kategori_id === this.kategoriTerpilih;
                const cocokCari = p.nama_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase()) || p.kode_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase());
                const cocokKetersediaan = this.filterKetersediaan === 'semua' || p.status_ketersediaan === this.filterKetersediaan;
                return cocokKategori && cocokCari && cocokKetersediaan;
            });
        },

        get subtotal() {
            return this.keranjang.reduce((s, i) => s + (i.harga_jual * i.jumlah), 0);
        },
        get nilaiDiskon() {
            return Math.round(this.subtotal * ((this.diskonPersen || 0) / 100));
        },
        get pajak() {
            const base = this.subtotal - this.nilaiDiskon;
            return Math.max(0, Math.round(base * (this.pajakPersen / 100)));
        },
        get totalTagihan() {
            return Math.max(0, this.subtotal - this.nilaiDiskon + this.pajak);
        },
        get jumlahDiterimaNum() { return parseFloat(this.jumlahDiterima) || 0; },
        get kembalianNum() { return this.jumlahDiterimaNum - this.totalTagihan; },
        get totalDitahan() { return this.daftarDitahan.length; },
        get jumlahDiterimaTampilan() {
            if (!this.jumlahDiterima) return '';
            return new Intl.NumberFormat('id-ID').format(this.jumlahDiterimaNum);
        },

        // Keranjang
        tambahKeKeranjang(produk) {
            const ada = this.keranjang.find(i => i.produk_id === produk.id);
            const qty = ada ? ada.jumlah : 0;
            if (produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= qty) { this.tambahToast('Stok tidak mencukupi!', 'error'); return; }
            if (ada) { ada.jumlah++; }
            else { this.keranjang.push({ produk_id: produk.id, nama_produk: produk.nama_produk, harga_jual: parseFloat(produk.harga_jual), foto: produk.foto, jumlah: 1, stok: produk.stok, status_ketersediaan: produk.status_ketersediaan }); }
            this.tambahToast('Produk ditambahkan.', 'sukses');
        },
        tambahJumlah(item) { if (item.status_ketersediaan !== 'pesan_dulu' && item.stok <= item.jumlah) { this.tambahToast('Stok tidak mencukupi!', 'error'); return; } item.jumlah++; },
        kurangiJumlah(item) { if (item.jumlah > 1) item.jumlah--; else this.hapusItem(item); },
        hapusItem(item) { this.keranjang = this.keranjang.filter(i => i.produk_id !== item.produk_id); this.tambahToast('Item dihapus.', 'info'); },

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

        // Numpad
        tekanNumpad(key) { if (this.jumlahDiterima === '' && (key === '0' || key === '00' || key === '000')) return; this.jumlahDiterima += key; },
        hapusKarakterNumpad() { this.jumlahDiterima = this.jumlahDiterima.slice(0, -1); },
        setJumlahDiterimaCepat(n) { this.jumlahDiterima = Math.round(n).toString(); },
        tambahNominalCepat(n) { this.jumlahDiterima = ((parseFloat(this.jumlahDiterima) || 0) + n).toString(); },

        // Ke pembayaran
        lanjutKePembayaran() {
            if (this.keranjang.length === 0) { this.tambahToast('Keranjang masih kosong!', 'error'); return; }
            if (this.namaPembeli.trim() === '') { this.tambahToast('Nama pembeli harus diisi terlebih dahulu!', 'error'); this.bukaModalPembeli(); return; }
            this.jumlahDiterima = '';
            this.halamanAktif = 'pembayaran';
        },

        // Hold Order
        tahanOrderan() {
            if (this.keranjang.length === 0) return;
            if (this.namaPembeli.trim() === '') { this.tambahToast('Nama pembeli harus diisi terlebih dahulu!', 'error'); this.bukaModalPembeli(); return; }

            fetch("{{ route('transaksi.tahan') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    nama_pembeli: this.namaPembeli,
                    item_pesanan: this.keranjang,
                    subtotal: this.subtotal,
                    pajak: this.pajak,
                    diskon: this.diskonPersen || 0,
                    total: this.totalTagihan,
                    catatan: this.catatan || null
                })
            })
            .then(r => r.json())
            .then(r => {
                if (r.status === 'success') { this.tambahToast('Pesanan ditahan.', 'sukses'); this.bersihkanKeranjang(); this.ambilDaftarDitahanDariServer(); }
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
                    this.keranjang = d.item_pesanan;
                    this.namaPembeli = d.nama_pembeli;
                    this.catatan = d.catatan || '';
                    this.diskonPersen = d.diskon || '';
                    this.modalDitahanTerbuka = false;
                    this.tambahToast('Pesanan dipulihkan.', 'sukses');
                    this.ambilDaftarDitahanDariServer();
                } else this.tambahToast(r.pesan, 'error');
            });
        },

        hapusPesananDitahan(id) {
            if (!confirm('Hapus pesanan ditahan ini?')) return;
            fetch(`/transaksi/hapus-ditahan/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } })
            .then(r => r.json())
            .then(r => { if (r.status === 'success') { this.tambahToast('Pesanan dihapus.', 'info'); this.ambilDaftarDitahanDariServer(); } else this.tambahToast(r.pesan, 'error'); });
        },

        // Pembayaran Final
        selesaikanPembayaran() {
            if (this.kembalianNum < 0) { this.tambahToast('Uang yang diterima kurang!', 'error'); return; }

            // Simpan data struk sebelum bersihkan keranjang
            const dataStruk = {
                nama_pembeli: this.namaPembeli,
                keranjang: JSON.parse(JSON.stringify(this.keranjang)),
                subtotal: this.subtotal,
                diskon_persen: this.diskonPersen || 0,
                nilai_diskon: this.nilaiDiskon,
                pajak_persen: this.pajakPersen,
                pajak: this.pajak,
                total_bayar: this.totalTagihan,
                jumlah_diterima: this.jumlahDiterimaNum,
                kembalian: this.kembalianNum,
                metode_pembayaran: this.metodePembayaran,
                catatan: this.catatan,
                tanggal: new Date().toLocaleString('id-ID')
            };

            fetch("{{ route('transaksi.bayar') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    nama_pembeli: this.namaPembeli,
                    item_pesanan: this.keranjang,
                    subtotal: this.subtotal,
                    pajak: this.pajak,
                    diskon: this.nilaiDiskon,
                    total_bayar: this.totalTagihan,
                    jumlah_diterima: this.jumlahDiterimaNum,
                    kembalian: this.kembalianNum,
                    metode_pembayaran: this.metodePembayaran
                })
            })
            .then(r => r.json())
            .then(r => {
                if (r.status === 'success') {
                    this.keranjang.forEach(item => { const p = this.daftarProduk.find(x => x.id === item.produk_id); if (p) p.stok -= item.jumlah; });
                    this.suksesData = { ...dataStruk, kode_transaksi: r.kode_transaksi };
                    this.modalSuksesTerbuka = true;
                    this.bersihkanKeranjang();
                    this.halamanAktif = 'beranda';
                } else this.tambahToast(r.pesan, 'error');
            })
            .catch(() => this.tambahToast('Gagal memproses transaksi!', 'error'));
        },

        // Cetak struk
        cetakStruk(jenis) {
            const d = this.suksesData;
            const toko = 'Toko Pastry Modern';
            const alamat = 'Jl. Contoh No. 123';
            let html = '';

            if (jenis === 'pelanggan') {
                html = `
                <div style="font-family:'Courier New',monospace;width:300px;margin:auto;padding:20px;font-size:12px;">
                    <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:10px;margin-bottom:10px;">
                        <h2 style="margin:0;font-size:16px;">${toko}</h2>
                        <p style="margin:4px 0 0 0;font-size:11px;">${alamat}</p>
                    </div>
                    <div style="margin-bottom:8px;font-size:11px;">
                        <div>No: <strong>${d.kode_transaksi}</strong></div>
                        <div>Tgl: ${d.tanggal}</div>
                        <div>Pelanggan: <strong>${d.nama_pembeli}</strong></div>
                        <div>Bayar: ${d.metode_pembayaran}</div>
                    </div>
                    <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:8px 0;">
                        ${d.keranjang.map((item, i) => `
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                <span>${i+1}. ${item.nama_produk}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;padding-left:16px;color:#555;">
                                <span>${item.jumlah} x ${this.formatRupiah(item.harga_jual)}</span>
                                <span>${this.formatRupiah(item.harga_jual * item.jumlah)}</span>
                            </div>
                        `).join('')}
                    </div>
                    <div style="padding:8px 0;border-bottom:1px dashed #000;">
                        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>${this.formatRupiah(d.subtotal)}</span></div>
                        ${d.nilai_diskon > 0 ? `<div style="display:flex;justify-content:space-between;"><span>Diskon (${d.diskon_persen}%)</span><span>-${this.formatRupiah(d.nilai_diskon)}</span></div>` : ''}
                        ${d.pajak > 0 ? `<div style="display:flex;justify-content:space-between;"><span>Pajak (${d.pajak_persen}%)</span><span>${this.formatRupiah(d.pajak)}</span></div>` : ''}
                        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;margin-top:6px;"><span>TOTAL</span><span>${this.formatRupiah(d.total_bayar)}</span></div>
                    </div>
                    <div style="padding:8px 0;border-bottom:1px dashed #000;">
                        <div style="display:flex;justify-content:space-between;"><span>Diterima</span><span>${this.formatRupiah(d.jumlah_diterima)}</span></div>
                        <div style="display:flex;justify-content:space-between;font-weight:bold;"><span>Kembalian</span><span>${this.formatRupiah(d.kembalian)}</span></div>
                    </div>
                    <div style="text-align:center;padding-top:12px;font-size:11px;color:#666;">
                        <p style="margin:0;">Terima kasih atas kunjungan Anda!</p>
                        <p style="margin:4px 0 0 0;">Selamat menikmati ☕</p>
                    </div>
                </div>`;
            } else {
                const itemsDapur = d.keranjang.filter(item => item.status_ketersediaan === 'pesan_dulu');
                if (itemsDapur.length === 0) {
                    this.tambahToast('Tidak ada item Pre-Order (Pesan Dulu) untuk dapur.', 'info');
                    return;
                }
                html = `
                <div style="font-family:'Courier New',monospace;width:300px;margin:auto;padding:20px;font-size:12px;">
                    <div style="text-align:center;border-bottom:2px solid #000;padding-bottom:10px;margin-bottom:10px;">
                        <h2 style="margin:0;font-size:18px;letter-spacing:2px;">🍳 ORDER DAPUR</h2>
                    </div>
                    <div style="margin-bottom:10px;font-size:12px;">
                        <div>No: <strong>${d.kode_transaksi}</strong></div>
                        <div>Tgl: ${d.tanggal}</div>
                        <div>Pelanggan: <strong style="font-size:14px;">${d.nama_pembeli}</strong></div>
                    </div>
                    <div style="border-top:2px solid #000;border-bottom:2px solid #000;padding:10px 0;">
                        ${itemsDapur.map((item, i) => `
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:14px;">
                                <span><strong>${i+1}. ${item.nama_produk}</strong></span>
                                <span style="background:#000;color:#fff;padding:2px 10px;border-radius:4px;font-weight:bold;font-size:16px;">x${item.jumlah}</span>
                            </div>
                        `).join('')}
                    </div>
                    ${d.catatan ? `<div style="padding:8px 0;border-bottom:1px dashed #000;"><strong>📝 Catatan:</strong> ${d.catatan}</div>` : ''}
                    <div style="text-align:center;padding-top:12px;font-size:11px;color:#666;">
                        <p style="margin:0;">Dicetak: ${d.tanggal}</p>
                    </div>
                </div>`;
            }

            const win = window.open('', '_blank', 'width=400,height=700');
            win.document.write(`<html><head><title>Struk ${jenis === 'pelanggan' ? 'Pelanggan' : 'Dapur'}</title></head><body style="margin:0;padding:0;">${html}</body></html>`);
            win.document.close();
            win.focus();
            setTimeout(() => win.print(), 500);
        },

        tutupModalSukses() { this.modalSuksesTerbuka = false; },

        bersihkanKeranjang() {
            this.keranjang = [];
            this.namaPembeli = '';
            this.catatan = '';
            this.diskonPersen = '';
            this.pajakPersen = 10;
            this.jumlahDiterima = '';
        },

        formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); },
        formatTanggal(d) { return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }); },
        tambahToast(pesan, tipe = 'info') { const id = this.toastIdCounter++; this.toasts.push({ id, pesan, tipe }); setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000); }
    }
}
</script>
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
[x-cloak] { display: none !important; }
@keyframes slide-in { from { transform: translateY(1rem); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.animate-slide-in { animation: slide-in 0.2s ease-out forwards; }
.text-xxs { font-size: 0.65rem; }
</style>
@endpush
