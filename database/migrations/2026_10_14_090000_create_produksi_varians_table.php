<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rincian hasil produksi per varian (tabel produk_varians). Satu baris = satu varian
        // (kombinasi ukuran + variasi). Nama, barcode, dan harga disalin supaya riwayat produksi
        // tetap terbaca walau varian-nya nanti diubah atau dihapus dari form produk.
        Schema::create('produksi_varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->cascadeOnDelete();
            $table->foreignId('produk_varian_id')->nullable()->constrained('produk_varians')->nullOnDelete();

            $table->string('ukuran', 50)->nullable();
            $table->string('variasi', 100)->nullable();
            $table->string('kode_barcode', 60)->nullable();
            $table->decimal('harga_jual', 15, 2)->default(0);

            $table->integer('jumlah_hasil')->default(0);
            $table->integer('kebutuhan')->default(0);

            $table->timestamps();

            $table->index('produksi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_varians');
    }
};