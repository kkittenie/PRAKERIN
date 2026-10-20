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
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kategori</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $produk->kategori->nama_kategori ?? '-' }}
                        </p>
                    </div>

                    @php $varians = $produk->detailProduks; @endphp

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Jual</p>
                        <p class="font-semibold text-green-600 dark:text-green-400">
                            @if($varians->isNotEmpty())
                                @if($varians->min('harga_jual') == $varians->max('harga_jual'))
                                    Rp {{ number_format($varians->min('harga_jual'), 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($varians->min('harga_jual'), 0, ',', '.') }} &ndash; Rp {{ number_format($varians->max('harga_jual'), 0, ',', '.') }}
                                @endif
                            @else
                                <span class="text-gray-400 text-sm">Belum ada detail produk</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Modal</p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            @if($varians->isNotEmpty())
                                @if($varians->min('harga_modal') == $varians->max('harga_modal'))
                                    Rp {{ number_format($varians->min('harga_modal'), 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($varians->min('harga_modal'), 0, ',', '.') }} &ndash; Rp {{ number_format($varians->max('harga_modal'), 0, ',', '.') }}
                                @endif
                            @else
                                <span class="text-gray-400 text-sm">Belum ada detail produk</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stok</p>
                        @if($varians->isNotEmpty())
                            @php $totalStok = (int) $varians->sum('stok'); $totalMin = (int) $varians->sum('stok_minimum'); @endphp
                            <p class="font-semibold {{ $totalStok <= $totalMin ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $totalStok }}
                                @unless($produk->is_global)
                                    <span class="text-gray-400 font-normal text-xs">(Total {{ $varians->count() }} varian)</span>
                                @endunless
                            </p>
                        @else
                            <p class="text-gray-400 text-sm">Belum ada detail produk</p>
                        @endif
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
                            @if($varians->isNotEmpty())
                                @php $jumlahHabis = $varians->filter(fn ($v) => $v->perlu_diproduksi)->count(); @endphp
                                @if($jumlahHabis === $varians->count())
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">⚠ Sedang Diproduksi</span>
                                @elseif($jumlahHabis > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">{{ $jumlahHabis }} varian habis</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">✓ Semua varian tersedia</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </div>
                    </div>

                    @if($varians->isNotEmpty())
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Daftar Varian</p>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800/60">
                                    <tr class="text-left text-gray-500 dark:text-gray-400">
                                        <th class="py-2 px-3 w-16">Foto</th>
                                        <th class="py-2 px-3">Ukuran</th>
                                        <th class="py-2 px-3">Variasi</th>
                                        <th class="py-2 px-3">Kode</th>
                                        <th class="py-2 px-3">Harga Modal</th>
                                        <th class="py-2 px-3">Harga Jual</th>
                                        <th class="py-2 px-3">Stok</th>
                                        <th class="py-2 px-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($varians as $v)
                                        <tr class="border-t border-gray-100 dark:border-gray-800 {{ $v->status_aktif ? '' : 'opacity-50' }}">
                                            <td class="py-2 px-3">
                                                @if($v->foto_url)
                                                    <img src="{{ $v->foto_url }}" alt="Foto varian" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                                @else
                                                    <div class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs bg-gray-50 dark:bg-gray-800">-</div>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 font-semibold text-gray-800 dark:text-gray-200">{{ $v->ukuran ?? '-' }}</td>
                                            <td class="py-2 px-3 font-semibold text-gray-800 dark:text-gray-200">{{ $v->variasi ?? '-' }}</td>
                                            <td class="py-2 px-3 font-mono text-xs text-gray-500">{{ $v->kode_barcode }}</td>
                                            <td class="py-2 px-3">Rp {{ number_format($v->harga_modal, 0, ',', '.') }}</td>
                                            <td class="py-2 px-3 font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($v->harga_jual, 0, ',', '.') }}</td>
                                            <td class="py-2 px-3">
                                                <span class="font-semibold {{ $v->stok <= $v->stok_minimum ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $v->stok }}</span>
                                                <span class="text-gray-400 text-xs">(Min: {{ $v->stok_minimum }})</span>
                                            </td>
                                            <td class="py-2 px-3">
                                                @if(!$v->status_aktif)
                                                    <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700/50 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400">Nonaktif</span>
                                                @elseif($v->perlu_diproduksi)
                                                    <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">Habis</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">Tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

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