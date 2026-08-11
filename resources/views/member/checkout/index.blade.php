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

            <div class="flex items-center justify-between">

                <h2 class="text-sm font-bold text-gray-900">
                    Order Details
                </h2>

                <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="text-xs font-bold text-brand-800">
                    + Add More
                </a>

            </div>


            <div class="mt-3 divide-y divide-gray-100 rounded-2xl border border-gray-100">

                @foreach ($items as $item)

                    <div class="flex items-center gap-3 p-3">

                        {{-- Product image --}}
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-50">

                            @if ($item['foto'])

                                <img src="{{ asset('storage/' . $item['foto']) }}" alt="{{ $item['nama_produk'] }}"
                                    class="h-full w-full object-cover">

                            @else

                                <div class="flex h-full w-full items-center justify-center text-brand-200">

                                    @include('member.components.icon-cup', [
                                        'class' => 'h-6 w-6'
                                    ])

                                </div>

                            @endif

                        </div>


                        {{-- Product information --}}
                        <div class="min-w-0 flex-1">

                            <p class="truncate text-xs font-bold text-gray-900">
                                {{ $item['nama_produk'] }}
                            </p>

                            @if ($item['ukuran'])

                                <p class="text-[11px] text-gray-400">
                                    {{ $item['ukuran'] }}
                                </p>

                            @endif

                            @if ($item['catatan'])

                                <p class="truncate text-[11px] text-gray-400">
                                    "{{ $item['catatan'] }}"
                                </p>

                            @endif

                            <p class="mt-1 text-xs font-extrabold text-gray-900">
                                Rp {{ number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') }}
                            </p>

                        </div>


                        {{-- Quantity + Hapus --}}
                        <div class="flex shrink-0 flex-col items-end gap-2">

                            <form method="POST" action="{{ route('member.keranjang.hapus', $item['id']) }}"
                                onsubmit="return confirm('Hapus {{ addslashes($item['nama_produk']) }} dari keranjang?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex h-6 w-6 items-center justify-center rounded-full text-gray-400 transition active:scale-90 active:text-red-500"
                                    aria-label="Hapus dari keranjang">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 .8 12.2a1 1 0 0 0 1 .8h6.4a1 1 0 0 0 1-.8L18 7"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </form>

                            <div class="flex items-center gap-2">

                                <form method="POST" action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                    @csrf
                                    @method('PATCH')

                                    <input type="hidden" name="jumlah" value="{{ $item['jumlah'] - 1 }}">

                                    <button type="submit"
                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition active:scale-90">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </form>

                                <span class="w-4 text-center text-xs font-bold text-gray-900">
                                    {{ $item['jumlah'] }}
                                </span>

                                <form method="POST" action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="jumlah" value="{{ $item['jumlah'] + 1 }}">
                                    <button type="submit"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-800 text-white transition active:scale-90">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                                stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
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
        {{-- VOUCHER --}}
        {{-- ========================================================= --}}
        <div class="mt-5">

            <p class="text-xs font-bold text-gray-900">
                Voucher
            </p>


            {{-- Ini hanya ROW pemicunya --}}
            <button type="button" onclick="openSheet('modal-pilih-voucher')"
                class="expandable-row mt-2 flex w-full items-center justify-between rounded-2xl border border-gray-200 px-4 py-3 text-left transition active:bg-gray-50">

                <span class="flex items-center gap-2 text-xs font-semibold text-gray-700">

                    @include('member.components.icon-ticket', [
                        'class' => 'h-4 w-4 text-brand-700'
                    ])

                    @if ($voucherDipilih)

                        {{ $voucherDipilih->reward->nama ?? 'Voucher' }}

                    @else

                        Pakai Voucher ({{ $voucherAktif->count() }} tersedia)

                    @endif

                </span>


                <span class="row-chevron text-gray-300">

                    @include('member.components.icon-chevron-right', [
                        'class' => 'h-3.5 w-3.5'
                    ])

                </span>

            </button>

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

        <div class="mt-5">

            <p class="text-xs font-bold text-gray-900">
                Metode Pembayaran
            </p>


            <form method="POST" action="{{ route('member.checkout.metode-pembayaran') }}"
                class="mt-2 grid grid-cols-2 gap-3">
                @csrf

                @foreach ($metodePembayaranList as $metode)

                        <button type="submit" name="metode_pembayaran" value="{{ $metode }}" class="flex items-center justify-center gap-2 rounded-2xl border px-4 py-3 text-xs font-bold transition
                                                        {{
                    $metodeDipilih === $metode
                    ? 'border-brand-800 bg-brand-50 text-brand-800'
                    : 'border-gray-200 text-gray-600 active:bg-gray-50'
                                                        }}">

                            @if ($metode === 'QRIS')

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

                            {{ $metode }}

                        </button>

                @endforeach

            </form>


            <p class="mt-2 text-[10px] text-gray-400">
                Pembayaran tetap dilakukan di kasir saat kamu mengambil pesanan —
                ini cuma preferensi buat ditunjukkan ke kasir.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- RINGKASAN PEMBAYARAN --}}
        {{-- ========================================================= --}}
        <div class="mt-5 space-y-2 rounded-2xl border border-dashed border-gray-200 p-4">

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


            <div class="mt-1 flex justify-between border-t border-dashed border-gray-200 pt-2 text-sm">

                <span class="font-bold text-gray-900">
                    Total Pembayaran
                </span>

                <span class="font-extrabold text-gray-900">
                    Rp {{ number_format($totalBayar, 0, ',', '.') }}
                </span>

            </div>


            @if ($poinPotensial > 0)

                <p class="!mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-brand-700">

                    @include('member.components.icon-gift', [
                        'class' => 'h-3.5 w-3.5'
                    ])

                    Kamu berpotensi dapat
                    {{ $poinPotensial }}
                    Fore Points

                </p>

            @endif


            <p class="!mt-2 text-[10px] text-gray-400">
                Pembayaran dilakukan di kasir saat kamu mengambil pesanan.
            </p>

        </div>


        {{-- Extra space supaya konten tidak ketutup sticky bottom --}}
        <div class="h-28"></div>

    </div>

    <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-none
                       border-t border-gray-100 bg-white/95 px-5 py-4
                       backdrop-blur sm:max-w-2xl sm:px-8 sm:pb-6
                       md:max-w-4xl md:px-10
                       lg:max-w-5xl
                       xl:max-w-6xl">

        <button type="submit" form="form-checkout"
            class="flex w-full items-center justify-between rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">

            <span>
                Order Now
            </span>

            <span>
                Rp {{ number_format($totalBayar, 0, ',', '.') }}
            </span>

        </button>

    </div>

@endsection