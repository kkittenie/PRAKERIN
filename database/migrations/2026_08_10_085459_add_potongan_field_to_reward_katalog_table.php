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
        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->enum('jenis_potongan', ['nominal', 'persentase'])->default('nominal')->after('jenis');
            $table->decimal('nominal_potongan', 15, 2)->default(0)->after('jenis_potongan');
            $table->decimal('persentase', 5, 2)->default(0)->after('nominal_potongan');
            $table->decimal('maksimal_potongan', 15, 2)->nullable()->after('persentase');
            $table->decimal('min_pembelian', 15, 2)->default(0)->after('maksimal_potongan');
            $table->unsignedInteger('masa_berlaku_hari')->nullable()->after('min_pembelian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_potongan',
                'nominal_potongan',
                'persentase',
                'maksimal_potongan',
                'min_pembelian',
                'masa_berlaku_hari',
            ]);
        });
    }
};