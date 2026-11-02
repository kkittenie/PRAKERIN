<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jam operasional pemesanan Member App dipindah dari konstanta di kode ke
 * database, supaya bisa diubah dari panel admin tanpa deploy atau restart server.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengaturan_jam_operasional')) {
            Schema::create('pengaturan_jam_operasional', function (Blueprint $table) {
                $table->id();
                $table->string('jam_buka', 5)->default('06:00');
                $table->string('jam_tutup', 5)->default('20:00');
                $table->timestamps();
            });
        }

        // Isi awal sama dengan jam yang sebelumnya ditulis di kode.
        if (DB::table('pengaturan_jam_operasional')->count() === 0) {
            DB::table('pengaturan_jam_operasional')->insert([
                'jam_buka' => '06:00',
                'jam_tutup' => '20:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_jam_operasional');
    }
};