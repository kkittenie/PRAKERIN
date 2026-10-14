<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kode ada di detail produk, bukan di produk. Produk baru dari form produk
        // tidak punya kode_produk (NULL). Kolomnya sendiri masih dipakai produk
        // khusus bundling, jadi belum dibuang. Indeks unique tetap, dan MySQL
        // mengizinkan banyak NULL di kolom unique.
        Schema::table('produks', function (Blueprint $table) {
            $table->string('kode_produk')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak dibalik: produk tanpa kode tidak bisa dikembalikan ke NOT NULL.
    }
};