@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Bundling" />

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

    <x-common.component-card title="Data Bundling">

        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <form method="GET" action="{{ route('bundling.index') }}" id="searchForm" class="w-full sm:w-auto">
                <div class="relative w-full">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari bundling..."
                        class="w-full sm:w-80 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">

                    <span class="absolute left-3 top-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z"/>
                        </svg>
                    </span>
                </div>
            </form>

            <a href="{{ route('bundling.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Bundling</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <th class="py-4 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                        <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama</th>
                        <th class="py-4 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Harga</th>
                        <th class="py-4 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Produk</th>
                        <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                @forelse($bundling as $item)
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition text-gray-700 dark:text-gray-300">
                        <td class="py-3.5 hidden sm:table-cell text-gray-500">{{ $bundling->firstItem() + $loop->index }}</td>
                        <td class="py-3.5 font-semibold text-gray-900 dark:text-white">
                            {{ $item->nama_bundling }}
                            <div class="md:hidden text-[11px] text-gray-400 font-normal mt-0.5">{{ $item->kode_bundling }} · {{ $item->jumlah_produk }} produk</div>
                        </td>
                        <td class="py-3.5 hidden md:table-cell text-center text-gray-500">{{ $item->kode_bundling }}</td>
                        <td class="py-3.5 text-center font-medium text-gray-800 dark:text-white">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-3.5 hidden md:table-cell text-center">{{ $item->jumlah_produk }} produk</td>
                        <td class="py-3.5 text-center space-x-1.5 whitespace-nowrap">
                            <a href="{{ route('bundling.show', $item->id) }}" class="text-gray-600 dark:text-gray-400 font-semibold hover:underline text-xs sm:text-sm">Detail</a>
                            <a href="{{ route('bundling.edit', $item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                            <form action="{{ route('bundling.destroy', $item->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Yakin ingin menghapus bundling ini?')" class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada bundling</td>
                    </tr>
                </tbody>
                @endforelse
            </table>
        </div>

        <div class="mt-6">
            {{ $bundling->withQueryString()->links() }}
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
</script>

@endsection