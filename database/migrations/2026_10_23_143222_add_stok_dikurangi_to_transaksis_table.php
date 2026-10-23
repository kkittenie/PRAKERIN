<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda kapan stok pesanan member app dikurangi (saat checkout).
     * Pesanan lama (null) tetap memakai alur lama: stok dikurangi saat diproses.
     * Nama tabelnya 'transaksi' (singular), sesuai $table di model Transaksi.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'stok_dikurangi_pada')) {
                $table->timestamp('stok_dikurangi_pada')->nullable()->after('diproses_pada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'stok_dikurangi_pada')) {
                $table->dropColumn('stok_dikurangi_pada');
            }
        });
    }
};