<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\PromoProdukEvent;
use App\Models\Produk;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    public function tambah(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->status_aktif, 404);

            $validated = $request->validate([
                'jumlah' => 'required|integer|min:1|max:50',
                'ukuran' => 'nullable|string|max:50',
                'varian_rasa' => 'nullable|string|max:100',
                'catatan' => 'nullable|string|max:255',
            ]);

            $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? $produk->ukuranDefault()) : null;
            $hargaAsal = $produk->getHarga($ukuran);

            // Pakai promoUntukHitungHarga($ukuran), bukan promo_aktif — promo_aktif
            // gak ngecek pembatasan ukuran, jadi ukuran yang harusnya gak dapet
            // diskon tetep ikut kediskon kalau produknya punya promo aktif lain.
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
            $validated['ukuran'] = $ukuran;
            $varianRasa = $this->tentukanVarianRasa($produk, $validated['varian_rasa'] ?? null);

            MemberCart::tambah([
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'foto' => $produk->foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $promoBerlaku?->nama_promo,
                'jenis_promo' => $promoBerlaku?->jenis_potongan,
                'persentase_promo' => $promoBerlaku?->persentase,
                // Disimpen biar bisa di-recheck di checkout terhadap subtotal
                // keranjang yang sebenarnya (lihat MemberCart::terapkanMinimalPembelian()).
                'min_pembelian_promo' => $promoBerlaku?->min_pembelian ?? 0,
                'ukuran' => $validated['ukuran'] ?? null,
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
                        'foto_url' => $produk->foto ? asset('storage/' . $produk->foto) : null,
                        'ukuran' => $validated['ukuran'] ?: null,
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
                'pilihan_khusus.*.produk_id' => 'required_with:pilihan_khusus|integer|exists:produks,id',
                // Boleh 0: form selalu ngirim baris utk SEMUA item bundling
                // (termasuk varian yang gak dipilih member). Baris qty 0
                // difilter di bawah, bukan ditolak validasi di sini.
                'pilihan_khusus.*.qty' => 'required_with:pilihan_khusus|integer|min:0',
                'pilihan_khusus.*.catatan' => 'nullable|string|max:255',
            ]);

            $pilihanKhusus = collect($validated['pilihan_khusus'] ?? [])
                ->filter(fn ($p) => (int) ($p['qty'] ?? 0) > 0)
                ->values()
                ->all();

            MemberCart::tambah([
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
                'pilihan_khusus' => $pilihanKhusus,
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

            $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? $produk->ukuranDefault()) : null;
            $hargaAsal = $produk->getHarga($ukuran);

            // Sama kayak di tambah() — pakai promoUntukHitungHarga($ukuran) biar
            // pembatasan ukuran ke-hormatin, bukan promo_aktif yang berlaku ke semua ukuran.
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
            $validated['ukuran'] = $ukuran;
            $varianRasa = $this->tentukanVarianRasa($produk, $validated['varian_rasa'] ?? null);

            MemberCart::perbarui($itemId, [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'foto' => $produk->foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $promoBerlaku?->nama_promo,
                'jenis_promo' => $promoBerlaku?->jenis_potongan,
                'persentase_promo' => $promoBerlaku?->persentase,
                'min_pembelian_promo' => $promoBerlaku?->min_pembelian ?? 0,
                'ukuran' => $validated['ukuran'] ?? null,
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
                'pilihan_khusus.*.produk_id' => 'required_with:pilihan_khusus|integer|exists:produks,id',
                // Boleh 0: form selalu ngirim baris utk SEMUA item bundling
                // (termasuk varian yang gak dipilih member). Baris qty 0
                // difilter di bawah, bukan ditolak validasi di sini.
                'pilihan_khusus.*.qty' => 'required_with:pilihan_khusus|integer|min:0',
                'pilihan_khusus.*.catatan' => 'nullable|string|max:255',
            ]);

            $bundling = Bundling::findOrFail($validated['bundling_id']);
            abort_unless($bundling->status_aktif, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.bundling.show', $bundling)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            $pilihanKhusus = collect($validated['pilihan_khusus'] ?? [])
                ->filter(fn ($p) => (int) ($p['qty'] ?? 0) > 0)
                ->values()
                ->all();

            MemberCart::perbarui($itemId, [
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
                'pilihan_khusus' => $pilihanKhusus,
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

            MemberCart::ubahJumlah($itemId, $validated['jumlah']);

            return back();
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function hapus(string $itemId)
    {
        try {
            MemberCart::hapus($itemId);

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
}
