<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat dua tabel ini sudah disalin ke produksi_varians oleh migration
        // 2026_10_18_090100_backfill_produksi_varians_dari_riwayat_lama.
        // Urutan: produksi_rasas dulu karena bisa menunjuk ke tabel ukuran.
        Schema::dropIfExists('produksi_rasas');
        Schema::dropIfExists('produksi_ukurans');
    }

    public function down(): void
    {
        // Tidak dibalik: datanya sudah ada di produksi_varians.
    }
};