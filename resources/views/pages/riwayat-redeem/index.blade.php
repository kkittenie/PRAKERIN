@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Riwayat Redeem" parentTitle="Reward" parentUrl="{{ route('reward-voucher.index') }}" />

    <div class="space-y-6" x-data="{
            modalAksiTerbuka: false,
            formTerpilih: null,
            kodeAksi: '',
            jenisAksi: '',
            bukaModalAksi(form, jenis, kode) {
                this.formTerpilih = form;
                this.jenisAksi = jenis;
                this.kodeAksi = kode;
                this.modalAksiTerbuka = true;
            },
            konfirmasiAksi() {
                if (this.formTerpilih) { this.formTerpilih.submit(); }
                this.modalAksiTerbuka = false;
            }
        }">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Redeem</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($ringkasan['total'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Menunggu Diambil</p>
                <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ number_format($ringkasan['menunggu_diambil'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Selesai</p>
                <p class="mt-1 text-2xl font-extrabold text-green-600 dark:text-green-400">{{ number_format($ringkasan['selesai'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Dibatalkan</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-500 dark:text-gray-400">{{ number_format($ringkasan['dibatalkan'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
            <form method="GET" action="{{ route('riwayat-redeem.index') }}" id="filterForm" class="flex flex-col lg:flex-row lg:items-end gap-3 flex-wrap">
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 text-sm">
                        <option value="">Semua Status</option>
                        <option value="menunggu_diambil" {{ request('status') === 'menunggu_diambil' ? 'selected' : '' }}>Menunggu Diambil</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis Reward</label>
                    <select name="jenis" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 text-sm">
                        <option value="">Semua Jenis</option>
                        <option value="voucher" {{ request('jenis') === 'voucher' ? 'selected' : '' }}>Voucher</option>
                        <option value="produk" {{ request('jenis') === 'produk' ? 'selected' : '' }}>Produk</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 text-sm">
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2.5 bg-amber-700 hover:bg-amber-800 text-white font-semibold rounded-xl text-sm transition shadow-xs whitespace-nowrap">Terapkan</button>
                    <a href="{{ route('riwayat-redeem.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-xl text-sm transition border border-gray-200 dark:border-gray-700 whitespace-nowrap">Reset</a>
                </div>
            </form>
        </div>

        {{-- DataTables Card --}}
        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari redeem, member, no hp...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="riwayatRedeemTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Kode Redeem</th>
                            <th class="py-3">Member</th>
                            <th class="py-3">Reward</th>
                            <th class="py-3 text-center">Poin Dipakai</th>
                            <th class="py-3">Tanggal Redeem</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-mono text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $item->kode_redeem }}</span>
                                </td>
                                <td>
                                    <span class="block font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $item->member->nama ?? 'Member tidak ditemukan' }}</span>
                                    <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="block font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</span>
                                    @if($item->reward)
                                        <span class="inline-flex mt-0.5 items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            {{ $item->reward->jenis === 'voucher' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400' }}">
                                            {{ ucfirst($item->reward->jenis) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center font-semibold text-red-500">-{{ number_format($item->poin_terpakai, 0, ',', '.') }}</td>
                                <td class="text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-center">
                                    @php
                                        $statusBadge = [
                                            'menunggu_diambil' => ['label' => 'Menunggu Diambil', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
                                            'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
                                            'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
                                        ][$item->status];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
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

        {{-- Modal Konfirmasi Aksi --}}
        <div x-show="modalAksiTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalAksiTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <template x-if="jenisAksi === 'selesai'">
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">✓</div>
                    </template>
                    <template x-if="jenisAksi === 'dibatalkan'">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                    </template>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base" x-text="jenisAksi === 'selesai' ? 'Tandai Redeem Selesai?' : 'Batalkan Redeem Ini?'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Kode redeem <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="kodeAksi"></span>
                        <template x-if="jenisAksi === 'selesai'"><span> akan ditandai sebagai sudah diberikan ke member.</span></template>
                        <template x-if="jenisAksi === 'dibatalkan'"><span> akan dibatalkan. Poin yang dipakai akan dikembalikan ke member.</span></template>
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalAksiTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiAksi()" :class="jenisAksi === 'selesai' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'" class="px-4 py-2 text-white font-bold text-xs rounded-lg transition shadow-xs">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('riwayatRedeemTable', {
            emptyText: 'Belum ada riwayat redeem',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush