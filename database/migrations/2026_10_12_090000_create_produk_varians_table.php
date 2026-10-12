<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();

            // Diketik bebas di form produk (bukan dari master)
            $table->string('ukuran', 50)->nullable();
            $table->string('variasi', 100)->nullable();
            $table->string('kode_barcode', 60)->nullable()->index();

            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->string('foto')->nullable();

            $table->string('status_ketersediaan', 50)->default('perlu_diproduksi');
            $table->boolean('status_aktif')->default(true);
            $table->unsignedInteger('urutan')->default(0);

            // SEMENTARA: jembatan ke data lama (dipakai langkah 4, dibuang di langkah 5)
            $table->unsignedBigInteger('ukuran_id')->nullable();
            $table->unsignedBigInteger('varian_rasa_id')->nullable();

            $table->timestamps();

            $table->index(['produk_id', 'ukuran', 'variasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_varians');
    }
};