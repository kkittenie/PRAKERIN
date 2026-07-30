@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Produksi" />

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

<x-common.component-card title="Data Produksi">

<div class="flex flex-col gap-4 mb-6">

    {{-- Baris 1: Search + Tombol Tambah --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
        <form method="GET" action="{{ route('produksi.index') }}" id="searchForm" class="w-full sm:w-auto">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="tanggal_ref" value="{{ request('tanggal_ref') }}">
            <div class="relative w-full">
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari produksi..."
                    class="w-full sm:w-80 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                <span class="absolute left-3 top-3 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z"/>
                    </svg>
                </span>
            </div>
        </form>

        <a href="{{ route('produksi.create') }}"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Produksi</span>
        </a>
    </div>

    {{-- Baris 2: Filter Periode Laporan Produksi --}}
    <div x-data="{ periode: '{{ $periode }}' }"
        class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">

        <span class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500 tracking-wider">Filter Periode:</span>

        <form method="GET" action="{{ route('produksi.index') }}" id="periodeForm" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="search" value="{{ request('search') }}">

            <select name="periode" x-model="periode" @change="$nextTick(() => $el.form.submit())"
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="harian">Per Hari</option>
                <option value="mingguan">Per Minggu</option>
                <option value="bulanan">Per Bulan</option>
            </select>

            <input type="date" name="tanggal_ref"
                x-show="periode === 'harian'" :disabled="periode !== 'harian'"
                value="{{ $tanggalRef->toDateString() }}"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">

            <input type="week" name="tanggal_ref"
                x-show="periode === 'mingguan'" :disabled="periode !== 'mingguan'"
                value="{{ $tanggalRef->format('o-\WW') }}"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">

            <input type="month" name="tanggal_ref"
                x-show="periode === 'bulanan'" :disabled="periode !== 'bulanan'"
                value="{{ $tanggalRef->format('Y-m') }}"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </form>

        <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">
            @if($periode === 'harian')
                Menampilkan data tanggal <strong>{{ $tanggalRef->format('d-m-Y') }}</strong>
            @elseif($periode === 'mingguan')
                Menampilkan data <strong>{{ $tanggalRef->copy()->startOfWeek()->format('d-m-Y') }}</strong> s/d <strong>{{ $tanggalRef->copy()->endOfWeek()->format('d-m-Y') }}</strong>
            @else
                Menampilkan data bulan <strong>{{ $tanggalRef->translatedFormat('F Y') }}</strong>
            @endif
        </span>

        {{-- TODO (teman): taruh tombol "Cetak PDF" & "Export Excel" di sini.
             Gunakan query filter yang sama (periode, tanggal_ref, search) supaya hasil cetak/export
             mengikuti data yang lagi difilter di halaman ini. --}}
    </div>
</div>

<!-- Unified Responsive Table -->
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead>
<tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
<th class="py-4 px-2 sm:hidden w-8 text-center"></th>
<th class="py-4 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
<th class="py-4 hidden md:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
<th class="py-4 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Jam</th>
<th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Produk</th>
<th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Hasil</th>
<th class="py-4 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Kadaluarsa</th>
<th class="py-4 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Dibuat Oleh</th>
<th class="py-4 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Keterangan</th>
<th class="py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
</tr>
</thead>
@forelse($produksi as $item)
<tbody x-data="{ open: false }" class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
<tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition text-gray-700 dark:text-gray-300">
<td class="py-3.5 px-2 sm:hidden text-center">
    <button @click="open = !open" type="button" class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200" :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
        <span x-text="open ? '−' : '+'"></span>
    </button>
</td>
<td class="py-3.5 hidden sm:table-cell text-gray-500">{{ $produksi->firstItem() + $loop->index }}</td>
<td class="py-3.5 hidden md:table-cell">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
<td class="py-3.5 hidden lg:table-cell">{{ $item->jam_produksi ? \Carbon\Carbon::parse($item->jam_produksi)->format('H:i') : '-' }}</td>
<td class="py-3.5 font-semibold text-gray-900 dark:text-white">
    {{ $item->produk->nama_produk }}
    <div class="md:hidden text-[11px] text-gray-400 font-normal mt-0.5">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</div>
</td>
<td class="py-3.5 text-center">
<span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">
{{ $item->jumlah_hasil }} pcs
</span>
</td>
<td class="py-3.5 hidden lg:table-cell">{{ $item->kadaluarsa_label }}</td>
<td class="py-3.5 hidden lg:table-cell">{{ $item->user->name }}</td>
<td class="py-3.5 hidden lg:table-cell">{{ $item->keterangan ?: '-' }}</td>
<td class="py-3.5 text-right">
<a href="{{ route('produksi.show',$item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Detail</a>
</td>
</tr>
<!-- Expandable child row -->
<tr x-show="open" x-cloak x-transition class="bg-amber-50/40 dark:bg-amber-950/20 text-xs">
<td colspan="10" class="p-3.5">
    <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300 mb-3">
        <div><span class="text-gray-400">Tanggal:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span></div>
        <div><span class="text-gray-400">Jam Produksi:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->jam_produksi ? \Carbon\Carbon::parse($item->jam_produksi)->format('H:i') : '-' }}</span></div>
        <div><span class="text-gray-400">Kadaluarsa:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->kadaluarsa_label }}</span></div>
        <div><span class="text-gray-400">Dibuat Oleh:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->user->name }}</span></div>
        <div class="col-span-2"><span class="text-gray-400">Keterangan:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->keterangan ?: '-' }}</span></div>
    </div>

    <div class="mt-2">
        <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Bahan Baku Digunakan</p>
        <table class="w-full text-xs border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                    <th class="py-2 px-2 text-left w-10">No</th>
                    <th class="py-2 px-2 text-left">Nama Bahan</th>
                    <th class="py-2 px-2 text-right">Jumlah Dipakai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($item->detailBahan as $bahan)
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <td class="py-2 px-2">{{ $loop->iteration }}</td>
                    <td class="py-2 px-2">{{ $bahan->bahanBaku->nama_bahan ?? '-' }}</td>
                    <td class="py-2 px-2 text-right">
                        {{ $bahan->jumlah_terpakai }} {{ $bahan->bahanBaku->satuan->nama ?? '' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-2 px-2 text-center text-gray-400">Tidak ada data bahan baku.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</td>
</tr>
</tbody>
@empty
<tbody>
<tr>
<td colspan="10" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada data produksi untuk periode ini.</td>
</tr>
</tbody>
@endforelse
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
        timer = setTimeout(() => { searchForm.submit(); }, 400);
    });

    window.addEventListener('load', function () {
        if (searchInput.value !== '') {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    });
</script>

@endsection