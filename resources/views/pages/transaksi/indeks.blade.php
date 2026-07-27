@extends('layouts.app')

@section('content')
<div x-data="aplikasiKasir()" x-init="inisialisasi()"
    class="bg-gray-50 dark:bg-gray-950 min-h-screen lg:h-[calc(100vh-64px)] text-gray-800 dark:text-gray-200 -m-4 md:-m-6 p-4 md:p-6 lg:overflow-hidden flex flex-col"
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
                    <input type="text" x-model="kataKunciPencarian" placeholder="Cari nama atau kode produk..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm bg-gray-50 dark:bg-gray-800 focus:bg-white dark:focus:bg-gray-900 dark:text-white transition-all">
                </div>
                <button @click="bukaModalDaftarDitahan()"
                    class="relative flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold rounded-lg border border-amber-100 dark:border-amber-500/25 transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Daftar Ditahan</span>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xxs font-bold px-2 py-0.5 rounded-full"
                        x-show="totalDitahan > 0" x-text="totalDitahan"></span>
                </button>
            </div>
        </div>

        <!-- Layout Utama -->
        <div class="grid grid-cols-12 gap-4 items-stretch flex-1 min-h-0 lg:overflow-hidden">

            <!-- Kiri: Produk -->
            <div class="col-span-12 lg:col-span-8 flex flex-col gap-4 lg:max-h-[calc(100vh-200px)] lg:overflow-hidden min-h-0">
                <div class="flex flex-col gap-4 shrink-0">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                            <button @click="kategoriTerpilih = 'semua'"
                                :class="kategoriTerpilih === 'semua' ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap">Semua
                                Produk</button>
                            <template x-for="kategori in daftarKategori" :key="kategori.id">
                                <button @click="kategoriTerpilih = kategori.id"
                                    :class="kategoriTerpilih === kategori.id ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-800'"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold tracking-wide transition whitespace-nowrap"
                                    x-text="kategori.nama_kategori"></button>
                            </template>
                        </div>

                        <!-- Filter Status Ketersediaan -->
                        <div
                            class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-lg border border-gray-100 dark:border-gray-800 shrink-0">
                            <button @click="filterKetersediaan = 'semua'"
                                :class="filterKetersediaan === 'semua' ? 'bg-slate-900 dark:bg-gray-800 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                class="px-3 py-1.5 rounded-md text-xs font-bold transition">Semua</button>
                            <button @click="filterKetersediaan = 'stok_ada'"
                                :class="filterKetersediaan === 'stok_ada' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                class="px-3 py-1.5 rounded-md text-xs font-bold transition">Ready Stock</button>
                            <button @click="filterKetersediaan = 'pesan_dulu'"
                                :class="filterKetersediaan === 'pesan_dulu' ? 'bg-amber-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                class="px-3 py-1.5 rounded-md text-xs font-bold transition">Pre-Order</button>
                            <button @click="filterKetersediaan = 'promo'"
                                :class="filterKetersediaan === 'promo' ? 'bg-rose-600 text-white shadow-xs' : 'bg-transparent text-gray-550 dark:text-gray-400 hover:bg-gray-550 dark:hover:bg-gray-850'"
                                class="px-3 py-1.5 rounded-md text-xs font-bold transition">Promo %</button>
                        </div>
                    </div>
                </div>

                <!-- Scrollable grid wrapper -->
                <div class="flex-1 lg:overflow-y-auto pr-1 min-h-0 custom-scrollbar">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pb-4">
                        <template x-for="produk in produkTersaring" :key="produk.id">
                            <div
                                class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group relative">
                                <div x-show="produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= 0"
                                    class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-10 flex flex-col items-center justify-center p-2 text-center">
                                    <span
                                        class="bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 font-bold px-3 py-1 rounded-full text-xs">Habis</span>
                                </div>
                                <div class="aspect-square bg-gray-100 dark:bg-gray-850 relative overflow-hidden">
                                    <img :src="getFotoUrl(produk.foto, produk.nama_produk)"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        alt="Produk">
                                    <div class="absolute top-2 left-2 z-5">
                                        <template x-if="produk.status_ketersediaan === 'stok_ada'">
                                            <span
                                                class="bg-emerald-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Ready</span>
                                        </template>
                                        <template x-if="produk.status_ketersediaan === 'pesan_dulu'">
                                            <span
                                                class="bg-amber-500/90 text-white backdrop-blur-xs font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xxs">Pre-Order</span>
                                        </template>
                                    </div>
                                    <div
                                        class="absolute bottom-2 right-2 text-xs font-semibold px-2 py-0.5 rounded backdrop-blur-xs"
                                        :class="produk.status_ketersediaan !== 'pesan_dulu' && produk.stok > 0 && produk.stok <= produk.stok_minimum ? 'bg-red-600/90 text-white font-bold animate-pulse' : 'bg-slate-900/75 dark:bg-gray-950/75 text-white'">
                                        <span
                                            x-text="produk.status_ketersediaan === 'pesan_dulu' ? 'Pre-Order' : (produk.stok <= produk.stok_minimum && produk.stok > 0 ? '⚠️ Sisa ' + produk.stok : 'Stok: ' + produk.stok)"></span>
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                                    <div>
                                        <p class="text-xxs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider"
                                            x-text="produk.kategori?.nama_kategori || 'Pastry'"></p>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-sm mt-0.5 line-clamp-2"
                                            x-text="produk.nama_produk"></h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500" x-text="produk.kode_produk">
                                        </p>
                                    </div>
                                    <div
                                        class="flex flex-col mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 gap-1">
                                        <template x-if="produk.promo_aktif">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span
                                                    class="text-xxs font-extrabold px-1.5 py-0.5 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-450 rounded animate-pulse"
                                                    x-text="'-' + Math.round(produk.promo_aktif.persentase) + '%'"></span>
                                                <span class="text-xs text-gray-450 dark:text-gray-500 line-through"
                                                    x-text="formatRupiah(produk.harga_jual)"></span>
                                            </div>
                                        </template>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-amber-700 dark:text-amber-400 text-sm"
                                                x-text="formatRupiah(produk.promo_aktif ? produk.harga_diskon : produk.harga_jual)"></span>
                                            <button @click="tambahKeKeranjang(produk)"
                                                :disabled="produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= 0"
                                                class="p-2 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-600 dark:hover:bg-amber-600 text-amber-600 dark:text-amber-400 hover:text-white rounded-lg transition disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600">
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
                    <div class="w-10 h-10 bg-amber-100 text-amber-700 font-bold flex items-center justify-center rounded-full shrink-0" x-text="namaPembeli.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()"></div>
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate flex-1" x-text="namaPembeli"></h4>
                    <button @click="bukaModalPembeli()" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition shrink-0">Ubah</button>
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

    <!-- Pilih Member -->
    <div class="p-3 border-b border-gray-100 dark:border-gray-800 shrink-0"
        x-data="{
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
                $data.memberIdTerpilih = member.id;
                $data.memberTerpilih = member;
                if ($data.namaPembeli.trim() === '') {
                    $data.namaPembeli = member.nama;
                }
                this.cariMember = '';
                this.dropdownTerbuka = false;
            },
            hapusMember() {
                $data.memberIdTerpilih = '';
                $data.memberTerpilih = null;
                this.cariMember = '';
            },
            getLevelColor(level) {
                if (level === 'Gold') return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400';
                if (level === 'Silver') return 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300';
                return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
            }
        }"
        @click.outside="dropdownTerbuka = false">
        <label class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider block mb-2">Member (Opsional)</label>

        <!-- Jika member sudah terpilih -->
        <template x-if="$data.memberTerpilih">
            <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/25 rounded-lg">
                <div class="w-7 h-7 bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center rounded-full shrink-0"
                    x-text="$data.memberTerpilih.nama.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="$data.memberTerpilih.nama"></p>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400" x-text="$data.memberTerpilih.no_hp + ' · ' + $data.memberTerpilih.poin + ' poin'"></p>
                </div>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold shrink-0"
                    :class="getLevelColor($data.memberTerpilih.level)"
                    x-text="$data.memberTerpilih.level"></span>
                <button @click="hapusMember()" class="p-1 hover:bg-red-100 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-md transition shrink-0" title="Hapus member">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>

        <!-- Input pencarian jika belum pilih member -->
        <template x-if="!$data.memberTerpilih">
            <div class="relative">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" x-model="cariMember"
                        @focus="dropdownTerbuka = true"
                        @input="dropdownTerbuka = true"
                        placeholder="Cari nama atau no. HP member..."
                        class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500 transition placeholder:text-gray-400 dark:placeholder:text-gray-600">
                </div>

                <!-- Dropdown hasil pencarian -->
                <div x-show="dropdownTerbuka" x-transition.opacity.duration.150ms
                    class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto custom-scrollbar">

                    <!-- Tanpa Member -->
                    <button @click="hapusMember(); dropdownTerbuka = false"
                        class="w-full px-3 py-2 text-left text-xs text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        <span>Tanpa Member (Pelanggan Umum)</span>
                    </button>

                    <div class="border-t border-gray-100 dark:border-gray-800"></div>

                    <!-- Hasil pencarian member -->
                    <template x-for="member in memberTersaring" :key="member.id">
                        <button @click="pilihMemberItem(member)"
                            class="w-full px-3 py-2 text-left hover:bg-amber-50 dark:hover:bg-amber-500/10 transition flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] font-bold flex items-center justify-center rounded-full shrink-0"
                                x-text="member.nama.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase()"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 dark:text-white truncate" x-text="member.nama"></p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500" x-text="member.no_hp + ' · ' + member.poin + ' poin'"></p>
                            </div>
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold shrink-0"
                                :class="getLevelColor(member.level)"
                                x-text="member.level"></span>
                        </button>
                    </template>

                    <!-- Tidak ditemukan -->
                    <template x-if="memberTersaring.length === 0 && cariMember.trim() !== ''">
                        <div class="px-3 py-4 text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Tidak ada member ditemukan</p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="akanDapatPoin">
            <p class="text-xxs text-emerald-600 dark:text-emerald-400 font-bold mt-1.5">🎁 Bonus poin masuk! +<span x-text="poinDiperoleh"></span> poin untuk member ✨</p>
        </template>
    </div>

    <!-- Daftar Keranjang -->
    <div class="p-3 border-b border-gray-100 dark:border-gray-800 overflow-y-auto flex-1 flex flex-col gap-2 min-h-0 custom-scrollbar">
        <div x-show="keranjang.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            <p class="text-gray-400 dark:text-gray-500 text-xs font-semibold">Keranjang masih kosong</p>
        </div>
        <template x-for="item in keranjang" :key="item.produk_id">
            <div class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-800/40 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded overflow-hidden bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shrink-0">
                        <img :src="getFotoUrl(item.foto, item.nama_produk)" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-bold text-gray-900 dark:text-white text-xs truncate" x-text="item.nama_produk"></h5>
                        <div class="flex flex-col gap-0.5 mt-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xxs font-bold text-amber-700 dark:text-amber-400" x-text="formatRupiah(item.harga_jual)"></span>
                                <template x-if="item.promo_aktif">
                                    <span class="text-[10px] text-gray-400 line-through" x-text="formatRupiah(item.harga_jual_asli)"></span>
                                </template>
                                <template x-if="item.promo_aktif">
                                    <span class="text-[9px] font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-1 rounded" x-text="Math.round(item.promo_aktif.persentase) + '%'"></span>
                                </template>
                            </div>
                            <span class="text-xxs text-gray-400 dark:text-gray-500">Total: <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="formatRupiah(item.harga_jual * item.jumlah)"></span></span>
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
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400 dark:text-gray-500 text-[10px]">📝</span>
                    <input type="text" x-model="item.catatan" placeholder="Catatan item (cth: Less sugar, extra hot)" class="w-full pl-6 pr-2 py-1 text-[10px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded focus:outline-none focus:ring-1 focus:ring-amber-500 transition">
                </div>
            </div>
        </template>
    </div>

    <!-- Pajak -->
    <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/20 dark:bg-gray-800/10 flex flex-col gap-1.5 shrink-0">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-1">Pajak (%)</label>
                <input type="number" x-model.number="pajakPersen" min="0" placeholder="10" class="w-full px-2 py-1 text-xs border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500">
            </div>
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
            <span class="font-black text-amber-700 dark:text-amber-400 text-lg" x-text="formatRupiah(totalTagihan)"></span>
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
                            <input x-ref="inputJumlahDiterima"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                :value="jumlahDiterimaTampilan"
                                @input="handleInputJumlahDiterima($event)"
                                @keydown.enter.prevent="if (kembalianNum >= 0) selesaikanPembayaran()"
                                placeholder="0"
                                class="w-full pl-12 pr-10 py-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-right font-black text-2xl text-slate-800 dark:text-white tracking-wide focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition">
                            <button type="button" x-show="jumlahDiterima !== ''" @click="jumlahDiterima = ''; $nextTick(() => $refs.inputJumlahDiterima.focus())" title="Hapus input" class="absolute right-3 text-gray-400 hover:text-red-500 transition p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
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
                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&color=5a3c27&data=' + encodeURIComponent('00020101021226380010co.saishos0114123456789012345204581153033605408' + totalTagihan + '.005802ID5907saishoS6007Jakarta6304')"
                                class="w-44 h-44" alt="QRIS QR Code">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-gray-800 dark:text-white tracking-wider">🥨
                                Saisho</span>
                            <span
                                class="px-2 py-0.5 bg-amber-600 text-white font-black text-[9px] rounded uppercase tracking-widest">QRIS</span>
                        </div>
                    </div>
                    <div class="max-w-xs">
                        <h4 class="font-extrabold text-gray-950 dark:text-white text-base">Scan QRIS Untuk Bayar</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">Silakan tunjukkan QR
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
                <template x-for="item in keranjang" :key="item.produk_id">
                    <div class="flex justify-between items-start gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-xs text-gray-900 dark:text-white" x-text="item.nama_produk"></p>
                            <p class="text-xxs text-gray-400 dark:text-gray-500">
                                <span x-text="item.jumlah"></span> x <span
                                    x-text="formatRupiah(item.harga_jual)"></span>
                                <template x-if="item.promo_aktif">
                                    <span
                                        class="text-[9px] font-bold text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-1 rounded ml-1"
                                        x-text="Math.round(item.promo_aktif.persentase) + '%'"></span>
                                </template>
                            </p>
                        </div>
                        <span class="font-bold text-xs text-gray-700 dark:text-gray-300 shrink-0"
                            x-text="formatRupiah(item.harga_jual * item.jumlah)"></span>
                    </div>
                </template>
            </div>
            <div class="flex flex-col gap-2 text-xs">
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Subtotal</span><span
                        class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(subtotal)"></span>
                </div>
                <div class="flex justify-between text-red-600 dark:text-red-400" x-show="nilaiDiskon > 0"><span>Diskon
                        (<span x-text="diskonPersen"></span>%)</span><span class="font-semibold"
                        x-text="'-' + formatRupiah(nilaiDiskon)"></span></div>
                <div class="flex justify-between text-gray-500 dark:text-gray-400" x-show="pajak > 0"><span>Pajak (<span
                            x-text="pajakPersen"></span>%)</span><span
                        class="font-semibold text-gray-800 dark:text-white" x-text="formatRupiah(pajak)"></span></div>
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

