<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bug fix: kolom `diskon` di tabel `transaksi` & `pesanan_ditahan` menyimpan
 * NOMINAL (Rp) total diskon (promo produk + diskon manual + voucher), bukan
 * persentase. Tapi di frontend (indeks.blade.php), nilai nominal ini pernah
 * dibaca ulang seolah-olah dia adalah `diskonPersen` saat pesanan ditahan
 * dipulihkan / dilunaskan / dicetak struknya — sehingga diskon manual
 * membengkak (mis. "Diskon Tambahan (3600%)") dan menimpa/menggandakan
 * potongan dari voucher.
 *
 * Solusi: simpan persentase diskon manual di kolom terpisah supaya saat
 * pesanan ditahan dipulihkan/dilunaskan, frontend tidak perlu lagi menebak
 * persentase dari nilai nominal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transaksi', 'diskon_persen')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->decimal('diskon_persen', 8, 2)->default(0)->after('diskon');
            });
        }

        if (!Schema::hasColumn('pesanan_ditahan', 'diskon_persen')) {
            Schema::table('pesanan_ditahan', function (Blueprint $table) {
                $table->decimal('diskon_persen', 8, 2)->default(0)->after('diskon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi', 'diskon_persen')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('diskon_persen');
            });
        }
        if (Schema::hasColumn('pesanan_ditahan', 'diskon_persen')) {
            Schema::table('pesanan_ditahan', function (Blueprint $table) {
                $table->dropColumn('diskon_persen');
            });
        }
    }
};
