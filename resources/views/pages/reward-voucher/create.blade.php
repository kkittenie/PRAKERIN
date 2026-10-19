@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Reward Voucher" parentTitle="Reward Voucher" :parentUrl="route('reward-voucher.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Reward Voucher">

        <form action="{{ route('reward-voucher.store') }}" method="POST" enctype="multipart/form-data"
            x-data="{
                jenisPotongan: '{{ old('jenis_potongan', 'nominal') }}',
                targetMember: '{{ old('target_member', 'semua') }}',
                persentaseDisplay: '{{ old('persentase') ? str_replace('.', ',', old('persentase')) : '' }}',
                nominalDisplay: '{{ old('nominal_potongan') ? number_format(old('nominal_potongan'), 0, ',', '.') : '' }}',
                maksimalDisplay: '{{ old('maksimal_potongan') ? number_format(old('maksimal_potongan'), 0, ',', '.') : '' }}',
                minPembelianDisplay: '{{ old('min_pembelian') ? number_format(old('min_pembelian'), 0, ',', '.') : '0' }}',
                getRawNumber(val) {
                    return (val || '').toString().replace(/\D/g, '') || '0';
                },
                getRawNumberNullable(val) {
                    return (val || '').toString().replace(/\D/g, '');
                },
                // Format & cursor-safe handler for rupiah inputs
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
                // Member data & multi-select
                semuaMember: @js($members->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'nama' => $m->nama,
                        'kode_member' => $m->kode_member,
                        'no_hp' => $m->no_hp,
                    ];
                })->values()),
                memberTerpilih: @js(old('member_ids') ? array_map('intval', old('member_ids')) : []),
                cariMemberText: '',
                dropdownMemberBuka: false,
                get listMemberTerpilih() {
                    return this.semuaMember.filter(m => this.memberTerpilih.includes(m.id));
                },
                get memberTersaring() {
                    if (!this.cariMemberText.trim()) return this.semuaMember;
                    const q = this.cariMemberText.toLowerCase();
                    return this.semuaMember.filter(m =>
                        (m.nama && m.nama.toLowerCase().includes(q)) ||
                        (m.no_hp && m.no_hp.toLowerCase().includes(q)) ||
                        (m.kode_member && m.kode_member.toLowerCase().includes(q))
                    );
                },
                toggleMember(id) {
                    const idx = this.memberTerpilih.indexOf(id);
                    if (idx > -1) {
                        this.memberTerpilih.splice(idx, 1);
                    } else {
                        this.memberTerpilih.push(id);
                    }
                },
                pilihSemuaMember(status) {
                    this.memberTerpilih = status ? this.semuaMember.map(m => m.id) : [];
                },
                // Produk data & multi-select
                semuaProduk: @js($produks),
                produkTerpilih: @js(old('produk_ids') ? array_map('intval', old('produk_ids')) : []),
                ukuranTerpilihPerProduk: @js((object) collect(old('ukuran_names', []))->mapWithKeys(fn ($n, $pid) => [(int) $pid => array_values(array_map('strval', (array) $n))])->all()),
                rasaTerpilihPerProduk: @js((object) collect(old('rasa_names', []))->mapWithKeys(fn ($n, $pid) => [(int) $pid => array_values(array_map('strval', (array) $n))])->all()),
                punyaPilihanVarian(p) {
                    return (p.punya_ukuran && p.ukurans.length > 0) || (p.punya_varian_rasa && p.rasas.length > 0);
                },
                get produkTerpilihDenganVarian() {
                    return this.semuaProduk.filter(p => this.produkTerpilih.includes(p.id) && this.punyaPilihanVarian(p));
                },
                isUkuranTerpilih(produkId, namaUkuran) {
                    return (this.ukuranTerpilihPerProduk[produkId] || []).includes(namaUkuran);
                },
                toggleUkuranProduk(produkId, namaUkuran) {
                    if (!this.ukuranTerpilihPerProduk[produkId]) this.ukuranTerpilihPerProduk[produkId] = [];
                    const list = this.ukuranTerpilihPerProduk[produkId];
                    const idx = list.indexOf(namaUkuran);
                    if (idx === -1) list.push(namaUkuran); else list.splice(idx, 1);
                },
                jumlahUkuranTerpilih(produkId) {
                    return (this.ukuranTerpilihPerProduk[produkId] || []).length;
                },
                isRasaTerpilih(produkId, namaRasa) {
                    return (this.rasaTerpilihPerProduk[produkId] || []).includes(namaRasa);
                },
                toggleRasaProduk(produkId, namaRasa) {
                    if (!this.rasaTerpilihPerProduk[produkId]) this.rasaTerpilihPerProduk[produkId] = [];
                    const list = this.rasaTerpilihPerProduk[produkId];
                    const idx = list.indexOf(namaRasa);
                    if (idx === -1) list.push(namaRasa); else list.splice(idx, 1);
                },
                jumlahRasaTerpilih(produkId) {
                    return (this.rasaTerpilihPerProduk[produkId] || []).length;
                },
                modalVarianTerbuka: false,
                modalProdukId: null,
                modalSnapshotUkuran: [],
                modalSnapshotRasa: [],
                bukaModalVarian(produkId) {
                    this.modalProdukId = produkId;
                    this.modalSnapshotUkuran = [...(this.ukuranTerpilihPerProduk[produkId] || [])];
                    this.modalSnapshotRasa = [...(this.rasaTerpilihPerProduk[produkId] || [])];
                    this.modalVarianTerbuka = true;
                },
                batalModalVarian() {
                    if (this.modalProdukId !== null) {
                        this.ukuranTerpilihPerProduk[this.modalProdukId] = [...this.modalSnapshotUkuran];
                        this.rasaTerpilihPerProduk[this.modalProdukId] = [...this.modalSnapshotRasa];
                    }
                    this.modalVarianTerbuka = false;
                    this.modalProdukId = null;
                },
                simpanModalVarian() {
                    this.modalVarianTerbuka = false;
                    this.modalProdukId = null;
                },
                get produkUntukModal() {
                    return this.semuaProduk.find(p => p.id === this.modalProdukId) || null;
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
                    if (!status) {
                        this.ukuranTerpilihPerProduk = {};
                        this.rasaTerpilihPerProduk = {};
                    }
                },
                toggleProdukTerpilih(produk, checked) {
                    if (checked) {
                        if (!this.produkTerpilih.includes(produk.id)) this.produkTerpilih.push(produk.id);
                        if (this.punyaPilihanVarian(produk)) {
                            this.bukaModalVarian(produk.id);
                        }
                    } else {
                        this.produkTerpilih = this.produkTerpilih.filter(id => id !== produk.id);
                        delete this.ukuranTerpilihPerProduk[produk.id];
                        delete this.rasaTerpilihPerProduk[produk.id];
                    }
                }
            }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Voucher <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                        placeholder="Contoh: Voucher Diskon 20%"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" rows="2"
                        placeholder="Contoh: Diskon 20% untuk pembelian minimal Rp 50.000"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Target Member --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Target Member <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-6 items-center">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="target_member" value="semua" x-model="targetMember" class="text-brand-500 focus:ring-brand-500">
                            <span>Umum (Semua Member)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <input type="radio" name="target_member" value="khusus" x-model="targetMember" class="text-brand-500 focus:ring-brand-500">
                            <span>Member Tertentu (Khusus)</span>
                        </label>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Voucher umum otomatis tersedia untuk semua member, voucher khusus hanya tersedia untuk member yang dipilih.</span>
                    @error('target_member')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Multi-Select Member (Select Dropdown dengan Pencarian) --}}
                <div class="md:col-span-2 space-y-2" x-show="targetMember === 'khusus'" x-cloak>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pilih Member Yang Berhak <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400"
                            x-text="memberTerpilih.length + ' member dipilih'"></span>
                    </div>

                    {{-- Dropdown Container --}}
                    <div class="relative" @click.outside="dropdownMemberBuka = false">
                        {{-- Dropdown Trigger Button --}}
                        <button type="button" @click="dropdownMemberBuka = !dropdownMemberBuka"
                            class="min-h-[48px] w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 flex items-center justify-between text-left cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-amber-500 hover:border-amber-500">
                            <div class="flex items-center gap-2 flex-wrap flex-1 min-w-0 pr-2">
                                <template x-if="memberTerpilih.length === 0">
                                    <span class="text-sm text-gray-400">Pilih member yang berhak menerima voucher...</span>
                                </template>
                                <template x-if="memberTerpilih.length > 0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span x-text="memberTerpilih.length + ' Member Terpilih'"></span>
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs"
                                            x-text="listMemberTerpilih.slice(0, 2).map(m => m.nama).join(', ') + (listMemberTerpilih.length > 2 ? ' +' + (listMemberTerpilih.length - 2) + ' lainnya' : '')"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center gap-1 shrink-0 text-gray-400">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': dropdownMemberBuka }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        {{-- Dropdown Menu Box --}}
                        <div x-show="dropdownMemberBuka" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 right-0 z-50 mt-1.5 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-3 space-y-2.5">

                            {{-- Search Bar di dalam dropdown --}}
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" x-model="cariMemberText" placeholder="Cari nama, nomor HP, atau kode member..."
                                    @click.stop
                                    class="w-full h-10 pl-9 pr-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 placeholder-gray-400">
                            </div>

                            {{-- Quick action links --}}
                            <div class="flex items-center justify-between px-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click.stop="pilihSemuaMember(true)" class="font-bold text-amber-700 hover:text-amber-800 dark:text-amber-400">Pilih Semua</button>
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                    <button type="button" @click.stop="pilihSemuaMember(false)" class="font-semibold text-gray-500 hover:text-gray-600 dark:text-gray-400">Hapus Semua</button>
                                </div>
                                <span class="text-[11px] text-gray-400" x-text="memberTersaring.length + ' member ditemukan'"></span>
                            </div>

                            {{-- List Option --}}
                            <div class="max-h-60 overflow-y-auto space-y-1 custom-scrollbar pr-1">
                                <template x-for="member in memberTersaring" :key="member.id">
                                    <div @click.stop="toggleMember(member.id)"
                                        class="flex items-center justify-between p-2.5 rounded-lg border border-transparent hover:border-amber-200 dark:hover:border-amber-800/50 hover:bg-amber-50/60 dark:hover:bg-amber-950/30 cursor-pointer transition select-none"
                                        :class="{ 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200/60 dark:border-amber-800/30': memberTerpilih.includes(member.id) }">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                             <input type="checkbox" :checked="memberTerpilih.includes(member.id)"
                                                @click.stop="toggleMember(member.id)"
                                                class="rounded border-gray-300 text-amber-700 focus:ring-amber-500 pointer-events-none">
                                            <div class="min-w-0">
                                                <span class="block text-xs font-bold text-gray-900 dark:text-white truncate" x-text="member.nama"></span>
                                                <span class="block text-[11px] text-gray-400 dark:text-gray-500" x-text="member.no_hp || '-'"></span>
                                            </div>
                                        </div>
                                        <span class="font-mono text-[10px] text-amber-700 dark:text-amber-400 font-semibold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 shrink-0 ml-2" x-text="member.kode_member"></span>
                                    </div>
                                </template>
                                <div x-show="memberTersaring.length === 0" class="py-6 text-center text-xs text-gray-400">
                                    Member tidak ditemukan.
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex justify-end">
                                <button type="button" @click="dropdownMemberBuka = false"
                                    class="px-3.5 py-1.5 bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs rounded-lg transition shadow-xs">
                                    Selesai
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Render Hidden Inputs untuk Form Submission --}}
                    <template x-for="id in memberTerpilih" :key="'input_mbr_' + id">
                        <input type="hidden" name="member_ids[]" :value="id">
                    </template>

                    {{-- Badges / Tags Member Terpilih di Luar Dropdown (Bisa Dihapus) --}}
                    <div x-show="memberTerpilih.length > 0" class="flex flex-wrap gap-1.5 pt-1">
                        <template x-for="member in listMemberTerpilih" :key="'chip_' + member.id">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-white dark:bg-gray-800 border border-amber-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 shadow-2xs">
                                <span x-text="member.nama"></span>
                                <button type="button" @click="toggleMember(member.id)" class="text-gray-400 hover:text-red-500 font-bold leading-none">&times;</button>
                            </span>
                        </template>
                    </div>

                    @error('member_ids')
                        <small class="text-red-500 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Batas Penggunaan Per Member --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Batas Penggunaan Per Member <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="batas_penggunaan_per_member" min="1" value="{{ old('batas_penggunaan_per_member', 1) }}"
                            placeholder="1"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-16 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">kali</span>
                    </div>
                    @error('batas_penggunaan_per_member')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>


                {{-- Jenis Potongan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jenis Potongan <span class="text-red-500">*</span>
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

                <div x-show="jenisPotongan === 'nominal'" x-cloak>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nominal Potongan (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            x-model="nominalDisplay"
                            @input="handleRupiahInput($event, 'nominalDisplay')"
                            placeholder="10.000"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold"
                            :required="jenisPotongan === 'nominal'">
                        <input type="hidden" name="nominal_potongan" :value="getRawNumber(nominalDisplay)">
                    </div>
                    @error('nominal_potongan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div x-show="jenisPotongan === 'persentase'" x-cloak>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Persentase Potongan (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            inputmode="decimal"
                            autocomplete="off"
                            x-model="persentaseDisplay"
                            @input="handlePersentaseInput($event)"
                            placeholder="Contoh: 10"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-10 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            :required="jenisPotongan === 'persentase'">
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">%</span>
                        <input type="hidden" name="persentase" :value="getPersentaseRaw()">
                    </div>
                    @error('persentase')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div x-show="jenisPotongan === 'persentase'" x-cloak>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Maksimal Potongan (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            x-model="maksimalDisplay"
                            @input="handleRupiahInput($event, 'maksimalDisplay')"
                            placeholder="Kosongkan jika tidak dibatasi"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold">
                        <input type="hidden" name="maksimal_potongan" :value="getRawNumberNullable(maksimalDisplay)">
                    </div>
                    @error('maksimal_potongan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Minimal Belanja (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            x-model="minPembelianDisplay"
                            @input="handleRupiahInput($event, 'minPembelianDisplay')"
                            placeholder="0"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 font-semibold">
                        <input type="hidden" name="min_pembelian" :value="getRawNumber(minPembelianDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Minimal subtotal transaksi agar voucher ini bisa dipakai. Kosongkan/0 = tidak ada minimal.</span>
                    @error('min_pembelian')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Voucher
                    </label>

                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktifkan Sekarang</span>
                    </label>
                    @error('status_aktif')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Gambar Voucher
                    </label>

                    <div class="flex items-center gap-4">
                        <div class="w-24 h-24 shrink-0 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                            <img id="preview-gambar" src="" alt="Preview gambar voucher" class="hidden w-full h-full object-cover">
                            <svg id="icon-gambar-kosong" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <label for="input-gambar"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 cursor-pointer transition">
                                Pilih Gambar
                            </label>
                            <input id="input-gambar" type="file" name="gambar" accept="image/*" class="hidden"
                                onchange="tampilkanPreviewGambar(this)">
                            <p id="nama-file-gambar" class="mt-2 text-xs text-gray-500 dark:text-gray-400 truncate">
                                Belum ada gambar dipilih.
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">Ditampilkan di halaman voucher member. Format jpg/png/webp, maks 2MB.</span>

                    @error('gambar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Pilih Produk --}}
            <div class="mb-8">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Produk Yang Berlaku Untuk Voucher Ini <span class="text-red-500">*</span>
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
                                :checked="produkTerpilih.includes(produk.id)"
                                @change="toggleProdukTerpilih(produk, $event.target.checked)"
                                class="produk-checkbox absolute top-3 right-3 rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">

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
                                <template x-if="produkTerpilih.includes(produk.id) && punyaPilihanVarian(produk)">
                                    <div class="mt-2 space-y-1">
                                        <template x-if="produk.punya_ukuran && produk.ukurans.length > 0">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <template x-if="jumlahUkuranTerpilih(produk.id) === 0">
                                                    <span class="text-[9px] font-semibold text-gray-400 dark:text-gray-500 italic">Semua ukuran</span>
                                                </template>
                                                <template x-for="namaUkuran in (ukuranTerpilihPerProduk[produk.id] || [])" :key="'badge-u-' + produk.id + '-' + namaUkuran">
                                                    <span class="text-[9px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-1.5 py-0.5 rounded-full"
                                                        x-text="namaUkuran"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="produk.punya_varian_rasa && produk.rasas.length > 0">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <template x-if="jumlahRasaTerpilih(produk.id) === 0">
                                                    <span class="text-[9px] font-semibold text-gray-400 dark:text-gray-500 italic">Semua rasa</span>
                                                </template>
                                                <template x-for="namaRasa in (rasaTerpilihPerProduk[produk.id] || [])" :key="'badge-r-' + produk.id + '-' + namaRasa">
                                                    <span class="text-[9px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded-full"
                                                        x-text="namaRasa"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <button type="button" @click.stop.prevent="bukaModalVarian(produk.id)"
                                            class="text-[9px] font-bold text-gray-400 dark:text-gray-500 underline hover:text-brand-600 dark:hover:text-brand-400">
                                            Ubah
                                        </button>
                                    </div>
                                </template>
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

            {{-- Hidden inputs pilihan ukuran & rasa per produk --}}
            <template x-for="produk in produkTerpilihDenganVarian" :key="'hidden-group-' + produk.id">
                <div class="hidden">
                    <template x-for="namaUkuran in (ukuranTerpilihPerProduk[produk.id] || [])" :key="'hidden-u-' + produk.id + '-' + namaUkuran">
                        <input type="hidden" :name="'ukuran_names[' + produk.id + '][]'" :value="namaUkuran">
                    </template>
                    <template x-for="namaRasa in (rasaTerpilihPerProduk[produk.id] || [])" :key="'hidden-r-' + produk.id + '-' + namaRasa">
                        <input type="hidden" :name="'rasa_names[' + produk.id + '][]'" :value="namaRasa">
                    </template>
                </div>
            </template>

            {{-- Modal Pilih Ukuran / Rasa --}}
            <div x-show="modalVarianTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
                <div @click.away="batalModalVarian()" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                    <template x-if="produkUntukModal">
                        <div>
                            <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                                <h3 class="font-extrabold text-gray-900 dark:text-white text-sm"
                                    x-text="(produkUntukModal.punya_ukuran && produkUntukModal.ukurans.length > 0 ? 'Pilih Ukuran' : '') + (produkUntukModal.punya_ukuran && produkUntukModal.ukurans.length > 0 && produkUntukModal.punya_varian_rasa && produkUntukModal.rasas.length > 0 ? ' & ' : '') + (produkUntukModal.punya_varian_rasa && produkUntukModal.rasas.length > 0 ? 'Rasa' : '')"></h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="produkUntukModal.nama_produk"></p>
                            </div>
                            <div class="p-5 max-h-72 overflow-y-auto space-y-5">
                                <template x-if="produkUntukModal.punya_ukuran && produkUntukModal.ukurans.length > 0">
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Berlaku untuk ukuran</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                                            Kosongkan semua supaya voucher berlaku ke semua ukuran produk ini.
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="ukuran in produkUntukModal.ukurans" :key="'m-u-' + ukuran.nama">
                                                <label class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer select-none text-sm font-medium transition"
                                                    :class="isUkuranTerpilih(produkUntukModal.id, ukuran.nama) ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                                    <input type="checkbox"
                                                        :checked="isUkuranTerpilih(produkUntukModal.id, ukuran.nama)"
                                                        @change="toggleUkuranProduk(produkUntukModal.id, ukuran.nama)"
                                                        class="rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">
                                                    <span x-text="ukuran.nama"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="produkUntukModal.punya_varian_rasa && produkUntukModal.rasas.length > 0">
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Berlaku untuk rasa</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                                            Kosongkan semua supaya voucher berlaku ke semua rasa produk ini.
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="rasa in produkUntukModal.rasas" :key="'m-r-' + rasa.nama">
                                                <label class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer select-none text-sm font-medium transition"
                                                    :class="isRasaTerpilih(produkUntukModal.id, rasa.nama) ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-400' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                                    <input type="checkbox"
                                                        :checked="isRasaTerpilih(produkUntukModal.id, rasa.nama)"
                                                        @change="toggleRasaProduk(produkUntukModal.id, rasa.nama)"
                                                        class="rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500">
                                                    <span x-text="rasa.nama"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 flex justify-end gap-2">
                                <button type="button" @click="batalModalVarian()"
                                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Batal</button>
                                <button type="button" @click="simpanModalVarian()"
                                    class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-xs font-bold text-white transition">Selesai</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('reward-voucher.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Simpan Voucher
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

    <script>
        function tampilkanPreviewGambar(input) {
            const preview = document.getElementById('preview-gambar');
            const icon = document.getElementById('icon-gambar-kosong');
            const namaFile = document.getElementById('nama-file-gambar');

            if (input.files && input.files[0]) {
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('hidden');
                icon.classList.add('hidden');
                namaFile.textContent = input.files[0].name;
            }
        }
    </script>

@endsection