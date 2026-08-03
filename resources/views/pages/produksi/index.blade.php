@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Produksi" />

    @if(session('success'))
        <div
            class="mb-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            class="mb-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6" x-data="{ modalOpen: false, produksiId: null, produksiNama: '', jumlahMax: 0, sisaBelum: 0 }">

        @if($produksiKadaluarsa->count() > 0)
            <div
                class="p-4 rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50/80 dark:bg-red-500/10 backdrop-blur-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start md:items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 mt-0.5 md:mt-0 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-red-900 dark:text-red-300">Peringatan Produk Kadaluarsa!</h4>
                        <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">
                            Terdapat <span class="font-bold">{{ $produksiKadaluarsa->count() }} batch produksi</span> yang sudah
                            kadaluarsa dan belum disesuaikan stoknya.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header & Filter --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Data Produksi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Catatan produksi dan status kadaluarsanya.</p>
                </div>

                <form method="GET" action="{{ route('produksi.index') }}" id="filterForm"
                    x-data="{ periode: '{{ $periode }}' }"
                    class="flex flex-col md:flex-row items-stretch md:items-end gap-3 w-full lg:w-auto flex-wrap">

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari Produk</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama produk..."
                            class="w-full md:w-56 px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                    </div>

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                            <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="kadaluarsa" {{ $status === 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Belum Kadaluarsa</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                        <select name="periode" x-model="periode" @change="$nextTick(() => $el.form.submit())"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
                            <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua</option>
                            <option value="harian" {{ $periode === 'harian' ? 'selected' : '' }}>Per Hari</option>
                            <option value="mingguan" {{ $periode === 'mingguan' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="bulanan" {{ $periode === 'bulanan' ? 'selected' : '' }}>Per Bulan</option>
                        </select>
                    </div>

                    <div class="flex-1 md:flex-initial" x-show="periode !== 'semua'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>

                        <input type="date" name="tanggal_ref" x-show="periode === 'harian'" :disabled="periode !== 'harian'"
                            value="{{ $tanggalRef->toDateString() }}" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">

                        <input type="week" name="tanggal_ref" x-show="periode === 'mingguan'"
                            :disabled="periode !== 'mingguan'" value="{{ $tanggalRef->format('o-\WW') }}"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">

                        <input type="month" name="tanggal_ref" x-show="periode === 'bulanan'"
                            :disabled="periode !== 'bulanan'" value="{{ $tanggalRef->format('Y-m') }}"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-900 transition">
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
                        <a href="{{ route('produksi.index', ['periode' => 'semua']) }}"
                            class="flex-1 md:flex-initial text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-lg text-sm transition border border-gray-200 dark:border-gray-700 whitespace-nowrap">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Baris aksi: tambah produksi + export --}}
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @if($periode === 'semua')
                        Menampilkan <strong>semua data produksi</strong>
                    @elseif($periode === 'harian')
                        Menampilkan data tanggal <strong>{{ $tanggalRef->format('d-m-Y') }}</strong>
                    @elseif($periode === 'mingguan')
                        Menampilkan data <strong>{{ $tanggalRef->copy()->startOfWeek()->format('d-m-Y') }}</strong> s/d
                        <strong>{{ $tanggalRef->copy()->endOfWeek()->format('d-m-Y') }}</strong>
                    @else
                        Menampilkan data bulan <strong>{{ $tanggalRef->translatedFormat('F Y') }}</strong>
                    @endif
                </span>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('produksi.pdf', [
        'periode' => $periode,
        'status' => $status,
        'search' => request('search'),
        'tanggal_ref' => $periode == 'harian'
            ? $tanggalRef->toDateString()
            : ($periode == 'mingguan'
                ? $tanggalRef->format('o-\WW')
                : ($periode == 'bulanan'
                    ? $tanggalRef->format('Y-m')
                    : null)),
    ]) }}"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                        📄 Cetak PDF
                    </a>

                    <a href="{{ route('produksi.excel', [
        'periode' => $periode,
        'status' => $status,
        'search' => request('search'),
        'tanggal_ref' => $periode === 'semua' ? null : (request('tanggal_ref') ?? $tanggalRef->toDateString()),
    ]) }}"
                        class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                        📊 Export CSV
                    </a>

                    <a href="{{ route('produksi.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Produksi</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabel Produksi --}}
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-2 py-4 sm:hidden w-8 text-center"></th>
                            <th
                                class="text-left px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden sm:table-cell">
                                No</th>
                            <th
                                class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden md:table-cell">
                                Tanggal</th>
                            <th
                                class="text-left px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Produk</th>
                            <th
                                class="text-center px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Hasil</th>
                            <th
                                class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden lg:table-cell">
                                Dibuat Oleh</th>
                            <th
                                class="text-left px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden lg:table-cell">
                                Keterangan</th>
                            <th
                                class="text-right px-4 sm:px-6 py-4 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    @forelse($produksi as $item)
                        <tbody x-data="{ open: false }"
                            class="divide-y divide-gray-50 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <tr
                                class="{{ $item->perlu_tindakan ? 'bg-red-50/70 dark:bg-red-500/10 hover:bg-red-100/70 dark:hover:bg-red-500/20' : 'hover:bg-amber-50/30 dark:hover:bg-amber-950/20' }} transition-colors">
                                <td class="px-2 py-3.5 sm:hidden text-center">
                                    <button @click="open = !open" type="button"
                                        class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                        :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                                        <span x-text="open ? '−' : '+'"></span>
                                    </button>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-gray-500 hidden sm:table-cell">
                                    {{ $produksi->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 font-medium text-gray-800 dark:text-white">
                                    {{ $item->produk->nama_produk }}
                                    @if($item->perlu_tindakan)
                                        <span
                                            class="inline-flex items-center ml-2 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:text-red-400">Kadaluarsa
                                            · sisa {{ $item->sisa_belum_disesuaikan }} pcs</span>
                                    @endif
                                    <div class="md:hidden text-[11px] text-gray-500 font-normal mt-0.5">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span
                                        class="inline-flex rounded-full bg-green-100 dark:bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400 text-xs font-bold">
                                        {{ $item->jumlah_hasil }} pcs
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-700 dark:text-gray-300 hidden lg:table-cell">
                                    {{ $item->user->name }}
                                </td>
                                <td class="px-6 py-3.5 text-gray-700 dark:text-gray-300 hidden lg:table-cell">
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('produksi.show', $item->id) }}"
                                        class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Detail</a>
                                    @if($item->perlu_tindakan)
                                        <button type="button"
                                            @click="modalOpen = true; produksiId = {{ $item->id }}; produksiNama = '{{ addslashes($item->produk->nama_produk) }}'; sisaBelum = {{ (int) $item->sisa_belum_disesuaikan }}; jumlahMax = {{ (int) min($item->sisa_belum_disesuaikan, $item->produk->stok) }}"
                                            class="text-red-600 dark:text-red-400 font-semibold hover:underline text-xs sm:text-sm">Kurangi
                                            Stok</button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Expandable child row -->
                            <tr x-show="open" x-cloak x-transition
                                class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                                <td colspan="8" class="p-3.5">
                                    <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300">
                                        <div><span class="text-gray-400">Tanggal:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span>
                                        </div>
                                        <div><span class="text-gray-400">Dibuat Oleh:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ $item->user->name }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Status:</span>

                                            @if($item->perlu_tindakan)
                                                <span class="font-semibold text-red-600 dark:text-red-400">Kadaluarsa · sisa
                                                    {{ $item->sisa_belum_disesuaikan }} pcs</span>
                                            @elseif($item->is_kadaluarsa)
                                                <span class="font-semibold text-red-600 dark:text-red-400">Kadaluarsa (sudah
                                                    disesuaikan)</span>
                                            @else
                                                <span class="font-semibold text-gray-600 dark:text-gray-400">
                                                    {{ ucfirst($item->status ?? '-') }}
                                                </span>

                                            @endif
                                        </div>
                                        <div><span class="text-gray-400">Kadaluarsa:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ $item->kadaluarsa_label }}</span>
                                        </div>
                                        <div class="col-span-2"><span class="text-gray-400">Keterangan:</span> <span
                                                class="font-semibold text-gray-800 dark:text-white">{{ $item->keterangan ?: '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-16 h-16 text-gray-200 dark:text-gray-700" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-400 dark:text-gray-500 font-medium">Belum ada data produksi untuk
                                            periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            {{-- Pagination --}}
            @if($produksi->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $produksi->withQueryString()->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Kurangi Stok --}}
        <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display:none;">
            <div @click.away="modalOpen = false"
                class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">Kurangi Stok Produk</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="'Produk: ' + produksiNama"></p>

                <form :action="'/produksi/' + produksiId + '/kurangi-stok'" method="POST">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah yang dikurangi
                        (pcs)</label>
                    <input type="number" name="jumlah" min="1" :max="jumlahMax" required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-gray-900 dark:text-white mb-1">
                    <p class="text-xs text-gray-400 mb-4"
                        x-text="'Sisa belum disesuaikan: ' + sisaBelum + ' pcs (maks input: ' + jumlahMax + ' pcs)'"></p>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">Kurangi
                            Stok</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        let timer;
        const searchInput = document.getElementById('search');
        const searchForm = document.getElementById('filterForm');

        searchInput.addEventListener('keyup', function () {
            clearTimeout(timer);
            timer = setTimeout(() => { searchForm.submit(); }, 400);
        });

        window.addEventListener('load', function () {
            if (searchInput.value !== '') {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        });
    </script>

@endsection