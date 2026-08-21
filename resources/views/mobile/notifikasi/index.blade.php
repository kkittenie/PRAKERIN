@extends('mobile.layout', ['title' => 'Notifikasi'])

@php
    $iconMap = [
        'info' => ['bg' => 'bg-blue-50 text-blue-600', 'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'success' => ['bg' => 'bg-green-50 text-green-600', 'path' => 'M5 13l4 4L19 7'],
        'warning' => ['bg' => 'bg-amber-50 text-amber-700', 'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'error' => ['bg' => 'bg-red-50 text-red-600', 'path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ];
@endphp

@section('content')

    <div class="-mt-6 relative flex flex-none items-center justify-center px-0 pt-6">
        <a href="{{ route('member.beranda') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
            class="absolute left-0 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="text-base font-extrabold text-brand-800">Notifikasi</h1>
        @if ($notifikasi->contains(fn ($n) => ! $n->is_read))
            <button type="button" id="btn-baca-semua" onclick="tandaiSemuaDibaca()"
                class="absolute right-0 top-1/2 -translate-y-1/2 whitespace-nowrap text-[11px] font-bold text-brand-800 underline underline-offset-2">
                Tandai dibaca
            </button>
        @endif
    </div>

    @if ($notifikasi->isEmpty())
        <div class="mt-16 flex flex-col items-center gap-3 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-25 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-7 w-7">
                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <p class="text-sm font-semibold text-gray-500">Belum ada notifikasi</p>
            <p class="max-w-[220px] text-xs text-gray-400">Update pesanan & promo akan muncul di sini.</p>
        </div>
    @else
        <div class="mt-5 space-y-2">
            @foreach ($notifikasi as $notif)
                @php $icon = $iconMap[$notif->icon_type] ?? $iconMap['info']; @endphp
                <a href="{{ $notif->url ?: '#' }}" data-notif-item data-notif-id="{{ $notif->id }}"
                    onclick="event.preventDefault(); tandaiNotifDibaca({{ $notif->id }}, '{{ $notif->url ?: '' }}');"
                    class="flex items-start gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-xs transition active:scale-[0.99] {{ ! $notif->is_read ? 'ring-1 ring-brand-100' : '' }}">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $icon['bg'] }}">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="{{ $icon['path'] }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-bold text-gray-800">{{ $notif->title }}</p>
                            @unless ($notif->is_read)
                                <span data-notif-dot class="h-2 w-2 shrink-0 rounded-full bg-brand-800"></span>
                            @endunless
                        </div>
                        <p class="mt-0.5 text-xs leading-relaxed text-gray-500">{{ $notif->desc }}</p>
                        <p class="mt-1.5 text-[11px] font-medium text-gray-400">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

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
                    document.querySelectorAll('[data-notif-item]').forEach((el) => el.classList.remove('ring-1', 'ring-brand-100'));
                    btn?.classList.add('invisible');
                })
                .catch(() => {
                    if (btn) btn.disabled = false;
                });
        }
    </script>

@endsection