@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Kelola Role" />

@php
    $allPermsListJson = json_encode($allPermissions->pluck('name')->toArray());
    $modulesJson = json_encode($modules);
@endphp

<div class="space-y-6"
    x-data="roleIndexHandler({{ $allPermsListJson }}, {{ $modulesJson }})"
    @buka-modal-edit-role.window="bukaModalEdit($event.detail)"
    @buka-modal-tambah-role.window="bukaModalTambah()">

    <!-- Toast Notifications -->
    <div class="fixed top-5 right-5 z-[999999] flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-[-10px]"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-[-10px]"
                class="text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 min-w-[280px] pointer-events-auto"
                :class="toast.tipe === 'sukses' ? 'bg-emerald-600' : 'bg-red-600'">
                <span class="font-bold text-lg" x-text="toast.tipe === 'sukses' ? '✓' : '⚠'"></span>
                <span class="text-sm font-medium" x-text="toast.pesan"></span>
            </div>
        </template>
    </div>

    <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="sdt-toolbar">
            <div class="sdt-toolbar-left">
                <div class="sdt-search">
                    <span class="sdt-search-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                    </span>
                    <input type="text" class="sdt-search-input" placeholder="Cari role...">
                </div>
                <div class="sdt-pagesize">
                    <span>Tampilkan</span>
                    <select><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
                </div>
            </div>
            <div class="sdt-toolbar-right">
                @can('tambah_role')
                <button type="button" @click="bukaModalTambah()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Role</span>
                </button>
                @endcan
            </div>
        </div>

        <div class="sdt-table-wrap">
            <table id="rolesTable" class="sdt-table">
                <thead>
                    <tr>
                        <th class="py-3">Nama Role</th>
                        <th class="py-3">Jumlah Permission</th>
                        <th class="py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Role?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Role <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span> akan dihapus dan tidak dapat dipulihkan kembali.
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                <button type="button" @click="modalHapusTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300 cursor-pointer">Batal</button>
                <button type="button" @click="konfirmasiHapus()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs cursor-pointer">Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL ROLE (TAMBAH & EDIT) -->
    @include('admin.roles.partials.role-modal')
</div>
@endsection

@push('scripts')
<script>
    function roleIndexHandler(allPermsList, modulesData) {
        return {
            toasts: [],
            tambahToast(pesan, tipe = 'sukses') {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, pesan, tipe, visible: true });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 4000);
            },

            // Modal Hapus
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
            },

            // Modal Role (Tambah & Edit)
            modalRoleTerbuka: false,
            mode: 'edit',
            formAction: '',
            formNama: '',
            isBawaan: false,
            selectedPerms: [],
            allPerms: allPermsList || [],
            modules: modulesData || {},
            cari: '',
            filterGrup: 'semua',
            sedangMenyimpan: false,
            pesanError: '',

            bukaModalTambah() {
                this.mode = 'tambah';
                this.formAction = '{{ route('roles.store') }}';
                this.formNama = '';
                this.isBawaan = false;
                this.selectedPerms = [];
                this.cari = '';
                this.filterGrup = 'semua';
                this.pesanError = '';
                this.modalRoleTerbuka = true;
            },

            bukaModalEdit(data) {
                this.mode = 'edit';
                this.formAction = data.action;
                this.formNama = data.name || '';
                this.isBawaan = !!data.isBawaan;
                this.selectedPerms = Array.isArray(data.permissions) ? [...data.permissions] : [];
                this.cari = '';
                this.filterGrup = 'semua';
                this.pesanError = '';
                this.modalRoleTerbuka = true;
            },

            tutupModal() {
                this.modalRoleTerbuka = false;
                this.pesanError = '';
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

            countModuleSelected(permNames) {
                return permNames.filter(p => this.selectedPerms.includes(p)).length;
            },

            isModuleAllSelected(permNames) {
                return permNames.length > 0 && permNames.every(p => this.selectedPerms.includes(p));
            },

            toggleModule(permNames) {
                if (this.isModuleAllSelected(permNames)) {
                    this.selectedPerms = this.selectedPerms.filter(p => !permNames.includes(p));
                } else {
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
            },

            simpanRole() {
                if (!this.formNama.trim()) {
                    this.pesanError = 'Nama role wajib diisi.';
                    return;
                }

                this.sedangMenyimpan = true;
                this.pesanError = '';

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                if (this.mode === 'edit') {
                    formData.append('_method', 'PUT');
                }
                formData.append('name', this.formNama.trim());
                this.selectedPerms.forEach(p => {
                    formData.append('permissions[]', p);
                });

                fetch(this.formAction, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const msg = data.errors?.name?.[0] || data.message || 'Terjadi kesalahan saat menyimpan role.';
                        throw new Error(msg);
                    }
                    return data;
                })
                .then(data => {
                    this.sedangMenyimpan = false;
                    this.modalRoleTerbuka = false;
                    if (window.rolesDt) {
                        window.rolesDt.ajax.reload(null, false);
                    }
                    this.tambahToast(data.message || 'Role berhasil disimpan.', 'sukses');
                })
                .catch(err => {
                    this.sedangMenyimpan = false;
                    this.pesanError = err.message || 'Gagal menyimpan role.';
                    this.tambahToast(this.pesanError, 'error');
                });
            }
        };
    }

    $(document).ready(function() {
        window.rolesDt = initSaishoDataTable('rolesTable', {
            serverSide: true,
            ajax: '{{ route('roles.index') }}',
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name', className: 'font-medium text-gray-800 dark:text-white/90' },
                { data: 'permission_html', name: 'permissions_count', searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Belum ada data role'
        });

        // Event delegation untuk tombol edit role dari DataTables
        $(document).on('click', '.btn-edit-role', function(e) {
            e.preventDefault();
            const btn = $(this);
            let perms = btn.attr('data-permissions');
            if (typeof perms === 'string') {
                try { perms = JSON.parse(perms); } catch(err) { perms = []; }
            }
            window.dispatchEvent(new CustomEvent('buka-modal-edit-role', {
                detail: {
                    id: btn.data('id'),
                    name: btn.data('name'),
                    isBawaan: btn.data('bawaan') == '1' || btn.data('bawaan') === true,
                    permissions: perms || [],
                    action: btn.data('action')
                }
            }));
        });
    });
</script>
@endpush