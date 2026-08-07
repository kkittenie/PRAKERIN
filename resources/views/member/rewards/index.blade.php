@extends('member.layout', ['title' => 'Vouchers'])

@section('content')

    <h1 class="text-center text-lg font-extrabold text-gray-900">Vouchers</h1>

    <div class="mt-3 flex items-center justify-center gap-1.5 rounded-2xl border border-gray-100 bg-brand-25 py-2.5 text-xs text-gray-500">
        Poin kamu saat ini:
        <span class="font-bold text-brand-800">{{ number_format($member->poin, 0, ',', '.') }}</span>
    </div>

    {{-- Fore-style underline tabs, stretched full width --}}
    <div class="mt-5 grid grid-cols-3 border-b border-gray-100">
        @foreach ([null => 'Semua', 'voucher' => 'Voucher', 'produk' => 'Produk'] as $val => $label)
            @php $isActive = $jenisAktif === $val; @endphp
            <a href="{{ route('member.reward.index', $val ? ['jenis' => $val] : []) }}"
               class="relative pb-3 text-center text-[13px] font-bold transition {{ $isActive ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                {{ $label }}
                @if ($isActive)
                    <span class="absolute inset-x-0 -bottom-px mx-auto h-[2.5px] w-10 rounded-full bg-brand-800"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Section header with count, Fore-style --}}
    <div class="mt-5 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-900">Katalog Reward</h2>
        <span class="text-xs text-gray-400">{{ $rewards->count() }} reward</span>
    </div>

    <div class="mt-3 space-y-3">
        @forelse ($rewards as $reward)
            @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
        @empty
            <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center">
                <p class="text-sm font-semibold text-gray-500">Belum ada reward di kategori ini.</p>
                <p class="mt-1 text-xs text-gray-400">Coba cek kategori lain, ya.</p>
            </div>
        @endforelse
    </div>

@endsection
