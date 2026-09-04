<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MemberStamp;
use App\Models\StampProgram;
use App\Models\StampRedemption;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampController extends Controller
{
    public function index(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();
            $program = StampProgram::aktifSekarang();
            $program?->load('hadiah.produk');

            $memberStamp = null;
            if ($program) {
                $memberStamp = MemberStamp::firstOrCreate(
                    ['member_id' => $member->id, 'stamp_program_id' => $program->id],
                    ['jumlah_stamp' => 0]
                );
            }

            return view('mobile.stamps.index', [
                'member' => $member,
                'program' => $program,
                'memberStamp' => $memberStamp,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function redeem(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();

            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
            ]);

            $redemption = DB::transaction(function () use ($member, $validated) {
                $program = StampProgram::aktifSekarang();
                abort_unless($program, 422, 'Tidak ada program stamp yang sedang aktif.');

                $memberStamp = MemberStamp::where('member_id', $member->id)
                    ->where('stamp_program_id', $program->id)
                    ->lockForUpdate()
                    ->first();

                if (!$memberStamp || $memberStamp->jumlah_stamp < $program->jumlah_stamp_dibutuhkan) {
                    abort(422, 'Stamp kamu belum penuh.');
                }

                $hadiahValid = $program->hadiah()->where('produk_id', $validated['produk_id'])->exists();
                abort_unless($hadiahValid, 422, 'Produk ini bukan pilihan hadiah yang tersedia.');

                if ($program->maksimal_redeem_tercapai) {
                    abort(422, 'Kuota penukaran program stamp ini sudah habis.');
                }

                $hadiah = $program->hadiah()->where('produk_id', $validated['produk_id'])->first();

                $produk = \App\Models\Produk::where('id', $validated['produk_id'])->lockForUpdate()->first();
                if ($produk->stok < $hadiah->jumlah) {
                    abort(422, 'Stok produk hadiah ini tidak mencukupi. Hubungi kasir/admin.');
                }
                $produk->decrement('stok', $hadiah->jumlah);

                $memberStamp->decrement('jumlah_stamp', $program->jumlah_stamp_dibutuhkan);
                $program->increment('jumlah_redeem');

                $redemption = StampRedemption::create([
                    'member_id' => $member->id,
                    'stamp_program_id' => $program->id,
                    'produk_id' => $validated['produk_id'],
                    'sumber' => 'member',
                    'status' => 'menunggu_diambil',
                ]);

                \App\Support\NotificationHelper::untukMember(
                    $member->id,
                    'Penukaran Stamp Berhasil',
                    'Kamu berhasil menukar stamp dengan ' . ($produk->nama_produk ?? 'hadiah') . '. Kode: ' . $redemption->kode_redeem,
                    'stamp',
                    route('member.reward.index', ['tab' => 'stamp']),
                    'success'
                );

                return $redemption;
            });

            return redirect()
                ->route('member.reward.index', ['tab' => 'stamp'])
                ->with('success', 'Berhasil ditukar! Tunjukkan kode ' . $redemption->kode_redeem . ' ke kasir untuk mengambil hadiahnya.')
                ->with('redeem_code', $redemption->kode_redeem);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}