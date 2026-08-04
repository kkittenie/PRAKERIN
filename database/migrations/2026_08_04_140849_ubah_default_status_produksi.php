<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->string('status')->default('belum kadaluwarsa')->change();
        });

        // Backfill: record lama yang masih berstatus 'aktif' diubah jadi 'belum kadaluwarsa'
        DB::table('produksis')
            ->where('status', 'aktif')
            ->update(['status' => 'belum kadaluwarsa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->string('status')->default('aktif')->change();
        });

        DB::table('produksis')
            ->where('status', 'belum kadaluwarsa')
            ->update(['status' => 'aktif']);
    }
};