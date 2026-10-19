<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Varian untuk produk "Stok Bersama" (global), mis. topping donat.
     * Tabel ini hanya menyimpan label (foto, kode, ukuran, variasi). Harga dan stok
     * tetap ikut detail produk utama, jadi semua varian harganya sama dan stoknya satu.
     */
    public function up(): void
    {
        Schema::create('varian_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->string('kode_produk', 60)->unique();
            $table->string('ukuran', 50)->nullable();
            $table->string('variasi', 100)->nullable();
            $table->string('foto')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('varian_produks');
    }
};