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
    $icon = 'icon-ticket';
    $iconBg = $tersedia ? '#5a3c27' : '#9ca3af';
    $tintRgba = $tersedia ? 'rgba(90,60,39,.05)' : 'rgba(156,163,175,.08)';

    $potonganLabel = '';
    if ($voucher->jenis_potongan === 'nominal') {
        $potonganLabel = 'Diskon Rp' . number_format($voucher->nominal_potongan, 0, ',', '.');
    } else {
        $potonganLabel = 'Diskon ' . rtrim(rtrim(number_format($voucher->persentase, 2, ',', '.'), '0'), ',') . '%';
        if ($voucher->maksimal_potongan) {
            $potonganLabel .= ' (Maks Rp' . number_format($voucher->maksimal_potongan, 0, ',', '.') . ')';
        }
    }
@endphp

<a href="{{ route('member.reward.show', $voucher) }}"
   onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
   class="lg:desktop-reward-card"
   style="position:relative; display:block; border-radius:18px; box-shadow:0 2px 6px rgba(16,24,40,.06); {{ !$tersedia ? 'opacity:.65; filter:grayscale(30%);' : '' }}">

    {{-- Top half --}}
    <div class="lg:desktop-reward-top" style="position:relative; overflow:hidden; border-radius:18px 18px 0 0; background:linear-gradient(100deg, #ffffff 65%, {{ $tintRgba }} 100%);">

        <span class="lg:desktop-reward-watermark" style="position:absolute; right:-14px; top:-14px; display:flex; height:84px; width:84px; align-items:center; justify-content:center; color:{{ $iconBg }}; opacity:.10;">
            @include('mobile.components.icon-ticket', ['class' => 'h-16 w-16'])
        </span>

        <div style="position:relative; display:flex; align-items:flex-start; gap:12px; padding:16px 16px 14px;">
            <span class="lg:desktop-reward-icon" style="display:flex; height:40px; width:40px; flex-shrink:0; align-items:center; justify-content:center; border-radius:9999px; background:{{ $iconBg }}; color:#fff;">
                @include('mobile.components.icon-ticket', ['class' => 'h-5 w-5'])
            </span>

            <div style="min-width:0; flex:1; padding-top:2px;">
                <p class="lg:desktop-reward-title" style="font-size:13.5px; font-weight:800; line-height:1.35; color:#101828; margin:0;">{{ $voucher->nama }}</p>
                <p class="lg:desktop-reward-discount" style="margin:4px 0 0; font-size:11.5px; font-weight:700; color:{{ $tersedia ? '#b45309' : '#6b7280' }};">
                    {{ $potonganLabel }}
                </p>
                @if($voucher->min_pembelian > 0)
                    <p class="lg:desktop-reward-min" style="margin:2px 0 0; font-size:10px; color:#8a8a8a;">
                        Min. belanja Rp{{ number_format($voucher->min_pembelian, 0, ',', '.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Perforation seam --}}
    <div style="position:relative; height:0; z-index:1;">
        <div class="lg:desktop-reward-seam" style="position:absolute; left:14px; right:14px; top:0; border-top:1.5px dashed #ddccb5;"></div>
        <span class="lg:desktop-reward-scallop" style="position:absolute; left:-11px; top:0; height:22px; width:22px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
        <span class="lg:desktop-reward-scallop" style="position:absolute; right:-11px; top:0; height:22px; width:22px; border-radius:9999px; background:#fff; transform:translateY(-50%);"></span>
    </div>

    {{-- Bottom half --}}
    <div class="lg:desktop-reward-bottom" style="position:relative; border-radius:0 0 18px 18px; background:linear-gradient(100deg, #ffffff 65%, {{ $tintRgba }} 100%); display:flex; align-items:center; justify-content:space-between; gap:8px; padding:12px 16px 14px;">
        <div style="min-width:0; flex:1;">
            <p class="lg:desktop-reward-batas-label" style="margin:0; font-size:10px; color:#8a8a8a;">Batas Penggunaan</p>
            <p class="lg:desktop-reward-batas-val" style="margin:1px 0 0; font-size:11.5px; font-weight:700; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                Maks {{ $batas }}x / Member
            </p>
        </div>

        <div>
            @if($tersedia)
                <span class="lg:desktop-reward-badge" style="display:inline-block; border-radius:9999px; padding:6px 14px; font-size:10.5px; font-weight:800; background:#fef3c7; color:#92400e;">
                    Sisa {{ $sisaJatah }}x Pakai
                </span>
            @else
                <span class="lg:desktop-reward-badge lg:desktop-reward-badge-disabled" style="display:inline-block; border-radius:9999px; padding:6px 12px; font-size:10px; font-weight:700; background:#fee2e2; color:#b91c1c;">
                    {{ $alasan ?: 'Nonaktif' }}
                </span>
            @endif
        </div>
    </div>
</a>