@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Produksi" parentTitle="Produksi" :parentUrl="route('produksi.index')" />

<div class="space-y-6">

<x-common.component-card title="Form Produksi">

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        <p class="font-bold">Mohon lengkapi atau perbaiki data produksi berikut:</p>
        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('produksi.store') }}" method="POST" id="formProduksi">

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Tanggal Produksi --}}
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

    {{-- Tipe Kadaluarsa --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Kadaluwarsa Berdasarkan <span class="text-red-500">*</span>
        </label>

        <div class="mb-3 flex gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="radio" name="kadaluarsa_tipe" value="tanggal" id="tipeTanggal"
                    {{ old('kadaluarsa_tipe', 'tanggal') == 'tanggal' ? 'checked' : '' }}>
                Tanggal
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
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
                    step="1"
                    inputmode="numeric"
                    placeholder="Contoh: 24"
                    value="{{ old('kadaluarsa_jam') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <span class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">jam</span>
            </div>

            <p id="peringatanKadaluarsaJam" class="hidden text-xs text-red-600 mt-1">
                Jam kadaluwarsa harus diisi bilangan bulat minimal 1 jam.
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

{{-- Pengambilan Bahan Baku --}}
<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">
    Pengambilan Bahan Baku <span class="text-red-500">*</span>
</h4>

<div id="bahan-container" class="space-y-4">
    {{-- Bahan baku yang dipilih akan muncul di sini --}}
</div>

<button
    type="button"
    id="btn-tambah-bahan"
    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600 transition shadow-xs text-sm font-medium">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Tambah Bahan Baku</span>
</button>

<hr class="my-8 border-gray-200 dark:border-gray-800">

{{-- Daftar Produk yang Diproduksi --}}
<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">
    Daftar Produk yang Diproduksi <span class="text-red-500">*</span>
</h4>

<div id="produk-container" class="space-y-4">
    {{-- Produk yang dipilih akan muncul di sini --}}
</div>

<button
    type="button"
    id="btn-tambah-produk"
    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600 transition shadow-xs text-sm font-medium">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Tambah Produk</span>
</button>

<hr class="my-8 border-gray-200 dark:border-gray-800">

{{-- Keterangan --}}
<div class="mb-4">
    <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
        Keterangan
    </label>

    <textarea
        name="keterangan"
        rows="3"
        placeholder="Catatan tambahan untuk sesi produksi ini (opsional)..."
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('keterangan') }}</textarea>

    @error('keterangan')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

<div class="mt-8 flex justify-end gap-3">
    <a href="{{ route('produksi.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm font-medium transition">
        Batal
    </a>

    <button type="submit" id="btnSubmitProduksi" class="rounded-lg bg-brand-500 px-6 py-2.5 text-white disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold hover:bg-brand-600 transition shadow-xs">
        Simpan Produksi
    </button>
</div>

</form>

</x-common.component-card>

</div>

<script>
    const daftarBahanBaku = @json($bahanBaku);
    const daftarProduk = @json($produk);
    const oldBahanBakuId = @json(old('bahan_baku_id', []));
    const oldJumlahTerpakai = @json(old('jumlah_terpakai', []));
    const oldProdukItems = @json(old('produk_items', []));
</script>

<script>
/* ==========================================================================
   1. BAHAN BAKU LOGIC
   ========================================================================== */
const containerBahan = document.getElementById('bahan-container');
const tombolTambahBahan = document.getElementById('btn-tambah-bahan');

function formatStok(stok, tipe) {
    const angka = parseFloat(stok) || 0;
    return (tipe === 'bulat') ? Math.round(angka) : parseFloat(angka.toFixed(2));
}

