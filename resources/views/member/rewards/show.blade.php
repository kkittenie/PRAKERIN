@extends('member.layout', ['title' => $reward->nama])

@php
    $cukupPoin = $member->poin >= $reward->poin_dibutuhkan;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $bisaDitukar = $cukupPoin && ! $habis;
    $kurang = max(0, $reward->poin_dibutuhkan - $member->poin);
    $icon = $reward->jenis === 'produk' ? 'icon-cup' : 'icon-ticket';
@endphp

@section('content')

    <a href="{{ route('member.reward.index') }}" class="inline-flex items-center gap-1 text-theme-xs font-medium text-gray-400">
        <span class="rotate-180"> @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5']) </span>
        Kembali ke katalog
    </a>

    <div class="mt-4 overflow-hidden rounded-3xl bg-gradient-to-br {{ $reward->jenis === 'produk' ? 'from-orange-400 to-orange-600' : 'from-brand-500 to-brand-700' }} p-8 text-center text-white">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15">
            @include('member.components.'.$icon, ['class' => 'h-8 w-8'])
        </span>
        <p class="mt-4 text-title-sm font-bold leading-tight">{{ $reward->nama }}</p>
        <p class="mt-1 text-theme-sm text-white/75">{{ $reward->jenis === 'produk' ? 'Produk gratis' : 'Voucher diskon' }}</p>
    </div>

    <div class="mt-5 rounded-2xl bg-white p-4">
        <p class="text-theme-xs font-medium uppercase tracking-wide text-gray-400">Deskripsi</p>
        <p class="mt-1.5 text-theme-sm leading-relaxed text-gray-600">{{ $reward->deskripsi ?: 'Tidak ada deskripsi tambahan untuk reward ini.' }}</p>
    </div>

    <div class="mt-3 flex items-center justify-between rounded-2xl bg-white p-4">
        <div>
            <p class="text-theme-xs text-gray-400">Dibutuhkan</p>
            <p class="text-theme-lg font-bold text-gray-800">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin</p>
        </div>
        <div class="text-right">
            <p class="text-theme-xs text-gray-400">Poin kamu</p>
            <p class="text-theme-lg font-bold {{ $cukupPoin ? 'text-success-600' : 'text-error-500' }}">{{ number_format($member->poin, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="sticky bottom-20 mt-5">
        @if ($habis)
            <button disabled class="w-full rounded-2xl bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Stok sudah habis</button>
        @elseif (! $cukupPoin)
            <button disabled class="w-full rounded-2xl bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Kurang {{ number_format($kurang, 0, ',', '.') }} poin lagi</button>
        @else
            <button type="button" onclick="document.getElementById('konfirmasi-tukar').showModal()"
                    class="w-full rounded-2xl bg-brand-600 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-700">
                Tukar Sekarang
            </button>
        @endif
    </div>

    <dialog id="konfirmasi-tukar" class="w-[calc(100%-2.5rem)] max-w-sm rounded-2xl p-0 backdrop:bg-gray-900/40">
        <div class="p-5 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                @include('member.components.icon-gift', ['class' => 'h-6 w-6'])
            </span>
            <p class="mt-3 text-theme-md font-semibold text-gray-800">Tukar {{ $reward->nama }}?</p>
            <p class="mt-1 text-theme-sm text-gray-500">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin akan dipotong dari saldo kamu dan tidak bisa dibatalkan.</p>

            <form method="POST" action="{{ route('member.reward.tukar', $reward) }}" class="mt-5 space-y-2">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-brand-600 py-3 text-theme-sm font-semibold text-white">Ya, Tukar Sekarang</button>
                <button type="button" onclick="document.getElementById('konfirmasi-tukar').close()" class="w-full rounded-xl py-3 text-theme-sm font-medium text-gray-500">Batal</button>
            </form>
        </div>
    </dialog>

@endsection
