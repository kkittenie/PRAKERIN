@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Produksi" />

    <div class="space-y-6" x-data="{ modalOpen: false, produksiId: null, produksiNama: '', jumlahMax: 0, sisaBelum: 0, varianId: null, daftarVarian: [],
            hitungMax() {
                const v = this.daftarVarian.find(x => x.id === this.varianId);
                this.jumlahMax = Math.min(this.sisaBelum, v ? v.stok : 0);
            } }">

        <div id="banner-produksi-kadaluwarsa" class="{{ $produksiKadaluarsa->count() > 0 ? '' : 'hidden' }} p-4 rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/80 dark:bg-red-500/10 backdrop-blur-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start md:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-red-900 dark:text-red-300">Peringatan Produk Kadaluwarsa!</h4>
                    <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">
                        Terdapat <span class="font-bold"><span id="banner-produksi-kadaluwarsa-count">{{ $produksiKadaluarsa->count() }}</span> batch produksi</span> yang sudah kadaluwarsa dan belum disesuaikan stoknya.
                    </p>
                </div>
            </div>
        </div>

        {{-- Top Filter Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Data Produksi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Catatan produksi dan status kadaluwarsanya.</p>
                </div>

                <form method="GET" action="{{ route('produksi.index') }}" id="filterForm"
                    x-data="{ periode: '{{ $periode }}' }"
                    class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full lg:w-auto flex-wrap">

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" id="filter_status_produksi" @change="$nextTick(() => window.reloadProduksiTable())"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="kadaluarsa" {{ $status === 'kadaluarsa' ? 'selected' : '' }}>Kadaluwarsa</option>
                            <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Belum Kadaluwarsa</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                        <select name="periode" id="filter_periode_produksi" x-model="periode" @change="$nextTick(() => window.reloadProduksiTable())"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua</option>
                            <option value="harian" {{ $periode === 'harian' ? 'selected' : '' }}>Per Hari</option>
                            <option value="mingguan" {{ $periode === 'mingguan' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="bulanan" {{ $periode === 'bulanan' ? 'selected' : '' }}>Per Bulan</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial" x-show="periode !== 'semua'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                        <input type="date" name="tanggal_ref" x-show="periode === 'harian'" :disabled="periode !== 'harian'" value="{{ $tanggalRef->toDateString() }}" @change="window.reloadProduksiTable()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                        <input type="week" name="tanggal_ref" x-show="periode === 'mingguan'" :disabled="periode !== 'mingguan'" value="{{ $tanggalRef->format('o-\WW') }}" @change="window.reloadProduksiTable()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                        <input type="month" name="tanggal_ref" x-show="periode === 'bulanan'" :disabled="periode !== 'bulanan'" value="{{ $tanggalRef->format('Y-m') }}" @change="window.reloadProduksiTable()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>

                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit" class="flex-1 md:flex-initial px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition">Filter</button>
                        <a href="{{ route('produksi.index', ['periode' => 'semua']) }}" class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                    </div>
                </form>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex gap-2">
                    <a href="{{ route('produksi.pdf') }}" id="btnCetakPdf" target="_blank" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Cetak PDF</a>
                    <a href="{{ route('produksi.excel') }}" id="btnExportExcel" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">Export Excel</a>
                </div>
                <a href="{{ route('produksi.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Produksi</span>
                </a>
            </div>
        </div>

        {{-- DataTables Card --}}
        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari produksi...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="produksiTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3 text-center">Hasil</th>
                            <th class="py-3">Dibuat Oleh</th>
                            <th class="py-3">Keterangan</th>
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

        {{-- Modal Kurangi Stok --}}
        <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div @click.away="modalOpen = false" class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Kurangi Stok Produk</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="'Produk: ' + produksiNama"></p>
                <form :action="'/produksi/' + produksiId + '/kurangi-stok'" method="POST">
                    @csrf
                    <template x-if="daftarVarian.length > 0">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Detail Produk</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="v in daftarVarian" :key="v.id">
                                    <button type="button" @click="varianId = v.id; hitungMax()"
                                        :class="varianId === v.id ? 'bg-brand-500 text-white border-brand-500' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700'"
                                        class="flex-1 min-w-[45%] rounded-lg border px-3 py-2 text-sm font-semibold transition">
                                        <span x-text="v.nama"></span> <span class="font-normal" x-text="'(' + v.stok + ' pcs)'"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="varian_id" :value="varianId">
                        </div>
                    </template>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah yang dikurangi (pcs)</label>
                    <input type="number" name="jumlah" min="1" :max="jumlahMax" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-gray-900 dark:text-white mb-1">
                    <p class="text-xs text-gray-400 mb-4" x-text="'Sisa belum disesuaikan: ' + sisaBelum + ' pcs (maks input: ' + jumlahMax + ' pcs)'"></p>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">Kurangi Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    window.reloadProduksiTable = function() {
        if ($.fn.DataTable.isDataTable('#produksiTable')) {
            $('#produksiTable').DataTable().ajax.reload();
        }
    };

    // Kumpulkan filter yang sedang aktif (status, periode, tanggal, dan kata kunci pencarian)
    function ambilFilterProduksi() {
        const params = {
            status: $('#filter_status_produksi').val() || 'semua',
            periode: $('#filter_periode_produksi').val() || 'semua',
            search: ($('#produksiTable').closest('.sdt-card').find('.sdt-search-input').val() || '').trim()
        };

        if (params.periode !== 'semua') {
            const tgl = $('input[name="tanggal_ref"]:not(:disabled)').val();
            if (tgl) params.tanggal_ref = tgl;
        }

        if (!params.search) delete params.search;
        return params;
    }

    // Pasang filter aktif ke link Cetak PDF & Export Excel
    function perbaruiLinkCetakProduksi() {
        const query = $.param(ambilFilterProduksi());
        ['btnCetakPdf', 'btnExportExcel'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            const base = el.href.split('?')[0];
            el.href = base + (query ? '?' + query : '');
        });
    }

    $(document).ready(function() {
        // Pastikan link selalu terbaru tepat saat diklik
        $('#btnCetakPdf, #btnExportExcel').on('click', perbaruiLinkCetakProduksi);

        initSaishoDataTable('produksiTable', {
            serverSide: true,
            ajax: {
                url: '{{ route('produksi.index') }}',
                data: function(d) {
                    d.status = $('#filter_status_produksi').val() || $('select[name="status"]').val();
                    d.periode = $('#filter_periode_produksi').val() || $('select[name="periode"]').val();
                    d.tanggal_ref = $('input[name="tanggal_ref"]:visible:not(:disabled)').val() || $('input[name="tanggal_ref"]:not(:disabled)').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'produk_html', name: 'produk.nama_produk' },
                { data: 'hasil_html', name: 'jumlah_hasil', className: 'text-center' },
                { data: 'user.name', name: 'user.name', defaultContent: '-' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            order: [[1, 'desc']], // batch terbaru di paling atas
            emptyText: 'Belum ada data produksi',
            onDraw: function() {
                perbaruiKadaluwarsaProduksi();
                perbaruiLinkCetakProduksi();
            }
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            window.reloadProduksiTable();
        });
    });

    // Jalan tiap detik: begitu waktu kadaluwarsa sebuah batch produksi lewat dari waktu sekarang,
    // badge, highlight baris, tombol "Kurangi Stok", dan banner peringatan otomatis update tanpa refresh.
    function perbaruiKadaluwarsaProduksi() {
        const sekarang = new Date();
        let jumlahPerluTindakan = 0;

        document.querySelectorAll('[data-produksi-row]').forEach(function (row) {
            const waktu = row.dataset.kadaluarsaWaktu;
            const sisa = parseInt(row.dataset.sisa || '0', 10);
            const kadaluarsa = !!waktu && sekarang > new Date(waktu);
            const perluTindakan = kadaluarsa && sisa > 0;

            const badgeTindakan = row.querySelector('.produksi-badge-tindakan');
            const badgePlain = row.querySelector('.produksi-badge-plain');
            const btnKurangi = row.querySelector('.produksi-btn-kurangi');

            const tampilkanBadgePlain = kadaluarsa && !perluTindakan;

            if (badgeTindakan) {
                badgeTindakan.classList.toggle('hidden', !perluTindakan);
                badgeTindakan.classList.toggle('inline-flex', perluTindakan);
            }
            if (badgePlain) {
                badgePlain.classList.toggle('hidden', !tampilkanBadgePlain);
                badgePlain.classList.toggle('inline-flex', tampilkanBadgePlain);
            }
            if (btnKurangi) {
                btnKurangi.classList.toggle('hidden', !perluTindakan);
                btnKurangi.classList.toggle('inline-flex', perluTindakan);
            }
            row.classList.toggle('bg-red-50/70', perluTindakan);
            row.classList.toggle('dark:bg-red-500/10', perluTindakan);

            if (perluTindakan) jumlahPerluTindakan++;
        });

        const banner = document.getElementById('banner-produksi-kadaluwarsa');
        const bannerCount = document.getElementById('banner-produksi-kadaluwarsa-count');
        if (banner) banner.classList.toggle('hidden', jumlahPerluTindakan === 0);
        if (bannerCount) bannerCount.textContent = jumlahPerluTindakan;
    }

    document.addEventListener('DOMContentLoaded', perbaruiKadaluwarsaProduksi);
    setInterval(perbaruiKadaluwarsaProduksi, 1000);
</script>
@endpush