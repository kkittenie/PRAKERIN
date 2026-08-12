@extends('member.layout', ['title' => 'Checkout'])

@section('content')

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Sticky checkout header */
        .checkout-sticky-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Voucher bottom sheet */
        #modal-pilih-voucher {
            z-index: 100;
        }
    </style>

    <div x-data="{ pilihVoucherOpen: false }">
        <div
            class="checkout-sticky-header relative -mx-5 -mt-6 flex h-14 items-center justify-center border-b border-gray-100 px-5 sm:-mx-8 md:-mx-10">
            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
                <span class="rotate-180">
                    @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                </span>
            </a>

            <h1 class="text-sm font-bold text-gray-900">
                Checkout
            </h1>
        </div>

        <div class="mt-5 flex items-center gap-3 rounded-2xl bg-brand-50 p-3.5">

            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-800">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M4 10.5V20a1 1 0 0 0 1 1h4v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5h4a1 1 0 0 0 1-1v-9.5"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />

                    <path d="M2.5 10 12 3l9.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>

            </span>

            <div class="min-w-0 flex-1">

                <p class="text-[10px] font-bold uppercase tracking-wide text-brand-700">
                    Pick Up di
                </p>

                <p class="truncate text-xs font-bold text-gray-900">
                    {{ $storeNama }}
                </p>

                <p class="truncate text-[11px] text-gray-500">
                    {{ $storeAlamat }}
                </p>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- ORDER DETAILS --}}
        {{-- ========================================================= --}}
        <div class="mt-5">

            <h2 class="text-sm font-bold text-gray-900">
                Order Details
            </h2>

            <div class="mt-2 divide-y divide-dashed divide-gray-200">

                @foreach ($items as $item)

                    <div class="py-3 first:pt-2">

                        {{-- Nama produk + varian, foto di kanan --}}
                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0 flex-1">

                                <p class="text-xs font-bold leading-snug text-gray-900">
                                    {{ $item['nama_produk'] }}
                                </p>

                                @if ($item['ukuran'])

                                    <p class="mt-0.5 text-[11px] text-gray-400">
                                        {{ $item['ukuran'] }}
                                    </p>

                                @endif

                                @if ($item['catatan'])

                                    <p class="mt-0.5 truncate text-[11px] text-gray-400">
                                        "{{ $item['catatan'] }}"
                                    </p>

                                @endif

                            </div>

                            {{-- Product image --}}
                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-gray-50">

                                @if ($item['foto'])

                                    <img src="{{ asset('storage/' . $item['foto']) }}" alt="{{ $item['nama_produk'] }}"
                                        class="h-full w-full object-cover">

                                @else

                                    <div class="flex h-full w-full items-center justify-center text-brand-200">

                                        @include('member.components.icon-cup', [
                                            'class' => 'h-5 w-5'
                                        ])

                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- Harga, Change, & stepper jumlah --}}
                        <div class="mt-2 flex items-center justify-between gap-3">

                            <p class="text-sm font-extrabold text-gray-900">
                                Rp {{ number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') }}
                            </p>

                            <div class="flex items-center gap-2">

                                @php
                                    $urlUbahItem = $item['bundling_id']
                                        ? route('member.bundling.show', $item['bundling_id']) . '?edit=' . $item['id']
                                        : route('member.produk.show', $item['produk_id']) . '?edit=' . $item['id'];
                                @endphp

                                <a href="{{ $urlUbahItem }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                    class="text-[11px] font-bold text-brand-800 transition active:opacity-70">
                                    Change
                                </a>

                                <span class="h-4 border-l border-dashed border-gray-300"></span>

                                <div class="flex items-center gap-0.5 rounded-full border border-brand-800 p-0.5">

                                    <form method="POST"
                                        action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                        @csrf
                                        @method('PATCH')

                                        <input type="hidden" name="jumlah" value="{{ $item['jumlah'] - 1 }}">

                                        <button type="submit"
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-brand-800 transition active:scale-90">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12h14" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    </form>

                                    <span class="w-4 text-center text-[11px] font-bold text-gray-900">
                                        {{ $item['jumlah'] }}
                                    </span>

                                    <form method="POST"
                                        action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="jumlah" value="{{ $item['jumlah'] + 1 }}">
                                        <button type="submit"
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-brand-800 transition active:scale-90">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            {{-- Want to add more? --}}
            <div class="flex items-center justify-between gap-3 border-t border-dashed border-gray-200 pt-3">

                <div class="min-w-0">

                    <p class="text-xs font-bold text-gray-900">
                        Want to add more?
                    </p>

                    <p class="mt-0.5 text-[11px] text-gray-400">
                        Feel free to add more items in your order
                    </p>

                </div>

                <a href="{{ route('member.menu') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="shrink-0 rounded-full border border-brand-800 px-3.5 py-1.5 text-[11px] font-bold text-brand-800 transition active:scale-95">
                    Add More
                </a>

            </div>
        </div>

        <div class="mt-5">

            <p class="text-xs font-bold text-gray-900">
                Catatan untuk Pesanan (opsional)
            </p>


            <form id="form-checkout" method="POST" action="{{ route('member.checkout.store') }}">
                @csrf

                <textarea name="catatan" rows="2" maxlength="255" placeholder="Contoh: tolong pisah kemasan, dll."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- VOUCHER DISKON --}}
        {{-- ========================================================= --}}
        <div class="mt-5">

            <p class="text-xs font-bold text-gray-900">
                Voucher Diskon
            </p>

            @php
                $adaVoucherUnggulan = $voucherDipilih || $voucherTerbaik;
            @endphp

            <div class="mt-2 overflow-hidden rounded-2xl border border-gray-200">

                @if ($voucherDipilih)

                    {{-- Voucher sedang dipakai --}}
                    <div class="flex items-center justify-between gap-3 bg-brand-50 p-3.5">

                        <div class="flex min-w-0 items-center gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-700">
                                @include('member.components.icon-ticket', ['class' => 'h-4 w-4'])
                            </span>

                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-brand-800">
                                    {{ $voucherDipilih->reward->nama ?? 'Voucher' }}
                                </p>
                                <p class="mt-0.5 text-[11px] font-semibold text-brand-700">
                                    Hemat Rp {{ number_format($diskon, 0, ',', '.') }} 🎉
                                </p>
                            </div>

                        </div>

                        <button type="button" onclick="openSheet('modal-pilih-voucher')"
                            class="shrink-0 rounded-full border border-brand-800 bg-white px-4 py-1.5 text-[11px] font-bold text-brand-800 transition active:scale-95">
                            Ganti
                        </button>

                    </div>

                @elseif ($voucherTerbaik)

                    {{-- Voucher terbaik yang disarankan --}}
                    @php
                        $vtVoucher = $voucherTerbaik['voucher'];
                        $vtDiskon = $voucherTerbaik['diskon'];
                    @endphp

                    <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-brand-50 to-white p-3.5">

                        <div class="flex min-w-0 items-center gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-700">
                                @include('member.components.icon-ticket', ['class' => 'h-4 w-4'])
                            </span>

                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-gray-500">
                                    1 voucher terbaik untukmu
                                </p>
                                <p class="truncate text-xs font-bold text-brand-700">
                                    Hemat Rp {{ number_format($vtDiskon, 0, ',', '.') }} 🎉
                                </p>
                            </div>

                        </div>

                        <form method="POST" action="{{ route('member.checkout.voucher') }}">
                            @csrf
                            <input type="hidden" name="voucher_id" value="{{ $vtVoucher->id }}">
                            <button type="submit"
                                class="shrink-0 rounded-full border border-brand-800 px-5 py-1.5 text-[11px] font-bold text-brand-800 transition active:scale-95">
                                Pakai
                            </button>
                        </form>

                    </div>

                @endif

                {{-- Ini hanya ROW pemicu buka sheet daftar voucher --}}
                <button type="button" onclick="openSheet('modal-pilih-voucher')"
                    class="expandable-row flex w-full items-center justify-between px-4 py-3 text-left transition active:bg-gray-50
                    {{ $adaVoucherUnggulan ? 'border-t border-dashed border-gray-200' : '' }}">

                    <span class="text-[11px] font-semibold text-gray-600">
                        @if ($voucherAktif->count() > 0)
                            Lihat voucher lainnya ({{ $voucherAktif->count() }})
                        @else
                            Belum ada voucher aktif
                        @endif
                    </span>

                    <span class="row-chevron text-gray-300">
                        @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                    </span>

                </button>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- BOTTOM SHEET PILIH VOUCHER --}}
        {{-- ========================================================= --}}
        <dialog id="modal-pilih-voucher"
            class="sheet fixed inset-x-0 bottom-0 top-auto m-0 mx-auto max-h-[82vh] w-full max-w-md rounded-t-3xl border-0 p-0 shadow-2xl sm:max-w-xl md:max-w-2xl">

            <div class="flex max-h-[82vh] flex-col">

                {{-- Handle seperti halaman Voucher Detail --}}
                <div class="flex shrink-0 justify-center pb-1 pt-3">

                    <span class="h-1 w-10 rounded-full bg-gray-200"></span>

                </div>


                {{-- Sheet header --}}
                <div class="shrink-0 px-6 pt-3">

                    <h3 class="text-center text-sm font-bold text-brand-700">
                        Pilih Voucher
                    </h3>

                </div>


                {{-- Voucher list --}}
                <div class="overflow-y-auto px-6 pb-2 pt-4">

                    <form method="POST" action="{{ route('member.checkout.voucher') }}" class="space-y-2">
                        @csrf


                        {{-- Tanpa Voucher --}}
                        <button type="submit" name="voucher_id" value="" class="flex w-full items-center justify-between rounded-2xl border px-4 py-3.5 text-left transition active:bg-gray-50
                                    {{ !$voucherDipilih
        ? 'border-brand-800 bg-brand-50'
        : 'border-gray-200 bg-white'
                                    }}">

                            <div class="flex items-center gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                            {{ !$voucherDipilih
        ? 'bg-white text-brand-700'
        : 'bg-gray-50 text-gray-400'
                                            }}">

                                    @include('member.components.icon-ticket', [
                                        'class' => 'h-4 w-4'
                                    ])

                                </span>


                                <div>

                                    <span class="block text-xs font-bold text-gray-900">
                                        Tanpa Voucher
                                    </span>

                                    <span class="mt-0.5 block text-[11px] text-gray-400">
                                        Tidak menggunakan voucher
                                    </span>

                                </div>

                            </div>


                            @if (!$voucherDipilih)

                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-700 text-white">

                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                        <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                </span>

                            @endif

                        </button>


                        {{-- Voucher tersedia --}}
                        @forelse ($voucherAktif as $voucher)

                                        @php
                                            $reward = $voucher->reward;

                                            $potonganLabel = null;

                                            if ($reward) {

                                                if ($reward->jenis_potongan === 'nominal') {

                                                    $potonganLabel =
                                                        'Potongan Rp ' .
                                                        number_format(
                                                            $reward->nominal_potongan,
                                                            0,
                                                            ',',
                                                            '.'
                                                        );

                                                } else {

                                                    $potonganLabel =
                                                        'Diskon ' .
                                                        rtrim(
                                                            rtrim(
                                                                number_format(
                                                                    $reward->persentase,
                                                                    1,
                                                                    ',',
                                                                    '.'
                                                                ),
                                                                '0'
                                                            ),
                                                            ','
                                                        ) . '%';

                                                    if (!is_null($reward->maksimal_potongan)) {

                                                        $potonganLabel .=
                                                            ' (maks Rp ' .
                                                            number_format(
                                                                $reward->maksimal_potongan,
                                                                0,
                                                                ', ',
                                                                '. '
                                                            ) . ')';

                                                    }

                                                }

                                            }

                                            $minBelanja = (float) ($reward->min_pembelian ?? 0);
                                            $voucherTerpilih =
                                                $voucherDipilih &&
                                                $voucherDipilih->id === $voucher->id;
                                        @endphp


                                        <button type="submit" name="voucher_id" value="{{ $voucher->id }}" class="flex w-full items-center justify-between rounded-2xl border px-4 py-3.5 text-left transition active:bg-gray-50
                                                                                        {{ $voucherTerpilih
                            ? 'border-brand-800 bg-brand-50'
                            : 'border-gray-200 bg-white'
                                                                                        }}">

                                            <div class="flex min-w-0 items-start gap-3">

                                                {{-- Icon --}}
                                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                                                                                {{ $voucherTerpilih
                            ? 'bg-white text-brand-700'
                            : 'bg-brand-50 text-brand-700'
                                                                                                }}">

                                                    @include('member.components.icon-ticket', [
                                                        'class' => 'h-4 w-4'
                                                    ])

                                                </span>


                                                {{-- Detail --}}
                                                <span class="min-w-0">

                                                    <span class="block truncate text-xs font-bold text-gray-900">
                                                        {{ $reward->nama ?? 'Voucher' }}
                                                    </span>

                                                    <span class="mt-0.5 block text-[11px] text-gray-400">
                                                        Kode {{ $voucher->kode_redeem }}
                                                    </span>


                                                    @if ($potonganLabel)

                                                        <span class="mt-1 block text-[11px] font-semibold text-brand-700">
                                                            {{ $potonganLabel }}
                                                        </span>

                                                    @endif


                                                    @if ($minBelanja > 0)

                                                        <span class="block text-[11px] text-gray-400">
                                                            Min. belanja Rp
                                                            {{ number_format($minBelanja, 0, ',', '.') }}
                                                        </span>

                                                    @endif

                                                </span>

                                            </div>


                                            {{-- Check --}}
                                            @if ($voucherTerpilih)

                                                <span
                                                    class="ml-3 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white">

                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                        <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>

                                                </span>

                                            @endif

                                        </button>

                        @empty

                            <div class="py-8 text-center">

                                <span
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">

                                    @include('member.components.icon-ticket', [
                                        'class' => 'h-5 w-5'
                                    ])

                                </span>

                                <p class="mt-3 text-xs font-semibold text-gray-500">
                                    Belum ada voucher aktif
                                </p>

                                <p class="mt-1 text-[11px] text-gray-400">
                                    Voucher yang tersedia akan muncul di sini.
                                </p>

                            </div>

                        @endforelse

                    </form>

                </div>


                {{-- Tombol tutup --}}
                <div class="shrink-0 bg-white px-6 pb-6 pt-4">

                    <button type="button" onclick="closeSheet('modal-pilih-voucher')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-xs font-semibold text-white transition active:bg-brand-800">
                        Mengerti
                    </button>

                </div>

            </div>

        </dialog>


        {{-- ========================================================= --}}
        {{-- METODE PEMBAYARAN --}}
        {{-- ========================================================= --}}
        <div class="mt-5">

            <p class="text-xs font-bold text-gray-900">
                Metode Pembayaran
            </p>

            {{-- Row ini navigasi ke halaman baru (bukan sheet) --}}
            <a href="{{ route('member.checkout.metode-pembayaran.pilih') }}"
                onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                class="mt-2 flex w-full items-center justify-between rounded-2xl border border-gray-200 px-4 py-3 text-left transition active:bg-gray-50">

                <span class="flex items-center gap-2.5 text-xs font-semibold text-gray-700">

                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700">

                        @if ($metodeDipilih === 'QRIS')

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                <path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 17v4" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                            </svg>

                        @else

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <rect x="2" y="6" width="20" height="13" rx="2" stroke="currentColor" stroke-width="1.8" />
                                <path d="M2 10h20" stroke="currentColor" stroke-width="1.8" />
                                <path d="M6 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>

                        @endif

                    </span>

                    {{ $metodeDipilih }}

                </span>

                <span class="text-gray-300">
                    @include('member.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                </span>

            </a>

            <p class="mt-2 text-[10px] text-gray-400">
                Pembayaran tetap dilakukan di kasir saat kamu mengambil pesanan —
                ini cuma preferensi buat ditunjukkan ke kasir.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- RINCIAN PEMBAYARAN --}}
        {{-- ========================================================= --}}
        <div class="mt-5">

            <h2 class="text-sm font-bold text-gray-900">
                Rincian Pembayaran
            </h2>

            <div class="mt-2 space-y-2">

                <div class="flex justify-between text-xs text-gray-500">

                    <span>
                        Subtotal
                    </span>

                    <span class="font-semibold text-gray-800">
                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                    </span>

                </div>


                @if ($diskon > 0)

                    <div class="flex justify-between text-xs text-gray-500">

                        <span>
                            Diskon Voucher
                        </span>

                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($diskon, 0, ',', '.') }}
                        </span>

                    </div>

                @endif


                @if ($pajak > 0)

                    <div class="flex justify-between text-xs text-gray-500">

                        <span>
                            Pajak
                            ({{ rtrim(rtrim(number_format($pajakPersen, 1, ',', '.'), '0'), ',') }}%)
                        </span>

                        <span class="font-semibold text-gray-800">
                            Rp {{ number_format($pajak, 0, ',', '.') }}
                        </span>

                    </div>

                @endif


                <div class="flex justify-between border-t border-dashed border-gray-200 pt-2 text-sm">

                    <span class="font-bold text-gray-900">
                        Total Pembayaran
                    </span>

                    <span class="font-extrabold text-gray-900">
                        Rp {{ number_format($totalBayar, 0, ',', '.') }}
                    </span>

                </div>

            </div>


            @if ($poinPotensial > 0)

                <div class="mt-3 flex items-center gap-2.5 rounded-2xl bg-amber-50 px-3.5 py-3">

                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-brand-700">
                        @include('member.components.icon-gift', [
                            'class' => 'h-3.5 w-3.5'
                        ])
                    </span>

                    <p class="text-[11px] font-semibold text-amber-800">
                        Kamu berpotensi dapat
                        <span class="font-extrabold text-brand-700">{{ $poinPotensial }} Fore Points</span>
                    </p>

                </div>

            @endif


            <div class="mt-4">

                <p class="text-xs font-bold text-gray-900">
                    Kebijakan Pembatalan
                </p>

                <p class="mt-1 text-[11px] leading-relaxed text-gray-400">
                    Pesanan tidak bisa dibatalkan atau diubah lagi setelah dibuat. Datang ke
                    kasir sesuai kode pickup untuk membayar dan mengambil pesanan kamu.
                </p>

            </div>

        </div>


        {{-- Extra space supaya konten tidak ketutup sticky bottom --}}
        <div class="h-28"></div>

    </div>

    <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-none
                       border-t border-gray-100 bg-white/95 px-5 py-4
                       backdrop-blur sm:px-8 sm:pb-6
                       md:px-10
                       2xl:max-w-6xl">

        <button type="submit" form="form-checkout"
            class="flex w-full items-center justify-center rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
            Order Now
        </button>

    </div>

@endsection