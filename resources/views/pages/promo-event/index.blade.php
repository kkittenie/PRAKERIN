@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Promo Produk" />

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
                if (this.formTerpilih) { this.formTerpilih.submit(); }
                this.modalHapusTerbuka = false;
            }
        }">

        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari promo produk...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
                <div class="sdt-toolbar-right">
                    <a href="{{ route('promo-event.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Promo Produk</span>
                    </a>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="promoEventTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Nama Event</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3 text-center">Isi Normal &rarr; Event</th>
                            <th class="py-3 text-center">Harga Event</th>
                            <th class="py-3 text-center">Periode</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promoEvent as $index => $item)
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
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-semibold text-gray-900 dark:text-white">
                                    <a href="{{ route('promo-event.show', $item->id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition">{{ $item->nama_event }}</a>
                                </td>
                                <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                                <td class="text-center text-xs">{{ $item->isi_normal }} pcs &rarr; {{ $item->isi_event }} pcs</td>
                                <td class="text-center font-bold text-brand-600 dark:text-brand-400 whitespace-nowrap">Rp {{ number_format($item->harga_event, 0, ',', '.') }}</td>
                                <td class="text-center text-xs">{{ $item->mulai_berlaku->format('d M Y H:i') }} - {{ $item->selesai_berlaku->format('d M Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <form action="{{ route('promo-event.toggle-status', $item->id) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan">
                                                <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                                            </label>
                                        </form>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeColor }}">
                                            {{ $status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-0.5 justify-end">
                                        <a href="{{ route('promo-event.show', $item->id) }}" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('promo-event.edit', $item->id) }}" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('promo-event.destroy', $item->id) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button" title="Hapus" @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama_event) }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="sdt-footer">
                <div class="sdt-info"></div>
                <div class="sdt-pagination"></div>
            </div>
        </div>

        <!-- MODAL HAPUS -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Promo Produk?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Promo produk <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dan tidak dapat dipulihkan kembali.
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalHapusTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiHapus()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('promoEventTable', {
            emptyText: 'Belum ada data promo produk event',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush