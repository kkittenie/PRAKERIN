<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            // Khusus bundling bebas: daftar id detail produk (rasa) yang boleh dipilih pembeli pada baris ini.
            // NULL = semua rasa aktif untuk produk + ukuran baris ini (perilaku lama).
            $table->json('rasa_diizinkan')->nullable()->after('varian_rasa');
        });
    }

    public function down(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            $table->dropColumn('rasa_diizinkan');
        });
    }
};