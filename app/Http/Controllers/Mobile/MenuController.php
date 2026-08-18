<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\KategoriProduk;
use App\Support\ActivityLogHelper;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $kategoris = KategoriProduk::with(['produk' => function ($query) {
                $query->where('status_aktif', true)
                    ->orderBy('nama_produk');
            }])
                ->orderBy('nama_kategori')
                ->get()
                ->filter(fn ($kategori) => $kategori->produk->isNotEmpty())
                ->values();

            $totalProduk = $kategoris->sum(fn ($kategori) => $kategori->produk->count());
            $daftarBundling = Bundling::with('items.produk')
                ->where('status_aktif', true)
                ->get();

            return view('mobile.menu', [
                'kategoris' => $kategoris,
                'totalProduk' => $totalProduk,
                'daftarBundling' => $daftarBundling,
                'waCs' => '6282000768044',
                'storeNama' => 'Saisho Cake & Bakery',
                'storeAlamat' => 'Jl. Kamboja No. 1 Tuparev, Cirebon',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}