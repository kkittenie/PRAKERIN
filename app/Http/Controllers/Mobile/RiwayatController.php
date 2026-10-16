<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        try {
            $member = Auth::guard('member')->user();

            $penukaran = $member->penukaranPoin()
                ->with('reward')
                ->latest()
                ->get();

            // Riwayat gabungan: pesanan pick up dari Member App (online) &
            // transaksi kasir/POS (offline) ditampilkan dalam satu tab "Riwayat",
            // dibedakan lewat label Online/Offline di masing-masing kartu.
            // Pesanan yang masih aktif (Diproses/Ready for Pickup) dipisah ke
            // section "Pesanan Aktif" di paling atas lewat blade, bukan di sini,
            // supaya urutannya tetap stabil walau ada transaksi kasir yang lebih baru.
            $riwayat = $member->transaksi()
                ->with('detailTransaksi')
                ->latest()
                ->take(40)
                ->get();

            // Riwayat penukaran hadiah dari kartu stamp — terpisah dari $penukaran
            // (yang khusus penukaran poin).
            $riwayatStamp = $member->stampRedemptions()
                ->with(['produk', 'detailProduk', 'stampProgram'])
                ->latest()
                ->get();

            return view('mobile.riwayat.index', [
                'member' => $member,
                'penukaran' => $penukaran,
                'riwayat' => $riwayat,
                'riwayatStamp' => $riwayatStamp,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}