{{-- Notification Dropdown Component --}}
@php
    $initialNotifications = \App\Support\NotificationHelper::getLiveNotifications(15);
    $notifyingCount = count(array_filter($initialNotifications, fn ($n) => !$n['is_read']));
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    notifyingCount: {{ $notifyingCount }},
    notifications: {{ json_encode($initialNotifications) }},
    posisiAtas: 0,
    posisiKiri: 0,
    tinggiMax: 360,

    init() {
        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 5000);
    },

    fetchNotifications() {
        fetch('{{ route('notifications.unread-json') }}')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    this.notifyingCount = data.unread_count;
                    this.notifications = data.notifications;
                }
            })
            .catch(() => {});
    },

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        if (this.dropdownOpen) {
            this.$nextTick(() => this.hitungPosisi());
        }
    },
    closeDropdown() {
        this.dropdownOpen = false;
    },
    hitungPosisi() {
        const margin = 12;
        const tombol = this.$refs.tombolNotifikasi.getBoundingClientRect();
        const panel = this.$refs.panelNotifikasi;
        const lebarPanel = panel.offsetWidth;

        let kiri = tombol.right - lebarPanel;
        kiri = Math.max(margin, Math.min(kiri, window.innerWidth - lebarPanel - margin));

        let atas = tombol.bottom + margin;
        let tinggi = window.innerHeight - atas - margin;

        if (tinggi < 200) {
            tinggi = Math.max(160, window.innerHeight - margin * 2);
            atas = margin;
        }

        this.posisiKiri = kiri;
        this.posisiAtas = atas;
        this.tinggiMax = tinggi;
    },
    markAllRead() {
        fetch('{{ route('notifications.mark-all-read') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(() => {
            this.notifyingCount = 0;
            this.fetchNotifications();
        });
    },
    markSingleRead(item, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        fetch('/notifikasi/' + item.id + '/baca', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(() => {
            item.is_read = true;
            this.fetchNotifications();
        });
    }
}" @click.away="closeDropdown()" @resize.window="if (dropdownOpen) hitungPosisi()" @scroll.window="if (dropdownOpen) hitungPosisi()">
    <!-- Notification Button -->
    <button
        x-ref="tombolNotifikasi"
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
        @click="toggleDropdown()"
        type="button"
        title="Notifikasi"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifyingCount > 0"
            class="absolute right-0 top-0.5 z-1 h-2.5 w-2.5 rounded-full bg-orange-500 flex items-center justify-center"
        >
            <span
                class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"
            ></span>
        </span>

        <!-- Bell Icon -->
        <svg
            class="fill-current"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill=""
            />
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-ref="panelNotifikasi"
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        :style="`top:${posisiAtas}px; left:${posisiKiri}px; max-height:${tinggiMax}px;`"
        class="fixed flex w-[92vw] max-w-[361px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 z-99999"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
            <h5 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-1.5">
                <span>Notifikasi POS</span>
            </h5>
            <button type="button" @click="markAllRead()" x-show="notifyingCount > 0" class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                Tandai dibaca
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col max-h-[280px] overflow-y-auto custom-scrollbar divide-y divide-gray-50 dark:divide-gray-800/60">
            <template x-for="item in notifications" :key="item.id">
                <li class="relative group" :class="item.is_read ? 'opacity-65' : ''">
                    <a
                        class="flex items-start gap-3 rounded-xl p-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        :href="item.url"
                    >
                        <span class="flex items-center justify-center h-9 w-9 rounded-full shrink-0 mt-0.5" :class="item.color" x-html="item.icon">
                        </span>

                        <span class="block min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-1">
                                <span class="block text-xs font-bold text-gray-800 dark:text-white truncate" x-text="item.title"></span>
                                <button
                                    type="button"
                                    x-show="!item.is_read"
                                    @click="markSingleRead(item, $event)"
                                    title="Tandai dibaca"
                                    class="shrink-0 text-[10px] font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 hover:underline px-1 py-0.5"
                                >
                                    Tandai dibaca
                                </button>
                            </span>
                            <span class="block text-[11px] text-gray-500 dark:text-gray-400 leading-snug line-clamp-2 mt-0.5" x-text="item.desc">
                            </span>
                            <span class="block text-[10px] text-gray-400 dark:text-gray-500 mt-1" x-text="item.time">
                            </span>
                        </span>
                    </a>
                </li>
            </template>
            <template x-if="notifications.length === 0">
                <li class="flex flex-col items-center justify-center py-10 text-center text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 mb-2 text-gray-200 dark:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-xs">Tidak ada notifikasi baru.</p>
                </li>
            </template>
        </ul>

        <!-- Dropdown Footer -->
        <div class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-800 text-center shrink-0">
            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline">
                Lihat Semua Notifikasi &rarr;
            </a>
        </div>
    </div>
    <!-- Dropdown End -->
</div>