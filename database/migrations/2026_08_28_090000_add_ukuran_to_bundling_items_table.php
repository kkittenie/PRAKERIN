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
        Schema::table('bundling_items', function (Blueprint $table) {
            // Nama ukuran (mis. "Besar"/"Kecil"/ukuran custom lain) yang dipilih
            // buat produk ini di dalam paket bundling. Null kalau produknya
            // gak punya varian ukuran (punya_ukuran = false).
            $table->string('ukuran', 50)->nullable()->after('produk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            $table->dropColumn('ukuran');
        });
    }
};