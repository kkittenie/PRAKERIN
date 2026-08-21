<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\StampRedemption;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StampRedemptionKasirController extends Controller
{
    public function daftarAktif(Member $member)
    {
        try {
            $redemption = StampRedemption::with('produk')
                ->where('member_id', $member->id)
                ->where('status', 'menunggu_diambil')
                ->latest()
                ->get()
                ->map(fn(StampRedemption $r) => $this->formatRedemption($r));

            return response()->json([
                'status' => 'success',
                'data' => $redemption,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function validasiKode(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|max:20',
                'member_id' => 'required|exists:members,id',
            ], [
                'kode.required' => 'Kode reward stamp wajib diisi.',
                'member_id.required' => 'Pilih member terlebih dahulu sebelum memakai reward stamp.',
            ]);

            $redemption = StampRedemption::with('produk')
                ->where('kode_redeem', strtoupper(trim($validated['kode'])))
                ->first();

            if (!$redemption) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Kode reward stamp tidak ditemukan.',
                ], 422);
            }

            if ((int) $redemption->member_id !== (int) $validated['member_id']) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Reward stamp ini bukan milik member yang dipilih.',
                ], 422);
            }

            if ($redemption->status !== 'menunggu_diambil') {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Reward stamp ini sudah pernah diambil atau dibatalkan.',
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'data' => $this->formatRedemption($redemption),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function formatRedemption(StampRedemption $redemption): array
    {
        $produk = $redemption->produk;

        return [
            'id' => $redemption->id,
            'kode_redeem' => $redemption->kode_redeem,
            'nama_produk' => $produk->nama_produk ?? 'Produk tidak tersedia',
            'gambar' => $produk?->foto ? asset('storage/' . $produk->foto) : null,
            'jumlah' => $redemption->jumlah,
            'butuh_produksi' => $produk
                ? $produk->habisSemuaUkuran()
                : false,
        ];
    }
}
