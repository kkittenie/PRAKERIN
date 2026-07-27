@extends('layouts.app')

@section('content')
<div x-data="{ openRole: null }" class="flex flex-col gap-4">

    <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Kelola Pengguna</h3>
        @can('tambah_user')
        <a href="{{ route('users.create') }}"
            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Tambah Pengguna
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800">{{ session('error') }}</div>
    @endif

    @forelse ($grouped as $roleName => $usersInRole)
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <button @click="openRole = (openRole === '{{ $roleName }}' ? null : '{{ $roleName }}')"
            class="w-full flex items-center justify-between p-5 text-left hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-600 dark:text-brand-400">
                    {{ $roleName }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $usersInRole->count() }} akun</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="openRole === '{{ $roleName }}' ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div x-show="openRole === '{{ $roleName }}'" x-transition class="border-t border-gray-100 dark:border-gray-800">
            <div class="overflow-x-auto p-5">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th class="py-3">Nama</th>
                            <th class="py-3">Username</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usersInRole as $user)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-3 font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</td>
                            <td class="py-3 text-gray-600 dark:text-gray-400">{{ $user->username }}</td>
                            <td class="py-3 text-right">
                                @can('edit_user')
                                <a href="{{ route('users.edit', $user) }}" class="text-brand-500 hover:underline">Edit</a>
                                @endcan
                                @can('hapus_user')
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin hapus pengguna ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-500 hover:underline">Hapus</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
        Belum ada data pengguna.
    </div>
    @endforelse

</div>
@endsection