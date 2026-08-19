@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Transaksi Bahan Baku" />

    <div class="space-y-6">

        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari transaksi bahan baku...">
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
                    <tbody>
                        @foreach($transaksi as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="font-semibold text-gray-900 dark:text-white">{{ $item->bahanBaku->nama_bahan }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">
                                        {{ \App\Models\Satuan::formatAngka($item->jumlah, $item->bahanBaku->satuan?->tipe) }} {{ $item->bahanBaku->satuan?->nama ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-right font-bold text-amber-700 dark:text-amber-400 whitespace-nowrap">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $item->supplier ?: '-' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('transaksi-bahan-baku.show', $item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs">Detail</a>
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

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('transaksiBahanBakuTable', {
            emptyText: 'Belum ada transaksi bahan baku',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush