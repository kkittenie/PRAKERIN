<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->unsignedInteger('kebutuhan_besar')->default(0)->after('kebutuhan_produksi');
            $table->unsignedInteger('kebutuhan_kecil')->default(0)->after('kebutuhan_besar');
        });
    }

    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn(['kebutuhan_besar', 'kebutuhan_kecil']);
        });
    }
};
