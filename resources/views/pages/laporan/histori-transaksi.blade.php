@extends('layouts.app')

@section('content')
    <style>
        /* Default (Desktop lebar / tabel tidak collapsed): sembunyikan badge metode di dalam kolom status */
        .badge-metode-responsive {
            display: none;
        }

        /* Saat tabel collapsed (mode responsif tab/mobile saat tombol + detail muncul) */
        #historiTransaksiTable.collapsed .badge-metode-responsive {
            display: inline-flex !important;
        }

        /* Di layar tab & mobile (< 1024px) */
        @media (max-width: 1023px) {
            .badge-metode-responsive {
                display: inline-flex !important;
            }
        }

        /* Di layar desktop (>= 1024px): pastikan sembunyi jika semua kolom tampil (tidak collapsed) */
        @media (min-width: 1024px) {
            #historiTransaksiTable:not(.collapsed) .badge-metode-responsive {
                display: none !important;
            }
        }

        /* Keluarkan / sembunyikan item Metode dari child row detail (+) */
        #historiTransaksiTable tbody tr.child ul.dtr-details > li[data-dtr-index="7"],
        #historiTransaksiTable tbody tr.child ul.dtr-details > li[data-dt-column="7"] {
            display: none !important;
        }
    </style>

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

        {{-- Filter Card --}}
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
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                            <option value="semua" {{ request('status', 'semua') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_bayar" {{ request('status') === 'belum_bayar' || request('status') === 'belum_lunas' ? 'selected' : '' }}>Belum Bayar</option>
                        </select>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" x-model="tanggalMulai" @change="onMulaiChange()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggalSelesai" :min="tanggalMulai" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>
                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">Filter</button>
                        <a href="{{ route('histori-transaksi') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- DataTables Card --}}
        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari histori transaksi...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="15">15</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="historiTransaksiTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">Kode Transaksi</th>
                            <th class="py-3">Pembeli</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-right">Subtotal</th>
                            <th class="py-3 text-right">Diskon</th>
                            <th class="py-3 text-right">Total Bayar</th>
                            <th class="py-3 text-center desktop">Metode</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="sdt-footer">
                <div class="sdt-info"></div>
                <div class="sdt-pagination"></div>
            </div>
        </div>

        {{-- Modal Detail Transaksi --}}
        <div x-show="modalDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalDetail = false"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/20 dark:to-gray-900">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail Transaksi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="detailData?.kode_transaksi"></p>
                    </div>
                    <button @click="modalDetail = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6 space-y-5" x-show="detailData">
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
                            <template x-if="detailData?.status_pembayaran?.toLowerCase() === 'lunas'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 w-fit">Lunas</span>
                            </template>
                            <template x-if="detailData?.status_pembayaran?.toLowerCase() !== 'lunas'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 w-fit">Belum Bayar</span>
                            </template>
                        </div>
                    </div>

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
                                    <template x-for="item in (detailData?.detail_transaksi || []).filter(i => !(i.bundling_id && i.produk_id && Number(i.harga_satuan) === 0 && Number(i.subtotal) === 0))" :key="item.id">
                                        <tr class="text-gray-750 dark:text-gray-300">
                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">
                                                <span x-text="item.nama_produk"></span>
                                                <div x-show="item.catatan" class="text-xxs text-amber-600 dark:text-amber-400 font-medium italic mt-0.5" x-text="'Catatan: ' + item.catatan"></div>
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID')"></td>
                                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400" x-text="item.jumlah"></td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-50 dark:border-amber-950/30 rounded-xl p-5 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-650 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="'Rp ' + Number(detailData?.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="detailData?.pajak && Number(detailData?.pajak) > 0">
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
                            <span class="font-bold text-amber-700 dark:text-amber-400 text-lg" x-text="'Rp ' + Number(detailData?.total_bayar || 0).toLocaleString('id-ID')"></span>
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

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl" x-show="detailData">
                    <button @click="cetakStruk('pelanggan')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <span>Cetak Struk</span>
                    </button>
                    {{-- Tiket Produksi: di-hide (logikanya tetap ada) --}}
                    @if (\App\Support\StrukHelper::TAMPILKAN_TIKET_PRODUKSI)
                    <button @click="cetakStruk('dapur')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <span>Tiket Produksi</span>
                    </button>
                    @endif
                    <button @click="cetakStruk('etalase')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                        <span>Tiket Dapur</span>
                    </button>
                    <button @click="modalDetail = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-sm border border-gray-200 dark:border-gray-700">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            initSaishoDataTable('historiTransaksiTable', {
                serverSide: true,
                ajax: @json(route('histori-transaksi', request()->query())),
                order: [[2, 'desc']],
                columnDefs: [
                    { responsivePriority: 2, targets: 3 }, // Status tetap prioritas tinggi di tabel saat mode responsif
                    { className: 'desktop', targets: 7 }   // Metode hanya tampil terpisah sebagai kolom di desktop
                ],
                columns: [
                    { data: 'kode_transaksi_html', name: 'kode_transaksi' },
                    { data: 'nama_pembeli', name: 'nama_pembeli', className: 'font-medium text-gray-800 dark:text-gray-200' },
                    { data: 'created_at', name: 'created_at', className: 'text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap' },
                    { data: 'status_html', name: 'status_html', className: 'text-center' },
                    { data: 'subtotal', name: 'subtotal', className: 'text-right text-gray-700 dark:text-gray-300' },
                    { data: 'diskon', name: 'diskon', className: 'text-right text-orange-600 dark:text-orange-400' },
                    { data: 'total_bayar', name: 'total_bayar', className: 'text-right font-bold text-gray-900 dark:text-white' },
                    { data: 'metode_html', name: 'metode_html', className: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                emptyText: 'Belum ada histori transaksi',
                dtOptions: {
                    pageLength: 15,
                    responsive: {
                        details: {
                            renderer: function(api, rowIdx, columns) {
                                // Keluarkan kolom Metode (index 7) dari child row / detail (+)
                                var filtered = $.grep(columns, function(col) {
                                    return col.columnIndex !== 7;
                                });
                                var hasHidden = $.grep(filtered, function(col) {
                                    return col.hidden;
                                }).length > 0;

                                if (!hasHidden) return false;

                                return $.fn.dataTable.Responsive.renderer.listHidden()(api, rowIdx, filtered);
                            }
                        }
                    }
                }
            });
        });

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