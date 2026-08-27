<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_produk_event_ukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_produk_event_id')->constrained('promo_produk_events')->cascadeOnDelete();
            $table->foreignId('ukuran_id')->constrained('ukurans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_produk_event_id', 'ukuran_id'], 'ppeu_event_ukuran_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_produk_event_ukurans');
    }
};