@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Bundling" parentTitle="Bundling" :parentUrl="route('bundling.index')" />

<div class="space-y-6">

<x-common.component-card title="Informasi Bundling">

<form action="{{ route('bundling.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nama Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Nama Bundling <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="nama_bundling"
            value="{{ old('nama_bundling') }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('nama_bundling')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Kode (otomatis) --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Kode
        </label>

        <input
            type="text"
            value="Otomatis dibuat setelah disimpan"
            disabled
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 px-4 py-3">
    </div>

    {{-- Harga Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Harga Bundling <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                Rp
            </span>
            <input
                type="text"
                inputmode="numeric"
                name="harga"
                placeholder="25.000"
                value="{{ old('harga') ? number_format((int) old('harga'), 0, ',', '.') : '' }}"
                class="input-rupiah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        </div>

        @error('harga')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Jenis Produk Bundling --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Jenis Produk Dalam Bundling <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-brand-500 transition">
                <input type="radio" name="jenis_produk" value="biasa" class="w-4 h-4 text-brand-600 focus:ring-brand-500" {{ old('jenis_produk', 'biasa') === 'biasa' ? 'checked' : '' }} onchange="gantiJenisProduk('biasa')">
                <div>
                    <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Biasa</span>
                    <span class="block text-xs text-gray-500">Mengambil dari katalog produk reguler/biasa, bisa pilih ukuran</span>
                </div>
            </label>
            <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-amber-500 transition">
                <input type="radio" name="jenis_produk" value="khusus_bundling" class="w-4 h-4 text-amber-600 focus:ring-amber-500" {{ old('jenis_produk') === 'khusus_bundling' ? 'checked' : '' }} onchange="gantiJenisProduk('khusus_bundling')">
                <div>
                    <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Khusus Bundling</span>
                    <span class="block text-xs text-gray-500">Varian rasa khusus yang dapat dipilih pembeli di mobile</span>
                </div>
            </label>
        </div>
        @error('jenis_produk')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Total Qty Pilihan Khusus (hanya tampil jika Produk Khusus Bundling) --}}
    <div id="wrapper-total-qty-khusus" class="{{ old('jenis_produk') === 'khusus_bundling' ? '' : 'hidden' }}">
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Total Isi Bundling<span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            min="1"
            name="total_qty_khusus"
            placeholder="Contoh: 6 (Pembeli dapat memilih 6 pcs varian rasa)"
            value="{{ old('total_qty_khusus', 6) }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        <small class="text-gray-400">Total jumlah produk yang wajib dipilih pembeli di mobile.</small>
        @error('total_qty_khusus')
            <small class="text-red-500 block">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Status Bundling
        </label>
        <label class="flex items-center gap-3 h-12 cursor-pointer">
            <input type="hidden" name="status_aktif" value="0">
            <input
                type="checkbox"
                name="status_aktif"
                value="1"
                class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Bundling Aktif</span>
        </label>
        @error('status_aktif')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Deskripsi --}}
<div>
    <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
        Deskripsi
    </label>

    <textarea
        name="deskripsi"
        rows="3"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi') }}</textarea>

    @error('deskripsi')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

{{-- Gambar Bundling --}}
<div>
    <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
        Gambar Bundling
    </label>

    <div class="flex items-center gap-4">
        <img id="preview-gambar" src="https://via.placeholder.com/96x96?text=No+Image"
             class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700">

        <input
            type="file"
            name="gambar"
            accept="image/*"
            onchange="document.getElementById('preview-gambar').src = URL.createObjectURL(this.files[0])"
            class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-white hover:file:bg-brand-600">
    </div>

    @error('gambar')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">
    Isi Bundling
</h4>

<div id="produk-container" class="space-y-4">
    {{-- Produk yang dipilih akan muncul di sini --}}
</div>

<button
    type="button"
    id="btn-tambah-produk"
    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">

    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>

    Tambah Produk

</button>

