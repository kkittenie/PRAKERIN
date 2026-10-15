@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Reward Stamp" parentTitle="Reward Stamp" :parentUrl="route('stamp-program.index')" />

<div class="space-y-6">

    @php
        $statusMap = [
            'Aktif' => 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400',
            'Nonaktif' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400',
            'Belum Mulai' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
            'Berakhir' => 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400',
            'Kuota Habis' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
        ];
        $statusClass = $statusMap[$program->status_saat_ini] ?? 'bg-gray-100 text-gray-600';
    @endphp

    <x-common.component-card title="Informasi Program">

        <div class="flex items-start justify-between gap-4 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $program->nama_program }}</h3>
            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                {{ $program->status_saat_ini }}
            </span>
        </div>

        @if($program->status_aktif && $program->status_saat_ini !== 'Aktif')
            <div class="mb-6 rounded-lg border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400 font-medium">
                ⚠ Program ini masih ditandai "Aktif" di sistem, tapi kondisinya sekarang
                <strong>{{ strtolower($program->status_saat_ini) }}</strong> sehingga tidak dipakai untuk transaksi.
                @if($program->status_saat_ini === 'Berakhir')
                    Ubah tanggal berakhir lewat menu Edit kalau mau melanjutkan program ini.
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $program->deskripsi ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Minimal Pembelian per Stamp</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    Rp {{ number_format($program->minimal_pembelian, 0, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Stamp Dibutuhkan</p>
                <p class="font-semibold text-brand-600 dark:text-brand-400">
                    {{ $program->jumlah_stamp_dibutuhkan }} stamp
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Maksimal Redeem</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    @if($program->maksimal_redeem)
                        {{ $program->jumlah_redeem ?? 0 }} / {{ $program->maksimal_redeem }} kartu
                    @else
                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium text-xs">Tidak terbatas</span>
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Sudah Ditukar</p>
                <p class="font-semibold text-green-600 dark:text-green-400">
                    {{ $program->jumlah_redeem ?? 0 }} kartu
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $program->tanggal_mulai ? $program->tanggal_mulai->format('d-m-Y') : 'Berlaku sejak dibuat' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Berakhir</p>
                <p class="font-semibold {{ $program->tanggal_berakhir && $program->tanggal_berakhir->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-white' }}">
                    {{ $program->tanggal_berakhir ? $program->tanggal_berakhir->format('d-m-Y') : 'Berlaku selamanya' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $program->created_at->format('d-m-Y H:i') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Terakhir Diubah</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $program->updated_at->format('d-m-Y H:i') }}
                </p>
            </div>

        </div>

    </x-common.component-card>

    <x-common.component-card title="Pilihan Hadiah">

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                        <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Produk</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah per Redeem</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Stok Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($program->hadiah as $item)
                        <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">
                            <td class="py-4">{{ $loop->iteration }}</td>
                            <td class="font-medium text-gray-800 dark:text-white">{{ $item->nama_tampil }}</td>
                            <td class="text-center font-bold text-amber-700 dark:text-amber-400">{{ $item->jumlah }}</td>
                            <td class="text-center">
                                @if(!is_null($item->stok_tersedia))
                                    <span class="{{ $item->stok_tersedia < $item->jumlah ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-300' }}">
                                        {{ $item->stok_tersedia }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-400">Belum ada produk hadiah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($program->hadiah as $item)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-gray-800 dark:text-white text-sm block">{{ $item->nama_tampil }}</span>
                        <span class="text-xs text-gray-400">Stok: {{ $item->stok_tersedia ?? '-' }}</span>
                    </div>
                    <span class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">
                        {{ $item->jumlah }}x
                    </span>
                </div>
            @empty
                <p class="py-6 text-center text-gray-500 dark:text-gray-400 text-sm">Belum ada produk hadiah.</p>
            @endforelse
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('stamp-program.index') }}"
                class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                Kembali
            </a>
            <a href="{{ route('stamp-program.edit', $program->id) }}"
                class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                Edit Program
            </a>
        </div>

    </x-common.component-card>

</div>

@endsection