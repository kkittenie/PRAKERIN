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
            $table->dropColumn('stok_disesuaikan');
            $table->unsignedInteger('jumlah_disesuaikan')->default(0)->after('kadaluarsa_jam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn('jumlah_disesuaikan');
            $table->boolean('stok_disesuaikan')->default(false)->after('kadaluarsa_jam');
        });
    }
};
