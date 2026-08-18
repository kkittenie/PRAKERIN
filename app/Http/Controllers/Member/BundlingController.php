<?php

namespace App\Http\Controllers\Member;

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

            $bundling->load('items.produk');

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['bundling_id'] ?? null) === $bundling->id) {
                    $itemEdit = $item;
                }
            }

            return view('member.bundling.show', [
                'bundling' => $bundling,
                'habis' => $bundling->perlu_diproduksi,
                'itemEdit' => $itemEdit,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}