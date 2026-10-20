<!-- MODAL PILIH PERMISSION -->
<div x-show="modalTerbuka" x-cloak x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-3 sm:p-5">

    <div @click.away="tutupModal()"
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100 dark:border-gray-800">

        <!-- Header Modal -->
        <div class="px-6 py-4.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/70 dark:bg-gray-950/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Pilih Hak Akses (Permissions)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400"
                            x-text="selectedPerms.length + ' dipilih'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Centang hak akses fitur dan tindakan (CRUD) yang diizinkan untuk role ini.
                    </p>
                </div>
            </div>

            <button type="button" @click="tutupModal()"
                class="w-9 h-9 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 flex items-center justify-center transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Toolbar / Search & Quick Filters -->
        <div class="p-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 space-y-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </span>
                    <input type="text" x-model="cari" placeholder="Cari modul atau tindakan (misal: banner, produk, tambah)..."
                        class="h-10 w-full pl-10 pr-9 rounded-xl border border-gray-200 bg-gray-50/70 text-xs text-gray-800 placeholder-gray-400 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-200 transition">
                    <button type="button" x-show="cari" @click="cari = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="pilihSemua()"
                        class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Pilih Semua</span>
                    </button>
                    <button type="button" @click="hapusSemua()"
                        class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Kosongkan</span>
                    </button>
                </div>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs">
                @php
                    $kategoriTabs = [
                        'semua' => 'Semua Modul',
                        'Dashboard' => 'Dashboard',
                        'Transaksi' => 'Transaksi',
                        'Data Master' => 'Data Master',
                        'Manajemen Produk' => 'Manajemen Produk',
                        'Member & Reward' => 'Member & Reward',
                        'Laporan' => 'Laporan',
                        'Manajemen Akses' => 'Manajemen Akses',
                    ];
                @endphp
                @foreach ($kategoriTabs as $kunciTab => $labelTab)
                    <button type="button" @click="filterGrup = '{{ $kunciTab }}'"
                        class="px-3 py-1.5 rounded-lg font-medium transition shrink-0"
                        :class="filterGrup === '{{ $kunciTab }}'
                            ? 'bg-amber-600 text-white shadow-xs font-bold'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'">
                        {{ $labelTab }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Body / Grid Modul Permissions -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($modules as $moduleKey => $moduleInfo)
                    @php
                        $modPerms = [
                            'lihat'  => "lihat_{$moduleKey}",
                            'tambah' => "tambah_{$moduleKey}",
                            'edit'   => "edit_{$moduleKey}",
                            'hapus'  => "hapus_{$moduleKey}",
                        ];
                        $allPermNamesJson = json_encode(array_values($modPerms));
                    @endphp

                    <div x-show="modulCocok('{{ strtolower($moduleInfo['nama']) }}', '{{ strtolower($moduleInfo['deskripsi']) }}', '{{ strtolower($moduleKey) }}', '{{ $moduleInfo['grup'] }}')"
                        class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/40 p-4 transition-all hover:border-amber-400/60 dark:hover:border-amber-500/40">

                        <!-- Header Card Modul -->
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-800/80">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                        {{ $moduleInfo['nama'] }}
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ $moduleInfo['grup'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">
                                    {{ $moduleInfo['deskripsi'] }}
                                </p>
                            </div>

                            <!-- Tombol Pilih Semua Per Modul -->
                            <button type="button"
                                @click="toggleModule({{ $allPermNamesJson }})"
                                class="shrink-0 text-[11px] font-semibold px-2 py-1 rounded-md transition"
                                :class="isModuleAllSelected({{ $allPermNamesJson }})
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200'">
                                <span x-text="isModuleAllSelected({{ $allPermNamesJson }}) ? 'Batal Semua' : 'Pilih Semua'"></span>
                                (<span x-text="countModuleSelected({{ $allPermNamesJson }})"></span>/4)
                            </button>
                        </div>

                        <!-- Daftar Checkbox CRUD -->
                        <div class="grid grid-cols-2 gap-2 pt-3">
                            @php
                                $aksiLabels = [
                                    'lihat'  => ['label' => 'Lihat Data', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                                    'tambah' => ['label' => 'Tambah Data', 'icon' => 'M12 4v16m8-8H4'],
                                    'edit'   => ['label' => 'Ubah / Edit', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                                    'hapus'  => ['label' => 'Hapus Data', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                                ];
                            @endphp

                            @foreach ($modPerms as $aksiKey => $permName)
                                <label class="flex items-center gap-2.5 p-2 rounded-lg border text-xs cursor-pointer select-none transition"
                                    :class="isSelected('{{ $permName }}')
                                        ? 'border-amber-400 bg-amber-50/60 dark:bg-amber-500/10 text-amber-900 dark:text-amber-300 font-semibold shadow-2xs'
                                        : 'border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400 hover:bg-gray-100/60'">
                                    <input type="checkbox"
                                        :checked="isSelected('{{ $permName }}')"
                                        @change="togglePerm('{{ $permName }}')"
                                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="truncate">{{ $aksiLabels[$aksiKey]['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-950/60 flex items-center justify-between">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Total: <span class="font-bold text-gray-800 dark:text-gray-200" x-text="selectedPerms.length"></span> dari {{ $allPermissions->count() }} permission terpilih
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="tutupModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition">
                    Tutup
                </button>
                <button type="button" @click="tutupModal()"
                    class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terapkan Hak Akses</span>
                </button>
            </div>
        </div>
    </div>
</div>
