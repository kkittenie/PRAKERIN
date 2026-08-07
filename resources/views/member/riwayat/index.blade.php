@extends('member.layout', ['title' => 'Riwayat'])

@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Menunggu diambil', 'class' => 'bg-warning-50 text-warning-600'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-success-50 text-success-600'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500'],
    ];
@endphp

@section('content')

    <h1 class="text-title-sm font-bold text-gray-800">Riwayat</h1>
    <p class="mt-1 text-theme-sm text-gray-500">Pantau penukaran reward dan perolehan poin kamu.</p>

    <h2 class="mb-2.5 mt-6 text-theme-sm font-semibold text-gray-800">Penukaran Reward</h2>
    <div class="space-y-2.5">
        @forelse ($penukaran as $item)
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
        @empty
            <p class="rounded-2xl bg-white p-4 text-center text-theme-sm text-gray-400">Belum ada reward yang ditukar.</p>
        @endforelse
    </div>

    <h2 class="mb-2.5 mt-7 text-theme-sm font-semibold text-gray-800">Poin dari Transaksi</h2>
    <div class="space-y-2.5">
        @forelse ($transaksi as $trx)
            <div class="flex items-center justify-between rounded-2xl bg-white p-4">
                <div class="min-w-0">
                    <p class="truncate text-theme-sm font-semibold text-gray-800">{{ $trx->kode_transaksi }}</p>
                    <p class="mt-0.5 text-theme-xs text-gray-400">{{ $trx->created_at->translatedFormat('d M Y, H:i') }} &middot; Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                </div>
                <p class="shrink-0 text-theme-sm font-semibold text-success-600">+{{ number_format($trx->poin_didapat ?? 0, 0, ',', '.') }} poin</p>
            </div>
        @empty
            <p class="rounded-2xl bg-white p-4 text-center text-theme-sm text-gray-400">Belum ada transaksi yang tercatat.</p>
        @endforelse
    </div>

@endsection
