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

        return redirect()
            ->route('member.menu')
            ->with('success', "{$produk->nama_produk} ditambahkan ke keranjang.");
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

        return redirect()
            ->route('member.menu')
            ->with('success', "{$bundling->nama_bundling} ditambahkan ke keranjang.");
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