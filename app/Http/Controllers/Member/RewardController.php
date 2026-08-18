<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PenukaranPoin;
use App\Models\RewardKatalog;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            $jenis = $request->query('jenis');

            $rewards = RewardKatalog::aktif()
                ->where('jenis', 'voucher')
                ->orderBy('poin_dibutuhkan')
                ->get();

            return view('member.rewards.index', [
                'member' => $member,
                'rewards' => $rewards,
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

            abort_unless($reward->status_aktif && $reward->jenis === 'voucher', 404);

            return view('member.rewards.show', [
                'member' => $member,
                'reward' => $reward->load(['produks' => function ($query) {
                    $query->select('produks.id', 'produks.kategori_id', 'produks.nama_produk', 'produks.foto')
                        ->with('kategori:id,nama_kategori')
                        ->orderBy('nama_produk');
                }]),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function redeem(Request $request, RewardKatalog $reward)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless($reward->jenis === 'voucher', 404);

            $penukaran = DB::transaction(function () use ($member, $reward) {
                $memberTerkunci = $member->newQuery()->lockForUpdate()->find($member->id);
                $rewardTerkunci = $reward->newQuery()->lockForUpdate()->find($reward->id);

                abort_unless($rewardTerkunci->status_aktif, 422, 'Reward ini sudah tidak tersedia.');

                if (! is_null($rewardTerkunci->stok) && $rewardTerkunci->stok <= 0) {
                    abort(422, 'Stok reward ini sudah habis.');
                }

                if ($memberTerkunci->poin < $rewardTerkunci->poin_dibutuhkan) {
                    abort(422, 'Poin kamu belum cukup untuk menukar reward ini.');
                }

                $memberTerkunci->decrement('poin', $rewardTerkunci->poin_dibutuhkan);

                if (! is_null($rewardTerkunci->stok)) {
                    $rewardTerkunci->decrement('stok');
                }

                return PenukaranPoin::create([
                    'member_id' => $memberTerkunci->id,
                    'reward_katalog_id' => $rewardTerkunci->id,
                    'poin_terpakai' => $rewardTerkunci->poin_dibutuhkan,
                    'status' => 'menunggu_diambil',
                    'berlaku_sampai' => $rewardTerkunci->masa_berlaku_hari
                        ? now()->addDays($rewardTerkunci->masa_berlaku_hari)->toDateString()
                        : null,
                ]);
            });

            return redirect()
                ->route('member.riwayat')
                ->with('success', 'Berhasil ditukar! Tunjukkan kode '.$penukaran->kode_redeem.' ke kasir untuk mengambil reward-nya.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}