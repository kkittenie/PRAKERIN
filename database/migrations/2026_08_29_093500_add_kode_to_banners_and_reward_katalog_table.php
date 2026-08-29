<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('kode_banner', 50)->nullable()->after('id');
        });

        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->string('kode_reward', 50)->nullable()->after('id');
        });

        // Populasi data lama jika ada
        DB::table('banners')->get()->each(function ($b) {
            DB::table('banners')->where('id', $b->id)->update([
                'kode_banner' => 'BNR' . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT),
            ]);
        });

        DB::table('reward_katalog')->get()->each(function ($r) {
            DB::table('reward_katalog')->where('id', $r->id)->update([
                'kode_reward' => 'RWD' . str_pad((string) $r->id, 3, '0', STR_PAD_LEFT),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('kode_banner');
        });

        Schema::table('reward_katalog', function (Blueprint $table) {
            $table->dropColumn('kode_reward');
        });
    }
};
