<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberStamp;
use App\Models\StampProgram;
use App\Models\StampRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();
        $program = StampProgram::with('hadiah.produk')->aktifSekarang();

        $memberStamp = null;
        if ($program) {
            $memberStamp = MemberStamp::firstOrCreate(
                ['member_id' => $member->id, 'stamp_program_id' => $program->id],
                ['jumlah_stamp' => 0]
            );
        }

        return view('member.stamps.index', [
            'member' => $member,
            'program' => $program,
            'memberStamp' => $memberStamp,
        ]);
    }

    public function redeem(Request $request)
    {
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

            return StampRedemption::create([
                'member_id' => $member->id,
                'stamp_program_id' => $program->id,
                'produk_id' => $validated['produk_id'],
                'sumber' => 'member',
                'status' => 'menunggu_diambil',
            ]);
        });

        return redirect()
            ->route('member.stamps.index')
            ->with('success', 'Berhasil ditukar! Tunjukkan kode ' . $redemption->kode_redeem . ' ke kasir untuk mengambil hadiahnya.');
    }
}