@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Kelola Role" />
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
    x-data="{
        modalHapusTerbuka: false,
        formTerpilih: null,
        namaHapus: '',
        bukaModalHapus(form, nama) {
            this.formTerpilih = form;
            this.namaHapus = nama;
            this.modalHapusTerbuka = true;
        },
        konfirmasiHapus() {
            if (this.formTerpilih) {
                this.formTerpilih.submit();
            }
            this.modalHapusTerbuka = false;
        }
    }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Role</h3>
        @can('tambah_role')
        <a href="{{ route('roles.create') }}"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Role</span>
        </a>
        @endcan
    </div>

    <!-- Unified Responsive Table -->
    <div class="overflow-x-auto p-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <th class="py-3">Nama Role</th>
                    <th class="py-3">Jumlah Permission</th>
                    <th class="py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">
                    <td class="py-3 font-medium text-gray-800 dark:text-white/90">{{ $role->name }}</td>
                    <td class="py-3">
                        <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:text-amber-400">
                            {{ $role->permissions_count }} permission
                        </span>
                    </td>
                    <td class="py-3 text-right space-x-1.5 whitespace-nowrap">
                        @can('edit_role')
                        <a href="{{ route('roles.edit', $role) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                        @endcan
                        @can('hapus_role')
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="button"
                                @click="bukaModalHapus($el.closest('form'), '{{ addslashes($role->name) }}')"
                                class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">Hapus</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ========== MODAL: KONFIRMASI HAPUS ROLE ========== -->
    <div x-show="modalHapusTerbuka" x-cloak x-transition
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapusTerbuka = false"
            class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                    ⚠
                </div>
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Role?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Role <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="namaHapus"></span>
                    akan dihapus dan tidak dapat dipulihkan kembali.
                </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                <button type="button" @click="modalHapusTerbuka = false"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                <button type="button" @click="konfirmasiHapus()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection