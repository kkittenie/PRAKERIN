<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = User::with('roles');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addColumn('role_html', function ($user) {
                        $roleName = $user->roles->pluck('name')->first() ?? 'Tanpa Role';
                        return '<span class="rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-600 dark:text-brand-400">' . e($roleName) . '</span>';
                    })
                    ->editColumn('name', function ($user) {
                        return '<span class="font-medium text-gray-800 dark:text-white/90">' . e($user->name) . '</span>';
                    })
                    ->editColumn('username', function ($user) {
                        return '<span class="text-gray-600 dark:text-gray-400">' . e($user->username) . '</span>';
                    })
                    ->addColumn('aksi', function ($user) {
                        $editBtn = '';
                        if (auth()->user()->can('edit_user')) {
                            $editUrl = route('users.edit', $user->id);
                            $editBtn = '<a href="' . $editUrl . '" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>';
                        }

                        $destroyBtn = '';
                        if (auth()->user()->can('hapus_user')) {
                            $destroyUrl = route('users.destroy', $user->id);
                            $csrf = csrf_field();
                            $method = method_field('DELETE');
                            $namaEscaped = e(addslashes($user->name));
                            $destroyBtn = '<form action="' . $destroyUrl . '" method="POST" class="inline">' . $csrf . $method .
                                          '<button type="button" title="Hapus" @click="bukaModalHapus($el.closest(\'form\'), \'' . $namaEscaped . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>' .
                                          '</form>';
                        }

                        return '<div class="inline-flex items-center gap-0.5 justify-end">' . $editBtn . $destroyBtn . '</div>';
                    })
                    ->rawColumns(['role_html', 'name', 'username', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pengguna.'], 500);
            }
        }

        try {
            return view('pages.users.index', ['title' => 'Kelola Pengguna']);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $roles = Role::all();
            return view('pages.users.create', ['title' => 'Tambah Pengguna', 'roles' => $roles]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|unique:users,username',
                'email' => 'nullable|email|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required|exists:roles,name',
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'username' => $user->username,
                    'nama' => $user->name,
                ])
                ->log('menambahkan user');

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
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
        try {
            $user = User::findOrFail($id);
            $roles = Role::all();
            $userRole = $user->roles->pluck('name')->first();

            return view('pages.users.edit', [
                'title' => 'Edit Pengguna',
                'user' => $user,
                'roles' => $roles,
                'userRole' => $userRole,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|unique:users,username,' . $user->id,
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'role' => 'required|exists:roles,name',
                'password' => 'nullable|min:6',
            ]);

            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $user->syncRoles([$request->role]);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'username' => $user->username,
                    'nama' => $user->name,
                ])
                ->log('mengubah data user');

            return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Tidak bisa menghapus akun sendiri');
            }

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'username' => $user->username,
                    'nama' => $user->name,
                ])
                ->log('menghapus user');

            $user->delete();
            return back()->with('success', 'User dihapus');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
