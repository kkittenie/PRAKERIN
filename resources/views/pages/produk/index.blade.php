@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Produk" />

    @if(session('success'))
        <div
            class="mb-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            class="mb-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

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

        <x-common.component-card title="Data Produk">

            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
                <form method="GET" action="{{ route('produk.index') }}" id="searchForm" class="w-full sm:w-auto">
                    <div class="relative w-full">
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Cari produk..."
                            class="w-full sm:w-80 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">

                        <span class="absolute left-3 top-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z" />
                            </svg>
                        </span>
                    </div>
                </form>

                <a href="{{ route('produk.create') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Produk</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
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
                        <tbody x-data="{ open: false }"
                            class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <tr
                                class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition text-gray-700 dark:text-gray-300">
                                <td class="py-3 px-2 sm:hidden text-center">
                                    <button @click="open = !open" type="button"
                                        class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                        :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                                        <span x-text="open ? '−' : '+'"></span>
                                    </button>
                                </td>
                                <td class="py-3 px-3 hidden sm:table-cell text-center text-gray-500">
                                    {{ $produk->firstItem() + $loop->index }}</td>
                                <td class="py-3 px-3 hidden sm:table-cell text-center">
                                    @if($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}"
                                            class="w-10 h-10 rounded-lg object-cover border mx-auto">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs mx-auto">
                                            -</div>
                                    @endif
                                </td>
                                <td class="py-3 px-2 sm:px-3 font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">
                                    {{ $item->nama_produk }}
                                    <div class="sm:hidden text-[10px] text-gray-400 font-normal mt-0.5">
                                        {{ $item->kategori->nama_kategori ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 hidden lg:table-cell font-mono text-xs">{{ $item->kode_produk }}</td>
                                <td class="py-3 px-3 hidden md:table-cell">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                                <td
                                    class="py-3 px-2 sm:px-3 text-right font-bold text-amber-700 dark:text-amber-400 text-xs sm:text-sm whitespace-nowrap">
                                    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="py-3 px-3 hidden sm:table-cell text-center">
                                    @if($item->stok <= $item->stok_minimum)
                                        <span
                                            class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-2.5 py-0.5 text-xs font-bold text-red-700 dark:text-red-400">{{ $item->stok }}</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">{{ $item->stok }}</span>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-0.5">Min {{ $item->stok_minimum }}</div>
                                </td>
                                <td class="py-3 px-3 hidden md:table-cell text-center">
                                    @if($item->status_aktif)
                                        <span
                                            class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-2 py-0.5 text-xs text-green-700 dark:text-green-400">Aktif</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-2 py-0.5 text-xs text-red-700 dark:text-red-400">Off</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 hidden lg:table-cell text-center">
                                    @if($item->stok <= 0 || $item->status_ketersediaan == 'perlu_diproduksi')
                                        <span
                                            class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-xs text-orange-700 dark:text-orange-400">Perlu
                                            Diproduksi</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-700 dark:text-emerald-400">Ready</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 sm:px-3 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('produk.edit', $item->id) }}"
                                        class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                                    <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama_produk) }}')"
                                            class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Dropdown Row for Hidden Mobile Columns -->
                            <tr x-show="open" x-cloak x-transition
                                class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                                <td colspan="11" class="p-4">
                                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                                        @if($item->foto)
                                            <img src="{{ asset('storage/' . $item->foto) }}"
                                                class="w-16 h-16 rounded-xl object-cover border border-amber-200 dark:border-amber-800 shrink-0">
                                        @endif
                                        <div class="grid grid-cols-2 gap-3 text-gray-600 dark:text-gray-300 w-full">
                                            <div><span class="text-gray-400 block text-[11px]">Kode Produk</span> <span
                                                    class="font-mono font-bold text-gray-800 dark:text-white">{{ $item->kode_produk }}</span>
                                            </div>
                                            <div><span class="text-gray-400 block text-[11px]">Kategori</span> <span
                                                    class="font-semibold text-gray-800 dark:text-white">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                                            </div>
                                            <div><span class="text-gray-400 block text-[11px]">Stok</span> <span
                                                    class="font-bold {{ $item->stok <= $item->stok_minimum ? 'text-red-600' : 'text-green-600' }}">{{ $item->stok }}</span>
                                                <span class="text-gray-400 text-[10px]">(Min: {{ $item->stok_minimum }})</span>
                                            </div>
                                            <div><span class="text-gray-400 block text-[11px]">Status</span> <span
                                                    class="font-semibold {{ $item->status_aktif ? 'text-green-600' : 'text-red-600' }}">{{ $item->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="text-gray-400 block text-[11px]">Ketersediaan</span>
                                                @if($item->stok <= 0 || $item->status_ketersediaan == 'perlu_diproduksi')
                                                    <span class="font-semibold text-orange-600">Perlu Diproduksi</span>
                                                @else
                                                    <span class="font-semibold text-emerald-600">Stok Ada (Ready)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="11" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada produk
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            <div class="mt-6">
                {{ $produk->withQueryString()->links() }}
            </div>

        </x-common.component-card>

        <!-- ========== MODAL: KONFIRMASI HAPUS PRODUK ========== -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div
                        class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                        ⚠
                    </div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Produk?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Produk <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span>
                        akan dihapus dan tidak dapat dipulihkan kembali.
                    </p>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
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