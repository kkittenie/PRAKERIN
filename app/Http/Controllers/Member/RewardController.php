<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PenukaranPoin;
use App\Models\RewardKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $member = Auth::guard('member')->user();

        $jenis = $request->query('jenis');

        $rewards = RewardKatalog::aktif()
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->orderBy('poin_dibutuhkan')
            ->get();

        return view('member.rewards.index', [
            'member' => $member,
            'rewards' => $rewards,
            'jenisAktif' => $jenis,
        ]);
    }

    public function show(RewardKatalog $reward)
    {
        $member = Auth::guard('member')->user();

        abort_unless($reward->status_aktif, 404);

        return view('member.rewards.show', [
            'member' => $member,
            'reward' => $reward,
        ]);
    }

    public function redeem(Request $request, RewardKatalog $reward)
    {
        $member = Auth::guard('member')->user();

        $penukaran = DB::transaction(function () use ($member, $reward) {
            // Lock baris supaya aman dari redeem ganda/stok minus saat diakses bersamaan.
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
            ]);
        });

        return redirect()
            ->route('member.riwayat')
            ->with('success', 'Berhasil ditukar! Tunjukkan kode '.$penukaran->kode_redeem.' ke kasir untuk mengambil reward-nya.');
    }
}