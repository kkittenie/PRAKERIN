@extends('mobile.layout', ['title' => 'Activity'])

@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Awaiting Pickup', 'class' => 'bg-amber-50 text-amber-700'],
        'selesai' => ['label' => 'Completed', 'class' => 'bg-green-50 text-green-600'],
        'dibatalkan' => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-500'],
    ];
    $statusPesananBadge = [
        \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Preparing', 'class' => 'bg-amber-50 text-amber-700'],
        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Ready for Pickup', 'class' => 'bg-blue-50 text-blue-700'],
        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Completed', 'class' => 'bg-green-50 text-green-600'],
        \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-500'],
    ];

    $pesananStatusInfo = function ($trx) use ($statusPesananBadge) {
        if ($trx->status_pesanan === \App\Models\Transaksi::STATUS_DIBATALKAN) {
            return $statusPesananBadge[\App\Models\Transaksi::STATUS_DIBATALKAN];
        }

        $lunas = $trx->status_pembayaran === 'Lunas';

        if (! $lunas) {
            return ['label' => 'Awaiting Payment', 'class' => 'bg-amber-50 text-amber-700'];
        }

        if (! $trx->diproses_pada) {
            return ['label' => 'Confirmed', 'class' => 'bg-blue-50 text-blue-700'];
        }

        return $statusPesananBadge[$trx->status_pesanan] ?? null;
    };
    $riwayatStamp = $riwayatStamp ?? collect();
    $riwayat = $riwayat ?? collect();
    $adaAturanPoin = \App\Models\PengaturanMember::adaAturanAktif();

    // Tab Voucher disembunyikan sementara (kodenya tetap ada). Ubah ke true untuk menampilkannya lagi.
    $tampilTabVoucher = false;

    $daftarTab = ['riwayat' => 'Orders', 'voucher' => 'Vouchers', 'stamp' => 'Stamps'];
    if (! $tampilTabVoucher) {
        unset($daftarTab['voucher']);
    }

    // Riwayat penukaran voucher tidak dihitung kalau tab-nya disembunyikan.
    $isEmpty = ($tampilTabVoucher ? $penukaran->isEmpty() : true) && $riwayat->isEmpty() && $riwayatStamp->isEmpty();
@endphp