function tambahBarisBahan() {
    const div = document.createElement('div');
    div.className = 'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cari Bahan Baku <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="text"
                        class="input-search-bahan w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm"
                        placeholder="Ketik nama bahan baku..."
                        autocomplete="off">
                    <div class="hasil-search-bahan absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 max-h-60 overflow-y-auto"></div>
                </div>
                <input type="hidden" name="bahan_baku_id[]" class="input-id-bahan">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Jumlah Dipakai <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <input
                        type="number"
                        name="jumlah_terpakai[]"
                        step="0.01"
                        min="0"
                        value="0"
                        class="input-jumlah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm">
                    <span class="satuan-bahan text-sm text-gray-500 min-w-10">-</span>
                </div>
                <p class="peringatan-stok hidden text-xs text-red-600 mt-1">
                    Jumlah melebihi stok yang tersedia!
                </p>
                <p class="peringatan-kosong hidden text-xs text-red-600 mt-1">
                    Jumlah dipakai harus diisi lebih dari 0!
                </p>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <div class="info-stok text-xs text-gray-500">
                Pilih bahan baku terlebih dahulu
            </div>
            <button type="button" class="btn-hapus-bahan text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-500/10">
                Hapus
            </button>
        </div>
    `;

    containerBahan.appendChild(div);
    aktifkanSearchBahan(div);
    return div;
}

tombolTambahBahan.addEventListener('click', function () {
    tambahBarisBahan();
    hitungTotalSemua();
});

function aktifkanSearchBahan(element) {
    const inputSearch = element.querySelector('.input-search-bahan');
    const hasilSearch = element.querySelector('.hasil-search-bahan');
    const inputId = element.querySelector('.input-id-bahan');
    const satuan = element.querySelector('.satuan-bahan');
    const infoStok = element.querySelector('.info-stok');
    const inputJumlah = element.querySelector('.input-jumlah');
    const peringatanStok = element.querySelector('.peringatan-stok');
    const peringatanKosong = element.querySelector('.peringatan-kosong');

    inputJumlah.addEventListener('input', function () {
        cekJumlahMelebihiStok(inputJumlah, peringatanStok, peringatanKosong, inputId);
        hitungTotalSemua();
    });

    inputSearch.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        if (keyword.length === 0) {
            hasilSearch.classList.add('hidden');
            return;
        }

        const hasil = daftarBahanBaku.filter(b => b.nama_bahan.toLowerCase().includes(keyword));
        hasilSearch.innerHTML = '';

        if (hasil.length === 0) {
            hasilSearch.innerHTML = '<div class="p-3 text-xs text-gray-500">Bahan baku tidak ditemukan</div>';
        } else {
            hasil.forEach(bahan => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full px-4 py-2.5 text-left hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700/60 last:border-0';
                item.innerHTML = `
                    <div class="font-medium text-gray-800 dark:text-white text-sm">${bahan.nama_bahan}</div>
                    <div class="text-xs text-gray-500">
                        Stok: ${formatStok(bahan.stok, bahan.satuan ? bahan.satuan.tipe : 'desimal')} ${bahan.satuan ? bahan.satuan.nama : '-'}
                    </div>
                `;
                item.addEventListener('click', function () {
                    pilihBahan(bahan, inputSearch, hasilSearch, inputId, satuan, infoStok, inputJumlah);
                    hitungTotalSemua();
                });
                hasilSearch.appendChild(item);
            });
        }
        hasilSearch.classList.remove('hidden');
    });

    element.querySelector('.btn-hapus-bahan').addEventListener('click', function () {
        element.remove();
        hitungTotalSemua();
    });

    document.addEventListener('click', function (e) {
        if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
            hasilSearch.classList.add('hidden');
        }
    });
}

function pilihBahan(bahan, inputSearch, hasilSearch, inputId, satuan, infoStok, inputJumlah) {
    const namaSatuan = bahan.satuan ? bahan.satuan.nama : '-';
    const tipeSatuan = bahan.satuan ? bahan.satuan.tipe : 'desimal';

    inputSearch.value = bahan.nama_bahan;
    inputId.value = bahan.id;
    satuan.textContent = namaSatuan;
    infoStok.innerHTML = `Stok tersedia: <strong>${formatStok(bahan.stok, tipeSatuan)} ${namaSatuan}</strong>`;

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
}

// Inisialisasi Bahan Baku Lama (jika ada)
if (Array.isArray(oldBahanBakuId) && oldBahanBakuId.length > 0) {
    oldBahanBakuId.forEach((id, index) => {
        const bahan = daftarBahanBaku.find(b => b.id == id);
        if (!bahan) return;

        const div = tambahBarisBahan();
        const inputSearch = div.querySelector('.input-search-bahan');
        const hasilSearch = div.querySelector('.hasil-search-bahan');
        const inputId = div.querySelector('.input-id-bahan');
        const satuan = div.querySelector('.satuan-bahan');
        const infoStok = div.querySelector('.info-stok');
        const inputJumlah = div.querySelector('.input-jumlah');

        pilihBahan(bahan, inputSearch, hasilSearch, inputId, satuan, infoStok, inputJumlah);
        inputJumlah.value = oldJumlahTerpakai[index] ?? 0;
        inputJumlah.dispatchEvent(new Event('input'));
    });
} else {
    tambahBarisBahan();
}


/* ==========================================================================
   2. KADALUARSA LOGIC
   ========================================================================== */
const tipeTanggal = document.getElementById('tipeTanggal');
const tipeJam = document.getElementById('tipeJam');
const inputKadaluarsaTanggal = document.getElementById('inputKadaluarsaTanggal');
const inputKadaluarsaJam = document.getElementById('inputKadaluarsaJam');
const fieldTanggalProduksi = document.querySelector('input[name="tanggal"]');
const fieldJamProduksi = document.querySelector('input[name="jam_produksi"]');
const fieldKadaluarsaTanggal = document.getElementById('kadaluarsa_tanggal');
const fieldKadaluarsaJam = document.getElementById('kadaluarsa_jam');
const peringatanKadaluarsaTanggal = document.getElementById('peringatanKadaluarsaTanggal');
const peringatanKadaluarsaJam = document.getElementById('peringatanKadaluarsaJam');
const infoWaktuKadaluarsa = document.getElementById('infoWaktuKadaluarsa');

function toggleKadaluarsa() {
    if (tipeJam.checked) {
        inputKadaluarsaTanggal.classList.add('hidden');
        inputKadaluarsaJam.classList.remove('hidden');
        fieldKadaluarsaTanggal.value = '';
        peringatanKadaluarsaTanggal.classList.add('hidden');
    } else {
        inputKadaluarsaTanggal.classList.remove('hidden');
        inputKadaluarsaJam.classList.add('hidden');
        fieldKadaluarsaJam.value = '';
        peringatanKadaluarsaJam.classList.add('hidden');
        infoWaktuKadaluarsa.classList.add('hidden');
    }
    hitungTotalSemua();
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
    const rawVal = String(fieldKadaluarsaJam.value ?? '').trim();
    const nilaiJam = Number(rawVal);

    if (!rawVal || isNaN(nilaiJam) || nilaiJam < 1 || !Number.isInteger(nilaiJam)) {
        peringatanKadaluarsaJam.classList.remove('hidden');
        fieldKadaluarsaJam.classList.add('border-red-500');
        infoWaktuKadaluarsa.classList.add('hidden');
        return false;
    }

    peringatanKadaluarsaJam.classList.add('hidden');
    fieldKadaluarsaJam.classList.remove('border-red-500');

    if (fieldTanggalProduksi.value && fieldJamProduksi.value) {
        const jamBersih = String(fieldJamProduksi.value).slice(0, 5);
        const mulai = new Date(`${fieldTanggalProduksi.value}T${jamBersih}:00`);
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

const sinkronKadaluarsa = () => {
    cekKadaluarsaTanggal();
    cekKadaluarsaJam();
    hitungTotalSemua();
};

tipeTanggal.addEventListener('change', toggleKadaluarsa);
tipeJam.addEventListener('change', toggleKadaluarsa);
fieldKadaluarsaTanggal.addEventListener('input', sinkronKadaluarsa);
fieldKadaluarsaTanggal.addEventListener('change', sinkronKadaluarsa);
fieldTanggalProduksi.addEventListener('input', sinkronKadaluarsa);
fieldTanggalProduksi.addEventListener('change', sinkronKadaluarsa);
fieldKadaluarsaJam.addEventListener('input', sinkronKadaluarsa);
fieldKadaluarsaJam.addEventListener('change', sinkronKadaluarsa);
fieldJamProduksi.addEventListener('input', sinkronKadaluarsa);
fieldJamProduksi.addEventListener('change', sinkronKadaluarsa);


/* ==========================================================================
   3. MULTI-PRODUK (DAFTAR PRODUK YANG DIPRODUKSI) LOGIC - PERSIS BAHAN BAKU
   ========================================================================== */
let counterProduk = 0;
const containerProduk = document.getElementById('produk-container');
const btnTambahProduk = document.getElementById('btn-tambah-produk');

function esc(t) {
    return String(t || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
}

function getSelectedProdukIds(excludeCardIndex = null) {
    const ids = [];
    document.querySelectorAll('.card-item-produk').forEach(c => {
        if (excludeCardIndex !== null && c.dataset.cardIndex == excludeCardIndex) return;
        const inp = c.querySelector('.input-id-produk');
        if (inp && inp.value) ids.push(String(inp.value));
    });
    return ids;
}

function tambahBarisProduk(oldData = null) {
    const cardIndex = counterProduk++;
    const div = document.createElement('div');
    div.className = 'card-item-produk relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800 space-y-3';
    div.dataset.cardIndex = cardIndex;

    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="kolom-search-produk md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cari Produk <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="text"
                        class="input-search-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm"
                        placeholder="Ketik nama produk..."
                        autocomplete="off">
                    <div class="hasil-search-produk absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 max-h-60 overflow-y-auto"></div>
                </div>
                <input type="hidden" name="produk_items[${cardIndex}][produk_id]" class="input-id-produk">
            </div>

            <div class="kolom-input-biasa grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah Hasil <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            min="0"
                            value="0"
                            name="produk_items[${cardIndex}][jumlah_hasil]"
                            class="input-biasa-hasil w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                        <span class="text-xs text-gray-500">pcs</span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pesanan
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            min="0"
                            value="0"
                            name="produk_items[${cardIndex}][kebutuhan_produksi]"
                            class="input-biasa-kebutuhan w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                        <span class="text-xs text-gray-500">pcs</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="peringatan-biasa-wrapper hidden">
            <p class="peringatan-biasa-hasil hidden text-xs text-red-600">Jumlah hasil harus lebih dari 0!</p>
            <p class="peringatan-biasa-kebutuhan hidden text-xs text-red-600">Pesanan tidak boleh melebihi Jumlah Hasil!</p>
        </div>

        {{-- Rincian per Varian (jika produk punya varian) --}}
        <div class="wadah-varian-produk hidden mt-2 pt-3 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-gray-750 dark:text-gray-300">
                    Rincian per Varian <span class="text-red-500">*</span>
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Subtotal: <strong class="text-brand-600 dark:text-brand-400 subtotal-hasil-val">0</strong> pcs hasil, <strong class="subtotal-pesanan-val">0</strong> pesanan
                </span>
            </div>
            <div class="list-baris-varian space-y-2"></div>
            <p class="peringatan-varian-kosong hidden text-xs text-red-600 mt-1">Minimal salah satu varian harus memiliki Jumlah Hasil lebih dari 0!</p>
            <p class="peringatan-varian-kebutuhan hidden text-xs text-red-600 mt-1">Pesanan tidak boleh melebihi Jumlah Hasil pada varian yang sama!</p>
        </div>

        <div class="mt-3 flex items-center justify-between pt-1">
            <div class="info-produk text-xs text-gray-500">
                Pilih produk terlebih dahulu
            </div>
            <button type="button" class="btn-hapus-produk text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-500/10">
                Hapus
            </button>
        </div>
    `;

    containerProduk.appendChild(div);
    aktifkanSearchProduk(div, cardIndex, oldData);
    hitungTotalSemua();
    return div;
}

