@extends('mobile.layout', ['title' => $produk->nama_produk])

@php
    $minimalBelanja = $pengaturan?->minimal_belanja ?? 0;
    $poinPerKelipatan = $pengaturan?->poin ?? 0;
    $sedangUbah = (bool) $itemEdit;
@endphp

@section('content')

    <div x-data="{
                    jumlah: {{ $itemEdit['jumlah'] ?? 1 }},
                    ukuran: @js($ukuranDipilihAwal ?? ''),
                    varianRasa: @js($varianRasaDipilihAwal ?? ''),
                    catatan: @js($itemEdit['catatan'] ?? ''),
                    punyaVarianBaru: {{ $punyaVarianBaru ? 'true' : 'false' }},
                    daftarVarian: @js($daftarVarian),
                    opsiUkuran: @js($opsiUkuran),
                    opsiVariasi: @js($opsiVariasi),
                    idVarianAwal: @js($varianIdDipilihAwal),
                    punyaUkuran: {{ $punyaUkuran ? 'true' : 'false' }},
                    punyaVarianRasa: {{ $punyaVarianRasa ? 'true' : 'false' }},
                    daftarUkuran: @js($daftarUkuran),
                    daftarVarianRasa: @js($daftarVarianRasa),
                    hargaDasar: {{ (float) $hargaFinal }},
                    hargaAsliDasar: {{ (float) $hargaAsli }},
                    fotoProdukUtama: @js($produk->foto ? asset('storage/' . $produk->foto) : null),
                    stokTanpaDetail: {{ (int) $stokTanpaDetail }},
                    terpakaiPerDetail: @js($terpakaiPerDetail),
                    terpakaiTanpaDetail: {{ (int) $terpakaiTanpaDetail }},

                    init() {
                        if (this.punyaVarianBaru && !this.varianTerpilih) {
                            let pertama = this.daftarVarian.find(v => v.aktif);
                            if (pertama) {
                                this.ukuran = pertama.ukuran || '';
                                this.varianRasa = pertama.variasi || '';
                            }
                        }
                    },

                    get varianTerpilih() {
                        if (!this.punyaVarianBaru) return null;
                        let cocok = this.daftarVarian.find(v => {
                            let matchU = !this.ukuran || v.ukuran === this.ukuran;
                            let matchR = !this.varianRasa || v.variasi === this.varianRasa;
                            return v.aktif && matchU && matchR;
                        });
                        return cocok || null;
                    },

                    ukuranTersedia(u) {
                        if (!this.punyaVarianBaru) return true;
                        return this.daftarVarian.some(v =>
                            v.aktif && v.ukuran === u
                            && (!this.varianRasa || v.variasi === this.varianRasa));
                    },

                    variasiTersedia(vr) {
                        if (!this.punyaVarianBaru) return true;
                        return this.daftarVarian.some(v =>
                            v.aktif && v.variasi === vr
                            && (!this.ukuran || v.ukuran === this.ukuran));
                    },

                    get idVarianTerpilih() {
                        return this.varianTerpilih ? this.varianTerpilih.id : null;
                    },

                    // Hanya terisi untuk produk Stok Bersama (varian-nya ada di tabel varian_produks).
                    get idVarianProdukTerpilih() {
                        return this.varianTerpilih ? (this.varianTerpilih.varian_produk_id || null) : null;
                    },

                    get fotoTampil() {
                        if (this.punyaVarianBaru && this.varianTerpilih && this.varianTerpilih.foto) {
                            return this.varianTerpilih.foto;
                        }
                        return this.fotoProdukUtama;
                    },

                    get opsiUkuranTerpilih() {
                        return this.daftarUkuran.find(o => o.nama === this.ukuran) || null;
                    },

                    get hargaSatuan() {
                        if (this.punyaVarianBaru) {
                            return this.varianTerpilih ? this.varianTerpilih.harga_diskon : this.hargaDasar;
                        }
                        if (this.punyaUkuran) {
                            return this.opsiUkuranTerpilih ? this.opsiUkuranTerpilih.harga_final : this.hargaDasar;
                        }
                        return this.hargaDasar;
                    },

                    get hargaAsliSatuan() {
                        if (this.punyaVarianBaru) {
                            return this.varianTerpilih ? this.varianTerpilih.harga_asli : this.hargaAsliDasar;
                        }
                        if (this.punyaUkuran) {
                            return this.opsiUkuranTerpilih ? this.opsiUkuranTerpilih.harga_asli : this.hargaAsliDasar;
                        }
                        return this.hargaAsliDasar;
                    },

                    get adaPromoVarian() {
                        if (this.punyaVarianBaru) {
                            return this.varianTerpilih ? (this.varianTerpilih.sedang_promo || this.varianTerpilih.harga_diskon < this.varianTerpilih.harga_asli) : false;
                        }
                        if (this.punyaUkuran) {
                            return this.opsiUkuranTerpilih ? this.opsiUkuranTerpilih.ada_promo : false;
                        }
                        return {{ $adaPromo ? 'true' : 'false' }};
                    },

                    get isHabis() {
                        if (this.punyaVarianBaru) {
                            return !this.varianTerpilih || this.varianTerpilih.stok <= 0;
                        }
                        return {{ $habis ? 'true' : 'false' }};
                    },

                    // Stok varian yang sedang dipilih (produk tanpa varian: total stok produk).
                    get stokTersedia() {
                        if (this.punyaVarianBaru) {
                            return this.varianTerpilih ? (parseInt(this.varianTerpilih.stok) || 0) : 0;
                        }
                        return this.stokTanpaDetail;
                    },

                    // Jumlah yang sudah ada di keranjang untuk stok yang sama (tanpa item yang sedang diubah).
                    get sudahDiKeranjang() {
                        if (!this.punyaVarianBaru) return this.terpakaiTanpaDetail;
                        if (!this.varianTerpilih) return 0;
                        return this.terpakaiPerDetail[this.varianTerpilih.id] || 0;
                    },

                    get tampilBadgeHabis() {
                        return this.isHabis && (!this.punyaVarianBaru || !!this.varianTerpilih);
                    },

                    get stokTidakCukup() {
                        return !this.isHabis && (this.jumlah + this.sudahDiKeranjang) > this.stokTersedia;
                    },

                    get pesanStokTidakCukup() {
                        if (!this.stokTidakCukup) return '';
                        let pesan = 'Insufficient stock. Only ' + this.stokTersedia + ' pcs available';
                        if (this.sudahDiKeranjang >= this.stokTersedia) {
                            return pesan + ' and all are already in your cart.';
                        }
                        return this.sudahDiKeranjang > 0
                            ? pesan + ', ' + this.sudahDiKeranjang + ' pcs are already in your cart.'
                            : pesan + '.';
                    },

                    get tidakBisaPesan() {
                        return (this.punyaVarianBaru && !this.varianTerpilih) || this.isHabis || this.stokTidakCukup;
                    },

                    get labelTidakBisa() {
                        if (this.punyaVarianBaru && !this.varianTerpilih) return 'Inactive Variant';
                        if (this.isHabis) return 'Sold Out';
                        if (this.stokTidakCukup) return 'Out of Stock';
                        return '';
                    },

                    pilihUkuran(u) {
                        if (!this.ukuranTersedia(u)) return;
                        this.ukuran = u;
                        if (this.punyaVarianBaru) {
                            let cocok = this.daftarVarian.some(v => v.aktif && v.ukuran === u && v.variasi === this.varianRasa);
                            if (!cocok) {
                                let varianCocok = this.daftarVarian.find(v => v.aktif && v.ukuran === u);
                                if (varianCocok) {
                                    this.varianRasa = varianCocok.variasi || '';
                                }
                            }
                        }
                    },

                    pilihVariasi(r) {
                        if (!this.variasiTersedia(r)) return;
                        this.varianRasa = r;
                        if (this.punyaVarianBaru) {
                            let cocok = this.daftarVarian.some(v => v.aktif && v.variasi === r && v.ukuran === this.ukuran);
                            if (!cocok) {
                                let varianCocok = this.daftarVarian.find(v => v.aktif && v.variasi === r);
                                if (varianCocok) {
                                    this.ukuran = varianCocok.ukuran || '';
                                }
                            }
                        }
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
                }" class="relative w-full">

        {{-- ━━━━ MOBILE / TABLET VIEW (< 1024px) ━━━━ --}}
        <div class="lg:hidden relative">

        {{-- Left column: 1:1 photo + info (nama, harga, deskripsi) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-gray-50 sm:-mx-8 md:-mx-10 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:aspect-square lg:w-full">

                <template x-if="fotoTampil">
                    <img :src="fotoTampil" alt="{{ $produk->nama_produk }}"
                        class="h-full w-full object-cover">
                </template>

                <div x-show="!fotoTampil" class="flex h-full w-full items-center justify-center text-brand-200">
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
                <template x-if="adaPromoVarian">
                    <span
                        class="absolute left-4 top-16 rounded-full bg-orange-500 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                        Promo
                    </span>
                </template>

                {{-- Badge stok habis (sama seperti di bundling) --}}
                <span x-show="tampilBadgeHabis" x-cloak
                    class="absolute left-4 bottom-4 rounded-full bg-gray-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">
                    Stok Habis
                </span>

            </div>

            {{-- Nama, kategori, harga --}}
            <div class="pt-1">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    {{ $produk->kategori->nama_kategori ?? '' }}
                </p>

                <h2 class="mt-1 text-lg lg:text-xl font-extrabold leading-snug text-gray-900">
                    {{ $produk->nama_produk }}
                </h2>

                <template x-if="punyaVarianBaru">
                    <div class="mt-2 flex items-center gap-2">
                        <template x-if="adaPromoVarian">
                            <span class="text-xs text-gray-400 line-through"
                                x-text="'Rp ' + Math.round(hargaAsliSatuan).toLocaleString('id-ID')"></span>
                        </template>

                        <span class="text-base lg:text-lg font-extrabold text-gray-900"
                            x-text="'Rp ' + Math.round(hargaSatuan).toLocaleString('id-ID')"></span>
                    </div>
                </template>

                <template x-if="!punyaVarianBaru && !punyaUkuran">
                    <div class="mt-2 flex items-center gap-2">
                        @if ($adaPromo)
                            <span class="text-xs text-gray-400 line-through">
                                Rp {{ number_format($hargaAsli, 0, ',', '.') }}
                            </span>
                        @endif

                        <span class="text-base lg:text-lg font-extrabold text-gray-900">
                            Rp {{ number_format($hargaFinal, 0, ',', '.') }}
                        </span>
                    </div>
                </template>
            </div>

            <template x-if="poinDidapat > 0">
                <div class="mt-3 flex items-center gap-2 rounded-2xl bg-brand-50 px-4 py-3 text-brand-800">
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
        </div>

        {{-- Right column: deskripsi, options & order button --}}
        <div class="mt-2 lg:col-span-7 lg:mt-0 space-y-4">

            @if ($produk->deskripsi)
                <div class="lg:rounded-2xl lg:border lg:border-gray-100 lg:bg-gray-50/50 lg:p-4">
                    <h3 class="hidden lg:block text-xs font-bold text-gray-900 mb-1">Deskripsi Produk</h3>
                    <p class="text-xs leading-relaxed text-gray-500 lg:text-gray-600 whitespace-pre-line">
                        {{ $produk->deskripsi }}
                    </p>
                </div>
            @endif

            <template x-if="isHabis">
                <div class="flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800">
                    @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                    <p class="text-[11px] font-semibold leading-relaxed">
                        Produk sedang diproduksi.
                    </p>
                </div>
            </template>

            @if ($punyaVarianBaru)
                <template x-if="opsiUkuran && opsiUkuran.length > 0">
                    <div>
                        <h3 class="text-xs font-bold text-gray-900">
                            Pilihan Ukuran
                        </h3>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <template x-for="u in opsiUkuran" :key="u">
                                <button type="button" @click="pilihUkuran(u)"
                                    :disabled="!ukuranTersedia(u)"
                                    :title="ukuranTersedia(u) ? '' : 'Nonaktif'"
                                    :class="
                                        !ukuranTersedia(u)
                                            ? 'border-dashed border-gray-200 bg-gray-100/80 text-gray-400 cursor-not-allowed opacity-50'
                                            : (ukuran === u
                                                ? 'border-brand-800 bg-brand-50 text-brand-800 font-extrabold shadow-xs cursor-pointer'
                                                : 'border-gray-200 text-gray-600 hover:border-brand-500 active:scale-[0.99] cursor-pointer')
                                    "
                                    :style="ukuranTersedia(u) ? '' : 'text-decoration:line-through;'"
                                    class="rounded-2xl border px-4 py-3 text-left transition relative">
                                    <span class="block text-xs font-bold" x-text="u"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="opsiVariasi && opsiVariasi.length > 0">
                    <div>
                        <h3 class="text-xs font-bold text-gray-900">
                            Pilihan Variasi
                        </h3>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <template x-for="v in opsiVariasi" :key="v">
                                <button type="button" @click="pilihVariasi(v)"
                                    :disabled="!variasiTersedia(v)"
                                    :title="variasiTersedia(v) ? '' : 'Nonaktif'"
                                    :class="
                                        !variasiTersedia(v)
                                            ? 'border-dashed border-gray-200 bg-gray-100/80 text-gray-400 cursor-not-allowed opacity-50'
                                            : (varianRasa === v
                                                ? 'border-brand-800 bg-brand-50 text-brand-800 font-extrabold shadow-xs cursor-pointer'
                                                : 'border-gray-200 text-gray-600 hover:border-brand-500 active:scale-[0.99] cursor-pointer')
                                    "
                                    :style="variasiTersedia(v) ? '' : 'text-decoration:line-through;'"
                                    class="flex items-center gap-2.5 rounded-2xl border px-3.5 py-3 text-left transition relative">
                                    <span class="block text-xs font-bold truncate" x-text="v"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            @else
                @if ($produk->punya_ukuran)
                    <div>
                        <h3 class="text-xs font-bold text-gray-900">
                            Pilihan Ukuran
                        </h3>

                        <template x-if="!daftarUkuran || daftarUkuran.length === 0">
                            <p class="mt-2 text-[11px] italic text-gray-400">
                                Belum ada ukuran aktif untuk produk ini.
                            </p>
                        </template>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <template x-for="opsi in daftarUkuran" :key="opsi.produk_ukuran_id">
                                <button type="button" @click="ukuran = opsi.nama" :class="
                                                ukuran === opsi.nama
                                                    ? 'border-brand-800 bg-brand-50 text-brand-800'
                                                    : 'border-gray-200 text-gray-600'
                                            " class="rounded-2xl border px-4 py-3 text-left transition active:scale-[0.99]">
                                    <span class="block text-xs font-bold" x-text="opsi.nama"></span>

                                    <template x-if="opsi.ada_promo">
                                        <span class="block text-[10px] font-semibold text-gray-400 line-through"
                                            x-text="'Rp ' + opsi.harga_asli.toLocaleString('id-ID')"></span>
                                    </template>

                                    <span class="block text-[11px] font-semibold text-gray-500"
                                        x-text="'Rp ' + opsi.harga_final.toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                @endif

                @if ($produk->punya_varian_rasa)
                    <div>
                        <h3 class="text-xs font-bold text-gray-900">
                            Pilihan Varian Rasa
                        </h3>

                        <template x-if="!daftarVarianRasa || daftarVarianRasa.length === 0">
                            <p class="mt-2 text-[11px] italic text-gray-400">
                                Belum ada varian rasa aktif untuk produk ini.
                            </p>
                        </template>

                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <template x-for="rasa in daftarVarianRasa" :key="rasa.produk_varian_rasa_id">
                                <button type="button" @click="varianRasa = rasa.nama" :class="
                                                varianRasa === rasa.nama
                                                    ? 'border-brand-800 bg-brand-50 text-brand-800 font-extrabold'
                                                    : 'border-gray-200 text-gray-600'
                                            " class="flex items-center gap-2.5 rounded-2xl border px-3.5 py-3 text-left transition active:scale-[0.99]">
                                    <template x-if="rasa.gambar">
                                        <img :src="rasa.gambar" :alt="rasa.nama" class="h-8 w-8 rounded-full object-cover shrink-0 border border-gray-100">
                                    </template>
                                    <span class="block text-xs font-bold truncate" x-text="rasa.nama"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                @endif
            @endif

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

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-gray-100 bg-white px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 lg:static lg:w-full lg:border-t-0 lg:px-0 lg:pt-4 lg:shadow-none">

            {{-- Pesan kalau jumlah yang diminta melebihi sisa stok --}}
            <template x-if="stokTidakCukup">
                <p class="mb-2 text-[11px] font-semibold leading-snug text-red-600" x-text="pesanStokTidakCukup"></p>
            </template>

            @if (Auth::guard('member')->check())

                    @if ($sedangUbah)

                        <form method="POST" action="{{ route('member.keranjang.perbarui', $itemEdit['id']) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="detail_produk_id" :value="idVarianTerpilih">
                        <input type="hidden" name="varian_produk_id" :value="idVarianProdukTerpilih">
                            <input type="hidden" name="jumlah" :value="jumlah">
                            <input type="hidden" name="ukuran" :value="ukuran">
                            <input type="hidden" name="varian_rasa" :value="varianRasa">
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
                                    :disabled="tidakBisaPesan"
                                    :class="tidakBisaPesan ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-brand-800 active:scale-[0.99]'"
                                    class="flex min-w-0 flex-1 items-center justify-center rounded-full px-6 py-4 text-sm font-bold text-white shadow-sm transition"
                                    x-text="tidakBisaPesan ? labelTidakBisa : ('Update – Rp ' + total.toLocaleString('id-ID'))">
                                </button>

                            </div>

                        </form>

                    @else

                    <form method="POST" action="{{ route('member.keranjang.tambah', $produk) }}"
                        onsubmit="event.preventDefault(); kirimTambahKeranjang(this);">
                        @csrf

                        <input type="hidden" name="detail_produk_id" :value="idVarianTerpilih">
                        <input type="hidden" name="varian_produk_id" :value="idVarianProdukTerpilih">

                        <input type="hidden" name="jumlah" :value="jumlah">

                        <input type="hidden" name="ukuran" :value="ukuran">

                        <input type="hidden" name="varian_rasa" :value="varianRasa">

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
                                :disabled="tidakBisaPesan"
                                :class="tidakBisaPesan ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-brand-800 active:scale-[0.99]'"
                                class="flex min-w-0 flex-1 lg:max-w-[260px] items-center justify-between rounded-full px-6 py-3.5 text-sm font-bold text-white shadow-sm transition">
                                <span x-text="tidakBisaPesan ? labelTidakBisa : 'Add'"></span>

                                <span x-show="!tidakBisaPesan" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
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

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — BESPOKE VINTAGE PRODUCT SHOWCASE
         Matches reference: 2-Column Product Hero with Dark Cocoa Photo Box,
         Specs Biscuit Table, and 3-Column Related Bakes Showcase
         ════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block w-full bg-[#FAF5E8] py-10 px-8 lg:px-12 xl:px-16 min-h-screen">
        {{-- Breadcrumb back link --}}
        <div class="max-w-6xl mx-auto mb-8">
            <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="group inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#3C2415]/60 hover:text-[#8E1E26] transition-colors">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Back to {{ $sedangUbah ? 'Checkout' : 'Menu' }}</span>
            </a>
        </div>

        {{-- Main 2-Column Product Showcase Hero --}}
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 xl:gap-16 items-start">
            {{-- Left: Dark Cocoa Photo Box (Matches Reference) --}}
            <div class="lg:col-span-6">
                <div class="aspect-square w-full rounded-[36px] bg-[#3C2415] p-10 lg:p-14 flex items-center justify-center relative overflow-hidden shadow-xl">
                    <template x-if="fotoTampil">
                        <img :src="fotoTampil" alt="{{ $produk->nama_produk }}"
                             class="max-h-full max-w-full object-contain filter drop-shadow-[0_16px_28px_rgba(0,0,0,0.45)] transition-transform duration-500 hover:scale-105 select-none pointer-events-none">
                    </template>
                    <div x-show="!fotoTampil" class="flex h-full w-full items-center justify-center text-[#FAF5E8]/20">
                        @include('mobile.components.icon-cup', ['class' => 'h-28 w-28'])
                    </div>

                    {{-- Badges on Photo --}}
                    <template x-if="adaPromoVarian">
                        <span class="absolute left-6 top-6 rounded-full bg-[#8E1E26] px-3.5 py-1 text-xs font-bold text-white shadow-sm tracking-wide">
                            Promo
                        </span>
                    </template>
                    <span x-show="tampilBadgeHabis" x-cloak
                          class="absolute left-6 bottom-6 rounded-full bg-[#3C2415]/90 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                        Sold Out
                    </span>
                </div>
            </div>

            {{-- Right: Product Details, Options, Price & Specs --}}
            <div class="lg:col-span-6 flex flex-col justify-between">
                @if (Auth::guard('member')->check())
                    <form method="POST"
                          action="{{ $sedangUbah ? route('member.keranjang.perbarui', $itemEdit['id']) : route('member.keranjang.tambah', $produk) }}"
                          @if (!$sedangUbah) onsubmit="event.preventDefault(); kirimTambahKeranjang(this);" @endif>
                        @csrf
                        @if ($sedangUbah)
                            @method('PATCH')
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        @endif
                        <input type="hidden" name="detail_produk_id" :value="idVarianTerpilih">
                        <input type="hidden" name="varian_produk_id" :value="idVarianProdukTerpilih">
                        <input type="hidden" name="jumlah" :value="jumlah">
                        <input type="hidden" name="ukuran" :value="ukuran">
                        <input type="hidden" name="varian_rasa" :value="varianRasa">
                        <input type="hidden" name="catatan" :value="catatan">
                @endif

                {{-- Category Badge (Matches SWEETS badge in reference) --}}
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-md bg-[#FCE8BD] text-[#3C2415] font-display font-black text-xs tracking-wider uppercase mb-3">
                        {{ $produk->kategori->nama_kategori ?? 'BAKERY' }}
                    </div>

                    {{-- Product Title --}}
                    <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-[#3C2415] tracking-tight leading-tight mb-4">
                        {{ $produk->nama_produk }}
                    </h1>

                    {{-- Product Description (Only from database, no hallucination) --}}
                    @if ($produk->deskripsi)
                        <p class="font-sans text-sm lg:text-base text-[#7C6558] leading-relaxed mb-6 whitespace-pre-line">
                            {{ $produk->deskripsi }}
                        </p>
                    @endif
                </div>

                {{-- Options (Sizes / Flavors) if present in database --}}
                @if ($punyaVarianBaru)
                    <template x-if="opsiUkuran && opsiUkuran.length > 0">
                        <div class="mb-5">
                            <p class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-2.5">
                                Select Size
                            </p>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="u in opsiUkuran" :key="u">
                                    <button type="button" @click="pilihUkuran(u)"
                                            :disabled="!ukuranTersedia(u)"
                                            :class="!ukuranTersedia(u) ? 'border-dashed border-gray-200 bg-gray-100/80 text-gray-400 cursor-not-allowed opacity-50' : (ukuran === u ? 'bg-[#3C2415] text-[#FAF5E8] border-[#3C2415] shadow-xs' : 'bg-[#F5ECE3] text-[#3C2415] border-[#3C2415]/15 hover:border-[#3C2415] active:scale-95')"
                                            class="rounded-xl border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                        <span x-text="u"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="opsiVariasi && opsiVariasi.length > 0">
                        <div class="mb-5">
                            <p class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-2.5">
                                Select Variation
                            </p>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="v in opsiVariasi" :key="v">
                                    <button type="button" @click="pilihVariasi(v)"
                                            :disabled="!variasiTersedia(v)"
                                            :class="!variasiTersedia(v) ? 'border-dashed border-gray-200 bg-gray-100/80 text-gray-400 cursor-not-allowed opacity-50' : (varianRasa === v ? 'bg-[#3C2415] text-[#FAF5E8] border-[#3C2415] shadow-xs' : 'bg-[#F5ECE3] text-[#3C2415] border-[#3C2415]/15 hover:border-[#3C2415] active:scale-95')"
                                            class="rounded-xl border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                        <span x-text="v"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                @else
                    @if ($produk->punya_ukuran)
                        <div class="mb-5">
                            <p class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-2.5">
                                Select Size
                            </p>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="opsi in daftarUkuran" :key="opsi.produk_ukuran_id">
                                    <button type="button" @click="ukuran = opsi.nama"
                                            :class="ukuran === opsi.nama ? 'bg-[#3C2415] text-[#FAF5E8] border-[#3C2415] shadow-xs' : 'bg-[#F5ECE3] text-[#3C2415] border-[#3C2415]/15 hover:border-[#3C2415] active:scale-95'"
                                            class="rounded-xl border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                        <span x-text="opsi.nama"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif

                    @if ($produk->punya_varian_rasa)
                        <div class="mb-5">
                            <p class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-2.5">
                                Select Variation
                            </p>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="rasa in daftarVarianRasa" :key="rasa.produk_varian_rasa_id">
                                    <button type="button" @click="varianRasa = rasa.nama"
                                            :class="varianRasa === rasa.nama ? 'bg-[#3C2415] text-[#FAF5E8] border-[#3C2415] shadow-xs' : 'bg-[#F5ECE3] text-[#3C2415] border-[#3C2415]/15 hover:border-[#3C2415] active:scale-95'"
                                            class="rounded-xl border px-4 py-2 text-xs font-bold transition-all cursor-pointer">
                                        <span x-text="rasa.nama"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Price & CTA Row (Matches $9.00 / 1pc + SHOP NOW -> in reference) --}}
                <div class="flex flex-wrap items-center justify-between gap-4 py-5 border-y border-[#3C2415]/10 mb-6">
                    <div class="flex items-baseline gap-2">
                        <template x-if="adaPromoVarian">
                            <span class="text-sm text-[#9C8C80] line-through font-sans"
                                  x-text="'Rp ' + Math.round(hargaAsliSatuan).toLocaleString('id-ID')"></span>
                        </template>
                        <span class="font-display font-black text-3xl xl:text-4xl text-[#3C2415]"
                              x-text="'Rp ' + Math.round(hargaSatuan).toLocaleString('id-ID')"></span>
                        <span class="font-sans text-xs text-[#9C8C80] font-bold uppercase tracking-wider">/ pc</span>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Counter [-] [1] [+] --}}
                        <div class="flex items-center gap-1.5 rounded-full bg-[#F5ECE3] border border-[#3C2415]/15 p-1 shadow-xs">
                            <button type="button" @click="kurangiJumlah()"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition active:scale-90 text-base font-bold shadow-xs">
                                &minus;
                            </button>
                            <span class="w-8 text-center font-display font-black text-base text-[#3C2415]" x-text="jumlah"></span>
                            <button type="button" @click="tambahJumlah()"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] transition active:scale-90 text-base font-bold shadow-xs">
                                &plus;
                            </button>
                        </div>

                        {{-- Action Button (ADD TO CART ->) --}}
                        @if (Auth::guard('member')->check())
                            <button type="submit"
                                    :disabled="tidakBisaPesan"
                                    :class="tidakBisaPesan ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-[#3C2415] hover:bg-[#4E2F1B] active:scale-95 cursor-pointer'"
                                    class="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full text-white font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-md">
                                <span x-text="tidakBisaPesan ? labelTidakBisa : '{{ $sedangUbah ? 'UPDATE ORDER' : 'ADD TO CART' }}'">{{ $sedangUbah ? 'UPDATE ORDER' : 'ADD TO CART' }}</span>
                                <svg class="h-3.5 w-3.5 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </button>
                        @else
                            <button type="button" onclick="bukaModalLoginRequired('Ordering')"
                                    class="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-[#3C2415] hover:bg-[#4E2F1B] text-white font-black text-xs uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-md cursor-pointer">
                                <span>LOGIN TO ORDER</span>
                                <svg class="h-3.5 w-3.5 text-[#EACE80] transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Stock warning message if exceeding stock --}}
                <template x-if="stokTidakCukup">
                    <p class="mb-4 text-xs font-bold text-red-600" x-text="pesanStokTidakCukup"></p>
                </template>

                {{-- Order Notes (Optional) --}}
                <div class="mb-6">
                    <label class="block font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-2">
                        Order Notes <span class="font-sans font-normal text-[#9C8C80] normal-case">(optional)</span>
                    </label>
                    <input type="text" x-model="catatan" maxlength="255" placeholder="Special requests, packaging instructions, etc."
                           class="w-full rounded-2xl bg-[#F5ECE3] border border-[#3C2415]/15 px-4 py-2.5 text-xs text-[#3C2415] placeholder:text-[#9C8C80] focus:border-[#8E1E26] focus:ring-1 focus:ring-[#8E1E26] focus:outline-none transition">
                </div>

                @if (Auth::guard('member')->check())
                    </form>
                @endif

                {{-- Product Specs Card (Matches Biscuit Specs Box in Reference, STRICTLY from Database) --}}
                <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/10 space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Category</span>
                        <span class="font-medium text-[#7C6558]">{{ $produk->kategori->nama_kategori ?? 'Artisan Bakes' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Product SKU</span>
                        <span class="font-mono text-[11px] text-[#7C6558]">{{ $produk->kode_produk ?? $produk->detailProduks->first()?->kode_produk ?? ('DB-' . str_pad($produk->id, 4, '0', STR_PAD_LEFT)) }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Stock Availability</span>
                        <span class="font-medium text-[#7C6558]" x-text="isHabis ? 'Sold Out' : (stokTersedia > 0 ? stokTersedia + ' pcs in stock' : 'Freshly Baked Daily')"></span>
                    </div>

                    <template x-if="poinDidapat > 0">
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Daisy Points</span>
                            <span class="font-bold text-[#8E1E26]" x-text="'+' + poinDidapat + ' Points on purchase'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
@endsection