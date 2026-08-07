<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penukaran_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('reward_katalog_id')->constrained('reward_katalog')->cascadeOnDelete();
            $table->string('kode_redeem', 12)->unique();
            $table->unsignedInteger('poin_terpakai');
            $table->enum('status', ['menunggu_diambil', 'selesai', 'dibatalkan'])->default('menunggu_diambil');
            $table->timestamp('diambil_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penukaran_poin');
    }
};
