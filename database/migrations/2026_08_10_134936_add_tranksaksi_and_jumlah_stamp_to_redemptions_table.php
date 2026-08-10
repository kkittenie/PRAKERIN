<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stamp_redemptions', function (Blueprint $table) {
            if (!Schema::hasColumn('stamp_redemptions', 'jumlah')) {
                $table->unsignedInteger('jumlah')->default(1)->after('produk_id');
            }

            if (!Schema::hasColumn('stamp_redemptions', 'transaksi_id')) {
                $table->foreignId('transaksi_id')->nullable()->after('diambil_pada')
                    ->constrained('transaksi')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stamp_redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('stamp_redemptions', 'transaksi_id')) {
                $table->dropConstrainedForeignId('transaksi_id');
            }
            if (Schema::hasColumn('stamp_redemptions', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
        });
    }
};