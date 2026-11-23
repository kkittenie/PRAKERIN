@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Rekap Shift" parentTitle="Laporan" />

    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $selisihTotal = (float) $total->selisih;
        $queryExport = request()->query();
        $queryExport['tanggal_mulai'] = $tanggalMulai;
        $queryExport['tanggal_selesai'] = $tanggalSelesai;
        $kasirOpsi = $kasirList->map(fn ($k) => ['id' => $k->user_id, 'nama' => $k->nama_kasir])->values();
    @endphp

    <div class="space-y-6">

        {{-- Filter --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col gap-5">
                {{-- Judul di baris sendiri (selebar kartu), filter di bawahnya melebar ke kanan --}}
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Rekap Shift Kasir</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Uang laci awal &amp; akhir, serta total pembayaran tunai, QRIS, debit, dan Member App di setiap shift.</p>
                </div>
                <form method="GET" action="{{ route('rekap-shift.index') }}"
                      x-data="{ mulai: @js($tanggalMulai), selesai: @js($tanggalSelesai) }"
                      class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full flex-wrap">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" x-model="mulai" @change="if (selesai < mulai) selesai = mulai"
                               class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" x-model="selesai" :min="mulai"
                               class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                        {{-- Ganti role: kosongkan pilihan kasir lalu muat ulang, supaya daftar kasir menyesuaikan --}}
                        <select name="role" @change="$root.elements['kasir'].value = ''; $root.submit()"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                            <option value="">Semua Role</option>
                            @foreach ($roleList as $namaRole)
                                <option value="{{ $namaRole }}" {{ request('role') === $namaRole ? 'selected' : '' }}>{{ ucfirst($namaRole) }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Kasir: dropdown yang bisa dicari (ketik sebagian nama) --}}
                    <div class="flex-1 md:flex-initial relative md:w-56"
                         x-data="{
                             buka: false,
                             cari: '',
                             pilih: @js((string) request('kasir')),
                             daftar: @js($kasirOpsi),
                             get hasil() {
                                 const q = this.cari.toLowerCase().trim();
                                 return q ? this.daftar.filter(k => k.nama.toLowerCase().includes(q)) : this.daftar;
                             },
                             get label() {
                                 const k = this.daftar.find(k => String(k.id) === this.pilih);
                                 return k ? k.nama : 'Semua Kasir';
                             },
                             ambil(id) { this.pilih = id; this.buka = false; this.cari = ''; }
                         }"
                         @click.outside="buka = false" @keydown.escape="buka = false">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kasir</label>
                        <input type="hidden" name="kasir" :value="pilih">
                        <button type="button" @click="buka = !buka; if (buka) $nextTick(() => $refs.cari.focus())"
                                :aria-expanded="buka.toString()"
                                class="daisy-select-trigger">
                            <span class="daisy-select-label">
                                <span x-show="pilih !== ''" class="daisy-select-dot"></span>
                                <span class="truncate" x-text="label"></span>
                            </span>
                            <svg class="daisy-select-chevron" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="buka" x-cloak x-transition.opacity
                             class="absolute left-0 z-40 mt-1.5 w-full min-w-[13rem] bg-[#FFFDF9] dark:bg-[#241A16] border border-[#3C2415]/15 dark:border-white/14 rounded-2xl shadow-[0_18px_40px_-10px_rgba(58,34,27,0.22)] p-1.5">
                            <div class="daisy-select-search-box">
                                <input x-ref="cari" x-model="cari" type="text" placeholder="Cari kasir..."
                                       @keydown.enter.prevent="if (hasil.length) ambil(String(hasil[0].id))"
                                       class="daisy-select-search">
                            </div>
                            <ul class="daisy-select-list custom-scrollbar">
                                <li x-show="!cari">
                                    <button type="button" @click="ambil('')"
                                            :class="pilih === '' ? 'is-selected' : ''"
                                            class="daisy-select-option">
                                        <span class="truncate">Semua Kasir</span>
                                        <svg x-show="pilih === ''" class="daisy-select-option-check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </li>
                                <template x-for="k in hasil" :key="k.id">
                                    <li>
                                        <button type="button" @click="ambil(String(k.id))"
                                                :class="String(k.id) === pilih ? 'is-selected' : ''"
                                                class="daisy-select-option">
                                            <span class="truncate" x-text="k.nama"></span>
                                            <svg x-show="String(k.id) === pilih" class="daisy-select-option-check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </li>
                                </template>
                                <li x-show="cari && hasil.length === 0" class="daisy-select-empty">Tidak ada yang cocok</li>
                            </ul>
                        </div>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                            <option value="">Semua Status</option>
                            <option value="berjalan" {{ request('status') === 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0 md:ml-auto">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Filter</button>
                        <a href="{{ route('rekap-shift.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                        <a href="{{ route('rekap-shift.export', $queryExport) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Ekspor Excel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ringkasan periode (shift selesai) --}}
        <div class="grid grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Shift Selesai</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($total->jumlah_shift, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ number_format($total->transaksi, 0, ',', '.') }} transaksi</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-green-600 dark:text-green-400">Total Tunai</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white">{{ $rp($total->tunai) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total QRIS</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white">{{ $rp($total->qris) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">Total Debit</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white">{{ $rp($total->debit) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">Total Member App</p>
                <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white">{{ $rp($total->member_app) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-5 col-span-2 xl:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Selisih Laci</p>
                <p class="mt-2 text-xl font-bold {{ abs($selisihTotal) < 0.5 ? 'text-green-600 dark:text-green-400' : ($selisihTotal > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                    {{ abs($selisihTotal) < 0.5 ? 'Sesuai' : ($selisihTotal > 0 ? 'Lebih ' : 'Kurang ') . $rp(abs($selisihTotal)) }}
                </p>
            </div>
        </div>
        <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">Kartu ringkasan hanya menghitung shift yang sudah selesai pada rentang tanggal ini. Shift berjalan tetap tampil di tabel.</p>

        {{-- Tabel --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Shift</th>
                            <th class="px-4 py-3 font-semibold">Kasir</th>
                            <th class="px-4 py-3 font-semibold text-right">Laci Awal</th>
                            <th class="px-4 py-3 font-semibold text-right">Tunai</th>
                            <th class="px-4 py-3 font-semibold text-right">QRIS</th>
                            <th class="px-4 py-3 font-semibold text-right">Debit</th>
                            <th class="px-4 py-3 font-semibold text-right">Member App</th>
                            <th class="px-4 py-3 font-semibold text-right">Seharusnya</th>
                            <th class="px-4 py-3 font-semibold text-right">Laci Akhir</th>
                            <th class="px-4 py-3 font-semibold">Selisih</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($daftar as $s)
                            @php $r = $s->ringkasan(); @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $s->tanggal->translatedFormat('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $s->tanggal->translatedFormat('l') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $s->labelShift() }}</p>
                                    <p class="text-xs text-gray-400">{{ optional($s->mulai_pada)->format('H:i') }}–{{ $s->selesai_pada ? $s->selesai_pada->format('H:i') : 'sekarang' }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $s->nama_kasir }}</td>
                                <td class="px-4 py-3 text-right">{{ $rp($s->modal_awal) }}</td>
                                <td class="px-4 py-3 text-right"><p class="font-semibold">{{ $rp($r['tunai']['nominal']) }}</p><p class="text-xs text-gray-400">{{ $r['tunai']['jumlah'] }}x</p></td>
                                <td class="px-4 py-3 text-right"><p class="font-semibold">{{ $rp($r['qris']['nominal']) }}</p><p class="text-xs text-gray-400">{{ $r['qris']['jumlah'] }}x</p></td>
                                <td class="px-4 py-3 text-right"><p class="font-semibold">{{ $rp($r['debit']['nominal']) }}</p><p class="text-xs text-gray-400">{{ $r['debit']['jumlah'] }}x</p></td>
                                <td class="px-4 py-3 text-right"><p class="font-semibold">{{ $rp($r['member_app']['nominal']) }}</p><p class="text-xs text-gray-400">{{ $r['member_app']['jumlah'] }}x</p></td>
                                <td class="px-4 py-3 text-right">{{ $rp($r['uang_diharapkan']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $s->uang_akhir !== null ? $rp($s->uang_akhir) : '-' }}</td>
                                <td class="px-4 py-3">@include('pages.shift-kasir.partials.badge-selisih', ['shift' => $s])</td>
                                <td class="px-4 py-3">
                                    @if ($s->sedangBerjalan())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>Berjalan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Selesai</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('rekap-shift.show', $s) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold rounded-lg text-xs transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">Belum ada shift pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($daftar->hasPages())
                <div class="px-4 py-4 border-t border-gray-100 dark:border-gray-800">{{ $daftar->links() }}</div>
            @endif
        </div>
    </div>
@endsection