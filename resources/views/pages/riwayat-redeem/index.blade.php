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
                if (this.formTerpilih) {
                    this.formTerpilih.submit();
                }
                this.modalAksiTerbuka = false;
            }
        }">

        {{-- Ringkasan --}}
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

        <x-common.component-card title="Riwayat Redeem" desc="Semua transaksi penukaran poin dari seluruh member.">

            {{-- Filter --}}
            <form method="GET" action="{{ route('riwayat-redeem.index') }}" id="filterForm"
                class="flex flex-col lg:flex-row lg:items-end gap-3 flex-wrap">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Kode redeem / nama member / no HP / nama reward..."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">
                </div>
                <div class="w-full sm:w-44">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                    <select name="status"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">Semua Status</option>
                        <option value="menunggu_diambil" {{ request('status') === 'menunggu_diambil' ? 'selected' : '' }}>Menunggu Diambil</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis Reward</label>
                    <select name="jenis"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">Semua Jenis</option>
                        <option value="voucher" {{ request('jenis') === 'voucher' ? 'selected' : '' }}>Voucher</option>
                        <option value="produk" {{ request('jenis') === 'produk' ? 'selected' : '' }}>Produk</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 focus:border-brand-500 focus:ring-brand-500 text-sm">
                </div>
                <div class="w-full sm:w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 px-4 focus:border-brand-500 focus:ring-brand-500 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2.5 bg-amber-700 hover:bg-amber-800 text-white font-semibold rounded-xl text-sm transition shadow-xs whitespace-nowrap">
                        Terapkan
                    </button>
                    <a href="{{ route('riwayat-redeem.index') }}"
                        class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold rounded-xl text-sm transition border border-gray-200 dark:border-gray-700 whitespace-nowrap">
                        Reset
                    </a>
                </div>
            </form>

            {{-- Tabel --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Kode Redeem</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Member</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Reward</th>
                            <th class="py-3.5 px-3 hidden md:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Poin Dipakai</th>
                            <th class="py-3.5 px-3 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Redeem</th>
                            <th class="py-3.5 px-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="py-3.5 px-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($riwayat as $item)
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300">
                                <td class="py-3.5 px-3 text-gray-500">{{ $riwayat->firstItem() + $loop->index }}</td>
                                <td class="py-3.5 px-3">
                                    <span class="font-mono text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $item->kode_redeem }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="block font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $item->member->nama ?? 'Member tidak ditemukan' }}</span>
                                    <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="block font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</span>
                                    @if($item->reward)
                                        <span class="inline-flex mt-0.5 items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            {{ $item->reward->jenis === 'voucher' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400' }}">
                                            {{ ucfirst($item->reward->jenis) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 hidden md:table-cell text-center font-semibold text-red-500">
                                    -{{ number_format($item->poin_terpakai, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 hidden lg:table-cell text-gray-500 dark:text-gray-400">
                                    {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
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
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    @can('edit_reward')
                                        @if($item->status === 'menunggu_diambil')
                                            <div class="inline-flex gap-1">
                                                <form action="{{ route('riwayat-redeem.update-status', $item->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="selesai">
                                                    <button type="button"
                                                        @click="bukaModalAksi($el.closest('form'), 'selesai', '{{ $item->kode_redeem }}')"
                                                        class="text-green-600 dark:text-green-400 font-semibold hover:underline text-xs sm:text-sm">
                                                        Tandai Selesai
                                                    </button>
                                                </form>
                                                <span class="text-gray-300">|</span>
                                                <form action="{{ route('riwayat-redeem.update-status', $item->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="dibatalkan">
                                                    <button type="button"
                                                        @click="bukaModalAksi($el.closest('form'), 'dibatalkan', '{{ $item->kode_redeem }}')"
                                                        class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">
                                                {{ $item->diambil_pada?->translatedFormat('d M Y, H:i') ?? '-' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada riwayat redeem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $riwayat->links() }}
            </div>

        </x-common.component-card>

        {{-- Modal Konfirmasi Aksi --}}
        <div x-show="modalAksiTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalAksiTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <template x-if="jenisAksi === 'selesai'">
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ✓
                        </div>
                    </template>
                    <template x-if="jenisAksi === 'dibatalkan'">
                        <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ⚠
                        </div>
                    </template>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base"
                        x-text="jenisAksi === 'selesai' ? 'Tandai Redeem Selesai?' : 'Batalkan Redeem Ini?'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Kode redeem <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="kodeAksi"></span>
                        <template x-if="jenisAksi === 'selesai'">
                            <span> akan ditandai sebagai sudah diberikan ke member.</span>
                        </template>
                        <template x-if="jenisAksi === 'dibatalkan'">
                            <span> akan dibatalkan. Poin yang dipakai akan dikembalikan ke member.</span>
                        </template>
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalAksiTerbuka = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiAksi()"
                        :class="jenisAksi === 'selesai' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                        class="px-4 py-2 text-white font-bold text-xs rounded-lg transition shadow-xs">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>

    </div>

@endsection