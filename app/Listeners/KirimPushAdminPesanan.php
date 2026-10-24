<?php

namespace App\Listeners;

use App\Events\PesananPickupUpdated;
use App\Models\Transaksi;
use App\Support\NotificationHelper;
use Illuminate\Support\Facades\Log;

/**
 * Kebalikan dari KirimNotifikasiMemberPesanan: kalau itu ngabarin MEMBER,
 * listener ini ngabarin ADMIN/KASIR lewat push notification (FCM) ke HP
 * mereka, jadi nggak perlu lagi buka dashboard buat tahu ada pesanan masuk.
 *
 * Event yang ditangani:
 * - 'baru'               : member baru checkout pesanan pickup
 * - 'bukti_dikonfirmasi' : member sudah upload & kunci bukti bayar, tinggal
 *                          menunggu kasir memverifikasi
 */
class KirimPushAdminPesanan
{
    public function handle(PesananPickupUpdated $event): void
    {
        if (! in_array($event->aksi, ['baru', 'bukti_dikonfirmasi'], true)) {
            return;
        }

        try {
            $transaksi = Transaksi::with('member')->find($event->transaksiId);

            if (! $transaksi) {
                return;
            }

            $namaMember = optional($transaksi->member)->nama ?? 'member';
            $total = 'Rp ' . number_format((float) $transaksi->total_bayar, 0, ',', '.');

            if ($event->aksi === 'baru') {
                $title = 'Pesanan Pickup Baru #' . $transaksi->kode_transaksi;
                $body = "Pesanan baru dari {$namaMember} ({$total}).";
            } else {
                $title = 'Bukti Pembayaran Masuk #' . $transaksi->kode_transaksi;
                $body = "{$namaMember} sudah mengirim bukti pembayaran ({$total}). Menunggu konfirmasi kasir.";
            }

            NotificationHelper::pushKeAdmin(
                $title,
                $body,
                route('pesanan-pickup.indeks', ['buka' => $transaksi->id]),
                'pesanan',
                'lihat_pesanan_pickup'
            );
        } catch (\Throwable $e) {
            // Gagal kirim push jangan sampai bikin checkout member ikut gagal.
            Log::error('Gagal kirim push notifikasi pesanan ke admin: ' . $e->getMessage());
        }
    }
}