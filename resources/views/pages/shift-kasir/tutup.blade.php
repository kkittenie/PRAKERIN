@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Selesai Shift" parentTitle="Shift Kasir" parentUrl="{{ route('shift.index') }}" />

    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    @endphp

    <div x-data="{ ...inputRupiah(@js(old('uang_akhir', ''))), diharapkan: @js((float) $ringkasan['uang_diharapkan']), konfirmasi: false, keluar: 0, kirim: false,
                   get selisih() { return this.mentah === '' ? null : Number(this.mentah) - this.diharapkan; },
                   rupiah(n) { return 'Rp ' + Math.round(Math.abs(Number(n) || 0)).toLocaleString('id-ID'); },
                   get teksSelisih() {
                       if (this.selisih === null) return '-';
                       if (Math.abs(this.selisih) < 0.5) return 'Sesuai';
                       return (this.selisih > 0 ? 'Lebih ' : 'Kurang ') + this.rupiah(this.selisih);
                   },
                   get warnaSelisih() {
                       if (this.selisih === null) return 'text-gray-400';
                       if (Math.abs(this.selisih) < 0.5) return 'text-green-600 dark:text-green-400';
                       return this.selisih > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400';
                   },
                   tanya(keluar) { if (!this.mentah) return; this.keluar = keluar; this.konfirmasi = true; },
                   kirimForm() { this.kirim = true; this.$refs.form.submit(); } }"
         class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- Ringkasan shift --}}
        <div class="xl:col-span-3 bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Ringkasan {{ $shift->labelShift() }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $shift->nama_kasir }} · {{ $shift->mulai_pada->translatedFormat('l, d M Y') }} · {{ $shift->mulai_pada->format('H:i') }} – {{ now()->format('H:i') }} WIB
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-3 font-semibold">Keterangan</th>
                            <th class="px-6 py-3 font-semibold text-right">Transaksi</th>
                            <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr>
                            <td class="px-6 py-3">Uang laci awal</td>
                            <td class="px-6 py-3 text-right text-gray-400">-</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['modal_awal']) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3">Tunai</td>
                            <td class="px-6 py-3 text-right">{{ $ringkasan['tunai']['jumlah'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['tunai']['nominal']) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3">QRIS</td>
                            <td class="px-6 py-3 text-right">{{ $ringkasan['qris']['jumlah'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['qris']['nominal']) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3">Debit</td>
                            <td class="px-6 py-3 text-right">{{ $ringkasan['debit']['jumlah'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['debit']['nominal']) }}</td>
                        </tr>
                        @if ($ringkasan['member_app']['jumlah'] > 0)
                            <tr>
                                <td class="px-6 py-3">Member App</td>
                                <td class="px-6 py-3 text-right">{{ $ringkasan['member_app']['jumlah'] }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ $rp($ringkasan['member_app']['nominal']) }}</td>
                            </tr>
                            @foreach ($ringkasan['member_app']['rincian'] as $b)
                                <tr class="text-xs text-gray-500 dark:text-gray-400">
                                    <td class="px-6 py-2 pl-10">{{ $b['metode'] }}</td>
                                    <td class="px-6 py-2 text-right">{{ $b['jumlah'] }}</td>
                                    <td class="px-6 py-2 text-right">{{ $rp($b['nominal']) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50 text-gray-800 dark:text-white">
                        <tr>
                            <td class="px-6 py-3 font-semibold">Total penjualan</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ $ringkasan['jumlah_transaksi'] }}</td>
                            <td class="px-6 py-3 text-right font-bold">{{ $rp($ringkasan['total_penjualan']) }}</td>
                        </tr>
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-6 py-4 font-bold" colspan="2">
                                Uang yang seharusnya ada di laci
                                <span class="block text-xs font-normal text-gray-400 dark:text-gray-500">Uang laci awal + penjualan tunai. QRIS, debit, dan Member App tidak masuk laci.</span>
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-extrabold text-amber-700 dark:text-amber-400">{{ $rp($ringkasan['uang_diharapkan']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Form uang laci akhir --}}
        <div class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 h-fit">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Hitung Uang di Laci</h2>
            <p class="mt-1 mb-5 text-sm text-gray-500 dark:text-gray-400">Hitung semua uang tunai yang ada di laci sekarang, lalu masukkan nominalnya.</p>

            <form x-ref="form" method="POST" action="{{ route('shift.tutup.simpan') }}" @submit.prevent="tanya(keluar)" class="space-y-5">
                @csrf
                <input type="hidden" name="keluar" :value="keluar">

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sisa uang di laci <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                        <input type="text" inputmode="numeric" autocomplete="off" x-model="tampil" @input="ubah($event)" placeholder="0" autofocus
                               class="h-14 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 text-xl font-bold focus:border-amber-500 focus:ring-amber-500">
                        <input type="hidden" name="uang_akhir" :value="mentah">
                    </div>
                    @error('uang_akhir')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Selisih langsung --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Seharusnya di laci</span>
                        <span class="font-semibold">{{ $rp($ringkasan['uang_diharapkan']) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Selisih</span>
                        <span class="font-bold" :class="warnaSelisih" x-text="teksSelisih"></span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan (opsional)</label>
                    <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Contoh: kurang karena uang kembalian salah"
                           class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 focus:border-amber-500 focus:ring-amber-500">
                    @error('catatan')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 pt-1">
                    <button type="button" @click="tanya(1)" :disabled="!mentah || kirim"
                            class="w-full px-5 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        Selesai Shift &amp; Keluar
                    </button>
                    <button type="button" @click="tanya(0)" :disabled="!mentah || kirim"
                            class="w-full px-5 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        Selesai Shift (tetap login)
                    </button>
                    <p x-show="!mentah" class="text-center text-xs text-amber-600 dark:text-amber-400">
                        Isi sisa uang di laci dulu supaya tombol di atas aktif. Shift wajib ditutup sebelum keluar.
                    </p>
                    <a href="{{ route('shift.index') }}"
                       class="w-full px-5 py-3 text-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">
                        Batal, kembali ke shift
                    </a>
                </div>
            </form>
        </div>

        {{-- Dialog konfirmasi --}}
        <div x-show="konfirmasi" x-cloak x-transition.opacity
             class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="konfirmasi = false">
            <div @click.away="konfirmasi = false" class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 shadow-xl border border-gray-100 dark:border-gray-800 p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Selesaikan shift sekarang?</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Setelah ditutup, data shift tidak bisa diubah lagi.</p>

                <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Seharusnya di laci</span><span class="font-semibold">{{ $rp($ringkasan['uang_diharapkan']) }}</span></div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Sisa uang di laci (input)</span><span class="font-semibold" x-text="'Rp ' + Number(mentah || 0).toLocaleString('id-ID')"></span></div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300"><span>Selisih</span><span class="font-bold" :class="warnaSelisih" x-text="teksSelisih"></span></div>
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
        function inputRupiah(awal = '') {
            const bersih = String(awal || '').replace(/\D/g, '');
            return {
                mentah: bersih,
                tampil: bersih ? Number(bersih).toLocaleString('id-ID') : '',
                isi(nilai) {
                    this.mentah = String(nilai).replace(/\D/g, '');
                    this.tampil = this.mentah ? Number(this.mentah).toLocaleString('id-ID') : '';
                },
                ubah(e) {
                    const el = e.target;
                    const posisi = el.selectionStart ?? el.value.length;
                    // Banyaknya digit di kiri kursor, dipakai untuk mengembalikan kursor setelah diformat ulang
                    const digitSebelum = el.value.slice(0, posisi).replace(/\D/g, '').length;

                    this.isi(el.value.replace(/\D/g, '').slice(0, 12));

                    let pos = 0, hitung = 0;
                    if (digitSebelum > 0) {
                        for (let i = 0; i < this.tampil.length; i++) {
                            if (/\d/.test(this.tampil[i]) && ++hitung === digitSebelum) { pos = i + 1; break; }
                        }
                        if (hitung < digitSebelum) pos = this.tampil.length;
                    }
                    this.$nextTick(() => { try { el.setSelectionRange(pos, pos); } catch (err) {} });
                },
            };
        }
    </script>
@endsection