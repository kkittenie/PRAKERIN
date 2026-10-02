<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pembatasan rasa per promo produk (mirip promo_produk_event_ukurans).
        // Tidak ada baris untuk sebuah promo = promo berlaku ke semua rasa.
        Schema::create('promo_produk_event_varian_rasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_produk_event_id')->constrained('promo_produk_events')->cascadeOnDelete();
            $table->foreignId('varian_rasa_id')->constrained('varian_rasas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_produk_event_id', 'varian_rasa_id'], 'ppevr_event_rasa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_produk_event_varian_rasas');
    }
};