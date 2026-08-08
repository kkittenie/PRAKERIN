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
                'badge' => 'Baru',
                'periode' => '1 - 31 Agustus 2026',
                'judul' => 'Paket Sarapan Hemat',
                'harga_coret' => 'Rp45K',
                'harga' => 'Rp35K',
                'waktu' => '7 - 11 Pagi',
                'gradient' => 'from-brand-700 via-brand-800 to-brand-900',
                'gambar' => 'storage/produk/chocolate-croissant.jpeg',
            ],
            [
                'badge' => 'Member Baru',
                'periode' => 'Berlaku Selamanya',
                'judul' => 'Bonus 50 Poin Pendaftaran',
                'harga_coret' => null,
                'harga' => 'Langsung cair',
                'waktu' => 'Daftar sekarang',
                'gradient' => 'from-orange-500 via-orange-600 to-brand-800',
                'gambar' => 'storage/produk/chocolate-fudge-cake.jpeg',
            ],
        ];
    }
}