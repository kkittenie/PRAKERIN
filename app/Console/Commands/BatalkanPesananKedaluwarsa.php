<?php

namespace App\Console\Commands;

use App\Models\Transaksi;
use App\Support\PembatalanPesananPickup;
use Illuminate\Console\Command;

class BatalkanPesananKedaluwarsa extends Command
{
    protected $signature = 'pesanan-pickup:batalkan-kedaluwarsa';

    protected $description = 'Batalkan otomatis pesanan pickup member yang tidak upload bukti pembayaran sampai batas waktunya (stok & voucher dikembalikan).';

    public function handle(): int
    {
        $jumlah = 0;

        Transaksi::telatBayar()
            ->orderBy('id')
            ->chunkById(50, function ($daftar) use (&$jumlah) {
                foreach ($daftar as $transaksi) {
                    if (PembatalanPesananPickup::batalkanJikaKedaluwarsa($transaksi)) {
                        $jumlah++;
                    }
                }
            });

        if ($jumlah > 0) {
            $this->info("{$jumlah} pesanan pickup dibatalkan otomatis (melebihi batas waktu pembayaran).");
        }

        return self::SUCCESS;
    }
}