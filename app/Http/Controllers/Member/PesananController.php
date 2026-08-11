<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    /**
     * Halaman status pesanan pick up: Diproses -> Ready for Pickup ->
     * Sudah Diambil, sekaligus menampilkan kode pickup buat ditunjukkan ke
     * kasir. Dipolling ringan (meta refresh via JS) supaya status ke-update
     * otomatis waktu kasir mengubahnya di POS.
     */
    public function show(Transaksi $transaksi)
    {
        $member = Auth::guard('member')->user();

        abort_unless(
            $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
            404
        );

        return view('member.pesanan.show', [
            'transaksi' => $transaksi->load('detailTransaksi'),
        ]);
    }

    /**
     * Dipakai halaman status untuk polling ringan (fetch JSON) tanpa reload
     * seluruh halaman.
     */
    public function status(Transaksi $transaksi)
    {
        $member = Auth::guard('member')->user();

        abort_unless(
            $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
            404
        );

        return response()->json([
            'status_pesanan' => $transaksi->status_pesanan,
            'label' => Transaksi::labelStatusPesanan($transaksi->status_pesanan),
            'status_pembayaran' => $transaksi->status_pembayaran,
        ]);
    }
}