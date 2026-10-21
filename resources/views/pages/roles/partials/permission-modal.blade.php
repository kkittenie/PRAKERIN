@php
    $totalModulePerms = collect($modules)->sum(fn($m) => count($m['permissions']));
@endphp

<!-- MODAL PILIH PERMISSION -->
<div x-show="modalTerbuka" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-[99999] p-3 sm:p-5">

    <div @click.away="tutupModal()"
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-200 dark:border-gray-800">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pilih Hak Akses (Permissions)</h3>
            </div>
            <button type="button" @click="tutupModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Toolbar -->
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 space-y-2 shrink-0">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </span>
                    <input type="text" x-model="cari" placeholder="Cari modul..."
                        class="h-9 w-full pl-9 pr-3 rounded-lg border border-gray-200 bg-gray-50 text-xs text-gray-800 placeholder-gray-400 focus:bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                    <button type="button" x-show="cari" @click="cari = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <button type="button" @click="pilihSemua()"
                    class="px-3 h-9 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 transition shrink-0">
                    Pilih Semua
                </button>
                <button type="button" @click="hapusSemua()"
                    class="px-3 h-9 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-400 transition shrink-0">
                    Kosongkan
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs">
                @php
                    $tabs = [
                        'semua'            => 'Semua',
                        'Dashboard'        => 'Dashboard',
                        'Transaksi'        => 'Transaksi',
                        'Data Master'      => 'Data Master',
                        'Manajemen Produk' => 'Manajemen Produk',
                        'Member & Reward'  => 'Member & Reward',
                        'Laporan'          => 'Laporan',
                        'Manajemen Akses'  => 'Manajemen Akses',
                    ];
                @endphp
                @foreach ($tabs as $k => $label)
                    <button type="button" @click="filterGrup = '{{ $k }}'"
                        class="px-2.5 py-1 rounded-md font-medium transition shrink-0"
                        :class="filterGrup === '{{ $k }}'
                            ? 'bg-amber-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($modules as $moduleKey => $moduleInfo)
                    @php
                        $modPerms = [];
                        foreach ($moduleInfo['permissions'] as $aksi) {
                            $modPerms[$aksi] = "{$aksi}_{$moduleKey}";
                        }
                        $allPermNamesJson = json_encode(array_values($modPerms));
                        $totalPerms = count($modPerms);
                        $aksiLabels = [
                            'lihat'  => 'Lihat',
                            'tambah' => 'Tambah',
                            'edit'   => 'Edit',
                            'hapus'  => 'Hapus',
                        ];
                    @endphp

                    <div x-show="modulCocok('{{ strtolower($moduleInfo['nama']) }}', '{{ strtolower($moduleInfo['deskripsi']) }}', '{{ strtolower($moduleKey) }}', '{{ $moduleInfo['grup'] }}')"
                        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-3">

                        <!-- Card Header -->
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $moduleInfo['nama'] }}</span>
                                <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $moduleInfo['grup'] }}</span>
                            </div>
                            <button type="button"
                                @click="toggleModule({{ $allPermNamesJson }})"
                                class="shrink-0 ml-2 text-[11px] font-medium px-2 py-0.5 rounded transition"
                                :class="isModuleAllSelected({{ $allPermNamesJson }})
                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200'">
                                <span x-text="isModuleAllSelected({{ $allPermNamesJson }}) ? 'Batal' : 'Pilih'"></span>
                                (<span x-text="countModuleSelected({{ $allPermNamesJson }})"></span>/{{ $totalPerms }})
                            </button>
                        </div>

                        <!-- Checkbox Badges -->
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($modPerms as $aksiKey => $permName)
                                <label class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs cursor-pointer select-none transition"
                                    :class="isSelected('{{ $permName }}')
                                        ? 'border-amber-400 bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-300 font-semibold'
                                        : 'border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                                    <input type="checkbox"
                                        :checked="isSelected('{{ $permName }}')"
                                        @change="togglePerm('{{ $permName }}')"
                                        class="w-3 h-3 rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900">
                                    <span>{{ $aksiLabels[$aksiKey] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950/60 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <button type="button" @click="tutupModal()"
                    class="px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition">
                    Tutup
                </button>
                <button type="button" @click="tutupModal()"
                    class="px-5 py-2 rounded-lg text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terapkan</span>
                </button>
            </div>
        </div>
    </div>
</div>
