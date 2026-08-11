@php
    $cartItems = \App\Support\MemberCart::items();
    $cartJumlah = collect($cartItems)->sum('jumlah');
    $cartTotal = collect($cartItems)->sum(fn ($item) => $item['harga_satuan'] * $item['jumlah']);
@endphp

@if ($cartJumlah > 0)
    <a href="{{ route('member.checkout.index') }}"
       onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
       class="fixed inset-x-5 sm:inset-x-8 md:inset-x-10 bottom-24 sm:bottom-32 z-40 mx-auto flex w-auto max-w-none items-center justify-between gap-3 rounded-2xl bg-brand-800 px-4 py-3.5 text-white shadow-xl shadow-brand-900/30 transition active:scale-[0.98] sm:max-w-lg">
        <span class="flex items-center gap-3 min-w-0">
            <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                    <path d="M4 10.5V20a1 1 0 0 0 1 1h4v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5h4a1 1 0 0 0 1-1v-9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2.5 10 12 3l9.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] font-extrabold text-white">{{ $cartJumlah }}</span>
            </span>
            <span class="min-w-0 text-left">
                <span class="block text-xs font-bold leading-tight">Your Cart</span>
                <span class="block text-[11px] leading-tight text-white/70">{{ $cartJumlah }} item</span>
            </span>
        </span>
        <span class="flex shrink-0 items-center gap-1.5 text-sm font-extrabold">
            Rp {{ number_format($cartTotal, 0, ',', '.') }}
            @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])
        </span>
    </a>
@endif
