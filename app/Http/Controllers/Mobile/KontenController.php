<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\KontenStatis;
use App\Support\ActivityLogHelper;

class KontenController extends Controller
{
    protected array $judulMap = [
        'help-center'      => 'Help Center',
        'terms-conditions' => 'Terms and Conditions',
        'privacy-policy'   => 'Privacy Policy',
    ];

    public function show($slug)
    {
        try {
            if (!array_key_exists($slug, $this->judulMap)) {
                abort(404);
            }

            $konten = KontenStatis::where('slug', $slug)->first();

            $judulHalaman = $this->judulMap[$slug];

            return view('mobile.konten-statis', compact('konten', 'judulHalaman', 'slug'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
