<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stamp_program_hadiah', function (Blueprint $table) {
            // Varian/ukuran (baris detail_produks) yang dijadikan hadiah. Null = hadiah lama
            // yang belum memilih varian (stok diambil dari total produk seperti sebelumnya).
            $table->foreignId('detail_produk_id')->nullable()->after('produk_id')
                ->constrained('detail_produks')->nullOnDelete();

            // Unique lama (program + produk) menghalangi produk yang sama dipilih dengan varian
            // berbeda. Index biasa dipasang dulu supaya foreign key ke stamp_programs tetap punya index.
            $table->index('stamp_program_id', 'stamp_hadiah_program_idx');
            $table->dropUnique(['stamp_program_id', 'produk_id']);
        });

        Schema::table('stamp_redemptions', function (Blueprint $table) {
            $table->foreignId('detail_produk_id')->nullable()->after('produk_id')
                ->constrained('detail_produks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stamp_redemptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('detail_produk_id');
        });

        Schema::table('stamp_program_hadiah', function (Blueprint $table) {
            $table->dropConstrainedForeignId('detail_produk_id');
            $table->unique(['stamp_program_id', 'produk_id']);
            $table->dropIndex('stamp_hadiah_program_idx');
        });
    }
};