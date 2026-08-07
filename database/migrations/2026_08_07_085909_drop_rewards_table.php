<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('rewards');
    }

    public function down(): void
    {
        // Struktur lama "Reward Produk" — dipertahankan di sini cuma buat jaga-jaga
        // kalau migration ini di-rollback. Fitur ini sudah digantikan Stamp Program.
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('nama_reward');
            $table->string('tipe_reward')->default('produk');
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->unsignedInteger('poin_dibutuhkan');
            $table->unsignedInteger('stok_reward')->default(0);
            $table->unsignedInteger('maksimal_redeem')->nullable();
            $table->unsignedInteger('jumlah_redeem')->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }
};