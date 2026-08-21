@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Produksi" />

    <div class="space-y-6" x-data="{ modalOpen: false, produksiId: null, produksiNama: '', jumlahMax: 0, sisaBelum: 0, punyaUkuran: false, ukuran: 'Besar', stokBesar: 0, stokKecil: 0 }">

        @if($produksiKadaluarsa->count() > 0)
            <div class="p-4 rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/80 dark:bg-red-500/10 backdrop-blur-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start md:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-red-900 dark:text-red-300">Peringatan Produk Kadaluwarsa!</h4>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">
                            Terdapat <span class="font-bold">{{ $produksiKadaluarsa->count() }} batch produksi</span> yang sudah kadaluwarsa dan belum disesuaikan stoknya.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Top Filter Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Data Produksi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Catatan produksi dan status kadaluwarsanya.</p>
                </div>

                <form method="GET" action="{{ route('produksi.index') }}" id="filterForm"
                    x-data="{ periode: '{{ $periode }}' }"
                    class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full lg:w-auto flex-wrap">

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="kadaluarsa" {{ $status === 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Belum Kadaluarsa</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                        <select name="periode" x-model="periode" @change="$nextTick(() => $el.form.submit())"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua</option>
                            <option value="harian" {{ $periode === 'harian' ? 'selected' : '' }}>Per Hari</option>
                            <option value="mingguan" {{ $periode === 'mingguan' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="bulanan" {{ $periode === 'bulanan' ? 'selected' : '' }}>Per Bulan</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial" x-show="periode !== 'semua'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                        <input type="date" name="tanggal_ref" x-show="periode === 'harian'" :disabled="periode !== 'harian'" value="{{ $tanggalRef->toDateString() }}" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                        <input type="week" name="tanggal_ref" x-show="periode === 'mingguan'" :disabled="periode !== 'mingguan'" value="{{ $tanggalRef->format('o-\WW') }}" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                        <input type="month" name="tanggal_ref" x-show="periode === 'bulanan'" :disabled="periode !== 'bulanan'" value="{{ $tanggalRef->format('Y-m') }}" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm">
                    </div>

                    <div class="flex gap-2 justify-end mt-2 md:mt-0">
                        <button type="submit" class="flex-1 md:flex-initial px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition">Filter</button>
                        <a href="{{ route('produksi.index', ['periode' => 'semua']) }}" class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700">Reset</a>
                    </div>
                </form>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex gap-2">
                    <a href="{{ route('produksi.pdf', ['periode' => $periode, 'status' => $status, 'search' => request('search'), 'tanggal_ref' => $periode == 'harian' ? $tanggalRef->toDateString() : ($periode == 'mingguan' ? $tanggalRef->format('o-\WW') : ($periode == 'bulanan' ? $tanggalRef->format('Y-m') : null))]) }}" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Cetak PDF</a>
                    <a href="{{ route('produksi.excel', ['periode' => $periode, 'status' => $status, 'search' => request('search'), 'tanggal_ref' => $periode === 'semua' ? null : (request('tanggal_ref') ?? $tanggalRef->toDateString())]) }}" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">Export CSV</a>
                </div>
                <a href="{{ route('produksi.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Produksi</span>
                </a>
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
                        <input type="text" class="sdt-search-input" placeholder="Cari produksi...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="produksiTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3 text-center">Hasil</th>
                            <th class="py-3">Dibuat Oleh</th>
                            <th class="py-3">Keterangan</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produksi as $index => $item)
                            <tr class="{{ $item->perlu_tindakan ? 'bg-red-50/70 dark:bg-red-500/10' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="font-medium text-gray-800 dark:text-white">
                                    {{ $item->produk->nama_produk }}
                                    @if($item->perlu_tindakan)
                                        <span class="inline-flex items-center ml-2 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:text-red-400">
                                            Kadaluarsa · sisa {{ $item->sisa_belum_disesuaikan }} pcs
                                        </span>
                                    @elseif($item->is_kadaluarsa)
                                        <span class="inline-flex items-center ml-2 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:text-red-400">
                                            Kadaluarsa
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">{{ $item->jumlah_hasil }} pcs</span>
                                    <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $item->rincian_ukuran }}</div>
                                </td>
                                <td>{{ $item->user->name }}</td>
                                <td class="text-xs text-gray-600 dark:text-gray-400">{{ $item->keterangan ?: '-' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-0.5 justify-end">
                                        <a href="{{ route('produksi.show', $item->id) }}" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if($item->perlu_tindakan)
                                            <button type="button" title="Kurangi Stok" @click="modalOpen = true; produksiId = {{ $item->id }}; produksiNama = '{{ addslashes($item->produk->nama_produk) }}'; sisaBelum = {{ (int) $item->sisa_belum_disesuaikan }}; punyaUkuran = {{ $item->produk->punya_ukuran ? 'true' : 'false' }}; stokBesar = {{ (int) ($item->produk->stok_besar ?? 0) }}; stokKecil = {{ (int) ($item->produk->stok_kecil ?? 0) }}; ukuran = 'Besar'; jumlahMax = punyaUkuran ? Math.min(sisaBelum, stokBesar) : {{ (int) min($item->sisa_belum_disesuaikan, $item->produk->stok) }}" class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 transition">Kurangi Stok</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="sdt-footer">
                <div class="sdt-info"></div>
                <div class="sdt-pagination"></div>
            </div>
        </div>

        {{-- Modal Kurangi Stok --}}
        <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div @click.away="modalOpen = false" class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Kurangi Stok Produk</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="'Produk: ' + produksiNama"></p>
                <form :action="'/produksi/' + produksiId + '/kurangi-stok'" method="POST">
                    @csrf
                    <template x-if="punyaUkuran">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ukuran</label>
                            <div class="flex gap-2">
                                <button type="button" @click="ukuran = 'Besar'; jumlahMax = Math.min(sisaBelum, stokBesar)"
                                    :class="ukuran === 'Besar' ? 'bg-brand-500 text-white border-brand-500' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700'"
                                    class="flex-1 rounded-lg border px-3 py-2 text-sm font-semibold transition">
                                    Besar <span class="font-normal" x-text="'(' + stokBesar + ' pcs)'"></span>
                                </button>
                                <button type="button" @click="ukuran = 'Kecil'; jumlahMax = Math.min(sisaBelum, stokKecil)"
                                    :class="ukuran === 'Kecil' ? 'bg-brand-500 text-white border-brand-500' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700'"
                                    class="flex-1 rounded-lg border px-3 py-2 text-sm font-semibold transition">
                                    Kecil <span class="font-normal" x-text="'(' + stokKecil + ' pcs)'"></span>
                                </button>
                            </div>
                            <input type="hidden" name="ukuran" :value="ukuran">
                        </div>
                    </template>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah yang dikurangi (pcs)</label>
                    <input type="number" name="jumlah" min="1" :max="jumlahMax" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-gray-900 dark:text-white mb-1">
                    <p class="text-xs text-gray-400 mb-4" x-text="'Sisa belum disesuaikan: ' + sisaBelum + ' pcs (maks input: ' + jumlahMax + ' pcs)'"></p>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">Kurangi Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('produksiTable', {
            emptyText: 'Belum ada data produksi',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush