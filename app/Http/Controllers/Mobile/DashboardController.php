<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\RewardKatalog;
use App\Models\Banner;
use App\Models\MemberLevel;
use App\Support\ActivityLogHelper;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            $rewardUnggulan = RewardKatalog::aktif()
                ->where('jenis', 'voucher')
                ->where(function ($q) use ($member) {
                    $q->where('target_member', 'semua');
                    if ($member) {
                        $q->orWhere(function ($sub) use ($member) {
                            $sub->where('target_member', 'khusus')
                                ->whereHas('members', fn ($m) => $m->where('members.id', $member->id));
                        });
                    }
                })
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($voucher) use ($member) {
                    if ($member) {
                        $status = $voucher->cekStatusUntukMember($member);
                        $voucher->tersedia = $status['tersedia'];
                        $voucher->alasan_nonaktif = $status['alasan'];
                        $voucher->jumlah_pakai = $voucher->jumlahPemakaianMember($member->id);
                        $voucher->sisa_jatah = max(0, ($voucher->batas_penggunaan_per_member ?: 1) - $voucher->jumlah_pakai);
                    } else {
                        $voucher->tersedia = true;
                        $voucher->alasan_nonaktif = null;
                        $voucher->sisa_jatah = $voucher->batas_penggunaan_per_member ?: 1;
                    }
                    return $voucher;
                });

            $produkPilihan = Produk::with(['kategori'])
                ->where('status_aktif', true)
                ->dijualSatuan()
                ->inRandomOrder()
                ->take(4)
                ->get();

            $kategoris = KategoriProduk::with(['produk' => function ($q) {
                $q->where('status_aktif', true)->dijualSatuan();
            }])->whereHas('produk', function ($q) {
                $q->where('status_aktif', true)->dijualSatuan();
            })->orderBy('nama_kategori')->get();

            return view('mobile.dashboard', [
                'member' => $member,
                'rewardUnggulan' => $rewardUnggulan,
                'produkPilihan' => $produkPilihan,
                'kategoris' => $kategoris,
                'promoBanners' => $this->promoBanners(),
                'semuaTier' => MemberLevel::aktif()->get(),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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