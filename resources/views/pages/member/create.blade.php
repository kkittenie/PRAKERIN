@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah Member" parentTitle="Member" :parentUrl="route('member.index')" />
    <form method="POST" action="{{ route('member.store') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf

        <div class="mb-5">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Nama <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" value="{{ old('nama') }}" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
            @error('nama')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                No. HP <span class="text-red-500">*</span>
            </label> <input type="text" name="no_hp" value="{{ old('no_hp') }}" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
            @error('no_hp')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            <p class="mt-1.5 text-xs text-gray-400">Nomor HP ini akan jadi password default member untuk login pertama kali.</p>
        </div>

        <div class="mb-5">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email <span class="text-gray-400 font-normal"></span>
            </label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="contoh@email.com"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan</button>
            <a href="{{ route('member.index') }}"
                class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
        </div>
    </form>
@endsection