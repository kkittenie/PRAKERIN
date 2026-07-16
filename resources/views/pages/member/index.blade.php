@extends('layouts.app')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Member</h3>
        @can('tambah_member')
        <a href="{{ route('member.create') }}"
            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Tambah Member
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mx-6 mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto p-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <th class="py-3">Nama</th>
                    <th class="py-3">No. HP</th>
                    <th class="py-3">Poin</th>
                    <th class="py-3">Level</th>
                    <th class="py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($members as $member)
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-3 font-medium text-gray-800 dark:text-white/90">{{ $member->nama }}</td>
                    <td class="py-3 text-gray-600 dark:text-gray-400">{{ $member->no_hp }}</td>
                    <td class="py-3 text-gray-600 dark:text-gray-400">{{ $member->poin }}</td>
                    <td class="py-3">
                        <span class="rounded-full px-3 py-1 text-xs font-medium
                            {{ $member->level === 'Gold' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->level }}
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        @can('edit_member')
                        <a href="{{ route('member.edit', $member) }}" class="text-brand-500 hover:underline">Edit</a>
                        @endcan
                        @can('hapus_member')
                        <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline"
                            onsubmit="return confirm('Yakin hapus member ini?')">
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