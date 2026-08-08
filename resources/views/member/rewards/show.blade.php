@extends('member.layout', ['title' => 'Voucher Detail'])

@php
    $cukupPoin = $member->poin >= $reward->poin_dibutuhkan;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $bisaDitukar = $cukupPoin && ! $habis;
    $kurang = max(0, $reward->poin_dibutuhkan - $member->poin);
    $isVoucher = $reward->jenis !== 'produk';
    $isCroissant = str_contains(strtolower($reward->nama ?? ''), 'croissant');
    $icon = $isVoucher ? 'icon-ticket' : ($isCroissant ? 'icon-cake' : 'icon-cup');

    // These three read from optional columns on the reward if present, and fall back
    // to sensible generic copy otherwise. Add `syarat_ketentuan`, `cara_pakai`,
    // `menu_berlaku` (text) columns on `rewards` to make them fully dynamic per reward.
    $syaratKetentuan = collect(preg_split('/\r\n|\r|\n/', trim((string) ($reward->syarat_ketentuan ?? ''))))->filter()->values();
    $caraPakai = collect(preg_split('/\r\n|\r|\n/', trim((string) ($reward->cara_pakai ?? ''))))->filter()->values();
    $menuBerlaku = trim((string) ($reward->menu_berlaku ?? ''));
@endphp

