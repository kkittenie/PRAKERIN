<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produksi_ukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produksi_id')->constrained('produksis')->cascadeOnDelete();
            $table->foreignId('produk_ukuran_id')->constrained('produk_ukurans')->cascadeOnDelete();

            $table->integer('jumlah_hasil')->default(0);
            $table->integer('kebutuhan')->default(0);

            $table->timestamps();

            $table->unique(['produksi_id', 'produk_ukuran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_ukurans');
    }
};