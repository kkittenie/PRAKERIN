@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Histori Transaksi" />

    <div x-data="historiTransaksi()" class="space-y-6">
        {{-- Header & Filter --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Lihat semua transaksi yang telah selesai.</p>
                </div>
                <form method="GET" action="{{ route('histori-transaksi') }}" class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full md:w-auto">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Kode / Nama pembeli..."
                               class="w-full md:w-56 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 focus:bg-white transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 focus:bg-white transition">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 focus:bg-white transition">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit"
                                class="flex-1 md:flex-initial px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            Cari
                        </button>
                        <a href="{{ route('histori-transaksi') }}"
                           class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-lg text-sm transition whitespace-nowrap">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Transaksi --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Kode Transaksi</th>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Pembeli</th>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Subtotal</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Diskon</th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Total Bayar</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Metode</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600 text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($transaksiList as $trx)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">{{ $trx->kode_transaksi }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $trx->nama_pembeli }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-right text-gray-700">Rp {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-orange-600">Rp {{ number_format($trx->diskon, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $trx->metode_pembayaran === 'tunai' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($trx->metode_pembayaran) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="lihatDetail({{ $trx->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-lg text-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <p class="text-gray-400 font-medium">Belum ada transaksi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($transaksiList->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
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
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Detail Transaksi</h3>
                        <p class="text-xs text-gray-500 font-mono" x-text="detailData?.kode_transaksi"></p>
                    </div>
                    <button @click="modalDetail = false" class="p-2 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-5" x-show="detailData">
                    {{-- Info Pembeli --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Pembeli</p>
                            <p class="font-semibold text-gray-800" x-text="detailData?.nama_pembeli || 'Pelanggan Umum'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Tanggal</p>
                            <p class="font-semibold text-gray-800" x-text="formatTanggal(detailData?.created_at)"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Metode Pembayaran</p>
                            <p class="font-semibold text-gray-800 capitalize" x-text="detailData?.metode_pembayaran"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Selesai
                            </span>
                        </div>
                    </div>

                    {{-- Daftar Item --}}
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 mb-3">Item Pesanan</h4>
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Harga</th>
                                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-for="item in detailData?.detail_transaksi || []" :key="item.id">
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-800" x-text="item.nama_produk"></td>
                                            <td class="px-4 py-3 text-right text-gray-600" x-text="'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></td>
                                            <td class="px-4 py-3 text-center text-gray-600" x-text="item.jumlah"></td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-5 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-800" x-text="'Rp ' + Number(detailData?.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Pajak</span>
                            <span class="font-medium text-gray-800" x-text="'Rp ' + Number(detailData?.pajak || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Diskon</span>
                            <span class="font-medium text-orange-600" x-text="'- Rp ' + Number(detailData?.diskon || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between text-base">
                            <span class="font-bold text-gray-800">Total Bayar</span>
                            <span class="font-bold text-indigo-700 text-lg" x-text="'Rp ' + Number(detailData?.total_bayar || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Diterima</span>
                            <span class="font-medium text-gray-800" x-text="'Rp ' + Number(detailData?.jumlah_diterima || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Kembalian</span>
                            <span class="font-medium text-green-600" x-text="'Rp ' + Number(detailData?.kembalian || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
                {{-- Loading --}}
                <div class="p-12 flex items-center justify-center" x-show="loadingDetail">
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
    }
}
</script>
@endpush
