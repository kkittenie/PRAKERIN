@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Jam Operasional" parentTitle="Data Master" />

<div class="space-y-6">

    <x-common.component-card title="Jam Operasional Pemesanan Member App">

        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Di luar jam ini member tidak bisa menambah produk ke keranjang maupun checkout.
        </p>

        <form action="{{ route('pengaturan-jam-operasional.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Jam Buka --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jam Buka <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="jam_buka"
                        value="{{ old('jam_buka', substr($pengaturan->jam_buka, 0, 5)) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-800/50"
                        @cannot('edit_jam_operasional') disabled @endcannot
                        required>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Termasuk jam ini (WIB)</span>
                    @error('jam_buka')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Jam Tutup --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jam Tutup <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="jam_tutup"
                        value="{{ old('jam_tutup', substr($pengaturan->jam_tutup, 0, 5)) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-800/50"
                        @cannot('edit_jam_operasional') disabled @endcannot
                        required>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Pesanan ditolak mulai jam ini (WIB)</span>
                    @error('jam_tutup')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            @can('edit_jam_operasional')
                <div class="flex justify-end gap-3">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                        Simpan
                    </button>
                </div>
            @else
                <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800/40 p-4 text-xs text-amber-800 dark:text-amber-300">
                    Akun Anda hanya memiliki hak akses untuk melihat pengaturan jam operasional (view-only).
                </div>
            @endcan

        </form>

    </x-common.component-card>

</div>

@endsection