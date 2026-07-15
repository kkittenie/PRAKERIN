@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Pegawai" />

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

<x-common.component-card title="Data Pegawai">

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

<form
    method="GET"
    action="{{ route('pegawai.index') }}"
    id="searchForm">

    <div class="relative">

        <input
            type="text"
            id="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari pegawai..."
            class="w-80 rounded-lg border border-gray-300 py-2 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500">

        <span class="absolute left-3 top-2.5 text-gray-400">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
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
    href="{{ route('pegawai.create') }}"
    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">

    <svg xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"/>

    </svg>

    Tambah Pegawai

</a>

</div>

<div class="overflow-x-auto">

<table class="w-full">

<thead>

<tr class="border-b bg-gray-50">

<th class="py-4 text-left text-sm font-semibold text-gray-700">
No
</th>

<th class="py-4 text-left text-sm font-semibold text-gray-700">
Nama
</th>

<th class="py-4 text-left text-sm font-semibold text-gray-700">
No HP
</th>

<th class="py-4 text-left text-sm font-semibold text-gray-700">
Jabatan
</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700">
Tanggal Masuk
</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700">
Status
</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700">
Aksi
</th>

</tr>

</thead>

<tbody>

@forelse($pegawai as $item)

<tr class="border-b hover:bg-gray-50 transition duration-200">

<td class="py-4">
{{ $pegawai->firstItem() + $loop->index }}
</td>

<td class="py-4">
{{ $item->nama }}
</td>

<td class="py-4">
{{ $item->no_hp }}
</td>

<td class="py-4">
{{ $item->jabatan }}
</td>

<td class="py-4 text-center">
{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d-m-Y') }}
</td>

<td class="py-4 text-center">

@if($item->status_aktif)

<span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
Aktif
</span>

@else

<span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
Tidak Aktif
</span>

@endif

</td>

<td class="py-4 text-center space-x-2">

<a
    href="{{ route('pegawai.edit',$item->id) }}"
    class="text-yellow-500">

    Edit

</a>

<form
    action="{{ route('pegawai.destroy',$item->id) }}"
    method="POST"
    class="inline">

    @csrf
    @method('DELETE')

    <button
        onclick="return confirm('Yakin ingin menghapus pegawai ini?')"
        class="text-red-600">

        Hapus

    </button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="7" class="py-10">

<div class="flex flex-col items-center">

<svg xmlns="http://www.w3.org/2000/svg"
    class="h-14 w-14 text-gray-300"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor">

<path
stroke-linecap="round"
stroke-linejoin="round"
stroke-width="1.5"
d="M17 20h5V4H2v16h5m10 0v-4H7v4m10 0H7"/>

</svg>

<p class="mt-3 font-medium text-gray-500">

Belum ada data pegawai

</p>

<p class="text-sm text-gray-400">

Silakan klik tombol Tambah Pegawai

</p>

</div>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

<div class="mt-6">

{{ $pegawai->withQueryString()->links() }}

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