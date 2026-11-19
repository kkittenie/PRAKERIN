@extends('mobile.layout', ['title' => $promoEvent->nama_event])

@php
    $sedangUbah = (bool) $itemEdit;
@endphp

@section('content')

    <div x-data="{
                    jumlah: {{ $itemEdit['jumlah'] ?? 1 }},
                    catatan: @js($itemEdit['catatan'] ?? ''),
                    hargaSatuan: {{ (float) $promoEvent->harga_event }},
                    habis: {{ $habis ? 'true' : 'false' }},
                    promoEventId: {{ $promoEvent->id }},
                    berakhir: false,
                    stokTersedia: {{ (int) $stokTersedia }},
                    terpakai: {{ (int) $terpakaiDiKeranjang }},

                    get stokTidakCukup() {
                        return !this.habis && (this.jumlah + this.terpakai) > this.stokTersedia;
                    },

                    get pesanStokTidakCukup() {
                        if (!this.stokTidakCukup) return '';
                        let pesan = 'Stok tidak mencukupi. Sisa stok ' + this.stokTersedia + ' paket';
                        if (this.terpakai >= this.stokTersedia) {
                            return pesan + ' dan semuanya sudah ada di keranjang kamu.';
                        }
                        return this.terpakai > 0
                            ? pesan + ', ' + this.terpakai + ' paket sudah ada di keranjang kamu.'
                            : pesan + '.';
                    },

                    get tidakBisaPesan() {
                        return this.berakhir || this.habis || this.stokTidakCukup;
                    },

                    get labelTidakBisa() {
                        if (this.habis) return 'Stok Habis';
                        if (this.stokTidakCukup) return 'Stok Tidak Cukup';
                        return '';
                    },

                    get total() {
                        return this.hargaSatuan * this.jumlah;
                    },

                    tambahJumlah() {
                        if (this.jumlah < 50) {
                            this.jumlah++;
                        }
                    },

                    kurangiJumlah() {
                        if (this.jumlah > 1) {
                            this.jumlah--;
                        }
                    },

                    // Poll berkala: kalau promo event ini kadaluwarsa/dinonaktifkan
                    // admin selagi dibuka, tombol beli otomatis kekunci + kasih notice,
                    // tanpa perlu member reload halaman sendiri.
                    cekStatusPromoEvent() {
                        fetch(`{{ route('member.promo-status') }}?produk_ids=`)
                            .then(r => r.json())
                            .then(r => {
                                if (r.status !== 'success') return;
                                const aktif = r.data.promo_event.find(pe => pe.id === this.promoEventId);
                                if (!aktif) {
                                    this.berakhir = true;
                                    return;
                                }
                                this.hargaSatuan = aktif.harga_event;
                                this.habis = aktif.perlu_diproduksi;
                                this.stokTersedia = parseInt(aktif.stok_tersedia) || 0;
                            })
                            .catch(() => {});
                    }
                }" x-init="setInterval(() => cekStatusPromoEvent(), 10000)" class="relative lg:max-w-5xl lg:mx-auto lg:grid lg:grid-cols-12 lg:gap-10 lg:items-start">

        {{-- Left column: 1:1 photo + info (nama, harga, deskripsi) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-gray-50 sm:-mx-8 md:-mx-10 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:aspect-square lg:w-full">

                @if ($promoEvent->produk && $promoEvent->produk->foto)
                    <img src="{{ asset('storage/' . $promoEvent->produk->foto) }}" alt="{{ $promoEvent->nama_event }}"
                        class="h-full w-full object-cover"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                @endif

                <div class="{{ ($promoEvent->produk && $promoEvent->produk->foto) ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                    @include('mobile.components.icon-cake', ['class' => 'h-20 w-20'])
                </div>

                <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-md transition active:scale-95 active:bg-gray-50"
                    aria-label="Kembali">
                    <span class="rotate-180">
                        @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                    </span>
                </a>

                <span class="absolute left-4 top-16 rounded-full bg-rose-500 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                    Promo Event
                </span>

                <span x-show="habis" x-cloak class="absolute left-4 bottom-4 rounded-full bg-gray-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">
                    Stok Habis
                </span>

            </div>

            {{-- Nama, kategori, harga di bawah foto --}}
            <div class="pt-1">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    {{ $promoEvent->produk->nama_produk ?? '' }}
                    @if ($ukuranEvent = $promoEvent->ukuranTunggalUntukProduksi())
                        {{ $ukuranEvent }}
                    @endif
                </p>

                <h2 class="mt-1 text-lg lg:text-xl font-extrabold leading-snug text-gray-900">
                    {{ $promoEvent->nama_event }}
                </h2>

                <div class="mt-2 flex items-center gap-2">
                    <span class="text-base lg:text-lg font-extrabold text-gray-900" x-text="'Rp ' + hargaSatuan.toLocaleString('id-ID')">
                        Rp {{ number_format($promoEvent->harga_event, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Right column: catatan & order form --}}
        <div class="mt-2 lg:col-span-7 lg:mt-0 space-y-4">

            {{-- Banner: promo event berakhir selagi dibuka --}}
            <div x-show="berakhir" x-cloak class="flex items-start gap-2 rounded-2xl bg-rose-50 px-4 py-3 text-rose-800 border border-rose-100">
                @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                <p class="text-[11px] font-semibold leading-relaxed">
                    Yah, promo event ini baru saja berakhir. Yuk cek menu lain yang masih tersedia.
                </p>
            </div>

            @if ($promoEvent->produk && $promoEvent->produk->deskripsi)
                <div class="lg:rounded-2xl lg:border lg:border-gray-100 lg:bg-gray-50/50 lg:p-4">
                    <h3 class="hidden lg:block text-xs font-bold text-gray-900 mb-1">Deskripsi Produk</h3>
                    <p class="text-xs leading-relaxed text-gray-500 lg:text-gray-600 whitespace-pre-line">
                        {{ $promoEvent->produk->deskripsi }}
                    </p>
                </div>
            @endif

            <div x-show="habis" x-cloak class="flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800 border border-amber-100">
                @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                <p class="text-[11px] font-semibold leading-relaxed">
                    Produk sedang diproduksi.
                </p>
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-900">
                    Catatan
                    <span class="font-medium text-gray-400">
                        (opsional)
                    </span>
                </h3>

                <textarea x-model="catatan" rows="2" maxlength="255" placeholder="Contoh: kurangi manis, dll."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>
            </div>

            {{-- Spacer pas di bawah kolom catatan agar scroll pas sampai batas kolom catatan --}}
            <div class="h-24 lg:hidden" aria-hidden="true"></div>

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-gray-100 bg-white px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 lg:static lg:w-full lg:border-t-0 lg:px-0 lg:pt-2 lg:shadow-none">

            {{-- Pesan kalau jumlah yang diminta melebihi sisa stok --}}
            <template x-if="stokTidakCukup">
                <p class="mb-2 text-[11px] font-semibold leading-snug text-red-600" x-text="pesanStokTidakCukup"></p>
            </template>

            @if (Auth::guard('member')->check())

                @if ($sedangUbah)

                    <form method="POST" action="{{ route('member.keranjang.perbarui-promo-event', $itemEdit['id']) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="promo_event_id" value="{{ $promoEvent->id }}">
                        <input type="hidden" name="jumlah" :value="jumlah">
                        <input type="hidden" name="catatan" :value="catatan">

                        <div class="flex items-center gap-3">

                            <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                                <button type="button" @click="kurangiJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                    aria-label="Kurangi jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    </svg>
                                </button>

                                <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                    x-text="jumlah"></span>

                                <button type="button" @click="tambahJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                    aria-label="Tambah jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <button type="submit" :disabled="tidakBisaPesan"
                                :class="tidakBisaPesan ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex min-w-0 flex-1 lg:max-w-[260px] items-center justify-center rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]"
                                x-text="berakhir ? 'Promo sudah berakhir' : (tidakBisaPesan ? labelTidakBisa : ('Update – Rp ' + total.toLocaleString('id-ID')))">
                            </button>

                        </div>

                    </form>

                @else

                <form method="POST" action="{{ route('member.keranjang.tambah-promo-event', $promoEvent) }}"
                    onsubmit="event.preventDefault(); if (!this.dataset.berakhir || this.dataset.berakhir !== 'true') kirimTambahKeranjang(this);"
                    x-effect="$el.dataset.berakhir = berakhir">
                    @csrf

                    <input type="hidden" name="jumlah" :value="jumlah">
                    <input type="hidden" name="catatan" :value="catatan">

                    <div class="flex items-center gap-3">

                        <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                            <button type="button" @click="kurangiJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                aria-label="Kurangi jumlah">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                </svg>
                            </button>

                            <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                x-text="jumlah"></span>

                            <button type="button" @click="tambahJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                aria-label="Tambah jumlah">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>

                        <button type="submit" :disabled="tidakBisaPesan"
                            :class="tidakBisaPesan ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex min-w-0 flex-1 lg:max-w-[260px] items-center justify-between rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                            <span x-text="berakhir ? 'Promo berakhir' : (tidakBisaPesan ? labelTidakBisa : 'Add')"></span>
                            <span x-show="!tidakBisaPesan" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </button>
                    </div>

                </form>

                @endif

            @else

                <button type="button" onclick="bukaModalLoginRequired('Pesan')"
                    class="flex w-full items-center justify-between rounded-2xl bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                    <span>Login untuk Pesan</span>
                    <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                </button>

            @endif

        </div>

    </div>

@endsection