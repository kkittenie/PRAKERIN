<?php

use App\Models\Transaksi;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Kode pesanan sebelumnya angka saja (mis. "483920"). Sekarang harus campuran huruf & angka
     * (mis. "A3F9K2"), jadi semua kode lama yang murni angka diganti dengan kode baru.
     * Kode yang sudah campuran huruf & angka tidak disentuh.
     */
    public function up(): void
    {
        Transaksi::whereNotNull('kode_pickup')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if (ctype_digit((string) $row->kode_pickup)) {
                    $row->forceFill(['kode_pickup' => Transaksi::buatKodePickupUnik()])->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        // Tidak ada yang perlu dikembalikan: kode angka lama tidak disimpan.
    }
};