<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        DB::table('produks')
            ->where('punya_ukuran', true)
            ->update([
                'stok' => DB::raw('stok_besar + stok_kecil'),
            ]);
    }

    public function down(): void
    {
        
    }
};