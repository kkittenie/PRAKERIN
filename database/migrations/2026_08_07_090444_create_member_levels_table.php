<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_levels', function (Blueprint $table) {
            $table->id();
            $table->string('nama_level'); // Bronze, Silver, Gold, dst (bebas nambah)
            $table->unsignedInteger('minimal_poin'); // ambang batas poin buat capai level ini
            $table->unsignedInteger('urutan'); // urutan tingkatan, 1 = paling rendah
            $table->string('warna_badge', 20)->nullable(); // buat badge di UI, mis. 'amber', 'gray', 'yellow'
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_levels');
    }
};