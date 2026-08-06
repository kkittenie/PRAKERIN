<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('nama_reward');
            $table->string('tipe_reward')->default('produk'); // produk_gratis | produk_diskon
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->unsignedInteger('poin_dibutuhkan');
            $table->unsignedInteger('stok_reward')->default(0);
            $table->unsignedInteger('maksimal_redeem')->nullable(); // null = tidak terbatas
            $table->unsignedInteger('jumlah_redeem')->default(0); // counter total sudah ditukar
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};