<!-- ========== MODAL: DAFTAR PESANAN DITAHAN ========== -->
<div x-show="modalDitahanTerbuka"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
    <div
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-3xl overflow-hidden border border-gray-100 dark:border-gray-800">
        <div class="bg-slate-900 dark:bg-gray-950 text-white p-5 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-base">Daftar Pesanan Ditahan / Kredit</h3>
                <p class="text-slate-400 dark:text-gray-400 text-xs mt-0.5">Pesanan belum lunas yang menunggu pembayaran.</p>
            </div>
            <button @click="modalDitahanTerbuka = false"
                class="text-slate-400 hover:text-white transition font-bold text-lg">&times;</button>
        </div>
        <div class="p-6 max-h-[450px] overflow-y-auto flex flex-col gap-4 custom-scrollbar">
            <template x-if="daftarDitahan.length === 0">
                <div class="text-center py-10">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-gray-400 dark:text-gray-500 font-semibold text-sm">Tidak ada pesanan ditahan.</p>
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
                                <span class="px-2 py-0.5 bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400 text-[10px] font-bold rounded-full">Belum Lunas</span>
                                <template x-if="pesanan.transaksi">
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-mono rounded-full" x-text="pesanan.transaksi.kode_transaksi"></span>
                                </template>
                            </div>
                            <p class="text-xxs text-gray-400 dark:text-gray-500 mt-0.5" x-text="formatTanggal(pesanan.created_at)"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Item: <span
                                    class="font-semibold text-gray-700 dark:text-gray-300"
                                    x-text="pesanan.item_pesanan.map(i => i.nama_produk + ' (' + i.jumlah + ')').join(', ')"></span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-show="pesanan.catatan">Catatan:
                                <span class="italic text-gray-600 dark:text-gray-400" x-text="pesanan.catatan"></span>
                            </p>
                        </div>
                        <span class="font-extrabold text-sm text-amber-700 dark:text-amber-400 shrink-0"
                            x-text="formatRupiah(pesanan.total)"></span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap border-t border-gray-100 dark:border-gray-800 pt-3">
                        <button @click="cetakOrderDapurDitahan(pesanan)"
                            class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak Tiket Produksi
                        </button>
                        <button @click="mulaiLunaskan(pesanan)"
                            class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-lg transition shadow-xs flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Lunaskan
                        </button>
                        <button @click="pulihkanPesanan(pesanan.id)"
                            class="px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Pulihkan
                        </button>
                        <button @click="hapusPesananDitahan(pesanan.id)"
                            class="p-1.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-500 dark:text-red-400 rounded-lg transition ml-auto" title="Hapus pesanan">
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
        <div class="bg-slate-900 dark:bg-gray-950 text-white p-5">
            <h3 class="font-extrabold text-base">Nama Pembeli</h3>
            <p class="text-slate-400 dark:text-gray-400 text-xs mt-0.5">Masukkan nama pembeli untuk pesanan ini.</p>
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
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-lg">Pesanan Berhasil Ditahan!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pesanan tercatat sebagai kredit. Cetak tiket produksi di bawah.</p>
                </div>
            </template>
            <span
                class="inline-block mt-3 px-3 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20 font-mono text-xs rounded-full font-bold"
                x-text="suksesData.kode_transaksi"></span>
            <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
                <span class="inline-block ml-1 px-2 py-0.5 bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400 text-[10px] font-bold rounded-full">Belum Lunas</span>
            </template>
        </div>
        <template x-if="suksesData.status_pembayaran === 'Lunas'">
            <div class="w-full bg-gray-50 dark:bg-gray-950 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col gap-2 text-xs">
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Total Pembayaran</span><span
                        class="font-semibold text-gray-800 dark:text-white"
                        x-text="formatRupiah(suksesData.total_bayar)"></span></div>
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Uang Diterima</span><span
                        class="font-semibold text-gray-800 dark:text-white"
                        x-text="formatRupiah(suksesData.jumlah_diterima)"></span></div>
                <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm">
                    <span>Uang Kembalian</span><span class="text-green-600 dark:text-green-400"
                        x-text="formatRupiah(suksesData.kembalian)"></span>
                </div>
            </div>
        </template>
        <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
            <div class="w-full bg-amber-50 dark:bg-amber-500/5 rounded-xl p-4 border border-amber-200 dark:border-amber-500/20 flex flex-col gap-2 text-xs">
                <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Total Tagihan</span><span
                        class="font-bold text-amber-700 dark:text-amber-400"
                        x-text="formatRupiah(suksesData.total_bayar)"></span></div>
                <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Status</span><span
                        class="font-bold text-red-600 dark:text-red-400">Belum Lunas</span></div>
            </div>
        </template>
        <!-- Tombol Cetak Struk -->
        <div class="w-full flex flex-col gap-3">
            <template x-if="suksesData.status_pembayaran === 'Lunas'">
                <div class="grid grid-cols-2 gap-3">
                    <button @click="cetakStruk('pelanggan')"
                        class="py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Struk Pelanggan
                    </button>
                    <button @click="cetakStruk('dapur')"
                        class="py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Tiket Produksi
                    </button>
                </div>
            </template>
            <template x-if="suksesData.status_pembayaran === 'Belum Lunas'">
            </template>
        </div>
        <button @click="tutupModalSukses()"
            class="w-full py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition text-sm"
            x-text="suksesData.status_pembayaran === 'Lunas' ? 'Transaksi Baru' : 'Kembali'"></button>
    </div>
