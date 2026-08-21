<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PengaturanMember;
use App\Models\Produk;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProdukController extends Controller
{
    public function show(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->status_aktif, 404);
            $habis = $produk->habisSemuaUkuran();

            $pengaturan = PengaturanMember::where('status_aktif', true)->first();

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['produk_id'] ?? null) === $produk->id) {
                    $itemEdit = $item;
                }
            }

            $punyaUkuran = (bool) $produk->punya_ukuran;

            try {
                if ($punyaUkuran) {
                    $hargaKecilAsli = (float) ($produk->harga_kecil ?? 0);
                    $hargaBesarAsli = (float) ($produk->harga_besar ?? 0);
                    $hargaKecilFinal = $produk->hargaSetelahPromo('Kecil');
                    $hargaBesarFinal = $produk->hargaSetelahPromo('Besar');
                    $adaPromoKecil = $hargaKecilFinal < $hargaKecilAsli;
                    $adaPromoBesar = $hargaBesarFinal < $hargaBesarAsli;
                    $adaPromo = $adaPromoKecil || $adaPromoBesar;

                    $hargaAsli = $hargaKecilAsli;
                    $hargaFinal = $hargaKecilFinal;
                } else {
                    $hargaKecilAsli = 0;
                    $hargaBesarAsli = 0;
                    $hargaKecilFinal = 0;
                    $hargaBesarFinal = 0;
                    $adaPromoKecil = false;
                    $adaPromoBesar = false;

                    $hargaAsli = (float) $produk->harga_jual;
                    $hargaFinal = (float) $produk->harga_diskon;
                    $adaPromo = $hargaFinal < $hargaAsli;
                }
            } catch (\Throwable $e) {
                Log::error('Gagal hitung harga/promo produk di detail mobile', [
                    'produk_id' => $produk->id,
                    'error' => $e->getMessage(),
                ]);

                $hargaAsli = (float) $produk->harga_jual;
                $hargaFinal = (float) $produk->harga_jual;
                $adaPromo = false;
                $hargaKecilAsli = (float) ($produk->harga_kecil ?? 0);
                $hargaBesarAsli = (float) ($produk->harga_besar ?? 0);
                $hargaKecilFinal = $hargaKecilAsli;
                $hargaBesarFinal = $hargaBesarAsli;
                $adaPromoKecil = false;
                $adaPromoBesar = false;
            }

            return view('mobile.produk.show', [
                'produk' => $produk->load('kategori'),
                'habis' => $habis,
                'pengaturan' => $pengaturan,
                'itemEdit' => $itemEdit,
                'punyaUkuran' => $punyaUkuran,
                'hargaAsli' => $hargaAsli,
                'hargaFinal' => $hargaFinal,
                'adaPromo' => $adaPromo,
                'hargaKecilAsli' => $hargaKecilAsli,
                'hargaBesarAsli' => $hargaBesarAsli,
                'hargaKecilFinal' => $hargaKecilFinal,
                'hargaBesarFinal' => $hargaBesarFinal,
                'adaPromoKecil' => $adaPromoKecil,
                'adaPromoBesar' => $adaPromoBesar,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}