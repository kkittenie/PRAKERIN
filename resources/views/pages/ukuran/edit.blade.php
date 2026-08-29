@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Ukuran" parentTitle="Ukuran" :parentUrl="route('ukuran.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Ukuran">

        <form action="{{ route('ukuran.update', $ukuran->id) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="mb-6">

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Ukuran
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="nama_ukuran"
                    value="{{ old('nama_ukuran', $ukuran->nama_ukuran) }}"
                    placeholder="Contoh : Kecil, Sedang, Besar, Jumbo"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">

                @error('nama_ukuran')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

                        <div class="mb-6">

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kode Ukuran
                    <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="kode_ukuran"
                    value="{{ old('kode_ukuran', $ukuran->kode_ukuran) }}"
                    maxlength="10"
                    placeholder="Contoh : B, K, M"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 uppercase">

                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    Kode singkat unik untuk ukuran ini, dipakai buat bikin barcode label produksi (misal: Besar → B).
                </p>

                @error('kode_ukuran')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="mb-6">

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Urutan Tampil
                </label>

                <input
                    type="number"
                    name="urutan"
                    value="{{ old('urutan', $ukuran->urutan) }}"
                    min="0"
                    placeholder="0"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">

                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    Angka lebih kecil tampil lebih dulu.
                </p>

                @error('urutan')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('ukuran.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">

                    Batal

                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@endsection