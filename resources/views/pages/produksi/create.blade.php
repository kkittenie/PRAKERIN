@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Produksi" parentTitle="Produksi" :parentUrl="route('produksi.index')" />

<div class="space-y-6">

<x-common.component-card title="Form Produksi">

<form action="{{ route('produksi.store') }}" method="POST">

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

   {{-- Tanggal --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Tanggal Produksi <span class="text-red-500">*</span>
        </label>

        <input
            type="date"
            name="tanggal"
            value="{{ old('tanggal', date('Y-m-d')) }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('tanggal')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Jam Produksi --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Jam Produksi <span class="text-red-500">*</span>
        </label>

        <input
            type="time"
            name="jam_produksi"
            value="{{ old('jam_produksi', date('H:i')) }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('jam_produksi')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Produk --}}
    <div class="relative">

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Produk <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            id="input-search-produk"
            placeholder="Ketik nama produk..."
            autocomplete="off"
            value="{{ old('produk_id') ? optional($produk->firstWhere('id', old('produk_id')))->nama_produk : '' }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 @error('produk_id') border-red-500 @enderror">

        <div
            id="hasil-search-produk"
            class="hasil-search absolute z-50 mt-1 hidden w-full max-h-72 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
        </div>

        <input type="hidden" name="produk_id" id="input-id-produk" value="{{ old('produk_id') }}">

        @error('produk_id')
            <small class="text-red-500">{{ $message }}</small>
        @enderror

        {{-- Preview produk terpilih --}}
        <div id="preview-produk-terpilih" class="hidden mt-3 flex items-center gap-3 rounded-xl border border-brand-200 dark:border-brand-800 bg-brand-50 dark:bg-brand-500/10 p-3">
            <img id="preview-produk-foto" src="" class="w-14 h-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-white">
            <div class="min-w-0 flex-1">
                <p id="preview-produk-nama" class="font-semibold text-gray-800 dark:text-white text-sm truncate"></p>
                <p id="preview-produk-kode" class="text-xs text-gray-500 dark:text-gray-400"></p>
                <div id="preview-produk-ukuran" class="mt-1.5 hidden flex-wrap gap-1.5"></div>
            </div>
            <button type="button" onclick="batalkanPilihProduk()"
                class="shrink-0 text-gray-400 hover:text-red-500 transition text-lg leading-none px-1">&times;</button>
        </div>

    </div>

    {{-- Tipe Kadaluarsa --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Kadaluwarsa Berdasarkan <span class="text-red-500">*</span>
        </label>

        <div class="mb-3 flex gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="radio" name="kadaluarsa_tipe" value="tanggal" id="tipeTanggal"
                    {{ old('kadaluarsa_tipe', 'tanggal') == 'tanggal' ? 'checked' : '' }}>
                Tanggal
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="radio" name="kadaluarsa_tipe" value="jam" id="tipeJam"
                    {{ old('kadaluarsa_tipe') == 'jam' ? 'checked' : '' }}>
                Jam (dari waktu produksi)
            </label>
        </div>


        <div id="inputKadaluarsaTanggal">
            <input
                type="date"
                id="kadaluarsa_tanggal"
                name="kadaluarsa_tanggal"
                value="{{ old('kadaluarsa_tanggal') }}"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

            <p id="peringatanKadaluarsaTanggal" class="hidden text-xs text-red-600 mt-1">
                Tanggal kadaluwarsa tidak boleh sebelum tanggal produksi.
            </p>

            @error('kadaluarsa_tanggal')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div id="inputKadaluarsaJam" class="hidden">
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    id="kadaluarsa_jam"
                    name="kadaluarsa_jam"
                    min="1"
                    placeholder="Contoh: 24"
                    value="{{ old('kadaluarsa_jam') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <span class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">jam</span>
            </div>

            <p id="peringatanKadaluarsaJam" class="hidden text-xs text-red-600 mt-1">
                Jam kadaluwarsa harus diisi (lebih dari 0).
            </p>

            <p id="infoWaktuKadaluarsa" class="hidden text-xs text-gray-500 dark:text-gray-400 mt-1">
                Produk akan kadaluwarsa sekitar: <span class="font-semibold"></span>
            </p>

            @error('kadaluarsa_jam')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
    </div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">

Pengambilan Bahan Baku <span class="text-red-500">*</span>

</h4>

<div id="bahan-container" class="space-y-4">

    {{-- Bahan baku yang dipilih akan muncul di sini --}}

</div>

<button
    type="button"
    id="btn-tambah-bahan"
    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">

    <svg xmlns="http://www.w3.org/2000/svg"
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24">

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"/>

    </svg>

    Tambah Bahan Baku

</button>

    <hr class="my-8 border-gray-200 dark:border-gray-800">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">

        Jumlah Hasil Produksi <span class="text-red-500">*</span>

        </label>

        <input
        type="number"
        id="jumlah_hasil"
        name="jumlah_hasil"
        placeholder="10"
        value="{{ old('jumlah_hasil') }}"
        min="1"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('jumlah_hasil')
        <small class="text-red-500">{{ $message }}</small>
        @enderror

        </div>

        {{-- Rincian Ukuran (khusus produk yang punya varian ukuran) — baris di
             bawah ini di-generate otomatis oleh JS sesuai ukuran aktif produk
             yang dipilih (bisa 2 baris, bisa 5, gak lagi cuma Besar/Kecil). --}}
        <div class="md:col-span-2 hidden" id="wrapperRincianUkuranDinamis">
            <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
                <span id="judulRincianUkuran">Rincian per Detail Produk</span> <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Isi "Jumlah Hasil" untuk tiap ukuran/varian yang diproduksi kali ini (boleh kosongkan/0 kalau varian itu gak diproduksi sekarang).
                Total semua "Jumlah Hasil" harus sama dengan Jumlah Hasil Produksi di atas.
                "Pesanan" opsional, kalau sebagian hasil langsung dipakai/dipotong lagi saat itu juga.
            </p>

            <div id="rincian-ukuran-container" class="space-y-3">
                {{-- Baris per ukuran di-generate oleh JS (bangunRincianUkuranDinamis) --}}
            </div>

            <p id="peringatanRincianUkuran" class="hidden text-xs text-red-600 mt-2">
                Total Jumlah Hasil semua detail harus sama dengan Jumlah Hasil Produksi.
            </p>
            <p id="peringatanKebutuhanUkuran" class="hidden text-xs text-red-600 mt-1">
                Pesanan tidak boleh melebihi Jumlah Hasil pada detail yang sama.
            </p>
        </div>

        {{-- Kebutuhan Produksi (biasa) - untuk produk yang TIDAK punya varian ukuran --}}
        <div class="md:col-span-2" id="wrapperKebutuhanBiasa">
            <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
                Pesanan
            </label>
            <input type="number" id="kebutuhan_produksi_biasa" name="kebutuhan_produksi" placeholder="0" value="{{ old('kebutuhan_produksi') }}" min="0"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
            <small id="infoKebutuhanBiasa" class="text-gray-500 dark:text-gray-400 mt-1 block">
                Jumlah stok produk yang akan dipakai/dikurangi untuk pesanan ini (tidak boleh melebihi Jumlah Hasil Produksi).
            </small>
            @error('kebutuhan_produksi') <small class="text-red-500">{{ $message }}</small> @enderror
        </div>

        <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">

        Keterangan

        </label>

        <textarea
        name="keterangan"
        rows="3"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('keterangan') }}</textarea>

        @error('keterangan')
        <small class="text-red-500">{{ $message }}</small>
        @enderror
        </div>

        </div>

<div class="mt-8 flex justify-end gap-3">

    <a href="{{ route('produksi.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
        Batal
    </a>

    <button type="submit" id="btnSubmitProduksi" class="rounded-lg bg-brand-500 px-5 py-2 text-white disabled:opacity-50 disabled:cursor-not-allowed">
        Simpan Produksi
    </button>

</div>

</form>

</x-common.component-card>

</div>

    <script>
        const daftarBahanBaku = @json($bahanBaku);
        const daftarProduk = @json($produk);
        window.oldVarianHasil = @json(old('varian_hasil', []));
        window.oldVarianKebutuhan = @json(old('varian_kebutuhan', []));
    </script>

<script>

let bahanTerpilih = [];

const container = document.getElementById('bahan-container');
const tombolTambah = document.getElementById('btn-tambah-bahan');

function tambahBarisBahan() {

    const div = document.createElement('div');

    div.className =
        'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

    div.innerHTML = `

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="md:col-span-2">

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cari Bahan Baku
                </label>

                <input
                    type="text"
                    class="input-search-bahan w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                    placeholder="Ketik nama bahan baku..."
                    autocomplete="off">

                <div
                    class="hasil-search absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                </div>

                <input
                    type="hidden"
                    name="bahan_baku_id[]"
                    class="input-id-bahan">

            </div>

            <div>

                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Jumlah Dipakai
                </label>

                <div class="flex items-center gap-2">

                   <input
                        type="number"
                        name="jumlah_terpakai[]"
                        step="0.01"
                        min="0"
                        value="0"
                        class="input-jumlah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">

                    <span class="satuan-bahan text-sm text-gray-500">
                        -
                    </span>

                </div>

                <p class="peringatan-stok hidden text-xs text-red-600 mt-1">
                    Jumlah melebihi stok yang tersedia!
                </p>

                <p class="peringatan-kosong hidden text-xs text-red-600 mt-1">
                    Jumlah dipakai harus diisi!
                </p>

            </div>

        </div>

        <div class="mt-3 flex items-center justify-between">

            <div class="info-stok text-sm text-gray-500">
                Pilih bahan baku terlebih dahulu
            </div>

            <button
                type="button"
                class="btn-hapus-bahan text-sm text-red-600 hover:text-red-800">

                Hapus

            </button>

        </div>
    `;

    container.appendChild(div);

    aktifkanSearch(div);

    return div;

    }

    tombolTambah.addEventListener('click', function () {
        tambahBarisBahan();
    });

function aktifkanSearch(element) {

    const inputSearch =
        element.querySelector('.input-search-bahan');

    const hasilSearch =
        element.querySelector('.hasil-search');

    const inputId =
        element.querySelector('.input-id-bahan');

    const satuan =
        element.querySelector('.satuan-bahan');

    const infoStok =
        element.querySelector('.info-stok');

    const inputJumlah =
        element.querySelector('.input-jumlah');

    const peringatanStok =
        element.querySelector('.peringatan-stok');

    const peringatanKosong =
        element.querySelector('.peringatan-kosong');

    inputJumlah.addEventListener('input', function () {
        cekJumlahMelebihiStok(inputJumlah, peringatanStok, peringatanKosong, inputId);
    });

    inputSearch.addEventListener('input', function () {

        const keyword = this.value.toLowerCase();

        if (keyword.length === 0) {

            hasilSearch.classList.add('hidden');

            return;

        }

        const hasil = daftarBahanBaku.filter(function (bahan) {

            return bahan.nama_bahan
                .toLowerCase()
                .includes(keyword);

        });

        hasilSearch.innerHTML = '';

        if (hasil.length === 0) {

            hasilSearch.innerHTML = `
                <div class="p-3 text-sm text-gray-500">
                    Bahan baku tidak ditemukan
                </div>
            `;

        } else {

            hasil.forEach(function (bahan) {

                const item = document.createElement('button');

                item.type = 'button';

                item.className =
                    'block w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700';

                item.innerHTML = `
                    <div class="font-medium text-gray-800 dark:text-white">
                        ${bahan.nama_bahan}
                    </div>

                    <div class="text-xs text-gray-500">
                        Stok: ${formatStok(bahan.stok, bahan.satuan ? bahan.satuan.tipe : 'desimal')} ${bahan.satuan ? bahan.satuan.nama : '-'}
                    </div>
                `;

                item.addEventListener('click', function () {

                    pilihBahan(
                        bahan,
                        inputSearch,
                        hasilSearch,
                        inputId,
                        satuan,
                        infoStok,
                        inputJumlah
                    );

                });

                hasilSearch.appendChild(item);

            });

        }

        hasilSearch.classList.remove('hidden');

    });

    element
        .querySelector('.btn-hapus-bahan')
        .addEventListener('click', function () {

            element.remove();

            perbaruiTombolSubmitProduksi();

        });
}

        function aktifkanSearchProduk() {

            const inputSearch = document.getElementById('input-search-produk');
            const hasilSearch = document.getElementById('hasil-search-produk');
            const inputId = document.getElementById('input-id-produk');
            const preview = document.getElementById('preview-produk-terpilih');
            const previewFoto = document.getElementById('preview-produk-foto');
            const previewNama = document.getElementById('preview-produk-nama');
            const previewKode = document.getElementById('preview-produk-kode');

            function fotoUrl(produk) {
                return produk.foto ? `/storage/${produk.foto}` : null;
            }

            // Bikin baris badge kecil per ukuran, dipakai di preview & di
            // daftar hasil pencarian — biar dari awal keliatan produk itu
            // punya ukuran apa aja dan mana yang stoknya udah perlu diproduksi.
            function badgeUkuranHtml(produk) {
                const dv = Array.isArray(produk.daftar_varian_produksi) ? produk.daftar_varian_produksi : [];
                if (dv.length === 0) {
                    return `<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 text-[11px] px-2 py-0.5">Belum ada detail aktif</span>`;
                }
                return dv.map(function (u) {
                    const namaAman = String(u.nama).replace(/</g, '&lt;');
                    const warna = u.perlu_produksi
                        ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800'
                        : 'bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
                    return `<span class="inline-flex items-center rounded-full ${warna} text-[11px] px-2 py-0.5 whitespace-nowrap">${namaAman}: ${u.stok}${u.perlu_produksi ? ' ⚠' : ''}</span>`;
                }).join('');
            }

            function tampilkanPreview(produk) {
                const url = fotoUrl(produk);
                if (url) {
                    previewFoto.src = url;
                    previewFoto.classList.remove('hidden');
                } else {
                    previewFoto.src = '';
                    previewFoto.classList.add('hidden');
                }
                previewNama.textContent = produk.nama_produk;

                const previewUkuran = document.getElementById('preview-produk-ukuran');

                previewKode.textContent = `Kode: ${produk.kode_produk}`;
                previewUkuran.innerHTML = badgeUkuranHtml(produk);
                previewUkuran.classList.remove('hidden');
                previewUkuran.classList.add('flex');

                preview.classList.remove('hidden');

                if (typeof bangunRincianUkuranDinamis === 'function') {
                    bangunRincianUkuranDinamis(produk);
                }
            }

            window.batalkanPilihProduk = function () {
                inputSearch.value = '';
                inputId.value = '';
                preview.classList.add('hidden');
                inputSearch.focus();

                if (typeof bangunRincianUkuranDinamis === 'function') {
                    bangunRincianUkuranDinamis(null);
                }
            };

            // Kalau ada old('produk_id') pas validasi gagal, langsung tampilkan preview-nya.
            if (inputId.value) {
                const produkLama = daftarProduk.find(p => String(p.id) === String(inputId.value));
                if (produkLama) tampilkanPreview(produkLama);
            }

            inputSearch.addEventListener('input', function () {

                const keyword = this.value.toLowerCase();

                inputId.value = '';
                preview.classList.add('hidden');

                if (keyword.length === 0) {
                    hasilSearch.classList.add('hidden');
                    return;
                }

                const hasil = daftarProduk.filter(function (produk) {
                    return produk.nama_produk.toLowerCase().includes(keyword)
                        || (produk.kode_produk || '').toLowerCase().includes(keyword);
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
                        const url = fotoUrl(produk);
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'flex w-full items-center gap-3 px-3 py-2.5 text-left hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 last:border-0';
                        item.innerHTML = `
                            ${url
                                ? `<img src="${url}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600 shrink-0">`
                                : `<div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-500 shrink-0 text-xs">No Img</div>`
                            }
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-800 dark:text-white text-sm truncate">
                                    ${produk.nama_produk}
                                </div>
                                <div class="text-xs text-gray-400 truncate">
                                    ${produk.kode_produk ?? '-'}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-1">${badgeUkuranHtml(produk)}</div>
                            </div>
                        `;

                        item.addEventListener('click', function () {
                            inputSearch.value = produk.nama_produk;
                            inputId.value = produk.id;
                            hasilSearch.classList.add('hidden');
                            tampilkanPreview(produk);
                        });

                        hasilSearch.appendChild(item);
                    });
                }

                hasilSearch.classList.remove('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
                    hasilSearch.classList.add('hidden');
                }
            });
        }

        aktifkanSearchProduk();

        function formatStok(stok, tipe) {
            const angka = parseFloat(stok) || 0;
            return (tipe === 'bulat') ? Math.round(angka) : parseFloat(angka.toFixed(2));
        }

        function pilihBahan(
            bahan,
            inputSearch,
            hasilSearch,
            inputId,
            satuan,
            infoStok,
            inputJumlah
        ) {

            const namaSatuan = bahan.satuan ? bahan.satuan.nama : '-';
            const tipeSatuan = bahan.satuan ? bahan.satuan.tipe : 'desimal';

            inputSearch.value = bahan.nama_bahan;

            inputId.value = bahan.id;

            satuan.textContent = namaSatuan;

            infoStok.innerHTML =`Stok tersedia: <strong>${formatStok(bahan.stok, tipeSatuan)} ${namaSatuan}</strong>`;

        if (inputJumlah) {
                inputJumlah.step = (tipeSatuan === 'bulat') ? '1' : '0.01';
                inputJumlah.dataset.stokMax = bahan.stok;
                inputJumlah.dispatchEvent(new Event('input'));
            }

            hasilSearch.classList.add('hidden');

        }

        function cekJumlahMelebihiStok(inputJumlah, peringatan, peringatanKosong, inputId) {

            const stokMax = parseFloat(inputJumlah.dataset.stokMax || 0);
            const nilai = parseFloat(inputJumlah.value) || 0;
            const sudahPilihBahan = !!(inputId && inputId.value);

            if (nilai > stokMax) {
                peringatan.classList.remove('hidden');
                inputJumlah.classList.add('border-red-500');
            } else {
                peringatan.classList.add('hidden');
                inputJumlah.classList.remove('border-red-500');
            }

            if (peringatanKosong) {
                if (sudahPilihBahan && nilai <= 0) {
                    peringatanKosong.classList.remove('hidden');
                    inputJumlah.classList.add('border-red-500');
                } else {
                    peringatanKosong.classList.add('hidden');
                }
            }

            perbaruiTombolSubmitProduksi();
        }

        const oldBahanBakuId = @json(old('bahan_baku_id', []));
        const oldJumlahTerpakai = @json(old('jumlah_terpakai', []));

        oldBahanBakuId.forEach(function (id, index) {

            const bahan = daftarBahanBaku.find(function (b) {
                return b.id == id;
            });

            if (!bahan) {
                return;
            }

            const div = tambahBarisBahan();

            const inputSearch = div.querySelector('.input-search-bahan');
            const hasilSearch = div.querySelector('.hasil-search');
            const inputId = div.querySelector('.input-id-bahan');
            const satuan = div.querySelector('.satuan-bahan');
            const infoStok = div.querySelector('.info-stok');
            const inputJumlah = div.querySelector('.input-jumlah');

            pilihBahan(bahan, inputSearch, hasilSearch, inputId, satuan, infoStok, inputJumlah);

            inputJumlah.value = oldJumlahTerpakai[index] ?? 0;
            inputJumlah.dispatchEvent(new Event('input'));

        });

        function perbaruiTombolSubmitProduksi() {
            const adaErrorStok = document.querySelectorAll('.peringatan-stok:not(.hidden)').length > 0;
            const adaErrorKosong = document.querySelectorAll('.peringatan-kosong:not(.hidden)').length > 0;
            const adaErrorRincianUkuran = !document.getElementById('peringatanRincianUkuran').classList.contains('hidden');
            const adaErrorKebutuhanUkuran = !document.getElementById('peringatanKebutuhanUkuran').classList.contains('hidden');
            const btn = document.getElementById('btnSubmitProduksi');
            if (btn) {
                btn.disabled = adaErrorStok || adaErrorKosong || adaErrorRincianUkuran || adaErrorKebutuhanUkuran;
            }
        }

    const tipeTanggal = document.getElementById('tipeTanggal');
    const tipeJam = document.getElementById('tipeJam');
    const inputTanggal = document.getElementById('inputKadaluarsaTanggal');
    const inputJam = document.getElementById('inputKadaluarsaJam');
    const fieldTanggalProduksi = document.querySelector('input[name="tanggal"]');
    const fieldJamProduksi = document.querySelector('input[name="jam_produksi"]');
    const fieldKadaluarsaTanggal = document.getElementById('kadaluarsa_tanggal');
    const fieldKadaluarsaJam = document.getElementById('kadaluarsa_jam');
    const peringatanKadaluarsaTanggal = document.getElementById('peringatanKadaluarsaTanggal');
    const peringatanKadaluarsaJam = document.getElementById('peringatanKadaluarsaJam');
    const infoWaktuKadaluarsa = document.getElementById('infoWaktuKadaluarsa');

    function toggleKadaluarsa() {
        if (tipeJam.checked) {
            inputTanggal.classList.add('hidden');
            inputJam.classList.remove('hidden');
            fieldKadaluarsaTanggal.value = '';
            peringatanKadaluarsaTanggal.classList.add('hidden');
        } else {
            inputTanggal.classList.remove('hidden');
            inputJam.classList.add('hidden');
            fieldKadaluarsaJam.value = '';
            peringatanKadaluarsaJam.classList.add('hidden');
            infoWaktuKadaluarsa.classList.add('hidden');
        }
    }

    function cekKadaluarsaTanggal() {
        if (tipeJam.checked) return true;

        const tglProduksi = fieldTanggalProduksi.value;
        const tglKadaluarsa = fieldKadaluarsaTanggal.value;

        if (tglProduksi && tglKadaluarsa && tglKadaluarsa < tglProduksi) {
            peringatanKadaluarsaTanggal.classList.remove('hidden');
            fieldKadaluarsaTanggal.classList.add('border-red-500');
            return false;
        }

        peringatanKadaluarsaTanggal.classList.add('hidden');
        fieldKadaluarsaTanggal.classList.remove('border-red-500');
        return true;
    }

    function cekKadaluarsaJam() {
        if (!tipeJam.checked) return true;

        const nilaiJam = parseFloat(fieldKadaluarsaJam.value);

        if (!fieldKadaluarsaJam.value || isNaN(nilaiJam) || nilaiJam <= 0) {
            peringatanKadaluarsaJam.classList.remove('hidden');
            fieldKadaluarsaJam.classList.add('border-red-500');
            infoWaktuKadaluarsa.classList.add('hidden');
            return false;
        }

        peringatanKadaluarsaJam.classList.add('hidden');
        fieldKadaluarsaJam.classList.remove('border-red-500');

        if (fieldTanggalProduksi.value && fieldJamProduksi.value) {
            const mulai = new Date(`${fieldTanggalProduksi.value}T${fieldJamProduksi.value}`);
            if (!isNaN(mulai.getTime())) {
                mulai.setHours(mulai.getHours() + nilaiJam);
                const pad = (n) => String(n).padStart(2, '0');
                const teks = `${pad(mulai.getDate())}-${pad(mulai.getMonth() + 1)}-${mulai.getFullYear()} ${pad(mulai.getHours())}:${pad(mulai.getMinutes())}`;
                infoWaktuKadaluarsa.querySelector('span').textContent = teks;
                infoWaktuKadaluarsa.classList.remove('hidden');
            }
        }

        return true;
    }

    tipeTanggal.addEventListener('change', toggleKadaluarsa);
    tipeJam.addEventListener('change', toggleKadaluarsa);
    window.addEventListener('load', toggleKadaluarsa);

    fieldKadaluarsaTanggal.addEventListener('input', cekKadaluarsaTanggal);
    fieldTanggalProduksi.addEventListener('input', cekKadaluarsaTanggal);
    fieldKadaluarsaJam.addEventListener('input', cekKadaluarsaJam);
    fieldTanggalProduksi.addEventListener('input', cekKadaluarsaJam);
    fieldJamProduksi.addEventListener('input', cekKadaluarsaJam);
    window.addEventListener('load', cekKadaluarsaJam);

    window.produkPunyaUkuran = false;
    window.produkPunyaRasa = false;
    window.produkPakaiRincian = false; // true kalau produk punya ukuran ATAU rasa
    window.produkTerpilihSaatIni = null;

    function terapkanModeUkuran(punyaUkuran, punyaRasa) {
        window.produkPunyaUkuran = !!punyaUkuran;
        window.produkPunyaRasa = !!punyaRasa;
        window.produkPakaiRincian = !!punyaUkuran || !!punyaRasa;

        const wrapperRincianUkuranDinamis = document.getElementById('wrapperRincianUkuranDinamis');
        const wrapperKebutuhanBiasa = document.getElementById('wrapperKebutuhanBiasa');
        const elKebBiasa = document.getElementById('kebutuhan_produksi_biasa');
        const elPeringatanRincian = document.getElementById('peringatanRincianUkuran');
        const elPeringatanKebutuhan = document.getElementById('peringatanKebutuhanUkuran');

        if (window.produkPakaiRincian) {
            wrapperRincianUkuranDinamis.classList.remove('hidden');
            wrapperKebutuhanBiasa.classList.add('hidden');
            elKebBiasa.disabled = true;
        } else {
            wrapperRincianUkuranDinamis.classList.add('hidden');
            wrapperKebutuhanBiasa.classList.remove('hidden');
            elKebBiasa.disabled = false;
            elPeringatanRincian.classList.add('hidden');
            elPeringatanKebutuhan.classList.add('hidden');

            const container = document.getElementById('rincian-ukuran-container');
            if (container) container.innerHTML = '';
        }

        if (typeof sinkronMaxBiasa === 'function') {
            sinkronMaxBiasa();
        }
        if (typeof perbaruiTombolSubmitProduksi === 'function') {
            perbaruiTombolSubmitProduksi();
        }
    }

    /**
     * Bikin ulang baris rincian sesuai detail produk AKTIF dari produk yang lagi dipilih.
     * Dipanggil tiap kali produk berubah (pilih baru / batal pilih): satu baris per detail
     * (kombinasi ukuran + variasi), berapa pun jumlahnya.
     */
    function bangunRincianUkuranDinamis(produk) {
        window.produkTerpilihSaatIni = produk;

        // Belum ada produk dipilih: tampilkan field Pesanan biasa, rincian dikosongkan.
        if (!produk) {
            terapkanModeUkuran(false, false);
            return;
        }

        terapkanModeUkuran(true, false);

        const judul = document.getElementById('judulRincianUkuran');
        if (judul) judul.textContent = 'Rincian per Detail Produk';

        const wadah = document.getElementById('rincian-ukuran-container');
        if (!wadah) return;
        wadah.innerHTML = '';

        bangunRincianVarian(wadah, produk);
        cekSemuaUkuranDanTombol();
    }

    /**
     * Produk dengan varian dari form produk (detail_produks): satu baris per varian aktif.
     * Memakai class .input-ukuran-hasil / .input-ukuran-kebutuhan dan atribut
     * data-produk-ukuran-id supaya validasi total & pesanan yang sudah ada tetap jalan.
     * Yang dikirim ke server: varian_hasil[id] dan varian_kebutuhan[id].
     */
    function bangunRincianVarian(container, produk) {
        const daftar = Array.isArray(produk.daftar_varian_produksi) ? produk.daftar_varian_produksi : [];

        if (daftar.length === 0) {
            container.innerHTML = '<p class="text-xs italic text-gray-400">Produk ini belum punya detail produk aktif. Aktifkan atau tambahkan detail lewat halaman Edit Produk.</p>';
            return;
        }

        const esc = function (t) { return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); };
        const inputCls = 'w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:border-brand-500 focus:ring-brand-500';

        daftar.forEach(function (opsi) {
            const baris = document.createElement('div');
            baris.className = 'grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 items-end border border-gray-200 dark:border-gray-800 rounded-lg p-3';
            baris.dataset.produkUkuranId = opsi.id;

            const idStr = String(opsi.id);
            const oldHasil = (window.oldVarianHasil && window.oldVarianHasil[idStr] !== undefined) ? window.oldVarianHasil[idStr] : 0;
            const oldKebutuhan = (window.oldVarianKebutuhan && window.oldVarianKebutuhan[idStr] !== undefined) ? window.oldVarianKebutuhan[idStr] : 0;
            const labelHabis = opsi.perlu_produksi
                ? '<span class="ml-2 rounded-full bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 text-[10px] font-semibold px-2 py-0.5">Perlu Diproduksi</span>'
                : '';

            baris.innerHTML = `
                <div>
                    <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200">${esc(opsi.nama)}${labelHabis}</span>
                    <span class="block text-xs text-gray-400">Stok saat ini: ${opsi.stok}</span>
                </div>
                <div class="w-full sm:w-32">
                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Jumlah Hasil</label>
                    <input type="number" min="0" placeholder="0" value="${oldHasil}" name="varian_hasil[${opsi.id}]" class="input-ukuran-hasil ${inputCls}">
                </div>
                <div class="w-full sm:w-32">
                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Pesanan</label>
                    <input type="number" min="0" placeholder="0" value="${oldKebutuhan}" name="varian_kebutuhan[${opsi.id}]" class="input-ukuran-kebutuhan ${inputCls}">
                </div>
            `;

            container.appendChild(baris);
        });

        container.querySelectorAll('.input-ukuran-hasil, .input-ukuran-kebutuhan').forEach(function (el) {
            el.addEventListener('input', cekSemuaUkuranDanTombol);
        });
    }

    const inputHasil = document.getElementById('jumlah_hasil');
    const inputKebutuhanBiasa = document.getElementById('kebutuhan_produksi_biasa');
    const infoKebutuhanBiasa = document.getElementById('infoKebutuhanBiasa');
    const formProduksi = document.querySelector('form');

    function maxHasilSaatIni() {
        const nilai = parseInt(inputHasil.value, 10);
        return (isNaN(nilai) || nilai < 0) ? 0 : nilai;
    }

    function updateInfoKebutuhanBiasa(maxHasil) {
        if (!infoKebutuhanBiasa) return;
        infoKebutuhanBiasa.textContent = maxHasil > 0
            ? `Maksimal ${maxHasil} (tidak boleh lebih dari jumlah hasil produksi).`
            : 'Isi jumlah hasil produksi terlebih dahulu.';
    }

    function sinkronMaxBiasa() {
        if (window.produkPakaiRincian) return;

        const maxHasil = maxHasilSaatIni();

        inputKebutuhanBiasa.max = maxHasil;

        let nilaiKebutuhan = parseInt(inputKebutuhanBiasa.value, 10);
        if (isNaN(nilaiKebutuhan) || nilaiKebutuhan < 0) {
            nilaiKebutuhan = 0;
        }
        if (nilaiKebutuhan > maxHasil) {
            nilaiKebutuhan = maxHasil;
        }
        inputKebutuhanBiasa.value = nilaiKebutuhan;

        updateInfoKebutuhanBiasa(maxHasil);
    }

    function clampKebutuhanBiasa() {
        if (window.produkPakaiRincian) return;

        const maxHasil = maxHasilSaatIni();

        let nilaiKebutuhan = parseInt(inputKebutuhanBiasa.value, 10);
        if (isNaN(nilaiKebutuhan) || nilaiKebutuhan < 0) {
            nilaiKebutuhan = 0;
        }
        if (nilaiKebutuhan > maxHasil) {
            nilaiKebutuhan = maxHasil;
        }

        inputKebutuhanBiasa.value = nilaiKebutuhan;
        updateInfoKebutuhanBiasa(maxHasil);
    }

    const peringatanRincianUkuran = document.getElementById('peringatanRincianUkuran');
    const peringatanKebutuhanUkuran = document.getElementById('peringatanKebutuhanUkuran');

    function cekRincianUkuran() {
        if (!window.produkPakaiRincian) {
            peringatanRincianUkuran.classList.add('hidden');
            return true;
        }

        const hasil = maxHasilSaatIni();
        const barisUkuran = document.querySelectorAll('#rincian-ukuran-container [data-produk-ukuran-id]');

        if (barisUkuran.length === 0) {
            // Produk ditandai "punya ukuran" tapi belum ada baris ukuran
            // aktif sama sekali — gak bisa divalidasi, biarin backend yang
            // nolak lewat pesan error biasa.
            peringatanRincianUkuran.classList.add('hidden');
            return true;
        }

        let total = 0;
        barisUkuran.forEach(function (baris) {
            const inputHasilUkuran = baris.querySelector('.input-ukuran-hasil');
            total += parseInt(inputHasilUkuran.value, 10) || 0;
        });

        const valid = total === hasil;

        barisUkuran.forEach(function (baris) {
            const inputHasilUkuran = baris.querySelector('.input-ukuran-hasil');
            inputHasilUkuran.classList.toggle('border-red-500', !valid);
        });

        peringatanRincianUkuran.classList.toggle('hidden', valid);

        return valid;
    }

    function hitungDanCekKebutuhanUkuran() {
        if (!window.produkPakaiRincian) {
            peringatanKebutuhanUkuran.classList.add('hidden');
            return true;
        }

        const barisUkuran = document.querySelectorAll('#rincian-ukuran-container [data-produk-ukuran-id]');
        let valid = true;

        barisUkuran.forEach(function (baris) {
            const inputHasilUkuran = baris.querySelector('.input-ukuran-hasil');
            const inputKebutuhanUkuran = baris.querySelector('.input-ukuran-kebutuhan');

            const hasilBaris = parseInt(inputHasilUkuran.value, 10) || 0;
            const kebutuhanBaris = parseInt(inputKebutuhanUkuran.value, 10) || 0;

            if (kebutuhanBaris > hasilBaris) {
                inputKebutuhanUkuran.classList.add('border-red-500');
                valid = false;
            } else {
                inputKebutuhanUkuran.classList.remove('border-red-500');
            }
        });

        peringatanKebutuhanUkuran.classList.toggle('hidden', valid);

        return valid;
    }

    function cekSemuaUkuranDanTombol() {
        cekRincianUkuran();
        hitungDanCekKebutuhanUkuran();
        perbaruiTombolSubmitProduksi();
    }

    inputHasil.addEventListener('input', cekSemuaUkuranDanTombol);
    window.addEventListener('load', cekSemuaUkuranDanTombol);

    inputHasil.addEventListener('input', sinkronMaxBiasa);
    inputHasil.addEventListener('change', sinkronMaxBiasa);

    inputKebutuhanBiasa.addEventListener('input', clampKebutuhanBiasa);
    inputKebutuhanBiasa.addEventListener('change', clampKebutuhanBiasa);
    inputKebutuhanBiasa.addEventListener('blur', clampKebutuhanBiasa);
    inputKebutuhanBiasa.addEventListener('keyup', clampKebutuhanBiasa);

    if (formProduksi) {
        formProduksi.addEventListener('submit', function (e) {
            clampKebutuhanBiasa();

            const kadaluarsaTanggalValid = cekKadaluarsaTanggal();
            const kadaluarsaJamValid = cekKadaluarsaJam();
            const rincianUkuranValid = cekRincianUkuran();
            const kebutuhanUkuranValid = hitungDanCekKebutuhanUkuran();

            let semuaJumlahValid = true;
            document.querySelectorAll('#bahan-container > div').forEach(function (baris) {
                const inputId = baris.querySelector('.input-id-bahan');
                const inputJumlah = baris.querySelector('.input-jumlah');
                const peringatanKosong = baris.querySelector('.peringatan-kosong');

                if (inputId && inputId.value) {
                    const nilai = parseFloat(inputJumlah.value) || 0;
                    if (nilai <= 0) {
                        semuaJumlahValid = false;
                        if (peringatanKosong) peringatanKosong.classList.remove('hidden');
                        inputJumlah.classList.add('border-red-500');
                    }
                }
            });

            if (!kadaluarsaTanggalValid || !kadaluarsaJamValid || !rincianUkuranValid || !kebutuhanUkuranValid || !semuaJumlahValid) {
                e.preventDefault();
                perbaruiTombolSubmitProduksi();
            }
        });
    }

    window.addEventListener('load', sinkronMaxBiasa);
    window.addEventListener('load', function () {
        if (!document.getElementById('input-id-produk').value) {
            terapkanModeUkuran(false, false);
        }
    });

</script>

@endsection