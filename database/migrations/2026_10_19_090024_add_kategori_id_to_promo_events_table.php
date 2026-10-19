<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promo produk (event) punya kategori (kategori_produks), sama seperti bundling, supaya di kasir
     * dan di menu mobile muncul di dalam kategori itu (bukan lagi lewat filter "Promo Event" sendiri).
     * Nullable di database; di form wajib diisi. Promo lama diisi otomatis dari kategori produknya.
     */
    public function up(): void
    {
        Schema::table('promo_produk_events', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->nullable()
                ->after('produk_id')
                ->constrained('kategori_produks')
                ->nullOnDelete();
        });

        DB::table('promo_produk_events')
            ->whereNull('kategori_id')
            ->update([
                'kategori_id' => DB::raw('(select produks.kategori_id from produks where produks.id = promo_produk_events.produk_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('promo_produk_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_id');
        });
    }
};