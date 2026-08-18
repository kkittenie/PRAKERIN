@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Member" />

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{
            search: '',
            modalHapusTerbuka: false,
            formTerpilih: null,
            namaHapus: '',

            modalResetTerbuka: false,
            formResetTerpilih: null,
            namaReset: '',
            passwordBaruReset: '',

            bukaModalHapus(form, nama) {
                this.formTerpilih = form;
                this.namaHapus = nama;
                this.modalHapusTerbuka = true;
            },

            konfirmasiHapus() {
                if (this.formTerpilih) {
                    this.formTerpilih.submit();
                }

                this.modalHapusTerbuka = false;
            },

            bukaModalReset(form, nama, passwordBaru) {
                this.formResetTerpilih = form;
                this.namaReset = nama;
                this.passwordBaruReset = passwordBaru;
                this.modalResetTerbuka = true;
            },

            konfirmasiReset() {
                if (this.formResetTerpilih) {
                    this.formResetTerpilih.submit();
                }

                this.modalResetTerbuka = false;
            },

            memberCocok(nama, username) {
                const keyword = this.search.trim().toLowerCase();

                if (keyword === '') {
                    return true;
                }

                return nama.toLowerCase().includes(keyword) ||
                       username.toLowerCase().includes(keyword);
            },

            memberAda() {
                const keyword = this.search.trim().toLowerCase();

                if (keyword === '') {
                    return true;
                }

                return Array.from(
                    this.$root.querySelectorAll('[data-member]')
                ).some(el => {
                    const nama = el.dataset.nama.toLowerCase();
                    const username = (el.dataset.username || '').toLowerCase();

                    return nama.includes(keyword) || username.includes(keyword);
                });
            }
        }">

        {{-- Header --}}
        <div class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

            {{-- Search --}}
            <div class="relative w-full sm:w-[275px]">

                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4.5 w-4.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />

                    </svg>
                </span>

                <input type="text"
                    x-model="search"
                    placeholder="Cari nama atau username..."
                    class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-4 text-sm text-gray-700 placeholder:text-gray-400 outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">

            </div>

            {{-- Tambah Member --}}
            @can('tambah_member')
                <a href="{{ route('member.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4" />

                    </svg>

                    Tambah Member

                </a>
            @endcan

        </div>


        {{-- Table --}}
        <div class="overflow-x-auto p-6">

            <table class="w-full text-left text-sm">

                <thead>
                    <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">

                        {{-- Mobile expand --}}
                        <th class="w-8 px-2 py-3 text-center sm:hidden"></th>

                        <th class="py-3">
                            Nama
                        </th>

                        <th class="hidden py-3 md:table-cell">
                            Username
                        </th>

                        <th class="hidden py-3 md:table-cell">
                            No. HP
                        </th>

                        <th class="hidden py-3 lg:table-cell">
                            Email
                        </th>
                        
                        <th class="hidden py-3 sm:table-cell">
                            Poin
                        </th>

                        <th class="py-3">
                            Level
                        </th>

                        <th class="py-3 text-right">
                            Aksi
                        </th>

                    </tr>
                </thead>


                @foreach ($members as $member)

                    @php
                        $levelClasses = match (strtolower($member->level ?? '')) {
                            'gold' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                            'silver' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                            'bronze' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp

                    <tbody
                        data-member
                        data-nama="{{ $member->nama }}"
                        data-no-hp="{{ $member->no_hp }}"
                        data-username="{{ $member->username }}"
                        x-data="{ open: false }"
                        x-show="memberCocok(
                            @js($member->nama ?? ''),
                            @js($member->username ?? '')
                        )"
                        class="divide-y divide-gray-100 border-b border-gray-100 dark:divide-gray-800 dark:border-gray-800">

                        {{-- Main Row --}}
                        <tr class="transition hover:bg-amber-50/30 dark:hover:bg-amber-950/20">

                            {{-- Mobile Expand --}}
                            <td class="w-8 px-2 py-3.5 text-center sm:hidden">

                                <button
                                    @click="open = !open"
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 transition-all duration-200 dark:bg-amber-500/20 dark:text-amber-400"
                                    :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">

                                    <span x-text="open ? '−' : '+'"></span>

                                </button>

                            </td>


                            {{-- Nama --}}
                            <td class="py-3.5 font-medium text-gray-800 dark:text-white/90">
                                {{ $member->nama }}
                            </td>


                            {{-- Username --}}
                            <td class="hidden py-3.5 text-gray-600 md:table-cell dark:text-gray-400">
                                {{ $member->username ? '@' . $member->username : '-' }}
                            </td>


                            {{-- No HP --}}
                            <td class="hidden py-3.5 text-gray-600 md:table-cell dark:text-gray-400">
                                {{ $member->no_hp }}
                            </td>


                            {{-- Email --}}
                            <td class="hidden py-3.5 text-gray-600 lg:table-cell dark:text-gray-400">
                                {{ $member->email ?: '-' }}
                            </td>


                            {{-- Poin --}}
                            <td class="hidden py-3.5 text-gray-600 sm:table-cell dark:text-gray-400">
                                {{ number_format($member->poin ?? 0, 0, ',', '.') }}
                            </td>


                            {{-- Level --}}
                            <td class="py-3.5">

                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $levelClasses }}">

                                    {{ ucfirst($member->level ?? '-') }}

                                </span>

                            </td>


                            {{-- Aksi --}}
                            <td class="whitespace-nowrap py-3.5 text-right">

                                <div class="flex items-center justify-end gap-2">

                                    @can('edit_member')

                                        <a href="{{ route('member.edit', $member) }}"
                                            class="text-xs font-semibold text-amber-600 hover:underline sm:text-sm dark:text-amber-400">

                                            Edit

                                        </a>

                                    @endcan


                                    @can('edit_member')

                                        <form
                                            action="{{ route('member.reset-password', $member) }}"
                                            method="POST"
                                            class="inline">

                                            @csrf

                                            <!-- <button
                                                type="button"
                                                @click="bukaModalReset(
                                                    $el.closest('form'),
                                                    @js($member->nama),
                                                    @js(\App\Models\Member::toLocalNoHp($member->no_hp))
                                                )"
                                                class="text-xs font-semibold text-blue-600 hover:underline sm:text-sm dark:text-blue-400">

                                                Reset Password

                                            </button> -->

                                        </form>

                                    @endcan


                                    @can('hapus_member')

                                        <form
                                            action="{{ route('member.destroy', $member) }}"
                                            method="POST"
                                            class="inline">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="button"
                                                @click="bukaModalHapus(
                                                    $el.closest('form'),
                                                    @js($member->nama)
                                                )"
                                                class="text-xs font-semibold text-red-500 hover:underline sm:text-sm">

                                                Hapus

                                            </button>

                                        </form>

                                    @endcan

                                </div>

                            </td>

                        </tr>


                        {{-- Mobile Detail --}}
                        <tr
                            x-show="open"
                            x-cloak
                            x-transition
                            class="bg-amber-50/50 text-xs dark:bg-amber-950/30 sm:hidden">

                            <td colspan="6" class="p-3.5">

                                <div class="grid grid-cols-2 gap-3 text-gray-600 dark:text-gray-300">

                                    {{-- Username --}}
                                    <div>

                                        <span class="text-gray-400">
                                            Username:
                                        </span>

                                        <span class="font-semibold text-gray-800 dark:text-white">
                                            {{ $member->username ? '@' . $member->username : '-' }}
                                        </span>

                                    </div>


                                    {{-- No HP --}}
                                    <div>

                                        <span class="text-gray-400">
                                            No. HP:
                                        </span>

                                        <span class="font-semibold text-gray-800 dark:text-white">
                                            {{ $member->no_hp }}
                                        </span>

                                    </div>


                                    {{-- Poin --}}
                                    <div>

                                        <span class="text-gray-400">
                                            Poin:
                                        </span>

                                        <span class="font-bold text-amber-700 dark:text-amber-400">
                                            {{ number_format($member->poin ?? 0, 0, ',', '.') }}
                                        </span>

                                    </div>


                                    {{-- Email --}}
                                    <div class="col-span-2">

                                        <span class="text-gray-400">
                                            Email:
                                        </span>

                                        <span class="font-semibold text-gray-800 dark:text-white">
                                            {{ $member->email ?: '-' }}
                                        </span>

                                    </div>


                                    {{-- Tanggal Lahir --}}
                                    <div>

                                        <span class="text-gray-400">
                                            Tanggal Lahir:
                                        </span>

                                        <span class="font-semibold text-gray-800 dark:text-white">
                                            {{ $member->tanggal_lahir
                                                ? \Carbon\Carbon::parse($member->tanggal_lahir)->format('d/m/Y')
                                                : '-' }}
                                        </span>

                                    </div>


                                    {{-- Level --}}
                                    <div>

                                        <span class="text-gray-400">
                                            Level:
                                        </span>

                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $levelClasses }}">

                                            {{ ucfirst($member->level ?? '-') }}

                                        </span>

                                    </div>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                @endforeach

            </table>


            {{-- Data tidak ditemukan --}}
            <div
                x-show="search.trim() !== '' && !memberAda()"
                x-cloak
                class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">

                Member tidak ditemukan.

            </div>

        </div>


        {{-- Modal Reset Password --}}
        <div
            x-show="modalResetTerbuka"
            x-cloak
            x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">

            <div
                @click.away="modalResetTerbuka = false"
                class="w-full max-w-sm overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">

                <div class="p-6 text-center">

                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-2xl font-bold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">

                        ⟳

                    </div>

                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                        Reset Password Member?
                    </h3>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">

                        Password member

                        <span
                            x-text="namaReset"
                            class="font-semibold text-gray-700 dark:text-gray-300">
                        </span>

                        akan dikembalikan ke default:

                        <span
                            x-text="passwordBaruReset"
                            class="font-semibold text-gray-700 dark:text-gray-300">
                        </span>

                        (sama dengan nomor HP).

                    </p>

                </div>


                <div
                    class="flex justify-center gap-3 border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">

                    <button
                        type="button"
                        @click="modalResetTerbuka = false"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">

                        Batal

                    </button>


                    <button
                        type="button"
                        @click="konfirmasiReset()"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700">

                        Reset Password

                    </button>

                </div>

            </div>

        </div>


        {{-- Modal Hapus --}}
        <div
            x-show="modalHapusTerbuka"
            x-cloak
            x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">

            <div
                @click.away="modalHapusTerbuka = false"
                class="w-full max-w-sm overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">

                <div class="p-6 text-center">

                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl font-bold text-red-600 dark:bg-red-500/10 dark:text-red-400">

                        ⚠

                    </div>

                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                        Hapus Member?
                    </h3>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">

                        Member

                        <span
                            x-text="namaHapus"
                            class="font-semibold text-gray-700 dark:text-gray-300">
                        </span>

                        akan dihapus dan tidak dapat dipulihkan kembali.

                    </p>

                </div>


                <div
                    class="flex justify-center gap-3 border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">

                    <button
                        type="button"
                        @click="modalHapusTerbuka = false"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">

                        Batal

                    </button>


                    <button
                        type="button"
                        @click="konfirmasiHapus()"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-red-700">

                        Hapus

                    </button>

                </div>

            </div>

        </div>

    </div>
@endsection