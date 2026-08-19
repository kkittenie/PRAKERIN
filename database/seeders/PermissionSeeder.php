<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'dashboard', 'user', 'role', 'kategori_produk', 'produk', 'stok', 'satuan', 'produksi', 'bundling', 'pegawai', 'member', 'promo', 'promo_event', 'transaksi', 'laporan', 'activity_log', 'banner', 'pajak',
        ];
        $actions = ['lihat', 'tambah', 'edit', 'hapus'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$module}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Permission untuk menu "Pesanan Pick Up" (pesanan dari Member App).
        foreach (['lihat_pesanan_pickup', 'edit_pesanan_pickup'] as $nama) {
            Permission::firstOrCreate(['name' => $nama, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $kasir = Role::firstOrCreate(['name' => 'Kasir']);
        $pegawai = Role::firstOrCreate(['name' => 'Pegawai']);

        //all akses buat admin
        $admin->syncPermissions(Permission::all());

        $kasir->syncPermissions([
            'lihat_dashboard',
            'lihat_transaksi', 'tambah_transaksi',
            'lihat_member', 'tambah_member', 'edit_member',
            'lihat_pesanan_pickup', 'edit_pesanan_pickup',
        ]);

        $pegawai->syncPermissions([
            'lihat_dashboard',
        ]);
    }
}