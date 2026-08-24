<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_ukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('ukuran_id')->constrained('ukurans')->cascadeOnDelete();

            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);

            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);

            $table->enum('status_ketersediaan', ['stok_ada', 'perlu_diproduksi'])->default('stok_ada');
            $table->boolean('status_aktif')->default(true);

            $table->timestamps();

            $table->unique(['produk_id', 'ukuran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_ukurans');
    }
};