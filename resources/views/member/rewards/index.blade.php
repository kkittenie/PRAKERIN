@extends('member.layout', ['title' => 'Tukar Reward'])

@section('content')

    <h1 class="text-title-sm font-bold text-gray-800">Tukar Poin</h1>
    <p class="mt-1 flex items-center gap-1.5 text-theme-sm text-gray-500">
        Poin kamu saat ini:
        <span class="font-semibold text-brand-600">{{ number_format($member->poin, 0, ',', '.') }}</span>
    </p>

    {{-- Filter tab --}}
    <div class="mt-4 flex gap-2">
        @foreach ([null => 'Semua', 'voucher' => 'Voucher', 'produk' => 'Produk'] as $val => $label)
            <a href="{{ route('member.reward.index', $val ? ['jenis' => $val] : []) }}"
               class="rounded-full px-4 py-1.5 text-theme-xs font-semibold {{ $jenisAktif === $val ? 'bg-brand-600 text-white' : 'bg-white text-gray-500 border border-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-4 space-y-2.5">
        @forelse ($rewards as $reward)
            @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
        @empty
            <div class="rounded-2xl bg-white p-6 text-center">
                <p class="text-theme-sm font-medium text-gray-500">Belum ada reward di kategori ini.</p>
                <p class="mt-1 text-theme-xs text-gray-400">Coba cek kategori lain, ya.</p>
            </div>
        @endforelse
    </div>

@endsection
