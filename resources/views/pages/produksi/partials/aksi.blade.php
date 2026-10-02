@php
    $showUrl = route('produksi.show', $item->id);
    $perlu = $item->perlu_tindakan;
    $namaEscaped = e(addslashes($item->produk->nama_produk ?? ''));
    $sisa = (int) $item->sisa_belum_disesuaikan;
    $punyaUkuran = ($item->produk && $item->produk->punya_ukuran) ? 'true' : 'false';
    $daftarUkuranJson = $item->produk
        ? collect($item->produk->daftar_ukuran)->map(fn ($u) => ['nama' => $u['nama'], 'stok' => $u['stok']])->values()
        : collect();
    $stokTot = (int) ($item->produk->stok ?? 0);
    $daftarRasaProduk = $item->produk ? collect($item->produk->daftar_varian_rasa) : collect();
    $punyaRasa = $daftarRasaProduk->isNotEmpty() ? 'true' : 'false';
    $namaUkuranPerId = $item->produk ? collect($item->produk->daftar_ukuran)->pluck('nama', 'ukuran_id') : collect();
    $daftarRasaJson = $daftarRasaProduk->map(function ($r) use ($namaUkuranPerId) {
        $stokPerNamaUkuran = [];
        foreach (($r['stok_per_ukuran'] ?? []) as $ukuranId => $stok) {
            if (isset($namaUkuranPerId[$ukuranId])) {
                $stokPerNamaUkuran[$namaUkuranPerId[$ukuranId]] = (int) $stok;
            }
        }
        return ['nama' => $r['nama'], 'stok' => (int) $r['stok'], 'stok_ukuran' => (object) $stokPerNamaUkuran];
    })->values();
    $minStok = min($sisa, $stokTot);
@endphp
<div class="inline-flex items-center gap-0.5 justify-end">
    <a href="{{ $showUrl }}" title="Detail" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    </a>
    <button type="button" title="Kurangi Stok"
        @click="modalOpen = true; produksiId = {{ $item->id }}; produksiNama = '{{ $namaEscaped }}'; sisaBelum = {{ $sisa }}; stokTotal = {{ $stokTot }}; punyaUkuran = {{ $punyaUkuran }}; daftarUkuran = {{ $daftarUkuranJson->toJson() }}; ukuran = (daftarUkuran[0] ? daftarUkuran[0].nama : null); punyaRasa = {{ $punyaRasa }}; daftarRasa = {{ $daftarRasaJson->toJson() }}; rasa = (daftarRasa[0] ? daftarRasa[0].nama : null); hitungMax()"
        class="produksi-btn-kurangi items-center justify-center px-2 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 transition {{ $perlu ? 'inline-flex' : 'hidden' }}">Kurangi Stok</button>
</div>