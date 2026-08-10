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
        Schema::table('penukaran_poin', function (Blueprint $table) {
            $table->date('berlaku_sampai')->nullable()->after('status');
            $table->foreignId('transaksi_id')->nullable()->after('diambil_pada')
                ->constrained('transaksi')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penukaran_poin', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaksi_id');
            $table->dropColumn('berlaku_sampai');
        });
    }
};
