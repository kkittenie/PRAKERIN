@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Reward Voucher" parentTitle="Reward Voucher" :parentUrl="route('reward-voucher.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Reward Voucher">

        <form action="{{ route('reward-voucher.store') }}" method="POST" enctype="multipart/form-data"
            x-data="{
                jenisPotongan: '{{ old('jenis_potongan', 'nominal') }}',
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
                        placeholder="Contoh: Diskon 20% untuk pembelian minimal Rp 50.000 (tulis nilai/nominal vouchernya di sini)"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
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
                        Masa Berlaku Voucher
                    </label>
                    <div class="relative">
                        <input type="number" name="masa_berlaku_hari" min="1" value="{{ old('masa_berlaku_hari') }}"
                            placeholder="Kosongkan jika tidak ada batas waktu"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-16 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">hari</span>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Dihitung sejak member menukar poin untuk voucher ini.</span>
                    @error('masa_berlaku_hari')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Poin Dibutuhkan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="poin_dibutuhkan" min="1" value="{{ old('poin_dibutuhkan') }}"
                            placeholder="Contoh: 100"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-16 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">poin</span>
                    </div>
                    @error('poin_dibutuhkan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok
                    </label>
                    <input type="number" name="stok" min="0" value="{{ old('stok') }}"
                        placeholder="Kosongkan jika tidak terbatas"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan = tidak terbatas.</span>
                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

<div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Gambar Reward
                    </label>

                    <div class="flex items-center gap-4">
                        <div class="w-24 h-24 shrink-0 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                            <img id="preview-gambar" src="" alt="Preview gambar reward" class="hidden w-full h-full object-cover">
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
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">Ditampilkan di katalog reward member. Format jpg/png/webp, maks 2MB.</span>

                    @error('gambar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Program
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