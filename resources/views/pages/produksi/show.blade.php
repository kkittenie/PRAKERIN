@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Produksi" />

<div class="space-y-6">

<x-common.component-card title="Informasi Produksi">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>

<p class="text-sm text-gray-500">
Tanggal Produksi
</p>

<p class="font-semibold">
{{ \Carbon\Carbon::parse($produksi->tanggal)->format('d-m-Y') }}
</p>

</div>

<div>

<p class="text-sm text-gray-500">
Produk
</p>

<p class="font-semibold">
{{ $produksi->produk->nama_produk }}
</p>

</div>

<div>

<p class="text-sm text-gray-500">
Jumlah Hasil
</p>

<p class="font-semibold text-green-600">
{{ $produksi->jumlah_hasil }}
</p>

</div>

<div>

<p class="text-sm text-gray-500">
Dibuat Oleh
</p>

<p class="font-semibold">
{{ $produksi->user->name }}
</p>

</div>

<div class="md:col-span-2">

<p class="text-sm text-gray-500">
Keterangan
</p>

<p>

{{ $produksi->keterangan ?: '-' }}

</p>

</div>

</div>

</x-common.component-card>

<x-common.component-card title="Bahan Baku yang Digunakan">

<div class="overflow-x-auto">

<table class="w-full">

<thead>

<tr class="border-b bg-gray-50">

<th class="py-4 text-left text-sm font-semibold text-gray-700">
No
</th>

<th class="py-4 text-left text-sm font-semibold text-gray-700">
Nama Bahan
</th>

<th class="py-4 text-center text-sm font-semibold text-gray-700">
Jumlah Dipakai
</th>

</tr>

</thead>

<tbody>

@foreach($produksi->detailBahan as $item)

<tr class="border-b">

<td class="py-4">

{{ $loop->iteration }}

</td>

<td>

{{ $item->bahanBaku->nama_bahan }}

</td>

<td class="text-center">

{{ $item->jumlah_terpakai }}
{{ $item->bahanBaku->satuan }}

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<div class="mt-6">

<a
href="{{ route('produksi.index') }}"
class="rounded-lg border px-5 py-2">

Kembali

</a>

</div>

</x-common.component-card>

</div>

@endsection