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
        Schema::create('pesanan_ditahan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pembeli')->default('Pelanggan Umum');
            $table->string('email_pembeli')->nullable();
            $table->json('item_pesanan'); // Menyimpan array produk, qty, dll.
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_ditahan');
    }
};
