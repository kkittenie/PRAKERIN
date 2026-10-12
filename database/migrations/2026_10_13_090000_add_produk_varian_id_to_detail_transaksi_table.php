<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // Menunjuk ke baris varian yang stoknya dikurangi saat transaksi,
            // supaya stok bisa dikembalikan dengan tepat (tahan/hapus pesanan).
            $table->unsignedBigInteger('produk_varian_id')->nullable()->after('produk_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropIndex(['produk_varian_id']);
            $table->dropColumn('produk_varian_id');
        });
    }
};