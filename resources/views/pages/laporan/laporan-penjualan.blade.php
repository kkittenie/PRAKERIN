@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Laporan Penjualan" />

    <div class="space-y-6">
        {{-- Kartu Statistik Ringkasan — Laba Bersih --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Hari Ini --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-100 to-green-50 dark:from-green-500/10 dark:to-green-500/5 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-green-100 dark:bg-green-500/15 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $periode === 'hari' ? 'Laba ' . $labelTanggal : 'Laba Hari Ini' }}</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold {{ $labaHariIni < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mb-1">
                        {{ $labaHariIni < 0 ? '-Rp ' . number_format(abs($labaHariIni), 0, ',', '.') : 'Rp ' . number_format($labaHariIni, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $jumlahTransaksiHariIni }} transaksi</span>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <span class="text-gray-400 dark:text-gray-500">
                            Pendapatan Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }} − Modal Rp {{ number_format($modalHariIni, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Bulan Ini --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-500/10 dark:to-amber-500/5 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-amber-100 dark:bg-amber-500/15 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $periode === 'bulan' ? 'Laba ' . $labelBulan : 'Laba Bulan Ini' }}</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold {{ $labaBulanIni < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mb-1">
                        {{ $labaBulanIni < 0 ? '-Rp ' . number_format(abs($labaBulanIni), 0, ',', '.') : 'Rp ' . number_format($labaBulanIni, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $jumlahTransaksiBulanIni }} transaksi</span>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <span class="text-gray-400 dark:text-gray-500">
                            Pendapatan Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }} − Modal Rp {{ number_format($modalBulanIni, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tahun Ini --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-100 to-orange-50 dark:from-orange-500/10 dark:to-orange-500/5 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-orange-100 dark:bg-orange-500/15 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <div>
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $periode === 'tahun' ? 'Laba Tahun ' . $tahun : 'Laba Tahun Ini' }}</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold {{ $labaTahunIni < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mb-1">
                        {{ $labaTahunIni < 0 ? '-Rp ' . number_format(abs($labaTahunIni), 0, ',', '.') : 'Rp ' . number_format($labaTahunIni, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $jumlahTransaksiTahunIni }} transaksi</span>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <span class="text-gray-400 dark:text-gray-500">
                            Pendapatan Rp {{ number_format($pendapatanTahunIni, 0, ',', '.') }} − Modal Rp {{ number_format($modalTahunIni, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafik Penjualan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 w-full overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Grafik Laba Bersih</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tren pendapatan vs laba bersih (setelah dikurangi harga modal) berdasarkan periode yang dipilih</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3 shrink-0 ml-auto">
                    <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden shrink-0">
                        <a href="{{ route('laporan-penjualan', ['periode' => 'hari', 'tanggal' => $tanggal]) }}"
                           class="px-3.5 py-2 text-xs sm:text-sm font-semibold transition border-r border-gray-200 dark:border-gray-700 {{ $periode === 'hari' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Hari
                        </a>
                        <a href="{{ route('laporan-penjualan', ['periode' => 'minggu']) }}"
                           class="px-3.5 py-2 text-xs sm:text-sm font-semibold transition {{ $periode === 'minggu' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Minggu
                        </a>
                        <a href="{{ route('laporan-penjualan', ['periode' => 'bulan', 'bulan' => $bulan]) }}"
                           class="px-3.5 py-2 text-xs sm:text-sm font-semibold transition border-x border-gray-200 dark:border-gray-700 {{ $periode === 'bulan' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Bulan
                        </a>
                        <a href="{{ route('laporan-penjualan', ['periode' => 'tahun', 'tahun' => $tahun]) }}"
                           class="px-3.5 py-2 text-xs sm:text-sm font-semibold transition {{ $periode === 'tahun' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Tahun
                        </a>
                    </div>
                    @if($periode === 'hari')
                    <form method="GET" action="{{ route('laporan-penjualan') }}" class="shrink-0">
                        <input type="hidden" name="periode" value="hari">
                        <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()"
                               class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer">
                    </form>
                    @elseif($periode === 'bulan')
                    <form method="GET" action="{{ route('laporan-penjualan') }}" class="shrink-0">
                        <input type="hidden" name="periode" value="bulan">
                        <input type="month" name="bulan" value="{{ $bulan }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()"
                               class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer">
                    </form>
                    @elseif($periode === 'tahun')
                    <form method="GET" action="{{ route('laporan-penjualan') }}" class="shrink-0">
                        <input type="hidden" name="periode" value="tahun">
                        <select name="tahun" onchange="this.form.submit()"
                                class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer">
                            @foreach($daftarTahun as $thn)
                                <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
            </div>
            <div id="chartPenjualan" class="min-h-[350px] w-full"></div>
        </div>

        {{-- Produk Terlaris DataTables --}}
        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden space-y-4">
            <div class="px-6 pt-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span>🏆 Produk Terlaris</span>
                        <span id="badgePeriodeTerlaris" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400">
                            Hari Ini
                        </span>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        10 produk dengan penjualan tertinggi pada <span id="labelTeksPeriodeTerlaris" class="font-medium text-gray-700 dark:text-gray-300">hari ini ({{ now()->translatedFormat('d M Y') }})</span> — pendapatan dan laba bersih setelah dikurangi harga modal
                    </p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                    {{-- Segmented Mode Buttons: Hari / Bulan / Tahun --}}
                    <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <button type="button" data-mode="hari"
                                class="btn-mode-terlaris px-3 py-1 text-xs font-semibold rounded-lg transition bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-xs">
                            Hari
                        </button>
                        <button type="button" data-mode="bulan"
                                class="btn-mode-terlaris px-3 py-1 text-xs font-semibold rounded-lg transition text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Bulan
                        </button>
                        <button type="button" data-mode="tahun"
                                class="btn-mode-terlaris px-3 py-1 text-xs font-semibold rounded-lg transition text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Tahun
                        </button>
                    </div>

                    {{-- Wrapper Filter Hari --}}
                    <div id="wrapperFilterHari" class="flex items-center gap-2">
                        <input type="date"
                               id="filter_tanggal_terlaris"
                               value="{{ now()->toDateString() }}"
                               max="{{ now()->toDateString() }}"
                               class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer font-medium">
                        <button type="button"
                                id="btnResetHariIniTerlaris"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition">
                            Hari Ini
                        </button>
                    </div>

                    {{-- Wrapper Filter Bulan --}}
                    <div id="wrapperFilterBulan" class="hidden items-center gap-2">
                        <input type="month"
                               id="filter_bulan_terlaris"
                               value="{{ now()->format('Y-m') }}"
                               max="{{ now()->format('Y-m') }}"
                               class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer font-medium">
                        <button type="button"
                                id="btnResetBulanIniTerlaris"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition">
                            Bulan Ini
                        </button>
                    </div>

                    {{-- Wrapper Filter Tahun --}}
                    <div id="wrapperFilterTahun" class="hidden items-center gap-2">
                        <select id="filter_tahun_terlaris"
                                class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent cursor-pointer font-medium">
                            @foreach($daftarTahun as $thn)
                                <option value="{{ $thn }}" {{ $thn == date('Y') ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                        <button type="button"
                                id="btnResetTahunIniTerlaris"
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 transition">
                            Tahun Ini
                        </button>
                    </div>
                </div>
            </div>

            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari produk terlaris...">
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="produkTerlarisTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3 w-12">#</th>
                            <th class="py-3">Nama Produk</th>
                            <th class="py-3 text-right">Total Terjual</th>
                            <th class="py-3 text-right">Pendapatan</th>
                            <th class="py-3 text-right">Laba Bersih</th>
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
    var modeTerlaris = 'hari';
    var todayStr = '{{ now()->toDateString() }}';
    var todayFormatted = '{{ now()->translatedFormat('d M Y') }}';
    var thisMonthStr = '{{ now()->format('Y-m') }}';
    var thisMonthFormatted = '{{ now()->translatedFormat('F Y') }}';
    var thisYearStr = '{{ date('Y') }}';

    var dtTerlaris = initSaishoDataTable('produkTerlarisTable', {
        serverSide: true,
        ajax: {
            url: '{{ route('laporan-penjualan') }}',
            data: function(d) {
                d.tipe_terlaris = modeTerlaris;
                d.tanggal_terlaris = $('#filter_tanggal_terlaris').val();
                d.bulan_terlaris = $('#filter_bulan_terlaris').val();
                d.tahun_terlaris = $('#filter_tahun_terlaris').val();
            }
        },
        order: [[2, 'desc']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'w-12' },
            { data: 'nama_produk', name: 'nama_produk', className: 'font-medium text-gray-800 dark:text-gray-200' },
            { data: 'total_terjual_html', name: 'total_terjual', className: 'text-right' },
            { data: 'total_pendapatan_html', name: 'total_pendapatan', className: 'text-right text-gray-700 dark:text-gray-300' },
            { data: 'laba_bersih_html', name: 'laba_bersih', className: 'text-right font-bold' }
        ],
        emptyText: 'Belum ada data penjualan pada periode ini',
        dtOptions: {
            createdRow: function(row, data) {
                var idx = data.DT_RowIndex;
                if (idx <= 3) {
                    var kelas = idx === 1
                        ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400'
                        : (idx === 2
                            ? 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
                            : 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400');
                    $('td:eq(0)', row).html('<span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold ' + kelas + '">' + idx + '</span>');
                } else {
                    $('td:eq(0)', row).html('<span class="text-gray-400 dark:text-gray-500 font-medium pl-2">' + idx + '</span>');
                }
            }
        }
    });

    var bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    var bulanLengkapNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) - 1;
        var d = parseInt(parts[2], 10);
        var dateObj = new Date(y, m, d);
        return d + ' ' + bulanNames[dateObj.getMonth()] + ' ' + y;
    }

    function formatBulanIndo(monthStr) {
        if (!monthStr) return '';
        var parts = monthStr.split('-');
        if (parts.length !== 2) return monthStr;
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) - 1;
        return bulanLengkapNames[m] + ' ' + y;
    }

    function updateLabelPeriode() {
        if (modeTerlaris === 'hari') {
            var val = $('#filter_tanggal_terlaris').val();
            if (!val || val === todayStr) {
                $('#badgePeriodeTerlaris').text('Hari Ini');
                $('#labelTeksPeriodeTerlaris').text('hari ini (' + todayFormatted + ')');
            } else {
                var formatted = formatTanggalIndo(val);
                $('#badgePeriodeTerlaris').text(formatted);
                $('#labelTeksPeriodeTerlaris').text('tanggal ' + formatted);
            }
        } else if (modeTerlaris === 'bulan') {
            var valBulan = $('#filter_bulan_terlaris').val();
            if (!valBulan || valBulan === thisMonthStr) {
                $('#badgePeriodeTerlaris').text('Bulan Ini');
                $('#labelTeksPeriodeTerlaris').text('bulan ini (' + thisMonthFormatted + ')');
            } else {
                var formattedBulan = formatBulanIndo(valBulan);
                $('#badgePeriodeTerlaris').text(formattedBulan);
                $('#labelTeksPeriodeTerlaris').text('bulan ' + formattedBulan);
            }
        } else if (modeTerlaris === 'tahun') {
            var valTahun = $('#filter_tahun_terlaris').val();
            if (!valTahun || valTahun === thisYearStr) {
                $('#badgePeriodeTerlaris').text('Tahun Ini');
                $('#labelTeksPeriodeTerlaris').text('tahun ini (' + thisYearStr + ')');
            } else {
                $('#badgePeriodeTerlaris').text('Tahun ' + valTahun);
                $('#labelTeksPeriodeTerlaris').text('tahun ' + valTahun);
            }
        }
    }

    // Switch mode button (Hari, Bulan, Tahun)
    $('.btn-mode-terlaris').on('click', function() {
        var mode = $(this).data('mode');
        if (mode === modeTerlaris) return;
        modeTerlaris = mode;

        $('.btn-mode-terlaris')
            .removeClass('bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-xs')
            .addClass('text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white');
        $(this)
            .addClass('bg-white dark:bg-gray-700 text-amber-600 dark:text-amber-400 shadow-xs')
            .removeClass('text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white');

        $('#wrapperFilterHari').toggleClass('hidden', mode !== 'hari').toggleClass('flex', mode === 'hari');
        $('#wrapperFilterBulan').toggleClass('hidden', mode !== 'bulan').toggleClass('flex', mode === 'bulan');
        $('#wrapperFilterTahun').toggleClass('hidden', mode !== 'tahun').toggleClass('flex', mode === 'tahun');

        updateLabelPeriode();
        if (dtTerlaris) {
            dtTerlaris.ajax.reload();
        }
    });

    // Hari listeners
    $('#filter_tanggal_terlaris').on('change', function() {
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });
    $('#btnResetHariIniTerlaris').on('click', function() {
        $('#filter_tanggal_terlaris').val(todayStr);
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });

    // Bulan listeners
    $('#filter_bulan_terlaris').on('change', function() {
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });
    $('#btnResetBulanIniTerlaris').on('click', function() {
        $('#filter_bulan_terlaris').val(thisMonthStr);
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });

    // Tahun listeners
    $('#filter_tahun_terlaris').on('change', function() {
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });
    $('#btnResetTahunIniTerlaris').on('click', function() {
        $('#filter_tahun_terlaris').val(thisYearStr);
        updateLabelPeriode();
        if (dtTerlaris) dtTerlaris.ajax.reload();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($labelGrafik);
    const dataPendapatan = @json($dataGrafikPendapatan);
    const dataLaba = @json($dataGrafikLaba);
    const isDark = document.documentElement.classList.contains('dark');

    if (typeof ApexCharts !== 'undefined' && document.querySelector('#chartPenjualan')) {
        const options = {
            series: [
                {
                    name: 'Pendapatan',
                    data: dataPendapatan,
                },
                {
                    name: 'Laba Bersih',
                    data: dataLaba,
                }
            ],
            chart: {
                type: 'area',
                height: 350,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            colors: ['#94a3b8', '#10b981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 100],
                },
            },
            stroke: {
                curve: 'smooth',
                width: [2, 3],
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '13px',
                fontWeight: 600,
                labels: {
                    colors: isDark ? '#d1d5db' : '#374151',
                },
                markers: {
                    radius: 3,
                },
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '12px',
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        const prefix = val < 0 ? '-Rp ' : 'Rp ';
                        const abs = Math.abs(val);
                        if (abs >= 1000000) return prefix + (abs / 1000000).toFixed(1) + ' jt';
                        if (abs >= 1000) return prefix + (abs / 1000).toFixed(0) + ' rb';
                        return prefix + abs;
                    },
                    style: {
                        colors: '#9ca3af',
                        fontSize: '12px',
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        const prefix = val < 0 ? '-Rp ' : 'Rp ';
                        return prefix + Math.abs(val).toLocaleString('id-ID');
                    }
                }
            },
            grid: {
                borderColor: isDark ? '#1e293b' : '#f3f4f6',
                strokeDashArray: 4,
            },
            markers: {
                size: 4,
                strokeColors: isDark ? '#0f172a' : '#fff',
                strokeWidth: 2,
                hover: { sizeOffset: 3 },
            },
        };

        const chart = new ApexCharts(document.querySelector('#chartPenjualan'), options);
        chart.render();
    }
});
</script>
@endpush