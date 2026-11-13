@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Shift Kasir" parentTitle="Transaksi" />

    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    @endphp

    @if ($shift)
        {{-- ======================= SHIFT SEDANG BERJALAN ======================= --}}
        <div x-data="shiftBerjalan(@js($ringkasan), @js(route('shift.ringkasan')), @js($shift->mulai_pada->toIso8601String()))"
             x-init="mulai()" class="space-y-6">

            @if ($lewatHari)
                <div class="rounded-xl border border-amber-200 dark:border-amber-800/40 bg-amber-50 dark:bg-amber-500/10 p-4 text-sm text-amber-800 dark:text-amber-300">
                    Shift ini dimulai pada <strong>{{ $shift->mulai_pada->translatedFormat('l, d M Y H:i') }}</strong> dan belum ditutup.
                    Jika shift sudah selesai, tutup sekarang supaya rekapnya akurat.
                </div>
            @endif

            {{-- Kartu status shift --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center gap-5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1 flex-wrap">
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $shift->labelShift() }} sedang berjalan</h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400">
                                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                                Aktif
                            </span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Kasir</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $shift->nama_kasir }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Tanggal</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $shift->tanggal->translatedFormat('l, d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Mulai</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $shift->mulai_pada->format('H:i') }} WIB</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Durasi</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white" x-text="durasi">-</p>
                            </div>
                        </div>
                        @if ($shift->catatan_mulai)
                            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Catatan awal: {{ $shift->catatan_mulai }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 lg:flex-col xl:flex-row">
                        @can('lihat_transaksi')
                            <a href="{{ route('transaksi.indeks') }}"
                               class="px-5 py-2.5 text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                                Buka Kasir
                            </a>
                        @endcan
                        @can('tambah_shift_kasir')
                            <a href="{{ route('shift.tutup') }}"
                               class="px-5 py-2.5 text-center bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                                Selesai Shift
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Kartu nominal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Uang Laci Awal</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white" x-text="rupiah(data.modal_awal)"></p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Dihitung saat shift dibuka</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-400">Tunai</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white" x-text="rupiah(data.tunai.nominal)"></p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500"><span x-text="data.tunai.jumlah"></span> transaksi</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">QRIS</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white" x-text="rupiah(data.qris.nominal)"></p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500"><span x-text="data.qris.jumlah"></span> transaksi</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">Debit</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white" x-text="rupiah(data.debit.nominal)"></p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500"><span x-text="data.debit.jumlah"></span> transaksi</p>
                </div>
            </div>

            {{-- Rincian --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3 flex-wrap">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Rincian Pembayaran Shift Ini</h3>
                    <div class="flex items-center gap-3">
                        <p class="text-xs text-gray-400 dark:text-gray-500">Update otomatis · terakhir <span x-text="diperbarui"></span></p>
                        <button type="button" @click="perbarui()" :disabled="memuat"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-60 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-xs transition border border-gray-200 dark:border-gray-700">
                            <svg class="w-3.5 h-3.5" :class="memuat && 'animate-spin'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Perbarui
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                                <th class="px-6 py-3 font-semibold">Keterangan</th>
                                <th class="px-6 py-3 font-semibold text-right">Jumlah Transaksi</th>
                                <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            <tr>
                                <td class="px-6 py-3">Uang laci awal</td>
                                <td class="px-6 py-3 text-right text-gray-400">-</td>
                                <td class="px-6 py-3 text-right font-semibold" x-text="rupiah(data.modal_awal)"></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-3">Tunai</td>
                                <td class="px-6 py-3 text-right" x-text="data.tunai.jumlah"></td>
                                <td class="px-6 py-3 text-right font-semibold" x-text="rupiah(data.tunai.nominal)"></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-3">QRIS</td>
                                <td class="px-6 py-3 text-right" x-text="data.qris.jumlah"></td>
                                <td class="px-6 py-3 text-right font-semibold" x-text="rupiah(data.qris.nominal)"></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-3">Debit</td>
                                <td class="px-6 py-3 text-right" x-text="data.debit.jumlah"></td>
                                <td class="px-6 py-3 text-right font-semibold" x-text="rupiah(data.debit.nominal)"></td>
                            </tr>
                            <template x-for="b in data.lainnya.rincian" :key="b.metode">
                                <tr>
                                    <td class="px-6 py-3" x-text="b.metode"></td>
                                    <td class="px-6 py-3 text-right" x-text="b.jumlah"></td>
                                    <td class="px-6 py-3 text-right font-semibold" x-text="rupiah(b.nominal)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50 text-gray-800 dark:text-white">
                            <tr>
                                <td class="px-6 py-3 font-semibold">Total penjualan</td>
                                <td class="px-6 py-3 text-right font-semibold" x-text="data.jumlah_transaksi"></td>
                                <td class="px-6 py-3 text-right font-bold" x-text="rupiah(data.total_penjualan)"></td>
                            </tr>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="px-6 py-4 font-bold" colspan="2">
                                    Uang yang seharusnya ada di laci
                                    <span class="block text-xs font-normal text-gray-400 dark:text-gray-500">Uang laci awal + penjualan tunai. QRIS dan debit tidak masuk laci.</span>
                                </td>
                                <td class="px-6 py-4 text-right text-lg font-extrabold text-amber-700 dark:text-amber-400" x-text="rupiah(data.uang_diharapkan)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- ======================= BELUM ADA SHIFT: FORM BUKA SHIFT ======================= --}}
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-2">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">Mulai Shift Kasir</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Hitung uang yang ada di laci sekarang, lalu masukkan nominalnya.</p>
                    </div>
                </div>

                @can('tambah_shift_kasir')
                    <form method="POST" action="{{ route('shift.mulai') }}" x-data="{ ...inputRupiah(@js(old('modal_awal', ''))), kirim: false }"
                          @submit="if (!mentah) { $event.preventDefault(); return; } kirim = true" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Uang di laci saat ini <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off" x-model="tampil" @input="ubah($event)"
                                       placeholder="0" autofocus
                                       class="h-14 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-12 pr-4 text-xl font-bold focus:border-amber-500 focus:ring-amber-500">
                                <input type="hidden" name="modal_awal" :value="mentah">
                            </div>
                            @error('modal_awal')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror

                            @if ($saranModal)
                                <button type="button" @click="isi(@js((string) round($saranModal->uang_akhir)))"
                                        class="mt-3 inline-flex items-center gap-2 rounded-lg border border-dashed border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-500/10 px-3 py-2 text-xs text-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                                    Pakai {{ $rp($saranModal->uang_akhir) }}
                                    <span class="text-amber-600/80 dark:text-amber-400/80">(sisa laci {{ $saranModal->labelShift() }} oleh {{ $saranModal->nama_kasir }}, {{ $saranModal->selesai_pada->translatedFormat('d M H:i') }})</span>
                                </button>
                            @endif
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan (opsional)</label>
                            <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Contoh: laci dicek bersama kasir sebelumnya"
                                   class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 focus:border-amber-500 focus:ring-amber-500">
                            @error('catatan')
                                <small class="text-red-500">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                            @can('lihat_dashboard')
                                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-center sm:text-left">
                                    Nanti dulu, ke Dashboard
                                </a>
                            @else
                                <span></span>
                            @endcan
                            <button type="submit" :disabled="!mentah || kirim"
                                    class="px-6 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition shadow-sm">
                                Mulai Shift
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Pembayaran di kasir baru bisa diproses setelah shift dimulai.</p>
                    </form>
                @else
                    <div class="mt-6 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800/40 p-4 text-xs text-amber-800 dark:text-amber-300">
                        Akun Anda hanya memiliki hak akses untuk melihat shift kasir (tidak bisa membuka shift).
                    </div>
                @endcan
            </div>

            {{-- Riwayat shift terakhir user ini --}}
            @if ($riwayat->isNotEmpty())
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Shift Terakhir Anda</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="px-6 py-3 font-semibold">Tanggal</th>
                                    <th class="px-6 py-3 font-semibold">Shift</th>
                                    <th class="px-6 py-3 font-semibold text-right">Laci Awal</th>
                                    <th class="px-6 py-3 font-semibold text-right">Laci Akhir</th>
                                    <th class="px-6 py-3 font-semibold text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                                @foreach ($riwayat as $r)
                                    <tr>
                                        <td class="px-6 py-3">{{ $r->tanggal->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-3">{{ $r->labelShift() }} <span class="text-xs text-gray-400">({{ $r->mulai_pada->format('H:i') }}–{{ optional($r->selesai_pada)->format('H:i') }})</span></td>
                                        <td class="px-6 py-3 text-right">{{ $rp($r->modal_awal) }}</td>
                                        <td class="px-6 py-3 text-right">{{ $rp($r->uang_akhir) }}</td>
                                        <td class="px-6 py-3 text-right">@include('pages.shift-kasir.partials.badge-selisih', ['shift' => $r])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <script>
        // Input nominal rupiah: tampil "1.250.000", nilai asli dikirim lewat input hidden.
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

        // Ringkasan shift berjalan: diperbarui otomatis dari server.
        function shiftBerjalan(awal, urlRingkasan, mulaiIso) {
            return {
                data: awal,
                durasi: '-',
                diperbarui: new Date().toLocaleTimeString('id-ID'),
                memuat: false,
                rupiah(n) {
                    return 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
                },
                hitungDurasi() {
                    const selisih = Math.max(0, Math.floor((Date.now() - new Date(mulaiIso).getTime()) / 60000));
                    const jam = Math.floor(selisih / 60);
                    const menit = selisih % 60;
                    this.durasi = jam > 0 ? `${jam} jam ${menit} menit` : `${menit} menit`;
                },
                async perbarui() {
                    if (this.memuat) return;
                    this.memuat = true;
                    try {
                        const res = await fetch(urlRingkasan, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const json = await res.json();
                        if (json.status === 'success') {
                            this.data = json.ringkasan;
                            this.diperbarui = json.diperbarui;
                        } else if (json.status === 'tidak_aktif') {
                            window.location.reload();
                        }
                    } catch (e) { /* abaikan, coba lagi di putaran berikutnya */ }
                    finally { this.memuat = false; }
                },
                mulai() {
                    this.hitungDurasi();
                    setInterval(() => this.hitungDurasi(), 30000);
                    // Cek ulang tiap 5 detik, dan langsung saat tab kembali dibuka / jendela difokuskan
                    setInterval(() => { if (!document.hidden) this.perbarui(); }, 5000);
                    document.addEventListener('visibilitychange', () => { if (!document.hidden) this.perbarui(); });
                    window.addEventListener('focus', () => this.perbarui());
                },
            };
        }
    </script>
@endsection