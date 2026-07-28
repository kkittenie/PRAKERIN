@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Bahan Baku" />

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

            <select
                id="satuan_id"
                name="satuan_id"
                class="w-full">

                <option value="">Pilih atau ketik satuan...</option>

                @foreach($satuan as $item)
                    <option
                        value="{{ $item->id }}"
                        {{ old('satuan_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->nama }}
                    </option>
                @endforeach

            </select>

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
                        value="{{ old('stok',0) }}"
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
                        value="{{ old('stok_minimum',0) }}"
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
new TomSelect("#satuan_id", {
    create: function(input, callback) {

        fetch("{{ route('satuan.storeAjax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                nama: input
            })
        })
        .then(response => response.json())
        .then(async response => {

            const json = await response.json();

            if(!response.ok){
                alert(json.message);
                callback();
                return;
            }

            callback({
                value: json.data.id,
                text: json.data.nama
            });

        })
        .catch(error => {

            alert("Satuan gagal ditambahkan");

            callback();

        });
    },

    persist: false,
    maxItems: 1,
    createOnBlur: true,
    placeholder: "Pilih atau ketik satuan..."
});
</script>

@endsection

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js">

new TomSelect("#satuan_id",{
    create:true,
    persist:false,
    maxItems:1,
    createOnBlur:true,
});

</script>

