{{--
    Form bundling (dipakai tambah & edit).
    Variabel: $produkForm (daftar produk + varian), $bundling (null saat tambah), $isiTersimpan (isi bundling saat edit)
--}}
@php
    $editing = isset($bundling) && $bundling;
    $isiTersimpan = $isiTersimpan ?? [];
    $jenisAwal = old('jenis_produk', $editing ? $bundling->jenis_produk : 'tetap');
    $jenisAwal = $jenisAwal === 'bebas' ? 'bebas' : 'tetap';
    $inputClass = 'w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-750 dark:text-gray-300';
@endphp

<form action="{{ $editing ? route('bundling.update', $bundling->id) : route('bundling.store') }}" method="POST" enctype="multipart/form-data">
@csrf
@if($editing)
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nama Bundling --}}
    <div>
        <label class="{{ $labelClass }}">Nama Bundling <span class="text-red-500">*</span></label>
        <input type="text" name="nama_bundling" value="{{ old('nama_bundling', $editing ? $bundling->nama_bundling : '') }}" class="{{ $inputClass }}">
        @error('nama_bundling')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Kode --}}
    <div>
        <label class="{{ $labelClass }}">Kode</label>
        <input type="text" value="{{ $editing ? $bundling->kode_bundling : 'Otomatis dibuat setelah disimpan' }}" disabled
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-400 px-4 py-3">
    </div>

    {{-- Harga Bundling --}}
    <div>
        <label class="{{ $labelClass }}">Harga Bundling <span class="text-red-500">*</span></label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-500 dark:text-gray-400 font-semibold text-sm">Rp</span>
            <input type="text" inputmode="numeric" name="harga" placeholder="25.000"
                value="{{ old('harga', $editing ? $bundling->harga : null) ? number_format((int) old('harga', $editing ? $bundling->harga : 0), 0, ',', '.') : '' }}"
                class="input-rupiah w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10 pr-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        </div>
        @error('harga')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Jenis Bundling --}}
    <div class="md:col-span-2">
        <label class="{{ $labelClass }}">Jenis Bundling <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-brand-500 transition">
                <input type="radio" name="jenis_produk" value="tetap" class="mt-1 w-4 h-4 text-brand-600 focus:ring-brand-500" {{ $jenisAwal === 'tetap' ? 'checked' : '' }} onchange="gantiJenis('tetap')">
                <div>
                    <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Tetap</span>
                    <span class="block text-xs text-gray-500">Isi paket ditentukan admin. Pembeli tidak bisa memilih sendiri.</span>
                </div>
            </label>
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-amber-500 transition">
                <input type="radio" name="jenis_produk" value="bebas" class="mt-1 w-4 h-4 text-amber-600 focus:ring-amber-500" {{ $jenisAwal === 'bebas' ? 'checked' : '' }} onchange="gantiJenis('bebas')">
                <div>
                    <span class="block font-bold text-sm text-gray-900 dark:text-white">Produk Bebas</span>
                    <span class="block text-xs text-gray-500">Pembeli memilih sendiri, tapi hanya dari produk dan varian yang admin tentukan.</span>
                </div>
            </label>
        </div>
        @error('jenis_produk')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Total isi (hanya produk bebas) --}}
    <div id="wrapper-total-qty" class="{{ $jenisAwal === 'bebas' ? '' : 'hidden' }}">
        <label class="{{ $labelClass }}">Total Isi Bundling <span class="text-red-500">*</span></label>
        <input type="number" min="1" name="total_qty_khusus" placeholder="Contoh: 6"
            value="{{ old('total_qty_khusus', $editing ? ($bundling->total_qty_khusus ?? 6) : 6) }}" class="{{ $inputClass }}">
        <small class="text-gray-400">Jumlah pcs yang wajib dipilih pembeli dari daftar pilihan di bawah.</small>
        @error('total_qty_khusus')
            <small class="text-red-500 block">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="{{ $labelClass }}">Status Bundling</label>
        <label class="flex items-center gap-3 h-12 cursor-pointer">
            <input type="hidden" name="status_aktif" value="0">
            <input type="checkbox" name="status_aktif" value="1" class="w-5 h-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', $editing ? (int) $bundling->status_aktif : '1') == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Bundling Aktif</span>
        </label>
        @error('status_aktif')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Deskripsi --}}
    <div>
        <label class="{{ $labelClass }}">Deskripsi</label>
        <textarea name="deskripsi" rows="3" class="{{ $inputClass }}">{{ old('deskripsi', $editing ? $bundling->deskripsi : '') }}</textarea>
        @error('deskripsi')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Gambar --}}
    <div>
        <label class="{{ $labelClass }}">Gambar Bundling</label>
        <div class="flex items-center gap-4">
            <img id="preview-gambar"
                src="{{ $editing && $bundling->gambar ? asset('storage/' . $bundling->gambar) : 'https://via.placeholder.com/96x96?text=No+Image' }}"
                class="w-24 h-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
            <input type="file" name="gambar" accept="image/*"
                onchange="if (this.files[0]) document.getElementById('preview-gambar').src = URL.createObjectURL(this.files[0])"
                class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-white hover:file:bg-brand-600">
        </div>
        @if($editing)
            <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak ingin mengganti gambar.</p>
        @endif
        @error('gambar')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

