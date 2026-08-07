@extends('member.layout', ['title' => 'Beranda'])

@section('content')

    <div class="pt-4">
        @auth('member')
            <p class="text-sm font-semibold text-gray-500">Halo, <span class="text-base font-bold text-gray-900">{{ explode(' ', $member->nama)[0] }}</span> 👋</p>
            <p class="mb-4 text-xs text-gray-400">Terima kasih sudah jadi pelanggan setia Saisho.</p>

            @include('member.components.member-card', ['member' => $member])

            {{-- Aksi cepat Member --}}
            <div class="mt-5 grid grid-cols-2 gap-3">
                <a href="{{ route('member.reward.index') }}" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-brand-200">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                        @include('member.components.icon-gift', ['class' => 'h-5 w-5'])
                    </span>
                    <span>
                        <span class="block text-xs font-bold text-gray-900">Tukar Poin</span>
                        <span class="block text-[11px] text-gray-400">Lihat katalog</span>
                    </span>
                </a>
                <a href="{{ route('member.riwayat') }}" class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-brand-200">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                        @include('member.components.icon-clock', ['class' => 'h-5 w-5'])
                    </span>
                    <span>
                        <span class="block text-xs font-bold text-gray-900">Riwayat</span>
                        <span class="block text-[11px] text-gray-400">Poin & redeem</span>
                    </span>
                </a>
            </div>
        @else
            {{-- Guest Welcome Card --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#5a3c27] via-[#704b30] to-[#8b5e3c] p-6 text-white shadow-md">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur">
                    Guest Mode
                </span>
                <h2 class="mt-2 text-xl font-extrabold tracking-tight">Selamat Datang di Saisho!</h2>
                <p class="mt-1 text-xs text-brand-100 leading-relaxed">Nikmati aneka kue, roti & minuman segar spesial. Login sekarang untuk kumpulkan poin & voucher gratis.</p>
                <div class="mt-5 flex items-center gap-3">
                    <a href="{{ route('member.login') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-brand-800 shadow-sm hover:bg-brand-50">
                        Login Member
                    </a>
                    <a href="{{ route('member.register') }}" class="rounded-xl border border-white/40 px-4 py-2.5 text-xs font-bold text-white backdrop-blur hover:bg-white/10">
                        Daftar Akun
                    </a>
                </div>
            </div>
        @endauth

        {{-- Reward unggulan --}}
        <div class="mt-7 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Spesial Katalog Reward</h2>
            @auth('member')
                <a href="{{ route('member.reward.index') }}" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @else
                <a href="{{ route('member.welcome') }}" class="flex items-center gap-1 text-xs font-bold text-brand-800 hover:underline">
                    Lihat semua
                    @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </a>
            @endauth
        </div>

        <div class="mt-3 space-y-3">
            @forelse ($rewardUnggulan as $reward)
                @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center text-xs font-medium text-gray-400">
                    Belum ada reward tersedia saat ini.
                </div>
            @endforelse
        </div>
    </div>

@endsection
