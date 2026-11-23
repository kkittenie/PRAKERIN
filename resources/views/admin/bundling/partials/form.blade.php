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

    {{-- Kategori Bundling: di kasir, bundling muncul saat kategori ini dipilih --}}
    <div>
        <label class="{{ $labelClass }}">Kategori Bundling <span class="text-red-500">*</span></label>
        <select name="kategori_id" class="{{ $inputClass }}">
            <option value="">Pilih Kategori</option>
            @foreach (($kategori ?? []) as $kat)
                <option value="{{ $kat->id }}" {{ (string) old('kategori_id', $editing ? $bundling->kategori_id : '') === (string) $kat->id ? 'selected' : '' }}>
                    {{ $kat->nama_kategori }}
                </option>
            @endforeach
        </select>
        <small class="text-gray-400">Bundling tampil di kasir saat kategori ini dipilih.</small>
        @error('kategori_id')
            <small class="text-red-500 block">{{ $message }}</small>
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
                    <span class="block text-xs text-gray-500">Admin menentukan produk dan ukurannya. Pembeli memilih rasanya sendiri.</span>
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

@foreach (['produk_id', 'varian_isi', 'qty'] as $kunci)
    @if ($errors->has($kunci))
        <p class="mb-2 text-sm text-red-500">{{ $errors->first($kunci) }}</p>
    @endif
