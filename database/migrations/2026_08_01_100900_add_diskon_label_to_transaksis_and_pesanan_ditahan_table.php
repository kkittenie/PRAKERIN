<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->text('diskon_label')->nullable()->after('diskon');
        });

        Schema::table('pesanan_ditahan', function (Blueprint $table) {
            $table->text('diskon_label')->nullable()->after('diskon');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('diskon_label');
        });
        Schema::table('pesanan_ditahan', function (Blueprint $table) {
            $table->dropColumn('diskon_label');
        });
    }
};
