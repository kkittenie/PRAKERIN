@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Promo Baru" parentTitle="Promo Produk" :parentUrl="route('promo.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Promo">

        <form action="{{ route('promo.store') }}" method="POST" x-data="{
            jenisPotongan: '{{ old('jenis_potongan', 'persentase') }}',
            nominalDisplay: '{{ old('nominal_potongan') ? number_format(old('nominal_potongan'), 0, ',', '.') : '' }}',
            minPembelianDisplay: '{{ old('min_pembelian') ? number_format(old('min_pembelian'), 0, ',', '.') : '0' }}',
            formatRupiahInput(val) {
                let number = (val || '').toString().replace(/\D/g, '');
                return number ? new Intl.NumberFormat('id-ID').format(number) : '';
            },
            getRawNumber(val) {
                return (val || '').toString().replace(/\D/g, '') || '0';
            }
        }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Nama Promo --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Promo <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_promo"
                        value="{{ old('nama_promo') }}"
                        placeholder="Contoh: Promo Merdeka, Diskon Spesial"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_promo')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Jenis Potongan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jenis Potongan Diskon <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4 items-center h-12">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="jenis_potongan" value="persentase" x-model="jenisPotongan" class="text-brand-500 focus:ring-brand-500">
                            Persentase (%)
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="jenis_potongan" value="nominal" x-model="jenisPotongan" class="text-brand-500 focus:ring-brand-500">
                            Nominal Rupiah (Rp)
                        </label>
                    </div>
                    @error('jenis_potongan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nilai Potongan Diskon --}}
                <div>
                    <div x-show="jenisPotongan === 'persentase'">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Persentase Diskon (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="persentase"
                            min="0"
                            max="100"
                            step="0.01"
                            value="{{ old('persentase') }}"
                            placeholder="Contoh: 15"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            :required="jenisPotongan === 'persentase'">
                        @error('persentase')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div x-show="jenisPotongan === 'nominal'" x-cloak>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nominal Diskon (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                            <input
                                type="text"
                                x-model="nominalDisplay"
                                @input="nominalDisplay = formatRupiahInput($event.target.value)"
                                placeholder="5.000"
                                class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold"
                                :required="jenisPotongan === 'nominal'">
                            <input type="hidden" name="nominal_potongan" :value="getRawNumber(nominalDisplay)">
                        </div>
                        @error('nominal_potongan')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Minimum Pembelian --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Minimum Pembelian (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            x-model="minPembelianDisplay"
                            @input="minPembelianDisplay = formatRupiahInput($event.target.value)"
                            placeholder="50.000 (Isi 0 jika tidak ada minimum)"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold">
                        <input type="hidden" name="min_pembelian" :value="getRawNumber(minPembelianDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Minimal total belanja agar promo bisa digunakan (0 = Tanpa Minimum)</span>
                    @error('min_pembelian')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mulai Berlaku <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="mulai_berlaku"
                        value="{{ old('mulai_berlaku', now()->format('Y-m-d\TH:i')) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                    @error('mulai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selesai Berlaku <span class="text-xs text-gray-400 font-normal"></span>
                    </label>
                    <input
                        type="datetime-local"
                        name="selesai_berlaku"
                        value="{{ old('selesai_berlaku') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan jika promo berlaku selamanya</span>
                    @error('selesai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif Promo --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Promo
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Promo Aktif</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Pilih Produk --}}
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Produk Yang Diberi Promo <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="pilihSemuaProduk(true)" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Pilih Semua</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="pilihSemuaProduk(false)" class="text-xs font-semibold text-gray-500 hover:text-gray-600">Hapus Semua</button>
                    </div>
                </div>

                @error('produk_ids')
                    <div class="mb-4"><small class="text-red-500">{{ $message }}</small></div>
                @enderror

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($produks as $produk)
                        <label class="relative flex flex-col bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/80 rounded-xl p-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150 select-none">
                            <input
                                type="checkbox"
                                name="produk_ids[]"
                                value="{{ $produk->id }}"
                                class="produk-checkbox absolute top-3 right-3 rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500"
                                {{ is_array(old('produk_ids')) && in_array($produk->id, old('produk_ids')) ? 'checked' : '' }}>
                            
                            <div class="w-full aspect-square rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
                                @if($produk->foto)
                                    <img src="{{ asset($produk->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xxs font-bold uppercase bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 text-amber-700 dark:text-amber-400">
                                        {{ substr($produk->nama_produk, 0, 2) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-2 leading-tight">{{ $produk->nama_produk }}</h4>
                                    <span class="text-xxs text-gray-400 dark:text-gray-500 mt-1 block">{{ $produk->kode_produk }}</span>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('promo.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Promo
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

<script>
function pilihSemuaProduk(status) {
    const checkboxes = document.querySelectorAll('.produk-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = status;
    });
}
</script>

@endsection
