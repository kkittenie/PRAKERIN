@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Promo Produk" parentTitle="Promo Produk" :parentUrl="route('promo-event.index')" />

<div class="space-y-6">

<x-common.component-card title="Form Tambah Promo Produk">

<form action="{{ route('promo-event.store') }}" method="POST" x-data="{
        hargaDisplay: '{{ old('harga_event') ? number_format(old('harga_event'), 0, ',', '.') : '' }}',
        getRawNumber(val) {
            return (val || '').toString().replace(/\D/g, '') || '0';
        },
        handleRupiahInput(event, prop) {
            const input = event.target;
            const oldValue = input.value;
            const cursorPos = input.selectionStart;
            const digitsBeforeCursor = oldValue.slice(0, cursorPos).replace(/\D/g, '').length;

            const rawDigits = oldValue.replace(/\D/g, '');
            const formatted = rawDigits ? new Intl.NumberFormat('id-ID').format(rawDigits) : '';

            this[prop] = formatted;

            this.$nextTick(() => {
                let newPos = formatted.length;
                let count = 0;
                for (let i = 0; i < formatted.length; i++) {
                    if (/\d/.test(formatted[i])) {
                        count++;
                        if (count === digitsBeforeCursor) {
                            newPos = i + 1;
                            break;
                        }
                    }
                }
                if (digitsBeforeCursor === 0) newPos = 0;
                input.setSelectionRange(newPos, newPos);
            });
        },
        semuaProduk: @js($produks->map(function ($p) {
            return [
                'id' => $p->id,
                'nama_produk' => $p->nama_produk,
                'kode_produk' => $p->kode_produk,
                'foto' => $p->foto,
                'harga_jual' => $p->harga_jual,
            ];
        })->values()),
        produkTerpilih: {{ old('produk_id') ? (int) old('produk_id') : 'null' }},
        ukuranTerpilih: @js(old('ukuran_ids') ? array_map('intval', old('ukuran_ids')) : []),
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
        },
        pilihProduk(id) {
            this.produkTerpilih = id;
        }
    }">
@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    {{-- Nama Event --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nama Event <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            name="nama_event"
            value="{{ old('nama_event') }}"
            placeholder="Contoh: Donat Event 2pcs"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
        @error('nama_event')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Isi Normal --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Isi Normal (pcs/kemasan) <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            name="isi_normal"
            value="{{ old('isi_normal') }}"
            min="1"
            placeholder="Contoh: 6"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
        @error('isi_normal')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Isi Event --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Isi Saat Event (pcs/kemasan) <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            name="isi_event"
            value="{{ old('isi_event') }}"
            min="1"
            placeholder="Contoh: 2"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
        @error('isi_event')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Harga Event --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Harga Event (Rp) <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
            <input
                type="text"
                inputmode="numeric"
                autocomplete="off"
                x-model="hargaDisplay"
                @input="handleRupiahInput($event, 'hargaDisplay')"
                placeholder="10.000"
                class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold">
            <input type="hidden" name="harga_event" :value="getRawNumber(hargaDisplay)">
        </div>
        @error('harga_event')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Mulai Berlaku --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Mulai Berlaku <span class="text-red-500">*</span>
        </label>
        <input
            type="datetime-local"
            name="mulai_berlaku"
            value="{{ old('mulai_berlaku', now()->format('Y-m-d\TH:i')) }}"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('mulai_berlaku')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Selesai Berlaku --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Selesai Berlaku <span class="text-red-500">*</span>
        </label>
        <input
            type="datetime-local"
            name="selesai_berlaku"
            value="{{ old('selesai_berlaku') }}"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('selesai_berlaku')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status Aktif --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status
        </label>
        <label class="flex items-center gap-3 h-12 cursor-pointer">
            <input type="hidden" name="status_aktif" value="0">
            <input
                type="checkbox"
                name="status_aktif"
                value="1"
                class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktif</span>
        </label>
        @error('status_aktif')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

</div>

{{-- Pilih Produk --}}
<div class="mb-8">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pilih Produk <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0">
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

    <input type="hidden" name="produk_id" :value="produkTerpilih">

    @error('produk_id')
        <div class="mb-4"><small class="text-red-500">{{ $message }}</small></div>
    @enderror

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <template x-for="produk in produkDitampilkan" :key="produk.id">
            <label
                @click="pilihProduk(produk.id)"
                class="relative flex flex-col bg-gray-50 dark:bg-gray-800/40 border rounded-xl p-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150 select-none"
                :class="produkTerpilih === produk.id ? 'border-brand-500 ring-2 ring-brand-500' : 'border-gray-200 dark:border-gray-700/80'">

                <input
                    type="radio"
                    name="produk_id_radio"
                    :checked="produkTerpilih === produk.id"
                    class="absolute top-3 right-3 rounded-full border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">

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
            </label>
        </template>
    </div>

    <div x-show="semuaProduk.length === 0" class="py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
        Belum ada data produk
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

@if($ukurans->isNotEmpty())
{{-- Berlaku Untuk Ukuran --}}
<div class="mb-8">
    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Berlaku Untuk Ukuran
    </label>
    <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
        Kosongkan semua (jangan centang apa pun) supaya event berlaku untuk semua ukuran. Kalau produk yang dipilih
        di atas tidak punya varian ukuran, pilihan ini otomatis diabaikan.
    </p>
    <div class="flex flex-wrap gap-2">
        @foreach($ukurans as $ukuran)
            <label class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer select-none text-sm font-medium transition"
                :class="ukuranTerpilih.includes({{ $ukuran->id }}) ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                <input
                    type="checkbox"
                    name="ukuran_ids[]"
                    value="{{ $ukuran->id }}"
                    x-model.number="ukuranTerpilih"
                    class="rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">
                {{ $ukuran->nama_ukuran }}
            </label>
        @endforeach
    </div>
    @error('ukuran_ids')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
    @error('ukuran_ids.*')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>
@endif

{{-- Tombol Aksi --}}
<div class="flex justify-end gap-3">
    <a href="{{ route('promo-event.index') }}"
       class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
        Batal
    </a>
    <button type="submit"
        class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
        Simpan Promo Produk
    </button>
</div>

</form>

</x-common.component-card>

</div>

@endsection