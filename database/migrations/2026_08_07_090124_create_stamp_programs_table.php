<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('minimal_pembelian'); // Rp per 1 stamp
            $table->unsignedInteger('jumlah_stamp_dibutuhkan'); // target stamp sampai penuh
            $table->unsignedInteger('maksimal_redeem')->nullable(); // null = tidak terbatas
            $table->unsignedInteger('jumlah_redeem')->default(0);
            $table->date('tanggal_mulai')->nullable(); // cocok buat program musiman/event
            $table->date('tanggal_berakhir')->nullable();
            $table->boolean('status_aktif')->default(false); // sengaja default false, admin aktifkan manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_programs');
    }
};