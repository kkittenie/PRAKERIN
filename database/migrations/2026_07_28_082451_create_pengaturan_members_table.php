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
        Schema::create('pengaturan_member', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aturan');
            $table->bigInteger('minimal_belanja');
            $table->integer('poin');
            $table->timestamp('mulai_berlaku');
            $table->timestamp('selesai_berlaku')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_member');
    }
};
