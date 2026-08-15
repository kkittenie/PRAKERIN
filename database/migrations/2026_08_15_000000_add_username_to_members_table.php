<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'username')) {
                // Nullable di level DB supaya member lama (yang dibuat sebelum
                // fitur ini ada) tidak error saat migrate. Tapi tetap wajib
                // diisi & unik untuk member baru (dipaksa lewat validasi di
                // AuthController & MemberController, bukan di level kolom).
                $table->string('username')->nullable()->unique()->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
