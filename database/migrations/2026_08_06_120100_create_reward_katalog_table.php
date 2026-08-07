<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_katalog', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['voucher', 'produk'])->default('voucher');
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->unsignedInteger('poin_dibutuhkan');
            $table->unsignedInteger('stok')->nullable(); // null = tidak terbatas
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_katalog');
    }
};
