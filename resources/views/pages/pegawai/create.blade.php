@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Pegawai" />

<div class="space-y-6">

<x-common.component-card title="Form Tambah Pegawai">

<form action="{{ route('pegawai.store') }}" method="POST">

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nama Pegawai --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Nama Pegawai
        </label>

        <input
            type="text"
            name="nama"
            value="{{ old('nama') }}"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('nama')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Nomor HP --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Nomor HP
        </label>

        <input
            type="tel"
            name="no_hp"
            value="{{ old('no_hp') }}"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('no_hp')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Jabatan --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Jabatan
        </label>

        <select
            name="jabatan"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

            <option value="">Pilih Jabatan</option>

            <option value="Admin" {{ old('jabatan') == 'Admin' ? 'selected' : '' }}>
                Admin
            </option>

            <option value="Kasir" {{ old('jabatan') == 'Kasir' ? 'selected' : '' }}>
                Kasir
            </option>

            <option value="Pegawai" {{ old('jabatan') == 'Pegawai' ? 'selected' : '' }}>
                Pegawai
            </option>

        </select>

        @error('jabatan')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Tanggal Masuk --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Tanggal Masuk
        </label>

        <input
            type="date"
            name="tanggal_masuk"
            value="{{ old('tanggal_masuk') }}"
            max="{{ date('Y-m-d') }}"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

        @error('tanggal_masuk')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Alamat --}}
    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Alamat
        </label>

        <textarea
            name="alamat"
            rows="3"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('alamat') }}</textarea>

        @error('alamat')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">
            Status Aktif
        </label>

        <select
            name="status_aktif"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-brand-500 focus:ring-brand-500">

            <option value="1" {{ old('status_aktif') == '1' ? 'selected' : '' }}>
                Aktif
            </option>

            <option value="0" {{ old('status_aktif') == '0' ? 'selected' : '' }}>
                Tidak Aktif
            </option>

        </select>

        @error('status_aktif')
            <small class="text-red-500">{{ $message }}</small>
        @enderror
    </div>

</div>

<div class="mt-8 flex justify-end gap-3">

<a
href="{{ route('pegawai.index') }}"
class="rounded-lg border px-5 py-2">

Batal

</a>

<button
type="submit"
class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">

Simpan Pegawai

</button>

</div>

</form>

</x-common.component-card>

</div>

@endsection