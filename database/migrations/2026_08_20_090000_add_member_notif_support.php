<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Notifikasi in-app buat member (dipakai di halaman /mobile/notifikasi).
        // Tabel app_notifications sebelumnya cuma buat notif dashboard admin
        // (user_id), sekarang ditambah member_id supaya bisa dipakai dua-duanya.
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('user_id')
                ->constrained('members')->onDelete('cascade');
            $table->index(['member_id', 'is_read']);
        });

        // Token FCM per device member, buat kirim push notification (kaya WA)
        // ke HP member waktu status pesanannya berubah. Satu member bisa
        // punya beberapa device/token (HP lama + baru dsb).
        Schema::create('member_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('token', 255)->unique();
            $table->string('platform', 20)->default('android');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
        });

        Schema::dropIfExists('member_device_tokens');
    }
};
