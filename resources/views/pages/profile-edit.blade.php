@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Profil" parentTitle="Profil Saya" :parentUrl="route('profile.edit')" />

<div class="space-y-6">

    @if (session('status') === 'profile-updated')
        <div class="rounded-lg bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
            Profil berhasil diperbarui.
        </div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="rounded-lg bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
            Password berhasil diperbarui.
        </div>
    @endif

    {{-- Info Akun --}}
    <x-common.component-card title="Informasi Akun">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('name')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Username</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('username')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Email <span class="text-xs text-gray-400"></span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('email')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-common.component-card>

    {{-- Ganti Password --}}
    <x-common.component-card title="Ganti Password">
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Password Saat Ini</label>
                    <input type="password" name="current_password"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('current_password', 'updatePassword')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Password Baru</label>
                    <input type="password" name="password"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('password', 'updatePassword')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Update Password
                </button>
            </div>
        </form>
    </x-common.component-card>

    {{-- Hapus Akun --}}
    <div x-data="{ modalHapusTerbuka: false }">
        <x-common.component-card title="Hapus Akun">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Setelah akun dihapus, semua data akan hilang permanen. Pastikan sudah yakin sebelum melanjutkan.
            </p>
            <form x-ref="formHapusAkun" action="{{ route('profile.destroy') }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="max-w-sm">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Password</label>
                    <input type="password" name="password"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('password', 'userDeletion')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                <div class="mt-6">
                    <button type="button" @click="modalHapusTerbuka = true" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                        Hapus Akun Saya
                    </button>
                </div>
            </form>
        </x-common.component-card>

        <!-- MODAL HAPUS AKUN -->
        <div x-show="modalHapusTerbuka" x-cloak x-transition class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalHapusTerbuka = false" class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">⚠</div>
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Hapus Akun?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Yakin mau hapus akun? Aksi ini tidak bisa dibatalkan.
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">
                    <button type="button" @click="modalHapusTerbuka = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="$refs.formHapusAkun.submit(); modalHapusTerbuka = false" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">Hapus Akun Saya</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection