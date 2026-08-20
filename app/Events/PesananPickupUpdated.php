<?php

namespace App\Events;

use App\Models\Transaksi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesananPickupUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tipe;

    public int $transaksiId;

    public string $kodeTransaksi;

    public string $statusPesanan;

    public string $statusPembayaran;

    /**
     * Aksi spesifik yang barusan dilakukan kasir (lunas/tolak_bukti/diproses/
     * siap/batal/selesai) atau 'baru' waktu member checkout. Dipakai sama
     * App\Listeners\KirimNotifikasiMemberPesanan buat nentuin pesan notifikasi
     * apa yang perlu dikirim ke member — nggak bisa ditebak cuma dari
     * statusPesanan/statusPembayaran soalnya beberapa aksi (mis. tolak bukti)
     * nggak selalu ganti kolom status itu sendiri.
     */
    public ?string $aksi;

    /**
     * @param  string  $tipe  'baru' (pesanan baru masuk) atau 'update' (status/pembayaran berubah)
     * @param  string|null  $aksi  lihat properti $aksi di atas
     */
    public function __construct(Transaksi $transaksi, string $tipe = 'update', ?string $aksi = null)
    {
        $this->tipe = $tipe;
        $this->transaksiId = $transaksi->id;
        $this->kodeTransaksi = $transaksi->kode_transaksi;
        $this->statusPesanan = $transaksi->status_pesanan;
        $this->statusPembayaran = $transaksi->status_pembayaran;
        $this->aksi = $aksi ?? ($tipe === 'baru' ? 'baru' : null);
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