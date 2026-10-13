<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_produk_rasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained('promos')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('varian_rasa_id')->constrained('varian_rasas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_id', 'produk_id', 'varian_rasa_id'], 'ppr_promo_produk_rasa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_produk_rasas');
    }
};
