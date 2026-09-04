@extends('mobile.layout', ['title' => $bundling->nama_bundling])

@php
    $sedangUbah = (bool) $itemEdit;
    $isKhusus = $bundling->jenis_produk === 'khusus_bundling';
    $targetQtyKhusus = (int) ($bundling->total_qty_khusus ?: $bundling->items->sum('qty'));

    // Inisialisasi awal untuk Alpine JS
    $initialVarian = [];
    $initialVarianCatatan = [];
    if ($isKhusus) {
        if ($itemEdit && !empty($itemEdit['pilihan_khusus'])) {
            foreach ($itemEdit['pilihan_khusus'] as $p) {
                if (isset($p['produk_id'])) {
                    $initialVarian[(int) $p['produk_id']] = (int) ($p['qty'] ?? 0);
                    $initialVarianCatatan[(int) $p['produk_id']] = $p['catatan'] ?? '';
                }
            }
        } else {
            foreach ($bundling->items as $bi) {
                $initialVarian[(int) $bi->produk_id] = 0;
            }
        }
    }
@endphp

@section('content')

    <div x-data="{
                    jumlah: {{ $itemEdit['jumlah'] ?? 1 }},
                    catatan: @js($itemEdit['catatan'] ?? ''),
                    hargaSatuan: {{ (float) $bundling->harga }},
                    habis: {{ $habis ? 'true' : 'false' }},
                    isKhusus: {{ $isKhusus ? 'true' : 'false' }},
                    targetQtyKhusus: {{ $targetQtyKhusus }},
                    varianQty: @js($initialVarian),
                    varianCatatan: @js($initialVarianCatatan),

                    get totalVarianTerpilih() {
                        if (!this.isKhusus) return 0;
                        return Object.values(this.varianQty).reduce((a, b) => a + (parseInt(b) || 0), 0);
                    },

                    get bisaSimpan() {
                        if (!this.isKhusus) return true;
                        return this.totalVarianTerpilih === this.targetQtyKhusus;
                    },

                    get sisaPilihan() {
                        return Math.max(0, this.targetQtyKhusus - this.totalVarianTerpilih);
                    },

                    tambahVarian(id) {
                        if (this.totalVarianTerpilih < this.targetQtyKhusus) {
                            this.varianQty[id] = (this.varianQty[id] || 0) + 1;
                        }
                    },

                    kurangiVarian(id) {
                        if ((this.varianQty[id] || 0) > 0) {
                            this.varianQty[id]--;
                        }
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
                    }
                }" class="relative lg:max-w-5xl lg:mx-auto lg:grid lg:grid-cols-12 lg:gap-10 lg:items-start">

        {{-- Left column: 1:1 photo + info (nama, harga, deskripsi) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-gray-50 sm:-mx-8 md:-mx-10 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:aspect-square lg:w-full">

                @if ($bundling->gambar)
                    <img src="{{ asset('storage/' . $bundling->gambar) }}" alt="{{ $bundling->nama_bundling }}"
                        class="h-full w-full object-cover"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                @endif

                <div class="{{ $bundling->gambar ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                    @include('mobile.components.icon-cup', ['class' => 'h-20 w-20'])
                </div>

                <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-md transition active:scale-95 active:bg-gray-50"
                    aria-label="Kembali">
                    <span class="rotate-180">
                        @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                    </span>
                </a>

                <span class="absolute left-4 top-16 rounded-full bg-amber-600 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                    Bundling
                </span>

                @if ($habis)
                    <span class="absolute left-4 bottom-4 rounded-full bg-gray-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">
                        Perlu Diproduksi
                    </span>
                @endif

            </div>

            {{-- Nama, kategori, harga --}}
            <div class="pt-1">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    Paket Bundling {{ $isKhusus ? 'Pilihan Varian' : '' }}
                </p>

                <h2 class="mt-1 text-lg lg:text-xl font-extrabold leading-snug text-gray-900">
                    {{ $bundling->nama_bundling }}
                </h2>

                <div class="mt-2 flex items-center gap-2">
                    <span class="text-base lg:text-lg font-extrabold text-gray-900">
                        Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if ($habis)
                <div class="mt-3 flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800">
                    @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                    <p class="text-[11px] font-semibold leading-relaxed">
                        Salah satu isi paket ini stoknya sedang kurang/habis, pesanan tetap bisa dibuat & akan
                        diproduksi dulu di dapur — waktu tunggu bisa lebih lama dari biasanya.
                    </p>
                </div>
            @endif
        </div>

        {{-- Right column: deskripsi, varian/isi paket & order form --}}
        <div class="mt-5 lg:col-span-7 lg:mt-0 space-y-4">

            @if ($bundling->deskripsi)
                <div class="lg:rounded-2xl lg:border lg:border-gray-100 lg:bg-gray-50/50 lg:p-4">
                    <h3 class="hidden lg:block text-xs font-bold text-gray-900 mb-1">Deskripsi Paket</h3>
                    <p class="text-xs leading-relaxed text-gray-500 lg:text-gray-600">
                        {{ $bundling->deskripsi }}
                    </p>
                </div>
            @endif

            {{-- Isi paket & Pemilihan Varian Rasa --}}
            <div>
                @if($isKhusus)
                    <div class="flex items-center justify-between rounded-xl bg-amber-50 p-3 text-amber-900 border border-amber-200">
                        <div>
                            <h3 class="text-xs font-bold">
                                Pilih Varian Rasa
                            </h3>
                            <p class="text-[11px] text-amber-700">
                                Wajib memilih total <strong class="font-extrabold">{{ $targetQtyKhusus }} Pcs</strong> varian rasa
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold" 
                                  :class="totalVarianTerpilih === targetQtyKhusus ? 'text-green-700' : 'text-amber-800'"
                                  x-text="totalVarianTerpilih + ' / ' + targetQtyKhusus + ' Pcs'"></span>
                        </div>
                    </div>

                    <div x-show="totalVarianTerpilih !== targetQtyKhusus" class="mt-2 text-[11px] font-medium text-amber-700">
                        <span x-show="totalVarianTerpilih < targetQtyKhusus">Pilih <strong x-text="sisaPilihan"></strong> Pcs varian rasa lagi.</span>
                        <span x-show="totalVarianTerpilih > targetQtyKhusus" class="text-red-600">Kelebihan varian rasa yang dipilih!</span>
                    </div>

                    <div class="mt-3 divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white shadow-xs">
                        @foreach ($bundling->items as $index => $bundlingItem)
                            @if($bundlingItem->produk)
                                <div class="p-3.5 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-gray-800">
                                                {{ $bundlingItem->produk->nama_produk }}
                                            </p>
                                        </div>

                                        {{-- Controller Varian Counter (+/-) --}}
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="kurangiVarian({{ $bundlingItem->produk_id }})"
                                                class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 text-gray-700 transition active:scale-90 disabled:opacity-30"
                                                :disabled="(varianQty[{{ $bundlingItem->produk_id }}] || 0) <= 0">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                            </button>

                                            <span class="w-5 text-center text-xs font-bold text-gray-900"
                                                x-text="varianQty[{{ $bundlingItem->produk_id }}] || 0"></span>

                                            <button type="button" @click="tambahVarian({{ $bundlingItem->produk_id }})"
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-800 text-white shadow-xs transition active:scale-90 disabled:opacity-30"
                                                :disabled="totalVarianTerpilih >= targetQtyKhusus">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="(varianQty[{{ $bundlingItem->produk_id }}] || 0) > 0" x-transition class="mt-1">
                                        <input type="text"
                                               x-model="varianCatatan[{{ $bundlingItem->produk_id }}]"
                                               placeholder="Catatan untuk {{ $bundlingItem->produk->nama_produk }} (opsional)"
                                               maxlength="255"
                                               class="w-full rounded-xl border border-gray-200 px-3 py-1.5 text-[11px] text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                @else

                    <h3 class="text-xs font-bold text-gray-900">
                        Isi Paket
                    </h3>

                    <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                        @foreach ($bundling->items as $bundlingItem)
                            <div class="flex items-center justify-between p-3">
                                <p class="text-xs font-semibold text-gray-800">{{ $bundlingItem->produk->nama_produk ?? 'Produk tidak ditemukan' }}</p>
                                <span class="shrink-0 text-xs font-bold text-gray-500">x{{ $bundlingItem->qty }}</span>
                            </div>
                        @endforeach
                    </div>

                @endif
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-900">
                    Catatan Pesanan
                    <span class="font-medium text-gray-400">
                        (opsional)
                    </span>
                </h3>

                <textarea x-model="catatan" rows="2" maxlength="255" placeholder="Contoh: kurangi manis, dll."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-gray-100 bg-white px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 lg:static lg:w-full lg:border-t-0 lg:px-0 lg:pt-4 lg:shadow-none">

            @if (Auth::guard('member')->check())

                @if ($sedangUbah)

                    <form method="POST" action="{{ route('member.keranjang.perbarui-bundling', $itemEdit['id']) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="bundling_id" value="{{ $bundling->id }}">
                        <input type="hidden" name="jumlah" :value="jumlah">
                        <input type="hidden" name="catatan" :value="catatan">

                        @if($isKhusus)
                            @foreach ($bundling->items as $index => $bi)
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][produk_id]" value="{{ $bi->produk_id }}">
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty[{{ $bi->produk_id }}] || 0">
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan[{{ $bi->produk_id }}] || ''">
                            @endforeach
                        @endif

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

                            <button type="submit"
                                :disabled="!bisaSimpan"
                                class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed"
                                x-text="bisaSimpan ? ('Update – Rp ' + total.toLocaleString('id-ID')) : 'Pilih Varian Dulu'">
                            </button>

                        </div>

                    </form>

                @else

                <form method="POST" action="{{ route('member.keranjang.tambah-bundling', $bundling) }}"
                    onsubmit="event.preventDefault(); kirimTambahKeranjang(this);">
                    @csrf

                    <input type="hidden" name="jumlah" :value="jumlah">
                    <input type="hidden" name="catatan" :value="catatan">

                    @if($isKhusus)
                        @foreach ($bundling->items as $index => $bi)
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][produk_id]" value="{{ $bi->produk_id }}">
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty[{{ $bi->produk_id }}] || 0">
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan[{{ $bi->produk_id }}] || ''">
                        @endforeach
                    @endif

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

                        <button type="submit"
                            :disabled="!bisaSimpan"
                            class="flex min-w-0 flex-1 lg:max-w-[260px] items-center justify-between rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="bisaSimpan ? 'Add' : 'Pilih Varian Dulu'"></span>
                            <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
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