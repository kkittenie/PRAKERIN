@props(['reward', 'member' => null])

@php
    $cukupPoin = $member ? $member->poin >= $reward->poin_dibutuhkan : false;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $icon = $reward->jenis === 'produk' ? 'icon-cup' : 'icon-ticket';
@endphp

<a href="{{ route('member.reward.show', $reward) }}"
   class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-3.5 transition {{ $habis ? 'opacity-50' : 'hover:border-brand-200 hover:bg-brand-25' }}">
    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $reward->jenis === 'produk' ? 'bg-orange-50 text-orange-500' : 'bg-brand-50 text-brand-600' }}">
        @include('member.components.'.$icon, ['class' => 'h-6 w-6'])
    </span>

    <div class="min-w-0 flex-1">
        <p class="truncate text-theme-sm font-semibold text-gray-800">{{ $reward->nama }}</p>
        <p class="mt-0.5 text-theme-xs text-gray-400">{{ $reward->jenis === 'produk' ? 'Produk gratis' : 'Voucher diskon' }}</p>
    </div>

    <div class="shrink-0 text-right">
        <p class="text-theme-sm font-bold {{ $cukupPoin && ! $habis ? 'text-brand-600' : 'text-gray-400' }}">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }}</p>
        <p class="text-[11px] text-gray-400">poin</p>
    </div>
</a>
