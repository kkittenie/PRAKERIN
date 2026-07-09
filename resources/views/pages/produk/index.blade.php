@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Produk" />

@if(session('success'))
<div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
    {{ session('error') }}
</div>
@endif

<div class="space-y-6">

<x-common.component-card title="Data Produk">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

        <form method="GET" action="{{ route('produk.index') }}" id="searchForm">            
        <div class="relative">
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari Produk..."
                    class="w-80 rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500">

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

        <a href="{{ route('produk.create') }}"
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

            Tambah Produk

        </a>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>
            <tr class="border-b bg-gray-50">

                <th class="py-4 text-left text-sm font-semibold text-gray-700">No</th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Foto
                </th>

                <th class="py-4 text-left text-sm font-semibold text-gray-700">
                    Kode Produk
                </th>

                <th class="py-4 text-left text-sm font-semibold text-gray-700">
                    Nama Produk
                </th>

                <th class="py-4 text-left text-sm font-semibold text-gray-700">
                    Kategori
                </th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Harga
                </th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Stok
                </th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Status
                </th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Ketersediaan
                </th>

                <th class="py-4 text-center text-sm font-semibold text-gray-700">
                    Aksi
                </th>

            </tr>
            </thead>

            <tbody>

            @forelse($produk as $item)

            <tr class="border-b hover:bg-gray-50 transition">

                {{-- No --}}
                <td class="py-4">
                    {{ $produk->firstItem() + $loop->index }}
                </td>

                {{-- Foto --}}
                <td class="py-4 text-center">

                @if($item->foto)

                <img
                src="{{ asset('storage/'.$item->foto) }}"
                class="w-14 h-14 rounded-lg object-cover border mx-auto">

                @else

                <div class="w-14 h-14 rounded-lg border flex items-center justify-center text-gray-400 text-xs mx-auto">

                -

                </div>

                @endif

                </td>

                {{-- Kode --}}
                <td class="py-4 font-medium">
                    {{ $item->kode_produk }}
                </td>

                {{-- Nama --}}
                <td class="py-4">
                    {{ $item->nama_produk }}
                </td>

                {{-- Kategori --}}
                <td class="py-4">
                    {{ $item->kategori->nama_kategori ?? '-' }}
                </td>

                {{-- Harga --}}
                <td class="py-4 text-right font-medium">

                Rp {{ number_format($item->harga_jual,0,',','.') }}

                </td>

                {{-- Stok --}}
                <td class="py-4 text-center">

                @if($item->stok <= $item->stok_minimum)

                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-red-700 text-sm font-medium">

                {{ $item->stok }}

                </span>

                @else

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-700 text-sm font-medium">

                {{ $item->stok }}

                </span>

                @endif

                <div class="text-xs text-gray-400 mt-1">

                Min {{ $item->stok_minimum }}

                </div>

                </td>

                {{-- Status --}}
                <td class="py-4 text-center">

                @if($item->status_aktif)

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-700 text-sm">

                Aktif

                </span>

                @else

                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-red-700 text-sm">

                Tidak Aktif

                </span>

                @endif

                </td>

                {{-- Ketersediaan --}}
                <td class="py-4 text-center">

                @if($item->status_ketersediaan=='stok_ada')

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-700 text-sm">

                Stok Ada

                </span>

                @else

                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-yellow-700 text-sm">

                Pesan Dulu

                </span>

                @endif

                </td>

                {{-- Aksi --}}
                <td class="py-4 text-center space-x-2">

                <a
                href="{{ route('produk.edit',$item->id) }}"
                class="text-yellow-500">

                Edit

                </a>

                    <form action="{{ route('produk.destroy',$item->id) }}"
                        method="POST"
                        class="inline">

                        @csrf
                        @method('DELETE')

                    <button
                    type="submit"
                    onclick="return confirm('Yakin ingin menghapus produk ini?')"
                    class="text-red-500">

                    Hapus

                    </button>

                    </form>

                </td>

            </tr>

            @empty

            <tr>

            <td colspan="10" class="py-10">

            <div class="flex flex-col items-center">

            <svg xmlns="http://www.w3.org/2000/svg"
            class="w-14 h-14 text-gray-300"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">

            <path stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4"/>

            </svg>

            <p class="mt-3 font-medium text-gray-500">

            Belum ada produk

            </p>

            <p class="text-sm text-gray-400">

            Silakan klik tombol Tambah Produk

            </p>

            </div>

            </td>

            </tr>

            @endforelse

            </tbody>

        </table>

        <div class="mt-6">

            {{ $produk->withQueryString()->links() }}

        </div>

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