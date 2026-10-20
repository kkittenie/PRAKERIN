<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dukungan voucher untuk pembeli non member.
     *
     * 1. reward_katalog.target_member mendapat nilai baru "non_member".
     * 2. voucher_usages.member_id boleh NULL, karena pemakaian voucher oleh
     *    pembeli non member tidak terkait ke data member mana pun.
     */
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE reward_katalog MODIFY target_member ENUM('semua', 'khusus', 'non_member') NOT NULL DEFAULT 'semua'");
        } else {
            Schema::table('reward_katalog', function (Blueprint $table) {
                $table->enum('target_member', ['semua', 'khusus', 'non_member'])->default('semua')->change();
            });
        }

        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Voucher non member dijadikan "khusus" tanpa member agar tidak bocor ke semua member.
        DB::table('reward_katalog')
            ->where('target_member', 'non_member')
            ->update(['target_member' => 'khusus', 'status_aktif' => false]);

        // Riwayat pemakaian non member tidak bisa disimpan lagi karena member_id wajib terisi.
        DB::table('voucher_usages')->whereNull('member_id')->delete();

        Schema::table('voucher_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE reward_katalog MODIFY target_member ENUM('semua', 'khusus') NOT NULL DEFAULT 'semua'");
        } else {
            Schema::table('reward_katalog', function (Blueprint $table) {
                $table->enum('target_member', ['semua', 'khusus'])->default('semua')->change();
            });
        }
    }
};