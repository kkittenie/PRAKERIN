<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\RewardKatalog;
use App\Models\Banner;
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

        $produkPilihan = Produk::with('kategori')
            ->where('status_aktif', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('member.dashboard', [
            'member' => $member,
            'rewardUnggulan' => $rewardUnggulan,
            'produkPilihan' => $produkPilihan,
            'promoBanners' => $this->promoBanners(),
        ]);
    }

    protected function promoBanners()
    {
        return \App\Models\Banner::tampil()
            ->orderBy('urutan')
            ->get()
            ->map(function ($banner) {
                return [
                    'gambar' => 'storage/' . $banner->gambar,
                ];
            });
    }
}