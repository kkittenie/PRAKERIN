@php
    $detailBaris = $detailBaris ?? [];
    $varianBaris = $varianBaris ?? [];
    $fotoUtamaUrl = $fotoUtamaUrl ?? null;

    // Mode stok: 'global' (polosan: satu kode, harga, stok) atau 'varian' (per ukuran/variasi).
    // Produk dengan satu baris tanpa ukuran & variasi (atau produk baru) = global.
    $modeStok = old('mode_stok');
    if (! in_array($modeStok, ['global', 'varian'], true)) {
        $kumpulanBaris = collect($detailBaris);
        $barisPertama = $kumpulanBaris->first();
        $modeStok = ($kumpulanBaris->count() <= 1
            && trim((string) ($barisPertama['ukuran'] ?? '')) === ''
            && trim((string) ($barisPertama['variasi'] ?? '')) === '')
            ? 'global' : 'varian';
    }
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

{{-- Tabel Detail Produk: satu baris per kombinasi ukuran + variasi --}}
<div id="wrapper-detail" class="md:col-span-2" x-data="formDetail()">
    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Jenis Produk <span class="text-red-500">*</span>
    </label>

    {{-- Pilihan mode stok --}}
    <input type="hidden" name="mode_stok" :value="mode">
    <div class="mb-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label class="flex items-start gap-3 p-4 rounded-xl border bg-white dark:bg-gray-800 cursor-pointer transition"
            :class="mode === 'global' ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500'">
            <input type="radio" class="mt-1 w-4 h-4 text-brand-600 focus:ring-brand-500" :checked="mode === 'global'" @change="setMode('global')">
            <div>
                <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Tunggal</span>
                <span class="block text-xs text-gray-500">satu stok bersama. Pilihan tambahan (mis. topping) boleh ditambah, harganya diatur per varian dan stoknya ikut produk ini.</span>
            </div>
        </label>
        <label class="flex items-start gap-3 p-4 rounded-xl border bg-white dark:bg-gray-800 cursor-pointer transition"
            :class="mode === 'varian' ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500'">
            <input type="radio" class="mt-1 w-4 h-4 text-brand-600 focus:ring-brand-500" :checked="mode === 'varian'" @change="setMode('varian')">
            <div>
                <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Bervarian</span>
                <span class="block text-xs text-gray-500">tiap kombinasi ukuran dan variasi punya kode, harga, dan stok sendiri.</span>
            </div>
        </label>
    </div>

    <span class="mb-2 block text-xs text-gray-400 dark:text-gray-500" x-show="mode === 'varian'">
        Satu baris per kombinasi ukuran dan variasi. Kode, harga, dan stok diatur di tiap baris.
    </span>
    <span class="mb-2 block text-xs text-gray-400 dark:text-gray-500" x-show="mode === 'global'" x-cloak>
        Isi kode dan stok untuk produk ini. Kalau produk punya varian (mis. topping), harga modal dan harga jual diisi per varian di bagian bawah, sedangkan stoknya tetap satu. Kalau tidak punya varian, isi harganya di sini.
    </span>

    {{-- Foto utama produk (dipakai di daftar produk, kasir, dan menu mobile). Selalu tampil di kedua mode. --}}
    <div class="mb-4">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Produk</label>
        <div class="flex items-center gap-4">
            <div class="group relative w-24 h-24 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                <template x-if="fotoUtama">
                    <img id="preview-foto-produk-utama" :src="fotoUtama" alt="Foto produk" class="w-24 h-24 rounded-xl object-contain p-1">
                </template>
                <template x-if="!fotoUtama">
                    <label for="input-foto-produk-utama" class="w-full h-full border border-dashed border-gray-300 dark:border-gray-600 rounded-xl flex items-center justify-center text-gray-400 text-2xl cursor-pointer hover:border-brand-500 hover:text-brand-500 transition">+</label>
                </template>

                {{-- Overlay Crop Ulang saat hover foto --}}
                <div x-show="fotoUtama" x-cloak
                     class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
                     onclick="bukaCropDariPreviewProduk('utama')" title="Klik untuk crop foto ini">
                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/95 text-gray-800 text-[11px] font-bold shadow">
                        <svg class="w-3 h-3 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4a1 1 0 001 1h4M7 3H5a1 1 0 00-1 1v14a1 1 0 001 1h14a1 1 0 001-1v-2M7 3l10 10M17 21v-4a1 1 0 00-1-1H7" />
                        </svg>
                        Crop
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <div class="flex items-center gap-2">
                    <label for="input-foto-produk-utama"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <span x-text="fotoUtama ? 'Ganti Foto' : 'Pilih Foto'"></span>
                    </label>
                    <button type="button" x-show="fotoUtama" x-cloak onclick="bukaCropDariPreviewProduk('utama')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-brand-500/40 bg-brand-50 dark:bg-brand-500/10 px-3 py-1.5 text-xs font-semibold text-brand-600 dark:text-brand-300 hover:bg-brand-100 transition">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4a1 1 0 001 1h4M7 3H5a1 1 0 00-1 1v14a1 1 0 001 1h14a1 1 0 001-1v-2M7 3l10 10M17 21v-4a1 1 0 00-1-1H7" />
                        </svg>
                        Crop Foto
                    </button>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">Format JPG, PNG, atau WebP (Disarankan 1:1 persegi).</span>
            </div>

            <input id="input-foto-produk-utama" type="file" name="foto" accept="image/png,image/jpeg,image/webp" class="hidden"
                @change="pilihFotoUtama($event)">
        </div>
        @error('foto')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Mode Varian: tabel satu baris per kombinasi ukuran + variasi --}}
    <template x-if="mode === 'varian'">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
            <table class="w-full text-sm" :class="mode === 'varian' ? 'min-w-[1150px]' : 'min-w-[760px]'">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="py-2 px-3 w-20">Foto</th>
                        <th class="py-2 px-3 w-40">Kode <span class="text-red-500">*</span></th>
                        <th class="py-2 px-3" x-show="mode === 'varian'">Ukuran</th>
                        <th class="py-2 px-3" x-show="mode === 'varian'">Variasi</th>
                        <th class="py-2 px-3">Harga Modal</th>
                        <th class="py-2 px-3">Harga Jual</th>
                        <th class="py-2 px-3 w-24">Stok</th>
                        <th class="py-2 px-3 w-28">Stok Minimum</th>
                        <th class="py-2 px-3 w-16">Status</th>
                        <th class="py-2 px-3 w-12" x-show="mode === 'varian'"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in baris" :key="item.uid">
                        <tr class="border-t border-gray-100 dark:border-gray-800 align-top" :class="{ 'opacity-50': !item.status_aktif }">
                            <td class="py-2 px-3">
                                <label class="block w-12 h-12 cursor-pointer" title="Klik untuk pilih foto">
                                    <template x-if="item.foto_url">
                                        <img :src="item.foto_url" alt="Foto detail produk" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                    </template>
                                    <template x-if="!item.foto_url">
                                        <div class="w-12 h-12 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-400 text-lg">+</div>
                                    </template>
                                    <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" :name="'detail_baris[' + index + '][foto]'" @change="pilihFoto(item, $event)">
                                </label>
                                <input type="hidden" :name="'detail_baris[' + index + '][id]'" :value="item.id">
                            </td>
                            <td class="py-2 px-3">
                                <input type="text" maxlength="60" placeholder="Mis. DN-K-COK" x-model="item.kode" :name="'detail_baris[' + index + '][kode]'" :style="pesanKode(index) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }} font-mono">
                                <p x-show="pesanKode(index)" x-text="pesanKode(index)" class="mt-1 text-xs font-medium text-red-600 dark:text-red-400"></p>
                            </td>
                            <td class="py-2 px-3" x-show="mode === 'varian'">
                                <input type="text" maxlength="50" placeholder="Mis. Kecil" x-model="item.ukuran" :name="'detail_baris[' + index + '][ukuran]'" :style="pesanKombinasi(index) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }}">
                            </td>
                            <td class="py-2 px-3" x-show="mode === 'varian'">
                                <input type="text" maxlength="100" placeholder="Mis. Coklat" x-model="item.variasi" :name="'detail_baris[' + index + '][variasi]'" :style="pesanKombinasi(index) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }}">
                                <p x-show="pesanKombinasi(index)" x-text="pesanKombinasi(index)" class="mt-1 text-xs font-medium text-red-600 dark:text-red-400"></p>
                            </td>
                            <td class="py-2 px-3">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                    <input type="text" inputmode="numeric" placeholder="8.000" :style="kosongAngka(item.harga_modal) ? 'border-color:#ef4444' : ''" :value="fmt(item.harga_modal)" @input="item.harga_modal = digits($event.target.value); $event.target.value = fmt(item.harga_modal)" class="{{ $inputClass }} pl-8">
                                    <input type="hidden" :name="'detail_baris[' + index + '][harga_modal]'" :value="item.harga_modal">
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                    <input type="text" inputmode="numeric" placeholder="10.000" :style="kosongAngka(item.harga_jual) ? 'border-color:#ef4444' : ''" :value="fmt(item.harga_jual)" @input="item.harga_jual = digits($event.target.value); $event.target.value = fmt(item.harga_jual)" class="{{ $inputClass }} pl-8">
                                    <input type="hidden" :name="'detail_baris[' + index + '][harga_jual]'" :value="item.harga_jual">
                                </div>
                            </td>
                            <td class="py-2 px-3">
                                <input type="number" min="0" placeholder="0" x-model="item.stok" :name="'detail_baris[' + index + '][stok]'" class="{{ $inputClass }}">
                            </td>
                            <td class="py-2 px-3">
                                <input type="number" min="0" placeholder="0" x-model="item.stok_minimum" :name="'detail_baris[' + index + '][stok_minimum]'" class="{{ $inputClass }}">
                            </td>
                            <td class="py-2 px-3">
                                <label class="relative inline-flex items-center cursor-pointer mt-2" title="Aktifkan/nonaktifkan detail ini">
                                    <input type="checkbox" class="sr-only peer" :checked="item.status_aktif" @change="item.status_aktif = $event.target.checked">
                                    <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-4"></div>
                                </label>
                                <input type="hidden" :name="'detail_baris[' + index + '][status_aktif]'" :value="item.status_aktif ? 1 : 0">
                            </td>
                            <td class="py-2 px-3 text-right" x-show="mode === 'varian'">
                                <button type="button" @click="bukaModalHapus(index)" :disabled="baris.length <= 1" :class="baris.length <= 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-red-50 dark:hover:bg-red-500/10'" :title="baris.length <= 1 ? 'Minimal 1 detail produk' : 'Hapus'" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    {{-- Mode Global: tanpa tabel, langsung form isian untuk satu detail produk --}}
    <template x-if="mode === 'global'">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <template x-for="(item, index) in baris.slice(0, 1)" :key="item.uid">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" :class="{ 'opacity-60': !item.status_aktif }">
                    <input type="hidden" :name="'detail_baris[' + index + '][id]'" :value="item.id">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode <span class="text-red-500">*</span></label>
                        <input type="text" maxlength="60" placeholder="Mis. DN-K-COK" x-model="item.kode" :name="'detail_baris[' + index + '][kode]'" :style="pesanKode(index) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }} font-mono">
                        <p x-show="pesanKode(index)" x-text="pesanKode(index)" class="mt-1 text-xs font-medium text-red-600 dark:text-red-400"></p>
                    </div>

                    <p x-show="varianBaris.length > 0" x-cloak class="md:col-span-2 text-xs text-gray-500 dark:text-gray-400">
                        Harga modal dan harga jual diatur per varian di tabel Varian Produk di bawah.
                    </p>

                    <div x-show="varianBaris.length === 0">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Modal <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                            <input type="text" inputmode="numeric" placeholder="8.000" :style="kosongAngka(item.harga_modal) ? 'border-color:#ef4444' : ''" :value="fmt(item.harga_modal)" @input="item.harga_modal = digits($event.target.value); $event.target.value = fmt(item.harga_modal)" class="{{ $inputClass }} pl-8">
                            <input type="hidden" :name="'detail_baris[' + index + '][harga_modal]'" :value="item.harga_modal">
                        </div>
                    </div>

                    <div x-show="varianBaris.length === 0">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                            <input type="text" inputmode="numeric" placeholder="10.000" :style="kosongAngka(item.harga_jual) ? 'border-color:#ef4444' : ''" :value="fmt(item.harga_jual)" @input="item.harga_jual = digits($event.target.value); $event.target.value = fmt(item.harga_jual)" class="{{ $inputClass }} pl-8">
                            <input type="hidden" :name="'detail_baris[' + index + '][harga_jual]'" :value="item.harga_jual">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok</label>
                        <input type="number" min="0" placeholder="0" x-model="item.stok" :name="'detail_baris[' + index + '][stok]'" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Minimum</label>
                        <input type="number" min="0" placeholder="0" x-model="item.stok_minimum" :name="'detail_baris[' + index + '][stok_minimum]'" class="{{ $inputClass }}">
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Mode Global: tabel varian (mis. topping). Harga dan stok ikut produk di atas. --}}
    <template x-if="mode === 'global'">
        <div class="mt-4">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Varian Produk (opsional)</label>
            <span class="mb-2 block text-xs text-gray-400 dark:text-gray-500">
                Tambahkan pilihan seperti topping. Harga jual dan harga modal diatur per varian. Stok tetap satu: berkurang dari stok produk di atas, bukan per varian.
            </span>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
                <table class="w-full text-sm min-w-[1100px]">
                    <thead class="bg-gray-50 dark:bg-gray-800/60">
                        <tr class="text-left text-gray-500 dark:text-gray-400">
                            <th class="py-2 px-3 w-20">Foto</th>
                            <th class="py-2 px-3 w-48">Kode</th>
                            <th class="py-2 px-3">Varian</th>
                            <th class="py-2 px-3">Ukuran</th>
                            <th class="py-2 px-3">Harga Modal <span class="text-red-500">*</span></th>
                            <th class="py-2 px-3">Harga Jual <span class="text-red-500">*</span></th>
                            <th class="py-2 px-3 w-16">Status</th>
                            <th class="py-2 px-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="varianBaris.length === 0">
                            <tr>
                                <td colspan="8" class="py-4 px-3 text-center text-xs text-gray-400">Belum ada varian. Klik "+ Tambah Varian" kalau produk ini punya pilihan (mis. topping).</td>
                            </tr>
                        </template>
                        <template x-for="(v, i) in varianBaris" :key="v.uid">
                            <tr class="border-t border-gray-100 dark:border-gray-800 align-top" :class="{ 'opacity-50': !v.status_aktif }">
                                <td class="py-2 px-3">
                                    <label class="block w-12 h-12 cursor-pointer" title="Klik untuk pilih foto">
                                        <template x-if="v.foto_url">
                                            <img :src="v.foto_url" alt="Foto varian" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                        </template>
                                        <template x-if="!v.foto_url">
                                            <div class="w-12 h-12 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-400 text-lg">+</div>
                                        </template>
                                        <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" :name="'varian_baris[' + i + '][foto]'" @change="pilihFotoVarian(v, $event)">
                                    </label>
                                    <input type="hidden" :name="'varian_baris[' + i + '][id]'" :value="v.id">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" maxlength="60" placeholder="Kosongkan = otomatis" x-model="v.kode" :name="'varian_baris[' + i + '][kode]'" :style="pesanKodeVarian(i) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }} font-mono">
                                    <p x-show="pesanKodeVarian(i)" x-text="pesanKodeVarian(i)" class="mt-1 text-xs font-medium text-red-600 dark:text-red-400"></p>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" maxlength="100" placeholder="Mis. Coklat" x-model="v.variasi" :name="'varian_baris[' + i + '][variasi]'" :style="pesanVarian(i) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }}">
                                    <p x-show="pesanVarian(i)" x-text="pesanVarian(i)" class="mt-1 text-xs font-medium text-red-600 dark:text-red-400"></p>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" maxlength="50" placeholder="Mis. Kecil" x-model="v.ukuran" :name="'varian_baris[' + i + '][ukuran]'" :style="pesanVarian(i) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }}">
                                </td>
                                <td class="py-2 px-3">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                        <input type="text" inputmode="numeric" placeholder="8.000" :style="kosongAngkaVarian(v, v.harga_modal) ? 'border-color:#ef4444' : ''" :value="fmt(v.harga_modal)" @input="v.harga_modal = digits($event.target.value); $event.target.value = fmt(v.harga_modal)" class="{{ $inputClass }} pl-8">
                                        <input type="hidden" :name="'varian_baris[' + i + '][harga_modal]'" :value="v.harga_modal">
                                    </div>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                        <input type="text" inputmode="numeric" placeholder="10.000" :style="kosongAngkaVarian(v, v.harga_jual) ? 'border-color:#ef4444' : ''" :value="fmt(v.harga_jual)" @input="v.harga_jual = digits($event.target.value); $event.target.value = fmt(v.harga_jual)" class="{{ $inputClass }} pl-8">
                                        <input type="hidden" :name="'varian_baris[' + i + '][harga_jual]'" :value="v.harga_jual">
                                    </div>
                                </td>
                                <td class="py-2 px-3">
                                    <label class="relative inline-flex items-center cursor-pointer mt-2" title="Aktifkan/nonaktifkan varian ini">
                                        <input type="checkbox" class="sr-only peer" :checked="v.status_aktif" @change="v.status_aktif = $event.target.checked">
                                        <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-4"></div>
                                    </label>
                                    <input type="hidden" :name="'varian_baris[' + i + '][status_aktif]'" :value="v.status_aktif ? 1 : 0">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <button type="button" @click="hapusVarian(i)" title="Hapus varian" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="button" @click="tambahVarian()" class="h-11 px-5 rounded-lg bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 transition">+ Tambah Varian</button>
            </div>
        </div>
    </template>

    <div class="mt-3" x-show="mode === 'varian'">
        <button type="button" @click="tambahBaris()" class="h-11 px-5 rounded-lg bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 transition">+ Tambah Detail</button>
    </div>

    {{-- Peringatan merah: muncul langsung saat kode/kombinasi dobel, dan saat simpan kalau ada yang kurang --}}
    <div x-show="daftarGalat().length > 0" x-cloak class="mt-3 rounded-lg border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3">
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">Periksa kembali detail produk:</p>
        <ul class="mt-1 list-disc pl-5 text-xs text-red-600 dark:text-red-400 space-y-0.5">
            <template x-for="p in daftarGalat()" :key="p">
                <li x-text="p"></li>
            </template>
        </ul>
    </div>

    @php
        $pesanServer = collect($errors->get('detail_baris*'))->merge($errors->get('varian_baris*'))->flatten()->unique()->take(3);
    @endphp
    @if ($pesanServer->isNotEmpty())
        <div class="mt-3 rounded-lg border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3">
            @foreach ($pesanServer as $pesan)
                <p class="text-xs font-medium text-red-600 dark:text-red-400">{{ $pesan }}</p>
            @endforeach
        </div>
    @endif

    {{-- MODAL HAPUS BARIS DETAIL --}}
    <div x-show="modalHapus" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapus = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Detail Produk?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Detail <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dari daftar ini dan dari database setelah produk disimpan.
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                <button type="button" @click="modalHapus = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                <button type="button" @click="konfirmasiHapus()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
    function formDetail() {
        return {
            baris: @json($detailBaris),
            fotoUtama: @json($fotoUtamaUrl),
            varianBaris: @json($varianBaris),
            mode: @json($modeStok),
            simpanVarian: null,
            uid: 0,
            modalHapus: false,
            indexHapus: null,
            namaHapus: '',
            tampilkanGalat: false,

            norm(v) {
                return String(v ?? '').trim().toLowerCase();
            },

            // Ganti mode stok. Ke Stok Bersama: sisakan baris pertama tanpa ukuran/variasi (baris lain
            // disimpan sementara, jadi kalau balik ke Varian isinya kembali). Ke Varian: kembalikan.
            setMode(m) {
                if (m === this.mode) return;

                if (m === 'global') {
                    if (this.baris.length === 0) this.tambahBaris();
                    const pertama = this.baris[0];
                    this.simpanVarian = {
                        ukuran: pertama.ukuran,
                        variasi: pertama.variasi,
                        sisa: this.baris.slice(1),
                    };
                    pertama.ukuran = '';
                    pertama.variasi = '';
                    this.baris = [pertama];
                } else if (this.simpanVarian) {
                    const s = this.simpanVarian;
                    this.baris[0].ukuran = s.ukuran;
                    this.baris[0].variasi = s.variasi;
                    s.sisa.forEach(b => this.baris.push(b));
                    this.simpanVarian = null;
                }

                this.mode = m;
            },

            kosongBaris(b) {
                return !this.norm(b.ukuran) && !this.norm(b.variasi);
            },

            kosongAngka(v) {
                return this.tampilkanGalat && (v === '' || v === null || v === undefined);
            },

            kodeDobel(index) {
                const b = this.baris[index];
                const k = this.norm(b && b.kode);
                if (!k) return false;
                return this.baris.some((x, i) => i !== index && this.norm(x.kode) === k);
            },

            kombinasiDobel(index) {
                const b = this.baris[index];
                if (!b || this.kosongBaris(b)) return false;
                const kunci = (x) => this.norm(x.ukuran) + '|' + this.norm(x.variasi);
                return this.baris.some((x, i) => i !== index && !this.kosongBaris(x) && kunci(x) === kunci(b));
            },

            // Dobel selalu tampil langsung; kolom yang kosong baru ditandai merah setelah klik simpan.
            pesanKode(index) {
                const b = this.baris[index];
                if (!b) return '';
                if (this.kodeDobel(index)) return 'Kode ini sudah dipakai di baris lain.';
                if (this.tampilkanGalat && !this.norm(b.kode)) return 'Kode wajib diisi.';
                return '';
            },

            pesanKombinasi(index) {
                const b = this.baris[index];
                if (!b) return '';
                if (this.kombinasiDobel(index)) return 'Kombinasi ukuran & variasi ini sudah dipakai di baris lain.';
                if (this.tampilkanGalat && this.baris.length > 1 && this.kosongBaris(b)) return 'Isi minimal Ukuran atau Variasi.';
                return '';
            },

            hitungGalat(paksa) {
                const out = [];
                if (paksa && this.baris.length === 0) out.push('Tambahkan minimal 1 detail produk sebelum menyimpan.');
                this.baris.forEach((b, i) => {
                    const nomor = 'Baris ' + (i + 1) + ': ';
                    if (this.kodeDobel(i)) out.push(nomor + 'kode "' + String(b.kode).trim() + '" sudah dipakai di baris lain.');
                    if (this.kombinasiDobel(i)) out.push(nomor + 'kombinasi ukuran & variasi ini sudah ada di baris lain.');
                    if (paksa) {
                        if (!this.norm(b.kode)) out.push(nomor + 'kode wajib diisi.');
                        if (this.baris.length > 1 && this.kosongBaris(b)) out.push(nomor + 'isi minimal Ukuran atau Variasi.');
                        if (!this.pakaiHargaVarian() && (b.harga_modal === '' || b.harga_jual === '')) out.push(nomor + 'Harga Modal dan Harga Jual wajib diisi.');
                    }
                });

                if (this.mode === 'global') {
                    this.varianBaris.forEach((v, i) => {
                        if (this.varianKosong(v)) return;
                        const nomor = 'Varian ' + (i + 1) + ': ';
                        if (this.kodeVarianDobel(i)) out.push(nomor + 'kode \"' + String(v.kode).trim() + '\" sudah dipakai di baris lain.');
                        if (this.varianDobel(i)) out.push(nomor + 'kombinasi ukuran & varian ini sudah ada di baris lain.');
                        if (paksa && !this.norm(v.ukuran) && !this.norm(v.variasi)) out.push(nomor + 'isi minimal Ukuran atau Varian.');
                        if (paksa && (v.harga_modal === '' || v.harga_modal === null || v.harga_jual === '' || v.harga_jual === null)) out.push(nomor + 'Harga Modal dan Harga Jual wajib diisi.');
                    });
                }

                return out;
            },

            // ---- Varian produk global (mis. topping) ----
            varianKosong(v) {
                return !this.norm(v.ukuran) && !this.norm(v.variasi) && !this.norm(v.kode) && !v.foto_url && !v.id;
            },

            varianDobel(index) {
                const v = this.varianBaris[index];
                if (!v || (!this.norm(v.ukuran) && !this.norm(v.variasi))) return false;
                const kunci = (x) => this.norm(x.ukuran) + '|' + this.norm(x.variasi);
                return this.varianBaris.some((x, i) => i !== index && kunci(x) === kunci(v));
            },

            kodeVarianDobel(index) {
                const v = this.varianBaris[index];
                const k = this.norm(v && v.kode);
                if (!k) return false;
                const dipakaiDetail = this.baris.slice(0, 1).some(x => this.norm(x.kode) === k);
                return dipakaiDetail || this.varianBaris.some((x, i) => i !== index && this.norm(x.kode) === k);
            },

            pesanKodeVarian(index) {
                return this.kodeVarianDobel(index) ? 'Kode ini sudah dipakai di baris lain.' : '';
            },

            pesanVarian(index) {
                const v = this.varianBaris[index];
                if (!v) return '';
                if (this.varianDobel(index)) return 'Kombinasi ukuran & varian ini sudah dipakai di baris lain.';
                if (this.tampilkanGalat && !this.varianKosong(v) && !this.norm(v.ukuran) && !this.norm(v.variasi)) return 'Isi minimal Ukuran atau Varian.';
                return '';
            },

            // Produk Tunggal bervarian: harga produk utama (tidak ditampilkan) mengikuti varian termurah,
            // supaya data detail produk tetap terisi dan lolos validasi.
            sinkronHargaUtama() {
                if (this.mode !== 'global' || this.varianBaris.length === 0 || this.baris.length === 0) return;

                const terisi = this.varianBaris.filter(v => v.harga_jual !== '' && v.harga_jual !== null && v.harga_jual !== undefined);
                if (terisi.length === 0) return;

                const termurah = terisi.reduce((a, b) => Number(b.harga_jual) < Number(a.harga_jual) ? b : a);
                this.baris[0].harga_jual = termurah.harga_jual;
                this.baris[0].harga_modal = (termurah.harga_modal !== '' && termurah.harga_modal !== null) ? termurah.harga_modal : this.baris[0].harga_modal;
            },

            pakaiHargaVarian() {
                return this.mode === 'global' && this.varianBaris.length > 0;
            },

            kosongAngkaVarian(v, nilai) {
                return this.tampilkanGalat && !this.varianKosong(v) && (nilai === '' || nilai === null || nilai === undefined);
            },

            tambahVarian() {
                // Harga awal varian baru = harga produk utama; tinggal diubah per varian.
                const utama = this.baris[0] || {};
                this.varianBaris.push({ uid: ++this.uid, id: '', kode: '', ukuran: '', variasi: '', harga_modal: utama.harga_modal ?? '', harga_jual: utama.harga_jual ?? '', status_aktif: true, foto_url: null });
            },

            hapusVarian(index) {
                this.varianBaris.splice(index, 1);
            },

            pilihFotoVarian(v, e) {
                const file = e.target.files[0];
                if (!file) return;
                v.foto_url = URL.createObjectURL(file);
                const idx = this.varianBaris.indexOf(v);
                if (typeof bukaCropProduk === 'function') {
                    bukaCropProduk(e.target, 'varian', idx);
                }
            },

            daftarGalat() {
                return this.hitungGalat(this.tampilkanGalat);
            },

            init() {
                this.baris = this.baris.map(b => ({ ...b, uid: ++this.uid }));
                this.varianBaris = this.varianBaris.map(v => ({ ...v, uid: ++this.uid }));
                if (this.baris.length === 0) this.tambahBaris();

                this.$watch('varianBaris', () => this.sinkronHargaUtama());

                window.addEventListener('foto-produk-cropped', (e) => {
                    if (e.detail.tipe === 'utama') {
                        this.fotoUtama = e.detail.url;
                    } else if (e.detail.tipe === 'baris' && e.detail.index !== null && this.baris[e.detail.index]) {
                        this.baris[e.detail.index].foto_url = e.detail.url;
                    } else if (e.detail.tipe === 'varian' && e.detail.index !== null && this.varianBaris[e.detail.index]) {
                        this.varianBaris[e.detail.index].foto_url = e.detail.url;
                    }
                });

                // Klik simpan: munculkan semua peringatan; kalau masih ada yang salah, simpan dibatalkan.
                const form = this.$root.closest('form');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        this.sinkronHargaUtama();
                        this.tampilkanGalat = true;
                        if (this.hitungGalat(true).length > 0) {
                            e.preventDefault();
                            this.$root.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            },

            tambahBaris() {
                this.baris.push({
                    uid: ++this.uid,
                    id: '',
                    kode: '',
                    ukuran: '',
                    variasi: '',
                    harga_modal: '',
                    harga_jual: '',
                    stok: 0,
                    stok_minimum: 0,
                    foto_url: null,
                    status_aktif: true,
                });
            },

            fmt(v) {
                return String(v ?? '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            digits(v) {
                return String(v ?? '').replace(/\D/g, '');
            },

            pilihFotoUtama(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.fotoUtama = URL.createObjectURL(file);
                if (typeof bukaCropProduk === 'function') {
                    bukaCropProduk(e.target, 'utama');
                }
            },

            pilihFoto(item, e) {
                const file = e.target.files[0];
                if (!file) return;
                item.foto_url = URL.createObjectURL(file);
                const idx = this.baris.indexOf(item);
                if (typeof bukaCropProduk === 'function') {
                    bukaCropProduk(e.target, 'baris', idx);
                }
            },

            bukaModalHapus(index) {
                if (this.baris.length <= 1) return;
                const b = this.baris[index];
                if (!b) return;
                this.indexHapus = index;
                this.namaHapus = String(b.kode || '').trim() || [b.ukuran, b.variasi].filter(Boolean).join(' / ') || 'ini';
                this.modalHapus = true;
            },

            konfirmasiHapus() {
                if (this.indexHapus !== null && this.baris.length > 1) this.baris.splice(this.indexHapus, 1);
                this.modalHapus = false;
                this.indexHapus = null;
            },
        };
    }
</script>

@include('pages.produk.partials.crop-modal')