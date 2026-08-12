<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Bundling;

class BundlingController extends Controller
{
    public function show(Bundling $bundling)
    {
        abort_unless($bundling->status_aktif, 404);

        $bundling->load('items.produk');

        return view('member.bundling.show', [
            'bundling' => $bundling,
            'habis' => $bundling->perlu_diproduksi,
        ]);
    }
}
