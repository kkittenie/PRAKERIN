@extends('mobile.layout', ['title' => 'Riwayat'])

@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Menunggu diambil', 'class' => 'bg-amber-50 text-amber-700'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-50 text-green-600'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500'],
    ];
    $statusPesananBadge = [
        \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Diproses', 'class' => 'bg-amber-50 text-amber-700'],
        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Ready for Pickup', 'class' => 'bg-blue-50 text-blue-700'],
        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Sudah Diambil', 'class' => 'bg-green-50 text-green-600'],
        \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500'],
    ];

    $pesananStatusInfo = function ($trx) use ($statusPesananBadge) {
        if ($trx->status_pesanan === \App\Models\Transaksi::STATUS_DIBATALKAN) {
            return $statusPesananBadge[\App\Models\Transaksi::STATUS_DIBATALKAN];
        }

        $lunas = $trx->status_pembayaran === 'Lunas';

        if (! $lunas) {
            return ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-50 text-amber-700'];
        }

        if (! $trx->diproses_pada) {
            return ['label' => 'Dikonfirmasi', 'class' => 'bg-blue-50 text-blue-700'];
        }

        return $statusPesananBadge[$trx->status_pesanan] ?? null;
    };
    $riwayatStamp = $riwayatStamp ?? collect();
    $riwayat = $riwayat ?? collect();

    // Tab Voucher disembunyikan sementara (kodenya tetap ada). Ubah ke true untuk menampilkannya lagi.
    $tampilTabVoucher = false;

    $daftarTab = ['riwayat' => 'Order', 'voucher' => 'Voucher', 'stamp' => 'Stamp'];
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
            background: #fff;
            box-shadow: 0 1px 3px rgba(90, 63, 39, .10), 0 1px 2px rgba(90, 63, 39, .06);
        }

        .redeem-voucher-ticket::before,
        .redeem-voucher-ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #fff;
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
            <h1 class="text-center text-lg font-extrabold text-gray-900">Riwayat</h1>

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
                'title' => 'Belum Ada Riwayat',
                'description' => 'Semua transaksi, penukaran reward, dan penukaran stamp kamu akan tercatat di sini.',
                'icon' => 'icon-clock',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-400',
            ])
        @else
            <div id="riwayat-tab-viewport" class="relative flex flex-1 flex-col overflow-hidden">
                <div id="riwayat-tab-track" class="flex flex-1 transition-transform duration-300 ease-out">

            {{-- TAB: RIWAYAT MOBILE --}}
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="0">
                @if ($riwayat->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Riwayat',
                        'description' => 'Pesanan & transaksi kamu akan tercatat di sini.',
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
                            <h2 class="text-sm font-bold text-gray-900">Pesanan Aktif</h2>
                            <span class="text-xs text-gray-400">{{ $riwayatAktif->count() }} pesanan</span>
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
                            <h2 class="text-sm font-bold text-gray-900">Riwayat</h2>
                            <span class="text-xs text-gray-400">{{ $riwayatLain->count() }} riwayat</span>
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
                                        class="flex w-full items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 text-left shadow-sm transition active:bg-gray-50"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
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
                                        data-status-label="{{ $belumLunas ? 'Belum Lunas' : 'Lunas' }}"
                                        data-status-class="{{ $belumLunas ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}"
                                        data-metode="{{ $trx->metode_pembayaran ?? '-' }}"
                                        data-subtotal="{{ $trx->subtotal }}"
                                        data-pajak="{{ $trx->pajak }}"
                                        data-diskon="{{ $trx->diskon }}"
                                        data-diskon-label="{{ $trx->diskon_label }}"
                                        data-total="{{ $trx->total_bayar }}"
                                        data-poin="{{ $trx->poin_didapat ?? 0 }}"
                                        data-items="{{ $itemsJson }}"
                                        class="flex w-full items-center gap-3 rounded-2xl border {{ $belumLunas ? 'border-red-100' : 'border-gray-100' }} bg-white p-4 text-left shadow-sm transition active:bg-gray-50"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $belumLunas ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600' }}">
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
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="1">
                @if ($penukaran->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Penukaran Voucher',
                        'description' => 'Penukaran voucher kamu bakal kecatat di sini.',
                        'icon' => 'icon-ticket',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Penukaran Reward</h2>
                        <span class="text-xs text-gray-400">{{ $penukaran->count() }} riwayat</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        @foreach ($penukaran as $item)
                            <button
                                type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="reward"
                                data-nama="{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Poin Terpakai"
                                data-extra-value="-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin"
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
                                                <p class="truncate text-sm font-bold text-gray-900">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</p>
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
                                                <p class="text-[11px] text-gray-400">Kode redeem</p>
                                                <p class="font-mono text-sm font-bold tracking-widest text-gray-800">{{ $item->kode_redeem }}</p>
                                            </div>
                                            <p class="text-sm font-bold text-red-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin</p>
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
            <div class="riwayat-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="{{ $tampilTabVoucher ? 2 : 1 }}">
                @if ($riwayatStamp->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Penukaran Stamp',
                        'description' => 'Penukaran hadiah stamp kamu bakal kecatat di sini.',
                        'icon' => 'icon-cake',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Penukaran Stamp</h2>
                        <span class="text-xs text-gray-400">{{ $riwayatStamp->count() }} riwayat</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        @foreach ($riwayatStamp as $item)
                            <button
                                type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="stamp"
                                data-id="{{ $item->id }}"
                                data-status-raw="{{ $item->status }}"
                                data-nama="{{ ($item->produk ? $item->nama_tampil : 'Produk tidak tersedia') }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Program Stamp"
                                data-extra-value="{{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}"
                                data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->translatedFormat('d M Y, H:i') : '' }}"
                                class="w-full rounded-2xl border border-gray-100 bg-white p-4 text-left shadow-sm transition active:bg-gray-50"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 p-2 text-brand-800">
                                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ ($item->produk ? $item->nama_tampil : 'Produk tidak tersedia') }}</p>
                                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusBadge[$item->status]['class'] }}">
                                                {{ $statusBadge[$item->status]['label'] }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 border-t border-dashed border-gray-100 pt-3">
                                    <p class="text-[11px] text-gray-400">Kode redeem</p>
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
    {{-- DESKTOP VIEW (>= 1024px) - REDESIGNED CARD --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block lg:max-w-6xl lg:mx-auto lg:my-6 lg:bg-white lg:rounded-3xl lg:border lg:border-gray-200/80 lg:shadow-xl lg:p-8 lg:space-y-8"
        x-data="{ desktopTab: 'order' }">

        {{-- Desktop Page Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-6">
            <div>
                <h1 class="text-2xl font-black text-brand-800 tracking-tight">Aktivitas & Riwayat Transaksi</h1>
                <p class="text-xs text-gray-500 mt-1">Pantau pesanan aktif, riwayat pembelian, {{ $tampilTabVoucher ? 'penukaran voucher, ' : '' }}dan stamp member Anda</p>
            </div>

            {{-- Desktop Tab Navigation Bar --}}
            @unless ($isEmpty)
                <div class="inline-flex items-center gap-1.5 rounded-2xl bg-gray-100/80 p-1.5 border border-gray-200/60 shadow-inner">
                    <button type="button" @click="desktopTab = 'order'"
                        :class="desktopTab === 'order' ? 'bg-white text-brand-800 shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 font-bold'"
                        class="rounded-xl px-5 py-2.5 text-xs transition duration-150">
                        Order
                    </button>
                    @if ($tampilTabVoucher)
                    <button type="button" @click="desktopTab = 'voucher'"
                        :class="desktopTab === 'voucher' ? 'bg-white text-brand-800 shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 font-bold'"
                        class="rounded-xl px-5 py-2.5 text-xs transition duration-150">
                        Voucher
                    </button>
                    @endif
                    <button type="button" @click="desktopTab = 'stamp'"
                        :class="desktopTab === 'stamp' ? 'bg-white text-brand-800 shadow-sm font-extrabold' : 'text-gray-600 hover:text-gray-900 font-bold'"
                        class="rounded-xl px-5 py-2.5 text-xs transition duration-150">
                        Stamp
                    </button>
                </div>
            @endunless
        </div>

        @if ($isEmpty)
            @include('mobile.components.empty-state', [
                'title' => 'Belum Ada Riwayat',
                'description' => 'Semua transaksi, penukaran reward, dan penukaran stamp kamu akan tercatat di sini.',
                'icon' => 'icon-clock',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-400',
            ])
        @else

            {{-- ================= DESKTOP TAB 1: ORDER ================= --}}
            <div x-show="desktopTab === 'order'" class="space-y-8">
                @if ($riwayat->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Riwayat Pesanan',
                        'description' => 'Pesanan & transaksi kamu akan tercatat di sini.',
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

                    {{-- Pesanan Aktif Section --}}
                    @if ($riwayatAktif->isNotEmpty())
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Pesanan Aktif</h2>
                                </div>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-700 border border-amber-200/60">{{ $riwayatAktif->count() }} Pesanan Sedang Berjalan</span>
                            </div>

                            <div class="grid grid-cols-3 gap-6">
                                @foreach ($riwayatAktif as $trx)
                                    @php $pesananBadge = $pesananStatusInfo($trx); @endphp
                                    <a href="{{ route('member.pesanan.show', $trx) }}"
                                        onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                        class="group flex flex-col justify-between rounded-2xl border border-amber-200/80 bg-gradient-to-b from-amber-50/40 to-white p-5 shadow-xs hover:border-amber-400 hover:shadow-md transition-all">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-800 text-white shadow-xs">
                                                    @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-extrabold text-gray-900 group-hover:text-brand-800 transition">{{ $trx->kode_transaksi }}</p>
                                                    <p class="text-xs text-gray-400 font-medium mt-0.5">Online &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-5 flex items-center justify-between border-t border-amber-100 pt-3">
                                            @if ($pesananBadge)
                                                <span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $pesananBadge['class'] }}">
                                                    {{ $pesananBadge['label'] }}
                                                </span>
                                            @endif
                                            <span class="text-xs font-extrabold text-brand-800 group-hover:translate-x-1 transition transform flex items-center gap-1">
                                                Lihat Detail &rarr;
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Riwayat Pesanan Selesai / Offline Section --}}
                    @if ($riwayatLain->isNotEmpty())
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Riwayat Transaksi</h2>
                                <span class="text-xs text-gray-500 font-semibold">{{ $riwayatLain->count() }} riwayat selesai / lampau</span>
                            </div>

                            <div class="grid grid-cols-3 gap-6">
                                @foreach ($riwayatLain as $trx)
                                    @php $isOnline = $trx->asal_pesanan === \App\Models\Transaksi::ASAL_MEMBER_APP; @endphp
                                    @if ($isOnline)
                                        @php
                                            $pesananBadge = $pesananStatusInfo($trx);
                                            $sudahDiambil = $trx->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL;
                                        @endphp
                                        <a href="{{ route('member.pesanan.show', $trx) }}"
                                            onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                            class="group flex flex-col justify-between rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs hover:border-brand-300 hover:shadow-md transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-800 group-hover:bg-brand-800 group-hover:text-white transition">
                                                        @include('mobile.components.icon-cake', ['class' => 'h-5 w-5'])
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-extrabold text-gray-900 group-hover:text-brand-800 transition">{{ $trx->kode_transaksi }}</p>
                                                        <p class="text-xs text-gray-400 font-medium mt-0.5">Online &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-3">
                                                @if ($pesananBadge)
                                                    <span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $pesananBadge['class'] }}">
                                                        {{ $pesananBadge['label'] }}
                                                    </span>
                                                @endif
                                                @unless ($sudahDiambil)
                                                    <p class="text-sm font-black text-gray-700">
                                                        {{ 'Rp' . number_format($trx->total_bayar, 0, ',', '.') }}
                                                    </p>
                                                @endunless
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
                                            data-tanggal="{{ $trx->created_at->translatedFormat('d M Y, H:i') }}"
                                            data-status-label="{{ $belumLunas ? 'Belum Lunas' : 'Lunas' }}"
                                            data-status-class="{{ $belumLunas ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}"
                                            data-metode="{{ $trx->metode_pembayaran ?? '-' }}"
                                            data-subtotal="{{ $trx->subtotal }}"
                                            data-pajak="{{ $trx->pajak }}"
                                            data-diskon="{{ $trx->diskon }}"
                                            data-diskon-label="{{ $trx->diskon_label }}"
                                            data-total="{{ $trx->total_bayar }}"
                                            data-poin="{{ $trx->poin_didapat ?? 0 }}"
                                            data-items="{{ $itemsJson }}"
                                            class="group flex flex-col justify-between rounded-2xl border {{ $belumLunas ? 'border-red-200 bg-red-50/30' : 'border-gray-200/80 bg-white' }} p-5 text-left shadow-xs hover:border-brand-300 hover:shadow-md transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $belumLunas ? 'bg-red-100 text-red-600' : 'bg-green-50 text-green-600' }}">
                                                        @if ($belumLunas)
                                                            @include('mobile.components.icon-clock', ['class' => 'h-5 w-5'])
                                                        @else
                                                            @include('mobile.components.icon-check-circle', ['class' => 'h-5 w-5'])
                                                        @endif
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-extrabold text-gray-900 group-hover:text-brand-800 transition">{{ $trx->kode_transaksi }}</p>
                                                        <p class="text-xs text-gray-400 font-medium mt-0.5">Kasir &middot; {{ $trx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-3">
                                                <span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $belumLunas ? 'bg-red-100 text-red-700' : 'bg-green-50 text-green-700' }}">
                                                    {{ $belumLunas ? 'Belum Lunas' : 'Selesai (Kasir)' }}
                                                </span>
                                                @if ($belumLunas)
                                                    <p class="text-sm font-black text-red-600">
                                                        {{ 'Rp' . number_format($trx->total_bayar, 0, ',', '.') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            @if ($tampilTabVoucher)
            {{-- ================= DESKTOP TAB 2: VOUCHER ================= --}}
            <div x-show="desktopTab === 'voucher'" class="space-y-6">
                @if ($penukaran->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Penukaran Voucher',
                        'description' => 'Penukaran voucher kamu bakal kecatat di sini.',
                        'icon' => 'icon-ticket',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Penukaran Reward & Voucher</h2>
                        <span class="text-xs text-gray-500 font-semibold">{{ $penukaran->count() }} voucher ditukarkan</span>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        @foreach ($penukaran as $item)
                            <button type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="reward"
                                data-nama="{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Poin Terpakai"
                                data-extra-value="-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin"
                                data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->translatedFormat('d M Y, H:i') : '' }}"
                                class="redeem-voucher-ticket w-full border border-gray-200/80 text-left transition hover:border-brand-300 hover:shadow-md">
                                <div class="redeem-voucher-ticket__body">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-800 text-white shadow-xs">
                                            @include('mobile.components.icon-ticket', ['class' => 'h-5 w-5'])
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="truncate text-sm font-extrabold text-gray-900">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</p>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $statusBadge[$item->status]['class'] }}">
                                                    {{ $statusBadge[$item->status]['label'] }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-400 font-medium">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="redeem-voucher-ticket__cut mt-4 pt-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-[10px] uppercase font-bold text-gray-400">Kode Redeem</p>
                                                <p class="font-mono text-sm font-extrabold tracking-widest text-brand-800">{{ $item->kode_redeem }}</p>
                                            </div>
                                            <p class="text-sm font-black text-red-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }} poin</p>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- ================= DESKTOP TAB 3: STAMP ================= --}}
            <div x-show="desktopTab === 'stamp'" class="space-y-6">
                @if ($riwayatStamp->isEmpty())
                    @include('mobile.components.empty-state', [
                        'title' => 'Belum Ada Penukaran Stamp',
                        'description' => 'Penukaran hadiah stamp kamu bakal kecatat di sini.',
                        'icon' => 'icon-cake',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Penukaran Hadiah Stamp</h2>
                        <span class="text-xs text-gray-500 font-semibold">{{ $riwayatStamp->count() }} stamp ditukarkan</span>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        @foreach ($riwayatStamp as $item)
                            <button type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="stamp"
                                data-id="{{ $item->id }}"
                                data-status-raw="{{ $item->status }}"
                                data-nama="{{ ($item->produk ? $item->nama_tampil : 'Produk tidak tersedia') }}"
                                data-status-label="{{ $statusBadge[$item->status]['label'] }}"
                                data-status-class="{{ $statusBadge[$item->status]['class'] }}"
                                data-tanggal="{{ $item->created_at->translatedFormat('d M Y, H:i') }}"
                                data-kode="{{ $item->kode_redeem }}"
                                data-extra-label="Program Stamp"
                                data-extra-value="{{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}"
                                data-diambil="{{ $item->diambil_pada ? \Illuminate\Support\Carbon::parse($item->diambil_pada)->translatedFormat('d M Y, H:i') : '' }}"
                                class="flex flex-col justify-between rounded-2xl border border-gray-200/80 bg-white p-5 text-left shadow-xs hover:border-brand-300 hover:shadow-md transition-all">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 p-2 text-brand-800">
                                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-sm font-extrabold text-gray-900">{{ ($item->produk ? $item->nama_tampil : 'Produk tidak tersedia') }}</p>
                                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $statusBadge[$item->status]['class'] }}">
                                                {{ $statusBadge[$item->status]['label'] }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400 font-medium">{{ $item->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $item->stampProgram?->nama_program ?? 'Program Stamp' }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-dashed border-gray-100 pt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Kode Redeem</p>
                                        <p class="font-mono text-sm font-extrabold tracking-widest text-brand-800">{{ $item->kode_redeem }}</p>
                                    </div>
                                    <span class="text-xs font-extrabold text-brand-800">Detail &rarr;</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

        @endif
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
                        <span>Pajak</span>
                        <span id="detail-trx-pajak" class="font-medium text-gray-700">–</span>
                    </div>
                    <div id="detail-trx-diskon-row" class="flex items-center justify-between text-gray-500">
                        <span id="detail-trx-diskon-label">Diskon</span>
                        <span id="detail-trx-diskon" class="font-medium text-red-500">–</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between border-t border-dashed border-gray-100 pt-2 text-theme-sm font-bold text-gray-900">
                        <span>Total Bayar</span>
                        <span id="detail-trx-total">–</span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-gray-100 px-4 py-3">
                        <p class="text-[11px] text-gray-400">Metode Bayar</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-gray-800" id="detail-trx-metode">–</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 px-4 py-3">
                        <p class="text-[11px] text-gray-400">Poin Didapat</p>
                        <p class="mt-0.5 text-theme-sm font-bold text-green-600" id="detail-trx-poin">–</p>
                    </div>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-detail-transaksi')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Tutup
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
                    <p class="text-[11px] text-gray-400">Kode Redeem</p>
                    <p class="mt-0.5 font-mono text-theme-lg font-bold tracking-[0.2em] text-brand-800" id="detail-redeem-kode">–</p>
                </div>

                <div class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 px-4 py-3">
                    <p class="text-[11px] text-gray-400" id="detail-redeem-extra-label">–</p>
                    <p class="text-theme-sm font-bold text-gray-800" id="detail-redeem-extra-value">–</p>
                </div>

                <div id="detail-redeem-diambil-row" class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 px-4 py-3">
                    <p class="text-[11px] text-gray-400">Diambil Pada</p>
                    <p class="text-theme-sm font-bold text-gray-800" id="detail-redeem-diambil">–</p>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4 space-y-2.5">
                <form id="form-detail-redeem-ambil-aplikasi" method="POST" action="" class="hidden">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-2xl bg-[#5a3c27] py-3.5 text-theme-sm font-bold text-white hover:bg-[#4a3020] active:scale-[0.99] flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <span>Ambil Hadiah via Aplikasi (Pickup)</span>
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>
                <button type="button" onclick="closeSheet('sheet-detail-redeem')"
                        class="w-full rounded-2xl bg-gray-100 py-3 text-theme-sm font-semibold text-gray-700 active:bg-gray-200 cursor-pointer">
                    Tutup
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
            if (!raw) return 'Diskon';
            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    const nama = parsed.map((item) => item && item.nama ? item.nama : null).filter(Boolean);
                    if (nama.length > 0) return nama.join(', ');
                }
                return 'Diskon';
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
            document.getElementById('detail-trx-poin').textContent = '+' + Number(d.poin || 0).toLocaleString('id-ID') + ' poin';

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
                itemsWrap.innerHTML = '<p class="py-4 text-center text-xs text-gray-400">Detail item tidak tersedia</p>';
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