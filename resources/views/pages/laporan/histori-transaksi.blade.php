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
                        'bg-blue-600': toast.tipe === 'info'
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
                <form method="GET" action="{{ route('histori-transaksi') }}" class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full md:w-auto">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari</label>
                        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Kode / Nama pembeli..."
                               class="w-full md:w-56 px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                               class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                               class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-850 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit"
                                class="flex-1 md:flex-initial px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
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
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Kode Transaksi</th>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Pembeli</th>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Subtotal</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Diskon</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Total Bayar</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Metode</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse ($transaksiList as $trx)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-md">{{ $trx->kode_transaksi }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">{{ $trx->nama_pembeli }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">Rp {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-orange-650 dark:text-orange-400">Rp {{ number_format($trx->diskon, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $trx->metode_pembayaran === 'tunai' ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' }}">
                                    {{ ucfirst($trx->metode_pembayaran) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="lihatDetail({{ $trx->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 font-semibold rounded-lg text-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <p class="text-gray-400 dark:text-gray-500 font-medium">Belum ada transaksi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
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
        <div x-show="modalDetail" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalDetail = false"></div>
            {{-- Content --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/20 dark:to-gray-900">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail Transaksi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="detailData?.kode_transaksi"></p>
                    </div>
                    <button @click="modalDetail = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400 dark:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-5" x-show="detailData">
                    {{-- Info Pembeli --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pembeli</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="detailData?.nama_pembeli || 'Pelanggan Umum'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tanggal</p>
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="formatTanggal(detailData?.created_at)"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Metode Pembayaran</p>
                            <p class="font-semibold text-gray-800 dark:text-white capitalize" x-text="detailData?.metode_pembayaran"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-xl p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 w-fit">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Selesai
                            </span>
                        </div>
                    </div>

                    {{-- Daftar Item --}}
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Item Pesanan</h4>
                        <div class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Produk</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Harga</th>
                                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-850">
                                    <template x-for="item in detailData?.detail_transaksi || []" :key="item.id">
                                        <tr class="text-gray-750 dark:text-gray-300 hover:bg-gray-55 dark:hover:bg-gray-850">
                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white" x-text="item.nama_produk"></td>
                                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></td>
                                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400" x-text="item.jumlah"></td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/20 dark:to-purple-950/20 border border-indigo-50 dark:border-indigo-950/30 rounded-xl p-5 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData?.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Pajak</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData?.pajak || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Diskon</span>
                            <span class="font-medium text-orange-600 dark:text-orange-400" x-text="'- Rp ' + Number(detailData?.diskon || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-800">
                        <div class="flex justify-between text-base">
                            <span class="font-bold text-gray-800 dark:text-white">Total Bayar</span>
                            <span class="font-bold text-indigo-700 dark:text-indigo-400 text-lg" x-text="'Rp ' + Number(detailData?.total_bayar || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Diterima</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData?.jumlah_diterima || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Kembalian</span>
                            <span class="font-medium text-green-600 dark:text-green-400" x-text="'Rp ' + Number(detailData?.kembalian || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl" x-show="detailData">
                    <button @click="cetakStruk('pelanggan')"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Cetak Struk</span>
                    </button>
                    <button @click="cetakStruk('dapur')"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        <span>Order Dapur</span>
                    </button>
                    <button @click="modalDetail = false"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">
                        Tutup
                    </button>
                </div>

                {{-- Loading --}}
                <div class="p-12 flex items-center justify-center bg-white dark:bg-gray-900" x-show="loadingDetail">
                    <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
            const toko = 'Crumb & Co.';
            const alamat = 'Melrose Avenue, Los Angeles';
            let html = '';

            const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val);
            const formatTanggalLocal = (tanggal) => {
                if (!tanggal) return '-';
                const date = new Date(tanggal);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            };

            const subtotal = Number(d.subtotal || 0);
            const diskon = Number(d.diskon || 0);
            const pajak = Number(d.pajak || 0);
            const totalBayar = Number(d.total_bayar || 0);
            const diterima = Number(d.jumlah_diterima || 0);
            const kembalian = Number(d.kembalian || 0);

            const diskonPersen = subtotal > 0 ? Math.round((diskon / subtotal) * 100) : 0;
            const pajakPersen = (subtotal - diskon) > 0 ? Math.round((pajak / (subtotal - diskon)) * 100) : 0;

            if (jenis === 'pelanggan') {
                html = `
                <div style="font-family:'Courier New',monospace;width:300px;margin:auto;padding:20px;font-size:12px;">
                    <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:10px;margin-bottom:10px;">
                        <h2 style="margin:0;font-size:16px;">${toko}</h2>
                        <p style="margin:4px 0 0 0;font-size:11px;">${alamat}</p>
                    </div>
                    <div style="margin-bottom:8px;font-size:11px;">
                        <div>No: <strong>${d.kode_transaksi}</strong></div>
                        <div>Tgl: ${formatTanggalLocal(d.created_at)}</div>
                        <div>Pelanggan: <strong>${d.nama_pembeli || 'Pelanggan Umum'}</strong></div>
                        <div style="text-transform: capitalize;">Bayar: ${d.metode_pembayaran}</div>
                    </div>
                    <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:8px 0;">
                        ${(d.detail_transaksi || []).map((item, i) => `
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                <span>${i+1}. ${item.nama_produk}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;padding-left:16px;color:#555;">
                                <span>${item.jumlah} x ${formatRupiah(item.harga_satuan)}</span>
                                <span>${formatRupiah(item.subtotal)}</span>
                            </div>
                        `).join('')}
                    </div>
                    <div style="padding:8px 0;border-bottom:1px dashed #000;">
                        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>${formatRupiah(subtotal)}</span></div>
                        ${diskon > 0 ? `<div style="display:flex;justify-content:space-between;"><span>Diskon (${diskonPersen}%)</span><span>-${formatRupiah(diskon)}</span></div>` : ''}
                        ${pajak > 0 ? `<div style="display:flex;justify-content:space-between;"><span>Pajak (${pajakPersen}%)</span><span>${formatRupiah(pajak)}</span></div>` : ''}
                        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;margin-top:6px;"><span>TOTAL</span><span>${formatRupiah(totalBayar)}</span></div>
                    </div>
                    <div style="padding:8px 0;border-bottom:1px dashed #000;">
                        <div style="display:flex;justify-content:space-between;"><span>Diterima</span><span>${formatRupiah(diterima)}</span></div>
                        <div style="display:flex;justify-content:space-between;font-weight:bold;"><span>Kembalian</span><span>${formatRupiah(kembalian)}</span></div>
                    </div>
                    <div style="text-align:center;padding-top:12px;font-size:11px;color:#666;">
                        <p style="margin:0;">Terima kasih sudah mampir! 🌷</p>
                        <p style="margin:4px 0 0 0;">Semoga harimu semanis pastry kami
                        ( ˶ˆᗜˆ˵ ) ♡</p>
                    </div>
                </div>`;
            } else {
                const itemsDapur = (d.detail_transaksi || []).filter(item => item.produk?.status_ketersediaan === 'pesan_dulu');
                if (itemsDapur.length === 0) {
                    this.tambahToast('Tidak ada item Pre-Order (Pesan Dulu) untuk dapur.', 'info');
                    return;
                }
                html = `
                <div style="font-family:'Courier New',monospace;width:300px;margin:auto;padding:20px;font-size:12px;">
                    <div style="text-align:center;border-bottom:2px solid #000;padding-bottom:10px;margin-bottom:10px;">
                        <h2 style="margin:0;font-size:18px;letter-spacing:2px;">🍳 ORDER DAPUR</h2>
                    </div>
                    <div style="margin-bottom:10px;font-size:12px;">
                        <div>No: <strong>${d.kode_transaksi}</strong></div>
                        <div>Tgl: ${formatTanggalLocal(d.created_at)}</div>
                        <div>Pelanggan: <strong style="font-size:14px;">${d.nama_pembeli || 'Pelanggan Umum'}</strong></div>
                    </div>
                    <div style="border-top:2px solid #000;border-bottom:2px solid #000;padding:10px 0;">
                        ${itemsDapur.map((item, i) => `
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:14px;">
                                <span><strong>${i+1}. ${item.nama_produk}</strong></span>
                                <span style="background:#000;color:#fff;padding:2px 10px;border-radius:4px;font-weight:bold;font-size:16px;">x${item.jumlah}</span>
                            </div>
                        `).join('')}
                    </div>
                    ${d.catatan ? `<div style="padding:8px 0;border-bottom:1px dashed #000;"><strong>📝 Catatan:</strong> ${d.catatan}</div>` : ''}
                    <div style="text-align:center;padding-top:12px;font-size:11px;color:#666;">
                        <p style="margin:0;">Dicetak: ${formatTanggalLocal(new Date())}</p>
                    </div>
                </div>`;
            }

            const win = window.open('', '_blank', 'width=400,height=700');
            win.document.write(`<html><head><title>Struk ${jenis === 'pelanggan' ? 'Pelanggan' : 'Dapur'}</title></head><body style="margin:0;padding:0;">${html}</body></html>`);
            win.document.close();
            win.focus();
            setTimeout(() => win.print(), 500);
        }
    }
}
</script>
@endpush
