@props(['member'])

@php
    // Warna ikon tier diambil dari pengaturan "Aturan Naik Level" di POS
    // (warna_badge), bukan hardcode nama level, supaya tetap konsisten
    // walau admin ganti nama/tambah level baru.
    $tierIcon = match ($member->warna_badge_level ?? 'gray') {
        'yellow' => 'text-yellow-200',
        'slate' => 'text-slate-200',
        'amber' => 'text-amber-200',
        'blue' => 'text-blue-200',
        'green' => 'text-green-200',
        'purple' => 'text-purple-200',
        'red' => 'text-red-200',
        default => 'text-white/80',
    };
    $progres = $member->progres_tier;
    $nomorSamar = str_repeat('•', max(0, strlen($member->no_hp) - 4)).substr($member->no_hp, -4);
@endphp

<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-gray-900 p-5 text-white shadow-theme-lg">

    {{-- Tekstur dekoratif: butiran kopi samar, bukan gradient blob generik --}}
    <svg class="pointer-events-none absolute -right-6 -top-8 h-40 w-40 text-white/[0.07]" viewBox="0 0 200 200" fill="none">
        <ellipse cx="60" cy="40" rx="22" ry="15" transform="rotate(-25 60 40)" stroke="currentColor" stroke-width="3"/>
        <path d="M45 40q15 -8 30 0" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        <ellipse cx="140" cy="90" rx="26" ry="18" transform="rotate(15 140 90)" stroke="currentColor" stroke-width="3"/>
        <path d="M120 90q20 -9 40 0" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        <ellipse cx="90" cy="150" rx="18" ry="13" transform="rotate(-10 90 150)" stroke="currentColor" stroke-width="3"/>
    </svg>

    <div class="relative flex items-start justify-between">
        <div>
            <p class="text-theme-xs font-medium uppercase tracking-widest text-white/60">Kartu Member</p>
            <p class="mt-0.5 text-theme-sm font-semibold">{{ $member->nama }}</p>
        </div>
        <span class="flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold backdrop-blur">
            @include('mobile.components.icon-crown', ['class' => 'h-3.5 w-3.5 '.$tierIcon])
            {{ $member->level }}
        </span>
    </div>

    <div class="relative mt-6 flex items-end justify-between">
        <div>
            <p class="text-title-sm font-bold leading-none tabular-nums">{{ number_format($member->poin, 0, ',', '.') }}</p>
            <p class="mt-1 text-theme-xs text-white/60">poin tersedia</p>
        </div>
        <p class="font-mono text-theme-sm tracking-[0.2em] text-white/70">{{ $nomorSamar }}</p>
    </div>

    @if ($progres)
        <div class="relative mt-5">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/15">
                <div class="h-full rounded-full bg-white" style="width: {{ $progres['persen'] }}%"></div>
            </div>
            <p class="mt-1.5 text-[11px] text-white/60">Rp {{ number_format($progres['transaksi_dibutuhkan'], 0, ',', '.') }} transaksi lagi menuju {{ $progres['tier_berikutnya'] }}</p>
        </div>
    @else
        <p class="relative mt-5 text-[11px] text-white/60">Kamu sudah di tier tertinggi ✨</p>
    @endif
</div>