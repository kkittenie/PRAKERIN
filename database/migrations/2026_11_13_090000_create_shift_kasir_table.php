<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur Shift Kasir:
 *  - tabel `shift_kasir`  : satu baris per shift (modal laci awal, uang laci akhir, ringkasan pembayaran)
 *  - kolom `transaksi.shift_kasir_id` : menandai transaksi itu terjadi di shift yang mana
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shift_kasir')) {
            Schema::create('shift_kasir', function (Blueprint $table) {
                $table->id();

                // Kasir. Nama disimpan juga sebagai arsip supaya rekap tetap utuh walau akun dihapus.
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nama_kasir');

                $table->date('tanggal')->index();
                $table->unsignedSmallInteger('nomor_shift')->default(1);
                $table->timestamp('mulai_pada')->nullable();
                $table->timestamp('selesai_pada')->nullable();
                $table->string('status', 12)->default('berjalan')->index(); // berjalan | selesai

                // Uang laci
                $table->decimal('modal_awal', 15, 2)->default(0);
                $table->decimal('uang_akhir', 15, 2)->nullable();
                $table->decimal('uang_diharapkan', 15, 2)->nullable();
                $table->decimal('selisih', 15, 2)->nullable(); // uang_akhir - uang_diharapkan (minus = kurang)

                // Ringkasan pembayaran (disimpan saat shift ditutup supaya rekap tidak berubah lagi)
                $table->decimal('total_tunai', 15, 2)->default(0);
                $table->decimal('total_qris', 15, 2)->default(0);
                $table->decimal('total_debit', 15, 2)->default(0);
                $table->decimal('total_lainnya', 15, 2)->default(0);
                $table->unsignedInteger('jumlah_tunai')->default(0);
                $table->unsignedInteger('jumlah_qris')->default(0);
                $table->unsignedInteger('jumlah_debit')->default(0);
                $table->unsignedInteger('jumlah_lainnya')->default(0);
                $table->unsignedInteger('jumlah_transaksi')->default(0);
                $table->json('rincian_lainnya')->nullable();

                $table->text('catatan_mulai')->nullable();
                $table->text('catatan_tutup')->nullable();

                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['tanggal', 'nomor_shift']);
            });
        }

        if (Schema::hasTable('transaksi') && ! Schema::hasColumn('transaksi', 'shift_kasir_id')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->foreignId('shift_kasir_id')
                    ->nullable()
                    ->after('metode_pembayaran')
                    ->constrained('shift_kasir')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'shift_kasir_id')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shift_kasir_id');
            });
        }

        Schema::dropIfExists('shift_kasir');
    }
};