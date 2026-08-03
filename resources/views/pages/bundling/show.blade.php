@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Detail Bundling" parentTitle="Bundling" :parentUrl="route('bundling.index')" />

    <div class="space-y-6">

        <x-common.component-card title="Informasi Bundling">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Gambar Bundling --}}
                <div class="md:col-span-1">
                    <img src="{{ $bundling->gambar ? asset('storage/'.$bundling->gambar) : 'https://via.placeholder.com/300x300?text=No+Image' }}"
                         class="w-full max-w-[220px] aspect-square rounded-xl object-cover border border-gray-200 dark:border-gray-700">
                </div>

                {{-- Info Bundling --}}
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Nama Bundling
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $bundling->nama_bundling }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Kode
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $bundling->kode_bundling }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Harga Bundling
                        </p>
                        <p class="font-semibold text-green-600 dark:text-green-400">
                            Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Jumlah Produk
                        </p>
                        <p class="font-semibold text-amber-600 dark:text-amber-400">
                            {{ $bundling->jumlah_produk }} pcs
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Deskripsi
                        </p>
                        <p class="text-gray-800 dark:text-gray-200">
                            {{ $bundling->deskripsi ?: '-' }}
                        </p>
                    </div>

                </div>

            </div>

        </x-common.component-card>

        <x-common.component-card title="Isi Bundling">

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                            <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Produk
                            </th>
                            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bundling->items as $item)
                            <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">
                                <td class="py-4">{{ $loop->iteration }}</td>
                                <td class="font-medium text-gray-800 dark:text-white">{{ $item->produk->nama_produk }}</td>
                                <td class="text-center font-bold text-amber-700 dark:text-amber-400">{{ $item->qty }} pcs</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($bundling->items as $item)
                    <div class="py-3 flex items-center justify-between">
                        <span class="font-bold text-gray-800 dark:text-white text-sm">{{ $item->produk->nama_produk }}</span>
                        <span
                            class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">
                            {{ $item->qty }} pcs
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end gap-3">

                <a href="{{ route('bundling.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Kembali
                </a>

                <a href="{{ route('bundling.edit', $bundling->id) }}"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Edit Bundling
                </a>

            </div>

        </x-common.component-card>

    </div>

@endsection