btnTambahProduk.addEventListener('click', function () {
    tambahBarisProduk();
    hitungTotalSemua();
});

function aktifkanSearchProduk(element, cardIndex, oldData = null) {
    const inputSearch = element.querySelector('.input-search-produk');
    const hasilSearch = element.querySelector('.hasil-search-produk');
    const inBiasaHasil = element.querySelector('.input-biasa-hasil');
    const inBiasaKebutuhan = element.querySelector('.input-biasa-kebutuhan');

    inBiasaHasil.addEventListener('input', hitungTotalSemua);
    inBiasaKebutuhan.addEventListener('input', hitungTotalSemua);

    inputSearch.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        if (keyword.length === 0) {
            hasilSearch.classList.add('hidden');
            return;
        }

        const selectedIds = getSelectedProdukIds(cardIndex);
        const hasil = daftarProduk.filter(p => {
            return p.nama_produk.toLowerCase().includes(keyword)
                || (p.kode_cari || '').toLowerCase().includes(keyword);
        });

        hasilSearch.innerHTML = '';
        if (hasil.length === 0) {
            hasilSearch.innerHTML = '<div class="p-3 text-xs text-gray-500">Produk tidak ditemukan</div>';
        } else {
            hasil.forEach(produk => {
                const sudahDipilih = selectedIds.includes(String(produk.id));
                const item = document.createElement('button');
                item.type = 'button';
                item.disabled = sudahDipilih;
                item.className = `block w-full px-4 py-2.5 text-left border-b border-gray-50 dark:border-gray-700/60 last:border-0 ${
                    sudahDipilih
                        ? 'opacity-40 bg-gray-50 dark:bg-gray-900 cursor-not-allowed'
                        : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                }`;

                item.innerHTML = `
                    <div class="font-medium text-gray-800 dark:text-white text-sm">
                        ${produk.nama_produk} ${sudahDipilih ? '<span class="text-red-500 font-semibold text-xs ml-1">(Sudah dipilih)</span>' : ''}
                    </div>
                    <div class="text-xs text-gray-500">
                        Kode: ${produk.kode_detail || '-'}
                    </div>
                `;

                if (!sudahDipilih) {
                    item.addEventListener('click', function () {
                        pilihProduk(produk, element);
                    });
                }
                hasilSearch.appendChild(item);
            });
        }
        hasilSearch.classList.remove('hidden');
    });

    element.querySelector('.btn-hapus-produk').addEventListener('click', function () {
        const totalRows = document.querySelectorAll('.card-item-produk').length;
        if (totalRows > 1) {
            element.remove();
            hitungTotalSemua();
        } else {
            appAlert('Minimal harus ada satu produk.');
        }
    });

    document.addEventListener('click', function (e) {
        if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
            hasilSearch.classList.add('hidden');
        }
    });

    if (oldData && oldData.produk_id) {
        const pLama = daftarProduk.find(p => String(p.id) === String(oldData.produk_id));
        if (pLama) {
            pilihProduk(pLama, element, oldData);
        }
    }
}

