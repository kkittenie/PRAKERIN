@extends('mobile.layout', ['title' => 'Detail Voucher'])

@php
    $tersedia = $voucher->tersedia ?? true;
    $alasan = $voucher->alasan_nonaktif ?? null;
    $sisaJatah = $voucher->sisa_jatah ?? 1;
    $batas = $voucher->batas_penggunaan_per_member ?: 1;
    $icon = 'icon-ticket';

    $potonganLabel = '';
    if ($voucher->jenis_potongan === 'nominal') {
        $potonganLabel = 'Diskon Rp' . number_format($voucher->nominal_potongan, 0, ',', '.');
    } else {
        $potonganLabel = 'Diskon ' . rtrim(rtrim(number_format($voucher->persentase, 2, ',', '.'), '0'), ',') . '%';
        if ($voucher->maksimal_potongan) {
            $potonganLabel .= ' (Maks Rp' . number_format($voucher->maksimal_potongan, 0, ',', '.') . ')';
        }
    }



    $eligibleProduk = $voucher->produks->map(fn ($p) => [
        'id' => $p->id,
        'nama' => $p->nama_produk,
        'kategori' => $p->kategori->nama_kategori ?? 'Lainnya',
        'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
    ])->values();
@endphp

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        {{-- Header --}}
        <div class="relative -mx-5 sm:mx-0 -mt-6 flex items-center justify-center border-b border-gray-100 px-5 py-4 sm:rounded-t-3xl">
            <a href="{{ route('member.reward.index') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-theme-sm font-bold text-gray-900">Detail Voucher</h1>
        </div>

        {{-- Hero image --}}
        <div class="relative -mx-5 sm:mx-0 h-36 overflow-hidden bg-gradient-to-br from-brand-100 to-brand-200 md:h-48 sm:rounded-2xl">
            @if ($voucher->gambar)
                <img src="{{ asset('storage/' . $voucher->gambar) }}" alt="{{ $voucher->nama }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/10"></div>
            @else
                <span class="absolute -left-6 -top-6 h-28 w-28 rounded-full bg-white/40"></span>
                <span class="absolute -right-8 top-8 h-20 w-20 rounded-full bg-white/30"></span>
                <span class="relative mx-auto flex h-full w-full items-center justify-center">
                    <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/70 shadow-sm text-brand-700">
                        @include('mobile.components.icon-ticket', ['class' => 'h-9 w-9'])
                    </span>
                </span>
            @endif
        </div>

        {{-- Title & details --}}
        <div class="pt-5 space-y-4">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-theme-lg font-bold leading-tight text-gray-900">{{ $voucher->nama }}</h2>
                    @if($tersedia)
                        <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600">
                            {{ $alasan ?: 'Nonaktif' }}
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-theme-sm leading-relaxed text-gray-500 whitespace-pre-line">
                    {{ $voucher->deskripsi ?: 'Gunakan voucher diskon ini saat checkout atau di kasir outlet.' }}
                </p>
            </div>

            <div class="border-t border-dashed border-gray-200"></div>

            {{-- Info Potongan & Sisa Kuota --}}
            <div class="grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Nilai Potongan</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-brand-800">{{ $potonganLabel }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Sisa Pemakaian</p>
                    <p class="mt-0.5 text-theme-sm font-bold {{ $sisaJatah > 0 ? 'text-gray-800' : 'text-red-500' }}">
                        {{ $sisaJatah }} dari {{ $batas }} kali
                    </p>
                </div>
            </div>

            {{-- Min Transaksi --}}
            <div class="grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Min. Transaksi</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                        {{ $voucher->min_pembelian > 0 ? 'Rp' . number_format($voucher->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                    </p>
                </div>
            </div>

            {{-- Expandable rows --}}
            <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                <button type="button" onclick="openSheet('sheet-menu-berlaku')"
                        class="flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Menu yang Berlaku ({{ $eligibleProduk->count() }} Produk)</span>
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])
                </button>
                <button type="button" onclick="openSheet('sheet-cara-pakai')"
                        class="flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Cara Penggunaan</span>
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])
                </button>
            </div>

            {{-- Tombol Pakai Sekarang --}}
            <div class="pt-2">
                @if($tersedia)
                    <a href="{{ route('member.menu') }}"
                       onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                       class="block w-full text-center rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-800">
                        Pesan Menu & Gunakan Voucher
                    </a>
                @else
                    <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">
                        Voucher Tidak Dapat Digunakan ({{ $alasan ?: 'Nonaktif' }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE BAKERY STYLE (MATCHES PRODUCT DETAIL) --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block w-full bg-[#FAF5E8] py-10 px-8 lg:px-12 xl:px-16 min-h-screen relative overflow-hidden text-[#3C2415]">
        @php
            $dNominal = (float) ($voucher->nominal_potongan ?? 0);
            $dPersen = (float) ($voucher->persentase ?? 0);
            $dMinSpend = (float) ($voucher->min_pembelian ?? 0);

            $dTextSearch = ($voucher->nama ?? '') . ' ' . ($voucher->deskripsi ?? '');
            if ($dNominal <= 0 && $dPersen <= 0) {
                if (preg_match('/(\d+[\.,]?\d*)\s*%/i', $dTextSearch, $pm)) {
                    $dPersen = (float) str_replace(',', '.', $pm[1]);
                } elseif (preg_match('/(?:Rp|IDR)\s*([\d\.]+)/i', $dTextSearch, $nm)) {
                    $dNominal = (float) str_replace('.', '', $nm[1]);
                }
            }
            if ($dMinSpend <= 0) {
                if (preg_match('/minimal\s+belanja\s+(?:Rp|IDR)?\s*([\d\.]+)/i', $dTextSearch, $sm)) {
                    $dMinSpend = (float) str_replace('.', '', $sm[1]);
                }
            }

            if ($dPersen > 0) {
                $dDiscountBadge = rtrim(rtrim(number_format($dPersen, 1, '.', ''), '0'), '.') . '% OFF';
                $dDiscountLabel = $dDiscountBadge;
            } elseif ($dNominal > 0) {
                $dDiscountLabel = 'Rp ' . number_format($dNominal, 0, ',', '.') . ' OFF';
                $dDiscountBadge = ($dNominal >= 1000)
                    ? 'Rp ' . rtrim(rtrim(number_format($dNominal / 1000, 1, '.', ''), '0'), '.') . 'K OFF'
                    : $dDiscountLabel;
            } else {
                $dDiscountBadge = 'SPECIAL DEAL';
                $dDiscountLabel = 'Special Discount';
            }

            $featuredPastries = $eligibleProduk->isNotEmpty()
                ? $eligibleProduk->take(3)
                : \App\Models\Produk::dijualSatuan()->where('status_aktif', true)->with('kategori')->take(3)->get();
        @endphp

        {{-- Strawberry Keyframes Style (Consistent with Menu & Beranda) --}}
        <style>
            @keyframes floatStrawberry {
                0%, 100% { transform: translateY(0px) rotate(var(--rot, 0deg)); }
                50% { transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg)); }
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
        <div class="desktop-floating-strawberry hidden xl:block absolute left-10 xl:left-14 2xl:left-24 top-1/2 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: 18deg; --duration: 4.8s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden xl:block absolute right-10 xl:left-auto xl:right-14 2xl:right-24 top-2/3 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: -14deg; --duration: 4.4s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        {{-- Breadcrumb back link (Matches Product Detail) --}}
        <div class="max-w-6xl mx-auto mb-8 relative z-10">
            <a href="{{ route('member.reward.index') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#3C2415]/60 hover:text-[#8E1E26] transition-colors">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Back to Vouchers</span>
            </a>
        </div>

        {{-- Main 2-Column Showcase Hero (Exact Deux / Product Detail Layout) --}}
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 xl:gap-16 items-start relative z-10">
            {{-- Left: Dark Cocoa Showcase Box (Matches Deux Reference) --}}
            <div class="lg:col-span-6">
                <div class="aspect-square w-full rounded-[36px] bg-[#3C2415] p-10 lg:p-14 flex flex-col items-center justify-center relative overflow-hidden shadow-xl text-center select-none">
                    {{-- Ambient Warm Glow Behind Graphic --}}
                    <div class="pointer-events-none absolute inset-0 opacity-40 select-none"
                         style="background: radial-gradient(circle at center, rgba(255, 230, 150, 0.45) 0%, rgba(220, 100, 50, 0.2) 45%, rgba(60, 36, 21, 0) 75%);">
                    </div>

                    {{-- Line Art Sparkles --}}
                    <svg class="pointer-events-none absolute inset-0 w-full h-full opacity-18 select-none" viewBox="0 0 400 400" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round">
                        <path d="M 60 70 L 63 85 L 78 88 L 63 91 L 60 106 L 57 91 L 42 88 L 57 85 Z"/>
                        <path d="M 340 80 L 342 92 L 354 94 L 342 96 L 340 108 L 338 96 L 326 94 L 338 92 Z"/>
                        <path d="M 330 310 L 333 325 L 348 328 L 333 331 L 330 346 L 327 331 L 312 328 L 327 325 Z"/>
                        <path d="M 70 320 L 72 330 L 82 332 L 72 334 L 70 344 L 68 334 L 58 332 L 68 330 Z"/>
                    </svg>

                    {{-- Badges on Photo Box --}}
                    <span class="absolute left-6 top-6 rounded-full bg-[#8E1E26] px-3.5 py-1 text-xs font-bold text-white shadow-sm tracking-wide">
                        Member Exclusive
                    </span>
                    <span class="absolute right-6 top-6 font-mono text-[11px] font-bold text-[#FAF5E8]/70 tracking-widest">
                        DB-VCH-{{ str_pad($voucher->id, 5, '0', STR_PAD_LEFT) }}
                    </span>

                    {{-- Centerpiece: Cake / Voucher Illustration --}}
                    @if ($voucher->gambar)
                        <img src="{{ asset('storage/' . $voucher->gambar) }}" alt="{{ $voucher->nama }}"
                             class="max-h-[220px] max-w-[220px] object-contain filter drop-shadow-[0_16px_28px_rgba(0,0,0,0.45)] transition-transform duration-500 hover:scale-105 select-none relative z-10">
                    @else
                        <div class="relative z-10 flex flex-col items-center">
                            <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="Artisanal Daisy Strawberry Celebration Cake"
                                 class="w-40 sm:w-44 lg:w-48 h-auto object-contain filter drop-shadow-[0_20px_26px_rgba(0,0,0,0.65)] select-none transition-transform duration-500 hover:scale-105">
                        </div>
                    @endif

                    {{-- Centerpiece Typography Overlay --}}
                    <div class="relative z-10 mt-6 space-y-1">
                        <p class="font-display font-black text-4xl sm:text-5xl text-amber-200 tracking-tight drop-shadow-md">
                            {{ $dDiscountBadge }}
                        </p>
                        <p class="text-xs font-semibold text-[#FAF5E8]/80">
                            {{ $dMinSpend > 0 ? 'Min. purchase Rp ' . number_format($dMinSpend, 0, ',', '.') : 'No minimum spend required' }}
                        </p>
                    </div>

                    {{-- Status Badge on Bottom-Left of Showcase Box --}}
                    @if($tersedia)
                        <span class="absolute left-6 bottom-6 rounded-full bg-emerald-950/80 border border-emerald-400/30 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-300 backdrop-blur-xs flex items-center gap-1.5 shadow-sm">
                            <span class=""></span> Ready to Use
                        </span>
                    @else
                        <span class="absolute left-6 bottom-6 rounded-full bg-[#8E1E26]/90 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-xs shadow-sm">
                            {{ $alasan ?: 'Unavailable' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Voucher Details, Price / CTA & Specifications (Matches Deux / Product Detail) --}}
            <div class="lg:col-span-6 flex flex-col justify-between">
                <div>

                    {{-- Voucher Title --}}
                    <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-[#3C2415] tracking-tight leading-tight mb-4">
                        {{ $voucher->nama }}
                    </h1>

                    {{-- Voucher Description (From database) --}}
                    <p class="font-sans text-sm lg:text-base text-[#7C6558] leading-relaxed mb-6 whitespace-pre-line">
                        {{ $voucher->deskripsi ?: 'Handcrafted bakery savings available for our sweet members. Use this discount during online pickup checkout or present your member ID directly in-store.' }}
                    </p>
                </div>

                {{-- Price & CTA Row (Matches $9.00 / 1pc + SHOP NOW -> in reference) --}}
                <div class="flex flex-wrap items-center justify-between gap-4 py-5 border-y border-[#3C2415]/10 mb-6">
                    <div class="flex flex-col">
                        <span class="font-display font-black text-3xl xl:text-4xl text-[#8E1E26]">
                            {{ $dDiscountLabel }}
                        </span>
                        <span class="font-sans text-xs text-[#9C8C80] font-bold uppercase tracking-wider mt-0.5">
                            {{ $dMinSpend > 0 ? 'Min. Purchase Rp ' . number_format($dMinSpend, 0, ',', '.') : 'No Minimum Purchase' }}
                        </span>
                    </div>

                    <div>
                        @if($tersedia)
                            <a href="{{ route('member.menu') }}"
                               onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                               class="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] active:scale-95 text-white font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-md cursor-pointer">
                                <span>ORDER MENU &amp; APPLY</span>
                                <svg class="h-3.5 w-3.5 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </a>
                        @else
                            <button disabled class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-gray-300 text-gray-500 font-black text-xs uppercase tracking-widest cursor-not-allowed">
                                <span>VOUCHER UNAVAILABLE</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Voucher Specifications Table Card (Matches Biscuit Specs Box in Reference) --}}
                <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/10 space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Discount Value</span>
                        <span class="font-black text-[#8E1E26]">{{ $dDiscountLabel }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Minimum Spend</span>
                        <span class="font-medium text-[#7C6558]">{{ $dMinSpend > 0 ? 'Rp ' . number_format($dMinSpend, 0, ',', '.') : 'No Minimum Spend' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Usage Quota</span>
                        <span class="font-medium text-[#7C6558]">{{ $sisaJatah }} of {{ $batas }} times remaining</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Validity Period</span>
                        <span class="font-medium text-[#7C6558]">
                            {{ $voucher->tanggal_selesai ? $voucher->tanggal_selesai->format('d M Y') : ($voucher->tanggal_akhir ? $voucher->tanggal_akhir->format('d M Y') : 'Valid Ongoing') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Voucher Code</span>
                        <span class="font-mono text-[11px] text-[#7C6558]">DB-VCH-{{ str_pad($voucher->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Redemption Method</span>
                        <span class="font-medium text-[#7C6558]">Online Pickup &amp; In-Store Cashier</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Featured Section: "A LITTLE TASTE OF EVERYTHING WE BAKE" (Matches Product Detail) --}}
        <div class="max-w-6xl mx-auto mt-24 pt-16 border-t border-[#3C2415]/10 text-center relative z-10">

            @if ($eligibleProduk->isNotEmpty())
                <h2 class="font-display font-black text-3xl lg:text-4xl text-[#3C2415] uppercase tracking-wider mb-12">
                    ELIGIBLE BAKERY ITEMS ({{ $eligibleProduk->count() }})
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    @foreach ($eligibleProduk->take(3) as $prod)
                        <div class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div class="aspect-square w-full rounded-[24px] bg-[#3C2415]/5 overflow-hidden flex items-center justify-center mb-5">
                                @if ($prod['foto'])
                                    <img src="{{ $prod['foto'] }}" alt="{{ $prod['nama'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="{{ $prod['nama'] }}" class="w-28 h-28 object-contain">
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-display font-bold uppercase tracking-wider text-[#8E1E26]">{{ $prod['kategori'] }}</span>
                                <h3 class="font-display font-black text-lg text-[#3C2415] mt-1">{{ $prod['nama'] }}</h3>
                            </div>
                            <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                <a href="{{ route('member.produk.show', $prod['id']) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-white text-[11px] font-bold uppercase tracking-wider transition-colors">
                                    <span>View Item</span> &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <h2 class="font-display font-black text-3xl lg:text-4xl text-[#3C2415] uppercase tracking-wider mb-12">
                    A LITTLE TASTE OF EVERYTHING WE BAKE
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    @foreach ($featuredPastries as $prod)
                        <div class="group rounded-[32px] bg-[#F5ECE3] p-7 text-left border border-[#3C2415]/10 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div class="aspect-square w-full rounded-[24px] bg-[#3C2415]/5 overflow-hidden flex items-center justify-center mb-5">
                                @if ($prod->foto)
                                    <img src="{{ asset('storage/' . $prod->foto) }}" alt="{{ $prod->nama_produk }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <img src="{{ asset('images/decor/vintage-strawberry-cake.png') }}" alt="{{ $prod->nama_produk }}" class="w-28 h-28 object-contain">
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-display font-bold uppercase tracking-wider text-[#8E1E26]">{{ $prod->kategori->nama_kategori ?? 'Artisan Bakes' }}</span>
                                <h3 class="font-display font-black text-lg text-[#3C2415] mt-1">{{ $prod->nama_produk }}</h3>
                                <p class="font-display font-bold text-sm text-[#3C2415] mt-1">Rp {{ number_format($prod->harga_jual ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                                <a href="{{ route('member.produk.show', $prod->id) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#3C2415] hover:bg-[#8E1E26] text-white text-[11px] font-bold uppercase tracking-wider transition-colors">
                                    <span>Shop</span> &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-12">
                <a href="{{ route('member.menu') }}"
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-white font-black text-xs uppercase tracking-widest transition active:scale-95 shadow-md">
                    <span>VIEW ALL MENU</span>
                    <svg class="h-3.5 w-3.5 text-[#EACE80]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    {{-- ========================================== --}}
    {{-- END DESKTOP VIEW --}}
    {{-- ========================================== --}}

    {{-- Bottom Sheet: Menu yang Berlaku --}}
    <dialog id="sheet-menu-berlaku" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Menu yang Berlaku</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($eligibleProduk as $prod)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                            @if($prod['foto'])
                                <img src="{{ $prod['foto'] }}" class="h-11 w-11 rounded-xl object-cover">
                            @else
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-100 text-brand-800 font-bold text-xs">
                                    {{ substr($prod['nama'], 0, 2) }}
                                </span>
                            @endif
                            <div>
                                <p class="text-xs font-bold text-gray-900">{{ $prod['nama'] }}</p>
                                <p class="text-[11px] text-gray-400">{{ $prod['kategori'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-menu-berlaku')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom Sheet: Cara Pakai --}}
    <dialog id="sheet-cara-pakai" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Cara Penggunaan Voucher</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    <ol class="list-decimal space-y-2.5 pl-4">
                        <li><strong>Pemesanan Pickup di Aplikasi:</strong> Masuk ke halaman Menu, pilih menu favoritmu, lalu buka Checkout. Pilih voucher ini di bagian Voucher untuk mendapatkan potongan otomatis.</li>
                        <li><strong>Pemesanan di Outlet (Kasir):</strong> Sebutkan nama atau nomor HP member kamu saat bertransaksi di kasir. Kasir akan langsung menerapkan voucher yang berhak kamu gunakan.</li>
                        <li>Setiap transaksi hanya dapat menggunakan maksimal 1 voucher.</li>
                    </ol>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-cara-pakai')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

@endsection