@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Produk" parentTitle="Produk" :parentUrl="route('produk.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Produk">

        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

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
                                {{ old('kategori_id')==$item->id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach

                    </select>

                    @error('kategori_id')
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
                        value="{{ old('nama_produk') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi Produk
                    </label>

                    <textarea
                        name="deskripsi"
                        rows="3"
                        placeholder="Deskripsi singkat produk, tampil di Detail Produk Member App"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi') }}</textarea>

                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Dijual Satuan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Dijual Satuan
                    </label>

                    <select
                        name="dijual_satuan"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="1" {{ old('dijual_satuan', '1') == '1' ? 'selected' : '' }}>Ya, bisa dijual satuan</option>
                        <option value="0" {{ old('dijual_satuan', '1') == '0' ? 'selected' : '' }}>Tidak, hanya untuk isi bundling</option>

                    </select>
                    <small class="text-gray-500 dark:text-gray-400">Produk yang tidak dijual satuan tidak muncul di kasir dan menu mobile, tapi tetap bisa dipilih di Bundling.</small>
                    @error('dijual_satuan')
                        <small class="text-red-500 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Produk
                    </label>

                    <div class="flex items-center gap-3 h-12">
                        <input type="hidden" name="status_aktif" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status_aktif" value="1" class="sr-only peer"
                                {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span>
                        </label>
                    </div>
                    <small class="text-gray-500 dark:text-gray-400">Jika dinonaktifkan, produk tidak akan muncul di menu kasir maupun aplikasi member.</small>
                    @error('status_aktif')
                        <small class="text-red-500 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Detail Produk (kode, ukuran, variasi, harga, stok per baris) --}}
                @include('pages.produk.partials.form-varian', [
                    'detailBaris' => $detailBaris,
                    'varianBaris' => $varianBaris ?? [],
                ])

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('produk.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Simpan Produk

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