@extends('layouts.app')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Role</h3>
        @can('tambah_role')
        <a href="{{ route('roles.create') }}"
            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Tambah Role
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mx-6 mt-4 rounded-lg bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mx-6 mt-4 rounded-lg bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800">{{ session('error') }}</div>
    @endif

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
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-3 font-medium text-gray-800 dark:text-white/90">{{ $role->name }}</td>
                    <td class="py-3 text-gray-600 dark:text-gray-400">{{ $role->permissions_count }} permission</td>
                    <td class="py-3 text-right">
                        @can('edit_role')
                        <a href="{{ route('roles.edit', $role) }}" class="text-brand-500 hover:underline">Edit</a>
                        @endcan
                        @can('hapus_role')
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                            onsubmit="return confirm('Yakin hapus role ini?')">
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