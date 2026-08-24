@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Produk" parentTitle="Produk" :parentUrl="route('produk.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Produk">

        <form action="{{ route('produk.update',$produk->id) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Kategori --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kategori Produk <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="kategori_id"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="">Pilih Kategori</option>

                        @foreach($kategori as $item)
                            <option
                                value="{{ $item->id }}"
                                {{ old('kategori_id',$produk->kategori_id)==$item->id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach

                    </select>

                    @error('kategori_id')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Kode Produk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kode Produk <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="kode_produk"
                        value="{{ old('kode_produk',$produk->kode_produk) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('kode_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nama Produk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_produk"
                        value="{{ old('nama_produk',$produk->nama_produk) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('nama_produk')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

               {{-- Deskripsi --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi Produk
                    </label>

                    <textarea
                        name="deskripsi"
                        rows="3"
                        placeholder="Deskripsi singkat produk, tampil di Detail Produk Member App"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi', $produk->deskripsi) }}</textarea>

                    @error('deskripsi')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

               {{-- Harga Jual --}}
                <div id="wrapper-harga-jual">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_jual"
                            placeholder="10.000"
                            value="{{ number_format((int) old('harga_jual', $produk->harga_jual), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    @error('harga_jual')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Punya Varian Ukuran --}}
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 h-12 cursor-pointer">
                        <input type="hidden" name="punya_ukuran" value="0">
                        <input
                            type="checkbox"
                            id="punya_ukuran"
                            name="punya_ukuran"
                            value="1"
                            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                            {{ old('punya_ukuran', $produk->punya_ukuran ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Produk ini punya varian ukuran (Besar/Kecil)</span>
                    </label>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">
                        Kalau dicentang, harga jual produk akan dipisah berdasarkan ukuran Besar dan Kecil.
                    </span>
                    @error('punya_ukuran')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Harga Besar --}}
                <div id="wrapper-harga-besar" class="hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Besar <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_besar"
                            placeholder="15.000"
                            value="{{ number_format((int) old('harga_besar', $produk->harga_besar ?? 0), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    @error('harga_besar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Harga Kecil --}}
                <div id="wrapper-harga-kecil" class="hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Kecil <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_kecil"
                            placeholder="10.000"
                            value="{{ number_format((int) old('harga_kecil', $produk->harga_kecil ?? 0), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    @error('harga_kecil')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Harga Modal --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Harga Modal <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                            Rp
                        </span>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="harga_modal"
                            placeholder="8.000"
                            value="{{ number_format((int) old('harga_modal', $produk->harga_modal), 0, ',', '.') }}"
                            class="input-rupiah h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    @error('harga_modal')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok --}}
                <div id="wrapper-stok">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Awal
                    </label>

                    <input
                        type="number"
                        name="stok"
                        placeholder="1"
                        value="{{ old('stok',$produk->stok) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Besar --}}
                <div id="wrapper-stok-besar" class="hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Besar
                    </label>

                    <input
                        type="number"
                        name="stok_besar"
                        placeholder="1"
                        value="{{ old('stok_besar', $produk->stok_besar) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok_besar')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Stok Kecil --}}
                <div id="wrapper-stok-kecil" class="hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stok Kecil
                    </label>

                    <input
                        type="number"
                        name="stok_kecil"
                        placeholder="1"
                        value="{{ old('stok_kecil', $produk->stok_kecil) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok_kecil')
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
                        name="stok_minimum"
                        placeholder="1"
                        value="{{ old('stok_minimum',$produk->stok_minimum) }}"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('stok_minimum')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Aktif
                    </label>

                    <select
                        name="status_aktif"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="1" {{ old('status_aktif',$produk->status_aktif)==1 ? 'selected':'' }}>
                            Aktif
                        </option>

                        <option value="0" {{ old('status_aktif',$produk->status_aktif)==0 ? 'selected':'' }}>
                            Tidak Aktif
                        </option>

                    </select>
                </div>

                {{-- Status Ketersediaan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Ketersediaan
                    </label>

                    <select
                        name="status_ketersediaan"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                        <option value="stok_ada"
                        {{ old('status_ketersediaan',$produk->status_ketersediaan)=='stok_ada' ? 'selected':'' }}>
                        Stok Ada
                        </option>

                        <option value="perlu_diproduksi"
                        {{ old('status_ketersediaan',$produk->status_ketersediaan)=='perlu_diproduksi' ? 'selected':'' }}>
                        Perlu Diproduksi
                        </option>

                    </select>

                    <small class="text-gray-400">
                        Otomatis menjadi "Perlu Diproduksi" jika stok 0.
                    </small>
                </div>

                {{-- Foto --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Foto Produk
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

                    @error('foto')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a
                    href="{{ route('produk.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

                    Update Produk

                </button>

            </div>

        </form>

    </x-common.component-card>

    <div x-data="{
            modalEditTerbuka: false,
            modalHapusTerbuka: false,
            barisEdit: null,
            formHapus: null,
            namaHapus: '',
            bukaModalEdit(baris) {
                this.barisEdit = baris;
                this.modalEditTerbuka = true;
            },
            bukaModalHapus(form, nama) {
                this.formHapus = form;
                this.namaHapus = nama;
                this.modalHapusTerbuka = true;
            },
            konfirmasiHapus() {
                if (this.formHapus) { this.formHapus.submit(); }
                this.modalHapusTerbuka = false;
            }
        }">

        <x-common.component-card title="Ukuran Dinamis (Baru)">

            <p class="text-xs text-amber-600 dark:text-amber-400 mb-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 rounded-lg px-3 py-2">
                Ini sistem ukuran baru yang bisa nambah berapa pun varian (gak cuma Kecil/Besar). Untuk sementara
                kasir &amp; produksi <strong>masih</strong> pakai kolom "Besar"/"Kecil" yang lama di atas — baris di
                bawah ini baru dipakai setelah bagian kasir &amp; produksi ikut diperbarui.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                            <th class="py-2 pr-3">Ukuran</th>
                            <th class="py-2 pr-3">Harga Modal</th>
                            <th class="py-2 pr-3">Harga Jual</th>
                            <th class="py-2 pr-3">Stok</th>
                            <th class="py-2 pr-3">Stok Min.</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 pr-3">Aktif</th>
                            <th class="py-2 pr-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produk->produkUkurans as $baris)
                        <tr class="border-b border-gray-50 dark:border-gray-900">
                            <td class="py-2 pr-3 font-semibold text-gray-900 dark:text-white">{{ $baris->ukuran->nama_ukuran }}</td>
                            <td class="py-2 pr-3">Rp {{ number_format($baris->harga_modal, 0, ',', '.') }}</td>
                            <td class="py-2 pr-3">Rp {{ number_format($baris->harga_jual, 0, ',', '.') }}</td>
                            <td class="py-2 pr-3">{{ $baris->stok }}</td>
                            <td class="py-2 pr-3">{{ $baris->stok_minimum }}</td>
                            <td class="py-2 pr-3">
                                @if($baris->status_ketersediaan === 'perlu_diproduksi')
                                    <span class="px-2 py-0.5 rounded-full text-xxs font-bold bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">Perlu Diproduksi</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xxs font-bold bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Stok Ada</span>
                                @endif
                            </td>
                            <td class="py-2 pr-3">
                                <form action="{{ route('produk-ukuran.toggle-status', $baris->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan">
                                        <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $baris->status_aktif ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                                    </label>
                                </form>
                            </td>
                            <td class="py-2 pr-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5 justify-end">
                                    <button type="button" title="Edit"
                                        @click="bukaModalEdit({
                                            id: {{ $baris->id }},
                                            nama: '{{ addslashes($baris->ukuran->nama_ukuran) }}',
                                            harga_modal: {{ (int) $baris->harga_modal }},
                                            harga_jual: {{ (int) $baris->harga_jual }},
                                            stok: {{ (int) $baris->stok }},
                                            stok_minimum: {{ (int) $baris->stok_minimum }},
                                            status_ketersediaan: '{{ $baris->status_ketersediaan }}'
                                        })"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('produk-ukuran.destroy', $baris->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button" title="Hapus" @click="bukaModalHapus($el.closest('form'), '{{ addslashes($baris->ukuran->nama_ukuran) }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-400 text-sm">Belum ada baris ukuran buat produk ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Form tambah baris ukuran baru --}}
            @if($daftarUkuranPilihan->isNotEmpty())
            <form action="{{ route('produk-ukuran.store', $produk->id) }}" method="POST" class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                @csrf
                <div class="col-span-2 md:col-span-1">
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Ukuran</label>
                    <select name="ukuran_id" required class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Pilih</option>
                        @foreach($daftarUkuranPilihan as $u)
                            <option value="{{ $u->id }}">{{ $u->nama_ukuran }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga Modal</label>
                    <input type="number" name="harga_modal" min="0" required placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                    <input type="number" name="harga_jual" min="0" required placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok</label>
                    <input type="number" name="stok" min="0" required placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok Min.</label>
                    <input type="number" name="stok_minimum" min="0" required placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <button type="submit" class="h-11 w-full rounded-lg bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Tambah Ukuran</button>
                </div>
            </form>
            @else
            <p class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400">
                Semua ukuran aktif di master Ukuran sudah dipakai produk ini. Tambah ukuran baru dulu lewat menu
                <a href="{{ route('ukuran.index') }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">Ukuran</a> kalau perlu varian lain.
            </p>
            @endif

        </x-common.component-card>

        <!-- MODAL EDIT BARIS UKURAN -->
        <div x-show="modalEditTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalEditTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="bg-amber-800 dark:bg-amber-900 text-white p-4">
                    <h3 class="font-extrabold text-sm">Edit Ukuran <span x-text="barisEdit ? barisEdit.nama : ''"></span></h3>
                </div>
                <form :action="barisEdit ? '{{ url('produk-ukuran') }}/' + barisEdit.id : '#'" method="POST" class="p-5 flex flex-col gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga Modal</label>
                        <input type="number" name="harga_modal" min="0" x-model="barisEdit.harga_modal" required class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                        <input type="number" name="harga_jual" min="0" x-model="barisEdit.harga_jual" required class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok</label>
                        <input type="number" name="stok" min="0" x-model="barisEdit.stok" required class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Stok Minimum</label>
                        <input type="number" name="stok_minimum" min="0" x-model="barisEdit.stok_minimum" required class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Status Ketersediaan</label>
                        <select name="status_ketersediaan" x-model="barisEdit.status_ketersediaan" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="stok_ada">Stok Ada</option>
                            <option value="perlu_diproduksi">Perlu Diproduksi</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalEditTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-lg transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL HAPUS BARIS UKURAN -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Baris Ukuran?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Ukuran <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dari produk ini.
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalHapusTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiHapus()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
                </div>
            </div>
        </div>

    </div>

</div>

    <script>

    const checkboxUkuran = document.getElementById('punya_ukuran');
    const wrapperBesar = document.getElementById('wrapper-harga-besar');
    const wrapperKecil = document.getElementById('wrapper-harga-kecil');
    const wrapperHargaJual = document.getElementById('wrapper-harga-jual');
    const wrapperStok = document.getElementById('wrapper-stok');
    const wrapperStokBesar = document.getElementById('wrapper-stok-besar');
    const wrapperStokKecil = document.getElementById('wrapper-stok-kecil');

    function toggleFieldUkuran() {
        const tampil = checkboxUkuran.checked;
        wrapperBesar.classList.toggle('hidden', !tampil);
        wrapperKecil.classList.toggle('hidden', !tampil);
        wrapperHargaJual.classList.toggle('hidden', tampil);
        wrapperStokBesar.classList.toggle('hidden', !tampil);
        wrapperStokKecil.classList.toggle('hidden', !tampil);
        wrapperStok.classList.toggle('hidden', tampil);
    }

    checkboxUkuran.addEventListener('change', toggleFieldUkuran);
    window.addEventListener('load', toggleFieldUkuran);

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