<div class="mt-8 flex justify-end gap-3">

    <a href="{{ route('bundling.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
        Batal
    </a>

    <button type="submit" id="btnSubmitBundling" class="rounded-lg bg-brand-500 px-5 py-2 text-white disabled:opacity-50 disabled:cursor-not-allowed">
        Simpan Bundling
    </button>

</div>

</form>

</x-common.component-card>

</div>

<script>
    // Produk biasa membawa `punya_ukuran` & `daftar_ukuran` (lihat accessor di
    // App\Models\Produk) sehingga baris produk bisa menampilkan pilihan ukuran
    // secara dinamis, sama seperti di menu Kasir & Produk. Produk khusus
    // bundling (varian rasa) gak pakai ukuran/qty per-baris — total isi
    // bundling-nya diatur lewat field "Total Isi Bundling" di atas.
    const produkBiasa = @json($produkBiasa);
    const produkKhusus = @json($produkKhusus);
    let jenisProdukAktif = "{{ old('jenis_produk', 'biasa') }}";
    let daftarProduk = jenisProdukAktif === 'khusus_bundling' ? produkKhusus : produkBiasa;

    function gantiJenisProduk(jenis) {
        jenisProdukAktif = jenis;
        daftarProduk = jenis === 'khusus_bundling' ? produkKhusus : produkBiasa;

        const wrapper = document.getElementById('wrapper-total-qty-khusus');
        if (wrapper) {
            wrapper.classList.toggle('hidden', jenis !== 'khusus_bundling');
        }

        const container = document.getElementById('produk-container');
        container.innerHTML = '';
        tambahBarisProduk();
    }
</script>

<script>

const container = document.getElementById('produk-container');
const tombolTambah = document.getElementById('btn-tambah-produk');

function tambahBarisProduk() {

    const isKhusus = jenisProdukAktif === 'khusus_bundling';
    const div = document.createElement('div');

    div.className =
        'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

    div.innerHTML = `

        <div class="relative col-search-produk">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Cari Produk
            </label>

            <input
                type="text"
                class="input-search-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                placeholder="Ketik nama produk..."
                autocomplete="off">

            <div
                class="hasil-search absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            </div>

            <input
                type="hidden"
                name="produk_id[]"
                class="input-id-produk">

        </div>

        <div class="col-varian-produk hidden mt-4">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Pilihan Varian <span class="text-red-500">*</span>
            </label>

            <select
                name="detail_produk_id[]"
                class="input-varian-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih varian</option>
            </select>

        </div>

        <div class="col-ukuran-produk hidden mt-4">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Ukuran <span class="text-red-500">*</span>
            </label>

            <select
                name="ukuran[]"
                class="input-ukuran-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih ukuran</option>
            </select>

        </div>

        <div class="col-rasa-produk hidden mt-4">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Varian Rasa <span class="text-red-500">*</span>
            </label>

            <select
                name="varian_rasa[]"
                class="input-rasa-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih rasa</option>
            </select>

        </div>

        <div class="col-qty-produk mt-4 ${isKhusus ? 'hidden' : ''}">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Qty
            </label>

            <input
                type="number"
                name="qty[]"
                min="1"
                value="1"
                class="input-qty w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">

        </div>

        <div class="mt-3 flex items-center justify-between">

            <div class="info-produk text-sm text-gray-500">
                Pilih produk terlebih dahulu
            </div>

            <button type="button" class="btn-hapus-produk text-sm text-red-600 hover:text-red-800">
                Hapus
            </button>

        </div>

        <p class="peringatan-duplikat hidden text-xs text-red-600 mt-1">
            Kombinasi produk, ukuran & rasa ini sudah dipilih di baris lain!
        </p>
    `;

    container.appendChild(div);

    aktifkanSearchProduk(div);

    return div;
}

tombolTambah.addEventListener('click', function () {
    tambahBarisProduk();
});

