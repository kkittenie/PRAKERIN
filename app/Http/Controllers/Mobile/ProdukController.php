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

            $produk->load([
                'kategori',
                'promos.detailProduks',
                'detailProduks' => fn ($q) => $q->orderBy('urutan')->orderBy('id'),
                'varianProduks',
            ]);

            $pengaturan = PengaturanMember::aktifSekarang();

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['produk_id'] ?? null) === $produk->id) {
                    $itemEdit = $item;
                }
            }

            $punyaVarianBaru = $produk->detailProduks->isNotEmpty();
            $daftarVarian = [];
            $opsiUkuran = [];
            $opsiVariasi = [];
            $varianIdDipilihAwal = null;

            $punyaUkuran = (bool) $produk->punya_ukuran;
            $daftarUkuran = [];

            try {
                if ($punyaVarianBaru) {
                    $daftarVarian = $produk->daftarVarianKasir();
                    $opsiUkuran = collect($daftarVarian)->pluck('ukuran')->filter()->unique()->values()->all();
                    $opsiVariasi = collect($daftarVarian)->pluck('variasi')->filter()->unique()->values()->all();

                    $varianEdit = null;
                    if (!empty($itemEdit['varian_produk_id'])) {
                        // Produk Stok Bersama: beberapa baris punya detail_produk_id yang sama, jadi cocokkan lewat varian_produk_id.
                        $varianEdit = collect($daftarVarian)->firstWhere('varian_produk_id', (int) $itemEdit['varian_produk_id']);
                    } elseif (!empty($itemEdit['detail_produk_id'])) {
                        $varianEdit = collect($daftarVarian)->firstWhere('id', (int) $itemEdit['detail_produk_id']);
                    } elseif (!empty($itemEdit['ukuran']) || !empty($itemEdit['varian_rasa'])) {
                        $varianEdit = collect($daftarVarian)->first(function ($v) use ($itemEdit) {
                            $matchU = empty($itemEdit['ukuran']) || $v['ukuran'] === $itemEdit['ukuran'];
                            $matchR = empty($itemEdit['varian_rasa']) || $v['variasi'] === $itemEdit['varian_rasa'];
                            return $matchU && $matchR;
                        });
                    }

                    // Ambil varian aktif pertama sebagai default
                    $varianAktifPertama = collect($daftarVarian)->firstWhere('aktif', true);

                    $varianIdDipilihAwal = $varianEdit['id'] ?? ($varianAktifPertama['id'] ?? null);
                    $ukuranDipilihAwal = $varianEdit['ukuran'] ?? ($itemEdit['ukuran'] ?? ($varianAktifPertama['ukuran'] ?? ($opsiUkuran[0] ?? null)));
                    $varianRasaDipilihAwal = $varianEdit['variasi'] ?? ($itemEdit['varian_rasa'] ?? ($varianAktifPertama['variasi'] ?? ($opsiVariasi[0] ?? null)));

                    $termurah = collect($daftarVarian)->where('aktif', true)->sortBy('harga_diskon')->first();
                    $hargaAsli = $termurah['harga_asli'] ?? (float) $produk->harga_jual;
                    $hargaFinal = $termurah['harga_diskon'] ?? (float) $produk->harga_diskon;
                    $adaPromo = collect($daftarVarian)->where('aktif', true)->contains('sedang_promo', true);
                    $habis = collect($daftarVarian)->where('aktif', true)->where('stok', '>', 0)->isEmpty();
                } elseif ($punyaUkuran) {
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
                    $habis = $produk->habisSemuaUkuran();
                    $ukuranDipilihAwal = $itemEdit['ukuran'] ?? ($daftarUkuran[0]['nama'] ?? null);
                    $varianRasaDipilihAwal = $itemEdit['varian_rasa'] ?? null;
                } else {
                    $hargaAsli = (float) $produk->harga_jual;
                    $hargaFinal = (float) $produk->harga_diskon;
                    $adaPromo = $hargaFinal < $hargaAsli;
                    $habis = (int) $produk->stok <= 0;
                    $ukuranDipilihAwal = null;
                    $varianRasaDipilihAwal = null;
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
                $habis = true;
                $ukuranDipilihAwal = null;
                $varianRasaDipilihAwal = null;
            }

            $punyaVarianRasa = (bool) $produk->punya_varian_rasa;
            $daftarVarianRasa = $produk->daftar_varian_rasa;
            if (!$punyaVarianBaru && $punyaVarianRasa) {
                $varianRasaDipilihAwal = $itemEdit['varian_rasa'] ?? ($daftarVarianRasa[0]['nama'] ?? null);
            }

            // Jumlah produk ini yang sudah ada di keranjang per detail produk (tanpa item yang sedang diubah),
            // dipakai halaman untuk menghitung sisa stok yang masih bisa dipesan.
            $terpakaiPerDetail = collect(MemberCart::items())
                ->filter(fn ($item) => empty($item['bundling_id'])
                    && empty($item['promo_event_id'])
                    && empty($item['is_reward_stamp'])
                    && ($item['produk_id'] ?? null) === $produk->id
                    && !empty($item['detail_produk_id'])
                    && ($itemEdit === null || $item['id'] !== $itemEdit['id']))
                ->groupBy('detail_produk_id')
                ->map(fn ($baris) => (int) $baris->sum('jumlah'))
                ->all();

            $terpakaiTanpaDetail = (int) collect(MemberCart::items())
                ->filter(fn ($item) => empty($item['bundling_id'])
                    && empty($item['promo_event_id'])
                    && empty($item['is_reward_stamp'])
                    && ($item['produk_id'] ?? null) === $produk->id
                    && empty($item['detail_produk_id'])
                    && ($itemEdit === null || $item['id'] !== $itemEdit['id']))
                ->sum('jumlah');

            // 3 produk rekomendasi dari database untuk showcase detail produk ("A Little Taste of Everything We Bake")
            $produkLainnya = Produk::dijualSatuan()
                ->where('status_aktif', true)
                ->where('id', '!=', $produk->id)
                ->where('kategori_id', $produk->kategori_id)
                ->with(['kategori', 'detailProduks'])
                ->take(3)
                ->get();

            if ($produkLainnya->count() < 3) {
                $tambahan = Produk::dijualSatuan()
                    ->where('status_aktif', true)
                    ->where('id', '!=', $produk->id)
                    ->whereNotIn('id', $produkLainnya->pluck('id'))
                    ->with(['kategori', 'detailProduks'])
                    ->take(3 - $produkLainnya->count())
                    ->get();
                $produkLainnya = $produkLainnya->merge($tambahan);
            }

            return view('mobile.produk.show', [
                'produk' => $produk,
                'produkLainnya' => $produkLainnya,
                'habis' => $habis,
                'pengaturan' => $pengaturan,
                'itemEdit' => $itemEdit,
                'punyaVarianBaru' => $punyaVarianBaru,
                'daftarVarian' => $daftarVarian,
                'opsiUkuran' => $opsiUkuran,
                'opsiVariasi' => $opsiVariasi,
                'varianIdDipilihAwal' => $varianIdDipilihAwal,
                'punyaUkuran' => $punyaUkuran,
                'daftarUkuran' => $daftarUkuran,
                'ukuranDipilihAwal' => $ukuranDipilihAwal,
                'punyaVarianRasa' => $punyaVarianRasa,
                'daftarVarianRasa' => $daftarVarianRasa,
                'varianRasaDipilihAwal' => $varianRasaDipilihAwal,
                'hargaAsli' => $hargaAsli,
                'hargaFinal' => $hargaFinal,
                'adaPromo' => $adaPromo,
                'stokTanpaDetail' => max(0, (int) $produk->stok),
                'terpakaiPerDetail' => $terpakaiPerDetail,
                'terpakaiTanpaDetail' => $terpakaiTanpaDetail,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}