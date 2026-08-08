@props(['reward', 'member' => null])

@php
    $cukupPoin = $member ? $member->poin >= $reward->poin_dibutuhkan : false;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $isVoucher = $reward->jenis === 'voucher';
    $isCroissant = str_contains(strtolower($reward->nama ?? ''), 'croissant');
    $icon = $isVoucher ? 'icon-ticket' : ($isCroissant ? 'icon-cake' : 'icon-cup');
    $iconBg = $isVoucher ? '#5a3c27' : '#f97316';
    $tintRgba = $isVoucher ? 'rgba(90,60,39,.05)' : 'rgba(249,115,22,.06)';
@endphp

{{--
    Ticket-stub shape: the card is built as two rounded halves (top / bottom)
    with an unclipped notch row sandwiched between them. Splitting it this way
    (instead of one div with overflow:hidden) is what lets the circular notches
    actually bite into the left/right edges instead of being clipped away.
--}}
<a href="{{ route('member.reward.show', $reward) }}"
   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
   style="position:relative; display:block; border-radius:18px; box-shadow:0 2px 6px rgba(16,24,40,.06); {{ $habis ? 'opacity:.5' : '' }}">

    {{-- Top half --}}
    <div style="position:relative; overflow:hidden; border-radius:18px 18px 0 0; background:linear-gradient(100deg, #ffffff 65%, {{ $tintRgba }} 100%);">

        <span style="position:absolute; right:-14px; top:-14px; display:flex; height:84px; width:84px; align-items:center; justify-content:center; color:{{ $iconBg }}; opacity:.10;">
            @include('member.components.'.$icon, ['class' => 'h-16 w-16'])
        </span>

        <div style="position:relative; display:flex; align-items:flex-start; gap:12px; padding:16px 16px 18px;">
            <span style="display:flex; height:40px; width:40px; flex-shrink:0; align-items:center; justify-content:center; border-radius:9999px; background:{{ $iconBg }}; color:#fff;">
                @include('member.components.'.$icon, ['class' => 'h-5 w-5'])
            </span>

            <div style="min-width:0; flex:1; padding-top:2px;">
                <p style="font-size:13.5px; font-weight:800; line-height:1.35; color:#101828; margin:0;">{{ $reward->nama }}</p>
                <p style="margin:4px 0 0; font-size:11px; color:#8a8a8a;">{{ $isVoucher ? 'Voucher diskon' : 'Produk gratis' }}</p>
            </div>
        </div>
    </div>

    {{-- Perforation seam: dashed line + circular notches punched into both edges --}}
    <div style="position:relative; height:0; z-index:1;">
        <div style="position:absolute; left:14px; right:14px; top:0; border-top:1.5px dashed #ddccb5;"></div>
        <span style="position:absolute; left:-11px; top:0; height:22px; width:22px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
        <span style="position:absolute; right:-11px; top:0; height:22px; width:22px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
    </div>

    {{-- Bottom half --}}
    <div style="position:relative; border-radius:0 0 18px 18px; background:linear-gradient(100deg, #ffffff 65%, {{ $tintRgba }} 100%); display:flex; align-items:center; justify-content:space-between; gap:8px; padding:16px 16px 16px;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div>
                <p style="margin:0; font-size:10px; color:#8a8a8a;">Dibutuhkan</p>
                <p style="margin:2px 0 0; font-size:13px; font-weight:800; color:{{ $cukupPoin && ! $habis ? '#101828' : '#98a2b3' }};">
                    {{ number_format($reward->poin_dibutuhkan, 0, ',', '.') }} Poin
                </p>
            </div>
            <div>
                <p style="margin:0; font-size:10px; color:#8a8a8a;">Stok</p>
                <p style="margin:2px 0 0; font-size:13px; font-weight:800; color:#101828;">
                    {{ is_null($reward->stok) ? 'Unlimited' : ($habis ? 'Habis' : $reward->stok.' pcs') }}
                </p>
            </div>
        </div>

        <span style="flex-shrink:0; border-radius:9999px; padding:10px 22px; font-size:11.5px; font-weight:800; {{ $habis ? 'background:#e4e4e4; color:#98a2b3;' : ($cukupPoin ? 'background:#5a3c27; color:#fff;' : 'background:#e4e4e4; color:#98a2b3;') }}">
            {{ $habis ? 'Habis' : 'Tukar' }}
        </span>
    </div>
</a>