// ==== Varian rasa ====
// Pilihan rasa muncul kalau produk reguler punya varian rasa aktif. Stok rasa
// dibaca per kombinasi ukuran x rasa (stok_per_ukuran), atau stok rasa
// keseluruhan kalau produknya tidak punya ukuran.
function produkPunyaRasa(produk) {
    if (!produk || jenisProdukAktif === 'khusus_bundling') {
        return false;
    }
    // Jika produk memiliki varian baru di detail produk, jangan gunakan varian rasa lama
    const punyaVarianBaru = (produk.punya_varian == 1 || produk.punya_varian === true) && (produk.daftar_varian || []).length > 0;
    if (punyaVarianBaru) {
        return false;
    }
    const punya = produk.punya_varian_rasa == 1 || produk.punya_varian_rasa === true;
    return punya && (produk.daftar_varian_rasa || []).length > 0;
}

function stokRasaProduk(produk, namaUkuran, namaRasa) {
    const rasa = (produk.daftar_varian_rasa || []).find(function (r) {
        return (r.nama || '').toLowerCase() === String(namaRasa || '').toLowerCase();
    });
    if (!rasa) {
        return null;
    }

    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
    if (punyaUkuran) {
        const opsi = (produk.daftar_ukuran || []).find(function (o) {
            return o.nama === namaUkuran;
        });
        if (!opsi) {
            return null;
        }
        const perUkuran = rasa.stok_per_ukuran || {};
        return perUkuran[opsi.ukuran_key] !== undefined ? (parseInt(perUkuran[opsi.ukuran_key]) || 0) : 0;
    }

    return parseInt(rasa.stok) || 0;
}

