@extends('member.layout', ['title' => 'Riwayat'])

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
    $riwayatStamp = $riwayatStamp ?? collect();
    $pesanan = $pesanan ?? collect();
    $isEmpty = $penukaran->isEmpty() && $transaksi->isEmpty() && $riwayatStamp->isEmpty() && $pesanan->isEmpty();
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

    <div x-data="{ tab: 'pesanan' }">
        <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-0 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
            <h1 class="text-center text-lg font-extrabold text-gray-900">Riwayat</h1>

            @unless ($isEmpty)
                {{-- Fore-style underline tabs — same pattern as the Vouchers page.
                     Lives inside the sticky header so it pins under the title
                     while the tab panels scroll underneath it. --}}
                <div class="relative mt-4 grid grid-cols-4 border-b border-gray-100">
                    <button
                        type="button"
                        @click="tab = 'pesanan'"
                        :class="tab === 'pesanan' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600'"
                        class="relative pb-3 text-center text-[13px] font-bold transition"
                    >
                        Pesanan
                    </button>
                    <button
                        type="button"
                        @click="tab = 'transaksi'"
                        :class="tab === 'transaksi' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600'"
                        class="relative pb-3 text-center text-[13px] font-bold transition"
                    >
                        Transaksi
                    </button>
                    <button
                        type="button"
                        @click="tab = 'voucher'"
                        :class="tab === 'voucher' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600'"
                        class="relative pb-3 text-center text-[13px] font-bold transition"
                    >
                        Voucher
                    </button>
                    <button
                        type="button"
                        @click="tab = 'stamp'"
                        :class="tab === 'stamp' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600'"
                        class="relative pb-3 text-center text-[13px] font-bold transition"
                    >
                        Stamp
                    </button>
                    <span
                        class="pointer-events-none absolute -bottom-px left-0 h-[2.5px] w-1/4 rounded-full bg-brand-800 transition-transform duration-300 ease-out"
                        :class="{ 'translate-x-0': tab === 'pesanan', 'translate-x-full': tab === 'transaksi', 'translate-x-[200%]': tab === 'voucher', 'translate-x-[300%]': tab === 'stamp' }"
                    ></span>
                </div>
            @endunless
        </div>

        @if ($isEmpty)
            @include('member.components.empty-state', [
                'title' => 'Belum Ada Riwayat',
                'description' => 'Semua transaksi, penukaran reward, dan penukaran stamp kamu bakal kecatat di sini.',
                'icon' => 'icon-clock',
                'iconBg' => 'bg-gray-100',
                'iconColor' => 'text-gray-400',
            ])
        @else
            <div class="mt-5">

            {{-- ================= TAB: PESANAN (pick up dari Member App) ================= --}}
            <div x-show="tab === 'pesanan'" x-cloak class="pt-5">
                @if ($pesanan->isEmpty())
                    @include('member.components.empty-state', [
                        'title' => 'Belum Ada Pesanan',
                        'description' => 'Pesanan pick up kamu bakal kecatat di sini.',
                        'icon' => 'icon-clock',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    @php
                        $statusAktif = [\App\Models\Transaksi::STATUS_DIPROSES, \App\Models\Transaksi::STATUS_SIAP_DIAMBIL];
                        $pesananAktif = $pesanan->whereIn('status_pesanan', $statusAktif);
                        $pesananSelesai = $pesanan->whereNotIn('status_pesanan', $statusAktif);
                    @endphp

                    @if ($pesananAktif->isNotEmpty())
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Pesanan Aktif</h2>
                            <span class="text-xs text-gray-400">{{ $pesananAktif->count() }} pesanan</span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
                            @foreach ($pesananAktif as $trx)
                                @php $pesananBadge = $statusPesananBadge[$trx->status_pesanan] ?? null; @endphp
                                <a
                                    href="{{ route('member.pesanan.show', $trx) }}"
                                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                    class="flex w-full items-center gap-3 rounded-2xl border border-brand-200 bg-brand-25 p-4 text-left shadow-sm transition active:bg-brand-50"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-800">
                                        @include('member.components.icon-cake', ['class' => 'h-5 w-5'])
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
                                        <p class="mt-0.5 text-xs text-gray-400">Kode pickup: <span class="font-mono font-bold text-gray-600">{{ $trx->kode_pickup }}</span></p>
                                    </div>
                                    @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4 shrink-0 text-gray-300'])
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($pesananSelesai->isNotEmpty())
                        <div class="{{ $pesananAktif->isNotEmpty() ? 'mt-6' : '' }} flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Pesanan</h2>
                            <span class="text-xs text-gray-400">{{ $pesananSelesai->count() }} pesanan</span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
                            @foreach ($pesananSelesai as $trx)
                                @php $pesananBadge = $statusPesananBadge[$trx->status_pesanan] ?? null; @endphp
                                <a
                                    href="{{ route('member.pesanan.show', $trx) }}"
                                    onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                    class="flex w-full items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 text-left shadow-sm transition active:bg-gray-50"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                        @include('member.components.icon-cake', ['class' => 'h-5 w-5'])
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
                                        <p class="mt-0.5 text-xs text-gray-400">{{ $trx->created_at->translatedFormat('d M Y, H:i') }} &middot; Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                                    </div>
                                    @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4 shrink-0 text-gray-300'])
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- ================= TAB: RIWAYAT TRANSAKSI ================= --}}
            <div x-show="tab === 'transaksi'" x-cloak class="pt-5">
                @if ($transaksi->isEmpty())
                    @include('member.components.empty-state', [
                        'title' => 'Belum Ada Transaksi',
                        'description' => 'Transaksi kamu bakal kecatat di sini.',
                        'icon' => 'icon-clock',
                        'iconBg' => 'bg-gray-100',
                        'iconColor' => 'text-gray-400',
                    ])
                @else
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Riwayat Transaksi</h2>
                        <span class="text-xs text-gray-400">{{ $transaksi->count() }} transaksi</span>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
                        @foreach ($transaksi as $trx)
                            @php
                                $belumLunas = strtolower($trx->status_pembayaran ?? 'lunas') !== 'lunas';
                                // Satu produk bisa punya lebih dari satu baris detail kalau
                                // sebagian dipenuhi dari stok & sebagian lagi butuh diproduksi
                                // (lihat CheckoutController::pecahJumlahProduksi()). Digabung
                                // per nama produk supaya riwayat cuma menampilkan satu baris.
                                $itemsJson = $trx->detailTransaksi
                                    ->groupBy('nama_produk')
                                    ->map(fn ($group) => [
                                        'nama' => $group->first()->nama_produk,
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
                                        @include('member.components.icon-clock', ['class' => 'h-5 w-5'])
                                    @else
                                        @include('member.components.icon-check-circle', ['class' => 'h-5 w-5'])
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-gray-900">{{ $trx->kode_transaksi }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">{{ $trx->created_at->translatedFormat('d M Y, H:i') }} &middot; Rp{{ number_format($trx->total_bayar, 0, ',', '.') }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold {{ $belumLunas ? 'text-red-500' : 'text-green-600' }}">
                                    {{ $belumLunas ? 'Rp' . number_format($trx->total_bayar, 0, ',', '.') : '+' . number_format($trx->poin_didapat ?? 0, 0, ',', '.') . ' poin' }}
                                </p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ================= TAB: VOUCHER (penukaran reward) ================= --}}
            <div x-show="tab === 'voucher'" x-cloak class="pt-5">
                @if ($penukaran->isEmpty())
                    @include('member.components.empty-state', [
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
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
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
                                            @include('member.components.icon-ticket', ['class' => 'h-5 w-5'])
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

            {{-- ================= TAB: STAMP (penukaran hadiah stamp) ================= --}}
            <div x-show="tab === 'stamp'" x-cloak class="pt-5">
                @if ($riwayatStamp->isEmpty())
                    @include('member.components.empty-state', [
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
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
                        @foreach ($riwayatStamp as $item)
                            <button
                                type="button"
                                onclick="bukaDetailRedeem(this)"
                                data-jenis="stamp"
                                data-nama="{{ $item->produk?->nama_produk ?? 'Produk tidak tersedia' }}"
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
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $item->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
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
                    <div class="flex items-center justify-between text-gray-500">
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
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-detail-redeem')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    <script>
        // diskon_label disimpan di DB sebagai JSON string, mis. [{"nama":"Voucher (CEF-PJAY)","nilai":10000}]
        // (rincian tiap komponen diskon: promo produk, diskon manual, voucher). Fungsi ini mengubahnya
        // jadi teks nama yang enak dibaca, bukan JSON mentah.
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
                // Bukan JSON (data lama / label polos) — tampilkan apa adanya.
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
            document.getElementById('detail-trx-pajak').textContent = fmtRupiah(d.pajak);
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
                        <p class="text-theme-sm font-semibold leading-snug text-gray-800">${item.jumlah}x ${item.nama}</p>
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
                : `@include('member.components.icon-ticket', ['class' => 'h-5 w-5'])`;

            const diambilRow = document.getElementById('detail-redeem-diambil-row');
            if (d.diambil) {
                diambilRow.classList.remove('hidden');
                document.getElementById('detail-redeem-diambil').textContent = d.diambil;
            } else {
                diambilRow.classList.add('hidden');
            }

            openSheet('sheet-detail-redeem');
        }
    </script>

@endsection