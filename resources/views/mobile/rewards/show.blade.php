@extends('mobile.layout', ['title' => 'Detail Voucher'])

@php
    $tersedia = $voucher->tersedia ?? true;
    $alasan = $voucher->alasan_nonaktif ?? null;
    $sisaJatah = $voucher->sisa_jatah ?? 1;
    $batas = $voucher->batas_penggunaan_per_member ?: 1;
    $icon = 'icon-ticket';

    $potonganLabel = '';
    if ($voucher->jenis_potongan === 'nominal') {
        $potonganLabel = 'Diskon Rp' . number_format($voucher->nominal_potongan, 0, ',', '.');
    } else {
        $potonganLabel = 'Diskon ' . rtrim(rtrim(number_format($voucher->persentase, 2, ',', '.'), '0'), ',') . '%';
        if ($voucher->maksimal_potongan) {
            $potonganLabel .= ' (Maks Rp' . number_format($voucher->maksimal_potongan, 0, ',', '.') . ')';
        }
    }



    $eligibleProduk = $voucher->produks->map(fn ($p) => [
        'id' => $p->id,
        'nama' => $p->nama_produk,
        'kategori' => $p->kategori->nama_kategori ?? 'Lainnya',
        'foto' => $p->foto ? asset('storage/' . $p->foto) : null,
    ])->values();
@endphp

