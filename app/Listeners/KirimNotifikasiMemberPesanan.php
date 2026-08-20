<?php

namespace App\Listeners;

use App\Events\PesananPickupUpdated;
use App\Models\Transaksi;
use App\Support\NotificationHelper;
use Illuminate\Support\Facades\Log;

/**
 * Setiap kali status pesanan pickup berubah (dari sisi kasir) ATAU member
 * baru checkout, event PesananPickupUpdated ini juga dipakai buat ngirimin
 * notifikasi ke MEMBER-nya (bukan cuma refresh dashboard kasir kaya
 * sebelumnya): notif in-app di halaman /mobile/notifikasi + push notification
 * FCM ke HP-nya.
 */
class KirimNotifikasiMemberPesanan
{
    public function handle(PesananPickupUpdated $event): void
    {
        if (! $event->aksi) {
            return;
        }

        $transaksi = Transaksi::find($event->transaksiId);

        if (! $transaksi || ! $transaksi->member_id) {
            return;
        }

        $pesan = NotificationHelper::pesanUntukAksiPesanan($transaksi, $event->aksi);

        if (! $pesan) {
            return;
        }

        [$title, $desc, $iconType] = $pesan;

        try {
            NotificationHelper::untukMember(
                $transaksi->member_id,
                $title,
                $desc,
                'pesanan',
                route('member.pesanan.show', $transaksi->id),
                $iconType
            );
        } catch (\Throwable $e) {
            // Jangan sampai gagal kirim notif bikin aksi kasir (mis. tandai
            // siap diambil) ikut gagal — cukup dicatat di log.
            Log::error('Gagal kirim notifikasi member: ' . $e->getMessage());
        }
    }
}
