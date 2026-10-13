<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganti nama tabel. MySQL otomatis memindahkan foreign key yang menunjuk
        // ke tabel ini (produksi_varians.produk_varian_id, detail_transaksis, bundling_items),
        // jadi nama kolom di tabel lain sengaja TIDAK diubah di langkah ini.
        if (Schema::hasTable('produk_varians') && !Schema::hasTable('detail_produks')) {
            Schema::rename('produk_varians', 'detail_produks');
        }

        // Isi kode untuk detail lama yang masih kosong: KODE-PRODUK-01, -02, dst.
        // Kode yang sudah ada tidak diubah, dan nomor yang bentrok dilewati.
        $dipakai = DB::table('detail_produks')
            ->whereNotNull('kode_barcode')
            ->pluck('kode_barcode')
            ->flip();

        $kosong = DB::table('detail_produks')
            ->where(function ($q) {
                $q->whereNull('kode_barcode')->orWhere('kode_barcode', '');
            })
            ->orderBy('produk_id')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get(['id', 'produk_id']);

        $kodeProduk = DB::table('produks')->pluck('kode_produk', 'id');
        $nomorTerakhir = [];

        foreach ($kosong as $baris) {
            $kode = $kodeProduk[$baris->produk_id] ?? null;
            if (!$kode) {
                continue;
            }

            $nomor = $nomorTerakhir[$baris->produk_id] ?? 0;
            do {
                $nomor++;
                $kandidat = $kode . '-' . str_pad($nomor, 2, '0', STR_PAD_LEFT);
            } while ($dipakai->has($kandidat));

            $nomorTerakhir[$baris->produk_id] = $nomor;
            $dipakai[$kandidat] = true;

            DB::table('detail_produks')->where('id', $baris->id)->update(['kode_barcode' => $kandidat]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detail_produks') && !Schema::hasTable('produk_varians')) {
            Schema::rename('detail_produks', 'produk_varians');
        }
    }
};