<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        return view('pages.roles.index', ['title' => 'Kelola Role', 'roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
        return view('pages.roles.create', ['title' => 'Tambah Role', 'permissions' => $permissions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->SyncPermissions($request->permissions ?? []);

        activity()->log("Membuat role: {$role->name}");

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.roles.edit', [
            'title' => 'Edit role',
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        request->validate([
            'nama' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);
        $role->SyncPermissions($request->permissions ?? []);

        activity()->log("Mengubah role: {$role->name}");

        return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (in_array($role->name, ['Admin', 'Kasir'])) {
            return back()->with('error', 'Role bawaan tidak bisa dihapus');
        }

        $role->delete();
        return back()->with('success', 'Role dihapus');
    }
}
