@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Transaksi Bahan Baku" />

    <div class="space-y-6">

        {{-- Filter Tanggal --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Transaksi Bahan Baku</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pilih tanggal untuk melihat bahan baku apa saja yang masuk pada tanggal tersebut.</p>
                </div>

                <form id="filterTransaksiBahanBaku" class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full lg:w-auto flex-wrap">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" id="filter_tanggal_dari" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" id="filter_tanggal_sampai" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit" class="flex-1 md:flex-initial px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition">Filter</button>
                        <button type="button" id="resetFilterTransaksiBahanBaku" class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari nama bahan baku...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
                <div class="sdt-toolbar-right">
                    <a href="{{ route('transaksi-bahan-baku.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Transaksi</span>
                    </a>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="transaksiBahanBakuTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Bahan Baku</th>
                            <th class="py-3 text-center">Jumlah</th>
                            <th class="py-3 text-right">Total Harga</th>
                            <th class="py-3">Supplier</th>
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

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('transaksiBahanBakuTable', {
            serverSide: true,
            ajax: {
                url: '{{ route('transaksi-bahan-baku.index') }}',
                data: function(d) {
                    d.tanggal_dari = $('#filter_tanggal_dari').val();
                    d.tanggal_sampai = $('#filter_tanggal_sampai').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal', className: 'whitespace-nowrap text-gray-500 dark:text-gray-400' },
                { data: 'bahan_baku_html', name: 'bahan_baku_html' },
                { data: 'jumlah_html', name: 'jumlah', className: 'text-center whitespace-nowrap' },
                { data: 'total_harga_html', name: 'total_harga', className: 'text-right font-bold text-amber-700 dark:text-amber-400 whitespace-nowrap' },
                { data: 'supplier', name: 'supplier' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Tidak ada transaksi bahan baku'
        });

        function muatUlangTabel() {
            $('#transaksiBahanBakuTable').DataTable().ajax.reload();
        }

        $('#filterTransaksiBahanBaku').on('submit', function(e) {
            e.preventDefault();
            muatUlangTabel();
        });

        $('#filter_tanggal_dari, #filter_tanggal_sampai').on('change', muatUlangTabel);

        $('#resetFilterTransaksiBahanBaku').on('click', function() {
            $('#filter_tanggal_dari, #filter_tanggal_sampai').val('');
            muatUlangTabel();
        });
    });
</script>
@endpush