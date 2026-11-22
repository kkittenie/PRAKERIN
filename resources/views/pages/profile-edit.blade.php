@extends('layouts.app')

@section('content')
@php
    $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();
    $primaryRole = $roles->isNotEmpty()
        ? ucwords(str_replace(['_', '-'], ' ', $roles->first()))
        : 'Staff Daisy Bakery';
    $joinedDate = $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-';
    $updatedDate = $user->updated_at ? $user->updated_at->diffForHumans() : '-';

    $initialTab = 'profil';
    if ($errors->updatePassword->any() || session('status') === 'password-updated') {
        $initialTab = 'keamanan';
    } elseif ($errors->userDeletion->any()) {
        $initialTab = 'akun';
    }
@endphp

<x-common.page-breadcrumb pageTitle="Profil Saya" />

<div class="space-y-6"
     x-data="{
         activeTab: '{{ $initialTab }}',
         modalHapusTerbuka: false,
         showCurPass: false,
         showNewPass: false,
         showConfPass: false,
         showDelPass: false
     }">

    {{-- Status Flash Banners --}}
    @if (session('status') === 'profile-updated')
        <div class="rounded-2xl bg-[#ECFDF3] dark:bg-[#2C5835]/20 px-4 py-3.5 text-sm font-semibold text-[#2C5835] dark:text-green-300 border border-[#2C5835]/20 flex items-center gap-3 shadow-2xs">
            <span class="flex items-center justify-center w-7 h-7 rounded-full bg-[#2C5835] text-[#FAF5E8] text-xs font-bold shrink-0">✓</span>
            <span>Informasi profil berhasil diperbarui.</span>
        </div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="rounded-2xl bg-[#ECFDF3] dark:bg-[#2C5835]/20 px-4 py-3.5 text-sm font-semibold text-[#2C5835] dark:text-green-300 border border-[#2C5835]/20 flex items-center gap-3 shadow-2xs">
            <span class="flex items-center justify-center w-7 h-7 rounded-full bg-[#2C5835] text-[#FAF5E8] text-xs font-bold shrink-0">✓</span>
            <span>Password akun berhasil diperbarui.</span>
        </div>
    @endif

    {{-- ═══════════ PROFILE IDENTITY HERO CARD ═══════════ --}}
    <div class="relative rounded-[26px] border border-[#3C2415]/14 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] shadow-[0_8px_28px_-10px_rgba(58,34,27,0.12)]">
        {{-- Cover Banner (clipped inner layer for strawberries; outer card NOT overflow-hidden so overlapping avatar never clips) --}}
        <div class="relative h-36 sm:h-44 rounded-t-[25px] daisy-awning-stripes overflow-hidden">
            {{-- Decorative floating strawberries --}}
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="pointer-events-none select-none absolute -top-4 left-8 w-20 h-20 opacity-20 -rotate-12">
            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
                class="pointer-events-none select-none absolute bottom-2 right-12 sm:right-24 w-24 h-24 opacity-25 rotate-12">
            <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt=""
                class="pointer-events-none select-none absolute top-5 right-6 sm:right-10 w-14 h-14 opacity-20 -rotate-6">

            {{-- Subtle top-right Daisy Bakery staff tag --}}
            <div class="absolute top-4 right-4 sm:top-5 sm:right-6 flex items-center gap-2 rounded-full bg-[#FAF5E8]/12 backdrop-blur-xs border border-[#FAF5E8]/15 px-3.5 py-1.5">
                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="w-4 h-4 rounded-full bg-[#FAF5E8] p-0.5">
                <span class="text-[11px] font-bold tracking-wider uppercase text-[#EACE80]">Daisy Bakery Staff</span>
            </div>
        </div>

        {{-- Profile Identity Body --}}
        <div class="px-5 sm:px-8 pb-6 sm:pb-7">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 pt-4 sm:pt-5 relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-5">
                    {{-- Avatar with crisp ring + online dot (negative margin ONLY on avatar so name never overlaps cover) --}}
                    <div class="relative -mt-16 sm:-mt-20 w-24 h-24 sm:w-28 sm:h-28 shrink-0">
                        <div class="w-full h-full rounded-[26px] overflow-hidden ring-4 ring-[#FFFDF9] dark:ring-[#241A16] shadow-[0_10px_24px_-6px_rgba(58,34,27,0.32)] bg-[#8E1E26]">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=256&background=8e1e26&color=faf5e8&bold=true"
                                alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </div>
                        <span title="Akun Aktif"
                            class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-[#2C5835] ring-4 ring-[#FFFDF9] dark:ring-[#241A16]">
                            <span class="h-2 w-2 rounded-full bg-[#FAF5E8]"></span>
                        </span>
                    </div>

                    {{-- Name, Role & Handle --}}
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="font-display text-2xl sm:text-[28px] font-bold text-[#3A221B] dark:text-[#FAF5E8] leading-tight">
                                {{ $user->name }}
                            </h1>
                            @if ($roles->isNotEmpty())
                                @foreach ($roles as $role)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EACE80]/35 dark:bg-[#EACE80]/20 border border-[#745B3F]/20 px-3 py-0.5 text-xs font-bold text-[#3A221B] dark:text-[#EACE80]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#8E1E26] dark:bg-[#EACE80]"></span>
                                        {{ ucwords(str_replace(['_', '-'], ' ', $role)) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FCEEEF] border border-[#8E1E26]/15 px-3 py-0.5 text-xs font-bold text-[#8E1E26]">
                                    {{ $primaryRole }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs sm:text-sm text-[#6B574C] dark:text-[#C5B5A8] font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#8E1E26] dark:text-[#EACE80] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ '@' . ($user->username ?? '-') }}
                            </span>
                            @if ($user->email)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#8E1E26] dark:text-[#EACE80] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $user->email }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#8E1E26] dark:text-[#EACE80] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Bergabung {{ $joinedDate }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Action Pills --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button type="button" @click="activeTab = 'profil'"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition border"
                        :class="activeTab === 'profil'
                            ? 'bg-[#8E1E26] text-[#FAF5E8] border-[#8E1E26] shadow-xs'
                            : 'bg-[#FAF5E8] dark:bg-[#2E211C] text-[#3A221B] dark:text-[#FAF5E8] border-[#3C2415]/15 dark:border-white/10 hover:border-[#8E1E26]/40'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profil
                    </button>
                    <button type="button" @click="activeTab = 'keamanan'"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition border"
                        :class="activeTab === 'keamanan'
                            ? 'bg-[#8E1E26] text-[#FAF5E8] border-[#8E1E26] shadow-xs'
                            : 'bg-[#FAF5E8] dark:bg-[#2E211C] text-[#3A221B] dark:text-[#FAF5E8] border-[#3C2415]/15 dark:border-white/10 hover:border-[#8E1E26]/40'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Ganti Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ 2-COLUMN PROFILE WORKSPACE ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: Section Tabs + Account Overview Card --}}
        <div class="lg:col-span-4 space-y-5">
            {{-- Navigation Menu Card (horizontal scroll pills on mobile, vertical menu on desktop) --}}
            <div class="rounded-[22px] border border-[#3C2415]/12 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] p-3 shadow-[0_2px_10px_-2px_rgba(58,34,27,0.05)]">
                <p class="hidden lg:block px-3 pt-1.5 pb-2.5 text-[11px] font-bold uppercase tracking-[0.14em] text-[#8A766B] dark:text-[#EACE80]/75">
                    Pengaturan Akun
                </p>
                <div class="flex lg:flex-col gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" @click="activeTab = 'profil'"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition text-left shrink-0 lg:w-full"
                        :class="activeTab === 'profil'
                            ? 'bg-[#FCEEEF] dark:bg-[#8E1E26]/30 text-[#8E1E26] dark:text-[#EACE80] font-bold'
                            : 'text-[#4A2E27] dark:text-[#E8DDD2] hover:bg-[#FAF5E8] dark:hover:bg-white/5'">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                            :class="activeTab === 'profil' ? 'bg-[#8E1E26] text-[#FAF5E8]' : 'bg-[#FAF5E8] dark:bg-[#2E211C] text-[#6B574C] dark:text-[#C5B5A8]'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <span class="flex-1">Informasi Profil</span>
                    </button>

                    <button type="button" @click="activeTab = 'keamanan'"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition text-left shrink-0 lg:w-full"
                        :class="activeTab === 'keamanan'
                            ? 'bg-[#FCEEEF] dark:bg-[#8E1E26]/30 text-[#8E1E26] dark:text-[#EACE80] font-bold'
                            : 'text-[#4A2E27] dark:text-[#E8DDD2] hover:bg-[#FAF5E8] dark:hover:bg-white/5'">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                            :class="activeTab === 'keamanan' ? 'bg-[#8E1E26] text-[#FAF5E8]' : 'bg-[#FAF5E8] dark:bg-[#2E211C] text-[#6B574C] dark:text-[#C5B5A8]'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <span class="flex-1">Keamanan &amp; Password</span>
                    </button>

                    <button type="button" @click="activeTab = 'akun'"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition text-left shrink-0 lg:w-full"
                        :class="activeTab === 'akun'
                            ? 'bg-[#FCEEEF] dark:bg-[#8E1E26]/30 text-[#8E1E26] dark:text-[#F09A9E] font-bold'
                            : 'text-[#6B574C] dark:text-[#C5B5A8] hover:bg-[#FAF5E8] dark:hover:bg-white/5'">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                            :class="activeTab === 'akun' ? 'bg-[#8E1E26] text-[#FAF5E8]' : 'bg-[#FAF5E8] dark:bg-[#2E211C] text-[#8E1E26] dark:text-[#F09A9E]'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </span>
                        <span class="flex-1">Hapus Akun</span>
                    </button>
                </div>
            </div>

            {{-- Ringkasan Detail Akun Card --}}
            <div class="relative overflow-hidden rounded-[22px] border border-[#3C2415]/12 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] p-5 shadow-[0_2px_10px_-2px_rgba(58,34,27,0.05)]">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt=""
                    class="pointer-events-none select-none absolute -bottom-4 -right-3 w-20 h-20 opacity-[0.12] -rotate-12">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-base font-bold text-[#3A221B] dark:text-[#FAF5E8]">Ringkasan Akun</h3>
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#ECFDF3] dark:bg-[#2C5835]/25 px-2.5 py-0.5 text-[11px] font-bold text-[#2C5835] dark:text-green-300">
                </div>

                <dl class="space-y-3 text-sm relative z-10">
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-[#3C2415]/8 dark:border-white/8">
                        <dt class="text-xs font-medium text-[#7D6A60] dark:text-[#B9A79B]">Jabatan / Role</dt>
                        <dd class="font-bold text-[#3A221B] dark:text-[#FAF5E8] text-right">{{ $primaryRole }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-[#3C2415]/8 dark:border-white/8">
                        <dt class="text-xs font-medium text-[#7D6A60] dark:text-[#B9A79B]">Username</dt>
                        <dd class="font-semibold text-[#3A221B] dark:text-[#FAF5E8] text-right">{{ '@' . ($user->username ?? '-') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-[#3C2415]/8 dark:border-white/8">
                        <dt class="text-xs font-medium text-[#7D6A60] dark:text-[#B9A79B]">Email</dt>
                        <dd class="font-semibold text-[#3A221B] dark:text-[#FAF5E8] text-right truncate max-w-[180px]">{{ $user->email ?: 'Belum diatur' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-[#3C2415]/8 dark:border-white/8">
                        <dt class="text-xs font-medium text-[#7D6A60] dark:text-[#B9A79B]">Bergabung Sejak</dt>
                        <dd class="font-semibold text-[#3A221B] dark:text-[#FAF5E8] text-right">{{ $joinedDate }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <dt class="text-xs font-medium text-[#7D6A60] dark:text-[#B9A79B]">Diperbarui</dt>
                        <dd class="font-medium text-[#6B574C] dark:text-[#C5B5A8] text-xs text-right">{{ $updatedDate }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- RIGHT COLUMN: Active Settings Panel --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- TAB 1: INFORMASI PROFIL --}}
            <div x-show="activeTab === 'profil'" x-transition.opacity
                class="rounded-[22px] border border-[#3C2415]/12 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] shadow-[0_2px_10px_-2px_rgba(58,34,27,0.05)] overflow-hidden">
                <div class="px-6 py-5 border-b border-[#3C2415]/10 dark:border-white/10 flex items-center justify-between gap-4 bg-[#FAF5E8]/55 dark:bg-[#2E211C]/40">
                    <div>
                        <h3 class="font-display text-lg font-bold text-[#3A221B] dark:text-[#FAF5E8]">Informasi Profil</h3>
                        <p class="mt-0.5 text-xs sm:text-sm text-[#7D6A60] dark:text-[#C5B5A8]">
                            Perbarui nama lengkap, username login, dan alamat email akun admin Anda.
                        </p>
                    </div>
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-8 h-8 shrink-0 hidden sm:block opacity-85">
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="p-5 sm:p-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="profile_name" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Nama Lengkap
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8A766B]">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-10 pr-4 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                            </div>
                            @error('name')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_username" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Username
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-[#8A766B]">@</span>
                                <input id="profile_username" type="text" name="username" value="{{ old('username', $user->username) }}" required
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-9 pr-4 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                            </div>
                            @error('username')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="profile_email" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Alamat Email
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8A766B]">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                    placeholder="nama@daisybakery.id"
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-10 pr-4 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-[#3C2415]/10 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-xs text-[#8A766B] dark:text-[#B9A79B]">
                            Username digunakan saat masuk ke halaman kasir &amp; admin Daisy Bakery.
                        </p>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8E1E26] hover:bg-[#741A21] px-6 py-2.5 text-sm font-bold text-[#FAF5E8] shadow-[0_6px_16px_-6px_rgba(142,30,38,0.55)] transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 2: KEAMANAN & PASSWORD --}}
            <div x-show="activeTab === 'keamanan'" x-cloak x-transition.opacity
                class="rounded-[22px] border border-[#3C2415]/12 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] shadow-[0_2px_10px_-2px_rgba(58,34,27,0.05)] overflow-hidden">
                <div class="px-6 py-5 border-b border-[#3C2415]/10 dark:border-white/10 flex items-center justify-between gap-4 bg-[#FAF5E8]/55 dark:bg-[#2E211C]/40">
                    <div>
                        <h3 class="font-display text-lg font-bold text-[#3A221B] dark:text-[#FAF5E8]">Keamanan &amp; Password</h3>
                        <p class="mt-0.5 text-xs sm:text-sm text-[#7D6A60] dark:text-[#C5B5A8]">
                            Gunakan password yang kuat dan unik untuk menjaga keamanan operasional toko.
                        </p>
                    </div>
                    <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="w-8 h-8 shrink-0 hidden sm:block opacity-85">
                </div>

                <form action="{{ route('password.update') }}" method="POST" class="p-5 sm:p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="current_password" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Password Saat Ini
                            </label>
                            <div class="relative">
                                <input id="current_password" :type="showCurPass ? 'text' : 'password'" name="current_password"
                                    placeholder="Masukkan password saat ini"
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-4 pr-11 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                                <button type="button" @click="showCurPass = !showCurPass"
                                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-[#8A766B] hover:text-[#3A221B] dark:hover:text-[#FAF5E8]"
                                    aria-label="Tampilkan password">
                                    <svg x-show="!showCurPass" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showCurPass" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input id="new_password" :type="showNewPass ? 'text' : 'password'" name="password"
                                    placeholder="Minimal 8 karakter"
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-4 pr-11 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                                <button type="button" @click="showNewPass = !showNewPass"
                                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-[#8A766B] hover:text-[#3A221B] dark:hover:text-[#FAF5E8]"
                                    aria-label="Tampilkan password baru">
                                    <svg x-show="!showNewPass" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showNewPass" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Konfirmasi Password Baru
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" :type="showConfPass ? 'text' : 'password'" name="password_confirmation"
                                    placeholder="Ulangi password baru"
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-4 pr-11 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                                <button type="button" @click="showConfPass = !showConfPass"
                                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-[#8A766B] hover:text-[#3A221B] dark:hover:text-[#FAF5E8]"
                                    aria-label="Tampilkan konfirmasi password">
                                    <svg x-show="!showConfPass" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showConfPass" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl bg-[#FAF5E8] dark:bg-[#2E211C] border border-[#3C2415]/10 dark:border-white/10 p-3.5 flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-[#EACE80]/40 text-[#3A221B] dark:text-[#EACE80] shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <p class="text-xs text-[#6B574C] dark:text-[#C5B5A8] leading-relaxed">
                            Tips keamanan: Kombinasikan huruf besar, huruf kecil, dan angka. Hindari menggunakan password yang sama dengan akun lain.
                        </p>
                    </div>

                    <div class="mt-6 pt-5 border-t border-[#3C2415]/10 dark:border-white/10 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8E1E26] hover:bg-[#741A21] px-6 py-2.5 text-sm font-bold text-[#FAF5E8] shadow-[0_6px_16px_-6px_rgba(142,30,38,0.55)] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- TAB 3: HAPUS AKUN --}}
            <div x-show="activeTab === 'akun'" x-cloak x-transition.opacity
                class="rounded-[22px] border border-[#8E1E26]/22 dark:border-[#8E1E26]/40 bg-[#FFFDF9] dark:bg-[#241A16] shadow-[0_2px_10px_-2px_rgba(142,30,38,0.08)] overflow-hidden">
                <div class="px-6 py-5 border-b border-[#8E1E26]/12 bg-[#FCEEEF]/70 dark:bg-[#8E1E26]/20 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-display text-lg font-bold text-[#8E1E26] dark:text-[#F09A9E]">Hapus Akun Permanen</h3>
                        <p class="mt-0.5 text-xs sm:text-sm text-[#6B574C] dark:text-[#C5B5A8]">
                            Tindakan ini tidak dapat dibatalkan dan akan menghapus akses akun Anda secara permanen.
                        </p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="rounded-xl bg-[#FCEEEF]/60 dark:bg-[#8E1E26]/15 border border-[#8E1E26]/18 p-4 mb-5 flex items-start gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-[#8E1E26] text-[#FAF5E8] shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <p class="text-xs sm:text-sm text-[#5C2528] dark:text-[#F5C5C8] leading-relaxed">
                            Setelah akun dihapus, semua kredensial login Anda akan hilang permanen. Masukkan password saat ini untuk mengonfirmasi kepemilikan akun sebelum melanjutkan.
                        </p>
                    </div>

                    <form x-ref="formHapusAkun" action="{{ route('profile.destroy') }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="max-w-md">
                            <label for="delete_password" class="mb-2 block text-xs font-bold uppercase tracking-wider text-[#5C4338] dark:text-[#EACE80]">
                                Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input id="delete_password" :type="showDelPass ? 'text' : 'password'" name="password"
                                    placeholder="Masukkan password Anda"
                                    class="w-full rounded-xl border border-[#3C2415]/18 dark:border-white/15 bg-[#FAF5E8]/60 dark:bg-[#1A120F] pl-4 pr-11 py-3 text-sm font-medium text-[#231815] dark:text-[#FAF5E8] focus:bg-[#FFFDF9] dark:focus:bg-[#2E211C] transition">
                                <button type="button" @click="showDelPass = !showDelPass"
                                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-[#8A766B] hover:text-[#3A221B] dark:hover:text-[#FAF5E8]"
                                    aria-label="Tampilkan password konfirmasi">
                                    <svg x-show="!showDelPass" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showDelPass" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password', 'userDeletion')
                                <p class="mt-1.5 text-xs font-semibold text-[#8E1E26] dark:text-[#F09A9E]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6">
                            <button type="button" @click="modalHapusTerbuka = true"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8E1E26] hover:bg-[#6B141B] px-5 py-2.5 text-sm font-bold text-[#FAF5E8] shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus Akun Saya
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS AKUN --}}
    <div x-show="modalHapusTerbuka" x-cloak x-transition
        class="fixed inset-0 bg-[#231815]/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
        <div @click.away="modalHapusTerbuka = false"
            class="bg-[#FFFDF9] dark:bg-[#241A16] rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden border border-[#3C2415]/15 dark:border-white/10">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-[#FCEEEF] dark:bg-[#8E1E26]/25 text-[#8E1E26] dark:text-[#F09A9E] flex items-center justify-center rounded-2xl mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-[#3A221B] dark:text-[#FAF5E8] text-lg">Hapus Akun?</h3>
                <p class="text-xs sm:text-sm text-[#6B574C] dark:text-[#C5B5A8] mt-2 leading-relaxed">
                    Yakin ingin menghapus akun ini secara permanen? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="bg-[#FAF5E8] dark:bg-[#1A120F] p-4 border-t border-[#3C2415]/10 dark:border-white/10 flex justify-center gap-3">
                <button type="button" @click="modalHapusTerbuka = false"
                    class="flex-1 px-4 py-2.5 bg-[#EFE5D6] dark:bg-[#2E211C] hover:bg-[#E5D7C4] dark:hover:bg-[#3A2A23] font-bold text-xs rounded-xl transition text-[#3A221B] dark:text-[#FAF5E8]">
                    Batal
                </button>
                <button type="button" @click="$refs.formHapusAkun.submit(); modalHapusTerbuka = false"
                    class="flex-1 px-4 py-2.5 bg-[#8E1E26] hover:bg-[#6B141B] text-[#FAF5E8] font-bold text-xs rounded-xl transition shadow-xs">
                    Ya, Hapus Akun
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
