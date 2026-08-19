@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Promo" parentTitle="Potongan Harga" :parentUrl="route('promo.index')" />

@php
    $now = now();
    if (!$promo->status_aktif) {
        $status = 'Nonaktif';
        $badgeColor = 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400';
    } elseif ($now < $promo->mulai_berlaku) {
        $status = 'Belum Mulai';
        $badgeColor = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
    } elseif ($promo->selesai_berlaku && $now > $promo->selesai_berlaku) {
        $status = 'Kedaluwarsa';
        $badgeColor = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
    } else {
        $status = 'Aktif';
        $badgeColor = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
    }

    if ($promo->jenis_potongan === 'nominal') {
        $diskonText = 'Rp ' . number_format($promo->nominal_potongan, 0, ',', '.');
    } else {
        $persentaseFormatted = rtrim(rtrim(number_format($promo->persentase, 2, ',', '.'), '0'), ',');
        $diskonText = $persentaseFormatted . '%';
    }
@endphp

<div class="space-y-6" x-data="{
    modalHapusTerbuka: false,
    semuaProduk: @js($promo->produks->map(function ($p) use ($promo) {
        if ($promo->jenis_potongan === 'nominal') {
            $diskon = $promo->nominal_potongan;
        } else {
            $diskon = (($promo->persentase ?? 0) / 100) * $p->harga_jual;
        }
        $hargaSetelahDiskon = max(0, $p->harga_jual - $diskon);

        return [
            'id' => $p->id,
            'nama_produk' => $p->nama_produk,
            'kode_produk' => $p->kode_produk,
            'kategori' => $p->kategori->nama_kategori ?? 'Umum',
            'foto' => $p->foto,
            'harga_jual' => $p->harga_jual,
            'harga_diskon' => $hargaSetelahDiskon,
        ];
    })->values()),
    itemsPerPage: '10',
    halamanProduk: 1,
    get totalHalamanProduk() {
        if (this.itemsPerPage === 'semua') return 1;
        const perPage = parseInt(this.itemsPerPage) || 10;
        return Math.max(1, Math.ceil(this.semuaProduk.length / perPage));
    },
    get produkDitampilkan() {
        if (this.itemsPerPage === 'semua') return this.semuaProduk;
        const perPage = parseInt(this.itemsPerPage) || 10;
        const start = (this.halamanProduk - 1) * perPage;
        return this.semuaProduk.slice(start, start + perPage);
    },
    fotoUrl(foto) {
        if (!foto) return '';
        if (foto.startsWith('http://') || foto.startsWith('https://')) return foto;
        return '/storage/' + foto;
    },
    formatNumber(v) {
        return new Intl.NumberFormat('id-ID').format(v);
    }
}">

    {{-- Detail Card --}}
    <x-common.component-card title="Informasi Promo">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-gray-800">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $promo->nama_promo }}</h2>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $badgeColor }}">
                        {{ $status }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Dibuat pada {{ $promo->created_at ? $promo->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('promo.edit', $promo->id) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Promo</span>
                </a>

                <form action="{{ route('promo.toggle-status', $promo->id) }}" method="POST" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 px-4 py-2 text-xs font-semibold transition">
                        <span>{{ $promo->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>
                </form>

                <form action="{{ route('promo.destroy', $promo->id) }}" method="POST" class="inline" id="formHapus">
                    @csrf @method('DELETE')
                    <button type="button" @click="modalHapusTerbuka = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-xs font-semibold transition">
                        <span>Hapus</span>
                    </button>
                </form>

                <a href="{{ route('promo.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 text-gray-600 dark:text-gray-300 px-4 py-2 text-xs font-semibold transition">
                    &laquo; Kembali
                </a>
            </div>
        </div>

        {{-- Detail Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-6">
            <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Besar Potongan</span>
                <p class="text-xl font-extrabold text-amber-700 dark:text-amber-400 mt-1">{{ $diskonText }}</p>
                <span class="text-[10px] text-gray-400 block mt-0.5">Tipe: {{ ucfirst($promo->jenis_potongan) }}</span>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Minimum Pembelian</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                    {{ $promo->min_pembelian > 0 ? 'Rp ' . number_format($promo->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                </p>
                <span class="text-[10px] text-gray-400 block mt-0.5">Syarat belanja minimum</span>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Mulai Berlaku</span>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                    {{ $promo->mulai_berlaku ? $promo->mulai_berlaku->format('d M Y, H:i') . ' WIB' : '-' }}
                </p>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Selesai Berlaku</span>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                    @if($promo->selesai_berlaku)
                        {{ $promo->selesai_berlaku->format('d M Y, H:i') . ' WIB' }}
                    @else
                        <span class="text-blue-600 dark:text-blue-400 font-semibold">Berlaku Selamanya</span>
                    @endif
                </p>
            </div>
        </div>
    </x-common.component-card>

    {{-- Associated Products Card --}}
    <x-common.component-card>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h3 class="text-base font-bold text-gray-800 dark:text-white">
                Produk Terkait (<span x-text="semuaProduk.length"></span> Produk)
            </h3>

            <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0" x-show="semuaProduk.length > 0">
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
        </div>

        <div x-show="semuaProduk.length === 0" class="py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
            Tidak ada produk yang menggunakan promo ini.
        </div>

        <div x-show="semuaProduk.length > 0">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <template x-for="produk in produkDitampilkan" :key="produk.id">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-3 flex flex-col justify-between shadow-xs">
                        <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden mb-2 relative">
                            <template x-if="produk.foto">
                                <img :src="fotoUrl(produk.foto)" class="w-full h-full object-cover" :alt="produk.nama_produk">
                            </template>
                            <template x-if="!produk.foto">
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs font-bold uppercase"
                                    x-text="produk.nama_produk.substring(0,2)">
                                </div>
                            </template>
                            <span class="absolute top-1 right-1 bg-rose-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                                -{{ $diskonText }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase" x-text="produk.kategori"></span>
                            <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-1 mt-0.5" x-text="produk.nama_produk"></h4>
                            <div class="mt-1 flex flex-col">
                                <span class="text-[11px] text-gray-400 line-through" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
                                <span class="text-xs font-extrabold text-amber-700 dark:text-amber-400" x-text="'Rp ' + formatNumber(produk.harga_diskon)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Product Pagination Bar -->
            <div x-show="itemsPerPage !== 'semua' && totalHalamanProduk > 1"
                class="flex flex-wrap items-center justify-between bg-gray-50 dark:bg-gray-800/40 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 mt-6 text-xs gap-2">
                <span class="text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-bold text-gray-800 dark:text-white" x-text="produkDitampilkan.length"></span> dari
                    <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
                </span>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="if(halamanProduk > 1) halamanProduk--" :disabled="halamanProduk <= 1"
                        class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                        &laquo; Prev
                    </button>
                    <span class="px-2 font-bold text-amber-600 dark:text-amber-400" x-text="halamanProduk + ' / ' + totalHalamanProduk"></span>
                    <button type="button" @click="if(halamanProduk < totalHalamanProduk) halamanProduk++" :disabled="halamanProduk >= totalHalamanProduk"
                        class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                        Next &raquo;
                    </button>
                </div>
            </div>
            <div x-show="itemsPerPage === 'semua'" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Menampilkan seluruh <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
            </div>
        </div>
    </x-common.component-card>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="modalHapusTerbuka" x-cloak x-transition
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapusTerbuka = false"
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                    ⚠
                </div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Promo?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Promo <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $promo->nama_promo }}</span>
                    akan dihapus dan tidak dapat dipulihkan kembali.
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                <button type="button" @click="modalHapusTerbuka = false"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                <button type="button" @click="document.getElementById('formHapus').submit()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
            </div>
        </div>
    </div>

</div>

@endsection
