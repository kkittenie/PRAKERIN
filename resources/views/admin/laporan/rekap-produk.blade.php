@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Rekap Produk" />

    <div x-data="rekapProduk()" class="space-y-6">
        {{-- Filter Card (persis seperti Histori Transaksi) --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4 flex-wrap">
                <div class="min-w-[220px] flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Rekap Produk</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat rekapitulasi produksi dan penjualan per produk.</p>
                </div>
                <form method="GET" action="{{ route('rekap-produk') }}" x-data="{
                              tanggalMulai: '{{ request('tanggal_mulai') }}',
                              tanggalSelesai: '{{ request('tanggal_selesai') }}',
                              onMulaiChange() {
                                  if (this.tanggalSelesai && this.tanggalSelesai < this.tanggalMulai) {
                                      this.tanggalSelesai = this.tanggalMulai;
                                  }
                              }
                          }" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2.5 w-full xl:w-auto flex-wrap min-w-0">
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kategori</label>
                        <select name="kategori_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ (string)$kategoriId === (string)$kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Aktivitas</label>
                        <select name="status_aktivitas" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                            <option value="semua" {{ $statusAktivitas === 'semua' ? 'selected' : '' }}>Semua Produk</option>
                            <option value="ada_aktivitas" {{ $statusAktivitas === 'ada_aktivitas' ? 'selected' : '' }}>Ada Produksi / Terjual</option>
                            <option value="ada_produksi" {{ $statusAktivitas === 'ada_produksi' ? 'selected' : '' }}>Hanya Diproduksi</option>
                            <option value="ada_penjualan" {{ $statusAktivitas === 'ada_penjualan' ? 'selected' : '' }}>Hanya Terjual</option>
                            <option value="tidak_ada_aktivitas" {{ $statusAktivitas === 'tidak_ada_aktivitas' ? 'selected' : '' }}>Tidak Ada Aktivitas</option>
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
                        <a href="{{ route('rekap-produk') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- DataTables Card (persis seperti Histori Transaksi) --}}
        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari rekap produk...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="15">15</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="rekapProdukTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">Produk</th>
                            <th class="py-3 text-center">Kategori</th>
                            <th class="py-3 text-center">Diproduksi</th>
                            <th class="py-3 text-center">Terjual</th>
                            <th class="py-3 text-center">Selisih</th>
                            <th class="py-3 text-center">Stok</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Aksi</th>
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

        {{-- Modal Detail Rincian Varian --}}
        <div x-show="modalVarian" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalVarian = false"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/20 dark:to-gray-900">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Rincian Varian Produk</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="detailVarianData?.nama_produk"></p>
                    </div>
                    <button @click="modalVarian = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto p-6" x-show="detailVarianData">
                    <div class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                    <th class="py-2.5 px-4 text-left font-semibold text-gray-600 dark:text-gray-300">Varian / Ukuran</th>
                                    <th class="py-2.5 px-3 text-center font-semibold text-gray-600 dark:text-gray-300">SKU</th>
                                    <th class="py-2.5 px-3 text-center font-semibold text-gray-600 dark:text-gray-300">Diproduksi</th>
                                    <th class="py-2.5 px-3 text-center font-semibold text-gray-600 dark:text-gray-300">Terjual</th>
                                    <th class="py-2.5 px-3 text-center font-semibold text-gray-600 dark:text-gray-300">Selisih</th>
                                    <th class="py-2.5 px-3 text-center font-semibold text-gray-600 dark:text-gray-300">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                <template x-for="v in (detailVarianData?.varians || [])" :key="v.id">
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-2.5 px-4 font-semibold text-gray-800 dark:text-gray-200" x-text="v.nama_varian"></td>
                                        <td class="py-2.5 px-3 text-center font-mono text-gray-500" x-text="v.kode_produk"></td>
                                        <td class="py-2.5 px-3 text-center">
                                            <template x-if="v.total_produksi > 0">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400" x-text="v.total_produksi + ' produksi'"></span>
                                            </template>
                                            <template x-if="v.total_produksi <= 0">
                                                <span class="text-gray-400">0</span>
                                            </template>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <template x-if="v.total_terjual > 0">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400" x-text="v.total_terjual + ' terjual'"></span>
                                            </template>
                                            <template x-if="v.total_terjual <= 0">
                                                <span class="text-gray-400">0</span>
                                            </template>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-bold">
                                            <template x-if="v.total_produksi === 0 && v.total_terjual === 0">
                                                <span class="text-gray-400 font-normal">-</span>
                                            </template>
                                            <template x-if="v.total_produksi !== 0 || v.total_terjual !== 0">
                                                <span :class="v.selisih > 0 ? 'text-blue-600 dark:text-blue-400' : (v.selisih < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500')"
                                                      x-text="(v.selisih > 0 ? '+' : '') + v.selisih"></span>
                                            </template>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-semibold text-gray-700 dark:text-gray-300" x-text="v.stok + ' pcs'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end px-6 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
                    <button type="button" @click="modalVarian = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-sm border border-gray-200 dark:border-gray-700 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            initSaishoDataTable('rekapProdukTable', {
                serverSide: true,
                ajax: @json(route('rekap-produk', request()->query())),
                order: [[0, 'asc']],
                columns: [
                    {
                        data: 'nama_produk',
                        name: 'nama_produk',
                        render: function(data, type, row) {
                            return type === 'display' ? row.produk_html : data;
                        }
                    },
                    {
                        data: 'nama_kategori',
                        name: 'nama_kategori',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return type === 'display' ? row.kategori_html : data;
                        }
                    },
                    {
                        data: 'total_produksi',
                        name: 'total_produksi',
                        className: 'text-center whitespace-nowrap',
                        render: function(data, type, row) {
                            return type === 'display' ? row.produksi_html : data;
                        }
                    },
                    {
                        data: 'total_terjual',
                        name: 'total_terjual',
                        className: 'text-center whitespace-nowrap',
                        render: function(data, type, row) {
                            return type === 'display' ? row.terjual_html : data;
                        }
                    },
                    {
                        data: 'selisih',
                        name: 'selisih',
                        className: 'text-center whitespace-nowrap',
                        render: function(data, type, row) {
                            return type === 'display' ? row.selisih_html : data;
                        }
                    },
                    {
                        data: 'stok',
                        name: 'stok',
                        className: 'text-center whitespace-nowrap font-bold text-xs',
                        render: function(data, type, row) {
                            return type === 'display' ? row.stok_html : data;
                        }
                    },
                    {
                        data: 'status_label',
                        name: 'status_label',
                        className: 'text-center whitespace-nowrap',
                        render: function(data, type, row) {
                            return type === 'display' ? row.status_html : data;
                        }
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center whitespace-nowrap'
                    }
                ],
                emptyText: 'Belum ada data rekap produk',
                dtOptions: {
                    pageLength: 15
                }
            });
        });

        function rekapProduk() {
            return {
                modalVarian: false,
                detailVarianData: null,
                loadingVarian: false,

                async lihatVarian(id) {
                    this.modalVarian = true;
                    this.loadingVarian = true;
                    this.detailVarianData = null;

                    const urlParams = new URLSearchParams(window.location.search);
                    const query = urlParams.toString();

                    try {
                        const response = await fetch(`/rekap-produk/${id}/varian?${query}`);
                        const json = await response.json();
                        if (json.status === 'success') {
                            this.detailVarianData = json.data;
                        }
                    } catch (error) {
                        console.error('Gagal memuat rincian varian:', error);
                    } finally {
                        this.loadingVarian = false;
                    }
                }
            }
        }
    </script>
@endpush
