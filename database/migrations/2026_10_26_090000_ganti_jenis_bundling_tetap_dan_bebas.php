<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis bundling diganti:
 *   biasa           -> tetap  (isi paket ditentukan admin, pembeli tidak memilih)
 *   khusus_bundling -> bebas  (admin menentukan daftar produk + varian yang boleh dipilih,
 *                              pembeli memilih sendiri sejumlah total_qty_khusus)
 *
 * Isi bundling "bebas" lama hanya menyimpan produk (tanpa varian). Sekarang tiap baris
 * isi bundling bebas = satu varian (detail_produk) yang boleh dipilih pembeli, jadi baris
 * lama tanpa varian diperluas menjadi satu baris per varian aktif produk itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('bundlings')->where('jenis_produk', 'biasa')->update(['jenis_produk' => 'tetap']);
        DB::table('bundlings')->where('jenis_produk', 'khusus_bundling')->update(['jenis_produk' => 'bebas']);

        Schema::table('bundlings', function (Blueprint $table) {
            $table->string('jenis_produk', 20)->default('tetap')->change();
        });

        $bebasIds = DB::table('bundlings')->where('jenis_produk', 'bebas')->pluck('id');

        foreach ($bebasIds as $bundlingId) {
            $items = DB::table('bundling_items')
                ->where('bundling_id', $bundlingId)
                ->whereNull('detail_produk_id')
                ->get();

            foreach ($items as $item) {
                $detail = DB::table('detail_produks')
                    ->where('produk_id', $item->produk_id)
                    ->where('status_aktif', true)
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->get();

                if ($detail->isEmpty()) {
                    continue;
                }

                // Baris asli dipakai untuk varian pertama, sisanya baris baru.
                foreach ($detail as $i => $d) {
                    $data = [
                        'detail_produk_id' => $d->id,
                        'ukuran' => $d->ukuran,
                        'varian_rasa' => $d->variasi,
                        'qty' => 1,
                        'updated_at' => now(),
                    ];

                    if ($i === 0) {
                        DB::table('bundling_items')->where('id', $item->id)->update($data);
                    } else {
                        DB::table('bundling_items')->insert($data + [
                            'bundling_id' => $bundlingId,
                            'produk_id' => $item->produk_id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('bundlings')->where('jenis_produk', 'tetap')->update(['jenis_produk' => 'biasa']);
        DB::table('bundlings')->where('jenis_produk', 'bebas')->update(['jenis_produk' => 'khusus_bundling']);

        Schema::table('bundlings', function (Blueprint $table) {
            $table->string('jenis_produk', 20)->default('biasa')->change();
        });
    }
};