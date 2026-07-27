@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Activity Log" />

<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">

    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white">
            Activity Log
        </h2>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Riwayat seluruh aktivitas pengguna sistem.
        </p>
    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>

                <tr class="bg-gray-50 dark:bg-gray-800">

                    <th class="px-6 py-3 text-left">
                        Waktu
                    </th>

                    <th class="px-6 py-3 text-left">
                        User
                    </th>

                    <th class="px-6 py-3 text-left">
                        Role
                    </th>

                    <th class="px-6 py-3 text-left">
                        Aktivitas
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($logs as $log)

                    <tr class="border-t border-gray-100 dark:border-gray-800">

                        <td class="px-6 py-4">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </td>

                        <td class="px-6 py-4">

                            {{ $log->causer?->name ?? '-' }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $log->causer?->roles->first()?->name ?? '-' }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $log->description }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center py-10 text-gray-400">

                            Belum ada activity log.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">

        {{ $logs->links() }}

    </div>

</div>

@endsection