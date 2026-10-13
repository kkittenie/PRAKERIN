<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\RewardKatalog;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            $jenis = $request->query('jenis');

            // Ambil seluruh voucher yang ditujukan untuk umum atau ditujukan khusus untuk member ini
            $vouchers = RewardKatalog::where('jenis', 'voucher')
                ->where(function ($q) use ($member) {
                    $q->where('target_member', 'semua')
                        ->orWhere(function ($sub) use ($member) {
                            $sub->where('target_member', 'khusus')
                                ->whereHas('members', fn ($m) => $m->where('members.id', $member->id));
                        });
                })
                ->orderByDesc('status_aktif')
                ->latest()
                ->get()
                ->map(function ($voucher) use ($member) {
                    $status = $voucher->cekStatusUntukMember($member);
                    $voucher->tersedia = $status['tersedia'];
                    $voucher->alasan_nonaktif = $status['alasan'];
                    $voucher->jumlah_pakai = $voucher->jumlahPemakaianMember($member->id);
                    $voucher->sisa_jatah = max(0, ($voucher->batas_penggunaan_per_member ?: 1) - $voucher->jumlah_pakai);
                    return $voucher;
                });

            return view('mobile.rewards.index', [
                'member' => $member,
                'vouchers' => $vouchers,
                'jenisAktif' => $jenis,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(RewardKatalog $reward)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless($reward->jenis === 'voucher', 404);

            // Cek status voucher untuk member ini
            $status = $reward->cekStatusUntukMember($member);
            $reward->tersedia = $status['tersedia'];
            $reward->alasan_nonaktif = $status['alasan'];
            $reward->jumlah_pakai = $reward->jumlahPemakaianMember($member->id);
            $reward->sisa_jatah = max(0, ($reward->batas_penggunaan_per_member ?: 1) - $reward->jumlah_pakai);

            return view('mobile.rewards.show', [
                'member' => $member,
                'voucher' => $reward->load(['produks' => function ($query) {
                    $query->select('produks.id', 'produks.kategori_id', 'produks.nama_produk', 'produks.foto')
                        ->with('kategori:id,nama_kategori')
                        ->orderBy('nama_produk');
                }]),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}