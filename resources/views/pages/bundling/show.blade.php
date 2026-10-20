@extends('layouts.app')

@section('content')

    <x-common.page-breadcrumb pageTitle="Detail Bundling" parentTitle="Bundling" :parentUrl="route('bundling.index')" />

    <div class="space-y-6">

        <x-common.component-card title="Informasi Bundling">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Gambar Bundling --}}
                <div class="md:col-span-1">
                    <img src="{{ $bundling->gambar ? asset('storage/'.$bundling->gambar) : 'https://via.placeholder.com/300x300?text=No+Image' }}"
                         class="w-full max-w-[220px] aspect-square rounded-xl object-cover border border-gray-200 dark:border-gray-700">
                </div>

                {{-- Info Bundling --}}
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Nama Bundling
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $bundling->nama_bundling }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Kode
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $bundling->kode_bundling }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Harga Bundling
                        </p>
                        <p class="font-semibold text-green-600 dark:text-green-400">
                            Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Jenis Produk
                        </p>
                        <p class="font-semibold text-gray-800 dark:text-white">
                            @if($bundling->isBebas())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    Produk Bebas
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    Produk Tetap
                                </span>
                            @endif
                        </p>
                    </div>

                    @if($bundling->isBebas())
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Total Isi Bundling
                            </p>
                            <p class="font-bold text-amber-600 dark:text-amber-400 text-base">
                                {{ $bundling->targetQtyBebas() }} pcs
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Jumlah Produk
                            </p>
                            <p class="font-semibold text-amber-600 dark:text-amber-400">
                                {{ $bundling->jumlah_produk }} pcs
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Status Produksi
                        </p>
                        <p class="mt-0.5">
                            @if($bundling->perlu_diproduksi)
                                <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                    ⚠ Sedang Diproduksi
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                    ✓ Stok Ada
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Deskripsi
                        </p>
                        <p class="text-gray-800 dark:text-gray-200">
                            {{ $bundling->deskripsi ?: '-' }}
                        </p>
                    </div>

                </div>

            </div>

        </x-common.component-card>

        <x-common.component-card title="Isi Bundling">

            @if($bundling->isBebas())
                <div class="mb-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-300 text-xs font-semibold">
                    💡 <strong>Produk Bebas:</strong> Pembeli memilih sendiri total <span class="text-amber-700 dark:text-amber-400 font-extrabold text-sm">{{ $bundling->targetQtyBebas() }} pcs</span> dari rasa yang tersedia untuk produk dan ukuran di bawah ini. Rasa dipilih pembeli sendiri.
                </div>
            @endif

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">No</th>
                            <th class="py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Produk</th>
                            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Ukuran</th>
                            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $bundling->isBebas() ? 'Rasa yang Bisa Dipilih' : 'Rasa' }}</th>
                            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $bundling->isBebas() ? 'Jumlah' : 'Qty Bundling' }}</th>
                            <th class="py-4 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Status Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bundling->items as $item)
                            @php
                                $p = $item->produk;
                                $v = $item->detailProduk;
                                if ($bundling->isBebas()) {
                                    // Produk Bebas: satu baris = produk + ukuran, rasa dipilih pembeli dari semua rasa aktif.
                                    $opsiRasa = $item->opsiRasa();
                                    $stokUkuran = (int) $opsiRasa->sum(fn ($d) => max(0, (int) $d->stok));
                                    $stokHabis = $opsiRasa->isEmpty() || $stokUkuran <= 0;
                                    $ukuranTampil = $item->ukuran ?: '-';
                                    $rasaTampil = $opsiRasa->pluck('variasi')->filter()->implode(', ') ?: null;
                                } elseif ($v) {
                                    $stokUkuran = (int) $v->stok;
                                    $stokHabis = !$v->status_aktif || $v->perlu_diproduksi || $stokUkuran <= 0;
                                    $ukuranTampil = $v->ukuran ?? '-';
                                    $rasaTampil = $v->variasi;
                                } else {
                                    $stokUkuran = $p ? $p->stokUntukUkuranRasa($item->ukuran, $item->varian_rasa) : 0;
                                    $stokHabis = !$p || $p->status_ketersediaan === 'perlu_diproduksi' || $stokUkuran <= 0;
                                    $ukuranTampil = $item->ukuran ?? '-';
                                    $rasaTampil = $item->varian_rasa;
                                }
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-750 dark:text-gray-300">
                                <td class="py-4">{{ $loop->iteration }}</td>
                                <td class="font-medium text-gray-800 dark:text-white">{{ $p ? $p->nama_produk : '-' }}</td>
                                <td class="text-center text-gray-500 dark:text-gray-400">{{ $ukuranTampil }}</td>
                                <td class="text-center text-gray-500 dark:text-gray-400">
                                    @if($rasaTampil)
                                        {{ $rasaTampil }}
                                    @elseif($p && $p->punya_varian_rasa && !$bundling->isBebas())
                                        <span class="text-xs font-semibold text-red-500">Belum dipilih</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center font-bold text-amber-700 dark:text-amber-400">
                                    @if($bundling->isBebas())
                                        <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:text-amber-300">Rasa dipilih pembeli</span>
                                    @else
                                        {{ $item->qty }} pcs
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stokHabis)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/15 px-2.5 py-0.5 text-xs font-bold text-red-600 dark:text-red-400">
                                            Sedang Diproduksi (Stok: {{ $stokUkuran }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                            Stok Ada ({{ $stokUkuran }} tersedia)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($bundling->items as $item)
                    @php
                        $p = $item->produk;
                        $v = $item->detailProduk;
                        if ($bundling->isBebas()) {
                            // Produk Bebas: satu baris = produk + ukuran, rasa dipilih pembeli dari semua rasa aktif.
                            $opsiRasa = $item->opsiRasa();
                            $stokUkuran = (int) $opsiRasa->sum(fn ($d) => max(0, (int) $d->stok));
                            $stokHabis = $opsiRasa->isEmpty() || $stokUkuran <= 0;
                            $ukuranTampil = $item->ukuran ?: null;
                            $rasaTampil = $opsiRasa->pluck('variasi')->filter()->implode(', ') ?: null;
                        } elseif ($v) {
                            $stokUkuran = (int) $v->stok;
                            $stokHabis = !$v->status_aktif || $v->perlu_diproduksi || $stokUkuran <= 0;
                            $ukuranTampil = $v->ukuran;
                            $rasaTampil = $v->variasi;
                        } else {
                            $stokUkuran = $p ? $p->stokUntukUkuranRasa($item->ukuran, $item->varian_rasa) : 0;
                            $stokHabis = !$p || $p->status_ketersediaan === 'perlu_diproduksi' || $stokUkuran <= 0;
                            $ukuranTampil = $item->ukuran;
                            $rasaTampil = $item->varian_rasa;
                        }
                    @endphp
                    <div class="py-3 flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ $p ? $p->nama_produk : '-' }}
                                @if($ukuranTampil)
                                    <span class="font-normal text-gray-400 dark:text-gray-500">({{ $ukuranTampil }})</span>
                                @endif
                                @if($rasaTampil)
                                    <span class="font-normal text-gray-400 dark:text-gray-500">· {{ $rasaTampil }}</span>
                                @elseif($p && $p->punya_varian_rasa && !$bundling->isBebas())
                                    <span class="text-xs font-semibold text-red-500">· Rasa belum dipilih</span>
                                @endif
                            </span>
                            <span
                                class="inline-flex rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">
                                @if($bundling->isBebas())
                                    Rasa dipilih pembeli
                                @else
                                    {{ $item->qty }} pcs
                                @endif
                            </span>
                        </div>
                        <div>
                            @if($stokHabis)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/15 px-2.5 py-0.5 text-xs font-bold text-red-600 dark:text-red-400">
                                            Sedang Diproduksi (Stok: {{ $stokUkuran }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                    Stok Ada ({{ $stokUkuran }} tersedia)
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>


            <div class="mt-6 flex justify-end gap-3">

                <a href="{{ route('bundling.index') }}"
                    class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Kembali
                </a>

                <a href="{{ route('bundling.edit', $bundling->id) }}"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                    Edit Bundling
                </a>

            </div>

        </x-common.component-card>

    </div>

@endsection