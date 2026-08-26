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

                {{-- Harga Modal & Harga Jual, kolom biasa (dipakai kalau produk TIDAK punya ukuran).
                     Ditaruh berdampingan (modal kiri, jual kanan) biar rapi & irit tempat.
                     Ke-hidden pas "Punya Ukuran" dicentang, karena begitu ada varian ukuran,
                     harga modal & jual-nya diisi per-baris di tabel Varian Ukuran di bawah. --}}
                <div id="wrapper-harga-modal">
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
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Produk ini punya varian ukuran</span>
                    </label>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">
                        Kalau dicentang, harga jual & stok produk diatur per ukuran di bawah — bisa nambah berapa pun varian, gak cuma Besar/Kecil.
                    </span>
                    @error('punya_ukuran')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Ukuran Dinamis --}}
                <div id="wrapper-ukuran-dinamis" class="hidden md:col-span-2" x-data="ukuranDinamis()">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Varian Ukuran <span class="text-red-500">*</span>
                    </label>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/60">
                                <tr class="text-left text-gray-500 dark:text-gray-400">
                                    <th class="py-2 px-3">Ukuran</th>
                                    <th class="py-2 px-3">Harga Modal</th>
                                    <th class="py-2 px-3">Harga Jual</th>
                                    <th class="py-2 px-3">Stok</th>
                                    <th class="py-2 px-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="baris.length === 0">
                                    <tr>
                                        <td colspan="5" class="py-3 px-3 text-center text-gray-400 text-xs">Belum ada baris ukuran ditambahkan.</td>
                                    </tr>
                                </template>
                                <template x-for="(item, index) in baris" :key="index">
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="py-2 px-3 font-semibold text-gray-800 dark:text-gray-200" x-text="item.nama"></td>
                                        <td class="py-2 px-3" x-text="'Rp ' + Number(item.harga_modal || 0).toLocaleString('id-ID')"></td>
                                        <td class="py-2 px-3" x-text="'Rp ' + Number(item.harga_jual || 0).toLocaleString('id-ID')"></td>
                                        <td class="py-2 px-3" x-text="item.stok"></td>
                                        <td class="py-2 px-3 text-right">
                                            <div class="inline-flex items-center gap-0.5">
                                                <button type="button" @click="bukaModalEditBaris(index)" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button type="button" @click="bukaModalHapusBaris(index)" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][ukuran_id]'" :value="item.ukuran_id">
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][harga_modal]'" :value="item.harga_modal">
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][harga_jual]'" :value="item.harga_jual">
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][stok]'" :value="item.stok">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-5 gap-2 items-end">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Ukuran</label>
                            <select x-model="pilihUkuranId" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Pilih ukuran</option>
                                <template x-for="u in opsiTersedia" :key="u.id">
                                    <option :value="u.id" x-text="u.nama"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Harga Modal</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                <input type="text" inputmode="numeric" x-model="hargaModalBaru" placeholder="8.000" class="input-rupiah h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Harga Jual</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                <input type="text" inputmode="numeric" x-model="hargaBaru" placeholder="10.000" class="input-rupiah h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Stok</label>
                            <input type="number" min="0" x-model="stokBaru" placeholder="0" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <button type="button" @click="tambahBaris()" class="h-11 w-full rounded-lg bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">+ Tambah Baris</button>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" @click="tampilFormUkuranBaru = !tampilFormUkuranBaru" class="text-xs font-semibold text-brand-600 hover:underline">
                            + Ukuran baru belum ada di daftar? Tambah di sini
                        </button>
                        <div x-show="tampilFormUkuranBaru" class="mt-2 flex gap-2">
                            <input type="text" x-model="namaUkuranBaru" placeholder="Nama ukuran baru, mis. Jumbo" class="h-10 flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <button type="button" @click="simpanUkuranBaru()" class="h-10 px-3 rounded-lg bg-brand-500 text-white text-sm hover:bg-brand-600">Simpan</button>
                            <button type="button" @click="tampilFormUkuranBaru = false; namaUkuranBaru = ''" class="h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-700 text-sm">Batal</button>
                        </div>
                        <p x-show="pesanErrorUkuranBaru" x-text="pesanErrorUkuranBaru" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">
                        Tambahkan minimal 1 ukuran. Harga & stok diatur per baris — bisa lebih dari 2 varian.
                    </span>
                    @error('ukuran_baris')
                        <small class="text-red-500 block mt-1">{{ $message }}</small>
                    @enderror

                    <!-- MODAL PERINGATAN VALIDASI BARIS UKURAN -->
                    <div x-show="modalPeringatan" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
                        <div @click.away="modalPeringatan = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                            <div class="p-6 text-center">
                                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Belum Lengkap</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="pesanPeringatan"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                                <button type="button" @click="modalPeringatan = false" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-lg transition">Oke</button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL EDIT BARIS UKURAN -->
                    <div x-show="modalEditBaris" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
                        <div @click.away="modalEditBaris = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                            <div class="p-6">
                                <h3 class="font-extrabold text-gray-900 dark:text-white text-base text-center mb-4">
                                    Edit Ukuran <span x-text="editUkuranNama"></span>
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Harga Modal</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                            <input type="text" inputmode="numeric" x-model="editHargaModal" class="input-rupiah h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                                        </div>
                                        <p x-show="errorEditHargaModal" x-text="errorEditHargaModal" class="text-xs text-red-500 mt-1"></p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Harga Jual</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                            <input type="text" inputmode="numeric" x-model="editHargaJual" class="input-rupiah h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                                        </div>
                                        <p x-show="errorEditHargaJual" x-text="errorEditHargaJual" class="text-xs text-red-500 mt-1"></p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Stok</label>
                                        <input type="number" min="0" x-model="editStok" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
                                        <p x-show="errorEditStok" x-text="errorEditStok" class="text-xs text-red-500 mt-1"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                                <button type="button" @click="modalEditBaris = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                                <button type="button" @click="simpanEditBaris()" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-lg transition shadow-xs">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL HAPUS BARIS UKURAN -->
                    <div x-show="modalHapusBaris" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
                        <div @click.away="modalHapusBaris = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                            <div class="p-6 text-center">
                                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Baris Ukuran?</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Ukuran <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapusBaris"></span> akan dihapus dari daftar ini.
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                                <button type="button" @click="modalHapusBaris = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                                <button type="button" @click="konfirmasiHapusBaris()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
                            </div>
                        </div>
                    </div>
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

