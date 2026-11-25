<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DetailProduk;
use App\Models\DetailTransaksi;
use App\Models\MemberStamp;
use App\Models\Produk;
use App\Models\StampProgram;
use App\Models\StampRedemption;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use App\Support\MemberCart;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StampController extends Controller
{
    public function index(Request $request)
    {
        try {
            $member = Auth::guard('member')->user();
            $program = StampProgram::aktifSekarang();
            $program?->load('hadiah.produk', 'hadiah.detailProduk');

            $memberStamp = null;
            if ($program) {
                $memberStamp = MemberStamp::firstOrCreate(
                    ['member_id' => $member->id, 'stamp_program_id' => $program->id],
                    ['jumlah_stamp' => 0]
                );
            }

            // Hadiah stamp yang sudah ditukar dan menunggu diambil
            $rewardAktif = StampRedemption::with(['produk', 'detailProduk', 'stampProgram'])
                ->where('member_id', $member->id)
                ->where('status', 'menunggu_diambil')
                ->latest()
                ->get();

            return view('mobile.stamps.index', [
                'member' => $member,
                'program' => $program,
                'memberStamp' => $memberStamp,
                'rewardAktif' => $rewardAktif,
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
                'detail_produk_id' => 'nullable|integer',
                'metode_ambil' => 'nullable|in:aplikasi,kasir',
            ]);

            $metodeAmbil = $validated['metode_ambil'] ?? 'aplikasi';

            $result = DB::transaction(function () use ($member, $validated, $metodeAmbil) {
                $program = StampProgram::aktifSekarang();
                abort_unless($program, 422, 'Tidak ada program stamp yang sedang aktif.');

                $memberStamp = MemberStamp::where('member_id', $member->id)
                    ->where('stamp_program_id', $program->id)
                    ->lockForUpdate()
                    ->first();

                if (!$memberStamp || $memberStamp->jumlah_stamp < $program->jumlah_stamp_dibutuhkan) {
                    abort(422, 'Stamp kamu belum penuh.');
                }

                $detailId = !empty($validated['detail_produk_id']) ? (int) $validated['detail_produk_id'] : null;

                $hadiahQuery = $program->hadiah()->where('produk_id', $validated['produk_id']);
                $detailId
                    ? $hadiahQuery->where('detail_produk_id', $detailId)
                    : $hadiahQuery->whereNull('detail_produk_id');

                $hadiah = $hadiahQuery->first();
                abort_unless($hadiah, 422, 'Produk ini bukan pilihan hadiah yang tersedia.');

                if ($program->maksimal_redeem_tercapai) {
                    abort(422, 'Kuota penukaran program stamp ini sudah habis.');
                }

                $produk = Produk::where('id', $validated['produk_id'])->lockForUpdate()->first();
                $detail = null;

                if ($detailId) {
                    // Hadiah menunjuk varian/ukuran tertentu: stok diambil dari varian itu saja.
                    $detail = DetailProduk::where('id', $detailId)
                        ->where('produk_id', $produk->id)
                        ->lockForUpdate()
                        ->first();

                    abort_unless($detail && $detail->status_aktif, 422, 'Varian hadiah ini sudah tidak tersedia. Hubungi kasir/admin.');

                    if ((int) $detail->stok < (int) $hadiah->jumlah) {
                        abort(422, 'Stok varian hadiah ini tidak mencukupi. Hubungi kasir/admin.');
                    }

                    $detail->stok = (int) $detail->stok - (int) $hadiah->jumlah;
                    $detail->save();
                    $produk->segarkanStok();
                } else {
                    if ($produk->stok < $hadiah->jumlah) {
                        abort(422, 'Stok produk hadiah ini tidak mencukupi. Hubungi kasir/admin.');
                    }
                    $produk->kurangiStokTotal((int) $hadiah->jumlah);
                }

                $memberStamp->decrement('jumlah_stamp', $program->jumlah_stamp_dibutuhkan);
                $program->increment('jumlah_redeem');

                $redemption = StampRedemption::create([
                    'member_id' => $member->id,
                    'stamp_program_id' => $program->id,
                    'produk_id' => $validated['produk_id'],
                    'detail_produk_id' => $detail?->id,
                    'jumlah' => $hadiah->jumlah ?: 1,
                    'sumber' => 'member',
                    'status' => 'menunggu_diambil',
                ]);

                if ($metodeAmbil === 'aplikasi') {
                    // Bersihkan item reward stamp lama dari keranjang
                    foreach (MemberCart::items() as $it) {
                        if (!empty($it['is_reward_stamp'])) {
                            MemberCart::hapus($it['id']);
                        }
                    }

                    MemberCart::tambah([
                        'produk_id' => $produk->id,
                        'detail_produk_id' => $detail?->id,
                        'nama_produk' => $produk->nama_produk,
                        'ukuran' => $detail?->ukuran,
                        'varian_rasa' => $detail?->variasi,
                        'foto' => $detail?->foto ?: $produk->foto,
                        'harga_satuan' => 0,
                        'harga_asal' => (float) ($detail?->harga_jual ?? $produk->harga_jual ?? 0),
                        'diskon_produk_satuan' => (float) ($detail?->harga_jual ?? $produk->harga_jual ?? 0),
                        'nama_promo' => 'Reward Stamp',
                        'catatan' => 'Hadiah Stamp (' . $redemption->kode_redeem . ')',
                        'jumlah' => $hadiah->jumlah ?: 1,
                        'is_reward_stamp' => true,
                        'stamp_redemption_id' => $redemption->id,
                    ]);

                    Session::put(CheckoutController::SESSION_STAMP_REDEMPTION_KEY, $redemption->id);

                    NotificationHelper::untukMember(
                        $member->id,
                        'Hadiah Stamp Masuk ke Keranjang',
                        'Hadiah ' . ($detail ? $produk->nama_produk . ' (' . $detail->nama_lengkap . ')' : $produk->nama_produk) . ' siap dipesan via aplikasi. Silakan selesaikan checkout.',
                        'stamps',
                        route('member.checkout.index'),
                        'success'
                    );

                    return ['metode' => 'aplikasi', 'redemption' => $redemption];
                }

                NotificationHelper::untukMember(
                    $member->id,
                    'Penukaran Stamp Berhasil',
                    'Kamu berhasil menukar stamp dengan ' . ($detail ? $produk->nama_produk . ' (' . $detail->nama_lengkap . ')' : ($produk->nama_produk ?? 'hadiah')) . '. Kode: ' . $redemption->kode_redeem,
                    'stamps',
                    route('member.stamps.index'),
                    'success'
                );

                return ['metode' => 'kasir', 'redemption' => $redemption];
            });

            if ($result['metode'] === 'aplikasi') {
                return redirect()
                    ->route('member.checkout.index')
                    ->with('success', 'Stamp reward added to cart! Please complete your checkout.');
            }

            return redirect()
                ->route('member.reward.index', ['tab' => 'stamp'])
                ->with('success', 'Redeemed successfully! Show code ' . $result['redemption']->kode_redeem . ' to the cashier or claim via app.')
                ->with('redeem_code', $result['redemption']->kode_redeem);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function ambilDiAplikasi(Request $request, StampRedemption $stampRedemption)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless((int) $stampRedemption->member_id === (int) $member->id, 403, 'Akses tidak diizinkan.');
            abort_unless($stampRedemption->status === 'menunggu_diambil', 422, 'Hadiah stamp ini sudah pernah diambil.');

            $produk = $stampRedemption->produk;
            abort_unless($produk, 422, 'Produk hadiah tidak ditemukan.');

            // Bersihkan item reward stamp lama jika ada di keranjang
            foreach (MemberCart::items() as $it) {
                if (!empty($it['is_reward_stamp'])) {
                    MemberCart::hapus($it['id']);
                }
            }

            $detail = $stampRedemption->detailProduk;

            MemberCart::tambah([
                'produk_id' => $produk->id,
                'detail_produk_id' => $detail?->id,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $detail?->ukuran,
                'varian_rasa' => $detail?->variasi,
                'foto' => $detail?->foto ?: $produk->foto,
                'harga_satuan' => 0,
                'harga_asal' => (float) ($detail?->harga_jual ?? $produk->harga_jual ?? 0),
                'diskon_produk_satuan' => (float) ($detail?->harga_jual ?? $produk->harga_jual ?? 0),
                'nama_promo' => 'Reward Stamp',
                'catatan' => 'Hadiah Stamp (' . $stampRedemption->kode_redeem . ')',
                'jumlah' => $stampRedemption->jumlah ?: 1,
                'is_reward_stamp' => true,
                'stamp_redemption_id' => $stampRedemption->id,
            ]);

            Session::put(CheckoutController::SESSION_STAMP_REDEMPTION_KEY, $stampRedemption->id);

            return redirect()
                ->route('member.checkout.index')
                ->with('success', 'Stamp reward added to cart! Please complete your checkout.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}