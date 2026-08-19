@extends('mobile.layout', ['title' => $produk->nama_produk])

@php
    $hargaAsli = (float) $produk->harga_jual;
    $hargaFinal = (float) $produk->harga_diskon;
    $adaPromo = $hargaFinal < $hargaAsli;
    $minimalBelanja = $pengaturan?->minimal_belanja ?? 0;
    $poinPerKelipatan = $pengaturan?->poin ?? 0;
    $sedangUbah = (bool) $itemEdit;
@endphp

@section('content')

    <div x-data="{
                    jumlah: {{ $itemEdit['jumlah'] ?? 1 }},
                    ukuran: @js($itemEdit['ukuran'] ?? ($produk->punya_ukuran ? 'Kecil' : '')),
                    catatan: @js($itemEdit['catatan'] ?? ''),
                    punyaUkuran: {{ $produk->punya_ukuran ? 'true' : 'false' }},
                    hargaKecil: {{ (float) ($produk->harga_kecil ?? 0) }},
                    hargaBesar: {{ (float) ($produk->harga_besar ?? 0) }},
                    get hargaSatuan() {
                        if (this.punyaUkuran) {
                            return this.ukuran === 'Besar' ? this.hargaBesar : this.hargaKecil;
                        }
                        return this.hargaDasar;
                    },

                    get total() {
                        return this.hargaSatuan * this.jumlah;
                    },

                    get poinDidapat() {
                        if (!this.minimalBelanja || this.minimalBelanja <= 0) {
                            return 0;
                        }

                        return Math.floor(this.total / this.minimalBelanja) * this.poinPerKelipatan;
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
                    }
                }" class="relative">


        <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-gray-50 sm:-mx-8 md:-mx-10">

            @if ($produk->foto)
                <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}"
                    class="h-full w-full object-cover"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            @endif

            <div class="{{ $produk->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                @include('mobile.components.icon-cup', ['class' => 'h-20 w-20'])
            </div>

            {{-- Tombol kembali --}}
            <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
                onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-md transition active:scale-95 active:bg-gray-50"
                aria-label="Kembali">
                <span class="rotate-180">
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                </span>
            </a>

            {{-- Badge promo --}}
            @if ($adaPromo)
                <span
                    class="absolute left-4 top-16 rounded-full bg-orange-500 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                    Promo
                </span>
            @endif

        </div>

        <div class="mt-5">

            {{-- Nama, kategori, harga --}}
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    {{ $produk->kategori->nama_kategori ?? '' }}
                </p>

                <h2 class="mt-1 text-lg font-extrabold leading-snug text-gray-900">
                    {{ $produk->nama_produk }}
                </h2>

                <div class="mt-4">
    
                    <p class="mt-1.5 text-xs leading-relaxed text-gray-500">
                        {{ $produk->deskripsi ?: '' }}
                    </p>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    @if ($adaPromo)
                        <span class="text-xs text-gray-400 line-through">
                            Rp {{ number_format($hargaAsli, 0, ',', '.') }}
                        </span>
                    @endif

                    <span class="text-base font-extrabold text-gray-900">
                        Rp {{ number_format($hargaFinal, 0, ',', '.') }}
                    </span>
                </div>
            </div>


            @if ($habis)
                <div class="mt-4 flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800">
                    @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                    <p class="text-[11px] font-semibold leading-relaxed">
                        Stok produk ini sedang kurang/habis, pesanan tetap bisa dibuat & akan
                        diproduksi dulu di dapur — waktu tunggu bisa lebih lama dari biasanya.
                    </p>
                </div>
            @endif

            @if ($produk->punya_ukuran)
                <div class="mt-5">
                    <h3 class="text-xs font-bold text-gray-900">
                        Pilihan Ukuran
                    </h3>

                    <div class="mt-2 grid grid-cols-2 gap-2">
                        {{-- Kecil --}}
                        <button type="button" @click="ukuran = 'Kecil'" :class="
                                        ukuran === 'Kecil'
                                            ? 'border-brand-800 bg-brand-50 text-brand-800'
                                            : 'border-gray-200 text-gray-600'
                                    " class="rounded-2xl border px-4 py-3 text-left transition active:scale-[0.99]">
                            <span class="block text-xs font-bold">
                                Kecil
                            </span>
                            <span class="block text-[11px] font-semibold text-gray-500">
                                Rp {{ number_format($produk->harga_kecil, 0, ',', '.') }}
                            </span>
                        </button>

                        {{-- Besar --}}
                        <button type="button" @click="ukuran = 'Besar'" :class="
                                        ukuran === 'Besar'
                                            ? 'border-brand-800 bg-brand-50 text-brand-800'
                                            : 'border-gray-200 text-gray-600'
                                    " class="rounded-2xl border px-4 py-3 text-left transition active:scale-[0.99]">
                            <span class="block text-xs font-bold">
                                Besar
                            </span>
                            <span class="block text-[11px] font-semibold text-gray-500">
                                Rp {{ number_format($produk->harga_besar, 0, ',', '.') }}
                            </span>
                        </button>
                    </div>
                </div>
            @endif

            <div class="mt-5">

                <h3 class="text-xs font-bold text-gray-900">
                    Catatan
                    <span class="font-medium text-gray-400">
                        (opsional)
                    </span>
                </h3>

                <textarea x-model="catatan" rows="2" maxlength="255" placeholder="Contoh: kurangi manis, dll."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>

            </div>

            <template x-if="poinDidapat > 0">

                <div class="mt-5 flex items-center gap-2 rounded-2xl bg-brand-50 px-4 py-3 text-brand-800">

                    @include('mobile.components.icon-gift', [
                        'class' => 'h-4 w-4 shrink-0'
                    ])

                    <p class="text-[11px] font-semibold">
                        Kamu berpotensi dapat
                        <span x-text="poinDidapat"></span>
                        Saisho Points dari pesanan ini
                    </p>

                </div>

            </template>


            {{-- Spacer supaya konten tidak tertutup sticky bar --}}
            <div class="h-32"></div>

        </div>

        <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-gray-100 bg-white px-5 pt-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 2xl:max-w-6xl"
            style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">

            @if (Auth::guard('member')->check())

                    @if ($sedangUbah)

                        <form method="POST" action="{{ route('member.keranjang.perbarui', $itemEdit['id']) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="jumlah" :value="jumlah">
                            <input type="hidden" name="ukuran" :value="ukuran">
                            <input type="hidden" name="catatan" :value="catatan">

                            {{-- Card Update --}}
                            <div class="flex items-center gap-3">

                                {{-- Kontrol jumlah --}}
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

                                {{-- Tombol Update --}}
                                <button type="submit"
                                    class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]"
                                    x-text="'Update – Rp ' + total.toLocaleString('id-ID')">
                                </button>

                            </div>

                        </form>

                    @else

                    <form method="POST" action="{{ route('member.keranjang.tambah', $produk) }}"
                        onsubmit="event.preventDefault(); kirimTambahKeranjang(this);">
                        @csrf

                        <input type="hidden" name="jumlah" :value="jumlah">

                        <input type="hidden" name="ukuran" :value="ukuran">

                        <input type="hidden" name="catatan" :value="catatan">


                        {{-- Card Add --}}
                        <div class="flex items-center gap-3">

                            {{-- Kontrol jumlah --}}
                            <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">

                                {{-- Minus --}}
                                <button type="button" @click="kurangiJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                    aria-label="Kurangi jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    </svg>
                                </button>

                                {{-- Jumlah --}}
                                <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                    x-text="jumlah"></span>

                                {{-- Plus --}}
                                <button type="button" @click="tambahJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                    aria-label="Tambah jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>

                            </div>


                            {{-- Tombol Add --}}
                            <button type="submit"
                                class="flex min-w-0 flex-1 items-center justify-between rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                                <span>
                                    Add
                                </span>

                                <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                            </button>

                        </div>

                </div>

                </form>

                    @endif

            @else

            {{-- Belum login --}}
            <button type="button" onclick="bukaModalLoginRequired('Pesan')"
                class="flex w-full items-center justify-between rounded-2xl bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                <span>
                    Login untuk Pesan
                </span>

                <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
            </button>

        @endif

    </div>

    </div>

@endsection