<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kategori_id')
                ->constrained('kategori_produks')
                ->cascadeOnDelete();

            $table->string('kode_produk')->unique();
            $table->string('nama_produk');    

            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('harga_modal', 15, 2)->default(0);

            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);

            $table->string('foto')->nullable();

            $table->boolean('status_aktif')->default(true);

            $table->enum('status_ketersediaan', [
                'stok_ada',
                'pesan_dulu'
            ])->default('stok_ada');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
