<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\Produk;
use App\Support\MemberCart;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{

    public function tambah(Request $request, Produk $produk)
    {
        abort_unless($produk->status_aktif, 404);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1|max:50',
            'ukuran' => 'nullable|string|max:50',
            'catatan' => 'nullable|string|max:255',
        ]);

        $hargaSatuan = (float) $produk->harga_diskon;
        if (($validated['ukuran'] ?? null) === 'Large') {
            $hargaSatuan += 3000;
        }

        MemberCart::tambah([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'foto' => $produk->foto,
            'harga_satuan' => $hargaSatuan,
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
                'suggestion' => $this->saranSilang(excludeProdukIds: [$produk->id]),
                'cart' => $this->ringkasanKeranjang(),
                'checkout_url' => route('member.checkout.index'),
                'menu_url' => route('member.menu'),
            ]);
        }

        return redirect()
            ->route('member.menu')
            ->with('success', $pesan);
    }

    public function tambahBundling(Request $request, Bundling $bundling)
    {
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
            $idProdukDalamBundling = $bundling->items()->pluck('produk_id')->all();

            return response()->json([
                'success' => true,
                'message' => $pesan,
                'item' => [
                    'nama' => $bundling->nama_bundling,
                    'foto_url' => $bundling->gambar ? asset('storage/' . $bundling->gambar) : null,
                    'ukuran' => null,
                    'jumlah' => (int) $validated['jumlah'],
                ],
                'suggestion' => $this->saranSilang(excludeProdukIds: $idProdukDalamBundling),
                'cart' => $this->ringkasanKeranjang(),
                'checkout_url' => route('member.checkout.index'),
                'menu_url' => route('member.menu'),
            ]);
        }

        return redirect()
            ->route('member.menu')
            ->with('success', $pesan);
    }

    /**
     * Perbarui item produk yang sudah ada di keranjang — dipakai dari
     * halaman produk saat member membukanya lewat "Change" di checkout.
     */
    public function perbarui(Request $request, string $itemId)
    {
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

        $hargaSatuan = (float) $produk->harga_diskon;
        if (($validated['ukuran'] ?? null) === 'Large') {
            $hargaSatuan += 3000;
        }

        MemberCart::perbarui($itemId, [
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama_produk,
            'foto' => $produk->foto,
            'harga_satuan' => $hargaSatuan,
            'ukuran' => $validated['ukuran'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'jumlah' => $validated['jumlah'],
        ]);

        return redirect()
            ->route('member.checkout.index')
            ->with('success', "{$produk->nama_produk} diperbarui.");
    }

    /**
     * Perbarui item bundling yang sudah ada di keranjang.
     */
    public function perbaruiBundling(Request $request, string $itemId)
    {
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
    }

    public function ubahJumlah(Request $request, string $itemId)
    {
        $validated = $request->validate([
            'jumlah' => 'required|integer|min:0|max:50',
        ]);

        MemberCart::ubahJumlah($itemId, $validated['jumlah']);

        return back();
    }

    public function hapus(string $itemId)
    {
        MemberCart::hapus($itemId);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    /**
     * Ambil satu produk "cocok buat ditambahkan juga" untuk ditampilkan di
     * sheet konfirmasi keranjang. Diprioritaskan produk yang lagi promo.
     */
    private function saranSilang(array $excludeProdukIds = []): ?array
    {
        $kandidat = Produk::with('promos')
            ->where('status_aktif', true)
            ->whereNotIn('id', $excludeProdukIds)
            ->get();

        if ($kandidat->isEmpty()) {
            return null;
        }

        $saran = $kandidat->filter(fn ($p) => $p->promo_aktif)->shuffle()->first()
            ?? $kandidat->shuffle()->first();

        if (!$saran) {
            return null;
        }

        return [
            'id' => $saran->id,
            'nama' => $saran->nama_produk,
            'foto_url' => $saran->foto ? asset('storage/' . $saran->foto) : null,
            'harga_asli' => (float) $saran->harga_jual,
            'harga_final' => (float) $saran->harga_diskon,
            'ada_promo' => (float) $saran->harga_diskon < (float) $saran->harga_jual,
            'add_url' => route('member.keranjang.tambah', $saran),
        ];
    }

    private function ringkasanKeranjang(): array
    {
        return [
            'jumlah' => MemberCart::jumlahItem(),
            'subtotal' => MemberCart::subtotal(),
        ];
    }
}