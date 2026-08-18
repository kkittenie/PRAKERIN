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
        Schema::table('produksis', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_besar')->default(0)->after('jumlah_hasil');
            $table->unsignedInteger('jumlah_kecil')->default(0)->after('jumlah_besar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn(['jumlah_besar', 'jumlah_kecil']);
        });
    }
};
