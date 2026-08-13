@extends('member.layout', ['title' => 'My Account'])

@section('content')

    {{-- Fixed-height page wrapper: only the middle section (below) scrolls --}}
    <div x-data="{ modalHapusAkunTerbuka: false, menghapusAkun: false }" class="flex h-screen max-h-screen flex-col overflow-hidden">

        <div class="-mt-6 flex flex-none items-center justify-between px-5 pt-6">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
            <h1 class="text-base font-extrabold text-brand-800">My Account</h1>
            <span class="w-8"></span>
        </div>

        @error('foto')
            <p class="mt-3 px-5 text-center text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
            class="mt-5 flex flex-1 flex-col overflow-hidden pb-6" x-data="{ uploadingFoto: false }">
            @csrf

            {{-- Background abu: full-bleed otomatis, karena <main> di halaman ini sudah tanpa padding horizontal.
                Foto profil "menggantung" absolute, separuh di dalam abu, separuh di putih. --}}
                <div class="flex-none">
                    <div class="relative bg-gray-50 px-5 pb-16 pt-4">
                        <div class="flex justify-center">
                            <span class="rounded-full bg-gray-100 px-4 py-1.5 text-xs font-medium text-gray-600">
                                Member since: {{ $member->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>

                        {{-- top-full + -translate-y-1/2 = dipatok di garis bawah box, naik separuh tinggi sendiri
                        → otomatis selalu 50:50 nutup abu vs nongol putih, apapun padding box-nya --}}
                        <div class="absolute left-1/2 top-full h-24 w-24 -translate-x-1/2 -translate-y-1/2">
                            <div class="relative h-full w-full">
                                <span
                                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-gray-200 ring-4 ring-white">
                                    @if ($member->foto)
                                        <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-gray-400">
                                            <path
                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    @endif

                                    {{-- Overlay loading pas foto lagi di-upload, biar keliatan ada respons
                                         (bukan cuma diem aja abis pencet ganti foto). --}}
                                    <span x-show="uploadingFoto" x-cloak
                                        class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40">
                                        <svg class="h-6 w-6 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                        </svg>
                                    </span>
                                </span>
                                <label for="foto"
                                    class="absolute -bottom-1 left-1/2 flex h-9 w-9 -translate-x-1/2 cursor-pointer items-center justify-center rounded-full border-2 border-brand-800 bg-white text-brand-800 active:scale-95">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 pointer-events-none">
                                        <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor"
                                            stroke-width="1.8" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="foto" id="foto" accept="image/*"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        @change="if ($event.target.files.length) { uploadingFoto = true; $event.target.form.submit(); }">
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Spacer: foto absolute nggak makan ruang di flow, jadi kasih jarak manual
                    sebesar separuh tinggi foto (h-24=96px → separuhnya h-12=48px)
                    supaya konten di bawah nggak ketiban foto --}}
                    <div class="h-12"></div>
                </div>

                {{-- Scrollable section starts here: from "Complete your profile" down to the Save button --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- Konten yang butuh inset kiri-kanan dibungkus px-5 di sini --}}
                    <div class="px-5">
                        @php $kelengkapan = $member->kelengkapan_profil; @endphp
                        <div class="px-0.5 pt-6 text-center">
                            <p class="text-sm font-bold text-brand-800">Complete your profile and earn vouchers on your
                                special day!</p>
                            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-brand-800 transition-all"
                                    style="width: {{ $kelengkapan }}%"></div>
                            </div>
                        </div>

                        <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-4 space-y-0">
                            <div>
                                <label for="nama" class="text-xs font-semibold text-gray-500">Nama</label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama', $member->nama) }}"
                                    class="mt-1.5 w-full rounded-xl border border-brand-100 bg-brand-25 px-4 py-3 text-sm font-bold uppercase tracking-wide text-brand-800 outline-none transition focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10">
                                @error('nama')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="text-xs font-semibold text-gray-500">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $member->email) }}"
                                    placeholder="Masukkan email kamu"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="tanggal_lahir_tombol" class="text-xs font-semibold text-gray-500">Date of Birth</label>
                                <button type="button" id="tanggal_lahir_tombol" onclick="bukaPilihTanggalLahir()"
                                    class="mt-1.5 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm outline-none transition focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                    <span id="tanggal_lahir_label" class="{{ $member->tanggal_lahir ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ $member->tanggal_lahir ? $member->tanggal_lahir->translatedFormat('d F Y') : 'Pilih tanggal lahir' }}
                                    </span>
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-gray-400">
                                        <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8" />
                                        <path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    </svg>
                                </button>
                                <input type="hidden" name="tanggal_lahir" id="tanggal_lahir"
                                    value="{{ old('tanggal_lahir', $member->tanggal_lahir?->format('Y-m-d')) }}">
                                @error('tanggal_lahir')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="no_hp" class="text-xs font-semibold text-gray-400">Phone Number</label>
                                <input type="text" id="no_hp" value="{{ $member->no_hp }}" disabled
                                    class="mt-1.5 w-full cursor-not-allowed rounded-xl border border-gray-100 bg-gray-100 px-4 py-3 text-sm text-gray-400">
                            </div>
                        </div>
                    </div>

                    {{-- OTHERS bar: full-bleed, nggak dibungkus px-5 di luar, cuma di dalam buat teksnya --}}
                    <div class="mt-8 bg-gray-50 px-5 py-3">
                        <span class="text-xs font-bold tracking-wide text-gray-500">OTHERS</span>
                    </div>

                    <div class="px-5">
                        <button type="button"
                            @click.stop="modalHapusAkunTerbuka = true"
                            class="mt-5 flex items-center gap-2 text-sm font-bold text-red-700">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path
                                    d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 7h12Z"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            DELETE ACCOUNT
                        </button>
                    </div>

                    {{-- Save button: full-bleed, tombolnya sendiri yang nyampe ujung (bukan cuma wadahnya) --}}
                    <div class="mt-8">
                        <button type="submit"
                            class="w-full rounded-lg bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white">
                            Save
                        </button>
                    </div>

                </div>
                {{-- Scrollable section ends here --}}

        </form>

        <!-- ========== MODAL: KONFIRMASI HAPUS AKUN ========== -->
        <div x-show="modalHapusAkunTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="if (!menghapusAkun) modalHapusAkunTerbuka = false"
                class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100">
                <div class="p-6 text-center">
                    <div
                        class="w-14 h-14 bg-red-100 text-red-600 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                        ⚠
                    </div>
                    <h3 class="font-extrabold text-gray-900 text-base">Hapus Akun?</h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Yakin ingin menghapus akun kamu? Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
                <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-center gap-3">
                    <button type="button" @click="modalHapusAkunTerbuka = false" :disabled="menghapusAkun"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-lg transition text-gray-700 disabled:opacity-50">Batal</button>
                    <button type="button" @click="menghapusAkun = true; $refs.formHapusAkun.submit()" :disabled="menghapusAkun"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs disabled:opacity-50">
                        <span x-show="!menghapusAkun">Hapus</span>
                        <span x-show="menghapusAkun" x-cloak>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('member.profil.hapus-akun') }}" x-ref="formHapusAkun" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <!-- ========== SHEET: PILIH TANGGAL LAHIR ========== -->
        <dialog id="sheet-tanggal-lahir"
            class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
            <div class="flex flex-col">
                <div class="flex justify-center pb-1 pt-3">
                    <span class="h-1 w-10 rounded-full bg-gray-200"></span>
                </div>

                <h3 class="px-6 pt-3 text-center text-theme-md font-extrabold text-brand-800">Select Birth date</h3>

                <div class="relative mt-5 px-6">
                    {{-- Highlight band behind the centered row --}}
                    <div class="pointer-events-none absolute inset-x-6 top-1/2 h-11 -translate-y-1/2 rounded-xl bg-brand-25"></div>
                    {{-- Fade top & bottom so the wheel doesn't look like it cuts off abruptly --}}
                    <div class="pointer-events-none absolute inset-x-6 inset-y-0 z-10"
                        style="background: linear-gradient(to bottom, #fff, rgba(255,255,255,0) 24%, rgba(255,255,255,0) 76%, #fff);">
                    </div>

                    <div class="relative grid grid-cols-3 gap-2">
                        <div id="kolom-hari" class="picker-kolom overflow-y-scroll" style="height:220px; scroll-snap-type:y mandatory;"></div>
                        <div id="kolom-bulan" class="picker-kolom overflow-y-scroll" style="height:220px; scroll-snap-type:y mandatory;"></div>
                        <div id="kolom-tahun" class="picker-kolom overflow-y-scroll" style="height:220px; scroll-snap-type:y mandatory;"></div>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-5">
                    <button type="button" onclick="pilihTanggalLahir()"
                        class="w-full rounded-lg bg-brand-800 py-3.5 text-sm font-bold uppercase tracking-wide text-white active:bg-brand-700">
                        Choose
                    </button>
                </div>
            </div>
        </dialog>

        <style>
            .picker-kolom { scrollbar-width: none; }
            .picker-kolom::-webkit-scrollbar { display: none; }
        </style>

        <script>
            (function () {
                var namaBulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                var namaBulanPanjang = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                var tinggiBaris = 44; // px — must match the row height below (h-11)
                var barisTampil = 5;  // must be odd, so there's a true center row
                var paddingKolom = tinggiBaris * Math.floor(barisTampil / 2);

                var tahunSekarang = new Date().getFullYear();
                var tahunMin = tahunSekarang - 100;
                var tahunMax = tahunSekarang;

                var kolomHari = document.getElementById('kolom-hari');
                var kolomBulan = document.getElementById('kolom-bulan');
                var kolomTahun = document.getElementById('kolom-tahun');
                var inputTanggalLahir = document.getElementById('tanggal_lahir');
                var labelTanggalLahir = document.getElementById('tanggal_lahir_label');

                if (!kolomHari || !kolomBulan || !kolomTahun || !inputTanggalLahir) return;

                var pilih = { hari: 1, bulan: 0, tahun: tahunSekarang - 20 };

                var nilaiAwal = inputTanggalLahir.value;
                if (nilaiAwal) {
                    var bagian = nilaiAwal.split('-');
                    if (bagian.length === 3) {
                        pilih = {
                            tahun: parseInt(bagian[0], 10),
                            bulan: parseInt(bagian[1], 10) - 1,
                            hari: parseInt(bagian[2], 10)
                        };
                    }
                }

                function jumlahHariDiBulan(bulan, tahun) {
                    return new Date(tahun, bulan + 1, 0).getDate();
                }

                function isiKolom(kolom, daftar, nilaiDipilih) {
                    kolom.innerHTML = '';
                    kolom.style.paddingTop = paddingKolom + 'px';
                    kolom.style.paddingBottom = paddingKolom + 'px';
                    var indexTerpilih = 0;
                    daftar.forEach(function (item, i) {
                        if (item.nilai === nilaiDipilih) indexTerpilih = i;
                        var baris = document.createElement('div');
                        baris.style.height = tinggiBaris + 'px';
                        baris.style.scrollSnapAlign = 'center';
                        baris.className = 'flex items-center justify-center text-sm text-gray-400 transition-all duration-150 tabular-nums';
                        baris.textContent = item.label;
                        baris.dataset.nilai = item.nilai;
                        kolom.appendChild(baris);
                    });
                    kolom.scrollTop = indexTerpilih * tinggiBaris;
                    perbaruiSorotan(kolom);
                }

                function perbaruiSorotan(kolom) {
                    var index = Math.max(0, Math.min(kolom.children.length - 1, Math.round(kolom.scrollTop / tinggiBaris)));
                    Array.from(kolom.children).forEach(function (baris, i) {
                        baris.className = i === index
                            ? 'flex items-center justify-center text-base font-bold text-brand-800 transition-all duration-150 tabular-nums'
                            : 'flex items-center justify-center text-sm text-gray-400 transition-all duration-150 tabular-nums';
                    });
                    return index;
                }

                function nilaiTerpilih(kolom) {
                    var index = Math.max(0, Math.min(kolom.children.length - 1, Math.round(kolom.scrollTop / tinggiBaris)));
                    var baris = kolom.children[index];
                    return baris ? parseInt(baris.dataset.nilai, 10) : 0;
                }

                var waktuScroll;
                function pasangScrollListener(kolom, onSelesai) {
                    kolom.addEventListener('scroll', function () {
                        perbaruiSorotan(kolom);
                        clearTimeout(waktuScroll);
                        waktuScroll = setTimeout(function () {
                            var index = perbaruiSorotan(kolom);
                            kolom.scrollTo({ top: index * tinggiBaris, behavior: 'smooth' });
                            if (onSelesai) onSelesai(nilaiTerpilih(kolom));
                        }, 120);
                    }, { passive: true });
                }

                function bangunHari() {
                    var jumlah = jumlahHariDiBulan(pilih.bulan, pilih.tahun);
                    if (pilih.hari > jumlah) pilih.hari = jumlah;
                    var daftar = [];
                    for (var h = 1; h <= jumlah; h++) daftar.push({ nilai: h, label: String(h) });
                    isiKolom(kolomHari, daftar, pilih.hari);
                }

                function bangunBulan() {
                    var daftar = namaBulanSingkat.map(function (nama, i) { return { nilai: i, label: nama }; });
                    isiKolom(kolomBulan, daftar, pilih.bulan);
                }

                function bangunTahun() {
                    var daftar = [];
                    for (var t = tahunMin; t <= tahunMax; t++) daftar.push({ nilai: t, label: String(t) });
                    isiKolom(kolomTahun, daftar, pilih.tahun);
                }

                pasangScrollListener(kolomHari, function (nilai) { pilih.hari = nilai; });
                pasangScrollListener(kolomBulan, function (nilai) { pilih.bulan = nilai; bangunHari(); });
                pasangScrollListener(kolomTahun, function (nilai) { pilih.tahun = nilai; bangunHari(); });

                bangunBulan();
                bangunTahun();
                bangunHari();

                window.bukaPilihTanggalLahir = function () {
                    openSheet('sheet-tanggal-lahir');
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            kolomHari.scrollTop = (pilih.hari - 1) * tinggiBaris;
                            kolomBulan.scrollTop = pilih.bulan * tinggiBaris;
                            kolomTahun.scrollTop = (pilih.tahun - tahunMin) * tinggiBaris;
                            perbaruiSorotan(kolomHari);
                            perbaruiSorotan(kolomBulan);
                            perbaruiSorotan(kolomTahun);
                        });
                    });
                };

                window.pilihTanggalLahir = function () {
                    pilih.hari = nilaiTerpilih(kolomHari);
                    pilih.bulan = nilaiTerpilih(kolomBulan);
                    pilih.tahun = nilaiTerpilih(kolomTahun);

                    var hariStr = String(pilih.hari).padStart(2, '0');
                    var bulanStr = String(pilih.bulan + 1).padStart(2, '0');
                    inputTanggalLahir.value = pilih.tahun + '-' + bulanStr + '-' + hariStr;

                    if (labelTanggalLahir) {
                        labelTanggalLahir.textContent = pilih.hari + ' ' + namaBulanPanjang[pilih.bulan] + ' ' + pilih.tahun;
                        labelTanggalLahir.classList.remove('text-gray-400');
                        labelTanggalLahir.classList.add('text-gray-800');
                    }

                    closeSheet('sheet-tanggal-lahir');
                };
            })();
        </script>

    </div>

@endsection