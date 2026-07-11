@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Bahan Baku" />

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

    <x-common.component-card title="Data Bahan Baku">

        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">

        <form method="GET" action="{{ route('bahan-baku.index') }}" id="searchForm">            
        <div class="relative">
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari bahan baku..."
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
            

            <a href="{{ route('bahan-baku.create') }}"
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

            Tambah Bahan Baku

            </a>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>

                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">

                    <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>

                    <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Bahan Baku</th>

                    <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Satuan</th>

                    <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Stok</th>

                    <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($bahanBaku as $item)

                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition duration-200 text-gray-700 dark:text-gray-300">

                        <td class="py-4">
                            {{ $bahanBaku->firstItem() + $loop->index }}
                        </td>

                        <td class="py-4">

                            {{ $item->nama_bahan }}

                        </td>

                        <td class="py-4 text-center">

                            <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-400">

                                {{ $item->satuan }}

                            </span>

                        </td>

                        <td class="py-4 text-center">

                        @if($item->stok <= $item->stok_minimum)

                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-red-700 text-sm font-medium">
                                {{ rtrim(rtrim(number_format($item->stok, 2, '.', ''), '0'), '.') }}
                            </span>

                        @else

                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-700 text-sm font-medium">
                                {{ rtrim(rtrim(number_format($item->stok, 2, '.', ''), '0'), '.') }}
                            </span>

                        @endif

                        <div class="text-xs text-gray-400 mt-1">
                            Min {{ rtrim(rtrim(number_format($item->stok_minimum, 2, '.', ''), '0'), '.') }}
                        </div>

                        </td>

                        <td class="py-4 text-center space-x-2">

                            <a
                                href="{{ route('bahan-baku.edit',$item->id) }}"
                                class="text-yellow-500">

                                Edit

                            </a>

                            <form
                                action="{{ route('bahan-baku.destroy',$item->id) }}"
                                method="POST"
                                class="inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    onclick="return confirm('Yakin ingin menghapus?')"
                                    class="text-red-600">

                                    Hapus

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                    <td colspan="4" class="py-10">

                    <div class="flex flex-col items-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-14 h-14 text-gray-300 dark:text-gray-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4"/>

                    </svg>

                    <p class="mt-3 font-medium text-gray-500 dark:text-gray-400">

                    Belum ada bahan baku

                    </p>

                    <p class="text-sm text-gray-400">

                    Silakan klik tombol Tambah Bahan Baku

                    </p>

                    </div>

                    </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-6">

            {{ $bahanBaku->withQueryString()->links() }}

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