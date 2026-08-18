<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('konten_statis', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // 'help-center', 'terms-conditions', 'privacy-policy'
            $table->string('judul');
            $table->longText('konten'); // HTML content (rich text)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konten_statis');
    }
};
