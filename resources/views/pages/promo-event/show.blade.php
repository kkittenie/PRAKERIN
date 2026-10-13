@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Promo Produk" parentTitle="Promo Produk" :parentUrl="route('promo-event.index')" />

<div class="space-y-6">

<x-common.component-card title="Informasi Promo Produk">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="md:col-span-2">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Produk</p>
        <div class="flex items-center gap-4">
            @if($promoEvent->produk->foto)
                <img src="{{ asset('storage/' . $promoEvent->produk->foto) }}"
                    alt="{{ $promoEvent->produk->nama_produk }}"
                    class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-white shrink-0">
            @else
                <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-500 shrink-0 text-xs">
                    No Img
                </div>
            @endif
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 dark:text-white truncate">{{ $promoEvent->produk->nama_produk }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $promoEvent->produk->kode_produk ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Event</p>
        <p class="font-semibold text-gray-800 dark:text-white">{{ $promoEvent->nama_event }}</p>
    </div>

    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
        @php
            $now = now();
            if (!$promoEvent->status_aktif) {
                $status = 'Nonaktif'; $badgeColor = 'bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400';
            } elseif ($now < $promoEvent->mulai_berlaku) {
                $status = 'Belum Mulai'; $badgeColor = 'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400';
            } elseif ($now > $promoEvent->selesai_berlaku) {
                $status = 'Kadaluwarsa'; $badgeColor = 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400';
            } else {
                $status = 'Aktif'; $badgeColor = 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
            }
        @endphp
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColor }}">{{ $status }}</span>
    </div>



    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Event</p>
        <p class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($promoEvent->harga_event, 0, ',', '.') }}</p>
    </div>

    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Mulai Berlaku</p>
        <p class="font-semibold text-gray-800 dark:text-white">{{ $promoEvent->mulai_berlaku->format('d M Y H:i') }}</p>
    </div>

    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Selesai Berlaku</p>
        <p class="font-semibold text-gray-800 dark:text-white">{{ $promoEvent->selesai_berlaku->format('d M Y H:i') }}</p>
    </div>

    @php
        $hasVarianBaru = $promoEvent->produk && $promoEvent->produk->produkVarians()->exists();
        if ($hasVarianBaru) {
            $validUkuranIds = $promoEvent->produk->produkVarians
                ->filter(fn ($v) => !empty($v->ukuran))
                ->map(fn ($v) => (int) ($v->ukuran_id ?: (\App\Models\Ukuran::where('nama_ukuran', $v->ukuran)->value('id') ?? 0)))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();
            $ukuransDitampilkan = $promoEvent->ukurans->filter(fn ($u) => $validUkuranIds->contains($u->id));

            $validRasaIds = $promoEvent->produk->produkVarians
                ->filter(fn ($v) => !empty($v->variasi))
                ->map(fn ($v) => (int) ($v->varian_rasa_id ?: (\App\Models\VarianRasa::where('nama_rasa', $v->variasi)->value('id') ?? 0)))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();
            $rasasDitampilkan = $promoEvent->varianRasas->filter(fn ($r) => $validRasaIds->contains($r->id));

            $tampilUkuran = $validUkuranIds->isNotEmpty();
            $tampilRasa = $validRasaIds->isNotEmpty();
        } else {
            $tampilUkuran = (bool) optional($promoEvent->produk)->punya_ukuran;
            $ukuransDitampilkan = $promoEvent->ukurans;
            $tampilRasa = (bool) optional($promoEvent->produk)->punya_varian_rasa;
            $rasasDitampilkan = $promoEvent->varianRasas;
        }
    @endphp

    @if($tampilUkuran)
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Berlaku Untuk Ukuran</p>
        <div class="flex flex-wrap gap-1.5">
            @forelse($ukuransDitampilkan as $ukuran)
                <span class="inline-flex items-center rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-700 dark:text-brand-400">{{ $ukuran->nama_ukuran }}</span>
            @empty
                <span class="text-sm font-semibold text-gray-800 dark:text-white">Semua ukuran</span>
            @endforelse
        </div>
    </div>
    @endif

    @if($tampilRasa)
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Berlaku Untuk Rasa</p>
        <div class="flex flex-wrap gap-1.5">
            @forelse($rasasDitampilkan as $rasa)
                <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $rasa->nama_rasa }}</span>
            @empty
                <span class="text-sm font-semibold text-gray-800 dark:text-white">Semua rasa</span>
            @endforelse
        </div>
    </div>
    @endif

</div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('promo-event.index') }}"
            class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
            Kembali
        </a>
        <a href="{{ route('promo-event.edit', $promoEvent->id) }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-sm transition">
            <span>Edit Promo Produk</span>
        </a>
    </div>

</x-common.component-card>

</div>

@endsection