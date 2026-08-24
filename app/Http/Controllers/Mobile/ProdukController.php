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
            $daftarUkuran = [];

            try {
                if ($punyaUkuran) {
                    // Dulu di sini cuma ngitung Kecil/Besar. Sekarang baca
                    // langsung dari daftar_ukuran (baris produk_ukurans yang
                    // aktif) supaya ukuran apapun yang didaftarin di menu
                    // Ukuran otomatis kebawa ke detail produk mobile ini.
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

            // Ukuran yang otomatis kepilih pas halaman dibuka: kalau lagi
            // "ubah" item keranjang, pakai ukuran yang udah ada; kalau
            // enggak, pakai baris ukuran aktif pertama.
            $ukuranDipilihAwal = $itemEdit['ukuran'] ?? ($daftarUkuran[0]['nama'] ?? null);

            return view('mobile.produk.show', [
                'produk' => $produk->load('kategori'),
                'habis' => $habis,
                'pengaturan' => $pengaturan,
                'itemEdit' => $itemEdit,
                'punyaUkuran' => $punyaUkuran,
                'daftarUkuran' => $daftarUkuran,
                'ukuranDipilihAwal' => $ukuranDipilihAwal,
                'hargaAsli' => $hargaAsli,
                'hargaFinal' => $hargaFinal,
                'adaPromo' => $adaPromo,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}