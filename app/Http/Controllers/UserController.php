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
    public function index()
    {
        try {
            $users = User::with('roles')->get();

            $grouped = $users->groupBy(function ($user) {
                return $user->roles->pluck('name')->first() ?? 'Tanpa Role';
            });

            return view('pages.users.index', ['title' => 'Kelola Pengguna', 'grouped' => $grouped]);
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
