<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_stamps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('stamp_program_id')->constrained('stamp_programs')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_stamp')->default(0);
            $table->timestamps();

            $table->unique(['member_id', 'stamp_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_stamps');
    }
};