@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Selesai Shift" parentTitle="Shift Kasir" parentUrl="{{ route('shift.index') }}" />

    @php
        // Kolom input mandiri kasir. Nominal seharusnya sengaja TIDAK ditampilkan di halaman ini.
        $kolomTotal = [
            'total_tunai' => ['label' => 'Total Tunai', 'warna' => 'text-green-600 dark:text-green-400'],
            'total_qris' => ['label' => 'Total QRIS', 'warna' => 'text-blue-600 dark:text-blue-400'],
            'total_debit' => ['label' => 'Total Debit', 'warna' => 'text-purple-600 dark:text-purple-400'],
            'total_member_app' => ['label' => 'Total Member App', 'warna' => 'text-teal-600 dark:text-teal-400'],
        ];
        $kelasInput = 'h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 text-lg font-bold focus:border-amber-500 focus:ring-amber-500';
    @endphp

    <div x-data="formTutupShift(@js([
            'uang_akhir' => old('uang_akhir', ''),
            'total_tunai' => old('total_tunai', ''),
            'total_qris' => old('total_qris', ''),
            'total_debit' => old('total_debit', ''),
            'total_member_app' => old('total_member_app', ''),
         ]))"
         class="w-full">

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Selesai {{ $shift->labelShift() }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $shift->nama_kasir }} · {{ $shift->mulai_pada->translatedFormat('l, d M Y') }} · {{ $shift->mulai_pada->format('H:i') }} – {{ now()->format('H:i') }} WIB
                </p>
            </div>

            <form x-ref="form" method="POST" action="{{ route('shift.tutup.simpan') }}" @submit.prevent="tanya(keluar)" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="keluar" :value="keluar">

                {{-- Hitung Uang di Laci --}}
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Hitung Uang di Laci</h3>
                    <p class="mt-1 mb-4 text-sm text-gray-500 dark:text-gray-400">Hitung semua uang tunai yang ada di laci sekarang, lalu masukkan nominalnya.</p>

                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sisa uang di laci <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                        <input type="text" inputmode="numeric" autocomplete="off" :value="teks.uang_akhir" @input="ubah('uang_akhir', $event)" placeholder="0" autofocus
                               class="h-14 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 text-xl font-bold focus:border-amber-500 focus:ring-amber-500">
                        <input type="hidden" name="uang_akhir" :value="nilai.uang_akhir">
                    </div>
                    @error('uang_akhir')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Total penjualan hitungan kasir sendiri --}}
                <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Total Penjualan Shift Ini</h3>
                    <p class="mt-1 mb-4 text-sm text-gray-500 dark:text-gray-400">Isi dari hitunganmu sendiri. Isi 0 kalau tidak ada.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        @foreach ($kolomTotal as $nama => $k)
                            <div>
                                <label class="mb-2 block text-sm font-medium {{ $k['warna'] }}">
                                    {{ $k['label'] }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" :value="teks.{{ $nama }}" @input="ubah('{{ $nama }}', $event)" placeholder="0"
                                           class="{{ $kelasInput }}">
                                    <input type="hidden" name="{{ $nama }}" :value="nilai.{{ $nama }}">
                                </div>
                                @error($nama)
                                    <small class="text-red-500">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan (opsional)</label>
                    <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Contoh: ada uang kembalian yang salah"
                           class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 focus:border-amber-500 focus:ring-amber-500">
                    @error('catatan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="flex flex-col md:flex-row gap-2 pt-1">
                    <button type="button" @click="tanya(1)" :disabled="!lengkap || kirim"
                            class="w-full md:flex-1 px-5 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        Selesai Shift &amp; Keluar
                    </button>
                    <button type="button" @click="tanya(0)" :disabled="!lengkap || kirim"
                            class="w-full md:flex-1 px-5 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        Selesai Shift (tetap login)
                    </button>
                    <a href="{{ route('shift.index') }}"
                       class="w-full md:w-auto px-5 py-3 text-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">
                        Batal, kembali ke shift
                    </a>
                </div>
                <p x-show="!lengkap" class="-mt-3 text-center text-xs text-amber-600 dark:text-amber-400">
                    Isi semua kolom dulu (isi 0 kalau tidak ada) supaya tombol selesai shift aktif. Shift wajib ditutup sebelum keluar.
                </p>
            </form>
        </div>

        {{-- Dialog konfirmasi --}}
        <div x-show="konfirmasi" x-cloak x-transition.opacity
             class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="konfirmasi = false">
            <div @click.away="konfirmasi = false" class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 shadow-xl border border-gray-100 dark:border-gray-800 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Selesaikan shift sekarang?</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Setelah ditutup, data shift tidak bisa diubah lagi. Pastikan angka di bawah sudah benar.</p>

                <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Sisa uang di laci</span><span class="font-semibold" x-text="rupiah(nilai.uang_akhir)"></span></div>
                    @foreach ($kolomTotal as $nama => $k)
                        <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>{{ $k['label'] }}</span><span class="font-semibold" x-text="rupiah(nilai.{{ $nama }})"></span></div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button type="button" @click="konfirmasi = false"
                            class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">
                        Cek lagi
                    </button>
                    <button type="button" @click="kirimForm()" :disabled="kirim"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-lg text-sm transition shadow-sm"
                            x-text="keluar ? 'Ya, selesai & keluar' : 'Ya, selesaikan shift'"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form selesai shift: 5 input nominal rupiah (tampil "1.250.000", nilai asli dikirim lewat input hidden).
        function formTutupShift(awal = {}) {
            const kunci = ['uang_akhir', 'total_tunai', 'total_qris', 'total_debit', 'total_member_app'];
            const bersihkan = (v) => String(v ?? '').replace(/\D/g, '').slice(0, 12);
            const format = (v) => (v === '' ? '' : Number(v).toLocaleString('id-ID'));

            const nilai = {};
            const teks = {};
            kunci.forEach((k) => {
                nilai[k] = bersihkan(awal[k]);
                teks[k] = format(nilai[k]);
            });

            return {
                nilai,
                teks,
                konfirmasi: false,
                keluar: 0,
                kirim: false,
                get lengkap() {
                    return kunci.every((k) => this.nilai[k] !== '');
                },
                rupiah(n) {
                    return 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
                },
                ubah(k, e) {
                    const el = e.target;
                    const posisi = el.selectionStart ?? el.value.length;
                    // Banyaknya digit di kiri kursor, dipakai untuk mengembalikan kursor setelah diformat ulang
                    const digitSebelum = el.value.slice(0, posisi).replace(/\D/g, '').length;

                    this.nilai[k] = bersihkan(el.value);
                    this.teks[k] = format(this.nilai[k]);

                    let pos = 0, hitung = 0;
                    const tampil = this.teks[k];
                    if (digitSebelum > 0) {
                        for (let i = 0; i < tampil.length; i++) {
                            if (/\d/.test(tampil[i]) && ++hitung === digitSebelum) { pos = i + 1; break; }
                        }
                        if (hitung < digitSebelum) pos = tampil.length;
                    }
                    this.$nextTick(() => { try { el.setSelectionRange(pos, pos); } catch (err) {} });
                },
                tanya(keluar) {
                    if (!this.lengkap) return;
                    this.keluar = keluar;
                    this.konfirmasi = true;
                },
                kirimForm() {
                    this.kirim = true;
                    this.$refs.form.submit();
                },
            };
        }
    </script>
@endsection