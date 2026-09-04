@extends('mobile.layout', ['title' => 'Notifikasi'])

@section('content')

    <div class="mx-auto max-w-6xl px-4 py-2 sm:py-6">
        <div class="flex flex-col lg:flex-row lg:items-start gap-4 lg:gap-8">

            {{-- Left Panel / Sidebar (Desktop: Sticky sidebar; Mobile: Sticky top header) --}}
            <div class="w-full lg:w-80 lg:shrink-0 sticky top-0 lg:top-24 z-30 bg-white/95 backdrop-blur-md py-2 lg:py-0 border-b border-gray-100/80 lg:border-none">
                <div class="rounded-3xl border border-gray-100 bg-white p-4 sm:p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                               class="flex h-9 w-9 lg:h-10 lg:w-10 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-700 shadow-xs transition hover:border-brand-800 hover:text-brand-800">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <div>
                                <h1 class="text-lg lg:text-xl font-extrabold tracking-tight text-gray-900">Notifikasi</h1>
                                <p class="text-[11px] lg:text-xs text-gray-500">Pembaruan pesanan & info toko</p>
                            </div>
                        </div>

                        @if ($notifikasi->contains(fn ($n) => ! $n->is_read))
                            <button type="button" id="btn-baca-semua-mobile" onclick="tandaiSemuaDibaca()"
                                class="flex lg:hidden items-center justify-center gap-1.5 rounded-xl bg-brand-800 px-3 py-1.5 text-[11px] font-bold text-white shadow-xs transition hover:bg-brand-900 active:scale-95 cursor-pointer">
                                Tandai Dibaca
                            </button>
                        @endif
                    </div>

                    @php $unreadCount = $notifikasi->where('is_read', false)->count(); @endphp

                    {{-- Stats Box --}}
                    <div class="mt-3 lg:mt-5 grid grid-cols-2 gap-3 rounded-2xl bg-gray-50/80 p-3 lg:p-3.5 border border-gray-100">
                        <div>
                            <span class="block text-[10px] lg:text-[11px] font-medium text-gray-400">Total</span>
                            <span class="text-base lg:text-lg font-black text-gray-900">{{ $notifikasi->count() }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] lg:text-[11px] font-medium text-gray-400">Belum Dibaca</span>
                            <span id="unread-count-stat" class="text-base lg:text-lg font-black {{ $unreadCount > 0 ? 'text-brand-800' : 'text-gray-400' }}">{{ $unreadCount }}</span>
                        </div>
                    </div>

                    @if ($notifikasi->contains(fn ($n) => ! $n->is_read))
                        <button type="button" id="btn-baca-semua" onclick="tandaiSemuaDibaca()"
                            class="hidden lg:flex mt-4 w-full items-center justify-center gap-2 rounded-2xl bg-brand-800 py-3 text-xs font-bold text-white shadow-xs transition hover:bg-brand-900 active:scale-98 cursor-pointer">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Tandai Semua Dibaca
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right Main Panel (Notification Items Stream) --}}
            <div class="flex-1 min-w-0 pb-16 lg:pb-0">
                @if ($notifikasi->isEmpty())
                    <div class="rounded-3xl border border-gray-100 bg-white p-12 text-center shadow-xs">
                        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-50 text-gray-400 ring-1 ring-gray-100">
                            <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10">
                                <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-base font-extrabold text-gray-800">Belum Ada Notifikasi</h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-gray-500 leading-relaxed">Setiap ada pembaruan pesanan atau info baru, notifikasi akan tampil di sini.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($notifikasi as $notif)
                            @php
                                $targetUrl = \App\Support\NotificationHelper::normalizeUrl($notif->url);
                            @endphp
                            <a href="{{ $targetUrl }}"
                               data-notif-item
                               data-notif-id="{{ $notif->id }}"
                               onclick="event.preventDefault(); tandaiNotifDibaca({{ $notif->id }}, '{{ $targetUrl }}');"
                               class="group relative flex items-start gap-3.5 sm:gap-4 rounded-3xl border p-3.5 sm:p-5 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-[0.99] {{ ! $notif->is_read ? 'bg-brand-50/40 border-brand-200/80 ring-1 ring-brand-200/50' : 'bg-white border-gray-100' }}">
                                
                                {{-- Icon Pill --}}
                                <span class="flex h-11 w-11 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-2xl transition group-hover:scale-105 {{ $notif->icon_type === 'success' ? 'bg-emerald-100 text-emerald-700' : ($notif->type === 'stamp' ? 'bg-brand-100 text-brand-800' : ($notif->type === 'pesanan' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700')) }}">
                                    @if ($notif->type === 'stamp')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5.5 w-5.5 sm:h-6 sm:w-6"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @elseif ($notif->type === 'pesanan')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5.5 w-5.5 sm:h-6 sm:w-6"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4M3 6h18M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5.5 w-5.5 sm:h-6 sm:w-6"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <p class="truncate text-xs sm:text-sm font-extrabold text-gray-900 group-hover:text-brand-800 transition">{{ $notif->title }}</p>
                                            @unless ($notif->is_read)
                                                <span data-notif-dot class="rounded-full bg-brand-800 px-1.5 sm:px-2 py-0.5 text-[8px] sm:text-[9px] font-extrabold uppercase text-white tracking-wider">BARU</span>
                                            @endunless
                                        </div>
                                        <span class="shrink-0 text-[10px] sm:text-xs font-medium text-gray-400 group-hover:text-gray-600 transition">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 sm:mt-1.5 text-xs sm:text-sm leading-relaxed text-gray-600">{{ $notif->desc }}</p>
                                </div>

                                <div class="hidden sm:flex shrink-0 items-center self-center text-gray-300 group-hover:text-brand-800 transition group-hover:translate-x-0.5">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function tandaiNotifDibaca(id, url) {
            fetch(`{{ url('mobile/notifikasi') }}/${id}/baca`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(() => {});

            if (url) {
                animateNavigate(url, 'forward');
            }
        }

        function tandaiSemuaDibaca() {
            const btn = document.getElementById('btn-baca-semua');
            if (btn) btn.disabled = true;
            const btnMobile = document.getElementById('btn-baca-semua-mobile');
            if (btnMobile) btnMobile.disabled = true;

            if (typeof clearNavbarNotifBadge === 'function') {
                clearNavbarNotifBadge();
            }

            const unreadStat = document.getElementById('unread-count-stat');
            if (unreadStat) {
                unreadStat.innerText = '0';
                unreadStat.classList.remove('text-brand-800');
                unreadStat.classList.add('text-gray-400');
            }

            fetch("{{ route('member.notifikasi.baca-semua') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((res) => (res.ok ? res.json() : Promise.reject()))
                .then(() => {
                    document.querySelectorAll('[data-notif-dot]').forEach((el) => el.remove());
                    document.querySelectorAll('[data-notif-item]').forEach((el) => {
                        el.classList.remove('bg-brand-50/40', 'border-brand-200/80', 'ring-1', 'ring-brand-200/50');
                        el.classList.add('bg-white', 'border-gray-100');
                    });
                    if (btn) btn.classList.add('invisible');
                    if (btnMobile) btnMobile.classList.add('invisible');
                })
                .catch(() => {
                    if (btn) btn.disabled = false;
                    if (btnMobile) btnMobile.disabled = false;
                });
        }
    </script>

@endsection