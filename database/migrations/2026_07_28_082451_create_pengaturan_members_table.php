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
        Schema::create('pengaturan_members', function (Blueprint $table) {
            $table->id();

            //apakah sistem masih aktif atau tidak
            $table->boolean('aktif')->default(true);

            //jumlah belanja minimal
            $table->integer('minimal_belanja');

            //poin yang didapatkan
            $table->integer('poin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_members');
    }
};
