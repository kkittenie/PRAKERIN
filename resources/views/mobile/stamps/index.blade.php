@extends('mobile.layout', ['title' => 'Stamp'])

@section('content')

    <div class="lg:hidden">

    <h1 class="text-center text-lg font-extrabold text-gray-900">Vouchers</h1>

    @if (\App\Models\PengaturanMember::adaAturanAktif())
        <div class="mt-3 flex items-center justify-center gap-1.5 rounded-2xl border border-gray-100 bg-brand-25 py-2.5 text-xs text-gray-500">
            Your current points:
            <span class="font-bold text-brand-800">{{ number_format($member->poin, 0, ',', '.') }}</span>
        </div>
    @endif

    {{-- Same tab bar as the voucher catalog, so Stamp reads as a tab, not a separate page --}}
    <div class="mt-5 grid grid-cols-3 border-b border-gray-100">
        @foreach ([
            ['label' => 'All', 'href' => route('member.reward.index'), 'active' => false],
            ['label' => 'Voucher', 'href' => route('member.reward.index', ['tab' => 'voucher']), 'active' => false],
            ['label' => 'Stamp', 'href' => route('member.stamps.index'), 'active' => true],
        ] as $tab)
            <a href="{{ $tab['href'] }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
               class="relative pb-3 text-center text-[13px] font-bold transition {{ $tab['active'] ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                {{ $tab['label'] }}
                @if ($tab['active'])
                    <span class="absolute inset-x-0 -bottom-px mx-auto h-[2.5px] w-10 rounded-full bg-brand-800"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Everything below is fetched and injected as-is into the Stamp pane on the
         Vouchers page (see rewards/index.blade.php), so this stays a self-contained
         chunk that also works fine on its own when this page is opened directly. --}}
    <div id="stamp-tab-content" class="flex flex-1 flex-col">

    @if (! $program)

        @include('mobile.components.empty-state', [
            'title' => 'No Active Stamp Program',
            'description' => 'Stay tuned, the next stamp collection program will be available soon.',
            'icon' => 'icon-cake',
            'iconBg' => 'bg-orange-50',
            'iconColor' => 'text-orange-500',
        ])

    @else

        @php
            $target = max(1, $program->jumlah_stamp_dibutuhkan);
            $current = min($memberStamp->jumlah_stamp, $target);
            $penuh = $memberStamp->jumlah_stamp >= $target;
            $kuotaHabis = $program->maksimal_redeem_tercapai;
            $bisaTukar = $penuh && ! $kuotaHabis;
        @endphp

        {{-- Punch-card progress --}}
        <div class="mt-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#3C2415] to-[#5A3825] p-5 text-white shadow-sm {{ $penuh ? 'stamp-card-penuh' : '' }}"
             id="stamp-program-data"
             data-nama-program="{{ $program->nama_program }}"
             data-current="{{ $current }}"
             data-target="{{ $target }}"
             data-penuh="{{ $penuh ? '1' : '0' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Stamp Program</p>
                    <p class="mt-0.5 truncate text-base font-extrabold">{{ $program->nama_program }}</p>
                    @if ($program->deskripsi)
                        <p class="mt-1 text-xs leading-relaxed text-white/75 whitespace-pre-line">{{ $program->deskripsi }}</p>
                    @endif
                </div>
                <span class="stamp-badge-icon flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#FAF5E8] p-2 shadow-xs border border-white/20">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                </span>
            </div>

            <p class="mt-4 text-[11px] text-white/70">
                Every order with min. spend Rp{{ number_format($program->minimal_pembelian, 0, ',', '.') }} (after discounts) = 1 stamp
            </p>

            {{-- Stamp dots pop in one by one (staggered) as they load, so a filled
                 punch-card feels alive instead of just appearing all at once. --}}
            <div class="mt-4 grid grid-cols-5 md:grid-cols-10 gap-2.5">
                @for ($i = 1; $i <= $target; $i++)
                    @php
                        $terisi = $i <= $current;
                    @endphp
                    <span
                        class="flex aspect-square items-center justify-center overflow-hidden rounded-full {{ $terisi ? 'stamp-dot-fill bg-[#FAF5E8] text-brand-800 shadow-sm' : 'border-2 border-dashed border-white/35 text-white/35' }}"
                        @if ($terisi) style="animation-delay: {{ ($i - 1) * 0.09 }}s" @endif
                    >
                        @if ($terisi)
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain p-2">
                        @else
                            @include('mobile.components.icon-cake', ['class' => 'h-4 w-4'])
                        @endif
                    </span>
                @endfor
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-white/15 pt-3">
                <p class="text-xs font-bold text-white">{{ $current }}/{{ $target }} stamps</p>
                @if ($penuh)
                    <p class="text-xs font-bold text-white">Card complete! Redeem your treat! 🎉</p>
                @else
                    <p class="text-xs text-white/75">{{ $target - $current }} more stamp(s) needed to redeem</p>
                @endif
            </div>
        </div>

        @if ($kuotaHabis)
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
                Redemption quota for this stamp program is currently reached.
            </div>
        @endif

        {{-- Daftar Hadiah Stamp yang Sudah Ditukar & Menunggu Diambil --}}
        @if (!empty($rewardAktif) && $rewardAktif->isNotEmpty())
            <div class="mt-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                        <span class="text-base">🎁</span>
                        <span>Stamp Rewards Ready for Pickup</span>
                    </h2>
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                        {{ $rewardAktif->count() }} Ready
                    </span>
                </div>

                @foreach ($rewardAktif as $reward)
                    @php
                        $produkHadiah = $reward->produk;
                        $isCroissant = str_contains(strtolower($produkHadiah?->nama_produk ?? ''), 'croissant');
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-amber-200/90 bg-gradient-to-br from-amber-50/70 via-white to-orange-50/40 p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white border border-amber-200 shadow-xs">
                                @if ($produkHadiah?->foto)
                                    <img src="{{ asset('storage/' . $produkHadiah->foto) }}" alt="{{ $produkHadiah->nama_produk }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-brand-800">@include('mobile.components.icon-gift', ['class' => 'h-6 w-6'])</span>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-extrabold text-gray-900">{{ $reward->jumlah ?: 1 }}x {{ $produkHadiah ? $reward->nama_tampil : 'Reward Item' }}</p>
                                    <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                        Ready for Pickup
                                    </span>
                                </div>
                                <p class="mt-0.5 text-[11px] text-gray-500 font-mono tracking-wider font-semibold">Code: {{ $reward->kode_redeem }}</p>
                            </div>
                        </div>

                        <div class="mt-3.5 pt-3 border-amber-200/60 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                            <p class="text-[11px] text-gray-500 leading-tight">
                            </p>
                            <form method="POST" action="{{ route('member.stamps.ambil-aplikasi', $reward->id) }}" class="shrink-0">
                                @csrf
                                <button type="submit"
                                    class="w-full sm:w-auto rounded-xl bg-[#5a3c27] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#4a3020] active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Claim now</span>
                                    <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Section header --}}
        <div class="mt-6 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Redeem Rewards</h2>
            <span class="text-xs text-gray-400">{{ $program->hadiah->count() }} options</span>
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
            @forelse ($program->hadiah as $hadiah)
                @php
                    $produk = $hadiah->produk;
                    $isCroissant = str_contains(strtolower($produk?->nama_produk ?? ''), 'croissant');
                    $fotoHadiah = $hadiah->detailProduk?->foto ?: $produk?->foto;
                    $stokHabis = !is_null($hadiah->stok_tersedia) && $hadiah->stok_tersedia < $hadiah->jumlah;
                    $bisaTukarHadiah = $bisaTukar && $produk && ! $stokHabis;
                @endphp
                <div class="flex items-center gap-3 rounded-2xl border border-[#3C2415]/10 bg-[#FDFBF7] p-3 shadow-xs {{ ! $bisaTukarHadiah ? 'opacity-60' : '' }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#FAF5E8] border border-[#3C2415]/10">
                        @if ($fotoHadiah)
                            <img src="{{ asset('storage/'.$fotoHadiah) }}" alt="{{ $produk->nama_produk }}" class="h-full w-full object-cover">
                        @elseif ($isCroissant)
                            <span class="text-brand-800">@include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])</span>
                        @else
                            <span class="text-brand-800">@include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])</span>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900">{{ $hadiah->jumlah }}x {{ $produk ? $hadiah->nama_tampil : 'Product unavailable' }}</p>
                        @if ($stokHabis)
                            <p class="text-xs font-semibold text-red-500">Reward out of stock</p>
                        @else
                            <p class="text-xs text-gray-400">Free with completed stamp card</p>
                        @endif
                    </div>

                    <button type="button"
                        @if ($bisaTukarHadiah)
                            data-produk-id="{{ $produk->id }}"
                            data-jumlah="{{ $hadiah->jumlah }}"
                            data-detail-id="{{ $hadiah->detail_produk_id }}"
                            data-nama-produk="{{ $hadiah->nama_tampil }}"
                            onclick="bukaKonfirmasiTukarStampFromBtn(this)"
                        @elseif ($stokHabis)
                            x-on:click="$dispatch('app-alert', { title: 'Out of stock', message: @js('Sorry, this reward is currently out of stock. Please choose another reward or check back later.') })"
                        @else
                            x-on:click="$dispatch('app-alert', { title: 'Not enough stamps', message: @js('You do not have enough stamps yet ('.$current.'/'.$target.' collected). Collect '.($target - $current).' more stamp(s) to redeem this reward!') })"
                        @endif
                        class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition cursor-pointer {{ $bisaTukarHadiah ? 'bg-[#5a3c27] text-white hover:bg-[#4a3020] active:scale-95' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                        Redeem
                    </button>
                </div>
            @empty
                <div class="rounded-2xl border border-[#3C2415]/10 bg-[#FDFBF7] p-6 text-center">
                    <p class="text-sm font-semibold text-gray-500">No rewards available yet for this program.</p>
                </div>
            @endforelse
        </div>

    @endif

    </div>

    </div>

    {{-- Shared confirmation dialog, filled in dynamically by bukaKonfirmasiTukarStamp() --}}
    <dialog id="konfirmasi-tukar-stamp" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
        <div class="flex shrink-0 justify-center pb-1 pt-3">
            <span class="h-1 w-10 rounded-full bg-gray-200"></span>
        </div>
        <div class="p-5 pt-2 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                @include('mobile.components.icon-gift', ['class' => 'h-7 w-7'])
            </span>
            <h3 class="mt-3 text-base font-bold text-gray-900">Redeem reward <span id="konfirmasi-tukar-stamp-nama"></span>?</h3>
            <p class="mt-1 text-xs text-gray-500">Your collected stamps will be exchanged for this treat.</p>

            <form method="POST" action="{{ route('member.stamps.redeem') }}" class="mt-6 pb-2">
                @csrf
                <input type="hidden" name="produk_id" id="konfirmasi-tukar-stamp-produk-id">
                <input type="hidden" name="detail_produk_id" id="konfirmasi-tukar-stamp-detail-id">
                <input type="hidden" name="metode_ambil" id="konfirmasi-tukar-stamp-metode" value="aplikasi">

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="closeSheet('konfirmasi-tukar-stamp')"
                        class="w-full rounded-2xl border border-[#3C2415]/15 bg-[#FAF5E8] py-3 text-xs font-bold text-[#3C2415] hover:bg-[#3C2415]/5 active:scale-[0.99] cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="w-full rounded-2xl bg-[#3C2415] py-3 text-xs font-bold text-white shadow-md hover:bg-[#2D1B10] active:scale-[0.99] cursor-pointer">
                        Confirm & Redeem
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        window.bukaKonfirmasiTukarStamp = function (produkId, jumlah, namaProduk, detailId) {
            const hiddenInput = document.getElementById('konfirmasi-tukar-stamp-produk-id');
            const hiddenDetail = document.getElementById('konfirmasi-tukar-stamp-detail-id');
            const namaSpan = document.getElementById('konfirmasi-tukar-stamp-nama');
            if (hiddenInput) hiddenInput.value = produkId;
            if (hiddenDetail) hiddenDetail.value = detailId || '';
            if (namaSpan) namaSpan.textContent = jumlah + 'x ' + namaProduk;
            openSheet('konfirmasi-tukar-stamp');
        };

        window.bukaKonfirmasiTukarStampFromBtn = function (btn) {
            if (!btn) return;
            const produkId = btn.dataset.produkId;
            const jumlah = btn.dataset.jumlah;
            const namaProduk = btn.dataset.namaProduk;
            window.bukaKonfirmasiTukarStamp(produkId, jumlah, namaProduk, btn.dataset.detailId);
        };

        // Only runs when this page is opened directly (not when the Vouchers
        // page injects this markup via innerHTML — injected <script> tags don't
        // execute there, and that page triggers the animation itself, at the
        // moment the Stamp tab actually becomes visible).
        document.querySelectorAll('#stamp-tab-content .stamp-dot-fill').forEach((dot) => {
            dot.classList.add('stamp-pop');
        });
    </script>

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - DAISY STAMP CLUB HUB --}}
    {{-- ========================================== --}}
    <style>
        @keyframes floatStrawberry {
            0%, 100% {
                transform: translateY(0px) rotate(var(--rot, 0deg));
            }
            50% {
                transform: translateY(-12px) rotate(calc(var(--rot, 0deg) + 4deg));
            }
        }
        .desktop-floating-strawberry {
            animation: floatStrawberry var(--duration, 4.5s) ease-in-out infinite;
            will-change: transform;
        }
    </style>

    <div id="stamps-desktop-root" class="hidden lg:block min-h-screen relative overflow-hidden bg-[#FAF5E8] text-[#3C2415] pb-24">
        {{-- Floating 2D Strawberry Accent Elements --}}
        <div class="desktop-floating-strawberry absolute left-6 xl:left-12 2xl:left-20 top-20 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: -8deg; --duration: 4.8s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry absolute right-6 xl:right-12 2xl:right-20 top-28 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 48px; height: 64px; --rot: 10deg; --duration: 5.2s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry absolute left-8 xl:left-16 2xl:left-24 bottom-32 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: 6deg; --duration: 4.4s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry absolute right-8 xl:right-16 2xl:right-24 bottom-24 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: -6deg; --duration: 5.0s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        <div class="relative max-w-6xl mx-auto px-6 lg:px-12 pt-8 z-10 space-y-10">

            {{-- Top Navigation & Badge Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Back to Account</span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#8E1E26]/10 text-[#8E1E26] text-[11px] font-bold uppercase tracking-wider">
                        <span class="h-2 w-2 rounded-full bg-[#8E1E26] animate-pulse"></span>
                        DAISY LOYALTY CLUB • Digital Stamp Card
                    </span>
                </div>
            </div>

            {{-- Clean Page Header with Strawberry Theme --}}
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#8E1E26]/10 border border-[#8E1E26]/20 text-[11px] font-black uppercase tracking-wider text-[#8E1E26]">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 object-contain">
                    <span>DAISY STAMP CLUB</span>
                </div>
                <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl uppercase tracking-tight text-[#3C2415]">
                    Stamp Collection
                </h1>
                <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                    Collect digital stamps on every bakery visit and treat yourself to complimentary handcrafted pastries.
                </p>
            </div>

            {{-- Tab Switcher Pills (All Rewards, Vouchers, Stamp Card) --}}
            <div class="flex items-center gap-3 border-b border-[#3C2415]/10 pb-4">
                <a href="{{ route('member.reward.index') }}"
                   class="px-5 py-2.5 rounded-full text-xs font-bold text-[#3C2415]/70 hover:text-[#8E1E26] hover:bg-[#3C2415]/5 transition">
                    All Rewards
                </a>
                <a href="{{ route('member.reward.index', ['tab' => 'voucher']) }}"
                   class="px-5 py-2.5 rounded-full text-xs font-bold text-[#3C2415]/70 hover:text-[#8E1E26] hover:bg-[#3C2415]/5 transition">
                    Vouchers
                </a>
                <a href="{{ route('member.stamps.index') }}"
                   class="px-5 py-2.5 rounded-full text-xs font-black bg-[#8E1E26] text-white shadow-xs">
                    Stamp Card
                </a>
            </div>

            @if (! $program)
                {{-- Empty State Card --}}
                <div class="rounded-[32px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center space-y-4 shadow-sm">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#FAF5E8] text-amber-600">
                        @include('mobile.components.icon-gift', ['class' => 'h-8 w-8'])
                    </span>
                    <h3 class="font-display font-black text-xl text-[#3C2415]">No Active Stamp Program</h3>
                    <p class="text-xs sm:text-sm text-[#3C2415]/70 max-w-md mx-auto">
                        Stay tuned! Our seasonal stamp collection program will launch again soon with free artisan pastries.
                    </p>
                </div>
            @else
                @php
                    $target = max(1, $program->jumlah_stamp_dibutuhkan);
                    $current = min($memberStamp->jumlah_stamp ?? 0, $target);
                    $penuh = ($memberStamp->jumlah_stamp ?? 0) >= $target;
                    $kuotaHabis = $program->maksimal_redeem_tercapai ?? false;
                    $bisaTukar = $penuh && ! $kuotaHabis;
                @endphp

                {{-- Expansive Desktop Punch Card --}}
                <div class="relative w-full rounded-[36px] bg-[#3C2415] text-[#FAF5E8] p-8 lg:p-12 shadow-2xl border border-white/10 overflow-hidden">
                    <div class="pointer-events-none absolute -right-12 -bottom-12 w-64 h-64 opacity-10 select-none">
                        <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain filter invert">
                    </div>

                    <div class="relative z-10 flex flex-wrap items-start justify-between gap-6 pb-8 border-b border-[#FAF5E8]/15">
                        <div class="space-y-2 max-w-xl">
                            <h2 class="font-display font-black text-2xl lg:text-3xl text-[#FAF5E8] tracking-tight">
                                @if(str_contains(strtolower($program->nama_program), 'kumpulkan 5 stamp'))
                                    Collect 5 Stamps, Get Free Pastry!
                                @else
                                    {{ $program->nama_program }}
                                @endif
                            </h2>
                            <p class="text-xs sm:text-sm text-[#FAF5E8]/80 font-medium leading-relaxed">
                                Every order min. Rp {{ number_format($program->minimal_pembelian, 0, ',', '.') }} = 1 digital stamp. Collect {{ $target }} stamps for a free bakery treat!
                            </p>
                        </div>

                        <div class="bg-[#FAF5E8]/15 border border-[#FAF5E8]/20 rounded-2xl px-6 py-4 text-center backdrop-blur-xs">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-[#FAF5E8]/70 block">STAMPS COLLECTED</span>
                            <p class="font-display font-black text-3xl text-amber-200 mt-0.5">
                                {{ $current }} <span class="text-sm font-sans font-bold text-[#FAF5E8]">/ {{ $target }}</span>
                            </p>
                            <span class="text-[11px] font-semibold text-emerald-300 block mt-1">
                                {{ $penuh ? '🎉 Card Complete!' : ($target - $current) . ' stamps to treat' }}
                            </span>
                        </div>
                    </div>

                    {{-- 5 Large Horizontal Stamp Punch Circles --}}
                    <div class="relative z-10 py-10">
                        <div class="flex items-center justify-between gap-4 max-w-4xl mx-auto">
                            @for ($i = 1; $i <= $target; $i++)
                                @php $terisi = $i <= $current; @endphp
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-20 h-20 lg:w-24 lg:h-24 rounded-full flex items-center justify-center transition-all duration-300 relative
                                                {{ $terisi
                                                    ? 'bg-gradient-to-br from-[#FAF5E8] to-[#FFFDF9] text-[#3C2415] shadow-lg ring-4 ring-amber-300/40 stamp-pop'
                                                    : 'border-2 border-dashed border-[#FAF5E8]/35 bg-[#FAF5E8]/5 text-[#FAF5E8]/40' }}">
                                        @if ($terisi)
                                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-10 w-10 lg:h-12 lg:w-12 object-contain">
                                            <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white text-[10px] font-bold shadow-xs">
                                                ✓
                                            </span>
                                        @else
                                            <div class="text-center">
                                                <span class="text-xs font-bold block opacity-60">#{{ $i }}</span>
                                                <span class="text-[9px] font-semibold uppercase tracking-wider block opacity-40">Stamp</span>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $terisi ? 'text-amber-200' : 'text-[#FAF5E8]/40' }}">
                                        {{ $terisi ? 'Collected' : 'Empty' }}
                                    </span>
                                </div>

                                @if ($i < $target)
                                    <div class="flex-1 h-0.5 border-t-2 border-dashed {{ $i < $current ? 'border-amber-300/60' : 'border-[#FAF5E8]/20' }} mb-5"></div>
                                @endif
                            @endfor
                        </div>
                    </div>

                    <div class="relative z-10 pt-6 border-t border-[#FAF5E8]/15 flex items-center justify-between">
                        <p class="text-xs font-semibold text-[#FAF5E8]/90">
                            @if ($penuh)
                                <strong class="text-amber-200">Congratulations!</strong> Choose your free artisan treat below.
                            @else
                                Collect <strong>{{ $target - $current }} more stamps</strong> to redeem your reward.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Claimed Rewards Ready for Pickup --}}
                @if (!empty($rewardAktif) && $rewardAktif->isNotEmpty())
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display font-black text-xl text-[#3C2415] uppercase tracking-wide flex items-center gap-2">
                                <span>🎁</span>
                                <span>Stamp Rewards Ready for Pickup</span>
                            </h3>
                            <span class="rounded-full bg-[#8E1E26]/10 px-3 py-1 text-xs font-bold text-[#8E1E26]">
                                {{ $rewardAktif->count() }} Available
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($rewardAktif as $reward)
                                @php
                                    $produkHadiah = $reward->produk;
                                @endphp
                                <div class="flex items-center justify-between gap-4 rounded-2xl bg-[#F5ECE3] p-5 border border-[#3C2415]/15 shadow-sm">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white border border-[#3C2415]/10 shadow-xs">
                                            @if ($produkHadiah?->foto)
                                                <img src="{{ asset('storage/' . $produkHadiah->foto) }}" alt="{{ $produkHadiah->nama_produk }}" class="h-full w-full object-cover">
                                            @else
                                                <span class="text-[#8E1E26]">@include('mobile.components.icon-gift', ['class' => 'h-7 w-7'])</span>
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-extrabold text-[#3C2415]">{{ $reward->jumlah ?: 1 }}x {{ $produkHadiah ? $reward->nama_tampil : 'Reward Item' }}</p>
                                            <p class="text-xs text-[#3C2415]/65 font-mono font-semibold mt-0.5">Code: {{ $reward->kode_redeem }}</p>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('member.stamps.ambil-aplikasi', $reward->id) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-xl bg-[#8E1E26] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#72181E] active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                            <span>Claim in Store</span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Available Pastry Treats to Redeem --}}
                <div class="space-y-6">
                    <div class="space-y-1">
                        <h3 class="font-display font-black text-2xl text-[#3C2415] uppercase tracking-wide">
                            Free Artisan Pastries to Redeem
                        </h3>
                        <p class="text-xs text-[#3C2415]/70">Redeem with your {{ $target }} collected stamps.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($program->hadiah as $hadiah)
                            @php
                                $produk = $hadiah->produk;
                                $isCroissant = str_contains(strtolower($produk?->nama_produk ?? ''), 'croissant');
                                $fotoHadiah = $hadiah->detailProduk?->foto ?: $produk?->foto;
                                $stokHabis = !is_null($hadiah->stok_tersedia) && $hadiah->stok_tersedia < $hadiah->jumlah;
                                $bisaTukarHadiah = $bisaTukar && $produk && ! $stokHabis;
                            @endphp
                            <div class="flex flex-col justify-between rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm transition hover:shadow-md hover:-translate-y-0.5">
                                <div>
                                    <div class="aspect-video w-full rounded-2xl overflow-hidden bg-white border border-[#3C2415]/10 flex items-center justify-center mb-4">
                                        @if ($fotoHadiah)
                                            <img src="{{ asset('storage/'.$fotoHadiah) }}" alt="{{ $produk->nama_produk }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[#8E1E26]">@include('mobile.components.icon-cake', ['class' => 'h-10 w-10'])</span>
                                        @endif
                                    </div>

                                    <h4 class="font-display font-bold text-base text-[#3C2415]">
                                        {{ $hadiah->jumlah }}x {{ $produk ? $hadiah->nama_tampil : 'Product unavailable' }}
                                    </h4>
                                    <p class="text-xs text-[#3C2415]/65 mt-1">
                                        @if ($stokHabis)
                                            <span class="text-red-600 font-semibold">Reward out of stock</span>
                                        @else
                                            Free with completed stamp card
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-5 pt-4 border-t border-[#3C2415]/10">
                                    @if ($bisaTukarHadiah)
                                        <button type="button"
                                            onclick="bukaKonfirmasiTukarStampDesktop({{ $produk->id }}, {{ $hadiah->jumlah }}, '{{ addslashes($hadiah->nama_tampil) }}', {{ $hadiah->detail_produk_id ? $hadiah->detail_produk_id : 'null' }})"
                                            class="w-full rounded-2xl bg-[#8E1E26] py-3 text-xs font-black uppercase tracking-wider text-white hover:bg-[#72181E] shadow-sm transition active:scale-95 cursor-pointer">
                                            Redeem Pastry
                                        </button>
                                    @elseif ($stokHabis)
                                        <button type="button" disabled
                                            class="w-full rounded-2xl bg-[#3C2415]/10 py-3 text-xs font-bold text-[#3C2415]/40 cursor-not-allowed">
                                            Out of Stock
                                        </button>
                                    @else
                                        <button type="button"
                                            x-on:click="$dispatch('app-alert', { title: 'Not enough stamps', message: 'You have {{ $current }}/{{ $target }} stamps. Collect {{ $target - $current }} more stamps to redeem this pastry treat!' })"
                                            class="w-full rounded-2xl bg-[#3C2415]/10 py-3 text-xs font-bold text-[#3C2415]/60 hover:bg-[#3C2415]/15 transition cursor-pointer">
                                            {{ $target - $current }} more needed
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full rounded-2xl border border-[#3C2415]/15 bg-[#F5ECE3] p-8 text-center">
                                <p class="text-sm font-semibold text-[#3C2415]/70">No rewards available yet for this program.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>

        {{-- Desktop Confirmation Modal --}}
        <dialog id="konfirmasi-tukar-stamp-desktop" class="sheet fixed inset-0 m-auto w-full max-w-md rounded-3xl border border-[#3C2415]/15 bg-[#FAF5E8] p-0 shadow-2xl backdrop:bg-black/40">
            <div class="p-6 text-center space-y-4">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#8E1E26]/10 text-[#8E1E26]">
                    @include('mobile.components.icon-gift', ['class' => 'h-7 w-7'])
                </span>
                <div class="space-y-1">
                    <h3 class="font-display font-black text-lg text-[#3C2415]">
                        Redeem <span id="konfirmasi-tukar-stamp-desktop-nama"></span>?
                    </h3>
                    <p class="text-xs text-[#3C2415]/70">
                        Your 5 collected stamps will be exchanged for this artisan bakery treat.
                    </p>
                </div>

                <form method="POST" action="{{ route('member.stamps.redeem') }}" class="pt-2">
                    @csrf
                    <input type="hidden" name="produk_id" id="konfirmasi-tukar-stamp-desktop-produk-id">
                    <input type="hidden" name="detail_produk_id" id="konfirmasi-tukar-stamp-desktop-detail-id">
                    <input type="hidden" name="metode_ambil" value="aplikasi">

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <button type="button" onclick="closeSheet('konfirmasi-tukar-stamp-desktop')"
                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white py-3 text-xs font-bold text-[#3C2415] hover:bg-[#3C2415]/5 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-full rounded-2xl bg-[#8E1E26] py-3 text-xs font-bold text-white shadow-md hover:bg-[#72181E] transition cursor-pointer">
                            Confirm Redeem
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <script>
            function bukaKonfirmasiTukarStampDesktop(produkId, jumlah, namaProduk, detailId) {
                const hiddenInput = document.getElementById('konfirmasi-tukar-stamp-desktop-produk-id');
                const hiddenDetail = document.getElementById('konfirmasi-tukar-stamp-desktop-detail-id');
                const namaSpan = document.getElementById('konfirmasi-tukar-stamp-desktop-nama');
                if (hiddenInput) hiddenInput.value = produkId || '';
                if (hiddenDetail) hiddenDetail.value = detailId || '';
                if (namaSpan) namaSpan.textContent = (jumlah ? jumlah + 'x ' : '') + namaProduk;
                openSheet('konfirmasi-tukar-stamp-desktop');
            }
        </script>
    </div>

@endsection
