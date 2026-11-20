@extends('mobile.layout', ['title' => 'Checkout'])

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

    <div x-data="{
        pilihVoucherOpen: false,
        modalHapusTerbuka: false,
        itemHapusId: null,
        itemHapusNama: '',
        konfirmasiHapus(id, nama) {
            this.itemHapusId = id;
            this.itemHapusNama = nama;
            this.modalHapusTerbuka = true;
        },
        eksekusiHapus() {
            if (this.itemHapusId) {
                const form = document.getElementById('form-hapus-item-' + this.itemHapusId);
                if (form) form.submit();
            }
            this.modalHapusTerbuka = false;
        }
    }">
        <div
            class="checkout-sticky-header relative -mx-5 -mt-6 flex h-14 items-center justify-center border-b border-gray-100 px-5 sm:-mx-8 md:-mx-10 lg:-mx-12 lg:hidden">
            <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100 lg:hidden">
                <span class="rotate-180">
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                </span>
            </a>

            <h1 class="text-sm font-bold text-gray-900">
                Checkout
            </h1>
        </div>

        <div class="lg:hidden lg:grid lg:grid-cols-12 lg:gap-8 lg:mt-6 lg:items-start">

            <div class="lg:col-span-7 space-y-5">

                <div class="mt-5 flex items-center gap-3 rounded-2xl bg-brand-50 p-3.5 lg:mt-0">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-800 shadow-xs">
                        @include('mobile.components.icon-home', ['class' => 'h-5 w-5'])
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

                {{-- Peringatan stok: stok bisa habis di antara saat item dimasukkan ke
                     keranjang dan saat checkout, jadi dicek ulang tiap halaman ini dibuka. --}}
                @if (! empty($peringatanStok))
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-3.5">
                        <p class="text-xs font-bold text-red-700">Stok ada yang berubah</p>
                        <ul class="mt-1.5 list-disc space-y-0.5 pl-4 text-[11px] text-red-700">
                            @foreach ($peringatanStok as $pesanStok)
                                <li>{{ $pesanStok }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-1.5 text-[11px] text-red-600">
                            Ubah jumlah atau hapus item tersebut dari keranjang, lalu checkout lagi.
                        </p>
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- ORDER DETAILS --}}
                {{-- ========================================================= --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-900">
                        Order Details
                    </h2>

            <div class="mt-2 divide-y divide-dashed divide-gray-200">

                @foreach ($items as $item)

                    <div class="py-3 first:pt-2">

                        {{-- Nama produk + varian, foto di kanan --}}
                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0 flex-1">

                                @if (!empty($item['is_reward_stamp']))
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-extrabold text-amber-800">
                                            🎁 Hadiah Stamp (Gratis)
                                        </span>
                                    </div>
                                @endif

                                <p class="text-xs font-bold leading-snug text-gray-900">
                                    {{ $item['nama_produk'] }}
                                </p>

                                @if (!empty($item['bundling_id']))
                                    @php
                                        $rincianBundling = $item['rincian_bundling'] ?? null;
                                        if (!$rincianBundling) {
                                            $bObj = \App\Models\Bundling::with(['items.produk.detailProduks', 'items.detailProduk'])->find($item['bundling_id']);
                                            if ($bObj) {
                                                if ($bObj->isBebas() && !empty($item['pilihan_khusus'])) {
                                                    // Satu baris per rasa yang dipilih pembeli (produk + ukuran + rasa).
                                                    $rincianBundling = [];
                                                    foreach ($bObj->barisIsi($item['pilihan_khusus']) as $baris) {
                                                        $bi = $baris['item'];
                                                        $rincianBundling[] = [
                                                            'nama_produk' => $bi->produk?->nama_produk ?? 'Produk',
                                                            'ukuran' => $bi->ukuran,
                                                            'varian_rasa' => $bi->varian_rasa,
                                                            'qty' => (int) $baris['qty'],
                                                            'catatan' => $baris['catatan'],
                                                        ];
                                                    }
                                                } else {
                                                    $rincianBundling = $bObj->items->map(function($bi) {
                                                        $dp = $bi->detailProduk ?? $bi->produk?->detailProduks?->first();
                                                        return [
                                                            'nama_produk' => $bi->produk?->nama_produk ?? 'Produk',
                                                            'ukuran' => $dp?->ukuran ?: $bi->ukuran,
                                                            'varian_rasa' => $dp?->variasi ?: $bi->varian_rasa,
                                                            'qty' => (int) $bi->qty,
                                                        ];
                                                    })->all();
                                                }
                                            }
                                        }
                                    @endphp
                                    @if (!empty($rincianBundling))
                                        <div class="mt-1 space-y-0.5 rounded-lg bg-gray-50/80 p-2 border border-gray-100">
                                            <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wider">Isi Paket:</p>
                                            @foreach ($rincianBundling as $rb)
                                                <div class="text-[11px] text-gray-600 flex items-center justify-between gap-2">
                                                    <span class="font-medium text-gray-800">
                                                        {{ $rb['qty'] }}x {{ $rb['nama_produk'] }}
                                                        @php
                                                            $tags = array_filter([$rb['ukuran'] ?? null, $rb['varian_rasa'] ?? null]);
                                                        @endphp
                                                        @if (!empty($tags))
                                                            <span class="text-[10px] text-amber-700 font-semibold">({{ implode(' - ', $tags) }})</span>
                                                        @endif
                                                    </span>
                                                    @if (!empty($rb['catatan']))
                                                        <span class="text-[10px] text-gray-400 italic">"{{ $rb['catatan'] }}"</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                @if (!empty($item['ukuran']) || !empty($item['varian_rasa']))
                                    <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                        @if (!empty($item['ukuran']))
                                            <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-800">
                                                {{ $item['ukuran'] }}
                                            </span>
                                        @endif
                                        @if (!empty($item['varian_rasa']))
                                            <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">
                                                Rasa: {{ $item['varian_rasa'] }}
                                            </span>
                                        @endif
                                    </div>
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

                                        @include('mobile.components.icon-cup', [
                                            'class' => 'h-5 w-5'
                                        ])

                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- Harga, Change, & stepper jumlah --}}
                        <div class="mt-2 flex items-center justify-between gap-3">

                            <div>
                                @php
                                    $itemDiskonSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);
                                    $itemHargaAsal   = (float) ($item['harga_asal'] ?? $item['harga_satuan']);
                                    $itemAdaDiskon   = $itemDiskonSatuan > 0;
                                @endphp

                                @if ($itemAdaDiskon)
                                    {{-- Promo badge: nama promo + persen/nominal, sama seperti tampilan promo produk --}}
                                    @php
                                        $badgeLabel = $item['nama_promo'] ?? 'Promo';
                                        if (($item['jenis_promo'] ?? null) === 'persentase' && ($item['persentase_promo'] ?? null)) {
                                            $pFormatted = rtrim(rtrim(number_format((float) $item['persentase_promo'], 2, ',', '.'), '0'), ',');
                                            $badgeLabel .= ' (' . $pFormatted . '%)';
                                        }
                                    @endphp
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-extrabold bg-rose-100 text-rose-600">
                                            {{ $badgeLabel }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 line-through">
                                            Rp {{ number_format($itemHargaAsal * $item['jumlah'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif

                                @if (!empty($item['is_reward_stamp']))
                                    <p class="text-sm font-extrabold text-emerald-600">
                                        Rp 0
                                    </p>
                                @else
                                    <p class="text-sm font-extrabold text-gray-900">
                                        Rp {{ number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">

                                @if (!empty($item['is_reward_stamp']))
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                                        {{ $item['jumlah'] }}x
                                    </span>
                                    <form id="form-hapus-item-{{ $item['id'] }}" method="POST" action="{{ route('member.keranjang.hapus', $item['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="konfirmasiHapus('{{ $item['id'] }}', 'Hadiah Stamp: {{ addslashes($item['nama_produk']) }}')" class="flex h-7 w-7 items-center justify-center rounded-full text-red-500 hover:bg-red-50 transition active:scale-90" title="Hapus Hadiah">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    @php
                                        if (!empty($item['bundling_id'])) {
                                            $urlUbahItem = route('member.bundling.show', $item['bundling_id']) . '?edit=' . $item['id'];
                                        } elseif (!empty($item['promo_event_id'])) {
                                            $urlUbahItem = route('member.promo-event.show', $item['promo_event_id']) . '?edit=' . $item['id'];
                                        } else {
                                            $urlUbahItem = route('member.produk.show', $item['produk_id']) . '?edit=' . $item['id'];
                                        }
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

                                    <form id="form-hapus-item-{{ $item['id'] }}" method="POST" action="{{ route('member.keranjang.hapus', $item['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="konfirmasiHapus('{{ $item['id'] }}', '{{ addslashes($item['nama_produk']) }}')"
                                            class="flex h-7 w-7 items-center justify-center rounded-full text-red-500 hover:bg-red-50 transition active:scale-90"
                                            title="Hapus dari keranjang">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                            </div>

                        </div>

                    </div>

                @endforeach

                @php
                    $rewardSudahDiItems = collect($items)->contains(fn ($it) => !empty($it['is_reward_stamp']));
                @endphp
                @if (!empty($stampRedemptionDipilih) && $stampRedemptionDipilih->produk && ! $rewardSudahDiItems)
                    <div class="flex items-center gap-3 border-t border-dashed border-amber-200/80 bg-amber-50/50 -mx-4 -mb-4 p-4 rounded-b-2xl">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white border border-amber-200 shadow-xs">
                            @if ($stampRedemptionDipilih->produk->foto)
                                <img src="{{ asset('storage/' . $stampRedemptionDipilih->produk->foto) }}" alt="{{ $stampRedemptionDipilih->produk->nama_produk }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-xl">🎁</span>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-extrabold bg-amber-200 text-amber-900">
                                    Gratis · Reward Stamp
                                </span>
                            </div>
                            <p class="truncate text-xs font-bold text-gray-900">
                                {{ $stampRedemptionDipilih->jumlah ?: 1 }}x {{ $stampRedemptionDipilih->nama_tampil }}
                            </p>
                            <p class="text-xs font-extrabold text-emerald-600">
                                Rp 0
                            </p>
                        </div>
                    </div>
                @endif

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

        </div>{{-- end lg:col-span-7 --}}

        <div class="lg:col-span-5 lg:sticky lg:top-24">

        {{-- ========================================================= --}}
        {{-- VOUCHER DISKON --}}
        {{-- ========================================================= --}}
        <div class="mt-5 lg:mt-0">

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
                                @include('mobile.components.icon-ticket', ['class' => 'h-4 w-4'])
                            </span>

                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-brand-800">
                                    {{ $voucherDipilih->nama ?? 'Voucher' }}
                                </p>
                                @if ($diskonVoucher > 0)
                                    <p class="mt-0.5 text-[11px] font-semibold text-brand-700">
                                        Hemat Rp {{ number_format($diskonVoucher, 0, ',', '.') }} 🎉
                                    </p>
                                @else
                                    <p class="mt-0.5 text-[11px] font-semibold text-amber-700">
                                        Syarat belum terpenuhi
                                    </p>
                                @endif
                            </div>

                        </div>

                        <div class="flex items-center gap-1.5">
                            <form method="POST" action="{{ route('member.checkout.voucher') }}">
                                @csrf
                                <input type="hidden" name="voucher_id" value="">
                                <button type="submit" class="shrink-0 rounded-full border border-gray-300 bg-white px-2.5 py-1.5 text-[10px] font-bold text-gray-500 transition active:scale-95">
                                    Hapus
                                </button>
                            </form>
                            <button type="button" onclick="openSheet('modal-pilih-voucher')"
                                class="shrink-0 rounded-full border border-brand-800 bg-white px-3.5 py-1.5 text-[11px] font-bold text-brand-800 transition active:scale-95">
                                Ganti
                            </button>
                        </div>

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
                                @include('mobile.components.icon-ticket', ['class' => 'h-4 w-4'])
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
                        @include('mobile.components.icon-chevron-right', ['class' => 'h-3.5 w-3.5'])
                    </span>

                </button>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- HADIAH STAMP (BISA DIAMBIL SAAT CHECKOUT) --}}
        {{-- ========================================================= --}}
        @if (!empty($stampRedemptionsAktif) && $stampRedemptionsAktif->isNotEmpty())
            <div class="mt-4">
                <p class="text-xs font-bold text-gray-900">
                    Hadiah Stamp
                </p>

                <div class="mt-2 overflow-hidden rounded-2xl border border-amber-200/90 bg-amber-50/40 shadow-xs">
                    @if ($stampRedemptionDipilih)
                        <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-amber-50 to-orange-50/30 p-3.5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-brand-800 shadow-xs">
                                    🎁
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-gray-900">
                                        {{ $stampRedemptionDipilih->jumlah ?: 1 }}x {{ $stampRedemptionDipilih->produk?->nama_produk ?? 'Produk Hadiah' }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-emerald-700">
                                        Gratis dari Stamp 🎉
                                    </p>
                                </div>
                            </div>
                            <button type="button" onclick="openSheet('modal-pilih-reward-stamp')"
                                class="shrink-0 rounded-full border border-brand-800 bg-white px-3.5 py-1.5 text-[11px] font-bold text-brand-800 transition active:scale-95 cursor-pointer">
                                Ganti
                            </button>
                        </div>
                    @else
                        <button type="button" onclick="openSheet('modal-pilih-reward-stamp')"
                            class="flex w-full items-center justify-between gap-3 p-3.5 text-left transition hover:bg-amber-100/40 active:bg-amber-100/60 cursor-pointer">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800">
                                    🎁
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900">
                                        {{ $stampRedemptionsAktif->count() }} Hadiah Stamp Siap Diambil
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-gray-500">
                                        Gunakan gratis pada pesanan ini
                                    </p>
                                </div>
                            </div>
                            <span class="rounded-full bg-brand-800 px-3 py-1 text-[11px] font-bold text-white shadow-xs">
                                Pakai
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        @endif


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

                                    @include('mobile.components.icon-ticket', [
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
                                $reward = $voucher;
                                $potonganLabel = null;

                                if ($reward) {
                                    if ($reward->jenis_potongan === 'nominal') {
                                        $potonganLabel = 'Potongan Rp ' . number_format($reward->nominal_potongan, 0, ',', '.');
                                    } else {
                                        $potonganLabel = 'Diskon ' . rtrim(rtrim(number_format($reward->persentase, 1, ',', '.'), '0'), ',') . '%';
                                        if (!is_null($reward->maksimal_potongan)) {
                                            $potonganLabel .= ' (maks Rp ' . number_format($reward->maksimal_potongan, 0, ',', '.') . ')';
                                        }
                                    }
                                }

                                $minBelanja = (float) ($reward->min_pembelian ?? 0);
                                $voucherTerpilih = $voucherDipilih && $voucherDipilih->id === $voucher->id;
                                $sisaPakai = max(0, ($voucher->batas_penggunaan_per_member ?: 1) - $voucher->jumlahPemakaianMember($member->id));
                                $kelayakan = $voucher->kelayakan ?? ['layak' => false, 'pesan' => 'Syarat belum terpenuhi', 'diskon' => 0];
                                $bisaDipakai = (bool) ($kelayakan['layak'] ?? false);
                            @endphp

                            @if ($bisaDipakai)
                                {{-- Voucher memenuhi syarat: BISA DIPILIH --}}
                                <button type="submit" name="voucher_id" value="{{ $voucher->id }}"
                                    class="flex w-full items-start justify-between rounded-2xl border px-4 py-3.5 text-left transition active:bg-gray-50
                                    {{ $voucherTerpilih ? 'border-brand-800 bg-brand-50' : 'border-gray-200 bg-white hover:border-brand-200' }}">

                                    <div class="flex min-w-0 items-start gap-3">
                                        {{-- Icon --}}
                                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                            {{ $voucherTerpilih ? 'bg-white text-brand-700' : 'bg-brand-50 text-brand-700' }}">
                                            @include('mobile.components.icon-ticket', ['class' => 'h-4 w-4'])
                                        </span>

                                        {{-- Detail --}}
                                        <span class="min-w-0">
                                            <span class="block truncate text-xs font-bold text-gray-900">
                                                {{ $reward->nama ?? 'Voucher' }}
                                            </span>

                                            <span class="mt-0.5 block text-[11px] text-gray-400">
                                                Sisa: {{ $sisaPakai }}x · Berlaku s/d {{ $voucher->tanggal_selesai ? $voucher->tanggal_selesai->format('d/m/Y') : 'Seterusnya' }}
                                            </span>

                                            @if ($potonganLabel)
                                                <span class="mt-1 block text-[11px] font-semibold text-brand-700">
                                                    {{ $potonganLabel }}
                                                </span>
                                            @endif

                                            @if ($minBelanja > 0)
                                                <span class="block text-[11px] text-gray-400">
                                                    Min. belanja Rp {{ number_format($minBelanja, 0, ',', '.') }}
                                                </span>
                                            @endif

                                            @if (!empty($kelayakan['diskon']) && $kelayakan['diskon'] > 0)
                                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-emerald-50 border border-emerald-200/70 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                    Hemat Rp {{ number_format($kelayakan['diskon'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Check / Status --}}
                                    @if ($voucherTerpilih)
                                        <span class="ml-3 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="ml-3 shrink-0 rounded-full border border-brand-800 bg-white px-3 py-1 text-[11px] font-bold text-brand-800">
                                            Pakai
                                        </span>
                                    @endif

                                </button>
                            @else
                                {{-- Voucher BELUM memenuhi syarat: TIDAK BISA DIPILIH & ADA INFORMASI LENGKAP --}}
                                <div class="flex w-full items-start justify-between rounded-2xl border border-gray-200 bg-gray-50/80 p-4 text-left opacity-85">

                                    <div class="flex min-w-0 items-start gap-3">
                                        {{-- Icon --}}
                                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-200/80 text-gray-400">
                                            @include('mobile.components.icon-ticket', ['class' => 'h-4 w-4'])
                                        </span>

                                        {{-- Detail --}}
                                        <div class="min-w-0 flex-1">
                                            <span class="block truncate text-xs font-bold text-gray-700">
                                                {{ $reward->nama ?? 'Voucher' }}
                                            </span>

                                            <span class="mt-0.5 block text-[11px] text-gray-400">
                                                Sisa: {{ $sisaPakai }}x · Berlaku s/d {{ $voucher->tanggal_selesai ? $voucher->tanggal_selesai->format('d/m/Y') : 'Seterusnya' }}
                                            </span>

                                            @if ($potonganLabel)
                                                <span class="mt-1 block text-[11px] font-medium text-gray-500">
                                                    {{ $potonganLabel }}
                                                </span>
                                            @endif

                                            @if ($minBelanja > 0)
                                                <span class="block text-[11px] text-gray-400">
                                                    Min. belanja Rp {{ number_format($minBelanja, 0, ',', '.') }}
                                                </span>
                                            @endif

                                            {{-- Penjelasan detail kenapa tidak bisa digunakan --}}
                                            <div class="mt-2.5 flex items-start gap-1.5 rounded-xl border border-amber-200/80 bg-amber-50 p-2 text-[11px] text-amber-900 leading-tight">
                                                <svg class="h-3.5 w-3.5 shrink-0 mt-0.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                <span>{{ $kelayakan['pesan'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="ml-2 shrink-0 rounded-full bg-gray-200/90 px-2.5 py-1 text-[10px] font-semibold text-gray-500">
                                        Tidak Berlaku
                                    </span>

                                </div>
                            @endif

                        @empty

                            <div class="py-8 text-center">

                                <span
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">

                                    @include('mobile.components.icon-ticket', [
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
        {{-- BOTTOM SHEET PILIH HADIAH STAMP --}}
        {{-- ========================================================= --}}
        @if (!empty($stampRedemptionsAktif) && $stampRedemptionsAktif->isNotEmpty())
            <dialog id="modal-pilih-reward-stamp"
                class="sheet fixed inset-x-0 bottom-0 top-auto m-0 mx-auto max-h-[82vh] w-full max-w-md rounded-t-3xl border-0 p-0 shadow-2xl sm:max-w-xl md:max-w-2xl">
                <div class="flex max-h-[82vh] flex-col">
                    <div class="flex shrink-0 justify-center pb-1 pt-3">
                        <span class="h-1 w-10 rounded-full bg-gray-200"></span>
                    </div>

                    <div class="shrink-0 px-6 pt-3">
                        <h3 class="text-center text-sm font-bold text-brand-800">
                            Pilih Hadiah Stamp
                        </h3>
                        <p class="text-center text-[11px] text-gray-500 mt-0.5">
                            Pilih hadiah stamp untuk diambil gratis bersamaan pesanan pickup ini.
                        </p>
                    </div>

                    <div class="overflow-y-auto px-6 pb-4 pt-4">
                        <form method="POST" action="{{ route('member.checkout.reward-stamp') }}" class="space-y-2.5">
                            @csrf

                            {{-- Tanpa Hadiah Stamp --}}
                            <button type="submit" name="stamp_redemption_id" value=""
                                class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition active:bg-gray-50 cursor-pointer
                                    {{ !$stampRedemptionDipilih ? 'border-brand-800 bg-brand-50' : 'border-gray-200 bg-white' }}">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ !$stampRedemptionDipilih ? 'bg-white text-brand-700' : 'bg-gray-50 text-gray-400' }}">
                                        ✕
                                    </span>
                                    <div>
                                        <span class="block text-xs font-bold text-gray-900">Tanpa Hadiah Stamp</span>
                                        <span class="block text-[11px] text-gray-500">Ambil hadiah stamp di waktu lain</span>
                                    </div>
                                </div>
                                <span class="h-5 w-5 rounded-full border-2 flex items-center justify-center {{ !$stampRedemptionDipilih ? 'border-brand-800 bg-brand-800' : 'border-gray-300' }}">
                                    @if (!$stampRedemptionDipilih)
                                        <span class="h-2 w-2 rounded-full bg-white"></span>
                                    @endif
                                </span>
                            </button>

                            {{-- Daftar Hadiah Stamp --}}
                            @foreach ($stampRedemptionsAktif as $redemption)
                                @php
                                    $isDipilih = $stampRedemptionDipilih && $stampRedemptionDipilih->id === $redemption->id;
                                    $produkR = $redemption->produk;
                                @endphp
                                <button type="submit" name="stamp_redemption_id" value="{{ $redemption->id }}"
                                    class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition active:bg-gray-50 cursor-pointer
                                        {{ $isDipilih ? 'border-brand-800 bg-brand-50' : 'border-gray-200 bg-white' }}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl border border-amber-200/80">
                                            🎁
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-gray-900">
                                                {{ $redemption->jumlah ?: 1 }}x {{ $produkR?->nama_produk ?? 'Hadiah Stamp' }}
                                            </p>
                                            <p class="text-[11px] font-semibold text-emerald-700 mt-0.5">
                                                Gratis · Kode: {{ $redemption->kode_redeem }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="h-5 w-5 rounded-full border-2 flex items-center justify-center shrink-0 ml-2 {{ $isDipilih ? 'border-brand-800 bg-brand-800' : 'border-gray-300' }}">
                                        @if ($isDipilih)
                                            <span class="h-2 w-2 rounded-full bg-white"></span>
                                        @endif
                                    </span>
                                </button>
                            @endforeach
                        </form>
                    </div>

                    <div class="shrink-0 border-t border-gray-100 p-4">
                        <button type="button" onclick="closeSheet('modal-pilih-reward-stamp')"
                            class="w-full rounded-full bg-gray-100 py-3 text-xs font-semibold text-gray-700 hover:bg-gray-200 cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </dialog>
        @endif


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


                @foreach ($rincianDiskonProduk ?? [] as $itemRincian)

                    <div class="flex justify-between text-xs text-gray-500">

                        <span>
                            {{ $itemRincian['nama'] }}
                        </span>

                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($itemRincian['nilai'], 0, ',', '.') }}
                        </span>

                    </div>

                @endforeach


                @if (($diskonVoucher ?? 0) > 0)

                    <div class="flex justify-between text-xs text-gray-500">

                        <span>
                            {{ $voucherDipilih?->nama ?? 'Voucher' }}
                        </span>

                        <span class="font-semibold text-red-500">
                            - Rp {{ number_format($diskonVoucher, 0, ',', '.') }}
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
                        @include('mobile.components.icon-gift', [
                            'class' => 'h-3.5 w-3.5'
                        ])
                    </span>

                    <p class="text-[11px] font-semibold text-amber-800">
                        Kamu berpotensi dapat
                        <span class="font-extrabold text-brand-700">{{ $poinPotensial }} Saisho Points</span>
                    </p>

                </div>

            @endif


            <div class="mt-4">

                <p class="text-xs font-bold text-gray-900">
                    Kebijakan Pembatalan
                </p>

                <p class="mt-1 text-[11px] leading-relaxed text-gray-400">
                    Pesanan tidak bisa dibatalkan atau diubah lagi setelah dibuat. Upload bukti dan konfirmasi pembayaran maksimal {{ \App\Models\Transaksi::BATAS_BAYAR_MENIT }} menit setelah pesanan dibuat, kalau tidak pesanan dibatalkan otomatis dan stok dikembalikan. Datang ke kasir sesuai kode pickup untuk mengambil pesanan kamu.
                </p>

            </div>

            <div class="mt-6 hidden lg:block">
                <button type="submit" form="form-checkout"
                    @disabled(! empty($peringatanStok))
                    class="flex w-full items-center justify-center rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-900 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                    Order Now
                </button>
            </div>

        </div>

        <div class="h-28 lg:h-4"></div>

        </div>{{-- end lg:col-span-5 --}}

        </div>{{-- end lg:grid --}}

        <!-- MODAL KONFIRMASI HAPUS PRODUK KERANJANG (ALPINE) -->
        <div x-show="modalHapusTerbuka" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden border border-gray-100">
                <div class="p-5 text-center">
                    <div class="w-12 h-12 bg-red-50 text-red-500 flex items-center justify-center rounded-full mx-auto mb-3">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="font-extrabold text-gray-900 text-sm">Hapus dari Keranjang?</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Yakin ingin menghapus <span class="font-bold text-gray-800" x-text="itemHapusNama"></span> dari keranjang?
                    </p>
                </div>
                <div class="bg-gray-50/80 px-4 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalHapusTerbuka = false"
                        class="flex-1 py-2.5 px-3 bg-white hover:bg-gray-100 font-bold text-xs rounded-xl transition text-gray-700 border border-gray-200 active:scale-[0.98]">
                        Batal
                    </button>
                    <button type="button" @click="eksekusiHapus()"
                        class="flex-1 py-2.5 px-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition shadow-xs active:scale-[0.98]">
                        Hapus
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-none
                       border-t border-gray-100 bg-white/95 px-5 py-4
                       backdrop-blur sm:px-8 sm:pb-6
                       md:px-10
                       2xl:max-w-6xl
                       lg:hidden">

        <button type="submit" form="form-checkout"
            @disabled(! empty($peringatanStok))
            class="flex w-full items-center justify-center rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
            Order Now
        </button>

    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE CART & CHECKOUT
         Matches Daisy Bakery theme: Warm #FAF5E8 cream canvas,
         biscuit cards, display typography, and 2-column layout.
         ════════════════════════════════════════════════════════════════ --}}
    <div x-data="{
        modalHapusTerbukaDesktop: false,
        itemHapusIdDesktop: null,
        itemHapusNamaDesktop: '',
        konfirmasiHapusDesktop(id, nama) {
            this.itemHapusIdDesktop = id;
            this.itemHapusNamaDesktop = nama;
            this.modalHapusTerbukaDesktop = true;
        },
        eksekusiHapusDesktop() {
            if (this.itemHapusIdDesktop) {
                const form = document.getElementById('form-hapus-item-d' + this.itemHapusIdDesktop);
                if (form) form.submit();
            }
            this.modalHapusTerbukaDesktop = false;
        }
    }" class="hidden lg:block w-full bg-[#FAF5E8] py-10 px-8 lg:px-12 xl:px-16 min-h-screen text-[#3C2415]">
        {{-- Breadcrumb back link --}}
        <div class="max-w-6xl mx-auto mb-8">
            <a href="{{ route('member.menu') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#3C2415]/60 hover:text-[#8E1E26] transition-colors">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Back to Menu</span>
            </a>
        </div>

        {{-- Page Header --}}
        <div class="max-w-6xl mx-auto mb-10 pb-6 border-b border-[#3C2415]/10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-[#3C2415] tracking-tight leading-none">
                    YOUR SHOPPING CART
                </h1>
            </div>
            <p class="font-sans text-xs sm:text-sm text-[#7C6558] font-medium">
                {{ collect($items)->sum('jumlah') }} fresh bakes reserved for store pickup
            </p>
        </div>

        {{-- Stock warning if any --}}
        @if (! empty($peringatanStok))
            <div class="max-w-6xl mx-auto mb-8 rounded-[24px] border border-red-300 bg-red-50/90 p-5 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 font-bold">
                        ⚠️
                    </div>
                    <div class="flex-1">
                        <h4 class="font-display font-bold text-sm text-red-800">Some items in your cart are currently out of stock</h4>
                        <ul class="mt-1.5 list-disc pl-5 space-y-0.5 text-xs text-red-700">
                            @foreach ($peringatanStok as $pesanStok)
                                <li>{{ $pesanStok }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs text-red-600 font-medium">Please adjust quantities or remove out-of-stock items before placing your order.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main 2-Column Grid --}}
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-14 items-start">

            {{-- LEFT COLUMN: Items List, Add More, Notes (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">

                <div class="flex items-center justify-between pb-1">
                    <h2 class="font-display font-black text-sm uppercase tracking-wider text-[#3C2415]">
                        Order Items ({{ count($items) }})
                    </h2>
                </div>

                {{-- Cart Items Loop --}}
                <div class="space-y-4">
                    @foreach ($items as $item)
                        @php
                            $itemDiskonSatuan = (float) ($item['diskon_produk_satuan'] ?? 0);
                            $itemHargaAsal   = (float) ($item['harga_asal'] ?? $item['harga_satuan']);
                            $itemAdaDiskon   = $itemDiskonSatuan > 0;
                            if (!empty($item['bundling_id'])) {
                                $urlUbahItem = route('member.bundling.show', $item['bundling_id']) . '?edit=' . $item['id'];
                            } elseif (!empty($item['promo_event_id'])) {
                                $urlUbahItem = route('member.promo-event.show', $item['promo_event_id']) . '?edit=' . $item['id'];
                            } else {
                                $urlUbahItem = route('member.produk.show', $item['produk_id']) . '?edit=' . $item['id'];
                            }
                        @endphp

                        <div class="rounded-[28px] bg-[#F5ECE3] border border-[#3C2415]/10 p-6 transition-all duration-200 hover:shadow-sm">
                            <div class="flex items-start gap-5">
                                {{-- Thumbnail --}}
                                <div class="h-24 w-24 shrink-0 rounded-2xl overflow-hidden bg-white/70 border border-[#3C2415]/10 flex items-center justify-center relative shadow-xs">
                                    @if ($item['foto'])
                                        <img src="{{ asset('storage/' . $item['foto']) }}" alt="{{ $item['nama_produk'] }}"
                                             class="h-full w-full object-cover select-none pointer-events-none">
                                    @else
                                        <div class="text-[#3C2415]/20">
                                            @include('mobile.components.icon-cup', ['class' => 'h-10 w-10'])
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            @if (!empty($item['is_reward_stamp']))
                                                <div class="mb-1">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-2.5 py-0.5 text-[10px] font-black text-amber-900">
                                                        🎁 Free Stamp Reward
                                                    </span>
                                                </div>
                                            @endif

                                            <h3 class="font-display font-black text-lg text-[#3C2415] leading-snug">
                                                {{ $item['nama_produk'] }}
                                            </h3>

                                            {{-- Variants --}}
                                            @if (!empty($item['ukuran']) || !empty($item['varian_rasa']))
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if (!empty($item['ukuran']))
                                                        <span class="rounded-md bg-[#FAF5E8] border border-[#3C2415]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#3C2415]">
                                                            Size: {{ $item['ukuran'] }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($item['varian_rasa']))
                                                        <span class="rounded-md bg-[#FAF5E8] border border-[#3C2415]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#8E1E26]">
                                                            Flavor: {{ $item['varian_rasa'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Catatan --}}
                                            @if ($item['catatan'])
                                                <p class="mt-1.5 text-xs text-[#7C6558] italic">
                                                    &ldquo;{{ $item['catatan'] }}&rdquo;
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Delete Form --}}
                                        <form id="form-hapus-item-d{{ $item['id'] }}" method="POST" action="{{ route('member.keranjang.hapus', $item['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" @click="konfirmasiHapusDesktop('{{ $item['id'] }}', '{{ addslashes($item['nama_produk']) }}')"
                                                    class="flex h-8 w-8 items-center justify-center rounded-full text-[#3C2415]/40 hover:text-red-600 hover:bg-red-50 transition active:scale-90 cursor-pointer"
                                                    title="Remove item">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Price & Stepper Row --}}
                                    <div class="mt-4 pt-3 border-t border-[#3C2415]/10 flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-baseline gap-2">
                                            @if ($itemAdaDiskon)
                                                <span class="text-xs text-[#9C8C80] line-through font-sans">
                                                    Rp {{ number_format($itemHargaAsal * $item['jumlah'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                            <span class="font-display font-black text-xl text-[#3C2415]">
                                                @if (!empty($item['is_reward_stamp']))
                                                    Rp 0
                                                @else
                                                    Rp {{ number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') }}
                                                @endif
                                            </span>
                                            <span class="text-xs text-[#7C6558] font-bold">
                                                ({{ $item['jumlah'] }} &times; Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }})
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            @if (empty($item['is_reward_stamp']))
                                                <a href="{{ $urlUbahItem }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                                                   class="text-xs font-bold text-[#8E1E26] hover:underline uppercase tracking-wider">
                                                    Change
                                                </a>

                                                <span class="h-4 border-l border-[#3C2415]/15"></span>

                                                {{-- Stepper [-] [1] [+] --}}
                                                <div class="flex items-center gap-1 rounded-full bg-[#FAF5E8] border border-[#3C2415]/15 p-1 shadow-xs">
                                                    <form method="POST" action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="jumlah" value="{{ $item['jumlah'] - 1 }}">
                                                        <button type="submit"
                                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition active:scale-90 text-sm font-bold shadow-xs cursor-pointer">
                                                            &minus;
                                                        </button>
                                                    </form>
                                                    <span class="w-7 text-center font-display font-black text-xs text-[#3C2415]">
                                                        {{ $item['jumlah'] }}
                                                    </span>
                                                    <form method="POST" action="{{ route('member.keranjang.ubah-jumlah', $item['id']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="jumlah" value="{{ $item['jumlah'] + 1 }}">
                                                        <button type="submit"
                                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] transition active:scale-90 text-sm font-bold shadow-xs cursor-pointer">
                                                            &plus;
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Want to add more bakes? --}}
                <div class="rounded-[28px] border-2 border-dashed border-[#3C2415]/20 p-6 flex items-center justify-between gap-4 bg-[#FAF5E8]">
                    <div>
                        <h4 class="font-display font-black text-sm text-[#3C2415] uppercase tracking-wider">Want to add more bakes?</h4>
                        <p class="text-xs text-[#7C6558] mt-0.5">Explore our daily fresh croissants, celebration cakes, and artisan sourdough.</p>
                    </div>
                    <a href="{{ route('member.menu') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                       class="shrink-0 px-6 py-2.5 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-[#FAF5E8] font-black text-xs uppercase tracking-wider transition shadow-xs">
                        + Add More
                    </a>
                </div>

                {{-- Order Notes Box --}}
                <div class="rounded-[28px] bg-[#F5ECE3] border border-[#3C2415]/10 p-6">
                    <label class="block font-display font-black text-xs uppercase tracking-wider text-[#3C2415] mb-2.5">
                        Order Notes <span class="font-sans font-normal text-[#9C8C80] normal-case">(optional)</span>
                    </label>
                    <form id="form-checkout-desktop" method="POST" action="{{ route('member.checkout.store') }}">
                        @csrf
                        <textarea name="catatan" rows="3" maxlength="255"
                                  placeholder="Special packing instructions, birthday candle request, allergen notes, etc."
                                  class="w-full resize-none rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/15 p-4 text-xs text-[#3C2415] placeholder:text-[#9C8C80] focus:border-[#8E1E26] focus:ring-1 focus:ring-[#8E1E26] focus:outline-none transition"></textarea>
                    </form>
                </div>

            </div>{{-- end LEFT COLUMN --}}

            {{-- RIGHT COLUMN: Pickup Store, Vouchers, Payment Summary (5 cols) --}}
            <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-6">
                
                {{-- Vouchers & Discounts --}}
                <div class="rounded-[28px] bg-[#F5ECE3] border border-[#3C2415]/10 p-6 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h4 class="font-display font-black text-xs uppercase tracking-wider text-[#3C2415] flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-[#8E1E26]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 5v14M9 5v14M4 10a2 2 0 0 0 0 4M20 10a2 2 0 0 1 0 4M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Vouchers & Discounts
                        </h4>
                        @if ($voucherDipilih)
                            <form method="POST" action="{{ route('member.checkout.voucher') }}">
                                @csrf
                                <input type="hidden" name="voucher_id" value="">
                                <button type="submit" class="text-xs font-bold text-[#8E1E26] hover:underline cursor-pointer">
                                    Remove
                                </button>
                            </form>
                        @endif
                    </div>

                    @if ($voucherDipilih)
                        <div class="rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/10 p-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-[#3C2415]">{{ $voucherDipilih->nama ?? 'Voucher' }}</p>
                                <p class="text-[11px] font-bold text-[#2E6F40]">Save Rp {{ number_format($diskonVoucher, 0, ',', '.') }} applied 🎉</p>
                            </div>
                            <button type="button" onclick="openSheet('modal-pilih-voucher-desktop')"
                                    class="px-3.5 py-1.5 rounded-full border border-[#3C2415]/20 text-xs font-bold text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition cursor-pointer">
                                Change
                            </button>
                        </div>
                    @elseif ($voucherTerbaik)
                        <div class="rounded-2xl bg-[#FAF5E8] border border-[#8E1E26]/20 p-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold text-[#7C6558]">Best voucher for you:</p>
                                <p class="text-xs font-bold text-[#8E1E26]">Save Rp {{ number_format($voucherTerbaik['diskon'], 0, ',', '.') }} 🎉</p>
                            </div>
                            <form method="POST" action="{{ route('member.checkout.voucher') }}">
                                @csrf
                                <input type="hidden" name="voucher_id" value="{{ $voucherTerbaik['voucher']->id }}">
                                <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-[#FAF5E8] text-xs font-bold transition shadow-xs cursor-pointer">
                                    Apply
                                </button>
                            </form>
                        </div>
                    @else
                        <button type="button" onclick="openSheet('modal-pilih-voucher-desktop')"
                                class="w-full flex items-center justify-between rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/10 p-3.5 text-xs text-[#7C6558] hover:border-[#3C2415]/30 transition cursor-pointer">
                            <span>{{ $voucherAktif->count() > 0 ? 'Select available voucher (' . $voucherAktif->count() . ')' : 'No active vouchers' }}</span>
                            <svg class="h-4 w-4 text-[#3C2415]/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Stamp Reward (if available) --}}
                @if (!empty($stampRedemptionsAktif) && $stampRedemptionsAktif->isNotEmpty())
                    <div class="rounded-[28px] bg-[#F5ECE3] border border-[#3C2415]/10 p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-display font-black text-xs uppercase tracking-wider text-[#3C2415] flex items-center gap-1.5">
                                <span>🎁</span>
                                Stamp Reward
                            </h4>
                        </div>
                        @if ($stampRedemptionDipilih)
                            <div class="rounded-2xl bg-[#FAF5E8] border border-amber-300 p-3.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-[#3C2415]">
                                        {{ $stampRedemptionDipilih->jumlah ?: 1 }}x {{ $stampRedemptionDipilih->produk?->nama_produk ?? 'Reward Bake' }}
                                    </p>
                                    <p class="text-[11px] font-bold text-[#2E6F40]">Free with Stamp Reward 🎉</p>
                                </div>
                                <button type="button" onclick="openSheet('modal-pilih-reward-stamp-desktop')"
                                        class="px-3.5 py-1.5 rounded-full border border-[#3C2415]/20 text-xs font-bold text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition cursor-pointer">
                                    Change
                                </button>
                            </div>
                        @else
                            <button type="button" onclick="openSheet('modal-pilih-reward-stamp-desktop')"
                                    class="w-full flex items-center justify-between rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/10 p-3.5 text-xs text-[#7C6558] hover:border-[#3C2415]/30 transition cursor-pointer">
                                <span>{{ $stampRedemptionsAktif->count() }} Stamp reward(s) ready to claim</span>
                                <span class="rounded-full bg-[#3C2415] px-2.5 py-1 text-[10px] font-bold text-white">Claim</span>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Payment Summary Box --}}
                <div class="rounded-[28px] bg-[#F5ECE3] border border-[#3C2415]/10 p-6 space-y-4 shadow-sm">
                    <h4 class="font-display font-black text-xs uppercase tracking-wider text-[#3C2415]">
                        Payment Summary
                    </h4>

                    <div class="space-y-2.5 text-xs text-[#7C6558]">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-bold text-[#3C2415]">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        @foreach ($rincianDiskonProduk ?? [] as $itemRincian)
                            <div class="flex justify-between text-[#8E1E26]">
                                <span>{{ $itemRincian['nama'] }}</span>
                                <span class="font-bold">&minus; Rp {{ number_format($itemRincian['nilai'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        @if (($diskonVoucher ?? 0) > 0)
                            <div class="flex justify-between text-[#8E1E26]">
                                <span>Voucher ({{ $voucherDipilih?->nama ?? 'Discount' }})</span>
                                <span class="font-bold">&minus; Rp {{ number_format($diskonVoucher, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if ($pajak > 0)
                            <div class="flex justify-between">
                                <span>Tax ({{ rtrim(rtrim(number_format($pajakPersen, 1, ',', '.'), '0'), ',') }}%)</span>
                                <span class="font-bold text-[#3C2415]">Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t-2 border-dashed border-[#3C2415]/15 flex items-baseline justify-between">
                            <span class="font-display font-black text-xs uppercase tracking-wider text-[#3C2415]">Total Payment</span>
                            <span class="font-display font-black text-2xl xl:text-3xl text-[#3C2415]">
                                Rp {{ number_format($totalBayar, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if ($poinPotensial > 0)
                        <div class="rounded-2xl bg-[#FAF5E8] border border-[#3C2415]/10 p-3.5 flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#8E1E26]/10 text-[#8E1E26]">
                                @include('mobile.components.icon-gift', ['class' => 'h-4 w-4'])
                            </span>
                            <p class="text-xs text-[#3C2415]">
                                You'll earn <strong class="text-[#8E1E26] font-bold">+{{ $poinPotensial }} Daisy Points</strong> with this order!
                            </p>
                        </div>
                    @endif

                    {{-- Primary Order Button --}}
                    <div class="pt-2">
                        <button type="submit" form="form-checkout-desktop"
                                @disabled(! empty($peringatanStok))
                                class="group w-full py-4 px-8 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-[#FAF5E8] font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-md flex items-center justify-center gap-2 active:scale-98 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">
                            <span>ORDER NOW</span>
                            <svg class="h-3.5 w-3.5 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </button>
                    </div>

                    <div class="pt-2 text-[11px] text-[#7C6558]/80 leading-relaxed space-y-1">
                        <p class="font-bold text-[#3C2415]/80">Pickup & Cancellation Policy:</p>
                        <p>All items are handcrafted fresh daily. Please complete payment confirmation within {{ \App\Models\Transaksi::BATAS_BAYAR_MENIT }} minutes to confirm your kitchen queue.</p>
                    </div>
                </div>

            </div>{{-- end RIGHT COLUMN --}}

        </div>

        {{-- Desktop Dialog: Select Voucher --}}
        <dialog id="modal-pilih-voucher-desktop"
                class="sheet fixed inset-0 m-auto w-full max-w-lg rounded-[28px] border border-[#3C2415]/15 bg-[#FAF5E8] p-0 shadow-2xl backdrop:bg-[#3C2415]/45 backdrop:backdrop-blur-xs">
            <div class="flex max-h-[85vh] flex-col relative p-6 sm:p-8">
                <button type="button" onclick="closeSheet('modal-pilih-voucher-desktop')"
                        class="absolute top-5 right-5 h-8 w-8 rounded-full bg-[#3C2415]/5 hover:bg-[#3C2415]/10 text-[#3C2415] flex items-center justify-center transition cursor-pointer">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <div class="text-center pb-4 border-b border-[#3C2415]/10">
                    <h3 class="font-display font-black text-2xl text-[#3C2415] tracking-tight">
                        Select Voucher
                    </h3>
                    <p class="text-xs text-[#7C6558] mt-1">
                        Choose a discount voucher to apply to your order
                    </p>
                </div>

                <div class="overflow-y-auto py-4 space-y-3 pr-1">
                    <form method="POST" action="{{ route('member.checkout.voucher') }}" class="space-y-3">
                        @csrf
                        <button type="submit" name="voucher_id" value=""
                                class="w-full flex items-center justify-between rounded-2xl border border-[#3C2415]/15 bg-[#F5ECE3] p-4 text-left hover:border-[#3C2415] transition cursor-pointer {{ empty($voucherDipilih) ? 'ring-2 ring-[#3C2415]' : '' }}">
                            <div>
                                <span class="font-display font-bold text-sm text-[#3C2415] block">Don't use any voucher</span>
                                <span class="text-xs text-[#7C6558] block mt-0.5">Proceed with normal pricing</span>
                            </div>
                            @if (empty($voucherDipilih))
                                <span class="h-6 w-6 rounded-full bg-[#3C2415] text-[#FAF5E8] flex items-center justify-center text-xs">✓</span>
                            @endif
                        </button>

                        @foreach ($voucherAktif as $voucher)
                            @php
                                $kelayakan = $voucher->kelayakan ?? ['layak' => true, 'diskon' => 0];
                                $isLayak = $kelayakan['layak'] ?? false;
                                $isDipilih = $voucherDipilih && $voucherDipilih->id === $voucher->id;
                            @endphp
                            @if ($isLayak)
                                <button type="submit" name="voucher_id" value="{{ $voucher->id }}"
                                        class="w-full flex items-center justify-between rounded-2xl border p-4 text-left transition cursor-pointer {{ $isDipilih ? 'bg-[#FAF5E8] border-[#3C2415] ring-2 ring-[#3C2415]' : 'bg-[#F5ECE3] border-[#3C2415]/15 hover:border-[#3C2415]' }}">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="rounded-md bg-[#8E1E26] px-2 py-0.5 text-[10px] font-black text-white uppercase tracking-wider">
                                                Voucher
                                            </span>
                                            @if (!empty($kelayakan['diskon']) && $kelayakan['diskon'] > 0)
                                                <span class="text-xs font-bold text-[#2E6F40]">
                                                    Save Rp {{ number_format($kelayakan['diskon'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                        <h4 class="font-display font-black text-sm text-[#3C2415] truncate">
                                            {{ $voucher->nama }}
                                        </h4>
                                        @if ($voucher->deskripsi)
                                            <p class="text-xs text-[#7C6558] mt-0.5 line-clamp-2">
                                                {{ $voucher->deskripsi }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 ml-3 px-3.5 py-1.5 rounded-full {{ $isDipilih ? 'bg-[#3C2415] text-white' : 'bg-white border border-[#3C2415]/20 text-[#3C2415]' }} text-xs font-bold">
                                        {{ $isDipilih ? 'Applied ✓' : 'Apply' }}
                                    </span>
                                </button>
                            @else
                                <div class="w-full flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-100/60 p-4 text-left opacity-60">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-display font-bold text-sm text-gray-700 truncate">
                                            {{ $voucher->nama }}
                                        </h4>
                                        <p class="text-xs text-red-600 mt-1">
                                            {{ $kelayakan['pesan'] ?? 'Not applicable for current order' }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 ml-3 text-xs text-gray-400 font-bold">
                                        Ineligible
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </form>
                </div>
            </div>
        </dialog>

        {{-- Desktop Dialog: Select Stamp Reward --}}
        @if (!empty($stampRedemptionsAktif) && $stampRedemptionsAktif->isNotEmpty())
        <dialog id="modal-pilih-reward-stamp-desktop"
                class="sheet fixed inset-0 m-auto w-full max-w-lg rounded-[28px] border border-[#3C2415]/15 bg-[#FAF5E8] p-0 shadow-2xl backdrop:bg-[#3C2415]/45 backdrop:backdrop-blur-xs">
            <div class="flex max-h-[85vh] flex-col relative p-6 sm:p-8">
                <button type="button" onclick="closeSheet('modal-pilih-reward-stamp-desktop')"
                        class="absolute top-5 right-5 h-8 w-8 rounded-full bg-[#3C2415]/5 hover:bg-[#3C2415]/10 text-[#3C2415] flex items-center justify-center transition cursor-pointer">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <div class="text-center pb-4 border-b border-[#3C2415]/10">
                    <h3 class="font-display font-black text-2xl text-[#3C2415] tracking-tight">
                        Select Stamp Reward
                    </h3>
                    <p class="text-xs text-[#7C6558] mt-1">
                        Claim a free bake earned from your loyalty stamps
                    </p>
                </div>

                <div class="overflow-y-auto py-4 space-y-3 pr-1">
                    <form method="POST" action="{{ route('member.checkout.reward-stamp') }}" class="space-y-3">
                        @csrf
                        <button type="submit" name="stamp_redemption_id" value=""
                                class="w-full flex items-center justify-between rounded-2xl border border-[#3C2415]/15 bg-[#F5ECE3] p-4 text-left hover:border-[#3C2415] transition cursor-pointer {{ empty($stampRedemptionDipilih) ? 'ring-2 ring-[#3C2415]' : '' }}">
                            <div>
                                <span class="font-display font-bold text-sm text-[#3C2415] block">Don't claim reward now</span>
                                <span class="text-xs text-[#7C6558] block mt-0.5">Save for a future visit</span>
                            </div>
                            @if (empty($stampRedemptionDipilih))
                                <span class="h-6 w-6 rounded-full bg-[#3C2415] text-[#FAF5E8] flex items-center justify-center text-xs">✓</span>
                            @endif
                        </button>

                        @foreach ($stampRedemptionsAktif as $redemption)
                            @php
                                $isDipilih = $stampRedemptionDipilih && $stampRedemptionDipilih->id === $redemption->id;
                            @endphp
                            <button type="submit" name="stamp_redemption_id" value="{{ $redemption->id }}"
                                    class="w-full flex items-center justify-between rounded-2xl border p-4 text-left transition cursor-pointer {{ $isDipilih ? 'bg-[#FAF5E8] border-[#3C2415] ring-2 ring-[#3C2415]' : 'bg-[#F5ECE3] border-[#3C2415]/15 hover:border-[#3C2415]' }}">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="h-14 w-14 rounded-xl overflow-hidden bg-white shrink-0 border border-[#3C2415]/10 flex items-center justify-center">
                                        @if ($redemption->produk?->foto)
                                            <img src="{{ asset('storage/' . $redemption->produk->foto) }}" alt="{{ $redemption->produk->nama_produk }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-2xl">🎁</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="rounded bg-amber-200 text-amber-900 text-[10px] font-black px-2 py-0.5 uppercase tracking-wider inline-block mb-1">
                                            Free Reward
                                        </span>
                                        <h4 class="font-display font-bold text-sm text-[#3C2415] truncate">
                                            {{ $redemption->jumlah ?: 1 }}x {{ $redemption->produk?->nama_produk }}
                                        </h4>
                                        <p class="text-xs font-bold text-[#2E6F40] mt-0.5">
                                            Rp 0 (Free)
                                        </p>
                                    </div>
                                </div>
                                <span class="shrink-0 ml-3 px-3.5 py-1.5 rounded-full {{ $isDipilih ? 'bg-[#3C2415] text-white' : 'bg-white border border-[#3C2415]/20 text-[#3C2415]' }} text-xs font-bold">
                                    {{ $isDipilih ? 'Claimed ✓' : 'Claim' }}
                                </span>
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>
        </dialog>
        @endif

        {{-- Desktop Modal: Confirm Delete Item --}}
        <div x-show="modalHapusTerbukaDesktop" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#3C2415]/45 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbukaDesktop = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="bg-[#FAF5E8] rounded-[28px] shadow-2xl w-full max-w-sm overflow-hidden border border-[#3C2415]/15 p-2">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-[#8E1E26]/10 text-[#8E1E26] flex items-center justify-center rounded-full mx-auto mb-3">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-xl text-[#3C2415]">Remove from Cart?</h3>
                    <p class="text-xs text-[#7C6558] mt-2 leading-relaxed">
                        Are you sure you want to remove <strong class="text-[#3C2415]" x-text="itemHapusNamaDesktop"></strong> from your order?
                    </p>
                </div>
                <div class="bg-[#F5ECE3] px-6 py-4 rounded-[20px] flex items-center justify-end gap-3">
                    <button type="button" @click="modalHapusTerbukaDesktop = false"
                        class="flex-1 py-3 px-4 bg-transparent hover:bg-[#3C2415]/5 font-display font-bold text-xs uppercase tracking-wider rounded-full transition text-[#3C2415] border-2 border-[#3C2415]/20 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="eksekusiHapusDesktop()"
                        class="flex-1 py-3 px-4 bg-[#8E1E26] hover:bg-[#72181E] text-white font-display font-bold text-xs uppercase tracking-wider rounded-full transition shadow-xs active:scale-[0.98] cursor-pointer">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection