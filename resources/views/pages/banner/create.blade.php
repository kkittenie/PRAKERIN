@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Banner" parentTitle="Banner" :parentUrl="route('banner.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Banner">

        <form action="{{ route('banner.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Judul --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Judul <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="judul" value="{{ old('judul') }}"
                        placeholder="Contoh: Promo Ramadhan"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    @error('judul')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Gambar --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Gambar Banner <span class="text-red-500">*</span>
                    </label>

                    <div class="flex items-center gap-4">
                        <button type="button" onclick="bukaCropDariPreview()"
                            class="group relative w-24 h-24 shrink-0 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                            <img id="preview-gambar" src="" alt="Preview gambar banner" class="hidden w-full h-full object-cover">
                            <svg id="icon-gambar-kosong" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span id="overlay-crop-preview" class="hidden absolute inset-0 items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 3v4a1 1 0 001 1h4M7 3H5a1 1 0 00-1 1v14a1 1 0 001 1h14a1 1 0 001-1v-2M7 3l10 10M17 21v-4a1 1 0 00-1-1H7" />
                                </svg>
                            </span>
                        </button>

                        <div class="flex-1 min-w-0">
                            <label for="input-gambar"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 cursor-pointer transition">
                                Pilih Gambar
                            </label>
                            <input id="input-gambar" type="file" name="gambar" accept="image/*" class="hidden"
                                onchange="bukaCropBanner(this)">
                            <p id="nama-file-gambar" class="mt-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                                Belum ada gambar dipilih.
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">Ditampilkan sebagai carousel di halaman utama mobile app. Disarankan landscape, format jpg/png, maks 2MB.</span>

                    @error('gambar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Urutan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Urutan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="urutan" value="{{ old('urutan', 0) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <small class="text-gray-400">Angka lebih kecil tampil lebih dulu di carousel.</small>
                    @error('urutan')
                        <small class="text-red-500 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Banner
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

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Periode Tampil - Mulai
                    </label>
                    <input type="date" name="tanggal_mulai_tampil" value="{{ old('tanggal_mulai_tampil') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    @error('tanggal_mulai_tampil')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Periode Tampil - Selesai
                    </label>
                    <input type="date" name="tanggal_selesai_tampil" value="{{ old('tanggal_selesai_tampil') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <small class="text-gray-400">Kosongkan kedua tanggal jika ingin banner tampil selamanya.</small>
                    @error('tanggal_selesai_tampil')
                        <small class="text-red-500 block">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('banner.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Batal
                </a>
                <button class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Banner
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@include('pages.banner.partials.crop-modal')

@endsection