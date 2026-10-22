<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Bundling;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class BundlingController extends Controller
{
    public function show(Request $request, Bundling $bundling)
    {
        try {
            abort_unless($bundling->status_aktif, 404);

            $bundling->load(['items.produk.detailProduks', 'items.detailProduk']);

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['bundling_id'] ?? null) === $bundling->id) {
                    $itemEdit = $item;
                }
            }

            return view('mobile.bundling.show', [
                'bundling' => $bundling,
                'habis' => $bundling->perlu_diproduksi,
                'itemEdit' => $itemEdit,
                // Berapa paket yang masih bisa dipenuhi stok (null untuk bundling bebas: dicek di server
                // setelah pembeli memilih rasa) dan paket sama yang sudah ada di keranjang.
                'maksPaket' => $bundling->maksPaketTersedia([]),
                'terpakaiDiKeranjang' => $bundling->isBebas()
                    ? 0
                    : MemberCart::jumlahBundlingDiKeranjang($bundling->id, $itemEdit['id'] ?? null),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}