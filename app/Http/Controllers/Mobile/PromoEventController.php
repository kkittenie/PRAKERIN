<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PromoProdukEvent;
use App\Support\MemberCart;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class PromoEventController extends Controller
{
    public function show(Request $request, PromoProdukEvent $promoEvent)
    {
        try {
            abort_unless($promoEvent->sedang_berlangsung, 404);

            $promoEvent->load('produk');

            $itemEdit = null;
            if ($request->filled('edit')) {
                $item = MemberCart::cariItem((string) $request->query('edit'));
                if ($item && ($item['promo_event_id'] ?? null) === $promoEvent->id) {
                    $itemEdit = $item;
                }
            }

            return view('mobile.promo-event.show', [
                'promoEvent' => $promoEvent,
                'habis' => $promoEvent->perlu_diproduksi,
                'itemEdit' => $itemEdit,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}