function isiOpsiRasa(div, produk, namaUkuran) {
    const wrapRasa = div.querySelector('.col-rasa-produk');
    const selectRasa = div.querySelector('.input-rasa-produk');
    const dipilih = selectRasa.value;

    if (!produkPunyaRasa(produk)) {
        wrapRasa.classList.add('hidden');
        // Tetap sisain 1 <option value=""> supaya varian_rasa[] ke-submit &
        // index-nya sejajar sama produk_id[].
        selectRasa.innerHTML = '<option value=""></option>';
        return;
    }

    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
    const bisaHitungStok = !punyaUkuran || !!namaUkuran;

    wrapRasa.classList.remove('hidden');
    selectRasa.innerHTML = '<option value="">Pilih rasa</option>' + produk.daftar_varian_rasa.map(function (r) {
        const stok = bisaHitungStok ? stokRasaProduk(produk, namaUkuran, r.nama) : null;
        const nama = String(r.nama).replace(/"/g, '&quot;');
        const label = stok === null ? r.nama : `${r.nama} (Stok: ${stok})`;
        return `<option value="${nama}">${label}</option>`;
    }).join('');

    selectRasa.value = dipilih;
}

// Nampilin info stok sesuai ukuran yang lagi dipilih (atau stok polos kalau
// produknya gak punya varian ukuran).
function perbaruiInfoStok(div, produk, namaUkuran) {

    const infoProduk = div.querySelector('.info-produk');

    if (!produk) {
        infoProduk.textContent = 'Pilih produk terlebih dahulu';
        return;
    }

    const isKhusus = jenisProdukAktif === 'khusus_bundling';
    const punyaVarianBaru = !isKhusus && (produk.punya_varian == 1 || produk.punya_varian === true) && (produk.daftar_varian || []).length > 0;

    if (punyaVarianBaru) {
        const selectVarian = div.querySelector('.input-varian-produk');
        const varianId = selectVarian ? selectVarian.value : '';
        if (!varianId) {
            infoProduk.textContent = 'Pilih varian untuk melihat stok';
            return;
        }
        const v = (produk.daftar_varian || []).find(function (x) { return x.id == varianId; });
        if (v) {
            const namaVarian = v.nama || [v.ukuran, v.variasi].filter(Boolean).join(' / ') || ('Varian ' + v.id);
            infoProduk.innerHTML = `Stok tersedia (${namaVarian}): <strong>${v.stok}</strong>${v.stok <= 0 ? ' <span class="text-amber-600">— perlu diproduksi</span>' : ''}`;
        }
        return;
    }

    const punyaUkuran = !isKhusus && (produk.punya_ukuran == 1 || produk.punya_ukuran === true);
    const punyaRasa = produkPunyaRasa(produk);
    const selectRasa = div.querySelector('.input-rasa-produk');
    const namaRasa = selectRasa ? selectRasa.value : '';

    if (punyaUkuran && !namaUkuran) {
        infoProduk.textContent = 'Pilih ukuran untuk melihat stok';
        return;
    }

    // Produk dengan varian rasa: stok dibaca per rasa (per ukuran x rasa
    // kalau produknya juga punya ukuran).
    if (punyaRasa) {
        if (!namaRasa) {
            infoProduk.textContent = 'Pilih rasa untuk melihat stok';
            return;
        }

        const stokRasa = stokRasaProduk(produk, namaUkuran, namaRasa) ?? 0;
        const label = [namaUkuran, namaRasa].filter(Boolean).join(' / ');

        infoProduk.innerHTML = `Stok tersedia (${label}): <strong>${stokRasa}</strong>${stokRasa <= 0 ? ' <span class="text-amber-600">— perlu diproduksi</span>' : ''}`;
        return;
    }

    if (!punyaUkuran) {
        infoProduk.innerHTML = `Stok tersedia: <strong>${produk.stok}</strong>`;
        return;
    }

    const opsi = (produk.daftar_ukuran || []).find(function (o) {
        return o.nama === namaUkuran;
    });

    infoProduk.innerHTML = opsi
        ? `Stok tersedia (${opsi.nama}): <strong>${opsi.stok}</strong>${opsi.perlu_produksi ? ' <span class="text-amber-600">— perlu diproduksi</span>' : ''}`
        : 'Pilih ukuran untuk melihat stok';
}

function pilihProdukPadaBaris(div, produk) {

    const inputSearch = div.querySelector('.input-search-produk');
    const inputId = div.querySelector('.input-id-produk');
    const hasilSearch = div.querySelector('.hasil-search');
    const wrapVarian = div.querySelector('.col-varian-produk');
    const selectVarian = div.querySelector('.input-varian-produk');
    const wrapUkuran = div.querySelector('.col-ukuran-produk');
    const selectUkuran = div.querySelector('.input-ukuran-produk');
    const wrapRasa = div.querySelector('.col-rasa-produk');
    const selectRasa = div.querySelector('.input-rasa-produk');

    inputSearch.value = produk.nama_produk;
    inputId.value = produk.id;
    hasilSearch.classList.add('hidden');

    const isKhusus = jenisProdukAktif === 'khusus_bundling';
    const punyaVarianBaru = !isKhusus && (produk.punya_varian == 1 || produk.punya_varian === true) && (produk.daftar_varian || []).length > 0;

    if (punyaVarianBaru) {
        wrapVarian.classList.remove('hidden');
        wrapUkuran.classList.add('hidden');
        wrapRasa.classList.add('hidden');

        selectUkuran.innerHTML = '<option value=""></option>';
        selectRasa.innerHTML = '<option value=""></option>';

        const varianAktif = (produk.daftar_varian || []).filter(function (v) { return v.aktif; });
        selectVarian.innerHTML = '<option value="">Pilih varian</option>' + varianAktif.map(function (v) {
            const namaVarian = v.nama || [v.ukuran, v.variasi].filter(Boolean).join(' / ') || ('Varian ' + v.id);
            return `<option value="${v.id}" data-stok="${v.stok}">${namaVarian} (Stok: ${v.stok})</option>`;
        }).join('');
    } else {
        wrapVarian.classList.add('hidden');
        selectVarian.innerHTML = '<option value=""></option>';

        const punyaUkuran = !isKhusus && (produk.punya_ukuran == 1 || produk.punya_ukuran === true);
        const daftarUkuran = produk.daftar_ukuran || [];

        if (punyaUkuran && daftarUkuran.length > 0) {
            wrapUkuran.classList.remove('hidden');

            const tampilStokUkuran = !produkPunyaRasa(produk);

            selectUkuran.innerHTML = '<option value="">Pilih ukuran</option>' + daftarUkuran.map(function (opsi) {
                const labelStok = tampilStokUkuran ? ` (Stok: ${opsi.stok})` : '';
                return `<option value="${opsi.nama}">${opsi.nama} — Rp ${Math.round(opsi.harga).toLocaleString('id-ID')}${labelStok}</option>`;
            }).join('');

        } else {
            wrapUkuran.classList.add('hidden');
            selectUkuran.innerHTML = '<option value=""></option>';
        }

        selectRasa.innerHTML = '<option value="">Pilih rasa</option>';
        isiOpsiRasa(div, produk, selectUkuran.value);
    }

    perbaruiInfoStok(div, produk, selectUkuran.value);

    cekValidasiForm();
}

function aktifkanSearchProduk(element) {

    const inputSearch = element.querySelector('.input-search-produk');
    const hasilSearch = element.querySelector('.hasil-search');
    const inputId = element.querySelector('.input-id-produk');
    const selectVarian = element.querySelector('.input-varian-produk');
    const selectUkuran = element.querySelector('.input-ukuran-produk');
    const selectRasa = element.querySelector('.input-rasa-produk');

    inputSearch.addEventListener('input', function () {

        const keyword = this.value.toLowerCase();

        inputId.value = '';

        if (keyword.length === 0) {
            hasilSearch.classList.add('hidden');
            cekValidasiForm();
            return;
        }

        const hasil = daftarProduk.filter(function (produk) {
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
                const punyaUkuran = jenisProdukAktif !== 'khusus_bundling' && (produk.punya_ukuran == 1 || produk.punya_ukuran === true);
                const punyaRasa = produkPunyaRasa(produk);
                const punyaVarian = jenisProdukAktif !== 'khusus_bundling' && (produk.punya_varian == 1 || produk.punya_varian === true) && (produk.daftar_varian || []).length > 0;

                let keterangan;
                if (punyaVarian) {
                    const jmlVarian = produk.daftar_varian.filter(function (v) { return v.aktif; }).length;
                    keterangan = jmlVarian + ' varian produk';
                } else if (punyaUkuran && punyaRasa) {
                    keterangan = 'Punya varian ukuran & rasa';
                } else if (punyaUkuran) {
                    keterangan = 'Punya varian ukuran';
                } else if (punyaRasa) {
                    keterangan = 'Punya varian rasa';
                } else {
                    keterangan = 'Stok: ' + produk.stok;
                }

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700';
                item.innerHTML = `
                    <div class="font-medium text-gray-800 dark:text-white">
                        ${produk.nama_produk}
                    </div>
                    <div class="text-xs text-gray-500">
                        ${keterangan}
                    </div>
                `;

                item.addEventListener('click', function () {
                    pilihProdukPadaBaris(element, produk);
                });

                hasilSearch.appendChild(item);
            });
        }

        hasilSearch.classList.remove('hidden');
    });

    if (selectVarian) {
        selectVarian.addEventListener('change', function () {
            const id = inputId.value;
            const produk = daftarProduk.find(function (p) { return p.id == id; });
            perbaruiInfoStok(element, produk, selectUkuran.value);
            cekValidasiForm();
        });
    }

    selectUkuran.addEventListener('change', function () {
        const id = inputId.value;
        const produk = daftarProduk.find(function (p) { return p.id == id; });
        // Stok per rasa beda tiap ukuran, jadi label opsi rasa ikut dihitung ulang.
        isiOpsiRasa(element, produk, this.value);
        perbaruiInfoStok(element, produk, this.value);
        cekValidasiForm();
    });

    selectRasa.addEventListener('change', function () {
        const id = inputId.value;
        const produk = daftarProduk.find(function (p) { return p.id == id; });
        perbaruiInfoStok(element, produk, selectUkuran.value);
        cekValidasiForm();
    });

    document.addEventListener('click', function (e) {
        if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
            hasilSearch.classList.add('hidden');
        }
    });

    element.querySelector('.btn-hapus-produk').addEventListener('click', function () {
        element.remove();
        cekValidasiForm();
    });
}

