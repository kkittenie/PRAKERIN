<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Produk Tunggal (Stok Bersama): stok tetap satu di detail produk utama,
     * tetapi harga jual & harga modal sekarang bisa berbeda untuk tiap varian.
     * Varian lama diisi dengan harga detail produk utamanya, jadi harga yang berlaku tidak berubah.
     */
    public function up(): void
    {
        Schema::table('varian_produks', function (Blueprint $table) {
            $table->decimal('harga_modal', 15, 2)->nullable()->after('foto');
            $table->decimal('harga_jual', 15, 2)->nullable()->after('harga_modal');
        });

        DB::table('varian_produks')->orderBy('id')->get()->each(function ($v) {
            $utama = DB::table('detail_produks')
                ->where('produk_id', $v->produk_id)
                ->orderBy('urutan')
                ->orderBy('id')
                ->first();

            if ($utama) {
                DB::table('varian_produks')->where('id', $v->id)->update([
                    'harga_modal' => $utama->harga_modal,
                    'harga_jual' => $utama->harga_jual,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('varian_produks', function (Blueprint $table) {
            $table->dropColumn(['harga_modal', 'harga_jual']);
        });
    }
};