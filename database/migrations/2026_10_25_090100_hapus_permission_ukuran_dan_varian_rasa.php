<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menu Ukuran Produk dan Varian Rasa sudah dihapus, jadi permission-nya
 * (lihat/tambah/edit/hapus untuk ukuran dan varian_rasa) ikut dibersihkan.
 */
return new class extends Migration
{
    private function daftar(): array
    {
        $nama = [];
        foreach (['lihat', 'tambah', 'edit', 'hapus'] as $aksi) {
            foreach (['ukuran', 'varian_rasa'] as $modul) {
                $nama[] = "{$aksi}_{$modul}";
            }
        }

        return $nama;
    }

    public function up(): void
    {
        // Lewat model Spatie supaya relasi ke role dan user ikut terlepas.
        Permission::whereIn('name', $this->daftar())->where('guard_name', 'web')->get()->each->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->daftar() as $nama) {
            Permission::firstOrCreate(['name' => $nama, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};