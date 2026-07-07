@extends('layouts.app')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Pengguna</h3>
        @can('tambah_user')
        <a href="{{ route('users.create') }}"
            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Tambah Pengguna
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mx-6 mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mx-6 mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto p-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <th class="py-3">Nama</th>
                    <th class="py-3">Username</th>
                    <th class="py-3">Role</th>
                    <th class="py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-3 font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</td>
                    <td class="py-3 text-gray-600 dark:text-gray-400">{{ $user->username }}</td>
                    <td class="py-3">
                        @foreach ($user->roles as $role)
                            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600">{{ $role->name }}</span>
                        @endforeach
                    </td>
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
@endsection