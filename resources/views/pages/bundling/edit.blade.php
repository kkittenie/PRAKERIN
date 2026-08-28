@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Bundling" parentTitle="Bundling" :parentUrl="route('bundling.index')" />

<div class="space-y-6">

<x-common.component-card title="Informasi Bundling">

<form action="{{ route('bundling.update', $bundling->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nama Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Nama Bundling <span class="text-red-500">*</span>
        </label>

        <input
            type="text"
            name="nama_bundling"
            value="{{ old('nama_bundling', $bundling->nama_bundling) }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('nama_bundling')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Kode --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Kode
        </label>

        <input
            type="text"
            value="{{ $bundling->kode_bundling }}"
            disabled
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 px-4 py-3">
    </div>

    {{-- Harga Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Harga Bundling <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">
                Rp
            </span>
            <input
                type="text"
                inputmode="numeric"
                name="harga"
                placeholder="25.000"
                value="{{ number_format((int) old('harga', $bundling->harga), 0, ',', '.') }}"
                class="input-rupiah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        </div>

        @error('harga')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status Bundling --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
            Status Bundling
        </label>
        <label class="flex items-center gap-3 h-12 cursor-pointer">
            <input type="hidden" name="status_aktif" value="0">
            <input
                type="checkbox"
                name="status_aktif"
                value="1"
                class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', $bundling->status_aktif) == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Bundling Aktif</span>
        </label>
        @error('status_aktif')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Deskripsi --}}
<div>
    <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
        Deskripsi
    </label>

    <textarea
        name="deskripsi"
        rows="3"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi', $bundling->deskripsi) }}</textarea>

    @error('deskripsi')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

{{-- Gambar Bundling --}}
<div>
    <label class="mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300">
        Gambar Bundling
    </label>

    <div class="flex items-center gap-4">
        <img id="preview-gambar"
             src="{{ $bundling->gambar ? asset('storage/'.$bundling->gambar) : 'https://via.placeholder.com/96x96?text=No+Image' }}"
             class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700">

        <input
            type="file"
            name="gambar"
            accept="image/*"
            onchange="document.getElementById('preview-gambar').src = URL.createObjectURL(this.files[0])"
            class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-white hover:file:bg-brand-600">
    </div>

    <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak ingin mengganti gambar.</p>

    @error('gambar')
        <small class="text-red-500">{{ $message }}</small>
    @enderror
</div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 class="font-semibold text-lg mb-4 text-gray-850 dark:text-white">
    Isi Bundling
</h4>

<div id="produk-container" class="space-y-4">
    {{-- Produk yang dipilih akan muncul di sini --}}
</div>

<button
    type="button"
    id="btn-tambah-produk"
    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">

    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>

    Tambah Produk

</button>

<div class="mt-8 flex justify-end gap-3">

    <a href="{{ route('bundling.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
        Batal
    </a>

    <button type="submit" id="btnSubmitBundling" class="rounded-lg bg-brand-500 px-5 py-2 text-white disabled:opacity-50 disabled:cursor-not-allowed">
        Simpan Perubahan
    </button>

</div>

</form>

</x-common.component-card>

</div>

<script>
    // Cuma produk reguler (biasa) yang bisa dimasukkan ke bundling. Setiap
    // produk sudah membawa `punya_ukuran` & `daftar_ukuran` (lihat accessor
    // di App\Models\Produk) sehingga baris produk bisa menampilkan pilihan
    // ukuran secara dinamis, sama seperti di menu Kasir & Produk.
    const daftarProduk = @json($produkBiasa);
</script>

<script>

const container = document.getElementById('produk-container');
const tombolTambah = document.getElementById('btn-tambah-produk');

function tambahBarisProduk() {

    const div = document.createElement('div');

    div.className =
        'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

    div.innerHTML = `

        <div class="relative col-search-produk">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Cari Produk
            </label>

            <input
                type="text"
                class="input-search-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                placeholder="Ketik nama produk..."
                autocomplete="off">

            <div
                class="hasil-search absolute z-50 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            </div>

            <input
                type="hidden"
                name="produk_id[]"
                class="input-id-produk">

        </div>

        <div class="col-ukuran-produk hidden mt-4">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Ukuran <span class="text-red-500">*</span>
            </label>

            <select
                name="ukuran[]"
                class="input-ukuran-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
                <option value="">Pilih ukuran</option>
            </select>

        </div>

        <div class="col-qty-produk mt-4">

            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Qty
            </label>

            <input
                type="number"
                name="qty[]"
                min="1"
                value="1"
                class="input-qty w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">

        </div>

        <div class="mt-3 flex items-center justify-between">

            <div class="info-produk text-sm text-gray-500">
                Pilih produk terlebih dahulu
            </div>

            <button type="button" class="btn-hapus-produk text-sm text-red-600 hover:text-red-800">
                Hapus
            </button>

        </div>

        <p class="peringatan-duplikat hidden text-xs text-red-600 mt-1">
            Kombinasi produk & ukuran ini sudah dipilih di baris lain!
        </p>
    `;

    container.appendChild(div);

    aktifkanSearchProduk(div);

    return div;
}

