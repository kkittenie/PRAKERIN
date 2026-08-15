{{-- Partial isi tabel pesanan pick up. Dipakai dua kali: dirender langsung
     di index.blade.php (kunjungan pertama / navigasi biasa), dan juga
     dikembalikan sendirian sebagai respons AJAX oleh
     PesananPickupController::indeks() waktu ada pembaruan real-time
     (lihat dengarkanPembaruanRealtime() di index.blade.php) — supaya
     tabelnya bisa di-refresh tanpa reload seluruh halaman. --}}
        <x-common.component-card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3.5 px-2 sm:hidden w-7 text-center"></th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Kode Transaksi</th>
                            <th class="py-3.5 px-3 hidden sm:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Pembeli</th>
                            <th class="py-3.5 px-3 hidden md:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Total</th>
                            <th class="py-3.5 px-3 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Waktu Order</th>
                            <th class="py-3.5 px-3 hidden sm:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status Pesanan</th>
                            <th class="py-3.5 px-3 hidden sm:table-cell text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status Pembayaran</th>
                            <th class="py-3.5 px-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    @forelse ($pesanan as $item)
                        @php
                            $statusBadge = [
                                \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Diproses', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
                                \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Siap Diambil', 'class' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
                                \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Sudah Diambil', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
                                \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
                            ][$item->status_pesanan] ?? ['label' => '-', 'class' => 'bg-gray-100 text-gray-500'];

                            if ($item->status_pembayaran === 'Lunas') {
                                $bayarBadge = ['label' => 'Lunas', 'class' => 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400'];
                            } elseif ($item->bukti_dikonfirmasi_pada) {
                                $bayarBadge = ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400'];
                            } else {
                                $bayarBadge = ['label' => 'Menunggu Pembayaran', 'class' => 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'];
                            }
                        @endphp
                        <tbody x-data="{ open: false }"
                            class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300 align-top">
                                <td class="py-3.5 px-2 sm:hidden text-center">
                                    <button @click="open = !open" type="button"
                                        class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                        :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                                        <span x-text="open ? '−' : '+'"></span>
                                    </button>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $item->kode_transaksi }}</span>
                                    <div class="sm:hidden text-[11px] text-gray-500 font-normal mt-1">{{ $item->nama_pembeli }}</div>
                                </td>
                                <td class="py-3.5 px-3 hidden sm:table-cell">
                                    <span class="block font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $item->nama_pembeli }}</span>
                                    <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3 hidden md:table-cell text-left font-semibold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 hidden lg:table-cell text-gray-500 dark:text-gray-400">
                                    {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-3 hidden sm:table-cell text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 hidden sm:table-cell text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $bayarBadge['class'] }}">
                                        {{ $bayarBadge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <button @click="lihatDetail({{ $item->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold rounded-lg text-xs transition">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Dropdown Row for Mobile -->
                            <tr x-show="open" x-cloak x-transition class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                                <td colspan="3" class="p-4">
                                    <div class="flex flex-col gap-3 text-gray-600 dark:text-gray-300 w-full">
                                        <div>
                                            <span class="text-gray-400 block text-[11px] mb-0.5">Pembeli</span>
                                            <span class="font-semibold text-gray-800 dark:text-white">{{ $item->nama_pembeli }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <span class="text-gray-400 block text-[11px] mb-0.5">Total</span>
                                                <span class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 block text-[11px] mb-0.5">Waktu Order</span>
                                                <span class="font-semibold text-gray-800 dark:text-white">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $bayarBadge['class'] }}">{{ $bayarBadge['label'] }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-gray-400">
                                    Belum ada pesanan untuk filter ini.
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            @if ($pesanan->hasPages())
                <div class="mt-4 px-1">
                    {{ $pesanan->onEachSide(1)->links() }}
                </div>
            @endif
        </x-common.component-card>