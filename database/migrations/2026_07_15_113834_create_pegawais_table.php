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
        Schema::create('pegawais', function (Blueprint $table) {

            $table->id();

            $table->string('nama');
            $table->string('no_hp',20);
            $table->text('alamat');

            $table->enum('jabatan',[
                'Admin',
                'Kasir',
                'Pegawai'
            ]);

            $table->date('tanggal_masuk');

            $table->boolean('status_aktif')
                  ->default(true);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
