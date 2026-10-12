@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Produk" parentTitle="Produk" :parentUrl="route('produk.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Produk">

        <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Kategori --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kategori Produk <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="kategori_id"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="">Pilih Kategori</option>

                        @foreach($kategori as $item)
                            <option
                                value="{{ $item->id }}"
                                {{ old('kategori_id', $produk->kategori_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach

                    </select>

                    @error('kategori_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Kode Produk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kode Produk <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="kode_produk"
                        value="{{ old('kode_produk', $produk->kode_produk) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('kode_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nama Produk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_produk"
                        value="{{ old('nama_produk', $produk->nama_produk) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi Produk
                    </label>

                    <textarea
                        name="deskripsi"
                        rows="3"
                        placeholder="Deskripsi singkat produk, tampil di Detail Produk Member App"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi', $produk->deskripsi) }}</textarea>

                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Varian Produk (ukuran / variasi, diketik bebas) --}}
                @include('pages.produk.partials.form-varian', [
                    'punyaVarian' => $punyaVarian,
                    'varianBaris' => $varianBaris,
                ])

                {{-- Harga Modal & Harga Jual --}}
                <div id="wrapper-harga-modal">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Modal <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_modal"
                            placeholder="8.000"
                            value="{{ number_format((int) old('harga_modal', $produk->harga_modal), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    @error('harga_modal')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div id="wrapper-harga-jual">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_jual"
                            placeholder="10.000"
                            value="{{ number_format((int) old('harga_jual', $produk->harga_jual), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    @error('harga_jual')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok --}}
                <div id="wrapper-stok">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Awal
                    </label>

                    <input
                        type="number"
                        name="stok"
                        placeholder="1"
                        value="{{ old('stok', $produk->stok) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Minimum --}}
                <div id="wrapper-stok-minimum">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Minimum
                    </label>

                    <input
                        type="number"
                        name="stok_minimum"
                        placeholder="1"
                        value="{{ old('stok_minimum', $produk->stok_minimum) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok_minimum')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Aktif
                    </label>

                    <select
                        name="status_aktif"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="1" {{ old('status_aktif', $produk->status_aktif)==1 ? 'selected':'' }}>
                            Aktif
                        </option>

                        <option value="0" {{ old('status_aktif', $produk->status_aktif)==0 ? 'selected':'' }}>
                            Tidak Aktif
                        </option>

                    </select>
                </div>

                {{-- Status Ketersediaan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Ketersediaan
                    </label>

                    <select
                        name="status_ketersediaan"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="stok_ada"
                        {{ old('status_ketersediaan', $produk->status_ketersediaan)=='stok_ada' ? 'selected':'' }}>
                        Stok Ada
                        </option>

                        <option value="perlu_diproduksi"
                        {{ old('status_ketersediaan', $produk->status_ketersediaan)=='perlu_diproduksi' ? 'selected':'' }}>
                        Perlu Diproduksi
                        </option>

                    </select>

                    <small class="text-gray-400">
                        Otomatis menjadi "Perlu Diproduksi" jika stok 0.
                    </small>
                </div>

                {{-- Foto --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Foto Produk
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('foto')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('produk.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update Produk

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

    <script>

    document.querySelectorAll('.input-rupiah').forEach(function (input) {

        input.addEventListener('input', function (e) {

            const cursorPos = input.selectionStart;
            const digitSebelumKursor = input.value.slice(0, cursorPos).replace(/\D/g, '').length;

            let digits = input.value.replace(/\D/g, '');
            const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            input.value = formatted;

            let jumlahDigit = 0;
            let posisiBaru = formatted.length;

            for (let i = 0; i < formatted.length; i++) {
                if (/\d/.test(formatted[i])) {
                    jumlahDigit++;
                }
                if (jumlahDigit === digitSebelumKursor) {
                    posisiBaru = i + 1;
                    break;
                }
            }

            if (digitSebelumKursor === 0) {
                posisiBaru = 0;
            }

            input.setSelectionRange(posisiBaru, posisiBaru);
        });
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.input-rupiah').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });
        });
    });
    </script>

@endsection