</div>

<hr class="my-8 border-gray-200 dark:border-gray-800">

<h4 id="judul-isi" class="font-semibold text-lg mb-1 text-gray-850 dark:text-white">Isi Bundling</h4>
<p id="petunjuk-isi" class="text-sm text-gray-500 dark:text-gray-400 mb-4"></p>

@foreach (['produk_id', 'detail_produk_id', 'qty'] as $kunci)
    @if ($errors->has($kunci))
        <p class="mb-2 text-sm text-red-500">{{ $errors->first($kunci) }}</p>
    @endif
@endforeach
@foreach ($errors->get('detail_produk_id.*') as $pesan)
    <p class="mb-2 text-sm text-red-500">{{ $pesan[0] }}</p>
@endforeach
@foreach ($errors->get('produk_id.*') as $pesan)
    <p class="mb-2 text-sm text-red-500">{{ $pesan[0] }}</p>
@endforeach

<div id="produk-container" class="space-y-4"></div>

<button type="button" id="btn-tambah-produk" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span id="label-tambah">Tambah Produk</span>
</button>

<div class="mt-8 flex justify-end gap-3">
    <a href="{{ route('bundling.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Batal</a>
    <button type="submit" id="btnSubmitBundling" class="rounded-lg bg-brand-500 px-5 py-2 text-white disabled:opacity-50 disabled:cursor-not-allowed">
        {{ $editing ? 'Simpan Perubahan' : 'Simpan Bundling' }}
    </button>
</div>

</form>

