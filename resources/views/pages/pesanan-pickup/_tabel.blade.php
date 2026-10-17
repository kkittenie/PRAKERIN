<div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
    <div class="sdt-toolbar">
        <div class="sdt-toolbar-left">
            <div class="sdt-search">
                <span class="sdt-search-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                </span>
                <input type="text" class="sdt-search-input" placeholder="Cari pesanan...">
            </div>
            <div class="sdt-pagesize">
                <span>Tampilkan</span>
                <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
            </div>
        </div>
    </div>

    <div class="sdt-table-wrap">
        <table id="pesananPickupTable" class="sdt-table">
            <thead>
                <tr>
                    <th class="py-3">No</th>
                    <th class="py-3">Kode Transaksi</th>
                    <th class="py-3 text-center">Kode Pesanan</th>
                    <th class="py-3">Pembeli</th>
                    <th class="py-3 text-right">Total</th>
                    <th class="py-3">Waktu Order</th>
                    <th class="py-3 text-center">Status Pesanan</th>
                    <th class="py-3 text-center">Status Pembayaran</th>
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

<script>
    window.reloadPesananPickupTable = function() {
        if (window.jQuery && $.fn.DataTable.isDataTable('#pesananPickupTable')) {
            $('#pesananPickupTable').DataTable().ajax.reload();
        }
    };

    $(document).ready(function() {
        initSaishoDataTable('pesananPickupTable', {
            serverSide: true,
            order: [[5, 'desc']],
            ajax: {
                url: '{{ route('pesanan-pickup.indeks') }}',
                data: function(d) {
                    d.status_pesanan = $('#filterPesananPickup select[name="status_pesanan"]').val();
                    d.status_pembayaran = $('#filterPesananPickup select[name="status_pembayaran"]').val();
                    d.cari = $('#filterPesananPickup input[name="cari"]').val();
                    d.tanggal_mulai = $('#filterPesananPickup input[name="tanggal_mulai"]').val();
                    d.tanggal_selesai = $('#filterPesananPickup input[name="tanggal_selesai"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kode_html', name: 'kode_transaksi' },
                { data: 'kode_pesanan_html', name: 'kode_pickup', className: 'text-center' },
                { data: 'pembeli_html', name: 'nama_pembeli' },
                { data: 'total_bayar', name: 'total_bayar', searchable: false, className: 'text-right font-semibold text-gray-800 dark:text-gray-200' },
                { data: 'created_at', name: 'created_at', searchable: false, className: 'text-xs text-gray-500 dark:text-gray-400' },
                { data: 'status_pesanan_html', name: 'status_pesanan', searchable: false, className: 'text-center' },
                { data: 'status_pembayaran_html', name: 'status_pembayaran', searchable: false, className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Belum ada pesanan'
        });

        $('#filterPesananPickup').on('submit', function(e) {
            e.preventDefault();
            window.reloadPesananPickupTable();
        });
    });
</script>