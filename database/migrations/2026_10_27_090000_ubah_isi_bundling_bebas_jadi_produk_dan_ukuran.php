<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Isi bundling "bebas" sekarang = produk + ukuran (bukan lagi produk + varian).
 * Admin menentukan produk dan ukurannya, sedangkan rasa dipilih sendiri oleh pembeli dari
 * semua rasa aktif untuk produk + ukuran itu.
 *
 * Migration sebelumnya memperluas isi bundling bebas jadi satu baris per varian (produk + ukuran + rasa).
 * Di sini baris-baris itu digabung kembali jadi satu baris per (produk, ukuran):
 *   - detail_produk_id dan varian_rasa dikosongkan (rasa ditentukan pembeli),
 *   - qty = 1 (tidak dipakai pada bundling bebas),
 *   - baris kembar dihapus.
 *
 * Catatan: rasa yang dulu sengaja tidak dimasukkan admin ke daftar pilihan sekarang ikut bisa dipilih
 * pembeli, karena daftar rasa mengikuti semua rasa aktif produk + ukuran itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $bebasIds = DB::table('bundlings')->where('jenis_produk', 'bebas')->pluck('id');

        foreach ($bebasIds as $bundlingId) {
            $items = DB::table('bundling_items')
                ->where('bundling_id', $bundlingId)
                ->orderBy('id')
                ->get();

            $detailMap = DB::table('detail_produks')
                ->whereIn('id', $items->pluck('detail_produk_id')->filter()->all())
                ->get()
                ->keyBy('id');

            $dipakai = [];

            foreach ($items as $item) {
                $ukuran = $item->ukuran;
                if (($ukuran === null || trim((string) $ukuran) === '') && $item->detail_produk_id && $detailMap->has($item->detail_produk_id)) {
                    $ukuran = $detailMap[$item->detail_produk_id]->ukuran;
                }
                $ukuran = trim((string) $ukuran) === '' ? null : trim((string) $ukuran);

                $kunci = $item->produk_id . '|' . mb_strtolower((string) $ukuran);

                if (isset($dipakai[$kunci])) {
                    DB::table('bundling_items')->where('id', $item->id)->delete();
                    continue;
                }

                $dipakai[$kunci] = true;

                DB::table('bundling_items')->where('id', $item->id)->update([
                    'detail_produk_id' => null,
                    'ukuran' => $ukuran,
                    'varian_rasa' => null,
                    'qty' => 1,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Kembalikan ke satu baris per varian aktif (produk + ukuran + rasa), sama seperti
        // hasil migration 2026_10_26_090000.
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
                    ->where(function ($q) use ($item) {
                        $item->ukuran === null
                            ? $q->whereNull('ukuran')->orWhere('ukuran', '')
                            : $q->where('ukuran', $item->ukuran);
                    })
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->get();

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
};