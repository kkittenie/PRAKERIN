<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            $table->decimal('minimal_transaksi', 15, 2)->default(0)->after('nama_level');
            $table->unsignedInteger('minimal_poin')->nullable()->change();
        });

        // Inisialisasi nilai default minimal_transaksi berdasarkan level yang ada
        if (Schema::hasTable('member_levels')) {
            DB::table('member_levels')->where('nama_level', 'Bronze')->update(['minimal_transaksi' => 50000]);
            DB::table('member_levels')->where('nama_level', 'Silver')->update(['minimal_transaksi' => 150000]);
            DB::table('member_levels')->where('nama_level', 'Gold')->update(['minimal_transaksi' => 500000]);
        }
    }

    public function down(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            $table->dropColumn('minimal_transaksi');
            $table->unsignedInteger('minimal_poin')->nullable(false)->change();
        });
    }
};
