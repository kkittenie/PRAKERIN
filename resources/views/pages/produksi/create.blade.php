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
    <div>

        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Produk <span class="text-red-500">*</span>
        </label>

        <select
            name="produk_id"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 @error('produk_id') border-red-500 @enderror">
         
            <option value="">Pilih Produk</option>

            @foreach($produk as $item)

            <option
                value="{{ $item->id }}"
                {{ old('produk_id') == $item->id ? 'selected' : '' }}>

                {{ $item->nama_produk }}

            </option>

            @endforeach

        </select>

        @error('produk_id')
            <small class="text-red-500">{{ $message }}</small>
        @enderror

    </div>

    {{-- Tipe Kadaluarsa --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Kadaluarsa Berdasarkan <span class="text-red-500">*</span>
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
                name="kadaluarsa_tanggal"
                value="{{ old('kadaluarsa_tanggal') }}"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

            @error('kadaluarsa_tanggal')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div id="inputKadaluarsaJam" class="hidden">
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    name="kadaluarsa_jam"
                    min="1"
                    placeholder="Contoh: 24"
                    value="{{ old('kadaluarsa_jam') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <span class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">jam</span>
            </div>

            @error('kadaluarsa_jam')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>
    </div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">

Pengambilan Bahan Baku

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
        name="jumlah_hasil"
        value="{{ old('jumlah_hasil') }}"
        min="1"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('jumlah_hasil')
        <small class="text-red-500">{{ $message }}</small>
        @enderror

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

<a
href="{{ route('produksi.index') }}"
class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

Batal

</a>

<button
type="submit"
class="rounded-lg bg-brand-500 px-5 py-2 text-white">
    Simpan Produksi
</button>

</div>

</form>

</x-common.component-card>

</div>

<script>
    const daftarBahanBaku = @json($bahanBaku);
</script>

<script>

let bahanTerpilih = [];

const container = document.getElementById('bahan-container');
const tombolTambah = document.getElementById('btn-tambah-bahan');

tombolTambah.addEventListener('click', function () {

    const id = Date.now();

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

        });

}

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
            }

            hasilSearch.classList.add('hidden');

        }

    const tipeTanggal = document.getElementById('tipeTanggal');
    const tipeJam = document.getElementById('tipeJam');
    const inputTanggal = document.getElementById('inputKadaluarsaTanggal');
    const inputJam = document.getElementById('inputKadaluarsaJam');

    function toggleKadaluarsa() {
        if (tipeJam.checked) {
            inputTanggal.classList.add('hidden');
            inputJam.classList.remove('hidden');
        } else {
            inputTanggal.classList.remove('hidden');
            inputJam.classList.add('hidden');
        }
    }

    tipeTanggal.addEventListener('change', toggleKadaluarsa);
    tipeJam.addEventListener('change', toggleKadaluarsa);
    window.addEventListener('load', toggleKadaluarsa);

</script>

@endsection