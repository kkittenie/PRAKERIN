<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            // Nama varian rasa (mis. "Coklat") yang dipilih buat produk ini di
            // dalam paket bundling, sama seperti kolom ukuran. Null kalau
            // produknya tidak punya varian rasa (atau bundling khusus).
            $table->string('varian_rasa', 100)->nullable()->after('ukuran');
        });
    }

    public function down(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            $table->dropColumn('varian_rasa');
        });
    }
};