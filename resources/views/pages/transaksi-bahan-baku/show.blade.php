@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Transaksi Bahan Baku" parentTitle="Transaksi Bahan Baku" :parentUrl="route('transaksi-bahan-baku.index')" />

<div class="space-y-6">

    <x-common.component-card title="Informasi Transaksi Bahan Baku">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Tanggal Transaksi --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tanggal Transaksi
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    {{ \Carbon\Carbon::parse(
                        $transaksiBahanBaku->tanggal
                    )->format('d-m-Y') }}

                </p>

            </div>


            {{-- Bahan Baku --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Bahan Baku
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    {{ $transaksiBahanBaku->bahanBaku->nama_bahan }}

                </p>

            </div>


            {{-- Jumlah Masuk --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Jumlah Masuk
                </p>

                <p class="font-semibold text-green-600 dark:text-green-400">

                    {{ \App\Models\Satuan::formatAngka(
                        $transaksiBahanBaku->jumlah,
                        $transaksiBahanBaku->bahanBaku->satuan?->tipe
                    ) }}

                    {{ $transaksiBahanBaku->bahanBaku->satuan?->nama }}

                </p>

            </div>


            {{-- Harga Satuan --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Harga Satuan
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    Rp {{ number_format(
                        $transaksiBahanBaku->harga_satuan,
                        0,
                        ',',
                        '.'
                    ) }}

                </p>

            </div>


            {{-- Total Harga --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Harga
                </p>

                <p class="font-semibold text-brand-500 dark:text-brand-400">

                    Rp {{ number_format(
                        $transaksiBahanBaku->total_harga,
                        0,
                        ',',
                        '.'
                    ) }}

                </p>

            </div>


            {{-- Supplier --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Supplier
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    {{ $transaksiBahanBaku->supplier ?: '-' }}

                </p>

            </div>


            {{-- Nomor Nota --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Nomor Nota
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    {{ $transaksiBahanBaku->nomor_nota ?: '-' }}

                </p>

            </div>


            {{-- Dicatat Oleh --}}
            <div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Dicatat Oleh
                </p>

                <p class="font-semibold text-gray-800 dark:text-white">

                    {{ $transaksiBahanBaku->user->name ?? '-' }}

                </p>

            </div>


            {{-- Keterangan --}}
            <div class="md:col-span-2">

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Keterangan
                </p>

                <p class="text-gray-800 dark:text-gray-200">

                    {{ $transaksiBahanBaku->keterangan ?: '-' }}

                </p>

            </div>

        </div>


        {{-- Tombol Kembali --}}
        <div class="mt-6">

            <a
                href="{{ route('transaksi-bahan-baku.index') }}"
                class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
            >

                Kembali

            </a>

        </div>

    </x-common.component-card>

</div>

@endsection