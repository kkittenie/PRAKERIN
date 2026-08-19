@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Promo Produk" />

    <div class="space-y-6" x-data="{
            search: @js(request('search', '')),
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
            },
            eventCocok(nama) {
                const keyword = this.search.trim().toLowerCase();
                if (keyword === '') return true;
                return nama.toLowerCase().includes(keyword);
            },
            eventAda() {
                const keyword = this.search.trim().toLowerCase();
                if (keyword === '') return true;
                return Array.from(
                    this.$root.querySelectorAll('[data-promo-event]')
                ).some(el => (el.dataset.nama || '').toLowerCase().includes(keyword));
            }
        }">

        <x-common.component-card title="Data Promo Produk">

            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
                <div class="relative w-full sm:w-80">
                    <input type="text"
                        x-model="search"
                        placeholder="Cari promo produk..."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">

                    <span class="absolute left-3 top-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.3-4.3m0 0A7 7 0 1110 3a7 7 0 016.7 13.7z" />
                        </svg>
                    </span>
                </div>

                <a href="{{ route('promo-event.create') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Promo Produk</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3.5 px-3 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                            <th class="py-3.5 px-2 sm:px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Event</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Produk</th>
                            <th class="py-3.5 px-3 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Isi Normal &rarr; Event</th>
                            <th class="py-3.5 px-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Harga Event</th>
                            <th class="py-3.5 px-3 hidden lg:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Periode</th>
                            <th class="py-3.5 px-3 hidden sm:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="py-3.5 px-2 sm:px-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    @forelse($promoEvent as $item)
                        @php
                            $now = now();
                            if (!$item->status_aktif) {
                                $status = 'Nonaktif';
                                $badgeColor = 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400';
                            } elseif ($now < $item->mulai_berlaku) {
                                $status = 'Belum Mulai';
                                $badgeColor = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
                            } elseif ($now > $item->selesai_berlaku) {
                                $status = 'Kedaluwarsa';
                                $badgeColor = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
                            } else {
                                $status = 'Aktif';
                                $badgeColor = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
                            }
                        @endphp
                        <tbody data-promo-event
                            data-nama="{{ $item->nama_event }}"
                            x-show="eventCocok(@js($item->nama_event))"
                            class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300">
                                <td class="py-3.5 px-3 hidden sm:table-cell text-gray-500">
                                    {{ $promoEvent->firstItem() + $loop->index }}</td>
                                <td class="py-3.5 px-2 sm:px-3 font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">
                                    <a href="{{ route('promo-event.show', $item->id) }}"
                                        class="hover:text-amber-600 dark:hover:text-amber-400 transition">
                                        {{ $item->nama_event }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-3 text-xs sm:text-sm">
                                    {{ $item->produk->nama_produk ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3 hidden md:table-cell text-center text-xs">
                                    {{ $item->isi_normal }} pcs &rarr; {{ $item->isi_event }} pcs
                                </td>
                                <td class="py-3.5 px-3 text-center text-brand-600 dark:text-brand-400 font-bold text-xs sm:text-sm whitespace-nowrap">
                                    Rp {{ number_format($item->harga_event, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 hidden lg:table-cell text-center text-xs">
                                    {{ $item->mulai_berlaku->format('d M Y H:i') }} -
                                    {{ $item->selesai_berlaku->format('d M Y H:i') }}
                                </td>
                                <td class="py-3.5 px-3 hidden sm:table-cell text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <form action="{{ route('promo-event.toggle-status', $item->id) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <label class="relative inline-flex items-center cursor-pointer"
                                                title="Klik untuk mengaktifkan/menonaktifkan">
                                                <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5">
                                                </div>
                                            </label>
                                        </form>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeColor }}">
                                            {{ $status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-2 sm:px-3 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('promo-event.show', $item->id) }}"
                                        class="text-blue-600 dark:text-blue-400 font-semibold hover:underline text-xs sm:text-sm">Detail</a>
                                    <a href="{{ route('promo-event.edit', $item->id) }}"
                                        class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                                    <form action="{{ route('promo-event.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama_event) }}')"
                                            class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-500 dark:text-gray-400">Belum ada data promo produk event</td>
                            </tr>
                        </tbody>
                    @endforelse

                    <tbody x-show="search.trim() !== '' && !eventAda()" x-cloak>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Promo produk tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $promoEvent->withQueryString()->links() }}
            </div>

        </x-common.component-card>

        <!-- ========== MODAL: KONFIRMASI HAPUS ========== -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                        ⚠
                    </div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Promo Produk?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Promo produk <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span>
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

@endsection