@section('content')

    <style>
        [x-cloak] { display: none !important; }

        .redeem-voucher-ticket {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            background: #FDFBF7;
            border: 1px solid rgba(60, 36, 21, 0.1);
            box-shadow: 0 2px 8px rgba(60, 36, 21, 0.04);
        }

        .redeem-voucher-ticket::before,
        .redeem-voucher-ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #FAF5E8;
            border: 1px solid rgba(60, 36, 21, 0.1);
            transform: translateY(-50%);
            z-index: 2;
        }

        .redeem-voucher-ticket::before { left: -9px; }
        .redeem-voucher-ticket::after { right: -9px; }

        .redeem-voucher-ticket__body {
            position: relative;
            z-index: 1;
            padding: 16px;
        }

        .redeem-voucher-ticket__cut {
            border-top: 1px dashed #eadfd5;
        }
    </style>

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-0 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
            <h1 class="text-center text-lg font-extrabold text-gray-900">Activity</h1>

            @unless ($isEmpty)
                <div class="relative mt-4 grid {{ count($daftarTab) === 3 ? 'grid-cols-3' : 'grid-cols-2' }} border-b border-gray-100">
                    @foreach ($daftarTab as $key => $label)
                        <button type="button" data-tab="{{ $key }}" onclick="switchRiwayatTab('{{ $key }}')"
                                class="riwayat-tab relative pb-3 text-center text-[13px] font-bold transition {{ $key === 'riwayat' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                    <span id="riwayat-tab-indicator"
                          class="pointer-events-none absolute -bottom-px left-0 h-[2.5px] {{ count($daftarTab) === 3 ? 'w-1/3' : 'w-1/2' }} rounded-full bg-brand-800 transition-transform duration-300 ease-out"
                          style="transform: translateX(0%);"></span>
                </div>
            @endunless
        </div>

        @if ($isEmpty)
            @include('mobile.components.empty-state', [
                'title' => 'No Activity Yet',
                'description' => 'Your orders, reward redemptions, and stamp collections will appear here.',
                'icon' => 'icon-clock',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-400',
            ])
        @else
            <div id="riwayat-tab-viewport" class="relative flex flex-1 flex-col overflow-hidden">
                <div id="riwayat-tab-track" class="flex flex-1 transition-transform duration-300 ease-out">

            {{-- TAB: RIWAYAT MOBILE --}}
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-transparent pt-5" data-pane-index="0">
                @if ($riwayat->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'No Orders Yet',
                        'description' => 'Your handcrafted orders and bakery purchases will appear here.',
                        'icon' => 'icon-clock',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    @php
                        $statusAktif = [\App\Models\Transaksi::STATUS_DIPROSES, \App\Models\Transaksi::STATUS_SIAP_DIAMBIL];
                        $riwayatAktif = $riwayat->filter(fn ($trx) => $trx->asal_pesanan === \App\Models\Transaksi::ASAL_MEMBER_APP
                            && in_array($trx->status_pesanan, $statusAktif));
                        $riwayatLain = $riwayat->reject(fn ($trx) => $riwayatAktif->contains('id', $trx->id));
                    @endphp

                    @if ($riwayatAktif->isNotEmpty())
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Active Orders</h2>
                            <span class="text-xs text-gray-400">{{ $riwayatAktif->count() }} {{ $riwayatAktif->count() === 1 ? 'order' : 'orders' }}</span>
                        </div>
                        <div class="mt-3 space-y-3">
                            @foreach ($riwayatAktif as $trx)
                                @php $pesananBadge = $pesananStatusInfo($trx); @endphp
                                <a
                                    href="{{ route('member.pesanan.show', $trx) }}"
                                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                    class="flex w-full items-center gap-3 rounded-2xl border border-brand-200 bg-brand-25 p-4 text-left shadow-sm transition active:bg-brand-50"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-800">
                                        @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $trx->kode_transaksi }}</p>
                                            @if ($pesananBadge)
                                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pesananBadge['class'] }}">
                                                    {{ $pesananBadge['label'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 text-xs text-gray-400">Online &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    </div>
                                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 shrink-0 text-gray-300'])
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($riwayatLain->isNotEmpty())
                        <div class="{{ $riwayatAktif->isNotEmpty() ? 'mt-6' : '' }} flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Order History</h2>
                            <span class="text-xs text-gray-400">{{ $riwayatLain->count() }} {{ $riwayatLain->count() === 1 ? 'order' : 'orders' }}</span>
                        </div>
                        <div class="mt-3 space-y-3">
                            @foreach ($riwayatLain as $trx)
                                @php $isOnline = $trx->asal_pesanan === \App\Models\Transaksi::ASAL_MEMBER_APP; @endphp
                                @if ($isOnline)
                                    @php
                                        $pesananBadge = $pesananStatusInfo($trx);
                                        $sudahDiambil = $trx->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL;
                                    @endphp
                                    <a
                                        href="{{ route('member.pesanan.show', $trx) }}"
                                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                        class="flex w-full items-center gap-3 rounded-2xl border border-[#3C2415]/10 bg-[#FDFBF7] p-4 text-left shadow-xs transition active:bg-[#F5ECE3]"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FAF5E8] text-brand-800 border border-[#3C2415]/10">
                                            @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="truncate text-sm font-bold text-gray-900">{{ $trx->kode_transaksi }}</p>
                                                @if ($pesananBadge)
                                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pesananBadge['class'] }}">
                                                        {{ $pesananBadge['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-xs text-gray-400">Online &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                        {{-- Poin dari transaksi ini disembunyikan di daftar riwayat; hanya total yang belum diambil yang tampil --}}
                                        @unless ($sudahDiambil)
                                            <p class="shrink-0 text-sm font-bold text-gray-400">
                                                {{ 'Rp' . number_format($trx->total_bayar, 0, ',', '.') }}
                                            </p>
                                        @endunless
                                    </a>
                                @else
                                    @php
                                        $belumLunas = strtolower($trx->status_pembayaran ?? 'lunas') !== 'lunas';
                                        $itemsJson = $trx->detailTransaksi
                                            ->reject(fn ($i) => ($i->bundling_id || $i->promo_event_id) && $i->produk_id && (float) $i->subtotal == 0)
                                            ->groupBy(function ($item) {
                                                return $item->nama_produk . '_' . ($item->ukuran ?? 'std') . '_' . ($item->varian_rasa ?? 'none');
                                            })
                                            ->map(fn ($group) => [
                                                'nama' => $group->first()->nama_produk,
                                                'ukuran' => $group->first()->ukuran,
                                                'varian_rasa' => $group->first()->varian_rasa,
                                                'jumlah' => $group->sum('jumlah'),
                                                'subtotal' => $group->sum('subtotal'),
                                                'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                                            ])
                                            ->values()
                                            ->toJson();
                                    @endphp
                                    <button
                                        type="button"
                                        onclick="bukaDetailTransaksi(this)"
                                        data-kode="{{ $trx->kode_transaksi }}"
                                        data-tanggal="{{ $trx->created_at->translatedFormat('d M Y, H:i') }}"
                                        data-status-label="{{ $belumLunas ? 'Unpaid' : 'Paid' }}"
                                        data-status-class="{{ $belumLunas ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}"
                                        data-metode="{{ $trx->metode_pembayaran ?? '-' }}"
                                        data-subtotal="{{ $trx->subtotal }}"
                                        data-pajak="{{ $trx->pajak }}"
                                        data-diskon="{{ $trx->diskon }}"
                                        data-diskon-label="{{ $trx->diskon_label }}"
                                        data-total="{{ $trx->total_bayar }}"
                                        data-poin="{{ $trx->poin_didapat ?? 0 }}"
                                        data-items="{{ $itemsJson }}"
                                        class="flex w-full items-center gap-3 rounded-2xl border {{ $belumLunas ? 'border-red-200' : 'border-[#3C2415]/10' }} bg-[#FDFBF7] p-4 text-left shadow-xs transition active:bg-[#F5ECE3]"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $belumLunas ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }}">
                                            @if ($belumLunas)
                                                @include('mobile.components.icon-clock', ['class' => 'h-5 w-5'])
                                            @else
                                                @include('mobile.components.icon-check-circle', ['class' => 'h-5 w-5'])
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $trx->kode_transaksi }}</p>
                                            <p class="mt-0.5 text-xs text-gray-400">Offline &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                        @if ($belumLunas)
                                            <p class="shrink-0 text-sm font-bold text-red-500">
                                                {{ 'Rp' . number_format($trx->total_bayar, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            @if ($tampilTabVoucher)
            {{-- TAB: VOUCHER MOBILE --}}
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-transparent pt-5" data-pane-index="1">
                @if ($penukaran->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'No Vouchers Redeemed',
                        'description' => 'Your voucher redemptions will appear here.',
                        'icon' => 'icon-ticket',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Reward Redemptions</h2>
                        <span class="text-xs text-gray-400">{{ $penukaran->count() }} {{ $penukaran->count() === 1 ? 'redemption' : 'redemptions' }}</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        @foreach ($penukaran as $item)
                            <button
                                type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="reward"
                                data-nama="{{ $item->reward->nama ?? 'Reward not found' }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Points Used"
                                data-extra-value="-{{ number_format($item->poin_terpakai, 0, ',', '.') }} pts"
                                data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->translatedFormat('d M Y, H:i') : '' }}"
                                class="redeem-voucher-ticket w-full border border-gray-100 text-left transition active:bg-gray-50"
                            >
                                <div class="redeem-voucher-ticket__body">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-800 text-white">
                                            @include('mobile.components.icon-ticket', ['class' => 'h-5 w-5'])
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="truncate text-sm font-bold text-gray-900">{{ $item->reward->nama ?? 'Reward not found' }}</p>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                                    {{ $statusBadge[$item->status]['label'] }}
                                                </span>
                                            </div>
                                            <p class="mt-0.5 text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="redeem-voucher-ticket__cut mt-3 pt-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-[11px] text-gray-400">Redeem Code</p>
                                                <p class="font-mono text-sm font-bold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                                            </div>
                                            @if ($adaAturanPoin)<p class="text-sm font-bold text-red-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }} pts</p>@endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- TAB: STAMP MOBILE --}}
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-transparent pt-5" data-pane-index="{{ $tampilTabVoucher ? 2 : 1 }}">
                @if ($riwayatStamp->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'No Stamp Rewards Claimed',
                        'description' => 'Your stamp reward redemptions will appear here.',
                        'icon' => 'icon-cake',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Stamp Redemptions</h2>
                        <span class="text-xs text-gray-400">{{ $riwayatStamp->count() }} {{ $riwayatStamp->count() === 1 ? 'reward' : 'rewards' }}</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        @foreach ($riwayatStamp as $item)
                            <button
                                type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="stamp"
                                data-id="{{ $item->id }}"
                                data-status-raw="{{ $item->status }}"
                                data-nama="{{ ($item->produk ? $item->nama_tampil : 'Product unavailable') }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Daisy Stamp Club"
                                data-extra-value="{{ $item->stampProgram?->nama_program ?? 'Stamp Program' }}"
                                data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->translatedFormat('d M Y, H:i') : '' }}"
                                class="w-full rounded-2xl border border-[#3C2415]/10 bg-[#FDFBF7] p-4 text-left shadow-xs transition active:bg-[#F5ECE3]"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FAF5E8] p-2 text-brand-800 border border-[#3C2415]/10">
                                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ ($item->produk ? $item->nama_tampil : 'Product unavailable') }}</p>
                                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                                {{ $statusBadge[$item->status]['label'] }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $item->stampProgram?->nama_program ?? 'Stamp Program' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 border-t border-dashed border-gray-100 pt-3">
                                    <p class="text-[11px] text-gray-400">Redeem Code</p>
                                    <p class="font-mono text-sm font-bold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

                </div>
            </div>
        @endif
    </div>


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - BESPOKE FRENCH BAKERY ACTIVITY HUB --}}
    {{-- Matches Menu & Vouchers: Full-bleed melting hero canopy, celebration cake, --}}
    {{-- rotating discover stamp, 2D floating strawberries, and scallop-badge tabs. --}}
    {{-- ========================================== --}}
    <div id="activity-desktop-root" class="hidden lg:block w-full bg-[#FAF5E8] min-h-screen text-[#3C2415]"
         x-data="{
             desktopTab: (new URLSearchParams(window.location.search).get('tab') || 'order')
         }">

        {{-- 2. SCALLOP FILTER TABS, FLOATING STRAWBERRIES & ACTIVITY CONTENT --}}
        <section id="activity-desktop-showcase" class="relative w-full bg-[#FAF5E8] overflow-hidden">
            {{-- Strawberry Keyframe Styles (Consistent with Menu & Vouchers) --}}
            <style>
                @keyframes floatStrawberry {
                    0%, 100% {
                        transform: translateY(0px) rotate(var(--rot, 0deg));
                    }
                    50% {
                        transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg));
                    }
                }
                .desktop-floating-strawberry {
                    animation: floatStrawberry var(--duration, 4s) ease-in-out infinite;
                    will-change: transform;
                }
            </style>

            {{-- 2D Floating Strawberries in Outer Gutters --}}
            <div class="desktop-floating-strawberry hidden xl:block absolute left-8 xl:left-12 2xl:left-20 top-12 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 48px; height: 64px; --rot: -10deg; --duration: 4.2s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-8 xl:left-auto xl:right-12 2xl:right-20 top-20 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: 14deg; --duration: 3.8s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-10 xl:left-14 2xl:left-24 top-1/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 42px; height: 56px; --rot: 18deg; --duration: 4.8s;">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute right-10 xl:left-auto xl:right-14 2xl:right-24 top-1/2 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 44px; height: 58px; --rot: -14deg; --duration: 4.4s;">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>
            <div class="desktop-floating-strawberry hidden xl:block absolute left-9 xl:left-13 2xl:left-22 top-2/3 pointer-events-none select-none z-0"
                 aria-hidden="true" style="width: 46px; height: 60px; --rot: -12deg; --duration: 5.0s;">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
            </div>

            <div id="activity-showcase-content" class="relative max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-10 pb-24 z-10 space-y-12">

                {{-- Scallop Filter Tabs (Matching Menu & Vouchers Badges) --}}
                <div id="activity-scallop-tabs" class="flex flex-wrap items-center justify-center gap-8 sm:gap-12 lg:gap-16">
                    {{-- TAB 1: ORDERS --}}
                    <button type="button"
                            @click="desktopTab = 'order'"
                            class="group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="order">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'order' ? 'text-[#8E1E26] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <span class="text-[#3C2415]">
                                    @include('mobile.components.icon-cake', ['class' => 'w-6 h-6'])
                                </span>
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'order' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            ORDERS ({{ $riwayat->count() }})
                        </span>
                    </button>

                    {{-- TAB 2: STAMP REWARDS --}}
                    <button type="button"
                            @click="desktopTab = 'stamp'"
                            class="group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="stamp">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'stamp' ? 'text-[#4A2D1A] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Stamp Activity" class="w-7 h-7 object-contain">
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'stamp' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            STAMP REWARDS ({{ $riwayatStamp->count() }})
                        </span>
                    </button>

                    @if ($tampilTabVoucher)
                    {{-- TAB 3: VOUCHERS (OPTIONAL) --}}
                    <button type="button"
                            @click="desktopTab = 'voucher'"
                            class="group flex flex-col items-center transition-transform hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
                            data-desktop-tab="voucher">
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 lg:w-22 lg:h-22 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full transition-all duration-300 filter drop-shadow-xs"
                                 :class="desktopTab === 'voucher' ? 'text-[#3C2415] scale-105 rotate-6' : 'text-[#FAF5E8] stroke-2 stroke-[#3C2415]/20 group-hover:rotate-12'"
                                 viewBox="0 0 100 100" fill="currentColor">
                                <path d="M50 0 C58 0 60 12 68 15 C76 18 84 14 89 21 C94 28 88 36 91 44 C94 52 100 58 98 66 C96 74 86 78 81 85 C76 92 74 100 66 100 C58 100 54 90 46 90 C38 90 32 98 25 95 C18 92 18 80 13 74 C8 68 0 62 0 54 C0 46 10 42 12 34 C14 26 8 18 15 13 C22 8 30 14 38 11 C46 8 48 0 50 0 Z" />
                            </svg>
                            <div class="relative z-10 w-12 h-12 sm:w-13 sm:h-13 lg:w-14 lg:h-14 rounded-full bg-white/70 flex items-center justify-center overflow-hidden shadow-xs">
                                <span class="text-[#3C2415]">
                                    @include('mobile.components.icon-ticket', ['class' => 'w-6 h-6'])
                                </span>
                            </div>
                        </div>
                        <span class="mt-3 font-display text-xs sm:text-sm font-black tracking-wider uppercase transition-colors"
                              :class="desktopTab === 'voucher' ? 'text-[#8E1E26] underline decoration-2 underline-offset-4' : 'text-[#3C2415] group-hover:text-[#8E1E26]'">
                            VOUCHERS ({{ $penukaran->count() }})
                        </span>
                    </button>
                    @endif
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     PANE 1: ORDERS & IN-STORE PURCHASES
                     ═══════════════════════════════════════════════════════════ --}}
                <div x-show="desktopTab === 'order'" class="space-y-12">
                    @if ($riwayat->isEmpty())
                        <div class="rounded-[32px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center space-y-4 shadow-sm">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] text-[#8E1E26] shadow-xs">
                                @include('mobile.components.icon-clock', ['class' => 'h-8 w-8'])
                            </span>
                            <h3 class="font-display font-black text-2xl text-[#3C2415] uppercase tracking-wide">No Orders Found</h3>
                            <p class="text-xs sm:text-sm text-[#7C6558] max-w-md mx-auto leading-relaxed">
                                You haven't placed any pickup orders yet. Discover our fresh artisanal bakery menu and taste the magic!
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('member.menu') }}"
                                   class="inline-flex items-center gap-2 rounded-full bg-[#8E1E26] hover:bg-[#72181E] px-8 py-3 text-xs font-black uppercase tracking-wider text-white transition-colors shadow-md">
                                    <span>Explore Our Menu</span> &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                        @php
                            $statusAktif = [\App\Models\Transaksi::STATUS_DIPROSES, \App\Models\Transaksi::STATUS_SIAP_DIAMBIL];
                            $riwayatAktif = $riwayat->filter(fn ($trx) => $trx->asal_pesanan === \App\Models\Transaksi::ASAL_MEMBER_APP
                                && in_array($trx->status_pesanan, $statusAktif));
                            $riwayatLain = $riwayat->reject(fn ($trx) => $riwayatAktif->contains('id', $trx->id));
                        @endphp

                        {{-- ACTIVE ONGOING ORDERS SECTION --}}
                        @if ($riwayatAktif->isNotEmpty())
                            <div class="space-y-6">
                                <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-3 w-3 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                        </span>
                                        <h2 class="font-display font-black text-2xl text-[#3C2415] tracking-wide uppercase">
                                            Active Pickup Orders
                                        </h2>
                                    </div>
                                    <span class="rounded-full bg-amber-100 border border-amber-300/50 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-amber-900 shadow-xs">
                                        {{ $riwayatAktif->count() }} Ongoing
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($riwayatAktif as $trx)
                                        @php $pesananBadge = $pesananStatusInfo($trx); @endphp
                                        <a href="{{ route('member.pesanan.show', $trx) }}"
                                           onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                           class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/15 shadow-xs hover:border-[#8E1E26]/40 hover:shadow-md transition-all flex flex-col justify-between cursor-pointer">
                                            <div>
                                                <div class="flex items-center justify-between gap-3 mb-4">
                                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs group-hover:scale-105 transition-transform">
                                                        @include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])
                                                    </span>
                                                    <div class="text-right">
                                                        <span class="font-mono text-[10px] font-bold text-[#3C2415]/50 uppercase tracking-widest block">ORDER ID</span>
                                                        <p class="font-mono font-bold text-xs text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">{{ $trx->kode_transaksi }}</p>
                                                    </div>
                                                </div>

                                                <div class="space-y-1 mb-5">
                                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FAF5E8] text-[10px] font-black uppercase tracking-wider text-[#3C2415]">
                                                        <span>Online Pickup</span> &middot; <span>{{ $trx->created_at->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <p class="font-display font-black text-2xl text-[#3C2415] pt-2">
                                                        Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                                @if ($pesananBadge)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold {{ $pesananBadge['class'] }}">
                                                        {{ $pesananBadge['label'] }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-wider text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                                    <span>View Status</span> &rarr;
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- PAST ORDERS & IN-STORE TRANSACTIONS SECTION --}}
                        @if ($riwayatLain->isNotEmpty())
                            <div class="space-y-6">
                                <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4 {{ $riwayatAktif->isNotEmpty() ? 'pt-6' : '' }}">
                                    <div>
                                        <h2 class="font-display font-black text-2xl text-[#3C2415] tracking-wide uppercase">
                                            Order &amp; Purchase History
                                        </h2>
                                        <p class="text-xs text-[#7C6558] font-medium mt-0.5">Completed pickup orders and past in-store cashier transactions</p>
                                    </div>
                                    <span class="text-xs font-bold text-[#3C2415]/60">
                                        {{ $riwayatLain->count() }} past orders
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($riwayatLain as $trx)
                                        @php $isOnline = $trx->asal_pesanan === \App\Models\Transaksi::ASAL_MEMBER_APP; @endphp
                                        @if ($isOnline)
                                            @php
                                                $pesananBadge = $pesananStatusInfo($trx);
                                                $sudahDiambil = $trx->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL;
                                            @endphp
                                            <a href="{{ route('member.pesanan.show', $trx) }}"
                                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                               class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs hover:border-[#8E1E26]/30 hover:shadow-md transition-all flex flex-col justify-between cursor-pointer">
                                                <div>
                                                    <div class="flex items-center justify-between gap-3 mb-4">
                                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#FAF5E8] text-[#3C2415] shadow-xs group-hover:bg-[#3C2415] group-hover:text-[#FAF5E8] transition-colors">
                                                            @include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])
                                                        </span>
                                                        <div class="text-right">
                                                            <span class="font-mono text-[10px] font-bold text-[#3C2415]/50 uppercase tracking-widest block">ONLINE PICKUP</span>
                                                            <p class="font-mono font-bold text-xs text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">{{ $trx->kode_transaksi }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-1 mb-5">
                                                        <p class="text-[11px] font-semibold text-[#7C6558]">
                                                            {{ $trx->created_at->format('d M Y, H:i') }}
                                                        </p>
                                                        <p class="font-display font-black text-2xl text-[#3C2415] pt-1">
                                                            Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                                    @if ($pesananBadge)
                                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold {{ $pesananBadge['class'] }}">
                                                            {{ $pesananBadge['label'] }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-wider text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                                        <span>View Details</span> &rarr;
                                                    </span>
                                                </div>
                                            </a>
                                        @else
                                            @php
                                                $belumLunas = strtolower($trx->status_pembayaran ?? 'lunas') !== 'lunas';
                                                $itemsJson = $trx->detailTransaksi
                                                    ->reject(fn ($i) => ($i->bundling_id || $i->promo_event_id) && $i->produk_id && (float) $i->subtotal == 0)
                                                    ->groupBy(function ($item) {
                                                        return $item->nama_produk . '_' . ($item->ukuran ?? 'std') . '_' . ($item->varian_rasa ?? 'none');
                                                    })
                                                    ->map(fn ($group) => [
                                                        'nama' => $group->first()->nama_produk,
                                                        'ukuran' => $group->first()->ukuran,
                                                        'varian_rasa' => $group->first()->varian_rasa,
                                                        'jumlah' => $group->sum('jumlah'),
                                                        'subtotal' => $group->sum('subtotal'),
                                                        'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                                                    ])
                                                    ->values()
                                                    ->toJson();
                                            @endphp
                                            <button type="button"
                                                    onclick="bukaDetailTransaksi(this)"
                                                    data-kode="{{ $trx->kode_transaksi }}"
                                                    data-tanggal="{{ $trx->created_at->format('d M Y, H:i') }}"
                                                    data-status-label="{{ $belumLunas ? 'Unpaid' : 'Completed (Cashier)' }}"
                                                    data-status-class="{{ $belumLunas ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}"
                                                    data-metode="{{ $trx->metode_pembayaran ?? '-' }}"
                                                    data-subtotal="{{ $trx->subtotal }}"
                                                    data-pajak="{{ $trx->pajak }}"
                                                    data-diskon="{{ $trx->diskon }}"
                                                    data-diskon-label="{{ $trx->diskon_label }}"
                                                    data-total="{{ $trx->total_bayar }}"
                                                    data-poin="{{ $trx->poin_didapat ?? 0 }}"
                                                    data-items="{{ $itemsJson }}"
                                                    class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs hover:border-[#8E1E26]/30 hover:shadow-md transition-all flex flex-col justify-between cursor-pointer">
                                                <div>
                                                    <div class="flex items-center justify-between gap-3 mb-4">
                                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $belumLunas ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }} shadow-xs">
                                                            @if ($belumLunas)
                                                                @include('mobile.components.icon-clock', ['class' => 'h-6 w-6'])
                                                            @else
                                                                @include('mobile.components.icon-check-circle', ['class' => 'h-6 w-6'])
                                                            @endif
                                                        </span>
                                                        <div class="text-right">
                                                            <span class="font-mono text-[10px] font-bold text-[#3C2415]/50 uppercase tracking-widest block">STORE CASHIER</span>
                                                            <p class="font-mono font-bold text-xs text-[#3C2415] group-hover:text-[#8E1E26] transition-colors">{{ $trx->kode_transaksi }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-1 mb-5">
                                                        <p class="text-[11px] font-semibold text-[#7C6558]">
                                                            {{ $trx->created_at->format('d M Y, H:i') }}
                                                        </p>
                                                        <p class="font-display font-black text-2xl text-[#3C2415] pt-1">
                                                            Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-bold {{ $belumLunas ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                        {{ $belumLunas ? 'Pending' : 'Completed' }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-wider text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                                        <span>View Receipt</span> &rarr;
                                                    </span>
                                                </div>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     PANE 2: STAMP REWARDS HISTORY
                     ═══════════════════════════════════════════════════════════ --}}
                <div x-show="desktopTab === 'stamp'" class="space-y-8">
                    @if ($riwayatStamp->isEmpty())
                        <div class="rounded-[32px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center space-y-4 shadow-sm">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] text-[#8E1E26] shadow-xs">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="w-8 h-8 object-contain">
                            </span>
                            <h3 class="font-display font-black text-2xl text-[#3C2415] uppercase tracking-wide">No Stamp Redemptions Yet</h3>
                            <p class="text-xs sm:text-sm text-[#7C6558] max-w-md mx-auto leading-relaxed">
                                Collect 5 digital stamps with your pastry purchases to unlock complimentary artisan treats!
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('member.reward.index', ['tab' => 'stamp']) }}"
                                   class="inline-flex items-center gap-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] px-8 py-3 text-xs font-black uppercase tracking-wider text-white transition-colors shadow-md">
                                    <span>View Stamp Club</span> &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-4">
                            <div>
                                <h2 class="font-display font-black text-2xl text-[#3C2415] tracking-wide uppercase">
                                    Stamp Rewards Claimed
                                </h2>
                                <p class="text-xs text-[#7C6558] font-medium mt-0.5">Complimentary artisan treats unlocked from your digital stamp club</p>
                            </div>
                            <span class="text-xs font-bold text-[#3C2415]/60">
                                {{ $riwayatStamp->count() }} treats claimed
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($riwayatStamp as $item)
                                <button type="button"
                                        onclick="bukaDetailRedeem(this)"
                                        data-jenis="stamp"
                                        data-id="{{ $item->id }}"
                                        data-status-raw="{{ $item->status }}"
                                        data-nama="{{ ($item->produk ? $item->nama_tampil : 'Produk tidak tersedia') }}"
                                        data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                        data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                        data-tanggal="{{ $item->created_at->format('d M Y, H:i') }}"
                                        data-kode="{{ $item->kode_redeem }}"
                                        data-extra-label="Program Stamp"
                                        data-extra-value="{{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}"
                                        data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->format('d M Y, H:i') : '' }}"
                                        class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs hover:border-[#8E1E26]/30 hover:shadow-md transition-all flex flex-col justify-between cursor-pointer">
                                    <div>
                                        <div class="flex items-center justify-between gap-3 mb-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-xs p-2">
                                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                            </span>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $statusBadge[$item->status]['class'] }}">
                                                {{ $statusBadge[$item->status]['label'] }}
                                            </span>
                                        </div>

                                        <div class="space-y-1 mb-5">
                                            <span class="text-[10px] font-display font-bold uppercase tracking-wider text-[#8E1E26] block">
                                                {{ $item->stampProgram?->nama_program ?? 'Daisy Stamp Club' }}
                                            </span>
                                            <h3 class="font-display font-black text-xl text-[#3C2415] group-hover:text-[#8E1E26] transition-colors leading-snug">
                                                {{ ($item->produk ? $item->nama_tampil : 'Pastry Treat') }}
                                            </h3>
                                            <p class="text-[11px] text-[#7C6558] font-medium pt-1">
                                                Claimed &middot; {{ $item->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                        <div>
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#3C2415]/50 block">REDEEM CODE</span>
                                            <span class="font-mono text-sm font-black tracking-widest text-[#8E1E26]">{{ $item->kode_redeem }}</span>
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-wider text-[#3C2415] group-hover:text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                            <span>Details</span> &rarr;
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($tampilTabVoucher)
                {{-- ═══════════════════════════════════════════════════════════
                     PANE 3: VOUCHERS REDEEMED
                     ═══════════════════════════════════════════════════════════ --}}
                <div x-show="desktopTab === 'voucher'" class="space-y-8">
                    @if ($penukaran->isEmpty())
                        <div class="rounded-[32px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center space-y-4 shadow-sm">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] text-[#8E1E26] shadow-xs">
                                @include('mobile.components.icon-ticket', ['class' => 'h-8 w-8'])
                            </span>
                            <h3 class="font-display font-black text-2xl text-[#3C2415] uppercase tracking-wide">No Vouchers Claimed</h3>
                            <p class="text-xs sm:text-sm text-[#7C6558] max-w-md mx-auto">
                                Check out our exclusive vouchers to enjoy handcrafted discounts on your sweet orders.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($penukaran as $item)
                                <button type="button"
                                        onclick="bukaDetailRedeem(this)"
                                        data-jenis="reward"
                                        data-nama="{{ $item->reward->nama ?? 'Reward' }}"
                                        data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                        data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                        data-tanggal="{{ $item->created_at->format('d M Y, H:i') }}"
                                        data-kode="{{ $item->kode_redeem }}"
                                        data-extra-label="Poin Terpakai"
                                        data-extra-value="-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin"
                                        data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->format('d M Y, H:i') : '' }}"
                                        class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs hover:border-[#8E1E26]/30 hover:shadow-md transition-all flex flex-col justify-between cursor-pointer">
                                    <div>
                                        <div class="flex items-center justify-between gap-3 mb-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#3C2415] text-[#FAF5E8] shadow-xs">
                                                @include('mobile.components.icon-ticket', ['class' => 'h-6 w-6'])
                                            </span>
                                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $statusBadge[$item->status]['class'] }}">
                                                {{ $statusBadge[$item->status]['label'] }}
                                            </span>
                                        </div>

                                        <div class="space-y-1 mb-5">
                                            <h3 class="font-display font-black text-xl text-[#3C2415] group-hover:text-[#8E1E26] transition-colors leading-snug">
                                                {{ $item->reward->nama ?? 'Reward' }}
                                            </h3>
                                            <p class="text-[11px] text-[#7C6558] font-medium pt-1">
                                                {{ $item->created_at->format('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                        <div>
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#3C2415]/50 block">REDEEM CODE</span>
                                            <span class="font-mono text-sm font-black tracking-widest text-[#8E1E26]">{{ $item->kode_redeem }}</span>
                                        </div>
                                        <span class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-wider text-[#3C2415] group-hover:text-[#8E1E26] group-hover:translate-x-1 transition-transform">
                                            <span>Details</span> &rarr;
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

            </div>
        </section>

        {{-- ━━━━ GSAP 3 & SCROLLTRIGGER INTERACTIVE STORYTELLING ENGINE (DESKTOP >= 1024px) ━━━━ --}}
        <script src="{{ asset('vendor/gsap/gsap.min.js') }}"></script>
        <script src="{{ asset('vendor/gsap/ScrollTrigger.min.js') }}"></script>
        <script src="{{ asset('js/desktop-storytelling.js') }}"></script>
    </div>

    {{-- Bottom sheet: Detail Transaksi --}}
    <dialog id="sheet-detail-transaksi" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[85vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[85vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-theme-md font-bold text-gray-900" id="detail-trx-kode">–</h3>
                        <p class="mt-0.5 text-xs text-gray-400" id="detail-trx-tanggal">–</p>
                    </div>
                    <span id="detail-trx-status" class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold">–</span>
                </div>

                <div class="mt-4 divide-y divide-dashed divide-gray-100 rounded-2xl border border-gray-100">
                    <div id="detail-trx-items" class="divide-y divide-gray-50 px-4"></div>
                </div>

                <div class="mt-4 space-y-1.5 rounded-2xl border border-gray-100 p-4 text-theme-sm">
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span id="detail-trx-subtotal" class="font-medium text-gray-700">–</span>
                    </div>
                    <div id="detail-trx-pajak-row" class="flex items-center justify-between text-gray-500">
                        <span>Tax</span>
                        <span id="detail-trx-pajak" class="font-medium text-gray-700">–</span>
                    </div>
                    <div id="detail-trx-diskon-row" class="flex items-center justify-between text-gray-500">
                        <span id="detail-trx-diskon-label">Discount</span>
                        <span id="detail-trx-diskon" class="font-medium text-red-500">–</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between border-t border-dashed border-gray-100 pt-2 text-theme-sm font-bold text-gray-900">
                        <span>Total Amount</span>
                        <span id="detail-trx-total">–</span>
                    </div>
                </div>

                <div class="mt-4 grid {{ $adaAturanPoin ? 'grid-cols-2' : 'grid-cols-1' }} gap-3" id="detail-trx-footer-grid">
                    <div class="rounded-2xl border border-gray-100 px-4 py-3" id="detail-trx-metode-col">
                        <p class="text-[11px] text-gray-400">Payment Method</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-gray-800" id="detail-trx-metode">–</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 px-4 py-3 {{ $adaAturanPoin ? '' : 'hidden' }}" id="detail-trx-poin-col">
                        <p class="text-[11px] text-gray-400">Points Earned</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-green-600" id="detail-trx-poin">–</p>
                    </div>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-detail-transaksi')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Close
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom sheet: Detail Redeem (dipakai untuk penukaran reward & stamp) --}}
    <dialog id="sheet-detail-redeem" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
        <div class="flex flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="px-6 pb-2 pt-3">
                <div class="flex items-start gap-3">
                    <span id="detail-redeem-icon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-theme-md font-bold text-gray-900" id="detail-redeem-nama">–</p>
                            <span id="detail-redeem-status" class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold">–</span>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-400" id="detail-redeem-tanggal">–</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-dashed border-gray-200 bg-brand-25 px-4 py-3 text-center">
                    <p class="text-[11px] text-gray-400">Redeem Code</p>
                    <p class="mt-0.5 font-mono text-theme-lg font-bold tracking-[0.2em] text-brand-800" id="detail-redeem-kode">–</p>
                </div>

                <div class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 px-4 py-3">
                    <p class="text-[11px] text-gray-400" id="detail-redeem-extra-label">–</p>
                    <p class="text-theme-sm font-bold text-gray-800" id="detail-redeem-extra-value">–</p>
                </div>

                <div id="detail-redeem-diambil-row" class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 px-4 py-3">
                    <p class="text-[11px] text-gray-400">Collected On</p>
                    <p class="text-theme-sm font-bold text-gray-800" id="detail-redeem-diambil">–</p>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4 space-y-2.5">
                <form id="form-detail-redeem-ambil-aplikasi" method="POST" action="" class="hidden">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-2xl bg-[#5a3c27] py-3.5 text-theme-sm font-bold text-white hover:bg-[#4a3020] active:scale-[0.99] flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <span>Collect Reward via Pickup</span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>
                <button type="button" onclick="closeSheet('sheet-detail-redeem')"
                        class="w-full rounded-2xl bg-gray-100 py-3 text-theme-sm font-semibold text-gray-700 active:bg-gray-200 cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </dialog>

    <script>
        (function () {
            const tabs = @json(array_keys($daftarTab));
            let current = 0;

            const track = document.getElementById('riwayat-tab-track');
            if (!track) return;

            const indicator = document.getElementById('riwayat-tab-indicator');
            const buttons = document.querySelectorAll('.riwayat-tab');
            const panes = document.querySelectorAll('.riwayat-tab-pane');
            const viewport = document.getElementById('riwayat-tab-viewport');

            const paneWidthPercent = 100 / tabs.length;
            track.style.width = (tabs.length * 100) + '%';
            panes.forEach((pane, i) => {
                pane.style.width = paneWidthPercent + '%';
                pane.style.pointerEvents = i === current ? 'auto' : 'none';
            });

            function paint() {
                track.style.transform = `translateX(-${current * paneWidthPercent}%)`;
                panes.forEach((pane, i) => {
                    pane.style.pointerEvents = i === current ? 'auto' : 'none';
                });
                indicator.style.transform = `translateX(${current * 100}%)`;
                buttons.forEach((btn, i) => {
                    btn.classList.toggle('text-brand-800', i === current);
                    btn.classList.toggle('text-gray-400', i !== current);
                });
            }

            window.switchRiwayatTab = function (name) {
                const idx = tabs.indexOf(name);
                if (idx === -1 || idx === current) return;
                current = idx;
                paint();
            };

            let startX = 0;
            let dragging = false;

            viewport.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                dragging = true;
            }, { passive: true });

            viewport.addEventListener('touchend', (e) => {
                if (!dragging) return;
                dragging = false;
                const diffX = startX - e.changedTouches[0].clientX;
                if (diffX > 40 && current < tabs.length - 1) {
                    current += 1;
                    paint();
                } else if (diffX < -40 && current > 0) {
                    current -= 1;
                    paint();
                }
            }, { passive: true });

            track.style.transition = 'none';
            paint();
            requestAnimationFrame(() => requestAnimationFrame(() => {
                track.style.transition = '';
            }));
        })();

        function formatDiskonLabel(raw) {
            if (!raw) return 'Discount';
            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    const nama = parsed.map((item) => item && item.nama ? item.nama : null).filter(Boolean);
                    if (nama.length > 0) return nama.join(', ');
                }
                return 'Discount';
            } catch (e) {
                return raw;
            }
        }

        function bukaDetailTransaksi(btn) {
            const d = btn.dataset;

            document.getElementById('detail-trx-kode').textContent = d.kode;
            document.getElementById('detail-trx-tanggal').textContent = d.tanggal;

            const statusEl = document.getElementById('detail-trx-status');
            statusEl.textContent = d.statusLabel;
            statusEl.className = 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ' + d.statusClass;

            const fmtRupiah = (n) => 'Rp' + Number(n || 0).toLocaleString('id-ID');

            document.getElementById('detail-trx-subtotal').textContent = fmtRupiah(d.subtotal);
            
            const pajakRow = document.getElementById('detail-trx-pajak-row');
            const pajak = Number(d.pajak || 0);
            if (pajak > 0) {
                pajakRow.classList.remove('hidden');
                document.getElementById('detail-trx-pajak').textContent = fmtRupiah(pajak);
            } else {
                pajakRow.classList.add('hidden');
            }

            document.getElementById('detail-trx-total').textContent = fmtRupiah(d.total);
            document.getElementById('detail-trx-metode').textContent = d.metode || '-';

            const adaAturanPoin = {{ $adaAturanPoin ? 'true' : 'false' }};
            const poinCol = document.getElementById('detail-trx-poin-col');
            const footerGrid = document.getElementById('detail-trx-footer-grid');
            const poinVal = Number(d.poin || 0);

            if (adaAturanPoin && poinVal > 0) {
                if (poinCol) poinCol.classList.remove('hidden');
                if (footerGrid) {
                    footerGrid.classList.remove('grid-cols-1');
                    footerGrid.classList.add('grid-cols-2');
                }
                document.getElementById('detail-trx-poin').textContent = '+' + poinVal.toLocaleString('id-ID') + ' pts';
            } else {
                if (poinCol) poinCol.classList.add('hidden');
                if (footerGrid) {
                    footerGrid.classList.remove('grid-cols-2');
                    footerGrid.classList.add('grid-cols-1');
                }
            }

            const diskonRow = document.getElementById('detail-trx-diskon-row');
            const diskon = Number(d.diskon || 0);
            if (diskon > 0) {
                diskonRow.classList.remove('hidden');
                document.getElementById('detail-trx-diskon-label').textContent = formatDiskonLabel(d.diskonLabel);
                document.getElementById('detail-trx-diskon').textContent = '-' + fmtRupiah(diskon);
            } else {
                diskonRow.classList.add('hidden');
            }

            const itemsWrap = document.getElementById('detail-trx-items');
            itemsWrap.innerHTML = '';
            let items = [];
            try { items = JSON.parse(d.items || '[]'); } catch (e) { items = []; }

            if (items.length === 0) {
                itemsWrap.innerHTML = '<p class="py-4 text-center text-xs text-gray-400">Item details not available</p>';
            } else {
                items.forEach((item) => {
                    const row = document.createElement('div');
                    row.className = 'py-3';
                    row.innerHTML = `
                        <p class="text-theme-sm font-semibold leading-snug text-gray-800">${item.jumlah}x ${item.nama} ${item.ukuran ? `<span class="text-brand-800 font-extrabold text-[11px] ml-1">(${item.ukuran})</span>` : ''} ${item.varian_rasa ? `<span class="text-amber-800 font-extrabold text-[11px] ml-1">(${item.varian_rasa})</span>` : ''}</p>
                        ${item.catatan ? `<p class="mt-0.5 text-xs text-gray-400">${item.catatan}</p>` : ''}
                        <p class="mt-1 text-right text-theme-sm font-semibold text-gray-700">${fmtRupiah(item.subtotal)}</p>
                    `;
                    itemsWrap.appendChild(row);
                });
            }

            openSheet('sheet-detail-transaksi');
        }

        function bukaDetailRedeem(btn) {
            const d = btn.dataset;
            const isStamp = d.jenis === 'stamp';

            document.getElementById('detail-redeem-nama').textContent = d.nama;
            document.getElementById('detail-redeem-tanggal').textContent = d.tanggal;
            document.getElementById('detail-redeem-kode').textContent = d.kode;
            document.getElementById('detail-redeem-extra-label').textContent = d.extraLabel;
            document.getElementById('detail-redeem-extra-value').textContent = d.extraValue;

            const statusEl = document.getElementById('detail-redeem-status');
            statusEl.textContent = d.statusLabel;
            statusEl.className = 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ' + d.statusClass;

            const iconEl = document.getElementById('detail-redeem-icon');
            iconEl.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full ' + (isStamp ? 'bg-brand-50 p-2 text-brand-800' : 'bg-brand-800 text-white');
            iconEl.innerHTML = isStamp
                ? `<img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">`
                : `@include('mobile.components.icon-ticket', ['class' => 'h-5 w-5'])`;

            const diambilRow = document.getElementById('detail-redeem-diambil-row');
            if (d.diambil) {
                diambilRow.classList.remove('hidden');
                document.getElementById('detail-redeem-diambil').textContent = d.diambil;
            } else {
                diambilRow.classList.add('hidden');
            }

            const formAmbil = document.getElementById('form-detail-redeem-ambil-aplikasi');
            if (formAmbil) {
                if (isStamp && d.statusRaw === 'menunggu_diambil' && d.id) {
                    formAmbil.action = '/mobile/stamps/' + d.id + '/ambil-aplikasi';
                    formAmbil.classList.remove('hidden');
                } else {
                    formAmbil.classList.add('hidden');
                }
            }

            openSheet('sheet-detail-redeem');
        }
    </script>

@endsection