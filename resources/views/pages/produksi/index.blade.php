    @extends('layouts.app')

    @section('content')

    <x-common.page-breadcrumb pageTitle="Produksi" />

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

    <x-common.component-card title="Data Produksi">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">

    <form method="GET" action="{{ route('produksi.index') }}" id="searchForm">

    <div class="relative">

    <input
    type="text"
    id="search"
    name="search"
    value="{{ request('search') }}"
    placeholder="Cari produksi..."
    class="w-80 rounded-lg border border-gray-300 py-2 pl-10 pr-4">

    <span class="absolute left-3 top-2.5 text-gray-400">

    <svg xmlns="http://www.w3.org/2000/svg"
    class="w-5 h-5"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor">

    <path
    stroke-linecap="round"
    stroke-linejoin="round"
    stroke-width="2"
    d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z"/>

    </svg>

    </span>

    </div>

    </form>

    <a
    href="{{ route('produksi.create') }}"
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
            Tambah Produksi
    </a>

    </div>

    <div class="overflow-x-auto">

    <table class="w-full">

    <thead>

    <tr class="border-b bg-gray-50">

    <th class="py-4 text-left text-sm font-semibold text-gray-700">No</th>

    <th class="py-4 text-left text-sm font-semibold text-gray-700">Tanggal</th>

    <th class="py-4 text-left text-sm font-semibold text-gray-700">Produk</th>

    <th class="py-4 text-center text-sm font-semibold text-gray-700">Hasil Produksi</th>

    <th class="py-4 text-left text-sm font-semibold text-gray-700">Dibuat Oleh</th>

    <th class="py-4 text-left text-sm font-semibold text-gray-700">Keterangan</th>

    <th class="py-4 text-center text-sm font-semibold text-gray-700">Aksi</th>

    </tr>

    </thead>

    <tbody>

    @forelse($produksi as $item)

    <tr class="border-b hover:bg-gray-50">

    <td class="py-4">
    {{ $produksi->firstItem() + $loop->index }}
    </td>

    <td>
    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
    </td>

    <td>
    {{ $item->produk->nama_produk }}
    </td>

    <td class="text-center">

    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-green-700">

    {{ $item->jumlah_hasil }}

    </span>

    </td>

    <td>

    {{ $item->user->name }}

    </td>

    <td>

    {{ $item->keterangan ?: '-' }}

    </td>

    <td class="text-center">

    <a
    href="{{ route('produksi.show',$item->id) }}"
    class="text-blue-600">

    Detail

    </a>

    </td>

    </tr>

    @empty

    <tr>

    <td colspan="7" class="py-10 text-center text-gray-500">

    Belum ada data produksi.

    </td>

    </tr>

    @endforelse

    </tbody>

    </table>

    </div>

    <div class="mt-6">

    {{ $produksi->withQueryString()->links() }}

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