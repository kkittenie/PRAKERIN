<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perluas enum dulu supaya kedua nilai (lama & baru) valid sementara,
        //    sehingga data lama tidak ikut ter-truncate.
        DB::statement("
            ALTER TABLE produks
            MODIFY status_ketersediaan
            ENUM('stok_ada', 'pesan_dulu', 'perlu_diproduksi')
            NOT NULL DEFAULT 'stok_ada'
        ");

        // 2. Migrasikan data lama: 'pesan_dulu' -> 'perlu_diproduksi'.
        //    Sekalian pastikan produk dengan stok 0 ikut ditandai perlu diproduksi.
        DB::table('produks')
            ->where('status_ketersediaan', 'pesan_dulu')
            ->update(['status_ketersediaan' => 'perlu_diproduksi']);

        DB::table('produks')
            ->where('stok', '<=', 0)
            ->update(['status_ketersediaan' => 'perlu_diproduksi']);

        // 3. Persempit enum jadi hanya 2 nilai final.
        DB::statement("
            ALTER TABLE produks
            MODIFY status_ketersediaan
            ENUM('stok_ada', 'perlu_diproduksi')
            NOT NULL DEFAULT 'stok_ada'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perluas lagi supaya data 'perlu_diproduksi' tidak ter-truncate saat rollback.
        DB::statement("
            ALTER TABLE produks
            MODIFY status_ketersediaan
            ENUM('stok_ada', 'pesan_dulu', 'perlu_diproduksi')
            NOT NULL DEFAULT 'stok_ada'
        ");

        DB::table('produks')
            ->where('status_ketersediaan', 'perlu_diproduksi')
            ->update(['status_ketersediaan' => 'pesan_dulu']);

        DB::statement("
            ALTER TABLE produks
            MODIFY status_ketersediaan
            ENUM('stok_ada', 'pesan_dulu')
            NOT NULL DEFAULT 'stok_ada'
        ");
    }
};