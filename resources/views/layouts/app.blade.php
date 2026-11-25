<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Daisy Bakery Admin</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme || 'light';
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
                        document.body?.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                        document.body?.classList.remove('dark');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply saved theme immediately to prevent flash -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || 'light';
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

    <!-- Custom DataTables Styling (Daisy Bakery Theme) -->
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
            .sdt-toolbar { flex-direction: column; align-items: stretch; padding: 1rem; }
            .sdt-toolbar-right { justify-content: stretch; }
            .sdt-toolbar-right > * { flex: 1; }
        }

        /* Search */
        .sdt-search { position: relative; width: 100%; max-width: 320px; }
        @media (max-width: 639px) { .sdt-search { max-width: 100%; } }
        .sdt-search-icon { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: #8A766B; pointer-events: none; }
        .sdt-search-input {
            width: 100%; height: 2.6rem; padding: 0 1rem 0 2.65rem;
            border: 1px solid rgba(60, 36, 21, 0.18); border-radius: 0.875rem;
            background: #FAF5E8; color: #231815; font-size: 0.875rem;
            outline: none; transition: all 150ms; font-weight: 500;
        }
        .sdt-search-input::placeholder { color: #9A877B; }
        .sdt-search-input:focus { background: #FFFDF9; border-color: #8E1E26; box-shadow: 0 0 0 3px rgba(142, 30, 38, 0.14); }
        .dark .sdt-search-input { background: #1A120F; border-color: rgba(250, 245, 232, 0.16); color: #FAF5E8; }
        .dark .sdt-search-input:focus { border-color: #EACE80; box-shadow: 0 0 0 3px rgba(234, 206, 128, 0.16); }

        /* Page Size & Inline Filter Wrapper */
        .sdt-pagesize {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.8125rem; color: #6B574C; font-weight: 600;
        }
        .sdt-pagesize select {
            height: 2.6rem; padding: 0 2.25rem 0 0.875rem;
            border: 1px solid rgba(60, 36, 21, 0.18); border-radius: 0.875rem;
            background: #FFFDF9; color: #3A221B; font-size: 0.875rem; font-weight: 600;
            outline: none; cursor: pointer; transition: all 150ms;
        }
        .sdt-pagesize select:focus { border-color: #8E1E26; box-shadow: 0 0 0 3px rgba(142, 30, 38, 0.14); }
        .dark .sdt-pagesize { color: #C5B5A8; }
        .dark .sdt-pagesize select { background: #2E211C; border-color: rgba(250, 245, 232, 0.16); color: #FAF5E8; }

        /* ===== CUSTOM TABLE STYLES ===== */
        .sdt-table-wrap { overflow-x: auto; width: 100%; }
        .sdt-table { width: 100% !important; text-align: left; font-size: 0.875rem; }
        .sdt-table thead tr {
            border-top: 1px solid rgba(60, 36, 21, 0.08);
            border-bottom: 1.5px solid rgba(60, 36, 21, 0.12);
            background: #F5EDE0;
        }
        .dark .sdt-table thead tr { border-color: rgba(250, 245, 232, 0.10); background: #2E211C; }
        .sdt-table thead th {
            padding: 0.825rem 0.9rem !important;
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #5C4338; white-space: nowrap; user-select: none;
            position: relative;
        }
        .dark .sdt-table thead th { color: #EACE80; }

        .sdt-table thead th.text-right, .sdt-table tbody td.text-right { text-align: right !important; }
        .sdt-table thead th.text-center, .sdt-table tbody td.text-center { text-align: center !important; }
        .sdt-table thead th.text-left, .sdt-table tbody td.text-left { text-align: left !important; }

        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            padding-right: 2rem !important;
        }

        /* Body rows */
        .sdt-table tbody tr {
            border-bottom: 1px solid rgba(60, 36, 21, 0.08);
            transition: background 150ms;
        }
        .dark .sdt-table tbody tr { border-color: rgba(250, 245, 232, 0.07); }
        .sdt-table tbody tr:hover { background: rgba(142, 30, 38, 0.035); }
        .dark .sdt-table tbody tr:hover { background: rgba(234, 206, 128, 0.06); }
        .sdt-table tbody td {
            padding: 0.825rem 0.9rem;
            color: #231815;
            font-size: 0.875rem;
            vertical-align: middle;
        }
        .dark .sdt-table tbody td { color: #F5EFE6; }
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
            height: 1.3rem !important;
            width: 1.3rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 9999px !important;
            background-color: #8E1E26 !important;
            color: #FAF5E8 !important;
            content: '+' !important;
            font-size: 0.875rem !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            box-shadow: 0 2px 4px rgba(142, 30, 38, 0.25) !important;
            border: none !important;
            transition: all 150ms ease !important;
        }
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
            content: '−' !important;
            background-color: #3A221B !important;
        }
        table.dataTable tbody tr.child { background: #FAF5E8 !important; }
        .dark table.dataTable tbody tr.child { background: rgba(26, 18, 15, 0.7) !important; }
        table.dataTable tbody tr.child td.child { padding: 0.75rem 1rem 1rem 1rem !important; }
        table.dataTable tbody tr.child ul.dtr-details {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem 1rem !important;
            padding: 0.875rem 1rem !important;
            background: #FFFDF9 !important;
            border: 1px solid rgba(60, 36, 21, 0.14) !important;
            border-radius: 1rem !important;
            box-shadow: 0 2px 8px rgba(58, 34, 27, 0.05) !important;
            width: 100%;
        }
        @media (min-width: 640px) {
            table.dataTable tbody tr.child ul.dtr-details {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
        .dark table.dataTable tbody tr.child ul.dtr-details {
            background: #241A16 !important;
            border-color: rgba(250, 245, 232, 0.12) !important;
            box-shadow: none !important;
        }
        table.dataTable tbody tr.child ul.dtr-details > li {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.25rem !important;
            border-bottom: 1px solid rgba(60, 36, 21, 0.08) !important;
            padding-bottom: 0.5rem !important;
            min-width: 0;
            overflow: hidden;
        }
        .dark table.dataTable tbody tr.child ul.dtr-details > li { border-color: rgba(250, 245, 232, 0.08) !important; }
        table.dataTable tbody tr.child ul.dtr-details > li:last-child { border-bottom: none !important; }
        table.dataTable tbody tr.child span.dtr-title {
            font-size: 0.7rem !important; font-weight: 700 !important;
            text-transform: uppercase !important; letter-spacing: 0.05em !important;
            color: #7D6A60 !important;
        }
        .dark table.dataTable tbody tr.child span.dtr-title { color: #EACE80 !important; }
        table.dataTable tbody tr.child span.dtr-data { font-size: 0.875rem !important; font-weight: 600 !important; color: #231815 !important; }
        .dark table.dataTable tbody tr.child span.dtr-data { color: #FAF5E8 !important; }

        /* ===== CUSTOM FOOTER ===== */
        .sdt-footer {
            display: flex; flex-wrap: wrap; align-items: center;
            justify-content: space-between; gap: 1rem;
            padding: 1rem 1.5rem; border-top: 1px solid rgba(60, 36, 21, 0.10);
            background: #FDF9F1;
        }
        .dark .sdt-footer { border-color: rgba(250, 245, 232, 0.08); background: #201713; }
        @media (max-width: 639px) {
            .sdt-footer { flex-direction: column; align-items: center; text-align: center; padding: 1rem; }
        }
        .sdt-info { font-size: 0.8125rem; color: #6B574C; font-weight: 600; }
        .dark .sdt-info { color: #B9A79B; }

        /* Pagination */
        .sdt-pagination { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; justify-content: center; }
        .sdt-pagination button {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2.25rem; height: 2.25rem; padding: 0 0.625rem;
            font-size: 0.8125rem; font-weight: 700;
            border: 1px solid rgba(60, 36, 21, 0.16); border-radius: 0.65rem;
            background: #FFFDF9; color: #3A221B; cursor: pointer;
            transition: all 150ms;
        }
        .sdt-pagination button:hover:not(:disabled):not(.active) {
            background: #FCEEEF; border-color: rgba(142, 30, 38, 0.45); color: #8E1E26;
        }
        .sdt-pagination button.active {
            background: #8E1E26; border-color: #8E1E26; color: #FAF5E8; font-weight: 700;
            box-shadow: 0 3px 8px -2px rgba(142, 30, 38, 0.45);
        }
        .sdt-pagination button:disabled { opacity: 0.38; cursor: not-allowed; }
        .dark .sdt-pagination button { background: #2E211C; border-color: rgba(250, 245, 232, 0.15); color: #E8DDD2; }
        .dark .sdt-pagination button:hover:not(:disabled):not(.active) {
            background: rgba(142, 30, 38, 0.25); border-color: #EACE80; color: #EACE80;
        }
        .dark .sdt-pagination button.active { background: #8E1E26; border-color: #8E1E26; color: #FAF5E8; }

        /* ===== EMPTY STATE ===== */
        .sdt-empty { padding: 3.5rem 1.5rem; text-align: center; }
        .sdt-empty-icon {
            display: flex; align-items: center; justify-content: center;
            width: 4rem; height: 4rem; margin: 0 auto 1rem;
            border-radius: 9999px; background: #FCEEEF; color: #8E1E26;
            border: 1px solid rgba(142, 30, 38, 0.15);
        }
        .dark .sdt-empty-icon { background: rgba(142, 30, 38, 0.2); color: #EACE80; border-color: rgba(234, 206, 128, 0.2); }
        .sdt-empty-title { font-size: 1rem; font-weight: 700; color: #3A221B; margin-bottom: 0.25rem; }
        .dark .sdt-empty-title { color: #FAF5E8; }
        .sdt-empty-desc { font-size: 0.875rem; color: #8A766B; }
        .dark .sdt-empty-desc { color: #A39287; }

        /* ===== LOADING STATE ===== */
        .sdt-loading { position: relative; min-height: 120px; }
        .sdt-loading::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(250, 245, 232, 0.72);
            display: flex; align-items: center; justify-content: center;
            z-index: 10; border-radius: 0.75rem;
        }
        .dark .sdt-loading::after { background: rgba(26, 18, 15, 0.72); }
    </style>

    <!-- Global DataTable initializer -->
    <script>
        if (typeof $.fn.dataTable !== 'undefined') {
            $.fn.dataTable.ext.errMode = 'none';
        }

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

<body class="admin-daisy-theme antialiased" x-data="{ 'loaded': true }" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
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

    {{-- Wavy Liquid Preloader --}}
    <x-common.preloader />

    <div class="min-h-screen xl:flex overflow-x-clip">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 min-h-screen flex flex-col transition-all duration-300 ease-in-out" :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->

            <main class="flex-1 min-w-0 max-w-full w-full p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6 lg:p-7 overflow-x-hidden">
                @yield('content')
            </main>

            <!-- Daisy Bakery Admin Footer -->
            <footer class="mt-auto border-t border-[#3C2415]/10 dark:border-white/10 bg-[#FFFDF9]/80 dark:bg-[#201713]/90 backdrop-blur-xs">
                <div class="mx-auto max-w-(--breakpoint-2xl) px-4 py-4 md:px-6 lg:px-7 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#7D6A60] dark:text-[#B9A79B]">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#FAF5E8] dark:bg-[#2E211C] border border-[#3C2415]/12 dark:border-white/10 p-1">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                        </span>
                        <div class="flex flex-wrap items-center gap-x-1.5">
                            <span class="font-display font-bold text-[#3A221B] dark:text-[#FAF5E8]">Daisy Bakery</span>
                            <span class="opacity-50">&middot;</span>
                            <span>POS &amp; Store Management</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 font-semibold">
                        <a href="{{ route('dashboard') }}" class="hover:text-[#8E1E26] dark:hover:text-[#EACE80] transition">Dashboard</a>
                        @if (\Illuminate\Support\Facades\Route::has('transaksi.indeks'))
                            <a href="{{ route('transaksi.indeks') }}" class="hover:text-[#8E1E26] dark:hover:text-[#EACE80] transition">Kasir POS</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="hover:text-[#8E1E26] dark:hover:text-[#EACE80] transition">Profil Saya</a>
                        <span class="hidden sm:inline opacity-35">|</span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[#8A766B] dark:text-[#A39287]">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5 opacity-85">
                            &copy; {{ date('Y') }} Daisy Bakery
                        </span>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    @if (session('success') || session('error'))
        <div x-data="{ show: false }" x-init="
                $nextTick(() => { show = true; });
                setTimeout(() => show = false, 4000);
            " x-show="show" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0" class="fixed top-5 right-5 z-[99999] max-w-[calc(100vw-2.5rem)]">
            <div
                class="text-[#FAF5E8] px-5 py-3.5 rounded-2xl shadow-xl border flex items-center gap-3 min-w-[280px] {{ session('success') ? 'bg-[#2C5835] border-[#2C5835]/30' : 'bg-[#8E1E26] border-[#8E1E26]/30' }}">
                <span class="flex items-center justify-center w-6 h-6 shrink-0 rounded-full bg-white/15 font-bold text-sm">{{ session('success') ? '✓' : '!' }}</span>
                <span class="text-sm font-semibold flex-1">{{ session('success') ?? session('error') }}</span>
                <button type="button" @click="show = false" aria-label="Tutup"
                    class="ml-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white/75 hover:bg-white/15 hover:text-white transition cursor-pointer">
                    &times;
                </button>
            </div>
        </div>
    @endif

    {{-- Global Alert Modal (Alpine.js) --}}
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
         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-[#231815]/60 backdrop-blur-xs"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <div @click.away="close()"
             class="bg-[#FFFDF9] dark:bg-[#241A16] rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden border border-[#3C2415]/15 dark:border-white/10 transition-all text-center">
            <div class="p-6">
                <div class="w-14 h-14 bg-[#FCEEEF] dark:bg-[#8E1E26]/25 text-[#8E1E26] dark:text-[#EACE80] font-bold flex items-center justify-center rounded-2xl text-2xl mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-[#3A221B] dark:text-[#FAF5E8] text-lg" x-text="title"></h3>
                <p class="text-sm text-[#6B574C] dark:text-[#C5B5A8] mt-2 leading-relaxed" x-text="message"></p>
            </div>
            <div class="bg-[#FAF5E8] dark:bg-[#1A120F] p-4 border-t border-[#3C2415]/10 dark:border-white/10 flex justify-center">
                <button type="button"
                        @click="close()"
                        class="w-full py-2.5 px-5 bg-[#8E1E26] hover:bg-[#741A21] text-[#FAF5E8] font-bold text-sm rounded-xl transition shadow-xs cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-[#8E1E26] focus:ring-offset-2">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    {{-- Daisy Custom Searchable Dropdown Engine (upgrades all admin dropdowns with Daisy Bakery theme + search) --}}
    <script>
        (function () {
            const FILTER_SELECTORS = [
                'form[method="GET"] select',
                'form[method="get"] select',
                '#filterForm select',
                '#filterPesananPickup select',
                'select[id^="filter_"]'
            ].join(', ');

            const instances = new WeakMap();
            let activeInstance = null;

            function isEligible(select) {
                if (!select || select.tagName !== 'SELECT') return false;
                if (select.multiple) return false;
                if (select.hasAttribute('data-native-select')) return false;
                if (select.closest('template')) return false;
                if (select.classList.contains('daisy-select-native-hidden')) return false;
                return true;
            }

            function closeActive() {
                if (activeInstance) activeInstance.close();
            }

            function createDaisySelect(select) {
                if (instances.has(select)) return instances.get(select);

                const isCompact = !!(
                    select.closest('.sdt-pagesize') && !select.id.startsWith('filter_')
                ) || select.getAttribute('x-model') === 'itemsPerPage' || select.id === 'filter_tahun_terlaris' || select.name === 'tahun';

                const isFilter = !isCompact && select.matches(FILTER_SELECTORS);

                const isFormSelect = !isCompact && !isFilter && (
                    select.classList.contains('h-12') ||
                    select.classList.contains('py-3') ||
                    !!select.closest('form:not([method="GET"]):not([method="get"])')
                );

                const isFullWidth = select.classList.contains('w-full') || (
                    (isFilter || isFormSelect) && !isCompact
                );

                const wrap = document.createElement('div');
                wrap.className = 'daisy-select-wrap' + (isFullWidth ? ' daisy-select-full' : '');

                select.parentNode.insertBefore(wrap, select);
                wrap.appendChild(select);
                select.classList.add('daisy-select-native-hidden');
                select.setAttribute('tabindex', '-1');
                select.setAttribute('aria-hidden', 'true');

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'daisy-select-trigger' +
                    (isCompact ? ' is-compact' : '') +
                    (isFormSelect ? ' is-form' : '');
                trigger.setAttribute('aria-haspopup', 'listbox');
                trigger.setAttribute('aria-expanded', 'false');

                const labelSpan = document.createElement('span');
                labelSpan.className = 'daisy-select-label';

                const chevronSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                chevronSvg.setAttribute('viewBox', '0 0 24 24');
                chevronSvg.setAttribute('fill', 'none');
                chevronSvg.setAttribute('stroke', 'currentColor');
                chevronSvg.setAttribute('stroke-width', '2.2');
                chevronSvg.classList.add('daisy-select-chevron');
                chevronSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>';

                trigger.appendChild(labelSpan);
                trigger.appendChild(chevronSvg);
                wrap.appendChild(trigger);

                let portal = null;
                let searchInput = null;
                let listEl = null;
                let highlightedIndex = -1;
                let isOpen = false;

                function getOptions() {
                    return Array.from(select.options)
                        .filter((opt) => !opt.hidden && opt.style.display !== 'none')
                        .map((opt, idx) => ({
                            index: idx,
                            value: opt.value,
                            label: (opt.textContent || '').trim(),
                            disabled: opt.disabled,
                            selected: opt.selected
                        }));
                }

                function updateTrigger() {
                    const opts = getOptions();
                    const selected = opts.find(o => o.value === select.value) || opts.find(o => o.selected) || opts[0];
                    const text = selected ? selected.label : 'Pilih...';
                    const isPlaceholder = !isCompact && (!selected || selected.value === '');
                    const isFiltered = isFilter && selected && selected.index > 0 && selected.value !== '' && selected.value !== 'semua';

                    labelSpan.innerHTML = '';
                    if (isFiltered) {
                        const dot = document.createElement('span');
                        dot.className = 'daisy-select-dot';
                        labelSpan.appendChild(dot);
                    }
                    const txt = document.createElement('span');
                    txt.className = 'truncate';
                    txt.textContent = text;
                    labelSpan.appendChild(txt);

                    trigger.classList.toggle('is-placeholder', isPlaceholder);
                    trigger.disabled = !!select.disabled;
                }

                function positionPortal() {
                    if (!portal) return;
                    const rect = trigger.getBoundingClientRect();
                    const margin = 10;
                    const desiredWidth = Math.max(rect.width, isCompact ? 176 : 240);
                    const maxW = Math.min(desiredWidth, window.innerWidth - margin * 2);

                    portal.style.width = maxW + 'px';
                    const portalHeight = Math.min(portal.offsetHeight || 280, 320);

                    const spaceBelow = window.innerHeight - rect.bottom - margin;
                    const spaceAbove = rect.top - margin;
                    const openAbove = spaceBelow < Math.min(portalHeight, 220) && spaceAbove > spaceBelow;

                    let left = rect.left;
                    if (left + maxW > window.innerWidth - margin) {
                        left = Math.max(margin, rect.right - maxW);
                    }
                    left = Math.max(margin, left);

                    portal.style.left = left + 'px';
                    if (openAbove) {
                        portal.classList.add('is-above');
                        portal.style.top = Math.max(margin, rect.top - portal.offsetHeight - 6) + 'px';
                    } else {
                        portal.classList.remove('is-above');
                        portal.style.top = (rect.bottom + 6) + 'px';
                    }
                }

                function renderOptions(filterQuery = '') {
                    if (!listEl) return;
                    const q = filterQuery.trim().toLowerCase();
                    const opts = getOptions().filter(o => !q || o.label.toLowerCase().includes(q));
                    listEl.innerHTML = '';

                    if (opts.length === 0) {
                        const empty = document.createElement('li');
                        empty.className = 'daisy-select-empty';
                        empty.textContent = 'Tidak ada pilihan cocok';
                        listEl.appendChild(empty);
                        return;
                    }

                    opts.forEach((opt, renderIdx) => {
                        const li = document.createElement('li');
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.role = 'option';
                        btn.dataset.value = opt.value;
                        const isSel = opt.value === select.value;
                        btn.setAttribute('aria-selected', isSel ? 'true' : 'false');
                        btn.disabled = opt.disabled;
                        btn.className = 'daisy-select-option' +
                            (isSel ? ' is-selected' : '') +
                            (renderIdx === highlightedIndex ? ' is-highlighted' : '');

                        const span = document.createElement('span');
                        span.className = 'truncate';
                        span.textContent = opt.label;
                        btn.appendChild(span);

                        if (isSel) {
                            const check = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            check.setAttribute('viewBox', '0 0 24 24');
                            check.setAttribute('fill', 'none');
                            check.setAttribute('stroke', 'currentColor');
                            check.setAttribute('stroke-width', '2.5');
                            check.classList.add('daisy-select-option-check');
                            check.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>';
                            btn.appendChild(check);
                        }

                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (!opt.disabled) chooseValue(opt.value);
                        });

                        li.appendChild(btn);
                        listEl.appendChild(li);
                    });
                }

                function chooseValue(newValue) {
                    const changed = select.value !== newValue;
                    select.value = newValue;
                    trigger.classList.remove('is-invalid');
                    updateTrigger();
                    close();
                    trigger.focus();
                    if (changed) {
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                function open() {
                    if (select.disabled) return;
                    if (activeInstance && activeInstance !== api) activeInstance.close();
                    isOpen = true;
                    activeInstance = api;
                    trigger.setAttribute('aria-expanded', 'true');

                    portal = document.createElement('div');
                    portal.className = 'daisy-select-portal';
                    portal.addEventListener('mousedown', (e) => e.stopPropagation());
                    portal.addEventListener('click', (e) => e.stopPropagation());

                    const sBox = document.createElement('div');
                    sBox.className = 'daisy-select-search-box';
                    const sWrap = document.createElement('div');
                    sWrap.className = 'daisy-select-search-wrap';
                    const sIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    sIcon.setAttribute('viewBox', '0 0 24 24');
                    sIcon.setAttribute('fill', 'none');
                    sIcon.setAttribute('stroke', 'currentColor');
                    sIcon.setAttribute('stroke-width', '2');
                    sIcon.classList.add('daisy-select-search-icon');
                    sIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>';

                    searchInput = document.createElement('input');
                    searchInput.type = 'text';
                    searchInput.className = 'daisy-select-search';
                    searchInput.placeholder = isCompact ? 'Cari...' : 'Cari pilihan...';
                    searchInput.setAttribute('autocomplete', 'off');
                    searchInput.addEventListener('input', () => {
                        highlightedIndex = 0;
                        renderOptions(searchInput.value);
                    });
                    searchInput.addEventListener('keydown', handleKeydown);

                    sWrap.appendChild(sIcon);
                    sWrap.appendChild(searchInput);
                    sBox.appendChild(sWrap);
                    portal.appendChild(sBox);

                    listEl = document.createElement('ul');
                    listEl.className = 'daisy-select-list custom-scrollbar';
                    listEl.setAttribute('role', 'listbox');
                    portal.appendChild(listEl);

                    const opts = getOptions();
                    highlightedIndex = opts.findIndex(o => o.value === select.value);
                    renderOptions('');

                    document.body.appendChild(portal);
                    positionPortal();

                    requestAnimationFrame(() => {
                        if (!portal) return;
                        positionPortal();
                        portal.classList.add('is-open');
                        const selectedEl = listEl.querySelector('.is-selected');
                        if (selectedEl) selectedEl.scrollIntoView({ block: 'nearest' });
                        if (searchInput) searchInput.focus();
                    });
                }

                function close() {
                    if (!isOpen) return;
                    isOpen = false;
                    trigger.setAttribute('aria-expanded', 'false');
                    if (portal && portal.parentNode) portal.parentNode.removeChild(portal);
                    portal = null;
                    searchInput = null;
                    listEl = null;
                    if (activeInstance === api) activeInstance = null;
                }

                function handleKeydown(e) {
                    if (!isOpen) {
                        if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            open();
                        }
                        return;
                    }
                    const items = listEl ? Array.from(listEl.querySelectorAll('.daisy-select-option:not(:disabled)')) : [];
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        close();
                        trigger.focus();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (items.length) {
                            highlightedIndex = (highlightedIndex + 1) % items.length;
                            items.forEach((el, i) => el.classList.toggle('is-highlighted', i === highlightedIndex));
                            items[highlightedIndex].scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (items.length) {
                            highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
                            items.forEach((el, i) => el.classList.toggle('is-highlighted', i === highlightedIndex));
                            items[highlightedIndex].scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (items[highlightedIndex]) chooseValue(items[highlightedIndex].dataset.value);
                    }
                }

                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    isOpen ? close() : open();
                });
                trigger.addEventListener('keydown', handleKeydown);

                select.addEventListener('change', () => {
                    trigger.classList.remove('is-invalid');
                    updateTrigger();
                });
                select.addEventListener('input', updateTrigger);
                select.addEventListener('invalid', () => {
                    trigger.classList.add('is-invalid');
                    trigger.focus();
                    trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                if (select.form) {
                    select.form.addEventListener('reset', () => setTimeout(updateTrigger, 0));
                }

                const desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
                if (desc && desc.set && desc.get) {
                    Object.defineProperty(select, 'value', {
                        configurable: true,
                        get() { return desc.get.call(this); },
                        set(v) {
                            desc.set.call(this, v);
                            updateTrigger();
                            if (isOpen) renderOptions(searchInput ? searchInput.value : '');
                        }
                    });
                }

                const optObserver = new MutationObserver(() => {
                    updateTrigger();
                    if (isOpen) renderOptions(searchInput ? searchInput.value : '');
                });
                optObserver.observe(select, { childList: true, subtree: true, attributes: true, characterData: true });

                updateTrigger();
                setTimeout(updateTrigger, 60);

                const api = { select, wrap, trigger, open, close, sync: updateTrigger, position: positionPortal };
                instances.set(select, api);
                return api;
            }

            function enhanceAll(root = document) {
                if (!root || !root.querySelectorAll) return;
                root.querySelectorAll('select').forEach((el) => {
                    if (isEligible(el)) createDaisySelect(el);
                });
            }

            document.addEventListener('click', () => closeActive());
            window.addEventListener('resize', () => { if (activeInstance) activeInstance.position(); });
            window.addEventListener('scroll', (e) => {
                if (!activeInstance) return;
                if (e.target && e.target.closest && e.target.closest('.daisy-select-portal')) return;
                activeInstance.position();
            }, true);

            window.DaisyCustomSelect = {
                enhanceAll,
                sync(target) {
                    const el = typeof target === 'string' ? document.querySelector(target) : target;
                    if (el && instances.has(el)) instances.get(el).sync();
                }
            };

            function boot() {
                enhanceAll(document);
                const domObserver = new MutationObserver((mutations) => {
                    for (const m of mutations) {
                        for (const node of m.addedNodes) {
                            if (node.nodeType !== 1) continue;
                            if (node.classList && node.classList.contains('daisy-select-portal')) continue;
                            if (isEligible(node)) {
                                createDaisySelect(node);
                            } else if (node.querySelectorAll) {
                                enhanceAll(node);
                            }
                        }
                    }
                });
                domObserver.observe(document.body, { childList: true, subtree: true });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => setTimeout(boot, 0));
            } else {
                setTimeout(boot, 0);
            }
        })();
    </script>

    @include('layouts.fcm-admin-register')

    {{-- Pulihkan halaman terakhir setelah layar diputar (khusus aplikasi Android / WebView). --}}
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
