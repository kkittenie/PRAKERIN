<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Models\KategoriProduk;
use App\Support\ActivityLogHelper;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $kategoris = KategoriProduk::with(['produk' => function ($query) {
                $query->where('status_aktif', true)
                    ->dijualSatuan()
                    ->with(['detailProduks', 'promos'])
                    ->orderBy('nama_produk');
            }])
                ->orderBy('nama_kategori')
                ->get()
                ->filter(fn ($kategori) => $kategori->produk->isNotEmpty())
                ->values();

            foreach ($kategoris as $kategori) {
                foreach ($kategori->produk as $produk) {
                    try {
                        $varians = collect($produk->daftarVarianKasir())->where('aktif', true);
                        if ($varians->isNotEmpty()) {
                            $termurah = $varians->sortBy('harga_diskon')->first();
                            $hargaAsli = (float) $termurah['harga_asli'];
                            $hargaFinal = (float) $termurah['harga_diskon'];
                            $habis = $varians->where('stok', '>', 0)->isEmpty();
                        } elseif ($produk->punya_ukuran) {
                            $namaTermurah = $produk->ukuranTermurah()['nama'] ?? null;
                            $hargaAsli = $produk->getHarga($namaTermurah);
                            $hargaFinal = $produk->hargaSetelahPromo($namaTermurah);
                            $habis = $produk->habisSemuaUkuran();
                        } else {
                            $hargaAsli = (float) $produk->harga_jual;
                            $hargaFinal = (float) $produk->harga_diskon;
                            $habis = (int) $produk->stok <= 0;
                        }

                        $produk->harga_tampil_asli = $hargaAsli;
                        $produk->harga_tampil_final = $hargaFinal;
                        $produk->ada_promo_tampil = $hargaFinal < $hargaAsli;
                        $produk->habis_tampil = $habis;
                    } catch (\Throwable $e) {
                        Log::error('Gagal hitung harga/promo produk di menu mobile', [
                            'produk_id' => $produk->id,
                            'error' => $e->getMessage(),
                        ]);

                        $produk->harga_tampil_asli = (float) $produk->harga_jual;
                        $produk->harga_tampil_final = (float) $produk->harga_jual;
                        $produk->ada_promo_tampil = false;
                        $produk->habis_tampil = true;
                    }
                }
            }

            $totalProduk = $kategoris->sum(fn ($kategori) => $kategori->produk->count());
            $daftarBundling = Bundling::with(['items.produk', 'items.detailProduk'])
                ->where('status_aktif', true)
                ->get();

            $daftarPromoEvent = \App\Models\PromoProdukEvent::with('produk')
                ->berlaku()
                ->get();

            return view('mobile.menu', [
                'kategoris' => $kategoris,
                'totalProduk' => $totalProduk,
                'daftarBundling' => $daftarBundling,
                'daftarPromoEvent' => $daftarPromoEvent,
                'waCs' => '6282000768044',
                'storeNama' => 'Saisho Cake & Bakery',
                'storeAlamat' => 'Jl. Kamboja No. 1 Tuparev, Cirebon',
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}