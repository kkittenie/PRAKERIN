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
                    <th class="py-3">Pembeli</th>
                    <th class="py-3 text-right">Total</th>
                    <th class="py-3">Waktu Order</th>
                    <th class="py-3 text-center">Status Pesanan</th>
                    <th class="py-3 text-center">Status Pembayaran</th>
                    <th class="py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pesanan as $index => $item)
                    @php
                        $statusBadge = [
                            \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Diproses', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
                            \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Siap Diambil', 'class' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
                            \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Sudah Diambil', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
                            \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
                        ][$item->status_pesanan] ?? ['label' => '-', 'class' => 'bg-gray-100 text-gray-500'];

                        if ($item->status_pembayaran === 'Lunas') {
                            $bayarBadge = ['label' => 'Lunas', 'class' => 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400'];
                        } elseif ($item->bukti_dikonfirmasi_pada) {
                            $bayarBadge = ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400'];
                        } else {
                            $bayarBadge = ['label' => 'Menunggu Pembayaran', 'class' => 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'];
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $item->kode_transaksi }}</span>
                        </td>
                        <td>
                            <span class="block font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $item->nama_pembeli }}</span>
                            <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                        </td>
                        <td class="text-right font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $bayarBadge['class'] }}">
                                {{ $bayarBadge['label'] }}
                            </span>
                        </td>
                        <td class="text-right">
                            <button @click="lihatDetail({{ $item->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 text-amber-700 dark:text-amber-400 font-semibold rounded-lg text-xs transition">
                                Detail
                            </button>
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

<script>
    $(document).ready(function() {
        initSaishoDataTable('pesananPickupTable', {
            emptyText: 'Belum ada pesanan',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>