tombolTambah.addEventListener('click', function () {
    tambahBarisProduk();
});

// Nampilin info stok sesuai ukuran yang lagi dipilih (atau stok polos kalau
// produknya gak punya varian ukuran).
function perbaruiInfoStok(div, produk, namaUkuran) {

    const infoProduk = div.querySelector('.info-produk');

    if (!produk) {
        infoProduk.textContent = 'Pilih produk terlebih dahulu';
        return;
    }

    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;

    if (!punyaUkuran) {
        infoProduk.innerHTML = `Stok tersedia: <strong>${produk.stok}</strong>`;
        return;
    }

    if (!namaUkuran) {
        infoProduk.textContent = 'Pilih ukuran untuk melihat stok';
        return;
    }

    const opsi = (produk.daftar_ukuran || []).find(function (o) {
        return o.nama === namaUkuran;
    });

    infoProduk.innerHTML = opsi
        ? `Stok tersedia (${opsi.nama}): <strong>${opsi.stok}</strong>${opsi.perlu_produksi ? ' <span class="text-amber-600">— perlu diproduksi</span>' : ''}`
        : 'Pilih ukuran untuk melihat stok';
}

function pilihProdukPadaBaris(div, produk) {

    const inputSearch = div.querySelector('.input-search-produk');
    const inputId = div.querySelector('.input-id-produk');
    const hasilSearch = div.querySelector('.hasil-search');
    const wrapUkuran = div.querySelector('.col-ukuran-produk');
    const selectUkuran = div.querySelector('.input-ukuran-produk');

    inputSearch.value = produk.nama_produk;
    inputId.value = produk.id;
    hasilSearch.classList.add('hidden');

    const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
    const daftarUkuran = produk.daftar_ukuran || [];

    if (punyaUkuran && daftarUkuran.length > 0) {

        wrapUkuran.classList.remove('hidden');

        selectUkuran.innerHTML = '<option value="">Pilih ukuran</option>' + daftarUkuran.map(function (opsi) {
            return `<option value="${opsi.nama}">${opsi.nama} — Rp ${Math.round(opsi.harga).toLocaleString('id-ID')} (Stok: ${opsi.stok})</option>`;
        }).join('');

    } else {
        wrapUkuran.classList.add('hidden');
        selectUkuran.innerHTML = '';
    }

    perbaruiInfoStok(div, produk, selectUkuran.value);

    cekValidasiForm();
}

function aktifkanSearchProduk(element) {

    const inputSearch = element.querySelector('.input-search-produk');
    const hasilSearch = element.querySelector('.hasil-search');
    const inputId = element.querySelector('.input-id-produk');
    const selectUkuran = element.querySelector('.input-ukuran-produk');

    inputSearch.addEventListener('input', function () {

        const keyword = this.value.toLowerCase();

        inputId.value = '';

        if (keyword.length === 0) {
            hasilSearch.classList.add('hidden');
            cekValidasiForm();
            return;
        }

        const hasil = daftarProduk.filter(function (produk) {
            return produk.nama_produk.toLowerCase().includes(keyword);
        });

        hasilSearch.innerHTML = '';

        if (hasil.length === 0) {
            hasilSearch.innerHTML = `
                <div class="p-3 text-sm text-gray-500">
                    Produk tidak ditemukan
                </div>
            `;
        } else {
            hasil.forEach(function (produk) {
                const punyaUkuran = produk.punya_ukuran == 1 || produk.punya_ukuran === true;
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700';
                item.innerHTML = `
                    <div class="font-medium text-gray-800 dark:text-white">
                        ${produk.nama_produk}
                    </div>
                    <div class="text-xs text-gray-500">
                        ${punyaUkuran ? 'Punya varian ukuran' : 'Stok: ' + produk.stok}
                    </div>
                `;

                item.addEventListener('click', function () {
                    pilihProdukPadaBaris(element, produk);
                });

                hasilSearch.appendChild(item);
            });
        }

        hasilSearch.classList.remove('hidden');
    });

    selectUkuran.addEventListener('change', function () {
        const id = inputId.value;
        const produk = daftarProduk.find(function (p) { return p.id == id; });
        perbaruiInfoStok(element, produk, this.value);
        cekValidasiForm();
    });

    document.addEventListener('click', function (e) {
        if (!inputSearch.contains(e.target) && !hasilSearch.contains(e.target)) {
            hasilSearch.classList.add('hidden');
        }
    });

    element.querySelector('.btn-hapus-produk').addEventListener('click', function () {
        element.remove();
        cekValidasiForm();
    });
}

