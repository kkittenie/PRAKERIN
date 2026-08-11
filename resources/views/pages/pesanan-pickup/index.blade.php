@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Pesanan Pick Up" parentTitle="Transaksi" :parentUrl="route('transaksi.indeks')" />

    <div class="space-y-6" x-data="{
            kodeInput: '',
            loading: false,
            hasilError: '',
            hasilData: null,
            modalSuksesTerbuka: false,
            verifikasi() {
                const kode = this.kodeInput.trim();
                if (! kode || this.loading) return;

                this.loading = true;
                this.hasilError = '';

                fetch('{{ route('pesanan-pickup.verifikasi') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ kode_pickup: kode })
                })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.status === 'success') {
                            this.hasilData = data.data;
                            this.kodeInput = '';
                            this.modalSuksesTerbuka = true;
                        } else {
                            this.hasilError = data.pesan || 'Kode pickup tidak valid.';
                        }
                    })
                    .catch(() => { this.hasilError = 'Gagal memverifikasi kode. Coba lagi.'; })
                    .finally(() => { this.loading = false; });
            },
            adaItemProduksi() {
                return !! (this.hasilData?.detail_transaksi || []).some(d => d.butuh_produksi);
            },
            formatRupiah(angka) {
                return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
            },
            cetakStrukHasil(jenis) {
                if (! this.hasilData?.kode_transaksi) return;
                window.open(`/transaksi/cetak/${this.hasilData.kode_transaksi}?jenis=${jenis}&print=true`, '_blank', 'width=400,height=700');
            },
            tutupModalSukses() {
                this.modalSuksesTerbuka = false;
                window.location.reload();
            },
            modalBatalTerbuka: false,
            formBatalTerpilih: null,
            kodeBatal: '',
            bukaModalBatal(form, kode) {
                this.formBatalTerpilih = form;
                this.kodeBatal = kode;
                this.modalBatalTerbuka = true;
            },
            konfirmasiBatal() {
                if (this.formBatalTerpilih) {
                    this.formBatalTerpilih.submit();
                }
                this.modalBatalTerbuka = false;
            }
         }">

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Diproses</p>
                <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ number_format($jumlahDiproses, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Siap Diambil</p>
                <p class="mt-1 text-2xl font-extrabold text-blue-600 dark:text-blue-400">{{ number_format($jumlahSiap, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Verifikasi Kode Pickup --}}
        @can('edit_pesanan_pickup')
            <x-common.component-card title="Verifikasi Kode Pickup" desc="Tanyakan kode pickup ke pembeli lalu masukkan di sini untuk menyerahkan pesanan.">
                <div class="flex flex-col sm:flex-row gap-3 sm:items-start">
                    <div class="flex-1">
                        <input type="text" x-model="kodeInput" @keydown.enter.prevent="verifikasi()"
                               placeholder="Contoh: A3F9K2" maxlength="10"
                               class="w-full uppercase tracking-[0.2em] font-mono font-bold rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-3 px-4 focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <template x-if="hasilError">
                            <p class="mt-2 text-xs font-semibold text-red-500" x-text="hasilError"></p>
                        </template>
                    </div>
                    <button type="button" @click="verifikasi()" :disabled="loading"
                            class="shrink-0 px-6 py-3 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white font-semibold rounded-xl text-sm transition shadow-xs whitespace-nowrap">
                        <span x-show="! loading">Verifikasi &amp; Serahkan</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </x-common.component-card>
        @endcan

        <!-- ========== MODAL: PESANAN DISERAHKAN + CETAK STRUK ========== -->
        <div x-show="modalSuksesTerbuka" x-cloak
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-9999 p-4" x-transition>
            <div
                class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-xl w-full max-w-sm overflow-hidden p-6 flex flex-col items-center text-center gap-6">
                <div
                    class="w-16 h-16 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold flex items-center justify-center rounded-full text-3xl animate-bounce">
                    ✓</div>
                <div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-lg">Pesanan Diserahkan!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pesanan pick up telah berhasil diserahkan
                        ke <span x-text="hasilData?.nama_pembeli"></span>.</p>
                    <span
                        class="inline-block mt-3 px-3 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20 font-mono text-xs rounded-full font-bold"
                        x-text="hasilData?.kode_transaksi"></span>
                </div>
                <div
                    class="w-full bg-gray-50 dark:bg-gray-950 rounded-xl p-4 border border-gray-100 dark:border-gray-800 flex flex-col gap-2 text-xs">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Total
                            Pembayaran</span><span class="font-semibold text-gray-800 dark:text-white"
                            x-text="formatRupiah(hasilData?.total_bayar)"></span></div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400"><span>Metode
                            Pembayaran</span><span class="font-semibold text-gray-800 dark:text-white"
                            x-text="hasilData?.metode_pembayaran"></span></div>
                    <div
                        class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-800 font-extrabold text-sm">
                        <span>Status</span><span class="text-green-600 dark:text-green-400">Lunas</span>
                    </div>
                </div>
                <!-- Tombol Cetak Struk -->
                <div class="w-full flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="cetakStrukHasil('pelanggan')"
                            class="py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Struk Pelanggan</span>
                        </button>
                        <button @click="cetakStrukHasil('dapur')" x-show="adaItemProduksi()"
                            class="py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Tiket Produksi</span>
                        </button>
                    </div>
                </div>
                <button @click="tutupModalSukses()"
                    class="w-full py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition text-sm">
                    Selesai
                </button>
            </div>
        </div>

        <x-common.component-card title="Daftar Pesanan Pick Up" desc="Pesanan yang masuk dari Member App.">

            {{-- Filter status --}}
            <div class="flex flex-wrap gap-2">
                @php
                    $tabs = [
                        'aktif' => 'Aktif',
                        'semua' => 'Semua',
                        \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
                        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
                        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
                        \App\Models\Transaksi::STATUS_DIBATALKAN => 'Dibatalkan',
                    ];
                @endphp
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('pesanan-pickup.indeks', ['status' => $value]) }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold transition
                       {{ $statusFilter === $value ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Tabel pesanan --}}
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Kode Pickup</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Pembeli</th>
                            <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Item</th>
                            <th class="py-3.5 px-3 hidden md:table-cell text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Total</th>
                            <th class="py-3.5 px-3 hidden lg:table-cell text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Waktu Order</th>
                            <th class="py-3.5 px-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="py-3.5 px-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($pesanan as $item)
                            @php
                                $statusBadge = [
                                    \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Diproses', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
                                    \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Ready for Pickup', 'class' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
                                    \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Sudah Diambil', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
                                    \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
                                ][$item->status_pesanan] ?? ['label' => '-', 'class' => 'bg-gray-100 text-gray-500'];
                            @endphp
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition duration-200 text-gray-700 dark:text-gray-300 align-top">
                                <td class="py-3.5 px-3">
                                    <span class="font-mono text-xs font-bold tracking-widest text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">{{ $item->kode_pickup }}</span>
                                    <span class="block mt-1 text-[11px] text-gray-400">{{ $item->kode_transaksi }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="block font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $item->nama_pembeli }}</span>
                                    <span class="block text-xs text-gray-400">{{ $item->member->no_hp ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <ul class="space-y-0.5 text-xs text-gray-600 dark:text-gray-300">
                                        @foreach ($item->detailTransaksi->groupBy('nama_produk') as $namaProduk => $group)
                                            <li>{{ $group->sum('jumlah') }}x {{ $namaProduk }}</li>
                                        @endforeach
                                    </ul>
                                    @if ($item->catatan_pesanan)
                                        <p class="mt-1 text-[11px] italic text-gray-400">"{{ $item->catatan_pesanan }}"</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 hidden md:table-cell text-right font-semibold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 hidden lg:table-cell text-gray-500 dark:text-gray-400">
                                    {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    @can('edit_pesanan_pickup')
                                        @if ($item->status_pesanan === \App\Models\Transaksi::STATUS_DIPROSES)
                                            <div class="inline-flex flex-col items-end gap-1">
                                                <form action="{{ route('pesanan-pickup.siap', $item) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline text-xs sm:text-sm">
                                                        Tandai Siap
                                                    </button>
                                                </form>
                                                <form action="{{ route('pesanan-pickup.batal', $item) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="button"
                                                        @click="bukaModalBatal($el.closest('form'), '{{ $item->kode_transaksi }}')"
                                                        class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif ($item->status_pesanan === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                                            <div class="inline-flex flex-col items-end gap-1">
                                                <span class="text-[11px] text-gray-400">Verifikasi kode di atas</span>
                                                <form action="{{ route('pesanan-pickup.batal', $item) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="button"
                                                        @click="bukaModalBatal($el.closest('form'), '{{ $item->kode_transaksi }}')"
                                                        class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">
                                                        Batalkan
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif ($item->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL)
                                            <div class="inline-flex flex-col items-end gap-1">
                                                <span class="text-[11px] text-gray-400">
                                                    {{ $item->diambil_pada?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                </span>
                                                <div class="flex gap-2">
                                                    <button type="button"
                                                        onclick="window.open('{{ route('transaksi.cetak', $item->kode_transaksi) }}?jenis=pelanggan&print=true', '_blank', 'width=400,height=700')"
                                                        class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-[11px]">
                                                        Struk
                                                    </button>
                                                    @if ($item->detailTransaksi->contains('butuh_produksi', true))
                                                        <button type="button"
                                                            onclick="window.open('{{ route('transaksi.cetak', $item->kode_transaksi) }}?jenis=dapur&print=true', '_blank', 'width=400,height=700')"
                                                            class="text-blue-600 dark:text-blue-400 font-semibold hover:underline text-[11px]">
                                                            Dapur
                                                        </button>
                                                    @endif
                                                </div>
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
                                <td colspan="7" class="py-10 text-center text-sm text-gray-400">
                                    Belum ada pesanan pick up untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <!-- ========== MODAL: KONFIRMASI BATALKAN PESANAN ========== -->
        <div x-show="modalBatalTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalBatalTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div
                        class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                        ⚠
                    </div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Batalkan Pesanan?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Pesanan <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="kodeBatal"></span>
                        akan dibatalkan. Stok & voucher yang terpakai akan dikembalikan.
                    </p>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalBatalTerbuka = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="konfirmasiBatal()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Batalkan</button>
                </div>
            </div>
        </div>

    </div>

@endsection