@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Reward Produk" parentTitle="Reward Produk" :parentUrl="route('reward.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Reward Produk">

        <form action="{{ route('reward.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Informasi Reward --}}
                <div class="md:col-span-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Informasi Reward</h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                {{-- Nama Reward --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Reward <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_reward"
                        value="{{ old('nama_reward') }}"
                        placeholder="Contoh: Gratis Butter Croissant"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_reward')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Produk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Produk <span class="text-red-500">*</span>
                    </label>
                    <select name="produk_id"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($produks as $produk)
                            <option value="{{ $produk->id }}" {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Produk yang akan ditukar member menggunakan poin</span>
                    @error('produk_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Kategori Reward --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kategori Reward
                    </label>
                    <select name="tipe_reward"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                        <option value="produk_gratis" {{ old('tipe_reward', 'produk_gratis') == 'produk_gratis' ? 'selected' : '' }}>Produk Gratis</option>
                        <option value="produk_diskon" {{ old('tipe_reward') == 'produk_diskon' ? 'selected' : '' }}>Produk Diskon</option>
                    </select>
                    @error('tipe_reward')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Pengaturan Penukaran --}}
                <div class="md:col-span-2 mt-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Pengaturan Penukaran</h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                {{-- Poin Dibutuhkan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah Poin Dibutuhkan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            name="poin_dibutuhkan"
                            min="1"
                            value="{{ old('poin_dibutuhkan') }}"
                            placeholder="Contoh: 100"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-14 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">poin</span>
                    </div>
                    @error('poin_dibutuhkan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Maksimal Redeem --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Maksimal Redeem
                    </label>
                    <input
                        type="number"
                        name="maksimal_redeem"
                        min="1"
                        value="{{ old('maksimal_redeem') }}"
                        placeholder="Kosongkan jika tidak terbatas"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Total maksimal reward ini boleh ditukar oleh seluruh member. Kosongkan = tidak terbatas.</span>
                    @error('maksimal_redeem')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Pengaturan Stok Reward --}}
                <div class="md:col-span-2 mt-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Pengaturan Stok Reward</h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                {{-- Stok Reward --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Reward <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="stok_reward"
                        min="0"
                        value="{{ old('stok_reward') }}"
                        placeholder="Contoh: 50"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Membatasi jumlah reward yang bisa ditukar. Jika sudah 0, reward otomatis berstatus "Habis".</span>
                    @error('stok_reward')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Masa Berlaku Reward --}}
                <div class="md:col-span-2 mt-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Masa Berlaku Reward <span class="font-normal text-gray-400 normal-case">(opsional)</span></h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Mulai
                    </label>
                    <input
                        type="date"
                        name="tanggal_mulai"
                        value="{{ old('tanggal_mulai') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    @error('tanggal_mulai')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Berakhir --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Berakhir
                    </label>
                    <input
                        type="date"
                        name="tanggal_berakhir"
                        value="{{ old('tanggal_berakhir') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan jika reward berlaku selamanya</span>
                    @error('tanggal_berakhir')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Reward
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Reward Aktif</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('reward.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Reward
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection