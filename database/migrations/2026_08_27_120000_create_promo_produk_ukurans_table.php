<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganti dari promo_ukurans (global, 1 checklist buat semua produk
        // dalam 1 promo) jadi per-produk: (promo_id, produk_id, ukuran_id).
        // Jadi promo yang sama bisa dibatasi ke ukuran berbeda-beda per
        // produknya masing-masing.
        Schema::create('promo_produk_ukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('ukuran_id')->constrained('ukurans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_id', 'produk_id', 'ukuran_id'], 'ppu_promo_produk_ukuran_unique');
        });

        // Tabel lama gak dipakai lagi (belum ada data produksi yang
        // bergantung ke sini, jadi aman dihapus).
        Schema::dropIfExists('promo_ukurans');
    }

    public function down(): void
    {
        Schema::create('promo_ukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->cascadeOnDelete();
            $table->foreignId('ukuran_id')->constrained('ukurans')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['promo_id', 'ukuran_id']);
        });

        Schema::dropIfExists('promo_produk_ukurans');
    }
};