<script>
    // Tiap baris isi bundling = satu produk + satu varian (detail produk).
    //  - Produk Tetap : baris punya qty, pembeli mendapat isi persis seperti ini.
    //  - Produk Bebas : baris adalah pilihan yang boleh diambil pembeli (qty dipilih pembeli,
    //                   total mengikuti "Total Isi Bundling"), jadi kolom qty disembunyikan.
    const DAFTAR_PRODUK = @json($produkForm);
    let jenisAktif = @json($jenisAwal);

    const wadahProduk = document.getElementById('produk-container');

    function esc(teks) {
        return String(teks ?? '').replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }

    function cariProduk(id) {
        return DAFTAR_PRODUK.find(function (p) { return p.id == id; });
    }

    function gantiJenis(jenis) {
        jenisAktif = jenis;
        const bebas = jenis === 'bebas';

        document.getElementById('wrapper-total-qty').classList.toggle('hidden', !bebas);
        document.getElementById('judul-isi').textContent = bebas ? 'Pilihan Produk untuk Pembeli' : 'Isi Bundling';
        document.getElementById('petunjuk-isi').textContent = bebas
            ? 'Tambahkan produk dan varian yang boleh dipilih pembeli. Satu baris = satu varian. Pembeli memilih sejumlah "Total Isi Bundling" dari daftar ini.'
            : 'Tambahkan produk beserta variannya dan jumlah yang didapat pembeli. Isi paket ini tetap, pembeli tidak bisa mengubahnya.';
        document.getElementById('label-tambah').textContent = bebas ? 'Tambah Pilihan' : 'Tambah Produk';

        wadahProduk.querySelectorAll('.col-qty-produk').forEach(function (el) {
            el.classList.toggle('hidden', bebas);
        });
    }

    function tambahBaris(awal) {
        const div = document.createElement('div');
        div.className = 'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="relative md:col-span-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Produk <span class="text-red-500">*</span></label>
                    <input type="text" class="input-search-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        placeholder="Ketik nama produk..." autocomplete="off">
                    <div class="hasil-search absolute z-50 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"></div>
                    <input type="hidden" name="produk_id[]" class="input-id-produk">
                </div>

                <div class="md:col-span-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Varian <span class="text-red-500">*</span></label>
                    <select name="detail_produk_id[]" class="input-varian-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500" disabled>
                        <option value="">Pilih produk dulu</option>
                    </select>
                </div>

                <div class="col-qty-produk md:col-span-2 ${jenisAktif === 'bebas' ? 'hidden' : ''}">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                    <input type="number" name="qty[]" min="1" value="1" class="input-qty w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="info-produk text-sm text-gray-500">Pilih produk terlebih dahulu</div>
                <button type="button" class="btn-hapus-produk text-sm text-red-600 hover:text-red-800">Hapus</button>
            </div>

            <p class="peringatan-duplikat hidden text-xs text-red-600 mt-1">Produk dan varian ini sudah dipilih di baris lain!</p>
        `;

        wadahProduk.appendChild(div);
        pasangEventBaris(div);

        if (awal && awal.produk_id) {
            const produk = cariProduk(awal.produk_id);
            if (produk) {
                pilihProduk(div, produk, awal.detail_produk_id);
                if (awal.qty) {
                    div.querySelector('.input-qty').value = awal.qty;
                }
            }
        }

        cekForm();
        return div;
    }

    function isiInfoStok(div) {
        const info = div.querySelector('.info-produk');
        const select = div.querySelector('.input-varian-produk');
        const produk = cariProduk(div.querySelector('.input-id-produk').value);

        if (!produk) {
            info.textContent = 'Pilih produk terlebih dahulu';
            return;
        }
        if (!select.value) {
            info.textContent = 'Pilih varian untuk melihat stok';
            return;
        }

        const v = produk.varian.find(function (x) { return x.id == select.value; });
        if (!v) {
            info.textContent = '';
            return;
        }
        info.innerHTML = 'Stok tersedia (' + esc(v.nama) + '): <strong>' + v.stok + '</strong>'
            + (v.stok <= 0 ? ' <span class="text-amber-600">— perlu diproduksi</span>' : '')
            + (v.aktif ? '' : ' <span class="text-red-600">— varian nonaktif</span>');
    }

    function pilihProduk(div, produk, detailTerpilih) {
        const inputSearch = div.querySelector('.input-search-produk');
        const inputId = div.querySelector('.input-id-produk');
        const select = div.querySelector('.input-varian-produk');

        inputSearch.value = produk.nama_produk;
        inputId.value = produk.id;
        div.querySelector('.hasil-search').classList.add('hidden');

        select.disabled = false;
        select.innerHTML = '<option value="">Pilih varian</option>' + produk.varian.map(function (v) {
            return '<option value="' + v.id + '">' + esc(v.nama) + ' (Stok: ' + v.stok + ')' + (v.aktif ? '' : ' - nonaktif') + '</option>';
        }).join('');

        if (detailTerpilih) {
            select.value = String(detailTerpilih);
        } else if (produk.varian.length === 1) {
            select.value = String(produk.varian[0].id);
        }

        isiInfoStok(div);
        cekForm();
    }

    function pasangEventBaris(div) {
        const inputSearch = div.querySelector('.input-search-produk');
        const hasil = div.querySelector('.hasil-search');
        const inputId = div.querySelector('.input-id-produk');
        const select = div.querySelector('.input-varian-produk');

        function tampilkanHasil() {
            const kata = inputSearch.value.trim().toLowerCase();
            inputId.value = '';
            select.disabled = true;
            select.innerHTML = '<option value="">Pilih produk dulu</option>';
            isiInfoStok(div);
            cekForm();

            if (!kata) {
                hasil.classList.add('hidden');
                return;
            }

            const cocok = DAFTAR_PRODUK.filter(function (p) {
                return p.nama_produk.toLowerCase().includes(kata);
            });

            hasil.innerHTML = '';
            if (cocok.length === 0) {
                hasil.innerHTML = '<div class="p-3 text-sm text-gray-500">Produk tidak ditemukan</div>';
            }

            cocok.forEach(function (p) {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full px-4 py-3 text-left hover:bg-gray-100 dark:hover:bg-gray-700';
                item.innerHTML = '<div class="font-medium text-gray-800 dark:text-white">' + esc(p.nama_produk) + '</div>'
                    + '<div class="text-xs text-gray-500">' + p.varian.length + ' varian'
                    + (p.dijual_satuan ? '' : ' · <span class="text-amber-600">Hanya untuk bundling</span>') + '</div>';
                item.addEventListener('click', function () { pilihProduk(div, p); });
                hasil.appendChild(item);
            });

            hasil.classList.remove('hidden');
        }

        inputSearch.addEventListener('input', tampilkanHasil);
        select.addEventListener('change', function () { isiInfoStok(div); cekForm(); });

        div.querySelector('.btn-hapus-produk').addEventListener('click', function () {
            div.remove();
            cekForm();
        });
    }

    // Tutup daftar hasil pencarian kalau klik di luar.
    document.addEventListener('click', function (e) {
        wadahProduk.querySelectorAll('.hasil-search').forEach(function (hasil) {
            const kolom = hasil.parentElement;
            if (!kolom.contains(e.target)) {
                hasil.classList.add('hidden');
            }
        });
    });

    // Tombol simpan aktif kalau: ada minimal 1 baris, semua baris sudah pilih produk + varian,
    // dan tidak ada kombinasi produk + varian yang kembar.
    function cekForm() {
        const baris = wadahProduk.querySelectorAll(':scope > div');
        const hitung = {};
        let ada = false;
        let masalah = baris.length === 0;

        baris.forEach(function (b) {
            const id = b.querySelector('.input-id-produk').value;
            const varian = b.querySelector('.input-varian-produk').value;
            if (id && varian) {
                hitung[varian] = (hitung[varian] || 0) + 1;
            }
        });

        baris.forEach(function (b) {
            const id = b.querySelector('.input-id-produk').value;
            const select = b.querySelector('.input-varian-produk');
            const varian = select.value;
            const kembar = !!(id && varian && hitung[varian] > 1);
            const belumLengkap = !id || !varian;

            b.querySelector('.peringatan-duplikat').classList.toggle('hidden', !kembar);
            b.querySelector('.input-search-produk').classList.toggle('border-red-500', kembar);
            select.classList.toggle('border-red-500', !!(id && !varian));

            if (kembar || belumLengkap) {
                masalah = true;
            }
            ada = true;
        });

        const tombol = document.getElementById('btnSubmitBundling');
        if (tombol) {
            tombol.disabled = masalah;
        }
    }

    document.getElementById('btn-tambah-produk').addEventListener('click', function () {
        tambahBaris();
    });

    // ==== Isi awal baris: dari old() kalau validasi gagal, kalau tidak dari data tersimpan (edit) ====
    (function () {
        const oldProduk = @json(old('produk_id', []));
        const oldDetail = @json(old('detail_produk_id', []));
        const oldQty = @json(old('qty', []));
        const tersimpan = @json($isiTersimpan);

        gantiJenis(jenisAktif);

        if (oldProduk.length > 0) {
            oldProduk.forEach(function (id, i) {
                tambahBaris({produk_id: id, detail_produk_id: oldDetail[i] || null, qty: oldQty[i] || 1});
            });
        } else if (tersimpan.length > 0) {
            tersimpan.forEach(function (item) {
                tambahBaris(item);
            });
        } else {
            tambahBaris();
        }

        cekForm();
    })();

    // ==== Format rupiah ====
    document.querySelectorAll('.input-rupiah').forEach(function (input) {
        input.addEventListener('input', function () {
            const posisi = input.selectionStart;
            const digitSebelumKursor = input.value.slice(0, posisi).replace(/\D/g, '').length;

            const formatted = input.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
            form.querySelectorAll('.input-rupiah').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });
        });
    });
</script>