</div>

<!-- ========== MODAL: KONFIRMASI HAPUS PESANAN DITAHAN ========== -->
<div x-show="modalKonfirmasiHapusTerbuka"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4" x-transition
    x-cloak>
    <div @click.away="modalKonfirmasiHapusTerbuka = false; modalDitahanTerbuka = true"
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
        <div class="p-6 text-center">
            <div
                class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                ⚠</div>
            <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Pesanan Ditahan?</h3>
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

</div>
@endsection

@push('scripts')
<script>
    function aplikasiKasir() {
        return {
            halamanAktif: 'beranda',
            daftarProduk: @json($daftarProduk),
            daftarKategori: @json($daftarKategori),
            daftarMember: @json($daftarMember),
            kataKunciPencarian: '',
            kategoriTerpilih: 'semua',
            filterKetersediaan: 'semua',

            // Keranjang
            keranjang: [],
            namaPembeli: '',
            memberIdTerpilih: '',
            memberTerpilih: null,
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
            modalKonfirmasiHapusTerbuka: false,
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

            inisialisasi() { },

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

            getFotoUrl(foto, nama) {
                if (foto) {
                    if (foto.startsWith('http://') || foto.startsWith('https://')) return foto;
                    if (foto.startsWith('storage/')) return '/' + foto;
                    return '/storage/' + foto;
                }
                const n = (nama || '').toLowerCase();
                if (n.includes('croissant')) return 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&auto=format&fit=crop';
                if (n.includes('danish')) return 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop';
                if (n.includes('cheese tart') || n.includes('tartlet')) return 'https://images.unsplash.com/photo-1519869325930-281384150729?w=400&auto=format&fit=crop';
                if (n.includes('fudge cake') || n.includes('forest cake') || n.includes('velvet cake') || n.includes('birthday cake') || n.includes('tiramisu')) {
                    return 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&auto=format&fit=crop';
                }
                if (n.includes('cinnamon roll')) return 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?w=400&auto=format&fit=crop';
                if (n.includes('bun') || n.includes('boule') || n.includes('bread')) return 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop';
                if (n.includes('cookie')) return 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400&auto=format&fit=crop';
                if (n.includes('macaron')) return 'https://images.unsplash.com/photo-1569864358642-9d1684040f43?w=400&auto=format&fit=crop';
                return '/images/logo/logo-icon.svg';
            },

            get produkTersaring() {
                return this.daftarProduk.filter(p => {
                    const cocokKategori = this.kategoriTerpilih === 'semua' || p.kategori_id === this.kategoriTerpilih;
                    const cocokCari = p.nama_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase()) || p.kode_produk.toLowerCase().includes(this.kataKunciPencarian.toLowerCase());

                    let cocokKetersediaan = false;
                    if (this.filterKetersediaan === 'semua') {
                        cocokKetersediaan = true;
                    } else if (this.filterKetersediaan === 'promo') {
                        cocokKetersediaan = p.promo_aktif !== null;
                    } else {
                        cocokKetersediaan = p.status_ketersediaan === this.filterKetersediaan;
                    }

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
            get poinDiperoleh() {
                return Math.floor(this.totalTagihan / 100000) * 100;
            },
            get akanDapatPoin() {
                return this.memberTerpilih && this.totalTagihan >= 100000;
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

            // Keranjang
            tambahKeKeranjang(produk) {
                const ada = this.keranjang.find(i => i.produk_id === produk.id);
                const qty = ada ? ada.jumlah : 0;
                if (produk.status_ketersediaan !== 'pesan_dulu' && produk.stok <= qty) { this.tambahToast('Stok tidak mencukupi!', 'error'); return; }
                if (ada) { ada.jumlah++; }
                else {
                    const hargaFinal = produk.promo_aktif ? parseFloat(produk.harga_diskon) : parseFloat(produk.harga_jual);
                    this.keranjang.push({
                        produk_id: produk.id,
                        nama_produk: produk.nama_produk,
                        harga_jual: hargaFinal,
                        harga_jual_asli: parseFloat(produk.harga_jual),
                        promo_aktif: produk.promo_aktif,
                        foto: produk.foto,
                        jumlah: 1,
                        stok: produk.stok,
                        status_ketersediaan: produk.status_ketersediaan,
                        catatan: ''
                    });
                }
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
                        if (r.status === 'success') {
                            // Kurangi stok di frontend UI segera setelah berhasil ditahan
                            this.keranjang.forEach(item => {
                                const p = this.daftarProduk.find(x => x.id === item.produk_id);
                                if (p && p.status_ketersediaan !== 'pesan_dulu') {
                                    p.stok -= item.jumlah;
                                }
                            });
                            this.tambahToast('Pesanan ditahan.', 'sukses');
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
                            this.keranjang = d.item_pesanan;
                            this.namaPembeli = d.nama_pembeli;
                            this.catatan = d.catatan || '';
                            this.diskonPersen = d.diskon || '';
                            this.modalDitahanTerbuka = false;

                            // Kembalikan stok di frontend UI saat pesanan dipulihkan
                            if (d.item_pesanan) {
                                d.item_pesanan.forEach(item => {
                                    const p = this.daftarProduk.find(x => x.id === item.produk_id);
                                    if (p && p.status_ketersediaan !== 'pesan_dulu') {
                                        p.stok = parseFloat(p.stok) + parseFloat(item.jumlah);
                                    }
                                });
                            }

                            this.pesananSedangDilunaskan = null;
                            this.tambahToast('Pesanan dipulihkan.', 'sukses');
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
                            // Kembalikan stok di frontend UI saat pesanan ditahan dihapus
                            if (pesanan && pesanan.item_pesanan) {
                                pesanan.item_pesanan.forEach(item => {
                                    const p = this.daftarProduk.find(x => x.id === item.produk_id);
                                    if (p && p.status_ketersediaan !== 'pesan_dulu') {
                                        p.stok = parseFloat(p.stok) + parseFloat(item.jumlah);
                                    }
                                });
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
                            member_id: this.memberIdTerpilih || null
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
                const itemsDapur = d.status_pembayaran === 'Belum Lunas'
                    ? d.keranjang
                    : d.keranjang.filter(item => item.status_ketersediaan === 'pesan_dulu');
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
                this.keranjang = JSON.parse(JSON.stringify(pesanan.item_pesanan));
                this.namaPembeli = pesanan.nama_pembeli;
                this.diskonPersen = pesanan.diskon || '';
                this.pajakPersen = pesanan.pajak && pesanan.subtotal && (parseFloat(pesanan.subtotal) > parseFloat(pesanan.diskon || 0)) ? Math.round((parseFloat(pesanan.pajak) / (parseFloat(pesanan.subtotal) - parseFloat(pesanan.diskon || 0))) * 100) : 10;
                this.catatan = pesanan.catatan || '';
                if (pesanan.transaksi && pesanan.transaksi.member_id) {
                    this.memberIdTerpilih = pesanan.transaksi.member_id;
                    this.memberTerpilih = this.daftarMember.find(m => m.id == this.memberIdTerpilih);
                } else {
                    this.memberIdTerpilih = '';
                    this.memberTerpilih = null;
                }
                this.modalDitahanTerbuka = false;
                this.jumlahDiterima = '';
                this.halamanAktif = 'pembayaran';
                this.$nextTick(() => { if (this.$refs.inputJumlahDiterima) this.$refs.inputJumlahDiterima.focus(); });
            },

            cetakOrderDapurDitahan(pesanan) {
                this.suksesData = {
                    nama_pembeli: pesanan.nama_pembeli,
                    keranjang: JSON.parse(JSON.stringify(pesanan.item_pesanan)),
                    subtotal: pesanan.subtotal,
                    diskon_persen: pesanan.diskon || 0,
                    nilai_diskon: pesanan.diskon ? Math.round(pesanan.subtotal * (pesanan.diskon / 100)) : 0,
                    pajak_persen: pesanan.pajak && pesanan.subtotal && (parseFloat(pesanan.subtotal) > parseFloat(pesanan.diskon || 0)) ? Math.round((parseFloat(pesanan.pajak) / (parseFloat(pesanan.subtotal) - parseFloat(pesanan.diskon || 0))) * 100) : 10,
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
                this.cetakStruk('dapur');
            },

            bersihkanKeranjang() {
                this.keranjang = [];
                this.namaPembeli = '';
                this.catatan = '';
                this.diskonPersen = '';
                this.pajakPersen = 10;
                this.jumlahDiterima = '';
                this.memberIdTerpilih = '';
                this.memberTerpilih = null;
            },

            formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); },
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