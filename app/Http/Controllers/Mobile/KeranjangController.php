<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
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
                'catatan' => 'nullable|string|max:255',
            ]);

            $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? 'Kecil') : null;
            $hargaAsal = $produk->getHarga($ukuran);

            $diskonSatuan = 0;
            if ($produk->promo_aktif) {
                if ($produk->promo_aktif->jenis_potongan === 'nominal') {
                    $diskonSatuan = (float) $produk->promo_aktif->nominal_potongan;
                } else {
                    $diskonSatuan = ((float) ($produk->promo_aktif->persentase ?? 0) / 100) * $hargaAsal;
                }
            }

            $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
            $validated['ukuran'] = $ukuran;

            MemberCart::tambah([
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'foto' => $produk->foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $produk->promo_aktif?->nama_promo,
                'jenis_promo' => $produk->promo_aktif?->jenis_potongan,
                'persentase_promo' => $produk->promo_aktif?->persentase,
                'ukuran' => $validated['ukuran'] ?? null,
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
            ]);

            MemberCart::tambah([
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
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
                'catatan' => 'nullable|string|max:255',
            ]);

            $produk = Produk::findOrFail($validated['produk_id']);
            abort_unless($produk->status_aktif, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.produk.show', $produk)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            $ukuran = $produk->punya_ukuran ? ($validated['ukuran'] ?? 'Kecil') : null;
            $hargaAsal = $produk->getHarga($ukuran);

            $diskonSatuan = 0;
            if ($produk->promo_aktif) {
                if ($produk->promo_aktif->jenis_potongan === 'nominal') {
                    $diskonSatuan = (float) $produk->promo_aktif->nominal_potongan;
                } else {
                    $diskonSatuan = ((float) ($produk->promo_aktif->persentase ?? 0) / 100) * $hargaAsal;
                }
            }

            $hargaSatuan = max(0, $hargaAsal - $diskonSatuan);
            $validated['ukuran'] = $ukuran;

            MemberCart::perbarui($itemId, [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'foto' => $produk->foto,
                'harga_satuan' => $hargaSatuan,
                'harga_asal' => $hargaAsal,
                'diskon_produk_satuan' => $diskonSatuan,
                'nama_promo' => $produk->promo_aktif?->nama_promo,
                'jenis_promo' => $produk->promo_aktif?->jenis_potongan,
                'persentase_promo' => $produk->promo_aktif?->persentase,
                'ukuran' => $validated['ukuran'] ?? null,
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
            ]);

            $bundling = Bundling::findOrFail($validated['bundling_id']);
            abort_unless($bundling->status_aktif, 404);

            if (! MemberCart::cariItem($itemId)) {
                return redirect()->route('member.bundling.show', $bundling)
                    ->with('error', 'Item keranjang tidak ditemukan, mungkin sudah dihapus.');
            }

            MemberCart::perbarui($itemId, [
                'bundling_id' => $bundling->id,
                'nama_produk' => $bundling->nama_bundling,
                'foto' => $bundling->gambar,
                'harga_satuan' => (float) $bundling->harga,
                'ukuran' => null,
                'catatan' => $validated['catatan'] ?? null,
                'jumlah' => $validated['jumlah'],
            ]);

            return redirect()
                ->route('member.checkout.index')
                ->with('success', "{$bundling->nama_bundling} diperbarui.");
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
}