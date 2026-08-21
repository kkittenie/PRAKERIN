<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->foreignId('promo_event_id')->nullable()->after('bundling_id')
                ->constrained('promo_produk_events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['promo_event_id']);
            $table->dropColumn('promo_event_id');
        });
    }
};