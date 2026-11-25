@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Pegawai" parentTitle="Pegawai" :parentUrl="route('pegawai.index')" />

@php
    $inisial = collect(explode(' ', trim($pegawai->nama)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('') ?: 'PG';
    $tanggalMasuk = $pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk) : null;
    $rolesAkun = $pegawai->user?->roles?->pluck('name') ?? collect();
@endphp

<div class="space-y-6">

    {{-- Profil Ringkas Pegawai --}}
    <x-common.component-card>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#FCEEEF] dark:bg-[#8E1E26]/25 border border-[#8E1E26]/20 text-xl font-display font-bold text-[#8E1E26] dark:text-[#EACE80]">
                    {{ $inisial }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="font-display text-xl font-bold text-[#3A221B] dark:text-[#FAF5E8]">
                            {{ $pegawai->nama }}
                        </h2>
                        @if ($pegawai->status_aktif)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 dark:bg-green-500/15 px-3 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-600 dark:bg-green-400"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 dark:bg-red-500/15 px-3 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-600 dark:bg-red-400"></span>
                                Tidak Aktif
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-[#7D6A60] dark:text-[#C5B5A8]">
                        {{ $pegawai->jabatan }}
                        @if ($pegawai->user?->email)
                            <span class="mx-1.5 opacity-50">&middot;</span>
                            <span>{{ $pegawai->user->email }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('pegawai.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <a href="{{ route('pegawai.edit', $pegawai->id) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Pegawai
                </a>
            </div>
        </div>
    </x-common.component-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Informasi Pribadi & Pekerjaan --}}
        <div class="lg:col-span-2">
            <x-common.component-card title="Informasi Pegawai">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $pegawai->nama }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jabatan</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $pegawai->jabatan }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nomor HP</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            @if ($pegawai->no_hp)
                                <a href="tel:{{ $pegawai->no_hp }}" class="hover:text-brand-600 dark:hover:text-amber-400 transition">
                                    {{ $pegawai->no_hp }}
                                </a>
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white break-all">
                            @if ($pegawai->user?->email)
                                <a href="mailto:{{ $pegawai->user->email }}" class="hover:text-brand-600 dark:hover:text-amber-400 transition">
                                    {{ $pegawai->user->email }}
                                </a>
                            @else
                                <span class="font-normal text-gray-400">Belum diatur</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Masuk</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            @if ($tanggalMasuk)
                                {{ $tanggalMasuk->translatedFormat('d F Y') }}
                                <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">
                                    Bergabung {{ $tanggalMasuk->diffForHumans() }}
                                </span>
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status Keaktifan</p>
                        <p class="mt-1">
                            @if ($pegawai->status_aktif)
                                <span class="inline-flex rounded-full bg-green-100 dark:bg-green-500/15 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400">
                                    Aktif Bekerja
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 dark:bg-red-500/15 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                                    Tidak Aktif
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Alamat Lengkap</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line">
                            {{ $pegawai->alamat ?: '-' }}
                        </p>
                    </div>

                </div>
            </x-common.component-card>
        </div>

        {{-- Informasi Akun Login --}}
        <div class="lg:col-span-1">
            <x-common.component-card title="Akun Login">
                @if ($pegawai->user)
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Username</p>
                            <p class="mt-1 font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $pegawai->user->username }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email Akun</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white break-all">
                                {{ $pegawai->user->email ?: '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Role Akses</p>
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                @forelse ($rolesAkun as $roleName)
                                    <span class="inline-flex rounded-full bg-amber-100 dark:bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:text-amber-300">
                                        {{ $roleName }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Belum memiliki role</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex justify-between gap-2">
                                <span>Terdaftar</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $pegawai->created_at ? $pegawai->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span>Diperbarui</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $pegawai->updated_at ? $pegawai->updated_at->translatedFormat('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pegawai ini belum terhubung dengan akun login.
                    </p>
                @endif
            </x-common.component-card>
        </div>

    </div>

</div>

@endsection
