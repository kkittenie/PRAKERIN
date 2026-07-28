@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Produksi" />

<div class="space-y-6">

<x-common.component-card title="Informasi Produksi">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
Produk
</p>

<p class="font-semibold text-gray-800 dark:text-white">
{{ $produksi->produk->nama_produk }}
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
<td class="text-center font-bold text-amber-700 dark:text-amber-400">{{ $item->jumlah_terpakai }} {{ $item->bahanBaku->satuan }}</td>
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
        {{ $item->jumlah_terpakai }} {{ $item->bahanBaku->satuan }}
    </span>
</div>
@endforeach
</div>

<div class="mt-6">

<a
href="{{ route('produksi.index') }}"
class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">

Kembali

</a>

</div>

</x-common.component-card>

</div>

@endsection