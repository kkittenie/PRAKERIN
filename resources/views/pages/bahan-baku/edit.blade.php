@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Bahan Baku" parentTitle="Bahan Baku" :parentUrl="route('bahan-baku.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Bahan Baku">

        <form action="{{ route('bahan-baku.update', $bahanBaku->id) }}" method="POST">
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
                        value="{{ old('nama_bahan', $bahanBaku->nama_bahan) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_bahan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Satuan --}}
                <div class="flex gap-2">

                    <select
                        id="satuan_id"
                        name="satuan_id"
                        class="flex-1 rounded-lg border">

                        @foreach($satuan as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->nama }}
                            </option>
                        @endforeach

                    </select>

                    <button
                        type="button"
                        id="btnTambahSatuan"
                        class="w-12 rounded-lg bg-brand-500 text-white text-xl">
                        +
                    </button>

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
                        value="{{ old('stok', $bahanBaku->stok) }}"
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
                        value="{{ old('stok_minimum', $bahanBaku->stok_minimum) }}"
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