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
        if (!Schema::hasColumn('transaksi', 'diskon_label')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->text('diskon_label')->nullable()->after('diskon');
            });
        }

        if (!Schema::hasColumn('pesanan_ditahan', 'diskon_label')) {
            Schema::table('pesanan_ditahan', function (Blueprint $table) {
                $table->text('diskon_label')->nullable()->after('diskon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi', 'diskon_label')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('diskon_label');
            });
        }
        if (Schema::hasColumn('pesanan_ditahan', 'diskon_label')) {
            Schema::table('pesanan_ditahan', function (Blueprint $table) {
                $table->dropColumn('diskon_label');
            });
        }
    }
};