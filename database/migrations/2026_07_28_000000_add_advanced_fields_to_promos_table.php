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
        Schema::table('promos', function (Blueprint $table) {
            $table->enum('jenis_potongan', ['persentase', 'nominal'])->default('persentase')->after('nama_promo');
            $table->decimal('persentase', 5, 2)->nullable()->default(0)->change();
            $table->decimal('nominal_potongan', 15, 2)->default(0)->after('persentase');
            $table->decimal('min_pembelian', 15, 2)->default(0)->after('nominal_potongan');
            $table->boolean('status_aktif')->default(true)->after('min_pembelian');
            $table->dateTime('selesai_berlaku')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn(['jenis_potongan', 'nominal_potongan', 'min_pembelian', 'status_aktif']);
            $table->decimal('persentase', 5, 2)->default(0)->change();
            $table->dateTime('selesai_berlaku')->nullable(false)->change();
        });
    }
};
