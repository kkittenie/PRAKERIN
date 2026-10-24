<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Support\ActivityLogHelper;
use App\Support\PembatalanPesananPickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

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

            // Telat bayar -> batalkan dulu supaya yang tampil selalu status terbaru
            // (command terjadwal jalan tiap menit, ini jaring pengaman).
            PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi);

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

            PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi);

            if ($transaksi->status_pesanan === Transaksi::STATUS_DIBATALKAN) {
                return redirect()->route('member.pesanan.show', $transaksi);
            }

            return view('mobile.pesanan.cara-pembayaran', [
                'transaksi' => $transaksi,
                // Gambar QRIS aktif yang diatur admin (fallback ke placeholder kalau belum ada).
                'qrisUrl' => \App\Models\QrisImage::urlAktif(),
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

            // Dipanggil juga oleh timer di halaman pesanan saat hitung mundur habis.
            PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi);

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
                'alasan_pembatalan' => $transaksi->alasan_pembatalan,
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
     *
     * Alur: upload → validasi → resize (max 1200px) → compress JPEG 75% → simpan.
     * PNG/WebP otomatis dikonversi ke JPEG untuk mengoptimalkan ukuran file.
     */
    public function uploadBukti(Request $request, Transaksi $transaksi)
    {
        try {
            $member = Auth::guard('member')->user();

            abort_unless(
                $transaksi->member_id === $member->id && $transaksi->asal_pesanan === Transaksi::ASAL_MEMBER_APP,
                404
            );

            if (PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi)) {
                return back()->with('error', 'Batas waktu pembayaran sudah habis, pesanan dibatalkan otomatis.');
            }

            if ($transaksi->status_pesanan === Transaksi::STATUS_DIBATALKAN) {
                return back()->with('error', 'Pesanan ini sudah dibatalkan, tidak bisa upload bukti pembayaran.');
            }

            if ($transaksi->bukti_dikonfirmasi_pada) {
                return back()->with('error', 'Bukti pembayaran sudah dikonfirmasi, tidak bisa diubah lagi.');
            }

            // Validasi: wajib gambar valid, tanpa batas ukuran MB.
            $request->validate([
                'bukti_pembayaran' => 'required|image',
            ]);

            // Hapus bukti lama jika ada (sebelum simpan yang baru).
            if ($transaksi->kode_transaksi) {
                Storage::disk('public')->deleteDirectory('bukti-pembayaran/' . $transaksi->kode_transaksi);
            } elseif ($transaksi->bukti_pembayaran) {
                Storage::disk('public')->delete($transaksi->bukti_pembayaran);
            }

            // --- Resize & compress menggunakan Intervention Image ---
            $manager = new ImageManager(GdDriver::class);
            $image = $manager->decodePath($request->file('bukti_pembayaran')->getPathname());

            // Resize hanya jika sisi terpanjang melebihi 1200px, menjaga aspect ratio.
            $image->scaleDown(width: 1200, height: 1200);

            // Encode ke JPEG kualitas 75% (PNG/WebP ikut dikonversi).
            $encoded = $image->encode(new JpegEncoder(quality: 75));

            // Generate nama file unik dengan ekstensi .jpg dalam folder kode_transaksi.
            $filename = 'bukti-pembayaran/' . $transaksi->kode_transaksi . '/' . uniqid('bukti_', true) . '.jpg';

            // Simpan ke disk public.
            Storage::disk('public')->put($filename, (string) $encoded);

            $transaksi->update(['bukti_pembayaran' => $filename]);

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

            // Waktu bayar sudah habis sebelum member sempat konfirmasi -> batalkan.
            if (PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi)) {
                return back()->with('error', 'Batas waktu pembayaran sudah habis, pesanan dibatalkan otomatis.');
            }

            if ($transaksi->status_pesanan === Transaksi::STATUS_DIBATALKAN) {
                return back()->with('error', 'Pesanan ini sudah dibatalkan.');
            }

            if (! $transaksi->bukti_pembayaran) {
                return back()->with('error', 'Upload bukti pembayaran dulu sebelum konfirmasi.');
            }

            if ($transaksi->bukti_dikonfirmasi_pada) {
                return back()->with('error', 'Bukti pembayaran sudah dikonfirmasi sebelumnya.');
            }

            $transaksi->update(['bukti_dikonfirmasi_pada' => now()]);

            // Kabari dashboard POS supaya tab "Menunggu Konfirmasi" langsung
            // update tanpa kasir perlu refresh manual.
            \App\Support\BroadcastHelper::aman(fn () => event(new \App\Events\PesananPickupUpdated($transaksi, 'update', 'bukti_dikonfirmasi')));

            return back()->with('success', 'Bukti pembayaran dikonfirmasi. Menunggu verifikasi dari toko.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}