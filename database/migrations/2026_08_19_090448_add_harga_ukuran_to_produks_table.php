<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->boolean('punya_ukuran')->default(false)->after('harga_jual');
            $table->decimal('harga_besar', 15, 2)->nullable()->after('punya_ukuran');
            $table->decimal('harga_kecil', 15, 2)->nullable()->after('harga_besar');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['punya_ukuran', 'harga_besar', 'harga_kecil']);
        });
    }
};