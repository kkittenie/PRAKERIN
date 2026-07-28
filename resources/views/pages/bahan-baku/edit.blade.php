@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Bahan Baku" parentTitle="Bahan Baku" :parentUrl="route('bahan-baku.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Bahan Baku">

        <form action="{{ route('bahan-baku.update', $bahan_baku->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nama Bahan --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Bahan <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_bahan"
                        value="{{ old('nama_bahan', $bahan_baku->nama_bahan) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_bahan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Satuan <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="satuan"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="">Pilih Satuan</option>
                        <option value="kg" {{ old('satuan')=='kg' ? 'selected':'' }}>Kg</option>
                        <option value="gram" {{ old('satuan')=='gram' ? 'selected':'' }}>Gram</option>
                        <option value="liter" {{ old('satuan')=='liter' ? 'selected':'' }}>Liter</option>
                        <option value="ml" {{ old('satuan')=='ml' ? 'selected':'' }}>mL</option>
                        <option value="pcs" {{ old('satuan')=='pcs' ? 'selected':'' }}>Pcs</option>

                    </select>

                    @error('satuan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="stok"
                        value="{{ old('stok', $bahan_baku->stok) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Minimum --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Minimum
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="stok_minimum"
                        value="{{ old('stok_minimum', $bahan_baku->stok_minimum) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok_minimum')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('bahan-baku.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update Bahan Baku

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@endsection