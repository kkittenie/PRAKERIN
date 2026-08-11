<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Support\MemberCart;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    /**
     * Produk baru resmi masuk ke keranjang setelah tombol "Add" di halaman
     * Detail Produk ini ditekan (bukan sebelumnya) — sesuai alur Fore.
     */
    public function tambah(Request $request, Produk $produk)
    {
        abort_unless($produk->status_aktif, 404);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1|max:50',
            'ukuran' => 'nullable|string|max:50',
            'catatan' => 'nullable|string|max:255',
        ]);

        // Sengaja TIDAK ada validasi stok di sini. Member tetap boleh order
        // walau stok kurang/habis — nanti di checkout item ini otomatis
        // ditandai "butuh_produksi" dan masuk ke tiket dapur, sama seperti
        // alur kasir POS (lihat CheckoutController::tentukanPerluProduksi()).

        $hargaSatuan = (float) $produk->harga_diskon;
        // Ukuran "Large" nambah harga — customization sederhana ala Fore,
        // hanya relevan untuk produk minuman.
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

        return redirect()
            ->route('member.menu')
            ->with('success', "{$produk->nama_produk} ditambahkan ke keranjang.");
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
}