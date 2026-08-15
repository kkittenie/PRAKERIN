<?php

namespace App\Events;

use App\Models\Transaksi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disiarkan tiap kali ada pesanan pick up (dari Member App) yang baru
 * masuk atau statusnya berubah, supaya halaman POS "Pesanan" (pesanan-
 * pickup.indeks) bisa update sendiri tanpa kasir harus refresh manual.
 *
 * Payload sengaja diringkas (bukan seluruh model Transaksi) — cukup buat
 * frontend memutuskan perlu muat ulang atau tidak, detail lengkapnya tetap
 * diambil lewat endpoint /pesanan-pickup/{id}/detail yang sudah ada.
 *
 * Pakai ShouldBroadcastNow (bukan ShouldBroadcast) supaya siaran dikirim
 * langsung di request yang sama, tanpa bergantung ke queue worker
 * (php artisan queue:work) yang terpisah — kalau worker itu nggak
 * jalan, event ShouldBroadcast biasa cuma numpuk di tabel `jobs` dan
 * nggak pernah terkirim ke kasir.
 */
class PesananPickupUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tipe;

    public int $transaksiId;

    public string $kodeTransaksi;

    public string $statusPesanan;

    public string $statusPembayaran;

    /**
     * @param  string  $tipe  'baru' (pesanan baru masuk) atau 'update' (status/pembayaran berubah)
     */
    public function __construct(Transaksi $transaksi, string $tipe = 'update')
    {
        $this->tipe = $tipe;
        $this->transaksiId = $transaksi->id;
        $this->kodeTransaksi = $transaksi->kode_transaksi;
        $this->statusPesanan = $transaksi->status_pesanan;
        $this->statusPembayaran = $transaksi->status_pembayaran;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pesanan-pickup'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pesanan.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'tipe' => $this->tipe,
            'transaksi_id' => $this->transaksiId,
            'kode_transaksi' => $this->kodeTransaksi,
            'status_pesanan' => $this->statusPesanan,
            'status_pembayaran' => $this->statusPembayaran,
        ];
    }
}