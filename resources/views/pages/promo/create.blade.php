@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Promo Baru" />

@if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
@endif

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Promo">

        <form action="{{ route('promo.store') }}" method="POST">
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

                {{-- Persentase Diskon --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Persentase Diskon (%) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="persentase"
                        min="0"
                        max="100"
                        value="{{ old('persentase') }}"
                        placeholder="Contoh: 15"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('persentase')
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
                        value="{{ old('mulai_berlaku') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                    @error('mulai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selesai Berlaku <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="selesai_berlaku"
                        value="{{ old('selesai_berlaku') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                    @error('selesai_berlaku')
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
                                    <img src="{{ asset('storage/'.$produk->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xxs font-bold uppercase bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 text-indigo-700 dark:text-indigo-400">
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
