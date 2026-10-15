@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Reward Stamp" parentTitle="Reward Stamp" :parentUrl="route('stamp-program.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Program Stamp">

        <form action="{{ route('stamp-program.update', $program->id) }}" method="POST" id="formStampProgram">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Informasi Program --}}
                <div class="md:col-span-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Informasi Program</h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Program <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_program" value="{{ old('nama_program', $program->nama_program) }}"
                        placeholder="Contoh: Kumpulkan 10 Stamp, Gratis 1 Croissant"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('nama_program')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" rows="2" placeholder="Deskripsi singkat program (opsional)"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">{{ old('deskripsi', $program->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Pengaturan Stamp --}}
                <div class="md:col-span-2 mt-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Pengaturan Stamp</h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                {{-- Minimal Pembelian (format Rupiah, kayak Harga Satuan di Transaksi Bahan Baku) --}}
                <div x-data="{
                        minimalPembelianDisplay: '{{ old('minimal_pembelian', $program->minimal_pembelian) ? number_format(old('minimal_pembelian', $program->minimal_pembelian), 0, ',', '.') : '' }}',
                        getRawNumber(val) {
                            return (val || '').toString().replace(/\D/g, '') || '0';
                        },
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
                        }
                    }">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Minimal Pembelian per Stamp <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-bold text-sm">Rp</span>
                        <input type="text" inputmode="numeric" autocomplete="off" x-model="minimalPembelianDisplay"
                            @input="handleRupiahInput($event, 'minimalPembelianDisplay')" placeholder="50.000"
                                class="h-12 w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-3 text-gray-900 font-semibold dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required>
                        <input type="hidden" name="minimal_pembelian" :value="getRawNumber(minimalPembelianDisplay)">
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Total bayar (setelah diskon) minimal segini = 1 stamp per transaksi</span>
                    @error('minimal_pembelian')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah Stamp Dibutuhkan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="jumlah_stamp_dibutuhkan" min="1" value="{{ old('jumlah_stamp_dibutuhkan', $program->jumlah_stamp_dibutuhkan) }}"
                            placeholder="Contoh: 10"
                            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-4 pr-16 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                            required>
                        <span class="absolute right-4 top-3 text-gray-500 font-bold text-sm">stamp</span>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kartu penuh di angka ini, baru bisa ditukar</span>
                    @error('jumlah_stamp_dibutuhkan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Maksimal Redeem
                    </label>
                    <input type="number" name="maksimal_redeem" min="1" value="{{ old('maksimal_redeem', $program->maksimal_redeem) }}"
                        placeholder="Kosongkan jika tidak terbatas"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Total maksimal kartu boleh ditukar seluruh member. Kosongkan = tidak terbatas. Sudah terpakai: {{ $program->jumlah_redeem ?? 0 }}.</span>
                    @error('maksimal_redeem')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <hr class="my-8 border-gray-200 dark:border-gray-800">

            <h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">
                Pilihan Hadiah <span class="text-red-500">*</span>
            </h4>

            <div id="hadiah-container" class="space-y-4">
                {{-- Produk hadiah yang dipilih akan muncul di sini --}}
            </div>

            <button type="button" id="btn-tambah-hadiah"
                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Produk Hadiah
            </button>
            @error('produk_hadiah')
                <small class="block text-red-500 mt-2">{{ $message }}</small>
            @enderror

            <hr class="my-8 border-gray-200 dark:border-gray-800">

            {{-- Masa Berlaku --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <div class="md:col-span-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-1">Masa Berlaku <span class="font-normal text-gray-400 normal-case">(opsional)</span></h4>
                    <div class="border-b border-gray-100 dark:border-gray-800"></div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Mulai
                    </label>
                    <input type="date" id="inputTanggalMulai" name="tanggal_mulai"
                        value="{{ old('tanggal_mulai', optional($program->tanggal_mulai)->format('Y-m-d')) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    @error('tanggal_mulai')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Berakhir
                    </label>
                    <input type="date" id="inputTanggalBerakhir" name="tanggal_berakhir"
                        value="{{ old('tanggal_berakhir', optional($program->tanggal_berakhir)->format('Y-m-d')) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Kosongkan jika berlaku selamanya</span>
                    @error('tanggal_berakhir')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Program
                    </label>
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" id="inputStatusAktif" name="status_aktif" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('status_aktif', $program->status_aktif) == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Aktifkan Sekarang</span>
                    </label>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">Cuma boleh 1 program aktif. Mengaktifkan ini otomatis menonaktifkan program lain.</span>
                    <p id="peringatanMasaBerlaku" class="hidden text-xs font-semibold text-red-600 dark:text-red-400 mt-1">
                        ⚠ Tanggal berakhir sudah lewat. Program tidak bisa diaktifkan &mdash; ubah tanggal berakhirnya dulu atau jangan aktifkan sekarang.
                    </p>
                    @error('status_aktif')
                        <small class="text-red-500 block mt-1">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('stamp-program.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit" id="btnSubmitStampProgram"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

<script>
    const daftarProdukHadiah = @json($produks);
</script>

<script>
    const containerHadiah = document.getElementById('hadiah-container');
    const tombolTambahHadiah = document.getElementById('btn-tambah-hadiah');

    function tambahBarisHadiah() {
        const div = document.createElement('div');
        div.className = 'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Cari Produk
                    </label>
                    <input type="text" class="input-search-hadiah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        placeholder="Ketik nama produk..." autocomplete="off">
                    <div class="hasil-search absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"></div>
                    <input type="hidden" name="produk_hadiah[]" class="input-id-hadiah">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah
                    </label>
                    <input type="number" name="jumlah_hadiah[]" min="1" value="1"
                        class="input-jumlah-hadiah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">
                    <p class="peringatan-stok hidden text-xs text-red-600 mt-1">
                        Jumlah melebihi stok yang tersedia!
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="info-stok-hadiah text-sm text-gray-500">
                    Pilih produk terlebih dahulu
                </div>
                <button type="button" class="btn-hapus-hadiah text-sm text-red-600 hover:text-red-800">
                    Hapus
                </button>
            </div>

            <p class="peringatan-duplikat hidden text-xs text-red-600 mt-1">
                Produk ini sudah dipilih di baris lain!
            </p>
        `;

        containerHadiah.appendChild(div);
        aktifkanSearchHadiah(div);
        return div;
    }

    tombolTambahHadiah.addEventListener('click', function () {
        tambahBarisHadiah();
    });

    function cekJumlahMelebihiStok(inputJumlah, peringatan) {
        const stokMax = parseFloat(inputJumlah.dataset.stokMax || 0);
        const nilai = parseFloat(inputJumlah.value) || 0;

        if (nilai > stokMax) {
            peringatan.classList.remove('hidden');
            inputJumlah.classList.add('border-red-500');
        } else {
            peringatan.classList.add('hidden');
            inputJumlah.classList.remove('border-red-500');
        }

        perbaruiTombolSubmitStampProgram();
    }

    function cekDuplikatHadiah() {
        const semuaBaris = document.querySelectorAll('#hadiah-container > div');
        const jumlahPerId = {};

        semuaBaris.forEach(function (baris) {
            const id = baris.querySelector('.input-id-hadiah').value;
            if (id) {
                jumlahPerId[id] = (jumlahPerId[id] || 0) + 1;
            }
        });

        let adaDuplikat = false;

        semuaBaris.forEach(function (baris) {
            const inputId = baris.querySelector('.input-id-hadiah');
            const inputSearch = baris.querySelector('.input-search-hadiah');
            const peringatan = baris.querySelector('.peringatan-duplikat');

            if (inputId.value && jumlahPerId[inputId.value] > 1) {
                peringatan.classList.remove('hidden');
                inputSearch.classList.add('border-red-500');
                adaDuplikat = true;
            } else {
                peringatan.classList.add('hidden');
                inputSearch.classList.remove('border-red-500');
            }
        });

        perbaruiTombolSubmitStampProgram();
        return adaDuplikat;
    }

    // --- Validasi Masa Berlaku vs Status Aktif ---
    const inputTanggalMulai = document.getElementById('inputTanggalMulai');
    const inputTanggalBerakhir = document.getElementById('inputTanggalBerakhir');
    const inputStatusAktif = document.getElementById('inputStatusAktif');
    const peringatanMasaBerlaku = document.getElementById('peringatanMasaBerlaku');

    function cekMasaBerlakuVsStatus() {
        if (!inputStatusAktif.checked || !inputTanggalBerakhir.value) {
            peringatanMasaBerlaku.classList.add('hidden');
            perbaruiTombolSubmitStampProgram();
            return;
        }

        const hariIni = new Date();
        hariIni.setHours(0, 0, 0, 0);
        const tanggalBerakhir = new Date(inputTanggalBerakhir.value + 'T00:00:00');

        if (tanggalBerakhir < hariIni) {
            peringatanMasaBerlaku.classList.remove('hidden');
        } else {
            peringatanMasaBerlaku.classList.add('hidden');
        }

        perbaruiTombolSubmitStampProgram();
    }

    inputStatusAktif.addEventListener('change', cekMasaBerlakuVsStatus);
    inputTanggalBerakhir.addEventListener('change', cekMasaBerlakuVsStatus);
    inputTanggalMulai.addEventListener('change', cekMasaBerlakuVsStatus);

    function perbaruiTombolSubmitStampProgram() {
        const adaErrorStok = containerHadiah.querySelectorAll('.peringatan-stok:not(.hidden)').length > 0;
        const adaErrorDuplikat = containerHadiah.querySelectorAll('.peringatan-duplikat:not(.hidden)').length > 0;
        const adaErrorMasaBerlaku = !peringatanMasaBerlaku.classList.contains('hidden');
        const btn = document.getElementById('btnSubmitStampProgram');
        if (btn) {
            btn.disabled = adaErrorStok || adaErrorDuplikat || adaErrorMasaBerlaku;
        }
    }

    function aktifkanSearchHadiah(element) {
        const inputSearch = element.querySelector('.input-search-hadiah');
        const hasilSearch = element.querySelector('.hasil-search');
        const inputId = element.querySelector('.input-id-hadiah');
        const infoStok = element.querySelector('.info-stok-hadiah');
        const inputJumlah = element.querySelector('.input-jumlah-hadiah');
        const peringatanStok = element.querySelector('.peringatan-stok');

        inputJumlah.addEventListener('input', function () {
            cekJumlahMelebihiStok(inputJumlah, peringatanStok);
        });

        inputSearch.addEventListener('input', function () {
            const keyword = this.value.toLowerCase();
            inputId.value = '';

            if (keyword.length === 0) {
                hasilSearch.classList.add('hidden');
                cekDuplikatHadiah();
                return;
            }

            const hasil = daftarProdukHadiah.filter(function (produk) {
                return produk.nama_produk.toLowerCase().includes(keyword);
            });

            hasilSearch.innerHTML = '';

            if (hasil.length === 0) {
                hasilSearch.innerHTML = `
                    <div class="p-3 text-sm text-gray-500">
                        Produk tidak ditemukan
                    </div>
                `;
            } else {
                hasil.forEach(function (produk) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'block w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700';
                    item.innerHTML = `
                        <div class="font-medium text-gray-800 dark:text-white">
                            ${produk.nama_produk}
                        </div>
                        <div class="text-xs text-gray-500">
                            Stok: ${produk.stok}
                        </div>
                    `;

                    item.addEventListener('click', function () {
                        pilihProdukHadiah(produk, inputSearch, hasilSearch, inputId, infoStok, inputJumlah);
                        cekDuplikatHadiah();
                    });

                    hasilSearch.appendChild(item);
                });
            }

            hasilSearch.classList.remove('hidden');
        });

        element.querySelector('.btn-hapus-hadiah').addEventListener('click', function () {
            element.remove();
            cekDuplikatHadiah();
        });

        document.addEventListener('click', function (e) {
            if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
                hasilSearch.classList.add('hidden');
            }
        });
    }

    function pilihProdukHadiah(produk, inputSearch, hasilSearch, inputId, infoStok, inputJumlah) {
        inputSearch.value = produk.nama_produk;
        inputId.value = produk.id;
        infoStok.innerHTML = `Stok saat ini: <strong>${produk.stok}</strong>`;
        hasilSearch.classList.add('hidden');

        if (inputJumlah) {
            inputJumlah.dataset.stokMax = produk.stok;
            inputJumlah.dispatchEvent(new Event('input'));
        }
    }

    // Prefill: kalau validasi gagal, pakai old input. Kalau load pertama kali, pakai data hadiah yang sudah tersimpan.
    const oldProdukHadiah = @json(old('produk_hadiah', []));
    const oldJumlahHadiah = @json(old('jumlah_hadiah', []));
    const hadiahTersimpan = @json($hadiahTerpilih->map(fn ($h) => ['produk_id' => $h->produk_id, 'jumlah' => $h->jumlah]));

    function isiBarisHadiah(produkId, jumlah) {
        const produk = daftarProdukHadiah.find(function (p) { return p.id == produkId; });
        if (!produk) return;

        const div = tambahBarisHadiah();
        const inputSearch = div.querySelector('.input-search-hadiah');
        const hasilSearch = div.querySelector('.hasil-search');
        const inputId = div.querySelector('.input-id-hadiah');
        const infoStok = div.querySelector('.info-stok-hadiah');
        const inputJumlah = div.querySelector('.input-jumlah-hadiah');

        pilihProdukHadiah(produk, inputSearch, hasilSearch, inputId, infoStok, inputJumlah);
        inputJumlah.value = jumlah ?? 1;
        inputJumlah.dispatchEvent(new Event('input'));
    }

    if (oldProdukHadiah.length > 0) {
        oldProdukHadiah.forEach(function (id, index) {
            isiBarisHadiah(id, oldJumlahHadiah[index]);
        });
    } else if (hadiahTersimpan.length > 0) {
        hadiahTersimpan.forEach(function (h) {
            isiBarisHadiah(h.produk_id, h.jumlah);
        });
    } else {
        tambahBarisHadiah();
    }
    cekDuplikatHadiah();
    cekMasaBerlakuVsStatus();
</script>

@endsection