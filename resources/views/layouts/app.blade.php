<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Saisho POS </title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{--
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        document.body?.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        document.body?.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <!-- Global Alert Helper (Alpine.js) -->
    <script>
        window.appAlert = function(message, title) {
            window.dispatchEvent(new CustomEvent('app-alert', {
                detail: {
                    message: typeof message === 'object' ? JSON.stringify(message) : String(message ?? ''),
                    title: title || 'Perhatian'
                }
            }));
        };
        window.tampilkanAlert = window.appAlert;
        window.alert = function(message) {
            window.appAlert(message, 'Perhatian');
        };
    </script>

    <!-- jQuery + DataTables + Responsive Extension -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom DataTables Styling -->
    <style>
        .dataTables_wrapper { font-family: inherit; }
        table.dataTable { border-collapse: collapse !important; border-spacing: 0 !important; width: 100% !important; }
        table.dataTable thead th, table.dataTable thead td { border-bottom: none !important; }
        table.dataTable.no-footer { border-bottom: none !important; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { display: none !important; }
        table.dataTable thead .sorting, table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc_disabled,
        table.dataTable thead .sorting_desc_disabled { background-image: none !important; cursor: pointer; }

        /* ===== CUSTOM TOOLBAR ===== */
        .sdt-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.875rem; padding: 1.25rem 1.5rem; }
        .sdt-toolbar-left { display: flex; flex-wrap: wrap; align-items: center; gap: 0.875rem; flex: 1; min-width: 0; }
        .sdt-toolbar-right { display: flex; align-items: center; gap: 0.875rem; flex-shrink: 0; }
        @media (max-width: 639px) {
            .sdt-toolbar { flex-direction: column; align-items: stretch; }
            .sdt-toolbar-right { justify-content: stretch; }
            .sdt-toolbar-right > * { flex: 1; }
        }

        /* Search */
        .sdt-search { position: relative; width: 100%; max-width: 320px; }
        @media (max-width: 639px) { .sdt-search { max-width: 100%; } }
        .sdt-search-icon { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
        .sdt-search-input {
            width: 100%; height: 2.75rem; padding: 0 1rem 0 2.75rem;
            border: 1px solid #d1d5db; border-radius: 0.75rem;
            background: #fff; color: #111827; font-size: 0.9375rem;
            outline: none; transition: all 150ms; font-weight: 500;
        }
        .sdt-search-input::placeholder { color: #9ca3af; }
        .sdt-search-input:focus { border-color: #b45309; box-shadow: 0 0 0 2px rgba(180,83,9,0.2); }
        .dark .sdt-search-input { background: #111827; border-color: #374151; color: #f9fafb; }
        .dark .sdt-search-input:focus { border-color: #d97706; box-shadow: 0 0 0 2px rgba(217,119,6,0.25); }

        /* Page Size */
        .sdt-pagesize {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem; color: #4b5563; font-weight: 500;
        }
        .sdt-pagesize select {
            height: 2.75rem; padding: 0 2.25rem 0 0.875rem;
            border: 1px solid #d1d5db; border-radius: 0.75rem;
            background: #fff; color: #111827; font-size: 0.875rem; font-weight: 600;
            outline: none; cursor: pointer; transition: all 150ms;
            appearance: auto;
        }
        .sdt-pagesize select:focus { border-color: #b45309; box-shadow: 0 0 0 2px rgba(180,83,9,0.2); }
        .dark .sdt-pagesize { color: #9ca3af; }
        .dark .sdt-pagesize select { background: #111827; border-color: #374151; color: #f9fafb; }

        /* ===== CUSTOM TABLE STYLES ===== */
        .sdt-table-wrap { overflow-x: auto; width: 100%; }
        .sdt-table { width: 100% !important; text-align: left; font-size: 0.875rem; }
        .sdt-table thead tr {
            border-bottom: 2px solid #e5e7eb;
            background: #f9fafb;
        }
        .dark .sdt-table thead tr { border-color: #374151; background: rgba(31,41,55,0.7); }
        .sdt-table thead th {
            padding: 0.75rem 0.875rem !important;
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #4b5563; white-space: nowrap; user-select: none;
            position: relative;
        }
        .dark .sdt-table thead th { color: #9ca3af; }

        /* Alignment synchronization */
        .sdt-table thead th.text-right,
        .sdt-table tbody td.text-right {
            text-align: right !important;
        }
        .sdt-table thead th.text-center,
        .sdt-table tbody td.text-center {
            text-align: center !important;
        }
        .sdt-table thead th.text-left,
        .sdt-table tbody td.text-left {
            text-align: left !important;
        }

        /* Reserve right padding for default DataTables sort indicators */
        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            padding-right: 2rem !important;
        }

        /* Body rows */
        .sdt-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 150ms;
        }
        .dark .sdt-table tbody tr { border-color: #1f2937; }
        .sdt-table tbody tr:hover { background: rgba(180,83,9,0.03); }
        .dark .sdt-table tbody tr:hover { background: rgba(217,119,6,0.05); }
        .sdt-table tbody td {
            padding: 0.75rem 0.875rem;
            color: #1f2937;
            font-size: 0.875rem;
            vertical-align: middle;
        }
        .dark .sdt-table tbody td { color: #e5e7eb; }
        .sdt-table tbody td img {
            width: 2.75rem !important;
            height: 2.75rem !important;
            max-width: 2.75rem !important;
            max-height: 2.75rem !important;
            object-fit: cover !important;
            object-position: center !important;
            aspect-ratio: 1 / 1 !important;
            flex-shrink: 0 !important;
            display: block;
        }
        .sdt-table tbody td.text-center img,
        .sdt-table tbody td img.mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .flex > img,
        .inline-flex > img,
        .sdt-table tbody td .flex img,
        .sdt-table tbody td .inline-flex img {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /* ===== EXPANDABLE RESPONSIVE CHILD ROWS FOR MOBILE ===== */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
            position: relative !important;
            padding-left: 2.35rem !important;
            text-align: left !important;
            white-space: nowrap !important;
            cursor: pointer;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
            position: absolute !important;
            top: 50% !important;
            left: 0.625rem !important;
            transform: translateY(-50%) !important;
            margin-top: 0 !important;
            height: 1.25rem !important;
            width: 1.25rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 9999px !important;
            background-color: #b45309 !important;
            color: #ffffff !important;
            content: '+' !important;
            font-size: 0.875rem !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.12) !important;
            border: none !important;
            transition: all 150ms ease !important;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
            content: '−' !important;
            background-color: #4b5563 !important;
        }
        table.dataTable tbody tr.child {
            background: #f9fafb !important;
        }
        .dark table.dataTable tbody tr.child {
            background: rgba(17, 24, 39, 0.6) !important;
        }
        table.dataTable tbody tr.child td.child {
            padding: 0.75rem 1rem 1rem 1rem !important;
        }
        table.dataTable tbody tr.child ul.dtr-details {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem 1rem !important;
            padding: 0.875rem 1rem !important;
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.875rem !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
            width: 100%;
        }
        @media (min-width: 640px) {
            table.dataTable tbody tr.child ul.dtr-details {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        .dark table.dataTable tbody tr.child ul.dtr-details {
            background: #111827 !important;
            border-color: #374151 !important;
            box-shadow: none !important;
        }
        table.dataTable tbody tr.child ul.dtr-details > li {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.25rem !important;
            border-bottom: 1px solid #f3f4f6 !important;
            padding-bottom: 0.5rem !important;
            min-width: 0;
            overflow: hidden;
        }
        .dark table.dataTable tbody tr.child ul.dtr-details > li {
            border-color: #1f2937 !important;
        }
        table.dataTable tbody tr.child ul.dtr-details > li:last-child {
            border-bottom: none !important;
        }
        table.dataTable tbody tr.child span.dtr-title {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            color: #6b7280 !important;
        }
        .dark table.dataTable tbody tr.child span.dtr-title {
            color: #9ca3af !important;
        }
        table.dataTable tbody tr.child span.dtr-data {
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #111827 !important;
        }
        .dark table.dataTable tbody tr.child span.dtr-data {
            color: #f9fafb !important;
        }

        /* ===== CUSTOM FOOTER ===== */
        .sdt-footer {
            display: flex; flex-wrap: wrap; align-items: center;
            justify-content: space-between; gap: 1rem;
            padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb;
        }
        .dark .sdt-footer { border-color: #1f2937; }
        @media (max-width: 639px) {
            .sdt-footer { flex-direction: column; align-items: center; text-align: center; }
        }
        .sdt-info { font-size: 0.875rem; color: #4b5563; font-weight: 500; }
        .dark .sdt-info { color: #9ca3af; }

        /* Pagination */
        .sdt-pagination { display: flex; align-items: center; gap: 0.35rem; }
        .sdt-pagination button {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2.25rem; height: 2.25rem; padding: 0 0.625rem;
            font-size: 0.875rem; font-weight: 600;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            background: #fff; color: #374151; cursor: pointer;
            transition: all 150ms;
        }
        .sdt-pagination button:hover:not(:disabled):not(.active) {
            background: #fef3c7; border-color: #d97706; color: #92400e;
        }
        .sdt-pagination button.active {
            background: #b45309; border-color: #b45309; color: #fff; font-weight: 700;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .sdt-pagination button:disabled { opacity: 0.4; cursor: not-allowed; }
        .dark .sdt-pagination button { background: #111827; border-color: #374151; color: #d1d5db; }
        .dark .sdt-pagination button:hover:not(:disabled):not(.active) {
            background: rgba(217,119,6,0.15); border-color: #d97706; color: #fbbf24;
        }
        .dark .sdt-pagination button.active { background: #d97706; border-color: #d97706; color: #fff; }

        /* ===== EMPTY STATE ===== */
        .sdt-empty { padding: 3.5rem 1.5rem; text-align: center; }
        .sdt-empty-icon {
            display: flex; align-items: center; justify-content: center;
            width: 4rem; height: 4rem; margin: 0 auto 1rem;
            border-radius: 9999px; background: #f3f4f6; color: #9ca3af;
        }
        .dark .sdt-empty-icon { background: #1f2937; color: #6b7280; }
        .sdt-empty-title { font-size: 1rem; font-weight: 700; color: #4b5563; margin-bottom: 0.25rem; }
        .dark .sdt-empty-title { color: #9ca3af; }
        .sdt-empty-desc { font-size: 0.875rem; color: #9ca3af; }
        .dark .sdt-empty-desc { color: #6b7280; }

        /* ===== LOADING STATE ===== */
        .sdt-loading {
            position: relative; min-height: 120px;
        }
        .sdt-loading::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(255,255,255,0.7);
            display: flex; align-items: center; justify-content: center;
            z-index: 10; border-radius: 0.5rem;
        }
        .dark .sdt-loading::after { background: rgba(17,24,39,0.7); }
    </style>

    <!-- Global DataTable initializer -->
    <script>
        // Disable DataTables default browser alert popup modal for AJAX errors
        if (typeof $.fn.dataTable !== 'undefined') {
            $.fn.dataTable.ext.errMode = 'none';
        }

        // Global jQuery AJAX setup with CSRF token and XMLHttpRequest header
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        function initSaishoDataTable(tableId, options = {}) {
            const $table = $('#' + tableId);
            if (!$table.length) return null;

            const wrapper = $table.closest('.sdt-card');
            const searchInput = wrapper.find('.sdt-search-input');
            const pageSizeSelect = wrapper.find('.sdt-pagesize select');
            const infoEl = wrapper.find('.sdt-info');
            const paginationEl = wrapper.find('.sdt-pagination');

            const baseOptions = {
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                responsive: true,
                pageLength: 10,
                lengthChange: false,
                dom: 'lrtip',
                language: { emptyTable: '', zeroRecords: '' },
                order: options.order || [],
                columnDefs: [{ responsivePriority: 1, targets: 0 }, { responsivePriority: 1, targets: -1 }, { responsivePriority: 2, targets: 1 }].concat(options.columnDefs || []),
                drawCallback: function() {
                    renderPagination(this.api());
                    renderInfo(this.api());
                    renderEmptyState(this.api(), options.emptyText || 'Belum ada data');
                    if (typeof options.onDraw === 'function') {
                        options.onDraw(this.api());
                    }
                }
            };

            if (options.serverSide) {
                baseOptions.serverSide = true;
                baseOptions.processing = true;

                let ajaxConfig = options.ajax;
                if (typeof ajaxConfig === 'string') {
                    ajaxConfig = { url: ajaxConfig };
                }

                baseOptions.ajax = $.extend({}, ajaxConfig, {
                    error: function(xhr, error, thrown) {
                        console.warn('DataTables AJAX info:', error, thrown);
                        if (xhr && (xhr.status === 401 || xhr.status === 419)) {
                            window.location.reload();
                        }
                        if (ajaxConfig && typeof ajaxConfig.error === 'function') {
                            ajaxConfig.error(xhr, error, thrown);
                        }
                    }
                });

                if (options.columns) {
                    baseOptions.columns = options.columns;
                }
            }

            const dt = $table.DataTable(Object.assign(baseOptions, options.dtOptions || {}));

            $table.on('error.dt', function(e, settings, techNote, message) {
                console.warn('DataTables error.dt handled quietly:', message);
            });

            // Search binding with debounce
            if (searchInput.length) {
                let searchTimer = null;
                searchInput.off('input keyup search').on('input keyup search', function() {
                    const val = this.value;
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        dt.search(val).draw();
                    }, 300);
                });
            }

            // Page size binding
            if (pageSizeSelect.length) {
                pageSizeSelect.off('change').on('change', function() {
                    dt.page.len(parseInt(this.value)).draw();
                });
            }

            function renderInfo(api) {
                if (!infoEl.length) return;
                const info = api.page.info();
                if (info.recordsDisplay === 0) { infoEl.text(''); return; }
                infoEl.text('Menampilkan ' + (info.start + 1) + '–' + info.end + ' dari ' + info.recordsDisplay + ' data');
            }

            function renderPagination(api) {
                if (!paginationEl.length) return;
                const info = api.page.info();
                if (info.pages <= 1) { paginationEl.html(''); return; }

                let html = '<button class="sdt-pg-prev" ' + (info.page === 0 ? 'disabled' : '') + '>&lsaquo;</button>';
                let start = Math.max(0, info.page - 2);
                let end = Math.min(info.pages, start + 5);
                if (end - start < 5) start = Math.max(0, end - 5);
                for (let i = start; i < end; i++) {
                    html += '<button class="' + (i === info.page ? 'active' : '') + '" data-page="' + i + '">' + (i + 1) + '</button>';
                }
                html += '<button class="sdt-pg-next" ' + (info.page === info.pages - 1 ? 'disabled' : '') + '>&rsaquo;</button>';
                paginationEl.html(html);

                paginationEl.find('button[data-page]').off('click').on('click', function() { dt.page(parseInt($(this).data('page'))).draw(false); });
                paginationEl.find('.sdt-pg-prev').off('click').on('click', function() { dt.page('previous').draw(false); });
                paginationEl.find('.sdt-pg-next').off('click').on('click', function() { dt.page('next').draw(false); });
            }

            function renderEmptyState(api, text) {
                const info = api.page.info();
                const tbody = $table.find('tbody');
                tbody.find('.sdt-empty-row').remove();
                if (info.recordsDisplay === 0) {
                    const cols = $table.find('thead th').length || 1;
                    tbody.append(
                        '<tr class="sdt-empty-row"><td colspan="' + cols + '">' +
                        '<div class="sdt-empty">' +
                        '<div class="sdt-empty-icon"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg></div>' +
                        '<div class="sdt-empty-title">' + text + '</div>' +
                        '<div class="sdt-empty-desc">Data akan muncul di sini setelah ditambahkan.</div>' +
                        '</div></td></tr>'
                    );
                }
            }

            if (!options.serverSide) {
                dt.draw();
            }

            return dt;
        }
    </script>

</head>

<body x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader />
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 transition-all duration-300 ease-in-out" :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

    @if (session('success') || session('error'))
        <div x-data="{ show: false }" x-init="
                show = true;
                setTimeout(() => show = false, 3000);
            " x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0" class="fixed top-5 right-5 z-99999">
            <div
                class="text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3 min-w-[280px] {{ session('success') ? 'bg-green-600' : 'bg-red-600' }}">
                <span class="font-semibold text-lg">{{ session('success') ? '✓' : '⚠' }}</span>
                <span class="text-sm">{{ session('success') ?? session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Global Alert Modal (Alpine.js) - Pengganti alert() bawaan browser --}}
    <div x-data="{
            open: false,
            title: 'Perhatian',
            message: '',
            show(msg, ttl = 'Perhatian') {
                this.message = msg || '';
                this.title = ttl || 'Perhatian';
                this.open = true;
            },
            close() {
                this.open = false;
            }
         }"
         @app-alert.window="show($event.detail?.message || $event.detail, $event.detail?.title || 'Perhatian')"
         @keydown.escape.window="close()"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div @click.away="close()"
             class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800 transition-all text-center">
            <div class="p-6">
                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base" x-text="title"></h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed" x-text="message"></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                <button type="button"
                        @click="close()"
                        class="w-full py-2.5 px-5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl transition shadow-xs cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    @include('layouts.fcm-admin-register')

    {{-- Pulihkan halaman terakhir setelah layar diputar (khusus aplikasi Android / WebView).
         Saat layar dimiringkan, WebView dibuat ulang dan memuat URL awal (dashboard). Selama halaman aktif,
         URL-nya dicatat berkala; kalau dashboard dibuka tanpa referrer (bukan hasil klik) tepat setelah
         halaman lain masih aktif, user dikembalikan ke halaman tadi. --}}
    <script>
        (function () {
            if (!window.Android) return;
            const KUNCI = 'pos_halaman_terakhir';
            const KUNCI_PULIH = 'pos_halaman_dipulihkan';
            const jalurDashboard = @json(parse_url(route('dashboard'), PHP_URL_PATH));
            const sekarang = Date.now();

            try {
                if (location.pathname === jalurDashboard) {
                    const simpanan = JSON.parse(localStorage.getItem(KUNCI) || 'null');
                    const terakhirPulih = parseInt(sessionStorage.getItem(KUNCI_PULIH) || '0', 10);
                    const dibukaTanpaKlik = document.referrer === '';
                    const barusanAktif = simpanan && (sekarang - simpanan.t) < 15000;
                    const sehost = simpanan && simpanan.u && new URL(simpanan.u).origin === location.origin;

                    if (dibukaTanpaKlik && barusanAktif && sehost && simpanan.u !== location.href && (sekarang - terakhirPulih) > 10000) {
                        sessionStorage.setItem(KUNCI_PULIH, String(sekarang));
                        location.replace(simpanan.u);
                        return;
                    }
                    localStorage.removeItem(KUNCI);
                    return;
                }
            } catch (e) {}

            // Catat halaman yang sedang aktif (bukan dashboard) sebagai tujuan pemulihan
            const catat = () => {
                try { localStorage.setItem(KUNCI, JSON.stringify({ u: location.href, t: Date.now() })); } catch (e) {}
            };
            catat();
            setInterval(catat, 3000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) catat(); });
        })();
    </script>

</body>

@stack('scripts')

</html>