@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Transaksi Bahan Baku" parentTitle="Transaksi Bahan Baku" :parentUrl="route('transaksi-bahan-baku.index')" />

@if(session('success'))

<div class="mb-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-green-700 dark:text-green-400">

    {{ session('success') }}

</div>

@endif


@if(session('error'))

<div class="mb-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-red-700 dark:text-red-400">

    {{ session('error') }}

</div>

@endif


<div class="space-y-6">

<x-common.component-card title="Form Transaksi Bahan Baku">

<form
    action="{{ route('transaksi-bahan-baku.store') }}"
    method="POST"
>

@csrf


<div class="grid grid-cols-1 gap-6 md:grid-cols-2">

    {{-- Tanggal --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Tanggal

            <span class="text-red-500">*</span>

        </label>

        <input
            type="date"
            name="tanggal"
            value="{{ old('tanggal', date('Y-m-d')) }}"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

        @error('tanggal')

            <p class="mt-1 text-sm text-red-500">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- Bahan Baku --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Bahan Baku

            <span class="text-red-500">*</span>

        </label>

        <select
            name="bahan_baku_id"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

            <option value="">

                Pilih Bahan Baku

            </option>


            @foreach($bahanBaku as $item)

                <option
                    value="{{ $item->id }}"
                    {{ old('bahan_baku_id') == $item->id ? 'selected' : '' }}
                >

                    {{ $item->nama_bahan }}

                </option>

            @endforeach

        </select>

        @error('bahan_baku_id')

            <p class="mt-1 text-sm text-red-500">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- Jumlah --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Jumlah

            <span class="text-red-500">*</span>

        </label>

        <input
            type="number"
            name="jumlah"
            step="0.01"
            min="0.01"
            value="{{ old('jumlah') }}"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

        @error('jumlah')

            <p class="mt-1 text-sm text-red-500">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- Harga Satuan --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Harga Satuan

            <span class="text-red-500">*</span>

        </label>

        <input
            type="number"
            name="harga_satuan"
            step="0.01"
            min="0"
            value="{{ old('harga_satuan') }}"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

        @error('harga_satuan')

            <p class="mt-1 text-sm text-red-500">
                {{ $message }}
            </p>

        @enderror

    </div>


    {{-- Supplier --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Supplier

        </label>

        <input
            type="text"
            name="supplier"
            value="{{ old('supplier') }}"
            placeholder="Contoh: Toko Makmur"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

    </div>


    {{-- Nomor Nota --}}
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

            Nomor Nota

        </label>

        <input
            type="text"
            name="nomor_nota"
            value="{{ old('nomor_nota') }}"
            placeholder="Contoh: INV-001"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >

    </div>

</div>


{{-- Keterangan --}}
<div class="mt-6">

    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">

        Keterangan

    </label>

    <textarea
        name="keterangan"
        rows="3"
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
    >{{ old('keterangan') }}</textarea>

</div>


{{-- Total Harga di dalam form/card --}}
<div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">

    <div class="flex items-center justify-between">

        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">

            Total Harga

        </span>

        <span
            id="total-harga"
            class="text-lg font-semibold text-gray-800 dark:text-white"
        >

            Rp 0

        </span>

    </div>

</div>


{{-- Tombol --}}
<div class="mt-8 flex justify-end gap-3">

    <a
        href="{{ route('transaksi-bahan-baku.index') }}"
        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
    >

        Batal

    </a>


    <button
        type="submit"
        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600"
    >

        Simpan Transaksi

    </button>

</div>


</form>

</x-common.component-card>

</div>


<script>

const inputJumlah =
    document.querySelector('[name="jumlah"]');

const inputHarga =
    document.querySelector('[name="harga_satuan"]');

const totalHarga =
    document.getElementById('total-harga');


function hitungTotal()
{

    const jumlah =
        parseFloat(inputJumlah.value) || 0;

    const harga =
        parseFloat(inputHarga.value) || 0;

    const total =
        jumlah * harga;


    totalHarga.textContent =
        'Rp ' + total.toLocaleString('id-ID');

}


inputJumlah.addEventListener(
    'input',
    hitungTotal
);


inputHarga.addEventListener(
    'input',
    hitungTotal
);


window.addEventListener(
    'load',
    hitungTotal
);

</script>

@endsection