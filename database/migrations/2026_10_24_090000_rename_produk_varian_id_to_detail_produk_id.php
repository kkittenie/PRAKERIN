<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan nama kolom penunjuk ke detail_produks: produk_varian_id -> detail_produk_id
 * di detail_transaksi, bundling_items, dan produksi_varians. Foreign key dan index ikut
 * berpindah otomatis ke nama kolom baru.
 */
return new class extends Migration
{
    private array $tabel = ['detail_transaksi', 'bundling_items', 'produksi_varians'];

    public function up(): void
    {
        foreach ($this->tabel as $nama) {
            if (Schema::hasColumn($nama, 'produk_varian_id') && !Schema::hasColumn($nama, 'detail_produk_id')) {
                Schema::table($nama, function ($table) {
                    $table->renameColumn('produk_varian_id', 'detail_produk_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $nama) {
            if (Schema::hasColumn($nama, 'detail_produk_id') && !Schema::hasColumn($nama, 'produk_varian_id')) {
                Schema::table($nama, function ($table) {
                    $table->renameColumn('detail_produk_id', 'produk_varian_id');
                });
            }
        }
    }
};