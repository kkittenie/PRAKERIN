<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah permission khusus jam_operasional (lihat, edit).
 * Sebelumnya fitur ini nebeng di permission pajak.
 * Role Admin otomatis dapat semua permission baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';
        $now = now();

        // Hapus jika sempat terbuat tambah/hapus_jam_operasional
        $unused = DB::table('permissions')
            ->whereIn('name', ['tambah_jam_operasional', 'hapus_jam_operasional'])
            ->pluck('id');
        if ($unused->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $unused)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $unused)->delete();
            DB::table('permissions')->whereIn('id', $unused)->delete();
        }

        $actions = ['lihat', 'edit'];

        foreach ($actions as $action) {
            $name = "{$action}_jam_operasional";

            // Hanya buat kalau belum ada
            if (! DB::table('permissions')->where('name', $name)->where('guard_name', $guard)->exists()) {
                DB::table('permissions')->insert([
                    'name'       => $name,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Berikan semua permission baru ke role Admin (supaya admin tetap bisa akses)
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();

        if ($adminRole) {
            $newPerms = DB::table('permissions')
                ->where('guard_name', $guard)
                ->where('name', 'like', '%_jam_operasional')
                ->pluck('id');

            foreach ($newPerms as $permId) {
                if (! DB::table('role_has_permissions')->where('role_id', $adminRole->id)->where('permission_id', $permId)->exists()) {
                    DB::table('role_has_permissions')->insert([
                        'role_id'       => $adminRole->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permIds = DB::table('permissions')
            ->where('name', 'like', '%_jam_operasional')
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();
    }
};
