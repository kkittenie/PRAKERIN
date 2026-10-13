@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Riwayat Penggunaan Voucher & Reward" parentTitle="Reward" parentUrl="{{ route('reward-voucher.index') }}" />

    <div class="space-y-6" x-data="{
            modalAksiTerbuka: false,
            formTerpilih: null,
            kodeAksi: '',
            jenisAksi: '',
            bukaModalAksi(form, jenis, kode) {
                this.formTerpilih = form;
                this.jenisAksi = jenis;
                this.kodeAksi = kode;
                this.modalAksiTerbuka = true;
            },
            konfirmasiAksi() {
                if (this.formTerpilih) { this.formTerpilih.submit(); }
                this.modalAksiTerbuka = false;
            }
        }">

        {{-- Tab Selector --}}
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-3">
            <a href="{{ route('riwayat-redeem.index', ['tipe' => 'voucher']) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition {{ ($tipeAktif ?? 'voucher') === 'voucher' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span>Penggunaan Voucher</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs font-semibold {{ ($tipeAktif ?? 'voucher') === 'voucher' ? 'bg-amber-700 text-amber-100' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ $ringkasan['total_voucher'] ?? 0 }}
                </span>
            </a>

            <a href="{{ route('riwayat-redeem.index', ['tipe' => 'produk']) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition {{ ($tipeAktif ?? 'voucher') === 'produk' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Penukaran Reward (Poin)</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs font-semibold {{ ($tipeAktif ?? 'voucher') === 'produk' ? 'bg-amber-700 text-amber-100' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ $ringkasan['total_produk'] ?? 0 }}
                </span>
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">
                        {{ ($tipeAktif ?? 'voucher') === 'voucher' ? 'Riwayat Penggunaan Voucher' : 'Riwayat Penukaran Reward Produk' }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ ($tipeAktif ?? 'voucher') === 'voucher' ? 'Daftar transaksi kasir dan pesanan pickup yang menggunakan voucher.' : 'Riwayat penukaran poin member dengan reward fisik.' }}
                    </p>
                </div>
                <form method="GET" action="{{ route('riwayat-redeem.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full md:w-auto flex-wrap">
                    <input type="hidden" name="tipe" value="{{ $tipeAktif ?? 'voucher' }}">

                    @if (($tipeAktif ?? 'voucher') === 'produk')
                        <div class="flex-1 md:flex-initial">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                                <option value="">Semua Status</option>
                                <option value="menunggu_diambil" {{ request('status') === 'menunggu_diambil' ? 'selected' : '' }}>Menunggu Diambil</option>
                                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                    @endif

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Filter</button>
                        <a href="{{ route('riwayat-redeem.index', ['tipe' => $tipeAktif ?? 'voucher']) }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                    </div>
                </form>
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
                        <input type="text" class="sdt-search-input" placeholder="{{ ($tipeAktif ?? 'voucher') === 'voucher' ? 'Cari voucher, member, no hp, transaksi...' : 'Cari redeem, member, no hp...' }}">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="riwayatRedeemTable" class="sdt-table">
                    <thead>
                        @if (($tipeAktif ?? 'voucher') === 'voucher')
                            <tr>
                                <th class="py-3">No</th>
                                <th class="py-3">Voucher</th>
                                <th class="py-3">Member</th>
                                <th class="py-3">Transaksi</th>
                                <th class="py-3">Waktu Pemakaian</th>
                            </tr>
                        @else
                            <tr>
                                <th class="py-3">No</th>
                                <th class="py-3">Kode Redeem</th>
                                <th class="py-3">Member</th>
                                <th class="py-3">Reward</th>
                                <th class="py-3 text-center">Poin Dipakai</th>
                                <th class="py-3">Tanggal Redeem</th>
                                <th class="py-3 text-center">Status</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="sdt-footer">
                <div class="sdt-info"></div>
                <div class="sdt-pagination"></div>
            </div>
        </div>

        {{-- Modal Konfirmasi Aksi (Hanya untuk redeem produk fisik) --}}
        @if (($tipeAktif ?? 'voucher') === 'produk')
            <div x-show="modalAksiTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
                <div @click.away="modalAksiTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                    <div class="p-6 text-center">
                        <template x-if="jenisAksi === 'selesai'">
                            <div class="w-14 h-14 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">✓</div>
                        </template>
                        <template x-if="jenisAksi === 'dibatalkan'">
                            <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                        </template>
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base" x-text="jenisAksi === 'selesai' ? 'Tandai Redeem Selesai?' : 'Batalkan Redeem Ini?'"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Kode redeem <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="kodeAksi"></span>
                            <template x-if="jenisAksi === 'selesai'"><span> akan ditandai sebagai sudah diberikan ke member.</span></template>
                            <template x-if="jenisAksi === 'dibatalkan'"><span> akan dibatalkan. Poin yang dipakai akan dikembalikan ke member.</span></template>
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                        <button type="button" @click="modalAksiTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                        <button type="button" @click="konfirmasiAksi()" :class="jenisAksi === 'selesai' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'" class="px-4 py-2 text-white font-bold text-xs rounded-lg transition shadow-xs">Ya, Lanjutkan</button>
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
<script>
    window.reloadRiwayatRedeemTable = function() {
        if ($.fn.DataTable.isDataTable('#riwayatRedeemTable')) {
            $('#riwayatRedeemTable').DataTable().ajax.reload();
        }
    };

    $(document).ready(function() {
        const tipeAktif = '{{ $tipeAktif ?? 'voucher' }}';

        let columnsConfig = [];
        let orderConfig = [[4, 'desc']];

        if (tipeAktif === 'voucher') {
            columnsConfig = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'voucher_html', name: 'voucher_nama' },
                { data: 'member_html', name: 'member.nama', searchable: false },
                { data: 'transaksi_html', name: 'transaksi.kode_transaksi', searchable: false },
                { data: 'used_at_html', name: 'used_at', searchable: false }
            ];
            orderConfig = [[4, 'desc']];
        } else {
            columnsConfig = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kode_html', name: 'kode_redeem' },
                { data: 'member_html', name: 'member.nama', searchable: false },
                { data: 'reward_html', name: 'reward.nama', searchable: false },
                { data: 'poin_terpakai', name: 'poin_terpakai', searchable: false, className: 'text-center font-semibold text-red-500' },
                { data: 'created_at', name: 'created_at', searchable: false, className: 'text-xs text-gray-500 dark:text-gray-400' },
                { data: 'status_html', name: 'status', searchable: false, className: 'text-center' }
            ];
            orderConfig = [[5, 'desc']];
        }

        initSaishoDataTable('riwayatRedeemTable', {
            serverSide: true,
            order: orderConfig,
            ajax: {
                url: '{{ route('riwayat-redeem.index') }}',
                data: function(d) {
                    d.tipe = tipeAktif;
                    if (tipeAktif === 'produk') {
                        d.status = $('#filterForm select[name="status"]').val();
                    }
                    d.tanggal_mulai = $('#filterForm input[name="tanggal_mulai"]').val();
                    d.tanggal_selesai = $('#filterForm input[name="tanggal_selesai"]').val();
                }
            },
            columns: columnsConfig,
            emptyText: tipeAktif === 'voucher' ? 'Belum ada riwayat penggunaan voucher' : 'Belum ada riwayat penukaran reward'
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            window.reloadRiwayatRedeemTable();
        });
    });
</script>
@endpush