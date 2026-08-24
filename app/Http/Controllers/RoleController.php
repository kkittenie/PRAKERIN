<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Role::withCount('permissions');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addColumn('permission_html', fn ($item) => view('pages.roles.partials.permission', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.roles.partials.aksi', compact('item'))->render())
                    ->rawColumns(['permission_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data role.'], 500);
            }
        }

        return view('pages.roles.index', ['title' => 'Kelola Role']);
    }

    public function create()
    {
        try {
            $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
            return view('pages.roles.create', ['title' => 'Tambah Role', 'permissions' => $permissions]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
                'permissions' => 'array',
            ]);

            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->permissions ?? []);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $request->permissions ?? [],
                ])
                ->log('menambahkan role');

            return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $permissions = Permission::all()->groupBy(fn($p) => explode('_', $p->name, 2)[1] ?? 'lainnya');
            $rolePermissions = $role->permissions->pluck('name')->toArray();

            return view('pages.roles.edit', [
                'title' => 'Edit role',
                'role' => $role,
                'permissions' => $permissions,
                'rolePermissions' => $rolePermissions,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $role = Role::findOrFail($id);

            $request->validate([
                'name' => 'required|string|unique:roles,name,' . $role->id,
                'permissions' => 'array',
            ]);

            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions ?? []);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $request->permissions ?? [],
                ])
                ->log('mengupdate role');

            return redirect()->route('roles.index')->with('success', 'Role berhasil diupdate');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);

            if (in_array($role->name, ['Admin', 'Kasir', 'Pegawai'])) {
                return back()->with('error', 'Role bawaan tidak bisa dihapus');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ])
                ->log('menghapus role');

            $role->delete();
            return back()->with('success', 'Role dihapus');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
