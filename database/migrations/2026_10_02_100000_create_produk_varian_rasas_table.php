<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_varian_rasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('varian_rasa_id')->constrained('varian_rasas')->cascadeOnDelete();
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->string('status_ketersediaan', 50)->default('stok_ada');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'punya_varian_rasa')) {
                $table->boolean('punya_varian_rasa')->default(false)->after('punya_ukuran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'punya_varian_rasa')) {
                $table->dropColumn('punya_varian_rasa');
            }
        });

        Schema::dropIfExists('produk_varian_rasas');
    }
};
