@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Promo Baru" parentTitle="Potongan Harga" :parentUrl="route('promo.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Promo">

        <form action="{{ route('promo.store') }}" method="POST" x-data="{
            jenisPotongan: '{{ old('jenis_potongan', 'persentase') }}',
            persentaseDisplay: '{{ old('persentase') ? str_replace('.', ',', old('persentase')) : '' }}',
            nominalDisplay: '{{ old('nominal_potongan') ? number_format(old('nominal_potongan'), 0, ',', '.') : '' }}',
            minPembelianDisplay: '{{ old('min_pembelian') ? number_format(old('min_pembelian'), 0, ',', '.') : '' }}',
            getRawNumber(val) {
                return (val || '').toString().replace(/\D/g, '') || '0';
            },
            // Format & format-cursor-safe handler for rupiah (ribuan) inputs
            handleRupiahInput(event, prop) {
                const input = event.target;
                const oldValue = input.value;
                const cursorPos = input.selectionStart;
                const digitsBeforeCursor = oldValue.slice(0, cursorPos).replace(/\D/g, '').length;

                const rawDigits = oldValue.replace(/\D/g, '');
                const formatted = rawDigits ? new Intl.NumberFormat('id-ID').format(rawDigits) : '';

                this[prop] = formatted;

                this.$nextTick(() => {
                    let newPos = formatted.length;
                    let count = 0;
                    for (let i = 0; i < formatted.length; i++) {
                        if (/\d/.test(formatted[i])) {
                            count++;
                            if (count === digitsBeforeCursor) {
                                newPos = i + 1;
                                break;
                            }
                        }
                    }
                    if (digitsBeforeCursor === 0) newPos = 0;
                    input.setSelectionRange(newPos, newPos);
                });
            },
            // Format & cursor-safe handler for persentase (allows koma sebagai desimal)
            handlePersentaseInput(event) {
                const input = event.target;
                const oldValue = input.value;
                const cursorPos = input.selectionStart;
                const relevantBeforeCursor = oldValue.slice(0, cursorPos).replace(/[^0-9,]/g, '').length;

                let cleaned = oldValue.replace(/[^0-9,]/g, '');
                const commaIndex = cleaned.indexOf(',');
                if (commaIndex !== -1) {
                    cleaned = cleaned.slice(0, commaIndex + 1) + cleaned.slice(commaIndex + 1).replace(/,/g, '');
                }

                let intPart = commaIndex === -1 ? cleaned : cleaned.slice(0, commaIndex);
                let decPart = commaIndex === -1 ? null : cleaned.slice(commaIndex + 1).slice(0, 2);

                intPart = intPart.slice(0, 3);
                if (intPart !== '' && parseInt(intPart, 10) > 100) intPart = '100';

                let formatted = intPart;
                if (decPart !== null) formatted += ',' + decPart;

                this.persentaseDisplay = formatted;

                this.$nextTick(() => {
                    let newPos = formatted.length;
                    let count = 0;
                    for (let i = 0; i < formatted.length; i++) {
                        if (/[0-9,]/.test(formatted[i])) {
                            count++;
                            if (count === relevantBeforeCursor) {
                                newPos = i + 1;
                                break;
                            }
                        }
                    }
                    if (relevantBeforeCursor === 0) newPos = 0;
                    input.setSelectionRange(newPos, newPos);
                });
            },
            getPersentaseRaw() {
                let v = (this.persentaseDisplay || '').toString();
                if (v === '' || v === ',') return '0';
                v = v.replace(',', '.');
                if (v.endsWith('.')) v = v.slice(0, -1);
                return v;
            },
            semuaProduk: @js($produks->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama_produk' => $p->nama_produk,
                    'kode_produk' => $p->kode_produk,
                    'foto' => $p->foto,
                    'harga_jual' => $p->harga_jual,
                    'punya_ukuran' => (bool) $p->punya_ukuran,
                    'ukurans' => $p->punya_ukuran
                        ? $p->produkUkurans->map(fn ($pu) => ['id' => $pu->ukuran_id, 'nama' => $pu->ukuran->nama_ukuran])->unique('id')->values()
                        : [],
                ];
            })->values()),
            produkTerpilih: @js(old('produk_ids') ? array_map('intval', old('produk_ids')) : []),
            // { produk_id: [ukuran_id, ...] } — checklist ukuran, per produk sendiri-sendiri
            ukuranTerpilihPerProduk: @js(collect(old('ukuran_ids', []))->mapWithKeys(
                fn ($ids, $pid) => [(int) $pid => array_map('intval', $ids ?? [])]
            )->all()),
            get produkTerpilihDenganUkuran() {
                return this.semuaProduk.filter(p => this.produkTerpilih.includes(p.id) && p.punya_ukuran && p.ukurans.length > 0);
            },
            isUkuranTerpilih(produkId, ukuranId) {
                return (this.ukuranTerpilihPerProduk[produkId] || []).includes(ukuranId);
            },
            toggleUkuranProduk(produkId, ukuranId) {
                if (!this.ukuranTerpilihPerProduk[produkId]) this.ukuranTerpilihPerProduk[produkId] = [];
                const list = this.ukuranTerpilihPerProduk[produkId];
                const idx = list.indexOf(ukuranId);
                if (idx === -1) list.push(ukuranId); else list.splice(idx, 1);
            },
            itemsPerPage: '10',
            halamanProduk: 1,
            get totalHalamanProduk() {
                if (this.itemsPerPage === 'semua') return 1;
                const perPage = parseInt(this.itemsPerPage) || 10;
                return Math.max(1, Math.ceil(this.semuaProduk.length / perPage));
            },
            get produkDitampilkan() {
                if (this.itemsPerPage === 'semua') return this.semuaProduk;
                const perPage = parseInt(this.itemsPerPage) || 10;
                const start = (this.halamanProduk - 1) * perPage;
                return this.semuaProduk.slice(start, start + perPage);
            },
            fotoUrl(foto) {
                if (!foto) return '';
                if (foto.startsWith('http://') || foto.startsWith('https://')) return foto;
                return '/storage/' + foto;
            },
            formatNumber(v) {
                return new Intl.NumberFormat('id-ID').format(v);
            },
            pilihSemuaProduk(status) {
                this.produkTerpilih = status ? this.semuaProduk.map(p => p.id) : [];
            }
        }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Nama Promo --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Promo <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nama_promo"
                        value="{{ old('nama_promo') }}"
                        placeholder="Contoh: Promo Merdeka, Diskon Spesial"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_promo')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Jenis Potongan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jenis Potongan Diskon <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4 items-center h-12">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="jenis_potongan" value="persentase" x-model="jenisPotongan" class="text-brand-500 focus:ring-brand-500">
                            Persentase (%)
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="jenis_potongan" value="nominal" x-model="jenisPotongan" class="text-brand-500 focus:ring-brand-500">
                            Nominal Rupiah (Rp)
                        </label>
                    </div>
                    @error('jenis_potongan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nilai Potongan Diskon --}}
                <div>
                    <div x-show="jenisPotongan === 'persentase'">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Persentase Diskon (%) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                x-model="persentaseDisplay"
                                @input="handlePersentaseInput($event)"
                                placeholder="Contoh: 15,5"
                                class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-10 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                                :required="jenisPotongan === 'persentase'">
                            <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">%</span>
                            <input type="hidden" name="persentase" :value="getPersentaseRaw()">
                        </div>
                        @error('persentase')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div x-show="jenisPotongan === 'nominal'" x-cloak>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nominal Diskon (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                            <input
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                x-model="nominalDisplay"
                                @input="handleRupiahInput($event, 'nominalDisplay')"
                                placeholder="5.000"
                                class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold"
                                :required="jenisPotongan === 'nominal'">
                            <input type="hidden" name="nominal_potongan" :value="getRawNumber(nominalDisplay)">
                        </div>
                        @error('nominal_potongan')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Minimum Pembelian --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Minimum Pembelian (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            x-model="minPembelianDisplay"
                            @input="handleRupiahInput($event, 'minPembelianDisplay')"
                            placeholder="50.000"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold">
                        <input type="hidden" name="min_pembelian" :value="getRawNumber(minPembelianDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Minimal total belanja agar promo bisa digunakan (0 = Tanpa Minimum)</span>
                    @error('min_pembelian')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mulai Berlaku <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="mulai_berlaku"
                        value="{{ old('mulai_berlaku', now()->format('Y-m-d\TH:i')) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500"
                        required>
                    @error('mulai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selesai Berlaku <span class="text-xs text-gray-400 font-normal"></span>
                    </label>
                    <input
                        type="datetime-local"
                        name="selesai_berlaku"
                        value="{{ old('selesai_berlaku') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan jika promo berlaku selamanya</span>
                    @error('selesai_berlaku')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif Promo --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Promo
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Promo Aktif</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Pilih Produk --}}
            <div class="mb-8">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Produk Yang Diberi Promo <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0">
                            <span>Tampilkan:</span>
                            <select x-model="itemsPerPage" @change="halamanProduk = 1"
                                class="bg-transparent font-bold text-amber-600 dark:text-amber-400 focus:outline-none cursor-pointer">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="semua">Semua</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="pilihSemuaProduk(true)" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Pilih Semua</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="pilihSemuaProduk(false)" class="text-xs font-semibold text-gray-500 hover:text-gray-600">Hapus Semua</button>
                        </div>
                    </div>
                </div>

                @error('produk_ids')
                    <div class="mb-4"><small class="text-red-500">{{ $message }}</small></div>
                @enderror

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="produk in produkDitampilkan" :key="produk.id">
                        <label class="relative flex flex-col bg-gray-50 dark:bg-gray-800/40 border rounded-xl p-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150 select-none"
                            :class="produkTerpilih.includes(produk.id) ? 'border-brand-500 ring-2 ring-brand-500' : 'border-gray-200 dark:border-gray-700/80'">
                            <input
                                type="checkbox"
                                name="produk_ids[]"
                                :value="produk.id"
                                x-model.number="produkTerpilih"
                                class="produk-checkbox absolute top-3 right-3 rounded-full border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">

                            <div class="w-full aspect-square rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
                                <template x-if="produk.foto">
                                    <img :src="fotoUrl(produk.foto)" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!produk.foto">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xxs font-bold uppercase bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 text-amber-700 dark:text-amber-400"
                                        x-text="produk.nama_produk.substring(0,2)"></div>
                                </template>
                            </div>

                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-2 leading-tight" x-text="produk.nama_produk"></h4>
                                    <span class="text-xxs text-gray-400 dark:text-gray-500 mt-1 block" x-text="produk.kode_produk"></span>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>

                <div x-show="semuaProduk.length === 0" class="py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
                    Belum ada data produk
                </div>

                <!-- Product Pagination Bar -->
                <div x-show="itemsPerPage !== 'semua' && totalHalamanProduk > 1"
                    class="flex flex-wrap items-center justify-between bg-gray-50 dark:bg-gray-800/40 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 mt-4 text-xs gap-2">
                    <span class="text-gray-500 dark:text-gray-400">
                        Menampilkan <span class="font-bold text-gray-800 dark:text-white" x-text="produkDitampilkan.length"></span> dari
                        <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
                        (<span x-text="produkTerpilih.length"></span> dipilih)
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="if(halamanProduk > 1) halamanProduk--" :disabled="halamanProduk <= 1"
                            class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                            &laquo; Prev
                        </button>
                        <span class="px-2 font-bold text-amber-600 dark:text-amber-400" x-text="halamanProduk + ' / ' + totalHalamanProduk"></span>
                        <button type="button" @click="if(halamanProduk < totalHalamanProduk) halamanProduk++" :disabled="halamanProduk >= totalHalamanProduk"
                            class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                            Next &raquo;
                        </button>
                    </div>
                </div>
                <div x-show="itemsPerPage === 'semua'" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan seluruh <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
                    (<span x-text="produkTerpilih.length"></span> dipilih)
                </div>
            </div>

            {{-- Berlaku Untuk Ukuran — checklist beda-beda per produk yang dipilih --}}
            <div class="mb-8" x-show="produkTerpilihDenganUkuran.length > 0" x-cloak>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Berlaku Untuk Ukuran
                </label>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                    Tiap produk ber-ukuran punya checklist ukurannya sendiri. Kosongkan (jangan centang apa pun) pada
                    salah satu produk supaya promo berlaku ke semua ukuran produk itu.
                </p>

                <div class="space-y-3">
                    <template x-for="produk in produkTerpilihDenganUkuran" :key="'ukuran-' + produk.id">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">Produk:</span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="produk.nama_produk"></span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="ukuran in produk.ukurans" :key="produk.id + '-' + ukuran.id">
                                    <label class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer select-none text-sm font-medium transition"
                                        :class="isUkuranTerpilih(produk.id, ukuran.id) ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                        <input
                                            type="checkbox"
                                            :name="'ukuran_ids[' + produk.id + '][]'"
                                            :value="ukuran.id"
                                            :checked="isUkuranTerpilih(produk.id, ukuran.id)"
                                            @change="toggleUkuranProduk(produk.id, ukuran.id)"
                                            class="rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">
                                        <span x-text="ukuran.nama"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                @error('ukuran_ids')
                    <small class="text-red-500 block mt-2">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('promo.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Promo
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

@endsection