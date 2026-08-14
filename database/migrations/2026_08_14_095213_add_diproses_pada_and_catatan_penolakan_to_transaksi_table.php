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
        Schema::table('transaksi', function (Blueprint $table) {
            $table->timestamp('diproses_pada')->nullable()->after('status_pesanan');
            $table->string('catatan_penolakan')->nullable()->after('bukti_dikonfirmasi_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['diproses_pada', 'catatan_penolakan']);
        });
    }
};
