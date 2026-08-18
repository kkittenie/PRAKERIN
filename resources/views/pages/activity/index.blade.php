@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Activity Log" />

    <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                Activity Log
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Riwayat seluruh aktivitas pengguna sistem.
            </p>
        </div>

        <!-- Unified Responsive Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-2 py-3 sm:hidden w-8 text-center"></th>
                        <th class="px-4 sm:px-6 py-3 text-left">Waktu</th>
                        <th class="px-4 sm:px-6 py-3 text-left">User</th>
                        <th class="px-6 py-3 text-left hidden md:table-cell">Role</th>
                        <th class="px-6 py-3 text-left hidden sm:table-cell">Aktivitas</th>
                    </tr>
                </thead>
                @forelse($logs as $log)
                    <tbody x-data="{ open: false }"
                        class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                        <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">
                            <td class="px-2 py-3.5 sm:hidden text-center">
                                <button @click="open = !open" type="button"
                                    class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                    :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">
                                    <span x-text="open ? '−' : '+'"></span>
                                </button>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5 text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                                {{ $log->created_at->format('d M Y H:i') }}</td>
                            @php
                                $causerNama = $log->causer?->name ?? $log->causer?->nama ?? ($log->causer ? 'User' : 'Sistem');
                                $causerRole = '-';
                                if ($log->causer) {
                                    if ($log->causer instanceof \App\Models\Member) {
                                        $causerRole = 'Member';
                                    } elseif (isset($log->causer->roles)) {
                                        $causerRole = $log->causer->roles->first()?->name ?? 'User';
                                    }
                                }
                            @endphp
                            <td class="px-4 sm:px-6 py-3.5 font-semibold text-gray-800 dark:text-white">
                                {{ $causerNama }}</td>
                            <td class="px-6 py-3.5 hidden md:table-cell">
                                <span
                                    class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400">
                                    {{ $causerRole }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 hidden sm:table-cell text-gray-600 dark:text-gray-300">
                                {{ $log->description }}</td>
                        </tr>
                        <!-- Expandable child row -->
                        <tr x-show="open" x-cloak x-transition class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">
                            <td colspan="5" class="p-3.5">
                                <div class="grid grid-cols-1 gap-2 text-gray-600 dark:text-gray-300">
                                    <div>
                                        <span class="text-gray-400">Role:</span>
                                        <span
                                            class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 ml-1">
                                            {{ $causerRole }}
                                        </span>
                                    </div>
                                    <div><span class="text-gray-400">Aktivitas:</span> <span
                                            class="font-semibold text-gray-800 dark:text-white">{{ $log->description }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400">Belum ada activity log.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">

            {{ $logs->links() }}

        </div>

    </div>

@endsection