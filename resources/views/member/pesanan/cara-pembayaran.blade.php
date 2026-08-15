@extends('member.layout', ['title' => 'Cara Pembayaran'])

@section('content')

    <style>
        .checkout-sticky-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>

    <div
        class="checkout-sticky-header relative -mx-5 -mt-6 flex h-14 items-center justify-center border-b border-gray-100 px-5 sm:-mx-8 md:-mx-10">
        <a href="{{ route('member.pesanan.show', $transaksi) }}"
           onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
           class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
            <span class="rotate-180">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
        </a>

        <h1 class="text-sm font-bold text-gray-900">Cara Pembayaran</h1>
    </div>

    <div class="mt-6 text-center">
        {{-- TODO: ganti gambar placeholder ini dengan kode QRIS asli toko
             (taruh file baru di public/images/payment/qris-placeholder.png,
             nama & path file tetap sama supaya nggak perlu ubah blade ini). --}}
        <img src="{{ asset('images/payment/qris-placeholder.png') }}" alt="Kode QRIS"
             class="mx-auto h-60 w-60 rounded-2xl border border-gray-100 object-contain">

        <p class="mt-4 text-xs font-medium text-gray-400">Total Tagihan</p>
        <p class="mt-1 text-2xl font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</p>
        <p class="mt-1 text-[11px] text-gray-400">{{ $transaksi->kode_transaksi }}</p>

        <div class="mx-5 mt-6 rounded-2xl border border-dashed border-gray-200 p-4 text-left">
            <p class="text-xs font-bold text-gray-900">Langkah pembayaran</p>
            <ol class="mt-2 space-y-1.5 text-[11px] text-gray-500">
                <li>1. Buka aplikasi bank atau e-wallet kamu.</li>
                <li>2. Pilih menu Scan QRIS, lalu scan kode di atas.</li>
                <li>3. Pastikan nominalnya sesuai total tagihan, lalu bayar.</li>
                <li>4. Kembali ke halaman pesanan, upload bukti transfernya.</li>
            </ol>
        </div>

        <a href="{{ route('member.pesanan.show', $transaksi) }}"
           onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
           class="mx-5 mt-6 block rounded-xl bg-brand-800 py-2.5 text-xs font-bold text-white transition active:bg-brand-900">
            Sudah Bayar, Upload Bukti
        </a>
    </div>

@endsection