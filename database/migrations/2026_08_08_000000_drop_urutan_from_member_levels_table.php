<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            // Urutan tampilan sekarang otomatis mengikuti minimal_poin (kecil -> besar),
            // jadi field urutan manual ini sudah tidak dipakai lagi dan dihapus supaya
            // tidak ada dua sumber kebenaran (urutan vs minimal_poin) yang bisa gak sinkron.
            $table->dropColumn('urutan');
        });
    }

    public function down(): void
    {
        Schema::table('member_levels', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->default(1)->after('minimal_poin');
        });
    }
};