@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Rekap Penjualan" />

    @if(($lowStockProduk && $lowStockProduk->count() > 0) || ($lowStockBahan && $lowStockBahan->count() > 0))
        <div class="mb-6 p-4 rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/80 dark:bg-red-500/10 backdrop-blur-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start md:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 mt-0.5 md:mt-0 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-red-900 dark:text-red-300">Peringatan Stok Menipis!</h4>
                    <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">
                        Terdapat
                        @if($lowStockProduk && $lowStockProduk->count() > 0)
                            <span class="font-bold">{{ $lowStockProduk->count() }} produk</span>
                        @endif
                        @if($lowStockProduk && $lowStockProduk->count() > 0 && $lowStockBahan && $lowStockBahan->count() > 0) dan @endif
                        @if($lowStockBahan && $lowStockBahan->count() > 0)
                            <span class="font-bold">{{ $lowStockBahan->count() }} bahan baku</span>
                        @endif
                        yang mencapai atau di bawah stok minimum.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
                @if($lowStockProduk && $lowStockProduk->count() > 0)
                    <a href="{{ route('produk.index') }}" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition shadow-xs">Cek Produk</a>
                @endif
                @if($lowStockBahan && $lowStockBahan->count() > 0)
                    <a href="{{ route('bahan-baku.index') }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition shadow-xs">Cek Bahan Baku</a>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== PESANAN MOBILE TERBARU (di bawah peringatan stok) ===== --}}
    @can('lihat_pesanan_pickup')
        <div class="mb-6 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pesanan Mobile Terbaru</h3>
                    <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $pesananMobileTerbaru->count() }} pesanan yang masih diproses dari aplikasi member</p>
                </div>
                <a href="{{ route('pesanan-pickup.indeks') }}" class="shrink-0 text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 transition">Lihat Semua</a>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="min-w-full text-left text-theme-sm">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="py-3 pr-4 font-medium">Kode Transaksi</th>
                            <th class="py-3 pr-4 font-medium text-center">Kode Pesanan</th>
                            <th class="py-3 pr-4 font-medium">Pembeli</th>
                            <th class="py-3 pr-4 font-medium text-center">Status Pesanan</th>
                            <th class="py-3 pr-4 font-medium text-center">Status Pembayaran</th>
                            <th class="py-3 pr-4 font-medium text-right">Total</th>
                            <th class="py-3 font-medium">Waktu Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($pesananMobileTerbaru as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="py-3 pr-4 whitespace-nowrap">@include('pages.pesanan-pickup.partials.kode', ['item' => $item])</td>
                                <td class="py-3 pr-4 whitespace-nowrap text-center">@include('pages.pesanan-pickup.partials.kode-pesanan', ['item' => $item])</td>
                                <td class="py-3 pr-4 whitespace-nowrap">
                                    <span class="block font-semibold text-gray-800 dark:text-white text-sm">{{ $item->nama_pembeli ?: 'Pelanggan' }}</span>
                                    <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                                </td>
                                <td class="py-3 pr-4 whitespace-nowrap text-center">@include('pages.pesanan-pickup.partials.status-pesanan', ['item' => $item])</td>
                                <td class="py-3 pr-4 whitespace-nowrap text-center">@include('pages.pesanan-pickup.partials.status-pembayaran', ['item' => $item])</td>
                                <td class="py-3 pr-4 whitespace-nowrap text-right font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                                <td class="py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-500">Tidak ada pesanan yang sedang diproses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan

    <div class="grid grid-cols-12 gap-4 md:gap-6">

        {{-- ===== KOLOM KIRI: METRICS + BAR CHART ===== --}}
        <div class="col-span-12 space-y-6 xl:col-span-7">

            {{-- Metrics Cards (2 kolom) --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
                {{-- Card: Pendapatan Hari Ini --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] p-5 md:p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-amber-100 dark:bg-amber-500/10 rounded-xl">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="flex items-end justify-between mt-5">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</span>
                            <h4 class="mt-2 font-bold text-gray-800 dark:text-white text-title-sm">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</h4>
                        </div>
                        <span class="flex items-center gap-1 rounded-full py-0.5 pl-2 pr-2.5 text-sm font-medium {{ $perubahanPendapatan >= 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400' }}">
                            @if($perubahanPendapatan >= 0)
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.5392 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" /></svg>
                            @else
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z" /></svg>
                            @endif
                            {{ abs($perubahanPendapatan) }}%
                        </span>
                    </div>
                </div>

                {{-- Card: Transaksi Hari Ini --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] p-5 md:p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    </div>
                    <div class="flex items-end justify-between mt-5">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Transaksi Hari Ini</span>
                            <h4 class="mt-2 font-bold text-gray-800 dark:text-white text-title-sm">{{ number_format($transaksiHariIni, 0, ',', '.') }}</h4>
                        </div>
                        <span class="flex items-center gap-1 rounded-full py-0.5 pl-2 pr-2.5 text-sm font-medium {{ $perubahanTransaksi >= 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400' }}">
                            @if($perubahanTransaksi >= 0)
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" /></svg>
                            @else
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z" /></svg>
                            @endif
                            {{ abs($perubahanTransaksi) }}%
                        </span>
                    </div>
                </div>
            </div>

            {{-- Grafik Penjualan Bulanan (Bar Chart) --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] px-5 pt-5 sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Penjualan Bulanan</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ date('Y') }}</span>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div id="chartPenjualanBulanan" class="-ml-5 h-full min-w-[690px] pl-2 xl:min-w-full"></div>
                </div>
            </div>
        </div>

        {{-- ===== KOLOM KANAN: TARGET BULANAN ===== --}}
        <div class="col-span-12 xl:col-span-5">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900">
                <div class="shadow-default rounded-2xl bg-white dark:bg-white/[0.03] px-5 pb-11 pt-5 sm:px-6 sm:pt-6">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pencapaian Bulan Ini</h3>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Dibandingkan dengan bulan lalu</p>
                        </div>
                    </div>
                    <div class="relative max-h-[195px]">
                        <div id="chartTargetBulanan" class="h-full"></div>
                        <span class="absolute left-1/2 top-[85%] -translate-x-1/2 -translate-y-[85%] rounded-full px-3 py-1 text-xs font-medium {{ $perubahanPendapatan >= 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400' }}">
                            {{ $perubahanPendapatan >= 0 ? '+' : '' }}{{ $perubahanPendapatan }}%
                        </span>
                    </div>
                    <p class="mx-auto mt-1.5 w-full max-w-[380px] text-center text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                        Pendapatan bulan ini Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}
                        @if($perubahanPendapatan >= 0)
                            — lebih tinggi dari bulan lalu. Pertahankan! 🎉
                        @else
                            — lebih rendah dari bulan lalu.
                        @endif
                    </p>
                </div>

                <div class="flex items-center justify-center gap-5 px-6 py-3.5 sm:gap-8 sm:py-5">
                    <div>
                        <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Bulan Lalu</p>
                        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white sm:text-lg">
                            Rp {{ number_format($pendapatanBulanLalu, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
                    <div>
                        <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Bulan Ini</p>
                        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white sm:text-lg">
                            Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
                    <div>
                        <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">Hari Ini</p>
                        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white sm:text-lg">
                            Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== GRAFIK STATISTIK (Tren Harian 7 Hari) ===== --}}
        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] px-5 pb-5 pt-5 sm:px-6 sm:pt-6">
                <div class="flex flex-col gap-5 mb-6 sm:flex-row sm:justify-between">
                    <div class="w-full">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Statistik 7 Hari Terakhir</h3>
                        <p class="mt-1 text-gray-500 dark:text-gray-400 text-theme-sm">Tren pendapatan dan jumlah transaksi harian</p>
                    </div>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div id="chartStatistikHarian" class="-ml-4 min-w-[700px] pl-2 xl:min-w-full"></div>
                </div>
            </div>
        </div>

        {{-- ===== TRANSAKSI TERBARU ===== --}}
        <div class="col-span-12 xl:col-span-5">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] p-5 sm:p-6">
                <div class="flex justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Transaksi Terbaru</h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">5 transaksi terakhir</p>
                    </div>
                    <a href="{{ route('histori-transaksi') }}" class="text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 transition">Lihat Semua</a>
                </div>
                <div class="space-y-4">
                    @forelse($transaksiTerbaru as $trx)
                    <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-gray-800 first:border-0 first:pt-0">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $loop->index % 3 === 0 ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : ($loop->index % 3 === 1 ? 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $trx->nama_pembeli ?: 'Pelanggan' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $trx->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-800 dark:text-white">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $trx->detailTransaksi->count() }} item</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <p>Belum ada transaksi.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===== PRODUK TERLARIS ===== --}}
        <div class="col-span-12 xl:col-span-7">
            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] px-4 pb-3 pt-4 sm:px-6">
                <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">🏆 Produk Terlaris</h3>
                    </div>
                    <a href="{{ route('laporan-penjualan') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-850 px-4 py-2.5 text-theme-sm font-medium text-gray-700 dark:text-gray-300 shadow-theme-xs hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-white transition">
                        Lihat Laporan
                    </a>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="py-3 text-left">
                                    <p class="font-medium text-gray-500 dark:text-gray-400 text-theme-xs">#</p>
                                </th>
                                <th class="py-3 text-left">
                                    <p class="font-medium text-gray-500 dark:text-gray-400 text-theme-xs">Nama Produk</p>
                                </th>
                                <th class="py-3 text-right">
                                    <p class="font-medium text-gray-500 dark:text-gray-400 text-theme-xs">Terjual</p>
                                </th>
                                <th class="py-3 text-right">
                                    <p class="font-medium text-gray-500 dark:text-gray-400 text-theme-xs">Pendapatan</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produkTerlaris as $index => $produk)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-3 whitespace-nowrap">
                                    @if($index < 3)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300' : 'bg-orange-100 text-orange-700') }}">
                                        {{ $index + 1 }}
                                    </span>
                                    @else
                                    <span class="text-gray-400 dark:text-gray-500 font-medium pl-2">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="py-3 whitespace-nowrap">
                                    <p class="font-medium text-gray-800 dark:text-white text-theme-sm">{{ $produk->nama_produk }}</p>
                                </td>
                                <td class="py-3 whitespace-nowrap text-right">
                                    <p class="text-gray-600 dark:text-gray-300 text-theme-sm font-semibold">{{ number_format($produk->total_terjual, 0, ',', '.') }} <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">pcs</span></p>
                                </td>
                                <td class="py-3 whitespace-nowrap text-right">
                                    <p class="font-bold text-gray-800 dark:text-white text-theme-sm">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</p>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500">Belum ada data penjualan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($produkTerlaris as $index => $produk)
                    <div class="py-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold shrink-0
                                {{ $index === 0 ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400' : ($index === 1 ? 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400' : ($index === 2 ? 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500')) }}">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 dark:text-white text-xs truncate">{{ $produk->nama_produk }}</p>
                                <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold">{{ number_format($produk->total_terjual, 0, ',', '.') }} pcs</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-extrabold text-gray-900 dark:text-white text-xs">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-xs text-gray-400">Belum ada data penjualan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ===== BAR CHART: Penjualan Bulanan =====
    if (typeof ApexCharts !== 'undefined' && document.querySelector('#chartPenjualanBulanan')) {
        const barChart = new ApexCharts(document.querySelector('#chartPenjualanBulanan'), {
            series: [{
                name: 'Penjualan',
                data: @json($penjualanBulanan),
            }],
            colors: ['#a0704a'],
            chart: {
                fontFamily: 'Outfit, sans-serif',
                type: 'bar',
                height: 180,
                toolbar: { show: false },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '39%',
                    borderRadius: 5,
                    borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 4, colors: ['transparent'] },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left',
                fontFamily: 'Outfit',
                markers: { radius: 99 },
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                        if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                        return 'Rp ' + val;
                    }
                }
            },
            grid: { yaxis: { lines: { show: true } } },
            fill: { opacity: 1 },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
        });
        barChart.render();
    }

    // ===== GAUGE CHART: Target Bulanan =====
    if (typeof ApexCharts !== 'undefined' && document.querySelector('#chartTargetBulanan')) {
        const gaugeChart = new ApexCharts(document.querySelector('#chartTargetBulanan'), {
            series: [{{ $pencapaianTarget }}],
            colors: ['#a0704a'],
            chart: {
                fontFamily: 'Outfit, sans-serif',
                type: 'radialBar',
                height: 195,
                sparkline: { enabled: true },
            },
            plotOptions: {
                radialBar: {
                    startAngle: -120,
                    endAngle: 120,
                    track: {
                        background: '#e6e0da',
                        strokeWidth: '97%',
                        margin: 5,
                    },
                    hollow: { size: '60%' },
                    dataLabels: {
                        name: { show: false },
                        value: {
                            offsetY: -2,
                            fontSize: '22px',
                            fontWeight: 'bold',
                            formatter: function (val) { return val + '%'; },
                        },
                    },
                },
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.15,
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 50, 65, 91],
                },
            },
            stroke: { lineCap: 'round' },
        });
        gaugeChart.render();
    }

    // ===== LINE CHART: Statistik Harian =====
    if (typeof ApexCharts !== 'undefined' && document.querySelector('#chartStatistikHarian')) {
        const lineChart = new ApexCharts(document.querySelector('#chartStatistikHarian'), {
            series: [
                {
                    name: 'Pendapatan',
                    type: 'area',
                    data: @json($dataPendapatanHarian),
                },
                {
                    name: 'Jumlah Transaksi',
                    type: 'line',
                    data: @json($dataTransaksiHarian),
                },
            ],
            colors: ['#a0704a', '#10b981'],
            chart: {
                fontFamily: 'Outfit, sans-serif',
                height: 310,
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            stroke: { width: [2, 2], curve: 'smooth' },
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 100],
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($labelHarian),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#9ca3af', fontSize: '12px' },
                },
            },
            yaxis: [
                {
                    title: { text: 'Pendapatan', style: { fontSize: '12px', color: '#a39789' } },
                    labels: {
                        formatter: function (val) {
                            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                            return 'Rp ' + val;
                        },
                        style: { colors: '#a39789', fontSize: '12px' },
                    },
                },
                {
                    opposite: true,
                    title: { text: 'Transaksi', style: { fontSize: '12px', color: '#a39789' } },
                    labels: {
                        style: { colors: '#a39789', fontSize: '12px' },
                    },
                },
            ],
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left',
                fontFamily: 'Outfit',
                markers: { radius: 99 },
            },
            grid: { borderColor: '#f3efeb', strokeDashArray: 4 },
            tooltip: {
                shared: true,
                y: {
                    formatter: function (val, { seriesIndex }) {
                        if (seriesIndex === 0) return 'Rp ' + val.toLocaleString('id-ID');
                        return val + ' transaksi';
                    }
                }
            },
        });
        lineChart.render();
    }
});
</script>
@endpush