@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Activity Log" />

    <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden space-y-4">
        <div class="px-6 pt-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Activity Log</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Riwayat seluruh aktivitas pengguna sistem.</p>
        </div>

        <div class="sdt-toolbar">
            <div class="sdt-toolbar-left">
                <div class="sdt-search">
                    <span class="sdt-search-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
                    </span>
                    <input type="text" class="sdt-search-input" placeholder="Cari aktivitas...">
                </div>
                <div class="sdt-pagesize">
                    <span>Tampilkan</span>
                    <select><option value="15">15</option><option value="25">25</option><option value="50">50</option></select>
                </div>
            </div>
        </div>

        <div class="sdt-table-wrap">
            <table id="activityLogTable" class="sdt-table">
                <thead>
                    <tr>
                        <th class="py-3" style="width: 18%;">Waktu</th>
                        <th class="py-3" style="width: 15%;">User</th>
                        <th class="py-3" style="width: 12%;">Role</th>
                        <th class="py-3" style="width: 55%;">Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
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
                        <tr>
                            <td class="text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="font-semibold text-gray-800 dark:text-white">{{ $causerNama }}</td>
                            <td>
                                <span class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-400">
                                    {{ $causerRole }}
                                </span>
                            </td>
                            <td class="text-gray-600 dark:text-gray-300 text-sm">{{ $log->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="sdt-footer">
            <div class="sdt-info"></div>
            <div class="sdt-pagination"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('activityLogTable', {
            emptyText: 'Belum ada activity log',
            dtOptions: { pageLength: 15 }
        });
    });
</script>
@endpush