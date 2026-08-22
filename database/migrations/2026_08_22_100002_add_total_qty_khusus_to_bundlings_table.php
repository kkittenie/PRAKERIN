<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bundlings', function (Blueprint $table) {
            $table->integer('total_qty_khusus')->nullable()->after('jenis_produk');
        });
    }

    public function down(): void
    {
        Schema::table('bundlings', function (Blueprint $table) {
            $table->dropColumn('total_qty_khusus');
        });
    }
};
