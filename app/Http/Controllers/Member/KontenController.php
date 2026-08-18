<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\KontenStatis;

class KontenController extends Controller
{
    protected array $judulMap = [
        'help-center'      => 'Help Center',
        'terms-conditions' => 'Terms and Conditions',
        'privacy-policy'   => 'Privacy Policy',
    ];

    public function show($slug)
    {
        if (!array_key_exists($slug, $this->judulMap)) {
            abort(404);
        }

        $konten = KontenStatis::where('slug', $slug)->first();

        $judulHalaman = $this->judulMap[$slug];

        return view('member.konten-statis', compact('konten', 'judulHalaman', 'slug'));
    }
}
