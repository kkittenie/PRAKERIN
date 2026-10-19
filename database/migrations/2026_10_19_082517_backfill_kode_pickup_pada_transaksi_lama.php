<?php

use App\Models\Transaksi;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Kode pesanan 6 karakter sekarang dipakai semua transaksi (termasuk kasir/offline).
     * Transaksi lama yang belum punya kode diberi kode baru supaya kolomnya tidak kosong.
     */
    public function up(): void
    {
        Transaksi::whereNull('kode_pickup')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $row->forceFill(['kode_pickup' => Transaksi::buatKodePickupUnik()])->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Tidak ada yang perlu dikembalikan: kode lama tidak bisa dibedakan dari kode baru.
    }
};