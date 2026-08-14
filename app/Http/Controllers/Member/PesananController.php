<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Upload bukti transfer/QRIS. Pembayaran tetap dilakukan member sendiri
     * di luar aplikasi (transfer/QRIS manual), foto ini yang jadi bukti
     * buat diverifikasi kasir sebelum status pembayaran ditandai Lunas.
     */
    public function uploadBukti(Request $request, Transaksi $transaksi)
    {
        $member = Auth::guard('member')->user();

        abort_unless(
            $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
            404
        );

        if ($transaksi->bukti_dikonfirmasi_pada) {
            return back()->with('error', 'Bukti pembayaran sudah dikonfirmasi, tidak bisa diubah lagi.');
        }

        $request->validate([
            'bukti_pembayaran' => 'required|image|max:4096',
        ]);

        if ($transaksi->bukti_pembayaran) {
            Storage::disk('public')->delete($transaksi->bukti_pembayaran);
        }

        $path = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $transaksi->update(['bukti_pembayaran' => $path]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }

    /**
     * Member mengunci bukti yang sudah diupload supaya nggak diubah-ubah
     * lagi sementara ditunggu diverifikasi kasir. Ini BUKAN yang menandai
     * status_pembayaran jadi Lunas — itu tetap wewenang kasir setelah
     * mengecek buktinya beneran valid.
     */
    public function konfirmasiBukti(Transaksi $transaksi)
    {
        $member = Auth::guard('member')->user();

        abort_unless(
            $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
            404
        );

        if (! $transaksi->bukti_pembayaran) {
            return back()->with('error', 'Upload bukti pembayaran dulu sebelum konfirmasi.');
        }

        if ($transaksi->bukti_dikonfirmasi_pada) {
            return back()->with('error', 'Bukti pembayaran sudah dikonfirmasi sebelumnya.');
        }

        $transaksi->update(['bukti_dikonfirmasi_pada' => now()]);

        return back()->with('success', 'Bukti pembayaran dikonfirmasi. Menunggu verifikasi dari toko.');
    }
}