<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salinan kode detail produk saat transaksi, supaya riwayat tetap terbaca
        // walau detail produknya nanti diubah atau dihapus.
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->string('kode_barcode', 60)->nullable()->after('varian_rasa');
        });

        // Isi untuk transaksi lama yang masih punya detail produknya.
        DB::statement(
            'UPDATE detail_transaksi dt
             JOIN detail_produks dp ON dp.id = dt.produk_varian_id
             SET dt.kode_barcode = dp.kode_barcode
             WHERE dt.kode_barcode IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn('kode_barcode');
        });
    }
};