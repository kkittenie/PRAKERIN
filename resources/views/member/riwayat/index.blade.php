@extends('member.layout', ['title' => 'Riwayat'])

@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Menunggu diambil', 'class' => 'bg-warning-50 text-warning-600'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-success-50 text-success-600'],
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
            <h2 class="mb-2.5 mt-6 text-theme-sm font-semibold text-gray-800">Penukaran Reward</h2>
            <div class="space-y-2.5">
                @foreach ($penukaran as $item)
                    <div class="rounded-2xl bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-theme-sm font-semibold text-gray-800">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</p>
                                <p class="mt-0.5 text-theme-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                {{ $statusBadge[$item->status]['label'] }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-dashed border-gray-200 pt-3">
                            <div>
                                <p class="text-[11px] text-gray-400">Kode redeem</p>
                                <p class="font-mono text-theme-sm font-semibold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                            </div>
                            <p class="text-theme-sm font-semibold text-error-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($riwayatStamp->isNotEmpty())
            <h2 class="mb-2.5 mt-7 text-theme-sm font-semibold text-gray-800">Penukaran Stamp</h2>
            <div class="space-y-2.5">
                @foreach ($riwayatStamp as $item)
                    <div class="rounded-2xl bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-theme-sm font-semibold text-gray-800">{{ $item->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                                <p class="mt-0.5 text-theme-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                {{ $statusBadge[$item->status]['label'] }}
                            </span>
                        </div>
                        <div class="mt-3 border-t border-dashed border-gray-200 pt-3">
                            <p class="text-[11px] text-gray-400">Kode redeem</p>
                            <p class="font-mono text-theme-sm font-semibold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($transaksi->isNotEmpty())
            <h2 class="mb-2.5 mt-7 text-theme-sm font-semibold text-gray-800">Poin dari Transaksi</h2>
            <div class="space-y-2.5">
                @foreach ($transaksi as $trx)
                    <div class="flex items-center justify-between rounded-2xl bg-white p-4">
                        <div class="min-w-0">
                            <p class="truncate text-theme-sm font-semibold text-gray-800">{{ $trx->kode_transaksi }}</p>
                            <p class="mt-0.5 text-theme-xs text-gray-400">{{ $trx->created_at->translatedFormat('d M Y, H:i') }} &middot; Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                        </div>
                        <p class="shrink-0 text-theme-sm font-semibold text-success-600">+{{ number_format($trx->poin_didapat ?? 0, 0, ',', '.') }} poin</p>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

@endsection