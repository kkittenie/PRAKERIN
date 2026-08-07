<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_program_hadiah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_program_id')->constrained('stamp_programs')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['stamp_program_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_program_hadiah');
    }
};