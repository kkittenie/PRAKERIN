@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Promo Produk" />

<div class="space-y-6">

    <x-common.component-card title="Data Promo">

        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <form method="GET" action="{{ route('promo.index') }}" id="searchForm" class="w-full sm:w-auto">            
                <div class="relative w-full">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari promo..."
                        class="w-full sm:w-80 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">

                    <span class="absolute left-3 top-3 text-gray-400">
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
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
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
                <span>Tambah Promo</span>
            </a>
        </div>

<!-- Unified Responsive Table -->
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="py-3.5 px-2 sm:hidden w-7 text-center"></th>
                <th class="py-3.5 px-3 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                <th class="py-3.5 px-2 sm:px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Promo</th>
                <th class="py-3.5 px-2 sm:px-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Potongan</th>
                <th class="py-3.5 px-3 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Min. Pembelian</th>
                <th class="py-3.5 px-3 hidden lg:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Mulai</th>
                <th class="py-3.5 px-3 hidden lg:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Selesai</th>
                <th class="py-3.5 px-3 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Produk</th>
                <th class="py-3.5 px-3 hidden sm:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                <th class="py-3.5 px-2 sm:px-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
            </tr>
        </thead>
        @forelse($promo as $item)
        @php
            $now = now();
            if (!$item->status_aktif) {
                $status = 'Nonaktif';
                $badgeColor = 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400';
            } elseif ($now < $item->mulai_berlaku) {
                $status = 'Belum Mulai';
                $badgeColor = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
            } elseif ($item->selesai_berlaku && $now > $item->selesai_berlaku) {
                $status = 'Kedaluwarsa';
                $badgeColor = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
            } else {
                $status = 'Aktif';
                $badgeColor = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
            }

            $diskonText = $item->jenis_potongan === 'nominal'
                ? 'Rp ' . number_format($item->nominal_potongan, 0, ',', '.')
                : number_format($item->persentase, 0) . '%';
        @endphp
        <tbody x-data="{ open: false }" class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300">
                <td class="py-3.5 px-2 sm:hidden text-center">
                    <button @click="open = !open" type="button" class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200" :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                        <span x-text="open ? '−' : '+'"></span>
                    </button>
                </td>
                <td class="py-3.5 px-3 hidden sm:table-cell text-gray-500">{{ $promo->firstItem() + $loop->index }}</td>
                <td class="py-3.5 px-2 sm:px-3 font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">
                    {{ $item->nama_promo }}
                </td>
                <td class="py-3.5 px-2 sm:px-3 text-center text-brand-600 dark:text-brand-400 font-bold text-xs sm:text-sm whitespace-nowrap">{{ $diskonText }}</td>
                <td class="py-3.5 px-3 hidden md:table-cell text-center text-xs">
                    {{ $item->min_pembelian > 0 ? 'Rp ' . number_format($item->min_pembelian, 0, ',', '.') : '-' }}
                </td>
                <td class="py-3.5 px-3 hidden lg:table-cell text-center text-xs">{{ $item->mulai_berlaku ? $item->mulai_berlaku->format('d M Y H:i') : '-' }}</td>
                <td class="py-3.5 px-3 hidden lg:table-cell text-center text-xs">
                    @if($item->selesai_berlaku)
                        {{ $item->selesai_berlaku->format('d M Y H:i') }}
                    @else
                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">Selamanya</span>
                    @endif
                </td>
                <td class="py-3.5 px-3 hidden md:table-cell text-center">
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/10 px-3 py-1 text-sm font-medium text-amber-800 dark:text-amber-400">
                        {{ $item->produks_count }} Produk
                    </span>
                </td>
                <td class="py-3.5 px-3 hidden sm:table-cell text-center">
                    <form action="{{ route('promo.toggle-status', $item->id) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" title="Klik untuk mengaktifkan/menonaktifkan promo" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-pointer hover:opacity-80 transition {{ $badgeColor }}">
                            {{ $status }}
                        </button>
                    </form>
                </td>
                <td class="py-3.5 px-2 sm:px-3 text-right space-x-1 whitespace-nowrap">
                    <a href="{{ route('promo.edit', $item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                    <form action="{{ route('promo.destroy', $item->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Yakin ingin menghapus promo ini?')" class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <!-- Expandable child row -->
            <tr x-show="open" x-cloak x-transition class="bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                <td colspan="10" class="p-4">
                    <div class="grid grid-cols-2 gap-3 text-gray-600 dark:text-gray-300">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Status</span>
                            <form action="{{ route('promo.toggle-status', $item->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badgeColor }} mt-0.5">
                                    {{ $status }}
                                </button>
                            </form>
                        </div>
                        <div><span class="text-gray-400 block text-[11px]">Min. Pembelian</span> <span class="font-bold text-gray-800 dark:text-white">{{ $item->min_pembelian > 0 ? 'Rp ' . number_format($item->min_pembelian, 0, ',', '.') : '-' }}</span></div>
                        <div><span class="text-gray-400 block text-[11px]">Jumlah Produk</span> <span class="font-bold text-amber-700 dark:text-amber-400">{{ $item->produks_count }} Produk</span></div>
                        <div><span class="text-gray-400 block text-[11px]">Periode Mulai</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->mulai_berlaku ? $item->mulai_berlaku->format('d M Y H:i') : '-' }}</span></div>
                        <div><span class="text-gray-400 block text-[11px]">Periode Selesai</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $item->selesai_berlaku ? $item->selesai_berlaku->format('d M Y H:i') : 'Selamanya' }}</span></div>
                    </div>
                </td>
            </tr>
        </tbody>
        @empty
        <tbody>
            <tr>
                <td colspan="10" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada data promo</td>
            </tr>
        </tbody>
        @endforelse
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
