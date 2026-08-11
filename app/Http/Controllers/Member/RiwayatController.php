<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        $penukaran = $member->penukaranPoin()
            ->with('reward')
            ->latest()
            ->get();

        // Semua pesanan pick up dari Member App (tab "Pesanan" tersendiri) —
        // aktif (Diproses/Ready for Pickup) maupun yang sudah selesai/dibatalkan.
        // Dipisah dari tab Riwayat Transaksi biar nggak dobel & gampang dibuka
        // lagi kalau halaman Status Pesanan-nya nggak sengaja ketutup.
        $pesanan = $member->transaksi()
            ->with('detailTransaksi')
            ->where('asal_pesanan', Transaksi::ASAL_MEMBER_APP)
            ->latest()
            ->take(20)
            ->get();

        // Riwayat Transaksi murni transaksi kasir (POS) — pesanan pick up
        // ditampilkan di tab "Pesanan" di atas.
        $transaksi = $member->transaksi()
            ->with('detailTransaksi')
            ->where('asal_pesanan', '!=', Transaksi::ASAL_MEMBER_APP)
            ->latest()
            ->take(20)
            ->get();

        // Riwayat penukaran hadiah dari kartu stamp — terpisah dari $penukaran
        // (yang khusus penukaran poin).
        $riwayatStamp = $member->stampRedemptions()
            ->with(['produk', 'stampProgram'])
            ->latest()
            ->get();

        return view('member.riwayat.index', [
            'member' => $member,
            'penukaran' => $penukaran,
            'pesanan' => $pesanan,
            'transaksi' => $transaksi,
            'riwayatStamp' => $riwayatStamp,
        ]);
    }
}