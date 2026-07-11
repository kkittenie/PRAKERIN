@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Produksi" />

<div class="space-y-6">

<x-common.component-card title="Form Produksi">

<form action="{{ route('produksi.store') }}" method="POST">

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Tanggal --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Tanggal Produksi
        </label>

        <input
            type="date"
            name="tanggal"
            value="{{ old('tanggal', date('Y-m-d')) }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
      
            @error('tanggal')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Produk --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Produk
        </label>

        <select
            name="produk_id"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 @error('produk_id') border-red-500 @enderror">
         
            <option value="">Pilih Produk</option>

            @foreach($produk as $item)

            <option
                value="{{ $item->id }}"
                {{ old('produk_id') == $item->id ? 'selected' : '' }}>

                {{ $item->nama_produk }}

            </option>

            @endforeach

        </select>

        @error('produk_id')
            <small class="text-red-500">{{ $message }}</small>
        @enderror

    </div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">

Pengambilan Bahan Baku

</h4>

<div class="overflow-x-auto">

<table class="w-full">

<thead>

<tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">

<th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">

Bahan Baku

</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">

Stok

</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">

Dipakai

</th>

</tr>

</thead>

<tbody>

@foreach($bahanBaku as $item)

<tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">

<td class="py-4">

{{ $item->nama_bahan }}

</td>

<td class="text-center">

{{ $item->stok }}

{{ $item->satuan }}

</td>

<td class="text-center">

<input
type="hidden"
name="bahan_baku_id[]"
value="{{ $item->id }}">

<input
type="number"
step="0.01"
min="0"
value="{{ old('jumlah_terpakai.'.$loop->index,0) }}"
name="jumlah_terpakai[]"
class="w-28 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-center focus:border-brand-500 focus:ring-brand-500">

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

    <hr class="my-8 border-gray-200 dark:border-gray-800">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">

        Jumlah Hasil Produksi

        </label>

        <input
        type="number"
        name="jumlah_hasil"
        value="{{ old('jumlah_hasil') }}"
        min="1"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('jumlah_hasil')
        <small class="text-red-500">{{ $message }}</small>
        @enderror

        </div>

        <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">

        Keterangan

        </label>

        <textarea
        name="keterangan"
        rows="3"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('keterangan') }}</textarea>

        @error('keterangan')
        <small class="text-red-500">{{ $message }}</small>
        @enderror
        </div>

        </div>

<div class="mt-8 flex justify-end gap-3">

<a
href="{{ route('produksi.index') }}"
class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

Batal

</a>

<button
type="submit"
class="rounded-lg bg-brand-500 px-5 py-2 text-white">
    Simpan Produksi
</button>

</div>

</form>

</x-common.component-card>

</div>

@endsection