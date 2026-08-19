@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Reward Voucher" />

    <div class="space-y-6" x-data="{
            modalHapusTerbuka: false,
            formTerpilih: null,
            namaHapus: '',
            bukaModalHapus(form, nama) {
                this.formTerpilih = form;
                this.namaHapus = nama;
                this.modalHapusTerbuka = true;
            },
            konfirmasiHapus() {
                if (this.formTerpilih) { this.formTerpilih.submit(); }
                this.modalHapusTerbuka = false;
            }
        }">

        <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="sdt-toolbar">
                <div class="sdt-toolbar-left">
                    <div class="sdt-search">
                        <span class="sdt-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                        </span>
                        <input type="text" class="sdt-search-input" placeholder="Cari reward voucher...">
                    </div>
                    <div class="sdt-pagesize">
                        <span>Tampilkan</span>
                        <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                    </div>
                </div>
                <div class="sdt-toolbar-right">
                    <a href="{{ route('reward-voucher.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Voucher</span>
                    </a>
                </div>
            </div>

            <div class="sdt-table-wrap">
                <table id="rewardVoucherTable" class="sdt-table">
                    <thead>
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Voucher</th>
                            <th class="py-3">Deskripsi</th>
                            <th class="py-3 text-center">Poin Dibutuhkan</th>
                            <th class="py-3 text-center">Stok</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vouchers as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $item->gambar ? asset('storage/'.$item->gambar) : 'https://via.placeholder.com/48x48?text=No+Image' }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                                        <a href="{{ route('reward-voucher.show', $item->id) }}" class="font-semibold text-gray-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition">
                                            {{ $item->nama }}
                                        </a>
                                    </div>
                                </td>
                                <td class="text-xs text-gray-600 dark:text-gray-400 max-w-xs">
                                    <span class="line-clamp-2">{{ $item->deskripsi ?: '-' }}</span>
                                </td>
                                <td class="text-center text-xs font-semibold">{{ number_format($item->poin_dibutuhkan, 0, ',', '.') }} poin</td>
                                <td class="text-center text-xs">
                                    @if(is_null($item->stok))
                                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium">Tidak terbatas</span>
                                    @else
                                        {{ $item->stok }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('reward-voucher.toggle-status', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                                        </label>
                                    </form>
                                </td>
                                <td class="text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('reward-voucher.show', $item->id) }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline text-xs">Detail</a>
                                    <a href="{{ route('reward-voucher.edit', $item->id) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs">Edit</a>
                                    <form action="{{ route('reward-voucher.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="bukaModalHapus($el.closest('form'), '{{ addslashes($item->nama) }}')" class="text-red-500 font-semibold hover:underline text-xs">Hapus</button>
                                    </form>
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

        <!-- MODAL HAPUS -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Reward Voucher?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Voucher <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dan tidak dapat dipulihkan kembali.
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalHapusTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiHapus()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('rewardVoucherTable', {
            emptyText: 'Belum ada data reward voucher',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush