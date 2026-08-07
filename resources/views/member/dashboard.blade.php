@extends('member.layout', ['title' => 'Beranda'])

@section('content')

    <p class="text-theme-sm text-gray-500">Halo, <span class="font-semibold text-gray-800">{{ explode(' ', $member->nama)[0] }}</span> 👋</p>
    <p class="mb-4 text-theme-xs text-gray-400">Terima kasih sudah jadi pelanggan setia kami.</p>

    @include('member.components.member-card', ['member' => $member])

    {{-- Aksi cepat --}}
    <div class="mt-5 grid grid-cols-2 gap-3">
        <a href="{{ route('member.reward.index') }}" class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-theme-xs">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                @include('member.components.icon-gift', ['class' => 'h-5 w-5'])
            </span>
            <span>
                <span class="block text-theme-sm font-semibold text-gray-800">Tukar Poin</span>
                <span class="block text-[11px] text-gray-400">Lihat katalog</span>
            </span>
        </a>
        <a href="{{ route('member.riwayat') }}" class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-theme-xs">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600">
                @include('member.components.icon-clock', ['class' => 'h-5 w-5'])
            </span>
            <span>
                <span class="block text-theme-sm font-semibold text-gray-800">Riwayat</span>
                <span class="block text-[11px] text-gray-400">Poin & redeem</span>
            </span>
        </a>
    </div>

    {{-- Reward unggulan --}}
    <div class="mt-7 flex items-center justify-between">
        <h2 class="text-theme-sm font-semibold text-gray-800">Bisa kamu tukar sekarang</h2>
        <a href="{{ route('member.reward.index') }}" class="flex items-center text-theme-xs font-medium text-brand-600">
            Lihat semua
            @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
        </a>
    </div>

    <div class="mt-3 space-y-2.5">
        @forelse ($rewardUnggulan as $reward)
            @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
        @empty
            <p class="rounded-2xl bg-white p-4 text-center text-theme-sm text-gray-400">Belum ada reward tersedia saat ini.</p>
        @endforelse
    </div>

@endsection
