@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Laporan Penjualan" />

    <div class="space-y-6">
        {{-- Kartu Statistik Ringkasan --}}
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
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Hari Ini</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white mb-0.5">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jumlahTransaksiHariIni }} transaksi</p>
                </div>
            </div>

            {{-- Bulan Ini --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-500/10 dark:to-indigo-500/5 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-500/15 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Bulan Ini</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white mb-0.5">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jumlahTransaksiBulanIni }} transaksi</p>
                </div>
            </div>

            {{-- Tahun Ini --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-500/10 dark:to-purple-500/5 rounded-bl-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 bg-purple-100 dark:bg-purple-500/15 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </div>
                        <div>
                            <p class="text-xxs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tahun Ini</p>
                        </div>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-900 dark:text-white mb-0.5">Rp {{ number_format($totalTahunIni, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $jumlahTransaksiTahunIni }} transaksi</p>
                </div>
            </div>
        </div>

        {{-- Grafik Penjualan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 w-full overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Grafik Penjualan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tren penjualan berdasarkan periode yang dipilih</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <a href="{{ route('laporan-penjualan', ['periode' => 'minggu']) }}"
                           class="px-4 py-2 text-sm font-semibold transition {{ $periode === 'minggu' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Minggu
                        </a>
                        <a href="{{ route('laporan-penjualan', ['periode' => 'bulan', 'tahun' => $tahun]) }}"
                           class="px-4 py-2 text-sm font-semibold transition border-x border-gray-200 dark:border-gray-700 {{ $periode === 'bulan' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Bulan
                        </a>
                        <a href="{{ route('laporan-penjualan', ['periode' => 'tahun']) }}"
                           class="px-4 py-2 text-sm font-semibold transition {{ $periode === 'tahun' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            Tahun
                        </a>
                    </div>
                    <form method="GET" action="{{ route('laporan-penjualan') }}">
                        <input type="hidden" name="periode" value="{{ $periode }}">
                        <select name="tahun" onchange="this.form.submit()"
                                class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-sm bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent cursor-pointer">
                            @foreach($daftarTahun as $thn)
                                <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <div id="chartPenjualan" class="min-h-[350px] w-full"></div>
        </div>

        {{-- Produk Terlaris --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">🏆 Produk Terlaris</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">10 produk dengan penjualan tertinggi sepanjang waktu</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider w-12">#</th>
                            <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Nama Produk</th>
                            <th class="text-right px-6 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Total Terjual</th>
                            <th class="text-right px-6 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($produkTerlaris as $index => $produk)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors">
                            <td class="px-6 py-4">
                                @if($index < 3)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                        {{ $index === 0 ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400' : ($index === 1 ? 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400' : 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400') }}">
                                        {{ $index + 1 }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 font-medium pl-2">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">{{ $produk->nama_produk }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center gap-1 text-indigo-700 dark:text-indigo-400 font-semibold">
                                    {{ number_format($produk->total_terjual, 0, ',', '.') }}
                                    <span class="text-gray-400 dark:text-gray-500 text-xs font-normal">pcs</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                Belum ada data penjualan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($labelGrafik);
    const data = @json($dataGrafik);
    const isDark = document.documentElement.classList.contains('dark');

    if (typeof ApexCharts !== 'undefined' && document.querySelector('#chartPenjualan')) {
        const options = {
            series: [{
                name: 'Penjualan',
                data: data,
            }],
            chart: {
                type: 'area',
                height: 350,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            colors: ['#6366f1'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 100],
                },
            },
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            dataLabels: { enabled: false },
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
                        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                        if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                        return 'Rp ' + val;
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
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            grid: {
                borderColor: isDark ? '#1e293b' : '#f3f4f6',
                strokeDashArray: 4,
            },
            markers: {
                size: 5,
                colors: ['#6366f1'],
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
