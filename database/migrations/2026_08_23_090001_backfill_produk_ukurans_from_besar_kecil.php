<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan master Ukuran "Kecil" dan "Besar" ada, karena data lama
        //    di kolom harga_besar/harga_kecil/stok_besar/stok_kecil perlu
        //    "rumah" buat dipindahin. Kalau adminnya udah sempat bikin manual
        //    duluan lewat menu Ukuran, pakai yang itu (gak bikin duplikat).
        $ukuranKecilId = DB::table('ukurans')->where('nama_ukuran', 'Kecil')->value('id');
        if (!$ukuranKecilId) {
            $ukuranKecilId = DB::table('ukurans')->insertGetId([
                'nama_ukuran' => 'Kecil',
                'urutan' => 1,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $ukuranBesarId = DB::table('ukurans')->where('nama_ukuran', 'Besar')->value('id');
        if (!$ukuranBesarId) {
            $ukuranBesarId = DB::table('ukurans')->insertGetId([
                'nama_ukuran' => 'Besar',
                'urutan' => 2,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Salin data produk yang punya_ukuran = true ke produk_ukurans.
        $produkList = DB::table('produks')->where('punya_ukuran', true)->get();

        foreach ($produkList as $produk) {
            $sudahAdaKecil = DB::table('produk_ukurans')
                ->where('produk_id', $produk->id)
                ->where('ukuran_id', $ukuranKecilId)
                ->exists();

            if (!$sudahAdaKecil) {
                DB::table('produk_ukurans')->insert([
                    'produk_id' => $produk->id,
                    'ukuran_id' => $ukuranKecilId,
                    'harga_modal' => $produk->harga_modal ?? 0,
                    'harga_jual' => $produk->harga_kecil ?? 0,
                    'stok' => $produk->stok_kecil ?? 0,
                    'stok_minimum' => $produk->stok_minimum ?? 0,
                    'status_ketersediaan' => $produk->status_ketersediaan ?? 'stok_ada',
                    'status_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sudahAdaBesar = DB::table('produk_ukurans')
                ->where('produk_id', $produk->id)
                ->where('ukuran_id', $ukuranBesarId)
                ->exists();

            if (!$sudahAdaBesar) {
                DB::table('produk_ukurans')->insert([
                    'produk_id' => $produk->id,
                    'ukuran_id' => $ukuranBesarId,
                    'harga_modal' => $produk->harga_modal ?? 0,
                    'harga_jual' => $produk->harga_besar ?? 0,
                    'stok' => $produk->stok_besar ?? 0,
                    'stok_minimum' => $produk->stok_minimum ?? 0,
                    'status_ketersediaan' => $produk->status_ketersediaan ?? 'stok_ada',
                    'status_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sengaja tidak menghapus baris produk_ukurans di sini — data yang
        // sudah terlanjur diedit manual oleh admin lewat menu baru jangan
        // ikut hilang kalau migration ini di-rollback. Kalau memang perlu
        // bersih total, hapus manual lewat halaman Produk atau tinker.
    }
};