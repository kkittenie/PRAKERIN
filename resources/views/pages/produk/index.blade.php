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
                    @if(isset($kategori) && $kategori->isNotEmpty())
                    <div class="sdt-pagesize">
                        <span>Kategori</span>
                        <select id="filter_kategori">
                            <option value="">Semua Kategori</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
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
                    <tbody></tbody>
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
            serverSide: true,
            ajax: {
                url: '{{ route('produk.index') }}',
                data: function(d) {
                    d.kategori_id = $('#filter_kategori').val();
                }
            },
            columns: [
                { data: 'foto', name: 'foto', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nama_produk', name: 'nama_produk' },
                { data: 'kode_produk', name: 'kode_produk', className: 'font-mono text-sm text-gray-600 dark:text-gray-400' },
                { data: 'kategori.nama_kategori', name: 'kategori.nama_kategori', defaultContent: '-', className: 'text-sm font-medium text-gray-700 dark:text-gray-300' },
                { data: 'harga_jual_html', name: 'harga_jual', className: 'text-right font-bold text-amber-700 dark:text-amber-400 whitespace-nowrap text-sm sm:text-base' },
                { data: 'stok_html', name: 'stok', className: 'text-center' },
                { data: 'status_html', name: 'status_aktif', className: 'text-center' },
                { data: 'ketersediaan_html', name: 'status_ketersediaan', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Belum ada produk'
        });

        $('#filter_kategori').on('change', function() {
            if ($.fn.DataTable.isDataTable('#produkTable')) {
                $('#produkTable').DataTable().ajax.reload();
            }
        });
    });
</script>
@endpush