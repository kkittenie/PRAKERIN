<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Field-field khusus alur Pick Up dari Member App:
     * - asal_pesanan  : bedakan transaksi dari kasir (POS) vs member app.
     * - status_pesanan: Diproses -> Ready for Pickup -> Sudah Diambil (atau Dibatalkan).
     * - kode_pickup   : kode pendek yang ditunjukkan member ke kasir buat verifikasi.
     * - catatan_pesanan, siap_pada, diambil_pada: pelengkap alur & timeline status.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('asal_pesanan')->default('kasir')->after('metode_pembayaran');
            $table->string('status_pesanan')->nullable()->after('asal_pesanan');
            $table->string('kode_pickup', 10)->nullable()->unique()->after('status_pesanan');
            $table->string('catatan_pesanan', 255)->nullable()->after('kode_pickup');
            $table->timestamp('siap_pada')->nullable()->after('catatan_pesanan');
            $table->timestamp('diambil_pada')->nullable()->after('siap_pada');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn([
                'asal_pesanan',
                'status_pesanan',
                'kode_pickup',
                'catatan_pesanan',
                'siap_pada',
                'diambil_pada',
            ]);
        });
    }
};
