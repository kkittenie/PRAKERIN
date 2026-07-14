@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Promo Produk" />

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

    <x-common.component-card title="Data Promo">

        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">

            <form method="GET" action="{{ route('promo.index') }}" id="searchForm">            
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari promo..."
                        class="w-80 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">

                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z"/>
                        </svg>
                    </span>
                </div>
            </form>

            <a href="{{ route('promo.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Promo
            </a>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                        <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Promo</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Persentase</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Mulai</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Selesai</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Produk</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($promo as $item)
                    @php
                        $now = now();
                        if ($now < $item->mulai_berlaku) {
                            $status = 'Belum Mulai';
                            $badgeColor = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
                        } elseif ($now >= $item->mulai_berlaku && $now <= $item->selesai_berlaku) {
                            $status = 'Aktif';
                            $badgeColor = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
                        } else {
                            $status = 'Kedaluwarsa';
                            $badgeColor = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
                        }
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition duration-200 text-gray-700 dark:text-gray-300">
                        <td class="py-4">
                            {{ $promo->firstItem() + $loop->index }}
                        </td>
                        <td class="py-4 font-semibold">
                            {{ $item->nama_promo }}
                        </td>
                        <td class="py-4 text-center text-brand-600 dark:text-brand-400 font-bold">
                            {{ number_format($item->persentase, 0) }}%
                        </td>
                        <td class="py-4 text-center text-xs">
                            {{ $item->mulai_berlaku->format('d M Y H:i') }}
                        </td>
                        <td class="py-4 text-center text-xs">
                            {{ $item->selesai_berlaku->format('d M Y H:i') }}
                        </td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-400">
                                {{ $item->produks_count }} Produk
                            </span>
                        </td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColor }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="py-4 text-center space-x-2">
                            <a href="{{ route('promo.edit', $item->id) }}" class="text-yellow-500 hover:text-yellow-600 font-medium">
                                Edit
                            </a>
                            <form action="{{ route('promo.destroy', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin ingin menghapus promo ini?')" class="text-red-600 hover:text-red-700 font-medium bg-transparent border-none cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-14 h-14 text-gray-300 dark:text-gray-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-3 font-medium text-gray-500 dark:text-gray-400">
                                    Belum ada data promo
                                </p>
                                <p class="text-sm text-gray-400">
                                    Silakan klik tombol Tambah Promo
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

        <div class="mt-6">
            {{ $promo->withQueryString()->links() }}
        </div>

    </x-common.component-card>

</div>

<script>
let timer;
const searchInput = document.getElementById('search');
const searchForm = document.getElementById('searchForm');

searchInput.addEventListener('keyup', function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
        searchForm.submit();
    }, 400);
});

window.addEventListener('load', function () {
    if (searchInput.value !== '') {
        searchInput.focus();
        searchInput.setSelectionRange(
            searchInput.value.length,
            searchInput.value.length
        );
    }
});
</script>

@endsection
