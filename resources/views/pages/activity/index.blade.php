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
                        <th class="py-3" style="width: 22%;">Waktu</th>
                        <th class="py-3" style="width: 18%;">User</th>
                        <th class="py-3 text-center" style="width: 15%;">Role</th>
                        <th class="py-3" style="width: 45%;">Aktivitas</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
            serverSide: true,
            ajax: '{{ route('activity-log') }}',
            order: [[0, 'desc']],
            columns: [
                { data: 'created_at', name: 'created_at', className: 'text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap' },
                { data: 'causer_nama', name: 'causer_nama', className: 'font-semibold text-gray-800 dark:text-white whitespace-nowrap' },
                { data: 'causer_role_html', name: 'causer_role_html', className: 'text-center whitespace-nowrap' },
                { data: 'description', name: 'description', className: 'text-gray-600 dark:text-gray-300 text-xs sm:text-sm break-words' }
            ],
            emptyText: 'Belum ada activity log',
            dtOptions: { pageLength: 15 }
        });
    });
</script>
@endpush