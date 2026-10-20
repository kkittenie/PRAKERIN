<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\RewardKatalog;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class VoucherKasirController extends Controller
{
    /**
     * Daftar voucher aktif yang berhak digunakan oleh seorang member.
     * Dipakai untuk mengisi modal "Gunakan Voucher" di kasir.
     */
    public function daftarAktif(Member $member)
    {
        try {
            $vouchers = RewardKatalog::with(['produks', 'rewardProduks'])
                ->where('jenis', 'voucher')
                ->where('status_aktif', true)
                ->where(function ($q) use ($member) {
                    $q->where('target_member', 'semua')
                      ->orWhere(function ($sub) use ($member) {
                          $sub->where('target_member', 'khusus')
                              ->whereHas('members', fn ($m) => $m->where('members.id', $member->id));
                      });
                })
                ->get()
                ->filter(fn (RewardKatalog $v) => $v->berhakDigunakan($member))
                ->values()
                ->map(fn (RewardKatalog $voucher) => $this->formatVoucher($voucher, $member));

            return response()->json([
                'status' => 'success',
                'data' => $vouchers,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Daftar voucher aktif untuk pembeli non member (kasir tanpa memilih member).
     * Hanya voucher bertarget "non_member" yang masih berlaku dan kuotanya belum habis.
     */
    public function daftarNonMember()
    {
        try {
            $vouchers = RewardKatalog::with(['produks', 'rewardProduks'])
                ->where('jenis', 'voucher')
                ->where('status_aktif', true)
                ->where('target_member', RewardKatalog::TARGET_NON_MEMBER)
                ->berlakuTanggal()
                ->get()
                ->filter(fn (RewardKatalog $v) => $v->berhakDigunakanNonMember())
                ->values()
                ->map(fn (RewardKatalog $voucher) => $this->formatVoucher($voucher));

            return response()->json([
                'status' => 'success',
                'data' => $vouchers,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Validasi kode manual dinonaktifkan sesuai alur voucher baru
     */
    public function validasiKode(Request $request)
    {
        return response()->json([
            'status' => 'error',
            'pesan' => 'Input kode manual sudah dinonaktifkan. Silakan pilih langsung voucher dari daftar voucher yang tersedia untuk member ini.',
        ], 422);
    }

    private function formatVoucher(RewardKatalog $voucher, ?Member $member = null): array
    {
        $nonMember = $voucher->untukNonMember();
        $batas = $voucher->batas_penggunaan_per_member ?: 1;

        if ($nonMember) {
            // Pembeli non member tidak terdata -> batas dihitung sebagai kuota total.
            $pemakaian = $voucher->jumlahPemakaianNonMember();
        } else {
            $pemakaian = $member ? $voucher->jumlahPemakaianMember($member->id) : 0;
        }

        return [
            'id' => $voucher->id,
            'nama' => $voucher->nama,
            'kode_reward' => $voucher->kode_reward,
            'kode_redeem' => $voucher->kode_reward,
            'deskripsi' => $voucher->deskripsi,
            'target_member' => $voucher->target_member,
            'jenis_potongan' => $voucher->jenis_potongan ?? 'nominal',
            'nominal_potongan' => (float) ($voucher->nominal_potongan ?? 0),
            'persentase' => (float) ($voucher->persentase ?? 0),
            'maksimal_potongan' => $voucher->maksimal_potongan !== null ? (float) $voucher->maksimal_potongan : null,
            'min_pembelian' => (float) ($voucher->min_pembelian ?? 0),
            'berlaku_sampai' => $voucher->tanggal_selesai ? $voucher->tanggal_selesai->format('d/m/Y') : null,
            'tanggal_mulai' => $voucher->tanggal_mulai ? $voucher->tanggal_mulai->format('d/m/Y') : null,
            'sisa_penggunaan' => max(0, $batas - $pemakaian),
            'batas_penggunaan' => $batas,
            'produk_ids' => $voucher->produks->pluck('id')->values()->all(),
            'reward_produks' => $voucher->rewardProduks->map(fn ($rp) => [
                'produk_id' => $rp->produk_id,
                'detail_produk_id' => $rp->detail_produk_id,
                'varian_produk_id' => $rp->varian_produk_id,
            ])->values()->all(),
        ];
    }
}