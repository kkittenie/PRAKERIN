<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (DB::table('produks')->get() as $produk) {
            if (DB::table('produk_varians')->where('produk_id', $produk->id)->exists()) {
                continue;
            }

            $ukurans = DB::table('produk_ukurans')
                ->join('ukurans', 'ukurans.id', '=', 'produk_ukurans.ukuran_id')
                ->where('produk_ukurans.produk_id', $produk->id)
                ->orderBy('ukurans.urutan')
                ->orderBy('produk_ukurans.id')
                ->select('produk_ukurans.*', 'ukurans.nama_ukuran', 'ukurans.urutan as urutan_ukuran')
                ->get();

            $rasas = DB::table('produk_varian_rasas')
                ->join('varian_rasas', 'varian_rasas.id', '=', 'produk_varian_rasas.varian_rasa_id')
                ->where('produk_varian_rasas.produk_id', $produk->id)
                ->orderBy('produk_varian_rasas.id')
                ->select('produk_varian_rasas.*', 'varian_rasas.nama_rasa', 'varian_rasas.gambar')
                ->get();

            $baris = [];

            if ($ukurans->isNotEmpty() && $rasas->isNotEmpty()) {
                // Ukuran x rasa -> satu baris per kombinasi
                $silang = DB::table('produk_ukuran_rasas')
                    ->where('produk_id', $produk->id)
                    ->get()
                    ->keyBy(fn ($r) => $r->ukuran_id . '-' . $r->varian_rasa_id);

                foreach ($ukurans as $u) {
                    foreach ($rasas as $r) {
                        $s = $silang->get($u->ukuran_id . '-' . $r->varian_rasa_id);
                        $baris[] = [
                            'ukuran' => $u->nama_ukuran,
                            'variasi' => $r->nama_rasa,
                            'kode_barcode' => null,
                            'harga_modal' => $u->harga_modal,
                            'harga_jual' => $u->harga_jual,
                            'stok' => (int) ($s->stok ?? 0),
                            'stok_minimum' => (int) ($s->stok_minimum ?? 0),
                            'foto' => $r->gambar,
                            'status_aktif' => ($u->status_aktif && $r->status_aktif) ? 1 : 0,
                            'urutan' => (int) $u->urutan_ukuran,
                            'ukuran_id' => $u->ukuran_id,
                            'varian_rasa_id' => $r->varian_rasa_id,
                        ];
                    }
                }
            } elseif ($ukurans->isNotEmpty()) {
                // Ukuran saja -> barcode lama dipertahankan
                foreach ($ukurans as $u) {
                    $baris[] = [
                        'ukuran' => $u->nama_ukuran,
                        'variasi' => null,
                        'kode_barcode' => $u->kode_barcode,
                        'harga_modal' => $u->harga_modal,
                        'harga_jual' => $u->harga_jual,
                        'stok' => (int) $u->stok,
                        'stok_minimum' => (int) $u->stok_minimum,
                        'foto' => null,
                        'status_aktif' => $u->status_aktif ? 1 : 0,
                        'urutan' => (int) $u->urutan_ukuran,
                        'ukuran_id' => $u->ukuran_id,
                        'varian_rasa_id' => null,
                    ];
                }
            } elseif ($rasas->isNotEmpty()) {
                // Rasa saja -> harga ikut produk
                foreach ($rasas as $i => $r) {
                    $baris[] = [
                        'ukuran' => null,
                        'variasi' => $r->nama_rasa,
                        'kode_barcode' => null,
                        'harga_modal' => $produk->harga_modal ?? 0,
                        'harga_jual' => $produk->harga_jual ?? 0,
                        'stok' => (int) $r->stok,
                        'stok_minimum' => (int) $r->stok_minimum,
                        'foto' => $r->gambar,
                        'status_aktif' => $r->status_aktif ? 1 : 0,
                        'urutan' => $i,
                        'ukuran_id' => null,
                        'varian_rasa_id' => $r->varian_rasa_id,
                    ];
                }
            }

            foreach ($baris as $i => $b) {
                DB::table('produk_varians')->insert($b + [
                    'produk_id' => $produk->id,
                    'kode_barcode' => $b['kode_barcode'] ?: $produk->kode_produk . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'status_ketersediaan' => $b['stok'] > 0 ? 'stok_ada' : 'perlu_diproduksi',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('produk_varians')->delete();
    }
};