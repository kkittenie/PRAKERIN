<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ukuran is a column on the global-product variant table again. Safe to run on any
     * database: the column is only added when it is missing (e.g. after a migration that
     * dropped it was run).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('varian_produks', 'ukuran')) {
            Schema::table('varian_produks', function (Blueprint $table) {
                $table->string('ukuran', 50)->nullable()->after('kode_produk');
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: the ukuran column belongs to the create_varian_produks_table migration.
    }
};