// Validasi baris: cek kombinasi produk+ukuran yang kembar, dan pastikan
// ukuran udah dipilih buat produk yang punya varian ukuran, sebelum tombol
// simpan diaktifkan.
function cekValidasiForm() {

    const semuaBaris = document.querySelectorAll('#produk-container > div');

    const jumlahPerKombinasi = {};

    semuaBaris.forEach(function (baris) {
        const id = baris.querySelector('.input-id-produk').value;
        const selectUkuran = baris.querySelector('.input-ukuran-produk');
        const ukuran = selectUkuran ? selectUkuran.value : '';
        if (id) {
            const kunci = id + '|' + ukuran;
            jumlahPerKombinasi[kunci] = (jumlahPerKombinasi[kunci] || 0) + 1;
        }
    });

    let adaMasalah = false;

    semuaBaris.forEach(function (baris) {
        const inputId = baris.querySelector('.input-id-produk');
        const inputSearch = baris.querySelector('.input-search-produk');
        const peringatan = baris.querySelector('.peringatan-duplikat');
        const wrapUkuran = baris.querySelector('.col-ukuran-produk');
        const selectUkuran = baris.querySelector('.input-ukuran-produk');
        const ukuran = selectUkuran ? selectUkuran.value : '';
        const kunci = inputId.value + '|' + ukuran;

        const duplikat = inputId.value && jumlahPerKombinasi[kunci] > 1;
        const ukuranWajibKosong = inputId.value && wrapUkuran && !wrapUkuran.classList.contains('hidden') && !ukuran;

        if (duplikat) {
            peringatan.classList.remove('hidden');
            inputSearch.classList.add('border-red-500');
            adaMasalah = true;
        } else {
            peringatan.classList.add('hidden');
            inputSearch.classList.remove('border-red-500');
        }

        if (selectUkuran) {
            selectUkuran.classList.toggle('border-red-500', !!ukuranWajibKosong);
        }
        if (ukuranWajibKosong) {
            adaMasalah = true;
        }
    });

    const btnSubmit = document.getElementById('btnSubmitBundling');
    if (btnSubmit) {
        btnSubmit.disabled = adaMasalah;
    }
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
    form.addEventListener('submit', function () {
        document.querySelectorAll('.input-rupiah').forEach(function (input) {
            input.value = input.value.replace(/\./g, '');
        });
    });
});

// ==== Isi ulang baris produk: dari old() kalau validasi gagal, kalau tidak dari data tersimpan ====
const oldProdukId = @json(old('produk_id', []));
const oldQty = @json(old('qty', []));
const oldUkuran = @json(old('ukuran', []));

if (oldProdukId.length > 0) {

    oldProdukId.forEach(function (id, index) {

        const produk = daftarProduk.find(function (p) {
            return p.id == id;
        });

        if (!produk) {
            return;
        }

        const div = tambahBarisProduk();

        pilihProdukPadaBaris(div, produk);

        div.querySelector('.input-qty').value = oldQty[index] ?? 1;

        const selectUkuran = div.querySelector('.input-ukuran-produk');
        if (selectUkuran && oldUkuran[index]) {
            selectUkuran.value = oldUkuran[index];
            perbaruiInfoStok(div, produk, oldUkuran[index]);
        }
    });

} else {

    @foreach($bundling->items as $item)
        (function () {
            const produk = daftarProduk.find(function (p) {
                return p.id == {{ $item->produk_id }};
            });

            if (!produk) {
                return;
            }

            const div = tambahBarisProduk();

            pilihProdukPadaBaris(div, produk);

            div.querySelector('.input-qty').value = {{ $item->qty }};

            const namaUkuran = @json($item->ukuran);
            const selectUkuran = div.querySelector('.input-ukuran-produk');
            if (selectUkuran && namaUkuran) {
                selectUkuran.value = namaUkuran;
                perbaruiInfoStok(div, produk, namaUkuran);
            }
        })();
    @endforeach

}

cekValidasiForm();

</script>

@endsection