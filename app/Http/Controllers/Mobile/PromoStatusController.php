<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\PromoProdukEvent;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class PromoStatusController extends Controller
{
    public function index(Request $request)
    {
        try {
            $produkIds = collect(explode(',', (string) $request->query('produk_ids', '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->values();

            $produk = [];

            if ($produkIds->isNotEmpty()) {
                $produk = Produk::with('promos')
                    ->whereIn('id', $produkIds)
                    ->where('status_aktif', true)
                    ->get()
                    ->map(function ($p) {
                        if ($p->punya_ukuran) {
                            $namaTermurah = $p->ukuranTermurah()['nama'] ?? null;
                            $hargaAsli = $p->getHarga($namaTermurah);
                            $hargaFinal = $p->hargaSetelahPromo($namaTermurah);
                        } else {
                            $hargaAsli = (float) $p->harga_jual;
                            $hargaFinal = (float) $p->harga_diskon;
                        }

                        return [
                            'id' => $p->id,
                            'harga_asli' => $hargaAsli,
                            'harga_final' => $hargaFinal,
                            'ada_promo' => $hargaFinal < $hargaAsli,
                            'habis' => $p->habisSemuaUkuran(),
                        ];
                    })
                    ->keyBy('id');
            }

            $promoEvent = PromoProdukEvent::with('produk')
                ->berlaku()
                ->get()
                ->map(function ($pe) {
                    return [
                        'id' => $pe->id,
                        'nama_event' => $pe->nama_event,
                        'harga_event' => (float) $pe->harga_event,
                        'isi_event' => $pe->isi_event,
                        'isi_normal' => $pe->isi_normal,
                        'perlu_diproduksi' => $pe->perlu_diproduksi,
                        'stok_tersedia' => $pe->stok_tersedia,
                        'produk_nama' => $pe->produk->nama_produk ?? '',
                        'produk_foto' => ($pe->produk && $pe->produk->foto) ? asset('storage/' . $pe->produk->foto) : null,
                        'url' => route('member.promo-event.show', $pe),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'produk' => $produk,
                    'promo_event' => $promoEvent,
                ],
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}