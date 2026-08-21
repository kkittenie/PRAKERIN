@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Detail Produk" parentTitle="Produk" :parentUrl="route('produk.index')" />

    <div class="space-y-6">

        <x-common.component-card title="Informasi Produk">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Foto Produk --}}
                <div class="md:col-span-1">
                    @if($produk->foto)
                        <img src="{{ asset('storage/' . $produk->foto) }}"
                            class="w-full max-w-[220px] aspect-square rounded-xl object-cover border border-gray-200 dark:border-gray-700">
                    @else
                        <div
                            class="w-full max-w-[220px] aspect-square rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-sm">
                            No Image
                        </div>
                    @endif
                </div>

                {{-- Info Produk --}}
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Produk</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $produk->nama_produk }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kode Produk</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $produk->kode_produk }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kategori</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $produk->kategori->nama_kategori ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Jual</p>
                        <p class="font-semibold text-green-600 dark:text-green-400">
                            @if($produk->punya_ukuran)
                                Besar: Rp {{ number_format($produk->harga_besar, 0, ',', '.') }} &nbsp;/&nbsp;
                                Kecil: Rp {{ number_format($produk->harga_kecil, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Modal</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            Rp {{ number_format($produk->harga_modal, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stok</p>
                        <p class="font-semibold {{ $produk->stok <= $produk->stok_minimum ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ $produk->stok }}
                            <span class="text-gray-400 font-normal text-xs">(Min: {{ $produk->stok_minimum }})</span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <p class="mt-0.5">
                            @if($produk->status_aktif)
                                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/15 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700/50 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400">
                                    Nonaktif
                                </span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ketersediaan</p>
                        <div class="mt-0.5">
                            @if($produk->punya_ukuran)
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 w-12">Besar:</span>
                                        @if($produk->stok <= 0 || $produk->status_ketersediaan == 'perlu_diproduksi')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                                ⚠ Perlu Diproduksi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                                ✓ {{ $produk->stok }} tersedia
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 w-12">Kecil:</span>
                                        @if($produk->stok <= 0 || $produk->status_ketersediaan == 'perlu_diproduksi')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                                ⚠ Perlu Diproduksi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                                ✓ {{ $produk->stok }} tersedia
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                                        Stok Besar &amp; Kecil masih digabung (belum dipisah per ukuran).
                                    </p>
                                </div>
                            @else
                                @if($produk->stok <= 0 || $produk->status_ketersediaan == 'perlu_diproduksi')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                        ⚠ Perlu Diproduksi
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                        ✓ Stok Ada
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                        <p class="text-gray-800 dark:text-gray-200">
                            {{ $produk->deskripsi ?: '-' }}
                        </p>
                    </div>

                </div>

            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('produk.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Kembali
                </a>

                <a href="{{ route('produk.edit', $produk->id) }}"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Edit Produk
                </a>
            </div>

        </x-common.component-card>

    </div>

@endsection