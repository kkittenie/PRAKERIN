<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom produk_varian_id ke bundling_items
        Schema::table('bundling_items', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_varian_id')->nullable()->after('produk_id');
            $table->foreign('produk_varian_id')->references('id')->on('produk_varians')->nullOnDelete();
        });

        // Backfill: cocokkan data lama (ukuran + varian_rasa string) ke produk_varian_id
        $items = DB::table('bundling_items')
            ->whereNull('produk_varian_id')
            ->whereNotNull('produk_id')
            ->get();

        foreach ($items as $item) {
            $query = DB::table('produk_varians')
                ->where('produk_id', $item->produk_id);

            // Match ukuran
            if ($item->ukuran) {
                $query->where('ukuran', $item->ukuran);
            } else {
                $query->whereNull('ukuran');
            }

            // Match variasi (was varian_rasa in old system)
            if ($item->varian_rasa) {
                $query->where('variasi', $item->varian_rasa);
            } else {
                $query->whereNull('variasi');
            }

            $varian = $query->first();

            // Jika match exact tidak ketemu, coba cari partial match
            if (!$varian && ($item->ukuran || $item->varian_rasa)) {
                $fallbackQuery = DB::table('produk_varians')
                    ->where('produk_id', $item->produk_id);

                if ($item->ukuran) {
                    $fallbackQuery->where('ukuran', $item->ukuran);
                }
                if ($item->varian_rasa) {
                    $fallbackQuery->where('variasi', $item->varian_rasa);
                }

                $varian = $fallbackQuery->first();
            }

            // Jika masih tidak ketemu dan produk hanya punya 1 varian, pakai itu
            if (!$varian) {
                $allVarians = DB::table('produk_varians')
                    ->where('produk_id', $item->produk_id)
                    ->get();
                if ($allVarians->count() === 1) {
                    $varian = $allVarians->first();
                }
            }

            if ($varian) {
                DB::table('bundling_items')
                    ->where('id', $item->id)
                    ->update(['produk_varian_id' => $varian->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('bundling_items', function (Blueprint $table) {
            $table->dropForeign(['produk_varian_id']);
            $table->dropColumn('produk_varian_id');
        });
    }
};
