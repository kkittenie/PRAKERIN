@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Bahan Baku" />

<div class="space-y-6" x-data="{
        modalHapusTerbuka: false,
        formTerpilih: null,
        namaHapus: '',
        bukaModalHapus(form, nama) {
            this.formTerpilih = form;
            this.namaHapus = nama;
            this.modalHapusTerbuka = true;
        },
        konfirmasiHapus() {
            if (this.formTerpilih) {
                this.formTerpilih.submit();
            }
            this.modalHapusTerbuka = false;
        }
    }">

    <x-common.component-card title="Data Bahan Baku">

        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <form method="GET" action="{{ route('bahan-baku.index') }}" id="searchForm" class="w-full sm:w-auto">            
                <div class="relative w-full">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari bahan baku..."
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

            <a href="{{ route('bahan-baku.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Bahan Baku</span>
            </a>
        </div>

<!-- Unified Responsive Table -->
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <th class="py-4 px-2 sm:hidden w-8 text-center"></th>
            <th class="py-4 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
            <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Bahan Baku</th>
            <th class="py-4 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Satuan</th>
            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Stok</th>
            <th class="py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
            </tr>
        </thead>
        @forelse($bahanBaku as $item)
        <tbody x-data="{ open: false }" class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300">
                <td class="py-3.5 px-2 sm:hidden text-center">
                    <button @click="open = !open" type="button" class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200" :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                        <span x-text="open ? '−' : '+'"></span>
                    </button>
                </td>
                <td class="py-3.5 hidden sm:table-cell text-gray-500">{{ $bahanBaku->firstItem() + $loop->index }}</td>
                <td class="py-3.5 font-semibold text-gray-900 dark:text-white">
                    {{ $item->nama_bahan }}
                    <div class="md:hidden text-[11px] text-gray-400 font-normal mt-0.5">{{ optional($item->satuan)->nama ?? '-' }}</div>
                </td>
                <td class="py-3.5 hidden md:table-cell text-center">
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/10 px-3 py-1 text-sm font-medium text-amber-800 dark:text-amber-400">
                        {{ optional($item->satuan)->nama ?? '-' }}
                    </span>
                </td>
                <td class="py-3.5 text-center">
                @if($item->stok <= $item->stok_minimum)
                    <span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-3 py-1 text-red-700 dark:text-red-400 text-xs font-bold">
                        {{ rtrim(rtrim(number_format($item->stok, 2, '.', ''), '0'), '.') }}
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">
                        {{ rtrim(rtrim(number_format($item->stok, 2, '.', ''), '0'), '.') }}
                    </span>
                @endif
                <div class="text-[10px] text-gray-400 mt-0.5">
                    Min {{ rtrim(rtrim(number_format($item->stok_minimum, 2, '.', ''), '0'), '.') }}
                </div>
                </td>
                <td class="py-3.5 text-right space-x-1.5 whitespace-nowrap">
                    <a href="{{ route('bahan-baku.edit',$item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                    <form action="{{ route('bahan-baku.destroy',$item->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="button"
                            @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama_bahan) }}')"
                            class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <!-- Expandable child row -->
            <tr x-show="open" x-cloak x-transition class="bg-amber-50/40 dark:bg-amber-950/20 text-xs">
                <td colspan="6" class="p-3.5">
                    <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300">
                        <div><span class="text-gray-400">Satuan:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ optional($item->satuan)->nama ?? '-' }}</span></div>
                        <div><span class="text-gray-400">Stok Minimum:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ rtrim(rtrim(number_format($item->stok_minimum, 2, '.', ''), '0'), '.') }}</span></div>
                    </div>
                </td>
            </tr>
        </tbody>
        @empty
        <tbody>
            <tr>
                <td colspan="6" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada bahan baku</td>
            </tr>
        </tbody>
        @endforelse
    </table>
</div>

        <div class="mt-6">

            {{ $bahanBaku->withQueryString()->links() }}

        </div>

    </x-common.component-card>

    <!-- ========== MODAL: KONFIRMASI HAPUS BAHAN BAKU ========== -->
    <div x-show="modalHapusTerbuka" x-cloak x-transition
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapusTerbuka = false"
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                    ⚠
                </div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Bahan Baku?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Bahan baku <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span>
                    akan dihapus dan tidak dapat dipulihkan kembali.
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                <button type="button" @click="modalHapusTerbuka = false"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                <button type="button" @click="konfirmasiHapus()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
            </div>
        </div>
    </div>

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