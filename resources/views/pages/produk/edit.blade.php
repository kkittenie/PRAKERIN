@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Produk" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Produk">

        <form action="{{ route('produk.update',$produk->id) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Kategori --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Kategori Produk <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="kategori_id"
                        class="w-full rounded-lg border border-gray-300">

                        <option value="">Pilih Kategori</option>

                        @foreach($kategori as $item)
                            <option
                                value="{{ $item->id }}"
                                {{ old('kategori_id',$produk->kategori_id)==$item->id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach

                    </select>

                    @error('kategori_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Kode Produk --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Kode Produk
                    </label>

                    <input
                        type="text"
                        name="kode_produk"
                        value="{{ old('kode_produk',$produk->kode_produk) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('kode_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nama Produk --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        name="nama_produk"
                        value="{{ old('nama_produk',$produk->nama_produk) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('nama_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Harga Jual --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Harga Jual
                    </label>

                    <input
                        type="number"
                        name="harga_jual"
                        value="{{ old('harga_jual',$produk->harga_jual) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('harga_jual')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Harga Modal --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Harga Modal
                    </label>

                    <input
                        type="number"
                        name="harga_modal"
                        value="{{ old('harga_modal',$produk->harga_modal) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('harga_modal')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Stok Awal
                    </label>

                    <input
                        type="number"
                        name="stok"
                        value="{{ old('stok',$produk->stok) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Minimum --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Stok Minimum
                    </label>

                    <input
                        type="number"
                        name="stok_minimum"
                        value="{{ old('stok_minimum',$produk->stok_minimum) }}"
                        class="w-full rounded-lg border border-gray-300">

                    @error('stok_minimum')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Status Aktif
                    </label>

                    <select
                        name="status_aktif"
                        class="w-full rounded-lg border border-gray-300">

                        <option value="1" {{ old('status_aktif',$produk->status_aktif)==1 ? 'selected':'' }}>
                            Aktif
                        </option>

                        <option value="0" {{ old('status_aktif',$produk->status_aktif)==0 ? 'selected':'' }}>
                            Tidak Aktif
                        </option>

                    </select>
                </div>

                {{-- Status Ketersediaan --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Status Ketersediaan
                    </label>

                    <select
                        name="status_ketersediaan"
                        class="w-full rounded-lg border border-gray-300">

                        <option value="stok_ada"
                        {{ old('status_ketersediaan',$produk->status_ketersediaan)=='stok_ada' ? 'selected':'' }}>
                        Stok Ada
                        </option>

                        <option value="pesan_dulu"
                        {{ old('status_ketersediaan',$produk->status_ketersediaan)=='pesan_dulu' ? 'selected':'' }}>
                        Pesan Dulu
                        </option>

                    </select>
                </div>

                {{-- Foto --}}
                <div class="md:col-span-2">
                    <label class="block mb-2 font-medium">
                        Foto Produk
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="w-full rounded-lg border border-gray-300">

                    @error('foto')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('produk.index') }}"
                    class="rounded-lg border px-5 py-2">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update Produk

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@endsection