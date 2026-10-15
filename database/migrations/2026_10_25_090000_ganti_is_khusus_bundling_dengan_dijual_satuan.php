<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menu "Produk Khusus Bundling" dihapus. Penggantinya: tiap produk punya pilihan
 * dijual_satuan. Produk khusus bundling lama menjadi produk dengan dijual_satuan = false.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->boolean('dijual_satuan')->default(true)->after('status_aktif');
        });

        DB::table('produks')->where('is_khusus_bundling', true)->update(['dijual_satuan' => false]);

        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('is_khusus_bundling');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->boolean('is_khusus_bundling')->default(false)->after('status_ketersediaan');
        });

        DB::table('produks')->where('dijual_satuan', false)->update(['is_khusus_bundling' => true]);

        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('dijual_satuan');
        });
    }
};