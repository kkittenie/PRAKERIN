@extends('mobile.layout', ['title' => 'Notifications'])

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - DAISY BAKERY WARM PATISSERIE & STRAWBERRY THEME --}}
    {{-- ========================================== --}}
    <div id="notifikasi-mobile-root" class="lg:hidden pb-10">
        @php $unreadCount = $notifikasi->where('is_read', false)->count(); @endphp

        {{-- Seamless Sticky Header (Matches warm cream canvas #FAF5E8) --}}
        <div class="sticky top-0 z-30 -mx-5 -mt-6 flex h-14 items-center justify-between border-b border-[#3C2415]/10 bg-[#FAF5E8]/95 px-5 backdrop-blur-md sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
            <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="flex h-8 w-8 items-center justify-center rounded-full border border-[#3C2415]/20 bg-[#F5ECE2] text-[#3A221B] shadow-xs transition active:scale-95">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>

            <div class="flex items-center gap-1.5">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-4 w-4 object-contain">
                <h1 class="font-display text-sm font-extrabold text-[#3A221B]">Notifications</h1>
            </div>

            <span class="w-8"></span>
        </div>

        {{-- Hero Summary Card (Dark Chocolate Patisserie Banner with Strawberry Decor) --}}
        <div class="mt-4 relative overflow-hidden rounded-[24px] bg-gradient-to-br from-[#3C2415] to-[#5A3825] p-4 sm:p-5 text-[#FAF5E8] shadow-md select-none">
            {{-- Decorative Strawberry Watermarks --}}
            <span class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 opacity-20 select-none rotate-12">
                <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-full w-full object-contain">
            </span>
            <span class="pointer-events-none absolute -left-3 -top-3 h-16 w-16 opacity-15 select-none -rotate-12">
                <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="h-full w-full object-contain">
            </span>

            <div class="relative z-10 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5 object-contain shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-wider text-[#FFCAD4]">Daisy Updates</span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-[#FAF5E8]/85 leading-snug">
                        Order updates &amp; bakery announcements
                    </p>
                </div>

                @if ($notifikasi->contains(fn ($n) => ! $n->is_read))
                    <button type="button" id="btn-baca-semua-mobile" onclick="tandaiSemuaDibaca()"
                        class="shrink-0 inline-flex items-center gap-1 rounded-full border border-[#FFCAD4]/40 bg-[#FAF5E8] px-3 py-1.5 text-[10.5px] font-extrabold text-[#8E1E26] shadow-xs whitespace-nowrap transition hover:bg-white active:scale-95 cursor-pointer">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3 w-3">
                            <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Mark Read</span>
                    </button>
                @endif
            </div>

            {{-- Stats Box (Warm Translucent Cocoa Pills) --}}
            <div class="relative z-10 mt-3.5 grid grid-cols-2 gap-2.5">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur-xs">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#FAF5E8]/65">Total</span>
                    <span class="text-lg font-black text-[#FAF5E8]">{{ $notifikasi->count() }}</span>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-3.5 py-2.5 backdrop-blur-xs">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#FAF5E8]/65">Unread</span>
                    <span id="unread-count-stat" class="text-lg font-black {{ $unreadCount > 0 ? 'text-[#FFCAD4]' : 'text-[#FAF5E8]/50' }}">{{ $unreadCount }}</span>
                </div>
            </div>
        </div>

        {{-- Notification Items Stream --}}
        <div class="mt-4 pb-8">
            @if ($notifikasi->isEmpty())
                <div class="relative overflow-hidden rounded-[24px] border border-[#3C2415]/12 bg-[#F5ECE2] p-10 text-center shadow-xs">
                    <span class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 opacity-15 select-none rotate-12">
                        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="pointer-events-none absolute -left-3 -top-3 h-20 w-20 opacity-15 select-none -rotate-12">
                        <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="h-full w-full object-contain">
                    </span>
                    <span class="relative z-10 mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[#3C2415]/10 bg-[#FAF5E8] text-[#8E1E26] shadow-xs">
                        <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <h3 class="relative z-10 mt-4 font-display text-base font-extrabold text-[#3A221B]">No Notifications Yet</h3>
                    <p class="relative z-10 mx-auto mt-1 max-w-xs text-xs text-[#706864] leading-relaxed">When there are order updates or new bakery announcements, they will appear here.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($notifikasi as $notif)
                        @php
                            $targetUrl = \App\Support\NotificationHelper::normalizeUrl($notif->url);
                            $translatedTitle = \App\Support\NotificationHelper::translateTitle($notif->title);
                            $translatedDesc = \App\Support\NotificationHelper::translateDesc($notif->desc);
                            $strawberryDecor = 'images/decor/strawberry-' . (($loop->index % 3) + 1) . '.svg';
                        @endphp
                        <a href="{{ $targetUrl }}"
                           data-notif-item
                           data-notif-id="{{ $notif->id }}"
                           onclick="event.preventDefault(); tandaiNotifDibaca({{ $notif->id }}, '{{ $targetUrl }}');"
                           class="group relative flex items-start gap-3.5 overflow-hidden rounded-[22px] border p-4 shadow-xs transition duration-200 active:scale-[0.99] {{ ! $notif->is_read ? 'bg-[#FBEAEC] border-[#8E1E26]/25 ring-1 ring-[#8E1E26]/10' : 'bg-[#F4EAE0] border-[#3C2415]/12' }}">

                            {{-- Subtle Strawberry Watermark --}}
                            <span class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 select-none {{ ! $notif->is_read ? 'opacity-[0.18] rotate-12' : 'opacity-[0.11] -rotate-12' }}">
                                <img src="{{ asset($strawberryDecor) }}" alt="" class="h-full w-full object-contain">
                            </span>

                            {{-- Patisserie-Themed Icon Pill --}}
                            <span data-notif-icon class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-xs transition group-hover:scale-105 {{ $notif->type === 'stamp' ? 'bg-[#3C2415] text-[#FAF5E8]' : (! $notif->is_read ? 'bg-[#8E1E26] text-[#FAF5E8]' : 'bg-[#E8D9C8] text-[#3C2415] border border-[#3C2415]/10') }}">
                                @if ($notif->type === 'stamp')
                                    @include('mobile.components.icon-gift', ['class' => 'h-5 w-5'])
                                @elseif ($notif->type === 'pesanan')
                                    @include('mobile.components.icon-shopping-bag', ['class' => 'h-5 w-5'])
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @endif
                            </span>

                            <div class="relative z-10 min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        @unless ($notif->is_read)
                                            <img data-notif-dot src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5 shrink-0 object-contain">
                                        @endunless
                                        <p class="truncate text-xs sm:text-sm font-extrabold text-[#3A221B] group-hover:text-[#8E1E26] transition">
                                            {{ $translatedTitle }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-[10px] font-semibold text-[#8A6B63]">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                @unless ($notif->is_read)
                                    <div data-notif-dot class="mt-1">
                                        <span class="inline-flex items-center rounded-full bg-[#8E1E26] px-2 py-0.5 text-[8.5px] font-black uppercase tracking-wider text-[#FAF5E8]">
                                            New Update
                                        </span>
                                    </div>
                                @endunless

                                <p class="mt-1.5 text-xs leading-relaxed text-[#5C4338]">
                                    {{ $translatedDesc }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Cute Strawberry Footer Divider --}}
                <div class="mt-6 flex items-center justify-center gap-2.5 opacity-80 select-none">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-4 w-4 object-contain -rotate-12">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#8A6B63]">You're all caught up</span>
                    <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="h-4 w-4 object-contain rotate-12">
                </div>
            @endif
        </div>
    </div>

    {{-- Shared Scripts --}}
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
                unreadStat.classList.remove('text-[#FFCAD4]');
                unreadStat.classList.add('text-[#FAF5E8]/50');
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
                    document.querySelectorAll('#notifikasi-mobile-root [data-notif-dot]').forEach((el) => el.remove());
                    document.querySelectorAll('#notifikasi-mobile-root [data-notif-item]').forEach((el) => {
                        el.classList.remove('bg-[#FBEAEC]', 'border-[#8E1E26]/25', 'ring-1', 'ring-[#8E1E26]/10');
                        el.classList.add('bg-[#F4EAE0]', 'border-[#3C2415]/12');
                    });
                    document.querySelectorAll('#notifikasi-mobile-root [data-notif-icon]').forEach((el) => {
                        if (el.classList.contains('bg-[#8E1E26]')) {
                            el.classList.remove('bg-[#8E1E26]', 'text-[#FAF5E8]');
                            el.classList.add('bg-[#E8D9C8]', 'text-[#3C2415]', 'border', 'border-[#3C2415]/10');
                        }
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

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - FRENCH BAKERY NOTIFICATIONS HUB --}}
    {{-- ========================================== --}}
    <div id="notifikasi-desktop-root" class="relative hidden lg:block w-full bg-[#FAF5E8] min-h-screen text-[#3C2415]">

        <style>
            @keyframes floatStrawberry {
                0%, 100% {
                    transform: translateY(0px) rotate(var(--rot, 0deg));
                }
                50% {
                    transform: translateY(-8px) rotate(calc(var(--rot, 0deg) + 3deg));
                }
            }
            .desktop-floating-strawberry {
                animation: floatStrawberry var(--duration, 4s) ease-in-out infinite;
                will-change: transform;
            }
        </style>

        {{-- 2D Floating Strawberries in Outer Gutters --}}
        <div class="desktop-floating-strawberry hidden xl:block absolute left-6 xl:left-10 2xl:left-16 top-16 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 46px; height: 60px; --rot: -12deg; --duration: 4.2s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden xl:block absolute right-6 xl:left-auto xl:right-10 2xl:right-16 top-24 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 42px; height: 56px; --rot: 14deg; --duration: 3.8s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden xl:block absolute left-8 xl:left-12 2xl:left-20 top-1/2 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: 18deg; --duration: 4.6s;">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden xl:block absolute right-8 xl:left-auto xl:right-12 2xl:right-20 top-2/3 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 44px; height: 58px; --rot: -10deg; --duration: 4.4s;">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>
        <div class="desktop-floating-strawberry hidden xl:block absolute left-7 xl:left-11 2xl:left-18 top-3/4 pointer-events-none select-none z-0"
             aria-hidden="true" style="width: 40px; height: 52px; --rot: -14deg; --duration: 5.0s;">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-full h-full object-contain select-none">
        </div>

        <div class="relative z-10 max-w-5xl 2xl:max-w-6xl mx-auto px-6 pt-10 pb-24 space-y-8">

            {{-- Top Navigation & Metadata Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Back to Account</span>
                </a>
            </div>

            {{-- Clean Page Header (NO HERO SECTION) --}}
            <div class="space-y-2">
                <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                    Notifications
                </h1>
                <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                    Real-time updates on your fresh daily orders, pickup statuses, and artisanal bakery alerts.
                </p>
            </div>

            {{-- 2-Column Responsive Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- Left Column: Summary Card & Concierge (Sticky) --}}
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24 self-start">
                    {{-- Stats Summary Card --}}
                    <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm space-y-5">
                        <div class="flex items-center justify-between pb-4 border-b border-[#3C2415]/10">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#8E1E26]">
                                Notification Center
                            </span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white border border-[#3C2415]/10 text-[#8E1E26]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>

                        {{-- Stats counters --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white p-4 border border-[#3C2415]/10 text-center">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-[#3C2415]/60">Total</span>
                                <span class="text-2xl font-black text-[#3C2415]">{{ $notifikasi->count() }}</span>
                            </div>
                            <div class="rounded-2xl bg-white p-4 border border-[#3C2415]/10 text-center">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-[#3C2415]/60">Unread</span>
                                <span id="unread-count-stat-desk" class="text-2xl font-black {{ $unreadCount > 0 ? 'text-[#8E1E26]' : 'text-[#3C2415]/40' }}">{{ $unreadCount }}</span>
                            </div>
                        </div>

                        @if ($notifikasi->contains(fn ($n) => ! $n->is_read))
                            <button type="button" id="btn-baca-semua-desk"
                                onclick="tandaiSemuaDibaca(); this.classList.add('invisible'); const ud = document.getElementById('unread-count-stat-desk'); if (ud) { ud.innerText = '0'; ud.className = 'text-2xl font-black text-[#3C2415]/40'; } document.querySelectorAll('#notifikasi-desktop-root [data-notif-dot]').forEach(el => el.remove()); document.querySelectorAll('#notifikasi-desktop-root [data-notif-item]').forEach(el => { el.className = el.className.replace('bg-[#F5ECE3] border-[#8E1E26]/25 ring-1 ring-[#8E1E26]/20', 'bg-white border-[#3C2415]/12 hover:border-[#3C2415]/25'); });"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#3C2415] hover:bg-[#8E1E26] py-3 text-xs font-bold uppercase tracking-wider text-[#FAF5E8] shadow-xs transition active:scale-95 cursor-pointer">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Mark All as Read</span>
                            </button>
                        @endif
                    </div>

                    {{-- Bakery Concierge Help Card --}}
                    <div class="rounded-2xl bg-white p-5 border border-[#3C2415]/10 shadow-xs space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#8E1E26] uppercase">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="w-4 h-4 object-contain">
                            <span>Bakery Concierge</span>
                        </div>
                        <p class="text-[11px] text-[#3C2415]/70 leading-relaxed">
                            Have questions regarding order preparation, allergens, or pickup times? Our concierge is available daily.
                        </p>
                        <a href="https://wa.me/62882000768044" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#8E1E26] hover:underline pt-1">
                            <span>Chat with Concierge</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Right Column: Notification Items Stream --}}
                <div class="lg:col-span-8 space-y-3.5">
                    @if ($notifikasi->isEmpty())
                        <div class="rounded-[28px] border border-[#3C2415]/15 bg-[#F5ECE3] p-12 text-center shadow-xs">
                            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white text-[#3C2415]/40 border border-[#3C2415]/10 shadow-xs">
                                <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10">
                                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <h3 class="mt-4 font-display font-bold text-lg text-[#3C2415]">No Notifications Yet</h3>
                            <p class="mx-auto mt-1 max-w-sm text-xs text-[#3C2415]/70 leading-relaxed">
                                When there are order updates or new bakery announcements, they will appear right here.
                            </p>
                        </div>
                    @else
                        @foreach ($notifikasi as $notif)
                            @php
                                $targetUrl = \App\Support\NotificationHelper::normalizeUrl($notif->url);
                                $translatedTitle = \App\Support\NotificationHelper::translateTitle($notif->title);
                                $translatedDesc = \App\Support\NotificationHelper::translateDesc($notif->desc);
                            @endphp
                            <a href="{{ $targetUrl }}"
                               data-notif-item
                               data-notif-id="{{ $notif->id }}"
                               onclick="event.preventDefault(); tandaiNotifDibaca({{ $notif->id }}, '{{ $targetUrl }}');"
                               class="group relative flex items-start gap-4 rounded-[24px] border p-5 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-[0.99] {{ ! $notif->is_read ? 'bg-[#F5ECE3] border-[#8E1E26]/25 ring-1 ring-[#8E1E26]/20' : 'bg-white border-[#3C2415]/12 hover:border-[#3C2415]/25' }}">

                                {{-- Icon Pill --}}
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition group-hover:scale-105 {{ $notif->icon_type === 'success' ? 'bg-[#FAF5E8] text-[#8E1E26] border border-[#8E1E26]/20' : ($notif->type === 'stamp' ? 'bg-[#3C2415] text-[#FAF5E8]' : ($notif->type === 'pesanan' ? 'bg-[#8E1E26] text-white' : 'bg-[#FAF5E8] text-[#3C2415] border border-[#3C2415]/15')) }}">
                                    @if ($notif->type === 'stamp')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @elseif ($notif->type === 'pesanan')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4M3 6h18M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <p class="truncate text-sm font-extrabold text-[#3C2415] group-hover:text-[#8E1E26] transition">
                                                {{ $translatedTitle }}
                                            </p>
                                            @unless ($notif->is_read)
                                                <span data-notif-dot class="rounded-full bg-[#8E1E26] px-2 py-0.5 text-[9px] font-extrabold uppercase text-white tracking-wider">
                                                    NEW
                                                </span>
                                            @endunless
                                        </div>
                                        <span class="shrink-0 text-xs font-medium text-[#3C2415]/50 group-hover:text-[#3C2415]/75 transition">
                                            {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mt-1.5 text-xs text-[#3C2415]/75 leading-relaxed">
                                        {{ $translatedDesc }}
                                    </p>
                                </div>

                                <div class="shrink-0 items-center self-center text-[#3C2415]/30 group-hover:text-[#8E1E26] transition group-hover:translate-x-1">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                        <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>

@endsection
