@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Reward Voucher" parentTitle="Reward Voucher" :parentUrl="route('reward-voucher.index')" />

<div class="space-y-6" x-data="{
    itemsPerPage: '10',
    halamanProduk: 1,
    semuaProduk: @js($voucher->produks->map(function ($p) {
        return [
            'id' => $p->id,
            'nama_produk' => $p->nama_produk,
            'kode_produk' => $p->kode_produk,
            'foto' => $p->foto,
            'harga_jual' => $p->harga_jual,
        ];
    })->values()),
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

    @php
        $statusClass = $voucher->status_aktif
            ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400'
            : 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400';
    @endphp

    <x-common.component-card title="Informasi Voucher">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-hidden flex items-center justify-center">
                    @if($voucher->gambar)
                        <img src="{{ asset('storage/'.$voucher->gambar) }}" class="w-full h-full object-cover" alt="{{ $voucher->nama }}">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $voucher->nama }}</h3>
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 block mt-0.5">{{ $voucher->kode_reward }}</span>
                </div>
            </div>
            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                {{ $voucher->status_aktif ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->deskripsi ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Poin Dibutuhkan</p>
                <p class="font-semibold text-brand-600 dark:text-brand-400">
                    {{ number_format($voucher->poin_dibutuhkan, 0, ',', '.') }} poin
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jenis Potongan</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->jenis_potongan === 'nominal' ? 'Nominal Rupiah (Rp)' : 'Persentase (%)' }}
                </p>
            </div>

            @if($voucher->jenis_potongan === 'nominal')
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nominal Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        Rp {{ number_format($voucher->nominal_potongan, 0, ',', '.') }}
                    </p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Persentase Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ rtrim(rtrim(number_format($voucher->persentase, 2, ',', '.'), '0'), ',') }}%
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Maksimal Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ $voucher->maksimal_potongan ? 'Rp ' . number_format($voucher->maksimal_potongan, 0, ',', '.') : 'Tidak dibatasi' }}
                    </p>
                </div>
            @endif

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Minimal Belanja</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->min_pembelian > 0 ? 'Rp ' . number_format($voucher->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Masa Berlaku Voucher</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->masa_berlaku_hari ? $voucher->masa_berlaku_hari . ' hari setelah ditukar' : 'Tidak ada batas waktu' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stok</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    @if(is_null($voucher->stok))
                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium text-xs">Tidak terbatas</span>
                    @else
                        {{ $voucher->stok }}
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->created_at->format('d-m-Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Terakhir Diubah</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->updated_at->format('d-m-Y H:i') }}
                </p>
            </div>

        </div>

    </x-common.component-card>

    <x-common.component-card>

        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h3 class="text-base font-bold text-gray-800 dark:text-white">
                Produk Yang Diberi Reward Voucher (<span x-text="semuaProduk.length"></span> Produk)
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
            Belum ada produk yang dipilih.
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
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
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
            <div x-show="itemsPerPage === 'semua'" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Menampilkan seluruh <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('reward-voucher.index') }}"
                class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                Kembali
            </a>
            <a href="{{ route('reward-voucher.edit', $voucher->id) }}"
                class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                Edit Voucher
            </a>
        </div>

    </x-common.component-card>

</div>

@endsection