@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Reward Voucher" parentTitle="Reward Voucher" :parentUrl="route('reward-voucher.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Reward Voucher">

        <form action="{{ route('reward-voucher.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Voucher <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                        placeholder="Contoh: Voucher Diskon 20%"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" rows="2"
                        placeholder="Contoh: Diskon 20% untuk pembelian minimal Rp 50.000 (tulis nilai/nominal vouchernya di sini)"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Nilai voucher (nominal/persen) ditulis di sini, bukan kolom terpisah.</span>
                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Poin Dibutuhkan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="poin_dibutuhkan" min="1" value="{{ old('poin_dibutuhkan') }}"
                            placeholder="Contoh: 100"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-16 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">poin</span>
                    </div>
                    @error('poin_dibutuhkan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok
                    </label>
                    <input type="number" name="stok" min="0" value="{{ old('stok') }}"
                        placeholder="Kosongkan jika tidak terbatas"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan = tidak terbatas.</span>
                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

<div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Gambar Reward
                    </label>

                    <div class="flex items-center gap-4">
                        <div class="w-24 h-24 shrink-0 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                            <img id="preview-gambar" src="" alt="Preview gambar reward" class="hidden w-full h-full object-cover">
                            <svg id="icon-gambar-kosong" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <label for="input-gambar"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 cursor-pointer transition">
                                Pilih Gambar
                            </label>
                            <input id="input-gambar" type="file" name="gambar" accept="image/*" class="hidden"
                                onchange="tampilkanPreviewGambar(this)">
                            <p id="nama-file-gambar" class="mt-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                                Belum ada gambar dipilih.
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">Ditampilkan di katalog reward member. Format jpg/png/webp, maks 2MB.</span>

                    @error('gambar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Program
                    </label>

                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktifkan Sekarang</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('reward-voucher.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Voucher
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

    <script>
        function tampilkanPreviewGambar(input) {
            const preview = document.getElementById('preview-gambar');
            const icon = document.getElementById('icon-gambar-kosong');
            const namaFile = document.getElementById('nama-file-gambar');

            if (input.files && input.files[0]) {
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('hidden');
                icon.classList.add('hidden');
                namaFile.textContent = input.files[0].name;
            }
        }
    </script>

@endsection