<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PengaturanMember;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function show(Produk $produk)
    {
        abort_unless($produk->status_aktif, 404);
        $habis = $produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0;

        $pengaturan = PengaturanMember::where('status_aktif', true)->first();

        return view('member.produk.show', [
            'produk' => $produk->load('kategori'),
            'habis' => $habis,
            'pengaturan' => $pengaturan,
        ]);
    }
}