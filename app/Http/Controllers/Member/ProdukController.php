<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PengaturanMember;
use App\Models\Produk;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function show(Request $request, Produk $produk)
    {
        try {
            abort_unless($produk->status_aktif, 404);
            $habis = $produk->status_ketersediaan === 'perlu_diproduksi' || (float) $produk->stok <= 0;

            $pengaturan = PengaturanMember::where('status_aktif', true)->first();

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['produk_id'] ?? null) === $produk->id) {
                    $itemEdit = $item;
                }
            }

            return view('member.produk.show', [
                'produk' => $produk->load('kategori'),
                'habis' => $habis,
                'pengaturan' => $pengaturan,
                'itemEdit' => $itemEdit,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}