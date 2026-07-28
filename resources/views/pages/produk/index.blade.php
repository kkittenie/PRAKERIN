@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Produk" />

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

<x-common.component-card title="Data Produk">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

        <form method="GET" action="{{ route('produk.index') }}" id="searchForm">            
        <div class="relative">
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari produk..."
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
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                <th class="py-3 px-2 sm:hidden w-7 text-center"></th>
                <th class="py-3 px-3 hidden sm:table-cell w-12 text-center">No</th>
                <th class="py-3 px-3 hidden sm:table-cell text-center">Foto</th>
                <th class="py-3 px-2 sm:px-3">Nama Produk</th>
                <th class="py-3 px-3 hidden lg:table-cell">Kode</th>
                <th class="py-3 px-3 hidden md:table-cell">Kategori</th>
                <th class="py-3 px-2 sm:px-3 text-right">Harga</th>
                <th class="py-3 px-3 hidden sm:table-cell text-center">Stok</th>
                <th class="py-3 px-3 hidden md:table-cell text-center">Status</th>
                <th class="py-3 px-3 hidden lg:table-cell text-center">Ketersediaan</th>
                <th class="py-3 px-2 sm:px-3 text-right">Aksi</th>
            </tr>
        </thead>
        @forelse($produk as $item)
        <tbody x-data="{ open: false }" class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition text-gray-700 dark:text-gray-300">
                <td class="py-3 px-2 sm:hidden text-center">
                    <button @click="open = !open" type="button" class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200" :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                        <span x-text="open ? '−' : '+'"></span>
                    </button>
                </td>
                <td class="py-3 px-3 hidden sm:table-cell text-center text-gray-500">{{ $produk->firstItem() + $loop->index }}</td>
                <td class="py-3 px-3 hidden sm:table-cell text-center">
                    @if($item->foto)
                    <img src="{{ asset('storage/'.$item->foto) }}" class="w-10 h-10 rounded-lg object-cover border mx-auto">
                    @else
                    <div class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs mx-auto">-</div>
                    @endif
                </td>
                <td class="py-3 px-2 sm:px-3 font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">
                    {{ $item->nama_produk }}
                    <div class="sm:hidden text-[10px] text-gray-400 font-normal mt-0.5">{{ $item->kategori->nama_kategori ?? '-' }}</div>
                </td>
                <td class="py-3 px-3 hidden lg:table-cell font-mono text-xs">{{ $item->kode_produk }}</td>
                <td class="py-3 px-3 hidden md:table-cell">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                <td class="py-3 px-2 sm:px-3 text-right font-bold text-amber-700 dark:text-amber-400 text-xs sm:text-sm whitespace-nowrap">Rp {{ number_format($item->harga_jual,0,',','.') }}</td>
                <td class="py-3 px-3 hidden sm:table-cell text-center">
                    @if($item->stok <= $item->stok_minimum)
                    <span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-2.5 py-0.5 text-xs font-bold text-red-700 dark:text-red-400">{{ $item->stok }}</span>
                    @else
                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">{{ $item->stok }}</span>
                    @endif
                    <div class="text-[10px] text-gray-400 mt-0.5">Min {{ $item->stok_minimum }}</div>
                </td>
                <td class="py-3 px-3 hidden md:table-cell text-center">
                    @if($item->status_aktif)
                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-2 py-0.5 text-xs text-green-700 dark:text-green-400">Aktif</span>
                    @else
                    <span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-2 py-0.5 text-xs text-red-700 dark:text-red-400">Off</span>
                    @endif
                </td>
                <td class="py-3 px-3 hidden lg:table-cell text-center">
                    @if($item->status_ketersediaan=='stok_ada')
                    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-700 dark:text-emerald-400">Ready</span>
                    @else
                    <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/10 px-2 py-0.5 text-xs text-amber-700 dark:text-amber-400">Pre-Order</span>
                    @endif
                </td>
                <td class="py-3 px-2 sm:px-3 text-right space-x-1 whitespace-nowrap">
                    <a href="{{ route('produk.edit',$item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                    <form action="{{ route('produk.destroy',$item->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                    </form>
                </td>
            </tr>

            <!-- Dropdown Row for Hidden Mobile Columns -->
            <tr x-show="open" x-cloak x-transition class="bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                <td colspan="11" class="p-4">
                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        @if($item->foto)
                        <img src="{{ asset('storage/'.$item->foto) }}" class="w-16 h-16 rounded-xl object-cover border border-amber-200 dark:border-amber-800 shrink-0">
                        @endif
                        <div class="grid grid-cols-2 gap-3 text-gray-600 dark:text-gray-300 w-full">
                            <div><span class="text-gray-400 block text-[11px]">Kode Produk</span> <span class="font-mono font-bold text-gray-800 dark:text-white">{{ $item->kode_produk }}</span></div>
                            <div><span class="text-gray-400 block text-[11px]">Kategori</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->kategori->nama_kategori ?? '-' }}</span></div>
                            <div><span class="text-gray-400 block text-[11px]">Stok</span> <span class="font-bold {{ $item->stok <= $item->stok_minimum ? 'text-red-600' : 'text-green-600' }}">{{ $item->stok }}</span> <span class="text-gray-400 text-[10px]">(Min: {{ $item->stok_minimum }})</span></div>
                            <div><span class="text-gray-400 block text-[11px]">Status</span> <span class="font-semibold {{ $item->status_aktif ? 'text-green-600' : 'text-red-600' }}">{{ $item->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span></div>
                            <div class="col-span-2"><span class="text-gray-400 block text-[11px]">Ketersediaan</span> <span class="font-semibold {{ $item->status_ketersediaan == 'stok_ada' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $item->status_ketersediaan == 'stok_ada' ? 'Stok Ada (Ready)' : 'Pesan Dulu (Pre-Order)' }}</span></div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        @empty
        <tbody>
            <tr>
                <td colspan="11" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada produk</td>
            </tr>
        </tbody>
        @endforelse
    </table>
</div>

<div class="mt-6">
    {{ $produk->withQueryString()->links() }}
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