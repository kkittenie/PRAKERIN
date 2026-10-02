@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Varian Rasa" parentTitle="Varian Rasa" :parentUrl="route('varian-rasa.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Varian Rasa">

        <form action="{{ route('varian-rasa.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            {{-- Nama --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Varian Rasa
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="nama_rasa"
                    value="{{ old('nama_rasa') }}"
                    placeholder="Contoh : Coklat, Keju, Strawberry"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">

                @error('nama_rasa')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Gambar --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Gambar
                </label>

                <div class="flex items-center gap-4">
                    <div class="w-24 h-24 shrink-0 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                        <img id="preview-gambar" src="" alt="Preview gambar" class="hidden w-full h-full object-cover">
                        <svg id="icon-gambar-kosong" xmlns="http://www.w3.org/2000/svg" class=" w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <label for="input-gambar"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 cursor-pointer transition">
                            Pilih Gambar
                        </label>
                        <input id="input-gambar" type="file" name="gambar" accept="image/*" class="hidden" onchange="previewGambar(this)">
                        <p id="nama-file-gambar" class="mt-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                            Belum ada gambar dipilih.
                        </p>
                    </div>
                </div>

                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Format jpg/png, maks 2MB.</p>

                @error('gambar')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Aktif --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="status_aktif" value="0">
                    <input type="checkbox" name="status_aktif" value="1"
                        class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktif</span>
                </label>

                @error('status_aktif')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a
                    href="{{ route('varian-rasa.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection

@push('scripts')
    <script>
        function previewGambar(input) {
            const file = input.files[0];
            if (!file) return;

            const img = document.getElementById('preview-gambar');
            img.src = URL.createObjectURL(file);
            img.classList.remove('hidden');
            document.getElementById('icon-gambar-kosong').classList.add('hidden');
            document.getElementById('nama-file-gambar').textContent = file.name;
        }
    </script>
@endpush