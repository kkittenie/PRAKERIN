@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Bahan Baku" parentTitle="Bahan Baku" :parentUrl="route('bahan-baku.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Bahan Baku">

        <form action="{{ route('bahan-baku.store') }}" method="POST">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nama Bahan --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Bahan <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_bahan"
                        value="{{ old('nama_bahan') }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_bahan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            {{-- Satuan --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Satuan <span class="text-red-500">*</span>
                </label>

                <div class="flex gap-2">
                    <select
                        id="satuan_id"
                        name="satuan_id"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="">Pilih satuan</option>

                        @foreach($satuan as $item)
                            <option
                                value="{{ $item->id }}"
                                data-tipe="{{ $item->tipe }}"
                                {{ old('satuan_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button" id="btnTambahSatuan"
                        class="h-12 w-12 shrink-0 rounded-lg bg-brand-500 text-white text-xl hover:bg-brand-600">
                        +
                    </button>
                </div>

                <div id="formTambahSatuan" class="mt-2 hidden gap-2 flex">
                    <input type="text" id="inputSatuanBaru" placeholder="Ketik nama satuan baru..."
                        class="h-10 flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3">
                        <button type="button" id="btnSimpanSatuan" class="h-10 px-3 rounded-lg bg-brand-500 text-white text-sm hover:bg-brand-600">Simpan</button>                    <button type="button" id="btnBatalSatuan" class="h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm">Batal</button>
                </div>

                <div id="notifSatuanBaru" class="mt-2 hidden rounded-lg border px-4 py-3 text-sm"></div>

                @error('satuan_id')
                    <small class="text-red-500">{{ $message }}</small>
                @enderror
            </div>

                {{-- Stok --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok
                    </label>

                    <input
                    type="number"
                    step="0.01"
                    name="stok"
                    id="inputStok"
                    placeholder="1"
                    value="{{ old('stok') }}"
                    class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Minimum --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Minimum
                    </label>

                   <input
                    type="number"
                    step="0.01"
                    name="stok_minimum"
                    id="inputStokMinimum"
                    placeholder="1"
                    value="{{ old('stok_minimum') }}"
                    class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                                
                    @error('stok_minimum')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('bahan-baku.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Simpan Bahan Baku

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

<script>
const satuanId = document.getElementById('satuan_id');
const inputStok = document.getElementById('inputStok');
const inputStokMinimum = document.getElementById('inputStokMinimum');

function terapkanTipeSatuan() {
    const selected = satuanId.options[satuanId.selectedIndex];
    const tipe = selected ? selected.dataset.tipe : 'desimal';

    if (tipe === 'bulat') {
        inputStok.step = '1';
        inputStokMinimum.step = '1';
    } else {
        inputStok.step = '0.01';
        inputStokMinimum.step = '0.01';
    }
}

satuanId.addEventListener('change', terapkanTipeSatuan);
window.addEventListener('load', terapkanTipeSatuan);

document.getElementById('btnTambahSatuan').addEventListener('click', function () {
    document.getElementById('formTambahSatuan').classList.toggle('hidden');
    document.getElementById('inputSatuanBaru').focus();
});

document.getElementById('btnBatalSatuan').addEventListener('click', function () {
    document.getElementById('formTambahSatuan').classList.add('hidden');
    document.getElementById('inputSatuanBaru').value = '';
    document.getElementById('notifSatuanBaru').classList.add('hidden');
});

document.getElementById('btnSimpanSatuan').addEventListener('click', function () {
    const nama = document.getElementById('inputSatuanBaru').value.trim();
    const notif = document.getElementById('notifSatuanBaru');

    function tampilkanNotif(pesan, tipe) {
        notif.textContent = pesan;
        notif.classList.remove('hidden');
        if (tipe === 'error') {
            notif.className = 'mt-2 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400';
        } else {
            notif.className = 'mt-2 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400';
        }
    }

    if (!nama) {
        tampilkanNotif('Nama satuan wajib diisi.', 'error');
        return;
    }

    fetch("{{ route('satuan.storeAjax') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ nama: nama })
    })
    .then(async response => {
        const json = await response.json();

        if (!response.ok) {
            tampilkanNotif(json.message, 'error');
            return;
        }

        const select = document.getElementById('satuan_id');
        const option = document.createElement('option');
        option.value = json.data.id;
        option.textContent = json.data.nama;
        option.dataset.tipe = json.data.tipe;
        option.selected = true;
        select.appendChild(option);

        document.getElementById('inputSatuanBaru').value = '';
        document.getElementById('formTambahSatuan').classList.add('hidden');
        notif.classList.add('hidden');

        terapkanTipeSatuan();
    })
    .catch(() => {
        tampilkanNotif('Gagal menambah satuan, coba lagi.', 'error');
    });
});
</script>

@endsection