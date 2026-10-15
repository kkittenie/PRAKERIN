<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
use App\Models\Produk;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KeranjangController extends Controller
{
    public function tambah(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->status_aktif, 404);

            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:50',
                'detail_produk_id' => 'nullable|integer',
                'ukuran' => 'nullable|string|max:50',
                'varian_rasa' => 'nullable|string|max:100',
                'catatan' => 'nullable|string|max:255',
            ]);

            $produkVarian = null;
            if (!empty($validated['detail_produk_id'])) {
                $produkVarian = $produk->detailProduks()->where('id', $validated['detail_produk_id'])->where('status_aktif', true)->first();
            } elseif ($produk->detailProduks()->exists()) {
                $produkVarian = $produk->detailProduks()
                    ->where('status_aktif', true)
                    ->where(function ($q) use ($validated) {
                        if (!empty($validated['ukuran'])) {
                            $q->where('ukuran', $validated['ukuran']);
                        }
                        if (!empty($validated['varian_rasa'])) {
                            $q->where('variasi', $validated['varian_rasa']);
                        }
                    })->first();
            }

            if ($produk->detailProduks()->exists() && !$produkVarian) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Varian yang dipilih sedang nonaktif atau tidak tersedia.',
                    ], 422);
                }
                return back()->with('error', 'Varian yang dipilih sedang nonaktif atau tidak tersedia.');
            }

            if ($produkVarian) {
                $ukuran = $produkVarian->ukuran;
                $varianRasa = $produkVarian->variasi;
                $hargaAsal = (float) $produkVarian->harga_jual;
                $promoBerlaku = $produk->promoAktifUntukDetail($produkVarian);

                $diskonSatuan = 0;
                if ($promoBerlaku) {
                    if ($promoBerlaku->jenis_potongan === 'nominal') {
                        $diskonSatuan = (float) $promoBerlaku->nominal_potongan;
                    } else {
                        $diskonSatuan = ((float) ($promoBerlaku->persentase ?? 0) / 100) * $hargaAsal;
                    }
                }

                $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
                $foto = $produkVarian->foto ?: $produk->foto;
                $produkVarianId = $produkVarian->id;
            } else {
                $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? $produk->ukuranDefault()) : null;
                $hargaAsal = $produk->getHarga($ukuran);
                $promoBerlaku = $produk->promoUntukHitungHarga($ukuran);

                $diskonSatuan = 0;
                if ($promoBerlaku) {
                    if ($promoBerlaku->jenis_potongan === 'nominal') {
                        $diskonSatuan = (float) $promoBerlaku->nominal_potongan;
                    } else {
                        $diskonSatuan = ((float) ($promoBerlaku->persentase ?? 0) / 100) * $hargaAsal;
                    }
                }

                $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
                $varianRasa = $this->tentukanVarianRasa($produk, $validated['varian_rasa'] ?? null);
                $foto = $produk->foto;
                $produkVarianId = null;
            }

            MemberCart::tambah([
                'produk_id' => $produk->id,
                'detail_produk_id' => $produkVarianId,
                'nama_produk' => $produk->nama_produk,
                'foto' => $foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $promoBerlaku?->nama_promo,
                'jenis_promo' => $promoBerlaku?->jenis_potongan,
                'persentase_promo' => $promoBerlaku?->persentase,
                'min_pembelian_promo' => $promoBerlaku?->min_pembelian ?? 0,
                'ukuran' => $ukuran,
                'varian_rasa' => $varianRasa,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
            ]);

            $pesan = "{$produk->nama_produk} ditambahkan ke keranjang.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $pesan,
                    'item' => [
                        'nama' => $produk->nama_produk,
                        'foto_url' => $foto ? asset('storage/' . $foto) : null,
                        'ukuran' => $ukuran ?: null,
                        'varian_rasa' => $varianRasa,
                        'jumlah' => (int) $validated['jumlah'],
                    ],
                    'cart' => $this->ringkasanKeranjang(),
                    'checkout_url' => route('member.checkout.index'),
                    'menu_url' => route('member.menu'),
                ]);
            }

            return redirect()
                ->route('member.menu')
                ->with('success', $pesan);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambahBundling(Request $request, Bundling $bundling)
    {
        try {
            abort_unless($bundling->status_aktif, 404);

            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:50',
                'catatan' => 'nullable|string|max:255',
                'pilihan_khusus' => 'nullable|array',
                // item_id = id baris isi bundling (produk + ukuran), detail_produk_id = rasa yang dipilih pembeli.
                'pilihan_khusus.*.item_id' => 'required_with:pilihan_khusus|integer',
                'pilihan_khusus.*.detail_produk_id' => 'required_with:pilihan_khusus|integer',
                // Boleh 0: form selalu ngirim baris utk SEMUA pilihan bundling
                // (termasuk yang gak dipilih member). Baris qty 0 dibuang di
                // validasiPilihanBebas(), bukan ditolak validasi di sini.
                'pilihan_khusus.*.qty' => 'required_with:pilihan_khusus|integer|min:0',
                'pilihan_khusus.*.catatan' => 'nullable|string|max:255',
            ]);

            // Bundling bebas: total pilihan harus pas dengan total isi, dan hanya boleh
            // dari pilihan yang ditentukan admin. Bundling tetap: pilihan diabaikan.
            $pilihanKhusus = $bundling->validasiPilihanBebas($validated['pilihan_khusus'] ?? []);

            $rincianBundling = $this->susunRincianBundling($bundling, $pilihanKhusus);

            MemberCart::tambah([
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
                'pilihan_khusus' => $pilihanKhusus,
                'rincian_bundling' => $rincianBundling,
            ]);

            $pesan = "{$bundling->nama_bundling} ditambahkan ke keranjang.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $pesan,
                    'item' => [
                        'nama' => $bundling->nama_bundling,
                        'foto_url' => $bundling->gambar ? asset('storage/' . $bundling->gambar) : null,
                        'ukuran' => null,
                        'jumlah' => (int) $validated['jumlah'],
                    ],
                    'cart' => $this->ringkasanKeranjang(),
                    'checkout_url' => route('member.checkout.index'),
                    'menu_url' => route('member.menu'),
                ]);
            }

            return redirect()
                ->route('member.menu')
                ->with('success', $pesan);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function tambahPromoEvent(Request $request, PromoProdukEvent $promoEvent)
    {
        try {
            abort_unless($promoEvent->sedang_berlangsung, 404);

            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:50',
                'catatan' => 'nullable|string|max:255',
            ]);

            MemberCart::tambah([
                'promo_event_id' => $promoEvent->id,
                'produk_id' => $promoEvent->produk_id,
                'isi_event' => $promoEvent->isi_event,
                'nama_produk' => $promoEvent->nama_event,
                'foto' => $promoEvent->produk?->foto,
                'harga_satuan' => (float) $promoEvent->harga_event,
                'ukuran' => $promoEvent->ukuranTunggalUntukProduksi(),
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
            ]);

            $pesan = "{$promoEvent->nama_event} ditambahkan ke keranjang.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $pesan,
                    'item' => [
                        'nama' => $promoEvent->nama_event,
                        'foto_url' => $promoEvent->produk?->foto ? asset('storage/' . $promoEvent->produk->foto) : null,
                        'ukuran' => $promoEvent->ukuranTunggalUntukProduksi(),
                        'jumlah' => (int) $validated['jumlah'],
                    ],
                    'cart' => [
                        'jumlah' => MemberCart::jumlahItem(),
                        'subtotal' => MemberCart::subtotal(),
                    ],
                    'checkout_url' => route('member.checkout.index'),
                    'menu_url' => route('member.menu'),
                ]);
            }

            return redirect()->route('member.menu')->with('success', $pesan);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Perbarui item produk yang sudah ada di keranjang — dipakai dari
     * halaman produk saat member membukanya lewat "Change" di checkout.
     */
    public function perbarui(Request $request, string $itemId)
    {
        try {
            $validated = $request->validate([
                'produk_id' => 'required|integer|exists:produks,id',
                'detail_produk_id' => 'nullable|integer',
                'jumlah' => 'required|integer|min:1|max:50',
                'ukuran' => 'nullable|string|max:50',
                'varian_rasa' => 'nullable|string|max:100',
                'catatan' => 'nullable|string|max:255',
            ]);

            $produk = Produk::findOrFail($validated['produk_id']);
            abort_unless($produk->status_aktif, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.produk.show', $produk)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            $produkVarian = null;
            if (!empty($validated['detail_produk_id'])) {
                $produkVarian = $produk->detailProduks()->where('id', $validated['detail_produk_id'])->where('status_aktif', true)->first();
            } elseif ($produk->detailProduks()->exists()) {
                $produkVarian = $produk->detailProduks()
                    ->where('status_aktif', true)
                    ->where(function ($q) use ($validated) {
                        if (!empty($validated['ukuran'])) {
                            $q->where('ukuran', $validated['ukuran']);
                        }
                        if (!empty($validated['varian_rasa'])) {
                            $q->where('variasi', $validated['varian_rasa']);
                        }
                    })->first();
            }

            if ($produk->detailProduks()->exists() && !$produkVarian) {
                return back()->with('error', 'Varian yang dipilih sedang nonaktif atau tidak tersedia.');
            }

            if ($produkVarian) {
                $ukuran = $produkVarian->ukuran;
                $varianRasa = $produkVarian->variasi;
                $hargaAsal = (float) $produkVarian->harga_jual;
                $promoBerlaku = $produk->promoAktifUntukDetail($produkVarian);

                $diskonSatuan = 0;
                if ($promoBerlaku) {
                    if ($promoBerlaku->jenis_potongan === 'nominal') {
                        $diskonSatuan = (float) $promoBerlaku->nominal_potongan;
                    } else {
                        $diskonSatuan = ((float) ($promoBerlaku->persentase ?? 0) / 100) * $hargaAsal;
                    }
                }

                $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
                $foto = $produkVarian->foto ?: $produk->foto;
                $produkVarianId = $produkVarian->id;
            } else {
                $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? $produk->ukuranDefault()) : null;
                $hargaAsal = $produk->getHarga($ukuran);
                $promoBerlaku = $produk->promoUntukHitungHarga($ukuran);

                $diskonSatuan = 0;
                if ($promoBerlaku) {
                    if ($promoBerlaku->jenis_potongan === 'nominal') {
                        $diskonSatuan = (float) $promoBerlaku->nominal_potongan;
                    } else {
                        $diskonSatuan = ((float) ($promoBerlaku->persentase ?? 0) / 100) * $hargaAsal;
                    }
                }

                $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
                $varianRasa = $this->tentukanVarianRasa($produk, $validated['varian_rasa'] ?? null);
                $foto = $produk->foto;
                $produkVarianId = null;
            }

            MemberCart::perbarui($itemId, [
                'produk_id' => $produk->id,
                'detail_produk_id' => $produkVarianId,
                'nama_produk' => $produk->nama_produk,
                'foto' => $foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $promoBerlaku?->nama_promo,
                'jenis_promo' => $promoBerlaku?->jenis_potongan,
                'persentase_promo' => $promoBerlaku?->persentase,
                'min_pembelian_promo' => $promoBerlaku?->min_pembelian ?? 0,
                'ukuran' => $ukuran,
                'varian_rasa' => $varianRasa,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
            ]);

            return redirect()
                ->route('member.checkout.index')
                ->with('success', "{$produk->nama_produk} diperbarui.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Perbarui item bundling yang sudah ada di keranjang.
     */
    public function perbaruiBundling(Request $request, string $itemId)
    {
        try {
            $validated = $request->validate([
                'bundling_id' => 'required|integer|exists:bundlings,id',
                'jumlah' => 'required|integer|min:1|max:50',
                'catatan' => 'nullable|string|max:255',
                'pilihan_khusus' => 'nullable|array',
                // item_id = id baris isi bundling (produk + ukuran), detail_produk_id = rasa yang dipilih pembeli.
                'pilihan_khusus.*.item_id' => 'required_with:pilihan_khusus|integer',
                'pilihan_khusus.*.detail_produk_id' => 'required_with:pilihan_khusus|integer',
                // Boleh 0: form selalu ngirim baris utk SEMUA pilihan bundling
                // (termasuk yang gak dipilih member). Baris qty 0 dibuang di
                // validasiPilihanBebas(), bukan ditolak validasi di sini.
                'pilihan_khusus.*.qty' => 'required_with:pilihan_khusus|integer|min:0',
                'pilihan_khusus.*.catatan' => 'nullable|string|max:255',
            ]);

            $bundling = Bundling::findOrFail($validated['bundling_id']);
            abort_unless($bundling->status_aktif, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.bundling.show', $bundling)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            // Bundling bebas: total pilihan harus pas dengan total isi, dan hanya boleh
            // dari pilihan yang ditentukan admin. Bundling tetap: pilihan diabaikan.
            $pilihanKhusus = $bundling->validasiPilihanBebas($validated['pilihan_khusus'] ?? []);

            $rincianBundling = $this->susunRincianBundling($bundling, $pilihanKhusus);

            MemberCart::perbarui($itemId, [
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
                'pilihan_khusus' => $pilihanKhusus,
                'rincian_bundling' => $rincianBundling,
            ]);

            return redirect()
                ->route('member.checkout.index')
                ->with('success', "{$bundling->nama_bundling} diperbarui.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function perbaruiPromoEvent(Request $request, string $itemId)
    {
        try {
            $validated = $request->validate([
                'promo_event_id' => 'required|integer|exists:promo_produk_events,id',
                'jumlah' => 'required|integer|min:1|max:50',
                'catatan' => 'nullable|string|max:255',
            ]);

            $promoEvent = PromoProdukEvent::with('produk')->findOrFail($validated['promo_event_id']);
            abort_unless($promoEvent->sedang_berlangsung, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.promo-event.show', $promoEvent)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            MemberCart::perbarui($itemId, [
                'promo_event_id' => $promoEvent->id,
                'produk_id' => $promoEvent->produk_id,
                'isi_event' => $promoEvent->isi_event,
                'nama_produk' => $promoEvent->nama_event,
                'foto' => $promoEvent->produk?->foto,
                'harga_satuan' => (float) $promoEvent->harga_event,
                'ukuran' => $promoEvent->ukuranTunggalUntukProduksi(),
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
            ]);

            return redirect()
                ->route('member.checkout.index')
                ->with('success', "{$promoEvent->nama_event} diperbarui.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ubahJumlah(Request $request, string $itemId)
    {
        try {
            $validated = $request->validate([
                'jumlah' => 'required|integer|min:0|max:50',
            ]);

            $item = MemberCart::cariItem($itemId);
            if ($item && !empty($item['is_reward_stamp'])) {
                if ($validated['jumlah'] <= 0) {
                    Session::forget(CheckoutController::SESSION_STAMP_REDEMPTION_KEY);
                    MemberCart::hapus($itemId);
                    return back()->with('success', 'Hadiah stamp dilepas dari keranjang.');
                }
                // Reward stamp quantity cannot be increased
                return back();
            }

            MemberCart::ubahJumlah($itemId, $validated['jumlah']);

            if (MemberCart::isEmpty()) {
                return redirect()->route('member.menu')->with('info', 'Item dihapus. Keranjang kamu sekarang kosong.');
            }

            return back();
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapus(string $itemId)
    {
        try {
            $item = MemberCart::cariItem($itemId);
            if ($item && (!empty($item['is_reward_stamp']) || !empty($item['stamp_redemption_id']))) {
                Session::forget(CheckoutController::SESSION_STAMP_REDEMPTION_KEY);
            }

            MemberCart::hapus($itemId);

            if (MemberCart::isEmpty()) {
                return redirect()->route('member.menu')->with('info', 'Item dihapus. Keranjang kamu sekarang kosong.');
            }

            return back()->with('success', 'Item dihapus dari keranjang.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function ringkasanKeranjang(): array
    {
        return [
            'jumlah' => MemberCart::jumlahItem(),
            'subtotal' => MemberCart::subtotal(),
        ];
    }

    /**
     * Tentukan rasa yang valid untuk produk. Nama dinormalisasi ke yang tersimpan di
     * master (huruf besar/kecil), dan kalau gak dikirim / gak terdaftar di produk ini,
     * jatuh ke rasa pertama yang aktif — sama kayak ukuranDefault(). Tanpa ini, nama rasa
     * sembarang bisa masuk keranjang dan stok rasa gak ikut berkurang saat pesanan diproses.
     */
    private function tentukanVarianRasa(Produk $produk, ?string $varianRasa): ?string
    {
        if (! $produk->punya_varian_rasa) {
            return null;
        }

        $daftar = $produk->daftar_varian_rasa;

        foreach ($daftar as $opsi) {
            if ($varianRasa !== null && strcasecmp($opsi['nama'], $varianRasa) === 0) {
                return $opsi['nama'];
            }
        }

        return $daftar[0]['nama'] ?? null;
    }

    private function susunRincianBundling(Bundling $bundling, array $pilihanKhusus = []): array
    {
        $rincian = [];
        $bundling->loadMissing(['items.produk', 'items.detailProduk']);

        if ($bundling->isBebas()) {
            // Satu baris rincian per rasa yang dipilih pembeli (produk + ukuran + rasa + qty).
            foreach ($bundling->barisIsi($pilihanKhusus) as $baris) {
                $bi = $baris['item'];
                $rincian[] = [
                    'item_id' => $bi->id,
                    'produk_id' => $bi->produk_id,
                    'detail_produk_id' => $bi->detail_produk_id,
                    'nama_produk' => $bi->produk?->nama_produk ?? 'Produk',
                    'ukuran' => $bi->ukuran,
                    'varian_rasa' => $bi->varian_rasa,
                    'qty' => (int) $baris['qty'],
                    'catatan' => $baris['catatan'],
                ];
            }

            return $rincian;
        }

        foreach ($bundling->items as $bi) {
            $dp = $bi->detailProduk;
            $rincian[] = [
                'produk_id' => $bi->produk_id,
                'nama_produk' => $bi->produk?->nama_produk ?? 'Produk',
                'ukuran' => $dp?->ukuran ?: $bi->ukuran,
                'varian_rasa' => $dp?->variasi ?: $bi->varian_rasa,
                'qty' => (int) $bi->qty,
                'catatan' => null,
            ];
        }

        return $rincian;
    }
}
