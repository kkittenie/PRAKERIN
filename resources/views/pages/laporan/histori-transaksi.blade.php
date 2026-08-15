@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Histori Transaksi" />

    <div x-data="historiTransaksi()" class="space-y-6">
        <!-- Toast Notifikasi -->
        <div class="fixed top-5 right-5 z-99999 flex flex-col gap-2">
            <template x-for="toast in toasts" :key="toast.id">
                <div :class="{
                            'bg-green-600': toast.tipe === 'sukses',
                            'bg-red-600': toast.tipe === 'error',
                            'bg-amber-700': toast.tipe === 'info'
                         }"
                    class="text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3 min-w-[280px] transform transition-all duration-300 animate-slide-in">
                    <span class="font-semibold text-lg" x-text="toast.tipe === 'sukses' ? '✓' : '⚠'"></span>
                    <span class="text-sm" x-text="toast.pesan"></span>
                </div>
            </template>
        </div>

        {{-- Header & Filter --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat semua transaksi yang telah selesai.</p>
                </div>
                <form method="GET" action="{{ route('histori-transaksi') }}" x-data="{
                              tanggalMulai: '{{ request('tanggal_mulai') }}',
                              tanggalSelesai: '{{ request('tanggal_selesai') }}',
                              onMulaiChange() {
                                  if (this.tanggalSelesai && this.tanggalSelesai < this.tanggalMulai) {
                                      this.tanggalSelesai = this.tanggalMulai;
                                  }
                              }
                          }" class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full md:w-auto flex-wrap">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari</label>
                        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Kode / Nama pembeli..."
                            class="w-full md:w-56 px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                            <option value="semua" {{ request('status', 'semua') === 'semua' ? 'selected' : '' }}>Semua Status
                            </option>
                            <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_bayar" {{ request('status') === 'belum_bayar' || request('status') === 'belum_lunas' ? 'selected' : '' }}>Belum Bayar</option>
                        </select>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" x-model="tanggalMulai" @change="onMulaiChange()"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai
                            Tanggal</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggalSelesai" :min="tanggalMulai"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit"
                            class="flex-1 md:flex-initial px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari
                        </button>
                        <a href="{{ route('histori-transaksi') }}"
                            class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700 whitespace-nowrap">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Transaksi --}}
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <!-- Unified Responsive Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-2 py-4 sm:hidden w-8 text-center"></th>
                            <th
                                class="text-left px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Kode Transaksi</th>
                            <th
                                class="text-left px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden sm:table-cell">
                                Pembeli</th>
                            <th
                                class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden lg:table-cell">
                                Tanggal</th>
                            <th
                                class="text-center px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden md:table-cell">
                                Status</th>
                            <th
                                class="text-right px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden lg:table-cell">
                                Subtotal</th>
                            <th
                                class="text-right px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden xl:table-cell">
                                Diskon</th>
                            <th
                                class="text-right px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Total Bayar</th>
                            <th
                                class="text-center px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden md:table-cell">
                                Metode</th>
                            <th
                                class="text-right px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    @forelse ($transaksiList as $trx)
                        <tbody x-data="{ open: false }"
                            class="divide-y divide-gray-50 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition-colors">
                                <td class="px-2 py-3.5 sm:hidden text-center">
                                    <button @click="open = !open" type="button"
                                        class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                        :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                                        <span x-text="open ? '−' : '+'"></span>
                                    </button>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span
                                        class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $trx->kode_transaksi }}</span>
                                    <div class="sm:hidden text-[11px] text-gray-500 font-normal mt-1">{{ $trx->nama_pembeli }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 sm:px-6 py-3.5 font-medium text-gray-800 dark:text-gray-200 hidden sm:table-cell">
                                    {{ $trx->nama_pembeli }}</td>
                                <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                                    {{ $trx->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-3.5 text-center hidden md:table-cell">
                                    @if(strtolower($trx->status_pembayaran) === 'lunas')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Lunas
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-right text-gray-700 dark:text-gray-300 hidden lg:table-cell">Rp
                                    {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                                <td class="px-6 py-3.5 text-right text-orange-650 dark:text-orange-400 hidden xl:table-cell">Rp
                                    {{ number_format($trx->diskon, 0, ',', '.') }}</td>
                                <td class="px-4 sm:px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white">Rp
                                    {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                                <td class="px-6 py-3.5 text-center hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            {{ $trx->metode_pembayaran === 'tunai' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400' }}">
                                        {{ ucfirst($trx->metode_pembayaran) }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right">
                                    <button @click="lihatDetail({{ $trx->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold rounded-lg text-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <!-- Expandable child row -->
                            <tr x-show="open" x-cloak x-transition
                                class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                                <td colspan="10" class="p-3.5">
                                    <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300">
                                        <div><span class="text-gray-400">Pembeli:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ $trx->nama_pembeli }}</span>
                                        </div>
                                        <div><span class="text-gray-400">Tanggal:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ $trx->created_at->format('d M Y, H:i') }}</span>
                                        </div>
                                        <div><span class="text-gray-400">Subtotal:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">Rp
                                                {{ number_format($trx->subtotal, 0, ',', '.') }}</span></div>
                                        <div><span class="text-gray-400">Diskon:</span> <span
                                                class="font-semibold text-orange-600 dark:text-orange-400">Rp
                                                {{ number_format($trx->diskon, 0, ',', '.') }}</span></div>
                                        <div><span class="text-gray-400">Metode:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white capitalize">{{ $trx->metode_pembayaran }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Status:</span>
                                            @if(strtolower($trx->status_pembayaran) === 'lunas')
                                                <span class="font-semibold text-green-600 dark:text-green-400">Lunas</span>
                                            @else
                                                <span class="font-semibold text-red-600 dark:text-red-400">Belum Bayar</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="10" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-16 h-16 text-gray-200 dark:text-gray-700" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-400 dark:text-gray-500 font-medium">Belum ada transaksi.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            {{-- Pagination --}}
            @if($transaksiList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $transaksiList->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Detail Transaksi --}}
        <div x-show="modalDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalDetail = false"></div>
            {{-- Content --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/20 dark:to-gray-900">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail Transaksi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="detailData?.kode_transaksi">
                        </p>
                    </div>
                    <button @click="modalDetail = false"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-5" x-show="detailData">
                    {{-- Info Pembeli --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pembeli</p>
                            <p class="font-semibold text-gray-800 dark:text-white"
                                x-text="detailData?.nama_pembeli || 'Pelanggan Umum'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tanggal</p>
                            <p class="font-semibold text-gray-800 dark:text-white"
                                x-text="formatTanggal(detailData?.created_at)"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Metode Pembayaran</p>
                            <p class="font-semibold text-gray-800 dark:text-white capitalize"
                                x-text="detailData?.metode_pembayaran"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                            <template x-if="detailData?.status_pembayaran?.toLowerCase() === 'lunas'">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 w-fit">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Lunas
                                </span>
                            </template>
                            <template x-if="detailData?.status_pembayaran?.toLowerCase() !== 'lunas'">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 w-fit">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Belum Bayar
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Daftar Item --}}
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Item Pesanan</h4>
                        <div class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                            <!-- Desktop Table View -->
                            <div class="hidden sm:block">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                                            <th
                                                class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                Produk</th>
                                            <th
                                                class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                Harga</th>
                                            <th
                                                class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                Qty</th>
                                            <th
                                                class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                                Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-850">
                                        <template x-for="item in (detailData?.detail_transaksi || []).filter(i => !(i.bundling_id && i.produk_id && Number(i.harga_satuan) === 0 && Number(i.subtotal) === 0))" :key="item.id">
                                            <tr
                                                class="text-gray-750 dark:text-gray-300 hover:bg-gray-55 dark:hover:bg-gray-850">
                                                <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">
                                                    <span x-text="item.nama_produk"></span>
                                                    <div x-show="item.catatan"
                                                        class="text-xxs text-amber-600 dark:text-amber-400 font-medium italic mt-0.5"
                                                        x-text="'Catatan: ' + item.catatan"></div>
                                                </td>
                                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400"
                                                    x-text="'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></td>
                                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400"
                                                    x-text="item.jumlah"></td>
                                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white"
                                                    x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Card View -->
                            <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800 p-3">
                                <template x-for="item in (detailData?.detail_transaksi || []).filter(i => !(i.bundling_id && i.produk_id && Number(i.harga_satuan) === 0 && Number(i.subtotal) === 0))" :key="item.id">
                                    <div class="py-2.5 space-y-1">
                                        <div class="flex justify-between items-start gap-2">
                                            <span class="font-bold text-gray-800 dark:text-white text-xs"
                                                x-text="item.nama_produk"></span>
                                            <span class="font-bold text-amber-700 dark:text-amber-400 text-xs"
                                                x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center text-[11px] text-gray-500 dark:text-gray-400">
                                            <span
                                                x-text="item.jumlah + ' x Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div x-show="item.catatan"
                                            class="text-xxs text-amber-600 dark:text-amber-400 font-medium italic"
                                            x-text="'Catatan: ' + item.catatan"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div
                        class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-50 dark:border-amber-950/30 rounded-xl p-5 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white"
                                x-text="'Rp ' + Number(detailData?.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="detailData?.pajak && Number(detailData?.pajak) > 0">
                            <span class="text-gray-650 dark:text-gray-400">Pajak</span>
                            <span class="font-medium text-gray-800 dark:text-white"
                                x-text="'Rp ' + Number(detailData?.pajak || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Diskon</span>
                            <span class="font-medium text-orange-600 dark:text-orange-400"
                                x-text="'- Rp ' + Number(detailData?.diskon || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-800">
                        <div class="flex justify-between text-base">
                            <span class="font-bold text-gray-800 dark:text-white">Total Bayar</span>
                            <span class="font-bold text-amber-700 dark:text-amber-400 text-lg"
                                x-text="'Rp ' + Number(detailData?.total_bayar || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Diterima</span>
                            <span class="font-medium text-gray-800 dark:text-white"
                                x-text="'Rp ' + Number(detailData?.jumlah_diterima || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Kembalian</span>
                            <span class="font-medium text-green-600 dark:text-green-400"
                                x-text="'Rp ' + Number(detailData?.kembalian || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl"
                    x-show="detailData">
                    <button @click="cetakStruk('pelanggan')"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk</span>
                    </button>
                    <button @click="cetakStruk('dapur')"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Tiket Produksi</span>
                    </button>
                    <button @click="modalDetail = false"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">
                        Tutup
                    </button>
                </div>

                {{-- Loading --}}
                <div class="p-12 flex items-center justify-center bg-white dark:bg-gray-900" x-show="loadingDetail">
                    <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function historiTransaksi() {
            return {
                modalDetail: false,
                detailData: null,
                loadingDetail: false,
                toasts: [],

                tambahToast(pesan, tipe = 'sukses') {
                    const id = Date.now();
                    this.toasts.push({ id, pesan, tipe });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 3000);
                },

                async lihatDetail(id) {
                    this.modalDetail = true;
                    this.loadingDetail = true;
                    this.detailData = null;
                    try {
                        const response = await fetch(`/histori-transaksi/${id}/detail`);
                        const json = await response.json();
                        if (json.status === 'success') {
                            this.detailData = json.data;
                        }
                    } catch (error) {
                        console.error('Gagal memuat detail:', error);
                    } finally {
                        this.loadingDetail = false;
                    }
                },

                formatTanggal(tanggal) {
                    if (!tanggal) return '-';
                    const d = new Date(tanggal);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                },

                cetakStruk(jenis) {
                    if (!this.detailData) return;
                    const d = this.detailData;

                    if (jenis === 'dapur') {
                        const itemsDapur = (d.detail_transaksi || []).filter(item => item.butuh_produksi || item.produk?.status_ketersediaan === 'perlu_diproduksi' || item.produk?.status_ketersediaan === 'pesan_dulu');
                        if (itemsDapur.length === 0) {
                            this.tambahToast('Tidak ada item yang perlu diproduksi oleh dapur.', 'info');
                            return;
                        }
                    }

                    window.open(`/transaksi/cetak/${d.kode_transaksi}?jenis=${jenis}&print=true`, '_blank', 'width=400,height=700');
                }
            }
        }
    </script>
@endpush