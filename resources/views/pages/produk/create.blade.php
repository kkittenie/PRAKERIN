@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Produk" parentTitle="Produk" :parentUrl="route('produk.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Tambah Produk">

        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

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
                                {{ old('kategori_id')==$item->id ? 'selected' : '' }}>
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
                        value="{{ old('kode_produk') }}"
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
                        value="{{ old('nama_produk') }}"
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
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi') }}</textarea>

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
                            value="{{ old('harga_jual') ? number_format((int) old('harga_jual'), 0, ',', '.') : '' }}"
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
                            {{ old('punya_ukuran') == '1' ? 'checked' : '' }}>
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
                                    <th class="py-2 px-3">Harga Jual</th>
                                    <th class="py-2 px-3">Stok</th>
                                    <th class="py-2 px-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="baris.length === 0">
                                    <tr>
                                        <td colspan="4" class="py-3 px-3 text-center text-gray-400 text-xs">Belum ada baris ukuran ditambahkan.</td>
                                    </tr>
                                </template>
                                <template x-for="(item, index) in baris" :key="index">
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="py-2 px-3 font-semibold text-gray-800 dark:text-gray-200" x-text="item.nama"></td>
                                        <td class="py-2 px-3" x-text="'Rp ' + Number(item.harga_jual || 0).toLocaleString('id-ID')"></td>
                                        <td class="py-2 px-3" x-text="item.stok"></td>
                                        <td class="py-2 px-3 text-right">
                                            <button type="button" @click="hapusBaris(index)" class="text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][ukuran_id]'" :value="item.ukuran_id">
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][harga_jual]'" :value="item.harga_jual">
                                            <input type="hidden" :name="'ukuran_baris[' + index + '][stok]'" :value="item.stok">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-2 items-end">
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
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Harga Jual</label>
                            <input type="number" min="0" x-model="hargaBaru" placeholder="10000" class="h-11 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 text-sm focus:border-brand-500 focus:ring-brand-500">
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
                            value="{{ old('harga_modal') ? number_format((int) old('harga_modal'), 0, ',', '.') : '' }}"
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
                        name="stok_minimum"
                        placeholder="1"
                        value="{{ old('stok_minimum') }}"
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

                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>

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

                        <option value="stok_ada">Stok Ada</option>
                        <option value="perlu_diproduksi">Perlu Diproduksi</option>

                    </select>

                    <small class="text-gray-400">
                        Otomatis menjadi "Perlu Diproduksi" jika stok awal 0.
                    </small>
                </div>

                {{-- Foto --}}
                <div>
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

                    Simpan Produk

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

    <script>

    const checkboxUkuran = document.getElementById('punya_ukuran');
    const wrapperUkuranDinamis = document.getElementById('wrapper-ukuran-dinamis');
    const wrapperHargaJual = document.getElementById('wrapper-harga-jual');
    const wrapperStok = document.getElementById('wrapper-stok');

    function toggleFieldUkuran() {
        const tampil = checkboxUkuran.checked;
        wrapperUkuranDinamis.classList.toggle('hidden', !tampil);
        wrapperHargaJual.classList.toggle('hidden', tampil);
        wrapperStok.classList.toggle('hidden', tampil);
    }

    checkboxUkuran.addEventListener('change', toggleFieldUkuran);
    window.addEventListener('load', toggleFieldUkuran);

    // Data Alpine buat repeater "Varian Ukuran" di form Tambah Produk.
    // Polanya niru "Tambah Bahan Baku": pilih dari master (Ukuran), atau
    // bikin ukuran baru langsung dari sini lewat AJAX (masuk ke menu Ukuran
    // juga), tanpa harus pindah halaman.
    function ukuranDinamis() {
        return {
            daftarMaster: @json($daftarUkuran->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_ukuran
            ])->values()),

            baris: @json($ukuranBaris),

            pilihUkuranId: '',
            hargaBaru: '',
            stokBaru: '',
            tampilFormUkuranBaru: false,
            namaUkuranBaru: '',
            pesanErrorUkuranBaru: '',

            get opsiTersedia() {
                const dipakai = this.baris.map(b => String(b.ukuran_id));
                return this.daftarMaster.filter(u => !dipakai.includes(String(u.id)));
            },

            tambahBaris() {
                if (!this.pilihUkuranId || this.hargaBaru === '' || this.stokBaru === '') {
                    alert('Pilih ukuran, lalu isi Harga Jual & Stok dulu sebelum menambah baris.');
                    return;
                }
                const master = this.daftarMaster.find(u => String(u.id) === String(this.pilihUkuranId));
                this.baris.push({
                    ukuran_id: this.pilihUkuranId,
                    nama: master ? master.nama : '',
                    harga_jual: this.hargaBaru,
                    stok: this.stokBaru,
                });
                this.pilihUkuranId = '';
                this.hargaBaru = '';
                this.stokBaru = '';
            },

            hapusBaris(index) {
                this.baris.splice(index, 1);
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