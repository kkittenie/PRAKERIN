{{-- Notification Dropdown Component --}}
@php
    $lowStockProduk = \App\Models\Produk::where('status_aktif', true)->whereColumn('stok', '<=', 'stok_minimum')->get();
    $lowStockBahan = \App\Models\BahanBaku::whereColumn('stok', '<=', 'stok_minimum')->get();
    $pesananDitahan = \App\Models\PesananDitahan::orderBy('created_at', 'desc')->limit(5)->get();
    $produksiKadaluarsa = \App\Models\Produksi::with('produk')->whereColumn('jumlah_disesuaikan', '<', 'jumlah_hasil')->get()->filter(fn ($item) => $item->is_kadaluarsa);    
 
    $notificationsList = [];
    
    // Add held orders
    foreach ($pesananDitahan as $item) {
        $itemPesanan = is_array($item->item_pesanan) ? $item->item_pesanan : json_decode($item->item_pesanan, true) ?? [];
        $totalItems = array_sum(array_column($itemPesanan, 'jumlah'));
        $notificationsList[] = [
            'type' => 'Transaksi Ditahan',
            'title' => 'Pesanan Ditahan',
            'desc' => 'Pesanan atas nama ' . ($item->nama_pembeli ?: 'Pelanggan') . ' (' . $totalItems . ' item) ditangguhkan.',
            'time' => $item->created_at->diffForHumans(),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'color' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
            'url' => route('transaksi.indeks'),
        ];
    }

    // Add low stock products
    foreach ($lowStockProduk as $produk) {
        $notificationsList[] = [
            'type' => 'Stok Produk Menipis',
            'title' => 'Stok Produk Menipis',
            'desc' => 'Produk "' . $produk->nama_produk . '" sisa ' . $produk->stok . ' pcs (minimum ' . $produk->stok_minimum . ' pcs).',
            'time' => $produk->updated_at->diffForHumans(),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
            'color' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'url' => route('produk.index'),
        ];
    }
    
    // Add low stock raw materials
    foreach ($lowStockBahan as $bahan) {
        $namaSatuan = optional($bahan->satuan)->nama ?? '-';
        $notificationsList[] = [
            'type' => 'Stok Bahan Menipis',
            'title' => 'Bahan Baku Menipis',
            'desc' => 'Bahan "' . $bahan->nama_bahan . '" sisa ' . \App\Models\Satuan::formatAngka($bahan->stok, optional($bahan->satuan)->tipe) . ' ' . $namaSatuan . ' (minimum ' . \App\Models\Satuan::formatAngka($bahan->stok_minimum, optional($bahan->satuan)->tipe) . ' ' . $namaSatuan . ').',
            'time' => $bahan->updated_at->diffForHumans(),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
            'color' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
            'url' => route('bahan-baku.index'),
        ];
    }
    
   // Add expired production batches
    foreach ($produksiKadaluarsa as $item) {
        $notificationsList[] = [
            'type' => 'Produk Kadaluarsa',
            'title' => 'Produk Kadaluwarsa',
            'desc' => 'Batch produksi "' . $item->produk->nama_produk . '" sisa ' . $item->sisa_belum_disesuaikan . ' pcs (dari ' . $item->jumlah_hasil . ' pcs) sudah lewat masa kadaluarsa.',
            'time' => $item->created_at->diffForHumans(),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>',
            'color' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
            'url' => route('produksi.index'),
        ];
    }
    
    $notifyingCount = count($notificationsList);
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    notifying: {{ $notifyingCount > 0 ? 'true' : 'false' }},
    posisiAtas: 0,
    posisiKiri: 0,
    tinggiMax: 400,

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        this.notifying = false;
        if (this.dropdownOpen) {
            this.$nextTick(() => this.hitungPosisi());
        }
    },
    closeDropdown() {
        this.dropdownOpen = false;
    },
    hitungPosisi() {
        const margin = 12;
        const tombol = this.$refs.tombolNotifikasi.getBoundingClientRect();
        const panel = this.$refs.panelNotifikasi;
        const lebarPanel = panel.offsetWidth;

        let kiri = tombol.right - lebarPanel;
        kiri = Math.max(margin, Math.min(kiri, window.innerWidth - lebarPanel - margin));

        let atas = tombol.bottom + margin;
        let tinggi = window.innerHeight - atas - margin;

        // Jika ruang di bawah tombol terlalu sempit (misal hp/tab landscape),
        // pastikan tetap ada tinggi minimum yang wajar untuk menampilkan list.
        if (tinggi < 200) {
            tinggi = Math.max(160, window.innerHeight - margin * 2);
            atas = margin;
        }

        this.posisiKiri = kiri;
        this.posisiAtas = atas;
        this.tinggiMax = tinggi;
    }
}" @click.away="closeDropdown()" @resize.window="if (dropdownOpen) hitungPosisi()" @scroll.window="if (dropdownOpen) hitungPosisi()">
    <!-- Notification Button -->
    <button
        x-ref="tombolNotifikasi"
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
        @click="toggleDropdown()"
        type="button"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifying"
            class="absolute right-0 top-0.5 z-1 h-2 w-2 rounded-full bg-orange-400"
        >
            <span
                class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"
            ></span>
        </span>

        <!-- Bell Icon -->
        <svg
            class="fill-current"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill=""
            />
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-ref="panelNotifikasi"
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        :style="`top:${posisiAtas}px; left:${posisiKiri}px; max-height:${tinggiMax}px;`"
        class="fixed flex w-[92vw] max-w-[361px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-99999"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
            <h5 class="text-lg font-semibold text-gray-800 dark:text-white">Notifikasi POS</h5>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                {{ $notifyingCount }} Baru
            </span>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar flex-1 min-h-0">
            @forelse ($notificationsList as $notification)
                <li>
                    <a
                        class="flex gap-3 rounded-lg border-b border-gray-50 dark:border-gray-800/60 p-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        href="{{ $notification['url'] }}"
                    >
                        <span class="flex items-center justify-center h-10 w-10 rounded-full shrink-0 {{ $notification['color'] }}">
                            {!! $notification['icon'] !!}
                        </span>

                        <span class="block">
                            <span class="mb-1 block text-sm font-semibold text-gray-800 dark:text-white">
                                {{ $notification['title'] }}
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1 leading-relaxed">
                                {{ $notification['desc'] }}
                            </span>
                            <span class="block text-xxs text-gray-400 dark:text-gray-500">
                                {{ $notification['time'] }}
                            </span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-500">
                    <svg class="w-12 h-12 mb-3 text-gray-200 dark:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm">Tidak ada notifikasi baru.</p>
                </li>
            @endforelse
        </ul>
    </div>
    <!-- Dropdown End -->
</div>