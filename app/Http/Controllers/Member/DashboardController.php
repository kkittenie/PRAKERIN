<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\RewardKatalog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        $rewardUnggulan = RewardKatalog::aktif()
            ->where('jenis', 'voucher')
            ->orderBy('poin_dibutuhkan')
            ->take(3)
            ->get();

        return view('member.dashboard', [
            'member' => $member,
            'rewardUnggulan' => $rewardUnggulan,
            'promoBanners' => $this->promoBanners(),
        ]);
    }

    protected function promoBanners(): array
    {
        return [
            [
                'gradient' => 'from-brand-700 via-brand-800 to-brand-900',
                'gambar' => 'storage/banner/banner-1.jpeg',
            ],
            [
                'gradient' => 'from-orange-500 via-orange-600 to-brand-800',
                'gambar' => 'storage/banner/banner-2.jpeg',
            ],
            [
                'gradient' => 'from-orange-600 via-orange-700 to-brand-900',
                'gambar' => 'storage/banner/banner-3.jpeg',
            ],
        ];
    }
}