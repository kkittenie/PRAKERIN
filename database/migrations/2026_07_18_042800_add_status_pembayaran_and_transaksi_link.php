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
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('Lunas')->after('metode_pembayaran');
        });

        Schema::table('pesanan_ditahan', function (Blueprint $table) {
            $table->unsignedBigInteger('transaksi_id')->nullable()->after('id');
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_ditahan', function (Blueprint $table) {
            $table->dropForeign(['transaksi_id']);
            $table->dropColumn('transaksi_id');
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });
    }
};
