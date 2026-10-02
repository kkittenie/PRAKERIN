<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rincian hasil produksi per rasa. Satu baris = satu kombinasi ukuran x rasa
        // (produk_ukuran_id NULL untuk produk tanpa ukuran). nama_rasa disimpan sebagai
        // salinan supaya riwayat produksi tetap terbaca walau rasanya dihapus dari master.
        Schema::create('produksi_rasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->cascadeOnDelete();
            $table->foreignId('produk_ukuran_id')->nullable()->constrained('produk_ukurans')->nullOnDelete();
            $table->foreignId('varian_rasa_id')->nullable()->constrained('varian_rasas')->nullOnDelete();
            $table->string('nama_rasa', 100);
            $table->integer('jumlah_hasil')->default(0);
            $table->integer('kebutuhan')->default(0);
            $table->timestamps();

            $table->index(['produksi_id', 'produk_ukuran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_rasas');
    }
};