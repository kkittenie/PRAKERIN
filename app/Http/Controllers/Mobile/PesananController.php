<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PesananController extends Controller
{
    public function show(Transaksi $transaksi)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless(
                $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
                404
            );

            return view('mobile.pesanan.show', [
                'transaksi' => $transaksi->load('detailTransaksi'),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Halaman terpisah "Cara Pembayaran" (dulunya modal di halaman status
     * pesanan) — nampilin kode QRIS buat di-scan. Ditutup akses begitu
     * status_pembayaran sudah Lunas (di-acc kasir), sama seperti kartunya
     * yang otomatis ilang dari halaman show waktu sudah Lunas.
     */
    public function caraPembayaran(Transaksi $transaksi)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless(
                $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
                404
            );

            abort_if($transaksi->status_pembayaran === 'Lunas', 404);

            return view('mobile.pesanan.cara-pembayaran', [
                'transaksi' => $transaksi,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Dipakai halaman status untuk polling ringan (fetch JSON) tanpa reload
     * seluruh halaman.
     */
    public function status(Transaksi $transaksi)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless(
                $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
                404
            );

            return response()->json([
                'status_pesanan' => $transaksi->status_pesanan,
                'label' => Transaksi::labelStatusPesanan($transaksi->status_pesanan),
                'status_pembayaran' => $transaksi->status_pembayaran,
                // Ikut dipantau supaya waktu kasir menolak/mengonfirmasi bukti
                // pembayaran, mulai proses pesanan, atau menandai siap diambil
                // (yang otomatis generate kode pickup), halaman member ikut
                // ke-refresh otomatis meski status_pesanan-nya sendiri belum
                // berubah.
                'bukti_pembayaran' => $transaksi->bukti_pembayaran,
                'bukti_dikonfirmasi_pada' => optional($transaksi->bukti_dikonfirmasi_pada)->toJSON(),
                'catatan_penolakan' => $transaksi->catatan_penolakan,
                'diproses_pada' => optional($transaksi->diproses_pada)->toJSON(),
                'kode_pickup' => $transaksi->kode_pickup,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Upload bukti transfer/QRIS. Pembayaran tetap dilakukan member sendiri
     * di luar aplikasi (transfer/QRIS manual), foto ini yang jadi bukti
     * buat diverifikasi kasir sebelum status pembayaran ditandai Lunas.
     */
    public function uploadBukti(Request $request, Transaksi $transaksi)
    {
        try {
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
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Member mengunci bukti yang sudah diupload supaya nggak diubah-ubah
     * lagi sementara ditunggu diverifikasi kasir. Ini BUKAN yang menandai
     * status_pembayaran jadi Lunas — itu tetap wewenang kasir setelah
     * mengecek buktinya beneran valid.
     */
    public function konfirmasiBukti(Transaksi $transaksi)
    {
        try {
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

            // Kabari dashboard POS supaya tab "Menunggu Konfirmasi" langsung
            // update tanpa kasir perlu refresh manual.
            \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update')));

            return back()->with('success', 'Bukti pembayaran dikonfirmasi. Menunggu verifikasi dari toko.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}