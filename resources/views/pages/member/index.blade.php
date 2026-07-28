@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Member" />
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Member</h3>
        @can('tambah_member')
        <a href="{{ route('member.create') }}"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Member</span>
        </a>
        @endcan
    </div>

    <!-- Unified Responsive Table -->
    <div class="overflow-x-auto p-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <th class="py-3 px-2 sm:hidden w-8 text-center"></th>
                    <th class="py-3">Nama</th>
                    <th class="py-3 hidden md:table-cell">No. HP</th>
                    <th class="py-3 hidden sm:table-cell">Poin</th>
                    <th class="py-3">Level</th>
                    <th class="py-3 text-right">Aksi</th>
                </tr>
            </thead>
            @foreach ($members as $member)
            @php
                $levelClasses = match($member->level) {
                    'Gold'   => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                    'Silver' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
                    default  => 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
                };
            @endphp
            <tbody x-data="{ open: false }" class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">
                    <td class="py-3.5 px-2 sm:hidden text-center">
                        <button @click="open = !open" type="button" class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200" :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                            <span x-text="open ? '−' : '+'"></span>
                        </button>
                    </td>
                    <td class="py-3.5 font-medium text-gray-800 dark:text-white/90">{{ $member->nama }}</td>
                    <td class="py-3.5 hidden md:table-cell text-gray-600 dark:text-gray-400">{{ $member->no_hp }}</td>
                    <td class="py-3.5 hidden sm:table-cell text-gray-600 dark:text-gray-400">{{ $member->poin }}</td>
                    <td class="py-3.5">
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $levelClasses }}">
                            {{ $member->level }}
                        </span>
                    </td>
                    <td class="py-3.5 text-right space-x-1.5 whitespace-nowrap">
                        @can('edit_member')
                        <a href="{{ route('member.edit', $member) }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">Edit</a>
                        @endcan
                        @can('hapus_member')
                        <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline delete-form">
                            @csrf @method('DELETE')
                            <button type="button" class="text-red-500 font-semibold hover:underline text-xs sm:text-sm delete-btn" data-name="{{ $member->nama }}">Hapus</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                <!-- Expandable child row -->
                <tr x-show="open" x-cloak x-transition class="bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                    <td colspan="6" class="p-3.5">
                        <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300">
                            <div><span class="text-gray-400">No. HP:</span> <span class="font-semibold text-gray-800 dark:text-white">{{ $member->no_hp }}</span></div>
                            <div><span class="text-gray-400">Poin:</span> <span class="font-bold text-amber-700 dark:text-amber-400">{{ $member->poin }}</span></div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirm Delete Member
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('.delete-form');
                const memberName = this.getAttribute('data-name');

                Swal.fire({
                    title: 'Hapus Member?',
                    text: `Apakah Anda yakin ingin menghapus member "${memberName}"? Tindakan ini tidak dapat dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection