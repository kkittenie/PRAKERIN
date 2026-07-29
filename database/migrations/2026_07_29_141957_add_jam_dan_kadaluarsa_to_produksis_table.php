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
            $table->time('jam_produksi')->nullable()->after('tanggal');
            $table->enum('kadaluarsa_tipe', ['tanggal', 'jam'])->default('tanggal')->after('keterangan');
            $table->date('kadaluarsa_tanggal')->nullable()->after('kadaluarsa_tipe');
            $table->unsignedInteger('kadaluarsa_jam')->nullable()->after('kadaluarsa_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn(['jam_produksi', 'kadaluarsa_tipe', 'kadaluarsa_tanggal', 'kadaluarsa_jam']);
        });
    }
};
