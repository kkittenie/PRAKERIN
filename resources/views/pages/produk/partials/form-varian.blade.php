@php
    $detailBaris = $detailBaris ?? [];
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

{{-- Tabel Detail Produk: satu baris per kombinasi ukuran + variasi --}}
<div id="wrapper-detail" class="md:col-span-2" x-data="formDetail()">
    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Detail Produk <span class="text-red-500">*</span>
    </label>
    <span class="mb-2 block text-xs text-gray-400 dark:text-gray-500">
        Satu baris per kombinasi ukuran dan variasi. Kode, harga, dan stok diatur di tiap baris. Produk tanpa ukuran/variasi cukup satu baris dengan Ukuran dan Variasi dikosongkan.
    </span>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm min-w-[1150px]">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr class="text-left text-gray-500 dark:text-gray-400">
                    <th class="py-2 px-3 w-20">Foto</th>
                    <th class="py-2 px-3 w-40">Kode <span class="text-red-500">*</span></th>
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
                        <td class="py-2 px-3">
                            <input type="text" maxlength="50" placeholder="Mis. Kecil" x-model="item.ukuran" :name="'detail_baris[' + index + '][ukuran]'" :style="pesanKombinasi(index) ? 'border-color:#ef4444' : ''" class="{{ $inputClass }}">
                        </td>
                        <td class="py-2 px-3">
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
                        <td class="py-2 px-3 text-right">
                            <button type="button" @click="bukaModalHapus(index)" :disabled="baris.length <= 1" :class="baris.length <= 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-red-50 dark:hover:bg-red-500/10'" :title="baris.length <= 1 ? 'Minimal 1 detail produk' : 'Hapus'" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
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
        $pesanServer = collect($errors->get('detail_baris*'))->flatten()->unique()->take(3);
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
            uid: 0,
            modalHapus: false,
            indexHapus: null,
            namaHapus: '',
            tampilkanGalat: false,

            norm(v) {
                return String(v ?? '').trim().toLowerCase();
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
                        if (b.harga_modal === '' || b.harga_jual === '') out.push(nomor + 'Harga Modal dan Harga Jual wajib diisi.');
                    }
                });
                return out;
            },

            daftarGalat() {
                return this.hitungGalat(this.tampilkanGalat);
            },

            init() {
                this.baris = this.baris.map(b => ({ ...b, uid: ++this.uid }));
                if (this.baris.length === 0) this.tambahBaris();

                // Klik simpan: munculkan semua peringatan; kalau masih ada yang salah, simpan dibatalkan.
                const form = this.$root.closest('form');
                if (form) {
                    form.addEventListener('submit', (e) => {
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

            pilihFoto(item, e) {
                const file = e.target.files[0];
                if (!file) return;
                item.foto_url = URL.createObjectURL(file);
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