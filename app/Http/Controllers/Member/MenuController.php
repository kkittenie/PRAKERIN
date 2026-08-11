<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;

class MenuController extends Controller
{
    public function index()
    {
        $kategoris = KategoriProduk::with(['produk' => function ($query) {
            $query->where('status_aktif', true)
                ->orderBy('nama_produk');
        }])
            ->orderBy('nama_kategori')
            ->get()
            ->filter(fn ($kategori) => $kategori->produk->isNotEmpty())
            ->values();

        $totalProduk = $kategoris->sum(fn ($kategori) => $kategori->produk->count());

        return view('member.menu', [
            'kategoris' => $kategoris,
            'totalProduk' => $totalProduk,
            'waCs' => '6282000768044',
            'storeNama' => 'Saisho Cake & Bakery',
            'storeAlamat' => 'Jl. Kemboja Jl. Tuparev No. 1, Cirebon',
        ]);
    }
}