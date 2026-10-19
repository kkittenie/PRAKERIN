@php
    $showUrl = route('produksi.show', $item->id);
    $perlu = $item->perlu_tindakan;
    $namaEscaped = e(addslashes($item->produk->nama_produk ?? ''));
    $sisa = (int) $item->sisa_belum_disesuaikan;

    // Stok yang dikurangi dipilih per detail produk. Detail dari produksi ini diurutkan paling atas,
    // lalu yang masih punya stok. Detail nonaktif tetap muncul karena stok kadaluwarsa harus bisa dibuang.
    $idDetailProduksi = $item->produksiVarians->pluck('detail_produk_id')->filter()->all();
    if ($item->produk && $item->produk->is_global) {
        $detailUtama = $item->produk->detailProduks->first();
        if ($item->produksiVarians->isNotEmpty() && $item->produksiVarians->contains(fn ($pv) => filled($pv->ukuran) || filled($pv->variasi))) {
            $daftarVarianJson = $item->produksiVarians->map(fn ($pv) => [
                'id' => $detailUtama?->id,
                'nama' => $pv->nama_lengkap ?: 'Standar',
                'stok' => (int) ($detailUtama?->stok ?? 0),
                'dari_produksi' => true,
            ])->values();
        } else {
            $daftarVarianJson = collect([[
                'id' => $detailUtama?->id,
                'nama' => $item->produk->nama_produk,
                'stok' => (int) ($detailUtama?->stok ?? 0),
                'dari_produksi' => true,
            ]]);
        }
    } else {
        $daftarVarianJson = $item->produk
            ? $item->produk->detailProduks
                ->map(fn ($v) => [
                    'id' => $v->id,
                    'nama' => $v->nama_lengkap ?: 'Standar',
                    'stok' => (int) $v->stok,
                    'dari_produksi' => in_array($v->id, $idDetailProduksi),
                ])
                ->sortByDesc(fn ($v) => ($v['dari_produksi'] ? 2 : 0) + ($v['stok'] > 0 ? 1 : 0))
                ->values()
            : collect();
    }
@endphp
<div class="inline-flex items-center gap-0.5 justify-end">
    <a href="{{ $showUrl }}" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    </a>
    <button type="button" title="Kurangi Stok"
        @click="modalOpen = true; produksiId = {{ $item->id }}; produksiNama = '{{ $namaEscaped }}'; sisaBelum = {{ $sisa }}; daftarVarian = {{ $daftarVarianJson->toJson() }}; varianId = (daftarVarian[0] ? daftarVarian[0].id : null); hitungMax()"
        class="produksi-btn-kurangi items-center justify-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 transition {{ $perlu ? 'inline-flex' : 'hidden' }}">Kurangi Stok</button>
</div>