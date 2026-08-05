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
        if (!Schema::hasColumn('transaksi', 'poin_sebelum')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->integer('poin_sebelum')->nullable()->after('member_id');
            });
        }

        if (!Schema::hasColumn('transaksi', 'poin_didapat')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->integer('poin_didapat')->nullable()->default(0)->after('poin_sebelum');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi', 'poin_didapat')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('poin_didapat');
            });
        }
        if (Schema::hasColumn('transaksi', 'poin_sebelum')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('poin_sebelum');
            });
        }
    }
};