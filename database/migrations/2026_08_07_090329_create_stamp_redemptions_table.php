<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('stamp_program_id')->constrained('stamp_programs')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete(); // hadiah yang dipilih
            $table->string('kode_redeem', 12)->unique();
            $table->enum('sumber', ['member', 'kasir'])->default('member');
            $table->enum('status', ['menunggu_diambil', 'selesai', 'dibatalkan'])->default('menunggu_diambil');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete(); // kasir yg proses
            $table->timestamp('diambil_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_redemptions');
    }
};