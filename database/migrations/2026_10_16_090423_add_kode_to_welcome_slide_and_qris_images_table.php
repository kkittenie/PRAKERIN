<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('welcome_slides', function (Blueprint $table) {
            $table->string('kode_slide', 50)->nullable()->after('id');
        });

        Schema::table('qris_images', function (Blueprint $table) {
            $table->string('kode_qris', 50)->nullable()->after('id');
        });

        // Isi kode untuk data lama: WLS001, WLS002, ... dan QRS001, QRS002, ...
        DB::table('welcome_slides')->get()->each(function ($s) {
            DB::table('welcome_slides')->where('id', $s->id)->update([
                'kode_slide' => 'WLS' . str_pad((string) $s->id, 3, '0', STR_PAD_LEFT),
            ]);
        });

        DB::table('qris_images')->get()->each(function ($q) {
            DB::table('qris_images')->where('id', $q->id)->update([
                'kode_qris' => 'QRS' . str_pad((string) $q->id, 3, '0', STR_PAD_LEFT),
            ]);
        });

        // Catatan: file gambar lama dipindahkan ke folder per kode lewat
        // `php artisan app:migrasi-struktur-foto` (migration ini hanya mengurus kolom kode).
    }

    public function down(): void
    {
        Schema::table('welcome_slides', function (Blueprint $table) {
            $table->dropColumn('kode_slide');
        });

        Schema::table('qris_images', function (Blueprint $table) {
            $table->dropColumn('kode_qris');
        });
    }
};