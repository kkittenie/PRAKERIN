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

            $pengaturan = PengaturanMember::aktifSekarang();

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['produk_id'] ?? null) === $produk->id) {
                    $itemEdit = $item;
                }
            }

            $punyaUkuran = (bool) $produk->punya_ukuran;
            $daftarUkuran = [];

            try {
                if ($punyaUkuran) {
                    $daftarUkuran = collect($produk->daftar_ukuran)
                        ->map(function ($opsi) use ($produk) {
                            $hargaAsliOpsi = (float) $opsi['harga'];
                            $hargaFinalOpsi = $produk->hargaSetelahPromo($opsi['nama']);

                            return [
                                'produk_ukuran_id' => $opsi['produk_ukuran_id'],
                                'nama' => $opsi['nama'],
                                'harga_asli' => $hargaAsliOpsi,
                                'harga_final' => $hargaFinalOpsi,
                                'ada_promo' => $hargaFinalOpsi < $hargaAsliOpsi,
                                'perlu_produksi' => (bool) ($opsi['perlu_produksi'] ?? false),
                            ];
                        })
                        ->values()
                        ->all();

                    $default = $daftarUkuran[0] ?? null;
                    $hargaAsli = $default['harga_asli'] ?? 0;
                    $hargaFinal = $default['harga_final'] ?? 0;
                    $adaPromo = collect($daftarUkuran)->contains('ada_promo', true);
                } else {
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
                $daftarUkuran = [];
            }

            $ukuranDipilihAwal = $itemEdit['ukuran'] ?? ($daftarUkuran[0]['nama'] ?? null);
            $punyaVarianRasa = (bool) $produk->punya_varian_rasa;
            $produk->load(['kategori', 'produkVarianRasas.varianRasa', 'produkUkuranRasas', 'produkUkurans.ukuran']);
            $daftarVarianRasa = $produk->daftar_varian_rasa;
            $varianRasaDipilihAwal = $itemEdit['varian_rasa'] ?? ($daftarVarianRasa[0]['nama'] ?? null);

            return view('mobile.produk.show', [
                'produk' => $produk,
                'habis' => $habis,
                'pengaturan' => $pengaturan,
                'itemEdit' => $itemEdit,
                'punyaUkuran' => $punyaUkuran,
                'daftarUkuran' => $daftarUkuran,
                'ukuranDipilihAwal' => $ukuranDipilihAwal,
                'punyaVarianRasa' => $punyaVarianRasa,
                'daftarVarianRasa' => $daftarVarianRasa,
                'varianRasaDipilihAwal' => $varianRasaDipilihAwal,
                'hargaAsli' => $hargaAsli,
                'hargaFinal' => $hargaFinal,
                'adaPromo' => $adaPromo,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}