// Validasi baris: cek kombinasi produk+ukuran yang kembar, dan pastikan
// ukuran udah dipilih buat produk yang punya varian ukuran, sebelum tombol
// simpan diaktifkan.
function cekValidasiForm() {

    const semuaBaris = document.querySelectorAll('#produk-container > div');

    const jumlahPerKombinasi = {};

    semuaBaris.forEach(function (baris) {
        const id = baris.querySelector('.input-id-produk').value;
        const selectVarian = baris.querySelector('.input-varian-produk');
        const selectUkuran = baris.querySelector('.input-ukuran-produk');
        const selectRasa = baris.querySelector('.input-rasa-produk');
        const varianId = selectVarian ? selectVarian.value : '';
        const ukuran = selectUkuran ? selectUkuran.value : '';
        const rasa = selectRasa ? selectRasa.value : '';
        if (id) {
            const kunci = id + '|' + (varianId ? ('var_' + varianId) : (ukuran + '|' + rasa));
            jumlahPerKombinasi[kunci] = (jumlahPerKombinasi[kunci] || 0) + 1;
        }
    });

    let adaMasalah = false;

    semuaBaris.forEach(function (baris) {
        const inputId = baris.querySelector('.input-id-produk');
        const inputSearch = baris.querySelector('.input-search-produk');
        const peringatan = baris.querySelector('.peringatan-duplikat');
        const wrapVarian = baris.querySelector('.col-varian-produk');
        const selectVarian = baris.querySelector('.input-varian-produk');
        const wrapUkuran = baris.querySelector('.col-ukuran-produk');
        const selectUkuran = baris.querySelector('.input-ukuran-produk');
        const wrapRasa = baris.querySelector('.col-rasa-produk');
        const selectRasa = baris.querySelector('.input-rasa-produk');

        const varianId = selectVarian ? selectVarian.value : '';
        const ukuran = selectUkuran ? selectUkuran.value : '';
        const rasa = selectRasa ? selectRasa.value : '';
        const kunci = inputId.value + '|' + (varianId ? ('var_' + varianId) : (ukuran + '|' + rasa));

        const duplikat = inputId.value && jumlahPerKombinasi[kunci] > 1;
        const varianWajibKosong = inputId.value && wrapVarian && !wrapVarian.classList.contains('hidden') && !varianId;
        const ukuranWajibKosong = inputId.value && wrapUkuran && !wrapUkuran.classList.contains('hidden') && !ukuran;
        const rasaWajibKosong = inputId.value && wrapRasa && !wrapRasa.classList.contains('hidden') && !rasa;

        if (duplikat) {
            peringatan.classList.remove('hidden');
            inputSearch.classList.add('border-red-500');
            adaMasalah = true;
        } else {
            peringatan.classList.add('hidden');
            inputSearch.classList.remove('border-red-500');
        }

        if (selectVarian) {
            selectVarian.classList.toggle('border-red-500', !!varianWajibKosong);
        }
        if (varianWajibKosong) {
            adaMasalah = true;
        }

        if (selectUkuran) {
            selectUkuran.classList.toggle('border-red-500', !!ukuranWajibKosong);
        }
        if (ukuranWajibKosong) {
            adaMasalah = true;
        }

        if (selectRasa) {
            selectRasa.classList.toggle('border-red-500', !!rasaWajibKosong);
        }
        if (rasaWajibKosong) {
            adaMasalah = true;
        }
    });

    const btnSubmit = document.getElementById('btnSubmitBundling');
    if (btnSubmit) {
        btnSubmit.disabled = adaMasalah;
    }
}

