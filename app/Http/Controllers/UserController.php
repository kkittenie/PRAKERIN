<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $users = User::with('roles')->get();
        return view('pages.users.index', ['title' => 'Kelola Pengguna', 'users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('pages.users.create', ['title' => 'Tambah Pengguna', 'roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
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
            ->log('Admin menambahkan user');

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
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
        $user = User::findOrFail($id);
        $roles = Role::all();
        $userRole = $user->roles->pluck('name')->first();

        return view('pages.users.edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'roles' => $roles,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|min:6',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
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
            ->log('Admin mengubah data user');

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
        {
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
            ->log('Admin menghapus user');

            $user->delete();
            return back()->with('success', 'User dihapus');
        }
}
