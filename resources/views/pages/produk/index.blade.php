@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Produk" />

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
                        <input type="text" class="sdt-search-input" placeholder="Cari nama, kode, kategori...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>
                    </div>
                </div>
                <div class="sdt-toolbar-right">
                    @can('tambah_produk')
                    <a href="{{ route('produk.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Produk</span>
                    </a>
                    @endcan
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="produkTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3.5 text-center w-16">Foto</th>
                            <th class="py-3.5">Nama Produk</th>
                            <th class="py-3.5">Kode</th>
                            <th class="py-3.5">Kategori</th>
                            <th class="py-3.5 text-right">Harga Jual</th>
                            <th class="py-3.5 text-center">Stok</th>
                            <th class="py-3.5 text-center">Status</th>
                            <th class="py-3.5 text-center">Ketersediaan</th>
                            <th class="py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produk as $item)
                        <tr>
                            <td class="text-center">
                                @if($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" class="w-11 h-11 aspect-square rounded-xl object-cover border mx-auto shadow-xs shrink-0">
                                @else
                                    <div class="w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-sm font-bold mx-auto bg-gray-50 dark:bg-gray-800">-</div>
                                @endif
                            </td>
                            <td class="font-semibold text-gray-900 dark:text-white text-sm sm:text-base">
                                <a href="{{ route('produk.show', $item->id) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition">
                                    {{ $item->nama_produk }}
                                </a>
                            </td>
                            <td class="font-mono text-sm text-gray-600 dark:text-gray-400">{{ $item->kode_produk }}</td>
                            <td class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td class="text-right font-bold text-amber-700 dark:text-amber-400 whitespace-nowrap text-sm sm:text-base">
                                @if($item->punya_ukuran)
                                    <div class="text-xs font-medium text-gray-500">Besar: <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Rp {{ number_format($item->harga_besar, 0, ',', '.') }}</span></div>
                                    <div class="text-xs font-medium text-gray-500">Kecil: <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Rp {{ number_format($item->harga_kecil, 0, ',', '.') }}</span></div>
                                @else
                                    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->stok <= $item->stok_minimum)
                                    <span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/10 px-3 py-1 text-xs font-bold text-red-700 dark:text-red-400">{{ $item->stok }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-xs font-bold text-green-700 dark:text-green-400">{{ $item->stok }}</span>
                                @endif
                                <div class="text-xs text-gray-400 mt-0.5 font-medium">Min {{ $item->stok_minimum }}</div>
                            </td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <form action="{{ route('produk.toggle-status', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan produk">
                                            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                                        </label>
                                    </form>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $item->status_aktif ? 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400' }}">
                                        {{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->stok <= 0 || $item->status_ketersediaan == 'perlu_diproduksi')
                                    <span class="inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400">Perlu Diproduksi</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Ready</span>
                                @endif
                            </td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('produk.show', $item->id) }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline text-sm">Detail</a>
                                <a href="{{ route('produk.edit', $item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-sm">Edit</a>
                                <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama_produk) }}')" class="text-red-500 font-semibold hover:underline text-sm">Hapus</button>
                                </form>
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
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Produk?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Produk <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dan tidak dapat dipulihkan.
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
        initSaishoDataTable('produkTable', {
            emptyText: 'Belum ada produk',
            columnDefs: [
                { orderable: false, targets: [0, -1] }
            ]
        });
    });
</script>
@endpush