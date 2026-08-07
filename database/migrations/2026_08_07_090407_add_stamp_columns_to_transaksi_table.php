<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transaksi', 'stamp_program_id')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->foreignId('stamp_program_id')->nullable()->after('poin_didapat')
                    ->constrained('stamp_programs')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('transaksi', 'stamp_sebelum')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->unsignedInteger('stamp_sebelum')->nullable()->after('stamp_program_id');
            });
        }

        if (!Schema::hasColumn('transaksi', 'stamp_didapat')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->unsignedInteger('stamp_didapat')->nullable()->default(0)->after('stamp_sebelum');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi', 'stamp_didapat')) {
                $table->dropColumn('stamp_didapat');
            }
            if (Schema::hasColumn('transaksi', 'stamp_sebelum')) {
                $table->dropColumn('stamp_sebelum');
            }
            if (Schema::hasColumn('transaksi', 'stamp_program_id')) {
                $table->dropConstrainedForeignId('stamp_program_id');
            }
        });
    }
};