function pilihProduk(produk, div, oldValues = null) {
    const inputSearch = div.querySelector('.input-search-produk');
    const hasilSearch = div.querySelector('.hasil-search-produk');
    const inputId = div.querySelector('.input-id-produk');
    const infoProduk = div.querySelector('.info-produk');
    const kolomSearch = div.querySelector('.kolom-search-produk');
    const kolomInputBiasa = div.querySelector('.kolom-input-biasa');
    const wadahVarian = div.querySelector('.wadah-varian-produk');
    const listBarisVarian = div.querySelector('.list-baris-varian');
    const inBiasaHasil = div.querySelector('.input-biasa-hasil');
    const inBiasaKebutuhan = div.querySelector('.input-biasa-kebutuhan');

    inputSearch.value = produk.nama_produk;
    inputId.value = produk.id;
    hasilSearch.classList.add('hidden');

    const cardIndex = div.dataset.cardIndex;
    const daftarVarian = Array.isArray(produk.daftar_varian_produksi) ? produk.daftar_varian_produksi : [];

    if (produk.punya_varian && daftarVarian.length > 0) {
        // Mode Varian
        kolomSearch.classList.remove('md:col-span-2');
        kolomSearch.classList.add('md:col-span-3');
        kolomInputBiasa.classList.add('hidden');
        inBiasaHasil.disabled = true;
        inBiasaKebutuhan.disabled = true;

        wadahVarian.classList.remove('hidden');
        listBarisVarian.innerHTML = '';

        daftarVarian.forEach(opsi => {
            const varIdStr = String(opsi.id);
            const valHasil = oldValues?.varian_hasil?.[varIdStr] ?? 0;
            const valKebutuhan = oldValues?.varian_kebutuhan?.[varIdStr] ?? 0;

            const row = document.createElement('div');
            row.className = 'baris-varian-item grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 items-center border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 bg-white dark:bg-gray-900';
            row.dataset.varianId = opsi.id;

            row.innerHTML = `
                <div>
                    <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                        ${esc(opsi.nama)}
                        ${opsi.perlu_produksi ? '<span class="ml-1.5 rounded-full bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 text-[10px] font-semibold px-2 py-0.5">Sedang Diproduksi</span>' : ''}
                    </span>
                    <span class="block text-xs text-gray-400">Stok saat ini: ${opsi.stok} pcs</span>
                </div>
                <div class="w-full sm:w-32">
                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Jumlah Hasil</label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" min="0" value="${valHasil}" name="produk_items[${cardIndex}][varian_hasil][${opsi.id}]" class="input-varian-hasil w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                        <span class="text-xs text-gray-500">pcs</span>
                    </div>
                </div>
                <div class="w-full sm:w-32">
                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Pesanan</label>
                    <div class="flex items-center gap-1.5">
                        <input type="number" min="0" value="${valKebutuhan}" name="produk_items[${cardIndex}][varian_kebutuhan][${opsi.id}]" class="input-varian-kebutuhan w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                        <span class="text-xs text-gray-500">pcs</span>
                    </div>
                </div>
            `;

            listBarisVarian.appendChild(row);
        });

        listBarisVarian.querySelectorAll('.input-varian-hasil, .input-varian-kebutuhan').forEach(inp => {
            inp.addEventListener('input', hitungTotalSemua);
        });

        infoProduk.innerHTML = `Memiliki <strong>${daftarVarian.length} varian</strong> (Kode: ${produk.kode_detail || '-'})`;

    } else {
        // Mode Tanpa Varian
        kolomSearch.classList.remove('md:col-span-3');
        kolomSearch.classList.add('md:col-span-2');
        kolomInputBiasa.classList.remove('hidden');
        inBiasaHasil.disabled = false;
        inBiasaKebutuhan.disabled = false;
        wadahVarian.classList.add('hidden');

        if (oldValues) {
            inBiasaHasil.value = oldValues.jumlah_hasil ?? 0;
            inBiasaKebutuhan.value = oldValues.kebutuhan_produksi ?? 0;
        }

        infoProduk.innerHTML = `Kode: <strong>${produk.kode_detail || '-'}</strong>`;
    }

    hitungTotalSemua();
}


