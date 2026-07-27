<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_bahan_bakus', function (Blueprint $table) {

            $table->id();

            $table->date('tanggal');

            $table->foreignId('bahan_baku_id')
                ->constrained('bahan_bakus')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('jumlah', 10, 2);

            $table->decimal('harga_satuan', 15, 2);

            $table->decimal('total_harga', 15, 2);

            $table->string('supplier')->nullable();

            $table->string('nomor_nota')->nullable();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->text('keterangan')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_bahan_bakus');
    }
};