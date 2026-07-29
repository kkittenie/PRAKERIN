@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Aturan Member" parentTitle="Pengaturan Member" :parentUrl="route('pengaturan-member.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Aturan Member">

        <form action="{{ route('pengaturan-member.update', $pengaturanMember->id) }}" method="POST" x-data="{
            belanjaDisplay: '{{ old('minimal_belanja', $pengaturanMember->minimal_belanja) ? number_format(old('minimal_belanja', $pengaturanMember->minimal_belanja), 0, ',', '.') : '' }}',
            formatRupiahInput(val) {
                let number = (val || '').toString().replace(/\D/g, '');
                return number ? new Intl.NumberFormat('id-ID').format(number) : '';
            },
            getRawNumber(val) {
                return (val || '').toString().replace(/\D/g, '') || '0';
            }
        }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Nama Aturan --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Pengaturan <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_aturan"
                        value="{{ old('nama_aturan', $pengaturanMember->nama_aturan) }}"
                        placeholder="Contoh: Poin Belanja Harian, Poin Member Silver"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_aturan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Minimal Belanja --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Belanja Setiap (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            x-model="belanjaDisplay"
                            @input="belanjaDisplay = formatRupiahInput($event.target.value)"
                            placeholder="50.000"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold"
                            required>
                        <input type="hidden" name="minimal_belanja" :value="getRawNumber(belanjaDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kelipatan belanja agar member mendapat poin</span>
                    @error('minimal_belanja')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Poin --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Poin Didapat <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="poin"
                        min="0"
                        value="{{ old('poin', $pengaturanMember->poin) }}"
                        placeholder="Contoh: 1"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('poin')
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
                        value="{{ old('mulai_berlaku', $pengaturanMember->mulai_berlaku ? $pengaturanMember->mulai_berlaku->format('Y-m-d\TH:i') : '') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                    @error('mulai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selesai Berlaku
                    </label>
                    <input
                        type="datetime-local"
                        name="selesai_berlaku"
                        value="{{ old('selesai_berlaku', $pengaturanMember->selesai_berlaku ? $pengaturanMember->selesai_berlaku->format('Y-m-d\TH:i') : '') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan jika aturan berlaku selamanya</span>
                    @error('selesai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Aturan
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', $pengaturanMember->status_aktif ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aturan Aktif</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('pengaturan-member.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Perbarui
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection