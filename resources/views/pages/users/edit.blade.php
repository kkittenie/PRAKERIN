@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}"
    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    @csrf
    @method('PUT')

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Username</label>
        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('username')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email (opsional)</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password baru (kosongkan kalau
            tidak diganti)</label>
        <input type="password" name="password"
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-6">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
        <select name="role" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
            @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected($userRole==$role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3">
        <button type="submit"
            class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Update</button>
        <a href="{{ route('users.index') }}"
            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
    </div>
</form>
@endsection