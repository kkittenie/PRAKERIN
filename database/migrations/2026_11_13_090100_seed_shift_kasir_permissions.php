<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission untuk fitur Shift Kasir & Rekap Shift:
 *  - lihat_shift_kasir  : melihat halaman Shift Kasir (ringkasan shift berjalan)
 *  - tambah_shift_kasir : membuka & menutup shift. Pemilik permission ini WAJIB buka shift
 *                         sebelum bisa memproses pembayaran di kasir.
 *  - lihat_rekap_shift  : melihat menu Rekap Shift (semua shift, semua kasir)
 *
 * Role Admin otomatis dapat semua permission baru. Role lain (mis. Kasir) diatur
 * lewat menu Kelola Role, jadi kasir lama tidak tiba-tiba terkunci sebelum diatur.
 */
return new class extends Migration
{
    private array $permissions = [
        'lihat_shift_kasir',
        'tambah_shift_kasir',
        'lihat_rekap_shift',
    ];

    public function up(): void
    {
        $guard = 'web';
        $now = now();

        foreach ($this->permissions as $name) {
            if (! DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->exists()) {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $adminRole = DB::table('roles')->where('name', 'Admin')->first();

        if ($adminRole) {
            $ids = DB::table('permissions')
                ->where('guard_name', $guard)
                ->whereIn('name', $this->permissions)
                ->pluck('id');

            foreach ($ids as $permId) {
                if (! DB::table('role_has_permissions')->where('role_id', $adminRole->id)->where('permission_id', $permId)->exists()) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->permissions)->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};