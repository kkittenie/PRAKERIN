<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom `jabatan` sebelumnya enum('Admin','Kasir','Pegawai'), jadi walaupun
     * dropdown di form sudah dibuat dinamis (ambil dari tabel roles), simpan
     * data tetap akan gagal untuk role baru selain 3 itu karena DB menolaknya.
     * Diubah ke string supaya bisa diisi role apapun yang ada di tabel roles.
     *
     * Pakai raw SQL (bukan Schema::table()->change()) supaya tidak perlu
     * tambah dependency doctrine/dbal cuma untuk 1 migration ini.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite tidak benar-benar menegakkan enum, jadi tidak perlu ALTER apa pun.
            return;
        }

        DB::statement('ALTER TABLE pegawais MODIFY jabatan VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE pegawais MODIFY jabatan ENUM('Admin','Kasir','Pegawai') NOT NULL");
    }
};