@section('content')

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) --}}
    {{-- ========================================== --}}
    <div class="lg:hidden">
        {{-- Header --}}
        <div class="relative -mx-5 sm:mx-0 -mt-6 flex items-center justify-center border-b border-gray-100 px-5 py-4 sm:rounded-t-3xl">
            <a href="{{ route('member.reward.index') }}"
               onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
                <span class="rotate-180">@include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-theme-sm font-bold text-gray-900">Detail Voucher</h1>
        </div>

        {{-- Hero image --}}
        <div class="relative -mx-5 sm:mx-0 h-36 overflow-hidden bg-gradient-to-br from-brand-100 to-brand-200 md:h-48 sm:rounded-2xl">
            @if ($voucher->gambar)
                <img src="{{ asset('storage/' . $voucher->gambar) }}" alt="{{ $voucher->nama }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/10"></div>
            @else
                <span class="absolute -left-6 -top-6 h-28 w-28 rounded-full bg-white/40"></span>
                <span class="absolute -right-8 top-8 h-20 w-20 rounded-full bg-white/30"></span>
                <span class="relative mx-auto flex h-full w-full items-center justify-center">
                    <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/70 shadow-sm text-brand-700">
                        @include('mobile.components.icon-ticket', ['class' => 'h-9 w-9'])
                    </span>
                </span>
            @endif
        </div>

        {{-- Title & details --}}
        <div class="pt-5 space-y-4">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-theme-lg font-bold leading-tight text-gray-900">{{ $voucher->nama }}</h2>
                    @if($tersedia)
                        <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600">
                            {{ $alasan ?: 'Nonaktif' }}
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-theme-sm leading-relaxed text-gray-500 whitespace-pre-line">
                    {{ $voucher->deskripsi ?: 'Gunakan voucher diskon ini saat checkout atau di kasir outlet.' }}
                </p>
            </div>

            <div class="border-t border-dashed border-gray-200"></div>

            {{-- Info Potongan & Sisa Kuota --}}
            <div class="grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Nilai Potongan</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-brand-800">{{ $potonganLabel }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Sisa Pemakaian</p>
                    <p class="mt-0.5 text-theme-sm font-bold {{ $sisaJatah > 0 ? 'text-gray-800' : 'text-red-500' }}">
                        {{ $sisaJatah }} dari {{ $batas }} kali
                    </p>
                </div>
            </div>

            {{-- Min Transaksi --}}
            <div class="grid grid-cols-2 divide-x divide-gray-100 rounded-2xl border border-gray-100 bg-white">
                <div class="px-4 py-3">
                    <p class="text-theme-xs text-gray-400">Min. Transaksi</p>
                    <p class="mt-0.5 text-theme-sm font-bold text-gray-800">
                        {{ $voucher->min_pembelian > 0 ? 'Rp' . number_format($voucher->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                    </p>
                </div>
            </div>

            {{-- Expandable rows --}}
            <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                <button type="button" onclick="openSheet('sheet-menu-berlaku')"
                        class="flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Menu yang Berlaku ({{ $eligibleProduk->count() }} Produk)</span>
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])
                </button>
                <button type="button" onclick="openSheet('sheet-cara-pakai')"
                        class="flex w-full items-center justify-between px-4 py-3.5 text-left">
                    <span class="text-theme-sm font-bold text-gray-800">Cara Penggunaan</span>
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4 text-gray-300'])
                </button>
            </div>

            {{-- Tombol Pakai Sekarang --}}
            <div class="pt-2">
                @if($tersedia)
                    <a href="{{ route('member.menu') }}"
                       onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
                       class="block w-full text-center rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-800">
                        Pesan Menu & Gunakan Voucher
                    </a>
                @else
                    <button disabled class="w-full rounded-full bg-gray-200 py-3.5 text-theme-sm font-semibold text-gray-500">
                        Voucher Tidak Dapat Digunakan ({{ $alasan ?: 'Nonaktif' }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) --}}
    {{-- ========================================== --}}
    <div class="hidden lg:block lg:max-w-4xl lg:mx-auto lg:my-6 lg:bg-white lg:rounded-3xl lg:border lg:border-gray-200/80 lg:shadow-xl lg:overflow-hidden">
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 bg-gray-50/50">
            <a href="{{ route('member.reward.index') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-gray-600 hover:text-brand-800 transition">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali ke Voucher Saya
            </a>
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-800">Detail Voucher</span>
        </div>

        <div class="grid grid-cols-12 gap-8 p-8 items-start">
            <div class="col-span-5 space-y-6">
                <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-br from-brand-100 to-brand-200 aspect-[4/3] shadow-sm flex items-center justify-center">
                    @if ($voucher->gambar)
                        <img src="{{ asset('storage/' . $voucher->gambar) }}" alt="{{ $voucher->nama }}" class="h-full w-full object-cover">
                    @else
                        <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/70 shadow-sm text-brand-700">
                            @include('mobile.components.icon-ticket', ['class' => 'h-9 w-9'])
                        </span>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200/80 bg-gray-50/50 p-5 space-y-3">
                    <p class="text-xs font-extrabold text-gray-700 uppercase tracking-wider">Status Voucher</p>
                    @if($tersedia)
                        <div class="flex items-center gap-2 text-sm font-bold text-green-700">
                            <span>Siap Digunakan (Sisa {{ $sisaJatah }}x)</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm font-bold text-red-600">
                            <span>{{ $alasan ?: 'Tidak Tersedia' }}</span>
                        </div>
                    @endif

                    <div class="pt-2">
                        @if($tersedia)
                            <a href="{{ route('member.menu') }}"
                               class="block w-full text-center rounded-xl bg-brand-800 py-3 text-xs font-extrabold text-white hover:bg-brand-900 transition">
                                Pesan Menu Sekarang &rarr;
                            </a>
                        @else
                            <button disabled class="w-full rounded-xl bg-gray-200 py-3 text-xs font-bold text-gray-400 cursor-not-allowed">
                                Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-span-7 space-y-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ $voucher->nama }}</h2>
                    <p class="mt-2 text-xs leading-relaxed text-gray-500 whitespace-pre-line">{{ $voucher->deskripsi ?: 'Gunakan voucher diskon ini saat checkout pesanan online atau transaksi offline di kasir.' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-gray-100 bg-brand-50/40 p-4">
                        <p class="text-[11px] font-bold text-brand-700 uppercase">Potongan</p>
                        <p class="mt-1 text-base font-black text-brand-900">{{ $potonganLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-[11px] font-bold text-gray-400 uppercase">Min. Belanja</p>
                        <p class="mt-1 text-base font-black text-gray-800">{{ $voucher->min_pembelian > 0 ? 'Rp' . number_format($voucher->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-[11px] font-bold text-gray-400 uppercase">Batas Pemakaian</p>
                        <p class="mt-1 text-xs font-extrabold text-gray-800">Maks {{ $batas }}x per member (Sisa {{ $sisaJatah }}x)</p>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-extrabold uppercase tracking-wide text-gray-900">Menu Yang Berlaku ({{ $eligibleProduk->count() }})</h3>
                    <div class="grid grid-cols-2 gap-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach ($eligibleProduk as $prod)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-100 bg-gray-50/40">
                                @if($prod['foto'])
                                    <img src="{{ $prod['foto'] }}" class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-100 text-brand-800 font-bold text-xs">
                                        {{ substr($prod['nama'], 0, 2) }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-800 truncate">{{ $prod['nama'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $prod['kategori'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Sheet: Menu yang Berlaku --}}
    <dialog id="sheet-menu-berlaku" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Menu yang Berlaku</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($eligibleProduk as $prod)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                            @if($prod['foto'])
                                <img src="{{ $prod['foto'] }}" class="h-11 w-11 rounded-xl object-cover">
                            @else
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-100 text-brand-800 font-bold text-xs">
                                    {{ substr($prod['nama'], 0, 2) }}
                                </span>
                            @endif
                            <div>
                                <p class="text-xs font-bold text-gray-900">{{ $prod['nama'] }}</p>
                                <p class="text-[11px] text-gray-400">{{ $prod['kategori'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-menu-berlaku')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    {{-- Bottom Sheet: Cara Pakai --}}
    <dialog id="sheet-cara-pakai" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto max-h-[82vh] rounded-t-3xl border-0 p-0">
        <div class="flex max-h-[82vh] flex-col">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="overflow-y-auto px-6 pb-2 pt-3">
                <h3 class="text-center text-theme-md font-bold text-brand-700">Cara Penggunaan Voucher</h3>
                <div class="mt-4 text-theme-sm leading-relaxed text-gray-700">
                    <ol class="list-decimal space-y-2.5 pl-4">
                        <li><strong>Pemesanan Pickup di Aplikasi:</strong> Masuk ke halaman Menu, pilih menu favoritmu, lalu buka Checkout. Pilih voucher ini di bagian Voucher untuk mendapatkan potongan otomatis.</li>
                        <li><strong>Pemesanan di Outlet (Kasir):</strong> Sebutkan nama atau nomor HP member kamu saat bertransaksi di kasir. Kasir akan langsung menerapkan voucher yang berhak kamu gunakan.</li>
                        <li>Setiap transaksi hanya dapat menggunakan maksimal 1 voucher.</li>
                    </ol>
                </div>
            </div>
            <div class="shrink-0 px-6 pb-6 pt-4">
                <button type="button" onclick="closeSheet('sheet-cara-pakai')"
                        class="w-full rounded-full bg-brand-700 py-3.5 text-theme-sm font-semibold text-white active:bg-brand-800">
                    Mengerti
                </button>
            </div>
        </div>
    </dialog>

@endsection