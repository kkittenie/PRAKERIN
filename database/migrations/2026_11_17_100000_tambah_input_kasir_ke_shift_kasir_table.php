<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Total penjualan yang diinput kasir sendiri saat menyelesaikan shift (tunai, QRIS, debit, Member App).
 * Disimpan apa adanya sebagai arsip. Rekap Shift tetap memakai total sebenarnya dari sistem
 * (total_tunai, total_qris, total_debit, total_lainnya), bukan angka input ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_kasir', function (Blueprint $table) {
            foreach (['input_tunai', 'input_qris', 'input_debit', 'input_member_app'] as $kolom) {
                if (! Schema::hasColumn('shift_kasir', $kolom)) {
                    $table->decimal($kolom, 15, 2)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_kasir', function (Blueprint $table) {
            foreach (['input_tunai', 'input_qris', 'input_debit', 'input_member_app'] as $kolom) {
                if (Schema::hasColumn('shift_kasir', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};