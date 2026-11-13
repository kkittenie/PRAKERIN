@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail {{ $shift->labelShift() }}" parentTitle="Rekap Shift" parentUrl="{{ route('rekap-shift.index') }}" />

    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    @endphp

    <div class="space-y-6">

        {{-- Info shift --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $shift->labelShift() }} · {{ $shift->tanggal->translatedFormat('l, d M Y') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dikerjakan oleh {{ $shift->nama_kasir }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($shift->sedangBerjalan())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>Sedang berjalan
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Selesai</span>
                    @endif
                    <a href="{{ route('rekap-shift.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Kembali</a>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Mulai</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ optional($shift->mulai_pada)->format('H:i') }} WIB</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Selesai</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $shift->selesai_pada ? $shift->selesai_pada->translatedFormat('d M, H:i') . ' WIB' : 'Belum selesai' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Durasi</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                        @php $menit = optional($shift->mulai_pada)->diffInMinutes($shift->selesai_pada ?? now()); @endphp
                        {{ intdiv((int) $menit, 60) }} jam {{ (int) $menit % 60 }} menit
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Total Transaksi</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</p>
                </div>
            </div>
            @if ($shift->catatan_mulai || $shift->catatan_tutup)
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    @if ($shift->catatan_mulai)
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 p-3"><p class="text-xs text-gray-400 mb-1">Catatan awal shift</p><p class="text-gray-700 dark:text-gray-300">{{ $shift->catatan_mulai }}</p></div>
                    @endif
                    @if ($shift->catatan_tutup)
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 p-3"><p class="text-xs text-gray-400 mb-1">Catatan akhir shift</p><p class="text-gray-700 dark:text-gray-300">{{ $shift->catatan_tutup }}</p></div>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Rekonsiliasi laci --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
                <h3 class="text-base font-bold text-gray-800 dark:text-white mb-4">Uang Laci</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><dt>Uang laci awal</dt><dd class="font-semibold">{{ $rp($ringkasan['modal_awal']) }}</dd></div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><dt>+ Penjualan tunai</dt><dd class="font-semibold">{{ $rp($ringkasan['tunai']['nominal']) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-100 dark:border-gray-800 pt-3 text-gray-800 dark:text-white"><dt class="font-semibold">Seharusnya di laci</dt><dd class="font-bold">{{ $rp($ringkasan['uang_diharapkan']) }}</dd></div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><dt>Uang laci akhir (input kasir)</dt><dd class="font-semibold">{{ $shift->uang_akhir !== null ? $rp($shift->uang_akhir) : 'Belum diinput' }}</dd></div>
                    <div class="flex justify-between items-center border-t border-gray-100 dark:border-gray-800 pt-3 text-gray-800 dark:text-white">
                        <dt class="font-semibold">Selisih</dt>
                        <dd>@include('pages.shift-kasir.partials.badge-selisih', ['shift' => $shift])</dd>
                    </div>
                </dl>
            </div>

            {{-- Rincian pembayaran --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800"><h3 class="text-base font-bold text-gray-800 dark:text-white">Rincian Pembayaran</h3></div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-3 font-semibold">Metode</th>
                            <th class="px-6 py-3 font-semibold text-right">Transaksi</th>
                            <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr><td class="px-6 py-3">Tunai</td><td class="px-6 py-3 text-right">{{ $ringkasan['tunai']['jumlah'] }}</td><td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['tunai']['nominal']) }}</td></tr>
                        <tr><td class="px-6 py-3">QRIS</td><td class="px-6 py-3 text-right">{{ $ringkasan['qris']['jumlah'] }}</td><td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['qris']['nominal']) }}</td></tr>
                        <tr><td class="px-6 py-3">Debit</td><td class="px-6 py-3 text-right">{{ $ringkasan['debit']['jumlah'] }}</td><td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['debit']['nominal']) }}</td></tr>
                        @foreach ($ringkasan['lainnya']['rincian'] as $b)
                            <tr><td class="px-6 py-3">{{ $b['metode'] }}</td><td class="px-6 py-3 text-right">{{ $b['jumlah'] }}</td><td class="px-6 py-3 text-right font-semibold">{{ $rp($b['nominal']) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50 text-gray-800 dark:text-white">
                        <tr><td class="px-6 py-3 font-semibold">Total penjualan</td><td class="px-6 py-3 text-right font-semibold">{{ $ringkasan['jumlah_transaksi'] }}</td><td class="px-6 py-3 text-right font-bold">{{ $rp($ringkasan['total_penjualan']) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Daftar transaksi --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800"><h3 class="text-base font-bold text-gray-800 dark:text-white">Transaksi di Shift Ini</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-3 font-semibold">Waktu</th>
                            <th class="px-6 py-3 font-semibold">Kode</th>
                            <th class="px-6 py-3 font-semibold">Pembeli</th>
                            <th class="px-6 py-3 font-semibold">Metode</th>
                            <th class="px-6 py-3 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($transaksi as $t)
                            <tr>
                                <td class="px-6 py-3">{{ $t->created_at->format('d M, H:i') }}</td>
                                <td class="px-6 py-3 font-mono text-xs">{{ $t->kode_transaksi }}</td>
                                <td class="px-6 py-3">{{ $t->nama_pembeli }}</td>
                                <td class="px-6 py-3 capitalize">{{ $t->metode_pembayaran }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ $rp($t->total_bayar) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">Belum ada transaksi di shift ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transaksi->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">{{ $transaksi->links() }}</div>
            @endif
        </div>
    </div>
@endsection