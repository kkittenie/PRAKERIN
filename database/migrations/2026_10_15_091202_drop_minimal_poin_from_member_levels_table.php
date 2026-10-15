<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Level member sekarang ditentukan oleh minimal_transaksi, bukan minimal_poin.
        if (Schema::hasColumn('member_levels', 'minimal_poin')) {
            Schema::table('member_levels', function (Blueprint $table) {
                $table->dropColumn('minimal_poin');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('member_levels', 'minimal_poin')) {
            Schema::table('member_levels', function (Blueprint $table) {
                // Nilai lama tidak bisa dipulihkan, jadi dikembalikan dengan default 0.
                $table->unsignedInteger('minimal_poin')->default(0)->after('minimal_transaksi');
            });
        }
    }
};