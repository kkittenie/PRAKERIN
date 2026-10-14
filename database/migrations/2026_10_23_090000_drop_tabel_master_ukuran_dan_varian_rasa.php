<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Membuang tabel master lama (ukurans, varian_rasas) beserta tabel turunannya
 * (produk_ukurans, produk_varian_rasas, produk_ukuran_rasas), dan kolom jembatan
 * ukuran_id / varian_rasa_id di detail_produks. Ukuran dan variasi sekarang hanya
 * teks di detail_produks. Promo sudah pindah ke detail_produks di migration
 * 2026_10_22_090000.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Tabel turunan dulu, baru master.
        Schema::dropIfExists('produk_ukuran_rasas');
        Schema::dropIfExists('produk_varian_rasas');
        Schema::dropIfExists('produk_ukurans');
        Schema::dropIfExists('ukurans');
        Schema::dropIfExists('varian_rasas');

        Schema::enableForeignKeyConstraints();

        foreach (['ukuran_id', 'varian_rasa_id'] as $kolom) {
            if (Schema::hasColumn('detail_produks', $kolom)) {
                Schema::table('detail_produks', function ($table) use ($kolom) {
                    $table->dropColumn($kolom);
                });
            }
        }
    }

    public function down(): void
    {
        // Tidak dibuat ulang: datanya sudah tidak dipakai (ukuran dan variasi ada di detail_produks).
    }
};