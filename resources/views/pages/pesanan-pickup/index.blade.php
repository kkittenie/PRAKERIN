@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pesanan" parentTitle="Transaksi" :parentUrl="route('transaksi.indeks')" />

    <div x-data="pesananPickup()" class="space-y-6">

        {{-- Header & Filter --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Pesanan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pesanan yang masuk dari Member App.</p>
                </div>
                <form id="filterPesananPickup" method="GET" action="{{ route('pesanan-pickup.indeks') }}" x-data="{
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
                            class="w-full md:w-48 px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status Pesanan</label>
                        @php
                            $tabsPesanan = [
                                'semua' => 'Semua',
                                \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
                                \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
                                \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
                                \App\Models\Transaksi::STATUS_DIBATALKAN => 'Dibatalkan',
                            ];
                        @endphp
                        <select name="status_pesanan"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                            @foreach ($tabsPesanan as $value => $label)
                                <option value="{{ $value }}" {{ $statusPesananFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status Pembayaran</label>
                        @php
                            $tabsPembayaran = [
                                'semua' => 'Semua',
                                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                'lunas' => 'Lunas',
                            ];
                        @endphp
                        <select name="status_pembayaran"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                            @foreach ($tabsPembayaran as $value => $label)
                                <option value="{{ $value }}" {{ $statusPembayaranFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" x-model="tanggalMulai" @change="onMulaiChange()"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggalSelesai" :min="tanggalMulai"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit"
                            class="flex-1 md:flex-initial px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm whitespace-nowrap">
                            Cari
                        </button>
                        <a href="{{ route('pesanan-pickup.indeks') }}"
                            class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700 whitespace-nowrap">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Pesanan: dibungkus id supaya bisa disuntik ulang lewat AJAX
             tiap ada siaran real-time, tanpa reload seluruh halaman (lihat
             dengarkanPembaruanRealtime() di bawah). --}}
        <div id="tabel-pesanan-wrapper" class="rounded-2xl transition-shadow duration-700">
            @include('pages.pesanan-pickup._tabel')
        </div>

        <!-- ========== MODAL DETAIL ========== -->
        <div x-show="modalDetail" x-cloak class="fixed inset-0 z-[999999] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalDetail = false"></div>

            {{-- Content --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                {{-- Loading --}}
                <div class="p-12 flex items-center justify-center" x-show="loadingDetail">
                    <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <template x-if="!loadingDetail && detailData">
                    <div class="flex flex-col min-h-0 flex-1">

                        {{-- Header (sticky) --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/20 dark:to-gray-900 shrink-0">
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white truncate">Detail Pesanan</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate" x-text="detailData.kode_transaksi"></p>
                            </div>
                            <button @click="modalDetail = false"
                                class="shrink-0 ml-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400 dark:text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Body (satu-satunya yang scroll) --}}
                        <div class="overflow-y-auto min-h-0 flex-1 p-6 space-y-5">

                            {{-- Info Pesanan: kotak-kotak, sama kayak histori-transaksi --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pembeli</p>
                                    <p class="font-semibold text-gray-800 dark:text-white text-sm truncate" x-text="detailData.nama_pembeli"></p>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate" x-text="detailData.member?.no_hp || '-'"></p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu Order</p>
                                    <p class="font-semibold text-gray-800 dark:text-white text-sm" x-text="formatTanggal(detailData.created_at)"></p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status Pesanan</p>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold w-fit"
                                        :class="{
                                            'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400': detailData.status_pesanan === 'diproses',
                                            'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400': detailData.status_pesanan === 'siap_diambil',
                                            'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400': detailData.status_pesanan === 'sudah_diambil',
                                            'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400': detailData.status_pesanan === 'dibatalkan',
                                        }" x-text="labelStatusPesanan(detailData.status_pesanan)"></span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status Pembayaran</p>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold w-fit"
                                        :class="detailData.status_pembayaran === 'Lunas' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'"
                                        x-text="detailData.status_pembayaran === 'Lunas' ? 'Lunas' : (detailData.bukti_dikonfirmasi_pada ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran')"></span>
                                </div>
                            </div>

                            {{-- Alasan pembatalan --}}
                            <template x-if="detailData.status_pesanan === 'dibatalkan' && detailData.alasan_pembatalan">
                                <div class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-xl p-3">
                                    <span class="font-bold">Alasan dibatalkan:</span> <span x-text="detailData.alasan_pembatalan"></span>
                                </div>
                            </template>

                            {{-- Catatan Pesanan --}}
                            <template x-if="detailData.catatan_pesanan">
                                <div class="text-xs italic text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3">
                                    "<span x-text="detailData.catatan_pesanan"></span>"
                                </div>
                            </template>

                            {{-- Riwayat Waktu --}}
                            <div class="border border-gray-100 dark:border-gray-800 rounded-xl p-3 space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-gray-400">Dipesan</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="formatTanggal(detailData.created_at)"></span></div>
                                <template x-if="detailData.diproses_pada">
                                    <div class="flex justify-between"><span class="text-gray-400">Mulai Diproses</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="formatTanggal(detailData.diproses_pada)"></span></div>
                                </template>
                                <template x-if="detailData.siap_pada">
                                    <div class="flex justify-between"><span class="text-gray-400">Siap Diambil</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="formatTanggal(detailData.siap_pada)"></span></div>
                                </template>
                                <template x-if="detailData.diambil_pada">
                                    <div class="flex justify-between"><span class="text-gray-400">Diserahkan</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="formatTanggal(detailData.diambil_pada)"></span></div>
                                </template>
                            </div>

                            {{-- Bukti Pembayaran: tetap tampil sampai akhir buat referensi kasir --}}
                            <template x-if="detailData.bukti_pembayaran || detailData.status_pembayaran !== 'Lunas'">
                                <div class="border border-gray-100 dark:border-gray-800 rounded-xl p-3 space-y-2">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Bukti Pembayaran QRIS</h4>
                                    <template x-if="detailData.catatan_penolakan && !detailData.bukti_pembayaran">
                                        <p class="text-xs text-red-500 bg-red-50 dark:bg-red-500/10 rounded-lg p-2">
                                            Ditolak sebelumnya: <span x-text="detailData.catatan_penolakan"></span> — menunggu member upload ulang.
                                        </p>
                                    </template>
                                    <template x-if="!detailData.bukti_pembayaran">
                                        <p class="text-xs text-gray-400">Member belum mengupload bukti pembayaran.</p>
                                    </template>

                                    <template x-if="detailData.bukti_pembayaran">
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="previewGambar = '/storage/' + detailData.bukti_pembayaran"
                                                class="relative w-16 h-16 shrink-0 group block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                <img :src="'/storage/' + detailData.bukti_pembayaran" class="w-full h-full object-cover">
                                                <span class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </span>
                                            </button>

                                            <div class="min-w-0 flex-1">
                                                <template x-if="detailData.status_pembayaran === 'Lunas'">
                                                    <p class="text-xs text-green-600 dark:text-green-400">Pembayaran sudah dikonfirmasi.</p>
                                                </template>
                                                <template x-if="detailData.status_pembayaran !== 'Lunas' && !detailData.bukti_dikonfirmasi_pada">
                                                    <p class="text-xs text-amber-600 dark:text-amber-400">Menunggu member mengonfirmasi bukti.</p>
                                                </template>

                                                <template x-if="detailData.status_pembayaran !== 'Lunas' && detailData.bukti_dikonfirmasi_pada">
                                                    <div class="flex flex-wrap gap-2">
                                                        <form :action="'/pesanan-pickup/' + detailData.id + '/konfirmasi-pembayaran'" method="POST">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-xs transition">
                                                                Konfirmasi
                                                            </button>
                                                        </form>
                                                        <button type="button" @click="bukaModalTolak()"
                                                            class="px-3 py-1.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 font-bold rounded-lg text-xs transition">
                                                            Tolak
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Daftar Item: tabel + card mobile, sama kayak histori-transaksi --}}
                            <div>
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Item Pesanan</h4>
                                <div class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                                    {{-- Desktop Table View --}}
                                    <div class="hidden sm:block">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50 dark:bg-gray-800/50">
                                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Produk</th>
                                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap">Harga</th>
                                                    <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                                <template x-for="item in itemPesananUtama()" :key="item.id">
                                                    <tr class="text-gray-700 dark:text-gray-300">
                                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">
                                                            <span x-text="item.nama_produk"></span>
                                                            <template x-if="item.ukuran">
                                                                <span class="ml-1 text-[11px] font-extrabold text-amber-700 dark:text-amber-400" x-text="'(' + item.ukuran + ')'"></span>
                                                            </template>
                                                            <template x-if="item.varian_rasa">
                                                                <span class="ml-1 text-[11px] font-extrabold text-purple-700 dark:text-purple-400" x-text="'Rasa: ' + item.varian_rasa"></span>
                                                            </template>
                                                            <template x-if="item.butuh_produksi">
                                                                <span class="ml-1.5 inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-700 dark:text-orange-400 align-middle">Perlu Diproduksi</span>
                                                            </template>
                                                            <div x-show="item.isi_label" class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                                                <div class="mb-0.5">Isi:</div>
                                                                <template x-for="k in item.isi_komponen" :key="k.nama_produk + (k.ukuran || '') + (k.varian_rasa || '')">
                                                                    <div class="mb-0.5">
                                                                        <div class="font-bold text-gray-700 dark:text-gray-200" x-text="k.nama_produk + (k.ukuran ? ' (' + k.ukuran + ')' : '') + (k.varian_rasa ? ' - ' + k.varian_rasa : '') + ' x' + k.jumlah"></div>
                                                                        <div x-show="k.catatan" class="text-amber-600 dark:text-amber-400 font-medium italic" x-text="'Catatan: ' + k.catatan"></div>
                                                                    </div>
                                                                </template>
                                                                <span x-show="item.komponen_perlu_produksi" class="mt-0.5 inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-700 dark:text-orange-400 align-middle">Perlu Diproduksi</span>
                                                            </div>
                                                            <div x-show="item.catatan" class="text-[11px] text-amber-600 dark:text-amber-400 font-medium italic mt-0.5" x-text="'Catatan: ' + item.catatan"></div>
                                                        </td>
                                                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400 whitespace-nowrap" x-text="'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></td>
                                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400" x-text="item.jumlah"></td>
                                                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white whitespace-nowrap" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Mobile Card View --}}
                                    <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800 p-3">
                                        <template x-for="item in itemPesananUtama()" :key="item.id">
                                            <div class="py-2.5 space-y-1">
                                                <div class="flex justify-between items-start gap-2">
                                                    <div>
                                                        <span class="font-bold text-gray-800 dark:text-white text-xs" x-text="item.nama_produk"></span>
                                                        <template x-if="item.ukuran">
                                                            <span class="ml-1 text-[11px] font-extrabold text-amber-700 dark:text-amber-400" x-text="'(' + item.ukuran + ')'"></span>
                                                        </template>
                                                        <template x-if="item.varian_rasa">
                                                            <span class="ml-1 text-[11px] font-extrabold text-purple-700 dark:text-purple-400" x-text="'Rasa: ' + item.varian_rasa"></span>
                                                        </template>
                                                        <template x-if="item.butuh_produksi">
                                                            <span class="ml-1.5 inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-[9px] font-semibold text-orange-700 dark:text-orange-400 align-middle">Perlu Diproduksi</span>
                                                        </template>
                                                    </div>
                                                    <span class="font-bold text-amber-700 dark:text-amber-400 text-xs shrink-0" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div class="flex justify-between items-center text-[11px] text-gray-500 dark:text-gray-400">
                                                    <span x-text="item.jumlah + ' x Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div x-show="item.isi_label" class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    <div class="mb-0.5">Isi:</div>
                                                    <template x-for="k in item.isi_komponen" :key="k.nama_produk + (k.ukuran || '') + (k.varian_rasa || '')">
                                                        <div class="mb-0.5">
                                                            <div class="font-bold text-gray-700 dark:text-gray-200" x-text="k.nama_produk + (k.ukuran ? ' (' + k.ukuran + ')' : '') + (k.varian_rasa ? ' - ' + k.varian_rasa : '') + ' x' + k.jumlah"></div>
                                                            <div x-show="k.catatan" class="text-amber-600 dark:text-amber-400 font-medium italic" x-text="'Catatan: ' + k.catatan"></div>
                                                        </div>
                                                    </template>
                                                    <span x-show="item.komponen_perlu_produksi" class="mt-0.5 inline-flex rounded-full bg-orange-100 dark:bg-orange-500/10 px-2 py-0.5 text-[9px] font-semibold text-orange-700 dark:text-orange-400 align-middle">Perlu Diproduksi</span>
                                                </div>
                                                <div x-show="item.catatan" class="text-[11px] text-amber-600 dark:text-amber-400 font-medium italic" x-text="'Catatan: ' + item.catatan"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Ringkasan Pembayaran --}}
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-50 dark:border-amber-950/30 rounded-xl p-4 space-y-1.5">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Metode Pembayaran</span>
                                    <span class="font-medium text-gray-800 dark:text-white" x-text="detailData.metode_pembayaran"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                    <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData.subtotal).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between text-sm" x-show="detailData.pajak && Number(detailData.pajak) > 0">
                                    <span class="text-gray-500 dark:text-gray-400">Pajak</span>
                                    <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData.pajak).toLocaleString('id-ID')"></span>
                                </div>
                                <template x-if="Number(detailData.diskon) > 0">
                                    <div>
                                        <template x-if="detailData.diskon_label && (() => { try { return Array.isArray(JSON.parse(detailData.diskon_label)); } catch(e) { return false; } })()">
                                            <template x-for="itemDiskon in JSON.parse(detailData.diskon_label)" :key="itemDiskon.nama">
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400" x-text="itemDiskon.nama.replace(/^(?:Diskon|Voucher)\s*\((.+)\)$/, '$1')"></span>
                                                    <span class="font-medium text-orange-600 dark:text-orange-400" x-text="'- Rp ' + Number(itemDiskon.nilai).toLocaleString('id-ID')"></span>
                                                </div>
                                            </template>
                                        </template>
                                        <template x-if="!detailData.diskon_label || !(() => { try { return Array.isArray(JSON.parse(detailData.diskon_label)); } catch(e) { return true; } })()">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500 dark:text-gray-400" x-text="'Diskon' + (detailData.diskon_label ? ' (' + detailData.diskon_label + ')' : '')"></span>
                                                <span class="font-medium text-orange-600 dark:text-orange-400" x-text="'- Rp ' + Number(detailData.diskon).toLocaleString('id-ID')"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <hr class="border-gray-200 dark:border-gray-800">
                                <div class="flex justify-between text-base">
                                    <span class="font-bold text-gray-800 dark:text-white">Total Bayar</span>
                                    <span class="font-bold text-amber-700 dark:text-amber-400 text-lg" x-text="'Rp ' + Number(detailData.total_bayar).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Diterima</span>
                                    <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData.jumlah_diterima || 0).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Kembalian</span>
                                    <span class="font-medium text-green-600 dark:text-green-400" x-text="'Rp ' + Number(detailData.kembalian || 0).toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            {{-- Poin & Stamp (kalau sudah lunas & dapat) --}}
                            <template x-if="detailData.status_pembayaran === 'Lunas' && (detailData.poin_didapat > 0 || detailData.stamp_didapat > 0)">
                                <div class="flex gap-3 text-xs">
                                    <template x-if="detailData.poin_didapat > 0">
                                        <span class="px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 font-semibold" x-text="'+' + detailData.poin_didapat + ' Poin'"></span>
                                    </template>
                                    <template x-if="detailData.stamp_didapat > 0">
                                        <span class="px-3 py-1.5 rounded-lg bg-pink-50 dark:bg-pink-500/10 text-pink-700 dark:text-pink-400 font-semibold" x-text="'+' + detailData.stamp_didapat + ' Stamp'"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Footer (sticky) --}}
                        <div class="flex flex-wrap items-center justify-end gap-2 px-6 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 shrink-0">

                            {{-- Cetak Struk & Tiket Produksi: muncul mulai status Lunas --}}
                            <template x-if="detailData.status_pembayaran === 'Lunas'">
                                <button @click="cetakStruk('pelanggan')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition">
                                    Cetak Struk
                                </button>
                            </template>
                            <template x-if="detailData.status_pembayaran === 'Lunas' && adaItemProduksi()">
                                <button @click="cetakStruk('dapur')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition">
                                    Tiket Produksi
                                </button>
                            </template>

                            {{-- Proses Pesanan --}}
                            <template x-if="detailData.status_pembayaran === 'Lunas' && !detailData.diproses_pada && detailData.status_pesanan === 'diproses'">
                                <form :action="'/pesanan-pickup/' + detailData.id + '/proses'" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white font-bold rounded-lg text-xs transition">
                                        Proses Pesanan
                                    </button>
                                </form>
                            </template>

                            {{-- Tandai Siap Diambil --}}
                            <template x-if="detailData.diproses_pada && detailData.status_pesanan === 'diproses'">
                                <form :action="'/pesanan-pickup/' + detailData.id + '/siap'" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-lg text-xs transition">
                                        Tandai Siap Diambil
                                    </button>
                                </form>
                            </template>

                            {{-- Tandai Sudah Diambil: wajib verifikasi kode pickup dulu --}}
                            <template x-if="detailData.status_pesanan === 'siap_diambil'">
                                <button type="button" @click="bukaModalSerahkan()"
                                    class="px-4 py-2 bg-green-700 hover:bg-green-800 text-white font-bold rounded-lg text-xs transition">
                                    Tandai Sudah Diambil
                                </button>
                            </template>

                            {{-- Batalkan: selama belum diambil/dibatalkan --}}
                            <template x-if="!['sudah_diambil','dibatalkan'].includes(detailData.status_pesanan)">
                                <button type="button" @click="bukaModalBatal()"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 font-semibold rounded-lg text-xs transition border border-gray-200 dark:border-gray-700">
                                    Batalkan
                                </button>
                            </template>

                            <button @click="modalDetail = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-xs transition border border-gray-200 dark:border-gray-700">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ========== MODAL: KONFIRMASI BATALKAN ========== -->
            <div x-show="modalBatalTerbuka" x-cloak x-transition
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-[999999]">
            <div class="flex items-center justify-center p-4" style="min-height: 100%">
            <div @click.away="modalBatalTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <form :action="detailData ? '/pesanan-pickup/' + detailData.id + '/batal' : '#'" method="POST">
                    @csrf @method('PATCH')
                    <div class="p-6">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ⚠
                        </div>
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base text-center">Batalkan Pesanan?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Pesanan <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="detailData?.kode_transaksi"></span>
                            akan dibatalkan. Stok & voucher yang terpakai akan dikembalikan. Alasan ini akan ditampilkan ke member.
                        </p>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mt-4 mb-1">Alasan pembatalan <span class="text-red-500">*</span></label>
                        <textarea name="alasan" x-model="alasanBatal" rows="3" maxlength="255" required
                            placeholder="Contoh: stok bahan habis"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                        <button type="button" @click="modalBatalTerbuka = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                        <button type="submit" :disabled="!alasanBatal.trim()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">Batalkan Pesanan</button>
                    </div>
                </form>
            </div>
            </div>
        </div>

        <!-- ========== MODAL: ALASAN TOLAK PEMBAYARAN ========== -->
        <div x-show="modalTolakTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-[999999]">
            <div class="flex items-center justify-center p-4" style="min-height: 100%">
            <div @click.away="modalTolakTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <form :action="detailData ? '/pesanan-pickup/' + detailData.id + '/tolak-pembayaran' : '#'" method="POST">
                    @csrf @method('PATCH')
                    <div class="p-6">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ⚠
                        </div>
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base text-center">Tolak Bukti Pembayaran?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Bukti akan dihapus & member diminta upload ulang. Alasan ini akan ditampilkan ke member.
                        </p>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mt-4 mb-1">Alasan penolakan <span class="text-red-500">*</span></label>
                        <textarea name="alasan" x-model="alasanTolak" rows="3" maxlength="255" required
                            placeholder="Contoh: nominal transfer tidak sesuai"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                        <button type="button" @click="modalTolakTerbuka = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                        <button type="submit" :disabled="!alasanTolak.trim()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">Tolak Bukti</button>
                    </div>
                </form>
            </div>
            </div>
        </div>

        <!-- ========== MODAL: VERIFIKASI KODE PICKUP ========== -->
        <div x-show="modalSerahkanTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs overflow-y-auto z-[999999]">
            <div class="flex items-center justify-center p-4" style="min-height: 100%">
            <div @click.away="modalSerahkanTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                {{-- Dikirim lewat fetch() (serahkanPesanan()), BUKAN submit form biasa,
                     supaya kalau kode pickup salah, errornya bisa ditampilkan di dalam
                     modal ini sendiri tanpa bikin modal ketutup / halaman reload. --}}
                <form @submit.prevent="serahkanPesanan()">
                    <div class="p-6">
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center rounded-full mx-auto mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base text-center">Verifikasi Kode Pickup</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Minta pembeli menunjukkan kode pickup dari aplikasi member, lalu masukkan kodenya sebelum menyerahkan pesanan
                            <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="detailData?.kode_transaksi"></span>.
                        </p>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mt-4 mb-1">Kode Pickup</label>
                        <input type="text" name="kode_pickup" x-model="kodePickupInput" maxlength="6" required
                            placeholder="Contoh: A3F9K2" autocomplete="off" x-ref="inputKodePickup"
                            @input="kodePickupInput = kodePickupInput.toUpperCase(); kodePickupError = ''"
                            :class="kodePickupError ? 'border-red-400 dark:border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-gray-700 focus:ring-green-500'"
                            class="w-full px-3 py-2.5 text-center tracking-[0.3em] font-mono font-bold uppercase bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-lg border focus:outline-none focus:ring-2 focus:border-transparent">

                        {{-- Alert error kode salah: tampil DI DALAM modal, modal tetap terbuka --}}
                        <div x-show="kodePickupError" x-cloak x-transition
                            class="mt-3 flex items-start gap-2 px-3 py-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-medium">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span x-text="kodePickupError"></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                        <button type="button" @click="modalSerahkanTerbuka = false" :disabled="prosesSerahkan"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300 disabled:opacity-50">Batal</button>
                        <button type="submit" :disabled="prosesSerahkan"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-lg transition shadow-xs disabled:opacity-60 disabled:cursor-not-allowed"
                            x-text="prosesSerahkan ? 'Memproses...' : 'Serahkan Pesanan'"></button>
                    </div>
                </form>
            </div>
            </div>
        </div>

        <!-- ========== MODAL: PREVIEW GAMBAR FULL ========== -->
        <div x-show="previewGambar" x-cloak x-transition class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/80"
            @click="previewGambar = null">
            <button type="button" @click="previewGambar = null"
                class="absolute top-4 right-4 p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="previewGambar" @click.stop class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
        </div>

        <!-- ========== TOAST SUKSES (khusus aksi via fetch, mis. serahkan pesanan) ========== -->
        <div x-show="toastSukses" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0" class="fixed top-5 right-5 z-[999999]">
            <div class="text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3 min-w-[280px] bg-green-600">
                <span class="font-semibold text-lg">✓</span>
                <span class="text-sm" x-text="toastSukses"></span>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function pesananPickup() {
            return {
                modalDetail: false,
                detailData: null,
                loadingDetail: false,
                modalBatalTerbuka: false,
                modalTolakTerbuka: false,
                modalSerahkanTerbuka: false,
                alasanTolak: '',
                alasanBatal: '',
                kodePickupInput: '',
                kodePickupError: '',
                prosesSerahkan: false,
                previewGambar: null,
                toastSukses: '',

                init() {
                    this.dengarkanPembaruanRealtime();

                    // Datang dari link notifikasi (?buka={id}) -> langsung
                    // buka modal detail pesanan itu, terus bersihin query-nya
                    // dari address bar biar rapi & gak ke-refresh ulang.
                    const params = new URLSearchParams(window.location.search);
                    const idDibuka = params.get('buka');
                    if (idDibuka) {
                        this.lihatDetail(idDibuka);
                        params.delete('buka');
                        const query = params.toString();
                        window.history.replaceState({}, '', window.location.pathname + (query ? `?${query}` : ''));
                    }
                },

                dengarkanPembaruanRealtime() {
                    if (! window.Echo) return;

                    window.Echo.private('pesanan-pickup')
                        .listen('.pesanan.updated', () => {
                            clearTimeout(this._muatUlangTimer);
                            this._muatUlangTimer = setTimeout(() => this.muatUlangTabel(), 400);
                        });
                },

                muatUlangTabel() {
                    // Tabelnya sekarang server-side (Yajra DataTables), jadi
                    // cukup suruh DataTable ambil ulang datanya lewat ajax,
                    // nggak perlu fetch & ganti seluruh HTML tabel lagi.
                    const wrapper = document.getElementById('tabel-pesanan-wrapper');
                    if (typeof window.reloadPesananPickupTable === 'function') {
                        window.reloadPesananPickupTable();
                    }

                    if (wrapper) {
                        wrapper.classList.add('ring-2', 'ring-amber-400');
                        setTimeout(() => wrapper.classList.remove('ring-2', 'ring-amber-400'), 900);
                    }
                },

                async lihatDetail(id) {
                    this.modalDetail = true;
                    this.loadingDetail = true;
                    this.detailData = null;
                    try {
                        const response = await fetch(`/pesanan-pickup/${id}/detail`);
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

                bukaModalTolak() {
                    this.alasanTolak = '';
                    this.modalTolakTerbuka = true;
                },

                bukaModalBatal() {
                    this.alasanBatal = '';
                    this.modalBatalTerbuka = true;
                },

                bukaModalSerahkan() {
                    this.kodePickupInput = '';
                    this.kodePickupError = '';
                    this.prosesSerahkan = false;
                    this.modalSerahkanTerbuka = true;
                    this.$nextTick(() => this.$refs.inputKodePickup?.focus());
                },

                async serahkanPesanan() {
                    if (! this.detailData || this.prosesSerahkan) return;

                    this.kodePickupError = '';
                    this.prosesSerahkan = true;
                    const kode = this.kodePickupInput.trim().toUpperCase();
                    this.kodePickupInput = kode;

                    try {
                        const response = await fetch(`/pesanan-pickup/${this.detailData.id}/selesai`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({ kode_pickup: kode }),
                        });

                        const json = await response.json().catch(() => null);

                        if (! response.ok) {
                            this.kodePickupError = json?.message || 'Gagal memverifikasi kode pickup.';
                            this.$nextTick(() => this.$refs.inputKodePickup?.focus());
                            return;
                        }

                        this.modalSerahkanTerbuka = false;
                        this.modalDetail = false;
                        this.detailData = null;
                        this.muatUlangTabel();
                        this.tampilkanToast(json?.message || 'Pesanan berhasil diserahkan.');
                    } catch (error) {
                        console.error('Gagal menyerahkan pesanan:', error);
                        this.kodePickupError = 'Terjadi kesalahan koneksi, coba lagi.';
                    } finally {
                        this.prosesSerahkan = false;
                    }
                },

                itemPesananUtama() {
                    // Baris "pecahan" produksi (produk penyusun bundling/promo event,
                    // harga_satuan 0) cuma dipakai internal buat tracking stok — jangan
                    // ditampilkan sebagai baris terpisah di modal, bikin bingung kasir.
                    // Sebagai gantinya, gabung jadi keterangan "Isi: ..." di baris utamanya.
                    const semua = this.detailData?.detail_transaksi || [];

                    const isKomponen = (item) => !!item.produk_id && (!!item.bundling_id || !!item.promo_event_id);

                    const komponenUntuk = (parent) => {
                        const cocok = semua.filter(item => {
                            if (!isKomponen(item)) return false;
                            if (parent.bundling_id) return item.bundling_id === parent.bundling_id;
                            if (parent.promo_event_id) return item.promo_event_id === parent.promo_event_id;
                            return false;
                        });

                        // Satu produk komponen bisa kepecah jadi beberapa baris
                        // (mis. sebagian sudah ready, sisanya perlu diproduksi) —
                        // gabung jadi satu per nama produk + ukuran biar tidak dobel
                        // (dan biar Jumbo/Reguler dari produk yang sama tetap kepisah).
                        const gabung = new Map();
                        for (const k of cocok) {
                            const kunci = k.nama_produk + '|' + (k.ukuran || '') + '|' + (k.varian_rasa || '');
                            const existing = gabung.get(kunci);
                            if (existing) {
                                existing.jumlah += k.jumlah;
                                existing.butuh_produksi = existing.butuh_produksi || k.butuh_produksi;
                                existing.catatan = existing.catatan || k.catatan || null;
                            } else {
                                gabung.set(kunci, { nama_produk: k.nama_produk, ukuran: k.ukuran || null, varian_rasa: k.varian_rasa || null, jumlah: k.jumlah, butuh_produksi: k.butuh_produksi, catatan: k.catatan || null });
                            }
                        }
                        return Array.from(gabung.values());
                    };

                    return semua
                        .filter(item => !isKomponen(item))
                        .map(item => {
                            const komponen = (item.bundling_id || item.promo_event_id) ? komponenUntuk(item) : [];
                            const isiLabel = komponen
                                .map(k => `${k.nama_produk}${k.ukuran ? ' (' + k.ukuran + ')' : ''}${k.varian_rasa ? ' - ' + k.varian_rasa : ''} x${k.jumlah}`)
                                .join(', ');
                            const perluProduksi = komponen.some(k => k.butuh_produksi);
                            return { ...item, isi_label: isiLabel, isi_komponen: komponen, komponen_perlu_produksi: perluProduksi };
                        });
                },

                adaItemProduksi() {
                    return !! (this.detailData?.detail_transaksi || []).some(d => d.butuh_produksi);
                },

                formatTanggal(tanggal) {
                    if (!tanggal) return '-';
                    return new Date(tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                },

                labelStatusPesanan(status) {
                    return {
                        diproses: 'Diproses',
                        siap_diambil: 'Siap Diambil',
                        sudah_diambil: 'Sudah Diambil',
                        dibatalkan: 'Dibatalkan',
                    }[status] || '-';
                },

                cetakStruk(jenis) {
                    if (!this.detailData) return;
                    window.open(`/transaksi/cetak/${this.detailData.kode_transaksi}?jenis=${jenis}&print=true`, '_blank', 'width=400,height=700');
                },

                tampilkanToast(pesan) {
                    this.toastSukses = pesan;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => this.toastSukses = '', 3000);
                }
            }
        }
    </script>
@endpush