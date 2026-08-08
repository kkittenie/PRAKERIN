<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
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

        $transaksi = $member->transaksi()
            ->with('detailTransaksi')
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
            'transaksi' => $transaksi,
            'riwayatStamp' => $riwayatStamp,
        ]);
    }
}