/* ==========================================================================
   4. VALIDASI & KALKULASI TOTAL
   ========================================================================== */
function hitungTotalSemua() {
    const cards = document.querySelectorAll('.card-item-produk');
    let totalJenis = 0;
    let totalHasil = 0;
    let totalPesanan = 0;
    let adaErrorProduk = false;

    cards.forEach(card => {
        const inputId = card.querySelector('.input-id-produk');
        if (!inputId || !inputId.value) {
            adaErrorProduk = true;
            return;
        }

        totalJenis++;
        const wrapperVarian = card.querySelector('.wadah-varian-produk');
        const kolomInputBiasa = card.querySelector('.kolom-input-biasa');

        if (!wrapperVarian.classList.contains('hidden')) {
            // Mode Varian
            let subHasil = 0;
            let subPesanan = 0;
            let barisError = false;

            card.querySelectorAll('.baris-varian-item').forEach(baris => {
                const inHasil = baris.querySelector('.input-varian-hasil');
                const inKebutuhan = baris.querySelector('.input-varian-kebutuhan');
                const valHasil = parseInt(inHasil.value, 10) || 0;
                const valKebutuhan = parseInt(inKebutuhan.value, 10) || 0;

                if (valKebutuhan > valHasil) {
                    inKebutuhan.classList.add('border-red-500');
                    barisError = true;
                    adaErrorProduk = true;
                } else {
                    inKebutuhan.classList.remove('border-red-500');
                }

                subHasil += valHasil;
                subPesanan += valKebutuhan;
            });

            const errTotal = card.querySelector('.peringatan-varian-kosong');
            const errKebutuhan = card.querySelector('.peringatan-varian-kebutuhan');

            if (subHasil <= 0) {
                errTotal.classList.remove('hidden');
                adaErrorProduk = true;
            } else {
                errTotal.classList.add('hidden');
            }

            if (barisError) {
                errKebutuhan.classList.remove('hidden');
            } else {
                errKebutuhan.classList.add('hidden');
            }

            card.querySelector('.subtotal-hasil-val').textContent = subHasil;
            card.querySelector('.subtotal-pesanan-val').textContent = subPesanan;

            totalHasil += subHasil;
            totalPesanan += subPesanan;

        } else if (!kolomInputBiasa.classList.contains('hidden')) {
            // Mode Tanpa Varian
            const inHasil = card.querySelector('.input-biasa-hasil');
            const inKebutuhan = card.querySelector('.input-biasa-kebutuhan');
            const peringatanWrapper = card.querySelector('.peringatan-biasa-wrapper');
            const errHasil = card.querySelector('.peringatan-biasa-hasil');
            const errKebutuhan = card.querySelector('.peringatan-biasa-kebutuhan');

            const valHasil = parseInt(inHasil.value, 10) || 0;
            const valKebutuhan = parseInt(inKebutuhan.value, 10) || 0;

            let hasError = false;

            if (valHasil <= 0) {
                errHasil.classList.remove('hidden');
                inHasil.classList.add('border-red-500');
                hasError = true;
                adaErrorProduk = true;
            } else {
                errHasil.classList.add('hidden');
                inHasil.classList.remove('border-red-500');
            }

            if (valKebutuhan > valHasil) {
                errKebutuhan.classList.remove('hidden');
                inKebutuhan.classList.add('border-red-500');
                hasError = true;
                adaErrorProduk = true;
            } else {
                errKebutuhan.classList.add('hidden');
                inKebutuhan.classList.remove('border-red-500');
            }

            if (hasError) {
                peringatanWrapper.classList.remove('hidden');
            } else {
                peringatanWrapper.classList.add('hidden');
            }

            totalHasil += valHasil;
            totalPesanan += valKebutuhan;
        }
    });

    perbaruiTombolSubmitProduksi(adaErrorProduk || totalJenis === 0 || totalHasil === 0);
}