// Isi ulang satu baris (ukuran + rasa / varian) dari data tersimpan / old() input.
function pulihkanBaris(div, produk, namaUkuran, namaRasa, varianId) {
    const isKhusus = jenisProdukAktif === 'khusus_bundling';
    const punyaVarianBaru = !isKhusus && (produk.punya_varian == 1 || produk.punya_varian === true) && (produk.daftar_varian || []).length > 0;

    const wrapVarian = div.querySelector('.col-varian-produk');
    const selectVarian = div.querySelector('.input-varian-produk');
    const wrapUkuran = div.querySelector('.col-ukuran-produk');
    const selectUkuran = div.querySelector('.input-ukuran-produk');
    const wrapRasa = div.querySelector('.col-rasa-produk');
    const selectRasa = div.querySelector('.input-rasa-produk');

    if (punyaVarianBaru) {
        if (wrapVarian) wrapVarian.classList.remove('hidden');
        if (wrapUkuran) wrapUkuran.classList.add('hidden');
        if (wrapRasa) wrapRasa.classList.add('hidden');

        if (selectUkuran) selectUkuran.innerHTML = '<option value=""></option>';
        if (selectRasa) selectRasa.innerHTML = '<option value=""></option>';

        if (selectVarian && varianId) {
            selectVarian.value = varianId;
        }

        perbaruiInfoStok(div, produk, null);
        cekValidasiForm();
        return;
    }

    if (selectUkuran && namaUkuran) {
        selectUkuran.value = namaUkuran;
    }

    isiOpsiRasa(div, produk, selectUkuran ? selectUkuran.value : '');

    if (selectRasa && namaRasa) {
        selectRasa.value = namaRasa;
    }

    perbaruiInfoStok(div, produk, selectUkuran ? selectUkuran.value : '');
    cekValidasiForm();
}

