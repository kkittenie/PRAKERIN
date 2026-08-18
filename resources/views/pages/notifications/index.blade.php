@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Semua Notifikasi" />

    <div class="space-y-6" x-data="{
        notifications: {{ json_encode($notifications) }},
        filter: 'semua',
        
        get filteredNotifications() {
            if (this.filter === 'belum_dibaca') {
                return this.notifications.filter(n => !n.is_read);
            }
            return this.notifications;
        },

        markRead(id) {
            fetch('/notifikasi/' + id + '/baca', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                const item = this.notifications.find(n => n.id === id);
                if (item) item.is_read = true;
            });
        },

        markAllRead() {
            fetch('{{ route('notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                this.notifications.forEach(n => n.is_read = true);
            });
        }
    }">
        {{-- Header Bar --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Pusat Notifikasi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Daftar pemberitahuan stok, produksi kadaluarsa, dan update sistem.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1 text-xs font-semibold">
                    <button type="button" @click="filter = 'semua'" :class="filter === 'semua' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1.5 rounded-lg transition">
                        Semua
                    </button>
                    <button type="button" @click="filter = 'belum_dibaca'" :class="filter === 'belum_dibaca' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1.5 rounded-lg transition">
                        Belum Dibaca
                    </button>
                </div>
                <button type="button" @click="markAllRead()" class="px-4 py-2 text-xs font-bold text-amber-700 bg-amber-50 dark:bg-amber-500/10 dark:text-amber-400 hover:bg-amber-100 rounded-xl transition">
                    Tandai Semua Dibaca
                </button>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="item in filteredNotifications" :key="item.id">
                    <div class="p-4 sm:p-5 flex items-start gap-4 transition-colors" :class="item.is_read ? 'bg-white dark:bg-gray-900' : 'bg-amber-50/40 dark:bg-amber-500/5'">
                        <div class="flex items-center justify-center h-10 w-10 rounded-xl shrink-0 mt-0.5" :class="item.color" x-html="item.icon">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                    <span x-text="item.title"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400" x-text="item.type"></span>
                                    <span x-show="!item.is_read" class="h-2 w-2 rounded-full bg-amber-500 inline-block"></span>
                                </h4>
                                <span class="text-xs text-gray-400 dark:text-gray-500" x-text="item.time"></span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-2" x-text="item.desc"></p>
                            <div class="flex items-center gap-3">
                                <a :href="item.url" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-1">
                                    <span>Lihat Detail</span>
                                    <span>&rarr;</span>
                                </a>
                                <button type="button" x-show="!item.is_read" @click="markRead(item.id)" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline">
                                    Tandai dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="filteredNotifications.length === 0">
                    <div class="p-12 text-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200 dark:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-sm font-medium">Tidak ada notifikasi untuk ditampilkan.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

@endsection
