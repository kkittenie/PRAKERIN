<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stok per kombinasi ukuran x rasa. Dipakai kalau produk punya ukuran DAN rasa.
        Schema::create('produk_ukuran_rasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('ukuran_id')->constrained('ukurans')->cascadeOnDelete();
            $table->foreignId('varian_rasa_id')->constrained('varian_rasas')->cascadeOnDelete();
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->string('status_ketersediaan', 50)->default('perlu_diproduksi');
            $table->timestamps();

            $table->unique(['produk_id', 'ukuran_id', 'varian_rasa_id'], 'pur_produk_ukuran_rasa_unique');
        });

        // Backfill produk lama yang sudah punya rasa: stok yang sudah ada TIDAK dipecah
        // (tidak ada data untuk memecahnya), jadi seluruhnya ditaruh di rasa pertama
        // supaya total stok per ukuran tetap sama. Rasa lain mulai dari 0.
        $produkIds = DB::table('produk_varian_rasas')->distinct()->pluck('produk_id');

        foreach ($produkIds as $produkId) {
            $rasaPertama = DB::table('produk_varian_rasas')
                ->where('produk_id', $produkId)
                ->orderBy('id')
                ->first();
            $semuaRasa = DB::table('produk_varian_rasas')->where('produk_id', $produkId)->orderBy('id')->get();
            $ukurans = DB::table('produk_ukurans')->where('produk_id', $produkId)->get();
            $now = now();

            if ($ukurans->isNotEmpty()) {
                foreach ($ukurans as $pu) {
                    foreach ($semuaRasa as $rasa) {
                        $adalahPertama = $rasa->id === $rasaPertama->id;
                        $stok = $adalahPertama ? (int) $pu->stok : 0;
                        DB::table('produk_ukuran_rasas')->insert([
                            'produk_id' => $produkId,
                            'ukuran_id' => $pu->ukuran_id,
                            'varian_rasa_id' => $rasa->varian_rasa_id,
                            'stok' => $stok,
                            'stok_minimum' => $adalahPertama ? (int) $pu->stok_minimum : 0,
                            'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
                $totalPerRasa = fn ($rasa) => $rasa->id === $rasaPertama->id
                    ? (int) $ukurans->sum('stok') : 0;
                $minPerRasa = fn ($rasa) => $rasa->id === $rasaPertama->id
                    ? (int) $ukurans->sum('stok_minimum') : 0;
            } else {
                $produk = DB::table('produks')->where('id', $produkId)->first();
                $totalPerRasa = fn ($rasa) => $rasa->id === $rasaPertama->id ? (int) ($produk->stok ?? 0) : 0;
                $minPerRasa = fn ($rasa) => $rasa->id === $rasaPertama->id ? (int) ($produk->stok_minimum ?? 0) : 0;
            }

            foreach ($semuaRasa as $rasa) {
                $stok = $totalPerRasa($rasa);
                DB::table('produk_varian_rasas')->where('id', $rasa->id)->update([
                    'stok' => $stok,
                    'stok_minimum' => $minPerRasa($rasa),
                    'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_ukuran_rasas');
    }
};