<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Slide halaman welcome / login member (gambar + judul + deskripsi), diatur admin seperti banner.
        Schema::create('welcome_slides', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('deskripsi', 255)->nullable();
            $table->string('gambar');
            $table->integer('urutan')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // Gambar QRIS untuk pembayaran pesanan online. Hanya satu yang aktif dalam satu waktu.
        Schema::create('qris_images', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('gambar');
            $table->boolean('status_aktif')->default(false);
            $table->timestamps();
        });

        // Permission baru (modul welcome_slide & qris), langsung diberikan ke role Admin.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['welcome_slide', 'qris'] as $modul) {
            foreach (['lihat', 'tambah', 'edit', 'hapus'] as $aksi) {
                Permission::firstOrCreate(['name' => "{$aksi}_{$modul}", 'guard_name' => 'web']);
            }
        }
        if ($admin = Role::where('name', 'Admin')->where('guard_name', 'web')->first()) {
            $admin->givePermissionTo(Permission::whereIn('name', [
                'lihat_welcome_slide', 'tambah_welcome_slide', 'edit_welcome_slide', 'hapus_welcome_slide',
                'lihat_qris', 'tambah_qris', 'edit_qris', 'hapus_qris',
            ])->get());
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('qris_images');
        Schema::dropIfExists('welcome_slides');

        Permission::whereIn('name', [
            'lihat_welcome_slide', 'tambah_welcome_slide', 'edit_welcome_slide', 'hapus_welcome_slide',
            'lihat_qris', 'tambah_qris', 'edit_qris', 'hapus_qris',
        ])->delete();
    }
};