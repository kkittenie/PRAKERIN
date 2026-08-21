<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->integer('stok_besar')->default(0)->after('harga_kecil');
            $table->integer('stok_kecil')->default(0)->after('stok_besar');
        });

        DB::table('produks')
            ->where('punya_ukuran', true)
            ->update([
                'stok_besar' => DB::raw('stok'),
                'stok_kecil' => 0,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['stok_besar', 'stok_kecil']);
        });
    }
};