function perbaruiTombolSubmitProduksi(adaErrorProduk = false) {
    const adaErrorStokBahan = document.querySelectorAll('.peringatan-stok:not(.hidden)').length > 0;
    const adaErrorKosongBahan = document.querySelectorAll('.peringatan-kosong:not(.hidden)').length > 0;
    const bahanRows = document.querySelectorAll('#bahan-container > div');
    const adaBahanTerpilih = Array.from(bahanRows).some(row => {
        const id = row.querySelector('.input-id-bahan')?.value;
        const jumlah = parseFloat(row.querySelector('.input-jumlah')?.value) || 0;
        return id && jumlah > 0;
    });

    const kadaluarsaTanggalValid = cekKadaluarsaTanggal();
    const kadaluarsaJamValid = cekKadaluarsaJam();

    const btn = document.getElementById('btnSubmitProduksi');
    if (btn) {
        btn.disabled = adaErrorProduk || adaErrorStokBahan || adaErrorKosongBahan || !adaBahanTerpilih || !kadaluarsaTanggalValid || !kadaluarsaJamValid;
    }
}

// Inisialisasi Produk Lama (jika ada)
if (Array.isArray(oldProdukItems) && oldProdukItems.length > 0) {
    oldProdukItems.forEach(item => {
        tambahBarisProduk(item);
    });
} else {
    tambahBarisProduk();
}