</div>

    <script>

    const checkboxUkuran = document.getElementById('punya_ukuran');
    const wrapperUkuranDinamis = document.getElementById('wrapper-ukuran-dinamis');
    const wrapperHargaJual = document.getElementById('wrapper-harga-jual');
    const wrapperHargaModal = document.getElementById('wrapper-harga-modal');
    const wrapperStok = document.getElementById('wrapper-stok');

    function toggleFieldUkuran() {
        const tampil = checkboxUkuran.checked;
        wrapperUkuranDinamis.classList.toggle('hidden', !tampil);
        wrapperHargaJual.classList.toggle('hidden', tampil);
        wrapperHargaModal.classList.toggle('hidden', tampil);
        wrapperStok.classList.toggle('hidden', tampil);
    }

    checkboxUkuran.addEventListener('change', toggleFieldUkuran);
    window.addEventListener('load', toggleFieldUkuran);

    // Sama persis kaya di form Tambah Produk, cuma "baris" di-prefill dari
    // varian ukuran yang udah tersimpan buat produk ini (lihat ProdukController::edit()).
    function ukuranDinamis() {
        return {
            daftarMaster: @json($daftarUkuran->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_ukuran
            ])->values()),

            baris: @json($ukuranBaris),

            pilihUkuranId: '',
            hargaModalBaru: '',
            hargaBaru: '',
            stokBaru: '',
            tampilFormUkuranBaru: false,
            namaUkuranBaru: '',
            pesanErrorUkuranBaru: '',
            modalPeringatan: false,
            pesanPeringatan: '',
            modalHapusBaris: false,
            indexHapusBaris: null,
            namaHapusBaris: '',
            modalEditBaris: false,
            indexEditBaris: null,
            editUkuranNama: '',
            editHargaModal: '',
            editHargaJual: '',
            editStok: '',
            errorEditHargaModal: '',
            errorEditHargaJual: '',
            errorEditStok: '',

            get opsiTersedia() {
                const dipakai = this.baris.map(b => String(b.ukuran_id));
                return this.daftarMaster.filter(u => !dipakai.includes(String(u.id)));
            },

            tambahBaris() {
                // hargaModalBaru/hargaBaru datang dari input yang diformat
                // pakai titik ribuan (.input-rupiah), jadi bersihin dulu
                // titiknya sebelum disimpan ke state/dikirim ke server.
                const hargaModalBersih = String(this.hargaModalBaru).replace(/\D/g, '');
                const hargaJualBersih = String(this.hargaBaru).replace(/\D/g, '');

                if (!this.pilihUkuranId || hargaModalBersih === '' || hargaJualBersih === '' || this.stokBaru === '') {
                    this.pesanPeringatan = 'Pilih ukuran, lalu isi Harga Modal, Harga Jual & Stok dulu sebelum menambah baris.';
                    this.modalPeringatan = true;
                    return;
                }
                const master = this.daftarMaster.find(u => String(u.id) === String(this.pilihUkuranId));
                this.baris.push({
                    ukuran_id: this.pilihUkuranId,
                    nama: master ? master.nama : '',
                    harga_modal: hargaModalBersih,
                    harga_jual: hargaJualBersih,
                    stok: this.stokBaru,
                });
                this.pilihUkuranId = '';
                this.hargaModalBaru = '';
                this.hargaBaru = '';
                this.stokBaru = '';
            },

            bukaModalHapusBaris(index) {
                this.indexHapusBaris = index;
                this.namaHapusBaris = this.baris[index] ? this.baris[index].nama : '';
                this.modalHapusBaris = true;
            },

            konfirmasiHapusBaris() {
                if (this.indexHapusBaris !== null) {
                    this.baris.splice(this.indexHapusBaris, 1);
                }
                this.modalHapusBaris = false;
                this.indexHapusBaris = null;
            },

            bukaModalEditBaris(index) {
                const item = this.baris[index];
                if (!item) return;
                this.indexEditBaris = index;
                this.editUkuranNama = item.nama;
                this.editHargaModal = String(item.harga_modal || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                this.editHargaJual = String(item.harga_jual || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                this.editStok = item.stok;
                this.errorEditHargaModal = '';
                this.errorEditHargaJual = '';
                this.errorEditStok = '';
                this.modalEditBaris = true;
            },

            simpanEditBaris() {
                if (this.indexEditBaris === null) return;

                const hargaModalBersih = String(this.editHargaModal).replace(/\D/g, '');
                const hargaJualBersih = String(this.editHargaJual).replace(/\D/g, '');

                this.errorEditHargaModal = hargaModalBersih === '' ? 'Harga Modal wajib diisi.' : '';
                this.errorEditHargaJual = hargaJualBersih === '' ? 'Harga Jual wajib diisi.' : '';
                this.errorEditStok = (this.editStok === '' || this.editStok === null) ? 'Stok wajib diisi.' : '';

                if (this.errorEditHargaModal || this.errorEditHargaJual || this.errorEditStok) {
                    return;
                }

                this.baris[this.indexEditBaris].harga_modal = hargaModalBersih;
                this.baris[this.indexEditBaris].harga_jual = hargaJualBersih;
                this.baris[this.indexEditBaris].stok = this.editStok;

                this.modalEditBaris = false;
                this.indexEditBaris = null;
            },

            async simpanUkuranBaru() {
                const nama = this.namaUkuranBaru.trim();
                this.pesanErrorUkuranBaru = '';
                if (!nama) {
                    this.pesanErrorUkuranBaru = 'Nama ukuran wajib diisi.';
                    return;
                }
                try {
                    const res = await fetch("{{ route('ukuran.storeAjax') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ nama_ukuran: nama }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        this.pesanErrorUkuranBaru = (data.errors && data.errors.nama_ukuran && data.errors.nama_ukuran[0])
                            || data.message
                            || 'Gagal menambah ukuran baru, coba lagi.';
                        return;
                    }
                    this.daftarMaster.push({ id: data.data.id, nama: data.data.nama_ukuran });
                    this.pilihUkuranId = String(data.data.id);
                    this.namaUkuranBaru = '';
                    this.tampilFormUkuranBaru = false;
                } catch (e) {
                    this.pesanErrorUkuranBaru = 'Gagal menambah ukuran baru, coba lagi.';
                }
            },
        };
    }

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
        form.addEventListener('submit', function (e) {
            document.querySelectorAll('.input-rupiah').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });

            if (checkboxUkuran.checked) {
                const jumlahBaris = form.querySelectorAll('input[name$="[ukuran_id]"]').length;
                if (jumlahBaris === 0) {
                    e.preventDefault();
                    alert('Tambahkan minimal 1 baris ukuran sebelum menyimpan produk.');
                }
            }
        });
    });
    </script>

@endsection