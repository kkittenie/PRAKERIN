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
        $status = 'Kadaluwarsa';
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
    tabAktif: 'produk',
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

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $promo->nama_promo }}</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">
                    Dibuat pada {{ $promo->created_at ? $promo->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                </span>
            </div>
            <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $badgeColor }}" id="badgeStatusPromo" data-selesai="{{ $promo->selesai_berlaku ? $promo->selesai_berlaku->toIso8601String() : '' }}" data-status-aktif="{{ $promo->status_aktif ? '1' : '0' }}" data-mulai="{{ $promo->mulai_berlaku ? $promo->mulai_berlaku->toIso8601String() : '' }}">
                {{ $status }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jenis Potongan</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $promo->jenis_potongan === 'nominal' ? 'Nominal Rupiah (Rp)' : 'Persentase (%)' }}
                </p>
            </div>

            @if($promo->jenis_potongan === 'nominal')
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nominal Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        Rp {{ number_format($promo->nominal_potongan, 0, ',', '.') }}
                    </p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Persentase Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ $diskonText }}
                    </p>
                </div>
            @endif

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Minimal Belanja</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $promo->min_pembelian > 0 ? 'Rp ' . number_format($promo->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mulai Berlaku</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $promo->mulai_berlaku ? $promo->mulai_berlaku->format('d-m-Y H:i') : '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Selesai Berlaku</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $promo->selesai_berlaku ? $promo->selesai_berlaku->format('d-m-Y H:i') : 'Berlaku Selamanya' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $promo->created_at ? $promo->created_at->format('d-m-Y H:i') : '-' }}
                </p>
            </div>

        </div>

    </x-common.component-card>

    {{-- Tabs: Produk Berlaku --}}
    <x-common.component-card>
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="flex gap-6 -mb-px">
                <button type="button" @click="tabAktif = 'produk'"
                    :class="tabAktif === 'produk' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition">
                    Produk Berlaku ({{ $promo->produks->count() }})
                </button>
            </nav>
        </div>

        {{-- TAB 1: PRODUK BERLAKU --}}
        <div x-show="tabAktif === 'produk'">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">
                    Produk Yang Diberi Promo (<span x-text="semuaProduk.length"></span> Produk)
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

            <div x-show="semuaProduk.length === 0" class="py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
                Tidak ada produk yang menggunakan promo ini.
            </div>

            <div x-show="semuaProduk.length > 0">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="produk in produkDitampilkan" :key="produk.id">
                        <div class="flex flex-col bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/80 rounded-xl p-3">
                            <div class="w-full aspect-square rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
                                <template x-if="produk.foto">
                                    <img :src="fotoUrl(produk.foto)" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!produk.foto">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xxs font-bold uppercase bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 text-amber-700 dark:text-amber-400"
                                        x-text="produk.nama_produk.substring(0,2)"></div>
                                </template>
                            </div>
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-2 leading-tight" x-text="produk.nama_produk"></h4>
                                    <span class="text-xxs text-gray-400 dark:text-gray-500 mt-1 block" x-text="produk.kode_produk"></span>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex flex-col">
                                    <span class="text-[11px] text-gray-400 line-through" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
                                    <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400" x-text="'Rp ' + formatNumber(produk.harga_diskon)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="itemsPerPage !== 'semua' && totalHalamanProduk > 1"
                    class="flex flex-wrap items-center justify-between bg-gray-50 dark:bg-gray-800/40 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 mt-4 text-xs gap-2">
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
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('promo.index') }}"
                class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                Kembali
            </a>
            <a href="{{ route('promo.edit', $promo->id) }}"
                class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                Edit Promo
            </a>
        </div>

    </x-common.component-card>

</div>

@endsection

@push('scripts')
<script>
    // Jalan tiap detik: begitu promo ini lewat selesai_berlaku, badge status ganti
    // jadi "Kadaluwarsa" tanpa refresh.
    function perbaruiStatusDetailPromo() {
        const sekarang = new Date();
        const badge = document.getElementById('badgeStatusPromo');
        if (!badge) return;

        const selesai = badge.dataset.selesai;
        const mulai = badge.dataset.mulai;
        const statusAktif = badge.dataset.statusAktif === '1';
        const kadaluwarsa = !!selesai && sekarang > new Date(selesai);

        let label, warna;
        if (!statusAktif) {
            label = 'Nonaktif';
            warna = 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400';
        } else if (mulai && sekarang < new Date(mulai)) {
            label = 'Belum Mulai';
            warna = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
        } else if (kadaluwarsa) {
            label = 'Kadaluwarsa';
            warna = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
        } else {
            label = 'Aktif';
            warna = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
        }

        badge.className = 'shrink-0 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ' + warna;
        badge.textContent = label;

    }

    document.addEventListener('DOMContentLoaded', perbaruiStatusDetailPromo);
    setInterval(perbaruiStatusDetailPromo, 1000);
</script>
@endpush