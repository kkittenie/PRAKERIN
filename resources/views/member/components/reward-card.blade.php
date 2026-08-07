@props(['reward', 'member' => null])

@php
    $cukupPoin = $member ? $member->poin >= $reward->poin_dibutuhkan : false;
    $habis = ! is_null($reward->stok) && $reward->stok <= 0;
    $isVoucher = $reward->jenis === 'voucher';
    $icon = $isVoucher ? 'icon-ticket' : 'icon-cup';
    $iconBg = $isVoucher ? '#5a3c27' : '#f97316';
    $gradTint = $isVoucher ? '#faf3ea' : '#fff3e9';
@endphp

<a href="{{ route('member.reward.show', $reward) }}"
   style="position:relative; display:block; overflow:hidden; border-radius:16px; background:linear-gradient(100deg, #ffffff 55%, {{ $gradTint }} 100%); box-shadow:0 6px 16px rgba(20,20,20,.06); {{ $habis ? 'opacity:.5' : '' }}">

    {{-- Watermark icon, Fore-style --}}
    <span style="position:absolute; right:-10px; top:-10px; display:flex; height:80px; width:80px; align-items:center; justify-content:center; color:{{ $iconBg }}; opacity:.12;">
        @include('member.components.'.$icon, ['class' => 'h-16 w-16'])
    </span>

    {{-- Top section --}}
    <div style="position:relative; display:flex; align-items:flex-start; gap:12px; padding:16px 16px 16px;">
        <span style="display:flex; height:40px; width:40px; flex-shrink:0; align-items:center; justify-content:center; border-radius:9999px; background:{{ $iconBg }}; color:#fff;">
            @include('member.components.'.$icon, ['class' => 'h-5 w-5'])
        </span>

        <div style="min-width:0; flex:1; padding-top:2px;">
            <p style="font-size:13.5px; font-weight:800; line-height:1.35; color:#101828; margin:0;">{{ $reward->nama }}</p>
            <p style="margin:4px 0 0; font-size:11px; color:#8a8a8a;">{{ $isVoucher ? 'Voucher diskon' : 'Produk gratis' }}</p>
        </div>
    </div>

    {{-- Ticket perforation: dashed cut with circular notches biting into both edges --}}
    <div style="position:relative; height:1px;">
        <div style="position:absolute; left:0; right:0; top:0; border-top:1.5px dashed #ddd4c2;"></div>
        <span style="position:absolute; left:-10px; top:0; height:20px; width:20px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
        <span style="position:absolute; right:-10px; top:0; height:20px; width:20px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
    </div>

    {{-- Bottom section --}}
    <div style="position:relative; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:14px 16px 16px;">
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