// ==== Isi ulang baris produk dari old() kalau validasi gagal, kalau tidak 1 baris kosong ====
const oldProdukId = @json(old('produk_id', []));
const oldVarian = @json(old('detail_produk_id', []));
const oldQty = @json(old('qty', []));
const oldUkuran = @json(old('ukuran', []));
const oldRasa = @json(old('varian_rasa', []));

if (oldProdukId.length > 0) {

    oldProdukId.forEach(function (id, index) {

        const produk = daftarProduk.find(function (p) {
            return p.id == id;
        });

        if (!produk) {
            return;
        }

        const div = tambahBarisProduk();

        pilihProdukPadaBaris(div, produk);

        div.querySelector('input[name="qty[]"]').value = oldQty[index] ?? 1;

        pulihkanBaris(div, produk, oldUkuran[index], oldRasa[index], oldVarian[index]);
    });

} else {
    tambahBarisProduk();
}

cekValidasiForm();

document.querySelectorAll('.input-rupiah').forEach(function (input) {

    input.addEventListener('input', function (e) {

        const cursorPos = input.selectionStart;
        const digitSebelumKursor = input.value.slice(0, cursorPos).replace(/\D/g, '').length;

        let digits = input.value.replace(/\D/g, '');
        const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        input.value = formatted;

        let jumlahDigit = 0;
        let posisiBaru = formatted.length;

        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) {
                jumlahDigit++;
            }
            if (jumlahDigit === digitSebelumKursor) {
                posisiBaru = i + 1;
                break;
            }
        }

        if (digitSebelumKursor === 0) {
            posisiBaru = 0;
        }

        input.setSelectionRange(posisiBaru, posisiBaru);
    });
});

document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
        document.querySelectorAll('.input-rupiah').forEach(function (input) {
            input.value = input.value.replace(/\./g, '');
        });
    });
});

</script>

@endsection