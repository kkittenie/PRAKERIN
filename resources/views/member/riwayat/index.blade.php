@extends('member.layout', ['title' => 'Riwayat'])

@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Menunggu diambil', 'class' => 'bg-amber-50 text-amber-700'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-50 text-green-600'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500'],
    ];
    // Optional — history of stamp-card redemptions (separate from poin-based
    // reward/voucher redemptions in $penukaran). Falls back to empty so this
    // page still renders fine before the controller passes it in.
    $riwayatStamp = $riwayatStamp ?? collect();
    $isEmpty = $penukaran->isEmpty() && $transaksi->isEmpty() && $riwayatStamp->isEmpty();
@endphp

@section('content')

    <h1 class="text-center text-lg font-extrabold text-gray-900">Riwayat</h1>

    @if ($isEmpty)
        @include('member.components.empty-state', [
            'title' => 'Belum Ada Riwayat',
            'description' => 'Semua transaksi, penukaran reward, dan penukaran stamp kamu bakal kecatat di sini.',
            'icon' => 'icon-clock',
            'iconBg' => 'bg-gray-100',
            'iconColor' => 'text-gray-400',
        ])
    @else
        @if ($penukaran->isNotEmpty())
            <div class="mt-6 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Penukaran Reward</h2>
                <span class="text-xs text-gray-400">{{ $penukaran->count() }} riwayat</span>
            </div>
            <div class="mt-3 space-y-3">
                @foreach ($penukaran as $item)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                @include('member.components.icon-gift', ['class' => 'h-5 w-5'])
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-bold text-gray-900">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</p>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                        {{ $statusBadge[$item->status]['label'] }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-dashed border-gray-100 pt-3">
                            <div>
                                <p class="text-[11px] text-gray-400">Kode redeem</p>
                                <p class="font-mono text-sm font-bold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                            </div>
                            <p class="text-sm font-bold text-red-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($riwayatStamp->isNotEmpty())
            <div class="mt-7 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Penukaran Stamp</h2>
                <span class="text-xs text-gray-400">{{ $riwayatStamp->count() }} riwayat</span>
            </div>
            <div class="mt-3 space-y-3">
                @foreach ($riwayatStamp as $item)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                                @include('member.components.icon-cup', ['class' => 'h-5 w-5'])
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-bold text-gray-900">{{ $item->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                        {{ $statusBadge[$item->status]['label'] }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 border-t border-dashed border-gray-100 pt-3">
                            <p class="text-[11px] text-gray-400">Kode redeem</p>
                            <p class="font-mono text-sm font-bold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($transaksi->isNotEmpty())
            <div class="mt-7 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Poin dari Transaksi</h2>
                <span class="text-xs text-gray-400">{{ $transaksi->count() }} transaksi</span>
            </div>
            <div class="mt-3 space-y-3">
                @foreach ($transaksi as $trx)
                    <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                            @include('member.components.icon-check-circle', ['class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900">{{ $trx->kode_transaksi }}</p>
                            <p class="mt-0.5 text-xs text-gray-400">{{ $trx->created_at->translatedFormat('d M Y, H:i') }} &middot; Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-green-600">+{{ number_format($trx->poin_didapat ?? 0, 0, ',', '.') }} poin</p>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

@endsection