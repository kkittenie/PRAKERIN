<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ukuran produk Stok Bersama ikut produk utama (detail_produks), jadi varian/topping
     * cukup menyimpan foto, kode, dan nama varian. Kolom ukuran di varian_produks dibuang.
     */
    public function up(): void
    {
        if (Schema::hasColumn('varian_produks', 'ukuran')) {
            Schema::table('varian_produks', function (Blueprint $table) {
                $table->dropColumn('ukuran');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('varian_produks', 'ukuran')) {
            Schema::table('varian_produks', function (Blueprint $table) {
                $table->string('ukuran', 50)->nullable()->after('kode_produk');
            });
        }
    }
};