<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        return view('pages.roles.index', ['title' => 'Kelola Role', 'roles' => $roles]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
        return view('pages.roles.create', ['title' => 'Tambah Role', 'permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->SyncPermissions($request->permissions ?? []);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'role' => $role->name,
            ])
            ->log('Admin menambahkan role');

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.roles.edit', [
            'title' => 'Edit role',
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'role' => $role->name,
            ])
            ->log('Admin mengupdate role');

        return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['Admin', 'Kasir', 'Pegawai'])) {
            return back()->with('error', 'Role bawaan tidak bisa dihapus');
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'role' => $role->name,
            ])
            ->log('Admin menghapus role');

        $role->delete();
        return back()->with('success', 'Role dihapus');
    }
}
