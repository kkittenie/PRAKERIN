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
        // Batas waktu upload bukti pembayaran untuk pesanan pickup dari Member App.
        // Terisi saat checkout (sekarang + 10 menit). Kalau sampai batas ini member
        // belum upload bukti, pesanan dibatalkan otomatis oleh sistem. Pesanan lama
        // (kolom NULL) tidak ikut dibatalkan otomatis.
        Schema::table('transaksi', function (Blueprint $table) {
            $table->timestamp('batas_bayar_pada')->nullable()->after('bukti_dikonfirmasi_pada');
            $table->index('batas_bayar_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['batas_bayar_pada']);
            $table->dropColumn('batas_bayar_pada');
        });
    }
};