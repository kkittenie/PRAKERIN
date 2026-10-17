<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bundling punya kategori (kategori_produks) supaya di kasir muncul saat kategori itu dipilih.
     * Nullable: bundling lama belum punya kategori sampai diedit.
     */
    public function up(): void
    {
        Schema::table('bundlings', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->nullable()
                ->after('nama_bundling')
                ->constrained('kategori_produks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bundlings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_id');
        });
    }
};