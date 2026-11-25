{{-- Notification Dropdown Component --}}
@php
    $initialNotifications = \App\Support\NotificationHelper::getLiveNotifications(15);
    $notifyingCount = count(array_filter($initialNotifications, fn ($n) => !$n['is_read']));
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    notifyingCount: {{ $notifyingCount }},
    notifications: {{ json_encode($initialNotifications) }},
    pollAbort: null,
    pollTimer: null,

    init() {
        this.pollTimer = setInterval(() => {
            if (!document.hidden) {
                this.fetchNotifications();
            }
        }, 10000);

        const stopPolling = () => {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
            if (this.pollAbort) {
                this.pollAbort.abort();
                this.pollAbort = null;
            }
        };

        window.addEventListener('beforeunload', stopPolling);
        document.addEventListener('submit', () => {
            if (this.pollAbort) {
                this.pollAbort.abort();
                this.pollAbort = null;
            }
        }, true);
    },

    fetchNotifications() {
        if (this.pollAbort) {
            this.pollAbort.abort();
        }
        this.pollAbort = new AbortController();
        fetch('{{ route('notifications.unread-json') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: this.pollAbort.signal
        })
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (data && data.status === 'success') {
                    this.notifyingCount = data.unread_count;
                    this.notifications = data.notifications;
                }
            })
            .catch(() => {});
    },

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
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
}" @click.away="closeDropdown()">
    <button
        x-ref="tombolNotifikasi"
        class="relative flex items-center justify-center text-[#4A2E27] transition-colors bg-[#FFFDF9] border border-[#3C2415]/15 rounded-full h-10 w-10 sm:h-11 sm:w-11 hover:bg-[#FCEEEF] hover:text-[#8E1E26] hover:border-[#8E1E26]/30 dark:bg-[#241A16] dark:border-white/10 dark:text-[#EACE80] dark:hover:bg-[#2E211C] cursor-pointer"
        @click="toggleDropdown()"
        type="button"
        title="Notifikasi"
    >
        <span
            x-show="notifyingCount > 0"
            x-cloak
            x-text="notifyingCount > 99 ? '99+' : notifyingCount"
            class="absolute -right-1 -top-1 z-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#8E1E26] px-1 text-[10px] font-extrabold leading-none text-[#FAF5E8] ring-2 ring-[#FAF5E8] dark:ring-[#1A120F]"
        ></span>

        <svg class="fill-current" width="19" height="19" viewBox="0 0 20 20" fill="none">
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill="currentColor"
            />
        </svg>
    </button>

    <div
        x-ref="panelNotifikasi"
        x-show="dropdownOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed left-3 right-3 top-16 sm:absolute sm:top-full sm:left-auto sm:right-0 sm:mt-3 sm:w-[365px] flex flex-col rounded-2xl border border-[#3C2415]/15 bg-[#FFFDF9] p-3.5 shadow-[0_18px_36px_-10px_rgba(58,34,27,0.22)] dark:border-white/10 dark:bg-[#241A16] z-50 max-h-[82vh] overflow-hidden"
    >
        <div class="flex items-center justify-between pb-3 mb-2.5 border-b border-[#3C2415]/10 dark:border-white/10 shrink-0">
            <h5 class="font-display text-sm font-bold text-[#3A221B] dark:text-[#FAF5E8] flex items-center gap-2">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-4 h-4">
                <span>Notifikasi POS</span>
            </h5>
            <button type="button" @click="markAllRead()" x-show="notifyingCount > 0"
                class="text-xs font-bold text-[#8E1E26] hover:text-[#6B141B] dark:text-[#EACE80]">
                Tandai semua dibaca
            </button>
        </div>

        <ul class="flex flex-col max-h-[280px] overflow-y-auto custom-scrollbar divide-y divide-[#3C2415]/8 dark:divide-white/8">
            <template x-for="item in notifications" :key="item.id">
                <li class="relative group py-0.5" :class="item.is_read ? 'opacity-70' : ''">
                    <a
                        class="flex items-start gap-3 rounded-xl p-2.5 transition-colors"
                        :class="item.is_read ? 'hover:bg-[#FAF5E8] dark:hover:bg-white/5' : 'bg-[#FCEEEF]/60 hover:bg-[#FCEEEF] dark:bg-[#8E1E26]/15 dark:hover:bg-[#8E1E26]/25'"
                        :href="item.url"
                    >
                        <span class="flex items-center justify-center h-9 w-9 rounded-full shrink-0 mt-0.5" :class="item.color" x-html="item.icon"></span>

                        <span class="block min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-1">
                                <span class="block text-xs font-bold text-[#3A221B] dark:text-[#FAF5E8] truncate" x-text="item.title"></span>
                                <button
                                    type="button"
                                    x-show="!item.is_read"
                                    @click="markSingleRead(item, $event)"
                                    title="Tandai dibaca"
                                    class="shrink-0 text-[10px] font-bold text-[#8E1E26] hover:underline dark:text-[#EACE80] px-1 py-0.5"
                                >
                                    Dibaca
                                </button>
                            </span>
                            <span class="block text-[11px] text-[#6B574C] dark:text-[#C5B5A8] leading-snug line-clamp-2 mt-0.5" x-text="item.desc"></span>
                            <span class="block text-[10px] text-[#9A877B] mt-1" x-text="item.time"></span>
                        </span>
                    </a>
                </li>
            </template>
            <template x-if="notifications.length === 0">
                <li class="flex flex-col items-center justify-center py-10 text-center text-[#8A766B]">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-10 h-10 mb-2 opacity-40">
                    <p class="text-xs font-medium">Tidak ada notifikasi baru.</p>
                </li>
            </template>
        </ul>

        <div class="pt-2.5 mt-2 border-t border-[#3C2415]/10 dark:border-white/10 text-center shrink-0">
            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-[#8E1E26] dark:text-[#EACE80] hover:underline">
                Lihat Semua Notifikasi &rarr;
            </a>
        </div>
    </div>
</div>