@section('content')

    {{-- App-bar style header: back button on the left, title centered --}}
    <div class="relative -mx-5 sm:-mx-8 md:-mx-10 -mt-6 flex items-center justify-center border-b border-gray-100 px-5 py-4">
        <a href="{{ route('member.reward.index') }}"
           onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
           class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
            <span class="rotate-180">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
        </a>
        <h1 class="text-theme-sm font-bold text-gray-900">Voucher Detail</h1>
    </div>

    {{-- Hero image: uses the reward image uploaded from the admin panel. --}}
    <div class="relative -mx-5 sm:-mx-8 md:-mx-10 h-36 overflow-hidden bg-gradient-to-br {{ $isVoucher ? 'from-brand-100 to-brand-200' : 'from-orange-100 to-orange-200' }} md:h-48">
        @if ($reward->gambar)
            <img
                src="{{ asset('storage/' . $reward->gambar) }}"
                alt="{{ $reward->nama }}"
                class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/10"></div>
        @else
            <span class="absolute -left-6 -top-6 h-28 w-28 rounded-full bg-white/40"></span>
            <span class="absolute -right-8 top-8 h-20 w-20 rounded-full bg-white/30"></span>
            <span class="absolute bottom-0 right-10 h-16 w-16 rounded-full bg-white/25"></span>

            <span class="relative mx-auto flex h-full w-full items-center justify-center">
                <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/70 shadow-sm {{ $isVoucher ? 'text-brand-700' : 'text-orange-600' }}">
                    @include('member.components.'.$icon, ['class' => 'h-9 w-9'])
                </span>
            </span>
        @endif
    </div>

    {{-- Title & description --}}
    <div class="pt-5 md:grid md:grid-cols-2 md:gap-8 md:items-start">
        <div>
        <h2 class="text-theme-lg font-bold leading-tight text-gray-900">{{ $reward->nama }}</h2>
        <p class="mt-2 text-theme-sm leading-relaxed text-gray-500">
            {{ $reward->deskripsi ?: ($isVoucher ? 'Voucher diskon spesial dari Saisho Cake & Bake.' : 'Tukar poin kamu dengan produk gratis ini.') }}
        </p>

        <div class="mt-4 border-t border-dashed border-gray-200"></div>

        {{-- Valid Until / Min Transaction --}}
        <div class="mt-3 grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100">
            <div class="px-4 py-3">
                <p class="text-theme-xs text-gray-400">Valid Until</p>
                <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                    {{ $reward->berlaku_hingga ? \Illuminate\Support\Carbon::parse($reward->berlaku_hingga)->translatedFormat('d M Y') : 'Selama stok ada' }}
                </p>
            </div>
            <div class="px-4 py-3">
                <p class="text-theme-xs text-gray-400">Min Transaction</p>
                <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                    {{ $reward->minimal_transaksi ? 'Rp'.number_format($reward->minimal_transaksi, 0, ',', '.') : '–' }}
                </p>
            </div>
        </div>

        {{-- Poin kamu vs dibutuhkan — kept from the original page since redeeming here
             spends poin, unlike Fore's checkout-coupon flow --}}
        <div class="mt-3 flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-4 py-3">
            <div>
                <p class="text-theme-xs text-gray-400">Dibutuhkan</p>
                <p class="text-theme-sm font-bold text-gray-800">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin</p>
            </div>
            <div class="text-right">
                <p class="text-theme-xs text-gray-400">Poin kamu</p>
                <p class="text-theme-sm font-bold {{ $cukupPoin ? 'text-success-600' : 'text-error-500' }}">{{ number_format($member->poin, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Expandable rows, each opens a bottom sheet like the Terms & Conditions example --}}
        <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white">
            <button type="button" data-sheet-target="sheet-menu-berlaku" onclick="openExpandableRow(this, 'sheet-menu-berlaku')"
                    class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                <span class="text-theme-sm font-bold text-gray-800">Menu yang Berlaku</span>
                <span class="row-chevron">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
            </button>
            <button type="button" data-sheet-target="sheet-syarat-ketentuan" onclick="openExpandableRow(this, 'sheet-syarat-ketentuan')"
                    class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                <span class="text-theme-sm font-bold text-gray-800">Syarat & Ketentuan</span>
                <span class="row-chevron">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
            </button>
            <button type="button" data-sheet-target="sheet-cara-pakai" onclick="openExpandableRow(this, 'sheet-cara-pakai')"
                    class="expandable-row flex w-full items-center justify-between px-4 py-3.5 text-left">
                <span class="text-theme-sm font-bold text-gray-800">Cara Pakai</span>
                <span class="row-chevron">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])</span>
            </button>
        </div>
    </div>

    {{-- Redeem button — same poin-based logic as before, just styled as a pill --}}
    <div class="sticky bottom-20 mt-5">
        @if ($habis)
            <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Stok sudah habis</button>
        @elseif (! $cukupPoin)
            <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">Kurang {{ number_format($kurang, 0, ',', '.') }} poin lagi</button>
        @else
            <button type="button" onclick="openSheet('konfirmasi-tukar')"
                    class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-800">
                Tukar Sekarang
            </button>
        @endif
    </div>

    {{-- Bottom sheet: Menu yang Berlaku --}}
    <dialog id="sheet-menu-berlaku" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Menu yang Berlaku</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    @if ($menuBerlaku)
                        <p>{{ $menuBerlaku }}</p>
                    @else
                        <p>Berlaku untuk semua menu di outlet Saisho Cake & Bake, selama stok produk masih tersedia.</p>
                    @endif
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-menu-berlaku')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom sheet: Syarat & Ketentuan (matches the Terms & Conditions example) --}}
    <dialog id="sheet-syarat-ketentuan" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Syarat & Ketentuan</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    @if ($syaratKetentuan->isNotEmpty())
                        <ol class="list-decimal space-y-2 pl-4">
                            @foreach ($syaratKetentuan as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ol class="list-decimal space-y-2 pl-4">
                            <li>Reward ini hanya berlaku untuk penukaran melalui aplikasi Saisho Cake & Bake.</li>
                            <li>Reward ini hanya dapat digunakan di outlet Saisho Cake & Bake yang berlaku.</li>
                            <li>Reward ini berlaku untuk jangka waktu yang ditentukan, selama poin dan stok masih tersedia.</li>
                            <li>Reward ini tidak dapat digabung dengan promo atau reward lain.</li>
                            <li>Poin yang sudah dipotong untuk penukaran tidak dapat dikembalikan atau dibatalkan.</li>
                            <li>Kami dapat mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan terlebih dahulu.</li>
                        </ol>
                    @endif
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-syarat-ketentuan')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom sheet: Cara Pakai --}}
    <dialog id="sheet-cara-pakai" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Cara Pakai</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    @if ($caraPakai->isNotEmpty())
                        <ol class="list-decimal space-y-2 pl-4">
                            @foreach ($caraPakai as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ol class="list-decimal space-y-2 pl-4">
                            <li>Tekan tombol "Tukar Sekarang" lalu konfirmasi penukaran reward.</li>
                            <li>Kode redeem akan muncul di halaman Riwayat setelah penukaran berhasil.</li>
                            <li>Tunjukkan kode redeem tersebut ke kasir saat kamu checkout di outlet.</li>
                            <li>Kasir akan memverifikasi dan memproses reward kamu langsung di kasir.</li>
                        </ol>
                    @endif
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

    {{-- Confirmation dialog before spending poin — bottom sheet, same as the
         expandable rows above (Menu yang Berlaku, Syarat & Ketentuan, Cara Pakai) --}}
    <dialog id="konfirmasi-tukar" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
        <div class="flex flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="px-6 pb-2 pt-3 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                    @include('member.components.icon-gift', ['class' => 'h-6 w-6'])
                </span>
                <p class="mt-3 text-theme-md font-semibold text-gray-800">Tukar {{ $reward->nama }}?</p>
                <p class="mt-1 text-theme-sm text-gray-500">{{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} poin akan dipotong dari saldo kamu dan tidak bisa dibatalkan.</p>
            </div>

            <form method="POST" action="{{ route('member.reward.tukar', $reward) }}" class="shrink-0 space-y-2 px-6 pb-6 pt-4">
                @csrf
                <button type="submit" class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">Ya, Tukar Sekarang</button>
                <button type="button" onclick="closeSheet('konfirmasi-tukar')" class="w-full rounded-full py-3 text-theme-sm font-medium text-gray-500">Batal</button>
            </form>
        </div>
    </dialog>

@endsection