@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="QRIS Pembayaran" />

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
                    <input type="text" class="sdt-search-input" placeholder="Cari QRIS...">
                </div>
                <div class="sdt-pagesize">
                    <span>Tampilkan</span>
                    <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                </div>
            </div>
            <div class="sdt-toolbar-right">
                @can('tambah_qris')
                <a href="{{ route('qris.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah QRIS</span>
                </a>
                @endcan
            </div>
        </div>

        <div class="sdt-table-wrap">
            <table id="qrisTable" class="sdt-table">
                <thead>
                    <tr>
                        <th class="py-3">No</th>
                        <th class="py-3 text-center">Gambar</th>
                        <th class="py-3">Nama</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-right">Aksi</th>
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
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus QRIS?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    QRIS <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dan tidak dapat dipulihkan kembali.
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
        initSaishoDataTable('qrisTable', {
            serverSide: true,
            ajax: '{{ route('qris.index') }}',
            order: [],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'gambar_html', name: 'gambar', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nama', name: 'nama' },
                { data: 'status_html', name: 'status_aktif', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Belum ada gambar QRIS. Halaman pembayaran memakai gambar placeholder.'
        });
    });
</script>
@endpush