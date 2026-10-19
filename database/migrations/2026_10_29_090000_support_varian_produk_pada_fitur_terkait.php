<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom varian_produk_id dan detail_produk_id pada tabel-tabel terkait
     * agar produk Stok Bersama (varian_produks) dan Stok Per Varian (detail_produks)
     * dapat memilih varian secara konsisten di Produksi, Bundling, Stamp, Voucher,
     * Potongan Harga (Promo), dan Promo Produk (Promo Event).
     */
    public function up(): void
    {
        // 1. produksi_varians
        if (Schema::hasTable('produksi_varians') && !Schema::hasColumn('produksi_varians', 'varian_produk_id')) {
            Schema::table('produksi_varians', function (Blueprint $table) {
                $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                    ->constrained('varian_produks')->nullOnDelete();
            });
        }

        // 2. bundling_items
        if (Schema::hasTable('bundling_items') && !Schema::hasColumn('bundling_items', 'varian_produk_id')) {
            Schema::table('bundling_items', function (Blueprint $table) {
                $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                    ->constrained('varian_produks')->nullOnDelete();
            });
        }

        // 3. stamp_program_hadiah
        if (Schema::hasTable('stamp_program_hadiah') && !Schema::hasColumn('stamp_program_hadiah', 'varian_produk_id')) {
            Schema::table('stamp_program_hadiah', function (Blueprint $table) {
                $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                    ->constrained('varian_produks')->nullOnDelete();
            });
        }

        // 4. stamp_redemptions
        if (Schema::hasTable('stamp_redemptions') && !Schema::hasColumn('stamp_redemptions', 'varian_produk_id')) {
            Schema::table('stamp_redemptions', function (Blueprint $table) {
                $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                    ->constrained('varian_produks')->nullOnDelete();
            });
        }

        // 5. promo_detail_produks
        if (Schema::hasTable('promo_detail_produks')) {
            Schema::table('promo_detail_produks', function (Blueprint $table) {
                if (!Schema::hasColumn('promo_detail_produks', 'varian_produk_id')) {
                    $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                        ->constrained('varian_produks')->cascadeOnDelete();
                }
            });
            // Pastikan detail_produk_id nullable jika varian_produk_id terisi
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE promo_detail_produks MODIFY detail_produk_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }
        }

        // 6. promo_produk_event_detail_produks
        if (Schema::hasTable('promo_produk_event_detail_produks')) {
            Schema::table('promo_produk_event_detail_produks', function (Blueprint $table) {
                if (!Schema::hasColumn('promo_produk_event_detail_produks', 'varian_produk_id')) {
                    $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                        ->constrained('varian_produks')->cascadeOnDelete();
                }
            });
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE promo_produk_event_detail_produks MODIFY detail_produk_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }
        }

        // 7. reward_katalog_produk
        if (Schema::hasTable('reward_katalog_produk')) {
            Schema::table('reward_katalog_produk', function (Blueprint $table) {
                if (!Schema::hasColumn('reward_katalog_produk', 'detail_produk_id')) {
                    $table->foreignId('detail_produk_id')->nullable()->after('produk_id')
                        ->constrained('detail_produks')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('reward_katalog_produk', 'varian_produk_id')) {
                    $table->foreignId('varian_produk_id')->nullable()->after('detail_produk_id')
                        ->constrained('varian_produks')->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reward_katalog_produk')) {
            Schema::table('reward_katalog_produk', function (Blueprint $table) {
                if (Schema::hasColumn('reward_katalog_produk', 'varian_produk_id')) {
                    $table->dropConstrainedForeignId('varian_produk_id');
                }
                if (Schema::hasColumn('reward_katalog_produk', 'detail_produk_id')) {
                    $table->dropConstrainedForeignId('detail_produk_id');
                }
            });
        }

        if (Schema::hasTable('promo_produk_event_detail_produks') && Schema::hasColumn('promo_produk_event_detail_produks', 'varian_produk_id')) {
            Schema::table('promo_produk_event_detail_produks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }

        if (Schema::hasTable('promo_detail_produks') && Schema::hasColumn('promo_detail_produks', 'varian_produk_id')) {
            Schema::table('promo_detail_produks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }

        if (Schema::hasTable('stamp_redemptions') && Schema::hasColumn('stamp_redemptions', 'varian_produk_id')) {
            Schema::table('stamp_redemptions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }

        if (Schema::hasTable('stamp_program_hadiah') && Schema::hasColumn('stamp_program_hadiah', 'varian_produk_id')) {
            Schema::table('stamp_program_hadiah', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }

        if (Schema::hasTable('bundling_items') && Schema::hasColumn('bundling_items', 'varian_produk_id')) {
            Schema::table('bundling_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }

        if (Schema::hasTable('produksi_varians') && Schema::hasColumn('produksi_varians', 'varian_produk_id')) {
            Schema::table('produksi_varians', function (Blueprint $table) {
                $table->dropConstrainedForeignId('varian_produk_id');
            });
        }
    }
};
