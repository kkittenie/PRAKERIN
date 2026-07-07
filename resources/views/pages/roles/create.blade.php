@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('roles.store') }}"
    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    @csrf

    <div class="mb-6">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Role</label>
        <input type="text" name="name" value="{{ old('name') }}" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Pilih Permission</h4>

    @foreach ($permissions as $module => $perms)
        <div class="mb-4 border-t border-gray-100 pt-4 dark:border-gray-800">
            <strong class="mb-2 block capitalize text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $module) }}</strong>
            <div class="flex flex-wrap gap-4">
                @foreach ($perms as $perm)
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}">
                        {{ $perm->name }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-6 flex gap-3">
        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan</button>
        <a href="{{ route('roles.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
    </div>
</form>
@endsection