@endforeach
@foreach ($errors->get('varian_isi.*') as $pesan)
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
    // Tiap baris isi bundling:
    //  - Produk Tetap : produk + varian (detail produk) + qty. Pembeli mendapat isi persis seperti ini.
    //  - Produk Bebas : produk + UKURAN. Rasa tidak dipilih di sini, pembeli yang memilih rasa dari semua
    //                   rasa aktif produk + ukuran itu (total mengikuti "Total Isi Bundling"), jadi qty disembunyikan.
    // Pilihan baris dikirim lewat satu field, varian_isi[]: id varian (tetap) atau nama ukuran (bebas).
    const DAFTAR_PRODUK = @json($produkForm);
    const TANPA_UKURAN = '__tanpa__';
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

    function nilaiUkuran(ukuran) {
        const u = String(ukuran ?? '').trim();
        return u === '' ? TANPA_UKURAN : u;
    }

    // Produk yang memang tidak punya ukuran (semua rasa aktifnya tanpa ukuran) tidak perlu memilih ukuran.
    // Pada bundling bebas, kolom ukuran disembunyikan dan nilainya otomatis "tanpa ukuran".
    function produkTanpaUkuran(produk) {
        return produk.ukuran.length > 0 && produk.ukuran.every(function (u) {
            return nilaiUkuran(u.nilai) === TANPA_UKURAN;
        });
    }

    // Produk tetap: produk yang cuma punya satu varian tanpa ukuran & tanpa rasa (cuma "Standar")
    // tidak perlu memilih varian, jadi kolom Varian disembunyikan dan varian satu-satunya otomatis terpilih.
    function produkTanpaVarian(produk) {
        return produk.varian.length === 1
            && String(produk.varian[0].ukuran ?? '').trim() === ''
            && String(produk.varian[0].variasi ?? '').trim() === '';
    }

    // Tampilkan / sembunyikan kolom ukuran. Kolom tetap ikut terkirim (select tidak di-disable),
    // jadi varian_isi[] tetap berisi '__tanpa__' untuk produk tanpa ukuran.
    function aturKolomUkuran(div, sembunyi) {
        div.querySelector('.kolom-varian').classList.toggle('hidden', sembunyi);
        const kolomProduk = div.querySelector('.kolom-produk');
        kolomProduk.classList.toggle('md:col-span-5', !sembunyi);
        kolomProduk.classList.toggle('md:col-span-10', sembunyi);
    }

    // Isi pilihan (opsi) select sesuai jenis aktif. 'dipilih' = nilai yang ingin dipilih lagi (kalau ada).
    function isiOpsiVarian(div, produk, dipilih) {
        const select = div.querySelector('.input-varian-produk');
        const label = div.querySelector('.label-varian');
        const bebas = jenisAktif === 'bebas';

        label.firstChild.textContent = bebas ? 'Ukuran ' : 'Varian ';
        select.disabled = false;

        if (bebas) {
            let daftar = produk.ukuran.slice();
            // Ukuran tersimpan yang sudah tidak punya rasa aktif tetap ditampilkan supaya jelas, tapi akan ditolak saat simpan.
            if (dipilih && !daftar.some(function (u) { return nilaiUkuran(u.nilai) === dipilih; })) {
                daftar.push({nilai: dipilih === TANPA_UKURAN ? '' : dipilih, nama: (dipilih === TANPA_UKURAN ? 'Tanpa ukuran' : dipilih) + ' (tidak ada rasa aktif)', jumlah_rasa: 0, stok: 0, rasa: [], ada_rasa: false});
            }
            select.innerHTML = '<option value="">Pilih ukuran</option>' + daftar.map(function (u) {
                return '<option value="' + esc(nilaiUkuran(u.nilai)) + '">' + esc(u.nama) + (u.ada_rasa ? ' (' + u.jumlah_rasa + ' rasa)' : '') + '</option>';
            }).join('');
        } else {
            select.innerHTML = '<option value="">Pilih varian</option>' + produk.varian.map(function (v) {
                return '<option value="' + v.id + '">' + esc(v.nama) + ' (Stok: ' + v.stok + ')' + (v.aktif ? '' : ' - nonaktif') + '</option>';
            }).join('');
        }

        if (dipilih) {
            select.value = String(dipilih);
        }

        // Tidak perlu memilih apa pun kalau pilihannya pasti satu:
        //  - bebas : produk tanpa ukuran -> langsung "tanpa ukuran".
        //  - tetap : produk tanpa ukuran & rasa (satu varian "Standar") -> langsung varian itu.
        let otomatis = false;
        let nilaiOtomatis = '';
        if (bebas) {
            otomatis = produkTanpaUkuran(produk) && (!dipilih || String(dipilih) === TANPA_UKURAN);
            nilaiOtomatis = TANPA_UKURAN;
        } else if (produkTanpaVarian(produk)) {
            const idVarian = String(produk.varian[0].id);
            otomatis = !dipilih || String(dipilih) === idVarian;
            nilaiOtomatis = idVarian;
        }
        aturKolomUkuran(div, otomatis);
        if (otomatis) {
            select.value = nilaiOtomatis;
            return;
        }

        if (!select.value) {
            // Hanya satu pilihan -> langsung dipilih.
            const jumlahOpsi = select.options.length - 1;
            if (jumlahOpsi === 1) {
                select.selectedIndex = 1;
            }
        }
    }

    function gantiJenis(jenis) {
        const jenisLama = jenisAktif;
        jenisAktif = jenis;
        const bebas = jenis === 'bebas';

        document.getElementById('wrapper-total-qty').classList.toggle('hidden', !bebas);
        document.getElementById('judul-isi').textContent = bebas ? 'Pilihan Produk untuk Pembeli' : 'Isi Bundling';
        // document.getElementById('petunjuk-isi').textContent = bebas
        //     ? 'Tambahkan produk dan ukuran yang boleh dipilih pembeli. Satu baris = satu produk + satu ukuran. Rasanya dipilih pembeli dari semua rasa aktif ukuran itu, sebanyak "Total Isi Bundling".'
        //     : 'Tambahkan produk beserta variannya dan jumlah yang didapat pembeli. Isi paket ini tetap, pembeli tidak bisa mengubahnya.';
        document.getElementById('label-tambah').textContent = bebas ? 'Tambah Pilihan' : 'Tambah Produk';

        wadahProduk.querySelectorAll('.col-qty-produk').forEach(function (el) {
            el.classList.toggle('hidden', bebas);
        });

        // Baris yang sudah diisi tidak dihapus. Pilihan varian/ukuran dibuat ulang sesuai jenis baru:
        // varian -> ukuran ikut ukuran varian itu, ukuran -> varian harus dipilih lagi.
        if (jenisLama !== jenis) {
            wadahProduk.querySelectorAll(':scope > div').forEach(function (div) {
                const produk = cariProduk(div.querySelector('.input-id-produk').value);
                if (!produk) {
                    return;
                }
                const select = div.querySelector('.input-varian-produk');
                const lama = select.value;
                let baru = '';

                if (lama) {
                    if (bebas) {
                        const v = produk.varian.find(function (x) { return x.id == lama; });
                        baru = v ? nilaiUkuran(v.ukuran) : '';
                    }
                }
                isiOpsiVarian(div, produk, baru);
                isiInfoStok(div);
            });
        }

        cekForm();
    }

    function tambahBaris(awal) {
        const div = document.createElement('div');
        div.className = 'relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-800';

        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="kolom-produk relative md:col-span-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Produk <span class="text-red-500">*</span></label>
                    <input type="text" class="input-search-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3"
                        placeholder="Ketik nama produk..." autocomplete="off">
                    <div class="hasil-search absolute z-50 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"></div>
                    <input type="hidden" name="produk_id[]" class="input-id-produk">
                </div>

                <div class="kolom-varian md:col-span-5">
                    <label class="label-varian mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Varian <span class="text-red-500">*</span></label>
                    <select name="varian_isi[]" class="input-varian-produk w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500" disabled>
                        <option value="">Pilih produk dulu</option>
                    </select>
                </div>

                <div class="col-qty-produk md:col-span-2 ${jenisAktif === 'bebas' ? 'hidden' : ''}">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                    <input type="number" name="qty[]" min="1" value="1" class="input-qty w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">
                </div>
            </div>

            {{-- Bundling bebas: admin menentukan rasa mana saja yang boleh dipilih pembeli (nilai: JSON id rasa, kosong = semua). --}}
            <input type="hidden" name="rasa_isi[]" class="input-rasa-isi" value="">
            <div class="pilihan-rasa hidden mt-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Rasa yang boleh dipilih pembeli</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Centang rasa yang tersedia untuk pilihan ini.</p>
                    </div>
                    <button type="button" class="btn-semua-rasa shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-500/15">Pilih semua</button>
                </div>
                <div class="daftar-rasa mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3"></div>
                <p class="peringatan-rasa hidden text-xs text-red-600 mt-2">Pilih minimal satu rasa.</p>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="info-produk text-sm text-gray-500">Pilih produk terlebih dahulu</div>
                <button type="button" class="btn-hapus-produk text-sm text-red-600 hover:text-red-800">Hapus</button>
            </div>

            <p class="peringatan-duplikat hidden text-xs text-red-600 mt-1">Pilihan ini sudah ada di baris lain!</p>
        `;

        wadahProduk.appendChild(div);
        pasangEventBaris(div);

        if (awal && awal.produk_id) {
            const produk = cariProduk(awal.produk_id);
            if (produk) {
                div._rasaAwal = awal.rasa || null;
                pilihProduk(div, produk, awal.pilihan);
                if (awal.qty) {
                    div.querySelector('.input-qty').value = awal.qty;
                }
            }
        }

        cekForm();
        return div;
    }

    // ---- Pilihan rasa per baris (bundling bebas) ----
    function bersihkanPilihanRasa(div) {
        div.querySelector('.pilihan-rasa').classList.add('hidden');
        div.querySelector('.daftar-rasa').innerHTML = '';
        div.querySelector('.input-rasa-isi').value = '';
    }

    // Baca centang -> isi input tersembunyi (semua tercentang = '' = tidak dibatasi) dan perbarui info stok.
    function sinkronRasa(div, u) {
        const kotak = Array.from(div.querySelectorAll('.daftar-rasa input[type=checkbox]'));
        const dicentang = kotak.filter(function (k) { return k.checked; });
        const input = div.querySelector('.input-rasa-isi');

        if (dicentang.length === kotak.length) {
            input.value = '';
        } else {
            input.value = JSON.stringify(dicentang.map(function (k) { return parseInt(k.value, 10); }));
        }

        const stok = dicentang.reduce(function (total, k) { return total + (parseInt(k.dataset.stok, 10) || 0); }, 0);
        div.querySelector('.info-produk').innerHTML = dicentang.length + ' dari ' + kotak.length + ' rasa dipilih'
            + ' · Total stok: <strong>' + stok + '</strong>';
        div.querySelector('.peringatan-rasa').classList.toggle('hidden', dicentang.length > 0);
        cekForm();
    }

    // Tampilkan daftar centang rasa untuk ukuran yang dipilih. 'awal' = id rasa tersimpan (null = semua).
    function tampilkanPilihanRasa(div, u, awal) {
        const wadah = div.querySelector('.pilihan-rasa');
        const daftar = div.querySelector('.daftar-rasa');
        daftar.innerHTML = '';

        u.opsi.forEach(function (o) {
            const dipakai = !awal || awal.length === 0 || awal.some(function (id) { return id == o.id; });
            // Chip rasa: checkbox asli disembunyikan (tetap bisa difokus lewat keyboard), tampilannya lewat kotak centang buatan
            // dan warna chip yang berubah saat tercentang (group-has-[:checked]).
            const label = document.createElement('label');
            label.className = 'group flex cursor-pointer select-none items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 transition '
                + 'hover:border-brand-300 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-500/40 '
                + 'has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 '
                + 'dark:border-gray-700 dark:bg-gray-800 dark:has-[:checked]:bg-brand-500/15';
            label.innerHTML = '<input type="checkbox" value="' + o.id + '" data-stok="' + o.stok + '" class="sr-only"' + (dipakai ? ' checked' : '') + '>'
                + '<span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 border-gray-300 bg-white transition '
                + 'group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-500 dark:border-gray-600 dark:bg-gray-900">'
                + '<svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 text-white opacity-0 transition group-has-[:checked]:opacity-100">'
                + '<path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></span>'
                + '<span class="flex flex-col leading-tight">'
                + '<span class="text-sm font-semibold text-gray-800 dark:text-gray-100">' + esc(o.nama) + '</span>'
                + '<span class="text-xs ' + (o.stok > 0 ? 'text-gray-500 dark:text-gray-400' : 'text-amber-600') + '">'
                + (o.stok > 0 ? 'Stok ' + o.stok : 'Stok habis') + '</span></span>';
            label.querySelector('input').addEventListener('change', function () { sinkronRasa(div, u); });
            daftar.appendChild(label);
        });

        div.querySelector('.btn-semua-rasa').onclick = function () {
            daftar.querySelectorAll('input[type=checkbox]').forEach(function (k) { k.checked = true; });
            sinkronRasa(div, u);
        };

        wadah.classList.remove('hidden');
        sinkronRasa(div, u);
    }

    function isiInfoStok(div) {
        const info = div.querySelector('.info-produk');
        const select = div.querySelector('.input-varian-produk');
        const produk = cariProduk(div.querySelector('.input-id-produk').value);
        const bebas = jenisAktif === 'bebas';

        bersihkanPilihanRasa(div);

        if (!produk) {
            info.textContent = 'Pilih produk terlebih dahulu';
            return;
        }
        if (!select.value) {
            info.textContent = bebas ? 'Pilih ukuran untuk melihat rasa yang tersedia' : 'Pilih varian untuk melihat stok';
            return;
        }

        if (bebas) {
            const u = produk.ukuran.find(function (x) { return nilaiUkuran(x.nilai) === select.value; });
            if (!u || u.jumlah_rasa === 0) {
                info.innerHTML = '<span class="text-red-600">Ukuran ini tidak punya rasa aktif</span>';
                return;
            }
            // Produk tanpa varian rasa: tidak perlu menampilkan daftar rasa, cukup stoknya.
            // Beberapa rasa: admin boleh memilih rasa mana saja yang tersedia untuk pembeli.
            if (u.ada_rasa && u.opsi.length > 1) {
                const awal = div._rasaAwal || null;
                div._rasaAwal = null;
                tampilkanPilihanRasa(div, u, awal);
                return;
            }
            info.innerHTML = (u.ada_rasa ? 'Rasa yang bisa dipilih pembeli: <strong>' + esc(u.rasa.join(', ')) + '</strong> · ' : '')
                + 'Total stok: <strong>' + u.stok + '</strong>';
            return;
        }

        const v = produk.varian.find(function (x) { return x.id == select.value; });
        if (!v) {
            info.textContent = '';
            return;
        }
        info.innerHTML = 'Stok tersedia' + (produkTanpaVarian(produk) ? '' : ' (' + esc(v.nama) + ')') + ': <strong>' + v.stok + '</strong>'
            + (v.stok <= 0 ? ' <span class="text-amber-600">— sedang diproduksi</span>' : '')
            + (v.aktif ? '' : ' <span class="text-red-600">— varian nonaktif</span>');
    }

    function pilihProduk(div, produk, pilihan) {
        const inputSearch = div.querySelector('.input-search-produk');
        const inputId = div.querySelector('.input-id-produk');

        inputSearch.value = produk.nama_produk;
        inputId.value = produk.id;
        div.querySelector('.hasil-search').classList.add('hidden');

        isiOpsiVarian(div, produk, pilihan ? String(pilihan) : '');
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
            aturKolomUkuran(div, false);
            isiInfoStok(div);
            cekForm();

            if (!kata) {
                hasil.classList.add('hidden');
                return;
            }

            const bebasAktif = jenisAktif === 'bebas';
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
                    + '<div class="text-xs text-gray-500">' + (!bebasAktif && produkTanpaVarian(p) ? 'tanpa varian' : p.varian.length + ' varian · ' + (produkTanpaUkuran(p) ? 'tanpa ukuran' : p.ukuran.length + ' ukuran'))
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

    // Tombol simpan aktif kalau: ada minimal 1 baris, semua baris sudah pilih produk + varian/ukuran,
    // tidak ada pilihan yang kembar (tetap: varian sama; bebas: produk + ukuran sama), dan
    // pada bundling bebas ukuran yang dipilih punya minimal satu rasa aktif.
    function cekForm() {
        const baris = wadahProduk.querySelectorAll(':scope > div');
        const bebas = jenisAktif === 'bebas';
        const hitung = {};
        let masalah = baris.length === 0;

        function kunciBaris(b) {
            const id = b.querySelector('.input-id-produk').value;
            const nilai = b.querySelector('.input-varian-produk').value;
            if (!id || !nilai) {
                return null;
            }
            return bebas ? (id + '|' + nilai.toLowerCase()) : nilai;
        }

        baris.forEach(function (b) {
            const kunci = kunciBaris(b);
            if (kunci) {
                hitung[kunci] = (hitung[kunci] || 0) + 1;
            }
        });

        baris.forEach(function (b) {
            const id = b.querySelector('.input-id-produk').value;
            const select = b.querySelector('.input-varian-produk');
            const kunci = kunciBaris(b);
            const kembar = !!(kunci && hitung[kunci] > 1);
            const belumLengkap = !id || !select.value;

            let tanpaRasa = false;
            if (bebas && id && select.value) {
                const produk = cariProduk(id);
                const u = produk ? produk.ukuran.find(function (x) { return nilaiUkuran(x.nilai) === select.value; }) : null;
                tanpaRasa = !u || u.jumlah_rasa === 0;
            }

            // Bundling bebas: kalau daftar rasa tampil, minimal satu rasa harus dicentang.
            const rasaKosong = bebas
                && !b.querySelector('.pilihan-rasa').classList.contains('hidden')
                && b.querySelector('.daftar-rasa input[type=checkbox]:checked') === null;

            b.querySelector('.peringatan-duplikat').classList.toggle('hidden', !kembar);
            b.querySelector('.input-search-produk').classList.toggle('border-red-500', kembar);
            select.classList.toggle('border-red-500', !!(id && !select.value) || tanpaRasa);

            if (kembar || belumLengkap || tanpaRasa || rasaKosong) {
                masalah = true;
            }
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
        const oldPilihan = @json(old('varian_isi', []));
        const oldQty = @json(old('qty', []));
        const oldRasa = @json(old('rasa_isi', []));

        function urai(json) {
            try {
                const hasil = json ? JSON.parse(json) : null;
                return Array.isArray(hasil) ? hasil : null;
            } catch (e) {
                return null;
            }
        }
        const tersimpan = @json($isiTersimpan);

        gantiJenis(jenisAktif);

        if (oldProduk.length > 0) {
            oldProduk.forEach(function (id, i) {
                tambahBaris({produk_id: id, pilihan: oldPilihan[i] || null, qty: oldQty[i] || 1, rasa: urai(oldRasa[i])});
            });
        } else if (tersimpan.length > 0) {
            tersimpan.forEach(function (item) {
                // Bundling bebas menyimpan ukuran, bundling tetap menyimpan id varian.
                const pilihan = jenisAktif === 'bebas' ? nilaiUkuran(item.ukuran) : item.detail_produk_id;
                tambahBaris({produk_id: item.produk_id, pilihan: pilihan, qty: item.qty, rasa: item.rasa_diizinkan || null});
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