@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Pengaturan Pajak" parentTitle="Pengaturan Pajak" :parentUrl="route('pengaturan-pajak.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Pengaturan Pajak">

        <form action="{{ route('pengaturan-pajak.update', $pajak->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Nama Pajak --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Pajak <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_pajak"
                        value="{{ old('nama_pajak', $pajak->nama_pajak) }}"
                        placeholder="Contoh: PB1 / Pajak Restoran"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_pajak')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Persentase Pajak --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Persentase Pajak (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="persentase"
                            value="{{ old('persentase', $pajak->persentase) }}"
                            placeholder="Contoh: 10"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-10 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">%</span>
                    </div>
                    @error('persentase')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Catatan / Keterangan (Opsional)
                    </label>
                    <textarea
                        name="catatan"
                        rows="3"
                        placeholder="Informasi tambahan mengenai penerapan pajak..."
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">{{ old('catatan', $pajak->catatan) }}</textarea>
                    @error('catatan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif Pajak --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Pajak
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', $pajak->status_aktif ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Pajak Aktif</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('pengaturan-pajak.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-amber-700 px-5 py-2 text-white hover:bg-amber-800">
                    Perbarui Pajak
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection
