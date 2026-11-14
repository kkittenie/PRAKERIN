<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah permission untuk fitur Rekap Produk.
     * Role Admin otomatis mendapatkan permission ini.
     */
    public function up(): void
    {
        $guard = 'web';
        $now = now();
        $permissions = ['lihat_rekap_produk', 'lihat_laporan_produk'];

        foreach ($permissions as $name) {
            if (! DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->exists()) {
                DB::table('permissions')->insert([
                    'name'       => $name,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Berikan permission ke role Admin
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if ($adminRole) {
            $permIds = DB::table('permissions')
                ->where('guard_name', $guard)
                ->whereIn('name', $permissions)
                ->pluck('id');

            foreach ($permIds as $permId) {
                if (! DB::table('role_has_permissions')->where('role_id', $adminRole->id)->where('permission_id', $permId)->exists()) {
                    DB::table('role_has_permissions')->insert([
                        'role_id'       => $adminRole->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissions = ['lihat_rekap_produk', 'lihat_laporan_produk'];
        $permIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');

        if ($permIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permIds)->delete();
            DB::table('permissions')->whereIn('id', $permIds)->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

