@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Tambah Role" parentTitle="Kelola Role" :parentUrl="route('roles.index')" />

@php
    $initialSelected = json_encode(old('permissions', []));
    $allPermListJson = json_encode($allPermissions->pluck('name')->toArray());
    $modulesJson = json_encode($modules);
    $totalModulePerms = collect($modules)->sum(fn($m) => count($m['permissions']));
@endphp

<div x-data="roleFormHandler({{ $initialSelected }}, {{ $allPermListJson }}, {{ $modulesJson }})" class="space-y-6">
    <form method="POST" action="{{ route('roles.store') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
        @csrf

        <!-- Input Nama Role -->
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Nama Role <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Supervisor, Barista, Gudang..."
                class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 text-sm text-gray-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-gray-700 dark:text-white/90" />
            @error('name')
                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Section Hak Akses (Permissions) -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Hak Akses (Permissions)</span>
                    </h4>
                </div>

                <button type="button" @click="bukaModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Pilih / Atur Permission</span>
                </button>
            </div>

            <!-- Preview Badges Modul Terpilih -->
            <div class="pt-3 border-t border-gray-200/70 dark:border-gray-800">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Ringkasan Fitur yang Diaktifkan:</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="(info, key) in modules" :key="key">
                        <div x-show="countModuleSelected(getModulePerms(key)) > 0"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span x-text="info.nama"></span>
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                x-text="'(' + countModuleSelected(getModulePerms(key)) + '/' + ((modules[key] && modules[key].permissions) ? modules[key].permissions.length : 4) + ')'"></span>
                        </div>
                    </template>
                    <div x-show="selectedPerms.length === 0" class="text-xs italic text-gray-400">
                        Belum ada permission yang dipilih. Klik tombol "Pilih / Atur Permission" di atas.
                    </div>
                </div>
            </div>

            <!-- Hidden Inputs for Form Submission -->
            <template x-for="perm in selectedPerms" :key="perm">
                <input type="hidden" name="permissions[]" :value="perm">
            </template>
        </div>

        <!-- Tombol Simpan & Batal -->
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-amber-700 shadow-xs transition">
                Simpan Role
            </button>
            <a href="{{ route('roles.index') }}"
                class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                Batal
            </a>
        </div>
    </form>

    <!-- Modal Permission -->
    @include('admin.roles.partials.permission-modal')
</div>
@endsection

@push('scripts')
<script>
function roleFormHandler(initialPermissions, allPermsList, modulesData) {
    return {
        modalTerbuka: false,
        cari: '',
        filterGrup: 'semua',
        selectedPerms: initialPermissions || [],
        allPerms: allPermsList || [],
        modules: modulesData || {},

        bukaModal() {
            this.modalTerbuka = true;
        },
        tutupModal() {
            this.modalTerbuka = false;
        },
        pilihSemua() {
            this.selectedPerms = [...this.allPerms];
        },
        hapusSemua() {
            this.selectedPerms = [];
        },
        isSelected(name) {
            return this.selectedPerms.includes(name);
        },
        togglePerm(name) {
            if (this.isSelected(name)) {
                this.selectedPerms = this.selectedPerms.filter(p => p !== name);
            } else {
                this.selectedPerms.push(name);
            }
        },
        getModulePerms(moduleKey) {
            const mod = this.modules[moduleKey];
            const perms = (mod && mod.permissions) ? mod.permissions : ['lihat', 'tambah', 'edit', 'hapus'];
            return perms.map(aksi => aksi + '_' + moduleKey);
        },
        countModuleSelected(permNames) {
            return permNames.filter(p => this.selectedPerms.includes(p)).length;
        },
        isModuleAllSelected(permNames) {
            return permNames.every(p => this.selectedPerms.includes(p));
        },
        toggleModule(permNames) {
            if (this.isModuleAllSelected(permNames)) {
                // Batalkan semua di modul ini
                this.selectedPerms = this.selectedPerms.filter(p => !permNames.includes(p));
            } else {
                // Pilih semua di modul ini
                permNames.forEach(p => {
                    if (!this.selectedPerms.includes(p)) {
                        this.selectedPerms.push(p);
                    }
                });
            }
        },
        modulCocok(nama, deskripsi, key, grup) {
            if (this.filterGrup !== 'semua' && grup !== this.filterGrup) {
                return false;
            }
            if (!this.cari) {
                return true;
            }
            const q = this.cari.toLowerCase();
            return nama.includes(q) || deskripsi.includes(q) || key.includes(q) || grup.toLowerCase().includes(q);
        }
    };
}
</script>
@endpush