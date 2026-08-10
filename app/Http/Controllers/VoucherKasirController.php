<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PenukaranPoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherKasirController extends Controller
{
    /**
     * Daftar voucher aktif (belum dipakai, belum expired) milik seorang member.
     * Dipakai untuk mengisi modal "Gunakan Voucher" di kasir.
     */
    public function daftarAktif(Member $member)
    {
        try {
            $voucher = PenukaranPoin::with('reward')
                ->where('member_id', $member->id)
                ->whereHas('reward', fn ($q) => $q->where('jenis', 'voucher'))
                ->aktifDigunakan()
                ->latest()
                ->get()
                ->map(fn (PenukaranPoin $penukaran) => $this->formatVoucher($penukaran));

            return response()->json([
                'status' => 'success',
                'data' => $voucher,
            ]);
        } catch (\Exception $e) {
            Log::error('Error daftarAktif VoucherKasirController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Gagal mengambil daftar voucher member.',
            ], 500);
        }
    }

    /**
     * Validasi voucher lewat kode yang diinput manual oleh kasir.
     * Kode harus valid, milik member yang sedang dipilih, masih aktif,
     * dan belum expired/dipakai.
     */
    public function validasiKode(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20',
            'member_id' => 'required|exists:members,id',
        ], [
            'kode.required' => 'Kode voucher wajib diisi.',
            'member_id.required' => 'Pilih member terlebih dahulu sebelum memakai voucher.',
        ]);

        $penukaran = PenukaranPoin::with(['reward', 'member'])
            ->where('kode_redeem', strtoupper(trim($validated['kode'])))
            ->whereHas('reward', fn ($q) => $q->where('jenis', 'voucher'))
            ->first();

        if (!$penukaran) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Kode voucher tidak ditemukan.',
            ], 422);
        }

        if ((int) $penukaran->member_id !== (int) $validated['member_id']) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Voucher ini bukan milik member yang dipilih.',
            ], 422);
        }

        if ($penukaran->status !== 'menunggu_diambil') {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Voucher ini sudah pernah dipakai atau dibatalkan.',
            ], 422);
        }

        if ($penukaran->sudahExpired()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Voucher ini sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->formatVoucher($penukaran),
        ]);
    }

    private function formatVoucher(PenukaranPoin $penukaran): array
    {
        $reward = $penukaran->reward;

        return [
            'id' => $penukaran->id,
            'kode_redeem' => $penukaran->kode_redeem,
            'nama' => $reward->nama ?? 'Voucher',
            'deskripsi' => $reward->deskripsi ?? null,
            'jenis_potongan' => $reward->jenis_potongan ?? 'nominal',
            'nominal_potongan' => (float) ($reward->nominal_potongan ?? 0),
            'persentase' => (float) ($reward->persentase ?? 0),
            'maksimal_potongan' => $reward->maksimal_potongan !== null ? (float) $reward->maksimal_potongan : null,
            'min_pembelian' => (float) ($reward->min_pembelian ?? 0),
            'berlaku_sampai' => $penukaran->berlaku_sampai ? $penukaran->berlaku_sampai->toDateString() : null,
        ];
    }
}