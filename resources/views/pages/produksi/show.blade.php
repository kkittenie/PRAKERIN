@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Produksi" parentTitle="Produksi" :parentUrl="route('produksi.index')" />

<div class="space-y-6">

<x-common.component-card title="Informasi Produksi">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="md:col-span-2">

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
        Produk
        </p>

        <div class="flex items-center gap-4">

            @if($produksi->produk->foto)
                <img src="{{ asset('storage/' . $produksi->produk->foto) }}"
                    alt="{{ $produksi->produk->nama_produk }}"
                    class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-white shrink-0">
            @else
                <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-500 shrink-0 text-xs">
                    No Img
                </div>
            @endif

            <div class="min-w-0">
                <p class="font-semibold text-gray-800 dark:text-white truncate">
                    {{ $produksi->produk->nama_produk }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $produksi->produksiVarians->pluck('kode_barcode')->filter()->unique()->implode(', ') ?: '-' }}
                </p>
            </div>

        </div>

        </div>

<div>

<p class="text-sm text-gray-500 dark:text-gray-400">
Tanggal Produksi
</p>

<p class="font-semibold text-gray-800 dark:text-white">
{{ \Carbon\Carbon::parse($produksi->tanggal)->format('d-m-Y') }}
</p>

</div>

<div>

<p class="text-sm text-gray-500 dark:text-gray-400">
Jam Produksi
</p>

<p class="font-semibold text-gray-800 dark:text-white">
{{ $produksi->jam_produksi ? \Carbon\Carbon::parse($produksi->jam_produksi)->format('H:i') : '-' }}
</p>

</div>

<div>

<p class="text-sm text-gray-500 dark:text-gray-400">
Kadaluwarsa
</p>

    <p class="font-semibold text-red-600 dark:text-red-400">
        @if($produksi->kadaluarsa_tipe === 'jam')
            {{ $produksi->kadaluarsa_jam }} jam setelah produksi
            @if($produksi->kadaluarsa_waktu)
                <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                    (sekitar {{ $produksi->kadaluarsa_waktu->format('d-m-Y H:i') }})
                </span>
            @endif
        @elseif($produksi->kadaluarsa_tanggal)
            {{ \Carbon\Carbon::parse($produksi->kadaluarsa_tanggal)->format('d-m-Y') }}
        @else
            -
        @endif
    </p>

    </div>

    <div>

    <p class="text-sm text-gray-500 dark:text-gray-400">
    Jumlah Hasil
    </p>

    <p class="font-semibold text-green-600 dark:text-green-400">
    {{ $produksi->jumlah_hasil }}
    </p>

</div>

<div class="md:col-span-2">
    @php
        $adaUkuranVarian = $produksi->produksiVarians->contains(fn ($v) => filled($v->ukuran));
        $adaVariasiVarian = $produksi->produksiVarians->contains(fn ($v) => filled($v->variasi));
    @endphp

    {{-- Produk global (tanpa ukuran & variasi) tidak perlu judul rincian --}}
    @if ($adaUkuranVarian || $adaVariasiVarian)
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
            {{ $adaUkuranVarian && $adaVariasiVarian ? 'Rincian per Ukuran & Varian' : ($adaUkuranVarian ? 'Rincian per Ukuran' : 'Rincian per Varian') }}
        </p>
    @endif

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                    @if($adaUkuranVarian)
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Ukuran</th>
                    @endif
                    @if($adaVariasiVarian)
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Variasi</th>
                    @endif
                    @if(!$adaUkuranVarian && !$adaVariasiVarian)
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Varian</th>
                    @endif
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Hasil</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produksi->produksiVarians->filter(fn ($v) => $v->jumlah_hasil > 0 || $v->kebutuhan > 0) as $v)
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">
                        @if($adaUkuranVarian)
                            <td class="px-4 py-3">{{ $v->ukuran ?: '-' }}</td>
                        @endif
                        @if($adaVariasiVarian)
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $v->variasi ?: '-' }}</td>
                        @endif
                        @if(!$adaUkuranVarian && !$adaVariasiVarian)
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $v->nama_lengkap }}</td>
                        @endif
                        <td class="px-4 py-3 text-center font-bold text-green-600 dark:text-green-400">{{ $v->jumlah_hasil }}</td>
                        <td class="px-4 py-3 text-center font-bold text-amber-600 dark:text-amber-400">{{ $v->kebutuhan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div>

<p class="text-sm text-gray-500 dark:text-gray-400">
Pesanan
</p>

<p class="font-semibold text-amber-600 dark:text-amber-400">
{{ $produksi->kebutuhan_produksi ?: 0 }} pcs @if($produksi->rincian_kebutuhan_ukuran !== '-') ({{ $produksi->rincian_kebutuhan_ukuran }}) @endif
</p>

</div>

<div>

<p class="text-sm text-gray-500 dark:text-gray-400">
Dibuat Oleh
</p>

<p class="font-semibold text-gray-800 dark:text-white">
{{ $produksi->user->name }}
</p>

</div>

<div class="md:col-span-2">

<p class="text-sm text-gray-500 dark:text-gray-400">
Keterangan
</p>

<p class="text-gray-800 dark:text-gray-200">

{{ $produksi->keterangan ?: '-' }}

</p>

</div>

</div>

</x-common.component-card>

<x-common.component-card title="Bahan Baku yang Digunakan">

<!-- Desktop Table View -->
<div class="hidden sm:block overflow-x-auto">
<table class="w-full">
<thead>
<tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
<th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
<th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Bahan</th>
<th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Jumlah Dipakai</th>
</tr>
</thead>
<tbody>
@foreach($produksi->detailBahan as $item)
<tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">
<td class="py-4">{{ $loop->iteration }}</td>
<td class="font-medium text-gray-800 dark:text-white">{{ $item->bahanBaku->nama_bahan }}</td>
<td class="text-center font-bold text-amber-700 dark:text-amber-400">{{ \App\Models\Satuan::formatAngka($item->jumlah_terpakai, $item->bahanBaku->satuan?->tipe) }} {{ $item->bahanBaku->satuan?->nama ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- Mobile Card View -->
<div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
@foreach($produksi->detailBahan as $item)
<div class="py-3 flex items-center justify-between">
    <span class="font-bold text-gray-800 dark:text-white text-sm">{{ $item->bahanBaku->nama_bahan }}</span>
    <span class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">
        {{ \App\Models\Satuan::formatAngka($item->jumlah_terpakai, $item->bahanBaku->satuan?->tipe) }} {{ $item->bahanBaku->satuan?->nama ?? '-' }}
    </span>
</div>
@endforeach
</div>

    <div class="mt-6 flex flex-wrap items-center gap-3">

        <a href="{{ route('produksi.index') }}"
            class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

            Kembali

        </a>

        <form action="{{ route('produksi.label', $produksi) }}" method="GET" target="_blank"
            class="inline-flex items-center gap-2">
            @if($produksi->produksiVarians->isEmpty())
            <input type="number" name="jumlah" min="1" max="500" value="{{ $produksi->jumlah_hasil }}"
                class="w-20 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-2 text-sm text-center">
            @endif
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition whitespace-nowrap">
                Cetak Label Barcode
            </button>
        </form>
</div>

</x-common.component-card>

</div>

@endsection