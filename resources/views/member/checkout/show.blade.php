@extends('member.layout', ['title' => 'Status Pesanan'])

@php
    $steps = [
        \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
    ];
    $urutan = array_keys($steps);
    $statusSekarang = $transaksi->status_pesanan;
    $dibatalkan = $statusSekarang === \App\Models\Transaksi::STATUS_DIBATALKAN;
    $indexSekarang = array_search($statusSekarang, $urutan, true);
@endphp

@section('content')

    <div x-data="{}"
         x-init="{{ $dibatalkan || $statusSekarang === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL ? '' : "setInterval(() => { fetch('" . route('member.pesanan.status', $transaksi) . "').then(r => r.json()).then(d => { if (d.status_pesanan !== '{$statusSekarang}') location.reload(); }); }, 8000)" }}">

        <div class="relative flex items-center justify-center">
            <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition active:bg-gray-50">
                <span class="rotate-180">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-sm font-bold text-gray-900">Status Pesanan</h1>
        </div>

        {{-- Kode pickup — besar, buat ditunjukkan ke kasir --}}
        <div class="mt-6 rounded-3xl bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 p-6 text-center text-white">
            @if ($dibatalkan)
                <p class="text-xs font-bold uppercase tracking-wide text-white/70">Pesanan Dibatalkan</p>
            @else
                <p class="text-xs font-bold uppercase tracking-wide text-white/70">Tunjukkan kode ini ke kasir</p>
                <p class="mt-2 text-4xl font-extrabold tracking-[0.3em]">{{ $transaksi->kode_pickup }}</p>
            @endif
            <p class="mt-3 text-[11px] text-white/60">{{ $transaksi->kode_transaksi }}</p>
        </div>

        {{-- Progress status --}}
        @unless ($dibatalkan)
            <div class="mt-6 flex items-center">
                @foreach ($steps as $key => $label)
                    @php
                        $idx = array_search($key, $urutan, true);
                        $selesai = $indexSekarang !== false && $idx <= $indexSekarang;
                    @endphp
                    <div class="flex flex-1 flex-col items-center text-center">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-white {{ $selesai ? 'bg-brand-800' : 'bg-gray-200' }}">
                            @if ($selesai)
                                @include('member.components.icon-check-circle', ['class' => 'h-4 w-4'])
                            @else
                                <span class="h-2 w-2 rounded-full bg-white"></span>
                            @endif
                        </span>
                        <p class="mt-2 max-w-[80px] text-[10px] font-bold {{ $selesai ? 'text-brand-800' : 'text-gray-400' }}">{{ $label }}</p>
                    </div>
                    @if (! $loop->last)
                        <div class="-mt-6 h-0.5 flex-1 {{ $idx < $indexSekarang ? 'bg-brand-800' : 'bg-gray-200' }}"></div>
                    @endif
                @endforeach
            </div>

            <p class="mt-4 text-center text-xs font-semibold text-gray-500">
                @if ($statusSekarang === \App\Models\Transaksi::STATUS_DIPROSES)
                    Pesanan kamu sedang disiapkan oleh toko.
                @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                    Pesanan sudah siap! Silakan ambil di kasir.
                @else
                    Pesanan sudah kamu ambil. Terima kasih!
                @endif
            </p>
        @endunless

        {{-- Detail pesanan --}}
        <div class="mt-6">
            <h2 class="text-sm font-bold text-gray-900">Detail Pesanan</h2>
            <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100">
                @foreach ($transaksi->detailTransaksi as $detail)
                    <div class="flex items-center justify-between p-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-gray-900">{{ $detail->jumlah }}x {{ $detail->nama_produk }}</p>
                            @if ($detail->catatan)
                                <p class="truncate text-[11px] text-gray-400">{{ $detail->catatan }}</p>
                            @endif
                        </div>
                        <p class="shrink-0 text-xs font-bold text-gray-700">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 space-y-1.5 rounded-2xl border border-dashed border-gray-200 p-4">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($transaksi->diskon > 0)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Diskon {{ $transaksi->diskon_label ? "({$transaksi->diskon_label})" : '' }}</span>
                        <span class="font-semibold text-red-500">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-dashed border-gray-200 pt-1.5 text-sm">
                    <span class="font-bold text-gray-900">Total</span>
                    <span class="font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="!mt-2 flex justify-between text-[11px] text-gray-400">
                    <span>Status Pembayaran</span>
                    <span class="font-semibold {{ $transaksi->status_pembayaran === 'Lunas' ? 'text-green-600' : 'text-amber-600' }}">{{ $transaksi->status_pembayaran }}</span>
                </div>
            </div>
        </div>

    </div>

@endsection