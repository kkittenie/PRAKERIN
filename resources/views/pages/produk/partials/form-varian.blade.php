@php
    $punyaVarian = $punyaVarian ?? false;
    $varianBaris = $varianBaris ?? [];
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

{{-- Checkbox: produk punya varian --}}
<div class="md:col-span-2">
    <label class="flex items-center gap-3 h-12 cursor-pointer">
        <input type="hidden" name="punya_varian" value="0">
        <input
            type="checkbox"
            id="punya_varian"
            name="punya_varian"
            value="1"
            class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
            {{ $punyaVarian ? 'checked' : '' }}>
        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Produk ini punya varian (ukuran / variasi)</span>
    </label>
    <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">
        Kalau dicentang, harga modal, harga jual, stok, stok minimum, dan foto diatur per varian di tabel di bawah. Ukuran dan variasi diketik bebas.
    </span>
    @error('punya_varian')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

{{-- Tabel Varian --}}
<div id="wrapper-varian" class="hidden md:col-span-2" x-data="formVarian()" @varian-toggle.window="if ($event.detail) pastikanSatuBaris()">
    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Daftar Varian Produk <span class="text-red-500">*</span>
    </label>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm min-w-[1000px]">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr class="text-left text-gray-500 dark:text-gray-400">
                    <th class="py-2 px-3 w-20">Foto</th>
                    <th class="py-2 px-3">Ukuran</th>
                    <th class="py-2 px-3">Variasi</th>
                    <th class="py-2 px-3">Harga Modal</th>
                    <th class="py-2 px-3">Harga Jual</th>
                    <th class="py-2 px-3 w-24">Stok</th>
                    <th class="py-2 px-3 w-28">Stok Minimum</th>
                    <th class="py-2 px-3 w-16">Aktif</th>
                    <th class="py-2 px-3 w-12"></th>
                </tr>
            </thead>
            <tbody>
                <template x-if="baris.length === 0">
                    <tr>
                        <td colspan="9" class="py-3 px-3 text-center text-gray-400 text-xs">Belum ada varian. Klik "+ Tambah Varian".</td>
                    </tr>
                </template>

                <template x-for="(item, index) in baris" :key="item.uid">
                    <tr class="border-t border-gray-100 dark:border-gray-800 align-middle" :class="{ 'opacity-50': !item.status_aktif }">
                        <td class="py-2 px-3">
                            <label class="block w-12 h-12 cursor-pointer" title="Klik untuk pilih foto">
                                <template x-if="item.foto_url">
                                    <img :src="item.foto_url" alt="Foto varian" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                </template>
                                <template x-if="!item.foto_url">
                                    <div class="w-12 h-12 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center text-gray-400 text-lg">+</div>
                                </template>
                                <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" :name="'varian_baris[' + index + '][foto]'" @change="pilihFoto(item, $event)">
                            </label>
                            <input type="hidden" :name="'varian_baris[' + index + '][id]'" :value="item.id">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" maxlength="50" placeholder="Mis. Kecil" x-model="item.ukuran" :name="'varian_baris[' + index + '][ukuran]'" class="{{ $inputClass }}">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" maxlength="100" placeholder="Mis. Coklat" x-model="item.variasi" :name="'varian_baris[' + index + '][variasi]'" class="{{ $inputClass }}">
                        </td>
                        <td class="py-2 px-3">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                <input type="text" inputmode="numeric" placeholder="8.000" :value="fmt(item.harga_modal)" @input="item.harga_modal = digits($event.target.value); $event.target.value = fmt(item.harga_modal)" class="{{ $inputClass }} pl-8">
                                <input type="hidden" :name="'varian_baris[' + index + '][harga_modal]'" :value="item.harga_modal">
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-xs">Rp</span>
                                <input type="text" inputmode="numeric" placeholder="10.000" :value="fmt(item.harga_jual)" @input="item.harga_jual = digits($event.target.value); $event.target.value = fmt(item.harga_jual)" class="{{ $inputClass }} pl-8">
                                <input type="hidden" :name="'varian_baris[' + index + '][harga_jual]'" :value="item.harga_jual">
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <input type="number" min="0" placeholder="0" x-model="item.stok" :name="'varian_baris[' + index + '][stok]'" class="{{ $inputClass }}">
                        </td>
                        <td class="py-2 px-3">
                            <input type="number" min="0" placeholder="0" x-model="item.stok_minimum" :name="'varian_baris[' + index + '][stok_minimum]'" class="{{ $inputClass }}">
                        </td>
                        <td class="py-2 px-3">
                            <label class="relative inline-flex items-center cursor-pointer" title="Aktifkan/nonaktifkan varian ini">
                                <input type="checkbox" class="sr-only peer" :checked="item.status_aktif" @change="item.status_aktif = $event.target.checked">
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-4"></div>
                            </label>
                            <input type="hidden" :name="'varian_baris[' + index + '][status_aktif]'" :value="item.status_aktif ? 1 : 0">
                        </td>
                        <td class="py-2 px-3 text-right">
                            <button type="button" @click="bukaModalHapus(index)" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <button type="button" @click="tambahBaris()" class="h-11 px-5 rounded-lg bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 transition">+ Tambah Varian</button>
    </div>

    <span class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">
        Satu baris = satu kombinasi yang dijual (contoh: Kecil + Coklat). Isi minimal Ukuran atau Variasi. Kosongkan salah satunya kalau produk cuma punya ukuran atau cuma punya variasi. Kombinasi yang sama tidak boleh dobel.
    </span>

    @foreach (collect($errors->get('varian_baris*'))->flatten()->unique()->take(3) as $pesanVarian)
        <small class="text-red-500 block mt-1">{{ $pesanVarian }}</small>
    @endforeach

    {{-- MODAL HAPUS BARIS VARIAN --}}
    <div x-show="modalHapus" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapus = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Varian?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Varian <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dari daftar ini.
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
    function formVarian() {
        return {
            baris: @json($varianBaris),
            uid: 0,
            modalHapus: false,
            indexHapus: null,
            namaHapus: '',

            init() {
                this.baris = this.baris.map(b => ({ ...b, uid: ++this.uid }));
                const cb = document.getElementById('punya_varian');
                if (cb && cb.checked) this.pastikanSatuBaris();
            },

            pastikanSatuBaris() {
                if (this.baris.length === 0) this.tambahBaris();
            },

            tambahBaris() {
                this.baris.push({
                    uid: ++this.uid,
                    id: '',
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

            pilihFoto(item, e) {
                const file = e.target.files[0];
                if (!file) return;
                item.foto_url = URL.createObjectURL(file);
            },

            bukaModalHapus(index) {
                const b = this.baris[index];
                if (!b) return;
                this.indexHapus = index;
                this.namaHapus = [b.ukuran, b.variasi].filter(Boolean).join(' / ') || 'ini';
                this.modalHapus = true;
            },

            konfirmasiHapus() {
                if (this.indexHapus !== null) this.baris.splice(this.indexHapus, 1);
                this.modalHapus = false;
                this.indexHapus = null;
            },
        };
    }

    (function () {
        function terapkan() {
            const cb = document.getElementById('punya_varian');
            if (!cb) return;

            const wrapVarian = document.getElementById('wrapper-varian');
            if (wrapVarian) wrapVarian.classList.toggle('hidden', !cb.checked);

            // Harga & stok produk dasar disembunyikan kalau diatur per varian.
            ['wrapper-harga-modal', 'wrapper-harga-jual', 'wrapper-stok', 'wrapper-stok-minimum'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('hidden', cb.checked);
            });

            window.dispatchEvent(new CustomEvent('varian-toggle', { detail: cb.checked }));
        }

        document.getElementById('punya_varian').addEventListener('change', terapkan);
        window.addEventListener('load', terapkan);

        document.getElementById('punya_varian').closest('form').addEventListener('submit', function (e) {
            const cb = document.getElementById('punya_varian');
            if (!cb.checked) return;

            const data = Alpine.$data(document.getElementById('wrapper-varian'));
            const gagal = function (pesan) {
                e.preventDefault();
                alert(pesan);
            };

            if (data.baris.length === 0) {
                return gagal('Tambahkan minimal 1 varian sebelum menyimpan produk.');
            }

            const dipakai = new Set();
            for (let i = 0; i < data.baris.length; i++) {
                const b = data.baris[i];
                const ukuran = String(b.ukuran || '').trim();
                const variasi = String(b.variasi || '').trim();
                const nomor = 'Baris ' + (i + 1) + ': ';

                if (!ukuran && !variasi) {
                    return gagal(nomor + 'isi minimal Ukuran atau Variasi.');
                }
                if (b.harga_modal === '' || b.harga_jual === '') {
                    return gagal(nomor + 'Harga Modal dan Harga Jual wajib diisi.');
                }

                const kunci = (ukuran + '|' + variasi).toLowerCase();
                if (dipakai.has(kunci)) {
                    return gagal(nomor + 'kombinasi ukuran & variasi ini sudah ada di baris lain.');
                }
                dipakai.add(kunci);
            }
        });
    })();
</script>