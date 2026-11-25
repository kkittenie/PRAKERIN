@extends('mobile.layout', ['title' => $bundling->nama_bundling])

@php
    $sedangUbah = (bool) $itemEdit;
    $isKhusus = $bundling->isBebas();
    $targetQtyKhusus = $bundling->targetQtyBebas();

    // Bundling bebas: admin menentukan produk + ukuran (satu baris isi), pembeli memilih rasanya.
    // $kelompokRasa = satu kelompok per baris isi, berisi rasa aktif yang boleh dipilih.
    // Kunci pilihan = "i{id baris isi}_d{id rasa}", jadi rasa berbeda dari produk yang sama dihitung terpisah.
    $kelompokRasa = [];
    $adaRasaDiBundling = false;
    $daftarPilihan = []; // urutan datar, dipakai untuk input form
    $initialVarian = [];
    $initialVarianCatatan = [];

    if ($isKhusus) {
        foreach ($bundling->items as $bi) {
            if (!$bi->produk) {
                continue;
            }
            $opsi = [];
            foreach ($bi->opsiRasa() as $dp) {
                $kunci = 'i' . $bi->id . '_d' . $dp->id;
                $opsi[] = [
                    'kunci' => $kunci,
                    'item_id' => (int) $bi->id,
                    'detail_id' => (int) $dp->id,
                    'rasa' => $dp->variasi ?: 'Standar',
                    // false = produk tanpa varian rasa (cuma "Standar"), jadi label rasa tidak perlu ditampilkan.
                    'ada_rasa' => trim((string) $dp->variasi) !== '',
                    'stok' => max(0, (int) $dp->stok),
                ];
                $daftarPilihan[] = end($opsi);
                $initialVarian[$kunci] = 0;
                $initialVarianCatatan[$kunci] = '';
            }
            $kelompokRasa[] = [
                'produk' => $bi->produk->nama_produk,
                'ukuran' => $bi->ukuran,
                'ada_rasa' => collect($opsi)->contains('ada_rasa', true),
                'opsi' => $opsi,
            ];
        }

        $adaRasaDiBundling = collect($kelompokRasa)->contains('ada_rasa', true);

        if ($itemEdit && !empty($itemEdit['pilihan_khusus'])) {
            foreach ($bundling->petaPilihanBebas($itemEdit['pilihan_khusus']) as $p) {
                $kunci = 'i' . $p['item_id'] . '_d' . $p['detail_produk_id'];
                $initialVarian[$kunci] = (int) $p['qty'];
                $initialVarianCatatan[$kunci] = $p['catatan'] ?? '';
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
                    maksPaket: {{ $maksPaket === null ? -1 : (int) $maksPaket }},
                    terpakai: {{ (int) $terpakaiDiKeranjang }},

                    get stokTidakCukup() {
                        return !this.habis && this.maksPaket >= 0 && (this.jumlah + this.terpakai) > this.maksPaket;
                    },

                    get pesanStokTidakCukup() {
                        if (!this.stokTidakCukup) return '';
                        let pesan = 'Insufficient stock. Only ' + this.maksPaket + ' package(s) available';
                        if (this.terpakai >= this.maksPaket) {
                            return pesan + ' and all are already in your cart.';
                        }
                        return this.terpakai > 0
                            ? pesan + ', ' + this.terpakai + ' package(s) already in your cart.'
                            : pesan + '.';
                    },

                    get tidakBisaPesan() {
                        return this.habis || this.stokTidakCukup;
                    },

                    get labelTidakBisa() {
                        if (this.habis) return 'Sold Out';
                        if (this.stokTidakCukup) return 'Out of Stock';
                        return '';
                    },
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
                }" class="relative w-full">

        {{-- ━━━━ MOBILE / TABLET VIEW (< 1024px) ━━━━ --}}
        <div class="lg:hidden relative">

        {{-- Left column: 1:1 photo + info (nama, harga, deskripsi) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">
            <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-[#FAF5E8] flex items-center justify-center p-6 sm:-mx-8 md:-mx-10 lg:mx-0 lg:mt-0 lg:rounded-2xl lg:aspect-square lg:w-full">

                @if ($bundling->foto)
                    <img src="{{ asset('storage/' . $bundling->foto) }}" alt="{{ $bundling->nama_bundling }}"
                        class="max-h-full max-w-full object-contain filter drop-shadow-md"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                @endif

                <div class="{{ $bundling->foto ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                    @include('mobile.components.icon-cup', ['class' => 'h-20 w-20'])
                </div>

                <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
                    onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                    class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-md transition active:scale-95 active:bg-gray-50"
                    aria-label="Back">
                    <span class="rotate-180">
                        @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                    </span>
                </a>

                <span class="absolute left-4 top-16 rounded-full bg-amber-600 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                    Bundling
                </span>

                @if ($habis)
                    <span class="absolute left-4 bottom-4 rounded-full bg-gray-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">
                        Sold Out
                    </span>
                @endif

            </div>

            {{-- Nama, kategori, harga --}}
            <div class="pt-1">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    Bundling Package {{ $isKhusus ? '(Custom Selection)' : '' }}
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

        </div>

        {{-- Right column: deskripsi, varian/isi paket & order form --}}
        <div class="mt-2 lg:col-span-7 lg:mt-0 space-y-4">

            @if ($bundling->deskripsi)
                <div class="lg:rounded-2xl lg:border lg:border-gray-100 lg:bg-gray-50/50 lg:p-4">
                    <h3 class="hidden lg:block text-xs font-bold text-gray-900 mb-1">Package Details</h3>
                    <p class="text-xs leading-relaxed text-gray-500 lg:text-gray-600 whitespace-pre-line">
                        {{ $bundling->deskripsi }}
                    </p>
                </div>
            @endif

            @if ($habis)
                <div class="flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800">
                    @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                    <p class="text-[11px] font-semibold leading-relaxed">Currently being freshly baked
                    </p>
                </div>
            @endif

            {{-- Isi paket & Pemilihan Varian Rasa --}}
            <div>
                @if($isKhusus)
                    <div class="flex items-center justify-between rounded-xl bg-amber-50 p-3 text-amber-900 border border-amber-200">
                        <div>
                            <h3 class="text-xs font-bold">
                                Choose Bundle Items
                            </h3>
                            <p class="text-[11px] text-amber-700">
                                {{ $adaRasaDiBundling ? 'Select flavor' : 'Select items' }}, total <strong class="font-extrabold">{{ $targetQtyKhusus }} pcs</strong> from the list below
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold" 
                                  :class="totalVarianTerpilih === targetQtyKhusus ? 'text-green-700' : 'text-amber-800'"
                                  x-text="totalVarianTerpilih + ' / ' + targetQtyKhusus + ' Pcs'"></span>
                        </div>
                    </div>

                    <div x-show="totalVarianTerpilih !== targetQtyKhusus" class="mt-2 text-[11px] font-medium text-amber-700">
                        <span x-show="totalVarianTerpilih < targetQtyKhusus">Select <strong x-text="sisaPilihan"></strong> more pcs.</span>
                        <span x-show="totalVarianTerpilih > targetQtyKhusus" class="text-red-600">Selection exceeds bundle capacity!</span>
                    </div>

                    <div class="mt-3 space-y-3">
                        @foreach ($kelompokRasa as $kelompok)
                            @if (! $kelompok['ada_rasa'])
                                {{-- Produk tanpa varian rasa: satu baris ringkas (nama + tombol +/-), tanpa header & ruang kosong. --}}
                                <div class="divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white px-3.5 shadow-xs">
                                    @forelse ($kelompok['opsi'] as $opsi)
                                        <div class="py-2.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <p class="text-xs font-bold text-gray-800">{{ $kelompok['produk'] }}</p>
                                                        @if ($kelompok['ukuran'])
                                                            <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-800">{{ $kelompok['ukuran'] }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($opsi['stok'] <= 0)
                                                        <p class="text-[10px] font-semibold text-amber-600">Baking soon</p>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    <button type="button" @click="kurangiVarian('{{ $opsi['kunci'] }}')"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 text-gray-700 transition active:scale-90 disabled:opacity-30"
                                                        :disabled="(varianQty['{{ $opsi['kunci'] }}'] || 0) <= 0">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>

                                                    <span class="w-5 text-center text-xs font-bold text-gray-900"
                                                        x-text="varianQty['{{ $opsi['kunci'] }}'] || 0"></span>

                                                    <button type="button" @click="tambahVarian('{{ $opsi['kunci'] }}')"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-800 text-white shadow-xs transition active:scale-90 disabled:opacity-30"
                                                        :disabled="totalVarianTerpilih >= targetQtyKhusus">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div x-show="(varianQty['{{ $opsi['kunci'] }}'] || 0) > 0" x-transition class="mt-2">
                                                <input type="text"
                                                       x-model="varianCatatan['{{ $opsi['kunci'] }}']"
                                                       placeholder="Notes for {{ $kelompok['produk'] }} (optional)"
                                                       maxlength="255"
                                                       class="w-full rounded-xl border border-gray-200 px-3 py-1.5 text-[11px] text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none">
                                            </div>
                                        </div>
                                    @empty
                                        <p class="py-2.5 text-[11px] font-medium text-gray-400">{{ $kelompok['produk'] }}: unavailable for this selection.</p>
                                    @endforelse
                                </div>
                            @else
                            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs">
                                <div class="flex flex-wrap items-center gap-1.5 bg-gray-50 px-3.5 py-2.5">
                                    <p class="text-xs font-bold text-gray-800">{{ $kelompok['produk'] }}</p>
                                    @if ($kelompok['ukuran'])
                                        <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-800">
                                            {{ $kelompok['ukuran'] }}
                                        </span>
                                    @endif
                                    @if ($kelompok['ada_rasa'])
                                        <span class="text-[10px] font-semibold text-gray-400">· choose flavor</span>
                                    @endif
                                </div>

                                <div class="divide-y divide-gray-100">
                                    @forelse ($kelompok['opsi'] as $opsi)
                                        <div class="p-3.5 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <div class="min-w-0 flex-1 pr-2">
                                                    @if ($opsi['ada_rasa'])
                                                        <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-bold text-amber-800">
                                                            Flavor: {{ $opsi['rasa'] }}
                                                        </span>
                                                    @endif
                                                    @if ($opsi['stok'] <= 0)
                                                        <span class="ml-1 text-[10px] font-semibold text-amber-600">Baking soon</span>
                                                    @endif
                                                </div>

                                                {{-- Controller rasa (+/-) --}}
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <button type="button" @click="kurangiVarian('{{ $opsi['kunci'] }}')"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 text-gray-700 transition active:scale-90 disabled:opacity-30"
                                                        :disabled="(varianQty['{{ $opsi['kunci'] }}'] || 0) <= 0">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>

                                                    <span class="w-5 text-center text-xs font-bold text-gray-900"
                                                        x-text="varianQty['{{ $opsi['kunci'] }}'] || 0"></span>

                                                    <button type="button" @click="tambahVarian('{{ $opsi['kunci'] }}')"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-800 text-white shadow-xs transition active:scale-90 disabled:opacity-30"
                                                        :disabled="totalVarianTerpilih >= targetQtyKhusus">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div x-show="(varianQty['{{ $opsi['kunci'] }}'] || 0) > 0" x-transition class="mt-1">
                                                <input type="text"
                                                       x-model="varianCatatan['{{ $opsi['kunci'] }}']"
                                                       placeholder="Notes for {{ $kelompok['produk'] }}{{ $opsi['ada_rasa'] ? ' '.$opsi['rasa'] : '' }} (optional)"
                                                       maxlength="255"
                                                       class="w-full rounded-xl border border-gray-200 px-3 py-1.5 text-[11px] text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none">
                                            </div>
                                        </div>
                                    @empty
                                        <p class="p-3.5 text-[11px] font-medium text-gray-400">No flavors available for this selection.</p>
                                    @endforelse
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                @else

                    <h3 class="text-xs font-bold text-gray-900">
                        Package Contents
                    </h3>

                    <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                        @foreach ($bundling->items as $bundlingItem)
                            @php
                                $dp = $bundlingItem->detailProduk;
                                $ukuran = $dp?->ukuran ?: $bundlingItem->ukuran;
                                $rasa = $dp?->variasi ?: $bundlingItem->varian_rasa;
                                if (!$ukuran && !$rasa && $bundlingItem->produk && $bundlingItem->produk->detailProduks && $bundlingItem->produk->detailProduks->isNotEmpty()) {
                                    $firstDp = $bundlingItem->produk->detailProduks->first();
                                    $ukuran = $firstDp->ukuran;
                                    $rasa = $firstDp->variasi;
                                }
                            @endphp
                            <div class="flex items-center justify-between p-3">
                                <div class="min-w-0 flex-1 pr-2">
                                    <p class="text-xs font-semibold text-gray-800">{{ $bundlingItem->produk->nama_produk ?? 'Product not found' }}</p>
                                    @if ($ukuran || $rasa)
                                        <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                            @if ($ukuran)
                                                <span class="rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-bold text-brand-800">
                                                    {{ $ukuran }}
                                                </span>
                                            @endif
                                            @if ($rasa)
                                                <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">
                                                    Flavor: {{ $rasa }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <span class="shrink-0 text-xs font-bold text-gray-500">x{{ $bundlingItem->qty }}</span>
                            </div>
                        @endforeach
                    </div>

                @endif
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-900">
                    Order Notes
                    <span class="font-medium text-gray-400">
                        (optional)
                    </span>
                </h3>

                <textarea x-model="catatan" rows="2" maxlength="255" placeholder="e.g. Less sweet, extra napkins, etc."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>
            </div>

            {{-- Spacer pas di bawah kolom catatan agar scroll pas sampai batas kolom catatan --}}
            <div class="h-24 lg:hidden" aria-hidden="true"></div>

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-[#3C2415]/10 bg-[#FAF5E8]/95 backdrop-blur-md px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 lg:static lg:w-full lg:border-t-0 lg:px-0 lg:pt-4 lg:shadow-none">

            {{-- Pesan kalau jumlah paket yang diminta melebihi sisa stok --}}
            <template x-if="stokTidakCukup">
                <p class="mb-2 text-[11px] font-semibold leading-snug text-red-600" x-text="pesanStokTidakCukup"></p>
            </template>

            @if (Auth::guard('member')->check())

                @if ($sedangUbah)

                    <form method="POST" action="{{ route('member.keranjang.perbarui-bundling', $itemEdit['id']) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="bundling_id" value="{{ $bundling->id }}">
                        <input type="hidden" name="jumlah" :value="jumlah">
                        <input type="hidden" name="catatan" :value="catatan">

                        @if($isKhusus)
                            @foreach ($daftarPilihan as $index => $pil)
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][item_id]" value="{{ $pil['item_id'] }}">
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][detail_produk_id]" value="{{ $pil['detail_id'] }}">
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty['{{ $pil['kunci'] }}'] || 0">
                                <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan['{{ $pil['kunci'] }}'] || ''">
                            @endforeach
                        @endif

                        <div class="flex items-center gap-3">

                            <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                                <button type="button" @click="kurangiJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                    aria-label="Decrease quantity">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    </svg>
                                </button>

                                <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                    x-text="jumlah"></span>

                                <button type="button" @click="tambahJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                    aria-label="Increase quantity">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <button type="submit"
                                :disabled="tidakBisaPesan || !bisaSimpan"
                                class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed"
                                x-text="tidakBisaPesan ? labelTidakBisa : (bisaSimpan ? ('Update – Rp ' + total.toLocaleString('id-ID')) : 'Complete Selection First')">
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
                        @foreach ($daftarPilihan as $index => $pil)
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][item_id]" value="{{ $pil['item_id'] }}">
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][detail_produk_id]" value="{{ $pil['detail_id'] }}">
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty['{{ $pil['kunci'] }}'] || 0">
                            <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan['{{ $pil['kunci'] }}'] || ''">
                        @endforeach
                    @endif

                    <div class="flex items-center gap-3">

                        <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                            <button type="button" @click="kurangiJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                aria-label="Decrease quantity">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                </svg>
                            </button>

                            <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                x-text="jumlah"></span>

                            <button type="button" @click="tambahJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                aria-label="Increase quantity">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>

                        <button type="submit"
                            :disabled="tidakBisaPesan || !bisaSimpan"
                            class="flex min-w-0 flex-1 lg:max-w-[260px] items-center justify-between rounded-full bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="tidakBisaPesan ? labelTidakBisa : (bisaSimpan ? 'Add' : 'Complete Selection First')"></span>
                            <span x-show="!tidakBisaPesan" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </button>

                    </div>

                </form>

                @endif

            @else

                <button type="button" onclick="bukaModalLoginRequired('Ordering')"
                    class="flex w-full items-center justify-between rounded-2xl bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                    <span>Sign In to Order</span>
                    <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                </button>

            @endif

        </div>

    </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         DESKTOP VIEW (>= 1024px) — DAISY BAKERY BESPOKE BUNDLE SHOWCASE
         2-Column Showcase Hero with Dark Cocoa Photo Box, Specs Biscuit Table,
         and Curated Items / Interactive Flavor Selection
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

        {{-- Main 2-Column Showcase Hero --}}
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 xl:gap-16 items-start">
            {{-- Left: Dark Cocoa Photo Box --}}
            <div class="lg:col-span-6">
                <div class="aspect-square w-full rounded-[36px] bg-[#3C2415] p-10 lg:p-14 flex items-center justify-center relative overflow-hidden shadow-xl">
                    @if ($bundling->foto)
                        <img src="{{ asset('storage/' . $bundling->foto) }}" alt="{{ $bundling->nama_bundling }}"
                             class="max-h-full max-w-full object-contain filter drop-shadow-[0_16px_28px_rgba(0,0,0,0.45)] transition-transform duration-500 hover:scale-105 select-none pointer-events-none">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-[#FAF5E8]/20">
                            @include('mobile.components.icon-cup', ['class' => 'h-28 w-28'])
                        </div>
                    @endif

                    {{-- Badges on Photo --}}
                    <span class="absolute left-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-[#8E1E26] px-4 py-1.5 text-xs font-black uppercase tracking-wider text-white shadow-sm">
                        Value Bundle
                    </span>

                    @if ($bundling->harga_asli > $bundling->harga)
                        <span class="absolute right-6 top-6 inline-flex items-center rounded-full bg-[#2E6F40] px-3.5 py-1 text-xs font-bold text-white shadow-sm">
                            Save {{ $bundling->hemat_persen }}%
                        </span>
                    @endif

                    @if ($habis)
                        <span class="absolute left-6 bottom-6 rounded-full bg-[#3C2415]/90 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-xs">
                            Sold Out
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Bundle Details, Items/Flavors Selection, Price & CTA --}}
            <div class="lg:col-span-6 flex flex-col justify-between">
                @if (Auth::guard('member')->check())
                    @if ($sedangUbah)
                        <form method="POST" action="{{ route('member.keranjang.perbarui-bundling', $itemEdit['id']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="bundling_id" value="{{ $bundling->id }}">
                            <input type="hidden" name="jumlah" :value="jumlah">
                            <input type="hidden" name="catatan" :value="catatan">
                            @if($isKhusus)
                                @foreach ($daftarPilihan as $index => $pil)
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][item_id]" value="{{ $pil['item_id'] }}">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][detail_produk_id]" value="{{ $pil['detail_id'] }}">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty['{{ $pil['kunci'] }}'] || 0">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan['{{ $pil['kunci'] }}'] || ''">
                                @endforeach
                            @endif
                    @else
                        <form method="POST" action="{{ route('member.keranjang.tambah-bundling', $bundling) }}"
                              onsubmit="event.preventDefault(); kirimTambahKeranjang(this);">
                            @csrf
                            <input type="hidden" name="jumlah" :value="jumlah">
                            <input type="hidden" name="catatan" :value="catatan">
                            @if($isKhusus)
                                @foreach ($daftarPilihan as $index => $pil)
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][item_id]" value="{{ $pil['item_id'] }}">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][detail_produk_id]" value="{{ $pil['detail_id'] }}">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][qty]" :value="varianQty['{{ $pil['kunci'] }}'] || 0">
                                    <input type="hidden" name="pilihan_khusus[{{ $index }}][catatan]" :value="varianCatatan['{{ $pil['kunci'] }}'] || ''">
                                @endforeach
                            @endif
                    @endif
                @endif

                {{-- Category Badge --}}
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-md bg-[#FCE8BD] text-[#3C2415] font-display font-black text-xs tracking-wider uppercase mb-3">
                        BUNDLING PACKAGE {{ $isKhusus ? '· CUSTOM SELECTION' : '· CURATED BOX' }}
                    </div>

                    {{-- Bundle Title --}}
                    <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-[#3C2415] tracking-tight leading-tight mb-4">
                        {{ $bundling->nama_bundling }}
                    </h1>

                    {{-- Description --}}
                    @if ($bundling->deskripsi)
                        <p class="font-sans text-sm lg:text-base text-[#7C6558] leading-relaxed mb-6 whitespace-pre-line">
                            {{ $bundling->deskripsi }}
                        </p>
                    @endif
                </div>

                {{-- Status Sold Out / Freshly Baked Banner --}}
                @if ($habis)
                    <div class="flex items-start gap-2.5 rounded-2xl bg-amber-50 border border-amber-200/70 p-4 text-amber-900 mb-6">
                        @include('mobile.components.icon-clock', ['class' => 'h-5 w-5 shrink-0 mt-0.5 text-amber-700'])
                        <p class="text-xs font-semibold leading-relaxed">
                            Currently being freshly prepared in our oven kitchen. Check back shortly!
                        </p>
                    </div>
                @endif

                {{-- Package Contents or Flavor Customization --}}
                <div class="mb-6">
                    @if ($isKhusus)
                        {{-- Custom Selection Header --}}
                        <div class="flex items-center justify-between rounded-2xl bg-[#F5ECE3] border border-[#3C2415]/10 p-4 mb-4">
                            <div>
                                <h3 class="font-display font-bold text-sm text-[#3C2415]">
                                    Choose Bundle Items
                                </h3>
                                <p class="text-xs text-[#7C6558] mt-0.5">
                                    {{ $adaRasaDiBundling ? 'Pick your preferred flavors' : 'Select items' }}, target total: <strong class="font-extrabold text-[#3C2415]">{{ $targetQtyKhusus }} pcs</strong>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-black tracking-wide"
                                      :class="totalVarianTerpilih === targetQtyKhusus ? 'bg-[#2E6F40] text-white' : 'bg-[#EACE80] text-[#3C2415]'"
                                      x-text="totalVarianTerpilih + ' / ' + targetQtyKhusus + ' Pcs'"></span>
                            </div>
                        </div>

                        <div x-show="totalVarianTerpilih !== targetQtyKhusus" class="mb-4 text-xs font-bold">
                            <span x-show="totalVarianTerpilih < targetQtyKhusus" class="text-[#8E1E26]">Select <span x-text="sisaPilihan"></span> more pcs to complete your bundle.</span>
                            <span x-show="totalVarianTerpilih > targetQtyKhusus" class="text-red-600">Selection exceeds bundle capacity!</span>
                        </div>

                        <div class="space-y-3.5">
                            @foreach ($kelompokRasa as $kelompok)
                                <div class="rounded-2xl border border-[#3C2415]/10 bg-[#F5ECE3]/70 p-4">
                                    <div class="flex items-center gap-2 mb-2.5">
                                        <p class="font-display font-bold text-sm text-[#3C2415]">{{ $kelompok['produk'] }}</p>
                                        @if ($kelompok['ukuran'])
                                            <span class="rounded bg-[#FAF5E8] border border-[#3C2415]/10 px-2 py-0.5 text-[10px] font-bold text-[#8E1E26]">{{ $kelompok['ukuran'] }}</span>
                                        @endif
                                        @if ($kelompok['ada_rasa'])
                                            <span class="text-[11px] text-[#7C6558] font-medium">· choose flavor</span>
                                        @endif
                                    </div>

                                    <div class="divide-y divide-[#3C2415]/10">
                                        @forelse ($kelompok['opsi'] as $opsi)
                                            <div class="py-2.5 flex items-center justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    @if ($opsi['ada_rasa'])
                                                        <span class="font-bold text-xs text-[#3C2415]">{{ $opsi['rasa'] }}</span>
                                                    @else
                                                        <span class="font-medium text-xs text-[#3C2415]">Standard</span>
                                                    @endif
                                                    @if ($opsi['stok'] <= 0)
                                                        <span class="ml-2 text-[10px] font-bold text-amber-700 uppercase">Baking soon</span>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    <button type="button" @click="kurangiVarian('{{ $opsi['kunci'] }}')"
                                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white border border-[#3C2415]/20 text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                                            :disabled="(varianQty['{{ $opsi['kunci'] }}'] || 0) <= 0">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>
                                                    <span class="w-6 text-center font-display font-bold text-xs text-[#3C2415]" x-text="varianQty['{{ $opsi['kunci'] }}'] || 0"></span>
                                                    <button type="button" @click="tambahVarian('{{ $opsi['kunci'] }}')"
                                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] shadow-xs transition active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                                            :disabled="totalVarianTerpilih >= targetQtyKhusus">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="py-2 text-xs text-[#9C8C80]">No options available.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Fixed Bundle Package Contents --}}
                        <div class="rounded-2xl border border-[#3C2415]/10 bg-[#F5ECE3] p-5">
                            <h3 class="font-display font-bold text-xs uppercase tracking-wider text-[#3C2415] mb-3">
                                Package Contents
                            </h3>
                            <div class="divide-y divide-[#3C2415]/10">
                                @foreach ($bundling->items as $bundlingItem)
                                    @php
                                        $dp = $bundlingItem->detailProduk;
                                        $ukuran = $dp?->ukuran ?: $bundlingItem->ukuran;
                                        $rasa = $dp?->variasi ?: $bundlingItem->varian_rasa;
                                        if (!$ukuran && !$rasa && $bundlingItem->produk && $bundlingItem->produk->detailProduks && $bundlingItem->produk->detailProduks->isNotEmpty()) {
                                            $firstDp = $bundlingItem->produk->detailProduks->first();
                                            $ukuran = $firstDp->ukuran;
                                            $rasa = $firstDp->variasi;
                                        }
                                    @endphp
                                    <div class="flex items-center justify-between py-2.5">
                                        <div class="min-w-0 flex-1 pr-3">
                                            <p class="font-display font-bold text-sm text-[#3C2415]">{{ $bundlingItem->produk->nama_produk ?? 'Product not found' }}</p>
                                            @if ($ukuran || $rasa)
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if ($ukuran)
                                                        <span class="rounded bg-[#FAF5E8] border border-[#3C2415]/10 px-2 py-0.5 text-[10px] font-bold text-[#8E1E26]">{{ $ukuran }}</span>
                                                    @endif
                                                    @if ($rasa)
                                                        <span class="rounded bg-[#FCE8BD] px-2 py-0.5 text-[10px] font-bold text-[#3C2415]">Flavor: {{ $rasa }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-display font-black text-sm text-[#8E1E26] bg-[#FAF5E8] border border-[#3C2415]/10 rounded-full px-3 py-1">
                                            x{{ $bundlingItem->qty }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Price & CTA Row --}}
                <div class="flex flex-wrap items-center justify-between gap-4 py-5 border-y border-[#3C2415]/10 mb-6">
                    <div class="flex items-baseline gap-2">
                        @if ($bundling->harga_asli > $bundling->harga)
                            <span class="text-sm text-[#9C8C80] line-through font-sans">
                                Rp {{ number_format($bundling->harga_asli, 0, ',', '.') }}
                            </span>
                        @endif
                        <span class="font-display font-black text-3xl xl:text-4xl text-[#3C2415]">
                            Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                        </span>
                        <span class="font-sans text-xs text-[#9C8C80] font-bold uppercase tracking-wider">/ bundle</span>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Quantity Stepper --}}
                        <div class="flex items-center gap-1.5 rounded-full bg-[#F5ECE3] border border-[#3C2415]/15 p-1 shadow-xs">
                            <button type="button" @click="kurangiJumlah()"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-[#3C2415] hover:bg-[#3C2415] hover:text-white transition active:scale-90 text-base font-bold shadow-xs cursor-pointer">
                                &minus;
                            </button>
                            <span class="w-8 text-center font-display font-black text-base text-[#3C2415]" x-text="jumlah"></span>
                            <button type="button" @click="tambahJumlah()"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] transition active:scale-90 text-base font-bold shadow-xs cursor-pointer">
                                &plus;
                            </button>
                        </div>

                        {{-- Action Button --}}
                        @if (Auth::guard('member')->check())
                            <button type="submit"
                                    :disabled="tidakBisaPesan || !bisaSimpan"
                                    :class="(tidakBisaPesan || !bisaSimpan) ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-[#3C2415] hover:bg-[#4E2F1B] active:scale-95 cursor-pointer'"
                                    class="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full text-white font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-md">
                                <span x-text="tidakBisaPesan ? labelTidakBisa : (!bisaSimpan ? 'COMPLETE SELECTION' : '{{ $sedangUbah ? 'UPDATE BUNDLE' : 'ADD TO CART' }}')"></span>
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

                {{-- Stock warning message --}}
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

                {{-- Package Specs Card (Biscuit Box) --}}
                <div class="rounded-[24px] bg-[#F5ECE3] p-6 border border-[#3C2415]/10 space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Package Type</span>
                        <span class="font-medium text-[#7C6558]">{{ $isKhusus ? 'Custom Bundle Selection' : 'Curated Value Package' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Total Items</span>
                        <span class="font-medium text-[#7C6558]">{{ $isKhusus ? ($targetQtyKhusus . ' Pieces') : ($bundling->items->sum('qty') . ' Pieces included') }}</span>
                    </div>

                    @if ($bundling->harga_asli > $bundling->harga)
                        <div class="flex items-center justify-between pb-3 border-b border-[#3C2415]/10">
                            <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Package Savings</span>
                            <span class="font-bold text-[#2E6F40]">Save Rp {{ number_format($bundling->hemat_nominal, 0, ',', '.') }} ({{ $bundling->hemat_persen }}%)</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-0.5">
                        <span class="font-display font-bold uppercase tracking-wider text-[#3C2415]">Preparation</span>
                        <span class="font-medium text-[#7C6558]">Freshly Packed with Care</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection