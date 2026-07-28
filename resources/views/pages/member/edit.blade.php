@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Member" parentTitle="Member" :parentUrl="route('member.index')" />
<form method="POST" action="{{ route('member.update', $member) }}"
    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    @csrf
    @method('PUT')

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama</label>
        <input type="text" name="nama" value="{{ old('nama', $member->nama) }}" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('nama')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">No. HP</label>
        <input type="text" name="no_hp" value="{{ old('no_hp', $member->no_hp) }}" required
            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" />
        @error('no_hp')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="mb-6">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Poin saat ini</label>
        <input type="text" value="{{ $member->poin }} ({{ $member->level }})" disabled
            class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-white/[0.02]" />
        <p class="mt-1 text-xs text-gray-400">Poin bertambah otomatis lewat transaksi, tidak bisa diedit manual</p>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Update</button>
        <a href="{{ route('member.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
    </div>
</form>
@endsection