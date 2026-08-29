@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Pegawai" parentTitle="Pegawai" :parentUrl="route('pegawai.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Pegawai">

        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nama Pegawai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nama Pegawai
                    </label>

                    <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">

                    @error('nama')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nomor HP --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nomor HP
                    </label>

                    <input type="tel" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">

                    @error('no_hp')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Username
                    </label>
                    <input type="text" name="username" value="{{ old('username', optional($pegawai->user)->username) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('username')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email', optional($pegawai->user)->email) }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('email')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Password baru (kosongkan kalau tidak diganti)
                    </label>
                    <input type="password" name="password"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('password')<small class="text-red-500">{{ $message }}</small>@enderror
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Jabatan
                    </label>

                    <select name="jabatan"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90 dark:bg-gray-900">

                        <option value="" class="dark:bg-gray-900">Pilih Jabatan</option>

                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('jabatan', $pegawai->jabatan) == $role ? 'selected' : '' }}
                                class="dark:bg-gray-900">
                                {{ $role }}
                            </option>
                        @endforeach

                    </select>

                    @error('jabatan')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tanggal Masuk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Tanggal Masuk
                    </label>

                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $pegawai->tanggal_masuk) }}"
                        max="{{ date('Y-m-d') }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">

                    @error('tanggal_masuk')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Alamat
                    </label>

                    <textarea name="alamat" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-850 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('alamat', $pegawai->alamat) }}</textarea>

                    @error('alamat')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status Aktif
                    </label>

                    <select name="status_aktif"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-855 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white/90 dark:bg-gray-900">

                        <option value="1" {{ old('status_aktif', $pegawai->status_aktif) == 1 ? 'selected' : '' }}
                            class="dark:bg-gray-900">
                            Aktif
                        </option>

                        <option value="0" {{ old('status_aktif', $pegawai->status_aktif) == 0 ? 'selected' : '' }}
                            class="dark:bg-gray-900">
                            Tidak Aktif
                        </option>

                    </select>

                    @error('status_aktif')
                    <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a href="{{ route('pegawai.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-750 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">

                    Batal

                </a>

                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">

                    Update Pegawai

                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@endsection