toggleKadaluarsa();
cekKadaluarsaJam();
hitungTotalSemua();

// Form Submit Handler
const formProduksi = document.getElementById('formProduksi');
if (formProduksi) {
    formProduksi.addEventListener('submit', function (e) {
        const kadaluarsaTanggalValid = cekKadaluarsaTanggal();
        const kadaluarsaJamValid = cekKadaluarsaJam();

        let bahanValid = true;
        const bahanRows = document.querySelectorAll('#bahan-container > div');
        let adaBahanTerpilih = false;

        bahanRows.forEach(row => {
            const id = row.querySelector('.input-id-bahan')?.value;
            const inJumlah = row.querySelector('.input-jumlah');
            const peringatanKosong = row.querySelector('.peringatan-kosong');
            const peringatanStok = row.querySelector('.peringatan-stok');

            if (id) {
                const nilai = parseFloat(inJumlah.value) || 0;
                const stokMax = parseFloat(inJumlah.dataset.stokMax || 0);
                if (nilai <= 0) {
                    peringatanKosong?.classList.remove('hidden');
                    inJumlah.classList.add('border-red-500');
                    bahanValid = false;
                } else if (nilai > stokMax) {
                    peringatanStok?.classList.remove('hidden');
                    inJumlah.classList.add('border-red-500');
                    bahanValid = false;
                } else {
                    adaBahanTerpilih = true;
                }
            }
        });

        if (!adaBahanTerpilih) {
            bahanValid = false;
        }

        let produkValid = true;
        const cards = document.querySelectorAll('.card-item-produk');
        if (cards.length === 0) produkValid = false;

        cards.forEach(card => {
            const id = card.querySelector('.input-id-produk')?.value;
            if (!id) produkValid = false;

            const wrapperVarian = card.querySelector('.wadah-varian-produk');
            if (!wrapperVarian.classList.contains('hidden')) {
                let subHasil = 0;
                card.querySelectorAll('.baris-varian-item').forEach(baris => {
                    const h = parseInt(baris.querySelector('.input-varian-hasil').value, 10) || 0;
                    const k = parseInt(baris.querySelector('.input-varian-kebutuhan').value, 10) || 0;
                    if (k > h) produkValid = false;
                    subHasil += h;
                });
                if (subHasil <= 0) produkValid = false;
            } else {
                const h = parseInt(card.querySelector('.input-biasa-hasil').value, 10) || 0;
                const k = parseInt(card.querySelector('.input-biasa-kebutuhan').value, 10) || 0;
                if (h <= 0 || k > h) produkValid = false;
            }
        });

        if (!kadaluarsaTanggalValid || !kadaluarsaJamValid || !bahanValid || !produkValid) {
            e.preventDefault();
            hitungTotalSemua();
            appAlert('Mohon periksa kembali form. Pastikan semua produk, varian, bahan baku, dan kadaluwarsa sudah diisi dengan benar.');
        }
    });
}
</script>

@endsection