<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setiap produk wajib punya minimal satu detail produk. Produk lama tanpa ukuran dan
        // tanpa rasa belum punya baris detail, jadi dibuatkan satu baris (ukuran & variasi kosong)
        // dari data produknya. Kode memakai kode_produk supaya label lama tetap bisa discan.
        if (!Schema::hasTable('detail_produks')) {
            return;
        }

        $now = now();
        $kodeDipakai = DB::table('detail_produks')->whereNotNull('kode_barcode')->pluck('kode_barcode')->flip();
        $adaStatusAktif = Schema::hasColumn('produks', 'status_aktif');

        foreach (DB::table('produks')->get() as $produk) {
            if (DB::table('detail_produks')->where('produk_id', $produk->id)->exists()) {
                continue;
            }

            $kode = $produk->kode_produk;
            $nomor = 0;
            while ($kode && $kodeDipakai->has($kode)) {
                $nomor++;
                $kode = $produk->kode_produk . '-' . str_pad($nomor, 2, '0', STR_PAD_LEFT);
            }

            $stok = (int) ($produk->stok ?? 0);

            DB::table('detail_produks')->insert([
                'produk_id' => $produk->id,
                'ukuran' => null,
                'variasi' => null,
                'kode_barcode' => $kode,
                'harga_modal' => $produk->harga_modal ?? 0,
                'harga_jual' => $produk->harga_jual ?? 0,
                'stok' => $stok,
                'stok_minimum' => (int) ($produk->stok_minimum ?? 0),
                'foto' => null,
                'status_ketersediaan' => $stok > 0 ? 'stok_ada' : 'perlu_diproduksi',
                'status_aktif' => $adaStatusAktif ? (int) (bool) $produk->status_aktif : 1,
                'urutan' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($kode) {
                $kodeDipakai[$kode] = true;
            }
        }
    }

    public function down(): void
    {
        // Tidak dibalik: baris detail yang sudah dipakai transaksi/produksi tidak boleh dihapus otomatis.
    }
};