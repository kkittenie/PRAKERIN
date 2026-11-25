@props(['voucher' => null, 'reward' => null, 'member' => null])

@php
    $voucher = $voucher ?? ($reward ?? null);
    if (!$voucher) {
        return;
    }

    $tersedia = $voucher->tersedia ?? true;
    $alasan = $voucher->alasan_nonaktif ?? null;
    $sisaJatah = $voucher->sisa_jatah ?? 1;
    $batas = $voucher->batas_penggunaan_per_member ?: 1;

    // Smart fallback if nominal/percentage was 0 in DB but stated in title
    $nom = (float) ($voucher->nominal_potongan ?? 0);
    $persen = (float) ($voucher->persentase ?? 0);

    if ($nom <= 0 && $persen <= 0) {
        $textSearch = ($voucher->nama ?? '') . ' ' . ($voucher->deskripsi ?? '');
        if (preg_match('/(\d+[\.,]?\d*)\s*%/i', $textSearch, $pm)) {
            $persen = (float) str_replace(',', '.', $pm[1]);
        } elseif (preg_match('/(?:Rp|IDR)?\s*([0-9]+(?:\.[0-9]{3})+)/i', $textSearch, $nm)) {
            $nom = (float) str_replace('.', '', $nm[1]);
        } elseif (preg_match('/(?:Rp|IDR)?\s*([0-9]+)/i', $textSearch, $nm)) {
            $nom = (float) $nm[1];
        }
    }

    if ($persen > 0) {
        $stubValue = rtrim(rtrim(number_format($persen, 1, '.', ''), '0'), '.') . '%';
        $fullPotongan = $stubValue . ' OFF';
    } elseif ($nom > 0) {
        $stubValue = $nom >= 1000 ? rtrim(rtrim(number_format($nom / 1000, 1, '.', ''), '0'), '.') . 'K' : number_format($nom);
        $fullPotongan = 'Rp ' . number_format($nom, 0, ',', '.') . ' OFF';
    } else {
        $stubValue = 'SPECIAL';
        $fullPotongan = 'Special Discount';
    }
@endphp

{{-- Voucher Card styled like the Daisy Bakery Account Card with Strawberry Watermark & Accent --}}
<a href="{{ route('member.reward.show', $voucher) }}"
   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
   class="relative flex items-center justify-between gap-3 overflow-hidden rounded-[24px] border border-[#3C2415]/10 bg-[#FAF4ED] p-4 sm:p-5 shadow-xs transition active:scale-[0.99] group select-none"
   style="{{ !$tersedia ? 'opacity:.6; filter:grayscale(30%);' : '' }}">

    {{-- Strawberry watermark in background (matching account card & desktop banner) --}}
    <span class="pointer-events-none absolute -right-3 -bottom-3 sm:-right-4 sm:-bottom-4 h-24 w-24 sm:h-28 sm:w-28 opacity-15 select-none rotate-12">
        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-full w-full object-contain">
    </span>

    {{-- Left Content Information --}}
    <div class="relative z-10 min-w-0 flex-1">

        {{-- Voucher Name --}}
        <h3 class="font-display text-[14px] sm:text-[15px] font-black uppercase leading-tight text-[#3A221B] group-hover:underline">
            {{ $voucher->nama }}
        </h3>

        {{-- Discount Amount --}}
        <p class="mt-1 text-xs sm:text-[13px] font-extrabold text-[#8E1E26]">
            {{ $fullPotongan }}
        </p>

        {{-- Terms & Conditions --}}
        <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] sm:text-[11px] font-medium text-[#706864]">
            @if($voucher->min_pembelian > 0)
                <span>Min. Spend Rp{{ number_format($voucher->min_pembelian, 0, ',', '.') }}</span>
            @else
                <span>No Min. Spend</span>
            @endif
            <span>&middot;</span>
            <span>Max {{ $batas }}x / member</span>
        </div>
    </div>

    {{-- Right Side Pill Button (matching the Account Card Sign-out / Action Pill) --}}
    <div class="relative z-10 shrink-0 flex flex-col items-end gap-1.5 my-auto pl-1 sm:pl-2">
        @if($tersedia)
            <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-white/95 px-3 sm:px-3.5 py-1.5 text-[11px] font-black uppercase tracking-wider text-rose-700 shadow-xs group-hover:bg-rose-50 transition">
                <span>{{ $stubValue }} OFF</span>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </span>
            <span class="text-[10px] font-bold text-[#8A6B63] pr-1">
                {{ $sisaJatah }} left
            </span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white/90 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500 shadow-xs">
                <span>{{ $alasan ?: 'Unavailable' }}</span>
            </span>
        @endif
    </div>
</a>
